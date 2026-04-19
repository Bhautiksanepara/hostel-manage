<?php
/**
 * UPI QR Code Generator Class
 * 
 * Generates UPI payment QR codes for fixed amounts
 * Uses the UPI deep linking scheme: upi://pay?...
 * 
 * Requirements: PHP GD Library
 */

class UPIQRCodeGenerator {
    private $conn;
    private $upi_id = 'pateldham@upi';
    private $receiving_name = 'Pateldham Hostel';
    private $merchant_category = 'Education';
    
    public function __construct($db_connection = null) {
        if ($db_connection) {
            $this->conn = $db_connection;
            $this->loadUPIConfig();
        }
    }
    
    /**
     * Load UPI configuration from database
     */
    private function loadUPIConfig() {
        if (!$this->conn) return false;
        
        $result = $this->conn->query("SELECT upi_id, receiving_name, merchant_category FROM upi_config WHERE is_active = 1 LIMIT 1");
        if ($result && $result->num_rows > 0) {
            $config = $result->fetch_assoc();
            $this->upi_id = $config['upi_id'];
            $this->receiving_name = $config['receiving_name'];
            $this->merchant_category = $config['merchant_category'];
            return true;
        }

        // Keep QR generation usable even when the optional upi_config table
        // has not been imported yet. Admin can still override this later.
        error_log("UPI config not found; using default Pateldham Hostel UPI configuration.");
        return false;
    }
    
    /**
     * Set UPI ID manually
     */
    public function setUPIID($upi_id, $name = '', $category = 'Education') {
        $this->upi_id = $upi_id;
        $this->receiving_name = $name;
        $this->merchant_category = $category;
    }
    
    /**
     * Generate UPI Payment URL with locked amount
     * 
     * @param float $amount Amount in rupees
     * @param string $otr_number Student OTR number (reference)
     * @param string $student_name Student name (optional)
     * @return string UPI payment URL
     */
    public function generateUPIURL($amount, $otr_number, $student_name = 'Student') {
        if (!$this->upi_id) {
            return null;
        }
        
        // Format: upi://pay?pa=UPI_ID&pn=NAME&am=AMOUNT&tn=DESCRIPTION&tr=REFERENCE
        $upi_url = sprintf(
            'upi://pay?pa=%s&pn=%s&am=%.2f&cu=INR&tn=%s&tr=%s&mc=%s',
            urlencode($this->upi_id),
            urlencode($this->receiving_name),
            $amount,
            urlencode('Hostel Fee Payment'),
            urlencode($otr_number),
            urlencode($this->merchant_category)
        );
        
        return $upi_url;
    }

    /**
     * Generate QR Server image URL.
     *
     * This is useful as a fallback when PHP cannot fetch remote images because
     * allow_url_fopen or outbound SSL/network access is disabled on the server.
     */
    public function generateQRCodeImageURL($amount, $otr_number, $student_name = 'Student', $size = 300) {
        $upi_url = $this->generateUPIURL($amount, $otr_number, $student_name);

        if (!$upi_url) {
            return null;
        }

        return "https://api.qrserver.com/v1/create-qr-code/?" . http_build_query([
            'size' => "{$size}x{$size}",
            'data' => $upi_url,
        ]);
    }
    
    /**
     * Generate QR Code as Base64 encoded image
     * Uses QR Server API (backup: PHP QR Library if needed)
     * 
     * @param float $amount Amount in rupees
     * @param string $otr_number Student OTR number
     * @param string $student_name Student name
     * @param int $size QR code size in pixels
     * @return string Base64 encoded QR code image
     */
    public function generateQRCodeBase64($amount, $otr_number, $student_name = 'Student', $size = 300) {
        $upi_url = $this->generateUPIURL($amount, $otr_number, $student_name);
        
        if (!$upi_url) {
            return null;
        }
        
        // Use QR Server API (more reliable than Google Charts).
        $qr_url = $this->generateQRCodeImageURL($amount, $otr_number, $student_name, $size);
        
        // Set timeout and error handling
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'HostelManagement/1.0'
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ]);
        
        // Fetch QR code image
        $qr_image = @file_get_contents($qr_url, false, $context);
        
        if ($qr_image === false) {
            // Fallback: Log error and return null
            error_log("QR Code generation failed for OTR: $otr_number");
            return null;
        }
        
        // Verify it's a valid PNG
        if (substr($qr_image, 0, 4) !== "\x89PNG") {
            error_log("QR Server returned invalid PNG for OTR: $otr_number");
            return null;
        }
        
        // Convert to base64
        $base64_image = 'data:image/png;base64,' . base64_encode($qr_image);
        
        return $base64_image;
    }
    
    /**
     * Generate QR Code and save as file
     * 
     * @param float $amount Amount in rupees
     * @param string $otr_number Student OTR number
     * @param string $student_name Student name
     * @param string $save_path Path to save QR code
     * @param int $size QR code size in pixels
     * @return bool Success status
     */
    public function generateQRCodeFile($amount, $otr_number, $student_name = 'Student', $save_path = null, $size = 300) {
        $upi_url = $this->generateUPIURL($amount, $otr_number, $student_name);
        
        if (!$upi_url) {
            return false;
        }
        
        // Use Google Charts API
        $qr_url = "https://chart.googleapis.com/chart?" . http_build_query([
            'chs' => "{$size}x{$size}",
            'chld' => 'M|0',
            'cht' => 'qr',
            'chl' => urlencode($upi_url),
        ]);
        
        // Set default save path
        if (!$save_path) {
            $save_path = __DIR__ . '/../../../uploads/qr_codes/' . $otr_number . '.png';
            
            // Create directory if not exists
            $dir = dirname($save_path);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
        }
        
        // Download and save QR code
        $qr_image = @file_get_contents($qr_url);
        
        if ($qr_image === false) {
            return false;
        }
        
        if (file_put_contents($save_path, $qr_image)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get UPI configuration details
     */
    public function getConfig() {
        return [
            'upi_id' => $this->upi_id,
            'receiving_name' => $this->receiving_name,
            'merchant_category' => $this->merchant_category
        ];
    }
}

// Example usage:
// $qr_gen = new UPIQRCodeGenerator($conn);
// $qr_base64 = $qr_gen->generateQRCodeBase64(15000, '240001', 'Student Name');
// $upi_url = $qr_gen->generateUPIURL(15000, '240001', 'Student Name');
?>

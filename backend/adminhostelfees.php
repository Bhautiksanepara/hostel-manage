<?php
session_start();
require_once('../../../fpdf186/fpdf.php');
require_once('../../../tfpdf/tfpdf.php');
require_once('../../../tFPDF/font/ttfonts.php');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hostel_manage";

$conn = new mysqli($servername, $username, $password, $dbname);
// $otr = $_SESSION['otr_number'];
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== 1 || $_SESSION['role'] !== 'admin') {
    header('Location: ../../../frontend/user/pages/login.php');
    exit();
}
// $otr = $_SESSION['otr_number'];
function generatePDFReceipt($receiptData) {
    global $conn;
    $otr = $receiptData['otr_number'];
    // Ensure the receipts folder exists inside uploads directory
    if (!file_exists('../../../uploads/receipts/')) {
        mkdir('../../../uploads/receipts/', 0777, true);
    }
   
    $pdf = new tFPDF();
    $pdf->AddPage();
    // $pdf->Image('../../../photos/logo.png',10,10,20);
    // $pdf->Image('../../../photos/sd.png', 60, 7, 100);
    // $pdf->Image('../../../photos/statue.png', 180, 10, 10);
    $pdf->AddFont('NotoSansGujarati', '', 'NotoSansGujarati.ttf', true);
    $pdf->SetFont('NotoSansGujarati', '', 14, true);
    $pdf->Ln(10);

    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(50, 10, 'Registration ID:', 0, 0,'C');
    $pdf->Cell(50, 10, $receiptData['registration_id'], 0, 1,'C');

    $pdf->Cell(50, 10, 'Name:', 0, 0,'C');
    $pdf->Cell(50, 10, $receiptData['name'], 0, 1,'C');

    $pdf->Cell(50, 10, 'OTR Number:', 0, 0,'C');
    $pdf->Cell(50, 10, $otr, 0, 1,'C');

    $pdf->Cell(50, 10, 'Receipt Date:', 0, 0,'C');
    $pdf->Cell(50, 10, $receiptData['receipt_date'], 0, 1,'C');

    $pdf->Cell(50, 10, 'Academic Year:', 0, 0,'C');
    $pdf->Cell(50, 10, $receiptData['academic_year'], 0, 1,'C');

    $pdf->Cell(50, 10, 'Valid Up To:', 0, 0,'C');
    $pdf->Cell(50, 10, $receiptData['valid_until'], 0, 1,'C');
    $pdf->Ln(10);

    // Add Fee Breakdown
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(15, 10, 'Sr.No.', 1, 0, 'R');
    $pdf->Cell(80, 10, 'Description', 1, 0, 'C');
    $pdf->Cell(30, 10, 'HSN/SAC', 1, 0, 'C');
    $pdf->Cell(30, 10, 'Amount', 1, 1, 'C');

    $pdf->SetFont('Arial', '', 10);
    foreach ($receiptData['fee_breakdown'] as $index => $fee) {
        $pdf->Cell(15, 10, $index + 1, 1, 0, 'C');
        $pdf->Cell(80, 10, $fee['description'], 1, 0,'C');
        $pdf->Cell(30, 10, $fee['hsn_sac'], 1, 0, 'C');
        $pdf->Cell(30, 10, number_format($fee['amount'], 2), 1, 1, 'R');
    }

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(125, 10, 'Total Amount', 1, 0, 'R');
    $pdf->Cell(30, 10, number_format($receiptData['total_amount'], 2), 1, 1, 'R');
    $pdf->Ln(10);

    // Add Payment Details
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(50, 10, 'Payment By:', 0, 0);
    $pdf->Cell(50, 10, $receiptData['payment_method'], 0, 1);
    $pdf->Cell(50, 10, 'Transaction Number:', 0, 0);
    $pdf->Cell(50, 10, $receiptData['transaction_number'], 0, 1);
    $pdf->Ln(10);

    // Amount in Words
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 10, 'Total Amount in Words: ' . ucwords($receiptData['amount_in_words']), 0, 1, 'C');

    // Save the PDF. Store a web-root relative path so student downloads work
    // from any page depth.
    $receipt_file_path = '../../../uploads/receipts/' . $receiptData['otr_number'] . '.pdf';
    $receipt_path = 'uploads/receipts/' . $receiptData['otr_number'] . '.pdf';
    $pdf->Output('F', $receipt_file_path);

    // Update database with the path to the generated PDF receipt
    $stmt = $conn->prepare("UPDATE receipts SET receipt_path = ? WHERE id = ?");
    $stmt->bind_param("si", $receipt_path, $receiptData['id']);
    $stmt->execute();
    $stmt->close();
 

}

// Handle approval or rejection
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $receipt_id = $_POST['receipt_id'];
    $action = $_POST['action'];

    if ($action == 'approve') {
        // Fetch receipt data
        $receipt_query = $conn->prepare("SELECT r.otr_number, r.upi_id, r.transaction_id, r.amount, u.firstName FROM receipts r INNER JOIN users u ON r.otr_number = u.otr_number WHERE r.id = ?");
        $receipt_query->bind_param("i", $receipt_id);
        $receipt_query->execute();
        $receipt_result = $receipt_query->get_result();
        $receipt_data = $receipt_result->fetch_assoc();

        if ($receipt_data) {
            $stmt = $conn->prepare("UPDATE receipts SET status = 'approved' WHERE id = ?");
            $stmt->bind_param("i", $receipt_id);
            $stmt->execute();
            $stmt->close();

            $stmt2 = $conn->prepare("UPDATE fees SET status = 'paid', payment_date = NOW() WHERE otr_number = ? AND status = 'pending'");
            $stmt2->bind_param("s", $receipt_data['otr_number']);
            $stmt2->execute();
            $stmt2->close();

            $receiptData = [
                'id' => $receipt_id,
                'registration_id' => 'SDH'.$receipt_data['otr_number'],
                'name' => $receipt_data['firstName'],
                'otr_number' => $receipt_data['otr_number'],
                'receipt_date' => date('Y-m-d'),
                'academic_year' => '2024-2025',
                'valid_until' => '2025-05-31',
                'fee_breakdown' => [
                    ['description' => 'Accommodation Fee', 'hsn_sac' => '', 'amount' => $receipt_data['amount']],
                ],
                'total_amount' => $receipt_data['amount'],
                'payment_method' => 'UPI : ' . $receipt_data['upi_id'],
                'transaction_number' => $receipt_data['transaction_id'],
                'amount_in_words' => 'forty-three thousand', // Replace with logic to convert amount to words
            ];

            generatePDFReceipt($receiptData);

            // Update user's fee status
            $update_user_stmt = $conn->prepare("UPDATE users SET fees_status = 'paid' WHERE otr_number = ?");
            $update_user_stmt->bind_param("s", $receipt_data['otr_number']);
            $update_user_stmt->execute();
            $update_user_stmt->close();
        }

        $receipt_query->close();
    } else {
        $stmt = $conn->prepare("UPDATE receipts SET status = 'rejected' WHERE id = ?");
        $stmt->bind_param("i", $receipt_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch receipts
$sql = "
SELECT 
    r.id, 
    r.otr_number, 
    r.file_path, 
    r.upi_id, 
    r.transaction_id, 
    r.amount, 
    r.status, 
    u.firstName AS user_name 
FROM 
    receipts r
INNER JOIN 
    users u 
ON 
    r.otr_number = u.otr_number
";

$result = $conn->query($sql);
?>

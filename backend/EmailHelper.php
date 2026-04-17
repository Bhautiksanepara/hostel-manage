<?php
/**
 * Email Helper Class
 * 
 * This class handles all email sending functionality for the Hostel Management System.
 * It uses the email configuration stored in the database.
 * 
 * Usage:
 * $mailer = new EmailHelper();
 * $mailer->send($to_email, $subject, $message, $is_html = true);
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/hostel-manage/PHPMailer/Exception.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/hostel-manage/PHPMailer/PHPMailer.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/hostel-manage/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailHelper {
    private $config;
    private $conn;
    private $errors = [];
    
    public function __construct() {
        $this->conn = new mysqli('localhost', 'root', '', 'hostel_manage');
        if ($this->conn->connect_error) {
            $this->errors[] = "Database connection failed: " . $this->conn->connect_error;
            $this->config = null;
            return;
        }
        
        // Load email configuration from database
        $result = $this->conn->query("SELECT * FROM email_config LIMIT 1");
        if ($result && $result->num_rows > 0) {
            $this->config = $result->fetch_assoc();
        } else {
            $this->errors[] = "Email configuration not found in database";
            $this->config = null;
        }
    }
    
    /**
     * Send an email
     * 
     * @param string $to_email Recipient email address
     * @param string $subject Email subject
     * @param string $message Email message body
     * @param bool $is_html Whether the message is HTML
     * @param array $cc Carbon copy recipients (optional)
     * @param array $bcc Blind carbon copy recipients (optional)
     * @return bool Success status
     */
    public function send($to_email, $subject, $message, $is_html = true, $cc = [], $bcc = []) {
        if (!$this->config) {
            $this->errors[] = "Email configuration is not available";
            return false;
        }
        
        try {
            $mail = new PHPMailer(true);
            
            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host = $this->config['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['smtp_username'];
            $mail->Password = $this->config['smtp_password'];
            $mail->SMTPSecure = $this->config['use_tls'] ? PHPMailer::ENCRYPTION_STARTTLS : '';
            $mail->Port = $this->config['smtp_port'];
            
            // Disable SSL verification for local/self-signed certificates (optional)
            // $mail->SMTPOptions = array(
            //     'ssl' => array(
            //         'verify_peer' => false,
            //         'verify_peer_name' => false,
            //         'allow_self_signed' => true
            //     )
            // );
            
            // Email details
            $mail->setFrom($this->config['from_email'], $this->config['from_name']);
            $mail->addAddress($to_email);
            
            // Add CC and BCC
            foreach ($cc as $cc_email) {
                $mail->addCC($cc_email);
            }
            foreach ($bcc as $bcc_email) {
                $mail->addBCC($bcc_email);
            }
            
            $mail->Subject = $subject;
            $mail->isHTML($is_html);
            $mail->Body = $message;
            
            // Alt body for HTML emails
            if ($is_html) {
                $mail->AltBody = strip_tags($message);
            }
            
            // Send email
            if ($mail->send()) {
                return true;
            } else {
                $this->errors[] = "Failed to send email: " . $mail->ErrorInfo;
                return false;
            }
            
        } catch (Exception $e) {
            $this->errors[] = "Exception: " . $e->getMessage();
            return false;
        }
    }
    
    /**
     * Get errors
     * @return array Array of error messages
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Get last error
     * @return string Last error message
     */
    public function getLastError() {
        return end($this->errors);
    }
    
    /**
     * Check if configuration exists
     * @return bool
     */
    public function isConfigured() {
        return $this->config !== null && count($this->errors) === 0;
    }
    
    /**
     * Send verification email for registration
     */
    public function sendVerificationEmail($email, $name, $token) {
        $verification_link = "http://localhost/hostel-manage/frontend/user/pages/confirm.php?email=" . urlencode($email) . "&token=" . urlencode($token);
        
        $subject = "Confirm Your Email - Pateldham Hostel Management";
        
        $message = "<html><body>";
        $message .= "<h2>Welcome to Pateldham Hostel, $name!</h2>";
        $message .= "<p>Please click the link below to confirm your email address:</p>";
        $message .= "<p><a href='$verification_link' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Confirm Email</a></p>";
        $message .= "<p>Or copy this link: $verification_link</p>";
        $message .= "<p>This link will expire in 24 hours.</p>";
        $message .= "<p>If you didn't register, please ignore this email.</p>";
        $message .= "</body></html>";
        
        return $this->send($email, $subject, $message, true);
    }
    
    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail($email, $name, $token) {
        $reset_link = "http://localhost/hostel-manage/frontend/user/pages/newpassword.php?email=" . urlencode($email) . "&token=" . urlencode($token);
        
        $subject = "Reset Your Password - Pateldham Hostel Management";
        
        $message = "<html><body>";
        $message .= "<h2>Password Reset Request</h2>";
        $message .= "<p>Hi $name,</p>";
        $message .= "<p>We received a request to reset your password. Click the link below to create a new password:</p>";
        $message .= "<p><a href='$reset_link' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Reset Password</a></p>";
        $message .= "<p>Or copy this link: $reset_link</p>";
        $message .= "<p>This link will expire in 24 hours.</p>";
        $message .= "<p>If you didn't request a password reset, please ignore this email.</p>";
        $message .= "</body></html>";
        
        return $this->send($email, $subject, $message, true);
    }
    
    /**
     * Send fee reminder email
     */
    public function sendFeeReminderEmail($email, $student_name, $amount, $due_date) {
        $subject = "Fee Payment Reminder - Pateldham Hostel";
        
        $message = "<html><body>";
        $message .= "<h2>Fee Payment Reminder</h2>";
        $message .= "<p>Hi $student_name,</p>";
        $message .= "<p>This is a reminder that your hostel fees are pending.</p>";
        $message .= "<p><strong>Amount Due:</strong> ₹" . number_format($amount, 2) . "</p>";
        $message .= "<p><strong>Due Date:</strong> $due_date</p>";
        $message .= "<p>Please log into your dashboard to submit your payment receipt.</p>";
        $message .= "<p>Dashboard: <a href='http://localhost/hostel-manage/frontend/user/pages/dashboard.php'>Click here</a></p>";
        $message .= "<p>Thank you!</p>";
        $message .= "</body></html>";
        
        return $this->send($email, $subject, $message, true);
    }
    
    /**
     * Send gate pass approval/rejection email
     */
    public function sendGatePassEmail($email, $student_name, $status, $request_id) {
        $status_text = ($status === 'approved') ? 'Approved' : 'Rejected';
        $subject = "Gate Pass Request $status_text - Pateldham Hostel";
        
        $message = "<html><body>";
        $message .= "<h2>Gate Pass Request Status</h2>";
        $message .= "<p>Hi $student_name,</p>";
        $message .= "<p>Your gate pass request (ID: $request_id) has been <strong>$status_text</strong>.</p>";
        $message .= "<p>Check your dashboard for more details.</p>";
        $message .= "<p>Dashboard: <a href='http://localhost/hostel-manage/frontend/user/pages/gate-pass-status.php'>Click here</a></p>";
        $message .= "</body></html>";
        
        return $this->send($email, $subject, $message, true);
    }
    
    /**
     * Destroy database connection
     */
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

// Example usage:
// $mailer = new EmailHelper();
// if ($mailer->isConfigured()) {
//     if ($mailer->sendVerificationEmail($email, $name, $token)) {
//         echo "Email sent successfully!";
//     } else {
//         echo "Error: " . $mailer->getLastError();
//     }
// } else {
//     echo "Email not configured!";
// }
?>

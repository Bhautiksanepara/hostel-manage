<?php
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/../PHPMailer/Exception.php';
require_once __DIR__ . '/../PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/SMTP.php';

session_start();

function redirectToPendingFees($type, $message) {
    $_SESSION['reminder_flash'] = [
        'type' => $type,
        'message' => $message,
    ];

    header('Location: /hostel-manage/frontend/admin/pages/pendingfees.php');
    exit();
}

function cleanMailerError($message) {
    $message = preg_replace('/[^\P{C}\r\n\t]+/u', '', (string) $message);
    $message = preg_replace('/SMTP server error: QUIT command failed.*$/s', 'SMTP server error: QUIT command failed.', $message);
    return trim($message);
}

if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== 1 || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /hostel-manage/frontend/user/pages/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['email'])) {
    redirectToPendingFees('error', 'Invalid reminder request.');
}

$to = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
if (!$to) {
    redirectToPendingFees('error', 'Invalid student email address.');
}

try {
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = '220170116004@vgecg.ac.in';
    $mail->Password = 'cruecbkasqqupioq';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->Timeout = 10;
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
        ],
    ];

    $mail->setFrom('220170116004@vgecg.ac.in', 'Hostel Admin');
    $mail->addAddress($to);

    $mail->isHTML(true);
    $mail->Subject = 'Pending Fees Reminder';
    $mail->Body = "Dear Student,<br><br>This is a reminder to kindly pay your pending fees as soon as possible.<br><br>Thank you!";
    $mail->AltBody = "Dear Student,\n\nThis is a reminder to kindly pay your pending fees as soon as possible.\n\nThank you!";

    $mail->send();
    redirectToPendingFees('success', "Reminder email sent successfully to {$to}.");
} catch (Throwable $e) {
    $error = !empty($mail) && !empty($mail->ErrorInfo) ? $mail->ErrorInfo : $e->getMessage();
    $error = cleanMailerError($error);
    redirectToPendingFees('error', 'Failed to send reminder email: ' . $error);
}
?>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require $_SERVER['DOCUMENT_ROOT'] . '/hostel-manage/PHPMailer/Exception.php';
require $_SERVER['DOCUMENT_ROOT'] . '/hostel-manage/PHPMailer/PHPMailer.php';
require $_SERVER['DOCUMENT_ROOT'] . '/hostel-manage/PHPMailer/SMTP.php';

$msg = "";
if (isset($_POST['submit'])) {
    $con = new mysqli('localhost', 'root', '', 'hostel_manage');
    if ($con->connect_error) {
        die("Connection failed: " . $con->connect_error);
    }

    $email = $con->real_escape_string($_POST['email']);
    
    // Server-side email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "<div class='alert alert-dismissible alert-warning'>
            <button type='button' class='close' data-dismiss='alert'>&times;</button>
            Invalid email format.</div>";
    } else {
        $sql = $con->query("SELECT email FROM users WHERE email='$email'");
        if ($sql->num_rows == 0) {
            $msg = "<div class='alert alert-dismissible alert-warning'>
                <button type='button' class='close' data-dismiss='alert'>&times;</button>
                Email doesn't exist.</div>";
        } else {
            $sql1 = $con->query("SELECT firstName FROM users WHERE email='$email'");
            $data = $sql1->fetch_array();
            $name = $data['firstName'];
            
            $passwordToken = hash('Sha512', 'dhumbar78barde.xembarab./a.out_o9oom88/avtya344_tyav');
            $passwordToken = str_shuffle($passwordToken);
            $token = substr($passwordToken, 12, 37); 
            
            $con->query("UPDATE users SET keyToken='$token' WHERE email='$email'");
            
            $mail = new PHPMailer();
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; 
            $mail->SMTPAuth = true;
            $mail->Username = '220170116004@vgecg.ac.in'; 
            $mail->Password = 'cruecbkasqqupioq'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
            $mail->Port = 587;

            $mail->setFrom('220170116004@vgecg.ac.in', 'Hostel Mangement');
            $mail->addAddress($email, $name);
            $mail->Subject = "Reset your password!";
            $mail->isHTML(true);
            $mail->Body = "Hello $name, <br>
            Please click on the link below to reset your password.<br><br>
            <a href='http://localhost/hostel-manage/frontend/user/pages/newpassword.php?email=$email&token=$token'>Reset Password</a>"; 
            
            if ($mail->send()) {
                $msg = "<div class='alert alert-dismissible alert-success'>
                        <button type='button' class='close' data-dismiss='alert'>&times;</button>
                        <strong>Check your email!</strong> We have sent you a reset link.
                        </div>";
            } else {
                $msg = "<div class='alert alert-dismissible alert-danger'>
                        <button type='button' class='close' data-dismiss='alert'>&times;</button>
                        <strong>Something went wrong:</strong> " . $mail->ErrorInfo . "
                        </div>";
            }
        }
    }

    $con->close();
}
?>
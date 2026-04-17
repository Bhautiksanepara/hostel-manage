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
    $conn = mysqli_connect('localhost', 'root', '', 'hostel_manage');
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($conn->real_escape_string($_POST['name']));
    $email = trim($conn->real_escape_string($_POST['email']));
    $password1 = $conn->real_escape_string($_POST['password']);
    $password2 = $conn->real_escape_string($_POST['cPassword']);

    // Validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "<div class='alert alert-warning'>Invalid email format.</div>";
    } elseif (!preg_match("/^[a-zA-Z ]*$/", $name)) {
        $msg = "<div class='alert alert-warning'>Name can only contain letters and spaces.</div>";
    } elseif ($password1 != $password2 || !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{6,}$/', $password1)) {
        $msg = "<div class='alert alert-warning'>Passwords must match and meet the required criteria.</div>";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $msg = "<div class='alert alert-warning '>Email already exists in the database.</div>";
        } else {
            // Generate token and hashed password
            $token = bin2hex(random_bytes(5));
            $hashedPassword = password_hash($password1, PASSWORD_BCRYPT);

            // Generate OTR Number
            $result = $conn->query("SELECT otr_number FROM users ORDER BY id DESC LIMIT 1");
            $lastOTR = $result->fetch_assoc();

            $newOTRNumber = $lastOTR ? (int)substr($lastOTR['otr_number'], 2) + 1 : 1;
            $OTRNumber = '24' . str_pad($newOTRNumber, 4, '0', STR_PAD_LEFT);

            // Insert user data
            $stmt = $conn->prepare("INSERT INTO users (firstName, email, password, otr_number, isEmailConfirmed, token, keyToken) VALUES (?, ?, ?, ?, 1, ?, ?)");
            $stmt->bind_param("ssssss", $name, $email, $hashedPassword, $OTRNumber, $token, $token);

            if ($stmt->execute()) {
                // Send confirmation email
                $mail = new PHPMailer();
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = '220170116004@vgecg.ac.in';
                $mail->Password = 'cruecbkasqqupioq'; // Consider using environment variables
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('220170116004@vgecg.ac.in', 'Hostel Management System');
                $mail->addAddress($email, $name);
                $mail->Subject = "Please verify your email!";
                $mail->isHTML(true);
                $mail->Body = "Hello $name, <br>Please click on the link below to confirm your email:<br><a href='http://localhost/hostel-manage/frontend/user/pages/confirm.php?email=$email&token=$token'>Confirm email</a>";

                if ($mail->send()) {
                    $msg = "<div class='alert alert-success'>Registration complete! Please check your email for confirmation.</div>";
                } else {
                    $msg = "<div class='alert alert-danger'>Email sending failed: {$mail->ErrorInfo}</div>";
                }
            } else {
                $msg = "<div class='alert alert-danger'>Database error: {$stmt->error}</div>";
            }
        }

        $stmt->close();
    }
    $conn->close();
}
?>

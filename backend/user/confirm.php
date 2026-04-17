<?php
function redirect() {
    header('Location: register.php');
    exit();
}

if (!isset($_GET['email']) || !isset($_GET['token'])) {
    redirect();
} else {
    $con = mysqli_connect('localhost', 'root', '', 'hostel_manage');

    if (!$con) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $email = $con->real_escape_string($_GET['email']);
    $token = $con->real_escape_string($_GET['token']);

    $sql = "SELECT id FROM users WHERE email='$email' AND token='$token' AND isEmailConfirmed=0";
    $result = mysqli_query($con, $sql);

    if (mysqli_num_rows($result) > 0) {
        $sql = "UPDATE users SET isEmailConfirmed=1, token='' WHERE email='$email'";
        if (mysqli_query($con, $sql)) {
            $message = "🎉 Email verification successful!";
            $status = "success";
            $subMessage = "Your email has been verified. You can now log in to your account.";
        } else {
            $message = "❌ Error updating record.";
            $status = "error";
            $subMessage = "An issue occurred during the verification process. Please try again.";
        }
    } else {
        $message = "⚠️ Invalid token or email.";
        $status = "error";
        $subMessage = "The link you used may be invalid or already used.";
    }
}

$con->close();
?>
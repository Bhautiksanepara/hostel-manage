<?php
// setPass.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Connect to the database
    $con = new mysqli('localhost', 'root', '', 'hostel_manage');
    if ($con->connect_error) {
        die('Connection Failed: ' . $con->connect_error);
    }

    // Sanitize the input
    $password = $con->real_escape_string($_POST['password']);
    $password2 = $con->real_escape_string($_POST['password2']);
    $email = $con->real_escape_string($_POST['email']);
    $token = $con->real_escape_string($_POST['token']);

    // Check if passwords match
    if ($password !== $password2) {
        header('Location: ../../frontend/user/pages/resetPassword.php?email=' . urlencode($email) . '&token=' . urlencode($token) . '&mp=false');
        exit();
    }

    // Password regex (uppercase, lowercase, special character)
    $passwordPattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\W).{6,}$/';

    // Validate the password
    if (!preg_match($passwordPattern, $password)) {
        header('Location: ../../frontend/user/pages/resetPassword.php?email=' . urlencode($email) . '&token=' . urlencode($token) . '&mp=invalid');
        exit();
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Update password in the database
    $sql = "UPDATE users SET password='$hashedPassword' WHERE email='$email' AND keyToken='$token'";
    if ($con->query($sql) === TRUE) {
        // Password update successful, redirect to login page
        header('Location: ../../frontend/user/pages/login.php?resetStatus=success');
    } else {
        // Error updating password
        echo "Error updating password: " . $con->error;
    }

    // Close the database connection
    $con->close();
}
?>

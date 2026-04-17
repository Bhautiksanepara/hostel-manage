<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $password2 = $_POST['password2'];
    $email = $_POST['email'];
    $token = $_POST['token'];

    // Check if passwords match
    if ($password != $password2) {
        header('Location: ../../frontend/user/pages/newpassword.php?mp=false'); // mismatch error
        exit();
    }
    // Regex pattern to validate the password (uppercase, lowercase, special character)
    $passwordPattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\W).{6,}$/';

    // Validate password
    if (!preg_match($passwordPattern, $password)) {
        // Redirect with an error message
        header('Location: ../../frontend/user/pages/newpassword.php?email=' . urlencode($email) . '&token=' . urlencode($token) . '&mp=invalid');
        exit();
    }

    // Proceed with password update (hash it before saving)
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Update the password in the database (you would perform the SQL update here)
    $sql = "UPDATE users SET password='$hashedPassword' WHERE email='$email' AND keyToken='$token'";

    if ($con->query($sql) === TRUE) {
        echo "Password updated successfully.";
        // Redirect to login or success page
        header('Location: ../../user/pages/login.php');
    } else {
        echo "Error updating password: " . $con->error;
    }
}
?>
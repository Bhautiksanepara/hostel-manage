<?php
include '../../../backend/user/confirm.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Email Confirmation</title>
    <link rel="stylesheet" href="../../global.css">
    <link rel="stylesheet" href="../CSS/modern-auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-left">
            <div class="icon"><?php echo ($status === "success") ? "✅" : "❌"; ?></div>
            <h2><?php echo $message; ?></h2>
            <p><?php echo $subMessage; ?></p>
        </div>
        <div class="auth-right">
            <div class="auth-form">
                <h3>Account Status</h3>
                <p class="subtitle">Please proceed to login or follow the instructions.</p>
                <a href="login.php" class="btn btn-primary">Go to Login</a>
            </div>
        </div>
    </div>
</body>
</html>

<?php
// resetPassword.php

// Retrieve the email and token from the URL parameters
$email = isset($_GET['email']) ? $_GET['email'] : '';
$token = isset($_GET['token']) ? $_GET['token'] : '';

// Optionally handle error messages
$msg = '';
if (isset($_GET['mp']) && $_GET['mp'] === 'false') {
    $msg = 'Passwords do not match.';
} elseif (isset($_GET['mp']) && $_GET['mp'] === 'invalid') {
    $msg = 'Password does not meet the required strength.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Set New Password</title>
    <link rel="stylesheet" href="../../global.css">
    <link rel="stylesheet" href="../CSS/modern-auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-left">
            <div class="icon">🔄</div>
            <h2>Create New Password</h2>
            <p>Set a secure password to get back into your account.</p>
        </div>
        <div class="auth-right">
            <div class="auth-form">
                <?php if ($msg): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($msg); ?></div>
                <?php endif; ?>
                <form action="../../../backend/user/setPass.php" method="post">
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" class="form-control" placeholder="New password" name="password" id="password" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" class="form-control" placeholder="Re-enter new password" name="password2" id="password2" required>
                    </div>
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    <button type="submit" name="submit" class="btn btn-primary">Update Password</button>
                </form>
                <p class="helper-text"><a href="login.php">Back to login</a></p>
            </div>
        </div>
    </div>
</body>
</html>

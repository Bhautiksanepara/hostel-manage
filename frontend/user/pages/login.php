<?php
include '../../../backend/user/login.php';
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login - Pateldham Hostel Management</title>
    <link rel="stylesheet" href="../../global.css">
    <link rel="stylesheet" href="../CSS/modern-auth.css">
    <!-- Dedicated CSS for login page -->
</head>

<body>
    <div class="auth-container">
        <div class="auth-left">
            <div class="icon">🏨</div>
            <h2>Welcome Back</h2>
            <p>Log in to access your hostel dashboard and payment details.</p>
        </div>
        <div class="auth-right">
            <div class="auth-form">
                <h3>Student Login</h3>
                <p class="subtitle">Enter your registered email and password.</p>
                <?php if ($msg != "") echo '<div class="alert alert-danger">' . $msg . '</div>'; ?>
                <form method="post" action="login.php">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" name="email" placeholder="Email" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" class="form-control" name="password" placeholder="Password" required>
                    </div>
                    <button class="btn btn-primary" type="submit" name="submit">Log In</button>
                </form>
                <p class="helper-text">
                    <span>New user? <a href="register.php">Register</a></span>
                    <a href="forgetpass.php">Forgot password?</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>

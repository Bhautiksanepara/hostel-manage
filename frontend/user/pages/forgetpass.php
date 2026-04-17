<?php
include '../../../backend/user/forgetpass.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reset Password</title>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="../../global.css">
    <link rel="stylesheet" href="../CSS/modern-auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-left">
            <div class="icon">🔐</div>
            <h2>Reset Password</h2>
            <p>Enter your registered email to receive a password reset link.</p>
        </div>
        <div class="auth-right">
            <div class="auth-form">
                <?php if ($msg != "") echo '<div class="alert alert-danger">' . $msg . '</div>'; ?>
                <form action="forgetpass.php" method="post">
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="Enter registered email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" title="Please enter a valid email address (e.g. user@example.com)" required>
                    </div>
                    <button type="submit" name="submit" class="btn btn-primary">Send reset link</button>
                </form>
                <p class="helper-text"><a href="login.php">Back to login</a></p>
            </div>
        </div>
    </div>
</body>
</html>

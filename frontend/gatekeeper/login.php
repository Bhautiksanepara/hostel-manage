<?php
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $gatekeeperEmail = 'gatekeeper@example.com';
    $gatekeeperPassword = 'gate123';

    if ($email === $gatekeeperEmail && $password === $gatekeeperPassword) {
        $_SESSION['gatekeeper_logged_in'] = 1;
        $_SESSION['role'] = 'gatekeeper';
        header('Location: gatekeeper.php');
        exit();
    }

    $error = 'Invalid gatekeeper credentials.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gatekeeper Login</title>
    <link rel="stylesheet" href="../global.css">
    <link rel="stylesheet" href="../user/CSS/modern-dashboard.css">
    <style>
        .login-wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
        }
    </style>
</head>
<body>
    <div class="login-wrap">
        <div class="widget-card login-card">
            <h2 class="mb-3">Gatekeeper Login</h2>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <button class="btn btn-primary btn-block" type="submit">Login</button>
            </form>
        </div>
    </div>
</body>
</html>

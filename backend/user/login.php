<?php
session_start();
$msg = "";
if (isset($_GET['resetStatus'])) {
    $resetStatus = $_GET['resetStatus'];
    if ($resetStatus == 'success') {
        $msg = "<div class='alert alert-dismissible alert-success'>
        <button type='button' class='close' data-dismiss='alert'>&times;</button>
        Password reset successful.</div>";
    }
}
if (isset($_SESSION['errMsg'])) {
    $msg .= "<div class='alert alert-dismissible alert-warning'>
    <button type='button' class='close' data-dismiss='alert'>&times;</button>
    {$_SESSION['errMsg']} </div>";
    unset($_SESSION['errMsg']);
}

if (isset($_POST['submit'])) {
    $con = new mysqli('localhost', 'root', '', 'hostel_manage');
    $email = $con->real_escape_string($_POST['email']);
    $password = $con->real_escape_string($_POST['password']);

    // admin credentials
    $adminEmail = 'admin@example.com';
    $adminPassword = 'admin123';

    if (empty($email) || empty($password)) {
        $msg = "<div class='alert alert-dismissible alert-warning'>
        <button type='button' class='close' data-dismiss='alert'>&times;</button>
        Please fill in all fields. </div>";
    } else {
        if ($email === $adminEmail && $password === $adminPassword) {
            $_SESSION['email'] = $email;
            $_SESSION['loggedIn'] = 1;
            $_SESSION['role'] = 'admin'; // Set role as admin
            header('Location: ../../admin/pages/dashboard.php'); // Redirect to the dashboard
            exit();
        }
        $sql = $con->query("SELECT id, password, isEmailConfirmed, otr_number FROM users WHERE email='$email'");

        if ($sql->num_rows > 0) {
            $data = $sql->fetch_assoc();
            if (password_verify($password, $data['password'])) {
                if ($data['isEmailConfirmed'] == 0) {
                    $msg = "<div class='alert alert-dismissible alert-warning'>
                    <button type='button' class='close' data-dismiss='alert'>&times;</button>
                    Please verify your email! </div>";
                } else {
                    $_SESSION['email'] = $email;
                    $_SESSION['loggedIn'] = 1;
                    $_SESSION['otr_number'] = $data['otr_number'];
                    

                    
                    header('Location: ../../user/pages/dashboard.php'); // Redirect to the dashboard
                    exit();
                }
            } else {
                $msg = "<div class='alert alert-dismissible alert-danger'>
                <button type='button' class='close' data-dismiss='alert'>&times;</button>
                Invalid credentials. </div>";
            }
        } else {
            $msg = "<div class='alert alert-dismissible alert-danger'>
            <button type='button' class='close' data-dismiss='alert'>&times;</button>
            Invalid credentials. </div>";
        }
    }
}
?>

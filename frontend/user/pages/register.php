<?php
include '../../../backend/user/register.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Register</title>
    <link rel="stylesheet" href="../../global.css">
    <link rel="stylesheet" href="../CSS/modern-auth.css">

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">

    <style>
  
    </style>
    <script>
        function validatePassword() {
            let password = document.getElementById("password").value;
            let criteria = document.getElementById("password-criteria");
            let upperCase = /[A-Z]/.test(password);
            let lowerCase = /[a-z]/.test(password);
            let number = /[0-9]/.test(password);
            let specialChar = /[\W_]/.test(password);
            let minLength = password.length >= 6;

            document.getElementById("criteria-uppercase").className = upperCase ? "valid" : "invalid";
            document.getElementById("criteria-lowercase").className = lowerCase ? "valid" : "invalid";
            document.getElementById("criteria-number").className = number ? "valid" : "invalid";
            document.getElementById("criteria-specialchar").className = specialChar ? "valid" : "invalid";
            document.getElementById("criteria-length").className = minLength ? "valid" : "invalid";
        }
    </script>
</head>
<body>
    <div class="auth-container">
        <div class="auth-left">
            <div class="icon">🧑‍🎓</div>
            <h2>Create your account</h2>
            <p>Register for hostel access, fees payment, and request tracking.</p>
        </div>
        <div class="auth-right">
            <div class="auth-form">
                <h3>Student Registration</h3>
                <p class="subtitle">Fill out your details to join.</p>
                <?php if ($msg != "") echo '<div class="alert alert-danger">' . $msg . '</div>'; ?>
                <form method="post" action="register.php">
                    <div class="form-group">
                        <label>Name</label>
                        <input class="form-control" name="name" placeholder="Name" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input class="form-control" name="email" placeholder="Email" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input class="form-control" id="password" name="password" type="password" placeholder="Password" required oninput="validatePassword()">
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input class="form-control" name="cPassword" type="password" placeholder="Confirm Password" required>
                    </div>
                    <button class="btn btn-primary" type="submit" name="submit">Register</button>
                </form>
                <p class="helper-text">Already a user? <a href="login.php">Log in</a></p>
            </div>
        </div>
    </div>
</body>
</html>

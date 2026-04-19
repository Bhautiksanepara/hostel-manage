<?php
session_start();
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== 1 || empty($_SESSION['otr_number'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SDHostel - Gate Pass & Leave Request</title>
    <link rel="stylesheet" href="../../global.css">
    <link rel="stylesheet" href="../CSS/modern-dashboard.css">
    <style>
     
    </style>
 <script src="../javascript/script.js"></script>
</head>

<body>
<?php include 'sidebar.php';?>
    <div class="content">
    <?php include 'topbar.php';?>

        <div class="main-content">
            <div class="form-container">
                <h2>Gate Pass & Leave Request</h2>
                <form action="../../../backend/user/gate-pass.php" method="POST">
                    <!-- <div class="form-group">
                        <label for="otr_number">OTR Number:</label>
                        <input type="text" id="otr_number" name="otr_number">
                    </div>
                    <div class="form-group">
                        <label for="otr_number">Name:</label>
                        <input type="text" id="Name" name="Name">   
                    </div> -->
                    <div class="form-group">
                        <label for="type">Type:</label>
                        <select id="type" name="type" required>
                            <option value="">Select Type</option>
                            <option value="Gate">Gate Pass</option>
                            <option value="Leave">Leave</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="reason">Reason:</label>
                        <select id="reason" name="reason" required>
                            <option value="">Select Reason</option>
                            <option value="Medical">Medical</option>
                            <option value="Relative Meeting">Relative Meeting</option>
                            <option value="College">College</option>
                            <option value="Home">Home</option>
                            <option value="Salon">Salon</option>
                            <option value="Shopping">Shopping</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="out_time">Approximate Out Time:</label>
                        <input type="time" id="out_time" name="out_time" required>
                    </div>

                    <div class="form-group">
                        <label for="in_time">Approximate In Time:</label>
                        <input type="time" id="in_time" name="in_time" required>
                    </div>

                    <div class="form-group">
                        <label for="date_from">Date From:</label>
                        <input type="date" id="date_from" name="date_from" required>
                    </div>

                    <div class="form-group">
                        <label for="date_to">Date To:</label>
                        <input type="date" id="date_to" name="date_to" required>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>

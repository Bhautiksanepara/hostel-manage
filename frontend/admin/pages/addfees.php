<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hostel_manage";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $academic_year = $_POST['academic_year'];
    $amount = $_POST['amount'];

    // First, update fees for students with pending status
    $update_sql = "UPDATE fees 
                   SET amount = amount + ?, academic_year = ? 
                   WHERE status = 'pending'";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ds", $amount, $academic_year);
    $update_stmt->execute();
    $updated_rows = $update_stmt->affected_rows;
    $update_stmt->close();

    // Insert fees for students who don't have a pending status
    $insert_sql = "INSERT INTO fees (otr_number, academic_year, amount, status)
                   SELECT u.otr_number, ?, ?, 'pending' 
                   FROM users u 
                   WHERE NOT EXISTS (SELECT 1 FROM fees f WHERE f.otr_number = u.otr_number AND f.status = 'pending')";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("sd", $academic_year, $amount);
    $insert_stmt->execute();
    $insert_stmt->close();

    if ($updated_rows > 0) {
        echo "<script>alert('Fees updated for students with pending status!');</script>";
    } else {
        echo "<script>alert('Fees added for students without pending fees!');</script>";
    }
}
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Add Fees</title>
    <link rel="stylesheet" href="../../../global.css">
    <link rel="stylesheet" href="../CSS/modern-admin.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <script src="../javascript/script.js"></script>
</head>
<body>
    <?php include 'admin_sidebar.php'; ?>
    <?php include 'admin_topbar.php'; ?>

    <div class="admin-content">
        <div class="container mt-4">
            <h2 class="mb-4">Add Hostel Fees</h2>
            <form method="POST" class="p-4 border rounded bg-light">
                <div class="form-group">
                    <label for="academic_year">Academic Year:</label>
                    <input type="text" name="academic_year" class="form-control" placeholder="e.g., 2024-25" required>
                </div>
                
                <div class="form-group">
                    <label for="amount">Amount:</label>
                    <input type="number" name="amount" step="0.01" class="form-control" placeholder="Enter fee amount" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Add Fees</button>
            </form>
        </div>
    </div>
</body>
</html>

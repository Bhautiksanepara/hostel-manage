<?php
// Start the session to check if the user is logged in
session_start();
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== 1) {
    header('Location: login.php');
    exit();
}

// Database connection
$con = new mysqli('localhost', 'root', '', 'hostel_manage');

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Fetch gate pass and leave requests for the logged-in user
$otr_number = $_SESSION['otr_number'];
$query = "SELECT * FROM gatepass WHERE otr_number = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("s", $otr_number); // Assuming otr_number is a string
$stmt->execute();
$result = $stmt->get_result();

$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row; // Collect data
    }
} else {
    // No records found
    $data = [];
}

$stmt->close(); // Close the prepared statement
$con->close(); // Close the connection
?>
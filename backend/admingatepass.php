<?php
include 'dbconnection.php';
// Start the session
session_start();
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== 1 || $_SESSION['role'] !== 'admin') {
    header('Location: ../../../frontend/user/pages/login.php');
    exit();
}

// Database connection
$con = new mysqli('localhost', 'root', '', 'hostel_manage');

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Fetch all gate pass requests
$query = "SELECT * FROM gatepass where status = 'pending'";
$result = $con->query($query);

$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row; // Collect data
    }
}

// Approve or Reject Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $update_query = "UPDATE gatepass SET status = 'Approved' WHERE id = ?";
    } elseif ($action === 'reject') {
        $update_query = "UPDATE gatepass SET status = 'Rejected' WHERE id = ?";
    }

    $update_stmt = $con->prepare($update_query);
    $update_stmt->bind_param("i", $request_id);
    $update_stmt->execute();
    $update_stmt->close();

    // Redirect to the same page to refresh the data
    // header("Location: manage-requests.php");
    // exit();
}

$con->close();
?>
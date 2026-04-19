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

// Approve or reject before loading pending rows, so the table refreshes correctly.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = (int)($_POST['request_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $update_query = '';

    if ($action === 'approve') {
        $update_query = "UPDATE gatepass SET status = 'Approved' WHERE id = ?";
    } elseif ($action === 'reject') {
        $update_query = "UPDATE gatepass SET status = 'Rejected' WHERE id = ?";
    }

    if ($request_id > 0 && $update_query !== '') {
        $update_stmt = $con->prepare($update_query);
        $update_stmt->bind_param("i", $request_id);
        $update_stmt->execute();
        $update_stmt->close();
    }
}

// Fetch all pending gate pass requests
$query = "SELECT * FROM gatepass WHERE status = 'pending' ORDER BY id DESC";
$result = $con->query($query);

$data = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row; // Collect data
    }
}

$con->close();
?>

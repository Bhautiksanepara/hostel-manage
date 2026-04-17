<?php
session_start(); // Start the session

if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== 1 || $_SESSION['role'] !== 'admin') {
    header('Location: ../../../frontend/user/pages/login.php');
    exit();
}
// Database connection
include 'dbconnection.php';

// Fetch maintenance issues from the database
$query = "SELECT id, otr_number, issue_type, issue, image_path, status, submitted_at, solved_at FROM maintenance_issues WHERE status = 'Pending' OR status = 'In Progress'";
$result = $conn->query($query);
?>

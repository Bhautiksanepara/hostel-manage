<?php
include 'dbconnection.php';
session_start();
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== 1 || $_SESSION['role'] !== 'admin') {
    header('Location: ../../../frontend/user/pages/login.php');
    exit();
}
// Fetch available students without a room
$query = "SELECT otr_number, firstName, email, otr_number, fees_status FROM users WHERE fees_status='unpaid'";
$result = $conn->query($query);
?>

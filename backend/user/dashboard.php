<?php
session_start(); // Ensure session is started at the very beginning

$conn = new mysqli('localhost', 'root', '', 'hostel_manage');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if 'otr_number' exists in session
if (!isset($_SESSION['otr_number'])) {
    die("Error: Session 'otr_number' is not set. Please log in again.");
}

$otr = $_SESSION['otr_number']; // Assign the session value to a variable

// Query to fetch user name, fees status, room number, and room status
$query = "SELECT u.firstName, u.fees_status, r.room_number, r.status AS room_status 
          FROM users u
          LEFT JOIN rooms r ON u.room_id = r.room_id
          WHERE u.otr_number = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $otr);
$stmt->execute();
$result = $stmt->get_result();

$userData = $result->fetch_assoc();

// Handle case where no user data is found
if (!$userData) {
    $userData = [
        'firstName' => 'Guest',
        'fees_status' => 'Unknown',
        'room_number' => 'Not Allocated',
        'room_status' => 'N/A'
    ];
}

$stmt->close();
$conn->close();
?>

<?php

session_start();
$host = 'localhost'; // Database host
$dbname = 'hostel_manage'; // Database name
$username = 'root'; // Database username
$password = ''; // Database password

// Create connection
$conn = mysqli_connect($host, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['otr_number'])) {
    die("OTR number is not set. Please log in again.");
}

if (isset($_GET['success']) && $_GET['success'] == 1) {
    echo '<script>alert("Receipt uploaded successfully!");</script>';
}

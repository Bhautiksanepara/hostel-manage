<?php
session_start();
include '../dbconnection.php';
// Database connection

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get OTR number from session
    $otr = $_SESSION['otr_number'];

    // Retrieve user details
    $query = "SELECT otr_number, firstName FROM users WHERE otr_number = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $otr); // Assuming otr_number is a string
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();

        $otr_number = $user_data['otr_number'];
        $name = $user_data['firstName'];
        $request_id = uniqid('GP'); // Generates a unique ID like GP64e7a5b0e1f56

        // Get form data
        $type = $_POST['type'];
        $reason = $_POST['reason'];
        $out_time = $_POST['out_time'];
        $in_time = $_POST['in_time'];
        $date_from = $_POST['date_from'];
        $date_to = $_POST['date_to'];

        // Insert into gatepass table
        $sql = "INSERT INTO gatepass (request_id, otr_number, name, type, reason, status, date_from, out_time, date_to, in_time)
                VALUES (?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssss", $request_id, $otr_number, $name, $type, $reason, $date_from, $out_time, $date_to, $in_time);

        if ($stmt->execute()) {
            echo "<script>alert('Gate pass request submitted successfully!');
             window.location.href = '../../frontend/user/pages/gate-pass.php';</script>";
        } else {
            echo "<script>alert('Error submitting gate pass request: " . $conn->error . "');</script>";
        }
    } else {
        echo "<script>alert('Invalid OTR number or user not found.');</script>";
    }
}

$conn->close();
?>

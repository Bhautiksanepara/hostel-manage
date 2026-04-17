<?php
include '../../../backend/dbconnection.php';

if (isset($_POST['room_id'])) {
    $room_id = $_POST['room_id'];
    $query = "SELECT firstName, otr_number FROM users WHERE room_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<li>{$row['firstName']} (OTR: {$row['otr_number']})</li>";
        }
    } else {
        echo "<li>No students allocated</li>";
    }
}
?>

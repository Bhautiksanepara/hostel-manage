<?php
include '../../../backend/dbconnection.php';

$query = "SELECT room_id, room_number FROM rooms WHERE room_id NOT IN 
          (SELECT room_id FROM users GROUP BY room_id HAVING COUNT(*) >= 4)";
$result = $conn->query($query);

$rooms = [];
while ($row = $result->fetch_assoc()) {
    $rooms[] = $row;
}

echo json_encode($rooms);
$conn->close();
?>

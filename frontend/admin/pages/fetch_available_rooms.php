<?php
include '../../../backend/dbconnection.php';

header('Content-Type: application/json');

$current_room_id = isset($_GET['current_room_id']) ? (int) $_GET['current_room_id'] : 0;

$query = "SELECT r.room_id, r.room_number, COUNT(u.id) AS occupants
          FROM rooms r
          LEFT JOIN users u ON r.room_id = u.room_id
          WHERE r.room_id <> ?
          GROUP BY r.room_id, r.room_number
          HAVING occupants < 4
          ORDER BY r.room_number";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $current_room_id);
$stmt->execute();
$result = $stmt->get_result();

$rooms = [];
while ($row = $result->fetch_assoc()) {
    $rooms[] = $row;
}

echo json_encode($rooms);
$stmt->close();
$conn->close();
?>

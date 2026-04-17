<?php
include 'dbconnection.php';
session_start();
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== 1 || $_SESSION['role'] !== 'admin') {
    header('Location: ../../../frontend/user/pages/login.php');
    exit();
}


// Handle Room Allocation via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['allocate'])) {
    $user_id = $_POST['user_id'];
    $room_id = $_POST['room_id'];

    // Check if room exists and has space
    $checkRoom = "SELECT capacity, current_occupants FROM rooms WHERE room_id = ?";
    $stmt = $conn->prepare($checkRoom);
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $room = $stmt->get_result()->fetch_assoc();

    if ($room && $room['current_occupants'] < $room['capacity']) {
        // Allocate room
        $allocateRoom = "INSERT INTO room_allocations (room_id, user_id) VALUES (?, ?)";
        $stmt = $conn->prepare($allocateRoom);
        $stmt->bind_param("ii", $room_id, $user_id);
        $stmt->execute();

        // Update room current_occupants
        $updateOccupants = "UPDATE rooms SET current_occupants = current_occupants + 1 WHERE room_id = ?";
        $stmt = $conn->prepare($updateOccupants);
        $stmt->bind_param("i", $room_id);
        $stmt->execute();

        echo json_encode(['status' => 'success', 'message' => 'Room allocated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Room is full or invalid']);
    }
    $stmt->close();
    exit();
}
?>
<?php
include '../../../backend/dbconnection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    header('Content-Type: application/json');

    if (!isset($_POST['student_id']) || !isset($_POST['new_room_id'])) {
        echo json_encode(["status" => "error", "message" => "Missing parameters."]);
        exit;
    }

    $student_id = $_POST['student_id'];
    $new_room_id = $_POST['new_room_id'];

    $studentQuery = "SELECT room_id FROM users WHERE id = ?";
    $stmt = $conn->prepare($studentQuery);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$student) {
        echo json_encode(["status" => "error", "message" => "Student not found."]);
        exit;
    }

    if ((int) $student['room_id'] === (int) $new_room_id) {
        echo json_encode(["status" => "error", "message" => "Student is already in this room."]);
        exit;
    }

    $roomCheck = "SELECT COUNT(*) AS occupants FROM users WHERE room_id = ?";
    $stmt = $conn->prepare($roomCheck);
    $stmt->bind_param("i", $new_room_id);
    $stmt->execute();
    $room = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$room || (int) $room['occupants'] >= 4) {
        echo json_encode(["status" => "error", "message" => "Selected room is already full."]);
        exit;
    }

    $updateQuery = "UPDATE users SET room_id = ? WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ii", $new_room_id, $student_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Room changed successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error updating room."]);
    }

    $stmt->close();
    $conn->close();
}
?>

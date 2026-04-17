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

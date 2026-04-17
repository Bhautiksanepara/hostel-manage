<?php
include 'dbconnection.php';
session_start();
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== 1 || $_SESSION['role'] !== 'admin') {
    header('Location: ../../../frontend/user/pages/login.php');
    exit();
}
// Fetch available students without a room
$studentsQuery = "SELECT id, firstName, otr_number FROM users WHERE room_id IS NULL";
$students = $conn->query($studentsQuery);

// Fetch rooms with available space
$roomsQuery = "
    SELECT r.room_id, r.room_number, COUNT(u.id) AS occupants
    FROM rooms r
    LEFT JOIN users u ON r.room_id = u.room_id
    GROUP BY r.room_id
    HAVING occupants < 4
";
$rooms = $conn->query($roomsQuery);


// Handle Room Allocation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['allocate'])) {
    $student_id = $_POST['student_id'];
    $room_id = $_POST['room_id'];

    // Check if the selected room has space
    $roomCheck = "
        SELECT COUNT(*) AS count 
        FROM users 
        WHERE room_id = ? 
    ";
    $stmt = $conn->prepare($roomCheck);
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result['count'] < 4) {
        // Allocate room to the student
        $allocateRoom = "UPDATE users SET room_id = ? WHERE id = ?";
        $stmt = $conn->prepare($allocateRoom);
        $stmt->bind_param("ii", $room_id, $student_id);
        $stmt->execute();

        echo "<script>alert('Room allocated successfully!');</script>";
        
    } else {
        echo "<script>alert('Room is already full!');</script>";
    }
}
?>

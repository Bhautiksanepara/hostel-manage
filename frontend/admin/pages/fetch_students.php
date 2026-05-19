<?php
include '../../../backend/dbconnection.php';

if (isset($_POST['room_id'])) {
    $room_id = $_POST['room_id'];
    $query = "SELECT id, firstName, otr_number FROM users WHERE room_id = ? ORDER BY firstName";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $studentId = htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8');
            $firstName = htmlspecialchars($row['firstName'], ENT_QUOTES, 'UTF-8');
            $otrNumber = htmlspecialchars($row['otr_number'], ENT_QUOTES, 'UTF-8');

            echo "<li data-student-id=\"{$studentId}\">{$firstName} (OTR: {$otrNumber})</li>";
        }
    } else {
        echo "<li data-empty=\"1\">No students allocated</li>";
    }
}
?>

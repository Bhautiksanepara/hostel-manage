<?php
include 'dbconnection.php'; // Ensure this file has a valid database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['id']) && isset($_POST['status'])) {
        $id = intval($_POST['id']);
        $status = $_POST['status'];

        // If status is "Resolved", update `solved_at` with the current timestamp
        if ($status == "Resolved") {
            $sql = "UPDATE maintenance_issues SET status = ?, solved_at = NOW() WHERE id = ?";
        } else {
            $sql = "UPDATE maintenance_issues SET status = ?, solved_at = NULL WHERE id = ?";
        }

        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("si", $status, $id);
            if ($stmt->execute()) {
                // Redirect back to the page after updating
                header("Location: {$_SERVER['HTTP_REFERER']}");
                exit();
            } else {
                echo "<script>alert('Error updating status.'); window.history.back();</script>";
            }
            $stmt->close();
        } else {
            echo "<script>alert('Database error.'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Invalid request.'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('Unauthorized access!'); window.history.back();</script>";
}

$conn->close();
?>

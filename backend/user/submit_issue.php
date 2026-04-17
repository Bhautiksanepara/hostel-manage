<?php
session_start(); // Start the session
include '../dbconnection.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['issue'])) {
    $issue = $_POST['issue'];
    $issue_type = $_POST['issue_type']; // Get selected issue type
    $otr_number = $_SESSION['otr_number']; // Assuming OTR number is stored in session
    $image_path = NULL; // Default no image

    // Handle image upload if provided
    if (!empty($_FILES['issue_image']['name'])) {
        $target_dir = "../../uploads/issues/";
        $image_name = basename($_FILES["issue_image"]["name"]);
        $target_file = $target_dir . time() . "_" . $image_name; // Unique filename

        // Move uploaded file to server
        if (move_uploaded_file($_FILES["issue_image"]["tmp_name"], $target_file)) {
            $image_path = "uploads/issues/" . time() . "_" . $image_name; // Store relative path
        }
    }

    // Prepare and bind SQL
    $stmt = $conn->prepare("INSERT INTO maintenance_issues (otr_number, issue, issue_type, image_path) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $otr_number, $issue, $issue_type, $image_path);

    // Execute the query
    if ($stmt->execute()) {
        echo "<script>
                alert('Maintenance issue submitted successfully.');
                window.location.href = '../../frontend/user/pages/maintenance-issue.php'; 
              </script>";
    } else {
        echo "<script>
                alert('Error: " . addslashes($stmt->error) . "');
              </script>";
    }

    $stmt->close();
}

$conn->close();
?>

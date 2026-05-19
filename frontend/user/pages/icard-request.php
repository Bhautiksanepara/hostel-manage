<?php
session_start();
include '../../../backend/dbconnection.php';

if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== 1 || empty($_SESSION['otr_number'])) {
    header('Location: login.php');
    exit();
}

$message = '';
$otr_number = $_SESSION['otr_number'];
$userQuery = "SELECT firstName FROM users WHERE otr_number = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("s", $otr_number);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
$studentName = $user['firstName'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $reason = trim($_POST['reason'] ?? '');
    $photoPath = null;

    if (!empty($_FILES['photo']['name'])) {
        $uploadDir = '../../../uploads/icards/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($extension, $allowedExtensions, true)) {
            $fileName = $otr_number . '_' . time() . '.' . $extension;
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
                $photoPath = 'uploads/icards/' . $fileName;
            }
        }
    }

    if ($name === '' || $department === '' || $reason === '') {
        $message = "<div class='alert alert-danger'>Please fill all required fields.</div>";
    } else {
        $insertQuery = "INSERT INTO icard_requests (otr_number, name, department, reason, photo) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("sssss", $otr_number, $name, $department, $reason, $photoPath);

        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>I-card request submitted successfully.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Unable to submit request.</div>";
        }

        $stmt->close();
    }
}

$historyQuery = "SELECT department, reason, photo, status, digital_icard, request_date
                 FROM icard_requests
                 WHERE otr_number = ?
                 ORDER BY request_date DESC";
$stmt = $conn->prepare($historyQuery);
$stmt->bind_param("s", $otr_number);
$stmt->execute();
$requests = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I-card Request</title>
    <link rel="stylesheet" href="../../global.css">
    <link rel="stylesheet" href="../CSS/modern-dashboard.css">
    <script src="../javascript/script.js"></script>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="content">
    <?php include 'topbar.php'; ?>
    <div class="main-content">
        <div class="page-header">
            <div>
                <h1>I-card Request</h1>
                <p>Submit and track your hostel I-card request.</p>
            </div>
        </div>

        <?php echo $message; ?>

        <div class="widget-card">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($studentName); ?>" required>
                </div>
                <div class="form-group">
                    <label>Department</label>
                    <input type="text" name="department" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Reason</label>
                    <textarea name="reason" rows="4" class="form-control" required></textarea>
                </div>
                <div class="form-group">
                    <label>Photo</label>
                    <input type="file" name="photo" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                </div>
                <button type="submit" class="btn btn-primary">Submit Request</button>
            </form>
        </div>

        <div class="section">
            <h3>Request History</h3>
            <table>
                <thead>
                    <tr>
                        <th>Department</th>
                        <th>Reason</th>
                        <th>Photo</th>
                        <th>Digital I-card</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $requests->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['department']); ?></td>
                            <td><?php echo htmlspecialchars($row['reason']); ?></td>
                            <td>
                                <?php if (!empty($row['photo'])): ?>
                                    <a href="../../../<?php echo htmlspecialchars($row['photo']); ?>" target="_blank">View</a>
                                <?php else: ?>
                                    No Photo
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($row['digital_icard'])): ?>
                                    <a href="../../../<?php echo htmlspecialchars($row['digital_icard']); ?>" target="_blank">Download</a>
                                <?php else: ?>
                                    Not Generated
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                            <td><?php echo htmlspecialchars($row['request_date']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>

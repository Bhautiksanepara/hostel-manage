<?php
session_start();
include '../../../backend/dbconnection.php';

if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== 1 || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../../user/pages/login.php');
    exit();
}

$result = $conn->query("SELECT id, name, email, phone, message, created_at
                        FROM contact_messages
                        ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages</title>
    <link rel="stylesheet" href="../../global.css">
    <link rel="stylesheet" href="../CSS/modern-admin.css">
    <script src="../javascript/script.js"></script>
</head>
<body>
<?php include 'admin_sidebar.php'; ?>
<?php include 'admin_topbar.php'; ?>
<div class="admin-content">
    <div class="admin-main">
        <h2>Contact Messages</h2>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Message</th>
                    <th>Received At</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($row['message'])); ?></td>
                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
<?php $conn->close(); ?>

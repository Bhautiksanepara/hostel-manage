<?php
session_start();
include '../../../backend/dbconnection.php'; // Ensure this file has a valid database connection

// Check if student is logged in
if (!isset($_SESSION['otr_number'])) {
    echo "<script>alert('Unauthorized access! Please login.'); window.location.href='login.php';</script>";
    exit();
}

$otr_number = $_SESSION['otr_number']; // Get logged-in student's ID

// Fetch maintenance history of the logged-in student
$sql = "SELECT otr_number, issue_type, issue, status, submitted_at, solved_at FROM maintenance_issues WHERE otr_number = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $otr_number);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance History</title>
    <link rel="stylesheet" href="../../global.css">
    <link rel="stylesheet" href="../CSS/modern-dashboard.css">
    
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.css">
    <script>
        $(document).ready(function(){
            $('#historyTable').DataTable({
                paging: true,
                searching: true,
                ordering: true,
                info: true
            });
        });
    </script>
    <script src="../javascript/script.js"></script>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="content">
        <?php include 'topbar.php'; ?>
        <h2>Maintenance Request History</h2>

        <table id="historyTable">
            <thead>
                <tr>
                    <th>OTR Number</th>
                    <th>Issue Type</th>
                    <th>Issue</th>
                    <th>Status</th>
                    <th>Submitted At</th>
                    <th>Solved At</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['otr_number']); ?></td>
                            <td><?php echo htmlspecialchars($row['issue_type']); ?></td>
                            <td><?php echo htmlspecialchars($row['issue']); ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                            <td><?php echo htmlspecialchars($row['submitted_at']); ?></td>
                            <td><?php echo htmlspecialchars($row['solved_at'] ?? 'Pending'); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No maintenance issues submitted.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
$conn->close();
?>

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
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.css">
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
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
        <div class="main-content">
            <div class="page-header">
                <div>
                    <h1>Maintenance Request History</h1>
                    <p>Track issue status, submission time, and resolution progress.</p>
                </div>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <div>
                        <h3>Submitted Issues</h3>
                    </div>
                </div>
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
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>

<?php
include '../../../backend/adminmaintainance.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Maintenance Issues</title>
   
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.css" />
    <link rel="stylesheet" href="../../global.css">
    <link rel="stylesheet" href="../CSS/modern-admin.css">
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>

    <script>
        $(document).ready(function(){
            $('#example').DataTable({
                columnDefs: [
                    { orderable: false, targets: [3, 7] }
                ],
                paging: true,
                searching: true,
                ordering: true,
                info: true
            });
        });
    </script>
    <script src ="../javascript/script.js"></script>
</head>
<body>
<?php include 'admin_sidebar.php'; ?>
<?php include 'admin_topbar.php'; ?>
    <div class="admin-content">
        <h2>Maintenance Issues</h2>
        <table id="example">
            <thead>
                <tr>
                    <th>OTR Number</th>
                    <th>Issue Type</th>
                    <th>Issue</th>
                    <th>Image</th>
                    <th>Status</th>
                    <th>Submitted At</th>
                    <th>Solved At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['otr_number']); ?></td>
                            <td><?php echo htmlspecialchars($row['issue_type']); ?></td>
                            <td><?php echo htmlspecialchars($row['issue']); ?></td>
                            <td>
                                <?php if (!empty($row['image_path'])): ?>
                                    <a href="../../../<?php echo htmlspecialchars($row['image_path']); ?>" target="_blank">
                                        <img src="../../../<?php echo htmlspecialchars($row['image_path']); ?>" alt="Issue Image" width="50" height="50">
                                    </a>
                                <?php else: ?>
                                    No Image
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                            <td><?php echo htmlspecialchars($row['submitted_at']); ?></td>
                            <td><?php echo htmlspecialchars($row['solved_at'] ?? 'Pending'); ?></td>
                            <td>
    <form action="../../../backend/admin_update_status.php" method="post" class="action-form">
        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
        <select name="status" required>
            <option value="Pending" <?php echo ($row['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
            <option value="In Progress" <?php echo ($row['status'] == 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
            <option value="Resolved">Resolved</option>
        </select>
        <button type="submit" class="btn btn-primary btn-sm">Update</button>
    </form>
</td>

                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
$conn->close();
?>

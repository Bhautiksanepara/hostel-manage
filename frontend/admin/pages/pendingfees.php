<?php
include '../../../backend/adminpendingfees.php';
$reminderFlash = $_SESSION['reminder_flash'] ?? null;
unset($_SESSION['reminder_flash']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Allocation</title>
    <link rel="stylesheet" href="../../../global.css">
    <link rel="stylesheet" href="../CSS/modern-admin.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="../javascript/script.js"></script>
    <style>
        .admin-alert {
            display: flex;
            gap: 8px;
            align-items: center;
            padding: 14px 16px;
            margin-bottom: 18px;
            border-radius: 8px;
            border: 1px solid transparent;
            font-size: 15px;
        }

        .admin-alert-success {
            color: #14532d;
            background: #dcfce7;
            border-color: #86efac;
        }

        .admin-alert-danger {
            color: #7f1d1d;
            background: #fee2e2;
            border-color: #fca5a5;
        }
    </style>
</head>

<body>
<?php include 'admin_sidebar.php'; ?>
<?php include 'admin_topbar.php'; ?>

    <div class="admin-content">
        <div class="admin-main">

<?php if ($reminderFlash): ?>
    <script>
        alert(<?php echo json_encode($reminderFlash['message']); ?>);
    </script>
    <div class="admin-alert <?php echo $reminderFlash['type'] === 'success' ? 'admin-alert-success' : 'admin-alert-danger'; ?>">
        <strong><?php echo $reminderFlash['type'] === 'success' ? 'Success:' : 'Error:'; ?></strong>
        <span><?php echo htmlspecialchars($reminderFlash['message']); ?></span>
    </div>
<?php endif; ?>

        <h1>Pending fees Students</h1>

<!-- Student Dropdown -->

<hr>

<!-- Allocation List -->

<table id="roomAllocationTable">
<thead>
        <tr>
            <th>OTR Number</th>
            <th>Name</th>
            <th>Email</th>
           
            <th>Fees Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['otr_number']; ?></td>
                <td><?php echo $row['firstName']; ?></td>
                <td><?php echo $row['email']; ?></td>
                <td><?php echo $row['fees_status']; ?></td>
                <td>
    <form action="../../../backend/adminsend_reminder.php" method="POST">
        <input type="hidden" name="email" value="<?php echo htmlspecialchars($row['email']); ?>">
        <button type="submit" class="btn message-btn">Send Reminder</button>
    </form>
</td>

            </tr>
        <?php } ?>
    </tbody>
</table>

<script>
    $(document).ready(function() {
        $('#roomAllocationTable').DataTable({
            paging: true,
            searching: true,
            ordering: true,
            info: true
        });
    });
</script>

        </div>
    </div>
</body>

</html>

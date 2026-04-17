<?php
include '../../../backend/adminroom.php';
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
</head>

<body>
<?php include 'admin_sidebar.php'; ?>
<?php include 'admin_topbar.php'; ?>

    <div class="admin-content">
        <div class="admin-main">
           
        <h1>Allocate Room to Students</h1>

<!-- Student Dropdown -->
<form method="POST" id="allocationForm">
    <label for="student">Select Student:</label>
    <select name="student_id" required>
        <option value="">Select Student</option>
        <?php while ($student = $students->fetch_assoc()): ?>
            <option value="<?= $student['id']; ?>"><?= $student['firstName']; ?> (<?= $student['otr_number']; ?>)</option>
        <?php endwhile; ?>
    </select>

    <label for="room">Select Room:</label>
    <select name="room_id" required>
        <option value="">Select Room</option>
        <?php while ($room = $rooms->fetch_assoc()): ?>
            <option value="<?= $room['room_id']; ?>">Room <?= $room['room_number']; ?> (<?= $room['occupants']; ?>/4)</option>
        <?php endwhile; ?>
    </select>

    <button type="submit" name="allocate" class="btn btn-primary">Allocate Room</button>
</form>

<hr>

<!-- Allocation List -->
<h2>Room Allocation List</h2>
<table id="roomAllocationTable">
    <thead>
        <tr>
            <th>Room Number</th>
            <th>Student Name</th>
            <th>OTR Number</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $allocationsQuery = "
            SELECT r.room_number, u.firstName, u.otr_number
            FROM users u
            JOIN rooms r ON u.room_id = r.room_id
            WHERE u.room_id IS NOT NULL
        ";
        $allocations = $conn->query($allocationsQuery);
        while ($allocation = $allocations->fetch_assoc()) {
            echo "<tr>
                    <td>{$allocation['room_number']}</td>
                    <td>{$allocation['firstName']}</td>
                    <td>{$allocation['otr_number']}</td>
                </tr>";
        }
        ?>
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

</body>
</html>


<script src="../javascript/script.js"></script>

        </div>
    </div>
</body>

</html>

<?php
include '../../../backend/dbconnection.php';

$studentQuery = "SELECT COUNT(*) AS total_students FROM users";
$studentResult = $conn->query($studentQuery);
$total_students = ($studentResult->num_rows > 0) ? $studentResult->fetch_assoc()['total_students'] : 0;

$roomQuery = "SELECT COUNT(*) AS total_rooms FROM rooms";
$roomResult = $conn->query($roomQuery);
$total_rooms = ($roomResult->num_rows > 0) ? $roomResult->fetch_assoc()['total_rooms'] : 0;

$availableRoomQuery = "SELECT COUNT(*) AS available_rooms FROM rooms WHERE status = 'available'";
$availableRoomResult = $conn->query($availableRoomQuery);
$available_rooms = ($availableRoomResult->num_rows > 0) ? $availableRoomResult->fetch_assoc()['available_rooms'] : 0;

$occupiedRoomQuery = "SELECT COUNT(*) AS occupied_rooms FROM rooms WHERE status = 'occupied'";
$occupiedRoomResult = $conn->query($occupiedRoomQuery);
$occupied_rooms = ($occupiedRoomResult->num_rows > 0) ? $occupiedRoomResult->fetch_assoc()['occupied_rooms'] : 0;

$pendingFeesQuery = "SELECT SUM(amount) AS total_pending FROM fees WHERE status = 'pending'";
$pendingFeesResult = $conn->query($pendingFeesQuery);
$total_pending_fees = ($pendingFeesResult->num_rows > 0) ? $pendingFeesResult->fetch_assoc()['total_pending'] : 0;

$maintenanceQuery = "SELECT COUNT(*) AS total_issues FROM maintenance_issues";
$maintenanceResult = $conn->query($maintenanceQuery);
$total_maintenance_issues = ($maintenanceResult->num_rows > 0) ? $maintenanceResult->fetch_assoc()['total_issues'] : 0;

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SDHostel</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../../global.css">
    <link rel="stylesheet" href="../CSS/modern-admin.css">
    <script src="../javascript/script.js"></script>
</head>
<body>
    <?php include 'admin_sidebar.php'; ?>
    <?php include 'admin_topbar.php'; ?>

    <div class="admin-content">
        <div class="admin-main">
            <div class="admin-page-header">
                <h1>Dashboard</h1>
                <p>Overview of hostel status and pending actions.</p>
            </div>

            <div class="admin-stats">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">Total Students</div>
                        <div class="stat-icon primary">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $total_students; ?></div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">Total Rooms</div>
                        <div class="stat-icon success">
                            <i class="fas fa-door-closed"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $total_rooms; ?></div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">Available Rooms</div>
                        <div class="stat-icon warning">
                            <i class="fas fa-bed"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $available_rooms; ?></div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">Occupied Rooms</div>
                        <div class="stat-icon danger">
                            <i class="fas fa-door-open"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $occupied_rooms; ?></div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">Pending Fees</div>
                        <div class="stat-icon primary">
                            <i class="fas fa-coins"></i>
                        </div>
                    </div>
                    <div class="stat-value">Rs. <?php echo number_format((float) $total_pending_fees, 2); ?></div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-title">Maintenance Issues</div>
                        <div class="stat-icon success">
                            <i class="fas fa-tools"></i>
                        </div>
                    </div>
                    <div class="stat-value"><?php echo $total_maintenance_issues; ?></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

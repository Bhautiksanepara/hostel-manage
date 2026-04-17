<?php
include '../../../backend/user/dashboard.php'; // Fetch user data from backend
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SDHostel - Dashboard</title>
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
                    <h1>Welcome back, <?php echo htmlspecialchars($userData['firstName']); ?> 👋</h1>
                    <p>OTR Number: <?php echo htmlspecialchars($_SESSION['otr_number'] ?? ''); ?></p>
                    <p>Manage your room, fees, and requests from one place.</p>
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="widget-card">
                    <div class="widget-icon primary"><i class="fa-solid fa-door-open"></i></div>
                    <div class="widget-label">Room Number</div>
                    <div class="widget-value"><?php echo htmlspecialchars($userData['room_number']); ?></div>
                    <div class="widget-footer">Current room assignment</div>
                </div>
                <div class="widget-card">
                    <div class="widget-icon primary"><i class="fa-solid fa-bed"></i></div>
                    <div class="widget-label">Room Status</div>
                    <div class="widget-value"><?php echo htmlspecialchars($userData['room_status']); ?></div>
                    <div class="widget-footer">Allocation status</div>
                </div>
                <div class="widget-card">
                    <div class="widget-icon primary"><i class="fa-solid fa-tools"></i></div>
                    <div class="widget-label">Maintenance Requests</div>
                    <div class="widget-value">2 Pending</div>
                    <div class="widget-footer">Open requests</div>
                </div>
                <div class="widget-card">
                    <div class="widget-icon primary"><i class="fa-solid fa-wallet"></i></div>
                    <div class="widget-label">Fees Status</div>
                    <div class="widget-value"><?php echo htmlspecialchars($userData['fees_status']); ?></div>
                    <div class="widget-footer">Payment summary</div>
                </div>
            </div>

            <div class="section">
                <h3>Latest Notices 📢</h3>
                <table class="notice-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Notice</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>2025-04-01</td>
                            <td>Water supply will be off from 10 AM to 1 PM.</td>
                        </tr>
                        <tr>
                            <td>2025-03-28</td>
                            <td>Submit your maintenance complaints by 30th March.</td>
                        </tr>
                        <tr>
                            <td>2025-03-25</td>
                            <td>Common area cleaning scheduled for Saturday.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>

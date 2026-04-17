<?php
session_start();
date_default_timezone_set('Asia/Kolkata');

if (!isset($_SESSION['gatekeeper_logged_in']) || $_SESSION['gatekeeper_logged_in'] !== 1) {
    header('Location: login.php');
    exit();
}

$mode = $_GET['mode'] ?? 'out';
$mode = in_array($mode, ['out', 'in', 'leave'], true) ? $mode : 'out';
$mode_label = strtoupper($mode);

$con = new mysqli('localhost', 'root', '', 'hostel_manage');
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

$status_message = '';
$image = '';
$isLate = false;
$debug_rows = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otr_number = trim($_POST['otr_number'] ?? '');

    if ($otr_number === '') {
        $status_message = 'Please enter an OTR number.';
        $image = 'cross.png';
    } else {
        if ($mode === 'out') {
            $query = "SELECT id, status, type, date_from, out_time, in_time, check_out_time, check_out_date, check_in_time, check_in_date, late_entry
                      FROM gatepass
                      WHERE otr_number = ? AND type = 'Gate'
                      ORDER BY id DESC";
        } elseif ($mode === 'in') {
            $query = "SELECT id, status, type, date_from, out_time, in_time, check_out_time, check_out_date, check_in_time, check_in_date, late_entry
                      FROM gatepass
                      WHERE otr_number = ? AND check_out_time IS NOT NULL AND check_in_time IS NULL
                      ORDER BY id DESC";
        } else {
            $query = "SELECT id, status, type, date_from, out_time, in_time, check_out_time, check_out_date, check_in_time, check_in_date, late_entry
                      FROM gatepass
                      WHERE otr_number = ? AND type = 'Leave'
                      ORDER BY id DESC";
        }

        $stmt = $con->prepare($query);
        $stmt->bind_param("s", $otr_number);
        $stmt->execute();
        $result = $stmt->get_result();
        $request = $result->fetch_assoc();
        $stmt->close();

        if (isset($_GET['debug']) && $_GET['debug'] === '1') {
            $debugQuery = "SELECT id, type, status, date_from, out_time, in_time, check_out_time, check_in_time
                           FROM gatepass WHERE otr_number = ? ORDER BY id DESC LIMIT 5";
            $debugStmt = $con->prepare($debugQuery);
            $debugStmt->bind_param("s", $otr_number);
            $debugStmt->execute();
            $debugResult = $debugStmt->get_result();
            while ($row = $debugResult->fetch_assoc()) {
                $debug_rows[] = $row;
            }
            $debugStmt->close();
        }

        if ($request) {
            if ($request['status'] === 'Approved' || $mode === 'in') {
                $current_date = date('Y-m-d');
                $current_time = date('H:i:s');

                if ($mode === 'out' || $mode === 'leave') {
                    if ($request['status'] !== 'Approved') {
                        $status_message = 'Request not approved. Student cannot go outside.';
                        $image = 'cross.png';
                    } elseif (!empty($request['check_out_time'])) {
                        $status_message = 'Already checked out. Use IN to check back in.';
                        $image = 'cross.png';
                    } elseif ($current_date >= $request['date_from'] && $current_time >= $request['out_time']) {
                        $updateQuery = "UPDATE gatepass SET check_out_time = ?, check_out_date = ? WHERE id = ?";
                        $stmt = $con->prepare($updateQuery);
                        $stmt->bind_param("ssi", $current_time, $current_date, $request['id']);
                        $stmt->execute();
                        $stmt->close();

                        $status_message = $mode === 'leave'
                            ? 'Leave approved: student checked out successfully.'
                            : 'Student has been checked out successfully.';
                        $image = 'right.png';
                    } else {
                        $status_message = 'Request approved, but not yet time to leave.';
                        $image = 'cross.png';
                    }
                }

                if ($mode === 'in') {
                    if (empty($request['check_out_time'])) {
                        $status_message = 'Student has not checked out yet. Use OUT first.';
                        $image = 'cross.png';
                    } elseif (!empty($request['check_in_time'])) {
                        $status_message = 'Already checked in.';
                        $image = 'cross.png';
                    } else {
                        $lateTimeLimit = $request['in_time'] ? date('H:i:s', strtotime($request['in_time'])) : null;
                        if ($lateTimeLimit && $current_time > $lateTimeLimit) {
                            $isLate = true;
                        }
                        $updateQuery = "UPDATE gatepass SET check_in_time = ?, check_in_date = ?, late_entry = ? WHERE id = ?";
                        $lateEntryValue = $isLate ? 1 : 0;
                        $stmt = $con->prepare($updateQuery);
                        $stmt->bind_param("ssii", $current_time, $current_date, $lateEntryValue, $request['id']);
                        $stmt->execute();
                        $stmt->close();

                        $status_message = $isLate ? 'Student is late in checking in!' : 'Student has been checked in successfully.';
                        $image = $isLate ? 'cross.png' : 'right.png';
                    }
                }
            } else {
                $status_message = 'Request not approved. Student cannot go outside.';
                $image = 'cross.png';
            }
        } else {
            if ($mode === 'out') {
                $status_message = 'No gate pass request found for OUT.';
            } elseif ($mode === 'in') {
                $status_message = 'No open checkout found for IN. Check if the student already checked in or did not check out.';
            } else {
                $status_message = 'No leave request found for OUT.';
            }
            $image = 'cross.png';
        }
    }
}
$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gatekeeper</title>
    <link rel="stylesheet" href="../global.css">
    <link rel="stylesheet" href="../user/CSS/modern-dashboard.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="content">
        <div class="main-content">
            <div class="page-header">
                <div>
                    <h1>Gatekeeper - <?php echo htmlspecialchars($mode_label); ?></h1>
                    <p>Verify student in/out/leave using the OTR number.</p>
                </div>
            </div>

            <div class="widget-card" style="max-width: 520px;">
                <form method="POST">
                    <div class="form-group">
                        <label for="otr_number">OTR Number</label>
                        <input type="text" id="otr_number" name="otr_number" class="form-control" placeholder="Enter OTR Number" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
                <?php if (!empty($status_message)): ?>
                    <div style="margin-top: 16px; font-size: 16px;"><?php echo $status_message; ?></div>
                    <img style="width: 90px; margin-top: 12px;" src="<?php echo $image; ?>" alt="Status">
                <?php endif; ?>
            </div>

            <?php if (!empty($debug_rows)): ?>
                <div class="table-container" style="margin-top: 24px;">
                    <div class="table-header">
                        <h3>Debug: Latest Gatepass Rows</h3>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Out Time</th>
                                <th>In Time</th>
                                <th>Check Out</th>
                                <th>Check In</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($debug_rows as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['type']); ?></td>
                                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                                    <td><?php echo htmlspecialchars($row['out_time']); ?></td>
                                    <td><?php echo htmlspecialchars($row['in_time']); ?></td>
                                    <td><?php echo htmlspecialchars($row['check_out_time']); ?></td>
                                    <td><?php echo htmlspecialchars($row['check_in_time']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

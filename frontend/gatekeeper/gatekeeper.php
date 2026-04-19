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

$con->query("CREATE TABLE IF NOT EXISTS gatepass_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gatepass_id INT NOT NULL,
    otr_number VARCHAR(255) NOT NULL,
    type VARCHAR(50) NOT NULL,
    check_out_date DATE NOT NULL,
    check_out_time TIME NOT NULL,
    check_in_date DATE DEFAULT NULL,
    check_in_time TIME DEFAULT NULL,
    late_entry TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_gatepass_open (gatepass_id, check_in_time),
    INDEX idx_otr_open (otr_number, check_in_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

// Migrate any currently-open checkout from the old single-use columns so
// students who are already OUT can still check IN after this update.
$con->query("INSERT INTO gatepass_logs (gatepass_id, otr_number, type, check_out_date, check_out_time)
    SELECT gp.id, gp.otr_number, gp.type, gp.check_out_date, gp.check_out_time
    FROM gatepass gp
    WHERE gp.check_out_time IS NOT NULL
      AND gp.check_in_time IS NULL
      AND NOT EXISTS (
          SELECT 1 FROM gatepass_logs gl
          WHERE gl.gatepass_id = gp.id AND gl.check_in_time IS NULL
      )");

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
        if ($mode === 'in') {
            $query = "SELECT gp.id, gp.status, gp.type, gp.date_from, gp.date_to, gp.out_time, gp.in_time,
                             gp.late_entry, gl.id AS log_id, gl.check_out_time, gl.check_out_date,
                             gl.check_in_time, gl.check_in_date
                      FROM gatepass gp
                      INNER JOIN gatepass_logs gl ON gp.id = gl.gatepass_id
                      WHERE gp.otr_number = ? AND gp.status = 'Approved' AND gl.check_in_time IS NULL
                      ORDER BY gl.check_out_date DESC, gl.check_out_time DESC, gl.id DESC
                      LIMIT 1";
            $stmt = $con->prepare($query);
            $stmt->bind_param("s", $otr_number);
        } else {
            $passType = $mode === 'leave' ? 'Leave' : 'Gate';
            $query = "SELECT id, status, type, date_from, date_to, out_time, in_time, check_out_time, check_out_date, check_in_time, check_in_date, late_entry
                      FROM gatepass
                      WHERE otr_number = ? AND type = ? AND status = 'Approved'
                      ORDER BY date_from ASC, out_time ASC, id ASC";
            $stmt = $con->prepare($query);
            $stmt->bind_param("ss", $otr_number, $passType);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $request = null;
        $currentTimestamp = time();
        $futureRequestFound = false;
        $expiredRequestFound = false;

        if ($mode === 'in') {
            $request = $result->fetch_assoc();
        } else {
            while ($row = $result->fetch_assoc()) {
                $startTimestamp = strtotime($row['date_from'] . ' ' . $row['out_time']);
                $endTimestamp = strtotime($row['date_to'] . ' ' . $row['in_time']);

                if ($startTimestamp !== false && $currentTimestamp < $startTimestamp) {
                    $futureRequestFound = true;
                    continue;
                }

                if ($endTimestamp !== false && $currentTimestamp > $endTimestamp) {
                    $expiredRequestFound = true;
                    continue;
                }

                $request = $row;
                break;
            }
        }
        $stmt->close();

        if (isset($_GET['debug']) && $_GET['debug'] === '1') {
            $debugQuery = "SELECT id, type, status, date_from, date_to, out_time, in_time, check_out_time, check_in_time
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
            if ($request['status'] === 'Approved') {
                $current_date = date('Y-m-d');
                $current_time = date('H:i:s');

                if ($mode === 'out' || $mode === 'leave') {
                    $openLogQuery = "SELECT id FROM gatepass_logs WHERE gatepass_id = ? AND check_in_time IS NULL LIMIT 1";
                    $openLogStmt = $con->prepare($openLogQuery);
                    $openLogStmt->bind_param("i", $request['id']);
                    $openLogStmt->execute();
                    $openLogResult = $openLogStmt->get_result();
                    $hasOpenLog = $openLogResult->num_rows > 0;
                    $openLogStmt->close();

                    if ($hasOpenLog) {
                        $status_message = 'Already checked out. Use IN to check back in.';
                        $image = 'cross.png';
                    } else {
                        $logQuery = "INSERT INTO gatepass_logs (gatepass_id, otr_number, type, check_out_time, check_out_date) VALUES (?, ?, ?, ?, ?)";
                        $stmt = $con->prepare($logQuery);
                        $stmt->bind_param("issss", $request['id'], $otr_number, $request['type'], $current_time, $current_date);
                        $stmt->execute();
                        $stmt->close();

                        $updateQuery = "UPDATE gatepass SET check_out_time = ?, check_out_date = ?, check_in_time = NULL, check_in_date = NULL WHERE id = ?";
                        $stmt = $con->prepare($updateQuery);
                        $stmt->bind_param("ssi", $current_time, $current_date, $request['id']);
                        $stmt->execute();
                        $stmt->close();

                        $status_message = $mode === 'leave'
                            ? 'Leave approved: student checked out successfully.'
                            : 'Student has been checked out successfully.';
                        $image = 'right.png';
                    }
                }

                if ($mode === 'in') {
                    if (empty($request['log_id'])) {
                        $status_message = 'Student has not checked out yet. Use OUT first.';
                        $image = 'cross.png';
                    } else {
                        $returnTimestamp = strtotime($request['date_to'] . ' ' . $request['in_time']);
                        if ($returnTimestamp !== false && $currentTimestamp > $returnTimestamp) {
                            $isLate = true;
                        }
                        $lateEntryValue = $isLate ? 1 : 0;

                        $logUpdateQuery = "UPDATE gatepass_logs SET check_in_time = ?, check_in_date = ?, late_entry = ? WHERE id = ?";
                        $stmt = $con->prepare($logUpdateQuery);
                        $stmt->bind_param("ssii", $current_time, $current_date, $lateEntryValue, $request['log_id']);
                        $stmt->execute();
                        $stmt->close();

                        $updateQuery = "UPDATE gatepass SET check_in_time = ?, check_in_date = ?, late_entry = ? WHERE id = ?";
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
            if ($futureRequestFound) {
                $status_message = 'Request approved, but not yet time to leave.';
            } elseif ($expiredRequestFound) {
                $status_message = 'Approved request time has expired. Student cannot use this pass now.';
            } elseif ($mode === 'out') {
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

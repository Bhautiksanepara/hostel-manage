<?php
session_start();
include '../../../backend/dbconnection.php';
require_once '../../../fpdf186/fpdf.php';

if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== 1 || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../../user/pages/login.php');
    exit();
}

$message = '';

function generateDigitalIcard($request)
{
    $outputDir = '../../../uploads/icards/';
    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0777, true);
    }

    $fileName = 'digital_icard_' . (int) $request['id'] . '.pdf';
    $relativePath = 'uploads/icards/' . $fileName;
    $absolutePath = $outputDir . $fileName;

    $pdf = new FPDF('L', 'mm', [86, 54]);
    $pdf->AddPage();
    $pdf->SetFillColor(37, 99, 235);
    $pdf->Rect(0, 0, 86, 14, 'F');
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(0, 9, 'PATELDHAM HOSTEL', 0, 1, 'C');

    $pdf->SetTextColor(15, 23, 42);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(0, 8, 'Student I-Card', 0, 1, 'C');

    $photoPath = !empty($request['photo']) ? '../../../' . $request['photo'] : '';
    if ($photoPath !== '' && file_exists($photoPath)) {
        $pdf->Image($photoPath, 6, 22, 18, 22);
    } else {
        $pdf->Rect(6, 22, 18, 22);
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetXY(6, 31);
        $pdf->Cell(18, 4, 'PHOTO', 0, 0, 'C');
    }

    $pdf->SetXY(28, 22);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(20, 5, 'Name:', 0, 0);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(35, 5, substr($request['name'], 0, 28), 0, 1);

    $pdf->SetX(28);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(20, 5, 'OTR:', 0, 0);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(35, 5, $request['otr_number'], 0, 1);

    $pdf->SetX(28);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(20, 5, 'Dept:', 0, 0);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(35, 5, substr($request['department'], 0, 24), 0, 1);

    $pdf->SetXY(6, 47);
    $pdf->SetFont('Arial', '', 6);
    $pdf->Cell(74, 4, 'Generated on ' . date('Y-m-d'), 0, 0, 'C');
    $pdf->Output('F', $absolutePath);

    return $relativePath;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'], $_POST['status'])) {
    $requestId = (int) $_POST['request_id'];
    $status = $_POST['status'];
    $allowedStatuses = ['Pending', 'Approved', 'Rejected'];

    if (in_array($status, $allowedStatuses, true)) {
        $digitalPath = null;

        if ($status === 'Approved') {
            $requestQuery = "SELECT id, otr_number, name, department, photo FROM icard_requests WHERE id = ?";
            $stmt = $conn->prepare($requestQuery);
            $stmt->bind_param("i", $requestId);
            $stmt->execute();
            $request = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($request) {
                $digitalPath = generateDigitalIcard($request);
            }
        }

        $query = "UPDATE icard_requests SET status = ?, digital_icard = COALESCE(?, digital_icard) WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssi", $status, $digitalPath, $requestId);

        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>I-card request updated.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Unable to update request.</div>";
        }

        $stmt->close();
    }
}

$result = $conn->query("SELECT id, otr_number, name, department, reason, photo, status, digital_icard, request_date
                        FROM icard_requests
                        ORDER BY request_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I-card Requests</title>
    <link rel="stylesheet" href="../../global.css">
    <link rel="stylesheet" href="../CSS/modern-admin.css">
    <script src="../javascript/script.js"></script>
</head>
<body>
<?php include 'admin_sidebar.php'; ?>
<?php include 'admin_topbar.php'; ?>
<div class="admin-content">
    <div class="admin-main">
        <h2>I-card Requests</h2>
        <?php echo $message; ?>

        <table>
            <thead>
                <tr>
                    <th>OTR</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Reason</th>
                    <th>Photo</th>
                    <th>Digital I-card</th>
                    <th>Status</th>
                    <th>Requested At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['otr_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['department']); ?></td>
                        <td><?php echo htmlspecialchars($row['reason']); ?></td>
                        <td>
                            <?php if (!empty($row['photo'])): ?>
                                <a href="../../../<?php echo htmlspecialchars($row['photo']); ?>" target="_blank">View</a>
                            <?php else: ?>
                                No Photo
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($row['digital_icard'])): ?>
                                <a href="../../../<?php echo htmlspecialchars($row['digital_icard']); ?>" target="_blank">Download</a>
                            <?php else: ?>
                                Not Generated
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td><?php echo htmlspecialchars($row['request_date']); ?></td>
                        <td>
                            <form method="POST" class="action-form">
                                <input type="hidden" name="request_id" value="<?php echo (int) $row['id']; ?>">
                                <select name="status" required>
                                    <option value="Pending" <?php echo $row['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Approved" <?php echo $row['status'] === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                    <option value="Rejected" <?php echo $row['status'] === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                                </select>
                                <button type="submit" class="btn btn-primary btn-sm">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
<?php $conn->close(); ?>

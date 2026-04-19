<?php
session_start();

// Check if user is admin
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== 1 || $_SESSION['role'] !== 'admin') {
    header('Location: ../../user/pages/login.php');
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'hostel_manage');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function resolveReceiptAssetPath($path) {
    $normalizedPath = str_replace('\\', '/', trim((string) $path));
    $normalizedPath = preg_replace('#^(\.\./)+#', '', $normalizedPath);
    return '../../../' . ltrim($normalizedPath, '/');
}

// Handle payment verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] === 'verify_payment') {
        $receipt_id = intval($_POST['receipt_id']);
        $otr_number = $conn->real_escape_string($_POST['otr_number']);
        
        // Update receipt and student fee statuses together so student pages
        // immediately show the payment as completed after admin approval.
        $stmt = $conn->prepare("UPDATE receipts SET status = 'approved', verified_by_admin = 1, admin_verified_at = NOW() WHERE id = ?");
        $stmt->bind_param("i", $receipt_id);
        if ($stmt->execute()) {
            // Update fee status to paid
            $stmt2 = $conn->prepare("UPDATE fees SET status = 'paid', payment_date = NOW() WHERE otr_number = ? AND status = 'pending'");
            $stmt2->bind_param("s", $otr_number);
            $stmt2->execute();
            $stmt2->close();

            $stmt3 = $conn->prepare("UPDATE users SET fees_status = 'paid' WHERE otr_number = ?");
            $stmt3->bind_param("s", $otr_number);
            $stmt3->execute();
            $stmt3->close();
            
            $message = "<div class='alert alert-success'>✅ Payment verified successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>❌ Error verifying payment: " . $conn->error . "</div>";
        }
        $stmt->close();
    }
    
    elseif ($_POST['action'] === 'reject_payment') {
        $receipt_id = intval($_POST['receipt_id']);
        $rejection_reason = $conn->real_escape_string($_POST['rejection_reason']);
        
        // Update receipt status to rejected
        $stmt = $conn->prepare("UPDATE receipts SET status = 'rejected', verified_by_admin = 0, rejection_note = ? WHERE id = ?");
        $stmt->bind_param("si", $rejection_reason, $receipt_id);
        if ($stmt->execute()) {
            $message = "<div class='alert alert-warning'>⚠️ Payment rejected. Student will be notified.</div>";
        } else {
            $message = "<div class='alert alert-danger'>❌ Error rejecting payment.</div>";
        }
        $stmt->close();
    }
}

// Get all pending fee payments with receipts
$query = "SELECT 
    r.id as receipt_id,
    r.otr_number,
    r.upi_id,
    r.transaction_id,
    r.amount,
    r.file_path,
    r.uploaded_at,
    r.verified_by_admin,
    r.rejection_note,
    u.firstName,
    u.email,
    f.academic_year,
    f.status as fee_status,
    f.amount as fee_amount
FROM receipts r
JOIN users u ON r.otr_number = u.otr_number
JOIN fees f ON r.otr_number = f.otr_number
WHERE f.status = 'pending'
ORDER BY r.uploaded_at DESC";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Payment Verification</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../../global.css">
    <link rel="stylesheet" href="../CSS/modern-admin.css">
    <style>
        body {
            background-color: #f5f5f5;
        }
        .content {
            padding: 20px;
        }
        .card {
            margin-bottom: 20px;
            border: 1px solid #e0e0e0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 8px 8px 0 0;
        }
        .card-body {
            padding: 20px;
        }
        .receipt-card {
            border-left: 5px solid #ffc107;
            margin-bottom: 15px;
        }
        .receipt-card.verified {
            border-left-color: #28a745;
            background-color: #f0fdf4;
        }
        .receipt-card.rejected {
            border-left-color: #dc3545;
            background-color: #fdf0f0;
        }
        .payment-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        .info-item {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 5px;
            border-left: 3px solid #667eea;
        }
        .info-label {
            font-weight: bold;
            color: #667eea;
            font-size: 12px;
            text-transform: uppercase;
        }
        .info-value {
            font-size: 16px;
            color: #333;
            margin-top: 5px;
        }
        .btn-verify {
            background-color: #28a745;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            margin-right: 5px;
        }
        .btn-verify:hover {
            background-color: #218838;
            color: white;
            text-decoration: none;
        }
        .btn-reject {
            background-color: #dc3545;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-reject:hover {
            background-color: #c82333;
            color: white;
            text-decoration: none;
        }
        .btn-view {
            background-color: #007bff;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            margin-right: 5px;
        }
        .btn-view:hover {
            background-color: #0056b3;
            color: white;
            text-decoration: none;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-pending {
            background-color: #ffc107;
            color: #333;
        }
        .badge-verified {
            background-color: #28a745;
            color: white;
        }
        .badge-rejected {
            background-color: #dc3545;
            color: white;
        }
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-box {
            background: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            border-left: 4px solid #667eea;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        .stat-label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            margin-top: 5px;
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<?php include 'admin_sidebar.php'; ?>
<?php include 'admin_topbar.php'; ?>

<div class="admin-content">
    <h2>🧾 Payment Verification Dashboard</h2>
    
    <?php if (isset($message)) echo $message; ?>
    
    <!-- Statistics -->
    <div class="stats">
        <?php
        $pending = $conn->query("SELECT COUNT(*) as count FROM receipts WHERE verified_by_admin = 0 OR verified_by_admin IS NULL")->fetch_assoc()['count'];
        $verified = $conn->query("SELECT COUNT(*) as count FROM receipts WHERE verified_by_admin = 1")->fetch_assoc()['count'];
        $rejected = $conn->query("SELECT COUNT(*) as count FROM receipts WHERE verified_by_admin = 0 AND rejection_note IS NOT NULL")->fetch_assoc()['count'];
        ?>
        <div class="stat-box" style="border-left-color: #ffc107;">
            <div class="stat-value" style="color: #ffc107;"><?php echo $pending; ?></div>
            <div class="stat-label">Pending Verification</div>
        </div>
        <div class="stat-box" style="border-left-color: #28a745;">
            <div class="stat-value" style="color: #28a745;"><?php echo $verified; ?></div>
            <div class="stat-label">Verified Payments</div>
        </div>
        <div class="stat-box" style="border-left-color: #dc3545;">
            <div class="stat-value" style="color: #dc3545;"><?php echo $rejected; ?></div>
            <div class="stat-label">Rejected Payments</div>
        </div>
    </div>

    <!-- Payments List -->
    <div class="card">
        <div class="card-header">
            <h5 style="color: white; margin: 0;">📋 Payment Receipts</h5>
        </div>
        <div class="card-body">
            <?php if ($result && $result->num_rows > 0): ?>
                
                <?php while ($payment = $result->fetch_assoc()): ?>
                    <div class="receipt-card <?php echo $payment['verified_by_admin'] ? 'verified' : ($payment['rejection_note'] ? 'rejected' : ''); ?>">
                        
                        <div class="payment-info">
                            <div class="info-item">
                                <div class="info-label">📍 OTR Number</div>
                                <div class="info-value"><?php echo htmlspecialchars($payment['otr_number']); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">👤 Student Name</div>
                                <div class="info-value"><?php echo htmlspecialchars($payment['firstName']); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">📧 Email</div>
                                <div class="info-value"><?php echo htmlspecialchars($payment['email']); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">💰 Amount</div>
                                <div class="info-value">₹<?php echo number_format($payment['amount'], 2); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">📅 Academic Year</div>
                                <div class="info-value"><?php echo htmlspecialchars($payment['academic_year']); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">🔗 Transaction ID</div>
                                <div class="info-value"><small><?php echo htmlspecialchars($payment['transaction_id']); ?></small></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">⏰ Upload Date</div>
                                <div class="info-value"><?php echo date('M d, Y H:i', strtotime($payment['uploaded_at'])); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">✓ Status</div>
                                <div class="info-value">
                                    <?php 
                                    if ($payment['verified_by_admin']) {
                                        echo "<span class='status-badge badge-verified'>✓ VERIFIED</span>";
                                    } elseif ($payment['rejection_note']) {
                                        echo "<span class='status-badge badge-rejected'>✗ REJECTED</span>";
                                    } else {
                                        echo "<span class='status-badge badge-pending'>⏳ PENDING</span>";
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>

                        <!-- Receipt Image Preview -->
                        <?php if ($payment['file_path']): ?>
                            <div style="margin: 15px 0;">
                                <p><strong>📸 Receipt Image:</strong></p>
                                <a href="<?php echo htmlspecialchars(resolveReceiptAssetPath($payment['file_path'])); ?>" target="_blank" class="btn btn-view">
                                    <i class="fas fa-image"></i> View Receipt
                                </a>
                            </div>
                        <?php endif; ?>

                        <!-- Rejection Note (if rejected) -->
                        <?php if ($payment['rejection_note']): ?>
                            <div style="background: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 10px; border-left: 3px solid #dc3545;">
                                <strong style="color: #721c24;">Rejection Reason:</strong>
                                <p style="color: #721c24; margin-bottom: 0;"><?php echo htmlspecialchars($payment['rejection_note']); ?></p>
                            </div>
                        <?php endif; ?>

                        <!-- Action Buttons (if not verified yet) -->
                        <?php if (!$payment['verified_by_admin'] && !$payment['rejection_note']): ?>
                            <div style="margin-top: 10px;">
                                <!-- Verify Form -->
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="verify_payment">
                                    <input type="hidden" name="receipt_id" value="<?php echo $payment['receipt_id']; ?>">
                                    <input type="hidden" name="otr_number" value="<?php echo $payment['otr_number']; ?>">
                                    <button type="submit" class="btn-verify" onclick="return confirm('Verify this payment?');">
                                        ✓ Verify Payment
                                    </button>
                                </form>

                                <!-- Reject Form -->
                                <button class="btn-reject" onclick="showRejectForm(<?php echo $payment['receipt_id']; ?>, '<?php echo htmlspecialchars($payment['firstName']); ?>');">
                                    ✗ Reject Payment
                                </button>
                            </div>
                        <?php endif; ?>

                    </div>
                <?php endwhile; ?>

            <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-inbox" style="font-size: 50px; margin-bottom: 20px;"></i>
                    <h4>No Pending Payments</h4>
                    <p>All payments have been verified or there are no pending fees.</p>
                </div>
            <?php endif; ?>

        </div>
    </div>

</div>

<!-- Reject Payment Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Payment</h5>
                <button type="button" class="close" data-dismiss="modal" style="color: white;">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST" id="rejectForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="reject_payment">
                    <input type="hidden" name="receipt_id" id="rejectReceiptId">
                    
                    <div class="form-group">
                        <label>Reason for Rejection:</label>
                        <textarea class="form-control" name="rejection_reason" rows="4" required placeholder="e.g., Receipt unclear, Amount mismatch, Student name not visible..."></textarea>
                        <small class="form-text text-muted">This reason will be visible to the student</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

<script>
function showRejectForm(receiptId, studentName) {
    document.getElementById('rejectReceiptId').value = receiptId;
    $('#rejectModal').modal('show');
}
</script>

</body>
</html>

<?php
$conn->close();
?>

<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== 1) {
    header('Location: login.php');
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'hostel_manage');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/hostel-manage/backend/UPIQRCodeGenerator.php';

$otr_number = $_SESSION['otr_number'];
$student_name = $_SESSION['email'];

// Get student details
$stmt = $conn->prepare("SELECT firstName, email FROM users WHERE otr_number = ?");
$stmt->bind_param("s", $otr_number);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$student_name = $student['firstName'] ?? 'Student';

// Get pending fees
$stmt = $conn->prepare("SELECT id, amount, academic_year FROM fees WHERE otr_number = ? AND status = 'pending' LIMIT 1");
$stmt->bind_param("s", $otr_number);
$stmt->execute();
$result = $stmt->get_result();
$pending_fee = $result->fetch_assoc();

$qr_generated = false;
$qr_code_image = null;
$upi_url = null;
$pending_amount = 0;

if ($pending_fee) {
    $pending_amount = $pending_fee['amount'];
    
    // Generate QR code
    $qr_gen = new UPIQRCodeGenerator($conn);
    $qr_code_image = $qr_gen->generateQRCodeBase64($pending_amount, $otr_number, $student_name, 400);
    $upi_url = $qr_gen->generateUPIURL($pending_amount, $otr_number, $student_name);
    $qr_generated = true;
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Payment QR Code</title>
    <link rel="stylesheet" href="../../global.css">
    <link rel="stylesheet" href="../CSS/modern-fees.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container-main {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #667eea;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #333;
            font-size: 28px;
            font-weight: bold;
        }
        .header p {
            color: #666;
            margin-top: 10px;
        }
        .fee-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 5px solid #10b981;
        }
        .fee-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
            font-size: 16px;
        }
        .fee-row:last-child {
            border-bottom: none;
        }
        .fee-label {
            color: #666;
            font-weight: 600;
        }
        .fee-value {
            color: #333;
            font-weight: 700;
        }
        .amount-due {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #ffc107;
        }
        .amount-due h5 {
            margin: 0;
            font-weight: bold;
        }
        .amount-display {
            font-size: 32px;
            font-weight: bold;
            color: #d48806;
            margin-top: 5px;
        }
        .qr-section {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background: #f0f4ff;
            border-radius: 10px;
        }
        .qr-code {
            display: inline-block;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .qr-code img {
            width: 300px;
            height: 300px;
            border-radius: 8px;
        }
        .qr-instructions {
            background: #e7f3ff;
            border: 2px solid #2196F3;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            color: #1565c0;
            font-size: 14px;
            line-height: 1.6;
        }
        .qr-instructions h5 {
            color: #1565c0;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .qr-instructions li {
            margin: 5px 0;
        }
        .no-pending {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }
        .no-pending i {
            font-size: 60px;
            color: #10b981;
            margin-bottom: 20px;
        }
        .btn-custom {
            padding: 12px 30px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 8px;
            margin-top: 10px;
        }
        .btn-pay {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            width: 100%;
            text-decoration: none;
            display: inline-block;
            border: none;
            cursor: pointer;
            transition: transform 0.3s;
        }
        .btn-pay:hover {
            transform: scale(1.02);
            color: white;
        }
        .btn-back {
            background: #6c757d;
            color: white;
            width: 48%;
            display: inline-block;
            text-align: center;
            text-decoration: none;
            padding: 12px;
            border-radius: 8px;
            margin: 10px 1%;
        }
        .btn-back:hover {
            background: #5a6268;
            color: white;
        }
        .payment-method {
            text-align: center;
            margin: 20px 0;
            font-size: 14px;
            color: #666;
        }
        .security-note {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-top: 15px;
            font-size: 13px;
        }
        .help-text {
            text-align: center;
            color: #999;
            font-size: 12px;
            margin-top: 15px;
        }
    </style>
</head>
<body>

<div class="container-main">
    <div class="header">
        <h1>💳 Pay Your Hostel Fees</h1>
        <p>Secure UPI Payment with Locked Amount</p>
    </div>

    <?php if ($pending_fee && $qr_generated): ?>
        
        <!-- Fee Details -->
        <div class="fee-details">
            <div class="fee-row">
                <span class="fee-label">📋 Reference (OTR):</span>
                <span class="fee-value"><?php echo htmlspecialchars($otr_number); ?></span>
            </div>
            <div class="fee-row">
                <span class="fee-label">👤 Name:</span>
                <span class="fee-value"><?php echo htmlspecialchars($student_name); ?></span>
            </div>
            <div class="fee-row">
                <span class="fee-label">📅 Academic Year:</span>
                <span class="fee-value"><?php echo htmlspecialchars($pending_fee['academic_year']); ?></span>
            </div>
        </div>

        <!-- Amount Due Display -->
        <div class="amount-due">
            <h5>Amount Due (Locked ✓)</h5>
            <div class="amount-display">₹<?php echo number_format($pending_amount, 2); ?></div>
            <small>This amount cannot be changed during payment</small>
        </div>

        <!-- QR Code Section -->
        <div class="qr-section">
            <h4 style="color: #333; margin-bottom: 20px;">📱 Scan to Pay</h4>
            <div class="qr-code">
                <img src="<?php echo htmlspecialchars($qr_code_image); ?>" alt="UPI QR Code">
            </div>
            <p style="margin-top: 15px; color: #666; font-size: 14px;">
                Scan this QR code with Google Pay, PhonePe, or any UPI app
            </p>
        </div>

        <!-- Instructions -->
        <div class="qr-instructions">
            <h5>📋 How to Pay:</h5>
            <ol style="margin-bottom: 0;">
                <li><strong>Open any UPI app</strong> (Google Pay, PhonePe, Paytm, etc.)</li>
                <li><strong>Tap "Scan QR Code"</strong> option</li>
                <li><strong>Point camera</strong> at the QR code above</li>
                <li><strong>Amount will auto-fill</strong> (₹<?php echo number_format($pending_amount, 2); ?>)</li>
                <li><strong>Review and tap "Pay"</strong></li>
                <li><strong>Use your PIN</strong> to confirm payment</li>
                <li><strong>Screenshot your receipt</strong> for hostel office</li>
            </ol>
        </div>

        <!-- Direct Payment Link (Fallback) -->
        <div style="text-align: center; margin-top: 20px;">
            <p style="color: #666; font-size: 13px; margin-bottom: 10px;">Can't scan? Use direct link:</p>
            <a href="<?php echo htmlspecialchars($upi_url); ?>" class="btn btn-pay btn-custom">
                💳 Pay ₹<?php echo number_format($pending_amount, 2); ?> Now
            </a>
        </div>

        <!-- After Payment Instructions -->
        <div class="security-note">
            <strong>✓ After Payment:</strong><br>
            1. Screenshot the payment receipt<br>
            2. Go to your dashboard → Upload Receipt<br>
            3. Share it with hostel office for verification<br>
            4. Your fees will be updated after admin verification
        </div>

        <!-- Payment Method Info -->
        <div class="payment-method">
            <small>💰 Payment received by: <strong>Pateldham Hostel</strong></small>
        </div>

    <?php else: ?>
        
        <!-- No Pending Fees -->
        <div class="no-pending">
            <div style="font-size: 50px; margin-bottom: 20px;">✅</div>
            <h4>No Pending Fees</h4>
            <p>You don't have any pending hostel fees at the moment.</p>
            <p>Keep checking back for any new fee updates.</p>
            <a href="dashboard.php" class="btn btn-secondary" style="margin-top: 20px;">← Back to Dashboard</a>
        </div>

    <?php endif; ?>

    <!-- Back Button -->
    <div style="text-align: center; margin-top: 20px;">
        <a href="dashboard.php" class="btn btn-back">← Back</a>
        <?php if ($qr_generated): ?>
            <a href="hostel-fees.php" class="btn btn-back">View All Fees →</a>
        <?php endif; ?>
    </div>

    <div class="help-text">
        Having issues? Contact hostel office or admin for support.
    </div>

</div>

</body>
</html>

<?php
$conn->close();
?>

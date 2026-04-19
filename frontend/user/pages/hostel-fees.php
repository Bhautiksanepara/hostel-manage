<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hostel_manage";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['otr_number'])) {
    die("OTR number is not set. Please log in again.");
}

function resolveReceiptFilePath($path) {
    $normalizedPath = str_replace('\\', '/', trim((string) $path));
    $normalizedPath = preg_replace('#^(\.\./)+#', '', $normalizedPath);
    $normalizedPath = preg_replace('#^hostel-manage/#', '', ltrim($normalizedPath, '/'));
    return '/hostel-manage/' . $normalizedPath;
}

function receiptFileExists($path) {
    $normalizedPath = str_replace('\\', '/', trim((string) $path));
    $normalizedPath = preg_replace('#^(\.\./)+#', '', $normalizedPath);
    $normalizedPath = preg_replace('#^hostel-manage/#', '', ltrim($normalizedPath, '/'));
    return $normalizedPath !== '' && file_exists($_SERVER['DOCUMENT_ROOT'] . '/hostel-manage/' . $normalizedPath);
}

$otr_number = $_SESSION['otr_number'];

require_once $_SERVER['DOCUMENT_ROOT'] . '/hostel-manage/backend/UPIQRCodeGenerator.php';

$checkFees = "SELECT amount, status FROM fees WHERE otr_number = ? ORDER BY FIELD(status, 'pending', 'paid'), id DESC LIMIT 1";
$stmt = $conn->prepare($checkFees);
$stmt->bind_param("s", $otr_number);
$stmt->execute();
$feesResult = $stmt->get_result();
$feesData = $feesResult->fetch_assoc();
$stmt->close();

$studentStmt = $conn->prepare("SELECT firstName FROM users WHERE otr_number = ?");
$studentStmt->bind_param("s", $otr_number);
$studentStmt->execute();
$studentResult = $studentStmt->get_result();
$student = $studentResult->fetch_assoc();
$studentStmt->close();

$student_name = $student['firstName'] ?? 'Student';
$hasPendingFees = ($feesData && $feesData['status'] === 'pending');
$amountToPay = $feesData ? $feesData['amount'] : "N/A";
$displayAmount = is_numeric($amountToPay) ? number_format((float) $amountToPay, 2) : 'N/A';

$qr_code_image = null;
$upi_url = null;
$upi_config = null;
if ($hasPendingFees && is_numeric($amountToPay)) {
    $qr_gen = new UPIQRCodeGenerator($conn);
    $qr_code_image = $qr_gen->generateQRCodeBase64($amountToPay, $otr_number, $student_name, 350);
    if (!$qr_code_image) {
        $qr_code_image = $qr_gen->generateQRCodeImageURL($amountToPay, $otr_number, $student_name, 350);
    }
    $upi_url = $qr_gen->generateUPIURL($amountToPay, $otr_number, $student_name);
    $upi_config = $qr_gen->getConfig();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hostel Fees</title>
    <link rel="stylesheet" href="../../global.css">
    <link rel="stylesheet" href="../CSS/modern-dashboard.css">
    <link rel="stylesheet" href="../CSS/modern-fees.css">
    <script src="../javascript/script.js"></script>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="content">
        <?php include 'topbar.php'; ?>

        <div class="main-content">
            <div class="page-header">
                <div>
                    <h1>Hostel Fees</h1>
                    <p>Review your pending payments and upload receipts for verification.</p>
                </div>
            </div>

            <div class="fees-container">
                <section class="fee-summary">
                    <div class="fee-card <?php echo $hasPendingFees ? 'pending' : 'paid'; ?>">
                        <div class="fee-header">
                            <h4>Current Balance</h4>
                            <span class="fee-badge <?php echo $hasPendingFees ? 'pending' : 'paid'; ?>">
                                <?php echo $hasPendingFees ? 'Pending' : 'Paid'; ?>
                            </span>
                        </div>

                        <div class="fee-amount"><?php echo is_numeric($amountToPay) ? 'Rs. ' . htmlspecialchars($displayAmount) : htmlspecialchars($displayAmount); ?></div>
                        <div class="fee-year">Current dues</div>

                        <?php if ($hasPendingFees): ?>
                            <div class="fee-action">
                                <button id="openModal" class="btn btn-primary" type="button">Pay Fees Now</button>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="table-container">
                    <div class="table-header">
                        <div>
                            <h3>Your Approved Receipts</h3>
                            <p>Track receipt verification and download approved payment records.</p>
                        </div>
                    </div>

                    <div class="fees-table-wrap">
                        <table class="fees-table">
                            <thead>
                                <tr>
                                    <th>OTR Number</th>
                                    <th>Status</th>
                                    <th>Receipt</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT * FROM receipts WHERE otr_number = ?";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("s", $otr_number);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                if ($result->num_rows > 0):
                                    while ($row = $result->fetch_assoc()):
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['otr_number']); ?></td>
                                            <td>
                                                <span class="receipt-status <?php echo htmlspecialchars($row['status']); ?>">
                                                    <?php echo htmlspecialchars(ucfirst($row['status'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (htmlspecialchars($row['status']) === 'pending'): ?>
                                                    <span class="receipt-empty">Receipt will be available after approval</span>
                                                <?php elseif (!empty($row['receipt_path']) && receiptFileExists($row['receipt_path'])): ?>
                                                    <a href="<?php echo htmlspecialchars(resolveReceiptFilePath($row['receipt_path'])); ?>" target="_blank" class="btn btn-primary btn-sm">
                                                        Download Receipt
                                                    </a>
                                                <?php else: ?>
                                                    <span class="receipt-empty">Approved receipt PDF is being generated. Please contact admin if it is not available.</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php
                                    endwhile;
                                else:
                                    ?>
                                    <tr>
                                        <td colspan="3" class="empty-state">No approved receipts available.</td>
                                    </tr>
                                    <?php
                                endif;
                                $stmt->close();
                                ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <?php if ($hasPendingFees): ?>
        <div id="modal" class="payment-modal">
            <div class="payment-modal-content">
                <div class="payment-modal-header">
                    <h3>Pay Your Hostel Fees</h3>
                    <button class="payment-modal-close" id="closeModal" type="button">&times;</button>
                </div>

                <div class="payment-modal-body">
                    <?php if ($qr_code_image): ?>
                        <div class="qr-display">
                            <p class="qr-intro"><strong>Scan with Google Pay, PhonePe, or any UPI app</strong></p>
                            <div class="qr-frame">
                                <img src="<?php echo htmlspecialchars($qr_code_image); ?>" alt="UPI QR Code">
                            </div>
                            <p class="qr-amount">Amount: Rs. <?php echo htmlspecialchars($displayAmount); ?></p>
                            <p class="qr-label">This amount is locked for the current payment request.</p>
                        </div>

                        <div class="direct-pay">
                            <p>Can't scan? Use the direct UPI link instead.</p>
                            <a href="<?php echo htmlspecialchars($upi_url); ?>" class="btn btn-primary direct-pay-link">Pay Rs. <?php echo htmlspecialchars($displayAmount); ?> Now</a>
                        </div>

                        <div class="payment-steps">
                            <strong>Steps to Pay:</strong>
                            <ol>
                                <li>Open Google Pay, PhonePe, or Paytm.</li>
                                <li>Tap Scan QR Code.</li>
                                <li>Scan the QR shown above.</li>
                                <li>Confirm the amount and complete payment.</li>
                                <li>Take a screenshot of the successful payment.</li>
                                <li>Upload the receipt below for verification.</li>
                            </ol>
                        </div>
                    <?php else: ?>
                        <div class="payment-alert payment-alert-danger">
                            <p>Unable to generate the QR code right now. Please try again.</p>
                        </div>
                    <?php endif; ?>

                    <div class="upload-section">
                        <h4>Upload Payment Receipt</h4>

                        <form id="receiptUploadForm" action="../../../backend/user/upload_receipt.php" method="POST" enctype="multipart/form-data" class="receipt-form">
                            <input type="hidden" name="otr_number" value="<?php echo htmlspecialchars($otr_number); ?>">
                            <input type="hidden" name="amount" value="<?php echo htmlspecialchars($amountToPay); ?>">
                            <input type="hidden" name="upi_id" value="<?php echo htmlspecialchars($upi_config['upi_id'] ?? 'pateldham@upi'); ?>">

                            <div class="form-group stacked">
                                <label for="transaction_id">Transaction ID</label>
                                <input type="text" id="transaction_id" name="transaction_id" placeholder="e.g. UPI2401091234567890" required>
                            </div>

                            <div class="form-group stacked">
                                <label for="receiptFile">Receipt Screenshot</label>
                                <input type="file" id="receiptFile" name="receiptFile" accept=".jpg,.jpeg,.png,.pdf" required>
                                <small class="help-text">Supported: JPG, PNG, PDF up to 5 MB.</small>
                            </div>

                            <button type="submit" id="uploadBtn" class="btn btn-success upload-btn">Upload Receipt</button>
                        </form>
                    </div>

                    <div class="payment-alert payment-alert-warning">
                        <strong>After Payment:</strong><br>
                        Admin will verify your receipt and update your fee status to "Paid". You will receive confirmation once it is approved.
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modal = document.getElementById('modal');
            var openModalBtn = document.getElementById('openModal');
            var closeModalBtn = document.getElementById('closeModal');

            if (!modal) {
                return;
            }

            if (openModalBtn) {
                openModalBtn.addEventListener('click', function () {
                    modal.style.display = 'flex';
                });
            }

            if (closeModalBtn) {
                closeModalBtn.addEventListener('click', function () {
                    modal.style.display = 'none';
                });
            }

            window.addEventListener('click', function (event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>

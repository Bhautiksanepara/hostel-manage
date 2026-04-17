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

$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] === 'update_upi') {
        $upi_id = $conn->real_escape_string($_POST['upi_id']);
        $receiving_name = $conn->real_escape_string($_POST['receiving_name']);
        $merchant_category = $conn->real_escape_string($_POST['merchant_category']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Validate UPI ID format (basic validation)
        if (!filter_var($upi_id, FILTER_VALIDATE_EMAIL) || strpos($upi_id, '@') === false) {
            $message = "<div class='alert alert-danger'>❌ Invalid UPI ID format. Expected: name@bankname (e.g., hostel@upi)</div>";
        } else {
            // Check if config exists
            $check = $conn->query("SELECT id FROM upi_config LIMIT 1");
            
            if ($check && $check->num_rows > 0) {
                $stmt = $conn->prepare("UPDATE upi_config SET upi_id = ?, receiving_name = ?, merchant_category = ?, is_active = ? WHERE id = 1");
                $stmt->bind_param("sssi", $upi_id, $receiving_name, $merchant_category, $is_active);
            } else {
                $stmt = $conn->prepare("INSERT INTO upi_config (upi_id, receiving_name, merchant_category, is_active) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("sssi", $upi_id, $receiving_name, $merchant_category, $is_active);
            }
            
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>✅ UPI configuration updated successfully!</div>";
            } else {
                $message = "<div class='alert alert-danger'>❌ Error updating configuration: " . $conn->error . "</div>";
            }
            $stmt->close();
        }
    }
}

// Get current UPI config
$upi_config = null;
$result = $conn->query("SELECT * FROM upi_config LIMIT 1");
if ($result && $result->num_rows > 0) {
    $upi_config = $result->fetch_assoc();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - UPI Configuration</title>
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
        .form-group label {
            font-weight: bold;
            color: #333;
        }
        .form-control {
            border-radius: 5px;
            border: 1px solid #ddd;
            padding: 10px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-save {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 30px;
            border-radius: 5px;
            border: none;
            font-weight: bold;
            cursor: pointer;
        }
        .btn-save:hover {
            color: white;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .example-box {
            background: #f5f5f5;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .example-label {
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .code {
            background: #fff;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 3px;
            font-family: monospace;
            margin: 5px 0;
            overflow-x: auto;
        }
        .config-display {
            background: #f0f4ff;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            border-left: 4px solid #667eea;
        }
        .config-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }
        .config-item:last-child {
            border-bottom: none;
        }
        .config-label {
            font-weight: bold;
            color: #667eea;
        }
        .config-value {
            color: #333;
            font-family: monospace;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-active {
            background-color: #28a745;
            color: white;
        }
        .badge-inactive {
            background-color: #dc3545;
            color: white;
        }
        .checkbox-wrapper {
            display: flex;
            align-items: center;
            margin-top: 10px;
        }
        .checkbox-wrapper input {
            margin-right: 10px;
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
            font-weight: bold;
        }
        h4 {
            color: #667eea;
            margin-top: 20px;
            margin-bottom: 15px;
        }
        .help-text {
            font-size: 13px;
            color: #999;
            margin-top: 5px;
        }
    </style>
</head>
<body>

<?php include 'admin_sidebar.php'; ?>
<?php include 'admin_topbar.php'; ?>

<div class="admin-content">
    <h2>💳 UPI Configuration Manager</h2>
    
    <?php if ($message) echo $message; ?>
    
    <!-- UPI Configuration Card -->
    <div class="card">
        <div class="card-header">
            <h5 style="color: white; margin: 0;">⚙️ UPI Payment Settings</h5>
        </div>
        <div class="card-body">
            
            <div class="info-box">
                <strong>ℹ️ Information:</strong><br>
                Configure your hostel's UPI ID to receive student fee payments. Students will generate QR codes using this UPI configuration for secure payments.
            </div>

            <form method="POST">
                <input type="hidden" name="action" value="update_upi">
                
                <div class="form-group">
                    <label for="upi_id">
                        <i class="fas fa-mobile-alt"></i> UPI ID: <span style="color: red;">*</span>
                    </label>
                    <input 
                        type="text" 
                        class="form-control" 
                        id="upi_id" 
                        name="upi_id" 
                        value="<?php echo $upi_config ? htmlspecialchars($upi_config['upi_id']) : 'pateldham@upi'; ?>" 
                        placeholder="e.g., hostel@upi or 9876543210@okaxis"
                        required
                    >
                    <small class="help-text">
                        Format: username@bankname (e.g., hostel@upi, hostel@okhdfcbank)
                    </small>
                </div>

                <div class="form-group">
                    <label for="receiving_name">
                        <i class="fas fa-user"></i> Receiving Name: <span style="color: red;">*</span>
                    </label>
                    <input 
                        type="text" 
                        class="form-control" 
                        id="receiving_name" 
                        name="receiving_name" 
                        value="<?php echo $upi_config ? htmlspecialchars($upi_config['receiving_name']) : 'Pateldham Hostel'; ?>" 
                        placeholder="e.g., Pateldham Hostel"
                        required
                    >
                    <small class="help-text">Name that will appear to students during payment</small>
                </div>

                <div class="form-group">
                    <label for="merchant_category">
                        <i class="fas fa-tag"></i> Merchant Category:
                    </label>
                    <input 
                        type="text" 
                        class="form-control" 
                        id="merchant_category" 
                        name="merchant_category" 
                        value="<?php echo $upi_config ? htmlspecialchars($upi_config['merchant_category']) : 'Education'; ?>" 
                        placeholder="e.g., Education, Accommodation"
                    >
                    <small class="help-text">Category for UPI transaction classification</small>
                </div>

                <div class="checkbox-wrapper">
                    <input 
                        type="checkbox" 
                        id="is_active" 
                        name="is_active" 
                        <?php echo ($upi_config && $upi_config['is_active']) ? 'checked' : ''; ?>
                    >
                    <label for="is_active" style="margin-bottom: 0;">
                        ✓ Active (Enable UPI QR code generation for students)
                    </label>
                </div>

                <div style="margin-top: 20px;">
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save"></i> Save Configuration
                    </button>
                </div>
            </form>

        </div>
    </div>

    <!-- Current Configuration Display -->
    <?php if ($upi_config): ?>
    <div class="card">
        <div class="card-header">
            <h5 style="color: white; margin: 0;">✓ Active Configuration</h5>
        </div>
        <div class="card-body">
            <div class="config-display">
                <div class="config-item">
                    <span class="config-label">UPI ID:</span>
                    <span class="config-value"><?php echo htmlspecialchars($upi_config['upi_id']); ?></span>
                </div>
                <div class="config-item">
                    <span class="config-label">Receiving Name:</span>
                    <span class="config-value"><?php echo htmlspecialchars($upi_config['receiving_name']); ?></span>
                </div>
                <div class="config-item">
                    <span class="config-label">Category:</span>
                    <span class="config-value"><?php echo htmlspecialchars($upi_config['merchant_category']); ?></span>
                </div>
                <div class="config-item">
                    <span class="config-label">Status:</span>
                    <span class="config-value">
                        <span class="status-badge <?php echo $upi_config['is_active'] ? 'badge-active' : 'badge-inactive'; ?>">
                            <?php echo $upi_config['is_active'] ? '🟢 Active' : '🔴 Inactive'; ?>
                        </span>
                    </span>
                </div>
                <div class="config-item">
                    <span class="config-label">Last Updated:</span>
                    <span class="config-value"><?php echo date('M d, Y H:i', strtotime($upi_config['updated_at'])); ?></span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- UPI Format Information -->
    <div class="card">
        <div class="card-header">
            <h5 style="color: white; margin: 0;">📚 UPI Format Reference</h5>
        </div>
        <div class="card-body">
            
            <h4>Common UPI ID Formats:</h4>
            <div class="example-box">
                <div class="example-label">💳 Bank UPI IDs:</div>
                <div class="code">name@okhdfcbank     (HDFC Bank)</div>
                <div class="code">name@okaxis         (Axis Bank)</div>
                <div class="code">name@okicici        (ICICI Bank)</div>
                <div class="code">name@ibl            (HDFC Bank)</div>
            </div>

            <div class="example-box">
                <div class="example-label">📱 Payment App UPI IDs:</div>
                <div class="code">name@paytm          (Paytm)</div>
                <div class="code">name@phonepe        (PhonePe)</div>
                <div class="code">name@googlepay      (Google Pay)</div>
                <div class="code">name@upi            (Generic UPI)</div>
            </div>

            <div class="example-box">
                <div class="example-label">📋 Sample UPI Configurations:</div>
                <div style="background: #fff; padding: 10px; border-radius: 3px; margin: 5px 0;">
                    <strong>Educational Institution:</strong><br>
                    <small>UPI ID: hostel123@upi<br>
                    Name: Pateldham Hostel<br>
                    Category: Education</small>
                </div>
                <div style="background: #fff; padding: 10px; border-radius: 3px; margin: 5px 0;">
                    <strong>Government Account:</strong><br>
                    <small>UPI ID: hostel.mgmt@okhdfcbank<br>
                    Name: Government Hostel Management<br>
                    Category: Accommodation</small>
                </div>
            </div>

            <div class="info-box" style="margin-top: 20px;">
                <strong>⚠️ Important:</strong><br>
                • Your UPI ID must be in format: <strong>username@bankname</strong><br>
                • The UPI ID you provide must be active and linked to your bank account<br>
                • Students will use this UPI to send hostel fee payments<br>
                • The exact amount will be locked in the QR code - students cannot change it
            </div>

        </div>
    </div>

    <!-- Student Payment Flow -->
    <div class="card">
        <div class="card-header">
            <h5 style="color: white; margin: 0;">🔄 Student Payment Flow</h5>
        </div>
        <div class="card-body">
            <ol style="line-height: 2;">
                <li><strong>Student logs in</strong> to their dashboard</li>
                <li><strong>Navigates to "Pay Fees"</strong> or similar option</li>
                <li><strong>Views pending fees</strong> amount</li>
                <li><strong>Generate QR code</strong> - automatically uses your UPI configuration</li>
                <li><strong>Scan with UPI app</strong> (Google Pay, PhonePe, etc.)</li>
                <li><strong>Amount is auto-filled</strong> (locked, cannot change)</li>
                <li><strong>Complete payment</strong> with UPI PIN</li>
                <li><strong>Upload receipt</strong> to dashboard</li>
                <li><strong>Admin verifies</strong> payment receipt</li>
                <li><strong>Fee marked as paid</strong> in system</li>
            </ol>
        </div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

</body>
</html>

<?php
$conn->close();
?>

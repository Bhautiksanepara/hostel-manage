<?php
session_start();
echo "<!DOCTYPE html><html><head><title>Email Configuration</title>";
echo "<style>
body {font-family: Arial; margin: 20px; background-color: #f5f5f5;}
.container {max-width: 700px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1);}
.form-group {margin: 15px 0;}
label {display: block; margin-bottom: 5px; font-weight: bold;}
input[type='text'], input[type='email'], input[type='password'], input[type='number'], select {width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;}
button {background-color: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px;}
button:hover {background-color: #45a049;}
.btn-test {background-color: #008CBA;}
.btn-test:hover {background-color: #007399;}
.success {background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 15px 0; border-radius: 4px; color: #155724;}
.error {background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; margin: 15px 0; border-radius: 4px; color: #721c24;}
.info {background-color: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; margin: 15px 0; border-radius: 4px; color: #0c5460;}
.warning {background-color: #fff3cd; border: 1px solid #ffeeba; padding: 15px; margin: 15px 0; border-radius: 4px; color: #856404;}
h1 {color: #333;}
h2 {color: #666;}
table {width: 100%; margin-top: 20px;}
td {padding: 8px; border-bottom: 1px solid #eee;}
.code {background-color: #f4f4f4; padding: 10px; border-radius: 3px; font-family: monospace; overflow-x: auto;}
small {color: #666;}
.checkbox-group {display: flex; align-items: center;}
input[type='checkbox'] {margin-right: 10px;}
</style></head><body>";

echo "<div class='container'>";
echo "<h1>📧 Email Configuration Manager</h1>";

$conn = new mysqli('localhost', 'root', '', 'hostel_manage');
if ($conn->connect_error) {
    echo "<div class='error'>❌ Database Connection Failed: " . $conn->connect_error . "</div>";
    exit;
}

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] === 'save_config') {
        $smtp_host = $conn->real_escape_string($_POST['smtp_host']);
        $smtp_port = intval($_POST['smtp_port']);
        $smtp_username = $conn->real_escape_string($_POST['smtp_username']);
        $smtp_password = $conn->real_escape_string($_POST['smtp_password']);
        $from_email = $conn->real_escape_string($_POST['from_email']);
        $from_name = $conn->real_escape_string($_POST['from_name']);
        $use_tls = isset($_POST['use_tls']) ? 1 : 0;
        
        // Check if config exists
        $check = $conn->query("SELECT id FROM email_config LIMIT 1");
        
        if ($check && $check->num_rows > 0) {
            $sql = "UPDATE email_config SET smtp_host='$smtp_host', smtp_port=$smtp_port, smtp_username='$smtp_username', smtp_password='$smtp_password', from_email='$from_email', from_name='$from_name', use_tls=$use_tls WHERE id=1";
        } else {
            $sql = "INSERT INTO email_config (smtp_host, smtp_port, smtp_username, smtp_password, from_email, from_name, use_tls) VALUES ('$smtp_host', $smtp_port, '$smtp_username', '$smtp_password', '$from_email', '$from_name', $use_tls)";
        }
        
        if ($conn->query($sql) === TRUE) {
            $message = "<div class='success'>✅ Email configuration saved successfully!</div>";
        } else {
            $message = "<div class='error'>❌ Error saving configuration: " . $conn->error . "</div>";
        }
    }
    
    elseif ($_POST['action'] === 'test_email') {
        $test_email = $conn->real_escape_string($_POST['test_email']);
        
        // Get config
        $result = $conn->query("SELECT * FROM email_config LIMIT 1");
        if (!$result || $result->num_rows === 0) {
            $message = "<div class='error'>❌ Email configuration not found. Please save configuration first.</div>";
        } else {
            $config = $result->fetch_assoc();
            
            require $_SERVER['DOCUMENT_ROOT'] . '/hostel-manage/PHPMailer/Exception.php';
            require $_SERVER['DOCUMENT_ROOT'] . '/hostel-manage/PHPMailer/PHPMailer.php';
            require $_SERVER['DOCUMENT_ROOT'] . '/hostel-manage/PHPMailer/SMTP.php';
            
            use PHPMailer\PHPMailer\PHPMailer;
            use PHPMailer\PHPMailer\Exception;
            
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = $config['smtp_host'];
                $mail->SMTPAuth = true;
                $mail->Username = $config['smtp_username'];
                $mail->Password = $config['smtp_password'];
                $mail->SMTPSecure = $config['use_tls'] ? PHPMailer::ENCRYPTION_STARTTLS : '';
                $mail->Port = $config['smtp_port'];
                
                $mail->setFrom($config['from_email'], $config['from_name']);
                $mail->addAddress($test_email);
                $mail->Subject = "Test Email from Hostel Management System";
                $mail->isHTML(true);
                $mail->Body = "<h2>Email Configuration Test</h2><p>If you received this email, your SMTP configuration is working correctly!</p><p>System: Pateldham Hostel Management</p>";
                
                if ($mail->send()) {
                    $message = "<div class='success'>✅ Test email sent successfully to $test_email!</div>";
                } else {
                    $message = "<div class='error'>❌ Failed to send test email: " . $mail->ErrorInfo . "</div>";
                }
            } catch (Exception $e) {
                $message = "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
            }
        }
    }
}

echo $message;

// Get current config
$config = null;
$result = $conn->query("SELECT * FROM email_config LIMIT 1");
if ($result && $result->num_rows > 0) {
    $config = $result->fetch_assoc();
}

?>

<h2>📋 SMTP Configuration</h2>

<div class='info'>
<strong>ℹ️ Common SMTP Providers:</strong><br>
<table>
<tr><td><strong>Gmail:</strong></td><td>smtp.gmail.com | Port: 587 | TLS: Yes | <a href='https://myaccount.google.com/apppasswords' target='_blank'>Get App Password</a></td></tr>
<tr><td><strong>Outlook:</strong></td><td>smtp.office365.com | Port: 587 | TLS: Yes</td></tr>
<tr><td><strong>SendGrid:</strong></td><td>smtp.sendgrid.net | Port: 587 | TLS: Yes | Username: apikey</td></tr>
<tr><td><strong>Mailgun:</strong></td><td>smtp.mailgun.org | Port: 587 | TLS: Yes</td></tr>
</table>
</div>

<form method='POST'>
<input type='hidden' name='action' value='save_config'>

<div class='form-group'>
<label for='smtp_host'>SMTP Host:</label>
<input type='text' id='smtp_host' name='smtp_host' value='<?php echo $config ? htmlspecialchars($config['smtp_host']) : 'smtp.gmail.com'; ?>' placeholder='e.g., smtp.gmail.com' required>
<small>The address of your email server</small>
</div>

<div class='form-group'>
<label for='smtp_port'>SMTP Port:</label>
<input type='number' id='smtp_port' name='smtp_port' value='<?php echo $config ? $config['smtp_port'] : '587'; ?>' placeholder='587 or 465' required>
<small>Usually 587 (TLS) or 465 (SSL)</small>
</div>

<div class='form-group'>
<label for='smtp_username'>SMTP Username:</label>
<input type='text' id='smtp_username' name='smtp_username' value='<?php echo $config ? htmlspecialchars($config['smtp_username']) : ''; ?>' placeholder='Your email address' required>
</div>

<div class='form-group'>
<label for='smtp_password'>SMTP Password / App Password:</label>
<input type='password' id='smtp_password' name='smtp_password' value='<?php echo $config ? htmlspecialchars($config['smtp_password']) : ''; ?>' placeholder='App-specific password (not regular password)'>
<small>⚠️ Never use your regular password! Use an app-specific password instead.</small>
</div>

<div class='form-group'>
<label for='from_email'>From Email Address:</label>
<input type='email' id='from_email' name='from_email' value='<?php echo $config ? htmlspecialchars($config['from_email']) : ''; ?>' placeholder='no-reply@hostel.com' required>
<small>The email address that will appear as the sender</small>
</div>

<div class='form-group'>
<label for='from_name'>From Name:</label>
<input type='text' id='from_name' name='from_name' value='<?php echo $config ? htmlspecialchars($config['from_name']) : 'Pateldham Hostel'; ?>' placeholder='Hostel Management System'>
<small>The display name for the sender</small>
</div>

<div class='form-group checkbox-group'>
<input type='checkbox' id='use_tls' name='use_tls' <?php echo ($config && $config['use_tls']) ? 'checked' : 'checked'; ?>>
<label for='use_tls' style='margin-bottom: 0;'>Use TLS Encryption</label>
<small>(Usually enabled for port 587)</small>
</div>

<button type='submit' style='width: 100%;'>💾 Save Email Configuration</button>
</form>

<?php if ($config): ?>
<h2>🧪 Test Email Configuration</h2>
<form method='POST'>
<input type='hidden' name='action' value='test_email'>

<div class='form-group'>
<label for='test_email'>Test Email Address:</label>
<input type='email' id='test_email' name='test_email' placeholder='your@email.com' required>
<small>Send a test email to verify the configuration</small>
</div>

<button type='submit' class='btn-test' style='width: 100%;'>📧 Send Test Email</button>
</form>
<?php endif; ?>

<h2>🔧 Email Integration Points</h2>
<div class='info'>
<strong>Emails are used in the following areas:</strong>
<ul>
<li><strong>Registration:</strong> Confirmation email with verification link</li>
<li><strong>Password Reset:</strong> Password reset link sent to user email</li>
<li><strong>Fee Reminders:</strong> Admins can send fee payment reminders</li>
<li><strong>Notifications:</strong> Gate pass approvals, maintenance updates</li>
</ul>
</div>

<h2>📝 Configuration Storage</h2>
<div class='code'>
Configuration is stored in the <strong>email_config</strong> table in the database.
Files that need to use this configuration:
• backend/user/register.php
• backend/user/forgetpass.php
• backend/adminsend_reminder.php
• Any custom email functionality
</div>

<h2>⚠️ Important Security Notes</h2>
<div class='warning'>
<ul>
<li>Never commit password/credentials to version control</li>
<li>Always use app-specific passwords, never your main account password</li>
<li>For Gmail: Enable 2FA and generate App Passwords from https://myaccount.google.com/apppasswords</li>
<li>Consider using environment variables in production instead of database storage</li>
<li>Ensure the email_config table is not directly accessible from the web</li>
</ul>
</div>

<?php
$conn->close();
?>

</div>
</body></html>

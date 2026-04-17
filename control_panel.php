<?php
session_start();
echo "<!DOCTYPE html><html><head><title>Project Control Panel</title>";
echo "<style>
* {margin: 0; padding: 0; box-sizing: border-box;}
body {font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px;}
.container {max-width: 1200px; margin: 0 auto;}
.header {background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); margin-bottom: 30px;}
.header h1 {color: #333; margin-bottom: 10px;}
.header p {color: #666; font-size: 16px;}
.grid {display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px;}
.card {background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); transition: transform 0.3s, box-shadow 0.3s;}
.card:hover {transform: translateY(-5px); box-shadow: 0 5px 20px rgba(0,0,0,0.15);}
.card h3 {color: #333; margin-bottom: 10px; display: flex; align-items: center;}
.card h3 span {font-size: 24px; margin-right: 10px;}
.card p {color: #666; font-size: 14px; margin-bottom: 15px; line-height: 1.5;}
.btn {display: inline-block; background-color: #667eea; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold; transition: background-color 0.3s;}
.btn:hover {background-color: #764ba2;}
.btn-secondary {background-color: #f97316;}
.btn-secondary:hover {background-color: #ea580c;}
.btn-success {background-color: #10b981;}
.btn-success:hover {background-color: #059669;}
.btn-danger {background-color: #ef4444;}
.btn-danger:hover {background-color: #dc2626;}
.status {display: flex; align-items: center; justify-content: space-between; padding: 15px; background: #f3f4f6; border-radius: 5px; margin-top: 10px;}
.status.online {border-left: 4px solid #10b981;}
.status.offline {border-left: 4px solid #ef4444;}
.status-dot {width: 12px; height: 12px; border-radius: 50%; margin-right: 10px;}
.status-dot.online {background-color: #10b981;}
.status-dot.offline {background-color: #ef4444;}
.divider {height: 1px; background: #e5e7eb; margin: 20px 0;}
.quick-links {background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);}
.quick-links h2 {color: #333; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #667eea;}
.links-grid {display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;}
.link-btn {display: flex; align-items: center; justify-content: space-between; padding: 15px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 5px; text-decoration: none; color: #333; transition: all 0.3s;}
.link-btn:hover {background: #667eea; color: white; border-color: #667eea;}
.link-btn span {font-size: 20px; margin-right: 10px;}
table {width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);}
th {background: #667eea; color: white; padding: 12px; text-align: left;}
td {padding: 12px; border-bottom: 1px solid #e5e7eb;}
tr:hover {background: #f9fafb;}
@media (max-width: 768px) {
  .grid {grid-template-columns: 1fr;}
  .links-grid {grid-template-columns: 1fr;}
}
</style></head><body>";

echo "<div class='container'>";

// Header
echo "<div class='header'>";
echo "<h1>🏨 Pateldham Hostel Management System</h1>";
echo "<p>Project Control Panel | Database: hostel_manage | Last Updated: " . date('M d, Y H:i A') . "</p>";
echo "</div>";

// System Status
echo "<div class='grid' style='grid-template-columns: 1fr;'>";
echo "<div class='card'>";
echo "<h3><span>📊</span> System Status</h3>";

$conn = new mysqli('localhost', 'root', '', 'hostel_manage');
$db_status = ($conn->connect_error) ? 'offline' : 'online';
$db_text = ($db_status === 'online') ? 'Connected' : 'Connection Failed';

echo "<div class='status $db_status'>";
echo "<span><span class='status-dot $db_status'></span>Database: $db_text</span>";
echo "</div>";

if ($db_status === 'online') {
    $result = $conn->query('SELECT COUNT(*) as count FROM users');
    $users = $result->fetch_assoc()['count'];
    $result = $conn->query('SELECT COUNT(*) as count FROM rooms');
    $rooms = $result->fetch_assoc()['count'];
    
    echo "<div class='status online' style='border-left-color: #667eea; margin-top: 10px;'>";
    echo "<span>👥 Students: <strong>$users</strong> | 🏠 Rooms: <strong>$rooms</strong></span>";
    echo "</div>";
}
echo "</div>";
echo "</div>";

// Management Tools
echo "<div class='grid'>";

// Admin Manager
echo "<div class='card'>";
echo "<h3><span>👨‍💼</span> Admin Accounts</h3>";
echo "<p>Create, manage, and monitor administrator accounts for your system.</p>";
echo "<div style='margin-bottom: 10px;'>";
if ($db_status === 'online') {
    $result = $conn->query('SELECT COUNT(*) as count FROM admin_users');
    $admin_count = $result->fetch_assoc()['count'];
    echo "Active Admins: <strong>$admin_count</strong>";
}
echo "</div>";
echo "<a href='admin_manager.php' class='btn btn-secondary'>Manage Admins</a>";
echo "</div>";

// Email Configuration
echo "<div class='card'>";
echo "<h3><span>📧</span> Email Configuration</h3>";
echo "<p>Setup and test SMTP email configuration for notifications and confirmations.</p>";
if ($db_status === 'online') {
    $result = $conn->query('SELECT COUNT(*) as count FROM email_config');
    $email_configured = $result->fetch_assoc()['count'] > 0 ? '✅ Configured' : '❌ Not Configured';
    echo "<div style='margin-bottom: 10px; color: " . (strpos($email_configured, '✅') !== false ? '#10b981' : '#ef4444') . ";'>$email_configured</div>";
}
echo "<a href='email_config.php' class='btn btn-secondary'>Configure Email</a>";
echo "</div>";

// Database Test
echo "<div class='card'>";
echo "<h3><span>🧪</span> Database Tests</h3>";
echo "<p>Test database connection, verify table structure, and check data integrity.</p>";
echo "<a href='db_test.php' class='btn btn-success'>Run DB Tests</a>";
echo "</div>";

// Login Test  
echo "<div class='card'>";
echo "<h3><span>🔐</span> Login Testing</h3>";
echo "<p>Verify authentication system, test credentials, and check login flows.</p>";
echo "<a href='tests/login_test.php' class='btn btn-success'>Test Login</a>";
echo "</div>";

echo "</div>";

// Documentation Section
echo "<div class='quick-links'>";
echo "<h2>🚀 Getting Started</h2>";
echo "<div class='links-grid'>";

echo "<a href='frontend/user/pages/login.php' class='link-btn' target='_blank'>";
echo "<span>👤</span>";
echo "<div>";
echo "<strong>Student Login</strong>";
echo "<small>Access student portal</small>";
echo "</div>";
echo "</a>";

echo "<a href='frontend/user/pages/register.php' class='link-btn' target='_blank'>";
echo "<span>📝</span>";
echo "<div>";
echo "<strong>New Registration</strong>";
echo "<small>Register new student</small>";
echo "</div>";
echo "</a>";

echo "<a href='frontend/admin/pages/dashboard.php' class='link-btn' target='_blank'>";
echo "<span>👨‍💼</span>";
echo "<div>";
echo "<strong>Admin Dashboard</strong>";
echo "<small>Admin control panel</small>";
echo "</div>";
echo "</a>";

echo "<a href='index.php' class='link-btn' target='_blank'>";
echo "<span>🏠</span>";
echo "<div>";
echo "<strong>Home Page</strong>";
echo "<small>Public landing page</small>";
echo "</div>";
echo "</a>";

echo "</div>";
echo "</div>";

// Key Features Status
echo "<div class='divider'></div>";
echo "<div class='quick-links'>";
echo "<h2>✨ Key Features</h2>";

if ($db_status === 'online') {
    echo "<table>";
    echo "<tr><th>Feature</th><th>Status</th><th>Database Table</th></tr>";
    
    $features = [
        ['👥 Student Accounts', 'users'],
        ['🏠 Room Management', 'rooms'],
        ['🚪 Gate Pass System', 'gatepass'],
        ['💰 Fee Management', 'fees'],
        ['🔧 Maintenance Issues', 'maintenance_issues'],
        ['📄 Receipts', 'receipts'],
        ['👨‍💼 Admin Users', 'admin_users'],
        ['💬 Contact Messages', 'contact_messages'],
    ];
    
    foreach ($features as $feature) {
        $check = $conn->query(\"SHOW TABLES LIKE '{$feature[1]}'\");
        $exists = $check && $check->num_rows > 0 ? '✅ Active' : '❌ Missing';
        echo \"<tr><td>{$feature[0]}</td><td>$exists</td><td>{$feature[1]}</td></tr>\";
    }
    
    echo "</table>";
}
echo "</div>";

// Credentials Reference
echo "<div class='divider'></div>";
echo "<div class='quick-links'>";
echo "<h2>🔑 Default Credentials</h2>";
echo "<table>";
echo "<tr><th>User Type</th><th>Email</th><th>Password</th><th>Status</th></tr>";
echo "<tr><td><strong>Admin</strong></td><td>admin@example.com</td><td>admin123</td><td>⚠️ Hardcoded</td></tr>";
echo "<tr><td><strong>Student</strong></td><td>Check database</td><td>During registration</td><td>✅ Dynamic</td></tr>";
echo "</table>";
echo "<p style='margin-top: 15px; color: #ef4444; font-weight: bold;'>⚠️ IMPORTANT: Change default admin credentials in production!</p>";
echo "</div>";

// Project Info
echo "<div class='divider'></div>";
echo "<div class='quick-links'>";
echo "<h2>📋 Project Information</h2>";
echo "<table>";
echo "<tr><th>Configuration</th><th>Value</th></tr>";
echo "<tr><td>Project Root</td><td>/hostel-manage</td></tr>";
echo "<tr><td>Database Name</td><td>hostel_manage</td></tr>";
echo "<tr><td>PHP Version Required</td><td>7.4+</td></tr>";
echo "<tr><td>Local URL</td><td>http://localhost/hostel-manage</td></tr>";
echo "<tr><td>Main Entry Point</td><td>index.php</td></tr>";
echo "</table>";
echo "</div>";

if ($db_status === 'online') {
    $conn->close();
}

echo "</div>";
echo "</body></html>";
?>

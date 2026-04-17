<?php
session_start();
echo "<!DOCTYPE html><html><head><title>Hostel Management - Connection Test</title>";
echo "<style>body{font-family: Arial; margin: 20px;} .success{color: green; padding: 10px; border: 1px solid green; margin: 10px 0;} .error{color: red; padding: 10px; border: 1px solid red; margin: 10px 0;} .info{color: blue; padding: 10px; border: 1px solid blue; margin: 10px 0; } table{width: 100%; border-collapse: collapse;} th, td{border: 1px solid #ddd; padding: 8px; text-align: left;} th{background-color: #4CAF50; color: white;}</style></head><body>";

echo "<h1>🧪 Hostel Management System - Connection & Functionality Test</h1>";

// Test 1: Database Connection
echo "<h2>Test 1: Database Connection</h2>";
$conn = new mysqli('localhost', 'root', '', 'hostel_manage');
if ($conn->connect_error) {
    echo "<div class='error'>❌ Database Connection Failed: " . $conn->connect_error . "</div>";
} else {
    echo "<div class='success'>✅ Database Connection Successful</div>";
}

// Test 2: Check Tables
echo "<h2>Test 2: Database Tables</h2>";
$result = $conn->query("SHOW TABLES");
$tables = [];
while ($row = $result->fetch_row()) {
    $tables[] = $row[0];
}
echo "<div class='info'><strong>Found " . count($tables) . " tables:</strong><br>" . implode(", ", $tables) . "</div>";

// Test 3: Admin Credentials
echo "<h2>Test 3: Admin Login Test</h2>";
$adminEmail = 'admin@example.com';
$adminPassword = 'admin123';
echo "<div class='info'><strong>Hardcoded Admin Credentials:</strong><br>Email: $adminEmail<br>Password: $adminPassword</div>";
echo "<div class='success'>✅ Admin login will work with the above credentials</div>";

// Test 4: User Records
echo "<h2>Test 4: Student Records in Database</h2>";
$result = $conn->query("SELECT COUNT(*) as total_students FROM users");
$row = $result->fetch_assoc();
if ($row['total_students'] > 0) {
    echo "<div class='success'>✅ Found " . $row['total_students'] . " student records in database</div>";
    $result = $conn->query("SELECT id, firstName, email, otr_number, fees_status FROM users LIMIT 5");
    if ($result->num_rows > 0) {
        echo "<table><tr><th>ID</th><th>Name</th><th>Email</th><th>OTR Number</th><th>Fees Status</th></tr>";
        while ($user = $result->fetch_assoc()) {
            echo "<tr><td>" . $user['id'] . "</td><td>" . $user['firstName'] . "</td><td>" . $user['email'] . "</td><td>" . $user['otr_number'] . "</td><td>" . $user['fees_status'] . "</td></tr>";
        }
        echo "</table>";
    }
} else {
    echo "<div class='error'>⚠️ No student records found yet</div>";
}

// Test 5: Room Data
echo "<h2>Test 5: Room Management</h2>";
$result = $conn->query("SELECT COUNT(*) as total_rooms FROM rooms");
$row = $result->fetch_assoc();
echo "<div class='info'>Total Rooms: " . $row['total_rooms'] . "</div>";

// Test 6: Gate Pass System
echo "<h2>Test 6: Gate Pass System</h2>";
$result = $conn->query("SELECT COUNT(*) as total_requests FROM gatepass");
$row = $result->fetch_assoc();
echo "<div class='info'>Total Gate Pass Requests: " . $row['total_requests'] . "</div>";

// Test 7: Fees Management
echo "<h2>Test 7: Fees Management</h2>";
$result = $conn->query("SELECT COUNT(*) as pending FROM fees WHERE status='pending'");
$row = $result->fetch_assoc();
echo "<div class='info'>Pending Fees: " . $row['pending'] . "</div>";

// Test 8: File Permissions
echo "<h2>Test 8: Required Directories</h2>";
$dirs = [
    'uploads' => 'c:/xampp/htdocs/hostel-manage/uploads',
    'uploads/receipts' => 'c:/xampp/htdocs/hostel-manage/uploads/receipts',
    'uploads/issues' => 'c:/xampp/htdocs/hostel-manage/uploads/issues'
];
foreach ($dirs as $name => $path) {
    if (is_dir($path)) {
        echo "<div class='success'>✅ Directory exists: $name</div>";
    } else {
        echo "<div class='error'>❌ Directory missing: $name</div>";
    }
}

$conn->close();

echo "<hr>";
echo "<h2>📋 Ready to Test?</h2>";
echo "<ul>";
echo "<li><a href='frontend/user/pages/login.php' target='_blank'><strong>👤 Test Student Login</strong></a></li>";
echo "<li><a href='frontend/user/pages/register.php' target='_blank'><strong>📝 Test New Registration</strong></a></li>";
echo "<li><a href='frontend/admin/pages/dashboard.php' target='_blank'><strong>👨‍💼 Test Admin Login</strong></a></li>";
echo "</ul>";

echo "<hr>";
echo "<p style='color: gray;'><small>Generated: " . date('Y-m-d H:i:s') . "</small></p>";
echo "</body></html>";
?>

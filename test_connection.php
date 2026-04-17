<?php
// Test database connection
require_once('backend/dbconnection.php');

echo "<h2>Database Connection Test</h2>";

if ($conn->connect_error) {
    die('<span style="color:red;"><b>Connection Failed:</b> ' . $conn->connect_error . '</span>');
} else {
    echo '<span style="color:green;"><b>✓ Connection Successful!</b></span><br><br>';
}

// Test tables exist
$tables_to_check = ['users', 'rooms', 'gatepass', 'fees', 'maintenance_issues', 'admin_users'];

echo "<h3>Database Tables Status:</h3>";
foreach ($tables_to_check as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo '<span style="color:green;">✓ Table ' . $table . ' exists</span><br>';
        
        // Get row count
        $count_result = $conn->query("SELECT COUNT(*) as count FROM $table");
        $count_row = $count_result->fetch_assoc();
        echo '&nbsp;&nbsp;&nbsp;&nbsp;→ Records: ' . $count_row['count'] . '<br>';
    } else {
        echo '<span style="color:red;">✗ Table ' . $table . ' NOT found</span><br>';
    }
}

// Test admin login
echo "<h3>Test Admin Login:</h3>";
$admin_email = 'admin@example.com';
$admin_password = 'admin123';

$sql = "SELECT * FROM admin_users WHERE email = '$admin_email'";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo '<span style="color:green;">✓ Admin user exists</span><br>';
    $admin = $result->fetch_assoc();
    echo 'Email: ' . $admin['email'] . '<br>';
    
    // Check password (if stored as plain text or hash)
    if ($admin['password'] == $admin_password) {
        echo '<span style="color:green;">✓ Password matches (stored as plain text)</span><br>';
    } else if (password_verify($admin_password, $admin['password'])) {
        echo '<span style="color:green;">✓ Password matches (hashed)</span><br>';
    } else {
        echo '<span style="color:orange;">? Password does not match or stored differently</span><br>';
    }
} else {
    echo '<span style="color:red;">✗ Admin user not found</span><br>';
    echo 'Creating test admin user...<br>';
    
    // Try to create admin user (without 'name' column - doesn't exist in admin_users table)
    $insert_sql = "INSERT INTO admin_users (email, password, created_at) VALUES ('admin@example.com', 'admin123', NOW())";
    if ($conn->query($insert_sql)) {
        echo '<span style="color:green;">✓ Test admin user created</span><br>';
    } else {
        echo '<span style="color:red;">Error: ' . $conn->error . '</span><br>';
    }
}

// Check user table
echo "<h3>Sample Users:</h3>";
$user_result = $conn->query("SELECT id, otr_number, firstName, email, fees_status FROM users LIMIT 5");
if ($user_result && $user_result->num_rows > 0) {
    echo '<table border="1" cellpadding="5">';
    echo '<tr><th>ID</th><th>OTR Number</th><th>Name</th><th>Email</th><th>Fees Status</th></tr>';
    while($row = $user_result->fetch_assoc()) {
        echo '<tr><td>' . $row['id'] . '</td><td>' . $row['otr_number'] . '</td><td>' . $row['firstName'] . '</td><td>' . $row['email'] . '</td><td>' . $row['fees_status'] . '</td></tr>';
    }
    echo '</table>';
} else {
    echo 'No users found in database<br>';
}

$conn->close();
?>

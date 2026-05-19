<?php
require_once('backend/dbconnection.php');

echo "<h2>Database Connection Test</h2>";

if ($conn->connect_error) {
    die('<span style="color:red;"><b>Connection Failed:</b> ' . $conn->connect_error . '</span>');
}

echo '<span style="color:green;"><b>Connection Successful!</b></span><br><br>';

$tables_to_check = [
    'users',
    'rooms',
    'gatepass',
    'gatepass_logs',
    'fees',
    'maintenance_issues',
    'receipts',
    'upi_config',
    'contact_messages',
    'icard_requests',
];

echo "<h3>Database Tables Status:</h3>";
foreach ($tables_to_check as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo '<span style="color:green;">Table ' . htmlspecialchars($table) . ' exists</span><br>';

        $count_result = $conn->query("SELECT COUNT(*) as count FROM `$table`");
        $count_row = $count_result->fetch_assoc();
        echo '&nbsp;&nbsp;&nbsp;&nbsp;Records: ' . $count_row['count'] . '<br>';
    } else {
        echo '<span style="color:red;">Table ' . htmlspecialchars($table) . ' NOT found</span><br>';
    }
}

echo "<h3>Admin Login:</h3>";
echo "Admin login uses fixed credentials in backend/user/login.php: admin@example.com / admin123<br>";

echo "<h3>Sample Users:</h3>";
$user_result = $conn->query("SELECT id, otr_number, firstName, email, fees_status FROM users LIMIT 5");
if ($user_result && $user_result->num_rows > 0) {
    echo '<table border="1" cellpadding="5">';
    echo '<tr><th>ID</th><th>OTR Number</th><th>Name</th><th>Email</th><th>Fees Status</th></tr>';
    while ($row = $user_result->fetch_assoc()) {
        echo '<tr><td>' . htmlspecialchars($row['id']) . '</td><td>' . htmlspecialchars($row['otr_number']) . '</td><td>' . htmlspecialchars($row['firstName']) . '</td><td>' . htmlspecialchars($row['email']) . '</td><td>' . htmlspecialchars($row['fees_status']) . '</td></tr>';
    }
    echo '</table>';
} else {
    echo 'No users found in database<br>';
}

$conn->close();
?>

<?php
session_start();
echo "<!DOCTYPE html><html><head><title>Admin Account Manager</title>";
echo "<style>
body {font-family: Arial; margin: 20px; background-color: #f5f5f5;}
.container {max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1);}
.form-group {margin: 15px 0;}
label {display: block; margin-bottom: 5px; font-weight: bold;}
input[type='text'], input[type='email'], input[type='password'] {width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;}
button {background-color: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px;}
button:hover {background-color: #45a049;}
.success {background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 15px 0; border-radius: 4px; color: #155724;}
.error {background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; margin: 15px 0; border-radius: 4px; color: #721c24;}
.info {background-color: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; margin: 15px 0; border-radius: 4px; color: #0c5460;}
table {width: 100%; border-collapse: collapse; margin-top: 20px;}
th, td {border: 1px solid #ddd; padding: 12px; text-align: left;}
th {background-color: #f0f0f0; font-weight: bold;}
h1 {color: #333;}
h2 {color: #666; margin-top: 30px;}
.btn-delete {background-color: #dc3545; padding: 5px 10px; font-size: 12px;}
.btn-delete:hover {background-color: #c82333;}
.warning {background-color: #fff3cd; border: 1px solid #ffeeba; padding: 15px; margin: 15px 0; border-radius: 4px; color: #856404;}
</style></head><body>";

echo "<div class='container'>";
echo "<h1>👨‍💼 Admin Account Manager</h1>";

$conn = new mysqli('localhost', 'root', '', 'hostel_manage');
if ($conn->connect_error) {
    echo "<div class='error'>❌ Database Connection Failed: " . $conn->connect_error . "</div>";
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] === 'add_admin') {
        $email = $conn->real_escape_string($_POST['email']);
        $password = $_POST['password'];
        $name = $conn->real_escape_string($_POST['name']);
        
        // Validation
        $errors = [];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }
        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters";
        }
        if (empty($name)) {
            $errors[] = "Name is required";
        }
        
        // Check if email already exists
        $checkEmail = $conn->query("SELECT id FROM admin_users WHERE email='$email'");
        if ($checkEmail && $checkEmail->num_rows > 0) {
            $errors[] = "Email already exists in admin accounts";
        }
        
        if (count($errors) > 0) {
            echo "<div class='error'><strong>❌ Validation Errors:</strong><ul>";
            foreach ($errors as $err) {
                echo "<li>$err</li>";
            }
            echo "</ul></div>";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO admin_users (email, password, name, created_at) VALUES ('$email', '$hashed_password', '$name', NOW())";
            
            if ($conn->query($sql) === TRUE) {
                echo "<div class='success'>";
                echo "<strong>✅ Admin Account Created Successfully!</strong><br>";
                echo "Email: $email<br>";
                echo "Name: $name<br>";
                echo "<p><strong>⚠️ Important:</strong> Share the password securely with the new admin.</p>";
                echo "</div>";
            } else {
                echo "<div class='error'>❌ Error creating account: " . $conn->error . "</div>";
            }
        }
    }
    
    elseif ($_POST['action'] === 'delete_admin') {
        $admin_id = intval($_POST['admin_id']);
        
        // Prevent deleting the first admin
        if ($admin_id <= 1) {
            echo "<div class='warning'>⚠️ Cannot delete the default admin account</div>";
        } else {
            $sql = "DELETE FROM admin_users WHERE id=$admin_id";
            if ($conn->query($sql) === TRUE) {
                echo "<div class='success'>✅ Admin account deleted successfully</div>";
            } else {
                echo "<div class='error'>❌ Error deleting account: " . $conn->error . "</div>";
            }
        }
    }
}

// Display form
echo "<h2>➕ Add New Admin Account</h2>";
echo "<form method='POST'>";
echo "<input type='hidden' name='action' value='add_admin'>";

echo "<div class='form-group'>";
echo "<label for='name'>Full Name:</label>";
echo "<input type='text' id='name' name='name' required>";
echo "</div>";

echo "<div class='form-group'>";
echo "<label for='email'>Email:</label>";
echo "<input type='email' id='email' name='email' required>";
echo "</div>";

echo "<div class='form-group'>";
echo "<label for='password'>Password:</label>";
echo "<input type='password' id='password' name='password' placeholder='Min 8 characters' required>";
echo "</div>";

echo "<button type='submit'>Create Admin Account</button>";
echo "</form>";

// Display existing admins
echo "<h2>📋 Existing Admin Accounts</h2>";
$result = $conn->query("SELECT id, email, name, created_at FROM admin_users");
if ($result && $result->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Created</th><th>Action</th></tr>";
    while ($row = $result->fetch_assoc()) {
        $created = date('M d, Y', strtotime($row['created_at']));
        $deleteBtn = ($row['id'] > 1) ? "<form method='POST' style='display:inline;'><input type='hidden' name='action' value='delete_admin'><input type='hidden' name='admin_id' value='" . $row['id'] . "'><button type='submit' class='btn-delete' onclick=\"return confirm('Delete this admin?')\">Delete</button></form>" : "<span style='color: #999;'>Protected</span>";
        
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>$created</td>";
        echo "<td>$deleteBtn</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No admin accounts found</p>";
}

echo "<div class='info'>";
echo "<strong>ℹ️ Information:</strong><br>";
echo "• Default Admin: admin@example.com / admin123 (hardcoded in login.php)<br>";
echo "• New admins added here will be checked against the admin_users table<br>";
echo "• Consider disabling the hardcoded admin credentials in production<br>";
echo "</div>";

$conn->close();
echo "</div>";
echo "</body></html>";
?>

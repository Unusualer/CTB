<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database config 
require_once 'includes/config.php';

echo "<h1>Login Debugging Tool</h1>";

// Test database connection
echo "<h2>Testing Database Connection</h2>";
try {
    $conn->query('SELECT 1');
    echo "<p style='color:green'>Database connection: SUCCESS</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>Database connection: FAILED - " . $e->getMessage() . "</p>";
    die("Fix database connection issues before continuing");
}

// Check if users table exists
echo "<h2>Checking Users Table</h2>";
try {
    $tables = $conn->query("SHOW TABLES LIKE 'users'")->fetchAll(PDO::FETCH_COLUMN);
    if (count($tables) > 0) {
        echo "<p style='color:green'>Users table exists</p>";
    } else {
        echo "<p style='color:red'>Users table does not exist</p>";
        die("Create the users table before continuing");
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Error checking tables: " . $e->getMessage() . "</p>";
}

// Display all existing users
echo "<h2>Existing User Accounts</h2>";
try {
    $stmt = $conn->prepare("SELECT id, email, role, status, name, phone FROM users");
    $stmt->execute();
    $allUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($allUsers) > 0) {
        echo "<p style='color:green'>Found " . count($allUsers) . " user accounts:</p>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Email</th><th>Name</th><th>Phone</th><th>Role</th><th>Status</th><th>Actions</th></tr>";
        foreach ($allUsers as $user) {
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td>{$user['name']}</td>";
            echo "<td>{$user['phone']}</td>";
            echo "<td>{$user['role']}</td>";
            echo "<td>{$user['status']}</td>";
            echo "<td><a href='#' onclick=\"testLoginForm('{$user['email']}'); return false;\">Test Login</a></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color:red'>No user accounts found in database.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Error checking users: " . $e->getMessage() . "</p>";
}

// Test login with sample accounts
echo "<h2>Testing Login Functionality</h2>";
echo "<div id='test-login-form'>";
echo "<form method='post' action='#'>";
echo "<p><strong>Test a login:</strong></p>";
echo "<input type='text' name='test_email' id='test_email' placeholder='Email' style='padding: 5px; margin-right: 10px;'>";
echo "<input type='password' name='test_password' id='test_password' placeholder='Password' style='padding: 5px; margin-right: 10px;'>";
echo "<button type='submit' name='test_login' style='padding: 5px 10px;'>Test Login</button>";
echo "</form>";
echo "</div>";
echo "<div id='test-result'></div>";

// Process login test
if (isset($_POST['test_login']) && !empty($_POST['test_email']) && !empty($_POST['test_password'])) {
    $testEmail = $_POST['test_email'];
    $testPassword = $_POST['test_password'];
    
    echo "<h3>Testing login with {$testEmail}</h3>";
    
    try {
        // Find user
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$testEmail]);
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<p>User found with ID: {$user['id']}, Role: {$user['role']}, Status: {$user['status']}</p>";
            
            // Check status
            if ($user['status'] !== 'active') {
                echo "<p style='color:red'>Account is not active</p>";
            } else {
                // Verify password
                $result = password_verify($testPassword, $user['password']);
                if ($result) {
                    echo "<p style='color:green'>Password verification: SUCCESS</p>";
                    echo "<p style='color:green'>This login should work! If you're still having issues, check for session problems.</p>";
                    
                    // Check for role session variable
                    echo "<p>When logged in, you should be redirected to: ";
                    switch ($user['role']) {
                        case 'admin':
                            echo "admin/dashboard.php";
                            break;
                        case 'manager':
                            echo "manager/dashboard.php";
                            break;
                        case 'resident':
                            echo "resident/dashboard.php";
                            break;
                        default:
                            echo "unknown destination (invalid role)";
                    }
                    echo "</p>";
                } else {
                    echo "<p style='color:red'>Password verification: FAILED</p>";
                    echo "<p>Password hash in database: " . substr($user['password'], 0, 20) . "...</p>";
                    
                    // Reset user password
                    echo "<form method='post' action='#'>";
                    echo "<input type='hidden' name='reset_user_id' value='{$user['id']}'>";
                    echo "<input type='password' name='new_password' placeholder='New password' style='padding: 5px; margin-right: 10px;'>";
                    echo "<button type='submit' name='reset_password' style='padding: 5px 10px;'>Reset Password</button>";
                    echo "</form>";
                }
            }
        } else {
            echo "<p style='color:red'>User not found with email: {$testEmail}</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red'>Error during login test: " . $e->getMessage() . "</p>";
    }
}

// Process password reset
if (isset($_POST['reset_password']) && !empty($_POST['reset_user_id']) && !empty($_POST['new_password'])) {
    $userId = $_POST['reset_user_id'];
    $newPassword = $_POST['new_password'];
    $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    
    try {
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$passwordHash, $userId]);
        
        echo "<p style='color:green'>Password has been reset successfully! New password: {$newPassword}</p>";
        
        // Get user details
        $stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<p>You can now log in with:</p>";
        echo "<p>Email: {$user['email']}<br>Password: {$newPassword}</p>";
    } catch (Exception $e) {
        echo "<p style='color:red'>Error resetting password: " . $e->getMessage() . "</p>";
    }
}

echo "<h2>Create New User</h2>";
echo "<form method='post' action='#'>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label for='new_email' style='display: inline-block; width: 100px;'>Email:</label>";
echo "<input type='email' name='new_email' id='new_email' required style='padding: 5px; width: 250px;'>";
echo "</div>";

echo "<div style='margin-bottom: 10px;'>";
echo "<label for='new_password' style='display: inline-block; width: 100px;'>Password:</label>";
echo "<input type='password' name='new_password' id='new_password' required style='padding: 5px; width: 250px;'>";
echo "</div>";

echo "<div style='margin-bottom: 10px;'>";
echo "<label for='name' style='display: inline-block; width: 100px;'>Full Name:</label>";
echo "<input type='text' name='name' id='name' required style='padding: 5px; width: 250px;'>";
echo "</div>";

echo "<div style='margin-bottom: 10px;'>";
echo "<label for='phone' style='display: inline-block; width: 100px;'>Phone:</label>";
echo "<input type='text' name='phone' id='phone' style='padding: 5px; width: 250px;'>";
echo "</div>";

echo "<div style='margin-bottom: 10px;'>";
echo "<label for='role' style='display: inline-block; width: 100px;'>Role:</label>";
echo "<select name='role' id='role' required style='padding: 5px; width: 250px;'>";
echo "<option value='admin'>Admin</option>";
echo "<option value='manager'>Manager</option>";
echo "<option value='resident'>Resident</option>";
echo "</select>";
echo "</div>";

echo "<button type='submit' name='create_user' style='padding: 5px 10px;'>Create User</button>";
echo "</form>";

// Process user creation
if (isset($_POST['create_user']) && !empty($_POST['new_email']) && !empty($_POST['new_password'])) {
    $newEmail = $_POST['new_email'];
    $newPassword = $_POST['new_password'];
    $name = $_POST['name'];
    $phone = $_POST['phone'] ?: null;
    $role = $_POST['role'];
    $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    
    try {
        // Check if user already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$newEmail]);
        
        if ($stmt->rowCount() > 0) {
            echo "<p style='color:red'>User with email {$newEmail} already exists!</p>";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (email, password, name, phone, role, status, created_at) VALUES (?, ?, ?, ?, ?, 'active', NOW())");
            $stmt->execute([$newEmail, $passwordHash, $name, $phone, $role]);
            
            echo "<p style='color:green'>User created successfully!</p>";
            echo "<p>Email: {$newEmail}<br>Password: {$newPassword}<br>Role: {$role}</p>";
            echo "<p>Refresh the page to see the new user in the list above.</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red'>Error creating user: " . $e->getMessage() . "</p>";
    }
}

echo "<h2>Login Issue Fixes</h2>";
echo "<p>If you're having login issues, you can try the following:</p>";
echo "<ol>";
echo "<li>Make sure the MySQL server is running</li>";
echo "<li>Check database credentials in includes/config.php</li>";
echo "<li>Use the exact email and password from a user in the table above</li>";
echo "<li>Check that the account status is 'active'</li>";
echo "<li>Reset the password using the 'Test Login' feature if needed</li>";
echo "<li>Check the redirect path in auth.php is correct (admin/dashboard.php, manager/dashboard.php, or resident/dashboard.php)</li>";
echo "<li>Check session variable names in auth.php match what's expected in the dashboard pages ('role' vs 'user_role')</li>";
echo "</ol>";

echo "<p><a href='login.php' style='display: inline-block; padding: 10px 15px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px;'>Return to Login Page</a></p>";

// JavaScript for form handling
echo "<script>
function testLoginForm(email) {
    document.getElementById('test_email').value = email;
    document.getElementById('test_password').focus();
}
</script>";
?> 
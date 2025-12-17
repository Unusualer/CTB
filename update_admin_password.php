<?php
// Start session and include translation function
session_start();
if (!function_exists('__')) {
    require_once 'includes/translations.php';
}

// Database connection parameters
$host = 'localhost';
$user = 'ctb';
$pass = 'Rm9c7@Bn.YM4Z!rE';
$db = 'ctb_db';

// Initialize variables
$message = '';
$success = false;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the submitted password
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';
    
    // Simple validation
    if (empty($password)) {
        $message = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $message = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm_password) {
        $message = 'Passwords do not match.';
    } else {
        try {
            // Connect to the database
            $conn = new mysqli($host, $user, $pass, $db);
            
            // Check connection
            if ($conn->connect_error) {
                throw new Exception("Connection failed: " . $conn->connect_error);
            }
            
            // Hash the password using PHP's password_hash function with BCRYPT
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            // Update the admin password
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = 'admin@ctb.com'");
            $stmt->bind_param("s", $hashed_password);
            
            if ($stmt->execute()) {
                $success = true;
                $message = __("Admin password has been updated successfully.");
            } else {
                $message = __("Failed to update password:") . ' ' . $conn->error;
            }
            
            // Close the statement and connection
            $stmt->close();
            $conn->close();
            
        } catch (Exception $e) {
            $message = 'Error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __("Update Admin Password"); ?> - <?php echo __("Complexe Tanger Boulevard"); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        form {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="password"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #45a049;
        }
        .message {
            padding: 10px;
            margin-top: 20px;
            border-radius: 4px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .note {
            background: #fff3cd;
            color: #856404;
            padding: 10px;
            margin-top: 20px;
            border-radius: 4px;
            border: 1px solid #ffeeba;
        }
    </style>
</head>
<body>
    <h1><?php echo __("Update Admin Password"); ?></h1>
    
    <?php if ($message): ?>
        <div class="message <?php echo $success ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="password"><?php echo __("New Password"); ?>:</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <div class="form-group">
            <label for="confirm_password"><?php echo __("Confirm Password"); ?>:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        
        <button type="submit"><?php echo __("Update Password"); ?></button>
    </form>
    
    <div class="note">
        <strong><?php echo __("Note"); ?>:</strong> <?php echo __("This page is for debugging/testing purposes only."); ?> 
        <?php echo __("It updates the password for the user with email"); ?> <strong>admin@ctb.com</strong>. 
        <?php echo __("In a production environment, this should be protected by authentication and stronger security measures."); ?>
    </div>
</body>
</html> 
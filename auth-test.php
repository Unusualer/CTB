<?php
// Start session
session_start();

// Include database connection file
require_once 'includes/config.php';

// Test function to create a user
function createTestUser($conn, $email, $password, $name, $role = 'admin', $status = 'active') {
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Output password info for debugging
    echo "Original password: $password<br>";
    echo "Hashed password: $hashed_password<br>";
    echo "Password verification test: " . (password_verify($password, $hashed_password) ? 'Success' : 'Failed') . "<br><br>";
    
    // Check if user already exists
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
    $checkStmt->bindParam(':email', $email);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() > 0) {
        $user = $checkStmt->fetch(PDO::FETCH_ASSOC);
        echo "User already exists with ID: " . $user['id'] . "<br>";
        
        // Update user
        $updateStmt = $conn->prepare("
            UPDATE users 
            SET password = :password, name = :name, role = :role, status = :status 
            WHERE email = :email
        ");
        
        $updateStmt->bindParam(':password', $hashed_password);
        $updateStmt->bindParam(':name', $name);
        $updateStmt->bindParam(':role', $role);
        $updateStmt->bindParam(':status', $status);
        $updateStmt->bindParam(':email', $email);
        
        if ($updateStmt->execute()) {
            echo "User updated successfully!<br>";
            return $user['id'];
        } else {
            echo "Error updating user.<br>";
            return false;
        }
    } else {
        // Create new user
        $insertStmt = $conn->prepare("
            INSERT INTO users (email, password, name, role, status, created_at) 
            VALUES (:email, :password, :name, :role, :status, NOW())
        ");
        
        $insertStmt->bindParam(':email', $email);
        $insertStmt->bindParam(':password', $hashed_password);
        $insertStmt->bindParam(':name', $name);
        $insertStmt->bindParam(':role', $role);
        $insertStmt->bindParam(':status', $status);
        
        if ($insertStmt->execute()) {
            $userId = $conn->lastInsertId();
            echo "User created successfully with ID: $userId<br>";
            return $userId;
        } else {
            echo "Error creating user.<br>";
            return false;
        }
    }
}

// Create test users
try {
    // Admin user
    createTestUser($conn, 'admin@ctb.com', 'Admin@1234', 'System Administrator', 'admin');
    
    // Manager user
    createTestUser($conn, 'manager@ctb.com', 'Manager@1234', 'Property Manager', 'manager');
    
    // Resident user
    createTestUser($conn, 'resident@ctb.com', 'Resident@1234', 'John Resident', 'resident');
    
    echo "<br>All test users created or updated successfully!";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}

// Test login verification
function testLoginVerification($conn, $email, $password) {
    echo "<hr><h3>Testing login for: $email</h3>";
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "User found with ID: " . $user['id'] . "<br>";
        echo "Stored hash: " . $user['password'] . "<br>";
        
        if (password_verify($password, $user['password'])) {
            echo "Password verification: <span style='color:green;font-weight:bold;'>SUCCESS</span><br>";
        } else {
            echo "Password verification: <span style='color:red;font-weight:bold;'>FAILED</span><br>";
            
            // Debug info
            echo "Attempted password: $password<br>";
            echo "Creating new hash with same password: " . password_hash($password, PASSWORD_DEFAULT) . "<br>";
        }
    } else {
        echo "User not found<br>";
    }
}

// Test all users
echo "<h2>Login Verification Tests</h2>";
testLoginVerification($conn, 'admin@ctb.com', 'Admin@1234');
testLoginVerification($conn, 'manager@ctb.com', 'Manager@1234');
testLoginVerification($conn, 'resident@ctb.com', 'Resident@1234');
?> 
<?php
// Include the database configuration
require_once 'includes/config.php';

echo "<h1>Setting up Users Table</h1>";

try {
    // Drop existing table if it exists
    $conn->exec("DROP TABLE IF EXISTS users");
    echo "Dropped existing users table.<br>";

    // Create users table
    $sql = "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'manager', 'resident') NOT NULL DEFAULT 'resident',
        phone VARCHAR(20),
        address TEXT,
        status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
        profile_image VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $conn->exec($sql);
    echo "Users table created successfully.<br>";

    // Insert test users with hashed passwords
    $admin_password = password_hash("password123", PASSWORD_DEFAULT);
    $manager_password = password_hash("Manager@1234", PASSWORD_DEFAULT);
    $resident_password = password_hash("Resident@1234", PASSWORD_DEFAULT);

    // Insert admin
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, phone, address, status) 
                          VALUES (:name, :email, :password, :role, :phone, :address, :status)");
    
    $stmt->execute([
        'name' => 'Admin User',
        'email' => 'admin@ctb.com',
        'password' => $admin_password,
        'role' => 'admin',
        'phone' => '123-456-7890',
        'address' => '123 Admin Street, City',
        'status' => 'active'
    ]);
    
    // Insert manager
    $stmt->execute([
        'name' => 'Manager User',
        'email' => 'manager@ctb.com',
        'password' => $manager_password,
        'role' => 'manager',
        'phone' => '123-456-7891',
        'address' => '456 Manager Avenue, City',
        'status' => 'active'
    ]);
    
    // Insert resident
    $stmt->execute([
        'name' => 'Resident User',
        'email' => 'resident@ctb.com',
        'password' => $resident_password,
        'role' => 'resident',
        'phone' => '123-456-7892',
        'address' => '789 Resident Road, City',
        'status' => 'active'
    ]);

    echo "Test users created successfully.<br>";
    
    // Show generated hashes
    echo "<h2>Generated Password Hashes:</h2>";
    echo "Admin password hash: $admin_password<br>";
    echo "Manager password hash: $manager_password<br>";
    echo "Resident password hash: $resident_password<br>";
    
    // Verify passwords work
    echo "<h2>Password Verification Tests:</h2>";
    echo "Admin password verification: " . (password_verify("password123", $admin_password) ? "Success" : "Failed") . "<br>";
    echo "Manager password verification: " . (password_verify("Manager@1234", $manager_password) ? "Success" : "Failed") . "<br>";
    echo "Resident password verification: " . (password_verify("Resident@1234", $resident_password) ? "Success" : "Failed") . "<br>";
    
    echo "<hr>";
    echo "<h2>Success! Users table has been set up with test users.</h2>";
    echo "<p>You can log in with the following credentials:</p>";
    echo "<ul>";
    echo "<li>Admin: admin@ctb.com / password123</li>";
    echo "<li>Manager: manager@ctb.com / Manager@1234</li>";
    echo "<li>Resident: resident@ctb.com / Resident@1234</li>";
    echo "</ul>";
    
} catch(PDOException $e) {
    echo "<div style='color: red;'>";
    echo "Error: " . $e->getMessage();
    echo "</div>";
}

// Close connection
$conn = null;
?> 
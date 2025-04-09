<?php
/**
 * Database Setup Script
 * Creates the database and tables needed for the CTB Property Management System
 */

// Set time limit and error reporting
set_time_limit(300);
ini_set('max_execution_time', 0);
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'ctb_property';

// Output function for progress messages
function outputMessage($message, $success = true) {
    $style = $success ? 'color:green;' : 'color:red;font-weight:bold;';
    echo "<div style='$style margin:5px 0;'>" . $message . "</div>";
    // Flush the output buffer to show progress in real-time
    if (ob_get_level() > 0) {
        ob_flush();
        flush();
    }
}

// Header
echo "<!DOCTYPE html>
<html>
<head>
    <title>CTB Property Management - Database Setup</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; line-height: 1.6; }
        h1 { color: #3f37c9; }
        h2 { color: #4361ee; margin-top: 20px; }
        hr { border: 0; height: 1px; background: #ddd; margin: 20px 0; }
        .success { color: green; }
        .error { color: red; font-weight: bold; }
        code { background: #f5f5f5; padding: 2px 5px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>CTB Property Management System - Database Setup</h1>
";

try {
    // Create database connection without selecting a database
    outputMessage("Connecting to MySQL server...");
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    outputMessage("Connected successfully!");
    
    // Drop database if it exists
    outputMessage("Checking for existing database...");
    $conn->exec("DROP DATABASE IF EXISTS `$dbname`");
    outputMessage("Dropped existing database (if it existed).");
    
    // Create new database
    outputMessage("Creating new database '$dbname'...");
    $conn->exec("CREATE DATABASE `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    outputMessage("Database created successfully!");
    
    // Select the database
    outputMessage("Selecting the database...");
    $conn->exec("USE `$dbname`");
    
    // Create users table
    outputMessage("<h2>Creating Users Table</h2>");
    $usersTable = "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        role ENUM('admin', 'manager', 'resident') NOT NULL DEFAULT 'resident',
        phone VARCHAR(20),
        address TEXT,
        profile_image VARCHAR(255) DEFAULT 'default.png',
        status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->exec($usersTable);
    outputMessage("Users table created successfully!");
    
    // Create properties table
    outputMessage("<h2>Creating Properties Table</h2>");
    $propertiesTable = "CREATE TABLE properties (
        id INT AUTO_INCREMENT PRIMARY KEY,
        property_name VARCHAR(100) NOT NULL,
        property_type ENUM('apartment', 'house', 'condo', 'commercial') NOT NULL,
        address TEXT NOT NULL,
        city VARCHAR(50) NOT NULL,
        state VARCHAR(50) NOT NULL,
        zip_code VARCHAR(20) NOT NULL,
        description TEXT,
        manager_id INT,
        units INT DEFAULT 0,
        status ENUM('active', 'inactive', 'maintenance') NOT NULL DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (manager_id) REFERENCES users(id) ON DELETE SET NULL
    )";
    $conn->exec($propertiesTable);
    outputMessage("Properties table created successfully!");
    
    // Create tickets table
    outputMessage("<h2>Creating Tickets Table</h2>");
    $ticketsTable = "CREATE TABLE tickets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        property_id INT,
        title VARCHAR(100) NOT NULL,
        description TEXT NOT NULL,
        priority ENUM('low', 'medium', 'high', 'urgent') NOT NULL DEFAULT 'medium',
        status ENUM('open', 'in_progress', 'closed') NOT NULL DEFAULT 'open',
        assigned_to INT,
        category ENUM('maintenance', 'electrical', 'plumbing', 'security', 'other') NOT NULL DEFAULT 'maintenance',
        attachments TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL,
        FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
    )";
    $conn->exec($ticketsTable);
    outputMessage("Tickets table created successfully!");
    
    // Create payments table
    outputMessage("<h2>Creating Payments Table</h2>");
    $paymentsTable = "CREATE TABLE payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        property_id INT,
        amount DECIMAL(10, 2) NOT NULL,
        payment_date DATE NOT NULL,
        payment_method ENUM('credit_card', 'debit_card', 'bank_transfer', 'cash', 'check', 'paypal') NOT NULL,
        description VARCHAR(255),
        status ENUM('pending', 'completed', 'failed', 'refunded') NOT NULL DEFAULT 'completed',
        reference_number VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL
    )";
    $conn->exec($paymentsTable);
    outputMessage("Payments table created successfully!");
    
    // Create activity log table
    outputMessage("<h2>Creating Activity Log Table</h2>");
    $activityLogTable = "CREATE TABLE activity_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        action VARCHAR(50) NOT NULL,
        entity_type VARCHAR(50) NOT NULL,
        entity_id INT NOT NULL,
        details TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $conn->exec($activityLogTable);
    outputMessage("Activity log table created successfully!");
    
    // Insert sample data - Admin user
    outputMessage("<h2>Creating Sample Data</h2>");
    outputMessage("Creating admin user...");
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $adminInsert = "INSERT INTO users (username, password, email, first_name, last_name, role, phone) 
                   VALUES ('admin', '$adminPassword', 'admin@ctb.com', 'Admin', 'User', 'admin', '555-123-4567')";
    $conn->exec($adminInsert);
    $adminId = $conn->lastInsertId();
    
    // Insert sample manager
    outputMessage("Creating sample manager...");
    $managerPassword = password_hash('manager123', PASSWORD_DEFAULT);
    $managerInsert = "INSERT INTO users (username, password, email, first_name, last_name, role, phone) 
                     VALUES ('manager', '$managerPassword', 'manager@ctb.com', 'Property', 'Manager', 'manager', '555-234-5678')";
    $conn->exec($managerInsert);
    $managerId = $conn->lastInsertId();
    
    // Insert sample residents
    outputMessage("Creating sample residents...");
    $resident1Password = password_hash('resident123', PASSWORD_DEFAULT);
    $resident1Insert = "INSERT INTO users (username, password, email, first_name, last_name, role, phone, address) 
                       VALUES ('resident1', '$resident1Password', 'resident1@example.com', 'John', 'Doe', 'resident', '555-345-6789', '123 Main St, Apt 101')";
    $conn->exec($resident1Insert);
    $resident1Id = $conn->lastInsertId();
    
    $resident2Password = password_hash('resident123', PASSWORD_DEFAULT);
    $resident2Insert = "INSERT INTO users (username, password, email, first_name, last_name, role, phone, address) 
                       VALUES ('resident2', '$resident2Password', 'resident2@example.com', 'Jane', 'Smith', 'resident', '555-456-7890', '456 Oak Ave, Unit 202')";
    $conn->exec($resident2Insert);
    $resident2Id = $conn->lastInsertId();
    
    // Insert sample properties
    outputMessage("Creating sample properties...");
    $property1Insert = "INSERT INTO properties (property_name, property_type, address, city, state, zip_code, description, manager_id, units) 
                       VALUES ('Sunset Apartments', 'apartment', '123 Sunset Blvd', 'Los Angeles', 'CA', '90028', 'Luxury apartment complex with pool and gym', $managerId, 24)";
    $conn->exec($property1Insert);
    $property1Id = $conn->lastInsertId();
    
    $property2Insert = "INSERT INTO properties (property_name, property_type, address, city, state, zip_code, description, manager_id, units) 
                       VALUES ('Oakwood Homes', 'house', '456 Oak Street', 'San Francisco', 'CA', '94107', 'Single family homes in quiet neighborhood', $managerId, 12)";
    $conn->exec($property2Insert);
    $property2Id = $conn->lastInsertId();
    
    // Insert sample tickets
    outputMessage("Creating sample maintenance tickets...");
    $ticket1Insert = "INSERT INTO tickets (user_id, property_id, title, description, priority, status, assigned_to, category) 
                     VALUES ($resident1Id, $property1Id, 'Leaking Faucet', 'The kitchen faucet is leaking and needs repair', 'medium', 'open', $managerId, 'plumbing')";
    $conn->exec($ticket1Insert);
    
    $ticket2Insert = "INSERT INTO tickets (user_id, property_id, title, description, priority, status, assigned_to, category) 
                     VALUES ($resident2Id, $property2Id, 'AC Not Working', 'Air conditioning unit is not cooling properly', 'high', 'in_progress', $managerId, 'maintenance')";
    $conn->exec($ticket2Insert);
    
    $ticket3Insert = "INSERT INTO tickets (user_id, property_id, title, description, priority, status, assigned_to, category) 
                     VALUES ($resident1Id, $property1Id, 'Light Fixture Broken', 'Bathroom light fixture needs replacement', 'low', 'closed', $managerId, 'electrical')";
    $conn->exec($ticket3Insert);
    
    // Insert sample payments
    outputMessage("Creating sample payment records...");
    
    // Generate payments for the last 6 months
    for ($i = 0; $i < 6; $i++) {
        $month = date('Y-m-d', strtotime("-$i month"));
        
        // Rent payment for resident 1
        $payment1Insert = "INSERT INTO payments (user_id, property_id, amount, payment_date, payment_method, description) 
                          VALUES ($resident1Id, $property1Id, 1250.00, '$month', 'bank_transfer', 'Monthly rent payment')";
        $conn->exec($payment1Insert);
        
        // Rent payment for resident 2
        $payment2Insert = "INSERT INTO payments (user_id, property_id, amount, payment_date, payment_method, description) 
                          VALUES ($resident2Id, $property2Id, 1500.00, '$month', 'credit_card', 'Monthly rent payment')";
        $conn->exec($payment2Insert);
        
        // Add utility payment every other month
        if ($i % 2 == 0) {
            $util1Amount = rand(80, 150);
            $util1Insert = "INSERT INTO payments (user_id, property_id, amount, payment_date, payment_method, description) 
                           VALUES ($resident1Id, $property1Id, $util1Amount, '$month', 'debit_card', 'Utility payment')";
            $conn->exec($util1Insert);
            
            $util2Amount = rand(100, 180);
            $util2Insert = "INSERT INTO payments (user_id, property_id, amount, payment_date, payment_method, description) 
                           VALUES ($resident2Id, $property2Id, $util2Amount, '$month', 'bank_transfer', 'Utility payment')";
            $conn->exec($util2Insert);
        }
    }
    
    // Insert activity log entries
    outputMessage("Creating sample activity log entries...");
    $log1Insert = "INSERT INTO activity_log (user_id, action, entity_type, entity_id, details) 
                  VALUES ($adminId, 'create', 'property', $property1Id, 'Created new property: Sunset Apartments')";
    $conn->exec($log1Insert);
    
    $log2Insert = "INSERT INTO activity_log (user_id, action, entity_type, entity_id, details) 
                  VALUES ($adminId, 'create', 'property', $property2Id, 'Created new property: Oakwood Homes')";
    $conn->exec($log2Insert);
    
    $log3Insert = "INSERT INTO activity_log (user_id, action, entity_type, entity_id, details) 
                  VALUES ($managerId, 'create', 'ticket', 1, 'Created new maintenance ticket')";
    $conn->exec($log3Insert);
    
    $log4Insert = "INSERT INTO activity_log (user_id, action, entity_type, entity_id, details) 
                  VALUES ($managerId, 'update', 'ticket', 2, 'Updated ticket status to in_progress')";
    $conn->exec($log4Insert);
    
    $log5Insert = "INSERT INTO activity_log (user_id, action, entity_type, entity_id, details) 
                  VALUES ($managerId, 'update', 'ticket', 3, 'Closed maintenance ticket')";
    $conn->exec($log5Insert);
    
    $log6Insert = "INSERT INTO activity_log (user_id, action, entity_type, entity_id, details) 
                  VALUES ($resident1Id, 'create', 'payment', 1, 'Made rent payment of $1250.00')";
    $conn->exec($log6Insert);
    
    $log7Insert = "INSERT INTO activity_log (user_id, action, entity_type, entity_id, details) 
                  VALUES ($resident2Id, 'create', 'payment', 2, 'Made rent payment of $1500.00')";
    $conn->exec($log7Insert);
    
    $log8Insert = "INSERT INTO activity_log (user_id, action, entity_type, entity_id, details) 
                  VALUES ($adminId, 'login', 'user', $adminId, 'Admin user logged in')";
    $conn->exec($log8Insert);
    
    // Close the connection
    $conn = null;
    
    // Success message
    echo "<hr>";
    echo "<h2 class='success'>Database Setup Complete!</h2>";
    echo "<p>The database <strong>$dbname</strong> has been created with all necessary tables and sample data.</p>";
    
    echo "<h3>Login Credentials:</h3>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> admin@ctb.com / admin123</li>";
    echo "<li><strong>Manager:</strong> manager@ctb.com / manager123</li>";
    echo "<li><strong>Resident 1:</strong> resident1@example.com / resident123</li>";
    echo "<li><strong>Resident 2:</strong> resident2@example.com / resident123</li>";
    echo "</ul>";
    
    echo "<p><a href='admin/dashboard.php' style='display:inline-block; background:#4361ee; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Go to Admin Dashboard</a></p>";
    
} catch (PDOException $e) {
    outputMessage("Error: " . $e->getMessage(), false);
    echo "<hr>";
    echo "<h2 class='error'>Database Setup Failed</h2>";
    echo "<p>Please check the error message above and try again.</p>";
}

// Footer
echo "</body></html>"; 
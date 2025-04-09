<?php
// Check for direct access
if (count(get_included_files()) == 1) {
    echo "<h1>Database Setup Utility</h1>";
}

// Set time limit to 5 minutes to allow for lengthy operations
set_time_limit(300);

// Disable max execution time
ini_set('max_execution_time', 0);

// Database configuration
$host = 'localhost';
$username = 'ctb';
$password = 'Rm9c7@Bn.YM4Z!rE';
$dbname = 'ctb_db';

try {
    // Create database connection without selecting a database
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Drop the database if it exists
    $conn->exec("DROP DATABASE IF EXISTS `$dbname`");
    echo "Dropped existing database.<br>";
    
    // Create new database
    $conn->exec("CREATE DATABASE `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Created new database: $dbname<br>";
    
    // Select the database
    $conn->exec("USE `$dbname`");
    echo "Selected database: $dbname<br>";
    
    // Close the connection
    $conn = null;
    
    // Run the table creation scripts
    echo "<h2>Setting up database tables...</h2>";
    echo "<hr>";
    
    // Create activity_log table first
    createActivityLogTable();
    
    // Create other tables
    include_once 'create-users-table.php';
    include_once 'create-properties-table.php';
    include_once 'create-payments-table.php';
    include_once 'create-tickets-table.php';
    
    echo "<hr>";
    echo "<h2>Database setup complete!</h2>";
    echo "<p>All tables have been created and sample data has been inserted.</p>";
    echo "<p><a href='login.php' class='btn btn-primary'>Go to Login Page</a></p>";
    
    echo "<h3>Login Credentials:</h3>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> admin@ctb.com / password123</li>";
    echo "<li><strong>Manager:</strong> manager@ctb.com / Manager@1234</li>";
    echo "<li><strong>Resident:</strong> resident@ctb.com / Resident@1234</li>";
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<div style='color: red;'>";
    echo "Error: " . $e->getMessage();
    echo "</div>";
}

// Function to create activity_log table
function createActivityLogTable() {
    global $host, $username, $password, $dbname;
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Drop existing table if it exists
        $conn->exec("DROP TABLE IF EXISTS activity_log");
        echo "Dropped existing activity_log table.<br>";
        
        // Create activity_log table
        $sql = "CREATE TABLE activity_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            action VARCHAR(50) NOT NULL,
            entity_type VARCHAR(50) NOT NULL,
            entity_id INT NOT NULL,
            details TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $conn->exec($sql);
        echo "Activity log table created successfully.<br>";
        
        // Close the connection
        $conn = null;
        
    } catch (PDOException $e) {
        echo "<div style='color: red;'>";
        echo "Error creating activity_log table: " . $e->getMessage();
        echo "</div>";
    }
}
?> 
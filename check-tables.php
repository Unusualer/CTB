<?php
// Include database configuration
require_once 'includes/config.php';

echo "<h1>Database Tables Status</h1>";

try {
    // List of tables that should exist in the database
    $requiredTables = [
        'users',
        'properties',
        'property_types',
        'resident_properties',
        'tickets',
        'payments',
        'activity_log'
    ];
    
    echo "<table border='1' cellpadding='5'>
        <tr>
            <th>Table Name</th>
            <th>Status</th>
        </tr>";
    
    // Check each table
    foreach ($requiredTables as $table) {
        $checkQuery = "SHOW TABLES LIKE '$table'";
        $result = $conn->query($checkQuery);
        
        $status = $result->rowCount() > 0 ? 
            "<span style='color: green;'>Exists</span>" : 
            "<span style='color: red;'>Missing</span>";
        
        echo "<tr>
            <td>$table</td>
            <td>$status</td>
        </tr>";
    }
    
    echo "</table>";
    
    // Get database user permissions
    echo "<h2>Database User Permissions</h2>";
    echo "<p>User: " . DB_USER . " (on database " . DB_NAME . ")</p>";
    
    try {
        $permissionsQuery = "SHOW GRANTS FOR CURRENT_USER()";
        $permissionsResult = $conn->query($permissionsQuery);
        
        echo "<pre>";
        while ($row = $permissionsResult->fetch(PDO::FETCH_NUM)) {
            echo htmlspecialchars($row[0]) . "\n";
        }
        echo "</pre>";
    } catch (PDOException $e) {
        echo "<p>Cannot retrieve permissions: " . $e->getMessage() . "</p>";
    }
    
} catch(PDOException $e) {
    echo "<div style='color: red;'>";
    echo "Error: " . $e->getMessage();
    echo "</div>";
}

// Close the connection
$conn = null;
?> 
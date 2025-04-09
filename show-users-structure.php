<?php
// Include database configuration
require_once 'includes/config.php';

echo "<h1>Users Table Structure</h1>";

try {
    // Get the structure of the users table
    $query = "DESCRIBE users";
    $result = $conn->query($query);
    
    if ($result) {
        echo "<table border='1' cellpadding='5'>
            <tr>
                <th>Field</th>
                <th>Type</th>
                <th>Null</th>
                <th>Key</th>
                <th>Default</th>
                <th>Extra</th>
            </tr>";
        
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>
                <td>{$row['Field']}</td>
                <td>{$row['Type']}</td>
                <td>{$row['Null']}</td>
                <td>{$row['Key']}</td>
                <td>{$row['Default']}</td>
                <td>{$row['Extra']}</td>
            </tr>";
        }
        
        echo "</table>";
        
        // Show a sample record
        echo "<h2>Sample User Record</h2>";
        
        $sampleQuery = "SELECT * FROM users LIMIT 1";
        $sampleResult = $conn->query($sampleQuery);
        
        if ($sampleResult && $sample = $sampleResult->fetch(PDO::FETCH_ASSOC)) {
            echo "<table border='1' cellpadding='5'>
                <tr>
                    <th>Column</th>
                    <th>Value</th>
                </tr>";
            
            foreach ($sample as $column => $value) {
                echo "<tr>
                    <td>{$column}</td>
                    <td>{$value}</td>
                </tr>";
            }
            
            echo "</table>";
        } else {
            echo "<p>No users found in the database.</p>";
        }
    } else {
        echo "<p>Could not get table structure.</p>";
    }
    
} catch(PDOException $e) {
    echo "<div style='color: red;'>";
    echo "Error: " . $e->getMessage();
    echo "</div>";
}

// Close the connection
$conn = null;
?> 
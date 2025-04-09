<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database config
require_once 'includes/config.php';

echo "<h1>User Table Check</h1>";

// Test database connection
echo "<h2>Testing Database Connection</h2>";
try {
    $conn->query('SELECT 1');
    echo "<p style='color:green'>Database connection: SUCCESS</p>";
} catch (Exception $e) {
    echo "<p style='color:red'>Database connection: FAILED - " . $e->getMessage() . "</p>";
    die("Fix database connection issues before continuing");
}

// Check table structure
echo "<h2>Users Table Structure</h2>";
try {
    $result = $conn->query("DESCRIBE users");
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    foreach ($columns as $column) {
        echo "<tr>";
        foreach ($column as $key => $value) {
            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    
    echo "</table>";
} catch (Exception $e) {
    echo "<p style='color:red'>Error checking table structure: " . $e->getMessage() . "</p>";
}

// Check table data
echo "<h2>Sample User Data</h2>";
try {
    $result = $conn->query("SELECT * FROM users LIMIT 1");
    $user = $result->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "<p>No user records found</p>";
    } else {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr>";
        foreach (array_keys($user) as $key) {
            echo "<th>" . htmlspecialchars($key) . "</th>";
        }
        echo "</tr>";
        
        echo "<tr>";
        foreach ($user as $value) {
            if ($key === 'password') {
                echo "<td>" . substr(htmlspecialchars($value ?? ''), 0, 20) . "...</td>";
            } else {
                echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            }
        }
        echo "</tr>";
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Error checking user data: " . $e->getMessage() . "</p>";
}

// Display column name differences
echo "<h2>Column Name Analysis</h2>";
echo "<p>These are the columns our debug script is looking for versus what's available:</p>";

$expectedColumns = ['id', 'email', 'password', 'role', 'status', 'first_name', 'last_name', 'created_at'];
$actualColumns = [];

try {
    $result = $conn->query("SELECT * FROM users LIMIT 1");
    $user = $result->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $actualColumns = array_keys($user);
    }
    
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Expected Column</th><th>Available?</th></tr>";
    
    foreach ($expectedColumns as $column) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($column) . "</td>";
        echo "<td>" . (in_array($column, $actualColumns) ? "<span style='color:green'>Yes</span>" : "<span style='color:red'>No</span>") . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Check for columns not in our expected list
    echo "<h3>Additional Columns</h3>";
    $extraColumns = array_diff($actualColumns, $expectedColumns);
    
    if (count($extraColumns) > 0) {
        echo "<p>Found additional columns that might be useful:</p>";
        echo "<ul>";
        foreach ($extraColumns as $column) {
            echo "<li>" . htmlspecialchars($column) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No additional columns found.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Error analyzing columns: " . $e->getMessage() . "</p>";
}
?> 
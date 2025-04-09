<?php
// Include database configuration
require_once 'includes/config.php';

// Set headers to display output properly
header('Content-Type: text/html; charset=utf-8');
echo "<!DOCTYPE html>
<html>
<head>
    <title>Create Activity Log Table</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
        h1, h2 { color: #333; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow-x: auto; }
        .success { color: green; }
        .error { color: red; }
        .sample { margin-bottom: 5px; padding: 8px; background: #f9f9f9; border-left: 4px solid #4361ee; }
    </style>
</head>
<body>
    <h1>Creating Activity Log Table</h1>";

try {
    // Drop existing table if it exists
    $conn->exec("DROP TABLE IF EXISTS activity_log");
    echo "<p>Dropped existing activity_log table.</p>";
    
    // Create activity log table
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
    echo "<p class='success'>Activity log table created successfully!</p>";
    
    // Get existing user IDs
    $stmt = $conn->query("SELECT id, username, role FROM users LIMIT 10");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        // If no users found, use default IDs
        $adminId = 1;
        $managerId = 2;
        $residentIds = [3, 4];
        echo "<p class='error'>No users found. Using default IDs.</p>";
    } else {
        // Use existing user IDs
        $adminId = null;
        $managerId = null;
        $residentIds = [];
        
        foreach ($users as $user) {
            if ($user['role'] == 'admin' && !$adminId) {
                $adminId = $user['id'];
            } elseif ($user['role'] == 'manager' && !$managerId) {
                $managerId = $user['id'];
            } elseif ($user['role'] == 'resident') {
                $residentIds[] = $user['id'];
            }
        }
        
        // Fallback values if roles not found
        if (!$adminId) $adminId = $users[0]['id'];
        if (!$managerId) $managerId = $adminId;
        if (empty($residentIds)) $residentIds = [$users[0]['id']];
        
        echo "<p>Using users: Admin ID: $adminId, Manager ID: $managerId, Resident IDs: " . implode(', ', $residentIds) . "</p>";
    }
    
    // Get property IDs
    $stmt = $conn->query("SELECT id, property_name FROM properties LIMIT 3");
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($properties)) {
        // If no properties found, use default IDs
        $propertyIds = [1, 2, 3];
        echo "<p class='error'>No properties found. Using default IDs.</p>";
    } else {
        $propertyIds = array_column($properties, 'id');
        echo "<p>Using property IDs: " . implode(', ', $propertyIds) . "</p>";
    }
    
    // Sample activity types
    $actions = ['create', 'update', 'delete', 'view', 'login', 'logout'];
    $entityTypes = ['user', 'property', 'ticket', 'payment', 'maintenance', 'notification'];
    
    // Generate sample activity log entries
    echo "<h2>Generating Sample Activity Log Entries</h2>";
    
    $sampleLogs = [];
    $totalEntries = 50; // Total number of sample entries to create
    
    for ($i = 0; $i < $totalEntries; $i++) {
        // Randomly select users and properties
        $userId = $i % 3 == 0 ? $adminId : ($i % 3 == 1 ? $managerId : $residentIds[array_rand($residentIds)]);
        $action = $actions[array_rand($actions)];
        $entityType = $entityTypes[array_rand($entityTypes)];
        $entityId = rand(1, 20);
        
        // Generate a meaningful description based on action and entity type
        switch ($action) {
            case 'create':
                $details = "Created new $entityType #$entityId";
                break;
            case 'update':
                $details = "Updated $entityType #$entityId information";
                break;
            case 'delete':
                $details = "Deleted $entityType #$entityId";
                break;
            case 'view':
                $details = "Viewed $entityType #$entityId details";
                break;
            case 'login':
                $details = "User logged into the system";
                break;
            case 'logout':
                $details = "User logged out of the system";
                break;
            default:
                $details = "Performed $action on $entityType #$entityId";
        }
        
        // Add some specific details for certain entity types
        if ($entityType === 'property' && $action === 'create') {
            $propertyNames = ['Sunset Apartments', 'Oakwood Homes', 'Marina Towers', 'Highland Gardens', 'Riverside Complex'];
            $propertyName = $propertyNames[array_rand($propertyNames)];
            $details = "Created new property: $propertyName";
        } elseif ($entityType === 'payment' && $action === 'create') {
            $amount = rand(500, 2000);
            $details = "Made payment of $$amount.00";
        } elseif ($entityType === 'ticket' && $action === 'update') {
            $statuses = ['open', 'in_progress', 'closed', 'pending'];
            $status = $statuses[array_rand($statuses)];
            $details = "Updated ticket status to $status";
        }
        
        // Randomize creation date over the last 3 months
        $daysAgo = rand(0, 90);
        $createdAt = date('Y-m-d H:i:s', strtotime("-$daysAgo days"));
        
        // Create log entry
        $sql = "INSERT INTO activity_log 
                (user_id, action, entity_type, entity_id, details, created_at) 
                VALUES 
                (?, ?, ?, ?, ?, ?)";
                
        $stmt = $conn->prepare($sql);
        $stmt->execute([$userId, $action, $entityType, $entityId, $details, $createdAt]);
        
        // Store the entry for display
        $sampleLogs[] = [
            'id' => $conn->lastInsertId(),
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'details' => $details,
            'created_at' => $createdAt
        ];
    }
    
    // Display sample entries
    echo "<p class='success'>Successfully inserted $totalEntries sample activity log entries.</p>";
    echo "<h3>Sample Activity Log Entries:</h3>";
    
    // Sort by created_at date (newest first)
    usort($sampleLogs, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    // Display the first 10 entries
    echo "<div style='max-height: 400px; overflow-y: auto;'>";
    foreach (array_slice($sampleLogs, 0, 10) as $log) {
        $actionClass = '';
        switch ($log['action']) {
            case 'create': $actionClass = 'background: #e3f2fd;'; break;
            case 'update': $actionClass = 'background: #fff8e1;'; break;
            case 'delete': $actionClass = 'background: #ffebee;'; break;
            case 'login': $actionClass = 'background: #e8f5e9;'; break;
            case 'logout': $actionClass = 'background: #f3e5f5;'; break;
        }
        
        echo "<div class='sample' style='$actionClass'>";
        echo "<strong>ID #{$log['id']}</strong> | ";
        echo "<span style='color:#4361ee; font-weight:bold;'>{$log['action']}</span> | ";
        echo "<em>{$log['entity_type']}</em> | ";
        echo "User ID: {$log['user_id']} | ";
        echo "{$log['details']} | ";
        echo "<span style='color:#666;'>{$log['created_at']}</span>";
        echo "</div>";
    }
    echo "</div>";
    
    echo "<p><strong>Total entries in database:</strong> $totalEntries</p>";
    
    // Link to the activity log page
    echo "<p><a href='admin/activity-log.php' style='display:inline-block; background:#4361ee; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>View Activity Log</a></p>";
    
} catch (PDOException $e) {
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
}

echo "</body></html>";
?> 
<?php
// Start session
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/role_access.php';

// Check if user is logged in and has appropriate role
requireRole('manager');


// Initialize variables from request
$search = $_GET['search'] ?? '';
$user_filter = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$action_filter = $_GET['action'] ?? '';
$entity_type_filter = $_GET['entity_type'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Generate filename with date
$timestamp = date('Y-m-d_H-i-s');
$filename = "activity_log_export_{$timestamp}.csv";

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// Create output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM to fix Excel compatibility
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Add CSV header
fputcsv($output, [
    'ID',
    'ID Utilisateur',
    'Nom Utilisateur',
    'Email Utilisateur',
    'Action',
    'Type d\'Entité',
    'ID de l\'Entité',
    'Détails',
    'Adresse IP',
    'Date & Heure'
]);

try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Build the query - select activity logs with user info
    $query = "SELECT a.*, 
              u.name as user_name, 
              u.email as user_email 
              FROM activity_log a 
              LEFT JOIN users u ON a.user_id = u.id 
              WHERE 1=1";
    $params = [];
    
    // Apply filters
    if (!empty($search)) {
        $query .= " AND (a.details LIKE :search OR u.name LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    if ($user_filter > 0) {
        $query .= " AND a.user_id = :user_id";
        $params[':user_id'] = $user_filter;
    }
    
    if (!empty($action_filter)) {
        $query .= " AND a.action = :action";
        $params[':action'] = $action_filter;
    }
    
    if (!empty($entity_type_filter)) {
        $query .= " AND a.entity_type = :entity_type";
        $params[':entity_type'] = $entity_type_filter;
    }
    
    if (!empty($date_from)) {
        $query .= " AND DATE(a.created_at) >= :date_from";
        $params[':date_from'] = $date_from;
    }
    
    if (!empty($date_to)) {
        $query .= " AND DATE(a.created_at) <= :date_to";
        $params[':date_to'] = $date_to;
    }
    
    // Add ordering
    $query .= " ORDER BY a.created_at DESC";
    
    // Get activity logs (no pagination for export)
    $stmt = $db->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    
    // Process and write each row to CSV
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['id'],
            $row['user_id'],
            $row['user_name'] ?? 'N/A',
            $row['user_email'] ?? 'N/A',
            ucfirst($row['action']),
            ucfirst($row['entity_type']),
            $row['entity_id'],
            $row['details'],
            $row['ip_address'] ?? 'N/A',
            date('Y-m-d H:i:s', strtotime($row['created_at']))
        ]);
    }
    
} catch (PDOException $e) {
    // In case of error, output error row
    fputcsv($output, ['Error exporting data: ' . $e->getMessage()]);
}

// Close the output stream
fclose($output);
exit; 
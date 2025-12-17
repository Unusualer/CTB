<?php
// Start session
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/role_access.php';
require_once '../includes/translations.php';

// Check if user is logged in and has appropriate role
requireAnyRole(['resident']);


// Initialize variables
$status_filter = $_GET['status'] ?? '';
$priority_filter = $_GET['priority'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

// Handle sorting
$sort_column = $_GET['sort'] ?? 'start_date';
$sort_direction = $_GET['dir'] ?? 'desc';

// Validate sort column (whitelist allowed columns)
$allowed_columns = ['id', 'title', 'location', 'status', 'priority', 'start_date', 'end_date'];
if (!in_array($sort_column, $allowed_columns)) {
    $sort_column = 'start_date';
}

// Validate sort direction
if (!in_array(strtolower($sort_direction), ['asc', 'desc'])) {
    $sort_direction = 'desc';
}

// Process form submissions
$success_message = '';
$error_message = '';

// Check for session messages
if (isset($_SESSION['success'])) {
    $success_message = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    $error_message = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Fetch maintenance updates with filters
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // For residents, show all maintenance records (view-only)
    // Build the query
    $query = "SELECT m.*, u.name as created_by_name 
              FROM maintenance m 
              LEFT JOIN users u ON m.created_by = u.id";
    $count_query = "SELECT COUNT(*) as total FROM maintenance m";
    $params = [];
    $where_clauses = [];
    
    // Apply filters
    if (!empty($search)) {
        $where_clauses[] = "(m.title LIKE :search OR m.description LIKE :search OR m.location LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    if (!empty($status_filter)) {
        $where_clauses[] = "m.status = :status";
        $params[':status'] = $status_filter;
    }
    
    if (!empty($priority_filter)) {
        $where_clauses[] = "m.priority = :priority";
        $params[':priority'] = $priority_filter;
    }
    
    if (!empty($date_from)) {
        $where_clauses[] = "m.start_date >= :date_from";
        $params[':date_from'] = $date_from;
    }
    
    if (!empty($date_to)) {
        $where_clauses[] = "m.start_date <= :date_to";
        $params[':date_to'] = $date_to;
    }
    
    // Add WHERE clause if there are any filters
    if (!empty($where_clauses)) {
        $where_sql = " WHERE " . implode(" AND ", $where_clauses);
        $query .= $where_sql;
        $count_query .= $where_sql;
    }
    
    // Add ordering and limit (column name is validated against whitelist, so safe to use)
    $query .= " ORDER BY m.`" . $sort_column . "` " . strtoupper($sort_direction) . " LIMIT :offset, :limit";
    
    // Get total count
    $count_stmt = $db->prepare($count_query);
    foreach ($params as $key => $value) {
        $count_stmt->bindValue($key, $value);
    }
    $count_stmt->execute();
    $total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total / $items_per_page);
    
    // Get maintenance updates
    $stmt = $db->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->execute();
    $maintenance_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get statistics
    // Status statistics
    $status_query = "SELECT status, COUNT(*) as count FROM maintenance GROUP BY status";
    $status_stmt = $db->prepare($status_query);
    $status_stmt->execute();
    $status_stats = [];
    while ($row = $status_stmt->fetch(PDO::FETCH_ASSOC)) {
        $status_stats[$row['status']] = $row['count'];
    }
    
    // Priority statistics
    $priority_query = "SELECT priority, COUNT(*) as count FROM maintenance GROUP BY priority";
    $priority_stmt = $db->prepare($priority_query);
    $priority_stmt->execute();
    $priority_stats = [];
    while ($row = $priority_stmt->fetch(PDO::FETCH_ASSOC)) {
        $priority_stats[$row['priority']] = $row['count'];
    }
    
    // Scheduled for next 7 days
    $upcoming_query = "SELECT COUNT(*) as count FROM maintenance 
                      WHERE start_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
    $upcoming_stmt = $db->prepare($upcoming_query);
    $upcoming_stmt->execute();
    $upcoming_stats = $upcoming_stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
    $maintenance_items = [];
    $total = 0;
    $total_pages = 0;
    $status_stats = [];
    $priority_stats = [];
    $upcoming_stats = ['count' => 0];
}

// Helper function to get badge class for status
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'scheduled':
            return 'info';
        case 'in_progress':
            return 'primary';
        case 'completed':
            return 'success';
        case 'delayed':
            return 'warning';
        case 'cancelled':
            return 'danger';
        default:
            return 'secondary';
    }
}

// Helper function to get badge class for priority
function getPriorityBadgeClass($priority) {
    switch ($priority) {
        case 'low':
            return 'success';
        case 'medium':
            return 'info';
        case 'high':
            return 'warning';
        case 'emergency':
            return 'danger';
        default:
            return 'secondary';
    }
}

$page_title = __("Maintenance Updates");
?>
<!DOCTYPE html>
<html lang="<?php echo isset($_SESSION['language']) ? substr($_SESSION['language'], 0, 2) : 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo __("Complexe Tanger Boulevard"); ?></title>
    <!-- Favicon -->
    <?php favicon_links(); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin-style.css">
    <link rel="stylesheet" href="css/colorful-theme.css">
    <style>
        /* Colorful Stat Cards for Maintenance Page */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8faff 100%);
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1), 0 0 0 1px rgba(0, 0, 0, 0.05);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid transparent;
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            gap: 20px;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 6px;
            height: 100%;
            background: linear-gradient(180deg, var(--card-accent, #4361ee) 0%, var(--card-accent-dark, #3a56e4) 100%);
        }
        
        /* First stat card - Total Updates (Blue) */
        .stat-card:nth-child(1) {
            background: linear-gradient(135deg, #ffffff 0%, #e8f0fe 50%, #f0f4ff 100%);
            border-color: rgba(67, 97, 238, 0.3);
            box-shadow: 0 4px 20px rgba(67, 97, 238, 0.15), 0 0 0 1px rgba(67, 97, 238, 0.1);
        }
        
        .stat-card:nth-child(1)::before {
            background: linear-gradient(180deg, #4361ee 0%, #3a56e4 100%);
            width: 6px;
            box-shadow: 0 0 20px rgba(67, 97, 238, 0.5);
        }
        
        .stat-card:nth-child(1):hover {
            box-shadow: 0 8px 32px rgba(67, 97, 238, 0.25), 0 0 0 2px rgba(67, 97, 238, 0.2);
            transform: translateY(-4px);
        }
        
        .stat-card:nth-child(1) .stat-number {
            color: #4361ee;
            text-shadow: 0 2px 8px rgba(67, 97, 238, 0.2);
        }
        
        .stat-card:nth-child(1) .stat-details h3 {
            color: #2d3f9e;
        }
        
        /* Second stat card - Upcoming (Orange) */
        .stat-card:nth-child(2) {
            background: linear-gradient(135deg, #ffffff 0%, #fff4e6 50%, #fff8f0 100%);
            border-color: rgba(255, 152, 0, 0.3);
            box-shadow: 0 4px 20px rgba(255, 152, 0, 0.15), 0 0 0 1px rgba(255, 152, 0, 0.1);
        }
        
        .stat-card:nth-child(2)::before {
            background: linear-gradient(180deg, #ff9800 0%, #f57c00 100%);
            width: 6px;
            box-shadow: 0 0 20px rgba(255, 152, 0, 0.5);
        }
        
        .stat-card:nth-child(2):hover {
            box-shadow: 0 8px 32px rgba(255, 152, 0, 0.25), 0 0 0 2px rgba(255, 152, 0, 0.2);
            transform: translateY(-4px);
        }
        
        .stat-card:nth-child(2) .stat-number {
            color: #ff9800;
            text-shadow: 0 2px 8px rgba(255, 152, 0, 0.2);
        }
        
        .stat-card:nth-child(2) .stat-details h3 {
            color: #e65100;
        }
        
        /* Third stat card - Status (Purple) */
        .stat-card:nth-child(3) {
            background: linear-gradient(135deg, #ffffff 0%, #f5f0ff 50%, #faf5ff 100%);
            border-color: rgba(155, 89, 182, 0.3);
            box-shadow: 0 4px 20px rgba(155, 89, 182, 0.15), 0 0 0 1px rgba(155, 89, 182, 0.1);
        }
        
        .stat-card:nth-child(3)::before {
            background: linear-gradient(180deg, #9b59b6 0%, #8e44ad 100%);
            width: 6px;
            box-shadow: 0 0 20px rgba(155, 89, 182, 0.5);
        }
        
        .stat-card:nth-child(3):hover {
            box-shadow: 0 8px 32px rgba(155, 89, 182, 0.25), 0 0 0 2px rgba(155, 89, 182, 0.2);
            transform: translateY(-4px);
        }
        
        .stat-card:nth-child(3) .stat-details h3 {
            color: #6c3483;
        }
        
        /* Fourth stat card - Priority (Red) */
        .stat-card:nth-child(4) {
            background: linear-gradient(135deg, #ffffff 0%, #ffeaea 50%, #fff5f5 100%);
            border-color: rgba(231, 74, 59, 0.3);
            box-shadow: 0 4px 20px rgba(231, 74, 59, 0.15), 0 0 0 1px rgba(231, 74, 59, 0.1);
        }
        
        .stat-card:nth-child(4)::before {
            background: linear-gradient(180deg, #e74a3b 0%, #c0392b 100%);
            width: 6px;
            box-shadow: 0 0 20px rgba(231, 74, 59, 0.5);
        }
        
        .stat-card:nth-child(4):hover {
            box-shadow: 0 8px 32px rgba(231, 74, 59, 0.25), 0 0 0 2px rgba(231, 74, 59, 0.2);
            transform: translateY(-4px);
        }
        
        .stat-card:nth-child(4) .stat-details h3 {
            color: #c0392b;
        }
        
        [data-theme="dark"] .stat-card {
            background: #2d3748;
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        [data-theme="dark"] .stat-card:nth-child(1) .stat-number,
        [data-theme="dark"] .stat-card:nth-child(2) .stat-number {
            color: #f8f9fc;
            text-shadow: none;
        }
        
        [data-theme="dark"] .stat-card:nth-child(1) .stat-details h3,
        [data-theme="dark"] .stat-card:nth-child(2) .stat-details h3,
        [data-theme="dark"] .stat-card:nth-child(3) .stat-details h3,
        [data-theme="dark"] .stat-card:nth-child(4) .stat-details h3 {
            color: #a0aec0;
        }
        
        /* Stat Icon Styling */
        .stat-icon {
            width: 64px;
            height: 64px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover .stat-icon {
            transform: scale(1.05);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
        }
        
        .stat-icon.maintenance {
            background: linear-gradient(135deg, #4361ee 0%, #3a56e4 100%);
        }
        
        .stat-icon.upcoming {
            background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
        }
        
        .stat-icon.status {
            background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
        }
        
        .stat-icon.priority {
            background: linear-gradient(135deg, #e74a3b 0%, #c0392b 100%);
        }
        
        .stat-icon i {
            font-size: 28px;
            color: white;
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: 700;
            line-height: 1.2;
            margin: 8px 0;
        }
        
        .stat-details h3 {
            font-size: 14px;
            font-weight: 600;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-breakdown {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 12px;
        }
        
        .stat-breakdown span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        /* First card breakdown - Blue */
        .stat-card:nth-child(1) .stat-breakdown span {
            background: rgba(67, 97, 238, 0.12);
            border: 2px solid rgba(67, 97, 238, 0.25);
            color: #2d3f9e;
        }
        
        .stat-card:nth-child(1) .stat-breakdown span:hover {
            background: rgba(67, 97, 238, 0.2);
            border-color: rgba(67, 97, 238, 0.4);
            transform: translateX(4px);
        }
        
        /* Second card breakdown - Orange */
        .stat-card:nth-child(2) .stat-breakdown span {
            background: rgba(255, 152, 0, 0.12);
            border: 2px solid rgba(255, 152, 0, 0.25);
            color: #e65100;
        }
        
        .stat-card:nth-child(2) .stat-breakdown span:hover {
            background: rgba(255, 152, 0, 0.2);
            border-color: rgba(255, 152, 0, 0.4);
            transform: translateX(4px);
        }
        
        /* Third card breakdown - Purple with colorful items */
        .stat-card:nth-child(3) .stat-breakdown span {
            background: rgba(155, 89, 182, 0.12);
            border: 2px solid rgba(155, 89, 182, 0.25);
            color: #6c3483;
        }
        
        .stat-card:nth-child(3) .stat-breakdown span:first-child {
            background: rgba(76, 201, 240, 0.12);
            border-color: rgba(76, 201, 240, 0.25);
            color: #0d6b8a;
        }
        
        .stat-card:nth-child(3) .stat-breakdown span:first-child i {
            color: #4cc9f0;
        }
        
        .stat-card:nth-child(3) .stat-breakdown span:nth-child(2) {
            background: rgba(67, 97, 238, 0.12);
            border-color: rgba(67, 97, 238, 0.25);
            color: #2d3f9e;
        }
        
        .stat-card:nth-child(3) .stat-breakdown span:nth-child(2) i {
            color: #4361ee;
        }
        
        .stat-card:nth-child(3) .stat-breakdown span:last-child {
            background: rgba(37, 198, 133, 0.12);
            border-color: rgba(37, 198, 133, 0.25);
            color: #0d7a4f;
        }
        
        .stat-card:nth-child(3) .stat-breakdown span:last-child i {
            color: #25c685;
        }
        
        .stat-card:nth-child(3) .stat-breakdown span:hover {
            transform: translateX(4px);
        }
        
        /* Fourth card breakdown - Red with colorful items */
        .stat-card:nth-child(4) .stat-breakdown span {
            background: rgba(231, 74, 59, 0.12);
            border: 2px solid rgba(231, 74, 59, 0.25);
            color: #c0392b;
        }
        
        .stat-card:nth-child(4) .stat-breakdown span:first-child {
            background: rgba(37, 198, 133, 0.12);
            border-color: rgba(37, 198, 133, 0.25);
            color: #0d7a4f;
        }
        
        .stat-card:nth-child(4) .stat-breakdown span:first-child i {
            color: #25c685;
        }
        
        .stat-card:nth-child(4) .stat-breakdown span:nth-child(2) {
            background: rgba(76, 201, 240, 0.12);
            border-color: rgba(76, 201, 240, 0.25);
            color: #0d6b8a;
        }
        
        .stat-card:nth-child(4) .stat-breakdown span:nth-child(2) i {
            color: #4cc9f0;
        }
        
        .stat-card:nth-child(4) .stat-breakdown span:nth-child(3) {
            background: rgba(248, 184, 48, 0.12);
            border-color: rgba(248, 184, 48, 0.25);
            color: #b8820f;
        }
        
        .stat-card:nth-child(4) .stat-breakdown span:nth-child(3) i {
            color: #f8b830;
        }
        
        .stat-card:nth-child(4) .stat-breakdown span:last-child {
            background: rgba(231, 74, 59, 0.12);
            border-color: rgba(231, 74, 59, 0.25);
            color: #c0392b;
        }
        
        .stat-card:nth-child(4) .stat-breakdown span:last-child i {
            color: #e74a3b;
        }
        
        .stat-card:nth-child(4) .stat-breakdown span:hover {
            transform: translateX(4px);
        }
        
        [data-theme="dark"] .stat-card:nth-child(1) .stat-breakdown span {
            background: rgba(78, 115, 223, 0.15);
            border-color: rgba(78, 115, 223, 0.25);
            color: #93c5fd;
        }
        
        [data-theme="dark"] .stat-card:nth-child(2) .stat-breakdown span {
            background: rgba(251, 146, 60, 0.15);
            border-color: rgba(251, 146, 60, 0.25);
            color: #fdba74;
        }
        
        [data-theme="dark"] .stat-card:nth-child(3) .stat-breakdown span:first-child {
            background: rgba(54, 185, 204, 0.15);
            border-color: rgba(54, 185, 204, 0.25);
            color: #7dd3fc;
        }
        
        [data-theme="dark"] .stat-card:nth-child(3) .stat-breakdown span:nth-child(2) {
            background: rgba(78, 115, 223, 0.15);
            border-color: rgba(78, 115, 223, 0.25);
            color: #93c5fd;
        }
        
        [data-theme="dark"] .stat-card:nth-child(3) .stat-breakdown span:last-child {
            background: rgba(28, 200, 138, 0.15);
            border-color: rgba(28, 200, 138, 0.25);
            color: #6ee7b7;
        }
        
        [data-theme="dark"] .stat-card:nth-child(4) .stat-breakdown span:first-child {
            background: rgba(28, 200, 138, 0.15);
            border-color: rgba(28, 200, 138, 0.25);
            color: #6ee7b7;
        }
        
        [data-theme="dark"] .stat-card:nth-child(4) .stat-breakdown span:nth-child(2) {
            background: rgba(54, 185, 204, 0.15);
            border-color: rgba(54, 185, 204, 0.25);
            color: #7dd3fc;
        }
        
        [data-theme="dark"] .stat-card:nth-child(4) .stat-breakdown span:nth-child(3) {
            background: rgba(246, 194, 62, 0.15);
            border-color: rgba(246, 194, 62, 0.25);
            color: #fcd34d;
        }
        
        [data-theme="dark"] .stat-card:nth-child(4) .stat-breakdown span:last-child {
            background: rgba(239, 68, 68, 0.15);
            border-color: rgba(239, 68, 68, 0.25);
            color: #fca5a5;
        }
        
        /* Colorful Table Headers */
        .table thead th {
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.12) 0%, rgba(37, 198, 133, 0.08) 100%);
            color: #2d3748;
            font-weight: 600;
            border-bottom: 3px solid rgba(67, 97, 238, 0.2);
            position: relative;
            padding: 16px;
        }
        
        .table thead th::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 3px;
            background: linear-gradient(90deg, #4361ee 0%, #25c685 50%, #4cc9f0 100%);
            transition: width 0.3s ease;
        }
        
        .table thead th:hover::after {
            width: 100%;
        }
        
        .table thead th:nth-child(1) {
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.12) 0%, rgba(67, 97, 238, 0.05) 100%);
            border-bottom-color: rgba(67, 97, 238, 0.3);
        }
        
        .table thead th:nth-child(1)::after {
            background: linear-gradient(90deg, #4361ee 0%, #3a56e4 100%);
        }
        
        .table thead th:nth-child(2) {
            background: linear-gradient(135deg, rgba(37, 198, 133, 0.12) 0%, rgba(37, 198, 133, 0.05) 100%);
            border-bottom-color: rgba(37, 198, 133, 0.3);
        }
        
        .table thead th:nth-child(2)::after {
            background: linear-gradient(90deg, #25c685 0%, #13b571 100%);
        }
        
        .table thead th:nth-child(3) {
            background: linear-gradient(135deg, rgba(248, 184, 48, 0.12) 0%, rgba(248, 184, 48, 0.05) 100%);
            border-bottom-color: rgba(248, 184, 48, 0.3);
        }
        
        .table thead th:nth-child(3)::after {
            background: linear-gradient(90deg, #f8b830 0%, #f6a819 100%);
        }
        
        .table thead th:nth-child(4) {
            background: linear-gradient(135deg, rgba(231, 74, 59, 0.12) 0%, rgba(231, 74, 59, 0.05) 100%);
            border-bottom-color: rgba(231, 74, 59, 0.3);
        }
        
        .table thead th:nth-child(4)::after {
            background: linear-gradient(90deg, #e74a3b 0%, #dc3545 100%);
        }
        
        .table thead th:nth-child(5) {
            background: linear-gradient(135deg, rgba(76, 201, 240, 0.12) 0%, rgba(76, 201, 240, 0.05) 100%);
            border-bottom-color: rgba(76, 201, 240, 0.3);
        }
        
        .table thead th:nth-child(5)::after {
            background: linear-gradient(90deg, #4cc9f0 0%, #39b8df 100%);
        }
        
        .table thead th:nth-child(6) {
            background: linear-gradient(135deg, rgba(155, 89, 182, 0.12) 0%, rgba(155, 89, 182, 0.05) 100%);
            border-bottom-color: rgba(155, 89, 182, 0.3);
        }
        
        .table thead th:nth-child(6)::after {
            background: linear-gradient(90deg, #9b59b6 0%, #8e44ad 100%);
        }
        
        .table thead th:nth-child(7) {
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.12) 0%, rgba(67, 97, 238, 0.05) 100%);
            border-bottom-color: rgba(67, 97, 238, 0.3);
        }
        
        .table thead th:nth-child(7)::after {
            background: linear-gradient(90deg, #4361ee 0%, #3a56e4 100%);
        }
        
        [data-theme="dark"] .table thead th {
            background: linear-gradient(135deg, rgba(78, 115, 223, 0.15) 0%, rgba(28, 200, 138, 0.1) 100%);
            color: #f8f9fc;
            border-bottom-color: rgba(78, 115, 223, 0.25);
        }
        
        [data-theme="dark"] .table thead th::after {
            background: linear-gradient(90deg, #4e73df 0%, #1cc88a 50%, #36b9cc 100%);
        }
        
        /* Sortable table header styles */
        .table th.sortable {
            cursor: pointer;
            user-select: none;
            position: relative;
            padding-right: 30px;
            transition: all 0.3s ease;
        }
        
        .table th.sortable:hover {
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.2) 0%, rgba(37, 198, 133, 0.15) 100%);
            transform: translateY(-2px);
        }
        
        .table th.sortable .sort-icon {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 0.85em;
            transition: all 0.3s ease;
        }
        
        .table th.sortable:hover .sort-icon {
            color: #4361ee;
            transform: translateY(-50%) scale(1.2);
        }
        
        .table th.sortable[data-sorted="true"] .sort-icon {
            color: #4361ee;
        }
        
        .table thead th:nth-child(1).sortable:hover .sort-icon {
            color: #4361ee;
        }
        
        .table thead th:nth-child(2).sortable:hover .sort-icon {
            color: #25c685;
        }
        
        .table thead th:nth-child(3).sortable:hover .sort-icon {
            color: #f8b830;
        }
        
        .table thead th:nth-child(4).sortable:hover .sort-icon {
            color: #e74a3b;
        }
        
        .table thead th:nth-child(5).sortable:hover .sort-icon {
            color: #4cc9f0;
        }
        
        .table thead th:nth-child(6).sortable:hover .sort-icon {
            color: #9b59b6;
        }
        
        .table th.actions {
            cursor: default;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/admin-sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <h1><?php echo __("Maintenance Updates"); ?></h1>
            </div>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon maintenance">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo __("Total Updates"); ?></h3>
                        <div class="stat-number"><?php echo number_format($total); ?></div>
                        <div class="stat-breakdown">
                            <span><?php echo __("All time"); ?></span>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon upcoming">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo __("Upcoming"); ?></h3>
                        <div class="stat-number"><?php echo isset($upcoming_stats['count']) ? number_format($upcoming_stats['count']) : 0; ?></div>
                        <div class="stat-breakdown">
                            <span><?php echo __("Next 7 days"); ?></span>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon status">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo __("Status"); ?></h3>
                        <div class="stat-breakdown">
                            <span><i class="fas fa-circle"></i> <?php echo __("Scheduled"); ?>: <?php echo isset($status_stats['scheduled']) ? $status_stats['scheduled'] : 0; ?></span>
                            <span><i class="fas fa-circle"></i> <?php echo __("In Progress"); ?>: <?php echo isset($status_stats['in_progress']) ? $status_stats['in_progress'] : 0; ?></span>
                            <span><i class="fas fa-circle"></i> <?php echo __("Completed"); ?>: <?php echo isset($status_stats['completed']) ? $status_stats['completed'] : 0; ?></span>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon priority">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo __("Priority"); ?></h3>
                        <div class="stat-breakdown">
                            <span><i class="fas fa-circle"></i> <?php echo __("Low"); ?>: <?php echo isset($priority_stats['low']) ? $priority_stats['low'] : 0; ?></span>
                            <span><i class="fas fa-circle"></i> <?php echo __("Medium"); ?>: <?php echo isset($priority_stats['medium']) ? $priority_stats['medium'] : 0; ?></span>
                            <span><i class="fas fa-circle"></i> <?php echo __("High"); ?>: <?php echo isset($priority_stats['high']) ? $priority_stats['high'] : 0; ?></span>
                            <span><i class="fas fa-circle"></i> <?php echo __("Emergency"); ?>: <?php echo isset($priority_stats['emergency']) ? $priority_stats['emergency'] : 0; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card user-filter-card">
                <div class="card-header user-filter-header">
                    <h3><i class="fas fa-filter"></i> <?php echo __("Updates List"); ?></h3>
                    <form id="filter-form" action="maintenance.php" method="GET" class="filter-form">
                        <div class="filter-wrapper">
                            <div class="search-filter">
                                <div class="search-bar">
                                    <i class="fas fa-search"></i>
                                    <input type="text" placeholder="<?php echo __("Search for maintenance updates..."); ?>" name="search" value="<?php echo htmlspecialchars($search); ?>" autocomplete="off" autofocus>
                                </div>
                            </div>
                            <div class="filter-group">
                                <label for="status"><?php echo __("Status"); ?>:</label>
                                <select name="status" id="status" onchange="this.form.submit()">
                                    <option value=""><?php echo __("All Statuses"); ?></option>
                                    <option value="scheduled" <?php echo $status_filter === 'scheduled' ? 'selected' : ''; ?>><?php echo __("Scheduled"); ?></option>
                                    <option value="in_progress" <?php echo $status_filter === 'in_progress' ? 'selected' : ''; ?>><?php echo __("In Progress"); ?></option>
                                    <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>><?php echo __("Completed"); ?></option>
                                    <option value="delayed" <?php echo $status_filter === 'delayed' ? 'selected' : ''; ?>><?php echo __("Delayed"); ?></option>
                                    <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>><?php echo __("Cancelled"); ?></option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label for="priority"><?php echo __("Priority"); ?>:</label>
                                <select name="priority" id="priority" onchange="this.form.submit()">
                                    <option value=""><?php echo __("All Priorities"); ?></option>
                                    <option value="low" <?php echo $priority_filter === 'low' ? 'selected' : ''; ?>><?php echo __("Low"); ?></option>
                                    <option value="medium" <?php echo $priority_filter === 'medium' ? 'selected' : ''; ?>><?php echo __("Medium"); ?></option>
                                    <option value="high" <?php echo $priority_filter === 'high' ? 'selected' : ''; ?>><?php echo __("High"); ?></option>
                                    <option value="emergency" <?php echo $priority_filter === 'emergency' ? 'selected' : ''; ?>><?php echo __("Emergency"); ?></option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label for="date_from"><?php echo __("Date From"); ?>:</label>
                                <input type="date" id="date_from" name="date_from" value="<?php echo $date_from; ?>" onchange="this.form.submit()">
                            </div>
                            <div class="filter-group">
                                <label for="date_to"><?php echo __("Date To"); ?>:</label>
                                <input type="date" id="date_to" name="date_to" value="<?php echo $date_to; ?>" onchange="this.form.submit()">
                            </div>
                            <a href="maintenance.php" class="reset-link"><?php echo __("Reset Filters"); ?></a>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    <?php if (empty($maintenance_items)): ?>
                        <div class="empty-state">
                            <i class="fas fa-tools"></i>
                            <p><?php echo __("No updates found. Try adjusting your filters."); ?></p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th class="sortable" data-column="title">
                                            <?php echo __("Title"); ?>
                                            <span class="sort-icon">
                                                <?php if ($sort_column === 'title'): ?>
                                                    <i class="fas fa-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-sort"></i>
                                                <?php endif; ?>
                                            </span>
                                        </th>
                                        <th class="sortable" data-column="location">
                                            <?php echo __("Location"); ?>
                                            <span class="sort-icon">
                                                <?php if ($sort_column === 'location'): ?>
                                                    <i class="fas fa-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-sort"></i>
                                                <?php endif; ?>
                                            </span>
                                        </th>
                                        <th class="sortable" data-column="status">
                                            <?php echo __("Status"); ?>
                                            <span class="sort-icon">
                                                <?php if ($sort_column === 'status'): ?>
                                                    <i class="fas fa-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-sort"></i>
                                                <?php endif; ?>
                                            </span>
                                        </th>
                                        <th class="sortable" data-column="priority">
                                            <?php echo __("Priority"); ?>
                                            <span class="sort-icon">
                                                <?php if ($sort_column === 'priority'): ?>
                                                    <i class="fas fa-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-sort"></i>
                                                <?php endif; ?>
                                            </span>
                                        </th>
                                        <th class="sortable" data-column="start_date">
                                            <?php echo __("Start Date"); ?>
                                            <span class="sort-icon">
                                                <?php if ($sort_column === 'start_date'): ?>
                                                    <i class="fas fa-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-sort"></i>
                                                <?php endif; ?>
                                            </span>
                                        </th>
                                        <th class="sortable" data-column="end_date">
                                            <?php echo __("End Date"); ?>
                                            <span class="sort-icon">
                                                <?php if ($sort_column === 'end_date'): ?>
                                                    <i class="fas fa-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-sort"></i>
                                                <?php endif; ?>
                                            </span>
                                        </th>
                                        <th><?php echo __("Actions"); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($maintenance_items as $update): ?>
                                        <tr>
                                            <td class="align-middle"><?php echo htmlspecialchars($update['title']); ?></td>
                                            <td class="align-middle"><?php echo htmlspecialchars($update['location']); ?></td>
                                            <td class="align-middle">
                                                <span class="status-indicator status-<?php echo getStatusBadgeClass($update['status']); ?>">
                                                    <?php echo __(ucfirst(str_replace('_', ' ', $update['status']))); ?>
                                                </span>
                                            </td>
                                            <td class="align-middle">
                                                <span class="status-indicator status-<?php echo getPriorityBadgeClass($update['priority']); ?>">
                                                    <?php echo __(ucfirst($update['priority'])); ?>
                                                </span>
                                            </td>
                                            <td class="align-middle">
                                                <div><?php echo date('M d, Y', strtotime($update['start_date'])); ?></div>
                                            </td>
                                            <td class="align-middle">
                                                <div><?php echo date('M d, Y', strtotime($update['end_date'])); ?></div>
                                            </td>
                                            <td class="actions">
                                                <a href="view-maintenance.php?id=<?php echo $update['id']; ?>" class="btn-icon" title="<?php echo __("View Update"); ?>">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&priority=<?php echo urlencode($priority_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>&sort=<?php echo urlencode($sort_column); ?>&dir=<?php echo urlencode($sort_direction); ?>" class="pagination-link">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&priority=<?php echo urlencode($priority_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>&sort=<?php echo urlencode($sort_column); ?>&dir=<?php echo urlencode($sort_direction); ?>" 
                                       class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&priority=<?php echo urlencode($priority_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>&sort=<?php echo urlencode($sort_column); ?>&dir=<?php echo urlencode($sort_direction); ?>" class="pagination-link">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="js/dark-mode.js"></script>
    <script>
        // Track current sort state - get from URL params or use defaults
        const urlParams = new URLSearchParams(window.location.search);
        let currentSort = {
            column: urlParams.get('sort') || 'start_date',
            direction: urlParams.get('dir') || 'desc'
        };
        
        // Function to initialize sortable headers
        function initSortableHeaders() {
            const sortableHeaders = document.querySelectorAll('.table th.sortable');
            sortableHeaders.forEach(header => {
                header.addEventListener('click', function() {
                    const column = this.getAttribute('data-column');
                    
                    // Toggle sort direction if clicking the same column, otherwise default to ascending
                    if (currentSort.column === column) {
                        currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
                    } else {
                        currentSort.column = column;
                        currentSort.direction = 'asc';
                    }
                    
                    // Reload page with new sort
                    const newUrl = new URL(window.location.href);
                    newUrl.searchParams.set('sort', currentSort.column);
                    newUrl.searchParams.set('dir', currentSort.direction);
                    window.location.href = newUrl.toString();
                });
            });
        }
        
        // Initialize sortable headers on page load
        document.addEventListener('DOMContentLoaded', function() {
            initSortableHeaders();
        });
        
        // Date filters functionality
        const dateFrom = document.getElementById('date_from');
        const dateTo = document.getElementById('date_to');
        
        dateFrom.addEventListener('change', function() {
            if (dateTo.value && new Date(this.value) > new Date(dateTo.value)) {
                dateTo.value = this.value;
            }
            this.form.submit();
        });
        
        dateTo.addEventListener('change', function() {
            if (dateFrom.value && new Date(this.value) < new Date(dateFrom.value)) {
                dateFrom.value = this.value;
            }
            this.form.submit();
        });
    </script>
</body>
</html> 
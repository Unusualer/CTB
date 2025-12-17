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
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$payment_method_filter = $_GET['payment_method'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

// Handle sorting
$sort_column = $_GET['sort'] ?? 'payment_date';
$sort_direction = $_GET['dir'] ?? 'desc';

// Validate sort column (whitelist allowed columns)
$allowed_columns = ['id', 'amount', 'type', 'status', 'payment_date', 'property'];
if (!in_array($sort_column, $allowed_columns)) {
    $sort_column = 'payment_date';
}

// Validate sort direction
if (!in_array(strtolower($sort_direction), ['asc', 'desc'])) {
    $sort_direction = 'desc';
}

// Get total count and payments list
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // For residents, only show payments for their properties
    $current_user_id = $_SESSION['user_id'];
    
    // Build the query - select payments with user and property info
    $query = "SELECT p.*, 
              p.type as payment_method,
              u.name as user_name, 
              pr.identifier as property_identifier 
              FROM payments p 
              LEFT JOIN properties pr ON p.property_id = pr.id 
              LEFT JOIN users u ON pr.user_id = u.id 
              WHERE pr.user_id = :user_id";
    $count_query = "SELECT COUNT(*) as total FROM payments p 
                    LEFT JOIN properties pr ON p.property_id = pr.id 
                    WHERE pr.user_id = :user_id";
    $params = [':user_id' => $current_user_id];
    
    // Apply filters
    if (!empty($search)) {
        $query .= " AND (p.id LIKE :search OR u.name LIKE :search)";
        $count_query .= " AND (p.id LIKE :search OR u.name LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    if (!empty($status_filter)) {
        $query .= " AND p.status = :status";
        $count_query .= " AND p.status = :status";
        $params[':status'] = $status_filter;
    }
    
    if (!empty($payment_method_filter)) {
        $query .= " AND p.type = :payment_method";
        $count_query .= " AND p.type = :payment_method";
        $params[':payment_method'] = $payment_method_filter;
    }
    
    if (!empty($date_from)) {
        $query .= " AND p.payment_date >= :date_from";
        $count_query .= " AND p.payment_date >= :date_from";
        $params[':date_from'] = $date_from;
    }
    
    if (!empty($date_to)) {
        $query .= " AND p.payment_date <= :date_to";
        $count_query .= " AND p.payment_date <= :date_to";
        $params[':date_to'] = $date_to;
    }
    
    // Add ordering (column name is validated against whitelist, so safe to use)
    if ($sort_column === 'property') {
        $query .= " ORDER BY pr.identifier " . strtoupper($sort_direction) . " LIMIT :offset, :limit";
    } else {
        $query .= " ORDER BY p.`" . $sort_column . "` " . strtoupper($sort_direction) . " LIMIT :offset, :limit";
    }
    
    // Get total count
    $count_stmt = $db->prepare($count_query);
    foreach ($params as $key => $value) {
        $count_stmt->bindValue($key, $value);
    }
    $count_stmt->execute();
    $total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total / $items_per_page);
    
    // Get payments
    $stmt = $db->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->execute();
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get payment statistics - filtered by user's properties
    // Total amounts
    $stats_query = "SELECT 
                    SUM(p.amount) as total_amount,
                    COUNT(*) as total_count
                    FROM payments p
                    LEFT JOIN properties pr ON p.property_id = pr.id
                    WHERE pr.user_id = :user_id";
    $stats_stmt = $db->prepare($stats_query);
    $stats_stmt->bindParam(':user_id', $current_user_id, PDO::PARAM_INT);
    $stats_stmt->execute();
    $payment_stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Status statistics
    $status_query = "SELECT p.status, COUNT(*) as count, SUM(p.amount) as total 
                     FROM payments p
                     LEFT JOIN properties pr ON p.property_id = pr.id
                     WHERE pr.user_id = :user_id
                     GROUP BY p.status";
    $status_stmt = $db->prepare($status_query);
    $status_stmt->bindParam(':user_id', $current_user_id, PDO::PARAM_INT);
    $status_stmt->execute();
    $status_stats = [];
    while ($row = $status_stmt->fetch(PDO::FETCH_ASSOC)) {
        $status_stats[$row['status']] = [
            'count' => $row['count'],
            'total' => $row['total']
        ];
    }
    
    // Payment method statistics
    $method_query = "SELECT p.type as payment_method, COUNT(*) as count
                    FROM payments p
                    LEFT JOIN properties pr ON p.property_id = pr.id
                    WHERE pr.user_id = :user_id
                    GROUP BY p.type";
    $method_stmt = $db->prepare($method_query);
    $method_stmt->bindParam(':user_id', $current_user_id, PDO::PARAM_INT);
    $method_stmt->execute();
    $method_stats = [];
    while ($row = $method_stmt->fetch(PDO::FETCH_ASSOC)) {
        $method_stats[$row['payment_method']] = $row['count'];
    }
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    $payments = [];
    $total = 0;
    $total_pages = 0;
    $payment_stats = ['total_amount' => 0, 'total_count' => 0];
    $status_stats = [];
    $method_stats = [];
}

// Page title
$page_title = __("Payment Management");
?>

<!DOCTYPE html>
<html lang="<?php echo substr($_SESSION['language'] ?? 'en_US', 0, 2); ?>">
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
        /* Colorful Stat Cards for Payments Page */
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
        
        /* First stat card - Total Payments (Cyan) */
        .stat-card:nth-child(1) {
            background: linear-gradient(135deg, #ffffff 0%, #e8f7fc 50%, #f0faff 100%);
            border-color: rgba(76, 201, 240, 0.3);
            box-shadow: 0 4px 20px rgba(76, 201, 240, 0.15), 0 0 0 1px rgba(76, 201, 240, 0.1);
        }
        
        .stat-card:nth-child(1)::before {
            background: linear-gradient(180deg, #4cc9f0 0%, #39b8df 100%);
            width: 6px;
            box-shadow: 0 0 20px rgba(76, 201, 240, 0.5);
        }
        
        .stat-card:nth-child(1):hover {
            box-shadow: 0 8px 32px rgba(76, 201, 240, 0.25), 0 0 0 2px rgba(76, 201, 240, 0.2);
            transform: translateY(-4px);
        }
        
        .stat-card:nth-child(1) .stat-number {
            color: #4cc9f0;
            text-shadow: 0 2px 8px rgba(76, 201, 240, 0.2);
        }
        
        .stat-card:nth-child(1) .stat-details h3 {
            color: #0d6b8a;
        }
        
        /* Second stat card - Paid (Green) */
        .stat-card:nth-child(2) {
            background: linear-gradient(135deg, #ffffff 0%, #e8f8f5 50%, #f0fdf9 100%);
            border-color: rgba(37, 198, 133, 0.3);
            box-shadow: 0 4px 20px rgba(37, 198, 133, 0.15), 0 0 0 1px rgba(37, 198, 133, 0.1);
        }
        
        .stat-card:nth-child(2)::before {
            background: linear-gradient(180deg, #25c685 0%, #13b571 100%);
            width: 6px;
            box-shadow: 0 0 20px rgba(37, 198, 133, 0.5);
        }
        
        .stat-card:nth-child(2):hover {
            box-shadow: 0 8px 32px rgba(37, 198, 133, 0.25), 0 0 0 2px rgba(37, 198, 133, 0.2);
            transform: translateY(-4px);
        }
        
        .stat-card:nth-child(2) .stat-number {
            color: #25c685;
            text-shadow: 0 2px 8px rgba(37, 198, 133, 0.2);
        }
        
        .stat-card:nth-child(2) .stat-details h3 {
            color: #0d7a4f;
        }
        
        /* Third stat card - Pending (Yellow) */
        .stat-card:nth-child(3) {
            background: linear-gradient(135deg, #ffffff 0%, #fff8e8 50%, #fffbf0 100%);
            border-color: rgba(248, 184, 48, 0.3);
            box-shadow: 0 4px 20px rgba(248, 184, 48, 0.15), 0 0 0 1px rgba(248, 184, 48, 0.1);
        }
        
        .stat-card:nth-child(3)::before {
            background: linear-gradient(180deg, #f8b830 0%, #f6a819 100%);
            width: 6px;
            box-shadow: 0 0 20px rgba(248, 184, 48, 0.5);
        }
        
        .stat-card:nth-child(3):hover {
            box-shadow: 0 8px 32px rgba(248, 184, 48, 0.25), 0 0 0 2px rgba(248, 184, 48, 0.2);
            transform: translateY(-4px);
        }
        
        .stat-card:nth-child(3) .stat-number {
            color: #f8b830;
            text-shadow: 0 2px 8px rgba(248, 184, 48, 0.2);
        }
        
        .stat-card:nth-child(3) .stat-details h3 {
            color: #b8820f;
        }
        
        /* Fourth stat card - Payment Methods (Purple) */
        .stat-card:nth-child(4) {
            background: linear-gradient(135deg, #ffffff 0%, #f5f0ff 50%, #faf5ff 100%);
            border-color: rgba(155, 89, 182, 0.3);
            box-shadow: 0 4px 20px rgba(155, 89, 182, 0.15), 0 0 0 1px rgba(155, 89, 182, 0.1);
        }
        
        .stat-card:nth-child(4)::before {
            background: linear-gradient(180deg, #9b59b6 0%, #8e44ad 100%);
            width: 6px;
            box-shadow: 0 0 20px rgba(155, 89, 182, 0.5);
        }
        
        .stat-card:nth-child(4):hover {
            box-shadow: 0 8px 32px rgba(155, 89, 182, 0.25), 0 0 0 2px rgba(155, 89, 182, 0.2);
            transform: translateY(-4px);
        }
        
        .stat-card:nth-child(4) .stat-number {
            color: #9b59b6;
            text-shadow: 0 2px 8px rgba(155, 89, 182, 0.2);
        }
        
        .stat-card:nth-child(4) .stat-details h3 {
            color: #6c3483;
        }
        
        [data-theme="dark"] .stat-card {
            background: #2d3748;
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        [data-theme="dark"] .stat-card:nth-child(1) .stat-number,
        [data-theme="dark"] .stat-card:nth-child(2) .stat-number,
        [data-theme="dark"] .stat-card:nth-child(3) .stat-number,
        [data-theme="dark"] .stat-card:nth-child(4) .stat-number {
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
        
        .stat-icon.payments {
            background: linear-gradient(135deg, #4cc9f0 0%, #39b8df 100%);
        }
        
        .stat-icon.tickets {
            background: linear-gradient(135deg, #25c685 0%, #13b571 100%);
        }
        
        .stat-icon.users {
            background: linear-gradient(135deg, #f8b830 0%, #f6a819 100%);
        }
        
        .stat-icon.properties {
            background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
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
            padding: 6px 12px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        /* First card breakdown - Cyan */
        .stat-card:nth-child(1) .stat-breakdown span {
            background: rgba(76, 201, 240, 0.12);
            border: 2px solid rgba(76, 201, 240, 0.25);
            color: #0d6b8a;
        }
        
        .stat-card:nth-child(1) .stat-breakdown span:hover {
            background: rgba(76, 201, 240, 0.2);
            border-color: rgba(76, 201, 240, 0.4);
            transform: translateX(4px);
        }
        
        /* Second card breakdown - Green */
        .stat-card:nth-child(2) .stat-breakdown span {
            background: rgba(37, 198, 133, 0.12);
            border: 2px solid rgba(37, 198, 133, 0.25);
            color: #0d7a4f;
        }
        
        .stat-card:nth-child(2) .stat-breakdown span:hover {
            background: rgba(37, 198, 133, 0.2);
            border-color: rgba(37, 198, 133, 0.4);
            transform: translateX(4px);
        }
        
        /* Third card breakdown - Yellow */
        .stat-card:nth-child(3) .stat-breakdown span {
            background: rgba(248, 184, 48, 0.12);
            border: 2px solid rgba(248, 184, 48, 0.25);
            color: #b8820f;
        }
        
        .stat-card:nth-child(3) .stat-breakdown span:hover {
            background: rgba(248, 184, 48, 0.2);
            border-color: rgba(248, 184, 48, 0.4);
            transform: translateX(4px);
        }
        
        /* Fourth card breakdown - Purple with colorful items */
        .stat-card:nth-child(4) .stat-breakdown span {
            background: rgba(155, 89, 182, 0.12);
            border: 2px solid rgba(155, 89, 182, 0.25);
            color: #6c3483;
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
            background: rgba(248, 184, 48, 0.12);
            border-color: rgba(248, 184, 48, 0.25);
            color: #b8820f;
        }
        
        .stat-card:nth-child(4) .stat-breakdown span:nth-child(2) i {
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
            background: rgba(54, 185, 204, 0.15);
            border-color: rgba(54, 185, 204, 0.25);
            color: #7dd3fc;
        }
        
        [data-theme="dark"] .stat-card:nth-child(2) .stat-breakdown span {
            background: rgba(28, 200, 138, 0.15);
            border-color: rgba(28, 200, 138, 0.25);
            color: #6ee7b7;
        }
        
        [data-theme="dark"] .stat-card:nth-child(3) .stat-breakdown span {
            background: rgba(246, 194, 62, 0.15);
            border-color: rgba(246, 194, 62, 0.25);
            color: #fcd34d;
        }
        
        [data-theme="dark"] .stat-card:nth-child(4) .stat-breakdown span:first-child {
            background: rgba(28, 200, 138, 0.15);
            border-color: rgba(28, 200, 138, 0.25);
            color: #6ee7b7;
        }
        
        [data-theme="dark"] .stat-card:nth-child(4) .stat-breakdown span:nth-child(2) {
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
            background: linear-gradient(135deg, rgba(37, 198, 133, 0.12) 0%, rgba(37, 198, 133, 0.05) 100%);
            border-bottom-color: rgba(37, 198, 133, 0.3);
        }
        
        .table thead th:nth-child(1)::after {
            background: linear-gradient(90deg, #25c685 0%, #13b571 100%);
        }
        
        .table thead th:nth-child(2) {
            background: linear-gradient(135deg, rgba(76, 201, 240, 0.12) 0%, rgba(76, 201, 240, 0.05) 100%);
            border-bottom-color: rgba(76, 201, 240, 0.3);
        }
        
        .table thead th:nth-child(2)::after {
            background: linear-gradient(90deg, #4cc9f0 0%, #39b8df 100%);
        }
        
        .table thead th:nth-child(3) {
            background: linear-gradient(135deg, rgba(155, 89, 182, 0.12) 0%, rgba(155, 89, 182, 0.05) 100%);
            border-bottom-color: rgba(155, 89, 182, 0.3);
        }
        
        .table thead th:nth-child(3)::after {
            background: linear-gradient(90deg, #9b59b6 0%, #8e44ad 100%);
        }
        
        .table thead th:nth-child(4) {
            background: linear-gradient(135deg, rgba(248, 184, 48, 0.12) 0%, rgba(248, 184, 48, 0.05) 100%);
            border-bottom-color: rgba(248, 184, 48, 0.3);
        }
        
        .table thead th:nth-child(4)::after {
            background: linear-gradient(90deg, #f8b830 0%, #f6a819 100%);
        }
        
        .table thead th:nth-child(5) {
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.12) 0%, rgba(67, 97, 238, 0.05) 100%);
            border-bottom-color: rgba(67, 97, 238, 0.3);
        }
        
        .table thead th:nth-child(5)::after {
            background: linear-gradient(90deg, #4361ee 0%, #3a56e4 100%);
        }
        
        .table thead th:nth-child(6) {
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.12) 0%, rgba(67, 97, 238, 0.05) 100%);
            border-bottom-color: rgba(67, 97, 238, 0.3);
        }
        
        .table thead th:nth-child(6)::after {
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
            color: #25c685;
        }
        
        .table thead th:nth-child(2).sortable:hover .sort-icon {
            color: #4cc9f0;
        }
        
        .table thead th:nth-child(3).sortable:hover .sort-icon {
            color: #9b59b6;
        }
        
        .table thead th:nth-child(4).sortable:hover .sort-icon {
            color: #f8b830;
        }
        
        .table thead th:nth-child(5).sortable:hover .sort-icon {
            color: #4361ee;
        }
        
        .table th.actions {
            cursor: default;
        }
        
        /* Status indicators with colored backgrounds and white text */
        .status-indicator {
            display: inline-block;
            padding: 0.4rem 0.9rem;
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #ffffff !important;
            text-align: center;
            white-space: nowrap;
        }
        
        .status-indicator.status-danger {
            background-color: #dc3545;
        }
        
        .status-indicator.status-warning {
            background-color: #ffc107;
            color: #000000 !important;
        }
        
        .status-indicator.status-success {
            background-color: #198754;
        }
        
        .status-indicator.status-primary {
            background-color: #4361ee;
        }
        
        .status-indicator.status-secondary {
            background-color: #6c757d;
        }
        
        /* Dark mode - keep white text but adjust backgrounds if needed */
        [data-theme="dark"] .status-indicator.status-danger {
            background-color: #dc3545;
            color: #ffffff !important;
        }
        
        [data-theme="dark"] .status-indicator.status-warning {
            background-color: #ffc107;
            color: #000000 !important;
        }
        
        [data-theme="dark"] .status-indicator.status-success {
            background-color: #198754;
            color: #ffffff !important;
        }
        
        [data-theme="dark"] .status-indicator.status-primary {
            background-color: #4361ee;
            color: #ffffff !important;
        }
        
        [data-theme="dark"] .status-indicator.status-secondary {
            background-color: #6c757d;
            color: #ffffff !important;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/admin-sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <h1><?php echo __("Payment Management"); ?></h1>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon payments">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo __("Total Payments"); ?></h3>
                        <div class="stat-number"><?php echo isset($payment_stats['total_count']) ? $payment_stats['total_count'] : 0; ?></div>
                        <div class="stat-breakdown">
                            <span><?php echo isset($payment_stats['total_amount']) ? number_format($payment_stats['total_amount'], 2) : '0.00'; ?> € <?php echo __("Total"); ?></span>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon tickets">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo __("Paid"); ?></h3>
                        <div class="stat-number"><?php echo isset($status_stats['paid']['count']) ? $status_stats['paid']['count'] : 0; ?></div>
                        <div class="stat-breakdown">
                            <span><?php echo isset($status_stats['paid']['total']) ? number_format($status_stats['paid']['total'], 2) : '0.00'; ?> €</span>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon users">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo __("Pending"); ?></h3>
                        <div class="stat-number"><?php echo isset($status_stats['pending']['count']) ? $status_stats['pending']['count'] : 0; ?></div>
                        <div class="stat-breakdown">
                            <span><?php echo isset($status_stats['pending']['total']) ? number_format($status_stats['pending']['total'], 2) : '0.00'; ?> €</span>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon properties">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo __("Payment Methods"); ?></h3>
                        <div class="stat-number"><?php echo $total; ?> <?php echo __("Total"); ?></div>
                        <div class="stat-breakdown">
                            <span><i class="fas fa-circle"></i> <?php echo __("Credit Card"); ?>: <?php echo isset($method_stats['credit_card']) ? $method_stats['credit_card'] : 0; ?></span>
                            <span><i class="fas fa-circle"></i> <?php echo __("Bank Transfer"); ?>: <?php echo (isset($method_stats['bank_transfer']) ? $method_stats['bank_transfer'] : 0) + (isset($method_stats['transfer']) ? $method_stats['transfer'] : 0); ?></span>
                            <span><i class="fas fa-circle"></i> <?php echo __("Other"); ?>: <?php 
                                $other_count = 0;
                                foreach ($method_stats as $method => $count) {
                                    if (!in_array($method, ['credit_card', 'bank_transfer', 'transfer'])) {
                                        $other_count += $count;
                                    }
                                }
                                echo $other_count;
                            ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card user-filter-card">
                <div class="card-header user-filter-header">
                    <h3><i class="fas fa-filter"></i> <?php echo __("Payment List"); ?></h3>
                    <form id="filter-form" action="payments.php" method="GET" class="filter-form">
                        <div class="filter-wrapper">
                            <div class="search-filter">
                                <div class="search-bar">
                                    <i class="fas fa-search"></i>
                                    <input type="text" placeholder="<?php echo __("Search for payments..."); ?>" name="search" value="<?php echo htmlspecialchars($search); ?>" autocomplete="off" autofocus>
                                </div>
                            </div>
                            <div class="filter-group">
                                <label for="status"><?php echo __("Status"); ?>:</label>
                                <select name="status" id="status" onchange="this.form.submit()">
                                    <option value=""><?php echo __("All Statuses"); ?></option>
                                    <option value="paid" <?php echo $status_filter === 'paid' ? 'selected' : ''; ?>><?php echo __("Paid"); ?></option>
                                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>><?php echo __("Pending"); ?></option>
                                    <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>><?php echo __("Cancelled"); ?></option>
                                    <option value="failed" <?php echo $status_filter === 'failed' ? 'selected' : ''; ?>><?php echo __("Failed"); ?></option>
                                    <option value="refunded" <?php echo $status_filter === 'refunded' ? 'selected' : ''; ?>><?php echo __("Refunded"); ?></option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label for="payment_method"><?php echo __("Method"); ?>:</label>
                                <select name="payment_method" id="payment_method" onchange="this.form.submit()">
                                    <option value=""><?php echo __("All Methods"); ?></option>
                                    <option value="transfer" <?php echo $payment_method_filter === 'transfer' ? 'selected' : ''; ?>><?php echo __("Transfer"); ?></option>
                                    <option value="cheque" <?php echo $payment_method_filter === 'cheque' ? 'selected' : ''; ?>><?php echo __("Check"); ?></option>
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
                            <a href="payments.php" class="reset-link"><?php echo __("Reset Filters"); ?></a>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    <?php if (empty($payments)): ?>
                        <div class="empty-state">
                            <i class="fas fa-credit-card"></i>
                            <p><?php echo __("No payments found. Try adjusting your filters."); ?></p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th class="sortable" data-column="property">
                                            <?php echo __("Property"); ?>
                                            <span class="sort-icon">
                                                <?php if ($sort_column === 'property'): ?>
                                                    <i class="fas fa-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-sort"></i>
                                                <?php endif; ?>
                                            </span>
                                        </th>
                                        <th class="sortable" data-column="amount">
                                            <?php echo __("Amount"); ?>
                                            <span class="sort-icon">
                                                <?php if ($sort_column === 'amount'): ?>
                                                    <i class="fas fa-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-sort"></i>
                                                <?php endif; ?>
                                            </span>
                                        </th>
                                        <th class="sortable" data-column="type">
                                            <?php echo __("Method"); ?>
                                            <span class="sort-icon">
                                                <?php if ($sort_column === 'type'): ?>
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
                                        <th class="sortable" data-column="payment_date">
                                            <?php echo __("Date"); ?>
                                            <span class="sort-icon">
                                                <?php if ($sort_column === 'payment_date'): ?>
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
                                    <?php foreach ($payments as $payment): ?>
                                        <tr>
                                            <td>
                                                <?php if (isset($payment['property_id']) && $payment['property_id']): ?>
                                                    <a href="view-property.php?id=<?php echo $payment['property_id']; ?>" class="property-link">
                                                        <?php echo htmlspecialchars($payment['property_identifier']); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>$<?php echo number_format($payment['amount'], 2); ?></td>
                                            <td>
                                                <?php 
                                                    $methodIcon = '';
                                                    // Check if payment_method exists
                                                    $payment_method = isset($payment['payment_method']) ? $payment['payment_method'] : 'other';
                                                    switch ($payment_method) {
                                                        case 'credit_card': 
                                                            $methodIcon = '<i class="fas fa-credit-card"></i> ';
                                                            break;
                                                        case 'bank_transfer': 
                                                            $methodIcon = '<i class="fas fa-university"></i> ';
                                                            break;
                                                        case 'cash': 
                                                            $methodIcon = '<i class="fas fa-money-bill"></i> ';
                                                            break;
                                                        default: 
                                                            $methodIcon = '<i class="fas fa-money-check"></i> ';
                                                    }
                                                    
                                                    // Only replace if payment_method exists
                                                    $paymentMethodDisplay = $payment_method ? __(ucfirst(str_replace('_', ' ', $payment_method))) : __("Other");
                                                    echo $methodIcon . $paymentMethodDisplay;
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                    $statusClass = '';
                                                    switch ($payment['status']) {
                                                        case 'paid': 
                                                        case 'completed': $statusClass = 'success'; break;
                                                        case 'pending': $statusClass = 'warning'; break;
                                                        case 'failed': $statusClass = 'danger'; break;
                                                        case 'refunded': $statusClass = 'primary'; break;
                                                    }
                                                ?>
                                                <span class="status-indicator status-<?php echo $statusClass; ?>">
                                                    <?php echo __($payment['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d M Y', strtotime($payment['payment_date'])); ?></td>
                                            <td class="actions">
                                                <a href="view-payment.php?id=<?php echo $payment['id']; ?>" class="btn-icon" title="<?php echo __("View Payment"); ?>">
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
                                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&payment_method=<?php echo urlencode($payment_method_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>&sort=<?php echo urlencode($sort_column); ?>&dir=<?php echo urlencode($sort_direction); ?>" class="pagination-link">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&payment_method=<?php echo urlencode($payment_method_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>&sort=<?php echo urlencode($sort_column); ?>&dir=<?php echo urlencode($sort_direction); ?>" 
                                       class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&payment_method=<?php echo urlencode($payment_method_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>&sort=<?php echo urlencode($sort_column); ?>&dir=<?php echo urlencode($sort_direction); ?>" class="pagination-link">
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
            column: urlParams.get('sort') || 'payment_date',
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
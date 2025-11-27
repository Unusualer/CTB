<?php
// Start session
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/role_access.php';
require_once '../includes/translations.php';

// Check if user is logged in and has appropriate role
requireAnyRole(['resident']);


// Check if maintenance ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = __("Maintenance ID is invalid.");
    header("Location: maintenance.php");
    exit();
}

$maintenance_id = intval($_GET['id']);
$error_message = '';
$maintenance = null;
$comments = [];

try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch maintenance update details
    $query = "SELECT m.*, u.name as created_by_name 
              FROM maintenance m 
              LEFT JOIN users u ON m.created_by = u.id 
              WHERE m.id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $maintenance_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $maintenance = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$maintenance) {
        $_SESSION['error'] = __("Maintenance update not found.");
        header("Location: maintenance.php");
        exit();
    }
    
    // Fetch comments for this maintenance update (if we have a comments table)
    // This is a placeholder - you may need to adjust this based on your actual database schema
    $comment_query = "SELECT c.*, u.name as user_name 
                     FROM maintenance_comment c 
                     LEFT JOIN users u ON c.user_id = u.id 
                     WHERE c.maintenance_id = :maintenance_id 
                     ORDER BY c.created_at DESC";
    
    // Only try to fetch comments if the table exists
    try {
        $comment_stmt = $db->prepare($comment_query);
        $comment_stmt->bindParam(':maintenance_id', $maintenance_id, PDO::PARAM_INT);
        $comment_stmt->execute();
        $comments = $comment_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Table might not exist yet, so we'll just leave comments empty
        $comments = [];
    }
    
} catch (PDOException $e) {
    $error_message = __("Database error:") . " " . $e->getMessage();
}

// Helper function to format date
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

// Helper function to get status badge class
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

// Helper function to get priority badge class
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

$page_title = __("View Maintenance");
?>
<!DOCTYPE html>
<html lang="<?php echo isset($_SESSION['language']) ? substr($_SESSION['language'], 0, 2) : 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo __("Community Trust Bank"); ?></title>
    <!-- Favicon -->
    <?php favicon_links(); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin-style.css">
    <style>
        .maintenance-details {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        
        @media (min-width: 768px) {
            .maintenance-details {
                grid-template-columns: 2fr 1fr;
            }
        }
        
        .detail-group {
            margin-bottom: 1rem;
        }
        
        .detail-group .label {
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: var(--text-secondary);
        }
        
        .detail-group .value {
            font-size: 1rem;
        }
        
        .meta-info {
            color: var(--text-secondary);
            font-size: 0.875rem;
            border-top: 1px solid var(--border-color);
            padding-top: 1rem;
            margin-top: 1.5rem;
        }
        
        .comment-list {
            margin-top: 1.5rem;
        }
        
        .comment {
            padding: 1rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            background-color: var(--card-bg);
        }
        
        .comment-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        
        .comment-author {
            font-weight: 600;
        }
        
        .comment-time {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }
        
        .comment-content {
            margin-top: 0.5rem;
            line-height: 1.5;
        }
        
        .description-box {
            background-color: var(--light-color);
            padding: 1.25rem;
            border-radius: 0.5rem;
            border: 1px solid var(--border-color);
            margin: 1rem 0;
            white-space: pre-line;
            line-height: 1.6;
        }
        
        /* Breadcrumb styles */
        .breadcrumb {
            display: flex;
            align-items: center;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            color: var(--text-secondary);
        }
        
        .breadcrumb a {
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.2s ease;
        }
        
        .breadcrumb a:hover {
            color: var(--primary-color-dark);
            text-decoration: underline;
        }
        
        .breadcrumb span:not(:last-child)::after {
            content: '/';
            margin: 0 0.5rem;
            color: var(--text-secondary);
        }
        
        /* Status badge styling */
        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .badge-success {
            background-color: var(--success-light);
            color: var(--success-color);
        }
        
        .badge-info {
            background-color: var(--info-light);
            color: var(--info-color);
        }
        
        .badge-warning {
            background-color: var(--warning-light);
            color: var(--warning-color);
        }
        
        .badge-danger {
            background-color: var(--danger-light);
            color: var(--danger-color);
        }
        
        .badge-primary {
            background-color: var(--primary-light);
            color: var(--primary-color);
        }
        
        .badge-secondary {
            background-color: var(--secondary-light);
            color: var(--secondary-color);
        }
        
        /* Card and metadata styling */
        .card {
            background-color: var(--card-bg);
            border-radius: 0.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
            overflow: hidden;
            transition: box-shadow 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }
        
        .card-header {
            padding: 1.25rem;
            border-bottom: 1px solid var(--border-color);
            background-color: var(--card-header-bg);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-body {
            padding: 1.25rem;
        }
        
        /* Dark mode enhancements */
        [data-theme="dark"] .description-box {
            background-color: rgba(32, 35, 42, 0.7);
            border-color: #3f4756;
            color: #e6e6e6;
        }
        
        [data-theme="dark"] .card {
            background-color: #282c34;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            }
        
        [data-theme="dark"] .card-header {
            background: linear-gradient(to right, var(--primary-color), var(--primary-color-dark));
            border-bottom-color: #3f4756;
        }
        
        [data-theme="dark"] .card-header h3 {
            color: #ffffff;
        }
        
        [data-theme="dark"] .comment {
            background-color: #2a2e35;
            border-color: #3f4756;
        }
        
        [data-theme="dark"] .meta-info {
            border-color: #3f4756;
        }
        
        [data-theme="dark"] .badge-success {
            background-color: rgba(40, 167, 69, 0.2);
            color: #5bea8d;
        }
        
        [data-theme="dark"] .badge-info {
            background-color: rgba(23, 162, 184, 0.2);
            color: #5bdcf0;
        }
        
        [data-theme="dark"] .badge-warning {
            background-color: rgba(255, 193, 7, 0.2);
            color: #ffdf5e;
        }
        
        [data-theme="dark"] .badge-danger {
            background-color: rgba(220, 53, 69, 0.2);
            color: #ff7a8e;
        }
        
        [data-theme="dark"] .badge-primary {
            background-color: rgba(var(--primary-rgb), 0.2);
            color: #93c5fd;
        }
        
        [data-theme="dark"] .badge-secondary {
            background-color: rgba(108, 117, 125, 0.2);
            color: #adb5bd;
        }
        
        [data-theme="dark"] input, 
        [data-theme="dark"] select,
        [data-theme="dark"] textarea {
            background-color: #2a2e35;
            border-color: #3f4756;
            color: #e6e6e6;
        }
        
        [data-theme="dark"] .detail-group .label {
            color: #93c5fd;
        }
        
        [data-theme="dark"] .breadcrumb {
            color: #8e99ad;
        }
        
        [data-theme="dark"] .breadcrumb a {
            color: #93c5fd;
        }
        
        [data-theme="dark"] .alert-danger {
            background-color: rgba(220, 53, 69, 0.15);
            color: #ff6b6b;
            border-color: rgba(220, 53, 69, 0.3);
        }
        
        [data-theme="dark"] .alert-success {
            background-color: rgba(40, 167, 69, 0.15);
            color: #2ecc71;
            border-color: rgba(40, 167, 69, 0.3);
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/admin-sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <div class="breadcrumb">
                    <a href="maintenance.php"><?php echo __("Maintenance"); ?></a>
                    <span><?php echo __("View Maintenance"); ?></span>
                </div>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <div class="card profile-header">
                        <div class="profile-header-content">
                            <div class="profile-avatar">
                                <i class="fas fa-tools"></i>
                            </div>
                    <div class="profile-info">
                        <h1><?php echo htmlspecialchars($maintenance['title']); ?></h1>
                                <div class="profile-meta">
                            <span>
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo htmlspecialchars($maintenance['location']); ?>
                            </span>
                            <span>
                                <i class="fas fa-calendar"></i>
                                <?php echo formatDate($maintenance['start_date']); ?> - <?php echo formatDate($maintenance['end_date']); ?>
                            </span>
                            <span>
                                <i class="fas fa-tag"></i>
                                <span class="status-indicator status-<?php echo getStatusBadgeClass($maintenance['status']); ?>">
                                    <?php echo __(ucfirst(str_replace('_', ' ', $maintenance['status']))); ?>
                                </span>
                            </span>
                            <span>
                                <i class="fas fa-exclamation-circle"></i>
                                <span class="status-indicator status-<?php echo getPriorityBadgeClass($maintenance['priority']); ?>">
                                    <?php echo __(ucfirst($maintenance['priority'])); ?>
                                    </span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

            <div class="maintenance-details">
                <div class="card">
                            <div class="card-header">
                        <h3><i class="fas fa-info-circle"></i> <?php echo __("Maintenance Information"); ?></h3>
                            </div>
                            <div class="card-body">
                                <div class="detail-group">
                            <div class="label"><?php echo __("Description"); ?></div>
                            <div class="description-box"><?php echo nl2br(htmlspecialchars($maintenance['description'])); ?></div>
                                </div>
                                
                        <div class="meta-info">
                            <div>
                                <i class="fas fa-user-edit"></i>
                                <?php echo __("Created by"); ?>: <?php echo htmlspecialchars($maintenance['created_by_name'] ?? 'N/A'); ?>
                            </div>
                            <div>
                                <i class="fas fa-calendar-plus"></i>
                                <?php echo __("Created on"); ?>: <?php echo formatDate($maintenance['created_at']); ?>
                            </div>
                            <?php if (!empty($maintenance['updated_at']) && $maintenance['updated_at'] !== $maintenance['created_at']): ?>
                                <div>
                                    <i class="fas fa-user-edit"></i>
                                    <?php echo __("Updated by"); ?>: <?php echo htmlspecialchars(getUserNameById($maintenance['updated_by'] ?? 0) ?? 'N/A'); ?>
                                    </div>
                                <div>
                                    <i class="fas fa-calendar-check"></i>
                                    <?php echo __("Last updated"); ?>: <?php echo formatDate($maintenance['updated_at']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="js/dark-mode.js"></script>
</body>
</html>

<?php
// Helper function to get user name by ID (if needed)
function getUserNameById($user_id) {
    if (empty($user_id)) return null;
    
    try {
        $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $db->prepare("SELECT name FROM users WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['name'] : null;
    } catch (PDOException $e) {
        return null;
    }
}

<?php
// Start session
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/role_access.php';
require_once '../includes/translations.php';

// Check if user is logged in and has appropriate role
requireRole('admin');


// Initialize variables
$search = $_GET['search'] ?? '';
$user_filter = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$action_filter = $_GET['action'] ?? '';
$entity_type_filter = $_GET['entity_type'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

// Handle sorting
$sort_column = $_GET['sort'] ?? 'created_at';
$sort_direction = $_GET['dir'] ?? 'desc';

// Validate sort column (whitelist allowed columns)
$allowed_columns = ['id', 'action', 'entity_type', 'entity_id', 'created_at'];
if (!in_array($sort_column, $allowed_columns)) {
    $sort_column = 'created_at';
}

// Validate sort direction
if (!in_array(strtolower($sort_direction), ['asc', 'desc'])) {
    $sort_direction = 'desc';
}

// Fetch activity logs with filters
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
    $count_query = "SELECT COUNT(*) as total FROM activity_log a WHERE 1=1";
    $params = [];
    
    // Apply filters
    if (!empty($search)) {
        $query .= " AND (a.description LIKE :search OR u.name LIKE :search)";
        $count_query .= " AND (a.description LIKE :search OR u.name LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    if ($user_filter > 0) {
        $query .= " AND a.user_id = :user_id";
        $count_query .= " AND a.user_id = :user_id";
        $params[':user_id'] = $user_filter;
    }
    
    if (!empty($action_filter)) {
        $query .= " AND a.action = :action";
        $count_query .= " AND a.action = :action";
        $params[':action'] = $action_filter;
    }
    
    if (!empty($entity_type_filter)) {
        $query .= " AND a.entity_type = :entity_type";
        $count_query .= " AND a.entity_type = :entity_type";
        $params[':entity_type'] = $entity_type_filter;
    }
    
    if (!empty($date_from)) {
        $query .= " AND DATE(a.created_at) >= :date_from";
        $count_query .= " AND DATE(a.created_at) >= :date_from";
        $params[':date_from'] = $date_from;
    }
    
    if (!empty($date_to)) {
        $query .= " AND DATE(a.created_at) <= :date_to";
        $count_query .= " AND DATE(a.created_at) <= :date_to";
        $params[':date_to'] = $date_to;
    }
    
    // Add ordering and limit (column name is validated against whitelist, so safe to use)
    $query .= " ORDER BY a.`" . $sort_column . "` " . strtoupper($sort_direction) . " LIMIT :offset, :limit";
    
    // Get total count
    $count_stmt = $db->prepare($count_query);
    foreach ($params as $key => $value) {
        $count_stmt->bindValue($key, $value);
    }
    $count_stmt->execute();
    $total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total / $items_per_page);
    
    // Get activity logs
    $stmt = $db->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->execute();
    $activity_logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get unique actions for filter
    $action_query = "SELECT DISTINCT action FROM activity_log ORDER BY action";
    $action_stmt = $db->prepare($action_query);
    $action_stmt->execute();
    $actions = $action_stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Get unique entity types for filter
    $entity_query = "SELECT DISTINCT entity_type FROM activity_log WHERE entity_type IS NOT NULL ORDER BY entity_type";
    $entity_stmt = $db->prepare($entity_query);
    $entity_stmt->execute();
    $entity_types = $entity_stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Remove any empty strings or null values from entity_types
    $entity_types = array_filter($entity_types, function($value) {
        return !is_null($value) && $value !== '';
    });
    
    // Get users for filter
    $users_query = "SELECT id, name FROM users ORDER BY name";
    $users_stmt = $db->prepare($users_query);
    $users_stmt->execute();
    $users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get activity statistics
    // Total activities count
    $stats_query = "SELECT COUNT(*) as total_count FROM activity_log";
    $stats_stmt = $db->prepare($stats_query);
    $stats_stmt->execute();
    $activity_stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Activity counts by action
    $action_stats_query = "SELECT action, COUNT(*) as count FROM activity_log GROUP BY action";
    $action_stats_stmt = $db->prepare($action_stats_query);
    $action_stats_stmt->execute();
    $action_stats = [];
    while ($row = $action_stats_stmt->fetch(PDO::FETCH_ASSOC)) {
        $action_stats[$row['action']] = $row['count'];
    }
    
    // Activity counts by entity type
    $entity_stats_query = "SELECT entity_type, COUNT(*) as count FROM activity_log GROUP BY entity_type";
    $entity_stats_stmt = $db->prepare($entity_stats_query);
    $entity_stats_stmt->execute();
    $entity_stats = [];
    while ($row = $entity_stats_stmt->fetch(PDO::FETCH_ASSOC)) {
        $entity_stats[$row['entity_type']] = $row['count'];
    }
    
    // Recent activity statistics (last 24 hours)
    $recent_query = "SELECT COUNT(*) as count FROM activity_log WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
    $recent_stmt = $db->prepare($recent_query);
    $recent_stmt->execute();
    $recent_stats = $recent_stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $_SESSION['error'] = __("Database error:") . " " . $e->getMessage();
    $activity_logs = [];
    $total = 0;
    $total_pages = 0;
    $actions = [];
    $entity_types = [];
    $users = [];
    $activity_stats = ['total_count' => 0];
    $action_stats = [];
    $entity_stats = [];
    $recent_stats = ['count' => 0];
}

// Helper function to get appropriate status class for badge
function getActionBadgeClass($action) {
    switch(strtolower($action)) {
        case 'create':
            return 'success';
        case 'update':
            return 'primary';
        case 'delete':
            return 'danger';
        case 'login':
            return 'info';
        case 'logout':
            return 'secondary';
        case 'view':
            return 'warning';
        default:
            return 'primary';
    }
}

// Helper function to get appropriate entity type class for badge
function getEntityTypeBadgeClass($entity_type) {
    // Handle null values
    if ($entity_type === null || $entity_type === '') {
        return 'secondary';
    }
    
    switch(strtolower($entity_type)) {
        case 'user':
            return 'info';
        case 'property':
            return 'success';
        case 'ticket':
            return 'warning';
        case 'payment':
            return 'primary';
        case 'maintenance':
            return 'secondary';
        default:
            return 'secondary';
    }
}

// Helper function to format date based on user's language
function formatDate($date, $format = '') {
    // Get current language
    $language = $_SESSION['language'] ?? 'en_US';
    
    if (empty($date)) {
        return __("Unknown date");
    }
    
    $timestamp = strtotime($date);
    
    // If format is provided, use it
    if (!empty($format)) {
        return date($format, $timestamp);
    }
    
    // Default datetime format based on language
    switch ($language) {
        case 'fr_FR':
            return date('d/m/Y H:i', $timestamp);
        case 'es_ES':
            return date('d/m/Y H:i', $timestamp);
        default:
            return date('M d, Y g:i A', $timestamp);
    }
}

// Page title
$page_title = __("Activity Log");
?>

<!DOCTYPE html>
<html lang="<?php echo substr($_SESSION['language'] ?? 'en_US', 0, 2); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo __("Community Trust Bank"); ?></title>
    <!-- Favicon -->
    <?php favicon_links(); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin-style.css">
    <link rel="stylesheet" href="css/colorful-theme.css">
    <style>
        /* Sortable table header styles */
        .table th.sortable {
            cursor: pointer;
            user-select: none;
            position: relative;
            padding-right: 30px;
            transition: background-color 0.2s;
        }
        
        .table th.sortable:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }
        
        .table th.sortable .sort-icon {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 0.85em;
        }
        
        .table th.sortable:hover .sort-icon {
            color: #007bff;
        }
        
        .table th.sortable[data-sorted="true"] .sort-icon {
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/admin-sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <h1><?php echo __("Activity Log"); ?></h1>
                <div class="page-header-actions">
                    <button class="btn btn-secondary" onclick="exportActivityLog()">
                        <i class="fas fa-download"></i>
                        <?php echo __("Export Log"); ?>
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon activities">
                        <i class="fas fa-list-ul"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo __("Total Activities"); ?></h3>
                        <div class="stat-number"><?php echo isset($activity_stats['total_count']) ? number_format($activity_stats['total_count']) : 0; ?></div>
                        <div class="stat-breakdown">
                            <span><?php echo __("All time"); ?></span>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon recent">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo __("Recent Activity"); ?></h3>
                        <div class="stat-number"><?php echo isset($recent_stats['count']) ? number_format($recent_stats['count']) : 0; ?></div>
                        <div class="stat-breakdown">
                            <span><?php echo __("Last 24 hours"); ?></span>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon actions">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo __("Top Actions"); ?></h3>
                        <div class="stat-breakdown">
                            <?php
                            $top_actions = array_slice($action_stats, 0, 3, true);
                            foreach ($top_actions as $action => $count) {
                                $class = getActionBadgeClass($action);
                                echo "<span><i class='fas fa-circle' style='color: var(--$class-color);'></i> " . __(ucfirst($action ?? '')) . ": $count</span>";
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon entities">
                        <i class="fas fa-database"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo __("Entity Types"); ?></h3>
                        <div class="stat-breakdown">
                            <?php
                            $top_entities = array_slice($entity_stats, 0, 3, true);
                            foreach ($top_entities as $entity => $count) {
                                echo "<span><i class='fas fa-circle'></i> " . __(ucfirst($entity ?? '')) . ": $count</span>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card user-filter-card">
                <div class="card-header user-filter-header">
                    <h3><i class="fas fa-filter"></i> <?php echo __("Activity Log"); ?></h3>
                    <form id="filter-form" action="activity-log.php" method="GET" class="filter-form">
                        <div class="filter-wrapper">
                            <div class="search-filter">
                                <div class="search-bar">
                                    <i class="fas fa-search"></i>
                                    <input type="text" placeholder="<?php echo __("Search logs..."); ?>" name="search" value="<?php echo htmlspecialchars($search); ?>" autocomplete="off" autofocus>
                                </div>
                            </div>
                            <div class="filter-group">
                                <label for="user_id"><?php echo __("User"); ?>:</label>
                                <select name="user_id" id="user_id" onchange="this.form.submit()">
                                    <option value="0"><?php echo __("All Users"); ?></option>
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?php echo $user['id']; ?>" <?php echo $user_filter === $user['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($user['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label for="action"><?php echo __("Action"); ?>:</label>
                                <select name="action" id="action" onchange="this.form.submit()">
                                    <option value=""><?php echo __("All Actions"); ?></option>
                                    <?php foreach ($actions as $action): ?>
                                        <option value="<?php echo $action; ?>" <?php echo $action_filter === $action ? 'selected' : ''; ?>>
                                            <?php echo __(ucfirst($action ?? '')); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label for="entity_type"><?php echo __("Entity Type"); ?>:</label>
                                <select name="entity_type" id="entity_type" onchange="this.form.submit()">
                                    <option value=""><?php echo __("All Types"); ?></option>
                                    <?php foreach ($entity_types as $type): ?>
                                        <option value="<?php echo htmlspecialchars($type); ?>" <?php echo $entity_type_filter === $type ? 'selected' : ''; ?>>
                                            <?php echo __(ucfirst($type)); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label for="date_from"><?php echo __("From"); ?>:</label>
                                <input type="date" id="date_from" name="date_from" value="<?php echo $date_from; ?>" onchange="this.form.submit()">
                            </div>
                            <div class="filter-group">
                                <label for="date_to"><?php echo __("To"); ?>:</label>
                                <input type="date" id="date_to" name="date_to" value="<?php echo $date_to; ?>" onchange="this.form.submit()">
                            </div>
                            <a href="activity-log.php" class="reset-link"><?php echo __("Reset"); ?></a>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    <?php if (empty($activity_logs)): ?>
                        <div class="empty-state">
                            <i class="fas fa-history"></i>
                            <p><?php echo __("No activity logs found. Try adjusting your filters."); ?></p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th class="sortable" data-column="id">
                                            <?php echo __("ID"); ?>
                                            <span class="sort-icon">
                                                <?php if ($sort_column === 'id'): ?>
                                                    <i class="fas fa-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-sort"></i>
                                                <?php endif; ?>
                                            </span>
                                        </th>
                                        <th><?php echo __("User"); ?></th>
                                        <th class="sortable" data-column="action">
                                            <?php echo __("Action"); ?>
                                            <span class="sort-icon">
                                                <?php if ($sort_column === 'action'): ?>
                                                    <i class="fas fa-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-sort"></i>
                                                <?php endif; ?>
                                            </span>
                                        </th>
                                        <th class="sortable" data-column="entity_type">
                                            <?php echo __("Entity Type"); ?>
                                            <span class="sort-icon">
                                                <?php if ($sort_column === 'entity_type'): ?>
                                                    <i class="fas fa-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-sort"></i>
                                                <?php endif; ?>
                                            </span>
                                        </th>
                                        <th class="sortable" data-column="entity_id">
                                            <?php echo __("Entity ID"); ?>
                                            <span class="sort-icon">
                                                <?php if ($sort_column === 'entity_id'): ?>
                                                    <i class="fas fa-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-sort"></i>
                                                <?php endif; ?>
                                            </span>
                                        </th>
                                        <th><?php echo __("Description"); ?></th>
                                        <th class="sortable" data-column="created_at">
                                            <?php echo __("Date & Time"); ?>
                                            <span class="sort-icon">
                                                <?php if ($sort_column === 'created_at'): ?>
                                                    <i class="fas fa-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-sort"></i>
                                                <?php endif; ?>
                                            </span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($activity_logs as $log): ?>
                                        <tr>
                                            <td><?php echo $log['id']; ?></td>
                                            <td>
                                                <?php if (!empty($log['user_name'])): ?>
                                                    <a href="view-user.php?id=<?php echo $log['user_id']; ?>" class="user-link">
                                                        <?php echo htmlspecialchars($log['user_name']); ?>
                                                    </a>
                                                    <div class="text-muted small"><?php echo htmlspecialchars($log['user_email'] ?? ''); ?></div>
                                                <?php else: ?>
                                                    <span class="text-muted"><?php echo __("User ID"); ?>: <?php echo $log['user_id']; ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="status-indicator status-<?php echo getActionBadgeClass($log['action']); ?>">
                                                    <?php echo __(ucfirst($log['action'])); ?>
                                                </span>
                                            </td>
                                            <td><?php echo __(ucfirst($log['entity_type'] ?? '')); ?></td>
                                            <td>
                                                <?php
                                                    $entity_link = '';
                                                    $entity_class = getEntityTypeBadgeClass($log['entity_type']);
                                                    
                                                    switch($log['entity_type']) {
                                                        case 'user':
                                                            $entity_link = "view-user.php?id={$log['entity_id']}";
                                                            $icon = "fa-user";
                                                            break;
                                                        case 'property':
                                                            $entity_link = "view-property.php?id={$log['entity_id']}";
                                                            $icon = "fa-home";
                                                            break;
                                                        case 'ticket':
                                                            $entity_link = "view-ticket.php?id={$log['entity_id']}";
                                                            $icon = "fa-ticket-alt";
                                                            break;
                                                        case 'payment':
                                                            $entity_link = "view-payment.php?id={$log['entity_id']}";
                                                            $icon = "fa-credit-card";
                                                            break;
                                                        case 'maintenance':
                                                            $entity_link = "view-maintenance.php?id={$log['entity_id']}";
                                                            $icon = "fa-tools";
                                                            break;
                                                        default:
                                                            $icon = "fa-circle";
                                                    }
                                                    
                                                    if (!empty($entity_link)) {
                                                        echo "<a href=\"$entity_link\" class=\"entity-badge status-{$entity_class}\">";
                                                        echo "<i class=\"fas {$icon}\"></i> {$log['entity_id']}";
                                                        echo "</a>";
                                                    } else {
                                                        echo "<span class=\"entity-badge status-secondary\">";
                                                        echo "<i class=\"fas {$icon}\"></i> {$log['entity_id']}";
                                                        echo "</span>";
                                                    }
                                                ?>
                                            </td>
                                            <td class="text-wrap"><?php echo htmlspecialchars($log['description'] ?? ''); ?></td>
                                            <td><?php echo formatDate($log['created_at']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&user_id=<?php echo $user_filter; ?>&action=<?php echo urlencode($action_filter); ?>&entity_type=<?php echo urlencode($entity_type_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>&sort=<?php echo urlencode($sort_column); ?>&dir=<?php echo urlencode($sort_direction); ?>" class="pagination-link">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&user_id=<?php echo $user_filter; ?>&action=<?php echo urlencode($action_filter); ?>&entity_type=<?php echo urlencode($entity_type_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>&sort=<?php echo urlencode($sort_column); ?>&dir=<?php echo urlencode($sort_direction); ?>" 
                                       class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&user_id=<?php echo $user_filter; ?>&action=<?php echo urlencode($action_filter); ?>&entity_type=<?php echo urlencode($entity_type_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>&sort=<?php echo urlencode($sort_column); ?>&dir=<?php echo urlencode($sort_direction); ?>" class="pagination-link">
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
            column: urlParams.get('sort') || 'created_at',
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
    </script>
    <style>
        /* Entity badge styles */
        .entity-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.2rem 0.5rem;
            border-radius: 30px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s ease;
            color: var(--text-primary);
            text-decoration: none;
            gap: 5px;
            white-space: nowrap;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        
        .entity-badge i {
            font-size: 0.75rem;
        }
        
        .entity-badge.status-primary {
            background: linear-gradient(135deg, #4361ee 0%, #3a51d4 100%);
            color: #ffffff;
            border: 1px solid #3a51d4;
        }
        
        .entity-badge.status-success {
            background: linear-gradient(135deg, #25c685 0%, #1fa76e 100%);
            color: #ffffff;
            border: 1px solid #1fa76e;
        }
        
        .entity-badge.status-warning {
            background: linear-gradient(135deg, #f8b830 0%, #e5a61c 100%);
            color: #ffffff;
            border: 1px solid #e5a61c;
        }
        
        .entity-badge.status-danger {
            background: linear-gradient(135deg, #e74a3b 0%, #c0392b 100%);
            color: #ffffff;
            border: 1px solid #c0392b;
        }
        
        .entity-badge.status-info {
            background: linear-gradient(135deg, #4cc9f0 0%, #36b5db 100%);
            color: #ffffff;
            border: 1px solid #36b5db;
        }
        
        .entity-badge.status-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
            color: #ffffff;
            border: 1px solid #5a6268;
        }
        
        .entity-badge:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
            opacity: 0.9;
        }
        
        /* Dark mode styles for entity badges */
        [data-theme="dark"] .entity-badge {
            color: #ffffff;
        }
        
        [data-theme="dark"] .entity-badge.status-primary {
            background: linear-gradient(135deg, #4e73df 0%, #3d5fc7 100%);
            color: #ffffff;
            border: 1px solid #3d5fc7;
        }
        
        [data-theme="dark"] .entity-badge.status-success {
            background: linear-gradient(135deg, #1cc88a 0%, #16a085 100%);
            color: #ffffff;
            border: 1px solid #16a085;
        }
        
        [data-theme="dark"] .entity-badge.status-warning {
            background: linear-gradient(135deg, #f6c23e 0%, #e5a61c 100%);
            color: #ffffff;
            border: 1px solid #e5a61c;
        }
        
        [data-theme="dark"] .entity-badge.status-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #ffffff;
            border: 1px solid #dc2626;
        }
        
        [data-theme="dark"] .entity-badge.status-info {
            background: linear-gradient(135deg, #36b9cc 0%, #2a9db0 100%);
            color: #ffffff;
            border: 1px solid #2a9db0;
        }
        
        [data-theme="dark"] .entity-badge.status-secondary {
            background: linear-gradient(135deg, #858796 0%, #6c757d 100%);
            color: #ffffff;
            border: 1px solid #6c757d;
        }
    </style>
    <script>
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
        
        // Export activity log
        function exportActivityLog() {
            // Get current filter parameters
            const params = new URLSearchParams(window.location.search);
            
            // Set export flag
            params.set('export', 'csv');
            
            // Create export URL
            const exportUrl = 'export-activity-log.php?' + params.toString();
            
            // Open in new tab or initiate download
            window.open(exportUrl, '_blank');
        }
    </script>
</body>
</html> 
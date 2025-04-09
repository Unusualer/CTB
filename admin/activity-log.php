<?php
// Start session
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "You must be logged in as an administrator to access this page.";
    header("Location: ../login.php");
    exit();
}

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
        $query .= " AND (a.details LIKE :search OR u.name LIKE :search)";
        $count_query .= " AND (a.details LIKE :search OR u.name LIKE :search)";
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
    
    // Add ordering and limit
    $query .= " ORDER BY a.created_at DESC LIMIT :offset, :limit";
    
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
    $entity_query = "SELECT DISTINCT entity_type FROM activity_log ORDER BY entity_type";
    $entity_stmt = $db->prepare($entity_query);
    $entity_stmt->execute();
    $entity_types = $entity_stmt->fetchAll(PDO::FETCH_COLUMN);
    
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
    $_SESSION['error'] = "Database error: " . $e->getMessage();
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

// Page title
$page_title = "Activity Log";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Community Trust Bank</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../assets/images/logo.png" alt="CTB Logo" class="logo">
                <h2>CTB Admin</h2>
            </div>
            
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="user-details">
                    <h4><?php echo htmlspecialchars($_SESSION['name']); ?></h4>
                    <p>Administrator</p>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <a href="dashboard.php">
                            <i class="fas fa-chart-line"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="users.php">
                            <i class="fas fa-users"></i>
                            <span>Users</span>
                        </a>
                    </li>
                    <li>
                        <a href="properties.php">
                            <i class="fas fa-building"></i>
                            <span>Properties</span>
                        </a>
                    </li>
                    <li>
                        <a href="tickets.php">
                            <i class="fas fa-ticket-alt"></i>
                            <span>Tickets</span>
                        </a>
                    </li>
                    <li>
                        <a href="payments.php">
                            <i class="fas fa-credit-card"></i>
                            <span>Payments</span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="activity-log.php">
                            <i class="fas fa-history"></i>
                            <span>Activity Log</span>
                        </a>
                    </li>
                    <li>
                        <a href="maintenance-new.php">
                            <i class="fas fa-tools"></i>
                            <span>Maintenance</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <div class="theme-toggle">
                    <i class="fas fa-moon"></i>
                    <label class="switch">
                        <input type="checkbox" id="darkModeToggle">
                        <span class="slider round"></span>
                    </label>
                </div>
                <a href="../logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="topbar">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search activity logs..." form="filter-form" name="search" value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="topbar-right">
                    <div class="notifications">
                        <i class="fas fa-bell"></i>
                        <span class="badge">3</span>
                    </div>
                    <div class="messages">
                        <i class="fas fa-envelope"></i>
                        <span class="badge">5</span>
                    </div>
                </div>
            </header>

            <div class="page-header">
                <h1>Activity Log</h1>
                <div class="page-header-actions">
                    <button class="btn btn-secondary" onclick="exportActivityLog()">
                        <i class="fas fa-download"></i>
                        Export Log
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
                        <h3>Total Activities</h3>
                        <div class="stat-number"><?php echo isset($activity_stats['total_count']) ? number_format($activity_stats['total_count']) : 0; ?></div>
                        <div class="stat-breakdown">
                            <span>All time</span>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon recent">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-details">
                        <h3>Recent Activity</h3>
                        <div class="stat-number"><?php echo isset($recent_stats['count']) ? number_format($recent_stats['count']) : 0; ?></div>
                        <div class="stat-breakdown">
                            <span>Last 24 hours</span>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon actions">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div class="stat-details">
                        <h3>Top Actions</h3>
                        <div class="stat-breakdown">
                            <?php
                            $top_actions = array_slice($action_stats, 0, 3, true);
                            foreach ($top_actions as $action => $count) {
                                $class = getActionBadgeClass($action);
                                echo "<span><i class='fas fa-circle' style='color: var(--status-$class);'></i> " . ucfirst($action) . ": $count</span>";
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
                        <h3>Entity Types</h3>
                        <div class="stat-breakdown">
                            <?php
                            $top_entities = array_slice($entity_stats, 0, 3, true);
                            foreach ($top_entities as $entity => $count) {
                                echo "<span><i class='fas fa-circle'></i> " . ucfirst($entity) . ": $count</span>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card">
                <div class="card-header user-filter-header">
                    <h3>Activity Log</h3>
                    <form id="filter-form" action="activity-log.php" method="GET" class="filter-form">
                        <div class="filter-group">
                            <label for="user_id">User:</label>
                            <select name="user_id" id="user_id" onchange="this.form.submit()">
                                <option value="0">All Users</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?php echo $user['id']; ?>" <?php echo $user_filter === $user['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($user['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="action">Action:</label>
                            <select name="action" id="action" onchange="this.form.submit()">
                                <option value="">All Actions</option>
                                <?php foreach ($actions as $action): ?>
                                    <option value="<?php echo $action; ?>" <?php echo $action_filter === $action ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($action); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="entity_type">Entity Type:</label>
                            <select name="entity_type" id="entity_type" onchange="this.form.submit()">
                                <option value="">All Types</option>
                                <?php foreach ($entity_types as $type): ?>
                                    <option value="<?php echo $type; ?>" <?php echo $entity_type_filter === $type ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($type); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="date_from">From:</label>
                            <input type="date" id="date_from" name="date_from" value="<?php echo $date_from; ?>">
                        </div>
                        <div class="filter-group">
                            <label for="date_to">To:</label>
                            <input type="date" id="date_to" name="date_to" value="<?php echo $date_to; ?>">
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                        <a href="activity-log.php" class="reset-link">Reset</a>
                    </form>
                </div>
                <div class="card-body">
                    <?php if (empty($activity_logs)): ?>
                        <div class="empty-state">
                            <i class="fas fa-history"></i>
                            <p>No activity logs found. Try adjusting your filters.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Action</th>
                                        <th>Entity Type</th>
                                        <th>Entity ID</th>
                                        <th>Details</th>
                                        <th>Date & Time</th>
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
                                                    <span class="text-muted">User ID: <?php echo $log['user_id']; ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="status-indicator status-<?php echo getActionBadgeClass($log['action']); ?>">
                                                    <?php echo ucfirst($log['action']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo ucfirst($log['entity_type']); ?></td>
                                            <td>
                                                <?php
                                                    $entity_link = '';
                                                    switch($log['entity_type']) {
                                                        case 'user':
                                                            $entity_link = "view-user.php?id={$log['entity_id']}";
                                                            break;
                                                        case 'property':
                                                            $entity_link = "view-property.php?id={$log['entity_id']}";
                                                            break;
                                                        case 'ticket':
                                                            $entity_link = "ticket-details.php?id={$log['entity_id']}";
                                                            break;
                                                        case 'payment':
                                                            $entity_link = "view-payment.php?id={$log['entity_id']}";
                                                            break;
                                                    }
                                                    
                                                    if (!empty($entity_link)) {
                                                        echo "<a href=\"$entity_link\">{$log['entity_id']}</a>";
                                                    } else {
                                                        echo $log['entity_id'];
                                                    }
                                                ?>
                                            </td>
                                            <td class="text-wrap"><?php echo htmlspecialchars($log['details']); ?></td>
                                            <td><?php echo date('M d, Y g:i A', strtotime($log['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&user_id=<?php echo $user_filter; ?>&action=<?php echo urlencode($action_filter); ?>&entity_type=<?php echo urlencode($entity_type_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>" class="pagination-link">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&user_id=<?php echo $user_filter; ?>&action=<?php echo urlencode($action_filter); ?>&entity_type=<?php echo urlencode($entity_type_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>" 
                                       class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&user_id=<?php echo $user_filter; ?>&action=<?php echo urlencode($action_filter); ?>&entity_type=<?php echo urlencode($entity_type_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>" class="pagination-link">
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

    <script>
        // Dark mode toggle
        const darkModeToggle = document.getElementById('darkModeToggle');
        
        // Check for saved dark mode preference
        if (localStorage.getItem('darkMode') === 'enabled') {
            document.body.classList.add('dark-mode');
            darkModeToggle.checked = true;
        }
        
        // Dark mode toggle event listener
        darkModeToggle.addEventListener('change', function() {
            if (this.checked) {
                document.body.classList.add('dark-mode');
                localStorage.setItem('darkMode', 'enabled');
            } else {
                document.body.classList.remove('dark-mode');
                localStorage.setItem('darkMode', null);
            }
        });
        
        // Date filters functionality
        const dateFrom = document.getElementById('date_from');
        const dateTo = document.getElementById('date_to');
        
        dateFrom.addEventListener('change', function() {
            if (dateTo.value && new Date(this.value) > new Date(dateTo.value)) {
                dateTo.value = this.value;
            }
        });
        
        dateTo.addEventListener('change', function() {
            if (dateFrom.value && new Date(this.value) < new Date(dateFrom.value)) {
                dateFrom.value = this.value;
            }
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
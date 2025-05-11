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
$status_filter = $_GET['status'] ?? '';
$priority_filter = $_GET['priority'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

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
    
    // Build the query
    $query = "SELECT m.*, u.name as created_by_name 
              FROM maintenance m 
              LEFT JOIN users u ON m.created_by = u.id 
              WHERE 1=1";
    $count_query = "SELECT COUNT(*) as total FROM maintenance m WHERE 1=1";
    $params = [];
    
    // Apply filters
    if (!empty($search)) {
        $query .= " AND (m.title LIKE :search OR m.description LIKE :search OR m.location LIKE :search)";
        $count_query .= " AND (m.title LIKE :search OR m.description LIKE :search OR m.location LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    if (!empty($status_filter)) {
        $query .= " AND m.status = :status";
        $count_query .= " AND m.status = :status";
        $params[':status'] = $status_filter;
    }
    
    if (!empty($priority_filter)) {
        $query .= " AND m.priority = :priority";
        $count_query .= " AND m.priority = :priority";
        $params[':priority'] = $priority_filter;
    }
    
    if (!empty($date_from)) {
        $query .= " AND m.start_date >= :date_from";
        $count_query .= " AND m.start_date >= :date_from";
        $params[':date_from'] = $date_from;
    }
    
    if (!empty($date_to)) {
        $query .= " AND m.start_date <= :date_to";
        $count_query .= " AND m.start_date <= :date_to";
        $params[':date_to'] = $date_to;
    }
    
    // Add ordering and limit
    $query .= " ORDER BY m.start_date DESC LIMIT :offset, :limit";
    
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
    <title><?php echo $page_title; ?> - <?php echo __("Community Trust Bank"); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/admin-sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <h1><?php echo __("Maintenance Updates"); ?></h1>
                <a href="add-maintenance.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <?php echo __("Add Maintenance Update"); ?>
                </a>
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
                            <span><i class="fas fa-circle" style="color: var(--status-info);"></i> <?php echo __("Scheduled"); ?>: <?php echo isset($status_stats['scheduled']) ? $status_stats['scheduled'] : 0; ?></span>
                            <span><i class="fas fa-circle" style="color: var(--status-primary);"></i> <?php echo __("In Progress"); ?>: <?php echo isset($status_stats['in_progress']) ? $status_stats['in_progress'] : 0; ?></span>
                            <span><i class="fas fa-circle" style="color: var(--status-success);"></i> <?php echo __("Completed"); ?>: <?php echo isset($status_stats['completed']) ? $status_stats['completed'] : 0; ?></span>
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
                            <span><i class="fas fa-circle" style="color: var(--priority-success);"></i> <?php echo __("Low"); ?>: <?php echo isset($priority_stats['low']) ? $priority_stats['low'] : 0; ?></span>
                            <span><i class="fas fa-circle" style="color: var(--priority-info);"></i> <?php echo __("Medium"); ?>: <?php echo isset($priority_stats['medium']) ? $priority_stats['medium'] : 0; ?></span>
                            <span><i class="fas fa-circle" style="color: var(--priority-warning);"></i> <?php echo __("High"); ?>: <?php echo isset($priority_stats['high']) ? $priority_stats['high'] : 0; ?></span>
                            <span><i class="fas fa-circle" style="color: var(--priority-danger);"></i> <?php echo __("Emergency"); ?>: <?php echo isset($priority_stats['emergency']) ? $priority_stats['emergency'] : 0; ?></span>
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
                            <p><?php echo __("No updates found. Try adjusting your filters or add a new update."); ?></p>
                            <a href="add-maintenance.php" class="btn btn-primary"><?php echo __("Add Maintenance Update"); ?></a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th><?php echo __("ID"); ?></th>
                                        <th><?php echo __("Title"); ?></th>
                                        <th><?php echo __("Location"); ?></th>
                                        <th><?php echo __("Status"); ?></th>
                                        <th><?php echo __("Priority"); ?></th>
                                        <th><?php echo __("Start Date"); ?></th>
                                        <th><?php echo __("End Date"); ?></th>
                                        <th><?php echo __("Actions"); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($maintenance_items as $update): ?>
                                        <tr>
                                            <td class="align-middle"><?php echo $update['id']; ?></td>
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
                                                <a href="edit-maintenance.php?id=<?php echo $update['id']; ?>" class="btn-icon" title="<?php echo __("Edit Update"); ?>">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn-icon delete-maintenance" data-id="<?php echo $update['id']; ?>" title="<?php echo __("Delete Update"); ?>">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
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
                                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&priority=<?php echo urlencode($priority_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>" class="pagination-link">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&priority=<?php echo urlencode($priority_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>" 
                                       class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&priority=<?php echo urlencode($priority_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>" class="pagination-link">
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

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><?php echo __("Confirm Deletion"); ?></h3>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <p><?php echo __("Are you sure you want to delete this maintenance update? This action cannot be undone."); ?></p>
            </div>
            <div class="modal-footer">
                <form id="deleteForm" action="delete-maintenance.php" method="POST">
                    <input type="hidden" name="maintenance_id" id="deleteMaintenanceId">
                    <button type="button" class="btn btn-secondary close-modal"><?php echo __("Cancel"); ?></button>
                    <button type="submit" class="btn btn-danger"><?php echo __("Delete"); ?></button>
                </form>
            </div>
        </div>
    </div>

    <script src="js/dark-mode.js"></script>
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
        
        // Delete maintenance modal functionality
        const modal = document.getElementById('deleteModal');
        const deleteButtons = document.querySelectorAll('.delete-maintenance');
        const closeButtons = document.querySelectorAll('.close, .close-modal');
        const deleteForm = document.getElementById('deleteForm');
        const deleteMaintenanceIdInput = document.getElementById('deleteMaintenanceId');
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const maintenanceId = this.getAttribute('data-id');
                deleteMaintenanceIdInput.value = maintenanceId;
                modal.style.display = 'block';
            });
        });
        
        closeButtons.forEach(button => {
            button.addEventListener('click', function() {
                modal.style.display = 'none';
            });
        });
        
        window.addEventListener('click', function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        });
    </script>
</body>
</html> 
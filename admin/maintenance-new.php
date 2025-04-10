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

// Handle delete action
if (isset($_POST['delete']) && isset($_POST['maintenance_id'])) {
    $id = intval($_POST['maintenance_id']);
    try {
        $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $delete_query = "DELETE FROM maintenance_updates WHERE id = :id";
        $delete_stmt = $db->prepare($delete_query);
        $delete_stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        if ($delete_stmt->execute()) {
            // Log the activity
            logActivity($db, $_SESSION['user_id'], 'delete', 'maintenance', $id, 'Deleted maintenance update');
            $success_message = "Maintenance update deleted successfully.";
        } else {
            $error_message = "Failed to delete maintenance update.";
        }
    } catch (PDOException $e) {
        $error_message = "Database error: " . $e->getMessage();
    }
}

// Fetch maintenance updates with filters
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Build the query
    $query = "SELECT m.*, u.name as created_by_name 
              FROM maintenance_updates m 
              LEFT JOIN users u ON m.created_by = u.id 
              WHERE 1=1";
    $count_query = "SELECT COUNT(*) as total FROM maintenance_updates m WHERE 1=1";
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
    $maintenance_updates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get statistics
    // Status statistics
    $status_query = "SELECT status, COUNT(*) as count FROM maintenance_updates GROUP BY status";
    $status_stmt = $db->prepare($status_query);
    $status_stmt->execute();
    $status_stats = [];
    while ($row = $status_stmt->fetch(PDO::FETCH_ASSOC)) {
        $status_stats[$row['status']] = $row['count'];
    }
    
    // Priority statistics
    $priority_query = "SELECT priority, COUNT(*) as count FROM maintenance_updates GROUP BY priority";
    $priority_stmt = $db->prepare($priority_query);
    $priority_stmt->execute();
    $priority_stats = [];
    while ($row = $priority_stmt->fetch(PDO::FETCH_ASSOC)) {
        $priority_stats[$row['priority']] = $row['count'];
    }
    
    // Scheduled for next 7 days
    $upcoming_query = "SELECT COUNT(*) as count FROM maintenance_updates 
                      WHERE start_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
    $upcoming_stmt = $db->prepare($upcoming_query);
    $upcoming_stmt->execute();
    $upcoming_stats = $upcoming_stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
    $maintenance_updates = [];
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

$page_title = "Maintenance Updates";
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
                    <li>
                        <a href="activity-log.php">
                            <i class="fas fa-history"></i>
                            <span>Activity Log</span>
                        </a>
                    </li>
                    <li class="active">
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
            <div class="page-header">
                <h1>Maintenance Updates</h1>
                <a href="add-maintenance.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Add Maintenance Update
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
                        <h3>Total Updates</h3>
                        <div class="stat-number"><?php echo number_format($total); ?></div>
                        <div class="stat-breakdown">
                            <span>All time</span>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon upcoming">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-details">
                        <h3>Upcoming</h3>
                        <div class="stat-number"><?php echo isset($upcoming_stats['count']) ? number_format($upcoming_stats['count']) : 0; ?></div>
                        <div class="stat-breakdown">
                            <span>Next 7 days</span>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon status">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <div class="stat-details">
                        <h3>Status</h3>
                        <div class="stat-breakdown">
                            <span><i class="fas fa-circle" style="color: var(--status-info);"></i> Scheduled: <?php echo isset($status_stats['scheduled']) ? $status_stats['scheduled'] : 0; ?></span>
                            <span><i class="fas fa-circle" style="color: var(--status-primary);"></i> In Progress: <?php echo isset($status_stats['in_progress']) ? $status_stats['in_progress'] : 0; ?></span>
                            <span><i class="fas fa-circle" style="color: var(--status-success);"></i> Completed: <?php echo isset($status_stats['completed']) ? $status_stats['completed'] : 0; ?></span>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon priority">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-details">
                        <h3>Priority</h3>
                        <div class="stat-breakdown">
                            <span><i class="fas fa-circle" style="color: var(--status-danger);"></i> Emergency: <?php echo isset($priority_stats['emergency']) ? $priority_stats['emergency'] : 0; ?></span>
                            <span><i class="fas fa-circle" style="color: var(--status-warning);"></i> High: <?php echo isset($priority_stats['high']) ? $priority_stats['high'] : 0; ?></span>
                            <span><i class="fas fa-circle" style="color: var(--status-info);"></i> Medium: <?php echo isset($priority_stats['medium']) ? $priority_stats['medium'] : 0; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card user-filter-card">
                <div class="card-header user-filter-header">
                    <h3><i class="fas fa-filter"></i> Maintenance List</h3>
                    <form id="filter-form" action="maintenance-new.php" method="GET" class="filter-form">
                        <div class="filter-wrapper">
                            <div class="search-filter">
                                <div class="search-bar">
                                    <i class="fas fa-search"></i>
                                    <input type="text" placeholder="Search maintenance updates..." name="search" value="<?php echo htmlspecialchars($search); ?>" autocomplete="off" autofocus>
                                </div>
                            </div>
                            <div class="filter-group">
                                <label for="status">Status:</label>
                                <select name="status" id="status" onchange="this.form.submit()">
                                    <option value="">All Statuses</option>
                                    <option value="scheduled" <?php echo $status_filter === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                                    <option value="in_progress" <?php echo $status_filter === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="delayed" <?php echo $status_filter === 'delayed' ? 'selected' : ''; ?>>Delayed</option>
                                    <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label for="priority">Priority:</label>
                                <select name="priority" id="priority" onchange="this.form.submit()">
                                    <option value="">All Priorities</option>
                                    <option value="low" <?php echo $priority_filter === 'low' ? 'selected' : ''; ?>>Low</option>
                                    <option value="medium" <?php echo $priority_filter === 'medium' ? 'selected' : ''; ?>>Medium</option>
                                    <option value="high" <?php echo $priority_filter === 'high' ? 'selected' : ''; ?>>High</option>
                                    <option value="emergency" <?php echo $priority_filter === 'emergency' ? 'selected' : ''; ?>>Emergency</option>
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
                            <a href="maintenance-new.php" class="reset-link">Reset</a>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    <?php if (empty($maintenance_updates)): ?>
                        <div class="empty-state">
                            <i class="fas fa-tools"></i>
                            <p>No maintenance updates found. Try adjusting your filters or add a new maintenance update.</p>
                            <a href="add-maintenance.php" class="btn btn-primary">Add Maintenance Update</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Location</th>
                                        <th>Dates</th>
                                        <th>Status</th>
                                        <th>Priority</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($maintenance_updates as $update): ?>
                                        <tr>
                                            <td><?php echo $update['id']; ?></td>
                                            <td><?php echo htmlspecialchars($update['title']); ?></td>
                                            <td><?php echo htmlspecialchars($update['location']); ?></td>
                                            <td>
                                                <div><strong>Start:</strong> <?php echo date('M d, Y', strtotime($update['start_date'])); ?></div>
                                                <div><strong>End:</strong> <?php echo date('M d, Y', strtotime($update['end_date'])); ?></div>
                                            </td>
                                            <td>
                                                <span class="status-indicator status-<?php echo getStatusBadgeClass($update['status']); ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $update['status'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="status-indicator status-<?php echo getPriorityBadgeClass($update['priority']); ?>">
                                                    <?php echo ucfirst($update['priority']); ?>
                                                </span>
                                            </td>
                                            <td class="actions">
                                                <a href="view-maintenance.php?id=<?php echo $update['id']; ?>" class="btn-icon" title="View Maintenance Update">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="edit-maintenance.php?id=<?php echo $update['id']; ?>" class="btn-icon" title="Edit Maintenance Update">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="javascript:void(0);" class="btn-icon delete-maintenance" data-id="<?php echo $update['id']; ?>" title="Delete Maintenance Update">
                                                    <i class="fas fa-trash-alt"></i>
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
                <h3>Confirm Deletion</h3>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this maintenance update? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <form id="deleteForm" action="maintenance-new.php" method="POST">
                    <input type="hidden" name="maintenance_id" id="deleteMaintenanceId">
                    <input type="hidden" name="delete" value="1">
                    <button type="button" class="btn btn-secondary close-modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
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
        });
        
        dateTo.addEventListener('change', function() {
            if (dateFrom.value && new Date(this.value) < new Date(dateFrom.value)) {
                dateFrom.value = this.value;
            }
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
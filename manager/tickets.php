<?php
// Start session
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/role_access.php';
require_once '../includes/translations.php';

// Check if user is logged in and has appropriate role
requireAnyRole(['admin', 'manager']);


// Initialize variables
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$priority_filter = $_GET['priority'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

// Handle sorting
$sort_column = $_GET['sort'] ?? 'created_at';
$sort_direction = $_GET['dir'] ?? 'desc';

// Validate sort column (whitelist allowed columns)
$allowed_columns = ['id', 'subject', 'status', 'priority', 'created_at'];
if (!in_array($sort_column, $allowed_columns)) {
    $sort_column = 'created_at';
}

// Validate sort direction
if (!in_array(strtolower($sort_direction), ['asc', 'desc'])) {
    $sort_direction = 'desc';
}

// Get total count and tickets list
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Build the query - select tickets with user info
    $query = "SELECT t.*, u.name as user_name, u.email as user_email 
              FROM tickets t 
              LEFT JOIN users u ON t.user_id = u.id
              WHERE 1=1";
    $count_query = "SELECT COUNT(*) as total FROM tickets t LEFT JOIN users u ON t.user_id = u.id WHERE 1=1";
    $params = [];
    
    // Apply filters
    if (!empty($search)) {
        $query .= " AND (t.subject LIKE :search OR t.description LIKE :search OR u.name LIKE :search)";
        $count_query .= " AND (t.subject LIKE :search OR t.description LIKE :search OR u.name LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    if (!empty($status_filter)) {
        $query .= " AND t.status = :status";
        $count_query .= " AND t.status = :status";
        $params[':status'] = $status_filter;
    }
    
    if (!empty($priority_filter)) {
        $query .= " AND t.priority = :priority";
        $count_query .= " AND t.priority = :priority";
        $params[':priority'] = $priority_filter;
    }
    
    // Add ordering (column name is validated against whitelist, so safe to use)
    $query .= " ORDER BY t.`" . $sort_column . "` " . strtoupper($sort_direction) . " LIMIT :offset, :limit";
    
    // Get total count
    $count_stmt = $db->prepare($count_query);
    foreach ($params as $key => $value) {
        $count_stmt->bindValue($key, $value);
    }
    $count_stmt->execute();
    $total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total / $items_per_page);
    
    // Get tickets
    $stmt = $db->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->execute();
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get counts by status for stats
    $status_counts = [];
    $status_stmt = $db->prepare("SELECT status, COUNT(*) as count FROM tickets GROUP BY status");
    $status_stmt->execute();
    $status_results = $status_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($status_results as $status_data) {
        $status_counts[$status_data['status']] = $status_data['count'];
    }
    
} catch (PDOException $e) {
    $_SESSION['error'] = __("Database error:") . " " . $e->getMessage();
    $tickets = [];
    $total = 0;
    $total_pages = 0;
    $status_counts = ['open' => 0, 'in_progress' => 0, 'closed' => 0, 'reopened' => 0];
}

// Page title
$page_title = __("Ticket Management");
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
        
        .table th.actions {
            cursor: default;
        }
        
        /* Status and Priority indicators with colored backgrounds and white text */
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
                <h1><?php echo __("Ticket Management"); ?></h1>
                <a href="add-ticket.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <?php echo __("Create New Ticket"); ?>
                </a>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                        echo $_SESSION['success']; 
                        unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                        echo $_SESSION['error']; 
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon tickets">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo __("Open"); ?></h3>
                        <div class="stat-number"><?php echo isset($status_counts['open']) ? $status_counts['open'] : 0; ?></div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon users">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo __("In Progress"); ?></h3>
                        <div class="stat-number"><?php echo isset($status_counts['in_progress']) ? $status_counts['in_progress'] : 0; ?></div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon properties">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo __("Closed"); ?></h3>
                        <div class="stat-number"><?php echo isset($status_counts['closed']) ? $status_counts['closed'] : 0; ?></div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon payments">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo __("Reopened"); ?></h3>
                        <div class="stat-number"><?php echo isset($status_counts['reopened']) ? $status_counts['reopened'] : 0; ?></div>
                        <div class="stat-breakdown">
                            <span><?php echo __("Total Tickets"); ?>: <?php echo $total; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card user-filter-card">
                <div class="card-header user-filter-header">
                    <h3><i class="fas fa-filter"></i> <?php echo __("Ticket List"); ?></h3>
                    <form id="filter-form" action="tickets.php" method="GET" class="filter-form">
                        <div class="filter-wrapper">
                            <div class="search-filter">
                                <div class="search-bar">
                                    <i class="fas fa-search"></i>
                                    <input type="text" placeholder="<?php echo __("Search..."); ?>" name="search" value="<?php echo htmlspecialchars($search); ?>" autocomplete="off" autofocus>
                                </div>
                            </div>
                            <div class="filter-group">
                                <label for="status"><?php echo __("Status"); ?>:</label>
                                <select name="status" id="status" onchange="this.form.submit()">
                                    <option value=""><?php echo __("All Statuses"); ?></option>
                                    <option value="open" <?php echo $status_filter === 'open' ? 'selected' : ''; ?>><?php echo __("Open"); ?></option>
                                    <option value="in_progress" <?php echo $status_filter === 'in_progress' ? 'selected' : ''; ?>><?php echo __("In Progress"); ?></option>
                                    <option value="closed" <?php echo $status_filter === 'closed' ? 'selected' : ''; ?>><?php echo __("Closed"); ?></option>
                                    <option value="reopened" <?php echo $status_filter === 'reopened' ? 'selected' : ''; ?>><?php echo __("Reopened"); ?></option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label for="priority"><?php echo __("Priority"); ?>:</label>
                                <select name="priority" id="priority" onchange="this.form.submit()">
                                    <option value=""><?php echo __("All Priorities"); ?></option>
                                    <option value="low" <?php echo $priority_filter === 'low' ? 'selected' : ''; ?>><?php echo __("Low"); ?></option>
                                    <option value="medium" <?php echo $priority_filter === 'medium' ? 'selected' : ''; ?>><?php echo __("Medium"); ?></option>
                                    <option value="high" <?php echo $priority_filter === 'high' ? 'selected' : ''; ?>><?php echo __("High"); ?></option>
                                    <option value="urgent" <?php echo $priority_filter === 'urgent' ? 'selected' : ''; ?>><?php echo __("Urgent"); ?></option>
                                </select>
                            </div>
                            <a href="tickets.php" class="reset-link"><?php echo __("Reset"); ?></a>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    <?php if (empty($tickets)): ?>
                        <div class="empty-state">
                            <i class="fas fa-ticket-alt"></i>
                            <p><?php echo __("No tickets found. Try adjusting your filters or add a new ticket."); ?></p>
                            <a href="add-ticket.php" class="btn btn-primary"><?php echo __("Add Ticket"); ?></a>
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
                                        <th class="sortable" data-column="subject">
                                            <?php echo __("Title"); ?>
                                            <span class="sort-icon">
                                                <?php if ($sort_column === 'subject'): ?>
                                                    <i class="fas fa-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-sort"></i>
                                                <?php endif; ?>
                                            </span>
                                        </th>
                                        <th><?php echo __("Property"); ?></th>
                                        <th><?php echo __("Reported By"); ?></th>
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
                                        <th class="sortable" data-column="created_at">
                                            <?php echo __("Created"); ?>
                                            <span class="sort-icon">
                                                <?php if ($sort_column === 'created_at'): ?>
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
                                    <?php foreach ($tickets as $ticket): ?>
                                        <tr>
                                            <td><?php echo $ticket['id']; ?></td>
                                            <td>
                                                <div class="ticket-cell">
                                                    <?php echo htmlspecialchars($ticket['subject']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-muted"><?php echo __("N/A"); ?></span>
                                            </td>
                                            <td>
                                                <?php if (isset($ticket['user_name']) && !empty($ticket['user_name'])): ?>
                                                    <div class="user-info-cell">
                                                        <div class="user-avatar">
                                                            <i class="fas fa-user"></i>
                                                        </div>
                                                        <div class="user-details">
                                                            <a href="view-user.php?id=<?php echo $ticket['user_id']; ?>" class="user-name">
                                                                <?php echo htmlspecialchars($ticket['user_name']); ?>
                                                            </a>
                                                            <?php if (isset($ticket['user_email'])): ?>
                                                                <span class="user-email"><?php echo htmlspecialchars($ticket['user_email']); ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="user-info-cell">
                                                        <div class="user-avatar unknown">
                                                            <i class="fas fa-user-slash"></i>
                                                        </div>
                                                        <div class="user-details">
                                                            <span class="user-unknown"><?php echo __("Unknown User"); ?></span>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                    $statusClass = '';
                                                    switch ($ticket['status']) {
                                                        case 'open': $statusClass = 'danger'; break;
                                                        case 'in_progress': $statusClass = 'warning'; break;
                                                        case 'closed': $statusClass = 'success'; break;
                                                        case 'reopened': $statusClass = 'primary'; break;
                                                    }
                                                ?>
                                                <span class="status-indicator status-<?php echo $statusClass; ?>">
                                                    <?php 
                                                        $statusLabels = [
                                                            'open' => __("Open"),
                                                            'in_progress' => __("In Progress"),
                                                            'closed' => __("Closed"),
                                                            'reopened' => __("Reopened")
                                                        ];
                                                        echo $statusLabels[$ticket['status']] ?? ucfirst($ticket['status']);
                                                    ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!empty($ticket['priority'])): ?>
                                                    <?php 
                                                        $priorityClass = '';
                                                        switch ($ticket['priority']) {
                                                            case 'low': $priorityClass = 'success'; break;
                                                            case 'medium': $priorityClass = 'warning'; break;
                                                            case 'high': $priorityClass = 'danger'; break;
                                                            case 'urgent': $priorityClass = 'danger'; break;
                                                            default: $priorityClass = 'secondary';
                                                        }
                                                        
                                                        $priorityLabels = [
                                                            'low' => __("Low"),
                                                            'medium' => __("Medium"),
                                                            'high' => __("High"),
                                                            'urgent' => __("Urgent")
                                                        ];
                                                    ?>
                                                    <span class="status-indicator status-<?php echo $priorityClass; ?>">
                                                        <?php echo $priorityLabels[$ticket['priority']] ?? ucfirst($ticket['priority']); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted"><?php echo __("N/A"); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('d M Y', strtotime($ticket['created_at'])); ?></td>
                                            <td class="actions">
                                                <a href="view-ticket.php?id=<?php echo $ticket['id']; ?>" class="btn-icon" title="<?php echo __("View Ticket"); ?>">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="edit-ticket.php?id=<?php echo $ticket['id']; ?>" class="btn-icon" title="<?php echo __("Edit Ticket"); ?>">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php if (getCurrentRole() === 'admin'): ?>
                                                <a href="javascript:void(0);" class="btn-icon delete-ticket" data-id="<?php echo $ticket['id']; ?>" title="<?php echo __("Delete Ticket"); ?>">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                                <?php endif; ?>
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
                                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&priority=<?php echo urlencode($priority_filter); ?>&sort=<?php echo urlencode($sort_column); ?>&dir=<?php echo urlencode($sort_direction); ?>" class="pagination-link">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&priority=<?php echo urlencode($priority_filter); ?>&sort=<?php echo urlencode($sort_column); ?>&dir=<?php echo urlencode($sort_direction); ?>" 
                                       class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&priority=<?php echo urlencode($priority_filter); ?>&sort=<?php echo urlencode($sort_column); ?>&dir=<?php echo urlencode($sort_direction); ?>" class="pagination-link">
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
                <p><?php echo __("Are you sure you want to delete this ticket? This action cannot be undone."); ?></p>
            </div>
            <div class="modal-footer">
                <form id="deleteForm" action="view-ticket.php" method="POST">
                    <input type="hidden" name="delete_ticket" value="1">
                    <input type="hidden" name="ticket_id" id="deleteTicketId">
                    <button type="button" class="btn btn-secondary close-modal"><?php echo __("Cancel"); ?></button>
                    <button type="submit" class="btn btn-danger"><?php echo __("Delete"); ?></button>
                </form>
            </div>
        </div>
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
        /* Styles pour la cellule d'information utilisateur */
        .user-info-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4a80f0, #2c57b5);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            flex-shrink: 0;
        }
        
        .user-avatar.unknown {
            background: linear-gradient(135deg, #6c757d, #495057);
        }
        
        .user-details {
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .user-name {
            font-weight: 500;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-decoration: none;
        }
        
        .user-name:hover {
            color: var(--primary-color);
            text-decoration: underline;
        }
        
        .user-email, .user-unknown {
            font-size: 0.8rem;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        /* Style pour le mode sombre */
        [data-theme="dark"] .user-name {
            color: #f0f0f0;
        }
        
        [data-theme="dark"] .user-name:hover {
            color: var(--primary-color-light);
        }
    </style>
    <script>
        // Delete ticket modal functionality
        const modal = document.getElementById('deleteModal');
        const deleteButtons = document.querySelectorAll('.delete-ticket');
        const closeButtons = document.querySelectorAll('.close, .close-modal');
        const deleteForm = document.getElementById('deleteForm');
        const deleteTicketIdInput = document.getElementById('deleteTicketId');
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const ticketId = this.getAttribute('data-id');
                deleteTicketIdInput.value = ticketId;
                deleteForm.action = `view-ticket.php?id=${ticketId}`;
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
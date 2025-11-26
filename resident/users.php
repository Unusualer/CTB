<?php
// Start session
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/role_access.php';

// Check if user is logged in and is a resident
requireAnyRole(['resident']);

// Include translation function if not already included
if (!function_exists('__')) {
    $translations_file = dirname(__DIR__) . '/includes/translations.php';
    if (file_exists($translations_file)) {
        require_once $translations_file;
    } else {
        // Fallback to alternate locations
        $alt_translations_file = $_SERVER['DOCUMENT_ROOT'] . '/CTB/includes/translations.php';
        if (file_exists($alt_translations_file)) {
            require_once $alt_translations_file;
        } else {
            // Define a minimal translation function as last resort
            function __($text) {
                return $text;
            }
        }
    }
}

// AJAX Endpoint
if (isset($_GET['ajax']) && $_GET['ajax'] === 'true') {
    header('Content-Type: application/json');
    
    // Initialize variables
    $search = $_GET['search'] ?? '';
    $role_filter = $_GET['role'] ?? '';
    $status_filter = $_GET['status'] ?? '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $items_per_page = 10;
    $offset = ($page - 1) * $items_per_page;
    
    // Handle sorting
    $sort_column = $_GET['sort'] ?? 'id';
    $sort_direction = $_GET['dir'] ?? 'asc';
    
    // Validate sort column (whitelist allowed columns)
    $allowed_columns = ['id', 'name', 'email', 'role', 'status'];
    if (!in_array($sort_column, $allowed_columns)) {
        $sort_column = 'id';
    }
    
    // Validate sort direction
    if (!in_array(strtolower($sort_direction), ['asc', 'desc'])) {
        $sort_direction = 'asc';
    }
    
    try {
        $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // For residents, only show their own profile
        $current_user_id = $_SESSION['user_id'];
        
        // Build the query - residents can only see their own profile
        $query = "SELECT * FROM users WHERE id = :user_id";
        $count_query = "SELECT COUNT(*) as total FROM users WHERE id = :user_id";
        $params = [':user_id' => $current_user_id];
        
        // Apply filters (limited for residents)
        if (!empty($search)) {
            $query .= " AND (name LIKE :search OR email LIKE :search)";
            $count_query .= " AND (name LIKE :search OR email LIKE :search)";
            $params[':search'] = "%$search%";
        }
        
        // Add ordering (column name is validated against whitelist, so safe to use)
        $query .= " ORDER BY `" . $sort_column . "` " . strtoupper($sort_direction) . " LIMIT :offset, :limit";
        
        // Get total count
        $count_stmt = $db->prepare($count_query);
        foreach ($params as $key => $value) {
            $count_stmt->bindValue($key, $value);
        }
        $count_stmt->execute();
        $total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
        $total_pages = ceil($total / $items_per_page);
        
        // Get users
        $stmt = $db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Start output buffer to capture rendered HTML
        ob_start();
        if (empty($users)): ?>
            <div class="no-data">
                <i class="fas fa-users"></i>
                <p><?php echo __("No users found. Try adjusting your filters or add a new user."); ?></p>
                <a href="add-user.php" class="btn btn-primary"><?php echo __("Add User"); ?></a>
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
                            <th class="sortable" data-column="name">
                                <?php echo __("Name"); ?>
                                <span class="sort-icon">
                                    <?php if ($sort_column === 'name'): ?>
                                        <i class="fas fa-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                    <?php else: ?>
                                        <i class="fas fa-sort"></i>
                                    <?php endif; ?>
                                </span>
                            </th>
                            <th class="sortable" data-column="email">
                                <?php echo __("Email"); ?>
                                <span class="sort-icon">
                                    <?php if ($sort_column === 'email'): ?>
                                        <i class="fas fa-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                    <?php else: ?>
                                        <i class="fas fa-sort"></i>
                                    <?php endif; ?>
                                </span>
                            </th>
                            <th class="sortable" data-column="role">
                                <?php echo __("Role"); ?>
                                <span class="sort-icon">
                                    <?php if ($sort_column === 'role'): ?>
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
                            <th><?php echo __("Actions"); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar-sm">
                                            <?php if (!empty($user['profile_image'])): ?>
                                                <img src="../assets/img/avatars/<?php echo htmlspecialchars($user['profile_image']); ?>" alt="<?php echo htmlspecialchars($user['name']); ?>">
                                            <?php else: ?>
                                                <div class="avatar-placeholder-sm">
                                                    <span class="avatar-initial-sm"><?php echo substr(htmlspecialchars($user['name']), 0, 1); ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <span class="user-name"><?php echo htmlspecialchars($user['name']); ?></span>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="user-role-badge">
                                        <?php
                                        $roleIcon = '';
                                        switch($user['role']) {
                                            case 'admin':
                                                $roleIcon = '<i class="fas fa-user-shield"></i>';
                                                break;
                                            case 'manager':
                                                $roleIcon = '<i class="fas fa-user-cog"></i>';
                                                break;
                                            case 'resident':
                                                $roleIcon = '<i class="fas fa-user"></i>';
                                                break;
                                        }
                                        echo $roleIcon . ' ' . __(ucfirst(htmlspecialchars($user['role'])));
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $user['status']; ?>">
                                        <i class="fas fa-circle"></i>
                                        <?php echo __(ucfirst(htmlspecialchars($user['status']))); ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <a href="view-user.php?id=<?php echo $user['id']; ?>" class="btn-icon" title="<?php echo __("View User"); ?>">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit-user.php?id=<?php echo $user['id']; ?>" class="btn-icon" title="<?php echo __("Edit User"); ?>">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="javascript:void(0);" class="btn-icon delete-user" data-id="<?php echo $user['id']; ?>" title="<?php echo __("Delete User"); ?>">
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
                        <a href="javascript:void(0);" onclick="loadUsers(<?php echo $page - 1; ?>)" class="pagination-link">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <a href="javascript:void(0);" onclick="loadUsers(<?php echo $i; ?>)"
                        class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="javascript:void(0);" onclick="loadUsers(<?php echo $page + 1; ?>)" class="pagination-link">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif;
        
        $html = ob_get_clean();
        
        echo json_encode([
            'html' => $html,
            'total' => $total,
            'page' => $page,
            'totalPages' => $total_pages
        ]);
        
        exit;
    } catch (PDOException $e) {
        echo json_encode(['error' => __("Database error") . ": " . $e->getMessage()]);
        exit;
    }
}

// Regular page load
// Initialize variables
$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? '';
$status_filter = $_GET['status'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

// Handle sorting
$sort_column = $_GET['sort'] ?? 'id';
$sort_direction = $_GET['dir'] ?? 'asc';

// Validate sort column (whitelist allowed columns)
$allowed_columns = ['id', 'name', 'email', 'role', 'status'];
if (!in_array($sort_column, $allowed_columns)) {
    $sort_column = 'id';
}

// Validate sort direction
if (!in_array(strtolower($sort_direction), ['asc', 'desc'])) {
    $sort_direction = 'asc';
}

// Get total count and users list
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // For residents, only show their own profile
    $current_user_id = $_SESSION['user_id'];
    
    // Build the query - residents can only see their own profile
    $query = "SELECT * FROM users WHERE id = :user_id";
    $count_query = "SELECT COUNT(*) as total FROM users WHERE id = :user_id";
    $params = [':user_id' => $current_user_id];
    
    // Apply filters (limited for residents)
    if (!empty($search)) {
        $query .= " AND (name LIKE :search OR email LIKE :search)";
        $count_query .= " AND (name LIKE :search OR email LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    // Add ordering
    $query .= " ORDER BY " . $sort_column . " " . strtoupper($sort_direction) . " LIMIT :offset, :limit";
    
    // Get total count
    $count_stmt = $db->prepare($count_query);
    foreach ($params as $key => $value) {
        $count_stmt->bindValue($key, $value);
    }
    $count_stmt->execute();
    $total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total / $items_per_page);
    
    // Get users
    $stmt = $db->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get counts by role for stats
    $role_counts = [];
    $role_stmt = $db->prepare("SELECT role, COUNT(*) as count FROM users GROUP BY role");
    $role_stmt->execute();
    $role_results = $role_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($role_results as $role_data) {
        $role_counts[$role_data['role']] = $role_data['count'];
    }
    
    // Get counts by status for stats
    $status_counts = [];
    $status_stmt = $db->prepare("SELECT status, COUNT(*) as count FROM users GROUP BY status");
    $status_stmt->execute();
    $status_results = $status_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($status_results as $status_data) {
        $status_counts[$status_data['status']] = $status_data['count'];
    }
    
} catch (PDOException $e) {
    $_SESSION['error'] = __("Database error") . ": " . $e->getMessage();
    $users = [];
    $total = 0;
    $total_pages = 0;
    $role_counts = ['admin' => 0, 'manager' => 0, 'resident' => 0];
    $status_counts = ['active' => 0, 'inactive' => 0];
}

// Page title
$page_title = __("User Management");
?>

<!DOCTYPE html>
<html lang="<?php echo isset($_SESSION['language']) ? substr($_SESSION['language'], 0, 2) : 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Community Trust Bank</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../images/logo.png">
    <link rel="shortcut icon" href="../images/logo.png" type="image/png">
    <link rel="apple-touch-icon" href="../images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin-style.css">
    <style>
        .loading-spinner {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem 0;
            color: #6c757d;
        }
        .loading-spinner i {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        
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
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/admin-sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <h1><?php echo __("User Management"); ?></h1>
                <a href="add-user.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <?php echo __("Add User"); ?>
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
            <div class="content-wrapper">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon users">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo __("Administrators"); ?></h3>
                            <div class="stat-number"><?php echo isset($role_counts['admin']) ? $role_counts['admin'] : 0; ?></div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon properties">
                            <i class="fas fa-user-cog"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo __("Managers"); ?></h3>
                            <div class="stat-number"><?php echo isset($role_counts['manager']) ? $role_counts['manager'] : 0; ?></div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon tickets">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo __("Residents"); ?></h3>
                            <div class="stat-number"><?php echo isset($role_counts['resident']) ? $role_counts['resident'] : 0; ?></div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon payments">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo __("Total Users"); ?></h3>
                            <div class="stat-number"><?php echo $total; ?></div>
                            <div class="stat-breakdown">
                                <span><i class="fas fa-circle" style="color: #28a745;"></i> <?php echo __("Active"); ?>: <?php echo isset($status_counts['active']) ? $status_counts['active'] : 0; ?></span>
                                <span><i class="fas fa-circle" style="color: #dc3545;"></i> <?php echo __("Inactive"); ?>: <?php echo isset($status_counts['inactive']) ? $status_counts['inactive'] : 0; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card user-filter-card">
                    <div class="card-header user-filter-header">
                        <h3><i class="fas fa-filter"></i> <?php echo __("User List"); ?></h3>
                        <form id="filter-form" action="users.php" method="GET" class="filter-form">
                            <div class="filter-wrapper">
                                <div class="search-filter">
                                    <div class="search-bar">
                                        <i class="fas fa-search"></i>
                                        <input type="text" placeholder="<?php echo __("Search..."); ?>" name="search" value="<?php echo htmlspecialchars($search); ?>" autocomplete="off" autofocus>
                                    </div>
                                </div>
                                <div class="filter-group">
                                    <label for="role"><?php echo __("Role"); ?>:</label>
                                    <select name="role" id="role">
                                        <option value=""><?php echo __("All Roles"); ?></option>
                                        <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>><?php echo __("Administrator"); ?></option>
                                        <option value="manager" <?php echo $role_filter === 'manager' ? 'selected' : ''; ?>><?php echo __("Manager"); ?></option>
                                        <option value="resident" <?php echo $role_filter === 'resident' ? 'selected' : ''; ?>><?php echo __("Resident"); ?></option>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label for="status"><?php echo __("Status"); ?>:</label>
                                    <select name="status" id="status">
                                        <option value=""><?php echo __("All Statuses"); ?></option>
                                        <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>><?php echo __("Active"); ?></option>
                                        <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>><?php echo __("Inactive"); ?></option>
                                    </select>
                                </div>
                                <a href="javascript:void(0);" class="reset-link"><?php echo __("Reset"); ?></a>
                            </div>
                        </form>
                    </div>
                    <div class="card-body">
                        <?php if (empty($users)): ?>
                            <div class="no-data">
                                <i class="fas fa-users"></i>
                                <p><?php echo __("No users found. Try adjusting your filters or add a new user."); ?></p>
                                <a href="add-user.php" class="btn btn-primary"><?php echo __("Add User"); ?></a>
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
                                            <th class="sortable" data-column="name">
                                                <?php echo __("Name"); ?>
                                                <span class="sort-icon">
                                                    <?php if ($sort_column === 'name'): ?>
                                                        <i class="fas fa-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-sort"></i>
                                                    <?php endif; ?>
                                                </span>
                                            </th>
                                            <th class="sortable" data-column="email">
                                                <?php echo __("Email"); ?>
                                                <span class="sort-icon">
                                                    <?php if ($sort_column === 'email'): ?>
                                                        <i class="fas fa-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-sort"></i>
                                                    <?php endif; ?>
                                                </span>
                                            </th>
                                            <th class="sortable" data-column="role">
                                                <?php echo __("Role"); ?>
                                                <span class="sort-icon">
                                                    <?php if ($sort_column === 'role'): ?>
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
                                            <th><?php echo __("Actions"); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td><?php echo $user['id']; ?></td>
                                                <td>
                                                    <div class="user-cell">
                                                        <div class="user-avatar-sm">
                                                            <?php if (!empty($user['profile_image'])): ?>
                                                                <img src="../assets/img/avatars/<?php echo htmlspecialchars($user['profile_image']); ?>" alt="<?php echo htmlspecialchars($user['name']); ?>">
                                                            <?php else: ?>
                                                                <div class="avatar-placeholder-sm">
                                                                    <span class="avatar-initial-sm"><?php echo substr(htmlspecialchars($user['name']), 0, 1); ?></span>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <span class="user-name"><?php echo htmlspecialchars($user['name']); ?></span>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td>
                                                    <span class="user-role-badge">
                                                        <?php
                                                        $roleIcon = '';
                                                        switch($user['role']) {
                                                            case 'admin':
                                                                $roleIcon = '<i class="fas fa-user-shield"></i>';
                                                                break;
                                                            case 'manager':
                                                                $roleIcon = '<i class="fas fa-user-cog"></i>';
                                                                break;
                                                            case 'resident':
                                                                $roleIcon = '<i class="fas fa-user"></i>';
                                                                break;
                                                        }
                                                        echo $roleIcon . ' ' . __(ucfirst(htmlspecialchars($user['role'])));
                                                        ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="status-badge <?php echo $user['status']; ?>">
                                                        <i class="fas fa-circle"></i>
                                                        <?php echo __(ucfirst(htmlspecialchars($user['status']))); ?>
                                                    </span>
                                                </td>
                                                <td class="actions">
                                                    <a href="view-user.php?id=<?php echo $user['id']; ?>" class="btn-icon" title="<?php echo __("View User"); ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="edit-user.php?id=<?php echo $user['id']; ?>" class="btn-icon" title="<?php echo __("Edit User"); ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="javascript:void(0);" class="btn-icon delete-user" data-id="<?php echo $user['id']; ?>" title="<?php echo __("Delete User"); ?>">
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
                                        <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role_filter); ?>&status=<?php echo urlencode($status_filter); ?>&sort=<?php echo urlencode($sort_column); ?>&dir=<?php echo urlencode($sort_direction); ?>" class="pagination-link">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role_filter); ?>&status=<?php echo urlencode($status_filter); ?>&sort=<?php echo urlencode($sort_column); ?>&dir=<?php echo urlencode($sort_direction); ?>" 
                                        class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role_filter); ?>&status=<?php echo urlencode($status_filter); ?>&sort=<?php echo urlencode($sort_column); ?>&dir=<?php echo urlencode($sort_direction); ?>" class="pagination-link">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
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
                <p><?php echo __("Are you sure you want to delete this user? This action cannot be undone."); ?></p>
            </div>
            <div class="modal-footer">
                <form id="deleteForm" action="delete-user.php" method="POST">
                    <input type="hidden" name="user_id" id="deleteUserId">
                    <button type="button" class="btn btn-secondary close-modal"><?php echo __("Cancel"); ?></button>
                    <button type="submit" class="btn btn-danger"><?php echo __("Delete"); ?></button>
                </form>
            </div>
        </div>
    </div>

    <script src="js/dark-mode.js"></script>
    <script>
        // Delete user modal functionality
        const modal = document.getElementById('deleteModal');
        const deleteButtons = document.querySelectorAll('.delete-user');
        const closeButtons = document.querySelectorAll('.close, .close-modal');
        const deleteForm = document.getElementById('deleteForm');
        const deleteUserIdInput = document.getElementById('deleteUserId');
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                deleteUserIdInput.value = userId;
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

        // AJAX loading and search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('.search-filter input[name="search"]');
            const roleSelect = document.getElementById('role');
            const statusSelect = document.getElementById('status');
            const filterForm = document.getElementById('filter-form');
            const contentContainer = document.querySelector('.card-body');
            
            // Function to initialize delete buttons after AJAX content is loaded
            function initDeleteButtons() {
                const newDeleteButtons = document.querySelectorAll('.delete-user');
                newDeleteButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const userId = this.getAttribute('data-id');
                        deleteUserIdInput.value = userId;
                        modal.style.display = 'block';
                    });
                });
            }
            
            // Track current sort state - get from URL params or use defaults
            const urlParams = new URLSearchParams(window.location.search);
            let currentSort = {
                column: urlParams.get('sort') || '<?php echo $sort_column; ?>',
                direction: urlParams.get('dir') || '<?php echo $sort_direction; ?>'
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
                        
                        // Reload users with new sort
                        loadUsers(1);
                    });
                });
            }
            
            // Initialize sortable headers on page load
            initSortableHeaders();
            
            // Function to load users via AJAX
            window.loadUsers = function(page = 1) {
                const search = searchInput ? searchInput.value : '';
                const role = roleSelect ? roleSelect.value : '';
                const status = statusSelect ? statusSelect.value : '';
                
                // Show loading indicator
                contentContainer.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i><p><?php echo __("Loading..."); ?></p></div>';
                
                // Build the AJAX URL with sort parameters
                const url = `users.php?ajax=true&search=${encodeURIComponent(search)}&role=${encodeURIComponent(role)}&status=${encodeURIComponent(status)}&sort=${encodeURIComponent(currentSort.column)}&dir=${encodeURIComponent(currentSort.direction)}&page=${page}`;
                
                // Make the AJAX request
                fetch(url)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.error) {
                            contentContainer.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                        } else {
                            contentContainer.innerHTML = data.html;
                            
                            // Initialize delete buttons on newly loaded content
                            initDeleteButtons();
                            
                            // Re-initialize sortable headers on newly loaded content
                            initSortableHeaders();
                            
                            // Update URL with the current filters and sort without reloading
                            const newUrl = `users.php?search=${encodeURIComponent(search)}&role=${encodeURIComponent(role)}&status=${encodeURIComponent(status)}&sort=${encodeURIComponent(currentSort.column)}&dir=${encodeURIComponent(currentSort.direction)}&page=${page}`;
                            window.history.pushState({ path: newUrl }, '', newUrl);
                        }
                    })
                    .catch(error => {
                        console.error('Error loading users:', error);
                        contentContainer.innerHTML = `<div class="alert alert-danger"><?php echo __("An error occurred while loading users."); ?></div>`;
                    });
            };
            
            // Prevent form submission and use AJAX instead
            if (filterForm) {
                filterForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    loadUsers(1);
                });
            }
            
            // Setup search input
            if (searchInput) {
                // Set focus on search input if it's empty
                if (!searchInput.value) {
                    setTimeout(() => {
                        searchInput.focus();
                    }, 100);
                }
                
                // Debounce search input
                let debounceTimer;
                searchInput.addEventListener('input', function() {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => {
                        loadUsers(1);
                    }, 500); // Search after 500ms of typing pause
                });
                
                // Add a clear button to the search input
                const searchContainer = searchInput.parentElement;
                const clearButton = document.createElement('i');
                clearButton.className = 'fas fa-times search-clear';
                clearButton.style.display = searchInput.value ? 'block' : 'none';
                clearButton.style.cursor = 'pointer';
                clearButton.style.position = 'absolute';
                clearButton.style.right = '10px';
                clearButton.style.top = '50%';
                clearButton.style.transform = 'translateY(-50%)';
                searchContainer.style.position = 'relative';
                searchContainer.appendChild(clearButton);
                
                // Show/hide clear button based on input value
                searchInput.addEventListener('input', function() {
                    clearButton.style.display = this.value ? 'block' : 'none';
                });
                
                // Clear search when button is clicked
                clearButton.addEventListener('click', function() {
                    searchInput.value = '';
                    loadUsers(1);
                    this.style.display = 'none';
                    searchInput.focus();
                });
            }
            
            // Handle select filters
            if (roleSelect) {
                roleSelect.addEventListener('change', function() {
                    loadUsers(1);
                });
            }
            
            if (statusSelect) {
                statusSelect.addEventListener('change', function() {
                    loadUsers(1);
                });
            }
            
            // Add click handler for reset link
            const resetLink = document.querySelector('.reset-link');
            if (resetLink) {
                resetLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Reset all form inputs
                    if (searchInput) searchInput.value = '';
                    if (roleSelect) roleSelect.value = '';
                    if (statusSelect) statusSelect.value = '';
                    
                    // Reset sort to default
                    currentSort.column = 'id';
                    currentSort.direction = 'asc';
                    
                    // Reload users
                    loadUsers(1);
                });
            }
            
            // On initial page load, check if we should use AJAX right away
            const initialUrlParams = new URLSearchParams(window.location.search);
            if (initialUrlParams.has('ajax') && initialUrlParams.get('ajax') === 'true') {
                // If this is an AJAX request, don't do anything
                return;
            } else if (initialUrlParams.toString() && !document.referrer.includes('users.php')) {
                // If there are URL parameters and we're not coming from a users.php page,
                // use AJAX to load the data without a full page reload
                // Update sort state from URL if present
                if (initialUrlParams.has('sort')) {
                    currentSort.column = initialUrlParams.get('sort');
                }
                if (initialUrlParams.has('dir')) {
                    currentSort.direction = initialUrlParams.get('dir');
                }
                loadUsers(initialUrlParams.get('page') || 1);
            }
        });
    </script>
</body>
</html> 
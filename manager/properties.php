<?php
// Start session
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/role_access.php';

// Check if user is logged in and has appropriate role
requireAnyRole(['admin', 'manager']);

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
    $type_filter = $_GET['type'] ?? '';
    $status_filter = $_GET['status'] ?? '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $items_per_page = 10;
    $offset = ($page - 1) * $items_per_page;
    
    // Handle sorting
    $sort_column = $_GET['sort'] ?? 'id';
    $sort_direction = $_GET['dir'] ?? 'asc';
    
    // Validate sort column (whitelist allowed columns)
    $allowed_columns = ['id', 'identifier', 'type'];
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
        
        // Build the query
        $query = "SELECT p.*, u.name as user_name 
                FROM properties p 
                LEFT JOIN users u ON p.user_id = u.id
                WHERE 1=1";
        $count_query = "SELECT COUNT(*) as total FROM properties WHERE 1=1";
        $params = [];
        
        // Apply filters
        if (!empty($search)) {
            $query .= " AND (p.identifier LIKE :search OR p.id LIKE :search OR p.type LIKE :search OR u.name LIKE :search)";
            $count_query .= " AND (identifier LIKE :search OR id LIKE :search OR type LIKE :search OR (properties.user_id IS NOT NULL AND properties.user_id IN (SELECT id FROM users WHERE name LIKE :search)))";
            $params[':search'] = "%$search%";
        }
        
        if (!empty($type_filter)) {
            $query .= " AND p.type = :type";
            $count_query .= " AND type = :type";
            $params[':type'] = $type_filter;
        }
        
        if (!empty($status_filter)) {
            if ($status_filter === 'assigned') {
                $query .= " AND p.user_id IS NOT NULL";
                $count_query .= " AND user_id IS NOT NULL";
            } else if ($status_filter === 'unassigned') {
                $query .= " AND p.user_id IS NULL";
                $count_query .= " AND user_id IS NULL";
            }
        }
        
        // Add ordering (column name is validated against whitelist, so safe to use)
        $query .= " ORDER BY p.`" . $sort_column . "` " . strtoupper($sort_direction) . " LIMIT :offset, :limit";
        
        // Get total count
        $count_stmt = $db->prepare($count_query);
        foreach ($params as $key => $value) {
            $count_stmt->bindValue($key, $value);
        }
        $count_stmt->execute();
        $total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
        $total_pages = ceil($total / $items_per_page);
        
        // Get properties
        $stmt = $db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
        $stmt->execute();
        $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Start output buffer to capture rendered HTML
        ob_start();
        if (empty($properties)): ?>
            <div class="no-data">
                <i class="fas fa-building"></i>
                <p><?php echo __("No properties found. Try adjusting your filters."); ?></p>
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
                            <th class="sortable" data-column="identifier">
                                <?php echo __("Identifier"); ?>
                                <span class="sort-icon">
                                    <?php if ($sort_column === 'identifier'): ?>
                                        <i class="fas fa-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                    <?php else: ?>
                                        <i class="fas fa-sort"></i>
                                    <?php endif; ?>
                                </span>
                            </th>
                            <th class="sortable" data-column="type">
                                <?php echo __("Type"); ?>
                                <span class="sort-icon">
                                    <?php if ($sort_column === 'type'): ?>
                                        <i class="fas fa-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                    <?php else: ?>
                                        <i class="fas fa-sort"></i>
                                    <?php endif; ?>
                                </span>
                            </th>
                            <th><?php echo __("Assigned to"); ?></th>
                            <th><?php echo __("Actions"); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($properties as $property): ?>
                            <tr>
                                <td><?php echo $property['id']; ?></td>
                                <td>
                                    <div class="property-cell">
                                        <div class="property-icon">
                                            <i class="fas fa-<?php echo $property['type'] === 'apartment' ? 'home' : 'car'; ?>"></i>
                                        </div>
                                        <span class="property-identifier"><?php echo htmlspecialchars($property['identifier']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="property-type-badge">
                                        <?php 
                                            if ($property['type'] === 'apartment') {
                                                echo __("Apartment");
                                            } else if ($property['type'] === 'parking') {
                                                echo __("Parking");
                                            } else {
                                                echo ucfirst(htmlspecialchars($property['type']));
                                            }
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($property['user_name'])): ?>
                                        <span class="resident-tag">
                                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($property['user_name']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="unassigned-tag">
                                            <i class="fas fa-times-circle"></i> <?php echo __("Unassigned"); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="actions">
                                    <a href="view-property.php?id=<?php echo $property['id']; ?>" class="btn-icon" title="<?php echo __("View Property"); ?>">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit-property.php?id=<?php echo $property['id']; ?>" class="btn-icon" title="<?php echo __("Edit Property"); ?>">
                                        <i class="fas fa-edit"></i>
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
                        <a href="javascript:void(0);" onclick="loadProperties(<?php echo $page - 1; ?>)" class="pagination-link">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <a href="javascript:void(0);" onclick="loadProperties(<?php echo $i; ?>)"
                        class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="javascript:void(0);" onclick="loadProperties(<?php echo $page + 1; ?>)" class="pagination-link">
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
$type_filter = $_GET['type'] ?? '';
$status_filter = $_GET['status'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

// Handle sorting
$sort_column = $_GET['sort'] ?? 'id';
$sort_direction = $_GET['dir'] ?? 'asc';

// Validate sort column (whitelist allowed columns)
$allowed_columns = ['id', 'identifier', 'type'];
if (!in_array($sort_column, $allowed_columns)) {
    $sort_column = 'id';
}

// Validate sort direction
if (!in_array(strtolower($sort_direction), ['asc', 'desc'])) {
    $sort_direction = 'asc';
}

// Get total count and properties list
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Build the query
    $query = "SELECT p.*, u.name as user_name 
              FROM properties p 
              LEFT JOIN users u ON p.user_id = u.id
              WHERE 1=1";
    $count_query = "SELECT COUNT(*) as total FROM properties WHERE 1=1";
    $params = [];
    
    // Apply filters
    if (!empty($search)) {
        $query .= " AND (p.identifier LIKE :search OR p.id LIKE :search OR p.type LIKE :search OR u.name LIKE :search)";
        $count_query .= " AND (identifier LIKE :search OR id LIKE :search OR type LIKE :search OR (properties.user_id IS NOT NULL AND properties.user_id IN (SELECT id FROM users WHERE name LIKE :search)))";
        $params[':search'] = "%$search%";
    }
    
    if (!empty($type_filter)) {
        $query .= " AND p.type = :type";
        $count_query .= " AND type = :type";
        $params[':type'] = $type_filter;
    }
    
    if (!empty($status_filter)) {
        if ($status_filter === 'assigned') {
            $query .= " AND p.user_id IS NOT NULL";
            $count_query .= " AND user_id IS NOT NULL";
        } else if ($status_filter === 'unassigned') {
            $query .= " AND p.user_id IS NULL";
            $count_query .= " AND user_id IS NULL";
        }
    }
    
    // Add ordering (column name is validated against whitelist, so safe to use)
    $query .= " ORDER BY p.`" . $sort_column . "` " . strtoupper($sort_direction) . " LIMIT :offset, :limit";
    
    // Get total count
    $count_stmt = $db->prepare($count_query);
    foreach ($params as $key => $value) {
        $count_stmt->bindValue($key, $value);
    }
    $count_stmt->execute();
    $total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total / $items_per_page);
    
    // Get properties
    $stmt = $db->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->execute();
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get counts by type for stats
    $type_counts = [];
    $type_stmt = $db->prepare("SELECT type, COUNT(*) as count FROM properties GROUP BY type");
    $type_stmt->execute();
    $type_results = $type_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($type_results as $type_data) {
        $type_counts[$type_data['type']] = $type_data['count'];
    }
    
    // Get counts for assigned vs unassigned
    $assigned_stmt = $db->prepare("SELECT COUNT(*) as count FROM properties WHERE user_id IS NOT NULL");
    $assigned_stmt->execute();
    $assigned_count = $assigned_stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    $unassigned_stmt = $db->prepare("SELECT COUNT(*) as count FROM properties WHERE user_id IS NULL");
    $unassigned_stmt->execute();
    $unassigned_count = $unassigned_stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
} catch (PDOException $e) {
    $_SESSION['error'] = __("Database error") . ": " . $e->getMessage();
    $properties = [];
    $total = 0;
    $total_pages = 0;
    $type_counts = ['apartment' => 0, 'parking' => 0];
    $assigned_count = 0;
    $unassigned_count = 0;
}

// Page title
$page_title = __("Property Management");
?>

<!DOCTYPE html>
<html lang="<?php echo isset($_SESSION['language']) ? substr($_SESSION['language'], 0, 2) : 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Community Trust Bank</title>
    <!-- Favicon -->
    <?php favicon_links(); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin-style.css">
    <link rel="stylesheet" href="css/colorful-theme.css">
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
                <h1><?php echo __("Property Management"); ?></h1>
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
                        <div class="stat-icon properties">
                            <i class="fas fa-home"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo __("Apartments"); ?></h3>
                            <div class="stat-number"><?php echo isset($type_counts['apartment']) ? $type_counts['apartment'] : 0; ?></div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon tickets">
                            <i class="fas fa-car"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo __("Parking"); ?></h3>
                            <div class="stat-number"><?php echo isset($type_counts['parking']) ? $type_counts['parking'] : 0; ?></div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon users">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo __("Assigned"); ?></h3>
                            <div class="stat-number"><?php echo $assigned_count; ?></div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon payments">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo __("Total Properties"); ?></h3>
                            <div class="stat-number"><?php echo $total; ?></div>
                            <div class="stat-breakdown">
                                <span><i class="fas fa-circle" style="color: #28a745;"></i> <?php echo __("Assigned"); ?>: <?php echo $assigned_count; ?></span>
                                <span><i class="fas fa-circle" style="color: #dc3545;"></i> <?php echo __("Unassigned"); ?>: <?php echo $unassigned_count; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card user-filter-card">
                    <div class="card-header user-filter-header">
                        <h3><i class="fas fa-filter"></i> <?php echo __("Property List"); ?></h3>
                        <form id="filter-form" action="properties.php" method="GET" class="filter-form">
                            <div class="filter-wrapper">
                                <div class="search-filter">
                                    <div class="search-bar">
                                        <i class="fas fa-search"></i>
                                        <input type="text" placeholder="<?php echo __("Search..."); ?>" name="search" value="<?php echo htmlspecialchars($search); ?>" autocomplete="off" autofocus>
                                    </div>
                                </div>
                                <div class="filter-group">
                                    <label for="type"><?php echo __("Type"); ?>:</label>
                                    <select name="type" id="type">
                                        <option value=""><?php echo __("All Types"); ?></option>
                                        <option value="apartment" <?php echo $type_filter === 'apartment' ? 'selected' : ''; ?>><?php echo __("Apartment"); ?></option>
                                        <option value="parking" <?php echo $type_filter === 'parking' ? 'selected' : ''; ?>><?php echo __("Parking"); ?></option>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label for="status"><?php echo __("Status"); ?>:</label>
                                    <select name="status" id="status">
                                        <option value=""><?php echo __("All Statuses"); ?></option>
                                        <option value="assigned" <?php echo $status_filter === 'assigned' ? 'selected' : ''; ?>><?php echo __("Assigned"); ?></option>
                                        <option value="unassigned" <?php echo $status_filter === 'unassigned' ? 'selected' : ''; ?>><?php echo __("Unassigned"); ?></option>
                                    </select>
                                </div>
                                <a href="javascript:void(0);" class="reset-link"><?php echo __("Reset"); ?></a>
                            </div>
                        </form>
                    </div>
                    <div class="card-body">
                        <?php if (empty($properties)): ?>
                            <div class="no-data">
                                <i class="fas fa-building"></i>
                                <p><?php echo __("No properties found. Try adjusting your filters."); ?></p>
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
                                            <th class="sortable" data-column="identifier">
                                                <?php echo __("Identifier"); ?>
                                                <span class="sort-icon">
                                                    <?php if ($sort_column === 'identifier'): ?>
                                                        <i class="fas fa-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-sort"></i>
                                                    <?php endif; ?>
                                                </span>
                                            </th>
                                            <th class="sortable" data-column="type">
                                                <?php echo __("Type"); ?>
                                                <span class="sort-icon">
                                                    <?php if ($sort_column === 'type'): ?>
                                                        <i class="fas fa-sort-<?php echo $sort_direction === 'asc' ? 'up' : 'down'; ?>"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-sort"></i>
                                                    <?php endif; ?>
                                                </span>
                                            </th>
                                            <th><?php echo __("Assigned to"); ?></th>
                                            <th><?php echo __("Actions"); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($properties as $property): ?>
                                            <tr>
                                                <td><?php echo $property['id']; ?></td>
                                                <td>
                                                    <div class="property-cell">
                                                        <div class="property-icon">
                                                            <i class="fas fa-<?php echo $property['type'] === 'apartment' ? 'home' : 'car'; ?>"></i>
                                                        </div>
                                                        <span class="property-identifier"><?php echo htmlspecialchars($property['identifier']); ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="property-type-badge">
                                                        <?php 
                                                            if ($property['type'] === 'apartment') {
                                                                echo __("Apartment");
                                                            } else if ($property['type'] === 'parking') {
                                                                echo __("Parking");
                                                            } else {
                                                                echo ucfirst(htmlspecialchars($property['type']));
                                                            }
                                                        ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if (!empty($property['user_name'])): ?>
                                                        <span class="resident-tag">
                                                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($property['user_name']); ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="unassigned-tag">
                                                            <i class="fas fa-times-circle"></i> <?php echo __("Unassigned"); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="actions">
                                                    <a href="view-property.php?id=<?php echo $property['id']; ?>" class="btn-icon" title="<?php echo __("View Property"); ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="edit-property.php?id=<?php echo $property['id']; ?>" class="btn-icon" title="<?php echo __("Edit Property"); ?>">
                                                        <i class="fas fa-edit"></i>
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
                                        <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($type_filter); ?>&status=<?php echo urlencode($status_filter); ?>&sort=<?php echo urlencode($sort_column); ?>&dir=<?php echo urlencode($sort_direction); ?>" class="pagination-link">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($type_filter); ?>&status=<?php echo urlencode($status_filter); ?>&sort=<?php echo urlencode($sort_column); ?>&dir=<?php echo urlencode($sort_direction); ?>" 
                                        class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($type_filter); ?>&status=<?php echo urlencode($status_filter); ?>&sort=<?php echo urlencode($sort_column); ?>&dir=<?php echo urlencode($sort_direction); ?>" class="pagination-link">
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

    <script src="js/dark-mode.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('.search-filter input[name="search"]');
            const typeSelect = document.getElementById('type');
            const statusSelect = document.getElementById('status');
            const filterForm = document.getElementById('filter-form');
            const contentContainer = document.querySelector('.card-body');
            
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
                        
                        // Reload properties with new sort
                        loadProperties(1);
                    });
                });
            }
            
            // Initialize sortable headers on page load
            initSortableHeaders();
            
            // Function to load properties via AJAX
            window.loadProperties = function(page = 1) {
                const search = searchInput ? searchInput.value : '';
                const type = typeSelect ? typeSelect.value : '';
                const status = statusSelect ? statusSelect.value : '';
                
                // Show loading indicator
                contentContainer.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i><p><?php echo __("Loading..."); ?></p></div>';
                
                // Build the AJAX URL with sort parameters
                const url = `properties.php?ajax=true&search=${encodeURIComponent(search)}&type=${encodeURIComponent(type)}&status=${encodeURIComponent(status)}&sort=${encodeURIComponent(currentSort.column)}&dir=${encodeURIComponent(currentSort.direction)}&page=${page}`;
                
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
                            
                            // Re-initialize sortable headers on newly loaded content
                            initSortableHeaders();
                            
                            // Update URL with the current filters and sort without reloading
                            const newUrl = `properties.php?search=${encodeURIComponent(search)}&type=${encodeURIComponent(type)}&status=${encodeURIComponent(status)}&sort=${encodeURIComponent(currentSort.column)}&dir=${encodeURIComponent(currentSort.direction)}&page=${page}`;
                            window.history.pushState({ path: newUrl }, '', newUrl);
                        }
                    })
                    .catch(error => {
                        console.error('Error loading properties:', error);
                        contentContainer.innerHTML = `<div class="alert alert-danger"><?php echo __("An error occurred while loading properties."); ?></div>`;
                    });
            };
            
            // Prevent form submission and use AJAX instead
            if (filterForm) {
                filterForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    loadProperties(1);
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
                        loadProperties(1);
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
                    loadProperties(1);
                    this.style.display = 'none';
                    searchInput.focus();
                });
            }
            
            // Handle select filters
            if (typeSelect) {
                typeSelect.addEventListener('change', function() {
                    loadProperties(1);
                });
            }
            
            if (statusSelect) {
                statusSelect.addEventListener('change', function() {
                    loadProperties(1);
                });
            }
            
            // Add click handler for reset link
            const resetLink = document.querySelector('.reset-link');
            if (resetLink) {
                resetLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Reset all form inputs
                    if (searchInput) searchInput.value = '';
                    if (typeSelect) typeSelect.value = '';
                    if (statusSelect) statusSelect.value = '';
                    
                    // Reset sort to default
                    currentSort.column = 'id';
                    currentSort.direction = 'asc';
                    
                    // Reload properties
                    loadProperties(1);
                });
            }
            
            // On initial page load, check if we should use AJAX right away
            const initialUrlParams = new URLSearchParams(window.location.search);
            if (initialUrlParams.has('ajax') && initialUrlParams.get('ajax') === 'true') {
                // If this is an AJAX request, don't do anything
                return;
            } else if (initialUrlParams.toString() && !document.referrer.includes('properties.php')) {
                // If there are URL parameters and we're not coming from a properties.php page,
                // use AJAX to load the data without a full page reload
                // Update sort state from URL if present
                if (initialUrlParams.has('sort')) {
                    currentSort.column = initialUrlParams.get('sort');
                }
                if (initialUrlParams.has('dir')) {
                    currentSort.direction = initialUrlParams.get('dir');
                }
                loadProperties(initialUrlParams.get('page') || 1);
            }
        });
    </script>
</body>
</html> 
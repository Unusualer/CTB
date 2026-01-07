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
$year_filter = $_GET['year'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

// Get total count and cotisations list
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Build the query - select cotisations with property info
    $query = "SELECT c.*, 
              p.identifier as property_identifier,
              p.type as property_type,
              u.name as user_name,
              COALESCE(SUM(CASE WHEN pay.status = 'paid' THEN pay.amount ELSE 0 END), 0) AS amount_paid
              FROM cotisations c 
              LEFT JOIN properties p ON c.property_id = p.id 
              LEFT JOIN users u ON p.user_id = u.id
              LEFT JOIN payments pay ON pay.property_id = c.property_id AND pay.year = c.year
              WHERE 1=1";
    $count_query = "SELECT COUNT(*) as total FROM cotisations c WHERE 1=1";
    $params = [];
    
    // Apply filters
    if (!empty($search)) {
        $query .= " AND (p.identifier LIKE :search OR u.name LIKE :search)";
        $count_query .= " AND (EXISTS(SELECT 1 FROM properties pr WHERE pr.id = c.property_id AND pr.identifier LIKE :search) OR EXISTS(SELECT 1 FROM properties pr JOIN users us ON pr.user_id = us.id WHERE pr.id = c.property_id AND us.name LIKE :search))";
        $params[':search'] = "%$search%";
    }
    
    if (!empty($year_filter)) {
        $query .= " AND c.year = :year";
        $count_query .= " AND c.year = :year";
        $params[':year'] = $year_filter;
    }
    
    // Group by for aggregate functions
    $query .= " GROUP BY c.id, c.property_id, c.year, c.amount_due, c.created_at, c.updated_at, p.identifier, p.type, u.name";
    
    // Add ordering
    $query .= " ORDER BY c.year DESC, p.identifier ASC LIMIT :offset, :limit";
    
    // Get total count
    $count_stmt = $db->prepare($count_query);
    foreach ($params as $key => $value) {
        $count_stmt->bindValue($key, $value);
    }
    $count_stmt->execute();
    $total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total / $items_per_page);
    
    // Get cotisations
    $stmt = $db->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->execute();
    $cotisations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get statistics
    $stats_query = "SELECT 
                    COUNT(*) as total_count,
                    SUM(amount_due) as total_due,
                    COUNT(DISTINCT year) as years_count,
                    COUNT(DISTINCT property_id) as properties_count
                    FROM cotisations";
    $stats_stmt = $db->prepare($stats_query);
    $stats_stmt->execute();
    $cotisation_stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get available years for filter
    $years_query = "SELECT DISTINCT year FROM cotisations ORDER BY year DESC";
    $years_stmt = $db->prepare($years_query);
    $years_stmt->execute();
    $available_years = $years_stmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    $cotisations = [];
    $total = 0;
    $total_pages = 0;
    $cotisation_stats = ['total_count' => 0, 'total_due' => 0, 'years_count' => 0, 'properties_count' => 0];
    $available_years = [];
}

// Page title
$page_title = __("Cotisations Management");
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
        .balance-indicator {
            display: inline-block;
            padding: 0.4rem 0.9rem;
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: 600;
        }
        
        .balance-paid {
            background-color: #d4edda;
            color: #155724;
        }
        
        .balance-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .balance-overdue {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        [data-theme="dark"] .balance-paid {
            background-color: rgba(40, 167, 69, 0.2);
            color: #2ecc71;
        }
        
        [data-theme="dark"] .balance-pending {
            background-color: rgba(255, 193, 7, 0.2);
            color: #f39c12;
        }
        
        [data-theme="dark"] .balance-overdue {
            background-color: rgba(220, 53, 69, 0.2);
            color: #e74c3c;
        }
        
        /* Reduce table row height */
        .table tbody tr td {
            padding: 8px 16px;
            line-height: 1.3;
        }
        
        .table thead th {
            padding: 10px 16px;
        }
        
        .table tbody tr td .property-link {
            display: block;
            margin-bottom: 4px;
        }
        
        .table tbody tr td .property-type {
            font-size: 0.8rem;
            line-height: 1.2;
            display: block;
            margin-top: 2px;
            margin-bottom: 2px;
        }
        
        .table tbody tr td .user-name {
            font-size: 0.8rem;
            line-height: 1.2;
            display: block;
            margin-top: 2px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/admin-sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <h1><?php echo __("Cotisations Management"); ?></h1>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon payments">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo __("Total Cotisations"); ?></h3>
                        <div class="stat-number"><?php echo isset($cotisation_stats['total_count']) ? $cotisation_stats['total_count'] : 0; ?></div>
                        <div class="stat-breakdown">
                            <span><?php echo isset($cotisation_stats['properties_count']) ? $cotisation_stats['properties_count'] : 0; ?> <?php echo __("Properties"); ?></span>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon tickets">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo __("Total Amount Due"); ?></h3>
                        <div class="stat-number"><?php echo isset($cotisation_stats['total_due']) ? number_format($cotisation_stats['total_due'], 2) : '0.00'; ?> MAD</div>
                        <div class="stat-breakdown">
                            <span><?php echo isset($cotisation_stats['years_count']) ? $cotisation_stats['years_count'] : 0; ?> <?php echo __("Years"); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card user-filter-card">
                <div class="card-header user-filter-header">
                    <h3><i class="fas fa-filter"></i> <?php echo __("Cotisations List"); ?></h3>
                    <form id="filter-form" action="cotisations.php" method="GET" class="filter-form">
                        <div class="filter-wrapper">
                            <div class="search-filter">
                                <div class="search-bar">
                                    <i class="fas fa-search"></i>
                                    <input type="text" placeholder="<?php echo __("Search by property or owner..."); ?>" name="search" value="<?php echo htmlspecialchars($search); ?>" autocomplete="off">
                                </div>
                            </div>
                            <div class="filter-group">
                                <label for="year"><?php echo __("Year"); ?>:</label>
                                <select name="year" id="year" onchange="this.form.submit()">
                                    <option value=""><?php echo __("All Years"); ?></option>
                                    <?php foreach ($available_years as $year): ?>
                                        <option value="<?php echo $year; ?>" <?php echo $year_filter == $year ? 'selected' : ''; ?>><?php echo $year; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <a href="cotisations.php" class="reset-link"><?php echo __("Reset Filters"); ?></a>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    <?php if (empty($cotisations)): ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-alt"></i>
                            <p><?php echo __("No cotisations found. Try adjusting your filters."); ?></p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th><?php echo __("Property"); ?></th>
                                        <th><?php echo __("Year"); ?></th>
                                        <th><?php echo __("Amount Due"); ?></th>
                                        <th><?php echo __("Amount Paid"); ?></th>
                                        <th><?php echo __("Balance"); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cotisations as $cotisation): 
                                        $remaining = $cotisation['amount_due'] - $cotisation['amount_paid'];
                                        $balance_class = $remaining <= 0 ? 'balance-paid' : ($remaining < $cotisation['amount_due'] * 0.5 ? 'balance-pending' : 'balance-overdue');
                                    ?>
                                        <tr>
                                            <td>
                                                <?php if (isset($cotisation['property_id']) && $cotisation['property_id']): ?>
                                                    <a href="view-property.php?id=<?php echo $cotisation['property_id']; ?>" class="property-link">
                                                        <?php echo htmlspecialchars($cotisation['property_identifier']); ?>
                                                    </a>
                                                    <small class="text-muted property-type"><?php echo ucfirst(htmlspecialchars($cotisation['property_type'])); ?></small>
                                                    <?php if ($cotisation['user_name']): ?>
                                                        <small class="text-muted user-name"><?php echo htmlspecialchars($cotisation['user_name']); ?></small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><strong><?php echo $cotisation['year']; ?></strong></td>
                                            <td><?php echo number_format($cotisation['amount_due'], 2); ?> MAD</td>
                                            <td><?php echo number_format($cotisation['amount_paid'], 2); ?> MAD</td>
                                            <td>
                                                <span class="balance-indicator <?php echo $balance_class; ?>">
                                                    <?php echo number_format($remaining, 2); ?> MAD
                                                </span>
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
                                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&year=<?php echo urlencode($year_filter); ?>" class="pagination-link">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&year=<?php echo urlencode($year_filter); ?>" 
                                       class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&year=<?php echo urlencode($year_filter); ?>" class="pagination-link">
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
        // Auto-submit search on input with debounce
        const filterForm = document.getElementById('filter-form');
        const searchInput = filterForm.querySelector('input[name="search"]');
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            // Clear previous timeout
            clearTimeout(searchTimeout);
            
            // Set new timeout to submit after user stops typing for 500ms
            searchTimeout = setTimeout(function() {
                filterForm.submit();
            }, 500);
        });
        
        // Also submit on Enter key press (immediate)
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(searchTimeout);
                filterForm.submit();
            }
        });
    </script>
</body>
</html>

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
$status_filter = $_GET['status'] ?? '';
$payment_method_filter = $_GET['payment_method'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

// Get total count and payments list
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Build the query - select payments with user and property info
    $query = "SELECT p.*, 
              p.type as payment_method,
              u.name as user_name, 
              pr.identifier as property_identifier 
              FROM payments p 
              LEFT JOIN properties pr ON p.property_id = pr.id 
              LEFT JOIN users u ON pr.user_id = u.id 
              WHERE 1=1";
    $count_query = "SELECT COUNT(*) as total FROM payments p WHERE 1=1";
    $params = [];
    
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
    
    // Add ordering
    $query .= " ORDER BY p.payment_date DESC LIMIT :offset, :limit";
    
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
    
    // Get payment statistics
    // Total amounts
    $stats_query = "SELECT 
                    SUM(amount) as total_amount,
                    COUNT(*) as total_count
                    FROM payments";
    $stats_stmt = $db->prepare($stats_query);
    $stats_stmt->execute();
    $payment_stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Status statistics
    $status_query = "SELECT status, COUNT(*) as count, SUM(amount) as total 
                     FROM payments 
                     GROUP BY status";
    $status_stmt = $db->prepare($status_query);
    $status_stmt->execute();
    $status_stats = [];
    while ($row = $status_stmt->fetch(PDO::FETCH_ASSOC)) {
        $status_stats[$row['status']] = [
            'count' => $row['count'],
            'total' => $row['total']
        ];
    }
    
    // Payment method statistics
    $method_query = "SELECT type as payment_method, COUNT(*) as count
                    FROM payments
                    GROUP BY type";
    $method_stmt = $db->prepare($method_query);
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
                <a href="add-payment.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <?php echo __("Add Payment"); ?>
                </a>
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
                            <span><i class="fas fa-circle" style="color: #28a745;"></i> <?php echo __("Credit Card"); ?>: <?php echo isset($method_stats['credit_card']) ? $method_stats['credit_card'] : 0; ?></span>
                            <span><i class="fas fa-circle" style="color: #ffc107;"></i> <?php echo __("Bank Transfer"); ?>: <?php echo isset($method_stats['bank_transfer']) ? $method_stats['bank_transfer'] : 0; ?></span>
                            <span><i class="fas fa-circle" style="color: #dc3545;"></i> <?php echo __("Other"); ?>: <?php echo isset($method_stats['other']) ? $method_stats['other'] : 0; ?></span>
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
                            <p><?php echo __("No payments found. Try adjusting your filters or add a new payment."); ?></p>
                            <a href="add-payment.php" class="btn btn-primary"><?php echo __("Add Payment"); ?></a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th><?php echo __("ID"); ?></th>
                                        <th><?php echo __("Property"); ?></th>
                                        <th><?php echo __("Amount"); ?></th>
                                        <th><?php echo __("Method"); ?></th>
                                        <th><?php echo __("Status"); ?></th>
                                        <th><?php echo __("Date"); ?></th>
                                        <th><?php echo __("Actions"); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $payment): ?>
                                        <tr>
                                            <td><?php echo $payment['payment_id'] ?? $payment['id']; ?></td>
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
                                                    $statusClass = 'secondary';
                                                    switch (strtolower($payment['status'])) {
                                                        case 'paid': 
                                                        case 'paid': $statusClass = 'success'; break;
                                                        case 'pending': $statusClass = 'warning'; break;
                                                        case 'failed': $statusClass = 'danger'; break;
                                                        case 'refunded': $statusClass = 'primary'; break;
                                                        case 'cancelled': $statusClass = 'secondary'; break;
                                                        default: $statusClass = 'secondary'; break;
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
                                                <a href="edit-payment.php?id=<?php echo $payment['id']; ?>" class="btn-icon" title="<?php echo __("Edit Payment"); ?>">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="javascript:void(0);" class="btn-icon delete-payment" data-id="<?php echo $payment['id']; ?>" title="<?php echo __("Delete Payment"); ?>">
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
                                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&payment_method=<?php echo urlencode($payment_method_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>" class="pagination-link">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&payment_method=<?php echo urlencode($payment_method_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>" 
                                       class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&payment_method=<?php echo urlencode($payment_method_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>" class="pagination-link">
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
                <p><?php echo __("Are you sure you want to delete this payment? This action cannot be undone."); ?></p>
            </div>
            <div class="modal-footer">
                <form id="deleteForm" action="delete-payment.php" method="POST">
                    <input type="hidden" name="payment_id" id="deletePaymentId">
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
        
        // Delete payment modal functionality
        const modal = document.getElementById('deleteModal');
        const deleteButtons = document.querySelectorAll('.delete-payment');
        const closeButtons = document.querySelectorAll('.close, .close-modal');
        const deleteForm = document.getElementById('deleteForm');
        const deletePaymentIdInput = document.getElementById('deletePaymentId');
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const paymentId = this.getAttribute('data-id');
                deletePaymentIdInput.value = paymentId;
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
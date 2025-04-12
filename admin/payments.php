<?php
// Start session
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Vous devez être connecté en tant qu'administrateur pour accéder à cette page.";
    header("Location: ../login.php");
    exit();
}

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
        $query .= " AND p.month >= :date_from";
        $count_query .= " AND p.month >= :date_from";
        $params[':date_from'] = $date_from;
    }
    
    if (!empty($date_to)) {
        $query .= " AND p.month <= :date_to";
        $count_query .= " AND p.month <= :date_to";
        $params[':date_to'] = $date_to;
    }
    
    // Add ordering
    $query .= " ORDER BY p.month DESC LIMIT :offset, :limit";
    
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
$page_title = "Gestion des Paiements";
?>

<!DOCTYPE html>
<html lang="fr">
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
        <?php include 'includes/admin-sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <h1>Gestion des Paiements</h1>
                <a href="add-payment.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Enregistrer un Nouveau Paiement
                </a>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon payments">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-details">
                        <h3>Total des Paiements</h3>
                        <div class="stat-number"><?php echo isset($payment_stats['total_count']) ? $payment_stats['total_count'] : 0; ?></div>
                        <div class="stat-breakdown">
                            <span><?php echo isset($payment_stats['total_amount']) ? number_format($payment_stats['total_amount'], 2) : '0.00'; ?> € Total</span>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon tickets">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3>Terminés</h3>
                        <div class="stat-number"><?php echo isset($status_stats['completed']['count']) ? $status_stats['completed']['count'] : 0; ?></div>
                        <div class="stat-breakdown">
                            <span><?php echo isset($status_stats['completed']['total']) ? number_format($status_stats['completed']['total'], 2) : '0.00'; ?> €</span>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon users">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div class="stat-details">
                        <h3>En Attente</h3>
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
                        <h3>Méthodes de Paiement</h3>
                        <div class="stat-number"><?php echo $total; ?> Total</div>
                        <div class="stat-breakdown">
                            <span><i class="fas fa-circle" style="color: #28a745;"></i> Carte Bancaire: <?php echo isset($method_stats['credit_card']) ? $method_stats['credit_card'] : 0; ?></span>
                            <span><i class="fas fa-circle" style="color: #ffc107;"></i> Virement: <?php echo isset($method_stats['bank_transfer']) ? $method_stats['bank_transfer'] : 0; ?></span>
                            <span><i class="fas fa-circle" style="color: #dc3545;"></i> Autre: <?php echo isset($method_stats['other']) ? $method_stats['other'] : 0; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card user-filter-card">
                <div class="card-header user-filter-header">
                    <h3><i class="fas fa-filter"></i> Liste des Paiements</h3>
                    <form id="filter-form" action="payments.php" method="GET" class="filter-form">
                        <div class="filter-wrapper">
                            <div class="search-filter">
                                <div class="search-bar">
                                    <i class="fas fa-search"></i>
                                    <input type="text" placeholder="Rechercher des paiements..." name="search" value="<?php echo htmlspecialchars($search); ?>" autocomplete="off" autofocus>
                                </div>
                            </div>
                            <div class="filter-group">
                                <label for="status">Statut:</label>
                                <select name="status" id="status" onchange="this.form.submit()">
                                    <option value="">Tous les Statuts</option>
                                    <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Terminé</option>
                                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>En Attente</option>
                                    <option value="failed" <?php echo $status_filter === 'failed' ? 'selected' : ''; ?>>Échoué</option>
                                    <option value="refunded" <?php echo $status_filter === 'refunded' ? 'selected' : ''; ?>>Remboursé</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label for="payment_method">Méthode:</label>
                                <select name="payment_method" id="payment_method" onchange="this.form.submit()">
                                    <option value="">Toutes les Méthodes</option>
                                    <option value="credit_card" <?php echo $payment_method_filter === 'credit_card' ? 'selected' : ''; ?>>Carte Bancaire</option>
                                    <option value="bank_transfer" <?php echo $payment_method_filter === 'bank_transfer' ? 'selected' : ''; ?>>Virement</option>
                                    <option value="other" <?php echo $payment_method_filter === 'other' ? 'selected' : ''; ?>>Autre</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label for="date_from">De:</label>
                                <input type="date" id="date_from" name="date_from" value="<?php echo $date_from; ?>">
                            </div>
                            <div class="filter-group">
                                <label for="date_to">À:</label>
                                <input type="date" id="date_to" name="date_to" value="<?php echo $date_to; ?>">
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">Appliquer</button>
                            <a href="payments.php" class="reset-link">Réinitialiser</a>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    <?php if (empty($payments)): ?>
                        <div class="empty-state">
                            <i class="fas fa-credit-card"></i>
                            <p>Aucun paiement trouvé. Essayez d'ajuster vos filtres ou d'ajouter un nouveau paiement.</p>
                            <a href="add-payment.php" class="btn btn-primary">Ajouter un Paiement</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Résident</th>
                                        <th>Propriété</th>
                                        <th>Montant</th>
                                        <th>Méthode</th>
                                        <th>Statut</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $payment): ?>
                                        <tr>
                                            <td><?php echo $payment['payment_id'] ?? $payment['id']; ?></td>
                                            <td>
                                                <?php if (isset($payment['user_id']) && $payment['user_id']): ?>
                                                    <a href="view-user.php?id=<?php echo $payment['user_id']; ?>" class="user-link">
                                                        <?php echo htmlspecialchars($payment['user_name']); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
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
                                                    $paymentMethodDisplay = $payment_method ? ucfirst(str_replace('_', ' ', $payment_method)) : 'Other';
                                                    echo $methodIcon . $paymentMethodDisplay;
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                    $statusClass = '';
                                                    switch ($payment['status']) {
                                                        case 'completed': $statusClass = 'success'; break;
                                                        case 'pending': $statusClass = 'warning'; break;
                                                        case 'failed': $statusClass = 'danger'; break;
                                                        case 'refunded': $statusClass = 'primary'; break;
                                                    }
                                                ?>
                                                <span class="status-indicator status-<?php echo $statusClass; ?>">
                                                    <?php echo ucfirst($payment['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($payment['month'])); ?></td>
                                            <td class="actions">
                                                <a href="view-payment.php?id=<?php echo $payment['id']; ?>" class="btn-icon" title="View Payment">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="edit-payment.php?id=<?php echo $payment['id']; ?>" class="btn-icon" title="Edit Payment">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="javascript:void(0);" class="btn-icon delete-payment" data-id="<?php echo $payment['id']; ?>" title="Delete Payment">
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
                <h3>Confirm Deletion</h3>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this payment? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <form id="deleteForm" action="delete-payment.php" method="POST">
                    <input type="hidden" name="payment_id" id="deletePaymentId">
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
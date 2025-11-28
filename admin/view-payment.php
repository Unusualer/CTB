<?php
// Start session and check if user is logged in as admin
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/role_access.php';
require_once '../includes/translations.php';

// Check if user is logged in and has appropriate role
requireRole('admin');

// Check if payment ID is set
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = __("Payment ID is required.");
    header("Location: payments.php");
    exit();
}

$payment_id = intval($_GET['id']);
$payment = null;
$error_message = '';

// Fetch payment details
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = "SELECT p.*, p.type as payment_method, pr.identifier as property_identifier, pr.type as property_type, 
            u.name as user_name, u.email as user_email, u.phone as user_phone, u.id as user_id
            FROM payments p 
            LEFT JOIN properties pr ON p.property_id = pr.id
            LEFT JOIN users u ON pr.user_id = u.id
            WHERE p.id = :id";

    $stmt = $db->prepare($sql);
    $stmt->bindParam(":id", $payment_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        $_SESSION['error'] = __("Payment not found.");
        header("Location: payments.php");
        exit();
    }
    
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Handle form submission for updating status
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
        $new_status = $_POST['status'];
        $admin_notes = $_POST['admin_notes'];
        
        // Check if admin_notes column exists
        $check_column_sql = "SHOW COLUMNS FROM payments LIKE 'admin_notes'";
        $check_stmt = $db->prepare($check_column_sql);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() === 0) {
            // Add admin_notes column if it doesn't exist
            $alter_sql = "ALTER TABLE payments ADD COLUMN admin_notes TEXT DEFAULT NULL";
            $alter_stmt = $db->prepare($alter_sql);
            $alter_stmt->execute();
        }
        
        $update_sql = "UPDATE payments SET status = :status, admin_notes = :notes, updated_at = NOW() WHERE id = :id";
        $update_stmt = $db->prepare($update_sql);
        $update_stmt->bindParam(":status", $new_status, PDO::PARAM_STR);
        $update_stmt->bindParam(":notes", $admin_notes, PDO::PARAM_STR);
        $update_stmt->bindParam(":id", $payment_id, PDO::PARAM_INT);
        
        if ($update_stmt->execute()) {
            // Log the activity
            $admin_id = $_SESSION['user_id'];
            log_activity($db, $admin_id, 'update', 'payment', $payment_id, __("Updated payment") . " #$payment_id " . __("status to:") . " " . ucfirst($new_status));
            
            $_SESSION['success'] = __("Payment successfully updated.");
            header("Location: view-payment.php?id=$payment_id");
            exit();
        } else {
            $error_message = __("Failed to update payment status.");
        }
    }
    
} catch (PDOException $e) {
    $error_message = __("Database error:") . " " . $e->getMessage();
}

// Get status class for styling
function getStatusBadgeClass($status) {
    switch($status) {
        case 'paid':
        case 'completed':
            return 'success';
        case 'pending':
            return 'warning';
        case 'cancelled':
            return 'secondary';
        case 'failed':
            return 'danger';
        case 'refunded':
            return 'primary';
        default:
            return 'secondary';
    }
}

// Format payment method for display
function formatPaymentMethod($method) {
    if (empty($method)) {
        return __("Unknown");
    }
    return __(ucfirst(str_replace('_', ' ', $method)));
}

// Format date helper function
function formatDate($date) {
    // Get current language
    $language = $_SESSION['language'] ?? 'en_US';
    
    if (empty($date)) {
        return __("Unknown date");
    }
    
    $timestamp = strtotime($date);
    $month_num = date('n', $timestamp) - 1; // 0-based month index
    $day = date('j', $timestamp);
    $year = date('Y', $timestamp);
    
    // Month names in different languages
    $months = [
        'en_US' => ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
        'fr_FR' => ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'],
        'es_ES' => ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre']
    ];
    
    // Ensure the language is supported, otherwise fallback to English
    if (!isset($months[$language])) {
        $language = 'en_US';
    }
    
    $month_name = $months[$language][$month_num];
    
    // Format based on language
    switch ($language) {
        case 'fr_FR':
            return $day . ' ' . $month_name . ' ' . $year;
        case 'es_ES':
            return $day . ' de ' . $month_name . ' de ' . $year;
        default:
            return $month_name . ' ' . $day . ', ' . $year;
    }
}

// Page title
$page_title = __("Payment Details");
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
        .payment-details {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        
        @media (min-width: 768px) {
            .payment-details {
                grid-template-columns: 2fr 1fr;
            }
        }
        
        .detail-group {
            margin-bottom: 1rem;
        }
        
        .detail-group .label {
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: var(--text-secondary);
        }
        
        .detail-group .value {
            font-size: 1rem;
        }
        
        .meta-info {
            color: var(--text-secondary);
            font-size: 0.875rem;
            border-top: 1px solid var(--border-color);
            padding-top: 1rem;
            margin-top: 1.5rem;
        }
        
        .description-box {
            background-color: var(--light-color);
            padding: 1.25rem;
            border-radius: 0.5rem;
            border: 1px solid var(--border-color);
            margin: 1rem 0;
            white-space: pre-line;
            line-height: 1.6;
        }
        
        .empty-state {
            text-align: center;
            padding: 2rem;
            color: var(--text-secondary);
        }
        
        .empty-state i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            display: block;
        }
        
        .card-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0.375rem;
            background-color: var(--light-color);
        }
        
        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }
        
        /* Dark mode specific styles */
        [data-theme="dark"] .description-box {
            background-color: #2a2e35;
            border-color: #3f4756;
        }
        
        [data-theme="dark"] .form-control {
            background-color: #2a2e35;
            color: #ffffff;
            border-color: #3f4756;
        }
        
        [data-theme="dark"] .form-control::placeholder {
            color: #8e99ad;
        }
        
        [data-theme="dark"] .meta-info {
            color: #a0a0a0;
            border-color: #3f4756;
        }
        
        [data-theme="dark"] .detail-group .label {
            color: #a0a0a0;
        }
        
        [data-theme="dark"] .empty-state {
            color: #a0a0a0;
        }
        
        [data-theme="dark"] .card {
            background-color: var(--card-bg);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }
        
        [data-theme="dark"] .modal-content {
            background-color: var(--card-bg);
            color: #ffffff;
            border-color: #3f4756;
        }
        
        [data-theme="dark"] .close, 
        [data-theme="dark"] .close-modal {
            color: #ffffff;
        }
        
        [data-theme="dark"] .modal-header,
        [data-theme="dark"] .modal-footer {
            border-color: #3f4756;
        }
        
        [data-theme="dark"] .btn-secondary {
            background-color: #3f4756;
            color: #ffffff;
            border: none;
        }
        
        [data-theme="dark"] .btn-secondary:hover {
            background-color: #4a5469;
        }
        
        [data-theme="dark"] .text-muted {
            color: #a0a0a0 !important;
        }
        
        [data-theme="dark"] small.form-text.text-muted {
            color: #a0a0a0 !important;
        }
        
        /* Status indicator with colored backgrounds and white text */
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
        
        .btn-block {
            display: block;
            width: 100%;
            text-align: center;
        }
        
        .mt-3 {
            margin-top: 1rem;
        }
        
        .mt-4 {
            margin-top: 1.5rem;
        }
        
        [data-theme="dark"] .detail-group .value a {
            color: var(--primary-color-light);
            text-decoration: none;
        }
        
        [data-theme="dark"] .detail-group .value a:hover {
            color: var(--primary-color);
            text-decoration: underline;
        }
        
        [data-theme="dark"] select.form-control {
            background-color: #2a2e35;
            color: #ffffff;
            border-color: #3f4756;
        }
        
        [data-theme="dark"] select.form-control:focus {
            background-color: #2d3239;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.15);
        }
        
        [data-theme="dark"] option {
            background-color: #2a2e35;
            color: #ffffff;
        }
        
        [data-theme="dark"] .card-header {
            background: linear-gradient(to right, var(--primary-color), var(--primary-color-dark));
        }
        
        [data-theme="dark"] .card-header h3 {
            color: #ffffff;
        }
        
        [data-theme="dark"] .form-group label {
            color: #ffffff;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/admin-sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <div class="breadcrumb">
                    <a href="payments.php"><?php echo __("Payments"); ?></a>
                    <span><?php echo __("View Payment"); ?></span>
                </div>
                <div class="actions">
                    <a href="edit-payment.php?id=<?php echo $payment_id; ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> <?php echo __("Edit Payment"); ?>
                    </a>
                    <a href="javascript:void(0);" class="btn btn-danger delete-payment" data-id="<?php echo $payment_id; ?>">
                        <i class="fas fa-trash-alt"></i> <?php echo __("Delete Payment"); ?>
                    </a>
                </div>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

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

            <?php if ($payment): ?>
                <div class="payment-details">
                    <!-- Payment Information Card -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-info-circle"></i> <?php echo __("Payment Information"); ?></h3>
                        </div>
                        <div class="card-body">
                            <div class="detail-group">
                                <div class="label"><?php echo __("ID"); ?></div>
                                <div class="value"><?php echo htmlspecialchars($payment['id']); ?></div>
                            </div>
                            
                            <div class="detail-group">
                                <div class="label"><?php echo __("Amount"); ?></div>
                                <div class="value">$<?php echo number_format($payment['amount'], 2); ?></div>
                            </div>
                            
                            <div class="detail-group">
                                <div class="label"><?php echo __("Status"); ?></div>
                                <div class="value">
                                    <span class="status-indicator status-<?php echo getStatusBadgeClass($payment['status']); ?>">
                                        <?php echo __($payment['status']); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="detail-group">
                                <div class="label"><?php echo __("Payment Method"); ?></div>
                                <div class="value"><?php echo formatPaymentMethod($payment['payment_method']); ?></div>
                            </div>
                            
                            <div class="detail-group">
                                <div class="label"><?php echo __("Payment Date"); ?></div>
                                <div class="value"><?php echo formatDate($payment['month']); ?></div>
                            </div>
                            
                            <div class="detail-group">
                                <div class="label"><?php echo __("Property"); ?></div>
                                <div class="value">
                                    <?php if (!empty($payment['property_identifier'])): ?>
                                        <a href="view-property.php?id=<?php echo $payment['property_id']; ?>">
                                            <?php echo htmlspecialchars($payment['property_identifier']); ?> 
                                            (<?php echo ucfirst($payment['property_type']); ?>)
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php if (!empty($payment['description'])): ?>
                            <div class="detail-group">
                                <div class="label"><?php echo __("Description"); ?></div>
                                <div class="description-box">
                                    <?php echo nl2br(htmlspecialchars($payment['description'])); ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="meta-info">
                                <div><i class="fas fa-calendar-plus"></i> <?php echo __("Created on"); ?>: <?php echo formatDate($payment['created_at']); ?></div>
                                <?php if (!empty($payment['updated_at']) && $payment['updated_at'] !== $payment['created_at']): ?>
                                <div><i class="fas fa-edit"></i> <?php echo __("Last updated"); ?>: <?php echo formatDate($payment['updated_at']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- User Information Card -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-user"></i> <?php echo __("User Information"); ?></h3>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($payment['user_name'])): ?>
                                <div class="detail-group">
                                    <div class="label"><?php echo __("Name"); ?></div>
                                    <div class="value">
                                        <a href="view-user.php?id=<?php echo $payment['user_id']; ?>">
                                            <?php echo htmlspecialchars($payment['user_name']); ?>
                                        </a>
                                    </div>
                                </div>
                                
                                <div class="detail-group">
                                    <div class="label"><?php echo __("Email"); ?></div>
                                    <div class="value"><?php echo htmlspecialchars($payment['user_email'] ?? 'N/A'); ?></div>
                                </div>
                                
                                <?php if (!empty($payment['user_phone'])): ?>
                                <div class="detail-group">
                                    <div class="label"><?php echo __("Phone"); ?></div>
                                    <div class="value"><?php echo htmlspecialchars($payment['user_phone']); ?></div>
                                </div>
                                <?php endif; ?>
                                
                                <div class="form-actions" style="margin-top: 2rem;">
                                    <a href="view-user.php?id=<?php echo $payment['user_id']; ?>" class="btn btn-secondary">
                                        <i class="fas fa-user"></i> <?php echo __("View User Profile"); ?>
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-user-slash"></i>
                                    <p><?php echo __("No user associated with this payment."); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Update Status Card -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h3><i class="fas fa-edit"></i> <?php echo __("Update Payment Status"); ?></h3>
                    </div>
                    <div class="card-body">
                        <form action="view-payment.php?id=<?php echo $payment_id; ?>" method="post">
                            <div class="form-group">
                                <label for="status"><?php echo __("Status"); ?></label>
                                <select id="status" name="status" class="form-control">
                                    <option value="completed" <?php echo $payment['status'] === 'completed' ? 'selected' : ''; ?>><?php echo __("Completed"); ?></option>
                                    <option value="pending" <?php echo $payment['status'] === 'pending' ? 'selected' : ''; ?>><?php echo __("Pending"); ?></option>
                                    <option value="failed" <?php echo $payment['status'] === 'failed' ? 'selected' : ''; ?>><?php echo __("Failed"); ?></option>
                                    <option value="refunded" <?php echo $payment['status'] === 'refunded' ? 'selected' : ''; ?>><?php echo __("Refunded"); ?></option>
                                    <option value="cancelled" <?php echo $payment['status'] === 'cancelled' ? 'selected' : ''; ?>><?php echo __("Cancelled"); ?></option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="admin_notes"><?php echo __("Admin Notes"); ?></label>
                                <textarea id="admin_notes" name="admin_notes" class="form-control" rows="3"><?php echo htmlspecialchars($payment['admin_notes'] ?? ''); ?></textarea>
                                <small class="form-text text-muted"><?php echo __("These notes are for administrative purposes only."); ?></small>
                            </div>
                            
                            <button type="submit" name="update_status" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo __("Save Changes"); ?>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
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
                    <input type="hidden" name="payment_id" id="deletePaymentId" value="<?php echo $payment_id; ?>">
                    <button type="button" class="btn btn-secondary close-modal"><?php echo __("Cancel"); ?></button>
                    <button type="submit" class="btn btn-danger"><?php echo __("Delete"); ?></button>
                </form>
            </div>
        </div>
    </div>

    <script src="js/dark-mode.js"></script>
    <!-- Add the modal JavaScript -->
    <script>
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
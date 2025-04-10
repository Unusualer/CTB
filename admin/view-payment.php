<?php
// Start session and check if user is logged in as admin
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Connect to database
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if payment ID is set
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: payments.php");
    exit();
}

$payment_id = intval($_GET['id']);

// Fetch payment details
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = "SELECT p.*, p.type as payment_method, pr.identifier as property_identifier, pr.type as property_type, 
            u.name as user_name, u.email as user_email, u.phone as user_phone
            FROM payments p 
            LEFT JOIN properties pr ON p.property_id = pr.id
            LEFT JOIN users u ON pr.user_id = u.id
            WHERE p.id = :id";

    $stmt = $db->prepare($sql);
    $stmt->bindParam(":id", $payment_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        header("Location: payments.php");
        exit();
    }
    
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Handle form submission for updating status
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
        $new_status = $_POST['status'];
        $admin_notes = $_POST['admin_notes'];
        
        $update_sql = "UPDATE payments SET status = :status, admin_notes = :notes, updated_at = NOW() WHERE id = :id";
        $update_stmt = $db->prepare($update_sql);
        $update_stmt->bindParam(":status", $new_status, PDO::PARAM_STR);
        $update_stmt->bindParam(":notes", $admin_notes, PDO::PARAM_STR);
        $update_stmt->bindParam(":id", $payment_id, PDO::PARAM_INT);
        
        if ($update_stmt->execute()) {
            // Refresh the page to show updated info
            header("Location: view-payment.php?id=$payment_id&success=1");
            exit();
        }
    }
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    header("Location: payments.php");
    exit();
}

// Get status class for styling
function getStatusClass($status) {
    switch($status) {
        case 'completed':
            return 'success';
        case 'pending':
            return 'warning';
        case 'failed':
            return 'danger';
        case 'refunded':
            return 'primary';
        default:
            return 'primary';
    }
}

// Format payment method for display
function formatPaymentMethod($method) {
    if (empty($method)) {
        return 'Unknown';
    }
    return ucfirst(str_replace('_', ' ', $method));
}

// Page title
$page_title = "Payment Details";
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
                    <li class="active">
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
                    <li>
                        <a href="settings.php">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
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
            <div class="content-header">
                <a href="payments.php" class="back-button">
                    <i class="fas fa-arrow-left"></i> Back to Payments
                </a>
                <h1>Payment Details</h1>
            </div>

            <!-- Page Content -->
            <div class="page-content">
                <!-- Success message -->
                <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <span>Payment updated successfully!</span>
                    <button class="close-btn">&times;</button>
                </div>
                <?php endif; ?>

                <div class="ticket-details-container">
                    <div class="ticket-details-grid">
                        <!-- Payment Information -->
                        <div class="ticket-details-card">
                            <div class="card-header">
                                <h2>Payment Information</h2>
                            </div>
                            <div class="card-body">
                                <div class="detail-row">
                                    <span class="detail-label">Payment ID:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($payment['payment_id'] ?? $payment['id']); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Amount:</span>
                                    <span class="detail-value">$<?php echo number_format($payment['amount'], 2); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Status:</span>
                                    <span class="detail-value">
                                        <span class="status-indicator status-<?php echo getStatusClass($payment['status']); ?>">
                                            <?php echo ucfirst($payment['status']); ?>
                                        </span>
                                    </span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Payment Method:</span>
                                    <span class="detail-value">
                                        <?php 
                                            $methodIcon = '';
                                            $paymentMethod = isset($payment['payment_method']) ? $payment['payment_method'] : 'transfer';
                                            switch ($paymentMethod) {
                                                case 'transfer': 
                                                    $methodIcon = '<i class="fas fa-university"></i> ';
                                                    break;
                                                case 'cheque': 
                                                    $methodIcon = '<i class="fas fa-money-check"></i> ';
                                                    break;
                                                default: 
                                                    $methodIcon = '<i class="fas fa-money-check"></i> ';
                                            }
                                            echo $methodIcon . formatPaymentMethod($paymentMethod);
                                        ?>
                                    </span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Transaction ID:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($payment['transaction_id'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Description:</span>
                                    <span class="detail-value description-text"><?php echo nl2br(htmlspecialchars($payment['description'] ?? 'No description available')); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Payment Month:</span>
                                    <span class="detail-value"><?php echo date('M d, Y', strtotime($payment['month'])); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Created:</span>
                                    <span class="detail-value"><?php echo date('M d, Y H:i', strtotime($payment['created_at'])); ?></span>
                                </div>
                                <?php if (isset($payment['updated_at'])): ?>
                                <div class="detail-row">
                                    <span class="detail-label">Last Updated:</span>
                                    <span class="detail-value"><?php echo date('M d, Y H:i', strtotime($payment['updated_at'])); ?></span>
                                </div>
                                <?php endif; ?>
                                <div class="detail-row">
                                    <span class="detail-label">Payment Type:</span>
                                    <span class="detail-value">Monthly Subscription</span>
                                </div>
                            </div>
                        </div>

                        <!-- User Information -->
                        <div class="ticket-details-card">
                            <div class="card-header">
                                <h2>User Information</h2>
                            </div>
                            <div class="card-body">
                                <?php if (isset($payment['user_id']) && $payment['user_id']): ?>
                                    <div class="detail-row">
                                        <span class="detail-label">Name:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($payment['user_name']); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Email:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($payment['user_email']); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Phone:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($payment['user_phone'] ?? 'N/A'); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">User ID:</span>
                                        <span class="detail-value"><?php echo $payment['user_id']; ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Actions:</span>
                                        <span class="detail-value">
                                            <a href="view-user.php?id=<?php echo $payment['user_id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-user"></i> View User Profile
                                            </a>
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fas fa-user-slash"></i>
                                        <p>No user associated with this payment.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Property Information -->
                        <div class="ticket-details-card">
                            <div class="card-header">
                                <h2>Property Information</h2>
                            </div>
                            <div class="card-body">
                                <?php if (isset($payment['property_id']) && $payment['property_id']): ?>
                                    <div class="detail-row">
                                        <span class="detail-label">Identifier:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($payment['property_identifier'] ?? 'N/A'); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Type:</span>
                                        <span class="detail-value"><?php echo ucfirst(htmlspecialchars($payment['property_type'] ?? 'N/A')); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Actions:</span>
                                        <span class="detail-value">
                                            <a href="view-property.php?id=<?php echo $payment['property_id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-home"></i> View Property
                                            </a>
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fas fa-building"></i>
                                        <p>No property associated with this payment.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Admin Response -->
                        <div class="ticket-details-card">
                            <div class="card-header">
                                <h2>Admin Response</h2>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="form-group">
                                        <label for="status">Update Status:</label>
                                        <select name="status" id="status" class="form-control">
                                            <option value="pending" <?php echo $payment['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="paid" <?php echo $payment['status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                            <option value="cancelled" <?php echo $payment['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            <option value="failed" <?php echo $payment['status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="admin_notes">Admin Notes:</label>
                                        <textarea name="admin_notes" id="admin_notes" class="form-control" rows="5"><?php echo htmlspecialchars($payment['admin_notes'] ?? ''); ?></textarea>
                                    </div>
                                    <div class="form-buttons">
                                        <button type="submit" name="update_status" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Update Payment
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
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
        
        // Close alert messages
        const closeButtons = document.querySelectorAll('.close-btn');
        closeButtons.forEach(button => {
            button.addEventListener('click', function() {
                const alert = button.parentElement;
                alert.style.display = 'none';
            });
        });
    </script>
</body>
</html> 
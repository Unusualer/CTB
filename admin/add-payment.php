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
$error = '';
$success = '';
$payment = [
    'user_id' => '',
    'property_id' => '',
    'amount' => '',
    'payment_method' => 'credit_card',
    'payment_date' => date('Y-m-d'),
    'status' => 'completed',
    'transaction_id' => '',
    'description' => ''
];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Validate and sanitize input
        $user_id = !empty($_POST['user_id']) ? intval($_POST['user_id']) : null;
        $property_id = !empty($_POST['property_id']) ? intval($_POST['property_id']) : null;
        $amount = !empty($_POST['amount']) ? floatval($_POST['amount']) : 0;
        $payment_method = !empty($_POST['payment_method']) ? $_POST['payment_method'] : 'credit_card';
        $payment_date = !empty($_POST['payment_date']) ? $_POST['payment_date'] : date('Y-m-d');
        $status = !empty($_POST['status']) ? $_POST['status'] : 'completed';
        $transaction_id = !empty($_POST['transaction_id']) ? $_POST['transaction_id'] : null;
        $description = !empty($_POST['description']) ? $_POST['description'] : null;
        
        // Generate a unique payment ID
        $payment_id = 'PAY-' . date('YmdHis') . '-' . substr(md5(uniqid()), 0, 6);
        
        // Validate required fields
        if (empty($user_id) || empty($amount) || $amount <= 0) {
            throw new Exception("User and amount are required fields. Amount must be greater than zero.");
        }
        
        // Insert payment record
        $query = "INSERT INTO payments (
                    payment_id, 
                    user_id, 
                    property_id, 
                    amount, 
                    payment_method, 
                    payment_date, 
                    status, 
                    transaction_id, 
                    description, 
                    created_at
                ) VALUES (
                    :payment_id, 
                    :user_id, 
                    :property_id, 
                    :amount, 
                    :payment_method, 
                    :payment_date, 
                    :status, 
                    :transaction_id, 
                    :description, 
                    NOW()
                )";
                
        $stmt = $db->prepare($query);
        $stmt->bindParam(':payment_id', $payment_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':property_id', $property_id);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':payment_method', $payment_method);
        $stmt->bindParam(':payment_date', $payment_date);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':transaction_id', $transaction_id);
        $stmt->bindParam(':description', $description);
        
        if ($stmt->execute()) {
            $payment_record_id = $db->lastInsertId();
            
            // Log activity
            logActivity($db, $_SESSION['user_id'], 'payment', $payment_record_id, 'create', "Added new payment: $payment_id for $amount");
            
            $success = "Payment added successfully!";
            
            // Clear form data
            $payment = [
                'user_id' => '',
                'property_id' => '',
                'amount' => '',
                'payment_method' => 'credit_card',
                'payment_date' => date('Y-m-d'),
                'status' => 'completed',
                'transaction_id' => '',
                'description' => ''
            ];
        } else {
            throw new Exception("Failed to add payment. Please try again.");
        }
        
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
        
        // Preserve form data
        $payment = [
            'user_id' => $_POST['user_id'] ?? '',
            'property_id' => $_POST['property_id'] ?? '',
            'amount' => $_POST['amount'] ?? '',
            'payment_method' => $_POST['payment_method'] ?? 'credit_card',
            'payment_date' => $_POST['payment_date'] ?? date('Y-m-d'),
            'status' => $_POST['status'] ?? 'completed',
            'transaction_id' => $_POST['transaction_id'] ?? '',
            'description' => $_POST['description'] ?? ''
        ];
    } catch (Exception $e) {
        $error = $e->getMessage();
        
        // Preserve form data
        $payment = [
            'user_id' => $_POST['user_id'] ?? '',
            'property_id' => $_POST['property_id'] ?? '',
            'amount' => $_POST['amount'] ?? '',
            'payment_method' => $_POST['payment_method'] ?? 'credit_card',
            'payment_date' => $_POST['payment_date'] ?? date('Y-m-d'),
            'status' => $_POST['status'] ?? 'completed',
            'transaction_id' => $_POST['transaction_id'] ?? '',
            'description' => $_POST['description'] ?? ''
        ];
    }
}

// Fetch users for dropdown
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $users_query = "SELECT id, name, email FROM users ORDER BY name";
    $users_stmt = $db->prepare($users_query);
    $users_stmt->execute();
    $users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $properties_query = "SELECT id, identifier, title FROM properties ORDER BY identifier";
    $properties_stmt = $db->prepare($properties_query);
    $properties_stmt->execute();
    $properties = $properties_stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    $users = [];
    $properties = [];
}

// Page title
$page_title = "Add New Payment";
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
    <style>
        .form-row {
            margin-bottom: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            background-color: var(--light-color);
            color: var(--text-primary);
            font-family: inherit;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }
        
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.2);
        }
        
        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
            border: 1px solid rgba(40, 167, 69, 0.2);
        }
    </style>
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
            <header class="topbar">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search..." disabled>
                </div>
                
                <div class="topbar-right">
                    <div class="notifications">
                        <i class="fas fa-bell"></i>
                        <span class="badge">3</span>
                    </div>
                    <div class="messages">
                        <i class="fas fa-envelope"></i>
                        <span class="badge">5</span>
                    </div>
                </div>
            </header>

            <div class="content-header">
                <a href="payments.php" class="back-button">
                    <i class="fas fa-arrow-left"></i> Back to Payments
                </a>
                <h1><?php echo $page_title; ?></h1>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3>Payment Details</h3>
                </div>
                <div class="card-body">
                    <form action="add-payment.php" method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="user_id">User <span class="text-danger">*</span></label>
                                <select name="user_id" id="user_id" class="form-control" required>
                                    <option value="">-- Select User --</option>
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?php echo $user['id']; ?>" <?php echo $payment['user_id'] == $user['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['email']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="property_id">Property</label>
                                <select name="property_id" id="property_id" class="form-control">
                                    <option value="">-- Select Property (Optional) --</option>
                                    <?php foreach ($properties as $property): ?>
                                        <option value="<?php echo $property['id']; ?>" <?php echo $payment['property_id'] == $property['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($property['identifier']); ?> - <?php echo htmlspecialchars($property['title']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="amount">Amount ($) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0.01" name="amount" id="amount" class="form-control" value="<?php echo htmlspecialchars($payment['amount']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="payment_method">Payment Method <span class="text-danger">*</span></label>
                                <select name="payment_method" id="payment_method" class="form-control" required>
                                    <option value="credit_card" <?php echo $payment['payment_method'] === 'credit_card' ? 'selected' : ''; ?>>Credit Card</option>
                                    <option value="bank_transfer" <?php echo $payment['payment_method'] === 'bank_transfer' ? 'selected' : ''; ?>>Bank Transfer</option>
                                    <option value="cash" <?php echo $payment['payment_method'] === 'cash' ? 'selected' : ''; ?>>Cash</option>
                                    <option value="other" <?php echo $payment['payment_method'] === 'other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="payment_date">Payment Date <span class="text-danger">*</span></label>
                                <input type="date" name="payment_date" id="payment_date" class="form-control" value="<?php echo htmlspecialchars($payment['payment_date']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="status">Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-control" required>
                                    <option value="completed" <?php echo $payment['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="pending" <?php echo $payment['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="failed" <?php echo $payment['status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                                    <option value="refunded" <?php echo $payment['status'] === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="transaction_id">Transaction ID</label>
                                <input type="text" name="transaction_id" id="transaction_id" class="form-control" value="<?php echo htmlspecialchars($payment['transaction_id']); ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea name="description" id="description" class="form-control" rows="4"><?php echo htmlspecialchars($payment['description']); ?></textarea>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <a href="payments.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Add Payment</button>
                        </div>
                    </form>
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
    </script>
</body>
</html> 
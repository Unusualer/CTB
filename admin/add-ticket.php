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

$statuses = ['open', 'in_progress', 'closed', 'reopened'];
$users = [];

// Get all users for the dropdown
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $users_stmt = $db->prepare("SELECT id, name, email FROM users ORDER BY name ASC");
    $users_stmt->execute();
    $users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    $user_id = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    $subject = trim($_POST['subject'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = trim($_POST['status'] ?? '');
    
    $errors = [];
    
    // Validate user_id
    if (empty($user_id)) {
        $errors[] = "User is required.";
    }
    
    // Validate subject
    if (empty($subject)) {
        $errors[] = "Subject is required.";
    }
    
    // Validate description
    if (empty($description)) {
        $errors[] = "Description is required.";
    }
    
    // Validate status
    if (empty($status) || !in_array($status, $statuses)) {
        $errors[] = "Valid status is required.";
    }
    
    // If no errors, add ticket
    if (empty($errors)) {
        try {
            $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Insert new ticket
            $stmt = $db->prepare("INSERT INTO tickets (user_id, subject, description, status, created_at, updated_at) 
                                 VALUES (:user_id, :subject, :description, :status, NOW(), NOW())");
            
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':subject', $subject);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':status', $status);
            
            $stmt->execute();
            
            $ticket_id = $db->lastInsertId();
            
            // Log the activity
            $admin_id = $_SESSION['user_id'];
            log_activity($db, $admin_id, 'create', 'ticket', $ticket_id, "Created new ticket: $subject");
            
            $_SESSION['success'] = "Ticket created successfully.";
            header("Location: tickets.php");
            exit();
            
        } catch (PDOException $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
}

// Page title
$page_title = "Add New Ticket";
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
        /* Enhanced Form Styling */
        .card {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }
        
        .card-header {
            padding: 18px 24px;
            border-bottom: none;
        }
        
        .card-header h3 {
            font-weight: 600;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card-header h3 i {
            font-size: 1.1rem;
        }
        
        .card-body {
            padding: 30px;
        }
        
        .form-group label {
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.95rem;
            letter-spacing: 0.2px;
            display: inline-block;
        }
        
        .required {
            color: #ff5c75;
            font-weight: 700;
        }
        
        input, select, textarea {
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background-color: var(--light-color);
            color: var(--text-primary);
            font-size: 0.95rem;
            transition: all 0.2s ease;
            width: 100%;
        }
        
        input:hover, select:hover, textarea:hover {
            border-color: var(--primary-color-light);
        }
        
        input:focus, select:focus, textarea:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.15);
            outline: none;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 24px;
        }
        
        .form-section-title {
            margin: 30px 0 20px;
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 24px;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-color-dark);
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background-color: var(--secondary-bg);
            color: var(--text-primary);
        }
        
        .btn-secondary:hover {
            background-color: var(--border-color);
            transform: translateY(-1px);
        }
        
        small {
            color: var(--text-secondary);
            font-size: 0.8rem;
            margin-top: 5px;
            display: block;
        }
        
        /* Dark mode specific styles */
        [data-theme="dark"] .card-header {
            background: linear-gradient(to right, var(--primary-color), var(--primary-color-dark));
        }
        
        [data-theme="dark"] .card-header h3 {
            color: #fff;
        }
        
        [data-theme="dark"] .form-group label {
            color: #ffffff;
            font-weight: 600;
        }
        
        [data-theme="dark"] .card {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            background-color: var(--card-bg);
        }
        
        [data-theme="dark"] input, 
        [data-theme="dark"] select,
        [data-theme="dark"] textarea {
            background-color: #2a2e35 !important;
            color: #ffffff !important;
            border-color: #3f4756;
        }
        
        [data-theme="dark"] input:hover, 
        [data-theme="dark"] select:hover,
        [data-theme="dark"] textarea:hover {
            border-color: var(--primary-color-light);
        }
        
        [data-theme="dark"] input:focus, 
        [data-theme="dark"] select:focus,
        [data-theme="dark"] textarea:focus {
            border-color: var(--primary-color);
            background-color: #2d3239 !important;
        }
        
        [data-theme="dark"] input::placeholder {
            color: #8e99ad;
        }
        
        [data-theme="dark"] .form-section-title {
            color: #ffffff;
            border-color: #3f4756;
        }
        
        [data-theme="dark"] .required {
            color: #ff7a8e;
        }
        
        [data-theme="dark"] small {
            color: #b0b0b0;
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
        
        [data-theme="dark"] .alert-danger {
            background-color: rgba(220, 53, 69, 0.15);
            color: #ff6b6b;
        }
        
        [data-theme="dark"] .alert-success {
            background-color: rgba(40, 167, 69, 0.15);
            color: #2ecc71;
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
                    <li class="active">
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
                    <li>
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
                <div class="breadcrumb">
                    <a href="tickets.php">Tickets</a>
                    <span>Add New Ticket</span>
                </div>
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

            <div class="content-wrapper">
                <div class="card user-filter-card">
                    <div class="card-header user-filter-header">
                        <h3><i class="fas fa-ticket-alt"></i> Add New Ticket</h3>
                    </div>
                    <div class="card-body">
                        <form action="add-ticket.php" method="POST" class="form">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="user_id">User <span class="required">*</span></label>
                                    <select id="user_id" name="user_id" required>
                                        <option value="" disabled <?php echo empty($user_id ?? '') ? 'selected' : ''; ?>>Select User</option>
                                        <?php foreach ($users as $user): ?>
                                            <option value="<?php echo $user['id']; ?>" <?php echo isset($user_id) && $user_id === $user['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($user['name'] . ' (' . $user['email'] . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="status">Status <span class="required">*</span></label>
                                    <select id="status" name="status" required>
                                        <option value="" disabled <?php echo empty($status ?? '') ? 'selected' : ''; ?>>Select Status</option>
                                        <?php foreach ($statuses as $s): ?>
                                            <option value="<?php echo $s; ?>" <?php echo isset($status) && $status === $s ? 'selected' : ''; ?>>
                                                <?php echo ucfirst(str_replace('_', ' ', $s)); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="subject">Subject <span class="required">*</span></label>
                                    <input type="text" id="subject" name="subject" value="<?php echo htmlspecialchars($subject ?? ''); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Description <span class="required">*</span></label>
                                <textarea id="description" name="description" rows="6" required><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                            </div>
                            
                            <div class="form-actions">
                                <a href="tickets.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus-circle"></i> Create Ticket
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="js/dark-mode.js"></script>
</body>
</html> 
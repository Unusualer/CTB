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

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Ticket ID is required.";
    header("Location: tickets.php");
    exit();
}

$ticket_id = (int)$_GET['id'];
$ticket = null;
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
    $admin_notes = trim($_POST['admin_notes'] ?? '');
    
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
    
    // If no errors, update ticket
    if (empty($errors)) {
        try {
            $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Check if admin_notes column exists
            $check_column_sql = "SHOW COLUMNS FROM tickets LIKE 'admin_notes'";
            $check_stmt = $db->prepare($check_column_sql);
            $check_stmt->execute();
            
            if ($check_stmt->rowCount() === 0) {
                // Add admin_notes column if it doesn't exist
                $alter_sql = "ALTER TABLE tickets ADD COLUMN admin_notes TEXT DEFAULT NULL";
                $alter_stmt = $db->prepare($alter_sql);
                $alter_stmt->execute();
            }
            
            // Update ticket
            $stmt = $db->prepare("UPDATE tickets SET user_id = :user_id, subject = :subject, 
                                 description = :description, status = :status, 
                                 admin_notes = :admin_notes, updated_at = NOW() 
                                 WHERE id = :id");
            
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':subject', $subject);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':admin_notes', $admin_notes);
            $stmt->bindParam(':id', $ticket_id, PDO::PARAM_INT);
            
            $stmt->execute();
            
            // Log the activity
            $admin_id = $_SESSION['user_id'];
            log_activity($db, $admin_id, 'update', 'ticket', $ticket_id, "Updated ticket #$ticket_id: $subject");
            
            $_SESSION['success'] = "Ticket updated successfully.";
            header("Location: view-ticket.php?id=$ticket_id");
            exit();
            
        } catch (PDOException $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
}

// Get ticket data
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch ticket details
    $stmt = $db->prepare("SELECT * FROM tickets WHERE id = :id");
    $stmt->bindParam(':id', $ticket_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        $_SESSION['error'] = "Ticket not found.";
        header("Location: tickets.php");
        exit();
    }
    
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    header("Location: tickets.php");
    exit();
}

// Page title
$page_title = "Edit Ticket";
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
                    <a href="tickets.php">Tickets</a> / 
                    <a href="view-ticket.php?id=<?php echo $ticket_id; ?>">View Ticket</a> / 
                    <span>Edit Ticket</span>
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
                        <h3><i class="fas fa-edit"></i> Edit Ticket #<?php echo $ticket_id; ?></h3>
                    </div>
                    <div class="card-body">
                        <form action="edit-ticket.php?id=<?php echo $ticket_id; ?>" method="POST" class="form">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="user_id">User <span class="required">*</span></label>
                                    <select id="user_id" name="user_id" required>
                                        <option value="" disabled>Select User</option>
                                        <?php foreach ($users as $user): ?>
                                            <option value="<?php echo $user['id']; ?>" <?php echo $ticket['user_id'] == $user['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($user['name'] . ' (' . $user['email'] . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="status">Status <span class="required">*</span></label>
                                    <select id="status" name="status" required>
                                        <?php foreach ($statuses as $status): ?>
                                            <option value="<?php echo $status; ?>" <?php echo $ticket['status'] === $status ? 'selected' : ''; ?>>
                                                <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="subject">Subject <span class="required">*</span></label>
                                    <input type="text" id="subject" name="subject" value="<?php echo htmlspecialchars($ticket['subject']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Description <span class="required">*</span></label>
                                <textarea id="description" name="description" rows="6" required><?php echo htmlspecialchars($ticket['description']); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="admin_notes">Admin Notes</label>
                                <textarea id="admin_notes" name="admin_notes" rows="4"><?php echo isset($ticket['admin_notes']) ? htmlspecialchars($ticket['admin_notes']) : ''; ?></textarea>
                                <small>Internal notes visible only to administrators</small>
                            </div>
                            
                            <div class="form-actions">
                                <a href="view-ticket.php?id=<?php echo $ticket_id; ?>" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update Ticket</button>
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
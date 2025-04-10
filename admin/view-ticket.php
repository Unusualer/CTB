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

// Get ticket data
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch ticket details with user information
    $stmt = $db->prepare("SELECT t.*, u.name, u.email, u.phone
                          FROM tickets t 
                          JOIN users u ON t.user_id = u.id
                          WHERE t.id = :id");
    $stmt->bindParam(':id', $ticket_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        $_SESSION['error'] = "Ticket not found.";
        header("Location: tickets.php");
        exit();
    }
    
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get activity log for this ticket
    $log_stmt = $db->prepare("SELECT * FROM activity_log 
                             WHERE entity_type = 'ticket' AND entity_id = :ticket_id 
                             ORDER BY created_at DESC LIMIT 10");
    $log_stmt->bindParam(':ticket_id', $ticket_id, PDO::PARAM_INT);
    $log_stmt->execute();
    $activity_logs = $log_stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    header("Location: tickets.php");
    exit();
}

// Function to get status class for styling
function getStatusClass($status) {
    switch($status) {
        case 'open':
            return 'danger';
        case 'in_progress':
            return 'warning';
        case 'closed':
            return 'success';
        case 'reopened':
            return 'primary';
        default:
            return 'primary';
    }
}

// Page title
$page_title = "View Ticket";
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
                    <a href="tickets.php">Tickets</a>
                    <span>View Ticket</span>
                </div>
                <div class="actions">
                    <a href="edit-ticket.php?id=<?php echo $ticket['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Ticket
                    </a>
                    <a href="javascript:void(0);" class="btn btn-danger delete-ticket" data-id="<?php echo $ticket['id']; ?>">
                        <i class="fas fa-trash-alt"></i> Delete Ticket
                    </a>
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
                <div class="profile-header card">
                    <div class="profile-header-content">
                        <div class="profile-avatar">
                            <div class="avatar-placeholder">
                                <i class="fas fa-ticket-alt"></i>
                            </div>
                        </div>
                        <div class="profile-details">
                            <div class="profile-name-wrapper">
                                <h2><?php echo htmlspecialchars($ticket['subject']); ?></h2>
                                <div class="user-id-badge">ID: <?php echo $ticket['id']; ?></div>
                            </div>
                            <div class="profile-meta">
                                <span class="user-status <?php echo getStatusClass($ticket['status']); ?>">
                                    <i class="fas fa-circle"></i> <?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($ticket['status']))); ?>
                                </span>
                                <span class="user-joined"><i class="far fa-calendar-alt"></i> Created <?php echo date('M d, Y', strtotime($ticket['created_at'])); ?></span>
                                <span class="user-joined"><i class="far fa-clock"></i> Updated <?php echo date('M d, Y', strtotime($ticket['updated_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-grid">
                    <!-- Ticket Description Card -->
                    <div class="card user-info-card">
                        <div class="card-header user-filter-header">
                            <h3><i class="fas fa-info-circle"></i> Ticket Description</h3>
                        </div>
                        <div class="card-body">
                            <div class="ticket-description">
                                <?php echo nl2br(htmlspecialchars($ticket['description'])); ?>
                            </div>
                        </div>
                    </div>

                    <!-- User Information Card -->
                    <div class="card user-info-card">
                        <div class="card-header user-filter-header">
                            <h3><i class="fas fa-user"></i> User Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="info-grid">
                                <div class="info-group">
                                    <label><i class="fas fa-user"></i> Name:</label>
                                    <span class="info-value"><?php echo htmlspecialchars($ticket['name']); ?></span>
                                </div>
                                <div class="info-group">
                                    <label><i class="fas fa-envelope"></i> Email:</label>
                                    <span class="info-value"><?php echo htmlspecialchars($ticket['email']); ?></span>
                                </div>
                                <div class="info-group">
                                    <label><i class="fas fa-phone"></i> Phone:</label>
                                    <span class="info-value"><?php echo !empty($ticket['phone']) ? htmlspecialchars($ticket['phone']) : 'Not provided'; ?></span>
                                </div>
                                <div class="info-group">
                                    <label><i class="fas fa-id-card"></i> User ID:</label>
                                    <span class="info-value"><?php echo $ticket['user_id']; ?></span>
                                </div>
                                <div class="info-group">
                                    <label><i class="fas fa-user-circle"></i> View Profile:</label>
                                    <span class="info-value">
                                        <a href="view-user.php?id=<?php echo $ticket['user_id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-user"></i> User Profile
                                        </a>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status Update Card -->
                    <div class="card">
                        <div class="card-header user-filter-header">
                            <h3><i class="fas fa-edit"></i> Update Status</h3>
                        </div>
                        <div class="card-body">
                            <form action="update-ticket-status.php" method="POST" class="form">
                                <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                
                                <div class="form-group">
                                    <label for="status">Status:</label>
                                    <select id="status" name="status" class="form-control">
                                        <option value="open" <?php echo $ticket['status'] === 'open' ? 'selected' : ''; ?>>Open</option>
                                        <option value="in_progress" <?php echo $ticket['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                        <option value="closed" <?php echo $ticket['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                                        <option value="reopened" <?php echo $ticket['status'] === 'reopened' ? 'selected' : ''; ?>>Reopened</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="admin_notes">Admin Notes:</label>
                                    <textarea id="admin_notes" name="admin_notes" rows="4" class="form-control"><?php echo isset($ticket['admin_notes']) ? htmlspecialchars($ticket['admin_notes']) : ''; ?></textarea>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">Update Status</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Activity Log Card -->
                    <div class="card activity-card">
                        <div class="card-header user-filter-header">
                            <h3><i class="fas fa-history"></i> Activity History</h3>
                        </div>
                        <div class="card-body">
                            <?php if (empty($activity_logs)): ?>
                                <div class="no-data">
                                    <i class="far fa-clock"></i>
                                    <p>No activity records found for this ticket.</p>
                                </div>
                            <?php else: ?>
                                <div class="activity-timeline">
                                    <?php foreach ($activity_logs as $log): ?>
                                        <div class="activity-item">
                                            <div class="activity-icon">
                                                <i class="fas fa-circle"></i>
                                            </div>
                                            <div class="activity-content">
                                                <p class="activity-text"><?php echo isset($log['details']) ? htmlspecialchars($log['details']) : (isset($log['description']) ? htmlspecialchars($log['description']) : 'No description available'); ?></p>
                                                <p class="activity-time"><?php echo date('M d, Y H:i', strtotime($log['created_at'])); ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
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
                <p>Are you sure you want to delete this ticket? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <form id="deleteForm" action="delete-ticket.php" method="POST">
                    <input type="hidden" name="ticket_id" id="deleteTicketId">
                    <button type="button" class="btn btn-secondary close-modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <script src="js/dark-mode.js"></script>
    <script>
        // Delete ticket modal functionality
        const modal = document.getElementById('deleteModal');
        const deleteButtons = document.querySelectorAll('.delete-ticket');
        const closeButtons = document.querySelectorAll('.close, .close-modal');
        const deleteForm = document.getElementById('deleteForm');
        const deleteTicketIdInput = document.getElementById('deleteTicketId');
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const ticketId = this.getAttribute('data-id');
                deleteTicketIdInput.value = ticketId;
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
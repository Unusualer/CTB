<?php
// Start session and check if user is logged in as admin
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Connect to database
require_once '../includes/db_connect.php';
$conn = getConnection();

// Check if ticket ID is set
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: tickets.php");
    exit();
}

$ticket_id = intval($_GET['id']);

// Handle ticket deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_ticket'])) {
    $delete_sql = "DELETE FROM tickets WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $ticket_id);
    
    if ($delete_stmt->execute()) {
        // Log the activity
        $admin_id = $_SESSION['user_id'];
        $description = "Deleted ticket #$ticket_id";
        $log_sql = "INSERT INTO activity_log (user_id, action, description) VALUES (?, 'delete', ?)";
        $log_stmt = $conn->prepare($log_sql);
        $log_stmt->bind_param("is", $admin_id, $description);
        $log_stmt->execute();
        
        // Redirect to tickets list with success message
        header("Location: tickets.php?deleted=1");
        exit();
    } else {
        $delete_error = "Failed to delete ticket. Please try again.";
    }
}

// Fetch ticket details
$sql = "SELECT t.*, u.name, u.email, u.phone
        FROM tickets t 
        JOIN users u ON t.user_id = u.id
        WHERE t.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: tickets.php");
    exit();
}

$ticket = $result->fetch_assoc();

// Handle form submission for updating status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $admin_notes = $_POST['admin_notes'];
    
    $update_sql = "UPDATE tickets SET status = ?, admin_notes = ?, updated_at = NOW() WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssi", $new_status, $admin_notes, $ticket_id);
    
    if ($update_stmt->execute()) {
        // Refresh the page to show updated info
        header("Location: ticket-details.php?id=$ticket_id&success=1");
        exit();
    }
}

// Get status class for styling
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

// Get priority class for styling
function getPriorityClass($priority) {
    switch($priority) {
        case 'low':
            return 'success';
        case 'medium':
            return 'warning';
        case 'high':
            return 'danger';
        case 'critical':
            return 'danger';
        default:
            return 'primary';
    }
}

// Format status for display
function formatStatus($status) {
    return ucfirst(str_replace('_', ' ', $status));
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Details - Community Trust Bank</title>
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
                    <a href="edit-ticket.php?id=<?php echo $ticket_id; ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Ticket
                    </a>
                    <a href="javascript:void(0);" class="btn btn-danger delete-ticket" data-id="<?php echo $ticket_id; ?>">
                        <i class="fas fa-trash-alt"></i> Delete Ticket
                    </a>
                </div>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <span>Ticket updated successfully!</span>
                </div>
            <?php endif; ?>

            <div class="ticket-details-container">
                <div class="ticket-details-card">
                    <div class="ticket-header">
                        <h2>Ticket #<?php echo $ticket_id; ?></h2>
                        <div class="ticket-status <?php echo getStatusClass($ticket['status']); ?>">
                            <?php echo formatStatus($ticket['status']); ?>
                        </div>
                    </div>
                    
                    <div class="ticket-content">
                        <div class="ticket-info">
                            <div class="info-group">
                                <div class="info-label">Created By</div>
                                <div class="info-value"><?php echo htmlspecialchars($ticket['name']); ?></div>
                            </div>
                            
                            <div class="info-group">
                                <div class="info-label">Created On</div>
                                <div class="info-value"><?php echo date('F j, Y, g:i a', strtotime($ticket['created_at'])); ?></div>
                            </div>
                            
                            <div class="info-group">
                                <div class="info-label">Priority</div>
                                <div class="info-value">
                                    <span class="priority-badge <?php echo getPriorityClass($ticket['priority']); ?>">
                                        <?php echo ucfirst($ticket['priority']); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="info-group">
                                <div class="info-label">Category</div>
                                <div class="info-value"><?php echo htmlspecialchars($ticket['category']); ?></div>
                            </div>
                            
                            <div class="info-group">
                                <div class="info-label">Property</div>
                                <div class="info-value">
                                    <?php 
                                    if (!empty($ticket['property_id'])) {
                                        echo "Property #" . htmlspecialchars($ticket['property_id']);
                                        if (!empty($ticket['identifier'])) {
                                            echo " (" . htmlspecialchars($ticket['identifier']) . ")";
                                        }
                                    } else {
                                        echo "N/A";
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="ticket-description">
                            <h3>Description</h3>
                            <p><?php echo nl2br(htmlspecialchars($ticket['description'])); ?></p>
                        </div>
                        
                        <!-- Admin Response Section -->
                        <div class="ticket-updates">
                            <h3>Admin Response</h3>
                            
                            <?php if (!empty($ticket['admin_response'])): ?>
                            <div class="admin-response">
                                <p><?php echo nl2br(htmlspecialchars($ticket['admin_response'])); ?></p>
                                <div class="response-meta">
                                    <span>Updated: <?php echo date('F j, Y, g:i a', strtotime($ticket['updated_at'])); ?></span>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="no-response">
                                <p>No response has been added yet.</p>
                            </div>
                            <?php endif; ?>
                            
                            <form id="responseForm" action="update-ticket-status.php" method="post" class="response-form">
                                <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
                                
                                <div class="form-group">
                                    <label for="status">Update Status</label>
                                    <select name="status" id="status" class="form-control" required>
                                        <option value="">Select Status</option>
                                        <option value="open" <?php echo ($ticket['status'] == 'open') ? 'selected' : ''; ?>>Open</option>
                                        <option value="in_progress" <?php echo ($ticket['status'] == 'in_progress') ? 'selected' : ''; ?>>In Progress</option>
                                        <option value="resolved" <?php echo ($ticket['status'] == 'resolved') ? 'selected' : ''; ?>>Resolved</option>
                                        <option value="closed" <?php echo ($ticket['status'] == 'closed') ? 'selected' : ''; ?>>Closed</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="admin_response">Response</label>
                                    <textarea name="admin_response" id="admin_response" class="form-control" rows="5"><?php echo htmlspecialchars($ticket['admin_response'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">Update Ticket</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="js/dark-mode.js"></script>
    <script>
        // Close alert messages
        const closeButtons = document.querySelectorAll('.close-btn');
        closeButtons.forEach(button => {
            button.addEventListener('click', () => {
                const alert = button.parentElement;
                alert.style.display = 'none';
            });
        });
        
        // Toggle sidebar
        const toggleSidebar = document.querySelector('.toggle-sidebar');
        const adminContainer = document.querySelector('.admin-container');
        
        if (toggleSidebar) {
            toggleSidebar.addEventListener('click', () => {
                adminContainer.classList.toggle('sidebar-collapsed');
            });
        }
    </script>

    <!-- Delete Ticket Modal -->
    <div class="modal" id="deleteTicketModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirm Deletion</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this ticket? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $ticket_id); ?>" method="post">
                    <input type="hidden" name="delete_ticket" value="1">
                    <button type="button" class="btn btn-secondary cancel-delete">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Ticket</button>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Delete ticket modal functionality
        const deleteButton = document.querySelector('.delete-ticket');
        const deleteModal = document.getElementById('deleteTicketModal');
        const closeModal = document.querySelector('.close-modal');
        const cancelDelete = document.querySelector('.cancel-delete');
        
        if (deleteButton) {
            deleteButton.addEventListener('click', function() {
                deleteModal.style.display = 'block';
            });
        }
        
        if (closeModal) {
            closeModal.addEventListener('click', function() {
                deleteModal.style.display = 'none';
            });
        }
        
        if (cancelDelete) {
            cancelDelete.addEventListener('click', function() {
                deleteModal.style.display = 'none';
            });
        }
        
        window.addEventListener('click', function(event) {
            if (event.target == deleteModal) {
                deleteModal.style.display = 'none';
            }
        });
    });
    </script>
</body>
</html> 
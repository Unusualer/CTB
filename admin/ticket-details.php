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

// Fetch ticket details
$sql = "SELECT t.*, u.first_name, u.last_name, u.email, u.phone_number, p.title as property_title, 
        p.location as property_location, p.price as property_price, p.type as property_type, 
        p.image as property_image
        FROM tickets t 
        JOIN users u ON t.user_id = u.id
        JOIN properties p ON t.property_id = p.id
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
        case 'reported':
            return 'danger';
        case 'in_progress':
            return 'warning';
        case 'completed':
            return 'success';
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
    <title>Ticket Details - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="toggle-sidebar">
                    <i class="fas fa-bars"></i>
                </div>
                <div class="user-info">
                    <span>Admin</span>
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="dark-mode-toggle" id="darkModeToggle">
                    <i class="fas fa-moon"></i>
                </div>
            </div>

            <!-- Page Content -->
            <div class="page-content">
                <!-- Success message -->
                <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <span>Ticket updated successfully!</span>
                    <button class="close-btn">&times;</button>
                </div>
                <?php endif; ?>

                <!-- Header with back button -->
                <div class="content-header">
                    <a href="tickets.php" class="back-button">
                        <i class="fas fa-arrow-left"></i> Back to Tickets
                    </a>
                    <h1>Ticket #<?php echo $ticket_id; ?></h1>
                </div>

                <div class="ticket-details-container">
                    <div class="ticket-details-grid">
                        <!-- Ticket Information -->
                        <div class="ticket-details-card">
                            <div class="card-header">
                                <h2>Ticket Information</h2>
                            </div>
                            <div class="card-body">
                                <div class="detail-row">
                                    <span class="detail-label">Title:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($ticket['title']); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Description:</span>
                                    <span class="detail-value description-text"><?php echo nl2br(htmlspecialchars($ticket['description'])); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Status:</span>
                                    <span class="detail-value">
                                        <span class="priority-indicator priority-<?php echo getStatusClass($ticket['status']); ?>">
                                            <?php echo formatStatus($ticket['status']); ?>
                                        </span>
                                    </span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Priority:</span>
                                    <span class="detail-value">
                                        <span class="priority-indicator priority-<?php echo getPriorityClass($ticket['priority']); ?>">
                                            <?php echo ucfirst($ticket['priority']); ?>
                                        </span>
                                    </span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Created:</span>
                                    <span class="detail-value"><?php echo date('M d, Y H:i', strtotime($ticket['created_at'])); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Last Updated:</span>
                                    <span class="detail-value"><?php echo date('M d, Y H:i', strtotime($ticket['updated_at'])); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- User Information -->
                        <div class="ticket-details-card">
                            <div class="card-header">
                                <h2>User Information</h2>
                            </div>
                            <div class="card-body">
                                <div class="detail-row">
                                    <span class="detail-label">Name:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($ticket['first_name'] . ' ' . $ticket['last_name']); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Email:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($ticket['email']); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Phone:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($ticket['phone_number']); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">User ID:</span>
                                    <span class="detail-value"><?php echo $ticket['user_id']; ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Actions:</span>
                                    <span class="detail-value">
                                        <a href="user-details.php?id=<?php echo $ticket['user_id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-user"></i> View User Profile
                                        </a>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Property Information -->
                        <div class="ticket-details-card">
                            <div class="card-header">
                                <h2>Property Information</h2>
                            </div>
                            <div class="card-body">
                                <div class="property-image">
                                    <img src="../uploads/properties/<?php echo htmlspecialchars($ticket['property_image']); ?>" alt="Property Image">
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Title:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($ticket['property_title']); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Location:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($ticket['property_location']); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Type:</span>
                                    <span class="detail-value"><?php echo ucfirst(htmlspecialchars($ticket['property_type'])); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Price:</span>
                                    <span class="detail-value">$<?php echo number_format($ticket['property_price']); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Actions:</span>
                                    <span class="detail-value">
                                        <a href="property-details.php?id=<?php echo $ticket['property_id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-home"></i> View Property
                                        </a>
                                    </span>
                                </div>
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
                                            <option value="reported" <?php echo $ticket['status'] === 'reported' ? 'selected' : ''; ?>>Reported</option>
                                            <option value="in_progress" <?php echo $ticket['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                            <option value="completed" <?php echo $ticket['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="admin_notes">Admin Notes:</label>
                                        <textarea name="admin_notes" id="admin_notes" class="form-control" rows="5"><?php echo htmlspecialchars($ticket['admin_notes'] ?? ''); ?></textarea>
                                    </div>
                                    <div class="form-buttons">
                                        <button type="submit" name="update_status" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Update Ticket
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Dark mode toggle functionality
        const darkModeToggle = document.getElementById('darkModeToggle');
        const body = document.body;
        
        // Check if dark mode is enabled in localStorage
        if (localStorage.getItem('darkMode') === 'enabled') {
            body.classList.add('dark-mode');
            darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
        }
        
        darkModeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            
            if (body.classList.contains('dark-mode')) {
                localStorage.setItem('darkMode', 'enabled');
                darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
            } else {
                localStorage.setItem('darkMode', 'disabled');
                darkModeToggle.innerHTML = '<i class="fas fa-moon"></i>';
            }
        });
        
        // Toggle sidebar
        const toggleSidebar = document.querySelector('.toggle-sidebar');
        const adminContainer = document.querySelector('.admin-container');
        
        toggleSidebar.addEventListener('click', () => {
            adminContainer.classList.toggle('sidebar-collapsed');
        });
        
        // Close alert messages
        const closeButtons = document.querySelectorAll('.close-btn');
        closeButtons.forEach(button => {
            button.addEventListener('click', () => {
                const alert = button.parentElement;
                alert.style.display = 'none';
            });
        });
    </script>
</body>
</html> 
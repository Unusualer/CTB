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
    $_SESSION['error'] = "User ID is required.";
    header("Location: users.php");
    exit();
}

$user_id = (int)$_GET['id'];
$user = null;
$assigned_properties = [];

// Get user data
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch user details
    $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        $_SESSION['error'] = "User not found.";
        header("Location: users.php");
        exit();
    }
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // If user is a resident, get assigned properties
    if ($user['role'] === 'resident') {
        $prop_stmt = $db->prepare("SELECT * FROM properties WHERE user_id = :user_id");
        $prop_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $prop_stmt->execute();
        $assigned_properties = $prop_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get activity log for this user
    $log_stmt = $db->prepare("SELECT * FROM activity_log WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 10");
    $log_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $log_stmt->execute();
    $activity_logs = $log_stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    header("Location: users.php");
    exit();
}

// Page title
$page_title = "View User";
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
                    <li class="active">
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
                    <a href="users.php">Users</a>
                    <span>View User</span>
                </div>
                <div class="actions">
                    <a href="edit-user.php?id=<?php echo $user['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit User
                    </a>
                    <a href="javascript:void(0);" class="btn btn-danger delete-user" data-id="<?php echo $user['id']; ?>">
                        <i class="fas fa-trash-alt"></i> Delete User
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
                            <?php if (!empty($user['profile_image'])): ?>
                                <img src="../assets/img/avatars/<?php echo htmlspecialchars($user['profile_image']); ?>" alt="<?php echo htmlspecialchars($user['name']); ?>">
                            <?php else: ?>
                                <div class="avatar-placeholder">
                                    <i class="fas fa-user-circle"></i>
                                    <span class="avatar-initial"><?php echo substr(htmlspecialchars($user['name']), 0, 1); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="profile-details">
                            <div class="profile-name-wrapper">
                                <h2><?php echo htmlspecialchars($user['name']); ?></h2>
                                <div class="user-id-badge">ID: <?php echo $user['id']; ?></div>
                            </div>
                            <div class="profile-meta">
                                <span class="user-role"><i class="fas fa-user-tag"></i> <?php echo ucfirst(htmlspecialchars($user['role'])); ?></span>
                                <span class="user-status <?php echo $user['status']; ?>">
                                    <i class="fas fa-circle"></i> <?php echo ucfirst(htmlspecialchars($user['status'])); ?>
                                </span>
                                <span class="user-joined"><i class="far fa-calendar-alt"></i> Joined <?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-grid">
                    <!-- User Information Card -->
                    <div class="card user-info-card">
                        <div class="card-header">
                            <h3><i class="fas fa-info-circle"></i> User Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="info-grid">
                                <div class="info-group">
                                    <label><i class="fas fa-envelope"></i> Email:</label>
                                    <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                                </div>
                                <div class="info-group">
                                    <label><i class="fas fa-phone"></i> Phone:</label>
                                    <span class="info-value"><?php echo !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'Not provided'; ?></span>
                                </div>
                                <div class="info-group">
                                    <label><i class="fas fa-user-shield"></i> Role:</label>
                                    <span class="info-value"><?php echo ucfirst(htmlspecialchars($user['role'])); ?></span>
                                </div>
                                <div class="info-group">
                                    <label><i class="fas fa-check-circle"></i> Status:</label>
                                    <span class="status-badge <?php echo $user['status']; ?>"><?php echo ucfirst(htmlspecialchars($user['status'])); ?></span>
                                </div>
                                <div class="info-group">
                                    <label><i class="fas fa-calendar-plus"></i> Created:</label>
                                    <span class="info-value"><?php echo date('F d, Y', strtotime($user['created_at'])); ?></span>
                                </div>
                                <div class="info-group">
                                    <label><i class="fas fa-edit"></i> Last Updated:</label>
                                    <span class="info-value"><?php echo date('F d, Y', strtotime($user['updated_at'])); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resident Properties Card (if user is a resident) -->
                    <?php if ($user['role'] === 'resident'): ?>
                        <div class="card property-assignments-card">
                            <div class="card-header">
                                <h3><i class="fas fa-building"></i> Assigned Properties</h3>
                            </div>
                            <div class="card-body">
                                <?php if (empty($assigned_properties)): ?>
                                    <div class="empty-state">
                                        <i class="fas fa-building"></i>
                                        <p>No properties assigned to this resident.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="property-list">
                                        <?php foreach ($assigned_properties as $property): ?>
                                            <div class="property-item">
                                                <div class="property-icon">
                                                    <i class="<?php echo ($property['type'] === 'apartment') ? 'fas fa-home' : 'fas fa-car'; ?>"></i>
                                                </div>
                                                <div class="property-details">
                                                    <h4><?php echo htmlspecialchars($property['type'] . ' ' . $property['identifier']); ?></h4>
                                                    <div class="property-meta">
                                                        <span class="property-id">ID: <?php echo $property['id']; ?></span>
                                                        <a href="view-property.php?id=<?php echo $property['id']; ?>" class="view-link">
                                                            View Property <i class="fas fa-arrow-right"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Activity Log Card -->
                    <div class="card activity-card">
                        <div class="card-header">
                            <h3><i class="fas fa-history"></i> Recent Activity</h3>
                        </div>
                        <div class="card-body">
                            <?php if (empty($activity_logs)): ?>
                                <div class="no-data">
                                    <i class="far fa-clock"></i>
                                    <p>No recent activity found.</p>
                                </div>
                            <?php else: ?>
                                <div class="activity-timeline">
                                    <?php foreach ($activity_logs as $log): ?>
                                        <div class="activity-item">
                                            <div class="activity-icon">
                                                <i class="fas fa-circle"></i>
                                            </div>
                                            <div class="activity-content">
                                                <p class="activity-text"><?php echo isset($log['description']) ? htmlspecialchars($log['description']) : 'No description available'; ?></p>
                                                <p class="activity-time"><?php echo isset($log['created_at']) ? date('M d, Y H:i', strtotime($log['created_at'])) : 'Unknown date'; ?></p>
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
                <p>Are you sure you want to delete this user? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <form id="deleteForm" action="delete-user.php" method="POST">
                    <input type="hidden" name="user_id" id="deleteUserId">
                    <button type="button" class="btn btn-secondary close-modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <script src="js/dark-mode.js"></script>
    <script>
        // Delete user modal functionality
        const modal = document.getElementById('deleteModal');
        const deleteButtons = document.querySelectorAll('.delete-user');
        const closeButtons = document.querySelectorAll('.close, .close-modal');
        const deleteForm = document.getElementById('deleteForm');
        const deleteUserIdInput = document.getElementById('deleteUserId');
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                deleteUserIdInput.value = userId;
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
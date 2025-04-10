<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Redirect to login page
    header("Location: ../login.php?error=unauthorized");
    exit();
}

// Get admin user details
require_once '../includes/config.php';

$admin_id = $_SESSION['user_id'];
try {
    // Get admin details
    $admin_query = "SELECT * FROM users WHERE id = ? AND role = 'admin'";
    $stmt = $conn->prepare($admin_query);
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        // Invalid admin user
        session_destroy();
        header("Location: ../login.php?error=invalid_admin");
        exit();
    }

    // Get counts for dashboard
    $total_residents_query = "SELECT COUNT(*) FROM users WHERE role = 'resident'";
    $stmt = $conn->prepare($total_residents_query);
    $stmt->execute();
    $total_residents = $stmt->fetchColumn();

    $total_properties_query = "SELECT COUNT(*) FROM properties";
    $stmt = $conn->prepare($total_properties_query);
    $stmt->execute();
    $total_properties = $stmt->fetchColumn();

    $total_tickets_query = "SELECT COUNT(*) FROM tickets";
    $stmt = $conn->prepare($total_tickets_query);
    $stmt->execute();
    $total_tickets = $stmt->fetchColumn();

    $total_payments_query = "SELECT COUNT(*) FROM payments";
    $stmt = $conn->prepare($total_payments_query);
    $stmt->execute();
    $total_payments = $stmt->fetchColumn();

    // Count unresolved tickets
    $unresolved_tickets_query = "SELECT COUNT(*) FROM tickets WHERE status != 'closed'";
    $stmt = $conn->prepare($unresolved_tickets_query);
    $stmt->execute();
    $unresolved_tickets = $stmt->fetchColumn();
} catch (PDOException $e) {
    // Log error
    error_log("Database error in admin sidebar: " . $e->getMessage());
    $error_message = "A database error occurred.";
}

// Define current page
$current_page = basename($_SERVER['PHP_SELF']);

// Get notification counts (this would be replaced with actual database queries)
$pending_tickets = $unresolved_tickets ?? 0;
$new_messages = 0;  // This would be populated from a database query
?>

<div class="admin-sidebar">
    <div class="sidebar-brand">
        <h2>CTB Admin</h2>
    </div>
    
    <div class="admin-profile">
        <img src="../images/avatar-placeholder.png" alt="Admin Profile">
        <h4><?= htmlspecialchars($admin['name'] ?? 'Administrator') ?></h4>
        <p>Administrator</p>
    </div>
    
    <div class="sidebar-menu">
        <div class="menu-section">
            <div class="menu-header">Main</div>
            
            <a href="dashboard.php" class="menu-item <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                <div class="menu-icon"><i class="fas fa-tachometer-alt"></i></div>
                <span class="menu-text">Dashboard</span>
            </a>
            
            <a href="analytics.php" class="menu-item <?php echo ($current_page == 'analytics.php') ? 'active' : ''; ?>">
                <div class="menu-icon"><i class="fas fa-chart-bar"></i></div>
                <span class="menu-text">Analytics</span>
            </a>
        </div>
        
        <div class="menu-section">
            <div class="menu-header">Management</div>
            
            <a href="properties.php" class="menu-item has-submenu <?php echo (strpos($current_page, 'properties') !== false) ? 'open' : ''; ?>">
                <div class="menu-icon"><i class="fas fa-building"></i></div>
                <span class="menu-text">Properties</span>
            </a>
            
            <div class="submenu">
                <a href="properties-list.php" class="submenu-item <?php echo ($current_page == 'properties-list.php') ? 'active' : ''; ?>">
                    Property List
                </a>
                <a href="properties-add.php" class="submenu-item <?php echo ($current_page == 'properties-add.php') ? 'active' : ''; ?>">
                    Add Property
                </a>
                <a href="properties-maintenance.php" class="submenu-item <?php echo ($current_page == 'properties-maintenance.php') ? 'active' : ''; ?>">
                    Maintenance
                </a>
            </div>
            
            <a href="residents.php" class="menu-item has-submenu <?php echo (strpos($current_page, 'residents') !== false) ? 'open' : ''; ?>">
                <div class="menu-icon"><i class="fas fa-users"></i></div>
                <span class="menu-text">Residents</span>
            </a>
            
            <div class="submenu">
                <a href="residents-list.php" class="submenu-item <?php echo ($current_page == 'residents-list.php') ? 'active' : ''; ?>">
                    Resident List
                </a>
                <a href="residents-add.php" class="submenu-item <?php echo ($current_page == 'residents-add.php') ? 'active' : ''; ?>">
                    Add Resident
                </a>
                <a href="residents-approval.php" class="submenu-item <?php echo ($current_page == 'residents-approval.php') ? 'active' : ''; ?>">
                    Approval Requests
                </a>
            </div>
            
            <a href="payments.php" class="menu-item <?php echo ($current_page == 'payments.php') ? 'active' : ''; ?>">
                <div class="menu-icon"><i class="fas fa-money-bill-wave"></i></div>
                <span class="menu-text">Payments</span>
            </a>
            
            <a href="tickets.php" class="menu-item <?php echo ($current_page == 'tickets.php') ? 'active' : ''; ?>">
                <div class="menu-icon"><i class="fas fa-ticket-alt"></i></div>
                <span class="menu-text">Support Tickets</span>
                <?php if ($pending_tickets > 0): ?>
                <span class="menu-badge"><?php echo $pending_tickets; ?></span>
                <?php endif; ?>
            </a>
        </div>
        
        <div class="menu-section">
            <div class="menu-header">Communications</div>
            
            <a href="messages.php" class="menu-item <?php echo ($current_page == 'messages.php') ? 'active' : ''; ?>">
                <div class="menu-icon"><i class="fas fa-envelope"></i></div>
                <span class="menu-text">Messages</span>
                <?php if ($new_messages > 0): ?>
                <span class="menu-badge"><?php echo $new_messages; ?></span>
                <?php endif; ?>
            </a>
            
            <a href="announcements.php" class="menu-item <?php echo ($current_page == 'announcements.php') ? 'active' : ''; ?>">
                <div class="menu-icon"><i class="fas fa-bullhorn"></i></div>
                <span class="menu-text">Announcements</span>
            </a>
            
            <a href="activity-log.php" class="menu-item <?php echo ($current_page == 'activity-log.php') ? 'active' : ''; ?>">
                <div class="menu-icon"><i class="fas fa-history"></i></div>
                <span class="menu-text">Activity Log</span>
            </a>
        </div>
        
        <div class="menu-section">
            <div class="menu-header">Settings</div>
            
            <a href="profile.php" class="menu-item <?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
                <div class="menu-icon"><i class="fas fa-user-cog"></i></div>
                <span class="menu-text">Profile</span>
            </a>
            
            <a href="settings.php" class="menu-item <?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>">
                <div class="menu-icon"><i class="fas fa-cog"></i></div>
                <span class="menu-text">System Settings</span>
            </a>
            
            <a href="../logout.php" class="menu-item">
                <div class="menu-icon"><i class="fas fa-sign-out-alt"></i></div>
                <span class="menu-text">Logout</span>
            </a>
        </div>
    </div>
</div>

<div class="sidebar-overlay"></div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle submenu when parent menu item is clicked
        const menuItemsWithSubmenu = document.querySelectorAll('.menu-item.has-submenu');
        
        menuItemsWithSubmenu.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                this.classList.toggle('open');
            });
        });
        
        // Toggle sidebar on mobile
        const sidebarToggle = document.querySelector('.menu-toggle');
        const adminSidebar = document.querySelector('.admin-sidebar');
        const sidebarOverlay = document.querySelector('.sidebar-overlay');
        
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                adminSidebar.classList.toggle('open');
                sidebarOverlay.classList.toggle('active');
            });
        }
        
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', function() {
                adminSidebar.classList.remove('open');
                this.classList.remove('active');
            });
        }
    });
</script> 
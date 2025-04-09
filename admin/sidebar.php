<?php
// Ensure this file is included in a valid context
if (!defined('BASE_PATH')) {
    die('Direct access to this file is not allowed.');
}
?>

<div class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <div class="logo">
            <a href="<?php echo BASE_URL; ?>admin/dashboard.php">
                <img src="<?php echo BASE_URL; ?>assets/images/logo.png" alt="CTB Property Management" class="logo-lg">
                <img src="<?php echo BASE_URL; ?>assets/img/logo-icon.png" alt="CTB" class="logo-sm">
            </a>
        </div>
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-chevron-left"></i>
        </button>
    </div>
    
    <div class="sidebar-user">
        <div class="user-avatar">
            <img src="<?php echo BASE_URL; ?>assets/img/avatars/<?php echo $_SESSION['avatar'] ?? 'default.png'; ?>" alt="<?php echo htmlspecialchars($_SESSION['name'] ?? 'Admin User'); ?>">
        </div>
        <div class="user-info">
            <h5><?php echo htmlspecialchars($_SESSION['name'] ?? 'Admin User'); ?></h5>
            <span><?php echo htmlspecialchars($_SESSION['role'] ?? 'Administrator'); ?></span>
        </div>
    </div>
    
    <div class="sidebar-menu">
        <ul class="menu-items">
            <li class="menu-title">Main</li>
            
            <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <li class="menu-title">Management</li>
            
            <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/users.php">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
            </li>
            
            <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'properties.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/properties.php">
                    <i class="fas fa-building"></i>
                    <span>Properties</span>
                </a>
            </li>
            
            <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'tickets.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/tickets.php">
                    <i class="fas fa-ticket-alt"></i>
                    <span>Tickets</span>
                </a>
            </li>
            
            <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/payments.php">
                    <i class="fas fa-credit-card"></i>
                    <span>Payments</span>
                </a>
            </li>
            
            <li class="menu-title">System</li>
            
            <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'activity-log.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/activity-log.php">
                    <i class="fas fa-history"></i>
                    <span>Activity Log</span>
                </a>
            </li>
            
            <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/settings.php">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="<?php echo BASE_URL; ?>includes/logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>
    
    <div class="sidebar-footer">
        <div class="dark-mode-toggle">
            <label class="dark-mode-switch">
                <input type="checkbox" id="darkModeToggle">
                <span class="dark-mode-slider"></span>
            </label>
            <span class="dark-mode-label">Dark Mode</span>
        </div>
        <div class="sidebar-version">
            <span>v1.0.0</span>
        </div>
    </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Mobile sidebar toggle -->
<button class="mobile-toggle" id="mobileSidebarToggle">
    <i class="fas fa-bars"></i>
</button> 
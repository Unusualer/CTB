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
                <img src="<?php echo BASE_URL; ?>images/logo.png" alt="<?php echo __('CTB Property Management'); ?>" class="logo-lg">
                <img src="<?php echo BASE_URL; ?>images/logo.png" alt="<?php echo __('CTB'); ?>" class="logo-sm">
            </a>
        </div>
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-chevron-left"></i>
        </button>
    </div>
    
    <div class="sidebar-user">
        <div class="user-avatar">
            <img src="<?php echo BASE_URL; ?>assets/img/avatars/<?php echo $_SESSION['avatar'] ?? 'default.png'; ?>" alt="<?php echo htmlspecialchars($_SESSION['name'] ?? __('Admin User')); ?>">
        </div>
        <div class="user-info">
            <h5><?php echo htmlspecialchars($_SESSION['name'] ?? __('Admin User')); ?></h5>
            <span><?php echo htmlspecialchars($_SESSION['role'] ?? __('Administrator')); ?></span>
        </div>
    </div>
    
    <div class="sidebar-menu">
        <ul class="menu-items">
            <li class="menu-title"><?php echo __('Principal'); ?></li>
            
            <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span><?php echo __('Dashboard'); ?></span>
                </a>
            </li>
            
            <li class="menu-title"><?php echo __('Management'); ?></li>
            
            <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/users.php">
                    <i class="fas fa-users"></i>
                    <span><?php echo __('Users'); ?></span>
                </a>
            </li>
            
            <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'properties.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/properties.php">
                    <i class="fas fa-building"></i>
                    <span><?php echo __('Properties'); ?></span>
                </a>
            </li>
            
            <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'tickets.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/tickets.php">
                    <i class="fas fa-ticket-alt"></i>
                    <span><?php echo __('Tickets'); ?></span>
                </a>
            </li>
            
            <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/payments.php">
                    <i class="fas fa-credit-card"></i>
                    <span><?php echo __('Payments'); ?></span>
                </a>
            </li>
            
            <li class="menu-title"><?php echo __('System'); ?></li>
            
            <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'activity-log.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/activity-log.php">
                    <i class="fas fa-history"></i>
                    <span><?php echo __('Activity Log'); ?></span>
                </a>
            </li>
            
            <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'maintenance.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/maintenance.php">
                    <i class="fas fa-tools"></i>
                    <span><?php echo __('Maintenance'); ?></span>
                </a>
            </li>
            
            <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>admin/settings.php">
                    <i class="fas fa-cog"></i>
                    <span><?php echo __('Settings'); ?></span>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="<?php echo BASE_URL; ?>includes/logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span><?php echo __('Logout'); ?></span>
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
            <span class="dark-mode-label"><?php echo __('Dark / Light'); ?></span>
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
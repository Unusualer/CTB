<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Redirect to login page
    header('Location: ../login.php?error=unauthorized');
    exit();
}
?>
<!DOCTYPE html>
<html lang="<?php echo isset($_SESSION['language']) ? substr($_SESSION['language'], 0, 2) : 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? __($pageTitle) : __('Admin Dashboard'); ?> - <?php echo __("Complexe Tanger Boulevard"); ?></title>
    
    <!-- Favicon -->
    <?php favicon_links(); ?>
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="css/admin-style.css">
    <link rel="stylesheet" href="css/colorful-theme.css">
    
    <!-- Page specific CSS if any -->
    <?php if (isset($pageCss)): ?>
        <?php foreach ($pageCss as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <div class="admin-container">
        <!-- Admin Topbar -->
        <div class="admin-topbar">
            <div class="sidebar-toggle">
                <i class="fas fa-bars"></i>
            </div>
            <div class="topbar-search">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="<?php echo __('Search...'); ?>">
            </div>
            <div class="topbar-right">
                <div class="notification-item">
                    <i class="fas fa-bell"></i>
                    <span class="badge">3</span>
                </div>
                <div class="admin-profile">
                    <img src="../assets/images/admin-avatar.jpg" alt="<?php echo __('Admin'); ?>">
                    <div class="admin-info">
                        <span class="admin-name"><?php echo $_SESSION['username']; ?></span>
                        <span class="admin-role"><?php echo __('Administrator'); ?></span>
                    </div>
                    <div class="profile-dropdown">
                        <a href="profile.php"><i class="fas fa-user"></i> <?php echo __('Profile'); ?></a>
                        <a href="settings.php"><i class="fas fa-cog"></i> <?php echo __('Settings'); ?></a>
                        <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> <?php echo __('Logout'); ?></a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="admin-main">
            <!-- Admin Sidebar -->
            <div class="admin-sidebar">
                <div class="sidebar-brand">
                    <img src="../images/logo.png" alt="<?php echo __('Logo'); ?>">
                    <h2><?php echo __('Complexe Tanger Boulevard'); ?></h2>
                </div>
                <div class="sidebar-menu">
                    <ul>
                        <li class="<?php echo ($currentPage == 'dashboard') ? 'active' : ''; ?>">
                            <a href="index.php">
                                <i class="fas fa-tachometer-alt"></i>
                                <span><?php echo __('Dashboard'); ?></span>
                            </a>
                        </li>
                        <li class="<?php echo ($currentPage == 'properties') ? 'active' : ''; ?>">
                            <a href="properties.php">
                                <i class="fas fa-building"></i>
                                <span><?php echo __('Properties'); ?></span>
                            </a>
                        </li>
                        <li class="<?php echo ($currentPage == 'residents') ? 'active' : ''; ?>">
                            <a href="residents.php">
                                <i class="fas fa-users"></i>
                                <span><?php echo __('Residents'); ?></span>
                            </a>
                        </li>
                        <li class="<?php echo ($currentPage == 'payments') ? 'active' : ''; ?>">
                            <a href="payments.php">
                                <i class="fas fa-credit-card"></i>
                                <span><?php echo __('Payments'); ?></span>
                            </a>
                        </li>
                        <li class="<?php echo ($currentPage == 'tickets') ? 'active' : ''; ?>">
                            <a href="tickets.php">
                                <i class="fas fa-ticket-alt"></i>
                                <span><?php echo __('Tickets'); ?></span>
                            </a>
                        </li>
                        <li class="<?php echo ($currentPage == 'reports') ? 'active' : ''; ?>">
                            <a href="reports.php">
                                <i class="fas fa-chart-bar"></i>
                                <span><?php echo __('Reports'); ?></span>
                            </a>
                        </li>
                        <li class="<?php echo ($currentPage == 'settings') ? 'active' : ''; ?>">
                            <a href="settings.php">
                                <i class="fas fa-cog"></i>
                                <span><?php echo __('Settings'); ?></span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Admin Content -->
            <div class="admin-content">
                <?php if (isset($pageHeader)): ?>
                <div class="content-header">
                    <h1><?php echo __($pageHeader); ?></h1>
                    <?php if (isset($headerActions)): ?>
                    <div class="header-actions">
                        <?php echo $headerActions; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <!-- Page Content -->
                <?php if (isset($alertMessage) && isset($alertType)): ?>
                <div class="alert alert-<?php echo $alertType; ?>">
                    <?php echo __($alertMessage); ?>
                </div>
                <?php endif; ?>
                
                <!-- Main content will be included here -->
                <?php include_once($contentFile); ?>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/fontawesome.min.js"></script>
    <script>
        // Toggle sidebar
        document.querySelector('.sidebar-toggle').addEventListener('click', function() {
            document.body.classList.toggle('sidebar-collapsed');
        });
        
        // Toggle profile dropdown
        document.querySelector('.admin-profile').addEventListener('click', function(e) {
            this.classList.toggle('active');
            e.stopPropagation();
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function() {
            document.querySelector('.admin-profile').classList.remove('active');
        });
    </script>
    
    <!-- Page specific scripts if any -->
    <?php if (isset($pageScripts)): ?>
        <?php foreach ($pageScripts as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html> 
<?php
// Get current page filename for active link highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Sidebar Overlay (for mobile) -->
<div id="sidebarOverlay"></div>

<!-- Mobile Sidebar Toggle -->
<button id="mobileSidebarToggle" class="mobile-toggle">
    <i class="fas fa-bars"></i>
</button>

<!-- Admin Sidebar -->
<aside id="adminSidebar" class="<?php echo isset($_COOKIE['sidebar_collapsed']) && $_COOKIE['sidebar_collapsed'] === 'true' ? 'collapsed' : ''; ?>">
    <!-- Sidebar Header -->
    <div class="sidebar-header">
        <a href="index.php" class="sidebar-logo">
            <img src="../images/logo.png" alt="CTB Logo">
            <span>CTB Admin</span>
        </a>
        <button id="sidebarToggle" class="sidebar-toggle">
            <i class="fas fa-chevron-left"></i>
        </button>
    </div>

    <!-- User Section -->
    <div class="sidebar-user">
        <img src="assets/img/avatars/<?php echo $_SESSION['user_avatar'] ?: 'default.png'; ?>" alt="User" class="sidebar-user-avatar">
        <div class="sidebar-user-info">
            <div class="sidebar-user-name"><?php echo $_SESSION['user_name']; ?></div>
            <div class="sidebar-user-role"><?php echo $_SESSION['user_role']; ?></div>
        </div>
    </div>

    <!-- Menu Section -->
    <nav class="sidebar-menu">
        <div class="sidebar-menu-category">Tableau de Bord</div>
        <ul class="sidebar-menu-items list-unstyled">
            <li class="sidebar-menu-item">
                <a href="index.php" class="sidebar-menu-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt sidebar-menu-icon"></i>
                    <span class="sidebar-menu-text">Tableau de Bord</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-menu-category">Gestion</div>
        <ul class="sidebar-menu-items list-unstyled">
            <li class="sidebar-menu-item">
                <a href="properties.php" class="sidebar-menu-link <?php echo $current_page == 'properties.php' || $current_page == 'add-property.php' || $current_page == 'edit-property.php' ? 'active' : ''; ?>">
                    <i class="fas fa-building sidebar-menu-icon"></i>
                    <span class="sidebar-menu-text">Propriétés</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="tickets.php" class="sidebar-menu-link <?php echo $current_page == 'tickets.php' || $current_page == 'view-ticket.php' ? 'active' : ''; ?>">
                    <i class="fas fa-ticket-alt sidebar-menu-icon"></i>
                    <span class="sidebar-menu-text">Tickets</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="users.php" class="sidebar-menu-link <?php echo $current_page == 'users.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users sidebar-menu-icon"></i>
                    <span class="sidebar-menu-text">Utilisateurs</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="payments.php" class="sidebar-menu-link <?php echo $current_page == 'payments.php' ? 'active' : ''; ?>">
                    <i class="fas fa-money-bill-wave sidebar-menu-icon"></i>
                    <span class="sidebar-menu-text">Paiements</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-menu-category">Rapports</div>
        <ul class="sidebar-menu-items list-unstyled">
            <li class="sidebar-menu-item">
                <a href="reports.php" class="sidebar-menu-link <?php echo $current_page == 'reports.php' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar sidebar-menu-icon"></i>
                    <span class="sidebar-menu-text">Rapports</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="activity-log.php" class="sidebar-menu-link <?php echo $current_page == 'activity-log.php' ? 'active' : ''; ?>">
                    <i class="fas fa-history sidebar-menu-icon"></i>
                    <span class="sidebar-menu-text">Journal d'Activité</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-menu-category">Paramètres</div>
        <ul class="sidebar-menu-items list-unstyled">
            <li class="sidebar-menu-item">
                <a href="settings.php" class="sidebar-menu-link <?php echo $current_page == 'settings.php' ? 'active' : ''; ?>">
                    <i class="fas fa-cogs sidebar-menu-icon"></i>
                    <span class="sidebar-menu-text">Paramètres</span>
                </a>
            </li>
            <li class="sidebar-menu-item">
                <a href="profile.php" class="sidebar-menu-link <?php echo $current_page == 'profile.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user-cog sidebar-menu-icon"></i>
                    <span class="sidebar-menu-text">Profil</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Footer -->
    <div class="sidebar-footer">
        <div class="dark-mode-toggle">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="darkModeToggle" <?php echo isset($_COOKIE['dark_mode']) && $_COOKIE['dark_mode'] === 'true' ? 'checked' : ''; ?>>
                <label class="form-check-label" for="darkModeToggle">Mode Sombre</label>
            </div>
        </div>
        <div class="sidebar-version">v1.0.0</div>
    </div>
</aside>

<!-- Include the sidebar.js script -->
<script src="assets/js/sidebar.js"></script> 
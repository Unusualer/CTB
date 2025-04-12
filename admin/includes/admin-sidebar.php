<?php
// Get current page filename for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);

// Helper function to determine active class
function isActive($page_name) {
    global $current_page;
    return ($current_page == $page_name) ? 'class="active"' : '';
}
?>
<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-header">
        <a href="dashboard.php" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 12px;">
            <img src="../assets/images/logo.png" alt="Logo CTB" class="logo">
            <h2>CTB Admin</h2>
        </a>
    </div>
    
    <div class="user-info">
        <div class="user-avatar">
            <i class="fas fa-user-circle"></i>
        </div>
        <div class="user-details">
            <h4><?php echo htmlspecialchars($_SESSION['name']); ?></h4>
            <p>Administrateur</p>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <li <?php echo isActive('dashboard.php'); ?>>
                <a href="dashboard.php">
                    <i class="fas fa-chart-line"></i>
                    <span>Tableau de Bord</span>
                </a>
            </li>
            <li <?php echo isActive('users.php') || $current_page == 'view-user.php' || $current_page == 'edit-user.php' || $current_page == 'add-user.php' ? 'class="active"' : ''; ?>>
                <a href="users.php">
                    <i class="fas fa-users"></i>
                    <span>Utilisateurs</span>
                </a>
            </li>
            <li <?php echo isActive('properties.php') || $current_page == 'view-property.php' || $current_page == 'edit-property.php' || $current_page == 'add-property.php' ? 'class="active"' : ''; ?>>
                <a href="properties.php">
                    <i class="fas fa-home"></i>
                    <span>Propriétés</span>
                </a>
            </li>
            <li <?php echo isActive('tickets.php') || $current_page == 'view-ticket.php' || $current_page == 'edit-ticket.php' || $current_page == 'add-ticket.php' ? 'class="active"' : ''; ?>>
                <a href="tickets.php">
                    <i class="fas fa-ticket-alt"></i>
                    <span>Tickets</span>
                </a>
            </li>
            <li <?php echo isActive('payments.php') || $current_page == 'view-payment.php' || $current_page == 'edit-payment.php' || $current_page == 'add-payment.php' ? 'class="active"' : ''; ?>>
                <a href="payments.php">
                    <i class="fas fa-credit-card"></i>
                    <span>Paiements</span>
                </a>
            </li>
            <li <?php echo isActive('activity-log.php'); ?>>
                <a href="activity-log.php">
                    <i class="fas fa-history"></i>
                    <span>Journal d'Activité</span>
                </a>
            </li>
            <li <?php echo isActive('maintenance.php') || $current_page == 'view-maintenance.php' || $current_page == 'edit-maintenance.php' || $current_page == 'add-maintenance.php' ? 'class="active"' : ''; ?>>
                <a href="maintenance.php">
                    <i class="fas fa-tools"></i>
                    <span>Maintenance</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <?php include 'sidebar-footer.php'; ?>
</aside> 
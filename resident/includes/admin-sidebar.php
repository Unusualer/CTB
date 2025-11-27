<?php
// Get current page filename for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);

// Helper function to determine active class
function isActive($page_name) {
    global $current_page;
    return ($current_page == $page_name) ? 'class="active"' : '';
}

// Include translation function if not already defined
if (!function_exists('__')) {
    $translations_file = dirname(dirname(__DIR__)) . '/includes/translations.php';
    if (file_exists($translations_file)) {
        require_once $translations_file;
    } else {
        // Fallback to alternate locations
        $alt_translations_file = $_SERVER['DOCUMENT_ROOT'] . '/CTB/includes/translations.php';
        if (file_exists($alt_translations_file)) {
            require_once $alt_translations_file;
        } else {
            // Define a minimal translation function as last resort
            function __($text) {
                return $text;
            }
        }
    }
}

$current_role = $_SESSION['role'] ?? ($_SESSION['user_role'] ?? 'resident');
$current_user_id = $_SESSION['user_id'] ?? null;
$role_labels = [
    'admin'    => __("Administrator"),
    'manager'  => __("Manager"),
    'resident' => __("Resident"),
];
$role_label = $role_labels[$current_role] ?? __("User");
$sidebar_title = "CTB " . $role_label;
$profile_link = $current_user_id ? "view-user.php?id=" . urlencode((string)$current_user_id) : "view-user.php";
?>
<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-header">
        <a href="dashboard.php" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 12px;">
            <img src="../images/logo.png" alt="Logo CTB" class="logo">
            <h2><?php echo htmlspecialchars($sidebar_title); ?></h2>
        </a>
    </div>
    
    <a href="<?php echo htmlspecialchars($profile_link); ?>" style="text-decoration: none; color: inherit; display: block;">
        <div class="user-info" style="cursor: pointer; transition: background-color 0.2s;">
            <div class="user-avatar">
                <i class="fas fa-user-circle"></i>
            </div>
            <div class="user-details">
                <h4><?php echo htmlspecialchars($_SESSION['name'] ?? 'Resident User'); ?></h4>
                <p><?php echo htmlspecialchars($role_label); ?></p>
            </div>
        </div>
    </a>
    
    <nav class="sidebar-nav">
        <ul>
            <li <?php echo isActive('dashboard.php'); ?>>
                <a href="dashboard.php">
                    <i class="fas fa-chart-line"></i>
                    <span><?php echo __("Dashboard"); ?></span>
                </a>
            </li>
            <li <?php echo isActive('properties.php') || $current_page == 'view-property.php' || $current_page == 'edit-property.php' || $current_page == 'add-property.php' ? 'class="active"' : ''; ?>>
                <a href="properties.php">
                    <i class="fas fa-home"></i>
                    <span><?php echo __("Properties"); ?></span>
                </a>
            </li>
            <li <?php echo isActive('tickets.php') || $current_page == 'view-ticket.php' || $current_page == 'edit-ticket.php' || $current_page == 'add-ticket.php' ? 'class="active"' : ''; ?>>
                <a href="tickets.php">
                    <i class="fas fa-ticket-alt"></i>
                    <span><?php echo __("Tickets"); ?></span>
                </a>
            </li>
            <li <?php echo isActive('payments.php') || $current_page == 'view-payment.php' || $current_page == 'edit-payment.php' || $current_page == 'add-payment.php' ? 'class="active"' : ''; ?>>
                <a href="payments.php">
                    <i class="fas fa-credit-card"></i>
                    <span><?php echo __("Payments"); ?></span>
                </a>
            </li>
            <li <?php echo isActive('maintenance.php') || $current_page == 'view-maintenance.php' || $current_page == 'edit-maintenance.php' || $current_page == 'add-maintenance.php' ? 'class="active"' : ''; ?>>
                <a href="maintenance.php">
                    <i class="fas fa-tools"></i>
                    <span><?php echo __("Maintenance"); ?></span>
                </a>
            </li>
            <li <?php echo in_array($current_page, ['users.php', 'view-user.php', 'edit-user.php'], true) ? 'class="active"' : ''; ?>>
                <a href="<?php echo htmlspecialchars($profile_link); ?>">
                    <i class="fas fa-user"></i>
                    <span><?php echo __("My Profile"); ?></span>
                </a>
            </li>
        </ul>
    </nav>
    
    <?php include 'sidebar-footer.php'; ?>
</aside> 
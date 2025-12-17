<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include role access functions
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/role_access.php';

// Include translation function if not already included
if (!function_exists('__')) {
    require_once __DIR__ . '/translations.php';
}

// Set default active page
$active_page = basename($_SERVER['PHP_SELF'], '.php');

// Get the current user's role
$currentRole = getCurrentRole();

// Common menu items for all roles
$common_menu_items = [
    [
        'name' => __("Dashboard"),
        'icon' => 'fa-tachometer-alt',
        'url' => 'dashboard.php',
        'page' => 'dashboard'
    ],
    [
        'name' => __("My Profile"),
        'icon' => 'fa-user',
        'url' => 'view-user.php?id=' . $_SESSION['user_id'],
        'page' => 'view-user'
    ],
    [
        'name' => __("Tickets"),
        'icon' => 'fa-ticket-alt',
        'url' => 'tickets.php',
        'page' => 'tickets'
    ],
    [
        'name' => __("Maintenance"),
        'icon' => 'fa-tools',
        'url' => 'maintenance.php',
        'page' => 'maintenance'
    ]
];

// Admin-specific menu items
$admin_menu_items = [
    [
        'name' => __("User Management"),
        'icon' => 'fa-users',
        'url' => 'users.php',
        'page' => 'users'
    ],
    [
        'name' => __("Properties"),
        'icon' => 'fa-building',
        'url' => 'properties.php',
        'page' => 'properties'
    ],
    [
        'name' => __("Payments"),
        'icon' => 'fa-credit-card',
        'url' => 'payments.php',
        'page' => 'payments'
    ],
    [
        'name' => __("Activity Log"),
        'icon' => 'fa-history',
        'url' => 'activity-log.php',
        'page' => 'activity-log'
    ]
];

// Manager-specific menu items
$manager_menu_items = [
    [
        'name' => __("Properties"),
        'icon' => 'fa-building',
        'url' => 'properties.php',
        'page' => 'properties'
    ],
    [
        'name' => __("Payments"),
        'icon' => 'fa-credit-card',
        'url' => 'payments.php',
        'page' => 'payments'
    ]
];

// Resident-specific menu items
$resident_menu_items = [
    [
        'name' => __("My Property"),
        'icon' => 'fa-home',
        'url' => 'view-property.php?id=' . ($_SESSION['property_id'] ?? ''),
        'page' => 'view-property'
    ],
    [
        'name' => __("My Payments"),
        'icon' => 'fa-credit-card',
        'url' => 'payments.php',
        'page' => 'payments'
    ]
];

// Determine which menu items to show based on role
$menu_items = $common_menu_items;

if ($currentRole === 'admin') {
    $menu_items = array_merge($menu_items, $admin_menu_items);
} elseif ($currentRole === 'manager') {
    $menu_items = array_merge($menu_items, $manager_menu_items);
} elseif ($currentRole === 'resident') {
    $menu_items = array_merge($menu_items, $resident_menu_items);
}
?>

<aside class="sidebar">
    <div class="sidebar-header">
        <h2><i class="fas fa-building"></i> <?php echo __("Complexe Tanger Boulevard"); ?></h2>
        <span class="close-sidebar" id="close-sidebar"><i class="fas fa-times"></i></span>
    </div>
    
    <div class="user-info">
        <div class="user-avatar">
            <i class="fas fa-user-circle"></i>
        </div>
        <div class="user-details">
            <h3><?php echo htmlspecialchars($_SESSION['name'] ?? ($_SESSION['user_name'] ?? __("User"))); ?></h3>
            <span class="user-role"><?php echo htmlspecialchars($currentRole ? __(ucfirst($currentRole)) : __("Unknown")); ?></span>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <?php foreach ($menu_items as $item): ?>
                <li class="<?php echo ($active_page === $item['page']) ? 'active' : ''; ?>">
                    <a href="<?php echo htmlspecialchars($item['url']); ?>">
                        <i class="fas <?php echo htmlspecialchars($item['icon']); ?>"></i>
                        <span><?php echo $item['name']; ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
            
            <li class="sidebar-divider"></li>
            <li>
                <a href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span><?php echo __("Logout"); ?></span>
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="sidebar-footer">
        <p>© <?php echo date('Y'); ?> <?php echo __("Complexe Tanger Boulevard"); ?></p>
    </div>
</aside>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle sidebar on mobile
    const toggleSidebar = document.getElementById('toggle-sidebar');
    const closeSidebar = document.getElementById('close-sidebar');
    const sidebar = document.querySelector('.sidebar');
    
    if (toggleSidebar) {
        toggleSidebar.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
    }
    
    if (closeSidebar) {
        closeSidebar.addEventListener('click', function() {
            sidebar.classList.remove('show');
        });
    }
});
</script> 
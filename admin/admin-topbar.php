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

// Get unread notifications
require_once '../includes/config.php';

$admin_id = $_SESSION['user_id'];
$unread_notifications_query = "SELECT COUNT(*) as count FROM notifications WHERE recipient_id = ? AND is_read = 0";
$stmt = $conn->prepare($unread_notifications_query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$notification_result = $stmt->get_result();
$unread_count = $notification_result->fetch_assoc()['count'];

// Get pending tickets count
$pending_tickets_query = "SELECT COUNT(*) as count FROM tickets WHERE status = 'open'";
$tickets_result = $conn->query($pending_tickets_query);
$pending_tickets = $tickets_result->fetch_assoc()['count'];
?>

<div class="admin-topbar">
    <div class="topbar-left">
        <button class="sidebar-toggle">
            <i class="fas fa-bars"></i>
        </button>
        <button class="mobile-sidebar-toggle d-lg-none">
            <i class="fas fa-bars"></i>
        </button>
        <form class="topbar-search">
            <div class="search-input">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search..." aria-label="Search">
            </div>
        </form>
    </div>
    
    <div class="topbar-right">
        <div class="topbar-item">
            <a href="tickets.php" class="topbar-icon">
                <i class="fas fa-ticket-alt"></i>
                <?php if ($pending_tickets > 0): ?>
                <span class="badge"><?= $pending_tickets ?></span>
                <?php endif; ?>
            </a>
        </div>
        
        <div class="topbar-item dropdown">
            <a href="#" class="topbar-icon" id="notificationsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-bell"></i>
                <?php if ($unread_count > 0): ?>
                <span class="badge"><?= $unread_count ?></span>
                <?php endif; ?>
            </a>
            <div class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationsDropdown">
                <div class="dropdown-header">
                    <h6>Notifications</h6>
                    <?php if ($unread_count > 0): ?>
                    <a href="notifications.php?mark_all_read=true" class="mark-all-read">Mark All Read</a>
                    <?php endif; ?>
                </div>
                
                <div class="notification-list">
                    <?php
                    // Get most recent 5 notifications
                    $recent_notifications_query = "SELECT * FROM notifications WHERE recipient_id = ? ORDER BY created_at DESC LIMIT 5";
                    $stmt = $conn->prepare($recent_notifications_query);
                    $stmt->bind_param("i", $admin_id);
                    $stmt->execute();
                    $notifications = $stmt->get_result();
                    
                    if ($notifications->num_rows > 0) {
                        while ($notification = $notifications->fetch_assoc()) {
                            $unread_class = $notification['is_read'] ? '' : 'unread';
                            $time_ago = time_elapsed_string($notification['created_at']);
                            
                            echo '<a href="' . $notification['link'] . '" class="notification-item ' . $unread_class . '">';
                            echo '<div class="notification-icon"><i class="fas fa-' . $notification['icon'] . '"></i></div>';
                            echo '<div class="notification-content">';
                            echo '<p>' . htmlspecialchars($notification['message']) . '</p>';
                            echo '<span class="notification-time">' . $time_ago . '</span>';
                            echo '</div>';
                            echo '</a>';
                        }
                    } else {
                        echo '<div class="empty-notifications">No notifications</div>';
                    }
                    ?>
                </div>
                
                <div class="dropdown-footer">
                    <a href="notifications.php">View All Notifications</a>
                </div>
            </div>
        </div>
        
        <div class="topbar-item dropdown">
            <a href="#" class="topbar-icon" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user-circle"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="profile.php">
                    <i class="fas fa-user fa-sm fa-fw mr-2"></i>
                    Profile
                </a>
                <a class="dropdown-item" href="settings.php">
                    <i class="fas fa-cogs fa-sm fa-fw mr-2"></i>
                    Settings
                </a>
                <a class="dropdown-item" href="activity-log.php">
                    <i class="fas fa-list fa-sm fa-fw mr-2"></i>
                    Activity Log
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="../logout.php">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2"></i>
                    Logout
                </a>
            </div>
        </div>
    </div>
</div>

<?php
// Helper function to display time in a human-readable format
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    
    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;
    
    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }
    
    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
?> 
<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    $_SESSION['error'] = "You don't have permission to access this page.";
    header("Location: ../login.php");
    exit();
}

// Get counts for dashboard
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Total users
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM users");
    $stmt->execute();
    $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Total residents
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'resident'");
    $stmt->execute();
    $total_residents = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Total properties
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM properties");
    $stmt->execute();
    $total_properties = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Total payments
    $stmt = $db->prepare("SELECT COUNT(*) as count, SUM(amount) as total FROM payments");
    $stmt->execute();
    $payment_data = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_payments = $payment_data['count'];
    $total_amount = $payment_data['total'] ?? 0;
    
    // Total tickets
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM tickets");
    $stmt->execute();
    $total_tickets = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Open tickets
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM tickets WHERE status = 'open'");
    $stmt->execute();
    $open_tickets = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Recent activity
    $stmt = $db->prepare("
        SELECT al.*, u.name 
        FROM activity_log al
        LEFT JOIN users u ON al.user_id = u.id
        ORDER BY al.created_at DESC 
        LIMIT 10
    ");
    $stmt->execute();
    $recent_activity = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Recent users
    $stmt = $db->prepare("
        SELECT * FROM users
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $recent_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $_SESSION['error'] = "A database error occurred. Please try again.";
}

// Include admin template
$page_title = "Dashboard";
include_once 'includes/admin-template.php';
?>

<div class="container-fluid px-4 py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4"><?php echo $page_title; ?></h1>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Residents</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_residents; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Revenue</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($total_amount, 2); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Properties</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_properties; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-home fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Open Tickets</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $open_tickets; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-ticket-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Recent Activity -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                    <a href="activity-log.php" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Entity</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_activity)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">No recent activity found</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($recent_activity as $activity): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($activity['name']); ?></td>
                                        <td>
                                            <?php 
                                            $badge_class = '';
                                            switch ($activity['action']) {
                                                case 'create':
                                                    $badge_class = 'success';
                                                    break;
                                                case 'update':
                                                    $badge_class = 'info';
                                                    break;
                                                case 'delete':
                                                    $badge_class = 'danger';
                                                    break;
                                                case 'login':
                                                    $badge_class = 'primary';
                                                    break;
                                                default:
                                                    $badge_class = 'secondary';
                                            }
                                            ?>
                                            <span class="badge badge-<?php echo $badge_class; ?>"><?php echo htmlspecialchars(ucfirst($activity['action'])); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars(ucfirst($activity['entity_type'])) . " #" . htmlspecialchars($activity['entity_id']); ?></td>
                                        <td><?php echo date('M d, Y H:i', strtotime($activity['created_at'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Users -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Users</h6>
                    <a href="users.php" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?php if (empty($recent_users)): ?>
                            <div class="text-center py-3">No recent users found</div>
                        <?php else: ?>
                            <?php foreach ($recent_users as $user): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0"><?php echo htmlspecialchars($user['name']); ?></h6>
                                        <small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                                    </div>
                                    <span class="badge badge-<?php echo $user['role'] == 'admin' ? 'danger' : ($user['role'] == 'manager' ? 'warning' : 'primary'); ?>">
                                        <?php echo htmlspecialchars(ucfirst($user['role'])); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- System Info -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">System Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>PHP Version:</strong> <?php echo phpversion(); ?>
                    </div>
                    <div class="mb-2">
                        <strong>Database:</strong> MySQL
                    </div>
                    <div class="mb-2">
                        <strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE']; ?>
                    </div>
                    <div>
                        <strong>Last Updated:</strong> <?php echo date('M d, Y'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 
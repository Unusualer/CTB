<?php
// Start session
session_start();

// Include database configuration
require_once '../includes/config.php';

// Check if user is logged in and has manager role
requireRole('manager');

// Get user information
$managerId = $_SESSION['user_id'];

// Get counts for dashboard statistics
try {
    // Count total residents
    $residentsStmt = $conn->prepare("
        SELECT COUNT(*) as count FROM users WHERE role = 'resident' AND status = 'active'
    ");
    $residentsStmt->execute();
    $residentCount = $residentsStmt->fetch()['count'];

    // Count total properties
    $propertiesStmt = $conn->prepare("
        SELECT COUNT(*) as count FROM properties
    ");
    $propertiesStmt->execute();
    $propertyCount = $propertiesStmt->fetch()['count'];

    // Count vacant properties
    $vacantStmt = $conn->prepare("
        SELECT COUNT(*) as count FROM properties WHERE status = 'vacant'
    ");
    $vacantStmt->execute();
    $vacantCount = $vacantStmt->fetch()['count'];

    // Count properties needing maintenance
    $maintenanceStmt = $conn->prepare("
        SELECT COUNT(*) as count FROM properties WHERE status = 'maintenance'
    ");
    $maintenanceStmt->execute();
    $maintenancePropertyCount = $maintenanceStmt->fetch()['count'];

    // Count ongoing maintenance requests
    $requestsStmt = $conn->prepare("
        SELECT COUNT(*) as count FROM maintenance_logs 
        WHERE status IN ('reported', 'in_progress')
    ");
    $requestsStmt->execute();
    $maintenanceRequestCount = $requestsStmt->fetch()['count'];

    // Calculate occupancy rate
    $occupancyRate = $propertyCount > 0 ? round((($propertyCount - $vacantCount) / $propertyCount) * 100) : 0;

    // Get recent payments
    $paymentsStmt = $conn->prepare("
        SELECT p.*, u.name as resident_name, pr.identifier as property_identifier 
        FROM payments p
        JOIN users u ON p.user_id = u.id
        JOIN properties pr ON p.property_id = pr.id
        ORDER BY p.payment_date DESC
        LIMIT 5
    ");
    $paymentsStmt->execute();
    $recentPayments = $paymentsStmt->fetchAll();

    // Get pending payments
    $pendingStmt = $conn->prepare("
        SELECT p.*, u.name as resident_name, pr.identifier as property_identifier 
        FROM payments p
        JOIN users u ON p.user_id = u.id
        JOIN properties pr ON p.property_id = pr.id
        WHERE p.status = 'pending'
        ORDER BY p.payment_month ASC
        LIMIT 5
    ");
    $pendingStmt->execute();
    $pendingPayments = $pendingStmt->fetchAll();

    // Get urgent maintenance requests
    $urgentStmt = $conn->prepare("
        SELECT m.*, u.name as reported_by_name, pr.identifier as property_identifier 
        FROM maintenance_logs m
        JOIN users u ON m.reported_by = u.id
        JOIN properties pr ON m.property_id = pr.id
        WHERE m.status IN ('reported', 'in_progress')
        AND m.priority IN ('high', 'urgent')
        ORDER BY 
            CASE 
                WHEN m.priority = 'urgent' THEN 1
                WHEN m.priority = 'high' THEN 2
            END,
            m.created_at ASC
        LIMIT 5
    ");
    $urgentStmt->execute();
    $urgentRequests = $urgentStmt->fetchAll();

} catch (PDOException $e) {
    // Handle database errors
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard - Complexe Tanger Boulevard</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="../css/vendor.css">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    
    <style>
        .dashboard-container {
            padding: 40px 0;
        }
        
        .dashboard-card {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            padding: 20px;
        }
        
        .dashboard-card-header {
            border-bottom: 1px solid #eee;
            margin-bottom: 20px;
            padding-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .dashboard-card-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin: 0;
        }
        
        .dashboard-stats {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -15px;
        }
        
        .dashboard-stat-item {
            flex: 1;
            min-width: 200px;
            padding: 15px;
        }
        
        .stat-card {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 20px;
            text-align: center;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .payment-table,
        .maintenance-table {
            width: 100%;
        }
        
        .payment-table th,
        .payment-table td,
        .maintenance-table th,
        .maintenance-table td {
            padding: 12px 15px;
            text-align: left;
        }
        
        .payment-table thead,
        .maintenance-table thead {
            background-color: #f8f9fa;
        }
        
        .payment-table tbody tr,
        .maintenance-table tbody tr {
            border-bottom: 1px solid #eee;
        }
        
        .payment-table tbody tr:last-child,
        .maintenance-table tbody tr:last-child {
            border-bottom: none;
        }
        
        .status-badge {
            border-radius: 20px;
            display: inline-block;
            font-size: 0.75rem;
            padding: 5px 10px;
            text-align: center;
            text-transform: uppercase;
        }
        
        .status-paid {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .status-reported {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-in-progress {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        
        .priority-badge {
            border-radius: 20px;
            display: inline-block;
            font-size: 0.75rem;
            padding: 3px 8px;
            text-align: center;
        }
        
        .priority-low {
            background-color: #d4edda;
            color: #155724;
        }
        
        .priority-medium {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .priority-high {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .priority-urgent {
            background-color: #dc3545;
            color: white;
        }
        
        .dashboard-action-btn {
            display: inline-block;
            margin-left: 5px;
        }
        
        .quick-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .dashboard-stat-item {
                flex: 0 0 50%;
            }
        }
        
        @media (max-width: 576px) {
            .dashboard-stat-item {
                flex: 0 0 100%;
            }
        }
    </style>
</head>
<body id="top">

    <!-- preloader -->
    <div id="preloader">
        <div id="loader"></div>
    </div>

    <!-- page wrap -->
    <div id="page" class="s-pagewrap">

        <!-- site header -->
        <header class="s-header">
            <div class="s-header__logo">
                <a class="logo" href="../index.php">
                    <img src="../images/logo.svg" alt="Homepage">
                </a>
            </div>

            <a class="s-header__menu-toggle" href="#0">
                <span class="s-header__menu-text">Menu</span>
                <span class="s-header__menu-icon"></span>
            </a>

            <nav class="s-header__nav">
                <a href="#0" class="s-header__nav-close-btn" title="close"><span>Close</span></a>
                <h3>CTB Manager</h3>

                <ul class="s-header__nav-list">
                    <li class="current"><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="residents.php">Residents</a></li>
                    <li><a href="properties.php">Properties</a></li>
                    <li><a href="payments.php">Payments</a></li>
                    <li><a href="maintenance.php">Maintenance</a></li>
                    <li><a href="reports.php">Reports</a></li>
                    <li><a href="profile.php">My Profile</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </header> <!-- end s-header -->

        <!-- site content -->
        <section id="content" class="s-content">
            <div class="row" style="max-width: 1200px; margin: 0 auto; padding-top: 120px;">
                <div class="column">
                    <h1>Manager Dashboard</h1>
                    <p>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! Here's an overview of Complexe Tanger Boulevard.</p>

                    <!-- Quick Actions -->
                    <div class="quick-actions">
                        <a href="residents-add.php" class="btn btn--stroke">
                            <i class="fas fa-user-plus"></i> Add Resident
                        </a>
                        <a href="properties-assign.php" class="btn btn--stroke">
                            <i class="fas fa-home"></i> Assign Property
                        </a>
                        <a href="payments-add.php" class="btn btn--stroke">
                            <i class="fas fa-money-bill-wave"></i> Record Payment
                        </a>
                        <a href="maintenance-add.php" class="btn btn--stroke">
                            <i class="fas fa-tools"></i> Add Maintenance
                        </a>
                    </div>

                    <div class="dashboard-container">
                        <!-- Stats Section -->
                        <div class="dashboard-card">
                            <div class="dashboard-card-header">
                                <h2 class="dashboard-card-title">Property Statistics</h2>
                            </div>
                            
                            <div class="dashboard-stats">
                                <div class="dashboard-stat-item">
                                    <div class="stat-card">
                                        <div class="stat-value"><?php echo $residentCount; ?></div>
                                        <div class="stat-label">Active Residents</div>
                                    </div>
                                </div>
                                
                                <div class="dashboard-stat-item">
                                    <div class="stat-card">
                                        <div class="stat-value"><?php echo $propertyCount; ?></div>
                                        <div class="stat-label">Total Properties</div>
                                    </div>
                                </div>
                                
                                <div class="dashboard-stat-item">
                                    <div class="stat-card">
                                        <div class="stat-value"><?php echo $vacantCount; ?></div>
                                        <div class="stat-label">Vacant Properties</div>
                                    </div>
                                </div>
                                
                                <div class="dashboard-stat-item">
                                    <div class="stat-card">
                                        <div class="stat-value"><?php echo $occupancyRate; ?>%</div>
                                        <div class="stat-label">Occupancy Rate</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Pending Payments Section -->
                        <div class="dashboard-card">
                            <div class="dashboard-card-header">
                                <h2 class="dashboard-card-title">Pending Payments</h2>
                                <a href="payments.php?status=pending" class="btn btn--small">View All</a>
                            </div>
                            
                            <?php if (count($pendingPayments) > 0): ?>
                                <div class="table-responsive">
                                    <table class="payment-table">
                                        <thead>
                                            <tr>
                                                <th>Resident</th>
                                                <th>Property</th>
                                                <th>Amount</th>
                                                <th>Due Month</th>
                                                <th>Type</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($pendingPayments as $payment): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($payment['resident_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($payment['property_identifier']); ?></td>
                                                    <td>₩ <?php echo number_format($payment['amount'], 2); ?></td>
                                                    <td><?php echo date('F Y', strtotime($payment['payment_month'])); ?></td>
                                                    <td><?php echo ucfirst(htmlspecialchars($payment['payment_type'])); ?></td>
                                                    <td>
                                                        <a href="payments-update.php?id=<?php echo $payment['id']; ?>" class="dashboard-action-btn" title="Update Status">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="payments-receipt.php?id=<?php echo $payment['id']; ?>" class="dashboard-action-btn" title="Generate Receipt">
                                                            <i class="fas fa-file-invoice"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p>No pending payments.</p>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Recent Payments Section -->
                        <div class="dashboard-card">
                            <div class="dashboard-card-header">
                                <h2 class="dashboard-card-title">Recent Payments</h2>
                                <a href="payments.php" class="btn btn--small">View All</a>
                            </div>
                            
                            <?php if (count($recentPayments) > 0): ?>
                                <div class="table-responsive">
                                    <table class="payment-table">
                                        <thead>
                                            <tr>
                                                <th>Resident</th>
                                                <th>Property</th>
                                                <th>Amount</th>
                                                <th>Date</th>
                                                <th>Month</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentPayments as $payment): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($payment['resident_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($payment['property_identifier']); ?></td>
                                                    <td>₩ <?php echo number_format($payment['amount'], 2); ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></td>
                                                    <td><?php echo date('F Y', strtotime($payment['payment_month'])); ?></td>
                                                    <td>
                                                        <span class="status-badge status-<?php echo strtolower($payment['status']); ?>">
                                                            <?php echo ucfirst(htmlspecialchars($payment['status'])); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p>No payment records found.</p>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Urgent Maintenance Section -->
                        <div class="dashboard-card">
                            <div class="dashboard-card-header">
                                <h2 class="dashboard-card-title">Urgent Maintenance</h2>
                                <a href="maintenance.php?priority=urgent" class="btn btn--small">View All</a>
                            </div>
                            
                            <?php if (count($urgentRequests) > 0): ?>
                                <div class="table-responsive">
                                    <table class="maintenance-table">
                                        <thead>
                                            <tr>
                                                <th>Property</th>
                                                <th>Issue</th>
                                                <th>Reported By</th>
                                                <th>Reported On</th>
                                                <th>Status</th>
                                                <th>Priority</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($urgentRequests as $request): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($request['property_identifier']); ?></td>
                                                    <td><?php echo htmlspecialchars($request['title']); ?></td>
                                                    <td><?php echo htmlspecialchars($request['reported_by_name']); ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($request['created_at'])); ?></td>
                                                    <td>
                                                        <span class="status-badge status-<?php echo str_replace('_', '-', strtolower($request['status'])); ?>">
                                                            <?php echo ucfirst(str_replace('_', ' ', $request['status'])); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="priority-badge priority-<?php echo strtolower($request['priority']); ?>">
                                                            <?php echo ucfirst($request['priority']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="maintenance-update.php?id=<?php echo $request['id']; ?>" class="dashboard-action-btn" title="Update Status">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="maintenance-view.php?id=<?php echo $request['id']; ?>" class="dashboard-action-btn" title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p>No urgent maintenance requests.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section> <!-- end content -->

        <!-- footer -->
        <footer id="colophon" class="s-footer">
            <div class="row">
                <div class="column lg-12 ss-copyright">
                    <span>© Copyright Complexe Tanger Boulevard 2023</span>
                </div>
            </div>

            <div class="ss-go-top">
                <a class="smoothscroll" title="Back to Top" href="#top">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: rgba(0, 0, 0, 1);transform: ;msFilter:;"><path d="M6 4h12v2H6zm5 10v6h2v-6h5l-6-6-6 6z"></path></svg>
                </a>
            </div>
        </footer> <!-- end footer -->
    </div> <!-- end page wrap -->

    <!-- Java Script -->
    <script src="../js/plugins.js"></script>
    <script src="../js/main.js"></script>
</body>
</html> 
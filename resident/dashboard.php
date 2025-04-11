<?php
// Start session
session_start();

// Include database configuration
require_once '../includes/config.php';

// Check if user is logged in and has resident role
requireRole('resident');

// Get user information
$userId = $_SESSION['user_id'];

// Fetch resident's properties
$propertiesStmt = $conn->prepare("
    SELECT p.* 
    FROM properties p
    WHERE p.user_id = :user_id
");
$propertiesStmt->bindParam(':user_id', $userId);
$propertiesStmt->execute();
$properties = $propertiesStmt->fetchAll();

// Get property IDs
$propertyIds = [];
foreach ($properties as $property) {
    $propertyIds[] = $property['id'];
}
$propertyIdStr = implode(',', $propertyIds ?: [0]);

// Fetch pending payments (if any property ID exists)
$pendingStmt = $conn->prepare("
    SELECT p.*, pr.identifier as property_identifier, pr.type as property_type 
    FROM payments p
    JOIN properties pr ON p.property_id = pr.id
    WHERE p.property_id IN ($propertyIdStr) 
    AND p.status = 'pending'
    ORDER BY p.payment_month DESC
");
$pendingStmt->execute();
$pendingPayments = $pendingStmt->fetchAll();

// Fetch recent payments
$recentStmt = $conn->prepare("
    SELECT p.*, pr.identifier as property_identifier, pr.type as property_type 
    FROM payments p
    JOIN properties pr ON p.property_id = pr.id
    WHERE p.property_id IN ($propertyIdStr) 
    AND p.status = 'paid'
    ORDER BY p.payment_date DESC
    LIMIT 5
");
$recentStmt->execute();
$recentPayments = $recentStmt->fetchAll();

// Fetch ongoing maintenance
$maintenanceStmt = $conn->prepare("
    SELECT m.*, pr.identifier as property_identifier
    FROM maintenance m
    JOIN properties pr ON m.property_id = pr.id
    WHERE m.property_id IN ($propertyIdStr) 
    AND m.status IN ('reported', 'in_progress')
    ORDER BY m.created_at DESC
");
$maintenanceStmt->execute();
$maintenanceRequests = $maintenanceStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Dashboard - Complexe Tanger Boulevard</title>
    
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
        
        .property-item {
            background-color: #f8f9fa;
            border-radius: 5px;
            margin-bottom: 15px;
            padding: 15px;
        }
        
        .property-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .property-item-title {
            font-weight: 700;
            margin: 0;
        }
        
        .property-item-type {
            background-color: #e9ecef;
            border-radius: 20px;
            font-size: 0.8rem;
            padding: 5px 10px;
        }
        
        .property-item-details {
            color: #6c757d;
            font-size: 0.9rem;
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
        
        @media (max-width: 768px) {
            .dashboard-stat-item {
                flex: 0 0 50%;
            }
        }
        
        @media (max-width: 576px) {
            .dashboard-stat-item {
                flex: 0 0 100%;
            }
            
            .payment-table,
            .maintenance-table {
                display: block;
                overflow-x: auto;
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
                <h3>CTB</h3>

                <ul class="s-header__nav-list">
                    <li class="current"><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="payments.php">Payment History</a></li>
                    <li><a href="maintenance.php">Maintenance Requests</a></li>
                    <li><a href="profile.php">My Profile</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </header> <!-- end s-header -->

        <!-- site content -->
        <section id="content" class="s-content">
            <div class="row" style="max-width: 1200px; margin: 0 auto; padding-top: 120px;">
                <div class="column">
                    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h1>
                    <p>Here's an overview of your properties, payments, and maintenance requests.</p>

                    <div class="dashboard-container">
                        <!-- Properties Section -->
                        <div class="dashboard-card">
                            <div class="dashboard-card-header">
                                <h2 class="dashboard-card-title">My Properties</h2>
                            </div>
                            
                            <div class="row">
                                <?php if (count($properties) > 0): ?>
                                    <?php foreach ($properties as $property): ?>
                                        <div class="column lg-6 tab-12">
                                            <div class="property-item">
                                                <div class="property-item-header">
                                                    <h3 class="property-item-title"><?php echo htmlspecialchars($property['identifier']); ?></h3>
                                                    <span class="property-item-type">
                                                        <?php echo ucfirst(htmlspecialchars($property['type'])); ?>
                                                    </span>
                                                </div>
                                                
                                                <div class="property-item-details">
                                                    <?php if (!empty($property['description'])): ?>
                                                        <p><?php echo htmlspecialchars($property['description']); ?></p>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($property['area'])): ?>
                                                        <p>Area: <?php echo htmlspecialchars($property['area']); ?> m²</p>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($property['floor'])): ?>
                                                        <p>Floor: <?php echo htmlspecialchars($property['floor']); ?></p>
                                                    <?php endif; ?>
                                                    
                                                    <p>Status: <?php echo ucfirst(htmlspecialchars($property['status'])); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="column">
                                        <p>No properties assigned to you yet.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Pending Payments Section -->
                        <div class="dashboard-card">
                            <div class="dashboard-card-header">
                                <h2 class="dashboard-card-title">Pending Payments</h2>
                            </div>
                            
                            <?php if (count($pendingPayments) > 0): ?>
                                <table class="payment-table">
                                    <thead>
                                        <tr>
                                            <th>Property</th>
                                            <th>Amount</th>
                                            <th>Month</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pendingPayments as $payment): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($payment['property_identifier']); ?></td>
                                                <td>₩ <?php echo number_format($payment['amount'], 2); ?></td>
                                                <td><?php echo date('F Y', strtotime($payment['payment_month'])); ?></td>
                                                <td><?php echo ucfirst(htmlspecialchars($payment['payment_type'])); ?></td>
                                                <td>
                                                    <span class="status-badge status-<?php echo strtolower($payment['status']); ?>">
                                                        <?php echo ucfirst(htmlspecialchars($payment['status'])); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
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
                                <table class="payment-table">
                                    <thead>
                                        <tr>
                                            <th>Property</th>
                                            <th>Amount</th>
                                            <th>Date</th>
                                            <th>Month</th>
                                            <th>Receipt</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentPayments as $payment): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($payment['property_identifier']); ?></td>
                                                <td>₩ <?php echo number_format($payment['amount'], 2); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></td>
                                                <td><?php echo date('F Y', strtotime($payment['payment_month'])); ?></td>
                                                <td>
                                                    <?php if (!empty($payment['receipt_number'])): ?>
                                                        <a href="receipt.php?id=<?php echo $payment['id']; ?>" target="_blank" class="dashboard-action-btn">
                                                            <i class="fas fa-file-invoice"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        N/A
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p>No payment records found.</p>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Maintenance Requests Section -->
                        <div class="dashboard-card">
                            <div class="dashboard-card-header">
                                <h2 class="dashboard-card-title">Ongoing Maintenance</h2>
                                <a href="maintenance-request.php" class="btn btn--small">New Request</a>
                            </div>
                            
                            <?php if (count($maintenanceRequests) > 0): ?>
                                <table class="maintenance-table">
                                    <thead>
                                        <tr>
                                            <th>Property</th>
                                            <th>Issue</th>
                                            <th>Reported On</th>
                                            <th>Status</th>
                                            <th>Priority</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($maintenanceRequests as $request): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($request['property_identifier']); ?></td>
                                                <td><?php echo htmlspecialchars($request['title']); ?></td>
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
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p>No ongoing maintenance requests.</p>
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
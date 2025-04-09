<?php
// Start session
session_start();

// Include database configuration
require_once '../includes/config.php';

// Check if user is logged in and has manager role
requireRole('manager');

// Check if resident ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = "Resident ID is required.";
    header("Location: residents.php");
    exit;
}

$residentId = intval($_GET['id']);
$resident = null;
$properties = [];
$payments = [];
$tickets = [];

try {
    // Get resident details
    $residentQuery = "SELECT u.*, COUNT(p.id) as property_count 
                     FROM users u 
                     LEFT JOIN properties p ON u.id = p.owner_id 
                     WHERE u.id = :id AND u.role = 'resident' 
                     GROUP BY u.id";
    $residentStmt = $conn->prepare($residentQuery);
    $residentStmt->bindParam(':id', $residentId, PDO::PARAM_INT);
    $residentStmt->execute();
    
    if ($residentStmt->rowCount() === 0) {
        $_SESSION['error_message'] = "Resident not found.";
        header("Location: residents.php");
        exit;
    }
    
    $resident = $residentStmt->fetch(PDO::FETCH_ASSOC);
    
    // Get resident properties
    $propertiesQuery = "SELECT p.*, pt.name as property_type_name 
                       FROM properties p 
                       LEFT JOIN property_types pt ON p.property_type_id = pt.id
                       WHERE p.owner_id = :owner_id
                       ORDER BY p.created_at DESC";
    $propertiesStmt = $conn->prepare($propertiesQuery);
    $propertiesStmt->bindParam(':owner_id', $residentId, PDO::PARAM_INT);
    $propertiesStmt->execute();
    $properties = $propertiesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get resident payments (recent 5)
    $paymentsQuery = "SELECT p.*, pr.name as property_name 
                     FROM payments p 
                     LEFT JOIN properties pr ON p.property_id = pr.id
                     WHERE pr.owner_id = :owner_id
                     ORDER BY p.payment_date DESC
                     LIMIT 5";
    $paymentsStmt = $conn->prepare($paymentsQuery);
    $paymentsStmt->bindParam(':owner_id', $residentId, PDO::PARAM_INT);
    $paymentsStmt->execute();
    $payments = $paymentsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get resident tickets (recent 5)
    $ticketsQuery = "SELECT t.*, p.identifier as property_name 
                    FROM tickets t 
                    LEFT JOIN properties p ON t.property_id = p.id
                    WHERE t.created_by = :user_id
                    ORDER BY t.created_at DESC
                    LIMIT 5";
    $ticketsStmt = $conn->prepare($ticketsQuery);
    $ticketsStmt->bindParam(':user_id', $residentId, PDO::PARAM_INT);
    $ticketsStmt->execute();
    $tickets = $ticketsStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    header("Location: residents.php");
    exit;
}

// Format the created date
$createdDate = new DateTime($resident['created_at']);
$formattedCreatedDate = $createdDate->format('F j, Y');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Details - Community Trust Bank</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .resident-profile {
            display: flex;
            margin-bottom: 20px;
        }
        .profile-info {
            flex: 1;
            padding-left: 20px;
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background-color: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: #666;
        }
        .profile-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .profile-email {
            color: #666;
            margin-bottom: 10px;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-active {
            background-color: #e6f7e6;
            color: #28a745;
        }
        .badge-inactive {
            background-color: #f8f9fa;
            color: #6c757d;
        }
        .info-section {
            margin-top: 30px;
        }
        .info-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .info-title {
            font-size: 18px;
            font-weight: bold;
        }
        .view-all {
            color: #007bff;
            text-decoration: none;
            font-size: 14px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .info-item {
            padding: 15px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }
        .info-item-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table th, .info-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        .info-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .action-btns {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
        .empty-data {
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-style: italic;
            border: 1px dashed #dee2e6;
            border-radius: 4px;
        }
        .links-section {
            margin-top: 30px;
        }
        .action-links {
            list-style: none;
            padding: 0;
        }
        .btn-link {
            display: inline-block;
            padding: 8px 16px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            margin-bottom: 5px;
        }
        .btn-link:hover {
            background-color: #0056b3;
        }
        .text-danger {
            background-color: #dc3545;
        }
        .text-danger:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="breadcrumb">
            <a href="dashboard.php">Dashboard</a> &gt;
            <a href="residents.php">Residents</a> &gt;
            <span>Resident Details</span>
        </div>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php 
                    echo $_SESSION['success_message']; 
                    unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-error">
                <?php 
                    echo $_SESSION['error_message']; 
                    unset($_SESSION['error_message']);
                ?>
            </div>
        <?php endif; ?>
        
        <div class="page-header">
            <h1>Resident Details</h1>
            <div>
                <a href="residents.php" class="btn btn-secondary">Back to Residents</a>
            </div>
        </div>
        
        <div class="card">
            <div class="resident-profile">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($resident['name'], 0, 1)); ?>
                </div>
                <div class="profile-info">
                    <div class="profile-name"><?php echo htmlspecialchars($resident['name']); ?></div>
                    <div class="profile-email"><?php echo htmlspecialchars($resident['email']); ?></div>
                    <div>
                        <span class="badge <?php echo $resident['status'] === 'active' ? 'badge-active' : 'badge-inactive'; ?>">
                            <?php echo ucfirst(htmlspecialchars($resident['status'])); ?>
                        </span>
                    </div>
                    <div class="action-btns">
                        <?php if ($resident['status'] === 'active'): ?>
                            <a href="residents-status.php?id=<?php echo $resident['id']; ?>&action=deactivate" class="btn btn-secondary" onclick="return confirm('Are you sure you want to deactivate this resident?');">Deactivate</a>
                        <?php else: ?>
                            <a href="residents-status.php?id=<?php echo $resident['id']; ?>&action=activate" class="btn btn-primary" onclick="return confirm('Are you sure you want to activate this resident?');">Activate</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-item-title">Phone Number</div>
                    <div><?php echo htmlspecialchars($resident['phone'] ?? 'Not provided'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-item-title">Address</div>
                    <div><?php echo htmlspecialchars($resident['address'] ?? 'Not provided'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-item-title">Member Since</div>
                    <div><?php echo $formattedCreatedDate; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-item-title">Property Count</div>
                    <div><?php echo $resident['property_count']; ?></div>
                </div>
            </div>
            
            <div class="info-section">
                <div class="info-header">
                    <h2 class="info-title">Properties</h2>
                    <?php if (count($properties) > 0): ?>
                    <a href="resident-properties.php?id=<?php echo $resident['id']; ?>" class="view-all">View All</a>
                    <?php endif; ?>
                </div>
                
                <?php if (count($properties) > 0): ?>
                <table class="info-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Address</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($properties as $property): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($property['name']); ?></td>
                            <td><?php echo htmlspecialchars($property['property_type_name']); ?></td>
                            <td><?php echo htmlspecialchars($property['address']); ?></td>
                            <td>
                                <span class="badge <?php echo $property['status'] === 'active' ? 'badge-active' : 'badge-inactive'; ?>">
                                    <?php echo ucfirst(htmlspecialchars($property['status'])); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-data">
                    No properties found for this resident.
                </div>
                <?php endif; ?>
            </div>
            
            <div class="info-section">
                <div class="info-header">
                    <h2 class="info-title">Recent Payments</h2>
                    <?php if (count($payments) > 0): ?>
                    <a href="resident-payments.php?id=<?php echo $resident['id']; ?>" class="view-all">View All</a>
                    <?php endif; ?>
                </div>
                
                <?php if (count($payments) > 0): ?>
                <table class="info-table">
                    <thead>
                        <tr>
                            <th>Payment ID</th>
                            <th>Property</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?php echo $payment['id']; ?></td>
                            <td><?php echo htmlspecialchars($payment['property_name']); ?></td>
                            <td>$<?php echo number_format($payment['amount'], 2); ?></td>
                            <td><?php echo date('M j, Y', strtotime($payment['payment_date'])); ?></td>
                            <td><?php echo ucfirst(htmlspecialchars($payment['status'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-data">
                    No recent payments found for this resident.
                </div>
                <?php endif; ?>
            </div>
            
            <div class="info-section">
                <div class="info-header">
                    <h2 class="info-title">Recent Support Tickets</h2>
                    <?php if (count($tickets) > 0): ?>
                    <a href="resident-tickets.php?id=<?php echo $resident['id']; ?>" class="view-all">View All</a>
                    <?php endif; ?>
                </div>
                
                <?php if (count($tickets) > 0): ?>
                <table class="info-table">
                    <thead>
                        <tr>
                            <th>Ticket ID</th>
                            <th>Property</th>
                            <th>Subject</th>
                            <th>Created</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $ticket): ?>
                        <tr>
                            <td><?php echo $ticket['id']; ?></td>
                            <td><?php echo htmlspecialchars($ticket['property_name']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($ticket['created_at'])); ?></td>
                            <td><?php echo ucfirst(htmlspecialchars($ticket['status'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-data">
                    No recent support tickets found for this resident.
                </div>
                <?php endif; ?>
            </div>
            
            <div class="links-section">
                <h3>Actions</h3>
                <ul class="action-links">
                    <li><a href="resident-properties.php?id=<?php echo $residentId; ?>" class="btn-link">View All Properties</a></li>
                    <li><a href="edit-resident.php?id=<?php echo $residentId; ?>" class="btn-link">Edit Resident</a></li>
                    <li><a href="#" class="btn-link text-danger" onclick="confirmDelete(<?php echo $residentId; ?>)">Delete Resident</a></li>
                </ul>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html> 
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

// Set up pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$totalProperties = 0;

try {
    // Get resident details first to verify it exists
    $residentQuery = "SELECT * FROM users WHERE id = :id AND role = 'resident'";
    $residentStmt = $conn->prepare($residentQuery);
    $residentStmt->bindParam(':id', $residentId, PDO::PARAM_INT);
    $residentStmt->execute();
    
    if ($residentStmt->rowCount() === 0) {
        $_SESSION['error_message'] = "Resident not found.";
        header("Location: residents.php");
        exit;
    }
    
    $resident = $residentStmt->fetch(PDO::FETCH_ASSOC);
    
    // Count total properties for pagination
    $countQuery = "SELECT COUNT(*) as total FROM properties WHERE owner_id = :owner_id";
    $countStmt = $conn->prepare($countQuery);
    $countStmt->bindParam(':owner_id', $residentId, PDO::PARAM_INT);
    $countStmt->execute();
    $totalProperties = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get resident properties with pagination
    $propertiesQuery = "SELECT p.*, pt.name as property_type_name 
                       FROM properties p 
                       LEFT JOIN property_types pt ON p.property_type_id = pt.id
                       WHERE p.owner_id = :owner_id
                       ORDER BY p.created_at DESC
                       LIMIT :offset, :limit";
    $propertiesStmt = $conn->prepare($propertiesQuery);
    $propertiesStmt->bindParam(':owner_id', $residentId, PDO::PARAM_INT);
    $propertiesStmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $propertiesStmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $propertiesStmt->execute();
    $properties = $propertiesStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    header("Location: resident-details.php?id=" . $residentId);
    exit;
}

// Calculate total pages for pagination
$totalPages = ceil($totalProperties / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Properties - Community Trust Bank</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .property-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .property-card {
            border: 1px solid #dee2e6;
            border-radius: 4px;
            overflow: hidden;
        }
        .property-header {
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
            background-color: #f8f9fa;
        }
        .property-title {
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 5px;
        }
        .property-type {
            color: #6c757d;
            font-size: 14px;
        }
        .property-content {
            padding: 15px;
        }
        .property-info {
            margin-bottom: 15px;
        }
        .property-label {
            font-weight: bold;
            margin-bottom: 3px;
        }
        .property-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 10px;
        }
        .status-active {
            background-color: #e6f7e6;
            color: #28a745;
        }
        .status-inactive {
            background-color: #f8f9fa;
            color: #6c757d;
        }
        .property-footer {
            padding: 15px;
            border-top: 1px solid #dee2e6;
            background-color: #f8f9fa;
            text-align: right;
        }
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 30px;
        }
        .pagination-item {
            margin: 0 5px;
        }
        .pagination-link {
            display: inline-block;
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            text-decoration: none;
            color: #007bff;
        }
        .pagination-link.active {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }
        .pagination-link:hover:not(.active) {
            background-color: #f8f9fa;
        }
        .property-empty {
            padding: 40px;
            text-align: center;
            background-color: #f8f9fa;
            border-radius: 4px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="breadcrumb">
            <a href="dashboard.php">Dashboard</a> &gt;
            <a href="residents.php">Residents</a> &gt;
            <a href="resident-details.php?id=<?php echo $residentId; ?>">Resident Details</a> &gt;
            <span>Properties</span>
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
            <h1>Properties for <?php echo htmlspecialchars($resident['name']); ?></h1>
            <div>
                <a href="resident-details.php?id=<?php echo $residentId; ?>" class="btn btn-secondary">Back to Resident</a>
            </div>
        </div>
        
        <?php if (count($properties) > 0): ?>
        <div class="property-cards">
            <?php foreach ($properties as $property): ?>
            <div class="property-card">
                <div class="property-header">
                    <div class="property-title"><?php echo htmlspecialchars($property['name']); ?></div>
                    <div class="property-type"><?php echo htmlspecialchars($property['property_type_name']); ?></div>
                </div>
                <div class="property-content">
                    <div class="property-info">
                        <div class="property-label">Address:</div>
                        <div><?php echo htmlspecialchars($property['address']); ?></div>
                    </div>
                    <div class="property-info">
                        <div class="property-label">Description:</div>
                        <div><?php echo htmlspecialchars($property['description'] ?? 'No description provided'); ?></div>
                    </div>
                    <div class="property-info">
                        <div class="property-label">Added on:</div>
                        <div><?php echo date('F j, Y', strtotime($property['created_at'])); ?></div>
                    </div>
                    <div class="property-status <?php echo $property['status'] === 'active' ? 'status-active' : 'status-inactive'; ?>">
                        <?php echo ucfirst(htmlspecialchars($property['status'])); ?>
                    </div>
                </div>
                <div class="property-footer">
                    <a href="property-details.php?id=<?php echo $property['id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
            <div class="pagination-item">
                <a href="?id=<?php echo $residentId; ?>&page=<?php echo $page - 1; ?>" class="pagination-link">&laquo; Previous</a>
            </div>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <div class="pagination-item">
                <a href="?id=<?php echo $residentId; ?>&page=<?php echo $i; ?>" class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
            </div>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
            <div class="pagination-item">
                <a href="?id=<?php echo $residentId; ?>&page=<?php echo $page + 1; ?>" class="pagination-link">Next &raquo;</a>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php else: ?>
        <div class="property-empty">
            <h3>No properties found</h3>
            <p>This resident has not added any properties yet.</p>
        </div>
        <?php endif; ?>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html> 
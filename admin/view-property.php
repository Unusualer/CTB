<?php
// Start session
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "You must be logged in as an administrator to access this page.";
    header("Location: ../login.php");
    exit();
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Property ID is required.";
    header("Location: properties.php");
    exit();
}

$property_id = (int)$_GET['id'];
$property = null;
$assigned_resident = null;
$maintenance_items = [];

// Get property data
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch property details
    $stmt = $db->prepare("SELECT p.*, u.name as resident_name, u.id as resident_id, u.email as resident_email 
                        FROM properties p 
                        LEFT JOIN users u ON p.user_id = u.id
                        WHERE p.id = :id");
    $stmt->bindParam(':id', $property_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        $_SESSION['error'] = "Property not found.";
        header("Location: properties.php");
        exit();
    }
    
    $property = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if there's a resident assigned to this property
    if (!empty($property['resident_id'])) {
        $assigned_resident = [
            'id' => $property['resident_id'],
            'name' => $property['resident_name'],
            'email' => $property['resident_email']
        ];
    }
    
    // Get maintenance items for this property
    try {
        // First check if maintenance table has property_id column
        $check_stmt = $db->prepare("SHOW COLUMNS FROM maintenance LIKE 'property_id'");
        $check_stmt->execute();
        $hasPropertyIdColumn = $check_stmt->rowCount() > 0;
        
        if ($hasPropertyIdColumn) {
            $maint_stmt = $db->prepare("SELECT * FROM maintenance
                                    WHERE property_id = :property_id 
                                    ORDER BY created_at DESC LIMIT 10");
            $maint_stmt->bindParam(':property_id', $property_id, PDO::PARAM_INT);
            $maint_stmt->execute();
            $maintenance_items = $maint_stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            // In the simplified schema, maintenance might not have property_id
            $maintenance_items = [];
        }
    } catch (PDOException $e) {
        // If there's an error, just set maintenance items to empty array
        $maintenance_items = [];
    }
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    header("Location: properties.php");
    exit();
}

// Page title
$page_title = "View Property";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Community Trust Bank</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/admin-sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <div class="breadcrumb">
                    <a href="properties.php">Properties</a>
                    <span>View Property</span>
                </div>
                <div class="actions">
                    <a href="edit-property.php?id=<?php echo $property['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Property
                    </a>
                </div>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                        echo $_SESSION['success']; 
                        unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                        echo $_SESSION['error']; 
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="content-wrapper">
                <div class="profile-header card">
                    <div class="profile-header-content">
                        <div class="profile-avatar">
                            <?php if ($property['type'] === 'apartment'): ?>
                                <i class="fas fa-home"></i>
                            <?php elseif ($property['type'] === 'parking'): ?>
                                <i class="fas fa-car"></i>
                            <?php else: ?>
                                <i class="fas fa-building"></i>
                            <?php endif; ?>
                        </div>
                        <div class="profile-details">
                            <div class="profile-name-wrapper">
                                <h2><?php echo ucfirst(htmlspecialchars($property['type'])); ?> <?php echo htmlspecialchars($property['identifier']); ?></h2>
                                <div class="user-id-badge">ID: <?php echo $property['id']; ?></div>
                            </div>
                            <div class="profile-meta">
                                <span class="user-role"><i class="fas fa-tag"></i> <?php echo ucfirst(htmlspecialchars($property['type'])); ?></span>
                                <span class="user-status">
                                    <i class="fas fa-<?php echo !empty($assigned_resident) ? 'check-circle' : 'times-circle'; ?>"></i> 
                                    <?php echo !empty($assigned_resident) ? 'Assigned' : 'Unassigned'; ?>
                                </span>
                                <span class="user-joined"><i class="far fa-calendar-alt"></i> Created <?php echo date('M d, Y', strtotime($property['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-grid">
                    <!-- Property Information Card -->
                    <div class="card user-info-card">
                        <div class="card-header">
                            <h3><i class="fas fa-info-circle"></i> Property Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="info-grid">
                                <div class="info-group">
                                    <label><i class="fas fa-id-card"></i> Identifier:</label>
                                    <span class="info-value"><?php echo htmlspecialchars($property['identifier']); ?></span>
                                </div>
                                <div class="info-group">
                                    <label><i class="fas fa-tag"></i> Type:</label>
                                    <span class="info-value"><?php echo ucfirst(htmlspecialchars($property['type'])); ?></span>
                                </div>
                                <div class="info-group">
                                    <label><i class="fas fa-calendar"></i> Created:</label>
                                    <span class="info-value"><?php echo date('F d, Y', strtotime($property['created_at'])); ?></span>
                                </div>
                                <div class="info-group">
                                    <label><i class="fas fa-calendar-alt"></i> Last Updated:</label>
                                    <span class="info-value"><?php echo date('F d, Y', strtotime($property['updated_at'])); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Assigned Resident Card -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-user"></i> Assigned Resident</h3>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($assigned_resident)): ?>
                                <div class="resident-info">
                                    <div class="resident-avatar">
                                        <i class="fas fa-user-circle"></i>
                                    </div>
                                    <div class="resident-details">
                                        <h4><?php echo htmlspecialchars($assigned_resident['name']); ?></h4>
                                        <p><?php echo htmlspecialchars($assigned_resident['email']); ?></p>
                                        <a href="view-user.php?id=<?php echo $assigned_resident['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View Resident
                                        </a>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-user-slash"></i>
                                    <p>No resident assigned to this property.</p>
                                    <a href="edit-property.php?id=<?php echo $property['id']; ?>" class="btn btn-primary">
                                        <i class="fas fa-user-plus"></i> Assign Resident
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Maintenance Items Card -->
                    <?php if (!empty($maintenance_items)): ?>
                    <div class="card maintenance-card">
                        <div class="card-header">
                            <h3><i class="fas fa-tools"></i> Recent Maintenance</h3>
                        </div>
                        <div class="card-body">
                            <div class="maintenance-list">
                                <?php foreach ($maintenance_items as $log): ?>
                                    <div class="maintenance-item">
                                        <div class="maintenance-status <?php echo $log['status']; ?>">
                                            <i class="fas fa-circle"></i>
                                        </div>
                                        <div class="maintenance-details">
                                            <div class="maintenance-title"><?php echo htmlspecialchars($log['description']); ?></div>
                                            <div class="maintenance-meta">
                                                <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($log['date'])); ?></span>
                                                <span><i class="fas fa-info-circle"></i> <?php echo ucfirst(htmlspecialchars($log['status'])); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="js/dark-mode.js"></script>
    
    <style>
        /* Breadcrumb styling for dark mode */
        [data-theme="dark"] .breadcrumb {
            color: #b0b0b0;
        }
        
        [data-theme="dark"] .breadcrumb a {
            color: #ffffff;
        }
    </style>
</body>
</html> 
<?php
// Start session
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/role_access.php';
require_once '../includes/translations.php';

// Check if user is logged in and has appropriate role
requireAnyRole(['admin', 'manager']);


// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = __("Property ID is required.");
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
        $_SESSION['error'] = __("Property not found.");
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
    $_SESSION['error'] = __("Database error:") . " " . $e->getMessage();
    header("Location: properties.php");
    exit();
}

// Page title
$page_title = __("View Property");
?>

<!DOCTYPE html>
<html lang="<?php echo substr($_SESSION['language'] ?? 'en_US', 0, 2); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo __("Complexe Tanger Boulevard"); ?></title>
    <!-- Favicon -->
    <?php favicon_links(); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin-style.css">
    <link rel="stylesheet" href="css/colorful-theme.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/admin-sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <div class="breadcrumb">
                    <a href="properties.php"><?php echo __("Properties"); ?></a>
                    <span><?php echo __("View Property"); ?></span>
                </div>
                <div class="actions">
                    <a href="edit-property.php?id=<?php echo $property['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> <?php echo __("Edit Property"); ?>
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
                                <h2><?php echo ucfirst(htmlspecialchars(__($property['type']))); ?> <?php echo htmlspecialchars($property['identifier']); ?></h2>
                                <div class="user-id-badge"><?php echo __("ID"); ?>: <?php echo $property['id']; ?></div>
                            </div>
                            <div class="profile-meta">
                                <span class="user-role"><i class="fas fa-tag"></i> <?php echo ucfirst(htmlspecialchars(__($property['type']))); ?></span>
                                <span class="user-status">
                                    <i class="fas fa-<?php echo !empty($assigned_resident) ? 'check-circle' : 'times-circle'; ?>"></i> 
                                    <?php echo !empty($assigned_resident) ? __('Assigned') : __('Unassigned'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-grid">
                    <!-- Property Information Card -->
                    <div class="card user-info-card">
                        <div class="card-header">
                            <h3><i class="fas fa-info-circle"></i> <?php echo __("Property Information"); ?></h3>
                        </div>
                        <div class="card-body">
                            <div class="info-grid">
                                <div class="info-group">
                                    <label><i class="fas fa-id-card"></i> <?php echo __("Identifier"); ?>:</label>
                                    <span class="info-value"><?php echo htmlspecialchars($property['identifier']); ?></span>
                                </div>
                                <div class="info-group">
                                    <label><i class="fas fa-tag"></i> <?php echo __("Type"); ?>:</label>
                                    <span class="info-value"><?php echo ucfirst(htmlspecialchars(__($property['type']))); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Assigned Resident Card -->
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-user"></i> <?php echo __("Assigned Resident"); ?></h3>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($assigned_resident)): ?>
                                <div class="resident-info">
                                    <div class="resident-avatar">
                                        <div class="avatar-circle">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    </div>
                                    <div class="resident-details">
                                        <h4><?php echo htmlspecialchars($assigned_resident['name']); ?></h4>
                                        <p class="resident-email"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($assigned_resident['email']); ?></p>
                                        <div class="resident-actions">
                                            <a href="view-user.php?id=<?php echo $assigned_resident['id']; ?>" class="btn btn-primary">
                                                <i class="fas fa-eye"></i> <?php echo __("View Profile"); ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <i class="fas fa-user-slash"></i>
                                    </div>
                                    <p><?php echo __("No resident assigned to this property."); ?></p>
                                    <a href="edit-property.php?id=<?php echo $property['id']; ?>" class="btn btn-primary">
                                        <i class="fas fa-user-plus"></i> <?php echo __("Assign Resident"); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Maintenance Items Card -->
                    <?php if (!empty($maintenance_items)): ?>
                    <div class="card maintenance-card">
                        <div class="card-header">
                            <h3><i class="fas fa-tools"></i> <?php echo __("Recent Maintenance"); ?></h3>
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
                                                <span><i class="fas fa-calendar"></i> <?php echo date('d M Y', strtotime($log['date'])); ?></span>
                                                <span><i class="fas fa-info-circle"></i> <?php echo ucfirst(htmlspecialchars(__($log['status']))); ?></span>
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
        
        /* Profile Header Icons */
        .profile-avatar {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #4a80f0, #2c57b5);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1.5rem;
            color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .profile-avatar i {
            font-size: 36px; /* Icône plus grande */
        }
        
        /* Resident Card Styling */
        .resident-info {
            display: flex;
            align-items: center;
            padding: 1.25rem;
            border-radius: 0.5rem;
            background-color: var(--light-color);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        
        .resident-info:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }
        
        .resident-avatar {
            margin-right: 1.5rem;
        }
        
        .avatar-circle {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #4a80f0, #2c57b5);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 2rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .resident-details {
            flex: 1;
        }
        
        .resident-details h4 {
            margin: 0 0 0.5rem 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .resident-email {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            color: var(--text-secondary);
            font-size: 0.95rem;
        }
        
        .resident-email i {
            margin-right: 0.5rem;
            font-size: 0.875rem;
            color: var(--primary-color);
        }
        
        .resident-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 0.5rem;
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        
        .btn-outline:hover {
            background-color: var(--secondary-bg);
            color: var(--primary-color);
            border-color: var(--primary-color-light);
        }
        
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1.5rem;
            text-align: center;
            background-color: var(--light-color);
            border-radius: 0.5rem;
            border: 1px dashed var(--border-color);
        }
        
        .empty-icon {
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--secondary-bg);
            border-radius: 50%;
            margin-bottom: 1.25rem;
        }
        
        .empty-icon i {
            font-size: 2rem;
            color: var(--text-secondary);
        }
        
        .empty-state p {
            margin-bottom: 1.25rem;
            color: var(--text-secondary);
            font-size: 1rem;
        }
        
        /* Dark mode styles */
        [data-theme="dark"] .resident-info {
            background-color: var(--card-bg);
            border-color: #3f4756;
        }
        
        [data-theme="dark"] .resident-details h4 {
            color: #ffffff;
        }
        
        [data-theme="dark"] .resident-email {
            color: #b0b0b0;
        }
        
        [data-theme="dark"] .btn-outline {
            border-color: #3f4756;
            color: #ffffff;
        }
        
        [data-theme="dark"] .btn-outline:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--primary-color-light);
        }
        
        [data-theme="dark"] .empty-state {
            background-color: var(--card-bg);
            border-color: #3f4756;
        }
        
        [data-theme="dark"] .empty-icon {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        [data-theme="dark"] .empty-state p {
            color: #b0b0b0;
        }
    </style>
</body>
</html> 
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
$maintenance_logs = [];

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
    
    // Get maintenance logs for this property
    try {
        // First check if maintenance_logs table has property_id column
        $check_stmt = $db->prepare("SHOW COLUMNS FROM maintenance_logs LIKE 'property_id'");
        $check_stmt->execute();
        $hasPropertyIdColumn = $check_stmt->rowCount() > 0;
        
        if ($hasPropertyIdColumn) {
            $maint_stmt = $db->prepare("SELECT * FROM maintenance_logs 
                                    WHERE property_id = :property_id 
                                    ORDER BY created_at DESC LIMIT 10");
            $maint_stmt->bindParam(':property_id', $property_id, PDO::PARAM_INT);
            $maint_stmt->execute();
            $maintenance_logs = $maint_stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            // In the simplified schema, maintenance_logs might not have property_id
            $maintenance_logs = [];
        }
    } catch (PDOException $e) {
        // If there's an error, just set maintenance logs to empty array
        $maintenance_logs = [];
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
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../assets/images/logo.png" alt="CTB Logo" class="logo">
                <h2>CTB Admin</h2>
            </div>
            
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="user-details">
                    <h4><?php echo htmlspecialchars($_SESSION['name']); ?></h4>
                    <p>Administrator</p>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <a href="dashboard.php">
                            <i class="fas fa-chart-line"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="users.php">
                            <i class="fas fa-users"></i>
                            <span>Users</span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="properties.php">
                            <i class="fas fa-building"></i>
                            <span>Properties</span>
                        </a>
                    </li>
                    <li>
                        <a href="tickets.php">
                            <i class="fas fa-ticket-alt"></i>
                            <span>Tickets</span>
                        </a>
                    </li>
                    <li>
                        <a href="payments.php">
                            <i class="fas fa-credit-card"></i>
                            <span>Payments</span>
                        </a>
                    </li>
                    <li>
                        <a href="activity-log.php">
                            <i class="fas fa-history"></i>
                            <span>Activity Log</span>
                        </a>
                    </li>
                    <li>
                        <a href="maintenance-new.php">
                            <i class="fas fa-tools"></i>
                            <span>Maintenance</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <div class="theme-toggle">
                    <i class="fas fa-moon"></i>
                    <label class="switch">
                        <input type="checkbox" id="darkModeToggle">
                        <span class="slider round"></span>
                    </label>
                </div>
                <a href="../logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

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
                    <a href="javascript:void(0);" class="btn btn-danger delete-property" data-id="<?php echo $property['id']; ?>">
                        <i class="fas fa-trash-alt"></i> Delete Property
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

                    <!-- Maintenance Logs Card -->
                    <?php if (!empty($maintenance_logs)): ?>
                    <div class="card maintenance-card">
                        <div class="card-header">
                            <h3><i class="fas fa-tools"></i> Recent Maintenance</h3>
                        </div>
                        <div class="card-body">
                            <div class="maintenance-list">
                                <?php foreach ($maintenance_logs as $log): ?>
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

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirm Deletion</h3>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this property? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <form id="deleteForm" action="delete-property.php" method="POST">
                    <input type="hidden" name="property_id" id="deletePropertyId">
                    <button type="button" class="btn btn-secondary close-modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <script src="js/dark-mode.js"></script>
    <script>
        // Delete property modal functionality
        const modal = document.getElementById('deleteModal');
        const deleteButtons = document.querySelectorAll('.delete-property');
        const closeButtons = document.querySelectorAll('.close, .close-modal');
        const deleteForm = document.getElementById('deleteForm');
        const deletePropertyIdInput = document.getElementById('deletePropertyId');
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const propertyId = this.getAttribute('data-id');
                deletePropertyIdInput.value = propertyId;
                modal.style.display = 'block';
            });
        });
        
        closeButtons.forEach(button => {
            button.addEventListener('click', function() {
                modal.style.display = 'none';
            });
        });
        
        window.addEventListener('click', function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        });
    </script>
</body>
</html> 
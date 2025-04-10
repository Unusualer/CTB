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
$types = ['apartment', 'parking'];
$residents = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    $type = trim($_POST['type'] ?? '');
    $identifier = trim($_POST['identifier'] ?? '');
    $user_id = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null;
    
    $errors = [];
    
    // Validate required fields
    if (empty($type) || !in_array($type, $types)) {
        $errors[] = "Valid property type is required.";
    }
    
    if (empty($identifier)) {
        $errors[] = "Identifier is required.";
    }
    
    // If no errors, update property
    if (empty($errors)) {
        try {
            $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Check if the identifier already exists for other properties
            $check_stmt = $db->prepare("SELECT id FROM properties WHERE identifier = :identifier AND id != :id");
            $check_stmt->bindParam(':identifier', $identifier);
            $check_stmt->bindParam(':id', $property_id, PDO::PARAM_INT);
            $check_stmt->execute();
            
            if ($check_stmt->rowCount() > 0) {
                $_SESSION['error'] = "Property identifier already exists. Please choose a different one.";
            } else {
                // Update property
                $update_stmt = $db->prepare("UPDATE properties SET 
                                type = :type, 
                                identifier = :identifier, 
                                user_id = :user_id,
                                updated_at = NOW() 
                                WHERE id = :id");
                
                $update_stmt->bindParam(':type', $type);
                $update_stmt->bindParam(':identifier', $identifier);
                $update_stmt->bindParam(':user_id', $user_id);
                $update_stmt->bindParam(':id', $property_id, PDO::PARAM_INT);
                
                $update_stmt->execute();
                
                // Log the activity
                $admin_id = $_SESSION['user_id'];
                $log_stmt = $db->prepare("INSERT INTO activity_log (user_id, action, description) 
                                       VALUES (:admin_id, 'update', :description)");
                
                $description = "Updated property information for property ID: $property_id";
                $log_stmt->bindParam(':admin_id', $admin_id);
                $log_stmt->bindParam(':description', $description);
                $log_stmt->execute();
                
                $_SESSION['success'] = "Property updated successfully.";
                header("Location: view-property.php?id=$property_id");
                exit();
            }
            
        } catch (PDOException $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
}

// Get property data
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch property details
    $stmt = $db->prepare("SELECT * FROM properties WHERE id = :id");
    $stmt->bindParam(':id', $property_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        $_SESSION['error'] = "Property not found.";
        header("Location: properties.php");
        exit();
    }
    
    $property = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get resident users for dropdown
    $user_stmt = $db->prepare("SELECT id, name, email FROM users WHERE role = 'resident' ORDER BY name");
    $user_stmt->execute();
    $residents = $user_stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    header("Location: properties.php");
    exit();
}

// Page title
$page_title = "Edit Property";
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
                    <a href="properties.php">Properties</a> / 
                    <a href="view-property.php?id=<?php echo $property_id; ?>">View Property</a> / 
                    <span>Edit Property</span>
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
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-edit"></i> Edit Property</h3>
                    </div>
                    <div class="card-body">
                        <form action="edit-property.php?id=<?php echo $property_id; ?>" method="POST" class="form">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="type">Property Type <span class="required">*</span></label>
                                    <select id="type" name="type" required>
                                        <option value="">Select Property Type</option>
                                        <option value="apartment" <?php echo $property['type'] === 'apartment' ? 'selected' : ''; ?>>Apartment</option>
                                        <option value="parking" <?php echo $property['type'] === 'parking' ? 'selected' : ''; ?>>Parking</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="identifier">Identifier <span class="required">*</span></label>
                                    <input type="text" id="identifier" name="identifier" value="<?php echo htmlspecialchars($property['identifier']); ?>" required>
                                    <div class="help-text">A unique identifier for this property (e.g., "A101" for apartment, "P45" for parking spot)</div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="user_id">Assigned Resident</label>
                                    <select id="user_id" name="user_id">
                                        <option value="">Unassigned</option>
                                        <?php foreach ($residents as $resident): ?>
                                            <option value="<?php echo $resident['id']; ?>" <?php echo (isset($property['user_id']) && $property['user_id'] == $resident['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($resident['name']); ?> (<?php echo htmlspecialchars($resident['email']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Property
                                </button>
                                <a href="view-property.php?id=<?php echo $property_id; ?>" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Dark mode toggle
        const darkModeToggle = document.getElementById('darkModeToggle');
        const htmlElement = document.documentElement;
        
        // Check for saved theme preference or use user's system preference
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            htmlElement.setAttribute('data-theme', 'dark');
            darkModeToggle.checked = true;
        } else {
            htmlElement.setAttribute('data-theme', 'light');
            darkModeToggle.checked = false;
        }
        
        // Toggle theme when switch is clicked
        darkModeToggle.addEventListener('change', function() {
            if (this.checked) {
                htmlElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
            } else {
                htmlElement.setAttribute('data-theme', 'light');
                localStorage.setItem('theme', 'light');
            }
        });
    </script>
</body>
</html> 
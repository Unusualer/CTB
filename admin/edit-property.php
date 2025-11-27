<?php
// Start session
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/role_access.php';
require_once '../includes/translations.php';

// Check if user is logged in and has appropriate role
requireRole('admin');


// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = __("Property ID is required.");
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
    
    // Validate required fields - only validate type and identifier existence, not their values
    if (empty($type) || !in_array($type, $types)) {
        $errors[] = __("Invalid property type.");
    }
    
    if (empty($identifier)) {
        $errors[] = __("Identifier is required.");
    }
    
    // If no errors, update property
    if (empty($errors)) {
        try {
            $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Get the existing property data
            $check_stmt = $db->prepare("SELECT type, identifier FROM properties WHERE id = :id");
            $check_stmt->bindParam(':id', $property_id, PDO::PARAM_INT);
            $check_stmt->execute();
            $existing_property = $check_stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$existing_property) {
                $_SESSION['error'] = __("Property not found.");
            } else {
                // Update only the user_id, keeping the original type and identifier
                $update_stmt = $db->prepare("UPDATE properties SET 
                                user_id = :user_id,
                                updated_at = NOW() 
                                WHERE id = :id");
                
                $update_stmt->bindParam(':user_id', $user_id);
                $update_stmt->bindParam(':id', $property_id, PDO::PARAM_INT);
                
                $update_stmt->execute();
                
                // Log the activity
                $admin_id = $_SESSION['user_id'];
                $log_stmt = $db->prepare("INSERT INTO activity_log (user_id, action, description) 
                                       VALUES (:admin_id, 'update', :description)");
                
                if ($user_id) {
                    $description = __("Assigned resident (ID:") . " $user_id) " . __("to property ID:") . " $property_id";
                } else {
                    $description = __("Removed resident assignment from property ID:") . " $property_id";
                }
                $log_stmt->bindParam(':admin_id', $admin_id);
                $log_stmt->bindParam(':description', $description);
                $log_stmt->execute();
                
                $_SESSION['success'] = __("Property assignment updated successfully.");
                header("Location: view-property.php?id=$property_id");
                exit();
            }
            
        } catch (PDOException $e) {
            $_SESSION['error'] = __("Database error:") . " " . $e->getMessage();
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
        $_SESSION['error'] = __("Property not found.");
        header("Location: properties.php");
        exit();
    }
    
    $property = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get resident users for dropdown
    $user_stmt = $db->prepare("SELECT id, name, email FROM users WHERE role = 'resident' ORDER BY name");
    $user_stmt->execute();
    $residents = $user_stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $_SESSION['error'] = __("Database error:") . " " . $e->getMessage();
    header("Location: properties.php");
    exit();
}

// Page title
$page_title = __("Assign Resident to Property");

// Set the current theme from localStorage or system preference
$theme = 'light';
if (isset($_COOKIE['darkMode']) && $_COOKIE['darkMode'] === 'enabled') {
    $theme = 'dark';
} else if (!isset($_COOKIE['darkMode']) && isset($_SERVER['HTTP_SEC_CH_PREFERS_COLOR_SCHEME']) && $_SERVER['HTTP_SEC_CH_PREFERS_COLOR_SCHEME'] === 'dark') {
    $theme = 'dark';
}
?>

<!DOCTYPE html>
<html lang="<?php echo substr($_SESSION['language'] ?? 'en_US', 0, 2); ?>" data-theme="<?php echo $theme; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo __("Community Trust Bank"); ?></title>
    <!-- Favicon -->
    <?php favicon_links(); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin-style.css">
    <style>
        /* Enhanced Form Styling */
        .card {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }
        
        .card-header {
            padding: 18px 24px;
            border-bottom: none;
        }
        
        .card-header h3 {
            font-weight: 600;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card-header h3 i {
            font-size: 1.1rem;
        }
        
        .card-body {
            padding: 30px;
        }
        
        .form-group label {
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.95rem;
            letter-spacing: 0.2px;
            display: inline-block;
        }
        
        .required {
            color: #ff5c75;
            font-weight: 700;
        }
        
        input, select {
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background-color: var(--light-color);
            color: var(--text-primary);
            font-size: 0.95rem;
            transition: all 0.2s ease;
            width: 100%;
        }
        
        input:hover, select:hover {
            border-color: var(--primary-color-light);
        }
        
        input:focus, select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.15);
            outline: none;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 24px;
        }
        
        .form-section-title {
            margin: 30px 0 20px;
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 24px;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-color-dark);
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background-color: var(--light-gray);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }
        
        .btn-secondary:hover {
            background-color: var(--light-color);
            transform: translateY(-1px);
        }
        
        select[multiple] {
            height: auto;
            min-height: 120px;
        }
        
        .property-type-icon {
            font-size: 1.8rem;
            color: var(--primary-color);
            margin-right: 10px;
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 14px;
            color: var(--text-secondary);
            pointer-events: none;
        }
        
        .input-with-icon input {
            padding-left: 40px;
        }
        
        .form-group-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .form-group-header h4 {
            margin: 0;
            font-weight: 600;
            font-size: 1.05rem;
        }
        
        .resident-item {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 8px;
            background-color: var(--light-color);
            border: 1px solid var(--border-color);
            transition: all 0.2s ease;
        }
        
        .resident-item:hover {
            border-color: var(--primary-color-light);
            background-color: rgba(var(--primary-rgb), 0.05);
        }
        
        .resident-item-info {
            flex: 1;
        }
        
        .resident-item-name {
            font-weight: 600;
            display: block;
            margin-bottom: 3px;
        }
        
        .resident-item-email {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }
        
        .resident-select-container {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 10px;
            background: var(--bg-color);
        }
        
        .no-residents-message {
            padding: 15px;
            text-align: center;
            color: var(--text-secondary);
            background-color: var(--light-color);
            border-radius: 8px;
            border: 1px dashed var(--border-color);
        }
        
        /* Dark mode specific styles */
        .dark-mode .card,
        [data-theme="dark"] .card {
            background-color: var(--card-bg);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .dark-mode .card-header,
        [data-theme="dark"] .card-header {
            border-bottom-color: #3f4756;
        }
        
        .dark-mode .card-header h3,
        [data-theme="dark"] .card-header h3 {
            color: #ffffff;
        }
        
        .dark-mode .form-section-title,
        [data-theme="dark"] .form-section-title {
            color: #ffffff;
            border-bottom-color: #3f4756;
        }
        
        .dark-mode .form-group label,
        [data-theme="dark"] .form-group label {
            color: #e0e0e0;
        }
        
        .dark-mode input, 
        .dark-mode select,
        [data-theme="dark"] input, 
        [data-theme="dark"] select {
            background-color: #2c3241;
            color: #ffffff;
            border-color: #3f4756;
        }
        
        .dark-mode input:hover, 
        .dark-mode select:hover,
        [data-theme="dark"] input:hover, 
        [data-theme="dark"] select:hover {
            border-color: var(--primary-color);
        }
        
        .dark-mode .property-type,
        .dark-mode .property-identifier,
        [data-theme="dark"] .property-type,
        [data-theme="dark"] .property-identifier {
            color: #e0e0e0;
            background-color: #2c3241;
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid #3f4756;
        }
        
        .dark-mode .text-muted,
        [data-theme="dark"] .text-muted {
            color: #b0b0b0 !important;
        }
        
        .dark-mode .btn-secondary,
        [data-theme="dark"] .btn-secondary {
            background-color: #3a4155;
            color: #ffffff;
            border-color: #4f5a75;
        }
        
        .dark-mode .btn-secondary:hover,
        [data-theme="dark"] .btn-secondary:hover {
            background-color: #4a526b;
        }
        
        .dark-mode .no-residents-message,
        [data-theme="dark"] .no-residents-message {
            background-color: #2c3241;
            border-color: #3f4756;
            color: #b0b0b0;
        }
        
        .dark-mode .breadcrumb,
        [data-theme="dark"] .breadcrumb {
            color: #b0b0b0;
        }
        
        .dark-mode .breadcrumb a,
        [data-theme="dark"] .breadcrumb a {
            color: #ffffff;
        }
    </style>
</head>
<body class="<?php echo $theme === 'dark' ? 'dark-mode' : ''; ?>">
    <div class="admin-container">
        <?php include 'includes/admin-sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <div class="breadcrumb">
                    <a href="properties.php"><?php echo __("Properties"); ?></a>
                    <a href="view-property.php?id=<?php echo $property_id; ?>"><?php echo __("View Property"); ?></a>
                    <span><?php echo __("Assign Resident"); ?></span>
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
                        <h3>
                            <i class="fas fa-building"></i> 
                            <?php echo __("Assign Resident to Property"); ?>: <?php echo ucfirst(htmlspecialchars(__($property['type']))); ?> <?php echo htmlspecialchars($property['identifier']); ?>
                        </h3>
                    </div>
                    <div class="card-body">
                        <form action="edit-property.php?id=<?php echo $property_id; ?>" method="post">
                            <input type="hidden" name="type" value="<?php echo htmlspecialchars($property['type']); ?>">
                            <input type="hidden" name="identifier" value="<?php echo htmlspecialchars($property['identifier']); ?>">
                            
                            <div class="form-section-title">
                                <i class="fas fa-info-circle"></i> <?php echo __("Property Details"); ?>
                            </div>
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="type">
                                        <i class="fas fa-tag"></i> <?php echo __("Type"); ?>
                                    </label>
                                    <div class="property-type">
                                        <?php if ($property['type'] === 'apartment'): ?>
                                            <div class="property-type-icon">
                                                <i class="fas fa-home"></i>
                                            </div>
                                            <div class="property-type-label">
                                                <?php echo __("Apartment"); ?>
                                            </div>
                                        <?php elseif ($property['type'] === 'parking'): ?>
                                            <div class="property-type-icon">
                                                <i class="fas fa-car"></i>
                                            </div>
                                            <div class="property-type-label">
                                                <?php echo __("Parking"); ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="property-type-icon">
                                                <i class="fas fa-building"></i>
                                            </div>
                                            <div class="property-type-label">
                                                <?php echo ucfirst(htmlspecialchars(__($property['type']))); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="identifier">
                                        <i class="fas fa-id-card"></i> <?php echo __("Identifier"); ?>
                                    </label>
                                    <div class="property-identifier">
                                        <?php echo htmlspecialchars($property['identifier']); ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-section-title">
                                <i class="fas fa-user"></i> <?php echo __("Resident Assignment"); ?>
                            </div>
                            
                                <div class="form-group">
                                <label for="user_id">
                                    <?php echo __("Assigned Resident"); ?> <span class="text-muted">(<?php echo __("optional"); ?>)</span>
                                </label>
                                
                                <?php if (count($residents) > 0): ?>
                                    <div class="form-resident-selection">
                                        <select name="user_id" id="user_id" class="form-control">
                                            <option value=""><?php echo __("No resident assigned"); ?></option>
                                        <?php foreach ($residents as $resident): ?>
                                                <option value="<?php echo $resident['id']; ?>" <?php echo ($property['user_id'] == $resident['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($resident['name']); ?> (<?php echo htmlspecialchars($resident['email']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    </div>
                                <?php else: ?>
                                    <div class="no-residents-message">
                                        <i class="fas fa-user-slash"></i> <?php echo __("No resident users available. Create resident users first."); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        
                            <div class="form-actions">
                                <a href="view-property.php?id=<?php echo $property_id; ?>" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> <?php echo __("Cancel"); ?>
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> <?php echo __("Save Changes"); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Include the global dark mode script -->
    <script src="js/dark-mode.js"></script>
</body>
</html> 
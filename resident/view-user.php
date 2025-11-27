<?php
// Start session
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/role_access.php';

// Check if user is logged in and has appropriate role
requireAnyRole(['resident']);

// Include translation function if not already included
if (!function_exists('__')) {
    $translations_file = dirname(__DIR__) . '/includes/translations.php';
    if (file_exists($translations_file)) {
        require_once $translations_file;
    } else {
        // Fallback to alternate locations
        $alt_translations_file = $_SERVER['DOCUMENT_ROOT'] . '/CTB/includes/translations.php';
        if (file_exists($alt_translations_file)) {
            require_once $alt_translations_file;
        } else {
            // Define a minimal translation function as last resort
            function __($text) {
                return $text;
            }
        }
    }
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = __("User ID is required.");
    header("Location: users.php");
    exit();
}

$user_id = (int)$_GET['id'];
$current_user_id = $_SESSION['user_id'];

// For residents, only allow viewing their own profile
if (getCurrentRole() === 'resident' && $user_id != $current_user_id) {
    $_SESSION['error'] = __("You can only view your own profile.");
    header("Location: view-user.php?id=" . $current_user_id);
    exit();
}

$user = null;
$assigned_properties = [];

// Get user data
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch user details
    $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        $_SESSION['error'] = __("User not found.");
        header("Location: users.php");
        exit();
    }
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // If user is a resident, get assigned properties
    if ($user['role'] === 'resident') {
        $prop_stmt = $db->prepare("SELECT * FROM properties WHERE user_id = :user_id");
        $prop_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $prop_stmt->execute();
        $assigned_properties = $prop_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
} catch (PDOException $e) {
    $_SESSION['error'] = __("Database error") . ": " . $e->getMessage();
    header("Location: users.php");
    exit();
}

// Page title
$page_title = __("My Profile");
?>

<!DOCTYPE html>
<html lang="<?php echo isset($_SESSION['language']) ? substr($_SESSION['language'], 0, 2) : 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo __("Community Trust Bank"); ?></title>
    <!-- Favicon -->
    <?php favicon_links(); ?>
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
                    <span><?php echo __("My Profile"); ?></span>
                </div>
                <div class="actions">
                    <a href="edit-user.php?id=<?php echo $user['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> <?php echo __("Edit User"); ?>
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
                            <?php if (!empty($user['profile_image'])): ?>
                                <img src="../assets/img/avatars/<?php echo htmlspecialchars($user['profile_image']); ?>" alt="<?php echo htmlspecialchars($user['name']); ?>">
                            <?php else: ?>
                                <div class="avatar-placeholder">
                                    <i class="fas fa-user-circle"></i>
                                    <span class="avatar-initial"><?php echo substr(htmlspecialchars($user['name']), 0, 1); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="profile-details">
                            <div class="profile-name-wrapper">
                                <h2><?php echo htmlspecialchars($user['name']); ?></h2>
                                <div class="user-id-badge"><?php echo __("ID"); ?>: <?php echo $user['id']; ?></div>
                            </div>
                            <div class="profile-meta">
                                <span class="user-role"><i class="fas fa-user-tag"></i> <?php echo __(ucfirst(htmlspecialchars($user['role']))); ?></span>
                                <span class="user-status <?php echo $user['status']; ?>">
                                    <i class="fas fa-circle"></i> <?php echo __(ucfirst(htmlspecialchars($user['status']))); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-grid">
                    <!-- User Information Card -->
                    <div class="card user-info-card">
                        <div class="card-header">
                            <h3><i class="fas fa-info-circle"></i> <?php echo __("User Information"); ?></h3>
                        </div>
                        <div class="card-body">
                            <div class="info-grid">
                                <div class="info-group">
                                    <label><i class="fas fa-envelope"></i> <?php echo __("Email"); ?>:</label>
                                    <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                                </div>
                                <div class="info-group">
                                    <label><i class="fas fa-phone"></i> <?php echo __("Phone"); ?>:</label>
                                    <span class="info-value"><?php echo !empty($user['phone']) ? htmlspecialchars($user['phone']) : __("Not provided"); ?></span>
                                </div>
                                <div class="info-group">
                                    <label><i class="fas fa-user-shield"></i> <?php echo __("Role"); ?>:</label>
                                    <span class="info-value"><?php echo __(ucfirst(htmlspecialchars($user['role']))); ?></span>
                                </div>
                                <div class="info-group">
                                    <label><i class="fas fa-check-circle"></i> <?php echo __("Status"); ?>:</label>
                                    <span class="status-badge <?php echo $user['status']; ?>"><?php echo __(ucfirst(htmlspecialchars($user['status']))); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resident Properties Card (if user is a resident) -->
                    <?php if ($user['role'] === 'resident'): ?>
                        <div class="card property-assignments-card">
                            <div class="card-header">
                                <h3><i class="fas fa-building"></i> <?php echo __("Assigned Properties"); ?></h3>
                            </div>
                            <div class="card-body">
                                <?php if (empty($assigned_properties)): ?>
                                    <div class="empty-state">
                                        <i class="fas fa-building"></i>
                                        <p><?php echo __("No properties assigned to this resident."); ?></p>
                                    </div>
                                <?php else: ?>
                                    <div class="property-list">
                                        <?php foreach ($assigned_properties as $property): ?>
                                            <div class="property-item">
                                                <div class="property-icon">
                                                    <i class="<?php echo ($property['type'] === 'apartment') ? 'fas fa-home' : 'fas fa-car'; ?>"></i>
                                                </div>
                                                <div class="property-details">
                                                    <h4><?php echo __(ucfirst(htmlspecialchars($property['type']))) . ' ' . htmlspecialchars($property['identifier']); ?></h4>
                                                    <div class="property-meta">
                                                        <span class="property-id"><?php echo __("ID"); ?>: <?php echo $property['id']; ?></span>
                                                        <a href="view-property.php?id=<?php echo $property['id']; ?>" class="view-link">
                                                            <?php echo __("View Property"); ?> <i class="fas fa-arrow-right"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </main>
    </div>

    <script src="js/dark-mode.js"></script>

    <style>
        /* Enhanced Property List Styling */
        .property-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        
        .property-item {
            display: flex;
            align-items: center;
            background-color: var(--light-color);
            border-radius: 8px;
            padding: 16px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        
        .property-item:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }
        
        .property-icon {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--primary-color-light);
            border-radius: 50%;
            margin-right: 16px;
            color: var(--primary-color);
        }
        
        .property-icon i {
            font-size: 1.25rem;
        }
        
        .property-details {
            flex: 1;
        }
        
        .property-details h4 {
            margin: 0 0 8px 0;
            font-size: 1.1rem;
            color: var(--text-primary);
        }
        
        .property-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .property-id {
            font-size: 0.85rem;
            color: var(--text-secondary);
            background-color: var(--secondary-bg);
            padding: 3px 8px;
            border-radius: 4px;
        }
        
        .view-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 12px;
            border-radius: 6px;
            transition: all 0.2s ease;
            background-color: rgba(var(--primary-rgb), 0.1);
        }
        
        .view-link i {
            font-size: 0.8rem;
            transition: transform 0.2s ease;
        }
        
        .view-link:hover {
            background-color: rgba(var(--primary-rgb), 0.15);
            color: var(--primary-color-dark);
        }
        
        .view-link:hover i {
            transform: translateX(3px);
        }
        
        /* Dark mode specific styles */
        [data-theme="dark"] .property-item {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
        }
        
        [data-theme="dark"] .property-details h4 {
            color: #ffffff;
        }
        
        [data-theme="dark"] .property-id {
            color: #b0b0b0;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        [data-theme="dark"] .view-link {
            color: var(--primary-color-light);
            background-color: rgba(var(--primary-rgb), 0.2);
        }
        
        [data-theme="dark"] .view-link:hover {
            background-color: rgba(var(--primary-rgb), 0.3);
            color: #ffffff;
        }
        
        [data-theme="dark"] .property-icon {
            background-color: rgba(var(--primary-rgb), 0.3);
            color: var(--primary-color-light);
        }
        
        /* Breadcrumb styling from edit-user.php */
        [data-theme="dark"] .breadcrumb {
            color: #b0b0b0;
        }
        
        [data-theme="dark"] .breadcrumb a {
            color: #ffffff;
        }
    </style>
</body>
</html> 
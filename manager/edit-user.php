<?php
// Start session
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/role_access.php';

// Check if user is logged in and has appropriate role
requireAnyRole(['admin', 'manager']);

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
$user = null;
$roles = ['admin', 'manager', 'resident'];
$statuses = ['active', 'inactive'];

// Get user data first to check permissions
$user = null;
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
    
    // For managers, only allow editing residents or their own profile
    $current_role = getCurrentRole();
    $current_user_id = (int)$_SESSION['user_id'];
    if ($current_role === 'manager' && $user['role'] !== 'resident' && $user_id !== $current_user_id) {
        $_SESSION['error'] = __("Managers can only edit resident accounts or their own profile.");
        header("Location: users.php");
        exit();
    }
    
} catch (PDOException $e) {
    $_SESSION['error'] = __("Database error") . ": " . $e->getMessage();
    header("Location: users.php");
    exit();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $status = trim($_POST['status'] ?? '');
    
    $errors = [];
    
    // Validate name
    if (empty($name)) {
        $errors[] = __("Name is required.");
    }
    
    // Validate email
    if (empty($email)) {
        $errors[] = __("Email is required.");
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = __("Invalid email format.");
    }
    
    // Validate role
    if (empty($role) || !in_array($role, $roles)) {
        $errors[] = __("A valid role is required.");
    }
    
    // For managers editing their own profile, ensure role stays as manager
    $current_role = getCurrentRole();
    $current_user_id = (int)$_SESSION['user_id'];
    if ($current_role === 'manager' && $user_id === $current_user_id) {
        // Managers cannot change their own role
        $role = 'manager';
    }
    
    // For managers editing residents, ensure role stays as resident
    if ($current_role === 'manager' && $user_id !== $current_user_id) {
        // Managers can only edit residents
        if ($role !== 'resident') {
            $errors[] = __("Managers can only edit resident accounts.");
            $role = 'resident';
        }
    }
    
    // Validate status
    if (empty($status) || !in_array($status, $statuses)) {
        $errors[] = __("A valid status is required.");
    }
    
    // If no errors, update user
    if (empty($errors)) {
        try {
            $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Update user
            $stmt = $db->prepare("UPDATE users SET name = :name, email = :email, phone = :phone, 
                                 role = :role, status = :status, updated_at = NOW() 
                                 WHERE id = :id");
            
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
            
            $stmt->execute();
            
            // Check if password should be updated
            if (!empty($_POST['password'])) {
                $password = $_POST['password'];
                $confirm_password = $_POST['confirm_password'];
                
                if ($password !== $confirm_password) {
                    $_SESSION['error'] = __("Passwords do not match.");
                } elseif (strlen($password) < 8) {
                    $_SESSION['error'] = __("Password must be at least 8 characters.");
                } else {
                    // Hash the password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Update password
                    $pwd_stmt = $db->prepare("UPDATE users SET password = :password WHERE id = :id");
                    $pwd_stmt->bindParam(':password', $hashed_password);
                    $pwd_stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
                    $pwd_stmt->execute();
                }
            }
            
            // Log the activity
            $admin_id = $_SESSION['user_id'];
            $log_stmt = $db->prepare("INSERT INTO activity_log (user_id, action, description) 
                                     VALUES (:admin_id, 'update', :description)");
            
            $description = __("User information updated for user ID") . ": $user_id";
            $log_stmt->bindParam(':admin_id', $admin_id);
            $log_stmt->bindParam(':description', $description);
            $log_stmt->execute();
            
            $_SESSION['success'] = __("User updated successfully.");
            header("Location: view-user.php?id=$user_id");
            exit();
            
        } catch (PDOException $e) {
            $_SESSION['error'] = __("Database error") . ": " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
}

// Page title
$page_title = __("Edit User");
?>

<!DOCTYPE html>
<html lang="<?php echo isset($_SESSION['language']) ? substr($_SESSION['language'], 0, 2) : 'en'; ?>">
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
        
        .section-divider {
            margin: 30px 0 20px;
            position: relative;
            height: 10px;
            text-align: center;
            border-bottom: 1px solid var(--border-color);
        }
        
        .section-divider span {
            background-color: var(--bg-color);
            padding: 0 15px;
            position: relative;
            top: 0;
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
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
            background-color: var(--secondary-bg);
            color: var(--text-primary);
        }
        
        .btn-secondary:hover {
            background-color: var(--border-color);
            transform: translateY(-1px);
        }
        
        small {
            color: var(--text-secondary);
            font-size: 0.8rem;
            margin-top: 5px;
            display: block;
        }
        
        /* Dark mode specific styles */
        [data-theme="dark"] .card-header {
            background: linear-gradient(to right, var(--primary-color), var(--primary-color-dark));
        }
        
        [data-theme="dark"] .card-header h3 {
            color: #fff;
        }
        
        [data-theme="dark"] .form-group label {
            color: #ffffff;
            font-weight: 600;
        }
        
        [data-theme="dark"] .card {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            background-color: var(--card-bg);
        }
        
        [data-theme="dark"] input, 
        [data-theme="dark"] select {
            background-color: #2a2e35 !important;
            color: #ffffff !important;
            border-color: #3f4756;
        }
        
        [data-theme="dark"] input:hover, 
        [data-theme="dark"] select:hover {
            border-color: var(--primary-color-light);
        }
        
        [data-theme="dark"] input:focus, 
        [data-theme="dark"] select:focus {
            border-color: var(--primary-color);
            background-color: #2d3239 !important;
        }
        
        [data-theme="dark"] input::placeholder {
            color: #8e99ad;
        }
        
        [data-theme="dark"] .form-section-title {
            color: #ffffff;
            border-color: #3f4756;
        }
        
        [data-theme="dark"] .section-divider {
            border-color: #3f4756;
        }
        
        [data-theme="dark"] .section-divider span {
            background-color: var(--card-bg);
            color: #ffffff;
        }
        
        [data-theme="dark"] .required {
            color: #ff7a8e;
        }
        
        [data-theme="dark"] small {
            color: #b0b0b0;
        }
        
        [data-theme="dark"] .breadcrumb {
            color: #b0b0b0;
        }
        
        [data-theme="dark"] .breadcrumb a {
            color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/admin-sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <div class="breadcrumb">
                    <a href="users.php"><?php echo __("Users"); ?></a>
                    <a href="view-user.php?id=<?php echo $user_id; ?>"><?php echo __("View User"); ?></a>
                    <span><?php echo __("Edit User"); ?></span>
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
                        <h3><i class="fas fa-user-edit"></i> <?php echo __("Edit User"); ?></h3>
                    </div>
                    <div class="card-body">
                        <form action="edit-user.php?id=<?php echo $user_id; ?>" method="POST" class="form">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="name"><?php echo __("Name"); ?> <span class="required">*</span></label>
                                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email"><?php echo __("Email"); ?> <span class="required">*</span></label>
                                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="phone"><?php echo __("Phone"); ?></label>
                                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="role"><?php echo __("Role"); ?> <span class="required">*</span></label>
                                    <?php 
                                    $current_role = getCurrentRole();
                                    $current_user_id = (int)$_SESSION['user_id'];
                                    $is_own_profile = ($current_role === 'manager' && $user_id === $current_user_id);
                                    ?>
                                    <select id="role" name="role" required <?php echo ($current_role === 'manager') ? 'disabled' : ''; ?>>
                                        <?php 
                                        $allowed_roles = $roles;
                                        if ($current_role === 'manager' && !$is_own_profile) {
                                            // Manager editing a resident - only show resident
                                            $allowed_roles = ['resident'];
                                        } elseif ($is_own_profile) {
                                            // Manager editing their own profile - only show manager
                                            $allowed_roles = ['manager'];
                                        }
                                        foreach ($allowed_roles as $role_option): ?>
                                            <option value="<?php echo $role_option; ?>" <?php echo $user['role'] === $role_option ? 'selected' : ''; ?>>
                                                <?php 
                                                    switch($role_option) {
                                                        case 'admin':
                                                            echo __("Administrator");
                                                            break;
                                                        case 'manager':
                                                            echo __("Manager");
                                                            break;
                                                        case 'resident':
                                                            echo __("Resident");
                                                            break;
                                                        default:
                                                            echo ucfirst($role_option);
                                                    }
                                                ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if ($current_role === 'manager'): ?>
                                    <input type="hidden" name="role" value="<?php echo $is_own_profile ? 'manager' : 'resident'; ?>">
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-group">
                                    <label for="status"><?php echo __("Status"); ?> <span class="required">*</span></label>
                                    <select id="status" name="status" required>
                                        <?php foreach ($statuses as $status): ?>
                                            <option value="<?php echo $status; ?>" <?php echo $user['status'] === $status ? 'selected' : ''; ?>>
                                                <?php 
                                                    switch($status) {
                                                        case 'active':
                                                            echo __("Active");
                                                            break;
                                                        case 'inactive':
                                                            echo __("Inactive");
                                                            break;
                                                        default:
                                                            echo ucfirst($status);
                                                    }
                                                ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <h4 class="form-section-title"><?php echo __("Change Password (leave empty to keep current password)"); ?></h4>
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="password"><?php echo __("New Password"); ?></label>
                                    <input type="password" id="password" name="password" minlength="8">
                                    <small><?php echo __("Minimum 8 characters"); ?></small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirm_password"><?php echo __("Confirm New Password"); ?></label>
                                    <input type="password" id="confirm_password" name="confirm_password">
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <a href="view-user.php?id=<?php echo $user_id; ?>" class="btn btn-secondary"><?php echo __("Cancel"); ?></a>
                                <button type="submit" class="btn btn-primary"><?php echo __("Update User"); ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="js/dark-mode.js"></script>
    <script>
        // Password confirmation validation
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        
        function validatePassword() {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity("<?php echo __("Passwords do not match"); ?>");
            } else {
                confirmPassword.setCustomValidity('');
            }
        }
        
        password.addEventListener('change', validatePassword);
        confirmPassword.addEventListener('keyup', validatePassword);
    </script>
</body>
</html> 
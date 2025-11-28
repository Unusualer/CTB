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
        $alt_translations_file = $_SERVER['DOCUMENT_ROOT'] . '/CTB/includes/translations.php';
        if (file_exists($alt_translations_file)) {
            require_once $alt_translations_file;
        } else {
            function __($text) {
                return $text;
            }
        }
    }
}

$current_role = getCurrentRole();


// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = __("Invalid request method.");
    header("Location: users.php");
    exit();
}

// Check if user_id is provided
if (!isset($_POST['user_id']) || empty($_POST['user_id'])) {
    $_SESSION['error'] = __("User ID is required.");
    header("Location: users.php");
    exit();
}

$user_id = (int)$_POST['user_id'];

// Prevent admin from deleting their own account
if ($user_id === (int)$_SESSION['user_id']) {
    $_SESSION['error'] = "Vous ne pouvez pas supprimer votre propre compte.";
    header("Location: users.php");
    exit();
}

try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // First check if user exists and get their info for the log
$check_stmt = $db->prepare("SELECT name, role FROM users WHERE id = :id");
    $check_stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $check_stmt->execute();
    
if ($check_stmt->rowCount() === 0) {
    $_SESSION['error'] = __("User not found.");
        header("Location: users.php");
        exit();
    }
    
    $user_info = $check_stmt->fetch(PDO::FETCH_ASSOC);

if ($current_role === 'manager' && $user_info['role'] !== 'resident') {
    $_SESSION['error'] = __("Managers can only delete resident accounts.");
    header("Location: users.php");
    exit();
}
    
    // Begin transaction
    $db->beginTransaction();

    // Update any properties owned by this user to have no owner
    $update_props = $db->prepare("UPDATE properties SET user_id = NULL WHERE user_id = :user_id");
    $update_props->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $update_props->execute();
    
    // Delete user from database
    $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    // Log the activity
    $admin_id = $_SESSION['user_id'];
    $log_stmt = $db->prepare("INSERT INTO activity_log (user_id, action, description) 
                            VALUES (:admin_id, 'delete', :description)");
    
    $description = __("Deleted user") . ": " . $user_info['name'] . " (ID: $user_id, " . __("Role") . ": " . $user_info['role'] . ")";
    $log_stmt->bindParam(':admin_id', $admin_id);
    $log_stmt->bindParam(':description', $description);
    $log_stmt->execute();
    
    // Commit transaction
    $db->commit();
    
    $_SESSION['success'] = __("User deleted successfully.");
    header("Location: users.php");
    exit();
    
} catch (PDOException $e) {
    // Rollback transaction on error
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: users.php");
    exit();
} 
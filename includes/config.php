<?php
/**
 * Database Configuration File
 * 
 * Contains constants and settings for database connection
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Set secure session parameters
    ini_set('session.cookie_httponly', 1);
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', 1);
    }
    session_name('ctb_session');
    session_start();
}

// Database connection constants
define('DB_HOST', 'localhost');
define('DB_NAME', 'ctb_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Error reporting and display settings
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Site URL configuration
define('BASE_URL', '/CTB/');
define('BASE_PATH', dirname(__DIR__) . '/');

// Set default timezone
date_default_timezone_set('America/New_York');

/**
 * PDO Database Connection
 * Use this if you need direct database access
 */
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
    $pdo = $conn; // Create an alias for backward compatibility
} catch (PDOException $e) {
    // Log the error but don't expose details
    error_log("Database Connection Error: " . $e->getMessage());
    
    // For development, you might want to see the error
    echo "Connection failed: " . $e->getMessage();
}

/**
 * Application settings
 */
// File upload settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB max file size
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);
define('UPLOAD_DIR', BASE_PATH . 'uploads/');

// Email settings
define('MAIL_FROM', 'noreply@ctbproperties.com');
define('MAIL_FROM_NAME', 'Complexe Tanger Boulevard');

// Application version
define('APP_VERSION', '1.0.0');

// Default pagination settings
define('DEFAULT_PAGE_LIMIT', 20);

// Security settings
define('PASSWORD_MIN_LENGTH', 8);

// System notifications
$system_notifications = [];

// Application constants
define('SITE_NAME', 'Complexe Tanger Boulevard');
define('EMAIL_FROM', 'noreply@ctb.com');
define('UPLOADS_DIR', 'uploads/');
define('ADMIN_ROLE', 'admin');
define('MANAGER_ROLE', 'manager');
define('RESIDENT_ROLE', 'resident');

// Helper functions
function redirect($url) {
    header("Location: $url");
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

function isAdmin() {
    return hasRole(ADMIN_ROLE);
}

function isManager() {
    return hasRole(MANAGER_ROLE);
}

function isResident() {
    return hasRole(RESIDENT_ROLE);
}

function displayAlert($message, $type = 'success') {
    return "<div class='alert alert-$type'>$message</div>";
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Set debug mode based on environment
$debug = true; // Set to false in production

if ($debug) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// Set global variables
$siteName = "Complexe Tanger Boulevard";
$siteUrl = "http://localhost/CTB";

// Global Functions
function requireRole($role) {
    // Check if session exists
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
        $_SESSION['error_message'] = "You must be logged in to access this page.";
        header("Location: ../login.php");
        exit;
    }
    
    // Check if user has the required role
    if ($_SESSION['user_role'] !== $role) {
        $_SESSION['error_message'] = "You don't have permission to access this page.";
        
        // Redirect based on user role
        if ($_SESSION['user_role'] === 'admin') {
            header("Location: ../admin/dashboard.php");
        } elseif ($_SESSION['user_role'] === 'manager') {
            header("Location: ../manager/dashboard.php");
        } elseif ($_SESSION['user_role'] === 'resident') {
            header("Location: ../resident/dashboard.php");
        } else {
            header("Location: ../login.php");
        }
        exit;
    }
}

// Format Currency
function formatCurrency($amount) {
    return '₩ ' . number_format($amount, 0);
}

// Generate random string
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

// Log activity
function logActivity($userId, $action, $entityType, $entityId, $description) {
    global $pdo;
    
    try {
        $query = "INSERT INTO activity_log (user_id, action, entity_type, entity_id, description, created_at) 
                 VALUES (:user_id, :action, :entity_type, :entity_id, :description, NOW())";
        
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':action', $action);
        $stmt->bindParam(':entity_type', $entityType);
        $stmt->bindParam(':entity_id', $entityId);
        $stmt->bindParam(':description', $description);
        $stmt->execute();
        
        return true;
    } catch (PDOException $e) {
        // Log the error but don't disturb user experience
        error_log("Activity log error: " . $e->getMessage());
        return false;
    }
}

// Function to redirect if not logged in
function requireLogin() {
    global $siteUrl;
    if (!isLoggedIn()) {
        $_SESSION['login_error'] = "Please log in to access that page.";
        header("Location: $siteUrl/login.php");
        exit();
    }
}

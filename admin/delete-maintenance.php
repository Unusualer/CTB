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

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request method.";
    header("Location: maintenance.php");
    exit();
}

// Check if maintenance_id is provided
if (!isset($_POST['maintenance_id']) || empty($_POST['maintenance_id'])) {
    $_SESSION['error'] = "Maintenance ID is required.";
    header("Location: maintenance.php");
    exit();
}

$maintenance_id = (int)$_POST['maintenance_id'];

try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // First check if maintenance update exists and get its info for the log
    $check_stmt = $db->prepare("SELECT title, location FROM maintenance WHERE id = :id");
    $check_stmt->bindParam(':id', $maintenance_id, PDO::PARAM_INT);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() === 0) {
        $_SESSION['error'] = "Maintenance update not found.";
        header("Location: maintenance.php");
        exit();
    }
    
    $maintenance_info = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Begin transaction
    $db->beginTransaction();
    
    // Delete maintenance update from database
    $stmt = $db->prepare("DELETE FROM maintenance WHERE id = :id");
    $stmt->bindParam(':id', $maintenance_id, PDO::PARAM_INT);
    $stmt->execute();
    
    // Log the activity
    $admin_id = $_SESSION['user_id'];
    $log_stmt = $db->prepare("INSERT INTO activity_log (user_id, action, entity_type, entity_id, description) 
                             VALUES (:admin_id, 'delete', 'maintenance', :entity_id, :description)");
    
    $description = "Deleted maintenance update: " . $maintenance_info['title'] . " (Location: " . $maintenance_info['location'] . ")";
    $log_stmt->bindParam(':admin_id', $admin_id);
    $log_stmt->bindParam(':entity_id', $maintenance_id, PDO::PARAM_INT);
    $log_stmt->bindParam(':description', $description);
    $log_stmt->execute();
    
    // Commit transaction
    $db->commit();
    
    $_SESSION['success'] = "Maintenance update deleted successfully.";
    header("Location: maintenance.php");
    exit();
    
} catch (PDOException $e) {
    // Rollback transaction on error
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    header("Location: maintenance.php");
    exit();
}
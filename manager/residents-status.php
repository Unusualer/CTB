<?php
// Start session
session_start();

// Include database configuration
require_once '../includes/config.php';

// Check if user is logged in and has manager role
requireRole('manager');

// Check if request has ID and action parameters
if (!isset($_GET['id']) || !isset($_GET['action'])) {
    $_SESSION['error_message'] = "Invalid request parameters.";
    header("Location: residents.php");
    exit;
}

$residentId = intval($_GET['id']);
$action = $_GET['action'];

// Validate action
if ($action !== 'activate' && $action !== 'deactivate') {
    $_SESSION['error_message'] = "Invalid action specified.";
    header("Location: residents.php");
    exit;
}

try {
    // Verify resident exists and is a resident
    $checkQuery = "SELECT id, name, status FROM users WHERE id = :id AND role = 'resident'";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bindParam(':id', $residentId, PDO::PARAM_INT);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() === 0) {
        $_SESSION['error_message'] = "Resident not found.";
        header("Location: residents.php");
        exit;
    }
    
    $resident = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    // Set new status based on action
    $newStatus = ($action === 'activate') ? 'active' : 'inactive';
    
    // Check if status is already as requested
    if ($resident['status'] === $newStatus) {
        $_SESSION['warning_message'] = "Resident is already " . ($newStatus === 'active' ? 'activated' : 'deactivated') . ".";
        header("Location: residents.php");
        exit;
    }
    
    // Update resident status
    $updateQuery = "UPDATE users SET status = :status, updated_at = NOW() WHERE id = :id";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bindParam(':status', $newStatus);
    $updateStmt->bindParam(':id', $residentId, PDO::PARAM_INT);
    $updateStmt->execute();
    
    // Log the action
    $logQuery = "INSERT INTO activity_log (user_id, action, entity_type, entity_id, details, created_at) 
                 VALUES (:user_id, :action, 'resident', :entity_id, :details, NOW())";
    
    $logStmt = $conn->prepare($logQuery);
    
    $actionType = ($action === 'activate') ? 'activated' : 'deactivated';
    $details = "Resident {$resident['name']} (ID: {$resident['id']}) was $actionType.";
    
    $logStmt->bindParam(':user_id', $_SESSION['user_id']);
    $logStmt->bindParam(':action', $actionType);
    $logStmt->bindParam(':entity_id', $residentId);
    $logStmt->bindParam(':details', $details);
    $logStmt->execute();
    
    // Set success message
    $_SESSION['success_message'] = "Resident " . ($action === 'activate' ? 'activated' : 'deactivated') . " successfully.";
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
}

// Redirect back to residents page
header("Location: residents.php");
exit; 
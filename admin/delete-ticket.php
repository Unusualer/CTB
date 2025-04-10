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
    header("Location: tickets.php");
    exit();
}

// Check if ticket_id is provided
if (!isset($_POST['ticket_id']) || empty($_POST['ticket_id'])) {
    $_SESSION['error'] = "Ticket ID is required.";
    header("Location: tickets.php");
    exit();
}

$ticket_id = (int)$_POST['ticket_id'];

try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // First check if ticket exists and get ticket info for the log
    $check_stmt = $db->prepare("SELECT subject, user_id FROM tickets WHERE id = :id");
    $check_stmt->bindParam(':id', $ticket_id, PDO::PARAM_INT);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() === 0) {
        $_SESSION['error'] = "Ticket not found.";
        header("Location: tickets.php");
        exit();
    }
    
    $ticket_info = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Begin transaction
    $db->beginTransaction();

    // Delete ticket from database
    $stmt = $db->prepare("DELETE FROM tickets WHERE id = :id");
    $stmt->bindParam(':id', $ticket_id, PDO::PARAM_INT);
    $stmt->execute();
    
    // Log the activity
    $admin_id = $_SESSION['user_id'];
    $description = "Deleted ticket: " . $ticket_info['subject'] . " (ID: $ticket_id, User ID: " . $ticket_info['user_id'] . ")";
    log_activity($db, $admin_id, 'delete', 'ticket', $ticket_id, $description);
    
    // Commit transaction
    $db->commit();
    
    $_SESSION['success'] = "Ticket deleted successfully.";
    header("Location: tickets.php");
    exit();
    
} catch (PDOException $e) {
    // Rollback transaction on error
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    header("Location: tickets.php");
    exit();
}
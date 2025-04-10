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
$status = trim($_POST['status'] ?? '');
$admin_notes = trim($_POST['admin_notes'] ?? '');

$valid_statuses = ['open', 'in_progress', 'closed', 'reopened'];

// Validate status
if (empty($status) || !in_array($status, $valid_statuses)) {
    $_SESSION['error'] = "Valid status is required.";
    header("Location: view-ticket.php?id=$ticket_id");
    exit();
}

try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if admin_notes column exists
    $check_column_sql = "SHOW COLUMNS FROM tickets LIKE 'admin_notes'";
    $check_stmt = $db->prepare($check_column_sql);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() === 0) {
        // Add admin_notes column if it doesn't exist
        $alter_sql = "ALTER TABLE tickets ADD COLUMN admin_notes TEXT DEFAULT NULL";
        $alter_stmt = $db->prepare($alter_sql);
        $alter_stmt->execute();
    }
    
    // First check if ticket exists and get current status
    $check_stmt = $db->prepare("SELECT status, subject FROM tickets WHERE id = :id");
    $check_stmt->bindParam(':id', $ticket_id, PDO::PARAM_INT);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() === 0) {
        $_SESSION['error'] = "Ticket not found.";
        header("Location: tickets.php");
        exit();
    }
    
    $ticket_info = $check_stmt->fetch(PDO::FETCH_ASSOC);
    $old_status = $ticket_info['status'];
    
    // Update ticket status
    $stmt = $db->prepare("UPDATE tickets SET status = :status, admin_notes = :admin_notes, updated_at = NOW() WHERE id = :id");
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':admin_notes', $admin_notes);
    $stmt->bindParam(':id', $ticket_id, PDO::PARAM_INT);
    $stmt->execute();
    
    // Log the activity
    $admin_id = $_SESSION['user_id'];
    $description = "Updated ticket #$ticket_id status from '$old_status' to '$status'";
    log_activity($db, $admin_id, 'update', 'ticket', $ticket_id, $description);
    
    $_SESSION['success'] = "Ticket status updated successfully.";
    header("Location: view-ticket.php?id=$ticket_id");
    exit();
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    header("Location: view-ticket.php?id=$ticket_id");
    exit();
} 
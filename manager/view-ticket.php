<?php
// Start session
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/role_access.php';

// Check if user is logged in and has appropriate role
requireRole('manager');


// Check if ticket ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID de ticket invalide.";
    header("Location: tickets.php");
    exit();
}

$ticket_id = (int)$_GET['id'];
$ticket = null;
$error_message = '';

// Get ticket data
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch ticket details with user information
    $stmt = $db->prepare("SELECT t.*, u.name AS username, u.email, u.id AS user_id FROM tickets t LEFT JOIN users u ON t.user_id = u.id WHERE t.id = ?");
    $stmt->execute([$ticket_id]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$ticket) {
        $_SESSION['error'] = "Ticket introuvable.";
        header("Location: tickets.php");
        exit();
    }
    
} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
}

// Function to get status class for styling
function getStatusBadgeClass($status) {
    switch($status) {
        case 'open':
            return 'danger';
        case 'in_progress':
            return 'warning';
        case 'closed':
            return 'success';
        case 'reopened':
            return 'primary';
        default:
            return 'secondary';
    }
}

// Format date helper function
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

// Handle ticket deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_ticket'])) {
    // Get ticket ID - either from URL or from form submission
    $delete_ticket_id = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : $ticket_id;
    
    try {
        $delete_stmt = $db->prepare("DELETE FROM tickets WHERE id = :id");
        $delete_stmt->bindParam(':id', $delete_ticket_id, PDO::PARAM_INT);
        
        if ($delete_stmt->execute()) {
            // Log the activity
            $admin_id = $_SESSION['user_id'];
            log_activity($db, $admin_id, 'delete', 'ticket', $delete_ticket_id, "Ticket #$delete_ticket_id supprimé");
            
            $_SESSION['success'] = "Le ticket a été supprimé avec succès.";
            header("Location: tickets.php");
            exit();
        }
    } catch (PDOException $e) {
        $error_message = "Failed to delete ticket: " . $e->getMessage();
    }
}

// Process status update if submitted via form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $response = $_POST['response'];
    $priority = isset($_POST['priority']) ? $_POST['priority'] : null;
    
    try {
        $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Update ticket status, priority and response
        $update_stmt = $db->prepare("UPDATE tickets SET status = :status, response = :response, 
                                    priority = :priority, updated_at = NOW() WHERE id = :id");
        $update_stmt->bindParam(':status', $new_status);
        $update_stmt->bindParam(':response', $response);
        $update_stmt->bindParam(':priority', $priority);
        $update_stmt->bindParam(':id', $ticket_id, PDO::PARAM_INT);
        $update_stmt->execute();
        
        // Log the activity
        $admin_id = $_SESSION['user_id'];
        log_activity($db, $admin_id, 'update', 'ticket', $ticket_id, "Ticket #$ticket_id mis à jour : Statut (" . ucfirst($new_status) . "), Priorité (" . ucfirst($priority) . ")");
        
        $_SESSION['success'] = "Le ticket a été mis à jour avec succès.";
        header("Location: view-ticket.php?id=$ticket_id");
        exit();
        
    } catch (PDOException $e) {
        $error_message = "Database error: " . $e->getMessage();
    }
}

// Get priority class for styling (from ticket-details.php)
function getPriorityClass($priority) {
    switch($priority) {
        case 'low':
            return 'success';
        case 'medium':
            return 'warning';
        case 'high':
            return 'danger';
        case 'critical':
            return 'danger';
        default:
            return 'primary';
    }
}

// Page title
$page_title = "Détails du Ticket #$ticket_id | Système de Gestion Immobilière";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Community Trust Bank</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin-style.css">
    <style>
        .ticket-details {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        
        @media (min-width: 768px) {
            .ticket-details {
                grid-template-columns: 2fr 1fr;
            }
        }
        
        .detail-group {
            margin-bottom: 1rem;
        }
        
        .detail-group .label {
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: var(--text-secondary);
        }
        
        .detail-group .value {
            font-size: 1rem;
        }
        
        .meta-info {
            color: var(--text-secondary);
            font-size: 0.875rem;
            border-top: 1px solid var(--border-color);
            padding-top: 1rem;
            margin-top: 1.5rem;
        }
        
        .description-box {
            background-color: var(--light-color);
            padding: 1.25rem;
            border-radius: 0.5rem;
            border: 1px solid var(--border-color);
            margin: 1rem 0;
            white-space: pre-line;
            line-height: 1.6;
        }
        
        .activity-timeline {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        
        .activity-item {
            display: flex;
            position: relative;
        }
        
        .activity-icon {
            width: 24px;
            height: 24px;
            margin-right: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .activity-icon i {
            font-size: 0.625rem;
            color: var(--primary-color);
        }
        
        .activity-content {
            flex: 1;
            background-color: var(--light-color);
            padding: 1rem;
            border-radius: 0.5rem;
            border: 1px solid var(--border-color);
        }
        
        .activity-text {
            margin: 0 0 0.5rem 0;
        }
        
        .activity-time {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin: 0;
        }
        
        .no-data {
            text-align: center;
            padding: 2rem;
            color: var(--text-secondary);
        }
        
        .no-data i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            display: block;
        }
        
        .card-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        [data-theme="dark"] .form-group label {
            color: #e0e0e0;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0.375rem;
            background-color: var(--light-color);
            color: var(--text-primary);
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            outline: 0;
            box-shadow: 0 0 0 0.25rem rgba(var(--primary-rgb), 0.25);
        }
        
        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }
        
        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
            padding-right: 2.5rem;
        }
        
        small {
            display: block;
            margin-top: 0.25rem;
            color: var(--text-secondary);
            font-size: 0.75rem;
        }
        
        /* Dark mode specific styles */
        [data-theme="dark"] .description-box,
        [data-theme="dark"] .activity-content {
            background-color: #2a2e35;
            border-color: #3f4756;
        }
        
        [data-theme="dark"] .form-control {
            background-color: #2a2e35;
            border-color: #3f4756;
            color: #e0e0e0;
        }
        
        [data-theme="dark"] .form-control:focus {
            border-color: var(--primary-color-light);
            box-shadow: 0 0 0 0.25rem rgba(var(--primary-rgb), 0.4);
        }
        
        [data-theme="dark"] select.form-control {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23adb5bd' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        }
        
        [data-theme="dark"] small {
            color: #a0a0a0;
        }
        
        [data-theme="dark"] .form-control::placeholder {
            color: #8e99ad;
        }
        
        [data-theme="dark"] .no-data {
            color: #8e99ad;
        }
        
        [data-theme="dark"] .activity-time {
            color: #8e99ad;
        }
        
        /* Status indicator */
        .status-indicator {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: 600;
        }
        
        .status-primary {
            background-color: rgba(var(--primary-rgb), 0.15);
            color: var(--primary-color);
        }
        
        .status-success {
            background-color: rgba(25, 135, 84, 0.15);
            color: #198754;
        }
        
        .status-warning {
            background-color: rgba(255, 193, 7, 0.15);
            color: #ffc107;
        }
        
        .status-danger {
            background-color: rgba(220, 53, 69, 0.15);
            color: #dc3545;
        }
        
        .status-info {
            background-color: rgba(13, 202, 240, 0.15);
            color: #0dcaf0;
        }
        
        .status-secondary {
            background-color: rgba(108, 117, 125, 0.15);
            color: #6c757d;
        }
        
        /* Dark mode status badges */
        [data-theme="dark"] .status-primary {
            background-color: rgba(var(--primary-rgb), 0.3);
            color: var(--primary-color-light);
        }
        
        [data-theme="dark"] .status-success {
            background-color: rgba(25, 135, 84, 0.3);
            color: #25c274;
        }
        
        [data-theme="dark"] .status-warning {
            background-color: rgba(255, 193, 7, 0.3);
            color: #ffda6a;
        }
        
        [data-theme="dark"] .status-danger {
            background-color: rgba(220, 53, 69, 0.3);
            color: #ff8085;
        }
        
        [data-theme="dark"] .status-info {
            background-color: rgba(13, 202, 240, 0.3);
            color: #6edbf7;
        }
        
        [data-theme="dark"] .status-secondary {
            background-color: rgba(108, 117, 125, 0.3);
            color: #a1a8ae;
        }
        
        /* Status badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            background-color: var(--secondary-bg);
            color: var(--text-primary);
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        
        .status-badge.active {
            background-color: rgba(25, 135, 84, 0.15);
            color: #198754;
        }
        
        .status-badge.inactive {
            background-color: rgba(220, 53, 69, 0.15);
            color: #dc3545;
        }
        
        .status-badge.open {
            background-color: rgba(220, 53, 69, 0.15);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }
        
        .status-badge.in_progress {
            background-color: rgba(255, 193, 7, 0.15);
            color: #ffc107;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }
        
        .status-badge.closed {
            background-color: rgba(25, 135, 84, 0.15);
            color: #198754;
            border: 1px solid rgba(25, 135, 84, 0.3);
        }
        
        .status-badge.reopened {
            background-color: rgba(var(--primary-rgb), 0.15);
            color: var(--primary-color);
            border: 1px solid rgba(var(--primary-rgb), 0.3);
        }
        
        /* Dark mode status badges */
        [data-theme="dark"] .status-badge {
            background-color: rgba(255, 255, 255, 0.05);
            color: #e0e0e0;
        }
        
        [data-theme="dark"] .status-badge.active {
            background-color: rgba(25, 135, 84, 0.25);
            color: #25c274;
            border: 1px solid rgba(25, 135, 84, 0.5);
        }
        
        [data-theme="dark"] .status-badge.inactive {
            background-color: rgba(220, 53, 69, 0.25);
            color: #ff8085;
            border: 1px solid rgba(220, 53, 69, 0.5);
        }
        
        [data-theme="dark"] .status-badge.open {
            background-color: rgba(220, 53, 69, 0.25);
            color: #ff8085;
            border: 1px solid rgba(220, 53, 69, 0.5);
        }
        
        [data-theme="dark"] .status-badge.in_progress {
            background-color: rgba(255, 193, 7, 0.25);
            color: #ffda6a;
            border: 1px solid rgba(255, 193, 7, 0.5);
        }
        
        [data-theme="dark"] .status-badge.closed {
            background-color: rgba(25, 135, 84, 0.25);
            color: #25c274;
            border: 1px solid rgba(25, 135, 84, 0.5);
        }
        
        [data-theme="dark"] .status-badge.reopened {
            background-color: rgba(var(--primary-rgb), 0.25);
            color: var(--primary-color-light);
            border: 1px solid rgba(var(--primary-rgb), 0.5);
        }
        
        /* Property Styling from view-user.php */
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
        
        /* Breadcrumb styles */
        .breadcrumb {
            display: flex;
            align-items: center;
            font-size: 0.875rem;
            color: var(--text-secondary);
        }
        
        .breadcrumb a {
            color: var(--text-primary);
            text-decoration: none;
            margin-right: 0.5rem;
            position: relative;
            padding-right: 1rem;
        }
        
        .breadcrumb a:after {
            content: '/';
            position: absolute;
            right: 0;
            color: var(--text-secondary);
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        
        .breadcrumb span {
            color: var(--text-primary);
        }
        
        .breadcrumb span::before {
            content: '';
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .actions {
            display: flex;
            gap: 0.5rem;
        }
        
        /* Additional dark mode styles */
        [data-theme="dark"] .breadcrumb {
            color: #b0b0b0;
        }
        
        [data-theme="dark"] .breadcrumb a {
            color: #ffffff;
        }
        
        /* Styles pour l'affichage des informations utilisateur */
        .user-info-display {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 5px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            flex-shrink: 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .user-avatar i {
            font-size: 1.2rem;
        }
        
        .user-details {
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .user-name {
            font-weight: 500;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-decoration: none;
        }
        
        .user-name:hover {
            color: var(--primary-color);
            text-decoration: underline;
        }
        
        .user-email {
            font-size: 0.8rem;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        /* Dark mode specific styles */
        [data-theme="dark"] .user-name {
            color: #f0f0f0;
        }
        
        [data-theme="dark"] .user-name:hover {
            color: var(--primary-color-light);
        }
        
        [data-theme="dark"] .user-email {
            color: #a0a0a0;
        }
        
        /* Fix for the small text in the update form */
        .form-group small {
            color: var(--text-secondary);
        }
        
        [data-theme="dark"] .form-group small {
            color: #a0a0a0;
        }
        
        .delete-btn {
            cursor: pointer;
        }
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .modal-content {
            background-color: var(--bg-color);
            margin: 15% auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 90%;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .modal-header h3 {
            margin: 0;
        }
        
        .close {
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .modal-footer {
            margin-top: 20px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        [data-theme="dark"] .modal-content {
            background-color: #2a2e35;
            border: 1px solid #3f4756;
        }
        
        /* Styles from view-user.php */
        .content-wrapper {
            margin-top: 1.5rem;
        }
        
        .profile-header {
            margin-bottom: 1.5rem;
        }
        
        .profile-header-content {
            display: flex;
            align-items: center;
            padding: 1.5rem;
        }
        
        .profile-avatar {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #4a80f0, #2c57b5);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1.5rem;
            color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .avatar-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: inherit;
        }
        
        .profile-avatar i {
            font-size: 36px; /* Icône plus grande */
        }
        
        .profile-details {
            flex: 1;
        }
        
        .profile-name-wrapper {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .profile-name-wrapper h2 {
            margin: 0;
            margin-right: 1rem;
            font-size: 1.5rem;
            color: var(--text-primary);
        }
        
        .user-id-badge {
            background-color: var(--secondary-bg);
            color: var(--text-secondary);
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .profile-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            color: var(--text-secondary);
            font-size: 0.875rem;
        }
        
        .user-role, .user-status, .user-joined {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .user-status i {
            font-size: 0.625rem;
        }
        
        .user-status.active {
            color: #198754;
        }
        
        .user-status.inactive {
            color: #dc3545;
        }
        
        .card-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        
        @media (min-width: 992px) {
            .card-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        @media (min-width: 576px) {
            .info-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
        
        .info-group {
            display: flex;
            flex-direction: column;
        }
        
        .info-group label {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .info-value {
            font-weight: 500;
            color: var(--text-primary);
        }
        
        /* New styles for compact user information */
        .card-column {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .mt-3 {
            margin-top: 1.5rem;
        }
        
        .compact-user-info {
            display: flex;
            flex-direction: column;
        }
        
        .user-info-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 10px;
        }
        
        .user-info-header h4 {
            margin: 0 0 5px 0;
            font-size: 1.1rem;
            color: var(--text-primary);
        }
        
        .user-meta {
            display: flex;
            flex-direction: column;
            gap: 5px;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }
        
        .user-meta span {
            display: flex;
            align-items: center;
            gap: 7px;
        }
        
        .user-meta i {
            width: 16px;
            color: var(--primary-color);
        }
        
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 0.25rem;
        }
        
        [data-theme="dark"] .user-info-header h4 {
            color: #f0f0f0;
        }
        
        [data-theme="dark"] .user-meta {
            color: #a0a0a0;
        }
        
        [data-theme="dark"] .user-meta i {
            color: var(--primary-color-light);
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
                    <a href="tickets.php">Tickets</a>
                    <span>Voir le Ticket</span>
                </div>
                <div class="actions">
                    <a href="edit-ticket.php?id=<?php echo $ticket['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Modifier le Ticket
                    </a>
                    <a href="javascript:void(0);" class="btn btn-danger delete-ticket" data-id="<?php echo $ticket['id']; ?>">
                        <i class="fas fa-trash-alt"></i> Supprimer le Ticket
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

            <?php if (isset($_SESSION['error']) || !empty($error_message)): ?>
                <div class="alert alert-danger">
                    <?php 
                        if (isset($_SESSION['error'])) {
                            echo $_SESSION['error']; 
                            unset($_SESSION['error']);
                        } else {
                            echo $error_message;
                        }
                    ?>
                </div>
            <?php endif; ?>

            <?php if ($ticket): ?>
                <div class="content-wrapper">
                    <div class="profile-header card">
                        <div class="profile-header-content">
                            <div class="profile-avatar">
                                <div class="avatar-placeholder">
                                    <i class="fas fa-ticket-alt"></i>
                                </div>
                            </div>
                            <div class="profile-details">
                                <div class="profile-name-wrapper">
                                    <h2><?php echo htmlspecialchars($ticket['subject']); ?></h2>
                                    <div class="user-id-badge">ID: <?php echo $ticket['id']; ?></div>
                                </div>
                                <div class="profile-meta">
                                    <span class="user-status <?php echo $ticket['status']; ?>">
                                        <i class="fas fa-circle"></i> <?php 
                                        $status_text = '';
                                        switch($ticket['status']) {
                                            case 'open':
                                                $status_text = 'Ouvert';
                                                break;
                                            case 'in_progress':
                                                $status_text = 'En cours';
                                                break;
                                            case 'closed':
                                                $status_text = 'Fermé';
                                                break;
                                            case 'reopened':
                                                $status_text = 'Réouvert';
                                                break;
                                            default:
                                                $status_text = ucfirst(str_replace('_', ' ', $ticket['status']));
                                        }
                                        echo $status_text;
                                        ?>
                                    </span>
                                    <?php if (isset($ticket['priority']) && !empty($ticket['priority'])): ?>
                                    <span class="status-badge <?php echo getPriorityClass($ticket['priority']); ?>">
                                        <?php 
                                        $priority_text = '';
                                        switch($ticket['priority']) {
                                            case 'low':
                                                $priority_text = 'Basse';
                                                break;
                                            case 'medium':
                                                $priority_text = 'Moyenne';
                                                break;
                                            case 'high':
                                                $priority_text = 'Haute';
                                                break;
                                            case 'urgent':
                                                $priority_text = 'Urgente';
                                                break;
                                            default:
                                                $priority_text = ucfirst($ticket['priority']);
                                        }
                                        echo $priority_text;
                                        ?>
                                    </span>
                                    <?php endif; ?>
                                    <?php if (isset($ticket['category']) && !empty($ticket['category'])): ?>
                                    <span class="status-badge <?php echo $ticket['category']; ?>">
                                        <?php echo ucfirst($ticket['category']); ?>
                                    </span>
                                    <?php endif; ?>
                                    <span class="user-joined"><i class="far fa-calendar-alt"></i> Créé <?php echo date('d M Y', strtotime($ticket['created_at'])); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-grid">
                        <!-- Ticket Information Card -->
                        <div class="card user-info-card">
                            <div class="card-header">
                                <h3><i class="fas fa-info-circle"></i> Informations du Ticket</h3>
                            </div>
                            <div class="card-body">
                                <div class="detail-group">
                                    <div class="label">Description</div>
                                    <div class="description-box">
                                        <?php echo nl2br(htmlspecialchars($ticket['description'])); ?>
                                    </div>
                                </div>
                                
                                <?php if (!empty($ticket['response'])): ?>
                                <div class="detail-group">
                                    <div class="label">Réponse</div>
                                    <div class="description-box">
                                        <?php echo nl2br(htmlspecialchars($ticket['response'])); ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <div class="info-grid">
                                    <div class="info-group">
                                        <label><i class="fas fa-user"></i> Soumis par:</label>
                                        <div class="user-info-display">
                                            <div class="user-avatar">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div class="user-details">
                                                <a href="view-user.php?id=<?php echo $ticket['user_id']; ?>" class="user-name">
                                                    <?php echo htmlspecialchars($ticket['username'] ?? 'Utilisateur inconnu'); ?>
                                                </a>
                                                <span class="user-email"><?php echo htmlspecialchars($ticket['email'] ?? 'Email inconnu'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="info-group">
                                        <label><i class="fas fa-check-circle"></i> Statut:</label>
                                        <span class="status-badge <?php echo $ticket['status']; ?>">
                                            <?php 
                                            $status_text = '';
                                            switch($ticket['status']) {
                                                case 'open':
                                                    $status_text = 'Ouvert';
                                                    break;
                                                case 'in_progress':
                                                    $status_text = 'En cours';
                                                    break;
                                                case 'closed':
                                                    $status_text = 'Fermé';
                                                    break;
                                                case 'reopened':
                                                    $status_text = 'Réouvert';
                                                    break;
                                                default:
                                                    $status_text = ucfirst(str_replace('_', ' ', $ticket['status']));
                                            }
                                            echo $status_text;
                                            ?>
                                        </span>
                                    </div>
                                    <?php if (isset($ticket['priority']) && !empty($ticket['priority'])): ?>
                                    <div class="info-group">
                                        <label><i class="fas fa-exclamation-triangle"></i> Priorité:</label>
                                        <span class="status-badge <?php echo getPriorityClass($ticket['priority']); ?>">
                                            <?php 
                                            $priority_text = '';
                                            switch($ticket['priority']) {
                                                case 'low':
                                                    $priority_text = 'Basse';
                                                    break;
                                                case 'medium':
                                                    $priority_text = 'Moyenne';
                                                    break;
                                                case 'high':
                                                    $priority_text = 'Haute';
                                                    break;
                                                case 'urgent':
                                                    $priority_text = 'Urgente';
                                                    break;
                                                default:
                                                    $priority_text = ucfirst($ticket['priority']);
                                            }
                                            echo $priority_text;
                                            ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (isset($ticket['category']) && !empty($ticket['category'])): ?>
                                    <div class="info-group">
                                        <label><i class="fas fa-tag"></i> Catégorie:</label>
                                        <span class="info-value"><?php echo htmlspecialchars($ticket['category']); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="info-group">
                                        <label><i class="fas fa-calendar-plus"></i> Créé le:</label>
                                        <span class="info-value"><?php echo date('d F Y', strtotime($ticket['created_at'])); ?></span>
                                    </div>
                                    <div class="info-group">
                                        <label><i class="fas fa-edit"></i> Dernière mise à jour:</label>
                                        <span class="info-value"><?php echo date('d F Y', strtotime($ticket['updated_at'])); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-column">
                            <!-- User Information Card - Compact version -->
                            <div class="card compact-user-card">
                                <div class="card-header">
                                    <h3><i class="fas fa-user"></i> Informations Utilisateur</h3>
                                </div>
                                <div class="card-body">
                                    <div class="compact-user-info">
                                        <div class="user-info-header">
                                            <div class="user-avatar">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div>
                                                <h4><?php echo htmlspecialchars($ticket['username'] ?? 'Utilisateur inconnu'); ?></h4>
                                                <div class="user-meta">
                                                    <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($ticket['email'] ?? 'Email inconnu'); ?></span>
                                                    <?php if (!empty($ticket['phone'])): ?>
                                                    <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($ticket['phone']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <a href="view-user.php?id=<?php echo $ticket['user_id']; ?>" class="btn btn-sm btn-primary mt-2">
                                            <i class="fas fa-eye"></i> Voir l'Utilisateur
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Update Status Card - Now placed below User Info -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h3><i class="fas fa-edit"></i> Mettre à Jour le Statut</h3>
                                </div>
                                <div class="card-body">
                                    <form action="view-ticket.php?id=<?php echo $ticket_id; ?>" method="POST" class="form">
                                        <input type="hidden" name="update_status" value="1">
                                        
                                        <div class="form-group">
                                            <label for="status">Statut:</label>
                                            <select id="status" name="status" class="form-control">
                                                <option value="open" <?php echo $ticket['status'] === 'open' ? 'selected' : ''; ?>>Ouvert</option>
                                                <option value="in_progress" <?php echo $ticket['status'] === 'in_progress' ? 'selected' : ''; ?>>En Cours</option>
                                                <option value="closed" <?php echo $ticket['status'] === 'closed' ? 'selected' : ''; ?>>Fermé</option>
                                                <option value="reopened" <?php echo $ticket['status'] === 'reopened' ? 'selected' : ''; ?>>Réouvert</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="priority">Priorité:</label>
                                            <select id="priority" name="priority" class="form-control">
                                                <option value="low" <?php echo isset($ticket['priority']) && $ticket['priority'] === 'low' ? 'selected' : ''; ?>>Basse</option>
                                                <option value="medium" <?php echo isset($ticket['priority']) && $ticket['priority'] === 'medium' ? 'selected' : ''; ?>>Moyenne</option>
                                                <option value="high" <?php echo isset($ticket['priority']) && $ticket['priority'] === 'high' ? 'selected' : ''; ?>>Haute</option>
                                                <option value="urgent" <?php echo isset($ticket['priority']) && $ticket['priority'] === 'urgent' ? 'selected' : ''; ?>>Urgente</option>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="response">Réponse:</label>
                                            <textarea id="response" name="response" class="form-control" rows="4"><?php echo isset($ticket['response']) ? htmlspecialchars($ticket['response']) : ''; ?></textarea>
                                            <small>Réponse au ticket (visible par l'utilisateur)</small>
                                        </div>
                                        
                                        <div class="form-actions">
                                            <button type="submit" class="btn btn-primary">Mettre à Jour le Statut</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirmer la Suppression</h3>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer ce ticket ? Cette action est irréversible.</p>
            </div>
            <div class="modal-footer">
                <form id="deleteForm" action="view-ticket.php?id=<?php echo $ticket_id; ?>" method="POST">
                    <input type="hidden" name="delete_ticket" value="1">
                    <button type="button" class="btn btn-secondary close-modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </form>
            </div>
        </div>
    </div>

    <script src="js/dark-mode.js"></script>
    <script>
        // Delete ticket modal functionality
        const modal = document.getElementById('deleteModal');
        const deleteButtons = document.querySelectorAll('.delete-ticket');
        const closeButtons = document.querySelectorAll('.close, .close-modal');
        const deleteForm = document.getElementById('deleteForm');
        const deleteTicketIdInput = document.getElementById('deleteTicketId');
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const ticketId = this.getAttribute('data-id');
                deleteTicketIdInput.value = ticketId;
                modal.style.display = 'block';
            });
        });
        
        closeButtons.forEach(button => {
            button.addEventListener('click', function() {
                modal.style.display = 'none';
            });
        });
        
        window.addEventListener('click', function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        });
    </script>
</body>
</html> 
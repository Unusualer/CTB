<?php
// Start session
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/role_access.php';
require_once '../includes/translations.php';

// Check if user is logged in and has appropriate role
requireAnyRole(['resident']);


// Check if ticket ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = __("Invalid ticket ID.");
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
        $_SESSION['error'] = __("Ticket not found.");
        header("Location: tickets.php");
        exit();
    }
    
} catch (PDOException $e) {
    $error_message = __("Database error:") . " " . $e->getMessage();
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
        log_activity($db, $admin_id, 'update', 'ticket', $ticket_id, __("Ticket #") . $ticket_id . " " . __("updated:") . " " . __("Status") . " (" . ucfirst($new_status) . "), " . __("Priority") . " (" . ucfirst($priority) . ")");
        
        $_SESSION['success'] = __("Ticket updated successfully.");
        header("Location: view-ticket.php?id=$ticket_id");
        exit();
        
    } catch (PDOException $e) {
        $error_message = __("Database error:") . " " . $e->getMessage();
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
$page_title = __("Ticket Details") . " #$ticket_id";
?>

<!DOCTYPE html>
<html lang="<?php echo substr($_SESSION['language'] ?? 'en_US', 0, 2); ?>">
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
                    <a href="tickets.php"><?php echo __("Tickets"); ?></a>
                    <span><?php echo __("View Ticket"); ?></span>
                </div>
                <div class="actions">
                    <a href="edit-ticket.php?id=<?php echo $ticket['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> <?php echo __("Edit Ticket"); ?>
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
                                    <div class="user-id-badge"><?php echo __("ID"); ?>: <?php echo $ticket['id']; ?></div>
                                </div>
                                <div class="profile-meta">
                                    <span class="user-status <?php echo $ticket['status']; ?>">
                                        <i class="fas fa-circle"></i> <?php 
                                        echo __($ticket['status']); ?>
                                    </span>
                                    <?php if (isset($ticket['priority']) && !empty($ticket['priority'])): ?>
                                    <span class="status-badge <?php echo getPriorityClass($ticket['priority']); ?>">
                                        <?php echo __($ticket['priority']); ?>
                                    </span>
                                    <?php endif; ?>
                                    <?php if (isset($ticket['category']) && !empty($ticket['category'])): ?>
                                    <span class="status-badge <?php echo $ticket['category']; ?>">
                                        <?php echo ucfirst($ticket['category']); ?>
                                    </span>
                                    <?php endif; ?>
                                    <span class="user-joined"><i class="far fa-calendar-alt"></i> <?php echo __("Created"); ?> <?php echo date('d M Y', strtotime($ticket['created_at'])); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-grid">
                        <!-- Ticket Information Card -->
                        <div class="card user-info-card">
                            <div class="card-header">
                                <h3><i class="fas fa-info-circle"></i> <?php echo __("Ticket Information"); ?></h3>
                            </div>
                            <div class="card-body">
                                <div class="detail-group">
                                    <div class="label"><?php echo __("Description"); ?></div>
                                    <div class="description-box">
                                        <?php echo nl2br(htmlspecialchars($ticket['description'])); ?>
                                    </div>
                                </div>
                                
                                <?php if (!empty($ticket['response'])): ?>
                                <div class="detail-group">
                                    <div class="label"><?php echo __("Response"); ?></div>
                                    <div class="description-box">
                                        <?php echo nl2br(htmlspecialchars($ticket['response'])); ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <div class="info-grid">
                                    <div class="info-group">
                                        <label><i class="fas fa-user"></i> <?php echo __("Submitted by"); ?>:</label>
                                        <div class="user-info-display">
                                            <div class="user-avatar">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div class="user-details">
                                                <a href="view-user.php?id=<?php echo $ticket['user_id']; ?>" class="user-name">
                                                    <?php echo htmlspecialchars($ticket['username'] ?? __("Unknown User")); ?>
                                                </a>
                                                <span class="user-email"><?php echo htmlspecialchars($ticket['email'] ?? __("Unknown Email")); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="info-group">
                                        <label><i class="fas fa-check-circle"></i> <?php echo __("Status"); ?>:</label>
                                        <span class="status-badge <?php echo $ticket['status']; ?>">
                                            <?php echo __($ticket['status']); ?>
                                        </span>
                                    </div>
                                    <?php if (isset($ticket['priority']) && !empty($ticket['priority'])): ?>
                                    <div class="info-group">
                                        <label><i class="fas fa-exclamation-triangle"></i> <?php echo __("Priority"); ?>:</label>
                                        <span class="status-badge <?php echo getPriorityClass($ticket['priority']); ?>">
                                            <?php echo __($ticket['priority']); ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (isset($ticket['category']) && !empty($ticket['category'])): ?>
                                    <div class="info-group">
                                        <label><i class="fas fa-tag"></i> <?php echo __("Category"); ?>:</label>
                                        <span class="info-value"><?php echo htmlspecialchars($ticket['category']); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="info-group">
                                        <label><i class="fas fa-calendar-plus"></i> <?php echo __("Created on"); ?>:</label>
                                        <span class="info-value"><?php echo date('d F Y', strtotime($ticket['created_at'])); ?></span>
                                    </div>
                                    <div class="info-group">
                                        <label><i class="fas fa-edit"></i> <?php echo __("Last updated"); ?>:</label>
                                        <span class="info-value"><?php echo date('d F Y', strtotime($ticket['updated_at'])); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-column">
                        <div class="card-column">
                            <!-- User Information Card - Compact version -->
                            <div class="card compact-user-card">
                                <div class="card-header">
                                    <h3><i class="fas fa-user"></i> <?php echo __("User Information"); ?></h3>
                                </div>
                                <div class="card-body">
                                    <div class="compact-user-info">
                                        <div class="user-info-header">
                                            <div class="user-avatar">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div>
                                                <h4><?php echo htmlspecialchars($ticket['username'] ?? __("Unknown User")); ?></h4>
                                                <div class="user-meta">
                                                    <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($ticket['email'] ?? __("Unknown Email")); ?></span>
                                                    <?php if (!empty($ticket['phone'])): ?>
                                                    <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($ticket['phone']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <a href="view-user.php?id=<?php echo $ticket['user_id']; ?>" class="btn btn-sm btn-primary mt-2">
                                            <i class="fas fa-eye"></i> <?php echo __("View User"); ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                                </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script src="js/dark-mode.js"></script>
</body>
</html> 
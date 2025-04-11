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

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Ticket ID is required.";
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
    $stmt = $db->prepare("SELECT t.*, u.name, u.email, u.phone
                          FROM tickets t 
                          JOIN users u ON t.user_id = u.id
                          WHERE t.id = :id");
    $stmt->bindParam(':id', $ticket_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        $_SESSION['error'] = "Ticket not found.";
        header("Location: tickets.php");
        exit();
    }
    
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get activity log for this ticket
    $log_stmt = $db->prepare("SELECT * FROM activity_log 
                             WHERE entity_type = 'ticket' AND entity_id = :ticket_id 
                             ORDER BY created_at DESC LIMIT 10");
    $log_stmt->bindParam(':ticket_id', $ticket_id, PDO::PARAM_INT);
    $log_stmt->execute();
    $activity_logs = $log_stmt->fetchAll(PDO::FETCH_ASSOC);
    
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
            log_activity($db, $admin_id, 'delete', 'ticket', $delete_ticket_id, "Deleted ticket #$delete_ticket_id");
            
            $_SESSION['success'] = "Ticket has been deleted successfully.";
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
    $admin_notes = isset($_POST['admin_notes']) ? $_POST['admin_notes'] : '';
    
    try {
        $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Update ticket status, response and admin notes
        $update_stmt = $db->prepare("UPDATE tickets SET status = :status, response = :response, 
                                    admin_notes = :admin_notes, updated_at = NOW() WHERE id = :id");
        $update_stmt->bindParam(':status', $new_status);
        $update_stmt->bindParam(':response', $response);
        $update_stmt->bindParam(':admin_notes', $admin_notes);
        $update_stmt->bindParam(':id', $ticket_id, PDO::PARAM_INT);
        $update_stmt->execute();
        
        // Log the activity
        $admin_id = $_SESSION['user_id'];
        log_activity($db, $admin_id, 'update', 'ticket', $ticket_id, "Updated ticket #$ticket_id status to: " . ucfirst($new_status));
        
        $_SESSION['success'] = "Ticket status updated successfully.";
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
$page_title = "View Ticket";
?>

<!DOCTYPE html>
<html lang="en">
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
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0.375rem;
            background-color: var(--light-color);
        }
        
        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }
        
        /* Dark mode specific styles */
        [data-theme="dark"] .description-box,
        [data-theme="dark"] .activity-content {
            background-color: #2a2e35;
            border-color: #3f4756;
        }
        
        [data-theme="dark"] .form-control {
            background-color: #2a2e35;
            color: #ffffff;
            border-color: #3f4756;
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
            width: 80px;
            height: 80px;
            margin-right: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .avatar-placeholder {
            width: 100%;
            height: 100%;
            background-color: var(--primary-color-light);
            color: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
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
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            background-color: var(--secondary-bg);
            color: var(--text-primary);
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: 600;
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
        }
        
        .status-badge.in_progress {
            background-color: rgba(255, 193, 7, 0.15);
            color: #ffc107;
        }
        
        .status-badge.closed {
            background-color: rgba(25, 135, 84, 0.15);
            color: #198754;
        }
        
        .status-badge.reopened {
            background-color: rgba(var(--primary-rgb), 0.15);
            color: var(--primary-color);
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
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        
        .breadcrumb span {
            margin-left: 0.5rem;
        }
        
        .breadcrumb span::before {
            content: '/';
            margin-right: 0.5rem;
            color: var(--text-secondary);
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
                    <span>View Ticket</span>
                </div>
                <div class="actions">
                    <a href="edit-ticket.php?id=<?php echo $ticket['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Ticket
                    </a>
                    <a href="javascript:void(0);" class="btn btn-danger delete-ticket" data-id="<?php echo $ticket['id']; ?>">
                        <i class="fas fa-trash-alt"></i> Delete Ticket
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
                                        <i class="fas fa-circle"></i> <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                                    </span>
                                    <?php if (isset($ticket['priority']) && !empty($ticket['priority'])): ?>
                                    <span class="status-badge <?php echo getPriorityClass($ticket['priority']); ?>">
                                        <?php echo ucfirst($ticket['priority']); ?>
                                    </span>
                                    <?php endif; ?>
                                    <?php if (isset($ticket['category']) && !empty($ticket['category'])): ?>
                                    <span class="status-badge <?php echo $ticket['category']; ?>">
                                        <?php echo ucfirst($ticket['category']); ?>
                                    </span>
                                    <?php endif; ?>
                                    <span class="user-joined"><i class="far fa-calendar-alt"></i> Created <?php echo date('M d, Y', strtotime($ticket['created_at'])); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-grid">
                        <!-- Ticket Information Card -->
                        <div class="card user-info-card">
                            <div class="card-header">
                                <h3><i class="fas fa-info-circle"></i> Ticket Information</h3>
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
                                    <div class="label">Response</div>
                                    <div class="description-box">
                                        <?php echo nl2br(htmlspecialchars($ticket['response'])); ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <div class="info-grid">
                                    <div class="info-group">
                                        <label><i class="fas fa-user"></i> Submitted By:</label>
                                        <span class="info-value">
                                            <a href="view-user.php?id=<?php echo $ticket['user_id']; ?>">
                                                <?php echo htmlspecialchars($ticket['name']); ?>
                                            </a>
                                        </span>
                                    </div>
                                    <div class="info-group">
                                        <label><i class="fas fa-check-circle"></i> Status:</label>
                                        <span class="status-badge <?php echo $ticket['status']; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                                        </span>
                                    </div>
                                    <?php if (isset($ticket['priority']) && !empty($ticket['priority'])): ?>
                                    <div class="info-group">
                                        <label><i class="fas fa-exclamation-triangle"></i> Priority:</label>
                                        <span class="status-badge <?php echo getPriorityClass($ticket['priority']); ?>">
                                            <?php echo ucfirst($ticket['priority']); ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (isset($ticket['category']) && !empty($ticket['category'])): ?>
                                    <div class="info-group">
                                        <label><i class="fas fa-tag"></i> Category:</label>
                                        <span class="info-value"><?php echo htmlspecialchars($ticket['category']); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="info-group">
                                        <label><i class="fas fa-calendar-plus"></i> Created:</label>
                                        <span class="info-value"><?php echo date('F d, Y', strtotime($ticket['created_at'])); ?></span>
                                    </div>
                                    <div class="info-group">
                                        <label><i class="fas fa-edit"></i> Last Updated:</label>
                                        <span class="info-value"><?php echo date('F d, Y', strtotime($ticket['updated_at'])); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- User Information Card -->
                        <div class="card property-assignments-card">
                            <div class="card-header">
                                <h3><i class="fas fa-user"></i> User Information</h3>
                            </div>
                            <div class="card-body">
                                <div class="property-list">
                                    <div class="property-item">
                                        <div class="property-icon">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="property-details">
                                            <h4><?php echo htmlspecialchars($ticket['name']); ?></h4>
                                            <div class="property-meta">
                                                <span class="property-id">ID: <?php echo $ticket['user_id']; ?></span>
                                                <a href="view-user.php?id=<?php echo $ticket['user_id']; ?>" class="view-link">
                                                    View User <i class="fas fa-arrow-right"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="property-item">
                                        <div class="property-icon">
                                            <i class="fas fa-envelope"></i>
                                        </div>
                                        <div class="property-details">
                                            <h4><?php echo htmlspecialchars($ticket['email']); ?></h4>
                                            <div class="property-meta">
                                                <span class="property-id">Email</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($ticket['phone'])): ?>
                                    <div class="property-item">
                                        <div class="property-icon">
                                            <i class="fas fa-phone"></i>
                                        </div>
                                        <div class="property-details">
                                            <h4><?php echo htmlspecialchars($ticket['phone']); ?></h4>
                                            <div class="property-meta">
                                                <span class="property-id">Phone</span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Activity Log Card -->
                        <div class="card activity-card">
                            <div class="card-header">
                                <h3><i class="fas fa-history"></i> Recent Activity</h3>
                            </div>
                            <div class="card-body">
                                <?php if (empty($activity_logs)): ?>
                                    <div class="no-data">
                                        <i class="far fa-clock"></i>
                                        <p>No recent activity found.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="activity-timeline">
                                        <?php foreach ($activity_logs as $log): ?>
                                            <div class="activity-item">
                                                <div class="activity-icon">
                                                    <i class="fas fa-circle"></i>
                                                </div>
                                                <div class="activity-content">
                                                    <p class="activity-text"><?php echo isset($log['description']) ? htmlspecialchars($log['description']) : 'No description available'; ?></p>
                                                    <p class="activity-time"><?php echo isset($log['created_at']) ? date('M d, Y H:i', strtotime($log['created_at'])) : 'Unknown date'; ?></p>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Update Status Card -->
                        <div class="card">
                            <div class="card-header">
                                <h3><i class="fas fa-edit"></i> Update Status</h3>
                            </div>
                            <div class="card-body">
                                <form action="view-ticket.php?id=<?php echo $ticket_id; ?>" method="POST" class="form">
                                    <input type="hidden" name="update_status" value="1">
                                    
                                    <div class="form-group">
                                        <label for="status">Status:</label>
                                        <select id="status" name="status" class="form-control">
                                            <option value="open" <?php echo $ticket['status'] === 'open' ? 'selected' : ''; ?>>Open</option>
                                            <option value="in_progress" <?php echo $ticket['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                            <option value="closed" <?php echo $ticket['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                                            <option value="reopened" <?php echo $ticket['status'] === 'reopened' ? 'selected' : ''; ?>>Reopened</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="response">Response:</label>
                                        <textarea id="response" name="response" class="form-control" rows="4"><?php echo isset($ticket['response']) ? htmlspecialchars($ticket['response']) : ''; ?></textarea>
                                        <small>Response to the ticket (visible to the user)</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="admin_notes">Admin Notes:</label>
                                        <textarea id="admin_notes" name="admin_notes" class="form-control" rows="3"><?php echo isset($ticket['admin_notes']) ? htmlspecialchars($ticket['admin_notes']) : ''; ?></textarea>
                                        <small>Internal notes (not visible to the user)</small>
                                    </div>
                                    
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary">Update Status</button>
                                    </div>
                                </form>
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
                <h3>Confirm Deletion</h3>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this ticket? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <form id="deleteForm" action="view-ticket.php?id=<?php echo $ticket_id; ?>" method="POST">
                    <input type="hidden" name="delete_ticket" value="1">
                    <button type="button" class="btn btn-secondary close-modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
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
<?php
// Start session
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/role_access.php';
require_once '../includes/translations.php';

// Check if user is logged in and has appropriate role
requireAnyRole(['resident']);


// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = __("Ticket ID is required.");
    header("Location: tickets.php");
    exit();
}

$ticket_id = (int)$_GET['id'];
$current_user_id = $_SESSION['user_id'];
$ticket = null;
$statuses = ['open', 'in_progress', 'closed', 'reopened'];
$priorities = ['low', 'medium', 'high', 'urgent'];
$users = [];

// Get ticket data first to check ownership
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $ticket_stmt = $db->prepare("SELECT * FROM tickets WHERE id = :id");
    $ticket_stmt->bindParam(':id', $ticket_id, PDO::PARAM_INT);
    $ticket_stmt->execute();
    
    if ($ticket_stmt->rowCount() === 0) {
        $_SESSION['error'] = __("Ticket not found.");
        header("Location: tickets.php");
        exit();
    }
    
    $ticket = $ticket_stmt->fetch(PDO::FETCH_ASSOC);
    
    // For residents, only allow editing their own tickets
    if (getCurrentRole() === 'resident' && $ticket['user_id'] != $current_user_id) {
        $_SESSION['error'] = __("You can only edit your own tickets.");
        header("Location: tickets.php");
        exit();
    }
    
    $users_stmt = $db->prepare("SELECT id, name, email FROM users ORDER BY name ASC");
    $users_stmt->execute();
    $users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = __("Database error:") . " " . $e->getMessage();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    $user_id = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    $subject = trim($_POST['subject'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = trim($_POST['status'] ?? '');
    $response = trim($_POST['response'] ?? '');
    
    $errors = [];
    
    // Validate user_id
    if (empty($user_id)) {
        $errors[] = __("User is required.");
    }
    
    // Validate subject
    if (empty($subject)) {
        $errors[] = __("Subject is required.");
    }
    
    // Validate description
    if (empty($description)) {
        $errors[] = __("Description is required.");
    }
    
    // For residents, preserve existing status (they can't change it) but allow priority update
    $current_role = getCurrentRole();
    if ($current_role === 'resident') {
        // Get existing ticket data to preserve status and user_id
        if (!isset($db)) {
            $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        $existing_stmt = $db->prepare("SELECT status, user_id FROM tickets WHERE id = :id");
        $existing_stmt->bindParam(':id', $ticket_id, PDO::PARAM_INT);
        $existing_stmt->execute();
        $existing_ticket = $existing_stmt->fetch(PDO::FETCH_ASSOC);
        $status = $existing_ticket['status'];
        $user_id = $existing_ticket['user_id']; // Preserve user_id too
        
        // Allow priority to be updated from POST
        $priority = trim($_POST['priority'] ?? 'medium');
        if (empty($priority) || !in_array($priority, $priorities)) {
            $errors[] = __("A valid priority is required.");
        }
    } else {
        // Validate status and priority for admin/manager
        if (empty($status) || !in_array($status, $statuses)) {
            $errors[] = __("A valid status is required.");
        }
        $priority = $_POST['priority'] ?? 'medium';
    }
    
    // If no errors, update ticket
    if (empty($errors)) {
        try {
            if (!isset($db)) {
                $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            
            // Get current status for activity log
            $current_status_stmt = $db->prepare("SELECT status FROM tickets WHERE id = :id");
            $current_status_stmt->bindParam(':id', $ticket_id, PDO::PARAM_INT);
            $current_status_stmt->execute();
            $current_status = $current_status_stmt->fetch(PDO::FETCH_ASSOC)['status'];
            
            // Update ticket - residents can update description, subject, and priority
            if ($current_role === 'resident') {
                $stmt = $db->prepare("UPDATE tickets SET subject = :subject, 
                                     description = :description, priority = :priority, updated_at = NOW() 
                                     WHERE id = :id");
                $stmt->bindParam(':subject', $subject);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':priority', $priority);
                $stmt->bindParam(':id', $ticket_id, PDO::PARAM_INT);
            } else {
                // Admin/manager can update all fields
                $stmt = $db->prepare("UPDATE tickets SET user_id = :user_id, subject = :subject, 
                                     description = :description, status = :status, priority = :priority,
                                     response = :response, updated_at = NOW() 
                                     WHERE id = :id");
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $stmt->bindParam(':subject', $subject);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':status', $status);
                $stmt->bindParam(':priority', $priority);
                $stmt->bindParam(':response', $response);
                $stmt->bindParam(':id', $ticket_id, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            
            // Log the activity - include status change in the description if it changed
            $admin_id = $_SESSION['user_id'];
            $log_description = __("Updated ticket") . " #$ticket_id: $subject";
            
            if ($current_status !== $status) {
                $log_description .= " (" . __("Status changed from") . " '" . __($current_status) . "' " . __("to") . " '" . __($status) . "')";
            }
            
            log_activity($db, $admin_id, 'update', 'ticket', $ticket_id, $log_description);
            
            $_SESSION['success'] = __("Ticket updated successfully.");
            header("Location: view-ticket.php?id=$ticket_id");
            exit();
            
        } catch (PDOException $e) {
            $_SESSION['error'] = __("Database error:") . " " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
}

// Get ticket data
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch ticket details
    $stmt = $db->prepare("SELECT * FROM tickets WHERE id = :id");
    $stmt->bindParam(':id', $ticket_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        $_SESSION['error'] = __("Ticket not found.");
        header("Location: tickets.php");
        exit();
    }
    
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $_SESSION['error'] = __("Database error:") . " " . $e->getMessage();
    header("Location: tickets.php");
    exit();
}

// Helper function to get status label
function getStatusLabel($status) {
    $labels = [
        'open' => 'Ouvert',
        'in_progress' => 'En cours',
        'closed' => 'Fermé',
        'reopened' => 'Réouvert'
    ];
    
    return $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
}

// Helper function to get priority label
function getPriorityLabel($priority) {
    $labels = [
        'low' => 'Basse',
        'medium' => 'Moyenne',
        'high' => 'Haute',
        'urgent' => 'Urgente'
    ];
    
    return $labels[$priority] ?? ucfirst($priority);
}

// Page title
$page_title = __("Edit Ticket");
?>

<!DOCTYPE html>
<html lang="<?php echo substr($_SESSION['language'] ?? 'en_US', 0, 2); ?>">
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
        
        input, select, textarea {
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background-color: var(--light-color);
            color: var(--text-primary);
            font-size: 0.95rem;
            transition: all 0.2s ease;
            width: 100%;
        }
        
        input:hover, select:hover, textarea:hover {
            border-color: var(--primary-color-light);
        }
        
        input:focus, select:focus, textarea:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.15);
            outline: none;
        }
        
        textarea {
            resize: vertical;
            min-height: 120px;
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
        [data-theme="dark"] select,
        [data-theme="dark"] textarea {
            background-color: #2a2e35 !important;
            color: #ffffff !important;
            border-color: #3f4756;
        }
        
        [data-theme="dark"] input:hover, 
        [data-theme="dark"] select:hover,
        [data-theme="dark"] textarea:hover {
            border-color: var(--primary-color-light);
        }
        
        [data-theme="dark"] input:focus, 
        [data-theme="dark"] select:focus,
        [data-theme="dark"] textarea:focus {
            border-color: var(--primary-color);
            background-color: #2d3239 !important;
        }
        
        [data-theme="dark"] input::placeholder,
        [data-theme="dark"] textarea::placeholder {
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
                    <a href="tickets.php"><?php echo __("Tickets"); ?></a>
                    <a href="view-ticket.php?id=<?php echo $ticket_id; ?>"><?php echo __("View Ticket"); ?></a>
                    <span><?php echo __("Edit Ticket"); ?></span>
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
                        <h3><i class="fas fa-edit"></i> <?php echo __("Edit Ticket"); ?> #<?php echo $ticket_id; ?></h3>
                    </div>
                    <div class="card-body">
                        <form action="edit-ticket.php?id=<?php echo $ticket_id; ?>" method="post">
                            <div class="form-section-title">
                                <i class="fas fa-info-circle"></i> <?php echo __("Ticket Information"); ?>
                            </div>
                            
                                <?php if (getCurrentRole() !== 'resident'): ?>
                                <div class="form-group">
                                <label for="user_id">
                                    <?php echo __("User"); ?> <span class="required">*</span>
                                </label>
                                <select name="user_id" id="user_id" required>
                                    <option value=""><?php echo __("Select a user"); ?></option>
                                        <?php foreach ($users as $user): ?>
                                        <option value="<?php echo $user['id']; ?>" <?php echo ($ticket['user_id'] == $user['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['email']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php else: ?>
                                <input type="hidden" name="user_id" value="<?php echo $ticket['user_id']; ?>">
                                <?php endif; ?>
                                
                                <div class="form-group">
                                <label for="subject">
                                    <?php echo __("Subject"); ?> <span class="required">*</span>
                                </label>
                                <input type="text" id="subject" name="subject" value="<?php echo htmlspecialchars($ticket['subject']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="description">
                                    <?php echo __("Description"); ?> <span class="required">*</span>
                                </label>
                                <textarea id="description" name="description" rows="6" required><?php echo htmlspecialchars($ticket['description']); ?></textarea>
                            </div>
                            
                            <?php if (getCurrentRole() !== 'resident'): ?>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="status">
                                        <?php echo __("Status"); ?> <span class="required">*</span>
                                    </label>
                                    <select name="status" id="status" required>
                                        <option value="open" <?php echo $ticket['status'] === 'open' ? 'selected' : ''; ?>><?php echo __("Open"); ?></option>
                                        <option value="in_progress" <?php echo $ticket['status'] === 'in_progress' ? 'selected' : ''; ?>><?php echo __("In Progress"); ?></option>
                                        <option value="closed" <?php echo $ticket['status'] === 'closed' ? 'selected' : ''; ?>><?php echo __("Closed"); ?></option>
                                        <option value="reopened" <?php echo $ticket['status'] === 'reopened' ? 'selected' : ''; ?>><?php echo __("Reopened"); ?></option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="priority">
                                        <?php echo __("Priority"); ?> <span class="required">*</span>
                                    </label>
                                    <select name="priority" id="priority" required>
                                        <option value="low" <?php echo $ticket['priority'] === 'low' ? 'selected' : ''; ?>><?php echo __("Low"); ?></option>
                                        <option value="medium" <?php echo $ticket['priority'] === 'medium' ? 'selected' : ''; ?>><?php echo __("Medium"); ?></option>
                                        <option value="high" <?php echo $ticket['priority'] === 'high' ? 'selected' : ''; ?>><?php echo __("High"); ?></option>
                                        <option value="urgent" <?php echo $ticket['priority'] === 'urgent' ? 'selected' : ''; ?>><?php echo __("Urgent"); ?></option>
                                    </select>
                                </div>
                            </div>
                            <?php else: ?>
                            <input type="hidden" name="status" value="<?php echo htmlspecialchars($ticket['status']); ?>">
                            <div class="form-group">
                                <label for="priority">
                                    <?php echo __("Priority"); ?> <span class="required">*</span>
                                </label>
                                <select name="priority" id="priority" required>
                                    <option value="low" <?php echo $ticket['priority'] === 'low' ? 'selected' : ''; ?>><?php echo __("Low"); ?></option>
                                    <option value="medium" <?php echo $ticket['priority'] === 'medium' ? 'selected' : ''; ?>><?php echo __("Medium"); ?></option>
                                    <option value="high" <?php echo $ticket['priority'] === 'high' ? 'selected' : ''; ?>><?php echo __("High"); ?></option>
                                    <option value="urgent" <?php echo $ticket['priority'] === 'urgent' ? 'selected' : ''; ?>><?php echo __("Urgent"); ?></option>
                                </select>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (getCurrentRole() !== 'resident'): ?>
                            <div class="form-group">
                                <label for="response">
                                    <?php echo __("Response"); ?>
                                </label>
                                <textarea id="response" name="response" rows="4"><?php echo htmlspecialchars($ticket['response'] ?? ''); ?></textarea>
                                <small><?php echo __("Your response will be visible to the ticket submitter."); ?></small>
                            </div>
                            <?php endif; ?>
                            
                            <div class="form-actions">
                                <a href="view-ticket.php?id=<?php echo $ticket_id; ?>" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> <?php echo __("Back"); ?>
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> <?php echo __("Save Changes"); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="js/dark-mode.js"></script>
</body>
</html> 
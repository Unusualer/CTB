<?php
// Start session
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/role_access.php';
require_once '../includes/translations.php';

// Check if user is logged in and has appropriate role
requireAnyRole(['resident']);


$current_user_id = $_SESSION['user_id'] ?? null;
$current_user_name = $_SESSION['name'] ?? __('Resident User');
$current_user_email = $_SESSION['email'] ?? '';
$priority_options = ['low', 'medium', 'high', 'urgent'];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    $user_id = (int)($current_user_id ?? 0);
    $subject = trim($_POST['subject'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = 'open';
    $priority = $_POST['priority'] ?? 'medium';
    
    $errors = [];
    
    if (empty($user_id)) {
        $errors[] = __("Unable to determine the current user. Please re-login.");
    }
    
    // Validate subject
    if (empty($subject)) {
        $errors[] = __("Subject is required.");
    }
    
    // Validate description
    if (empty($description)) {
        $errors[] = __("Description is required.");
    }
    
    if (!in_array($priority, $priority_options, true)) {
        $errors[] = __("A valid priority is required.");
    }
    
    // If no errors, add ticket
    if (empty($errors)) {
        try {
            $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Insert new ticket
            $stmt = $db->prepare("INSERT INTO tickets (user_id, subject, description, status, priority, created_at, updated_at) 
                                 VALUES (:user_id, :subject, :description, :status, :priority, NOW(), NOW())");
            
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':subject', $subject);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':priority', $priority);
            
            $stmt->execute();
            
            $ticket_id = $db->lastInsertId();
            
            // Log the activity
            $admin_id = $_SESSION['user_id'];
            log_activity($db, $admin_id, 'create', 'ticket', $ticket_id, __("Created new ticket:") . " $subject");
            
            $_SESSION['success'] = __("Ticket created successfully.");
            header("Location: tickets.php");
            exit();
            
        } catch (PDOException $e) {
            $_SESSION['error'] = __("Database error:") . " " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
}

// Page title
$page_title = __("Add Ticket");
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
        
        [data-theme="dark"] input::placeholder {
            color: #8e99ad;
        }
        
        [data-theme="dark"] .form-section-title {
            color: #ffffff;
            border-color: #3f4756;
        }
        
        [data-theme="dark"] .required {
            color: #ff7a8e;
        }
        
        [data-theme="dark"] small {
            color: #b0b0b0;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }
        
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.2);
        }
        
        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
            border: 1px solid rgba(40, 167, 69, 0.2);
        }
        
        [data-theme="dark"] .alert-danger {
            background-color: rgba(220, 53, 69, 0.15);
            color: #ff6b6b;
        }
        
        [data-theme="dark"] .alert-success {
            background-color: rgba(40, 167, 69, 0.15);
            color: #2ecc71;
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
                    <span><?php echo __("Add Ticket"); ?></span>
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
                        <h3><i class="fas fa-plus-circle"></i> <?php echo __("Add New Ticket"); ?></h3>
                    </div>
                    <div class="card-body">
                        <form action="add-ticket.php" method="post">
                            <div class="form-section-title">
                                <i class="fas fa-info-circle"></i> <?php echo __("Ticket Information"); ?>
                            </div>
                            
                            <div class="form-group">
                                <label><?php echo __("User"); ?></label>
                                <input type="text" value="<?php echo htmlspecialchars($current_user_name); ?><?php echo $current_user_email ? ' (' . htmlspecialchars($current_user_email) . ')' : ''; ?>" disabled>
                                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($current_user_id); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="subject">
                                    <?php echo __("Subject"); ?> <span class="required">*</span>
                                </label>
                                <input type="text" id="subject" name="subject" value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>" required>
                            </div>
                                
                                <div class="form-group">
                                <label for="description">
                                    <?php echo __("Description"); ?> <span class="required">*</span>
                                </label>
                                <textarea id="description" name="description" rows="6" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label><?php echo __("Status"); ?></label>
                                <input type="text" value="<?php echo __("Open"); ?>" disabled>
                                <input type="hidden" name="status" value="open">
                            </div>

                            <div class="form-group">
                                <label for="priority">
                                    <?php echo __("Priority"); ?> <span class="required">*</span>
                                </label>
                                <select name="priority" id="priority" required>
                                    <?php foreach ($priority_options as $option): ?>
                                        <option value="<?php echo $option; ?>" <?php echo (isset($_POST['priority']) ? $_POST['priority'] : 'medium') === $option ? 'selected' : ''; ?>>
                                            <?php echo __(ucfirst($option)); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-actions">
                                <a href="tickets.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> <?php echo __("Cancel"); ?>
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> <?php echo __("Create Ticket"); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="js/dark-mode.js"></script>
    
    <style>
        /* Breadcrumb styling for dark mode */
        [data-theme="dark"] .breadcrumb {
            color: #b0b0b0;
        }
        
        [data-theme="dark"] .breadcrumb a {
            color: #ffffff;
        }
    </style>
</body>
</html> 
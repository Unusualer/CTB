<?php
// Start session
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/role_access.php';
require_once '../includes/translations.php';

// Check if user is logged in and has appropriate role
requireAnyRole(['admin', 'manager']);


// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = __("Maintenance ID is required.");
    header("Location: maintenance.php");
    exit();
}

$maintenance_id = (int)$_GET['id'];
$maintenance = null;
$statuses = ['scheduled', 'in_progress', 'completed', 'delayed', 'cancelled'];
$priorities = ['low', 'medium', 'high', 'emergency'];
$users = [];

// Get all users for the dropdown
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $users_stmt = $db->prepare("SELECT id, name, email FROM users ORDER BY name ASC");
    $users_stmt->execute();
    $users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = __("Database error:") . " " . $e->getMessage();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $start_date = trim($_POST['start_date'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');
    $status = trim($_POST['status'] ?? '');
    $priority = trim($_POST['priority'] ?? '');
    
    $errors = [];
    
    // Validate title
    if (empty($title)) {
        $errors[] = __("Title is required.");
    }
    
    // Validate description
    if (empty($description)) {
        $errors[] = __("Description is required.");
    }
    
    // Validate location
    if (empty($location)) {
        $errors[] = __("Location is required.");
    }
    
    // Validate dates
    if (empty($start_date)) {
        $errors[] = __("Start date is required.");
    }
    
    if (empty($end_date)) {
        $errors[] = __("End date is required.");
    }
    
    if (!empty($start_date) && !empty($end_date) && strtotime($end_date) < strtotime($start_date)) {
        $errors[] = __("End date must be after start date.");
    }
    
    // Validate status
    if (empty($status) || !in_array($status, $statuses)) {
        $errors[] = __("A valid status is required.");
    }
    
    // Validate priority
    if (empty($priority) || !in_array($priority, $priorities)) {
        $errors[] = __("A valid priority is required.");
    }
    
    // If no errors, update maintenance update
    if (empty($errors)) {
        try {
            $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Update maintenance update
            $stmt = $db->prepare("UPDATE maintenance SET 
                                  title = :title, 
                                  description = :description, 
                                  location = :location, 
                                  start_date = :start_date, 
                                  end_date = :end_date, 
                                  status = :status, 
                                  priority = :priority, 
                                  updated_by = :updated_by, 
                                  updated_at = NOW() 
                                  WHERE id = :id");
            
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':start_date', $start_date);
            $stmt->bindParam(':end_date', $end_date);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':priority', $priority);
            $stmt->bindParam(':updated_by', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->bindParam(':id', $maintenance_id, PDO::PARAM_INT);
            
            $stmt->execute();
            
            // Log the activity
            $admin_id = $_SESSION['user_id'];
            log_activity($db, $admin_id, 'update', 'maintenance', $maintenance_id, "Updated maintenance record #$maintenance_id: $title");
            
            $_SESSION['success'] = __("Maintenance update edited successfully.");
            header("Location: view-maintenance.php?id=$maintenance_id");
            exit();
            
        } catch (PDOException $e) {
            $_SESSION['error'] = __("Database error:") . " " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
}

// Get maintenance data
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch maintenance details
    $stmt = $db->prepare("SELECT * FROM maintenance WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $maintenance_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        $_SESSION['error'] = __("Maintenance record not found.");
        header("Location: maintenance.php");
        exit();
    }
    
    $maintenance = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $_SESSION['error'] = __("Database error:") . " " . $e->getMessage();
    header("Location: maintenance.php");
    exit();
}

// Page title
$page_title = __("Edit Maintenance");

// Helper function to get priority label
function getPriorityLabel($priority) {
    return __($priority);
}

// Helper function to get status label
function getStatusLabel($status) {
    return __(str_replace('_', ' ', $status));
}
?>

<!DOCTYPE html>
<html lang="<?php echo isset($_SESSION['language']) ? substr($_SESSION['language'], 0, 2) : 'en'; ?>">
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
        
        .btn-outline {
            background-color: transparent;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }
        
        .btn-outline:hover {
            background-color: rgba(var(--primary-rgb), 0.08);
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
                    <a href="maintenance.php"><?php echo __("Maintenance"); ?></a>
                    <a href="view-maintenance.php?id=<?php echo $maintenance_id; ?>"><?php echo __("View Maintenance"); ?></a>
                    <span><?php echo __("Edit Maintenance"); ?></span>
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
                        <h3><i class="fas fa-edit"></i> <?php echo __("Edit Maintenance #"); ?><?php echo $maintenance_id; ?></h3>
                    </div>
                    <div class="card-body">
                        <form action="edit-maintenance.php?id=<?php echo $maintenance_id; ?>" method="POST" class="form">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="title"><?php echo __("Title"); ?> <span class="required">*</span></label>
                                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($maintenance['title']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="location"><?php echo __("Location"); ?> <span class="required">*</span></label>
                                    <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($maintenance['location']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="description"><?php echo __("Description"); ?> <span class="required">*</span></label>
                                <textarea id="description" name="description" rows="6" required><?php echo htmlspecialchars($maintenance['description']); ?></textarea>
                            </div>
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="start_date"><?php echo __("Start Date"); ?> <span class="required">*</span></label>
                                    <input type="date" id="start_date" name="start_date" value="<?php echo $maintenance['start_date']; ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="end_date"><?php echo __("End Date"); ?> <span class="required">*</span></label>
                                    <input type="date" id="end_date" name="end_date" value="<?php echo $maintenance['end_date']; ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="status"><?php echo __("Status"); ?> <span class="required">*</span></label>
                                    <select id="status" name="status" required>
                                        <?php foreach ($statuses as $status): ?>
                                            <option value="<?php echo $status; ?>" <?php echo $maintenance['status'] === $status ? 'selected' : ''; ?>>
                                                <?php echo __(ucfirst(str_replace('_', ' ', $status))); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="priority"><?php echo __("Priority"); ?> <span class="required">*</span></label>
                                    <select id="priority" name="priority" required>
                                        <?php foreach ($priorities as $priority): ?>
                                            <option value="<?php echo $priority; ?>" <?php echo $maintenance['priority'] === $priority ? 'selected' : ''; ?>>
                                                <?php echo __(ucfirst($priority)); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i>
                                    <?php echo __("Save Changes"); ?>
                                </button>
                                <a href="view-maintenance.php?id=<?php echo $maintenance_id; ?>" class="btn btn-outline">
                                    <i class="fas fa-times"></i>
                                    <?php echo __("Cancel"); ?>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="js/dark-mode.js"></script>
    <script>
        // Date validation
        document.getElementById('end_date').addEventListener('change', function() {
            const startDate = document.getElementById('start_date').value;
            if (startDate && this.value && new Date(this.value) < new Date(startDate)) {
                alert('End date cannot be before start date.');
                this.value = startDate;
            }
        });
        
        document.getElementById('start_date').addEventListener('change', function() {
            const endDate = document.getElementById('end_date').value;
            if (endDate && this.value && new Date(endDate) < new Date(this.value)) {
                document.getElementById('end_date').value = this.value;
            }
        });
    </script>
</body>
</html> 
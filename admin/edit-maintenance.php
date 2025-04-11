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
    $_SESSION['error'] = "Maintenance ID is required.";
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
    $_SESSION['error'] = "Database error: " . $e->getMessage();
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
        $errors[] = "Title is required.";
    }
    
    // Validate description
    if (empty($description)) {
        $errors[] = "Description is required.";
    }
    
    // Validate location
    if (empty($location)) {
        $errors[] = "Location is required.";
    }
    
    // Validate dates
    if (empty($start_date)) {
        $errors[] = "Start date is required.";
    }
    
    if (empty($end_date)) {
        $errors[] = "End date is required.";
    }
    
    if (!empty($start_date) && !empty($end_date) && strtotime($end_date) < strtotime($start_date)) {
        $errors[] = "End date must be after start date.";
    }
    
    // Validate status
    if (empty($status) || !in_array($status, $statuses)) {
        $errors[] = "Valid status is required.";
    }
    
    // Validate priority
    if (empty($priority) || !in_array($priority, $priorities)) {
        $errors[] = "Valid priority is required.";
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
            
            $_SESSION['success'] = "Maintenance update modified successfully.";
            header("Location: view-maintenance.php?id=$maintenance_id");
            exit();
            
        } catch (PDOException $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
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
        $_SESSION['error'] = "Maintenance record not found.";
        header("Location: maintenance.php");
        exit();
    }
    
    $maintenance = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    header("Location: maintenance.php");
    exit();
}

// Page title
$page_title = "Edit Maintenance";

// Helper function to get priority label
function getPriorityLabel($priority) {
    switch ($priority) {
        case 'low':
            return 'Low';
        case 'medium':
            return 'Medium';
        case 'high':
            return 'High';
        case 'emergency':
            return 'Emergency';
        default:
            return ucfirst($priority);
    }
}

// Helper function to get status label
function getStatusLabel($status) {
    return ucfirst(str_replace('_', ' ', $status));
}
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
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/admin-sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <div class="breadcrumb">
                    <a href="maintenance.php">Maintenance</a>
                    <a href="view-maintenance.php?id=<?php echo $maintenance_id; ?>">View Maintenance</a>
                    <span>Edit Maintenance</span>
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
                        <h3><i class="fas fa-edit"></i> Edit Maintenance Update #<?php echo $maintenance_id; ?></h3>
                    </div>
                    <div class="card-body">
                        <form action="edit-maintenance.php?id=<?php echo $maintenance_id; ?>" method="POST" class="form">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="title">Title <span class="required">*</span></label>
                                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($maintenance['title']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="location">Location <span class="required">*</span></label>
                                    <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($maintenance['location']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="start_date">Start Date <span class="required">*</span></label>
                                    <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($maintenance['start_date']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="end_date">End Date <span class="required">*</span></label>
                                    <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($maintenance['end_date']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="status">Status <span class="required">*</span></label>
                                    <select id="status" name="status" required>
                                        <?php foreach ($statuses as $status): ?>
                                            <option value="<?php echo $status; ?>" <?php echo $maintenance['status'] === $status ? 'selected' : ''; ?>>
                                                <?php echo getStatusLabel($status); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="priority">Priority <span class="required">*</span></label>
                                    <select id="priority" name="priority" required>
                                        <?php foreach ($priorities as $priority): ?>
                                            <option value="<?php echo $priority; ?>" <?php echo $maintenance['priority'] === $priority ? 'selected' : ''; ?>>
                                                <?php echo getPriorityLabel($priority); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Description <span class="required">*</span></label>
                                <textarea id="description" name="description" rows="6" required><?php echo htmlspecialchars($maintenance['description']); ?></textarea>
                            </div>
                            
                            <div class="form-actions">
                                <a href="view-maintenance.php?id=<?php echo $maintenance_id; ?>" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update Maintenance</button>
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
        document.addEventListener('DOMContentLoaded', function() {
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');
            
            // Validate end date is after start date
            function validateDates() {
                const startDate = new Date(startDateInput.value);
                const endDate = new Date(endDateInput.value);
                
                if (endDate < startDate) {
                    endDateInput.setCustomValidity('End date must be after start date');
                } else {
                    endDateInput.setCustomValidity('');
                }
            }
            
            startDateInput.addEventListener('change', validateDates);
            endDateInput.addEventListener('change', validateDates);
        });
    </script>
    
    <style>
        /* Form styling */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 24px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .required {
            color: #ff5c75;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            background-color: var(--light-color);
            color: var(--text-primary);
        }
        
        textarea {
            resize: vertical;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 20px;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
            border: none;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-color-dark);
        }
        
        .btn-secondary {
            background-color: var(--secondary-bg);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            text-decoration: none;
        }
        
        .btn-secondary:hover {
            background-color: var(--border-color);
        }
        
        /* Dark mode specific styles */
        [data-theme="dark"] .breadcrumb {
            color: #b0b0b0;
        }
        
        [data-theme="dark"] .breadcrumb a {
            color: #ffffff;
        }
        
        [data-theme="dark"] input,
        [data-theme="dark"] select,
        [data-theme="dark"] textarea {
            background-color: #2a2e35;
            color: #ffffff;
            border-color: #3f4756;
        }
    </style>
</body>
</html> 
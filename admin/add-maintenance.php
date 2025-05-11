<?php
// Start session
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/role_access.php';
require_once '../includes/translations.php';

// Check if user is logged in and has appropriate role
requireRole('admin');


// Initialize variables
$success_message = '';
$error_message = '';
$maintenance = [
    'title' => '',
    'description' => '',
    'location' => '',
    'start_date' => date('Y-m-d'),
    'end_date' => date('Y-m-d', strtotime('+1 day')),
    'status' => 'scheduled',
    'priority' => 'medium'
];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Validate and sanitize input
        $title = !empty($_POST['title']) ? trim($_POST['title']) : '';
        $description = !empty($_POST['description']) ? trim($_POST['description']) : '';
        $location = !empty($_POST['location']) ? trim($_POST['location']) : '';
        $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-d');
        $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : date('Y-m-d', strtotime('+1 day'));
        $status = !empty($_POST['status']) ? $_POST['status'] : 'scheduled';
        $priority = !empty($_POST['priority']) ? $_POST['priority'] : 'medium';
        
        // Validation
        if (empty($title) || empty($description) || empty($location)) {
            throw new Exception(__("Please fill in all required fields: title, description, and location."));
        }
        
        if (strtotime($end_date) < strtotime($start_date)) {
            throw new Exception(__("End date cannot be earlier than start date."));
        }
        
        // Insert maintenance update
        $query = "INSERT INTO maintenance (
                    title, 
                    description, 
                    location, 
                    start_date, 
                    end_date, 
                    status, 
                    priority, 
                    created_by, 
                    created_at
                ) VALUES (
                    :title, 
                    :description, 
                    :location, 
                    :start_date, 
                    :end_date, 
                    :status, 
                    :priority, 
                    :created_by, 
                    NOW()
                )";
                
        $stmt = $db->prepare($query);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':location', $location);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':priority', $priority);
        $stmt->bindParam(':created_by', $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $maintenance_id = $db->lastInsertId();
            
            // Log activity
            logActivity($db, $_SESSION['user_id'], 'create', 'maintenance', $maintenance_id, "Added new maintenance update: $title");
            
            $success_message = __("Maintenance update added successfully!");
            
            // Clear form data
            $maintenance = [
                'title' => '',
                'description' => '',
                'location' => '',
                'start_date' => date('Y-m-d'),
                'end_date' => date('Y-m-d', strtotime('+1 day')),
                'status' => 'scheduled',
                'priority' => 'medium'
            ];
        } else {
            throw new Exception(__("Failed to add maintenance update. Please try again."));
        }
        
    } catch (PDOException $e) {
        $_SESSION['error'] = __("Database error:") . " " . $e->getMessage();
        
        // Preserve form data
        $maintenance = [
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? '',
            'location' => $_POST['location'] ?? '',
            'start_date' => $_POST['start_date'] ?? date('Y-m-d'),
            'end_date' => $_POST['end_date'] ?? date('Y-m-d', strtotime('+1 day')),
            'status' => $_POST['status'] ?? 'scheduled',
            'priority' => $_POST['priority'] ?? 'medium'
        ];
    } catch (Exception $e) {
        $error_message = $e->getMessage();
        
        // Preserve form data
        $maintenance = [
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? '',
            'location' => $_POST['location'] ?? '',
            'start_date' => $_POST['start_date'] ?? date('Y-m-d'),
            'end_date' => $_POST['end_date'] ?? date('Y-m-d', strtotime('+1 day')),
            'status' => $_POST['status'] ?? 'scheduled',
            'priority' => $_POST['priority'] ?? 'medium'
        ];
    }
}

$page_title = __("Add Maintenance");
?>
<!DOCTYPE html>
<html lang="<?php echo isset($_SESSION['language']) ? substr($_SESSION['language'], 0, 2) : 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo __("Community Trust Bank"); ?></title>
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
        
        .text-danger {
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
        
        .form-row {
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
        
        .btn-outline {
            background-color: transparent;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }
        
        .btn-outline:hover {
            background-color: rgba(var(--primary-rgb), 0.08);
            transform: translateY(-1px);
        }
        
        small.form-text {
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
        
        [data-theme="dark"] .text-danger {
            color: #ff7a8e;
        }
        
        [data-theme="dark"] small.form-text {
            color: #b0b0b0;
        }
        
        [data-theme="dark"] .breadcrumb {
            color: #b0b0b0;
        }
        
        [data-theme="dark"] .breadcrumb a {
            color: #ffffff;
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
                <div class="breadcrumb">
                <a href="maintenance.php"><?php echo __("Maintenance"); ?></a>
                <span><?php echo __("Add Maintenance Update"); ?></span>
            </div>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo $error_message; ?>
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
                        <h3><i class="fas fa-plus-circle"></i> <?php echo __("Add Maintenance Update"); ?></h3>
                </div>
                <div class="card-body">
                        <form action="add-maintenance.php" method="POST" class="form">
                            <div class="form-section-title"><?php echo __("General Information"); ?></div>
                        <div class="form-row">
                            <div class="form-group">
                                    <label for="title"><?php echo __("Title"); ?> <span class="text-danger">*</span></label>
                                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($maintenance['title']); ?>" placeholder="e.g. Elevator Maintenance" required>
                                    <small class="form-text"><?php echo __("Required field"); ?></small>
                                </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                    <label for="location"><?php echo __("Location"); ?> <span class="text-danger">*</span></label>
                                    <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($maintenance['location']); ?>" placeholder="e.g. Building A, Floor 3" required>
                                    <small class="form-text"><?php echo __("Required field"); ?></small>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                    <label for="description"><?php echo __("Description"); ?> <span class="text-danger">*</span></label>
                                    <textarea id="description" name="description" rows="6" placeholder="Provide detailed information about the maintenance update..." required><?php echo htmlspecialchars($maintenance['description']); ?></textarea>
                                    <small class="form-text"><?php echo __("Required field"); ?></small>
                            </div>
                        </div>
                        
                            <div class="form-section-title"><?php echo __("Schedule Information"); ?></div>
                        <div class="form-row">
                            <div class="form-group">
                                    <label for="start_date"><?php echo __("Start Date"); ?> <span class="text-danger">*</span></label>
                                    <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($maintenance['start_date']); ?>" required>
                                </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                    <label for="end_date"><?php echo __("End Date"); ?> <span class="text-danger">*</span></label>
                                    <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($maintenance['end_date']); ?>" required>
                                </div>
                        </div>
                        
                            <div class="form-section-title"><?php echo __("Details"); ?></div>
                        <div class="form-row">
                            <div class="form-group">
                                    <label for="status"><?php echo __("Status"); ?> <span class="text-danger">*</span></label>
                                    <select id="status" name="status" required>
                                        <option value="scheduled" <?php echo $maintenance['status'] === 'scheduled' ? 'selected' : ''; ?>><?php echo __("Scheduled"); ?></option>
                                        <option value="in_progress" <?php echo $maintenance['status'] === 'in_progress' ? 'selected' : ''; ?>><?php echo __("In Progress"); ?></option>
                                        <option value="completed" <?php echo $maintenance['status'] === 'completed' ? 'selected' : ''; ?>><?php echo __("Completed"); ?></option>
                                        <option value="delayed" <?php echo $maintenance['status'] === 'delayed' ? 'selected' : ''; ?>><?php echo __("Delayed"); ?></option>
                                        <option value="cancelled" <?php echo $maintenance['status'] === 'cancelled' ? 'selected' : ''; ?>><?php echo __("Cancelled"); ?></option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                    <label for="priority"><?php echo __("Priority"); ?> <span class="text-danger">*</span></label>
                                    <select id="priority" name="priority" required>
                                        <option value="low" <?php echo $maintenance['priority'] === 'low' ? 'selected' : ''; ?>><?php echo __("Low"); ?></option>
                                        <option value="medium" <?php echo $maintenance['priority'] === 'medium' ? 'selected' : ''; ?>><?php echo __("Medium"); ?></option>
                                        <option value="high" <?php echo $maintenance['priority'] === 'high' ? 'selected' : ''; ?>><?php echo __("High"); ?></option>
                                        <option value="emergency" <?php echo $maintenance['priority'] === 'emergency' ? 'selected' : ''; ?>><?php echo __("Emergency"); ?></option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                                <a href="maintenance.php" class="btn btn-outline">
                                    <i class="fas fa-times"></i>
                                    <?php echo __("Cancel"); ?>
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus"></i>
                                    <?php echo __("Create Maintenance"); ?>
                                </button>
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
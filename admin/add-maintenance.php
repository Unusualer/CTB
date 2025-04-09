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
            throw new Exception("Please fill in all required fields: title, description, and location.");
        }
        
        if (strtotime($end_date) < strtotime($start_date)) {
            throw new Exception("End date cannot be earlier than start date.");
        }
        
        // Insert maintenance update
        $query = "INSERT INTO maintenance_updates (
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
            
            $success_message = "Maintenance update added successfully!";
            
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
            throw new Exception("Failed to add maintenance update. Please try again.");
        }
        
    } catch (PDOException $e) {
        $error_message = "Database error: " . $e->getMessage();
        
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

$page_title = "Add Maintenance Update";
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
        .form-row {
            margin-bottom: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            background-color: var(--light-color);
            color: var(--text-primary);
            font-family: inherit;
        }
        
        textarea.form-control {
            min-height: 150px;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
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
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../assets/images/logo.png" alt="CTB Logo" class="logo">
                <h2>CTB Admin</h2>
            </div>
            
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="user-details">
                    <h4><?php echo htmlspecialchars($_SESSION['name']); ?></h4>
                    <p>Administrator</p>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <a href="dashboard.php">
                            <i class="fas fa-chart-line"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="users.php">
                            <i class="fas fa-users"></i>
                            <span>Users</span>
                        </a>
                    </li>
                    <li>
                        <a href="properties.php">
                            <i class="fas fa-building"></i>
                            <span>Properties</span>
                        </a>
                    </li>
                    <li>
                        <a href="tickets.php">
                            <i class="fas fa-ticket-alt"></i>
                            <span>Tickets</span>
                        </a>
                    </li>
                    <li>
                        <a href="payments.php">
                            <i class="fas fa-credit-card"></i>
                            <span>Payments</span>
                        </a>
                    </li>
                    <li>
                        <a href="activity-log.php">
                            <i class="fas fa-history"></i>
                            <span>Activity Log</span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="maintenance-new.php">
                            <i class="fas fa-tools"></i>
                            <span>Maintenance</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <div class="theme-toggle">
                    <i class="fas fa-moon"></i>
                    <label class="switch">
                        <input type="checkbox" id="darkModeToggle">
                        <span class="slider round"></span>
                    </label>
                </div>
                <a href="../logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="topbar">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search..." disabled>
                </div>
                
                <div class="topbar-right">
                    <div class="notifications">
                        <i class="fas fa-bell"></i>
                        <span class="badge">3</span>
                    </div>
                    <div class="messages">
                        <i class="fas fa-envelope"></i>
                        <span class="badge">5</span>
                    </div>
                </div>
            </header>

            <div class="content-header">
                <a href="maintenance-new.php" class="back-button">
                    <i class="fas fa-arrow-left"></i> Back to Maintenance Updates
                </a>
                <h1><?php echo $page_title; ?></h1>
            </div>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3>Maintenance Details</h3>
                </div>
                <div class="card-body">
                    <form action="add-maintenance.php" method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="title">Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" id="title" class="form-control" value="<?php echo htmlspecialchars($maintenance['title']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="location">Location <span class="text-danger">*</span></label>
                                <input type="text" name="location" id="location" class="form-control" value="<?php echo htmlspecialchars($maintenance['location']); ?>" required>
                                <small class="form-text text-muted">Specify where in the residential complex the maintenance will occur.</small>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="description">Description <span class="text-danger">*</span></label>
                                <textarea name="description" id="description" class="form-control" required><?php echo htmlspecialchars($maintenance['description']); ?></textarea>
                                <small class="form-text text-muted">Provide detailed information about the maintenance work being performed.</small>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="start_date">Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" id="start_date" class="form-control" value="<?php echo htmlspecialchars($maintenance['start_date']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="end_date">End Date <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" id="end_date" class="form-control" value="<?php echo htmlspecialchars($maintenance['end_date']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="status">Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-control" required>
                                    <option value="scheduled" <?php echo $maintenance['status'] === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                                    <option value="in_progress" <?php echo $maintenance['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="completed" <?php echo $maintenance['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="delayed" <?php echo $maintenance['status'] === 'delayed' ? 'selected' : ''; ?>>Delayed</option>
                                    <option value="cancelled" <?php echo $maintenance['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="priority">Priority <span class="text-danger">*</span></label>
                                <select name="priority" id="priority" class="form-control" required>
                                    <option value="low" <?php echo $maintenance['priority'] === 'low' ? 'selected' : ''; ?>>Low</option>
                                    <option value="medium" <?php echo $maintenance['priority'] === 'medium' ? 'selected' : ''; ?>>Medium</option>
                                    <option value="high" <?php echo $maintenance['priority'] === 'high' ? 'selected' : ''; ?>>High</option>
                                    <option value="emergency" <?php echo $maintenance['priority'] === 'emergency' ? 'selected' : ''; ?>>Emergency</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <a href="maintenance-new.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Add Maintenance Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Dark mode toggle
        const darkModeToggle = document.getElementById('darkModeToggle');
        
        // Check for saved dark mode preference
        if (localStorage.getItem('darkMode') === 'enabled') {
            document.body.classList.add('dark-mode');
            darkModeToggle.checked = true;
        }
        
        // Dark mode toggle event listener
        darkModeToggle.addEventListener('change', function() {
            if (this.checked) {
                document.body.classList.add('dark-mode');
                localStorage.setItem('darkMode', 'enabled');
            } else {
                document.body.classList.remove('dark-mode');
                localStorage.setItem('darkMode', null);
            }
        });
        
        // Date validation
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        
        startDateInput.addEventListener('change', function() {
            if (endDateInput.value && new Date(this.value) > new Date(endDateInput.value)) {
                endDateInput.value = this.value;
            }
        });
        
        endDateInput.addEventListener('change', function() {
            if (new Date(this.value) < new Date(startDateInput.value)) {
                this.setCustomValidity('End date cannot be earlier than start date');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html> 
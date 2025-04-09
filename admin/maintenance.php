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

// Handle form submission for new maintenance update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Get form data
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $start_date = trim($_POST['start_date']);
        $end_date = trim($_POST['end_date']);
        $status = trim($_POST['status']);
        $affected_areas = trim($_POST['affected_areas']);
        
        // Validate inputs
        if (empty($title)) {
            throw new Exception("Title cannot be empty");
        }
        
        if (empty($description)) {
            throw new Exception("Description cannot be empty");
        }
        
        if (empty($start_date)) {
            throw new Exception("Start date cannot be empty");
        }
        
        // Insert new maintenance record
        $stmt = $db->prepare("
            INSERT INTO maintenance_updates 
            (title, description, start_date, end_date, status, affected_areas, created_by, created_at) 
            VALUES 
            (:title, :description, :start_date, :end_date, :status, :affected_areas, :created_by, NOW())
        ");
        
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':start_date' => $start_date,
            ':end_date' => $end_date,
            ':status' => $status,
            ':affected_areas' => $affected_areas,
            ':created_by' => $_SESSION['user_id']
        ]);
        
        // Log the activity
        logActivity($db, $_SESSION['user_id'], 'create', 'maintenance_update', $db->lastInsertId(), "Added new maintenance update: $title");
        
        $success_message = "Maintenance update added successfully";
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Get maintenance updates from database
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if maintenance_updates table exists, if not create it
    $db->query("
        CREATE TABLE IF NOT EXISTS maintenance_updates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE,
            status ENUM('scheduled', 'in-progress', 'completed', 'postponed', 'cancelled') NOT NULL DEFAULT 'scheduled',
            affected_areas TEXT,
            created_by INT NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            FOREIGN KEY (created_by) REFERENCES users(id)
        )
    ");
    
    // Fetch maintenance updates
    $stmt = $db->query("
        SELECT m.*, u.name as admin_name 
        FROM maintenance_updates m
        JOIN users u ON m.created_by = u.id
        ORDER BY 
            CASE 
                WHEN m.status = 'in-progress' THEN 1
                WHEN m.status = 'scheduled' THEN 2
                WHEN m.status = 'postponed' THEN 3
                WHEN m.status = 'completed' THEN 4
                WHEN m.status = 'cancelled' THEN 5
            END,
            m.start_date DESC
    ");
    
    $maintenance_updates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
    $maintenance_updates = [];
}

// Page title
$page_title = "Maintenance Updates";
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
        .maintenance-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .maintenance-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .maintenance-title {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-scheduled {
            background-color: #e0f7fa;
            color: #0288d1;
        }
        
        .status-in-progress {
            background-color: #fff8e1;
            color: #ffa000;
        }
        
        .status-completed {
            background-color: #e8f5e9;
            color: #43a047;
        }
        
        .status-postponed {
            background-color: #f3e5f5;
            color: #8e24aa;
        }
        
        .status-cancelled {
            background-color: #ffebee;
            color: #e53935;
        }
        
        .maintenance-dates {
            display: flex;
            gap: 15px;
            margin-bottom: 10px;
            color: #666;
            font-size: 14px;
        }
        
        .maintenance-description {
            margin-bottom: 15px;
            line-height: 1.5;
        }
        
        .maintenance-meta {
            display: flex;
            justify-content: space-between;
            color: #888;
            font-size: 13px;
        }
        
        .add-maintenance-form {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .form-group-full {
            grid-column: 1 / -1;
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
                        <a href="maintenance.php">
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
                <div class="topbar-actions">
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

            <div class="page-header">
                <h1><?php echo $page_title; ?></h1>
                <p>Manage maintenance updates for the residential complex</p>
            </div>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <section class="add-maintenance-form">
                <h2><i class="fas fa-plus-circle"></i> Add New Maintenance Update</h2>
                <form method="POST" action="">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" id="title" name="title" required placeholder="E.g., Elevator Maintenance">
                        </div>
                        
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" required>
                                <option value="scheduled">Scheduled</option>
                                <option value="in-progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="postponed">Postponed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="start_date">Start Date</label>
                            <input type="date" id="start_date" name="start_date" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="end_date">End Date (Optional)</label>
                            <input type="date" id="end_date" name="end_date">
                        </div>
                        
                        <div class="form-group-full">
                            <label for="affected_areas">Affected Areas</label>
                            <input type="text" id="affected_areas" name="affected_areas" placeholder="E.g., Building A, Swimming Pool, Main Entrance">
                        </div>
                        
                        <div class="form-group-full">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="4" required placeholder="Provide details about the maintenance work..."></textarea>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Maintenance Update
                        </button>
                    </div>
                </form>
            </section>
            
            <section class="maintenance-list">
                <h2><i class="fas fa-clipboard-list"></i> Recent Maintenance Updates</h2>
                
                <?php if (empty($maintenance_updates)): ?>
                    <div class="empty-state">
                        <i class="fas fa-info-circle"></i>
                        <p>No maintenance updates have been added yet.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($maintenance_updates as $update): ?>
                        <div class="maintenance-card">
                            <div class="maintenance-header">
                                <h3 class="maintenance-title"><?php echo htmlspecialchars($update['title']); ?></h3>
                                <span class="status-badge status-<?php echo $update['status']; ?>">
                                    <?php 
                                        $status_labels = [
                                            'scheduled' => 'Scheduled',
                                            'in-progress' => 'In Progress',
                                            'completed' => 'Completed',
                                            'postponed' => 'Postponed',
                                            'cancelled' => 'Cancelled'
                                        ];
                                        echo $status_labels[$update['status']];
                                    ?>
                                </span>
                            </div>
                            
                            <div class="maintenance-dates">
                                <div>
                                    <i class="far fa-calendar-alt"></i> 
                                    Start: <?php echo date('M d, Y', strtotime($update['start_date'])); ?>
                                </div>
                                <?php if (!empty($update['end_date'])): ?>
                                <div>
                                    <i class="far fa-calendar-check"></i> 
                                    End: <?php echo date('M d, Y', strtotime($update['end_date'])); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="maintenance-description">
                                <?php echo nl2br(htmlspecialchars($update['description'])); ?>
                            </div>
                            
                            <?php if (!empty($update['affected_areas'])): ?>
                            <div class="affected-areas">
                                <strong><i class="fas fa-map-marker-alt"></i> Affected Areas:</strong> 
                                <?php echo htmlspecialchars($update['affected_areas']); ?>
                            </div>
                            <?php endif; ?>
                            
                            <div class="maintenance-meta">
                                <span>Added by <?php echo htmlspecialchars($update['admin_name']); ?></span>
                                <span>Posted on <?php echo date('M d, Y, g:i A', strtotime($update['created_at'])); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <script>
        // Dark mode toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const darkModeToggle = document.getElementById('darkModeToggle');
            
            // Check if user previously enabled dark mode
            if (localStorage.getItem('darkMode') === 'enabled') {
                document.body.classList.add('dark-mode');
                darkModeToggle.checked = true;
            }
            
            darkModeToggle.addEventListener('change', function() {
                if (this.checked) {
                    document.body.classList.add('dark-mode');
                    localStorage.setItem('darkMode', 'enabled');
                } else {
                    document.body.classList.remove('dark-mode');
                    localStorage.setItem('darkMode', null);
                }
            });
        });
    </script>
</body>
</html> 
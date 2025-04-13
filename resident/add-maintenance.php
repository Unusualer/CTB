<?php
// Start session
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/role_access.php';

// Check if user is logged in and has appropriate role
requireRole('resident');


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
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        
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

$page_title = "Ajouter une Mise à Jour de Maintenance";
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
        
        [data-theme="dark"] .text-danger {
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
        
        textarea.form-control {
            min-height: 150px;
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
                    <a href="maintenance.php">Maintenance</a>
                    <span>Ajouter une Mise à Jour de Maintenance</span>
                </div>
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

            <div class="content-wrapper">
            <div class="card">
                <div class="card-header">
                        <h3><i class="fas fa-tools"></i> Ajouter une Mise à Jour de Maintenance</h3>
                </div>
                <div class="card-body">
                    <form action="add-maintenance.php" method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="title">Titre <span class="text-danger">*</span></label>
                                    <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($maintenance['title']); ?>" required>
                                </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="location">Emplacement <span class="text-danger">*</span></label>
                                    <input type="text" name="location" id="location" value="<?php echo htmlspecialchars($maintenance['location']); ?>" required>
                                <small class="form-text text-muted">Spécifiez où dans le complexe résidentiel la maintenance aura lieu.</small>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="description">Description <span class="text-danger">*</span></label>
                                    <textarea name="description" id="description" required><?php echo htmlspecialchars($maintenance['description']); ?></textarea>
                                <small class="form-text text-muted">Fournissez des informations détaillées sur les travaux de maintenance à effectuer.</small>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="start_date">Date de Début <span class="text-danger">*</span></label>
                                    <input type="date" name="start_date" id="start_date" value="<?php echo htmlspecialchars($maintenance['start_date']); ?>" required>
                                </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="end_date">Date de Fin <span class="text-danger">*</span></label>
                                    <input type="date" name="end_date" id="end_date" value="<?php echo htmlspecialchars($maintenance['end_date']); ?>" required>
                                </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="status">Statut <span class="text-danger">*</span></label>
                                    <select name="status" id="status" required>
                                    <option value="scheduled" <?php echo $maintenance['status'] === 'scheduled' ? 'selected' : ''; ?>>Planifié</option>
                                    <option value="in_progress" <?php echo $maintenance['status'] === 'in_progress' ? 'selected' : ''; ?>>En Cours</option>
                                    <option value="completed" <?php echo $maintenance['status'] === 'completed' ? 'selected' : ''; ?>>Terminé</option>
                                    <option value="delayed" <?php echo $maintenance['status'] === 'delayed' ? 'selected' : ''; ?>>Retardé</option>
                                    <option value="cancelled" <?php echo $maintenance['status'] === 'cancelled' ? 'selected' : ''; ?>>Annulé</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="priority">Priorité <span class="text-danger">*</span></label>
                                    <select name="priority" id="priority" required>
                                    <option value="low" <?php echo $maintenance['priority'] === 'low' ? 'selected' : ''; ?>>Basse</option>
                                    <option value="medium" <?php echo $maintenance['priority'] === 'medium' ? 'selected' : ''; ?>>Moyenne</option>
                                    <option value="high" <?php echo $maintenance['priority'] === 'high' ? 'selected' : ''; ?>>Haute</option>
                                    <option value="emergency" <?php echo $maintenance['priority'] === 'emergency' ? 'selected' : ''; ?>>Urgence</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <a href="maintenance.php" class="btn btn-secondary">Annuler</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus-circle"></i> Ajouter la Mise à Jour de Maintenance
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
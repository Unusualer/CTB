<?php
// Start session
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Vous devez être connecté en tant qu'administrateur pour accéder à cette page.";
    header("Location: ../login.php");
    exit();
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "L'ID du ticket est requis.";
    header("Location: tickets.php");
    exit();
}

$ticket_id = (int)$_GET['id'];
$ticket = null;
$statuses = ['open', 'in_progress', 'closed', 'reopened'];
$priorities = ['low', 'medium', 'high', 'urgent'];
$users = [];

// Get all users for the dropdown
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $users_stmt = $db->prepare("SELECT id, name, email FROM users ORDER BY name ASC");
    $users_stmt->execute();
    $users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données: " . $e->getMessage();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    $user_id = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    $subject = trim($_POST['subject'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = trim($_POST['status'] ?? '');
    $response = trim($_POST['response'] ?? '');
    $admin_notes = trim($_POST['admin_notes'] ?? '');
    
    $errors = [];
    
    // Validate user_id
    if (empty($user_id)) {
        $errors[] = "L'utilisateur est requis.";
    }
    
    // Validate subject
    if (empty($subject)) {
        $errors[] = "Le sujet est requis.";
    }
    
    // Validate description
    if (empty($description)) {
        $errors[] = "La description est requise.";
    }
    
    // Validate status
    if (empty($status) || !in_array($status, $statuses)) {
        $errors[] = "Un statut valide est requis.";
    }
    
    // If no errors, update ticket
    if (empty($errors)) {
        try {
            $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Check if admin_notes column exists (from update-ticket-status.php)
            $check_column_sql = "SHOW COLUMNS FROM tickets LIKE 'admin_notes'";
            $check_stmt = $db->prepare($check_column_sql);
            $check_stmt->execute();
            
            if ($check_stmt->rowCount() === 0) {
                // Add admin_notes column if it doesn't exist
                $alter_sql = "ALTER TABLE tickets ADD COLUMN admin_notes TEXT DEFAULT NULL";
                $alter_stmt = $db->prepare($alter_sql);
                $alter_stmt->execute();
            }

            // Get current status for activity log
            $current_status_stmt = $db->prepare("SELECT status FROM tickets WHERE id = :id");
            $current_status_stmt->bindParam(':id', $ticket_id, PDO::PARAM_INT);
            $current_status_stmt->execute();
            $current_status = $current_status_stmt->fetch(PDO::FETCH_ASSOC)['status'];
            
            // Update ticket using only columns that exist in the database schema
            $stmt = $db->prepare("UPDATE tickets SET user_id = :user_id, subject = :subject, 
                                 description = :description, status = :status, 
                                 response = :response, admin_notes = :admin_notes, updated_at = NOW() 
                                 WHERE id = :id");
            
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':subject', $subject);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':response', $response);
            $stmt->bindParam(':admin_notes', $admin_notes);
            $stmt->bindParam(':id', $ticket_id, PDO::PARAM_INT);
            
            $stmt->execute();
            
            // Log the activity - include status change in the description if it changed
            $admin_id = $_SESSION['user_id'];
            $log_description = "Ticket mis à jour #$ticket_id: $subject";
            
            if ($current_status !== $status) {
                $log_description .= " (Statut changé de '" . ucfirst($current_status) . "' à '" . ucfirst($status) . "')";
            }
            
            log_activity($db, $admin_id, 'update', 'ticket', $ticket_id, $log_description);
            
            $_SESSION['success'] = "Ticket mis à jour avec succès.";
            header("Location: view-ticket.php?id=$ticket_id");
            exit();
            
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur de base de données: " . $e->getMessage();
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
        $_SESSION['error'] = "Ticket non trouvé.";
        header("Location: tickets.php");
        exit();
    }
    
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données: " . $e->getMessage();
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

// Page title
$page_title = "Modifier le Ticket";
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
                    <a href="tickets.php">Tickets</a>
                    <a href="view-ticket.php?id=<?php echo $ticket_id; ?>">Voir le Ticket</a>
                    <span>Modifier le Ticket</span>
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
                        <h3><i class="fas fa-edit"></i> Modifier le Ticket #<?php echo $ticket_id; ?></h3>
                    </div>
                    <div class="card-body">
                        <form action="edit-ticket.php?id=<?php echo $ticket_id; ?>" method="POST" class="form">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="user_id">Utilisateur <span class="required">*</span></label>
                                    <select id="user_id" name="user_id" required>
                                        <option value="" disabled>Sélectionner un Utilisateur</option>
                                        <?php foreach ($users as $user): ?>
                                            <option value="<?php echo $user['id']; ?>" <?php echo $ticket['user_id'] == $user['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($user['name'] . ' (' . $user['email'] . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="status">Statut <span class="required">*</span></label>
                                    <select id="status" name="status" required>
                                        <?php foreach ($statuses as $status): ?>
                                            <option value="<?php echo $status; ?>" <?php echo $ticket['status'] === $status ? 'selected' : ''; ?>>
                                                <?php echo getStatusLabel($status); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="subject">Sujet <span class="required">*</span></label>
                                <input type="text" id="subject" name="subject" value="<?php echo htmlspecialchars($ticket['subject']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Description <span class="required">*</span></label>
                                <textarea id="description" name="description" rows="6" required><?php echo htmlspecialchars($ticket['description']); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="response">Réponse</label>
                                <textarea id="response" name="response" rows="4"><?php echo isset($ticket['response']) ? htmlspecialchars($ticket['response']) : ''; ?></textarea>
                                <small>Réponse au ticket (visible par l'utilisateur)</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="admin_notes">Notes Administrateur</label>
                                <textarea id="admin_notes" name="admin_notes" rows="3"><?php echo isset($ticket['admin_notes']) ? htmlspecialchars($ticket['admin_notes']) : ''; ?></textarea>
                                <small>Notes internes (non visibles par l'utilisateur)</small>
                            </div>
                            
                            <div class="form-actions">
                                <a href="view-ticket.php?id=<?php echo $ticket_id; ?>" class="btn btn-secondary">Annuler</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Mettre à Jour le Ticket
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
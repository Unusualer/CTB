<?php
// Start session
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/role_access.php';

// Check if user is logged in and has appropriate role
requireRole('admin');


// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "L'ID de la propriété est requis.";
    header("Location: properties.php");
    exit();
}

$property_id = (int)$_GET['id'];
$property = null;
$types = ['apartment', 'parking'];
$residents = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    $type = trim($_POST['type'] ?? '');
    $identifier = trim($_POST['identifier'] ?? '');
    $user_id = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null;
    
    $errors = [];
    
    // Validate required fields - only validate type and identifier existence, not their values
    if (empty($type) || !in_array($type, $types)) {
        $errors[] = "Le type de propriété est invalide.";
    }
    
    if (empty($identifier)) {
        $errors[] = "L'identifiant est requis.";
    }
    
    // If no errors, update property
    if (empty($errors)) {
        try {
            $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Get the existing property data
            $check_stmt = $db->prepare("SELECT type, identifier FROM properties WHERE id = :id");
            $check_stmt->bindParam(':id', $property_id, PDO::PARAM_INT);
            $check_stmt->execute();
            $existing_property = $check_stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$existing_property) {
                $_SESSION['error'] = "Propriété non trouvée.";
            } else {
                // Update only the user_id, keeping the original type and identifier
                $update_stmt = $db->prepare("UPDATE properties SET 
                                user_id = :user_id,
                                updated_at = NOW() 
                                WHERE id = :id");
                
                $update_stmt->bindParam(':user_id', $user_id);
                $update_stmt->bindParam(':id', $property_id, PDO::PARAM_INT);
                
                $update_stmt->execute();
                
                // Log the activity
                $admin_id = $_SESSION['user_id'];
                $log_stmt = $db->prepare("INSERT INTO activity_log (user_id, action, description) 
                                       VALUES (:admin_id, 'update', :description)");
                
                if ($user_id) {
                    $description = "Assigned resident (ID: $user_id) to property ID: $property_id";
                } else {
                    $description = "Removed resident assignment from property ID: $property_id";
                }
                $log_stmt->bindParam(':admin_id', $admin_id);
                $log_stmt->bindParam(':description', $description);
                $log_stmt->execute();
                
                $_SESSION['success'] = "Attribution de la propriété mise à jour avec succès.";
                header("Location: view-property.php?id=$property_id");
                exit();
            }
            
        } catch (PDOException $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
}

// Get property data
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch property details
    $stmt = $db->prepare("SELECT * FROM properties WHERE id = :id");
    $stmt->bindParam(':id', $property_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        $_SESSION['error'] = "Property not found.";
        header("Location: properties.php");
        exit();
    }
    
    $property = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get resident users for dropdown
    $user_stmt = $db->prepare("SELECT id, name, email FROM users WHERE role = 'resident' ORDER BY name");
    $user_stmt->execute();
    $residents = $user_stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    header("Location: properties.php");
    exit();
}

// Page title
$page_title = "Assigner un Résident à la Propriété";
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
        
        input, select {
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background-color: var(--light-color);
            color: var(--text-primary);
            font-size: 0.95rem;
            transition: all 0.2s ease;
            width: 100%;
        }
        
        input:hover, select:hover {
            border-color: var(--primary-color-light);
        }
        
        input:focus, select:focus {
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
        
        .help-text {
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
        [data-theme="dark"] select {
            background-color: #2a2e35 !important;
            color: #ffffff !important;
            border-color: #3f4756;
        }
        
        [data-theme="dark"] input:hover, 
        [data-theme="dark"] select:hover {
            border-color: var(--primary-color-light);
        }
        
        [data-theme="dark"] input:focus, 
        [data-theme="dark"] select:focus {
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
        
        [data-theme="dark"] .help-text {
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
                    <a href="properties.php">Propriétés</a>
                    <a href="view-property.php?id=<?php echo $property_id; ?>">Voir la Propriété</a>
                    <span>Assigner un Résident</span>
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
                        <h3><i class="fas fa-user-edit"></i> Assigner un Résident à la Propriété</h3>
                    </div>
                    <div class="card-body">
                        <form action="edit-property.php?id=<?php echo $property_id; ?>" method="POST" class="form">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="type">Type de Propriété</label>
                                    <input type="text" id="type" value="<?php echo ucfirst($property['type']); ?>" readonly>
                                    <input type="hidden" name="type" value="<?php echo htmlspecialchars($property['type']); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="identifier">Identifiant</label>
                                    <input type="text" id="identifier" value="<?php echo htmlspecialchars($property['identifier']); ?>" readonly>
                                    <input type="hidden" name="identifier" value="<?php echo htmlspecialchars($property['identifier']); ?>">
                                    <div class="help-text">L'identifiant de la propriété est défini lors de sa création et ne peut pas être modifié ici</div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="user_id">Résident Assigné</label>
                                    <select id="user_id" name="user_id">
                                        <option value="">Non assigné</option>
                                        <?php foreach ($residents as $resident): ?>
                                            <option value="<?php echo $resident['id']; ?>" <?php echo (isset($property['user_id']) && $property['user_id'] == $resident['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($resident['name']); ?> (<?php echo htmlspecialchars($resident['email']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="help-text">Sélectionnez un résident à assigner à cette propriété, ou laissez non assigné</div>
                                </div>
                            </div>
                        
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-user-plus"></i> Assigner le Résident
                                </button>
                                <a href="view-property.php?id=<?php echo $property_id; ?>" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Annuler
                                </a>
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
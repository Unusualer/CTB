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

// Check if payment ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID de paiement invalide.";
    header("Location: payments.php");
    exit();
}

$payment_id = intval($_GET['id']);

// Initialize variables
$error = '';
$success = '';
$payment = [];

// Get payment details
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $query = "SELECT p.*, p.type as payment_method 
              FROM payments p 
              WHERE p.id = :id";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $payment_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        $_SESSION['error'] = "Paiement non trouvé.";
        header("Location: payments.php");
        exit();
    }
    
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    header("Location: payments.php");
    exit();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Validate and sanitize input
        $property_id = !empty($_POST['property_id']) ? intval($_POST['property_id']) : null;
        $amount = !empty($_POST['amount']) ? floatval($_POST['amount']) : 0;
        $payment_method = !empty($_POST['payment_method']) ? $_POST['payment_method'] : 'credit_card';
        $month = !empty($_POST['month']) ? $_POST['month'] : date('Y-m-d');
        $status = !empty($_POST['status']) ? $_POST['status'] : 'completed';
        $description = !empty($_POST['description']) ? $_POST['description'] : null;
        
        // Validate required fields
        if (empty($property_id) || empty($amount) || $amount <= 0) {
            throw new Exception("La propriété et le montant sont requis. Le montant doit être supérieur à zéro.");
        }
        
        // Update payment record
        $query = "UPDATE payments SET 
                    property_id = :property_id, 
                    amount = :amount, 
                    month = :month, 
                    status = :status, 
                    type = :payment_method
                  WHERE id = :id";
                
        $stmt = $db->prepare($query);
        $stmt->bindParam(':property_id', $property_id);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':month', $month);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':payment_method', $payment_method);
        $stmt->bindParam(':id', $payment_id);
        
        if ($stmt->execute()) {
            // Log activity
            log_activity($db, $_SESSION['user_id'], 'update', 'payment', $payment_id, "Updated payment #$payment_id");
            
            $success = "Paiement mis à jour avec succès !";
            
            // Refresh payment data
            $query = "SELECT p.*, p.type as payment_method 
                    FROM payments p 
                    WHERE p.id = :id";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $payment_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            throw new Exception("Échec de la mise à jour du paiement. Veuillez réessayer.");
        }
        
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Fetch properties for dropdown
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $properties_query = "SELECT id, identifier, type FROM properties ORDER BY identifier";
    $properties_stmt = $db->prepare($properties_query);
    $properties_stmt->execute();
    $properties = $properties_stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    $properties = [];
}

// Page title
$page_title = "Modifier le Paiement";
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
        
        /* Breadcrumb styling for dark mode */
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
                    <a href="payments.php">Paiements</a>
                    <span>Modifier le Paiement</span>
                </div>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <div class="content-wrapper">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-edit"></i> Modifier le Paiement</h3>
                    </div>
                    <div class="card-body">
                        <form action="edit-payment.php?id=<?php echo $payment_id; ?>" method="POST">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="property_id">Propriété <span class="text-danger">*</span></label>
                                    <select name="property_id" id="property_id" required>
                                        <option value="">-- Sélectionner une Propriété --</option>
                                        <?php foreach ($properties as $property): ?>
                                            <option value="<?php echo $property['id']; ?>" <?php echo $payment['property_id'] == $property['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($property['identifier']); ?> - <?php echo htmlspecialchars($property['type']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="amount">Montant ($) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0.01" name="amount" id="amount" value="<?php echo htmlspecialchars($payment['amount']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="payment_method">Méthode de Paiement <span class="text-danger">*</span></label>
                                    <select name="payment_method" id="payment_method" required>
                                        <option value="transfer" <?php echo $payment['payment_method'] === 'transfer' ? 'selected' : ''; ?>>Virement Bancaire</option>
                                        <option value="cheque" <?php echo $payment['payment_method'] === 'cheque' ? 'selected' : ''; ?>>Chèque</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="month">Mois de Paiement <span class="text-danger">*</span></label>
                                    <input type="date" name="month" id="month" value="<?php echo htmlspecialchars($payment['month']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="status">Statut <span class="text-danger">*</span></label>
                                    <select name="status" id="status" required>
                                        <option value="paid" <?php echo $payment['status'] === 'paid' ? 'selected' : ''; ?>>Payé</option>
                                        <option value="pending" <?php echo $payment['status'] === 'pending' ? 'selected' : ''; ?>>En Attente</option>
                                        <option value="cancelled" <?php echo $payment['status'] === 'cancelled' ? 'selected' : ''; ?>>Annulé</option>
                                        <option value="failed" <?php echo $payment['status'] === 'failed' ? 'selected' : ''; ?>>Échoué</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea name="description" id="description" rows="4"><?php echo htmlspecialchars($payment['description'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <a href="payments.php" class="btn btn-secondary">Annuler</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Mettre à Jour le Paiement
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
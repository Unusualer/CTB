<?php
// Start session and check if user is logged in as admin
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Vous devez être connecté en tant qu'administrateur pour accéder à cette page.";
    header("Location: ../login.php");
    exit();
}

// Check if payment ID is set
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "L'ID du paiement est requis.";
    header("Location: payments.php");
    exit();
}

$payment_id = intval($_GET['id']);
$payment = null;
$error_message = '';

// Fetch payment details
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = "SELECT p.*, p.type as payment_method, pr.identifier as property_identifier, pr.type as property_type, 
            u.name as user_name, u.email as user_email, u.phone as user_phone, u.id as user_id
            FROM payments p 
            LEFT JOIN properties pr ON p.property_id = pr.id
            LEFT JOIN users u ON pr.user_id = u.id
            WHERE p.id = :id";

    $stmt = $db->prepare($sql);
    $stmt->bindParam(":id", $payment_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        $_SESSION['error'] = "Paiement non trouvé.";
        header("Location: payments.php");
        exit();
    }
    
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Handle form submission for updating status
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
        $new_status = $_POST['status'];
        $admin_notes = $_POST['admin_notes'];
        
        // Check if admin_notes column exists
        $check_column_sql = "SHOW COLUMNS FROM payments LIKE 'admin_notes'";
        $check_stmt = $db->prepare($check_column_sql);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() === 0) {
            // Add admin_notes column if it doesn't exist
            $alter_sql = "ALTER TABLE payments ADD COLUMN admin_notes TEXT DEFAULT NULL";
            $alter_stmt = $db->prepare($alter_sql);
            $alter_stmt->execute();
        }
        
        $update_sql = "UPDATE payments SET status = :status, admin_notes = :notes, updated_at = NOW() WHERE id = :id";
        $update_stmt = $db->prepare($update_sql);
        $update_stmt->bindParam(":status", $new_status, PDO::PARAM_STR);
        $update_stmt->bindParam(":notes", $admin_notes, PDO::PARAM_STR);
        $update_stmt->bindParam(":id", $payment_id, PDO::PARAM_INT);
        
        if ($update_stmt->execute()) {
            // Log the activity
            $admin_id = $_SESSION['user_id'];
            log_activity($db, $admin_id, 'update', 'payment', $payment_id, "Updated payment #$payment_id status to: " . ucfirst($new_status));
            
            $_SESSION['success'] = "Payment updated successfully.";
            header("Location: view-payment.php?id=$payment_id");
            exit();
        } else {
            $error_message = "Failed to update payment status.";
        }
    }
    
} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
}

// Get status class for styling
function getStatusBadgeClass($status) {
    switch($status) {
        case 'paid':
            return 'success';
        case 'pending':
            return 'warning';
        case 'cancelled':
            return 'secondary';
        case 'failed':
            return 'danger';
        default:
            return 'secondary';
    }
}

// Format payment method for display
function formatPaymentMethod($method) {
    if (empty($method)) {
        return 'Unknown';
    }
    return ucfirst(str_replace('_', ' ', $method));
}

// Format date helper function
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

// Page title
$page_title = "Détails du Paiement";
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
        .payment-details {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        
        @media (min-width: 768px) {
            .payment-details {
                grid-template-columns: 2fr 1fr;
            }
        }
        
        .detail-group {
            margin-bottom: 1rem;
        }
        
        .detail-group .label {
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: var(--text-secondary);
        }
        
        .detail-group .value {
            font-size: 1rem;
        }
        
        .meta-info {
            color: var(--text-secondary);
            font-size: 0.875rem;
            border-top: 1px solid var(--border-color);
            padding-top: 1rem;
            margin-top: 1.5rem;
        }
        
        .description-box {
            background-color: var(--light-color);
            padding: 1.25rem;
            border-radius: 0.5rem;
            border: 1px solid var(--border-color);
            margin: 1rem 0;
            white-space: pre-line;
            line-height: 1.6;
        }
        
        .empty-state {
            text-align: center;
            padding: 2rem;
            color: var(--text-secondary);
        }
        
        .empty-state i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            display: block;
        }
        
        .card-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0.375rem;
            background-color: var(--light-color);
        }
        
        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }
        
        /* Dark mode specific styles */
        [data-theme="dark"] .description-box {
            background-color: #2a2e35;
            border-color: #3f4756;
        }
        
        [data-theme="dark"] .form-control {
            background-color: #2a2e35;
            color: #ffffff;
            border-color: #3f4756;
        }
        
        [data-theme="dark"] .form-control::placeholder {
            color: #8e99ad;
        }
        
        /* Status indicator */
        .status-indicator {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: 600;
        }
        
        .status-primary {
            background-color: rgba(var(--primary-rgb), 0.15);
            color: var(--primary-color);
        }
        
        .status-success {
            background-color: rgba(25, 135, 84, 0.15);
            color: #198754;
        }
        
        .status-warning {
            background-color: rgba(255, 193, 7, 0.15);
            color: #ffc107;
        }
        
        .status-danger {
            background-color: rgba(220, 53, 69, 0.15);
            color: #dc3545;
        }
        
        .status-info {
            background-color: rgba(13, 202, 240, 0.15);
            color: #0dcaf0;
        }
        
        .status-secondary {
            background-color: rgba(108, 117, 125, 0.15);
            color: #6c757d;
        }
        
        .btn-block {
            display: block;
            width: 100%;
            text-align: center;
        }
        
        .mt-3 {
            margin-top: 1rem;
        }
        
        .mt-4 {
            margin-top: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/admin-sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-header">
                <a href="payments.php" class="back-button">
                    <i class="fas fa-arrow-left"></i> Retour aux Paiements
                </a>
                <h1><?php echo $page_title; ?></h1>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <!-- Success message -->
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

            <?php if ($payment): ?>
                <div class="card">
                    <div class="card-header">
                        <h3>Paiement #<?php echo $payment_id; ?></h3>
                        <div class="card-actions">
                            <a href="export-payment.php?id=<?php echo $payment_id; ?>" class="btn btn-secondary">
                                <i class="fas fa-file-export"></i> Exporter
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="payment-details">
                            <div class="payment-main">
                                <div class="detail-group">
                                    <div class="label">Montant</div>
                                    <div class="value"><strong>$<?php echo number_format($payment['amount'], 2); ?></strong></div>
                                </div>
                                
                                <div class="detail-group">
                                    <div class="label">Statut</div>
                                    <div class="value">
                                        <span class="status-indicator status-<?php echo getStatusBadgeClass($payment['status']); ?>">
                                            <?php 
                                                switch($payment['status']) {
                                                    case 'pending':
                                                        echo 'En Attente';
                                                        break;
                                                    case 'paid':
                                                        echo 'Payé';
                                                        break;
                                                    case 'cancelled':
                                                        echo 'Annulé';
                                                        break;
                                                    case 'failed':
                                                        echo 'Échoué';
                                                        break;
                                                    default:
                                                        echo ucfirst($payment['status']);
                                                }
                                            ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="detail-group">
                                    <div class="label">Méthode de Paiement</div>
                                    <div class="value">
                                        <?php 
                                            $methodIcon = '';
                                            $paymentMethod = isset($payment['payment_method']) ? $payment['payment_method'] : 'transfer';
                                            switch ($paymentMethod) {
                                                case 'transfer': 
                                                    $methodIcon = '<i class="fas fa-university"></i> ';
                                                    echo $methodIcon . 'Virement';
                                                    break;
                                                case 'cheque': 
                                                    $methodIcon = '<i class="fas fa-money-check"></i> ';
                                                    echo $methodIcon . 'Chèque';
                                                    break;
                                                default: 
                                                    $methodIcon = '<i class="fas fa-money-check"></i> ';
                                                    echo $methodIcon . ucfirst($paymentMethod);
                                            }
                                        ?>
                                    </div>
                                </div>
                                
                                <div class="detail-group">
                                    <div class="label">Mois de Paiement</div>
                                    <div class="value"><?php echo formatDate($payment['month']); ?></div>
                                </div>
                                
                                <div class="detail-group">
                                    <div class="label">ID de Transaction</div>
                                    <div class="value"><?php echo htmlspecialchars($payment['transaction_id'] ?? 'N/A'); ?></div>
                                </div>
                                
                                <?php if (!empty($payment['description'])): ?>
                                <div class="detail-group">
                                    <div class="label">Description</div>
                                    <div class="description-box">
                                        <?php echo nl2br(htmlspecialchars($payment['description'] ?? 'Aucune description disponible')); ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($payment['admin_notes'])): ?>
                                <div class="detail-group">
                                    <div class="label">Notes Administrateur</div>
                                    <div class="description-box">
                                        <?php echo nl2br(htmlspecialchars($payment['admin_notes'])); ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <div class="meta-info">
                                    <p>Créé le <?php echo date('d F Y \à G:i', strtotime($payment['created_at'])); ?></p>
                                    <?php if (isset($payment['updated_at'])): ?>
                                        <p>Dernière mise à jour le <?php echo date('d F Y \à G:i', strtotime($payment['updated_at'])); ?></p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="card mt-4">
                                    <div class="card-header">
                                        <h4>Mettre à Jour le Statut du Paiement</h4>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="">
                                            <input type="hidden" name="update_status" value="1">
                                            <div class="form-group">
                                                <label for="status">Statut:</label>
                                                <select name="status" id="status" class="form-control">
                                                    <option value="pending" <?php echo $payment['status'] === 'pending' ? 'selected' : ''; ?>>En Attente</option>
                                                    <option value="paid" <?php echo $payment['status'] === 'paid' ? 'selected' : ''; ?>>Payé</option>
                                                    <option value="cancelled" <?php echo $payment['status'] === 'cancelled' ? 'selected' : ''; ?>>Annulé</option>
                                                    <option value="failed" <?php echo $payment['status'] === 'failed' ? 'selected' : ''; ?>>Échoué</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="admin_notes">Notes Administrateur:</label>
                                                <textarea name="admin_notes" id="admin_notes" class="form-control" rows="4"><?php echo htmlspecialchars($payment['admin_notes'] ?? ''); ?></textarea>
                                                <small>Notes internes visibles uniquement par les administrateurs</small>
                                            </div>
                                            <div class="form-actions">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-save"></i> Mettre à Jour le Paiement
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="payment-sidebar">
                                <div class="card sidebar-card">
                                    <div class="card-header">
                                        <h4>Informations Utilisateur</h4>
                                    </div>
                                    <div class="card-body">
                                        <?php if (isset($payment['user_id']) && $payment['user_id']): ?>
                                            <div class="detail-group">
                                                <div class="label">Nom</div>
                                                <div class="value"><?php echo htmlspecialchars($payment['user_name']); ?></div>
                                            </div>
                                            
                                            <div class="detail-group">
                                                <div class="label">Email</div>
                                                <div class="value"><?php echo htmlspecialchars($payment['user_email']); ?></div>
                                            </div>
                                            
                                            <div class="detail-group">
                                                <div class="label">Téléphone</div>
                                                <div class="value"><?php echo htmlspecialchars($payment['user_phone'] ?? 'N/A'); ?></div>
                                            </div>
                                            
                                            <div class="detail-group">
                                                <div class="label">ID Utilisateur</div>
                                                <div class="value">#<?php echo $payment['user_id']; ?></div>
                                            </div>
                                            
                                            <a href="view-user.php?id=<?php echo $payment['user_id']; ?>" class="btn btn-primary btn-block mt-3">
                                                <i class="fas fa-user"></i> Voir le Profil Utilisateur
                                            </a>
                                        <?php else: ?>
                                            <div class="empty-state">
                                                <i class="fas fa-user-slash"></i>
                                                <p>Aucun utilisateur associé à ce paiement.</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="card sidebar-card mt-4">
                                    <div class="card-header">
                                        <h4>Informations Propriété</h4>
                                    </div>
                                    <div class="card-body">
                                        <?php if (isset($payment['property_id']) && $payment['property_id']): ?>
                                            <div class="detail-group">
                                                <div class="label">Identifiant</div>
                                                <div class="value"><?php echo htmlspecialchars($payment['property_identifier'] ?? 'N/A'); ?></div>
                                            </div>
                                            
                                            <div class="detail-group">
                                                <div class="label">Type</div>
                                                <div class="value"><?php echo ucfirst(htmlspecialchars($payment['property_type'] ?? 'N/A')); ?></div>
                                            </div>
                                            
                                            <div class="detail-group">
                                                <div class="label">ID Propriété</div>
                                                <div class="value">#<?php echo $payment['property_id']; ?></div>
                                            </div>
                                            
                                            <a href="view-property.php?id=<?php echo $payment['property_id']; ?>" class="btn btn-primary btn-block mt-3">
                                                <i class="fas fa-home"></i> Voir la Propriété
                                            </a>
                                        <?php else: ?>
                                            <div class="empty-state">
                                                <i class="fas fa-building"></i>
                                                <p>Aucune propriété associée à ce paiement.</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script src="js/dark-mode.js"></script>
</body>
</html> 
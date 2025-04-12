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

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Méthode de requête invalide.";
    header("Location: payments.php");
    exit();
}

// Check if payment_id is provided
if (!isset($_POST['payment_id']) || empty($_POST['payment_id'])) {
    $_SESSION['error'] = "L'ID du paiement est requis.";
    header("Location: payments.php");
    exit();
}

$payment_id = (int)$_POST['payment_id'];

try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // First check if payment exists and get payment info for the log
    $check_stmt = $db->prepare("SELECT amount, property_id FROM payments WHERE id = :id");
    $check_stmt->bindParam(':id', $payment_id, PDO::PARAM_INT);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() === 0) {
        $_SESSION['error'] = "Paiement non trouvé.";
        header("Location: payments.php");
        exit();
    }
    
    $payment_info = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Begin transaction
    $db->beginTransaction();

    // Delete payment from database
    $stmt = $db->prepare("DELETE FROM payments WHERE id = :id");
    $stmt->bindParam(':id', $payment_id, PDO::PARAM_INT);
    $stmt->execute();
    
    // Log the activity
    $admin_id = $_SESSION['user_id'];
    $description = "Paiement supprimé : Montant $" . number_format($payment_info['amount'], 2) . " (ID: $payment_id, ID Propriété: " . $payment_info['property_id'] . ")";
    log_activity($db, $admin_id, 'delete', 'payment', $payment_id, $description);
    
    // Commit transaction
    $db->commit();
    
    $_SESSION['success'] = "Paiement supprimé avec succès.";
    header("Location: payments.php");
    exit();
    
} catch (PDOException $e) {
    // Rollback transaction on error
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: payments.php");
    exit();
} 
<?php
// Start session
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/role_access.php';

// Check if user is logged in and has appropriate role
requireRole('manager');


// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Méthode de requête invalide.";
    header("Location: maintenance.php");
    exit();
}

// Check if maintenance_id is provided
if (!isset($_POST['maintenance_id']) || empty($_POST['maintenance_id'])) {
    $_SESSION['error'] = "L'ID de la maintenance est requis.";
    header("Location: maintenance.php");
    exit();
}

$maintenance_id = (int)$_POST['maintenance_id'];

try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // First check if maintenance update exists and get its info for the log
    $check_stmt = $db->prepare("SELECT title, location FROM maintenance WHERE id = :id");
    $check_stmt->bindParam(':id', $maintenance_id, PDO::PARAM_INT);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() === 0) {
        $_SESSION['error'] = "Mise à jour de maintenance non trouvée.";
        header("Location: maintenance.php");
        exit();
    }
    
    $maintenance_info = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Begin transaction
    $db->beginTransaction();
    
    // Delete maintenance update from database
    $stmt = $db->prepare("DELETE FROM maintenance WHERE id = :id");
    $stmt->bindParam(':id', $maintenance_id, PDO::PARAM_INT);
    $stmt->execute();
    
    // Log the activity
    $admin_id = $_SESSION['user_id'];
    $log_stmt = $db->prepare("INSERT INTO activity_log (user_id, action, entity_type, entity_id, description) 
                             VALUES (:admin_id, 'delete', 'maintenance', :entity_id, :description)");
    
    $description = "Mise à jour de maintenance supprimée : " . $maintenance_info['title'] . " (Emplacement : " . $maintenance_info['location'] . ")";
    $log_stmt->bindParam(':admin_id', $admin_id);
    $log_stmt->bindParam(':entity_id', $maintenance_id, PDO::PARAM_INT);
    $log_stmt->bindParam(':description', $description);
    $log_stmt->execute();
    
    // Commit transaction
    $db->commit();
    
    $_SESSION['success'] = "Mise à jour de maintenance supprimée avec succès.";
    header("Location: maintenance.php");
    exit();
    
} catch (PDOException $e) {
    // Rollback transaction on error
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: maintenance.php");
    exit();
}
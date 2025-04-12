<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    $_SESSION['error'] = "Vous n'avez pas l'autorisation d'accéder à cette page.";
    header("Location: ../login.php");
    exit();
}

// Redirect to dashboard
header("Location: dashboard.php");
exit();
?> 
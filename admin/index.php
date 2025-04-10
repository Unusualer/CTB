<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    $_SESSION['error'] = "You don't have permission to access this page.";
    header("Location: ../login.php");
    exit();
}

// Redirect to dashboard
header("Location: dashboard.php");
exit();
?> 
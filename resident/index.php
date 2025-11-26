<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/role_access.php';

// Check if user is logged in and has appropriate role
requireAnyRole(['resident']);


// Redirect to dashboard
header("Location: dashboard.php");
exit();
?> 
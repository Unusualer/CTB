<?php
// Start session if not already started
session_start();

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Destroy the session
session_destroy();

// Set success message for the login page
session_start();
$_SESSION['login_success'] = "You have been successfully logged out.";

// Redirect to login page
header("Location: login.php");
exit();
?> 
<?php
/**
 * Role-based Access Control for CTB Application - Extension Module
 * This file extends the authentication functions in config.php
 * with additional role-based access control functions
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include config file which has the basic auth functions
require_once __DIR__ . '/config.php';

/**
 * Check if user has at least one of the specified roles
 * 
 * @param array $roles Array of roles to check for
 * @return bool True if user has at least one of the roles, false otherwise
 */
function hasAnyRole($roles) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $userRole = isset($_SESSION['role']) ? $_SESSION['role'] : 
                (isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null);
    
    if (!$userRole) {
        return false;
    }
    
    return in_array($userRole, $roles);
}

/**
 * Require user to have at least one of the specified roles, redirect if not
 * 
 * @param array $roles Array of roles allowed to access this page
 * @param string $redirect_url URL to redirect to if user doesn't have required roles (default: '../login.php')
 * @return void
 */
function requireAnyRole($roles, $redirect_url = '../login.php') {
    requireLogin($redirect_url);
    
    if (!hasAnyRole($roles)) {
        $_SESSION['error'] = "Vous n'avez pas les permissions nécessaires pour accéder à cette page.";
        header("Location: $redirect_url");
        exit();
    }
}

/**
 * Get the current user's role from session
 * Works with either 'role' or 'user_role' session variables
 * 
 * @return string|null The user's role or null if not set
 */
function getCurrentRole() {
    if (isset($_SESSION['role'])) {
        return $_SESSION['role'];
    } elseif (isset($_SESSION['user_role'])) {
        return $_SESSION['user_role'];
    }
    return null;
}
?> 
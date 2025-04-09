<?php 
// If not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get the current page name for highlighting active menu items
$current_page = basename($_SERVER['PHP_SELF']);

// Set page title if not already set
if (!isset($page_title)) {
    $page_title = "Admin Dashboard";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - CTB Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="admin-styles.css">
    <link rel="stylesheet" href="admin-sidebar.css">
    <link rel="stylesheet" href="admin-topbar.css">
    <link rel="stylesheet" href="admin-main.css">
    <link rel="stylesheet" href="dashboard-ui.css">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="admin-container">
        <!-- Top Navigation Bar -->
        <nav class="admin-topbar">
            <div class="menu-toggle">
                <i class="fas fa-bars"></i>
            </div>
            
            <div class="logo">
                <span>CTB Admin</span>
            </div>
            
            <div class="right-items">
                <div class="notifications">
                    <i class="fas fa-bell"></i>
                    <span class="badge">3</span>
                </div>
                
                <div class="user-dropdown">
                    <div class="user-info">
                        <img src="../images/avatar-placeholder.png" alt="User Avatar" class="avatar">
                        <span class="user-name"><?php echo htmlspecialchars($_SESSION['name'] ?? 'Admin User'); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="dropdown-menu">
                        <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                        <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                        <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Main Content Wrapper - the sidebar is included separately in each file --> 
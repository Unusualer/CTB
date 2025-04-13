<?php
/**
 * Admin Template - Main layout template for admin pages
 * 
 * This file serves as the base template for all admin pages, including
 * the sidebar navigation, header, and content area structure.
 */

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and has admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    // Redirect to login page
    header("Location: ../login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']) . "&error=unauthorized");
    exit;
}

// Get current page for active menu highlighting
$currentPage = basename($_SERVER['PHP_SELF']);

// Get user details for display in sidebar
require_once '../includes/config.php';

// Establish database connection if not already established
if (!isset($pdo)) {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        // Log the error but continue with default values
        error_log("Database connection error in admin-template.php: " . $e->getMessage());
    }
}

$userId = $_SESSION['user_id'];

try {
    if (isset($pdo)) {
        $stmt = $pdo->prepare("SELECT first_name, last_name, profile_image FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
    } else {
        throw new PDOException("Database connection not available");
    }
} catch (PDOException $e) {
    // Default user info if DB query fails
    $user = [
        'first_name' => 'Admin',
        'last_name' => 'User',
        'profile_image' => '../images/default-avatar.png'
    ];
}

// Default page title - will be overridden by page specific titles
$pageTitle = isset($page_title) ? $page_title . ' - CTB Admin' : 'CTB Admin Dashboard';

// Default active menu - can be overridden by the page
$activeMenu = isset($activeMenu) ? $activeMenu : '';

// Check for any system notifications
$systemNotifications = [];
$openTickets = 0;
$pendingPayments = 0;

try {
    if (isset($pdo)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE status = 'open'");
        $stmt->execute();
        $openTickets = $stmt->fetchColumn();
        
        if ($openTickets > 0) {
            $systemNotifications[] = [
                'count' => $openTickets,
                'type' => 'tickets'
            ];
        }
        
        // Check for pending payments
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM payments WHERE status = 'pending'");
        $stmt->execute();
        $pendingPayments = $stmt->fetchColumn();
        
        if ($pendingPayments > 0) {
            $systemNotifications[] = [
                'count' => $pendingPayments,
                'type' => 'payments'
            ];
        }
    }
} catch (PDOException $e) {
    // Silently fail - notifications are not critical
    error_log("Error fetching notifications in admin-template.php: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr" data-theme="<?php echo isset($_COOKIE['darkMode']) && $_COOKIE['darkMode'] === 'true' ? 'dark' : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="../favicon.ico" type="image/x-icon">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Admin Core CSS -->
    <link rel="stylesheet" href="css/admin-style.css">
    
    <!-- Page specific CSS -->
    <?php if (isset($pageCss)): ?>
    <link rel="stylesheet" href="<?php echo $pageCss; ?>">
    <?php endif; ?>
</head>
<body id="page-top">
    <!-- Mobile Sidebar Toggle Button -->
    <div class="mobile-sidebar-toggle" id="mobileSidebarToggle">
        <i class="fas fa-bars"></i>
    </div>
    
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Admin Sidebar -->
    <aside class="admin-sidebar">
        <!-- Sidebar Header -->
        <div class="sidebar-header">
            <a href="dashboard.php" class="sidebar-brand">
                <i class="fas fa-building"></i>
                <span>CTB Admin</span>
            </a>
            <button id="sidebarToggle" class="sidebar-toggle">
                <i class="fas fa-chevron-left"></i>
            </button>
        </div>
        
        <!-- Sidebar User -->
        <div class="sidebar-user">
            <div class="sidebar-user-avatar">
                <img src="<?php echo htmlspecialchars($user['profile_image'] ?: '../images/default-avatar.png'); ?>" alt="User Avatar">
            </div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                <div class="sidebar-user-role">Administrator</div>
            </div>
        </div>
        
        <!-- Sidebar Menu -->
        <div class="sidebar-menu">
            <div class="sidebar-menu-category">Tableau de Bord</div>
            
            <div class="sidebar-menu-item">
                <a href="dashboard.php" class="sidebar-menu-link <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="sidebar-menu-text">Tableau de Bord</span>
                </a>
            </div>
            
            <div class="sidebar-menu-category">Résidents</div>
            
            <div class="sidebar-menu-item">
                <a href="residents.php" class="sidebar-menu-link <?php echo in_array($currentPage, ['residents.php', 'resident-details.php', 'add-resident.php', 'edit-resident.php']) ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span class="sidebar-menu-text">Gérer les Résidents</span>
                    <?php if (isset($systemNotifications) && !empty($systemNotifications)): ?>
                    <span class="sidebar-badge">Nouveau</span>
                    <?php endif; ?>
                </a>
            </div>
            
            <div class="sidebar-menu-category">Propriétés</div>
            
            <div class="sidebar-menu-item">
                <a href="properties.php" class="sidebar-menu-link <?php echo in_array($currentPage, ['properties.php', 'property-details.php', 'add-property.php', 'edit-property.php']) ? 'active' : ''; ?>">
                    <i class="fas fa-building"></i>
                    <span class="sidebar-menu-text">Gérer les Propriétés</span>
                </a>
            </div>
            
            <div class="sidebar-menu-item">
                <a href="units.php" class="sidebar-menu-link <?php echo in_array($currentPage, ['units.php', 'unit-details.php', 'add-unit.php', 'edit-unit.php']) ? 'active' : ''; ?>">
                    <i class="fas fa-door-open"></i>
                    <span class="sidebar-menu-text">Gérer les Unités</span>
                </a>
            </div>
            
            <div class="sidebar-menu-category">Finances</div>
            
            <div class="sidebar-menu-item">
                <a href="payments.php" class="sidebar-menu-link <?php echo in_array($currentPage, ['payments.php', 'payment-details.php', 'add-payment.php']) ? 'active' : ''; ?>">
                    <i class="fas fa-credit-card"></i>
                    <span class="sidebar-menu-text">Paiements</span>
                    <?php if (isset($pendingPayments) && $pendingPayments > 0): ?>
                    <span class="sidebar-badge"><?php echo $pendingPayments; ?></span>
                    <?php endif; ?>
                </a>
            </div>
            
            <div class="sidebar-menu-item">
                <a href="invoices.php" class="sidebar-menu-link <?php echo in_array($currentPage, ['invoices.php', 'invoice-details.php', 'create-invoice.php']) ? 'active' : ''; ?>">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span class="sidebar-menu-text">Factures</span>
                </a>
            </div>
            
            <div class="sidebar-menu-category">Support</div>
            
            <div class="sidebar-menu-item">
                <a href="tickets.php" class="sidebar-menu-link <?php echo in_array($currentPage, ['tickets.php', 'view-ticket.php']) ? 'active' : ''; ?>">
                    <i class="fas fa-ticket-alt"></i>
                    <span class="sidebar-menu-text">Tickets Support</span>
                    <?php if (isset($openTickets) && $openTickets > 0): ?>
                    <span class="sidebar-badge"><?php echo $openTickets; ?></span>
                    <?php endif; ?>
                </a>
            </div>
            
            <div class="sidebar-menu-category">Activité</div>
            
            <div class="sidebar-menu-item">
                <a href="activity-log.php" class="sidebar-menu-link <?php echo $currentPage === 'activity-log.php' ? 'active' : ''; ?>">
                    <i class="fas fa-history"></i>
                    <span class="sidebar-menu-text">Journal d'Activité</span>
                </a>
            </div>
            
            <div class="sidebar-menu-category">Paramètres</div>
            
            <div class="sidebar-menu-item">
                <a href="profile.php" class="sidebar-menu-link <?php echo $currentPage === 'profile.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user-cog"></i>
                    <span class="sidebar-menu-text">Profil</span>
                </a>
            </div>
            
            <div class="sidebar-menu-item">
                <a href="settings.php" class="sidebar-menu-link <?php echo $currentPage === 'settings.php' ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i>
                    <span class="sidebar-menu-text">Paramètres Système</span>
                </a>
            </div>
            
            <div class="sidebar-menu-item">
                <a href="../logout.php" class="sidebar-menu-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="sidebar-menu-text">Déconnexion</span>
                </a>
            </div>
        </div>
        
        <!-- Sidebar Footer -->
        <div class="sidebar-footer">
            <div class="dark-mode-toggle">
                <label class="switch">
                    <input type="checkbox" id="darkModeToggle">
                    <span class="slider round"></span>
                </label>
                <span class="toggle-label">Mode Sombre</span>
            </div>
        </div>
    </aside>
    
    <!-- Main Content -->
    <main class="admin-content">
        <!-- Top Navigation -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
            <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                <i class="fa fa-bars"></i>
            </button>
            
            <!-- Page title -->
            <h1 class="page-title"><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Tableau de Bord'; ?></h1>
            
            <!-- Topbar Navbar -->
            <ul class="navbar-nav ml-auto">
                <!-- Alerts -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell fa-fw"></i>
                        <?php if (!empty($systemNotifications)): ?>
                        <span class="badge badge-danger badge-counter"><?php echo count($systemNotifications); ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="alertsDropdown">
                        <h6 class="dropdown-header">Centre de Notifications</h6>
                        
                        <?php if (!empty($systemNotifications)): ?>
                            <?php foreach ($systemNotifications as $notification): ?>
                                <?php if ($notification['type'] === 'tickets'): ?>
                                <a class="dropdown-item d-flex align-items-center" href="tickets.php">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-primary">
                                            <i class="fas fa-ticket-alt text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">Aujourd'hui</div>
                                        <span><?php echo $notification['count']; ?> nouveaux tickets support</span>
                                    </div>
                                </a>
                                <?php elseif ($notification['type'] === 'payments'): ?>
                                <a class="dropdown-item d-flex align-items-center" href="payments.php">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-success">
                                            <i class="fas fa-credit-card text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">Aujourd'hui</div>
                                        <span><?php echo $notification['count']; ?> paiements en attente</span>
                                    </div>
                                </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <a class="dropdown-item d-flex align-items-center" href="#">
                                <div>Aucune nouvelle notification</div>
                            </a>
                        <?php endif; ?>
                        
                        <a class="dropdown-item text-center small text-gray-500" href="activity-log.php">Voir Toutes les Notifications</a>
                    </div>
                </li>
                
                <div class="topbar-divider d-none d-sm-block"></div>
                
                <!-- User Information -->
                <li class="nav-item dropdown no-arrow">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                            <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                        </span>
                        <img class="img-profile rounded-circle" src="<?php echo htmlspecialchars($user['profile_image'] ?: '../images/default-avatar.png'); ?>">
                    </a>
                    <!-- Dropdown - User Information -->
                    <div class="dropdown-menu dropdown-menu-end shadow animated--grow-in" aria-labelledby="userDropdown">
                        <a class="dropdown-item" href="profile.php">
                            <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                            Profil
                        </a>
                        <a class="dropdown-item" href="settings.php">
                            <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                            Paramètres
                        </a>
                        <a class="dropdown-item" href="activity-log.php">
                            <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                            Journal d'Activité
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="../logout.php">
                            <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                            Déconnexion
                        </a>
                    </div>
                </li>
            </ul>
        </nav>
        
        <!-- Begin Page Content -->
        <div class="container-fluid main-container">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo $_SESSION['success']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo $_SESSION['error']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <?php if (isset($content)) echo $content; ?>
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="admin-footer mt-auto">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; <?php echo date('Y'); ?> CTB Property Management</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>Version 1.0.0</p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery (required for some admin features) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Admin Core JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile Sidebar Toggle
            const sidebarToggle = document.querySelector('.mobile-sidebar-toggle');
            const sidebar = document.querySelector('.admin-sidebar');
            const sidebarOverlay = document.querySelector('.sidebar-overlay');
            const body = document.body;
            
            function toggleSidebar() {
                sidebar.classList.toggle('active');
                sidebarOverlay.classList.toggle('active');
                body.classList.toggle('sidebar-open');
            }
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', toggleSidebar);
            }
            
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', toggleSidebar);
            }
            
            // Dark Mode Toggle
            const darkModeToggle = document.getElementById('darkModeToggle');
            
            if (darkModeToggle) {
                // Set initial state based on localStorage or system preference
                const savedDarkMode = localStorage.getItem('dark_mode');
                if (savedDarkMode === 'true') {
                    document.documentElement.setAttribute('data-theme', 'dark');
                    darkModeToggle.checked = true;
                }
                
                darkModeToggle.addEventListener('change', function() {
                    const isDarkMode = this.checked;
                    document.documentElement.setAttribute('data-theme', isDarkMode ? 'dark' : 'light');
                    localStorage.setItem('dark_mode', isDarkMode);
                });
            }
            
            // Initialize tooltips
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
        });
    </script>
    
    <!-- Page specific JavaScript -->
    <?php if (isset($pageJs)): ?>
    <script src="<?php echo $pageJs; ?>"></script>
    <?php endif; ?>
    
    <?php if (isset($inlineJs)): ?>
    <script>
    <?php echo $inlineJs; ?>
    </script>
    <?php endif; ?>
</body>
</html> 
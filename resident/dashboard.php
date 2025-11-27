<?php
// Start session
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/role_access.php';
require_once '../includes/language.php';

// Define translation function directly to prevent errors
if (!function_exists('__')) {
    function __($text) {
        // Get current language from session
        $lang = isset($_SESSION['language']) ? $_SESSION['language'] : 'en_US';
        
        // Load translations from JSON file
        static $translations = [];
        
        if (empty($translations[$lang])) {
            $json_file = __DIR__ . '/locale/' . $lang . '/translations.json';
            
            if (file_exists($json_file)) {
                $json_content = file_get_contents($json_file);
                $translations[$lang] = json_decode($json_content, true) ?: [];
            } else {
                $translations[$lang] = [];
            }
        }
        
        // Return translation or original text
        return isset($translations[$lang][$text]) ? $translations[$lang][$text] : $text;
    }
}

// Simple language helper function
if (!function_exists('get_current_language')) {
    function get_current_language() {
        return isset($_SESSION['language']) ? $_SESSION['language'] : 'en_US';
    }
}

// Handle language change
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en_US', 'fr_FR', 'es_ES'])) {
    $_SESSION['language'] = $_GET['lang'];
    
    // Redirect to remove lang parameter
    $redirect_url = strtok($_SERVER['REQUEST_URI'], '?');
    $query_params = $_GET;
    unset($query_params['lang']);
    
    if (!empty($query_params)) {
        $redirect_url .= '?' . http_build_query($query_params);
    }
    
    header("Location: $redirect_url");
    exit;
}

// Check if user is logged in and is a resident
requireAnyRole(['resident']);

// Get current user ID
$current_user_id = $_SESSION['user_id'] ?? null;

if (!$current_user_id) {
    $_SESSION['error'] = __("User session not found. Please log in again.");
    header("Location: ../login.php");
    exit();
}

// Get count statistics
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Count properties for current user only
    $stmt = $db->prepare("SELECT type, COUNT(*) as count FROM properties WHERE user_id = :user_id GROUP BY type");
    $stmt->bindParam(':user_id', $current_user_id, PDO::PARAM_INT);
    $stmt->execute();
    $properties_by_type = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total_properties = 0;
    $total_apartments = 0;
    $total_parking = 0;
    
    foreach ($properties_by_type as $type_data) {
        if ($type_data['type'] == 'apartment') {
            $total_apartments = $type_data['count'];
        } elseif ($type_data['type'] == 'parking') {
            $total_parking = $type_data['count'];
        }
        $total_properties += $type_data['count'];
    }
    
    // Count tickets by status for current user only
    $stmt = $db->prepare("SELECT status, COUNT(*) as count FROM tickets WHERE user_id = :user_id GROUP BY status");
    $stmt->bindParam(':user_id', $current_user_id, PDO::PARAM_INT);
    $stmt->execute();
    $tickets_by_status = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total_tickets = 0;
    $open_tickets = 0;
    $in_progress_tickets = 0;
    $closed_tickets = 0;
    $reopened_tickets = 0;
    
    foreach ($tickets_by_status as $status_data) {
        if ($status_data['status'] == 'open') {
            $open_tickets = $status_data['count'];
        } elseif ($status_data['status'] == 'in_progress') {
            $in_progress_tickets = $status_data['count'];
        } elseif ($status_data['status'] == 'closed') {
            $closed_tickets = $status_data['count'];
        } elseif ($status_data['status'] == 'reopened') {
            $reopened_tickets = $status_data['count'];
        }
        $total_tickets += $status_data['count'];
    }
    
    // Get total payments for current user's properties
    $stmt = $db->prepare("
        SELECT SUM(p.amount) as total 
        FROM payments p
        INNER JOIN properties pr ON p.property_id = pr.id
        WHERE pr.user_id = :user_id
    ");
    $stmt->bindParam(':user_id', $current_user_id, PDO::PARAM_INT);
    $stmt->execute();
    $total_payments = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?: 0;
    
    // Get payments by status for current user's properties
    $stmt = $db->prepare("
        SELECT p.status, SUM(p.amount) as total 
        FROM payments p
        INNER JOIN properties pr ON p.property_id = pr.id
        WHERE pr.user_id = :user_id
        GROUP BY p.status
    ");
    $stmt->bindParam(':user_id', $current_user_id, PDO::PARAM_INT);
    $stmt->execute();
    $payments_by_status = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $paid_amount = 0;
    $pending_amount = 0;
    
    foreach ($payments_by_status as $payment_data) {
        if ($payment_data['status'] == 'paid') {
            $paid_amount = $payment_data['total'];
        } elseif ($payment_data['status'] == 'pending') {
            $pending_amount = $payment_data['total'];
        }
    }
    
    // Get maintenance data (all maintenance records - residents can view all)
    $stmt = $db->prepare("
        SELECT status, COUNT(*) as count 
        FROM maintenance 
        GROUP BY status
    ");
    $stmt->execute();
    $maintenance_by_status = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total_maintenance = 0;
    $scheduled_maintenance = 0;
    $in_progress_maintenance = 0;
    $completed_maintenance = 0;
    
    foreach ($maintenance_by_status as $status_data) {
        if ($status_data['status'] == 'scheduled') {
            $scheduled_maintenance = $status_data['count'];
        } elseif ($status_data['status'] == 'in_progress') {
            $in_progress_maintenance = $status_data['count'];
        } elseif ($status_data['status'] == 'completed') {
            $completed_maintenance = $status_data['count'];
        }
        $total_maintenance += $status_data['count'];
    }
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    $total_properties = $total_tickets = $total_payments = $total_maintenance = 0;
    $total_apartments = $total_parking = 0;
    $open_tickets = $in_progress_tickets = $closed_tickets = 0;
    $paid_amount = $pending_amount = 0;
    $scheduled_maintenance = $in_progress_maintenance = $completed_maintenance = 0;
}

// Page title
$page_title = __("Dashboard");

// Available languages
$available_languages = [
    'en_US' => 'English',
    'fr_FR' => 'Français',
    'es_ES' => 'Español',
];

// Current language
$current_language = get_current_language();
$current_language_name = $available_languages[$current_language] ?? $available_languages['en_US'];
?>

<!DOCTYPE html>
<html lang="<?php echo substr(get_current_language(), 0, 2); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Community Trust Bank</title>
    <!-- Favicon -->
    <?php favicon_links(); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin-style.css">
    <link rel="stylesheet" href="css/dashboard-style.css">
    <style>
    /* Enhanced Card Styling */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 24px;
    }
    
    .stat-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 28px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(0, 0, 0, 0.06);
        display: flex;
        flex-direction: row;
        align-items: flex-start;
        gap: 20px;
        position: relative;
        overflow: hidden;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: linear-gradient(180deg, var(--card-accent, #4361ee) 0%, var(--card-accent-dark, #3a56e4) 100%);
    }
    
    .stat-card:nth-child(1)::before {
        background: linear-gradient(180deg, #25c685 0%, #13b571 100%);
    }
    
    .stat-card:nth-child(2)::before {
        background: linear-gradient(180deg, #f8b830 0%, #f6a819 100%);
    }
    
    .stat-card:nth-child(3)::before {
        background: linear-gradient(180deg, #4cc9f0 0%, #39b8df 100%);
    }
    
    .stat-card:nth-child(4)::before {
        background: linear-gradient(180deg, #4361ee 0%, #3a56e4 100%);
    }
    
    [data-theme="dark"] .stat-card {
        background: #2d3748;
        border-color: rgba(255, 255, 255, 0.1);
    }
    
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    }
    
    /* Icon Styling */
    .stat-icon {
        width: 64px;
        height: 64px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease;
    }
    
    .stat-card:hover .stat-icon {
        transform: scale(1.05);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
    }
    
    .stat-icon.properties {
        background: linear-gradient(135deg, #25c685 0%, #13b571 100%);
    }
    
    .stat-icon.tickets {
        background: linear-gradient(135deg, #f8b830 0%, #f6a819 100%);
    }
    
    .stat-icon.payments {
        background: linear-gradient(135deg, #4cc9f0 0%, #39b8df 100%);
    }
    
    .stat-icon.maintenance {
        background: linear-gradient(135deg, #4361ee 0%, #3a56e4 100%);
    }
    
    .stat-icon i {
        font-size: 28px;
        color: white;
    }
    
    /* Stat Details */
    .stat-details {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    
    .stat-details h3 {
        font-size: 14px;
        font-weight: 600;
        color: #718096;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-block;
    }
    
    [data-theme="dark"] .stat-details h3 {
        color: #a0aec0;
    }
    
    .stat-header-row {
        display: flex;
        align-items: baseline;
        gap: 12px;
        margin-bottom: 4px;
    }
    
    .stat-number {
        font-size: 36px;
        font-weight: 700;
        color: #2d3748;
        line-height: 1.2;
        margin: 0;
    }
    
    [data-theme="dark"] .stat-number {
        color: #f8f9fc;
    }
    
    /* Breakdown Styling */
    .stat-breakdown {
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 8px;
    }
    
    .stat-breakdown span {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: #718096;
        padding: 6px 10px;
        background: rgba(0, 0, 0, 0.03);
        border-radius: 6px;
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    
    [data-theme="dark"] .stat-breakdown span {
        background: rgba(255, 255, 255, 0.05);
        color: #a0aec0;
    }
    
    .stat-breakdown span:hover {
        background: rgba(0, 0, 0, 0.06);
        transform: translateX(2px);
    }
    
    [data-theme="dark"] .stat-breakdown span:hover {
        background: rgba(255, 255, 255, 0.08);
    }
    
    .stat-breakdown span i {
        width: 16px;
        opacity: 0.8;
    }
    
    .stat-card:nth-child(1) .stat-breakdown span i {
        color: #25c685;
    }
    
    .stat-card:nth-child(2) .stat-breakdown span i {
        color: #f8b830;
    }
    
    .stat-card:nth-child(3) .stat-breakdown span i {
        color: #4cc9f0;
    }
    
    .stat-card:nth-child(4) .stat-breakdown span i {
        color: #4361ee;
    }
    
    /* View Link Styling */
    .view-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 16px;
        background: rgba(67, 97, 238, 0.08);
        color: #4361ee;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        transition: all 0.3s ease;
        margin-top: 4px;
        border: 1px solid rgba(67, 97, 238, 0.15);
    }
    
    [data-theme="dark"] .view-link {
        background: rgba(78, 115, 223, 0.15);
        color: #9bb5ff;
        border-color: rgba(78, 115, 223, 0.25);
    }
    
    .view-link:hover {
        background: #4361ee;
        color: white;
        transform: translateX(4px);
        box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
        border-color: #4361ee;
    }
    
    [data-theme="dark"] .view-link:hover {
        background: #4e73df;
        box-shadow: 0 4px 12px rgba(78, 115, 223, 0.4);
    }
    
    .view-link i {
        transition: transform 0.3s ease;
    }
    
    .view-link:hover i {
        transform: translateX(2px);
    }
    
    /* Responsive grid for 2x2 layout */
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr !important;
        }
    }
    /* Language Switcher Styles */
    .language-switcher {
        position: relative;
        display: inline-block;
        margin-left: 15px;
    }
    .language-switcher .dropdown-toggle {
        background-color: var(--success-color);
        color: white;
        border: 1px solid var(--success-color);
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        font-weight: 500;
        font-size: 14px;
        padding: 10px 16px;
        border-radius: 6px;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .language-switcher .dropdown-toggle:hover {
        background-color: #19b777;
        border-color: #19b777;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    .language-switcher .dropdown-toggle:active {
        transform: translateY(1px);
    }
    .language-switcher .dropdown-toggle i {
        font-size: 14px;
    }
    .language-switcher .dropdown-menu {
        position: absolute;
        right: 0;
        top: calc(100% + 10px);
        min-width: 150px;
        background: white;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border-radius: 8px;
        padding: 8px 0;
        display: none;
        z-index: 1000;
        animation: fadeIn 0.2s ease;
        transform-origin: top right;
    }
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    .language-switcher .dropdown-menu:before {
        content: '';
        position: absolute;
        top: -6px;
        right: 20px;
        width: 12px;
        height: 12px;
        background: white;
        transform: rotate(45deg);
        border-left: 1px solid rgba(0,0,0,0.05);
        border-top: 1px solid rgba(0,0,0,0.05);
    }
    .language-switcher .dropdown-menu.show {
        display: block;
    }
    .language-switcher .dropdown-item {
        display: flex;
        align-items: center;
        padding: 10px 15px;
        color: var(--text-primary);
        text-decoration: none;
        transition: all 0.2s;
        position: relative;
    }
    .language-switcher .dropdown-item:after {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 0;
        background-color: var(--success-color);
        transition: width 0.2s ease;
    }
    .language-switcher .dropdown-item:hover {
        background-color: rgba(28, 200, 138, 0.05);
        padding-left: 20px;
    }
    .language-switcher .dropdown-item:hover:after {
        width: 4px;
    }
    /* Dark mode styles */
    [data-theme="dark"] .language-switcher .dropdown-menu {
        background: #2d3748;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    [data-theme="dark"] .language-switcher .dropdown-menu:before {
        background: #2d3748;
        border-color: rgba(255,255,255,0.05);
    }
    [data-theme="dark"] .language-switcher .dropdown-item {
        color: #e2e8f0;
    }
    [data-theme="dark"] .language-switcher .dropdown-item:hover {
        background-color: rgba(28, 200, 138, 0.1);
    }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/admin-sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <div class="dashboard-content">
                <div class="page-header">
                    <h1><?php echo __("Dashboard Overview"); ?></h1>
                    <div class="header-actions" style="display: flex; align-items: center;">
                        <!-- Language Switcher -->
                        <div class="language-switcher">
                            <button class="dropdown-toggle" type="button" id="languageDropdown" onclick="toggleLanguageDropdown()">
                                <i class="fas fa-globe"></i>
                                <?php echo $current_language_name; ?>
                            </button>
                            <div class="dropdown-menu" id="languageDropdownMenu">
                                <?php foreach ($available_languages as $lang_code => $lang_name): ?>
                                    <?php if ($lang_code !== $current_language): ?>
                                        <a class="dropdown-item" href="?lang=<?php echo $lang_code; ?>">
                                            <?php echo $lang_name; ?>
                                        </a>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards (2x2 Grid) -->
                <div class="stats-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                    <!-- 1. My Properties -->
                    <div class="stat-card">
                        <div class="stat-icon properties">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-header-row">
                                <h3><?php echo __("My Properties"); ?></h3>
                            <p class="stat-number"><?php echo number_format($total_properties); ?></p>
                            </div>
                            <div class="stat-breakdown">
                                <span><i class="fas fa-home"></i> <?php echo __("Apartments"); ?>: <?php echo number_format($total_apartments); ?></span>
                                <span><i class="fas fa-car"></i> <?php echo __("Parking"); ?>: <?php echo number_format($total_parking); ?></span>
                            </div>
                            <a href="properties.php" class="view-link">
                                <i class="fas fa-arrow-right"></i> <?php echo __("View All"); ?>
                            </a>
                        </div>
                    </div>

                    <!-- 2. My Tickets -->
                    <div class="stat-card">
                        <div class="stat-icon tickets">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-header-row">
                                <h3><?php echo __("My Tickets"); ?></h3>
                            <p class="stat-number"><?php echo number_format($total_tickets); ?></p>
                            </div>
                            <div class="stat-breakdown">
                                <span><i class="fas fa-exclamation-circle"></i> <?php echo __("Open"); ?>: <?php echo number_format($open_tickets); ?></span>
                                <span><i class="fas fa-clock"></i> <?php echo __("In Progress"); ?>: <?php echo number_format($in_progress_tickets); ?></span>
                                <span><i class="fas fa-check"></i> <?php echo __("Closed"); ?>: <?php echo number_format($closed_tickets); ?></span>
                            </div>
                            <a href="tickets.php" class="view-link">
                                <i class="fas fa-arrow-right"></i> <?php echo __("View All"); ?>
                            </a>
                        </div>
                    </div>

                    <!-- 3. My Payments -->
                    <div class="stat-card">
                        <div class="stat-icon payments">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-header-row">
                                <h3><?php echo __("My Payments"); ?></h3>
                            <p class="stat-number"><?php echo format_currency($total_payments); ?></p>
                            </div>
                            <div class="stat-breakdown">
                                <span><i class="fas fa-check-circle"></i> <?php echo __("Paid"); ?>: <?php echo format_currency($paid_amount); ?></span>
                                <span><i class="fas fa-clock"></i> <?php echo __("Pending"); ?>: <?php echo format_currency($pending_amount); ?></span>
                            </div>
                            <a href="payments.php" class="view-link">
                                <i class="fas fa-arrow-right"></i> <?php echo __("View All"); ?>
                            </a>
                        </div>
                    </div>

                    <!-- 4. Maintenance Overview -->
                    <div class="stat-card">
                        <div class="stat-icon maintenance">
                                <i class="fas fa-tools"></i>
                        </div>
                        <div class="stat-details">
                            <div class="stat-header-row">
                                <h3><?php echo __("Maintenance Overview"); ?></h3>
                                <p class="stat-number"><?php echo number_format($total_maintenance); ?></p>
                            </div>
                            <div class="stat-breakdown">
                                <span><i class="fas fa-calendar"></i> <?php echo __("Scheduled"); ?>: <?php echo number_format($scheduled_maintenance); ?></span>
                                <span><i class="fas fa-tools"></i> <?php echo __("In Progress"); ?>: <?php echo number_format($in_progress_maintenance); ?></span>
                                <span><i class="fas fa-check"></i> <?php echo __("Completed"); ?>: <?php echo number_format($completed_maintenance); ?></span>
                            </div>
                            <a href="maintenance.php" class="view-link">
                                <i class="fas fa-arrow-right"></i> <?php echo __("View All"); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="js/dark-mode.js"></script>
    <script src="js/dashboard.js"></script>
    
    <!-- Language Switcher Script -->
    <script>
    function toggleLanguageDropdown() {
        document.getElementById('languageDropdownMenu').classList.toggle('show');
    }
    
    // Close the dropdown if clicked outside
    window.onclick = function(event) {
        if (!event.target.matches('.dropdown-toggle')) {
            var dropdowns = document.getElementsByClassName("dropdown-menu");
            for (var i = 0; i < dropdowns.length; i++) {
                var openDropdown = dropdowns[i];
                if (openDropdown.classList.contains('show')) {
                    openDropdown.classList.remove('show');
                }
            }
        }
    }
    </script>
</body>
</html> 
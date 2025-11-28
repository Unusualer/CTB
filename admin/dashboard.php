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

// Check if user is logged in and is an admin
requireRole('admin');

// Get count statistics
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Count users by role
    $stmt = $db->prepare("SELECT role, COUNT(*) as count FROM users GROUP BY role");
    $stmt->execute();
    $users_by_role = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total_users = 0;
    $total_admins = 0;
    $total_managers = 0;
    $total_residents = 0;
    
    foreach ($users_by_role as $role_data) {
        if ($role_data['role'] == 'admin') {
            $total_admins = $role_data['count'];
        } elseif ($role_data['role'] == 'manager') {
            $total_managers = $role_data['count'];
        } elseif ($role_data['role'] == 'resident') {
            $total_residents = $role_data['count'];
        }
        $total_users += $role_data['count'];
    }
    
    // Count properties
    $stmt = $db->prepare("SELECT type, COUNT(*) as count FROM properties GROUP BY type");
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
    
    // Count occupied properties
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM properties WHERE user_id IS NOT NULL");
    $stmt->execute();
    $occupied_properties = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Count tickets by status
    $stmt = $db->prepare("SELECT status, COUNT(*) as count FROM tickets GROUP BY status");
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
    
    // Get total payments 
    $stmt = $db->prepare("SELECT SUM(amount) as total FROM payments");
    $stmt->execute();
    $total_payments = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?: 0;
    
    // Get payments by status
    $stmt = $db->prepare("SELECT status, SUM(amount) as total FROM payments GROUP BY status");
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
    
    // Get monthly payment data for chart (last 6 months)
    $stmt = $db->prepare("
        SELECT 
            DATE_FORMAT(month, '%Y-%m') as month,
            SUM(amount) as total_amount
        FROM 
            payments
        WHERE 
            month >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY 
            DATE_FORMAT(month, '%Y-%m')
        ORDER BY 
            month ASC
    ");
    $stmt->execute();
    $payment_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get recent activity (last 10 entries)
    $stmt = $db->prepare("
        SELECT a.*, u.name
        FROM activity_log a
        LEFT JOIN users u ON a.user_id = u.id
        ORDER BY a.created_at DESC
        LIMIT 10
    ");
    $stmt->execute();
    $recent_activity = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get recent tickets (5 most recent)
    $stmt = $db->prepare("
        SELECT t.*, u.name
        FROM tickets t
        LEFT JOIN users u ON t.user_id = u.id
        ORDER BY t.created_at DESC
        LIMIT 5
    ");
    $stmt->execute();
    $recent_tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get maintenance data
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
    $total_users = $total_properties = $total_tickets = $total_payments = 0;
    $recent_activity = $recent_tickets = [];
    $payment_data = [];
}

// Page title
$page_title = __("Admin Dashboard");

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
    <link rel="stylesheet" href="css/colorful-theme.css">
    <style>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                        <button class="btn btn-primary">
                            <i class="fas fa-download"></i>
                            <?php echo __("Generate Report"); ?>
                        </button>
                        
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

                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon users">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo __("Total Users"); ?></h3>
                            <p class="stat-number"><?php echo number_format($total_users); ?></p>
                            <div class="stat-breakdown">
                                <span><i class="fas fa-user-shield"></i> <?php echo __("Administrators"); ?>: <?php echo number_format($total_admins ?? 0); ?></span>
                                <span><i class="fas fa-user-tie"></i> <?php echo __("Managers"); ?>: <?php echo number_format($total_managers); ?></span>
                                <span><i class="fas fa-user"></i> <?php echo __("Residents"); ?>: <?php echo number_format($total_residents); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon properties">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo __("Properties"); ?></h3>
                            <p class="stat-number"><?php echo number_format($total_properties); ?></p>
                            <div class="stat-breakdown">
                                <span><i class="fas fa-home"></i> <?php echo __("Apartments"); ?>: <?php echo number_format($total_apartments); ?></span>
                                <span><i class="fas fa-car"></i> <?php echo __("Parking"); ?>: <?php echo number_format($total_parking); ?></span>
                                <span><i class="fas fa-check-circle"></i> <?php echo __("Occupied"); ?>: <?php echo number_format($occupied_properties); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon tickets">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo __("Tickets"); ?></h3>
                            <p class="stat-number"><?php echo number_format($total_tickets); ?></p>
                            <div class="stat-breakdown">
                                <span><i class="fas fa-exclamation-circle"></i> <?php echo __("Open"); ?>: <?php echo number_format($open_tickets); ?></span>
                                <span><i class="fas fa-clock"></i> <?php echo __("In Progress"); ?>: <?php echo number_format($in_progress_tickets); ?></span>
                                <span><i class="fas fa-check"></i> <?php echo __("Closed"); ?>: <?php echo number_format($closed_tickets); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon payments">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo __("Total Payments"); ?></h3>
                            <p class="stat-number"><?php echo format_currency($total_payments); ?></p>
                            <div class="stat-breakdown">
                                <span><i class="fas fa-check-circle"></i> <?php echo __("Paid"); ?>: <?php echo format_currency($paid_amount); ?></span>
                                <span><i class="fas fa-clock"></i> <?php echo __("Pending"); ?>: <?php echo format_currency($pending_amount); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="charts-grid">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3><i class="fas fa-chart-line"></i> <?php echo __("Monthly Payment Overview"); ?></h3>
                            <div class="chart-actions">
                                <a href="payments.php" class="btn-icon" title="<?php echo __("View All Payments"); ?>"><i class="fas fa-external-link-alt"></i></a>
                            </div>
                        </div>
                        <div class="chart-body">
                            <canvas id="monthlyPaymentsChart"></canvas>
                        </div>
                    </div>

                    <div class="chart-card">
                        <div class="chart-header">
                            <h3><i class="fas fa-chart-pie"></i> <?php echo __("Ticket Status Distribution"); ?></h3>
                            <div class="chart-actions">
                                <a href="tickets.php" class="btn-icon" title="<?php echo __("View All Tickets"); ?>"><i class="fas fa-external-link-alt"></i></a>
                            </div>
                        </div>
                        <div class="chart-body">
                            <canvas id="ticketStatusChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity and Tickets -->
                <div class="content-grid">
                    <div class="content-card">
                        <div class="card-header">
                            <h3><i class="fas fa-history"></i> <?php echo __("Recent Activity"); ?></h3>
                            <a href="activity-log.php" class="view-all"><?php echo __("View All"); ?></a>
                        </div>
                        <div class="activity-list dashboard-activity">
                            <?php if (!empty($recent_activity)): ?>
                                <?php foreach ($recent_activity as $activity): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon <?php echo $activity['action']; ?>">
                                            <?php
                                            $icon_class = '';
                                            switch ($activity['action']) {
                                                case 'create':
                                                case 'add':
                                                    $icon_class = 'fas fa-plus';
                                                    break;
                                                case 'payment':
                                                    $icon_class = 'fas fa-credit-card';
                                                    break;
                                                case 'update':
                                                    $icon_class = 'fas fa-edit';
                                                    break;
                                                case 'delete':
                                                    $icon_class = 'fas fa-trash';
                                                    break;
                                                case 'login':
                                                    $icon_class = 'fas fa-sign-in-alt';
                                                    break;
                                                default:
                                                    $icon_class = 'fas fa-history';
                                            }
                                            ?>
                                            <i class="<?php echo $icon_class; ?>"></i>
                                        </div>
                                        <div class="activity-details">
                                            <p class="activity-text">
                                                <strong><?php echo htmlspecialchars($activity['name']); ?></strong> 
                                                <?php 
                                                    $action_verb = $activity['action'];
                                                    switch ($action_verb) {
                                                        case 'payment':
                                                            echo __("made a payment for");
                                                            break;
                                                        case 'login':
                                                            echo __("logged in");
                                                            break;
                                                        case 'create':
                                                            echo __("created");
                                                            break;
                                                        case 'update':
                                                            echo __("updated");
                                                            break;
                                                        case 'delete':
                                                            echo __("deleted");
                                                            break;
                                                        default:
                                                            echo ucfirst($action_verb);
                                                    }
                                                ?>
                                                <?php echo strtolower($activity['entity_type']); ?>
                                                <?php if (!empty($activity['entity_id'])): ?>
                                                    #<?php echo $activity['entity_id']; ?>
                                                <?php endif; ?>
                                                <?php if (!empty($activity['details'])): ?>
                                                    - <?php echo __($activity['details']); ?>
                                                <?php endif; ?>
                                            </p>
                                            <span class="activity-time">
                                                <?php echo time_elapsed_string($activity['created_at']); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-history"></i>
                                    <p><?php echo __("No recent activity"); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="content-card">
                        <div class="card-header">
                            <h3><i class="fas fa-ticket-alt"></i> <?php echo __("Recent Tickets"); ?></h3>
                            <a href="tickets.php" class="view-all"><?php echo __("View All"); ?></a>
                        </div>
                        <div class="tickets-list dashboard-tickets">
                            <?php if (!empty($recent_tickets)): ?>
                                <?php foreach ($recent_tickets as $ticket): ?>
                                    <div class="ticket-item">
                                        <div class="ticket-status <?php echo $ticket['status']; ?>">
                                            <?php 
                                                $status_text = $ticket['status'];
                                                switch ($status_text) {
                                                    case 'open':
                                                        echo __("Open");
                                                        break;
                                                    case 'in_progress':
                                                        echo __("In Progress");
                                                        break;
                                                    case 'closed':
                                                        echo __("Closed");
                                                        break;
                                                    case 'reopened':
                                                        echo __("Reopened");
                                                        break;
                                                    default:
                                                        echo ucfirst($status_text);
                                                }
                                            ?>
                                        </div>
                                        <div class="ticket-details">
                                            <h4><?php echo htmlspecialchars($ticket['subject']); ?></h4>
                                            <p class="ticket-info">
                                                <span class="ticket-user">
                                                    <i class="fas fa-user"></i>
                                                    <?php echo htmlspecialchars($ticket['name']); ?>
                                                </span>
                                            </p>
                                            <span class="ticket-time">
                                                <?php echo time_elapsed_string($ticket['created_at']); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-ticket-alt"></i>
                                    <p><?php echo __("No recent tickets"); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Maintenance Section -->
                <div class="content-card maintenance-card">
                    <div class="card-header">
                        <h3><i class="fas fa-tools"></i> <?php echo __("Maintenance Overview"); ?></h3>
                        <a href="maintenance.php" class="view-all"><?php echo __("View All"); ?></a>
                    </div>
                    <div class="maintenance-stats">
                        <div class="maintenance-stat">
                            <div class="stat-circle scheduled">
                                <i class="fas fa-calendar"></i>
                                <span class="count"><?php echo $scheduled_maintenance; ?></span>
                            </div>
                            <p><?php echo __("Scheduled"); ?></p>
                        </div>
                        <div class="maintenance-stat">
                            <div class="stat-circle in-progress">
                                <i class="fas fa-tools"></i>
                                <span class="count"><?php echo $in_progress_maintenance; ?></span>
                            </div>
                            <p><?php echo __("In Progress"); ?></p>
                        </div>
                        <div class="maintenance-stat">
                            <div class="stat-circle completed">
                                <i class="fas fa-check"></i>
                                <span class="count"><?php echo $completed_maintenance; ?></span>
                            </div>
                            <p><?php echo __("Completed"); ?></p>
                        </div>
                        <div class="maintenance-stat">
                            <div class="stat-circle total">
                                <i class="fas fa-clipboard-list"></i>
                                <span class="count"><?php echo $total_maintenance; ?></span>
                            </div>
                            <p><?php echo __("Total"); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
    // Set new default font family and font color to mimic Bootstrap's default styling
    Chart.defaults.font.family = 'Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';
    Chart.defaults.color = '#858796';

    document.addEventListener('DOMContentLoaded', function() {
        // Get theme from localStorage or default to light
        const currentTheme = localStorage.getItem('darkMode') === 'enabled' ? 'dark' : 'light';
        
        // Monthly Payments Chart
        var monthlyPaymentsChart = document.getElementById("monthlyPaymentsChart");
        var paymentLabels = [];
        var paymentData = [];

        <?php foreach ($payment_data as $data): ?>
            paymentLabels.push("<?php echo format_month_year($data['month'] . '-01'); ?>");
            paymentData.push(<?php echo $data['total_amount']; ?>);
        <?php endforeach; ?>

        // Add a default dataset if none exists
        if (paymentLabels.length === 0) {
            const months = [
                "<?php echo format_month_year(date('Y-01-01')); ?>", 
                "<?php echo format_month_year(date('Y-02-01')); ?>", 
                "<?php echo format_month_year(date('Y-03-01')); ?>", 
                "<?php echo format_month_year(date('Y-04-01')); ?>", 
                "<?php echo format_month_year(date('Y-05-01')); ?>", 
                "<?php echo format_month_year(date('Y-06-01')); ?>"
            ];
            
            months.forEach(month => {
                paymentLabels.push(month);
                paymentData.push(0);
            });
        }

        if (monthlyPaymentsChart) {
            var myLineChart = new Chart(monthlyPaymentsChart, {
                type: 'line',
                data: {
                    labels: paymentLabels,
                    datasets: [{
                        label: "<?php echo __('Monthly Payment Overview'); ?>",
                        lineTension: 0.3,
                        backgroundColor: "rgba(78, 115, 223, 0.05)",
                        borderColor: "rgba(78, 115, 223, 1)",
                        pointRadius: 3,
                        pointBackgroundColor: "rgba(78, 115, 223, 1)",
                        pointBorderColor: "rgba(78, 115, 223, 1)",
                        pointHoverRadius: 3,
                        pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                        pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                        pointHitRadius: 10,
                        pointBorderWidth: 2,
                        data: paymentData,
                        fill: true
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: currentTheme === 'dark' ? '#2d3748' : '#fff',
                            titleColor: currentTheme === 'dark' ? '#fff' : '#5a5c69',
                            bodyColor: currentTheme === 'dark' ? '#e0e0e0' : '#858796',
                            borderColor: currentTheme === 'dark' ? '#4a5568' : '#e3e6f0',
                            borderWidth: 1,
                            padding: 12,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return 'Montant: ' + context.raw.toLocaleString(undefined, {
                                        style: 'currency',
                                        currency: 'MAD',
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                color: currentTheme === 'dark' ? '#e0e0e0' : '#858796'
                            }
                        },
                        y: {
                            ticks: {
                                color: currentTheme === 'dark' ? '#e0e0e0' : '#858796',
                                callback: function(value) {
                                    return value.toLocaleString(undefined, {
                                        style: 'currency',
                                        currency: 'MAD',
                                        minimumFractionDigits: 0,
                                        maximumFractionDigits: 0
                                    });
                                }
                            },
                            grid: {
                                color: currentTheme === 'dark' ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
                            }
                        }
                    }
                }
            });
        }

        // Ticket Status Chart
        var ticketStatusChart = document.getElementById("ticketStatusChart");
        var ticketStatuses = ["<?php echo __('Open'); ?>", "<?php echo __('In Progress'); ?>", "<?php echo __('Closed'); ?>"];
        var ticketData = [
            <?php echo $open_tickets; ?>,
            <?php echo $in_progress_tickets; ?>,
            <?php echo $closed_tickets; ?>
        ];
        var ticketColors = [
            'rgba(78, 115, 223, 1)', 
            'rgba(28, 200, 138, 1)', 
            'rgba(54, 185, 204, 1)'
        ];
        var ticketHoverColors = [
            'rgba(46, 89, 217, 1)', 
            'rgba(23, 166, 115, 1)', 
            'rgba(44, 159, 175, 1)'
        ];

        if (ticketStatusChart) {
            var myPieChart = new Chart(ticketStatusChart, {
                type: 'doughnut',
                data: {
                    labels: ticketStatuses,
                    datasets: [{
                        data: ticketData,
                        backgroundColor: ticketColors,
                        hoverBackgroundColor: ticketHoverColors,
                        hoverBorderColor: currentTheme === 'dark' ? '#2c3035' : '#ffffff',
                        borderWidth: 2,
                        borderColor: currentTheme === 'dark' ? '#2d3748' : '#ffffff',
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                color: currentTheme === 'dark' ? '#e0e0e0' : '#858796',
                                padding: 20,
                                font: {
                                    size: 12,
                                    weight: '500'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: currentTheme === 'dark' ? '#2d3748' : '#fff',
                            titleColor: currentTheme === 'dark' ? '#fff' : '#5a5c69',
                            bodyColor: currentTheme === 'dark' ? '#e0e0e0' : '#858796',
                            borderColor: currentTheme === 'dark' ? '#4a5568' : '#e3e6f0',
                            borderWidth: 1,
                            padding: 12,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    var label = context.label || '';
                                    var value = context.raw;
                                    var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    var percentage = Math.round((value / total) * 100);
                                    return label + ': ' + value + ' (' + percentage + '%)';
                                }
                            }
                        }
                    },
                    cutout: '70%',
                    animation: {
                        animateScale: true,
                        animateRotate: true,
                        duration: 2000,
                        easing: 'easeOutCirc'
                    }
                },
            });
        }
    });
    </script>
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
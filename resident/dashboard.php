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
    <title><?php echo $page_title; ?> - <?php echo __("Complexe Tanger Boulevard"); ?></title>
    <!-- Favicon -->
    <?php favicon_links(); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin-style.css">
    <link rel="stylesheet" href="css/dashboard-style.css">
    <style>
    /* Enhanced Card Styling with More Color */
    html:not([data-theme="dark"]) body {
        background: linear-gradient(135deg, #f0f4ff 0%, #e8f0fe 25%, #f5f9ff 50%, #f0f4ff 75%, #e8f0fe 100%);
        background-size: 400% 400%;
        animation: gradientShift 15s ease infinite;
    }
    
    @keyframes gradientShift {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 24px;
        position: relative;
        z-index: 1;
    }
    
    .stat-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8faff 100%);
        border-radius: 16px;
        padding: 28px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1), 0 0 0 1px rgba(0, 0, 0, 0.05);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 2px solid transparent;
        display: flex;
        flex-direction: row;
        align-items: flex-start;
        gap: 20px;
        position: relative;
        overflow: hidden;
        z-index: 1;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 6px;
        height: 100%;
        background: linear-gradient(180deg, var(--card-accent, #4361ee) 0%, var(--card-accent-dark, #3a56e4) 100%);
    }
    
    .stat-card:nth-child(1) {
        background: linear-gradient(135deg, #ffffff 0%, #e8f8f5 50%, #f0fdf9 100%);
        border-color: rgba(37, 198, 133, 0.3);
        box-shadow: 0 4px 20px rgba(37, 198, 133, 0.15), 0 0 0 1px rgba(37, 198, 133, 0.1);
    }
    
    .stat-card:nth-child(1)::before {
        background: linear-gradient(180deg, #25c685 0%, #13b571 100%);
        width: 6px;
        box-shadow: 0 0 20px rgba(37, 198, 133, 0.5);
    }
    
    .stat-card:nth-child(2) {
        background: linear-gradient(135deg, #ffffff 0%, #fff8e8 50%, #fffbf0 100%);
        border-color: rgba(248, 184, 48, 0.3);
        box-shadow: 0 4px 20px rgba(248, 184, 48, 0.15), 0 0 0 1px rgba(248, 184, 48, 0.1);
    }
    
    .stat-card:nth-child(2)::before {
        background: linear-gradient(180deg, #f8b830 0%, #f6a819 100%);
        width: 6px;
        box-shadow: 0 0 20px rgba(248, 184, 48, 0.5);
    }
    
    .stat-card:nth-child(3) {
        background: linear-gradient(135deg, #ffffff 0%, #e8f7fc 50%, #f0faff 100%);
        border-color: rgba(76, 201, 240, 0.3);
        box-shadow: 0 4px 20px rgba(76, 201, 240, 0.15), 0 0 0 1px rgba(76, 201, 240, 0.1);
    }
    
    .stat-card:nth-child(3)::before {
        background: linear-gradient(180deg, #4cc9f0 0%, #39b8df 100%);
        width: 6px;
        box-shadow: 0 0 20px rgba(76, 201, 240, 0.5);
    }
    
    .stat-card:nth-child(4) {
        background: linear-gradient(135deg, #ffffff 0%, #e8edff 50%, #f0f4ff 100%);
        border-color: rgba(67, 97, 238, 0.3);
        box-shadow: 0 4px 20px rgba(67, 97, 238, 0.15), 0 0 0 1px rgba(67, 97, 238, 0.1);
    }
    
    .stat-card:nth-child(4)::before {
        background: linear-gradient(180deg, #4361ee 0%, #3a56e4 100%);
        width: 6px;
        box-shadow: 0 0 20px rgba(67, 97, 238, 0.5);
    }
    
    [data-theme="dark"] .stat-card {
        background: #2d3748;
        border-color: rgba(255, 255, 255, 0.1);
    }
    
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    }
    
    .stat-card:nth-child(1):hover {
        box-shadow: 0 8px 32px rgba(37, 198, 133, 0.25), 0 0 0 2px rgba(37, 198, 133, 0.2);
    }
    
    .stat-card:nth-child(2):hover {
        box-shadow: 0 8px 32px rgba(248, 184, 48, 0.25), 0 0 0 2px rgba(248, 184, 48, 0.2);
    }
    
    .stat-card:nth-child(3):hover {
        box-shadow: 0 8px 32px rgba(76, 201, 240, 0.25), 0 0 0 2px rgba(76, 201, 240, 0.2);
    }
    
    .stat-card:nth-child(4):hover {
        box-shadow: 0 8px 32px rgba(67, 97, 238, 0.25), 0 0 0 2px rgba(67, 97, 238, 0.2);
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
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-block;
    }
    
    .stat-card:nth-child(1) .stat-details h3 {
        color: #0d7a4f;
    }
    
    .stat-card:nth-child(2) .stat-details h3 {
        color: #b8820f;
    }
    
    .stat-card:nth-child(3) .stat-details h3 {
        color: #1a8fb3;
    }
    
    .stat-card:nth-child(4) .stat-details h3 {
        color: #6d7fe8;
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
        line-height: 1.2;
        margin: 0;
    }
    
    .stat-card:nth-child(1) .stat-number {
        color: #25c685;
        text-shadow: 0 2px 8px rgba(37, 198, 133, 0.2);
    }
    
    .stat-card:nth-child(2) .stat-number {
        color: #f8b830;
        text-shadow: 0 2px 8px rgba(248, 184, 48, 0.2);
    }
    
    .stat-card:nth-child(3) .stat-number {
        color: #4cc9f0;
        text-shadow: 0 2px 8px rgba(76, 201, 240, 0.2);
    }
    
    .stat-card:nth-child(4) .stat-number {
        color: #4361ee;
        text-shadow: 0 2px 8px rgba(67, 97, 238, 0.2);
    }
    
    [data-theme="dark"] .stat-number {
        color: #f8f9fc;
        text-shadow: none;
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
        color: #4a5568;
        font-weight: 500;
        padding: 8px 12px;
        background: rgba(255, 255, 255, 0.8);
        border: 1px solid rgba(0, 0, 0, 0.08);
        border-radius: 8px;
        transition: all 0.2s ease;
        white-space: nowrap;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    .stat-card:nth-child(1) .stat-breakdown span {
        background: rgba(37, 198, 133, 0.12);
        border-color: rgba(37, 198, 133, 0.25);
        color: #0d7a4f;
    }
    
    .stat-card:nth-child(2) .stat-breakdown span {
        background: rgba(248, 184, 48, 0.12);
        border-color: rgba(248, 184, 48, 0.25);
        color: #b8820f;
    }
    
    .stat-card:nth-child(3) .stat-breakdown span {
        background: rgba(76, 201, 240, 0.12);
        border-color: rgba(76, 201, 240, 0.25);
        color: #1a8fb3;
    }
    
    .stat-card:nth-child(4) .stat-breakdown span {
        background: rgba(67, 97, 238, 0.12);
        border-color: rgba(67, 97, 238, 0.25);
        color: #6d7fe8;
    }
    
    [data-theme="dark"] .stat-breakdown span {
        background: rgba(255, 255, 255, 0.05);
        color: #a0aec0;
    }
    
    .stat-breakdown span:hover {
        transform: translateX(2px) translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .stat-card:nth-child(1) .stat-breakdown span:hover {
        background: rgba(37, 198, 133, 0.2);
        border-color: rgba(37, 198, 133, 0.4);
    }
    
    .stat-card:nth-child(2) .stat-breakdown span:hover {
        background: rgba(248, 184, 48, 0.2);
        border-color: rgba(248, 184, 48, 0.4);
    }
    
    .stat-card:nth-child(3) .stat-breakdown span:hover {
        background: rgba(76, 201, 240, 0.2);
        border-color: rgba(76, 201, 240, 0.4);
    }
    
    .stat-card:nth-child(4) .stat-breakdown span:hover {
        background: rgba(67, 97, 238, 0.2);
        border-color: rgba(67, 97, 238, 0.4);
    }
    
    [data-theme="dark"] .stat-breakdown span:hover {
        background: rgba(255, 255, 255, 0.08);
    }
    
    .stat-breakdown span i {
        width: 16px;
        opacity: 1;
        font-weight: 600;
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
        background: rgba(67, 97, 238, 0.12);
        color: #4361ee;
        text-decoration: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 13px;
        transition: all 0.3s ease;
        margin-top: 4px;
        border: 2px solid rgba(67, 97, 238, 0.2);
        box-shadow: 0 2px 6px rgba(67, 97, 238, 0.1);
    }
    
    .stat-card:nth-child(1) .view-link {
        background: rgba(37, 198, 133, 0.12);
        color: #25c685;
        border-color: rgba(37, 198, 133, 0.25);
        box-shadow: 0 2px 6px rgba(37, 198, 133, 0.15);
    }
    
    .stat-card:nth-child(2) .view-link {
        background: rgba(248, 184, 48, 0.12);
        color: #f8b830;
        border-color: rgba(248, 184, 48, 0.25);
        box-shadow: 0 2px 6px rgba(248, 184, 48, 0.15);
    }
    
    .stat-card:nth-child(3) .view-link {
        background: rgba(76, 201, 240, 0.12);
        color: #4cc9f0;
        border-color: rgba(76, 201, 240, 0.25);
        box-shadow: 0 2px 6px rgba(76, 201, 240, 0.15);
    }
    
    .stat-card:nth-child(4) .view-link {
        background: rgba(67, 97, 238, 0.12);
        color: #4361ee;
        border-color: rgba(67, 97, 238, 0.25);
        box-shadow: 0 2px 6px rgba(67, 97, 238, 0.15);
    }
    
    [data-theme="dark"] .view-link {
        background: rgba(78, 115, 223, 0.15);
        color: #9bb5ff;
        border-color: rgba(78, 115, 223, 0.25);
    }
    
    .view-link:hover {
        transform: translateX(4px) translateY(-1px);
        box-shadow: 0 6px 16px rgba(67, 97, 238, 0.35);
        border-color: #4361ee;
    }
    
    .stat-card:nth-child(1) .view-link:hover {
        background: #25c685;
        color: white;
        box-shadow: 0 6px 16px rgba(37, 198, 133, 0.4);
        border-color: #25c685;
    }
    
    .stat-card:nth-child(2) .view-link:hover {
        background: #f8b830;
        color: white;
        box-shadow: 0 6px 16px rgba(248, 184, 48, 0.4);
        border-color: #f8b830;
    }
    
    .stat-card:nth-child(3) .view-link:hover {
        background: #4cc9f0;
        color: white;
        box-shadow: 0 6px 16px rgba(76, 201, 240, 0.4);
        border-color: #4cc9f0;
    }
    
    .stat-card:nth-child(4) .view-link:hover {
        background: #4361ee;
        color: white;
        box-shadow: 0 6px 16px rgba(67, 97, 238, 0.4);
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
    
    /* Page Header Styling */
    .page-header {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(248, 250, 255, 0.9) 100%);
        padding: 24px 32px;
        border-radius: 16px;
        margin-bottom: 32px;
        box-shadow: 0 4px 16px rgba(67, 97, 238, 0.08);
        border: 1px solid rgba(67, 97, 238, 0.1);
        backdrop-filter: blur(10px);
        position: relative;
        z-index: 10;
    }
    
    .header-actions {
        position: relative;
        z-index: 11;
    }
    
    .page-header h1 {
        background: linear-gradient(135deg, #4361ee 0%, #25c685 50%, #4cc9f0 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-weight: 700;
        font-size: 28px;
        margin: 0;
    }
    
    [data-theme="dark"] .page-header {
        background: rgba(45, 55, 72, 0.8);
        border-color: rgba(255, 255, 255, 0.1);
    }
    
    [data-theme="dark"] .page-header h1 {
        background: linear-gradient(135deg, #4e73df 0%, #1cc88a 50%, #36b9cc 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-weight: 700;
    }
    
    /* Main Content Area */
    .dashboard-content {
        position: relative;
        z-index: 1;
    }
    
    /* Responsive grid for 2x2 layout */
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr !important;
        }
        
        .page-header {
            padding: 20px 24px;
        }
        
        .page-header h1 {
            font-size: 24px;
        }
    }
    /* Sidebar Colorful Enhancements */
    html:not([data-theme="dark"]) .sidebar {
        background: linear-gradient(180deg, #ffffff 0%, #f8faff 50%, #f0f4ff 100%);
        border-right: 2px solid rgba(67, 97, 238, 0.15);
        box-shadow: 4px 0 20px rgba(67, 97, 238, 0.08);
    }
    
    html:not([data-theme="dark"]) .sidebar-header {
        background: linear-gradient(135deg, rgba(67, 97, 238, 0.08) 0%, rgba(37, 198, 133, 0.05) 100%);
        border-bottom: 2px solid rgba(67, 97, 238, 0.15);
        padding: 1.5rem;
        border-radius: 0 0 12px 0;
        margin: -1.5rem -1.5rem 1.5rem -1.5rem;
    }
    
    html:not([data-theme="dark"]) .sidebar-header h2 {
        background: linear-gradient(135deg, #4361ee 0%, #25c685 50%, #4cc9f0 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-weight: 700;
    }
    
    html:not([data-theme="dark"]) .user-info {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(248, 250, 255, 0.9) 100%);
        border: 1px solid rgba(67, 97, 238, 0.15);
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 8px rgba(67, 97, 238, 0.08);
        transition: all 0.3s ease;
    }
    
    html:not([data-theme="dark"]) .user-info:hover {
        background: linear-gradient(135deg, rgba(67, 97, 238, 0.1) 0%, rgba(37, 198, 133, 0.08) 100%);
        border-color: rgba(67, 97, 238, 0.3);
        box-shadow: 0 4px 12px rgba(67, 97, 238, 0.15);
        transform: translateX(4px);
    }
    
    html:not([data-theme="dark"]) .user-avatar {
        background: linear-gradient(135deg, #4361ee 0%, #25c685 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(67, 97, 238, 0.25);
    }
    
    html:not([data-theme="dark"]) .user-details h4 {
        color: #2d3748;
        font-weight: 600;
    }
    
    html:not([data-theme="dark"]) .user-details p {
        color: #4361ee;
        font-weight: 500;
    }
    
    /* Colorful Menu Items */
    html:not([data-theme="dark"]) .sidebar-nav a {
        border-radius: 10px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    html:not([data-theme="dark"]) .sidebar-nav a::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 4px;
        height: 100%;
        background: transparent;
        transition: all 0.3s ease;
    }
    
    /* General active state override */
    html:not([data-theme="dark"]) .sidebar-nav li.active a {
        background: linear-gradient(90deg, rgba(67, 97, 238, 0.15) 0%, rgba(67, 97, 238, 0.08) 100%);
        color: #4361ee;
        border-left: 4px solid #4361ee;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(67, 97, 238, 0.15);
    }
    
    /* Dashboard menu item - Blue */
    html:not([data-theme="dark"]) .sidebar-nav a[href*="dashboard"]::before {
        background: linear-gradient(180deg, #4361ee 0%, #3a56e4 100%);
    }
    
    html:not([data-theme="dark"]) .sidebar-nav a[href*="dashboard"]:hover {
        background: linear-gradient(90deg, rgba(67, 97, 238, 0.12) 0%, rgba(67, 97, 238, 0.05) 100%);
        color: #4361ee;
        transform: translateX(4px);
    }
    
    html:not([data-theme="dark"]) .sidebar-nav li.active a[href*="dashboard"],
    html:not([data-theme="dark"]) .sidebar-nav li.active a[href*="dashboard.php"] {
        background: linear-gradient(90deg, rgba(67, 97, 238, 0.15) 0%, rgba(67, 97, 238, 0.08) 100%);
        color: #4361ee;
        border-left: 4px solid #4361ee;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(67, 97, 238, 0.15);
    }
    
    /* Properties menu item - Green */
    html:not([data-theme="dark"]) .sidebar-nav a[href*="properties"]::before {
        background: linear-gradient(180deg, #25c685 0%, #13b571 100%);
    }
    
    html:not([data-theme="dark"]) .sidebar-nav a[href*="properties"]:hover {
        background: linear-gradient(90deg, rgba(37, 198, 133, 0.12) 0%, rgba(37, 198, 133, 0.05) 100%);
        color: #25c685;
        transform: translateX(4px);
    }
    
    html:not([data-theme="dark"]) .sidebar-nav li.active a[href*="properties"],
    html:not([data-theme="dark"]) .sidebar-nav li.active a[href*="properties.php"] {
        background: linear-gradient(90deg, rgba(37, 198, 133, 0.15) 0%, rgba(37, 198, 133, 0.08) 100%);
        color: #25c685;
        border-left: 4px solid #25c685;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(37, 198, 133, 0.15);
    }
    
    /* Tickets menu item - Yellow */
    html:not([data-theme="dark"]) .sidebar-nav a[href*="tickets"]::before {
        background: linear-gradient(180deg, #f8b830 0%, #f6a819 100%);
    }
    
    html:not([data-theme="dark"]) .sidebar-nav a[href*="tickets"]:hover {
        background: linear-gradient(90deg, rgba(248, 184, 48, 0.12) 0%, rgba(248, 184, 48, 0.05) 100%);
        color: #f8b830;
        transform: translateX(4px);
    }
    
    html:not([data-theme="dark"]) .sidebar-nav li.active a[href*="tickets"],
    html:not([data-theme="dark"]) .sidebar-nav li.active a[href*="tickets.php"] {
        background: linear-gradient(90deg, rgba(248, 184, 48, 0.15) 0%, rgba(248, 184, 48, 0.08) 100%);
        color: #f8b830;
        border-left: 4px solid #f8b830;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(248, 184, 48, 0.15);
    }
    
    /* Payments menu item - Cyan */
    html:not([data-theme="dark"]) .sidebar-nav a[href*="payments"]::before {
        background: linear-gradient(180deg, #4cc9f0 0%, #39b8df 100%);
    }
    
    html:not([data-theme="dark"]) .sidebar-nav a[href*="payments"]:hover {
        background: linear-gradient(90deg, rgba(76, 201, 240, 0.12) 0%, rgba(76, 201, 240, 0.05) 100%);
        color: #4cc9f0;
        transform: translateX(4px);
    }
    
    html:not([data-theme="dark"]) .sidebar-nav li.active a[href*="payments"],
    html:not([data-theme="dark"]) .sidebar-nav li.active a[href*="payments.php"] {
        background: linear-gradient(90deg, rgba(76, 201, 240, 0.15) 0%, rgba(76, 201, 240, 0.08) 100%);
        color: #4cc9f0;
        border-left: 4px solid #4cc9f0;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(76, 201, 240, 0.15);
    }
    
    /* Maintenance menu item - Blue */
    html:not([data-theme="dark"]) .sidebar-nav a[href*="maintenance"]::before {
        background: linear-gradient(180deg, #4361ee 0%, #3a56e4 100%);
    }
    
    html:not([data-theme="dark"]) .sidebar-nav a[href*="maintenance"]:hover {
        background: linear-gradient(90deg, rgba(67, 97, 238, 0.12) 0%, rgba(67, 97, 238, 0.05) 100%);
        color: #4361ee;
        transform: translateX(4px);
    }
    
    html:not([data-theme="dark"]) .sidebar-nav li.active a[href*="maintenance"],
    html:not([data-theme="dark"]) .sidebar-nav li.active a[href*="maintenance.php"] {
        background: linear-gradient(90deg, rgba(67, 97, 238, 0.15) 0%, rgba(67, 97, 238, 0.08) 100%);
        color: #4361ee;
        border-left: 4px solid #4361ee;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(67, 97, 238, 0.15);
    }
    
    /* Profile menu item - Purple */
    html:not([data-theme="dark"]) .sidebar-nav a[href*="view-user"]::before,
    html:not([data-theme="dark"]) .sidebar-nav a[href*="profile"]::before {
        background: linear-gradient(180deg, #9b59b6 0%, #8e44ad 100%);
    }
    
    html:not([data-theme="dark"]) .sidebar-nav a[href*="view-user"]:hover,
    html:not([data-theme="dark"]) .sidebar-nav a[href*="profile"]:hover {
        background: linear-gradient(90deg, rgba(155, 89, 182, 0.12) 0%, rgba(155, 89, 182, 0.05) 100%);
        color: #9b59b6;
        transform: translateX(4px);
    }
    
    html:not([data-theme="dark"]) .sidebar-nav li.active a[href*="view-user"],
    html:not([data-theme="dark"]) .sidebar-nav li.active a[href*="profile"] {
        background: linear-gradient(90deg, rgba(155, 89, 182, 0.15) 0%, rgba(155, 89, 182, 0.08) 100%);
        color: #9b59b6;
        border-left: 4px solid #9b59b6;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(155, 89, 182, 0.15);
    }
    
    /* Sidebar Footer */
    html:not([data-theme="dark"]) .sidebar-footer {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(248, 250, 255, 0.9) 100%);
        border-top: 2px solid rgba(67, 97, 238, 0.15);
        box-shadow: 0 -2px 8px rgba(67, 97, 238, 0.08);
    }
    
    /* Icon colors in sidebar */
    html:not([data-theme="dark"]) .sidebar-nav a i {
        transition: all 0.3s ease;
    }
    
    html:not([data-theme="dark"]) .sidebar-nav a[href*="dashboard"] i {
        color: #4361ee;
    }
    
    html:not([data-theme="dark"]) .sidebar-nav a[href*="properties"] i {
        color: #25c685;
    }
    
    html:not([data-theme="dark"]) .sidebar-nav a[href*="tickets"] i {
        color: #f8b830;
    }
    
    html:not([data-theme="dark"]) .sidebar-nav a[href*="payments"] i {
        color: #4cc9f0;
    }
    
    html:not([data-theme="dark"]) .sidebar-nav a[href*="maintenance"] i {
        color: #4361ee;
    }
    
    html:not([data-theme="dark"]) .sidebar-nav a[href*="view-user"] i,
    html:not([data-theme="dark"]) .sidebar-nav a[href*="profile"] i {
        color: #9b59b6;
    }
    
    /* ========== DARK THEME SIDEBAR ENHANCEMENTS ========== */
    
    /* Dark Theme Sidebar Background */
    [data-theme="dark"] .sidebar {
        background: linear-gradient(180deg, #1a202c 0%, #1e2532 50%, #1a202c 100%);
        border-right: 2px solid rgba(78, 115, 223, 0.2);
        box-shadow: 4px 0 20px rgba(0, 0, 0, 0.3);
    }
    
    [data-theme="dark"] .sidebar-header {
        background: linear-gradient(135deg, rgba(78, 115, 223, 0.15) 0%, rgba(28, 200, 138, 0.1) 100%);
        border-bottom: 2px solid rgba(78, 115, 223, 0.25);
        padding: 1.5rem;
        border-radius: 0 0 12px 0;
        margin: -1.5rem -1.5rem 1.5rem -1.5rem;
    }
    
    [data-theme="dark"] .sidebar-header h2 {
        background: linear-gradient(135deg, #4e73df 0%, #1cc88a 50%, #36b9cc 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-weight: 700;
    }
    
    [data-theme="dark"] .user-info {
        background: linear-gradient(135deg, rgba(45, 55, 72, 0.8) 0%, rgba(42, 50, 65, 0.8) 100%);
        border: 1px solid rgba(78, 115, 223, 0.25);
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
    }
    
    [data-theme="dark"] .user-info:hover {
        background: linear-gradient(135deg, rgba(78, 115, 223, 0.2) 0%, rgba(28, 200, 138, 0.15) 100%);
        border-color: rgba(78, 115, 223, 0.4);
        box-shadow: 0 4px 12px rgba(78, 115, 223, 0.2);
        transform: translateX(4px);
    }
    
    [data-theme="dark"] .user-avatar {
        background: linear-gradient(135deg, #4e73df 0%, #1cc88a 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(78, 115, 223, 0.3);
    }
    
    [data-theme="dark"] .user-details h4 {
        color: #f8f9fc;
        font-weight: 600;
    }
    
    [data-theme="dark"] .user-details p {
        color: #93c5fd;
        font-weight: 500;
    }
    
    /* Dark Theme Colorful Menu Items */
    [data-theme="dark"] .sidebar-nav a {
        border-radius: 10px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    [data-theme="dark"] .sidebar-nav a::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 4px;
        height: 100%;
        background: transparent;
        transition: all 0.3s ease;
    }
    
    /* Dark Theme - General active state override */
    [data-theme="dark"] .sidebar-nav li.active a {
        background: linear-gradient(90deg, rgba(78, 115, 223, 0.25) 0%, rgba(78, 115, 223, 0.15) 100%);
        color: #93c5fd;
        border-left: 4px solid #4e73df;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(78, 115, 223, 0.3);
    }
    
    /* Dark Theme - Dashboard menu item - Blue */
    [data-theme="dark"] .sidebar-nav a[href*="dashboard"]::before {
        background: linear-gradient(180deg, #4e73df 0%, #3a56e4 100%);
    }
    
    [data-theme="dark"] .sidebar-nav a[href*="dashboard"]:hover {
        background: linear-gradient(90deg, rgba(78, 115, 223, 0.2) 0%, rgba(78, 115, 223, 0.1) 100%);
        color: #93c5fd;
        transform: translateX(4px);
    }
    
    [data-theme="dark"] .sidebar-nav li.active a[href*="dashboard"],
    [data-theme="dark"] .sidebar-nav li.active a[href*="dashboard.php"] {
        background: linear-gradient(90deg, rgba(78, 115, 223, 0.25) 0%, rgba(78, 115, 223, 0.15) 100%);
        color: #93c5fd;
        border-left: 4px solid #4e73df;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(78, 115, 223, 0.3);
    }
    
    /* Dark Theme - Properties menu item - Green */
    [data-theme="dark"] .sidebar-nav a[href*="properties"]::before {
        background: linear-gradient(180deg, #1cc88a 0%, #13b571 100%);
    }
    
    [data-theme="dark"] .sidebar-nav a[href*="properties"]:hover {
        background: linear-gradient(90deg, rgba(28, 200, 138, 0.2) 0%, rgba(28, 200, 138, 0.1) 100%);
        color: #6ee7b7;
        transform: translateX(4px);
    }
    
    [data-theme="dark"] .sidebar-nav li.active a[href*="properties"],
    [data-theme="dark"] .sidebar-nav li.active a[href*="properties.php"] {
        background: linear-gradient(90deg, rgba(28, 200, 138, 0.25) 0%, rgba(28, 200, 138, 0.15) 100%);
        color: #6ee7b7;
        border-left: 4px solid #1cc88a;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(28, 200, 138, 0.3);
    }
    
    /* Dark Theme - Tickets menu item - Yellow */
    [data-theme="dark"] .sidebar-nav a[href*="tickets"]::before {
        background: linear-gradient(180deg, #f6c23e 0%, #f6a819 100%);
    }
    
    [data-theme="dark"] .sidebar-nav a[href*="tickets"]:hover {
        background: linear-gradient(90deg, rgba(246, 194, 62, 0.2) 0%, rgba(246, 194, 62, 0.1) 100%);
        color: #fcd34d;
        transform: translateX(4px);
    }
    
    [data-theme="dark"] .sidebar-nav li.active a[href*="tickets"],
    [data-theme="dark"] .sidebar-nav li.active a[href*="tickets.php"] {
        background: linear-gradient(90deg, rgba(246, 194, 62, 0.25) 0%, rgba(246, 194, 62, 0.15) 100%);
        color: #fcd34d;
        border-left: 4px solid #f6c23e;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(246, 194, 62, 0.3);
    }
    
    /* Dark Theme - Payments menu item - Cyan */
    [data-theme="dark"] .sidebar-nav a[href*="payments"]::before {
        background: linear-gradient(180deg, #36b9cc 0%, #39b8df 100%);
    }
    
    [data-theme="dark"] .sidebar-nav a[href*="payments"]:hover {
        background: linear-gradient(90deg, rgba(54, 185, 204, 0.2) 0%, rgba(54, 185, 204, 0.1) 100%);
        color: #7dd3fc;
        transform: translateX(4px);
    }
    
    [data-theme="dark"] .sidebar-nav li.active a[href*="payments"],
    [data-theme="dark"] .sidebar-nav li.active a[href*="payments.php"] {
        background: linear-gradient(90deg, rgba(54, 185, 204, 0.25) 0%, rgba(54, 185, 204, 0.15) 100%);
        color: #7dd3fc;
        border-left: 4px solid #36b9cc;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(54, 185, 204, 0.3);
    }
    
    /* Dark Theme - Maintenance menu item - Blue */
    [data-theme="dark"] .sidebar-nav a[href*="maintenance"]::before {
        background: linear-gradient(180deg, #4e73df 0%, #3a56e4 100%);
    }
    
    [data-theme="dark"] .sidebar-nav a[href*="maintenance"]:hover {
        background: linear-gradient(90deg, rgba(78, 115, 223, 0.2) 0%, rgba(78, 115, 223, 0.1) 100%);
        color: #93c5fd;
        transform: translateX(4px);
    }
    
    [data-theme="dark"] .sidebar-nav li.active a[href*="maintenance"],
    [data-theme="dark"] .sidebar-nav li.active a[href*="maintenance.php"] {
        background: linear-gradient(90deg, rgba(78, 115, 223, 0.25) 0%, rgba(78, 115, 223, 0.15) 100%);
        color: #93c5fd;
        border-left: 4px solid #4e73df;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(78, 115, 223, 0.3);
    }
    
    /* Dark Theme - Profile menu item - Purple */
    [data-theme="dark"] .sidebar-nav a[href*="view-user"]::before,
    [data-theme="dark"] .sidebar-nav a[href*="profile"]::before {
        background: linear-gradient(180deg, #9b59b6 0%, #8e44ad 100%);
    }
    
    [data-theme="dark"] .sidebar-nav a[href*="view-user"]:hover,
    [data-theme="dark"] .sidebar-nav a[href*="profile"]:hover {
        background: linear-gradient(90deg, rgba(155, 89, 182, 0.2) 0%, rgba(155, 89, 182, 0.1) 100%);
        color: #c084fc;
        transform: translateX(4px);
    }
    
    [data-theme="dark"] .sidebar-nav li.active a[href*="view-user"],
    [data-theme="dark"] .sidebar-nav li.active a[href*="profile"] {
        background: linear-gradient(90deg, rgba(155, 89, 182, 0.25) 0%, rgba(155, 89, 182, 0.15) 100%);
        color: #c084fc;
        border-left: 4px solid #9b59b6;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(155, 89, 182, 0.3);
    }
    
    /* Dark Theme - Sidebar Footer */
    [data-theme="dark"] .sidebar-footer {
        background: linear-gradient(135deg, rgba(45, 55, 72, 0.8) 0%, rgba(42, 50, 65, 0.8) 100%);
        border-top: 2px solid rgba(78, 115, 223, 0.25);
        box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.2);
    }
    
    /* Dark Theme - Icon colors in sidebar */
    [data-theme="dark"] .sidebar-nav a i {
        transition: all 0.3s ease;
    }
    
    [data-theme="dark"] .sidebar-nav a[href*="dashboard"] i {
        color: #93c5fd;
    }
    
    [data-theme="dark"] .sidebar-nav a[href*="properties"] i {
        color: #6ee7b7;
    }
    
    [data-theme="dark"] .sidebar-nav a[href*="tickets"] i {
        color: #fcd34d;
    }
    
    [data-theme="dark"] .sidebar-nav a[href*="payments"] i {
        color: #7dd3fc;
    }
    
    [data-theme="dark"] .sidebar-nav a[href*="maintenance"] i {
        color: #93c5fd;
    }
    
    [data-theme="dark"] .sidebar-nav a[href*="view-user"] i,
    [data-theme="dark"] .sidebar-nav a[href*="profile"] i {
        color: #c084fc;
    }
    
    /* Language Switcher Styles - z-index already set above */
    .language-switcher .dropdown-toggle {
        background: linear-gradient(135deg, #25c685 0%, #1cc88a 100%);
        color: white;
        border: 2px solid rgba(37, 198, 133, 0.3);
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        font-weight: 600;
        font-size: 14px;
        padding: 10px 16px;
        border-radius: 10px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(37, 198, 133, 0.25);
    }
    .language-switcher .dropdown-toggle:hover {
        background: linear-gradient(135deg, #1cc88a 0%, #19b777 100%);
        border-color: rgba(37, 198, 133, 0.5);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(37, 198, 133, 0.35);
    }
    .language-switcher .dropdown-toggle:active {
        transform: translateY(1px);
    }
    .language-switcher .dropdown-toggle i {
        font-size: 14px;
    }
    .language-switcher {
        position: relative;
        display: inline-block;
        margin-left: 15px;
        z-index: 9999;
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
        z-index: 99999 !important;
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
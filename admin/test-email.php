<?php
// Start session
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get JSON data from request
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// Validate required fields
if (!isset($data['recipient']) || !filter_var($data['recipient'], FILTER_VALIDATE_EMAIL)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid recipient email address']);
    exit();
}

$recipient = $data['recipient'];
$smtp_host = $data['smtp_host'] ?? '';
$smtp_port = $data['smtp_port'] ?? '587';
$smtp_username = $data['smtp_username'] ?? '';
$smtp_password = $data['smtp_password'] ?? '';

// If SMTP details not provided, try to get from database
if (empty($smtp_host)) {
    try {
        $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $db->query("SELECT setting_value FROM system_settings WHERE setting_key = 'smtp_host'");
        $smtp_host = $stmt->fetchColumn() ?: '';
        
        $stmt = $db->query("SELECT setting_value FROM system_settings WHERE setting_key = 'smtp_port'");
        $smtp_port = $stmt->fetchColumn() ?: '587';
        
        $stmt = $db->query("SELECT setting_value FROM system_settings WHERE setting_key = 'smtp_username'");
        $smtp_username = $stmt->fetchColumn() ?: '';
        
        $stmt = $db->query("SELECT setting_value FROM system_settings WHERE setting_key = 'smtp_password'");
        $smtp_password = $stmt->fetchColumn() ?: '';
        
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        exit();
    }
}

// Test email functionality
try {
    // Attempt to send email
    $headers = [
        'From' => 'Community Trust Bank <noreply@communitytrust.com>',
        'Reply-To' => 'noreply@communitytrust.com',
        'MIME-Version' => '1.0',
        'Content-Type' => 'text/html; charset=UTF-8'
    ];
    
    $subject = 'Test Email from Community Trust Bank';
    $message = '
    <html>
    <head>
        <title>Test Email</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #3498db; color: white; padding: 15px; text-align: center; }
            .content { padding: 20px; border: 1px solid #ddd; }
            .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #777; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h2>Test Email</h2>
            </div>
            <div class="content">
                <p>Hello,</p>
                <p>This is a test email from Community Trust Bank admin panel.</p>
                <p>If you received this email, it means your email configuration is working correctly.</p>
                <p>Time sent: ' . date('Y-m-d H:i:s') . '</p>
            </div>
            <div class="footer">
                <p>This is an automated message, please do not reply.</p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    // If SMTP details are provided, use PHPMailer
    if (!empty($smtp_host) && !empty($smtp_username)) {
        // Try to use PHPMailer if available
        if (file_exists('../vendor/autoload.php')) {
            require '../vendor/autoload.php';
            
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = $smtp_host;
            $mail->SMTPAuth = true;
            $mail->Username = $smtp_username;
            $mail->Password = $smtp_password;
            $mail->SMTPSecure = 'tls';
            $mail->Port = $smtp_port;
            
            // Recipients
            $mail->setFrom('noreply@communitytrust.com', 'Community Trust Bank');
            $mail->addAddress($recipient);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;
            
            $mail->send();
            
            // Log the activity
            try {
                $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                logActivity($db, $_SESSION['user_id'], 'test', 'email', 0, "Sent test email to $recipient");
            } catch (PDOException $e) {
                // Ignore logging errors
            }
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Test email sent successfully to ' . $recipient]);
            exit();
        } else {
            throw new Exception('PHPMailer is not installed. Please use the default PHP mail function or install PHPMailer.');
        }
    } else {
        // Use default PHP mail function
        if (mail($recipient, $subject, $message, implode("\r\n", $headers))) {
            // Log the activity
            try {
                $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                logActivity($db, $_SESSION['user_id'], 'test', 'email', 0, "Sent test email to $recipient");
            } catch (PDOException $e) {
                // Ignore logging errors
            }
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Test email sent successfully to ' . $recipient]);
            exit();
        } else {
            throw new Exception('Failed to send email using PHP mail function. Check your server configuration.');
        }
    }
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error sending email: ' . $e->getMessage()]);
    exit();
} 
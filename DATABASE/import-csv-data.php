<?php
/**
 * CSV Data Import Script for CTB Database
 * 
 * This script imports property and cotisation data from Liste DB CTB.csv
 * 
 * Usage: Run this script once via browser or command line after setting up the database
 * 
 * Features:
 * - Skips empty lines
 * - Creates users from owner names (if not exists)
 * - Creates properties (apartments and parkings)
 * - Links properties to users
 * - Creates cotisations for 2026
 * - Handles multiple parkings (split by +)
 * - Handles phone/email extraction from contact field
 * - Handles invalid values like #REF!
 */

// Start session (for web access)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
require_once __DIR__ . '/../includes/config.php';

// Set execution time limit for large imports
set_time_limit(300); // 5 minutes

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CSV file path
$csv_file = __DIR__ . '/Liste DB CTB.csv';
$year = 2026; // Year for cotisations

// Statistics
$stats = [
    'users_created' => 0,
    'users_existing' => 0,
    'apartments_created' => 0,
    'apartments_existing' => 0,
    'parkings_created' => 0,
    'parkings_existing' => 0,
    'cotisations_created' => 0,
    'cotisations_existing' => 0,
    'errors' => [],
    'warnings' => []
];

try {
    // Connect to database
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Check if CSV file exists
    if (!file_exists($csv_file)) {
        throw new Exception("CSV file not found: $csv_file");
    }
    
    // Open CSV file
    $handle = fopen($csv_file, 'r');
    if ($handle === false) {
        throw new Exception("Cannot open CSV file: $csv_file");
    }
    
    // Read header row
    $header = fgetcsv($handle);
    if ($header === false) {
        throw new Exception("Cannot read CSV header");
    }
    
    // Expected columns: Bloc, Nom et prénom de copropriétaire, Cotisation 2026, Parking n°, Cotisation 2026, N° tel
    $line_number = 1;
    
    // Start transaction
    $db->beginTransaction();
    
    // Process each row
    while (($row = fgetcsv($handle)) !== false) {
        $line_number++;
        
        // Skip empty rows
        if (empty(array_filter($row))) {
            continue;
        }
        
        // Extract data
        $bloc = trim($row[0] ?? '');
        $owner_name = trim($row[1] ?? '');
        $apartment_cotisation = trim($row[2] ?? '');
        $parking_number = trim($row[3] ?? '');
        $parking_cotisation = trim($row[4] ?? '');
        $contact_info = trim($row[5] ?? '');
        
        // Skip if no bloc identifier
        if (empty($bloc)) {
            continue;
        }
        
        // Get or create user first (if owner name exists)
        $user_id = null;
        if (!empty($owner_name)) {
            try {
                $user_id = getOrCreateUser($db, $owner_name, $contact_info, $stats);
            } catch (Exception $e) {
                $stats['errors'][] = "Line $line_number (Bloc: $bloc, Owner: $owner_name): " . $e->getMessage();
            }
        }
        
        // Process apartment
        if (!empty($apartment_cotisation)) {
            // Clean and validate cotisation amount
            $apartment_cotisation = str_replace(',', '.', $apartment_cotisation); // Handle comma as decimal
            $apartment_cotisation = preg_replace('/[^\d.]/', '', $apartment_cotisation); // Remove non-numeric except decimal
            
            if (is_numeric($apartment_cotisation) && floatval($apartment_cotisation) > 0) {
                try {
                    // Create or get apartment property
                    $apartment_id = getOrCreateProperty($db, 'apartment', $bloc, $user_id, $stats);
                    
                    // Create cotisation for apartment
                    createCotisation($db, $apartment_id, $year, floatval($apartment_cotisation), $stats);
                    
                } catch (Exception $e) {
                    $stats['errors'][] = "Line $line_number (Bloc: $bloc, Apartment): " . $e->getMessage();
                }
            } else {
                $stats['warnings'][] = "Line $line_number (Bloc: $bloc): Invalid apartment cotisation value: " . htmlspecialchars($row[2]);
            }
        }
        
        // Process parking(s)
        if (!empty($parking_number) && !empty($parking_cotisation)) {
            // Clean and validate cotisation amount
            $parking_cotisation = str_replace(',', '.', $parking_cotisation); // Handle comma as decimal
            $parking_cotisation = preg_replace('/[^\d.]/', '', $parking_cotisation); // Remove non-numeric except decimal
            
            // Skip #REF! and invalid values
            if (strtoupper(trim($row[4])) === '#REF!' || !is_numeric($parking_cotisation) || floatval($parking_cotisation) <= 0) {
                $stats['warnings'][] = "Line $line_number (Bloc: $bloc, Parking: $parking_number): Invalid parking cotisation value";
            } else {
                try {
                    // Handle multiple parkings (split by +)
                    $parking_numbers = preg_split('/\s*\+\s*/', $parking_number);
                    
                    foreach ($parking_numbers as $parking_num) {
                        $parking_num = trim($parking_num);
                        
                        // Skip invalid parking numbers
                        if (empty($parking_num) || strtoupper($parking_num) === '#REF!') {
                            continue;
                        }
                        
                        // Remove "box" or other text suffixes
                        $parking_num = preg_replace('/\s*(box|BOX|Box).*$/i', '', $parking_num);
                        
                        // Skip if parking number is empty after cleaning
                        if (empty($parking_num)) {
                            continue;
                        }
                        
                        // Create parking identifier (e.g., "P146" for parking 146)
                        $parking_identifier = 'P' . $parking_num;
                        
                        // Create or get parking property
                        $parking_id = getOrCreateProperty($db, 'parking', $parking_identifier, $user_id, $stats);
                        
                        // Create cotisation for parking
                        createCotisation($db, $parking_id, $year, floatval($parking_cotisation), $stats);
                    }
                    
                } catch (Exception $e) {
                    $stats['errors'][] = "Line $line_number (Bloc: $bloc, Parking: $parking_number): " . $e->getMessage();
                }
            }
        }
    }
    
    // Commit transaction
    $db->commit();
    fclose($handle);
    
    // Display results
    displayResults($stats);
    
} catch (Exception $e) {
    // Rollback on error
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    
    echo "<h2 style='color: red;'>Error:</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

/**
 * Get or create user
 */
function getOrCreateUser($db, $name, $contact_info, &$stats) {
    // Extract email and phone from contact info
    $email = extractEmail($contact_info);
    $phone = extractPhone($contact_info);
    
    // If email exists, check if user already exists
    if (!empty($email)) {
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $existing = $stmt->fetch();
        if ($existing) {
            // Update phone if provided and different
            if ($phone && $phone !== $existing['phone']) {
                $update_stmt = $db->prepare("UPDATE users SET phone = ? WHERE id = ?");
                $update_stmt->execute([$phone, $existing['id']]);
            }
            $stats['users_existing']++;
            return $existing['id'];
        }
    }
    
    // Check if user with same name already exists (to avoid duplicates)
    if (empty($email)) {
        $stmt = $db->prepare("SELECT id FROM users WHERE name = ? AND email LIKE '%@ctb.ma'");
        $stmt->execute([$name]);
        $existing = $stmt->fetch();
        if ($existing) {
            // Update phone if provided
            if ($phone) {
                $update_stmt = $db->prepare("UPDATE users SET phone = ? WHERE id = ?");
                $update_stmt->execute([$phone, $existing['id']]);
            }
            $stats['users_existing']++;
            return $existing['id'];
        }
    }
    
    // Generate email if not found
    if (empty($email)) {
        // Create email from name (sanitized)
        $email_base = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $name)));
        if (empty($email_base)) {
            $email_base = 'user' . time() . rand(1000, 9999);
        }
        $email = $email_base . '@ctb.ma';
        
        // Check if email already exists, append number if needed
        $counter = 1;
        $original_email = $email;
        while (userExists($db, $email)) {
            $email = $email_base . $counter . '@ctb.ma';
            $counter++;
        }
    }
    
    // Create new user
    // Generate a default password hash (users will need to reset password)
    $default_password = password_hash('changeme123', PASSWORD_DEFAULT);
    
    $stmt = $db->prepare("
        INSERT INTO users (email, password, name, phone, role, status, created_at, updated_at)
        VALUES (?, ?, ?, ?, 'resident', 'active', NOW(), NOW())
    ");
    
    $stmt->execute([$email, $default_password, $name, $phone]);
    $user_id = $db->lastInsertId();
    
    $stats['users_created']++;
    return $user_id;
}

/**
 * Check if user exists by email
 */
function userExists($db, $email) {
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $result = $stmt->fetch();
    return $result['count'] > 0;
}

/**
 * Get or create property
 */
function getOrCreateProperty($db, $type, $identifier, $user_id, &$stats) {
    // Check if property exists
    $stmt = $db->prepare("SELECT id, user_id FROM properties WHERE identifier = ?");
    $stmt->execute([$identifier]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Update user_id if property exists but user_id is different
        if ($user_id && (empty($existing['user_id']) || $existing['user_id'] != $user_id)) {
            $update_stmt = $db->prepare("UPDATE properties SET user_id = ? WHERE id = ?");
            $update_stmt->execute([$user_id, $existing['id']]);
        }
        
        // Track existing properties
        if ($type === 'apartment') {
            $stats['apartments_existing']++;
        } else {
            $stats['parkings_existing']++;
        }
        
        return $existing['id'];
    }
    
    // Create new property
    $stmt = $db->prepare("
        INSERT INTO properties (type, identifier, user_id, created_at, updated_at)
        VALUES (?, ?, ?, NOW(), NOW())
    ");
    
    $stmt->execute([$type, $identifier, $user_id]);
    $property_id = $db->lastInsertId();
    
    if ($type === 'apartment') {
        $stats['apartments_created']++;
    } else {
        $stats['parkings_created']++;
    }
    
    return $property_id;
}

/**
 * Create cotisation
 */
function createCotisation($db, $property_id, $year, $amount_due, &$stats) {
    // Check if cotisation already exists
    $stmt = $db->prepare("SELECT id, amount_due FROM cotisations WHERE property_id = ? AND year = ?");
    $stmt->execute([$property_id, $year]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Update amount if different
        if (abs(floatval($existing['amount_due']) - floatval($amount_due)) > 0.01) {
            $update_stmt = $db->prepare("UPDATE cotisations SET amount_due = ? WHERE id = ?");
            $update_stmt->execute([$amount_due, $existing['id']]);
        }
        $stats['cotisations_existing']++;
        return;
    }
    
    // Create new cotisation
    $stmt = $db->prepare("
        INSERT INTO cotisations (property_id, year, amount_due, created_at, updated_at)
        VALUES (?, ?, ?, NOW(), NOW())
    ");
    
    $stmt->execute([$property_id, $year, $amount_due]);
    $stats['cotisations_created']++;
}

/**
 * Extract email from contact info
 */
function extractEmail($contact_info) {
    if (empty($contact_info)) {
        return null;
    }
    
    // Look for email pattern
    if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $contact_info, $matches)) {
        return strtolower(trim($matches[0]));
    }
    
    return null;
}

/**
 * Extract phone from contact info
 */
function extractPhone($contact_info) {
    if (empty($contact_info)) {
        return null;
    }
    
    // Remove email if present
    $phone_text = preg_replace('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', '', $contact_info);
    
    // Remove common words
    $phone_text = preg_replace('/\b(email|gmail|hotmail|com|wtsp|whatsapp)\b/i', '', $phone_text);
    
    // Extract phone numbers (various formats)
    // Look for sequences of digits with optional +, spaces, dashes, slashes
    if (preg_match_all('/(?:\+?\d{1,4}[-.\s\/]?)?\(?\d{1,4}\)?[-.\s\/]?\d{1,4}[-.\s\/]?\d{1,9}/', $phone_text, $matches)) {
        // Take the first phone number found
        $phone = trim($matches[0][0]);
        // Clean up phone number - keep digits, +, and common separators
        $phone = preg_replace('/[^\d+\-\s\/]/', '', $phone);
        $phone = trim($phone);
        
        // Remove separators but keep + at start
        if (strpos($phone, '+') === 0) {
            $phone = '+' . preg_replace('/[^\d]/', '', substr($phone, 1));
        } else {
            $phone = preg_replace('/[^\d]/', '', $phone);
        }
        
        // Only return if it looks like a valid phone (at least 8 digits)
        if (!empty($phone) && strlen(preg_replace('/[^\d]/', '', $phone)) >= 8) {
            return $phone;
        }
    }
    
    return null;
}

/**
 * Display import results
 */
function displayResults($stats) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>CSV Import Results</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                max-width: 1200px;
                margin: 50px auto;
                padding: 20px;
                background: #f5f5f5;
            }
            .container {
                background: white;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            h1 {
                color: #333;
                border-bottom: 3px solid #4e73df;
                padding-bottom: 10px;
            }
            .stats {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
                margin: 30px 0;
            }
            .stat-card {
                background: #f8f9fc;
                padding: 20px;
                border-radius: 8px;
                border-left: 4px solid #4e73df;
            }
            .stat-card h3 {
                margin: 0 0 10px 0;
                color: #666;
                font-size: 14px;
                text-transform: uppercase;
            }
            .stat-card .number {
                font-size: 32px;
                font-weight: bold;
                color: #4e73df;
            }
            .success {
                color: #28a745;
                background: #d4edda;
                padding: 15px;
                border-radius: 5px;
                margin: 20px 0;
            }
            .error {
                color: #dc3545;
                background: #f8d7da;
                padding: 15px;
                border-radius: 5px;
                margin: 20px 0;
            }
            .warning {
                color: #856404;
                background: #fff3cd;
                padding: 15px;
                border-radius: 5px;
                margin: 20px 0;
            }
            ul {
                margin: 10px 0;
                padding-left: 25px;
            }
            li {
                margin: 5px 0;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>📊 CSV Import Results</h1>
            
            <div class="stats">
                <div class="stat-card">
                    <h3>Users Created</h3>
                    <div class="number"><?php echo $stats['users_created']; ?></div>
                    <small style="color: #666;">Existing: <?php echo $stats['users_existing']; ?></small>
                </div>
                <div class="stat-card">
                    <h3>Apartments Created</h3>
                    <div class="number"><?php echo $stats['apartments_created']; ?></div>
                    <small style="color: #666;">Existing: <?php echo $stats['apartments_existing']; ?></small>
                </div>
                <div class="stat-card">
                    <h3>Parkings Created</h3>
                    <div class="number"><?php echo $stats['parkings_created']; ?></div>
                    <small style="color: #666;">Existing: <?php echo $stats['parkings_existing']; ?></small>
                </div>
                <div class="stat-card">
                    <h3>Cotisations Created</h3>
                    <div class="number"><?php echo $stats['cotisations_created']; ?></div>
                    <small style="color: #666;">Existing: <?php echo $stats['cotisations_existing']; ?></small>
                </div>
            </div>
            
            <?php if (count($stats['errors']) > 0): ?>
                <div class="error">
                    <h3>❌ Errors (<?php echo count($stats['errors']); ?>)</h3>
                    <ul>
                        <?php foreach ($stats['errors'] as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php if (count($stats['warnings']) > 0): ?>
                <div class="warning">
                    <h3>⚠️ Warnings (<?php echo count($stats['warnings']); ?>)</h3>
                    <ul>
                        <?php foreach ($stats['warnings'] as $warning): ?>
                            <li><?php echo htmlspecialchars($warning); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php if (count($stats['errors']) === 0): ?>
                <div class="success">
                    <h3>✅ Import Completed Successfully!</h3>
                    <p>All data has been imported into the database.</p>
                </div>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
}

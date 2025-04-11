<?php
/**
 * Common Functions for CTB Property Management System
 * 
 * This file contains utility functions used throughout the application
 */

/**
 * Convert a timestamp to a human-readable time elapsed string (e.g., "5 minutes ago")
 *
 * @param string $datetime Timestamp string
 * @param bool $full Whether to show full detail
 * @return string Human-readable time elapsed string
 */
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    // Create an array without using dynamic properties
    $string = array();
    
    // Calculate weeks separately
    $weeks = floor($diff->d / 7);
    $days_remaining = $diff->d % 7;
    
    if ($diff->y > 0) {
        $string['y'] = $diff->y . ' year' . ($diff->y > 1 ? 's' : '');
    }
    
    if ($diff->m > 0) {
        $string['m'] = $diff->m . ' month' . ($diff->m > 1 ? 's' : '');
    }
    
    if ($weeks > 0) {
        $string['w'] = $weeks . ' week' . ($weeks > 1 ? 's' : '');
    }
    
    if ($days_remaining > 0) {
        $string['d'] = $days_remaining . ' day' . ($days_remaining > 1 ? 's' : '');
    }
    
    if ($diff->h > 0) {
        $string['h'] = $diff->h . ' hour' . ($diff->h > 1 ? 's' : '');
    }
    
    if ($diff->i > 0) {
        $string['i'] = $diff->i . ' minute' . ($diff->i > 1 ? 's' : '');
    }
    
    if ($diff->s > 0) {
        $string['s'] = $diff->s . ' second' . ($diff->s > 1 ? 's' : '');
    }

    if (!$full) {
        $string = array_slice($string, 0, 1);
    }
    
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

/**
 * Format currency with dollar sign and two decimal places
 *
 * @param float $amount Amount to format
 * @return string Formatted currency string
 */
function format_currency($amount) {
    return '$' . number_format((float)$amount, 2, '.', ',');
}

/**
 * Generate a color-coded badge for status display
 *
 * @param string $status Status text
 * @return string HTML for badge with appropriate color
 */
function get_status_badge($status) {
    $status_lower = strtolower($status);
    $badge_class = '';
    
    switch ($status_lower) {
        case 'active':
        case 'paid':
        case 'completed':
        case 'approved':
        case 'success':
            $badge_class = 'success';
            break;
        case 'pending':
        case 'in_progress':
        case 'in progress':
        case 'processing':
            $badge_class = 'warning';
            break;
        case 'inactive':
        case 'unpaid':
        case 'cancelled':
        case 'rejected':
        case 'error':
            $badge_class = 'danger';
            break;
        case 'open':
        case 'new':
            $badge_class = 'primary';
            break;
        case 'closed':
        case 'archived':
            $badge_class = 'secondary';
            break;
        default:
            $badge_class = 'info';
    }
    
    return '<span class="badge badge-' . $badge_class . '">' . ucfirst(str_replace('_', ' ', $status)) . '</span>';
}

/**
 * Get the appropriate color for an activity action
 *
 * @param string $action The action name
 * @return string CSS color class
 */
function getActionBadgeColor($action) {
    switch (strtolower($action)) {
        case 'create':
        case 'add':
        case 'insert':
            return 'badge-success';
        case 'update':
        case 'edit':
        case 'modify':
            return 'badge-info';
        case 'delete':
        case 'remove':
            return 'badge-danger';
        case 'login':
        case 'access':
            return 'badge-primary';
        case 'logout':
            return 'badge-secondary';
        case 'view':
        case 'read':
            return 'badge-light';
        default:
            return 'badge-secondary';
    }
}

/**
 * Sanitize and validate email address
 *
 * @param string $email Email address to validate
 * @return string|false Sanitized email or false if invalid
 */
function validate_email($email) {
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Sanitize text input
 *
 * @param string $text Text to sanitize
 * @return string Sanitized text
 */
function sanitize_text($text) {
    return htmlspecialchars(trim($text), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate a random string for tokens, etc.
 *
 * @param int $length Length of the random string
 * @return string Random string
 */
function generate_random_string($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}

/**
 * Add an entry to the activity log
 *
 * @param PDO $db Database connection
 * @param int $user_id User ID
 * @param string $action Action performed (create, update, delete, etc.)
 * @param string $entity_type Type of entity (user, property, ticket, etc.)
 * @param int $entity_id ID of the entity
 * @param string $details Optional additional details
 * @return bool Success or failure
 */
function log_activity($db, $user_id, $action, $entity_type, $entity_id, $details = null) {
    try {
        $stmt = $db->prepare("
            INSERT INTO activity_log (user_id, action, entity_type, entity_id, details, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        return $stmt->execute([$user_id, $action, $entity_type, $entity_id, $details]);
    } catch (PDOException $e) {
        error_log("Error logging activity: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if a user has permission for a specific action
 *
 * @param string $required_role Required role for the action
 * @param array $user_roles Array of user's roles
 * @return bool Whether user has permission
 */
function has_permission($required_role, $user_roles) {
    // Admin has all permissions
    if (in_array('admin', $user_roles)) {
        return true;
    }
    
    // Manager has more permissions than resident
    if ($required_role == 'resident' && in_array('manager', $user_roles)) {
        return true;
    }
    
    // Direct role match
    return in_array($required_role, $user_roles);
}

/**
 * Upload a file with validation
 * 
 * @param array $file $_FILES array element
 * @param string $destination Directory to upload to
 * @param array $allowed_types Array of allowed MIME types
 * @param int $max_size Maximum file size in bytes
 * @return array Result with success status and message
 */
function upload_file($file, $destination, $allowed_types = [], $max_size = 5242880) {
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
        ];
        
        return [
            'success' => false, 
            'message' => $errors[$file['error']] ?? 'Unknown upload error'
        ];
    }
    
    // Validate file size
    if ($file['size'] > $max_size) {
        return [
            'success' => false, 
            'message' => 'File size exceeds the allowed limit'
        ];
    }
    
    // Validate file type if restrictions provided
    if (!empty($allowed_types)) {
        $file_info = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($file_info, $file['tmp_name']);
        finfo_close($file_info);
        
        if (!in_array($mime_type, $allowed_types)) {
            return [
                'success' => false, 
                'message' => 'File type not allowed'
            ];
        }
    }
    
    // Create destination directory if it doesn't exist
    if (!file_exists($destination)) {
        mkdir($destination, 0755, true);
    }
    
    // Generate unique filename
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $file_name = generate_random_string(16) . '.' . $file_extension;
    $target_path = $destination . '/' . $file_name;
    
    // Move the file
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return [
            'success' => true,
            'message' => 'File uploaded successfully',
            'filename' => $file_name,
            'path' => $target_path
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Failed to move uploaded file'
        ];
    }
}

/**
 * Format a date according to the application's standard format
 *
 * @param string $date Date string
 * @param string $format PHP date format string
 * @return string Formatted date
 */
function format_date($date, $format = 'M j, Y') {
    return date($format, strtotime($date));
}

/**
 * Paginate an array of items
 *
 * @param array $items Array of items to paginate
 * @param int $current_page Current page number
 * @param int $per_page Items per page
 * @return array Array containing paginated items and pagination info
 */
function paginate_array($items, $current_page = 1, $per_page = 10) {
    $current_page = max(1, $current_page);
    $total_items = count($items);
    $total_pages = ceil($total_items / $per_page);
    
    // Ensure current page isn't beyond the last page
    $current_page = min($current_page, max(1, $total_pages));
    
    $offset = ($current_page - 1) * $per_page;
    
    // Get just the items for this page
    $paginated_items = array_slice($items, $offset, $per_page);
    
    return [
        'items' => $paginated_items,
        'current_page' => $current_page,
        'per_page' => $per_page,
        'total_items' => $total_items,
        'total_pages' => $total_pages,
        'has_more_pages' => ($current_page < $total_pages),
        'previous_page' => ($current_page > 1) ? $current_page - 1 : null,
        'next_page' => ($current_page < $total_pages) ? $current_page + 1 : null,
    ];
}

/**
 * Generate pagination HTML
 *
 * @param int $current_page Current page number
 * @param int $total_pages Total number of pages
 * @param string $url_pattern URL pattern with placeholder for page number
 * @return string HTML for pagination controls
 */
function pagination_links($current_page, $total_pages, $url_pattern = '?page=%d') {
    if ($total_pages <= 1) {
        return '';
    }
    
    $links = '<nav aria-label="Page navigation"><ul class="pagination">';
    
    // Previous button
    if ($current_page > 1) {
        $links .= '<li class="page-item"><a class="page-link" href="' . sprintf($url_pattern, $current_page - 1) . '" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>';
    } else {
        $links .= '<li class="page-item disabled"><a class="page-link" href="#" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>';
    }
    
    // Calculate range of visible pages
    $range = 2; // Pages to show on either side of current page
    $start = max(1, $current_page - $range);
    $end = min($total_pages, $current_page + $range);
    
    // Add first page and ellipsis if necessary
    if ($start > 1) {
        $links .= '<li class="page-item"><a class="page-link" href="' . sprintf($url_pattern, 1) . '">1</a></li>';
        if ($start > 2) {
            $links .= '<li class="page-item disabled"><a class="page-link" href="#">…</a></li>';
        }
    }
    
    // Add page numbers
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $current_page) {
            $links .= '<li class="page-item active"><a class="page-link" href="#">' . $i . '</a></li>';
        } else {
            $links .= '<li class="page-item"><a class="page-link" href="' . sprintf($url_pattern, $i) . '">' . $i . '</a></li>';
        }
    }
    
    // Add last page and ellipsis if necessary
    if ($end < $total_pages) {
        if ($end < $total_pages - 1) {
            $links .= '<li class="page-item disabled"><a class="page-link" href="#">…</a></li>';
        }
        $links .= '<li class="page-item"><a class="page-link" href="' . sprintf($url_pattern, $total_pages) . '">' . $total_pages . '</a></li>';
    }
    
    // Next button
    if ($current_page < $total_pages) {
        $links .= '<li class="page-item"><a class="page-link" href="' . sprintf($url_pattern, $current_page + 1) . '" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';
    } else {
        $links .= '<li class="page-item disabled"><a class="page-link" href="#" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';
    }
    
    $links .= '</ul></nav>';
    
    return $links;
} 
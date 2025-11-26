<?php
// Start session
session_start();

// Include translation functions
if (!file_exists('../includes/translations.php')) {
    $alt_translations_file = $_SERVER['DOCUMENT_ROOT'] . '/CTB/includes/translations.php';
    if (file_exists($alt_translations_file)) {
        require_once $alt_translations_file;
    } else {
        // Define a minimal setLanguage function as last resort
        function setLanguage($lang) {
            $_SESSION['language'] = $lang;
            return true;
        }
    }
} else {
    require_once '../includes/translations.php';
}

// Get language from URL parameter
$language = isset($_GET['lang']) ? $_GET['lang'] : 'en_US';

// Validate and set language
if (setLanguage($language)) {
    // Language successfully set
} else {
    // Invalid language, default to en_US
    $_SESSION['language'] = 'en_US';
}

// Redirect back to the referring page or to dashboard if referer not available
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'dashboard.php';
header("Location: $referer");
exit;
?> 
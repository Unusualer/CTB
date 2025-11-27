<?php
/**
 * Translation System for CTB Property Management System
 * 
 * This file contains functions for handling translations and internationalization
 */

// Default language if not set
if (!isset($_SESSION['language'])) {
    $_SESSION['language'] = 'en_US';
}

// Function to change the language
function setLanguage($lang) {
    $supported_languages = ['en_US', 'fr_FR', 'es_ES'];
    
    if (in_array($lang, $supported_languages)) {
        $_SESSION['language'] = $lang;
        return true;
    }
    return false;
}

// Cache for translations to avoid loading the same file multiple times
$translations_cache = [];

/**
 * Get translation for a string
 * 
 * @param string $text Text to translate
 * @return string Translated text or original if translation not found
 */
function __($text) {
    global $translations_cache;
    
    // If no text is provided, return empty string
    if (empty($text)) {
        return '';
    }
    
    $language = $_SESSION['language'] ?? 'en_US';
    
    // If translations for this language aren't in cache, load them
    if (!isset($translations_cache[$language])) {
        // Determine which role directory we're in
        $script_path = $_SERVER['SCRIPT_FILENAME'];
        $role_dirs = ['admin', 'manager', 'resident'];
        $detected_role = null;
        
        foreach ($role_dirs as $role) {
            if (strpos($script_path, '/' . $role . '/') !== false || strpos($script_path, '\\' . $role . '\\') !== false) {
                $detected_role = $role;
                break;
            }
        }
        
        $translation_paths = [];
        
        // If we detected a role, prioritize that role's locale directory
        if ($detected_role) {
            $translation_paths[] = dirname(__DIR__) . '/' . $detected_role . '/locale/' . $language . '/translations.json';
        }
        
        // Try from the current script's location (for role-specific pages)
        $translation_paths[] = dirname($_SERVER['SCRIPT_FILENAME']) . '/locale/' . $language . '/translations.json';
        
        // Try all role directories
        foreach ($role_dirs as $role) {
            $translation_paths[] = dirname(__DIR__) . '/' . $role . '/locale/' . $language . '/translations.json';
        }
        
        // Last resort - from document root
        foreach ($role_dirs as $role) {
            $translation_paths[] = $_SERVER['DOCUMENT_ROOT'] . '/CTB/' . $role . '/locale/' . $language . '/translations.json';
        }
        
        // Remove duplicates while preserving order
        $translation_paths = array_unique($translation_paths);
        
        $loaded = false;
        foreach ($translation_paths as $translation_file) {
            if (file_exists($translation_file)) {
                $json_content = file_get_contents($translation_file);
                $translations = json_decode($json_content, true);
                
                if ($translations) {
                    $translations_cache[$language] = $translations;
                    $loaded = true;
                    break;
                }
            }
        }
        
        if (!$loaded) {
            // If no translation file could be loaded, use an empty cache
            error_log("Translation file not found for language: $language");
            $translations_cache[$language] = [];
        }
    }
    
    // Return the translation if exists, otherwise return original text
    return $translations_cache[$language][$text] ?? $text;
} 
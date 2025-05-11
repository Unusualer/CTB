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
        $translation_paths = [
            // Try main admin locale directory
            dirname(__DIR__) . '/admin/locale/' . $language . '/translations.json',
            // Try from the current script's location
            dirname($_SERVER['SCRIPT_FILENAME']) . '/locale/' . $language . '/translations.json',
            // Last resort - from document root
            $_SERVER['DOCUMENT_ROOT'] . '/CTB/admin/locale/' . $language . '/translations.json'
        ];
        
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
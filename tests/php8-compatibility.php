<?php
/**
 * PHP 8.x Compatibility Helper for PHPUnit
 *
 * This script helps ensure the tests run properly with PHP 8.x
 * It adds polyfills and compatibility patches as needed.
 * 
 * @package Simple_WP_Optimizer
 */

// Detect PHP version
$php_version       = phpversion();
$php_major_version = (int)explode('.', $php_version)[0];

// Only apply fixes for PHP 8.x
if ($php_major_version >= 8) {
    // When running on PHP 8.x, add compatibility layers for PHPUnit
    
    // Check if we're running PHPUnit 7.x on PHP 8.x (problematic combination)
    if (class_exists('\PHPUnit\Runner\Version')) {
        $phpunit_version       = \PHPUnit\Runner\Version::id();
        $phpunit_major_version = (int)explode('.', $phpunit_version)[0];
        
        // PHPUnit 7.x with PHP 8.x needs special handling
        if ($phpunit_major_version < 9) {
            // Prevent warnings about passing null to non-nullable parameters
            error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED & ~E_STRICT);
            
            // Register a custom error handler to suppress specific errors
            set_error_handler(function($errno, $errstr, $errfile, $errline) {
                // Only handle specific PHPUnit errors
                if (strpos($errfile, 'phpunit') !== false && 
                    (strpos($errstr, 'Expecting parameter') !== false || 
                     strpos($errstr, 'Passing null') !== false ||
                     strpos($errstr, 'Cannot use positional argument') !== false ||
                     strpos($errstr, 'Cannot acquire reference to $GLOBALS') !== false)) {
                    return true; // Suppress this error
                }
                // Let PHP handle all other errors
                return false;
            }, E_ALL);
            
            // Add specific workarounds for known PHPUnit issues with PHP 8.x
            
            // Workaround for PHPUnit 7.5 with PHP 8.1+ specific issues
            if (version_compare($php_version, '8.1', '>=')) {
                // Define a global flag to indicate we're in compatibility mode
                define('PHPUNIT_PHP81_COMPAT', true);
                
                // Override problematic PHPUnit functions if needed
                if (!function_exists('_fix_phpunit_globals_issue')) {
                    /**
                     * Helper function to safely access globals in a PHP 8.1+ compatible way
                     * Used as a workaround for the "Cannot acquire reference to $GLOBALS" error
                     */
                    function _fix_phpunit_globals_issue($key, $default = null) {
                        return isset($GLOBALS[$key]) ? $GLOBALS[$key] : $default;
                    }
                }
            }
        }
    }
}

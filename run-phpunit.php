#!/usr/bin/env php
<?php
/**
 * Custom PHPUnit runner script for PHP 8.x compatibility
 * 
 * This script helps run PHPUnit tests with proper compatibility settings
 * for PHP 8.x environments. It automatically detects the PHP version
 * and applies the appropriate PHPUnit compatibility settings.
 * 
 * Usage: php run-phpunit.php [args]
 * Any arguments passed to this script will be forwarded to PHPUnit.
 * 
 * @package Simple_WP_Optimizer
 */

// Detect PHP version
$php_version = phpversion();
$php_major_version = (int)explode('.', $php_version)[0];
$php_minor_version = (int)explode('.', explode('.', $php_version)[1])[0];

echo "PHP Version: $php_version\n";

// Define the path to the PHPUnit executable
$phpunit_path = __DIR__ . '/vendor/bin/phpunit';

if (!file_exists($phpunit_path)) {
    echo "Error: PHPUnit not found at $phpunit_path\n";
    echo "Please run 'composer install' first.\n";
    exit(1);
}

// Get the PHPUnit version
$phpunit_version_output = shell_exec("$phpunit_path --version");
preg_match('/PHPUnit\s+([0-9]+\.[0-9]+)/', $phpunit_version_output, $matches);
$phpunit_version = isset($matches[1]) ? $matches[1] : 'unknown';
echo "PHPUnit Version: $phpunit_version\n";

// Build the command arguments
$args = $_SERVER['argv'];
array_shift($args); // Remove the script name

// Default arguments
$default_args = [];

// PHP 8.x specific settings
if ($php_major_version >= 8) {
    echo "Running in PHP 8.x compatibility mode\n";
    
    // For PHP 8.x we need to be more careful with deprecation notices
    // Suppress deprecation notices for PHPUnit 7.x
    if (version_compare($phpunit_version, '9.0', '<')) {
        echo "Using PHPUnit < 9.0 with PHP 8.x requires special handling\n";
        
        // Load the compatibility layer first
        if (file_exists(__DIR__ . '/tests/php8-compatibility.php')) {
            echo "Loading PHP 8.x compatibility layer\n";
            require_once __DIR__ . '/tests/php8-compatibility.php';
        }
        
        // Use custom error settings
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED & ~E_STRICT);
    } else {
        echo "Using PHPUnit 9.x with PHP 8.x\n";
    }
}

// Build the command
$command = escapeshellcmd($phpunit_path);
$command .= ' ' . implode(' ', array_map('escapeshellarg', array_merge($default_args, $args)));

echo "Running command: $command\n";
echo "-----------------------------------------------------------\n";

// Execute PHPUnit with the arguments
passthru($command, $return_var);

// Return the same exit code as PHPUnit
exit($return_var);

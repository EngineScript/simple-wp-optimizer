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

if (function_exists('esc_html')) {
    echo esc_html("PHP Version: $php_version") . "\n";
} else {
    echo "PHP Version: $php_version\n";
}

// Define the path to the PHPUnit executable
$phpunit_path = __DIR__ . '/vendor/bin/phpunit';

if (!file_exists($phpunit_path)) {
    if (function_exists('esc_html')) {
        echo esc_html("Error: PHPUnit not found at $phpunit_path") . "\n";
        echo esc_html("Please run 'composer install' first.") . "\n";
    } else {
        echo "Error: PHPUnit not found at $phpunit_path\n";
        echo "Please run 'composer install' first.\n";
    }
    exit(1);
}

// Get the PHPUnit version
$phpunit_version_output = shell_exec("$phpunit_path --version");
preg_match('/PHPUnit\s+([0-9]+\.[0-9]+)/', $phpunit_version_output, $matches);
$phpunit_version = isset($matches[1]) ? $matches[1] : 'unknown';
if (function_exists('esc_html')) {
    echo esc_html("PHPUnit Version: $phpunit_version") . "\n";
} else {
    echo "PHPUnit Version: $phpunit_version\n";
}

// Build the command arguments
$args = $_SERVER['argv'];
array_shift($args); // Remove the script name

// Default arguments
$default_args = [];

// PHP 8.x specific settings
if ($php_major_version >= 8) {
    if (function_exists('esc_html')) {
        echo esc_html("Running in PHP 8.x compatibility mode") . "\n";
    } else {
        echo "Running in PHP 8.x compatibility mode\n";
    }
    
    // For PHP 8.3 and 8.4, use PHPUnit 10+ with specific settings
    if (8 === $php_major_version && ($php_minor_version >= 3)) {
        if (function_exists('esc_html')) {
            echo esc_html("Using PHP 8.3+ with PHPUnit requires special handling") . "\n";
        } else {
            echo "Using PHP 8.3+ with PHPUnit requires special handling\n";
        }
        
        // Add any PHP 8.3/8.4 specific flags
        $default_args[] = '--no-deprecations';
        
        // For PHPUnit 10+
        if (version_compare($phpunit_version, '10.0', '>=')) {
            if (function_exists('esc_html')) {
                echo esc_html("Using PHPUnit 10+ with PHP 8.3+") . "\n";
            } else {
                echo "Using PHPUnit 10+ with PHP 8.3+\n";
            }
            // No special settings needed for PHPUnit 10+
        } else {
            if (function_exists('esc_html')) {
                echo esc_html("Warning: Using older PHPUnit with PHP 8.3+, some features may not work correctly") . "\n";
            } else {
                echo "Warning: Using older PHPUnit with PHP 8.3+, some features may not work correctly\n";
            }
        }
    }
    
    // For PHP 8.0-8.2 (using PHPUnit 9.x typically)
    elseif ($php_minor_version >= 0 && $php_minor_version <= 2) {
        if (function_exists('esc_html')) {
            echo esc_html("Using PHP 8.0-8.2 with appropriate PHPUnit version") . "\n";
        } else {
            echo "Using PHP 8.0-8.2 with appropriate PHPUnit version\n";
        }
        
        // If using older PHPUnit with PHP 8.x
        if (version_compare($phpunit_version, '9.0', '<')) {
            if (function_exists('esc_html')) {
                echo esc_html("Using PHPUnit < 9.0 with PHP 8.x requires special handling") . "\n";
            } else {
                echo "Using PHPUnit < 9.0 with PHP 8.x requires special handling\n";
            }
            
            // Load the compatibility layer first
            if (file_exists(__DIR__ . '/tests/php8-compatibility.php')) {
                if (function_exists('esc_html')) {
                    echo esc_html("Loading PHP 8.x compatibility layer") . "\n";
                } else {
                    echo "Loading PHP 8.x compatibility layer\n";
                }
                require_once __DIR__ . '/tests/php8-compatibility.php';
            }
            
            // Use custom error settings
            error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED & ~E_STRICT);
        } else {
            if (function_exists('esc_html')) {
                echo esc_html("Using PHPUnit 9.x with PHP 8.0-8.2") . "\n";
            } else {
                echo "Using PHPUnit 9.x with PHP 8.0-8.2\n";
            }
        }
    }
}

// Build the command
$command  = escapeshellcmd($phpunit_path);
$command .= ' ' . implode(' ', array_map('escapeshellarg', array_merge($default_args, $args)));

if (function_exists('esc_html')) {
    echo esc_html("Running command: $command") . "\n";
    echo esc_html("-----------------------------------------------------------") . "\n";
} else {
    echo "Running command: $command\n";
    echo "-----------------------------------------------------------\n";
}

// Execute PHPUnit with the arguments
passthru($command, $return_var);

// Return the same exit code as PHPUnit
exit((int) $return_var);

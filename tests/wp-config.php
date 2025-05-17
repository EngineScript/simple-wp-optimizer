<?php
/**
 * WordPress test environment configuration.
 *
 * @package Simple_WP_Optimizer
 */

// Test with WordPress debug mode on
define( 'WP_DEBUG', true );

// Database settings - will be overridden by the test framework
define( 'DB_NAME', 'wordpress_test' );
define( 'DB_USER', 'root' );
define( 'DB_PASSWORD', '' );
define( 'DB_HOST', 'localhost' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

$table_prefix = 'wptests_';

define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Test Blog' );

define( 'WP_PHP_BINARY', 'php' );

// For integration testing
define( 'WPLANG', '' );
define( 'WP_CONTENT_DIR', dirname( __DIR__ ) . '/wp-content' );

// Prevent filesystem operations in tests
define( 'FS_METHOD', 'direct' );

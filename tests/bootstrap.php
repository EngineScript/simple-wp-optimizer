<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package Simple_WP_Optimizer
 */

// Load the Composer autoloader.
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Load the PHPUnit Polyfills for cross-version compatibility.
require_once dirname( __DIR__ ) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';

// Set up WordPress test environment constants.
if ( ! defined( 'WP_TESTS_DIR' ) ) {
    define( 'WP_TESTS_DIR', getenv( 'WP_TESTS_DIR' ) ?: '/tmp/wordpress-tests-lib' );
}

// Ensure WordPress tests configuration exists.
if ( ! file_exists( WP_TESTS_DIR . '/includes/bootstrap.php' ) ) {
    echo "Error: WordPress tests framework not found at " . WP_TESTS_DIR . PHP_EOL;
    echo "Please check your WP_TESTS_DIR environment variable" . PHP_EOL;
    exit( 1 );
}

// Ensure PHPMailer is available - try to locate it from WordPress core if needed.
if ( ! file_exists( WP_TESTS_DIR . '/includes/class-wp-phpmailer.php' ) ) {
    if ( file_exists( '/tmp/wordpress/wp-includes/class-phpmailer.php' ) ) {
        @mkdir( WP_TESTS_DIR . '/includes', 0777, true );
        copy( '/tmp/wordpress/wp-includes/class-phpmailer.php', WP_TESTS_DIR . '/includes/class-wp-phpmailer.php' );
        echo "Notice: Copied PHPMailer class to test environment" . PHP_EOL;
    }
}

// Manually load the plugin being tested.
function _manually_load_plugin() {
    require dirname( __DIR__ ) . '/simple-wp-optimizer.php';
}

// Start up the WP testing environment.
require WP_TESTS_DIR . '/includes/bootstrap.php';
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );
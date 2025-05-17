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

// Make sure the tests directory is in the include path.
if ( ! defined( 'WP_CONTENT_DIR' ) ) {
    define( 'WP_CONTENT_DIR', dirname( __DIR__ ) . '/tests/wp-content' );
}

if ( ! defined( 'WP_PLUGIN_DIR' ) ) {
    define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
}

// Define a test plugin directory name.
if ( ! defined( 'TEST_PLUGIN_DIR' ) ) {
    define( 'TEST_PLUGIN_DIR', dirname( __DIR__ ) );
}

// Manually load the plugin being tested.
function _manually_load_plugin() {
    require dirname( __DIR__ ) . '/simple-wp-optimizer.php';
}

// Start up the WP testing environment.
// Ideally, this would be automatically handled by the integration test action.
// If WP_TESTS_DIR is defined, we'll use it, otherwise we'll set up a basic mock.
if ( defined( 'WP_TESTS_DIR' ) && file_exists( WP_TESTS_DIR . '/includes/bootstrap.php' ) ) {
    require WP_TESTS_DIR . '/includes/bootstrap.php';
    tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );
} else {
    // Simple mock class if WP test suite isn't available
    class WP_UnitTestCase extends \Yoast\PHPUnitPolyfills\TestCases\TestCase {
        // Include test helper methods here
    }
}
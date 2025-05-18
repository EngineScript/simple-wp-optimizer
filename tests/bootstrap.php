<?php
/**
 * PHPUnit bootstrap file for plugin tests.
 *
 * @package Simple_WP_Optimizer
 */

// Load the Composer autoloader.
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Load the PHPUnit Polyfills for cross-version compatibility.
require_once dirname( __DIR__ ) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';

// Use the requested test WordPress instance
$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

// Handle trailing slash in path
if ( substr( $_tests_dir, -1 ) !== '/' ) {
	$_tests_dir .= '/';
}

// Make sure the tests directory exists
if ( ! file_exists( $_tests_dir . 'includes/functions.php' ) ) {
	echo "Could not find $_tests_dir/includes/functions.php, have you run the test installer script?" . PHP_EOL;
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . 'includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	// Define global $wp_widget_factory to prevent null reference errors
	global $wp_widget_factory;
	$wp_widget_factory = (object) ['widgets' => []];
	
	// Add a mock register_widget function that works with our fake widget factory
	if (!function_exists('register_widget')) {
		function register_widget($widget) {
			global $wp_widget_factory;
			$wp_widget_factory->widgets[] = $widget;
			return true;
		}
	}
	
	// Now load the plugin
	require_once dirname( __DIR__ ) . '/simple-wp-optimizer.php';
	
	// Initialize the plugin without triggering widgets_init
	remove_all_actions('widgets_init');
	do_action('plugins_loaded');
	do_action('init');
}

// Start up the WP testing environment.
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require $_tests_dir . 'includes/bootstrap.php';
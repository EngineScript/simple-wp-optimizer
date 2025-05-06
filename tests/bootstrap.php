<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Simple_WP_Optimizer
 */

// Load PHP 8.x compatibility helper first
if (file_exists(__DIR__ . '/php8-compatibility.php')) {
	require_once __DIR__ . '/php8-compatibility.php';
}

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	if (function_exists('esc_html')) {
		echo esc_html("Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?") . PHP_EOL;
	} else {
		echo "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL;
	}
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/simple-wp-optimizer.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';

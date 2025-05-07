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

// Check alternative paths if the initial path doesn't exist
if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	$alternative_dirs = array(
		'/wordpress-tests-lib',
		dirname( __FILE__ ) . '/../wordpress-tests-lib',
		'/tmp/wordpress-tests-lib',
	);
	
	foreach ( $alternative_dirs as $alt_dir ) {
		if ( file_exists( $alt_dir . '/includes/functions.php' ) ) {
			$_tests_dir = $alt_dir;
			break;
		}
	}
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

// Check and handle missing class-basic-object.php file
if ( ! file_exists( $_tests_dir . '/includes/class-basic-object.php' ) && file_exists( __DIR__ . '/class-basic-object.php' ) ) {
	// If class-basic-object.php is missing in test lib but exists locally, use our version
	echo "Using local class-basic-object.php as fallback..." . PHP_EOL;
	
	// If the includes directory doesn't exist in the test directory, create it
	if ( ! file_exists( $_tests_dir . '/includes' ) ) {
		mkdir( $_tests_dir . '/includes', 0777, true );
	}
	
	// Copy our local version to the test directory
	copy( __DIR__ . '/class-basic-object.php', $_tests_dir . '/includes/class-basic-object.php' );
}

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/simple-wp-optimizer.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';

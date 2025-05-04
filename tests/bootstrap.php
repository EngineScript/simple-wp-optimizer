<?php
/**
 * PHPUnit bootstrap file
 */

// Path to the WordPress tests checkout
$_tests_dir = getenv( 'WP_TESTS_DIR' ) ?: '/tmp/wordpress-tests-lib';

// Path to the plugin directory
$_plugin_dir = dirname( __DIR__ );

// Require the tests bootstrapper
if ( ! file_exists( "{$_tests_dir}/includes/functions.php" ) ) {
    echo "Could not find {$_tests_dir}/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL;
    exit( 1 );
}

// Give access to tests_add_filter() function
require_once "{$_tests_dir}/includes/functions.php";

/**
 * Manually load the plugin being tested
 */
function _manually_load_plugin() {
    require dirname( __DIR__ ) . '/simple-wp-optimizer.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment
require "{$_tests_dir}/includes/bootstrap.php";

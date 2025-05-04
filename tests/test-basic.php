<?php
/**
 * Basic test case.
 *
 * @package Simple_WP_Optimizer
 */

/**
 * Basic test class.
 */
class BasicTest extends WP_UnitTestCase {

	/**
	 * A basic test to ensure the plugin is loaded.
	 */
	public function test_plugin_is_loaded() {
		$this->assertTrue( function_exists( 'simple_wp_optimizer_init' ) );
	}

	/**
	 * A simple test to ensure the test suite is working.
	 */
	public function test_testing_environment() {
		$this->assertTrue( true );
	}
}

<?php
/**
 * Class Test_Simple_WP_Optimizer
 *
 * @package Simple_WP_Optimizer
 */

/**
 * Sample test case.
 */
class Test_Simple_WP_Optimizer extends WP_UnitTestCase {
	/**
	 * Test that the plugin can be loaded correctly.
	 */
	public function test_plugin_loaded() {
		// Simply check that the plugin file has been loaded
		$this->assertTrue( function_exists( 'simple_wp_optimizer_init' ) || class_exists( 'Simple_WP_Optimizer' ), 'Plugin functions not found, plugin may not be loaded correctly.' );
	}
}

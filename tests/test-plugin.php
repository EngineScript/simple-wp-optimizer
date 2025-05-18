<?php
/**
 * Class Test_Simple_WP_Optimizer
 *
 * @package Simple_WP_Optimizer
 */

/**
 * Simple test case for Simple WP Optimizer plugin.
 */
class Test_Simple_WP_Optimizer extends WP_UnitTestCase {
	/**
	 * Test that the plugin can be loaded correctly.
	 * 
	 * This test simply checks that the plugin loads in WordPress
	 * without causing any errors.
	 */
	public function test_plugin_loaded() {
		// Load the plugin directly to ensure it's available for testing
		require_once dirname( __DIR__ ) . '/simple-wp-optimizer.php';
		
		// Check for at least one function to verify the plugin loaded
		$this->assertTrue(function_exists('es_optimizer_init_settings'), 'Plugin was not loaded correctly');
	}
}

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
		// Load the plugin directly to ensure it's available for testing
		require_once dirname( __DIR__ ) . '/simple-wp-optimizer.php';
		
		// Check for specific functions that we know exist in the plugin
		$this->assertTrue( 
			function_exists( 'es_optimizer_init_settings' ) || 
			function_exists( 'disable_emojis' ) || 
			function_exists( 'remove_jquery_migrate' ), 
			'Plugin core functions not found, plugin may not be loaded correctly.' 
		);
	}
}

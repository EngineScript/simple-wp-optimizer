<?php
/**
 * Basic integration test for Simple WP Optimizer
 *
 * @package Simple_WP_Optimizer
 */

/**
 * Class Test_Simple_WP_Optimizer
 */
class Test_Simple_WP_Optimizer extends WP_UnitTestCase {

    /**
     * Test that the plugin can be loaded correctly.
     */
    public function test_plugin_loads() {
        // Check if the plugin main class or function exists
        $this->assertTrue(function_exists('simple_wp_optimizer_init') || class_exists('Simple_WP_Optimizer'), 
            'Plugin does not appear to be loaded correctly');
    }

    /**
     * A basic test ensuring that the WordPress version is set.
     */
    public function test_wp_version() {
        $this->assertTrue(defined('ABSPATH'), 'WordPress not loaded properly');
        // Use assertNotEmpty to accommodate PHPUnit versions (polyfill handles compatibility)
        $this->assertNotEmpty(get_bloginfo('version'), 'WordPress version not available');
    }
}

<?php
/**
 * Simple WP Optimizer Tests
 */

class TestSimpleWpOptimizer extends WP_UnitTestCase {
    
    /**
     * Test that the plugin is loaded
     */
    public function test_plugin_loaded() {
        $this->assertTrue( defined('ES_WP_OPTIMIZER_VERSION') );
    }
    
    /**
     * Test that default options exist
     */
    public function test_default_options_exist() {
        $this->assertTrue( function_exists('es_optimizer_get_default_options') );
        $defaults = es_optimizer_get_default_options();
        $this->assertIsArray( $defaults );
        $this->assertArrayHasKey( 'disable_emojis', $defaults );
    }
}

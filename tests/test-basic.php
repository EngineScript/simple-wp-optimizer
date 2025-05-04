<?php
/**
 * Class BasicTest
 *
 * @package Simple_WP_Optimizer
 */

/**
 * Basic test case.
 */
class BasicTest extends WP_UnitTestCase {

	/**
	 * A basic test that verifies the plugin is loaded.
	 */
	public function test_plugin_loaded() {
		$this->assertTrue( defined( 'ES_WP_OPTIMIZER_VERSION' ) );
	}
}

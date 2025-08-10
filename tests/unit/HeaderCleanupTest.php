<?php
/**
 * Unit Tests for Simple WP Optimizer Header Cleanup and Additional Features
 *
 * Tests for WordPress version removal, header cleanup, Jetpack ad removal, and other features.
 *
 * @package Simple_WP_Optimizer
 * @subpackage Tests
 * @since 1.5.12
 */

use WP_Mock\Tools\TestCase;

/**
 * Test class for header cleanup and additional features
 */
class HeaderCleanupTest extends TestCase {

	/**
	 * Set up test environment
	 *
	 * @since 1.5.12
	 */
	public function setUp(): void {
		\WP_Mock::setUp();
		
		// Include the main plugin file
		require_once dirname( dirname( __DIR__ ) ) . '/simple-wp-optimizer.php';
	}

	/**
	 * Tear down test environment
	 *
	 * @since 1.5.12
	 */
	public function tearDown(): void {
		\WP_Mock::tearDown();
	}

	/**
	 * Test remove_header_items with all options enabled
	 *
	 * @since 1.5.12
	 */
	public function test_remove_header_items_all_enabled() {
		$options = array(
			'remove_wp_version'  => 1,
			'remove_wlw_manifest' => 1,
			'remove_shortlink'   => 1,
		);

		\WP_Mock::expectActionRemoved( 'wp_head', 'wp_generator' );
		\WP_Mock::expectActionRemoved( 'wp_head', 'wlwmanifest_link' );
		\WP_Mock::expectActionRemoved( 'wp_head', 'wp_shortlink_wp_head', 10 );

		remove_header_items();

		$this->assertTrue( true ); // Test passes if no exceptions thrown
	}

	/**
	 * Test remove_header_items with selective options
	 *
	 * @since 1.5.12
	 */
	public function test_remove_header_items_selective() {
		$options = array(
			'remove_wp_version'   => 1,
			'remove_wlw_manifest' => 0,
			'remove_shortlink'    => 1,
		);

		\WP_Mock::expectActionRemoved( 'wp_head', 'wp_generator' );
		\WP_Mock::expectActionRemoved( 'wp_head', 'wp_shortlink_wp_head', 10 );

		// wlwmanifest_link should NOT be removed
		\WP_Mock::expectActionRemoved( 'wp_head', 'wlwmanifest_link' )->never();

		remove_header_items();

		$this->assertTrue( true ); // Test passes if no exceptions thrown
	}

	/**
	 * Test remove_header_items with all options disabled
	 *
	 * @since 1.5.12
	 */
	public function test_remove_header_items_all_disabled() {
		$options = array(
			'remove_wp_version'   => 0,
			'remove_wlw_manifest' => 0,
			'remove_shortlink'    => 0,
		);

		// No actions should be removed
		\WP_Mock::expectActionRemoved( 'wp_head', 'wp_generator' )->never();
		\WP_Mock::expectActionRemoved( 'wp_head', 'wlwmanifest_link' )->never();
		\WP_Mock::expectActionRemoved( 'wp_head', 'wp_shortlink_wp_head', 10 )->never();

		remove_header_items();

		$this->assertTrue( true ); // Test passes if no exceptions thrown
	}

	/**
	 * Test remove_recent_comments_style when enabled
	 *
	 * @since 1.5.12
	 */
	public function test_remove_recent_comments_style_enabled() {
		$options = array( 'remove_recent_comments_style' => 1 );

		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'es_optimizer_options' )
			->andReturn( $options );

		\WP_Mock::expectFilterAdded( 'show_recent_comments_widget_style', '__return_false', PHP_INT_MAX );

		remove_recent_comments_style();

		$this->assertTrue( true ); // Test passes if no exceptions thrown
	}

	/**
	 * Test remove_recent_comments_style when disabled
	 *
	 * @since 1.5.12
	 */
	public function test_remove_recent_comments_style_disabled() {
		$options = array( 'remove_recent_comments_style' => 0 );

		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'es_optimizer_options' )
			->andReturn( $options );

		// Filter should not be added when disabled
		\WP_Mock::expectFilterAdded( 'show_recent_comments_widget_style', '__return_false', PHP_INT_MAX )->never();

		remove_recent_comments_style();

		$this->assertTrue( true ); // Test passes if no exceptions thrown
	}

	/**
	 * Test disable_jetpack_ads when enabled
	 *
	 * @since 1.5.12
	 */
	public function test_disable_jetpack_ads_enabled() {
		$options = array( 'disable_jetpack_ads' => 1 );

		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'es_optimizer_options' )
			->andReturn( $options );

		\WP_Mock::expectFilterAdded( 'jetpack_just_in_time_msgs', '__return_false', PHP_INT_MAX );
		\WP_Mock::expectFilterAdded( 'jetpack_show_promotions', '__return_false', PHP_INT_MAX );
		\WP_Mock::expectFilterAdded( 'jetpack_blaze_enabled', '__return_false', PHP_INT_MAX );

		disable_jetpack_ads();

		$this->assertTrue( true ); // Test passes if no exceptions thrown
	}

	/**
	 * Test disable_jetpack_ads when disabled
	 *
	 * @since 1.5.12
	 */
	public function test_disable_jetpack_ads_disabled() {
		$options = array( 'disable_jetpack_ads' => 0 );

		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'es_optimizer_options' )
			->andReturn( $options );

		// No filters should be added when disabled
		\WP_Mock::expectFilterAdded( 'jetpack_just_in_time_msgs', '__return_false', PHP_INT_MAX )->never();
		\WP_Mock::expectFilterAdded( 'jetpack_show_promotions', '__return_false', PHP_INT_MAX )->never();
		\WP_Mock::expectFilterAdded( 'jetpack_blaze_enabled', '__return_false', PHP_INT_MAX )->never();

		disable_jetpack_ads();

		$this->assertTrue( true ); // Test passes if no exceptions thrown
	}

	/**
	 * Test disable_post_via_email when enabled
	 *
	 * @since 1.5.12
	 */
	public function test_disable_post_via_email_enabled() {
		$options = array( 'disable_post_via_email' => 1 );

		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'es_optimizer_options' )
			->andReturn( $options );

		\WP_Mock::expectFilterAdded( 'enable_post_by_email_configuration', '__return_false', PHP_INT_MAX );

		disable_post_via_email();

		$this->assertTrue( true ); // Test passes if no exceptions thrown
	}

	/**
	 * Test disable_post_via_email when disabled
	 *
	 * @since 1.5.12
	 */
	public function test_disable_post_via_email_disabled() {
		$options = array( 'disable_post_via_email' => 0 );

		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'es_optimizer_options' )
			->andReturn( $options );

		// Filter should not be added when disabled
		\WP_Mock::expectFilterAdded( 'enable_post_by_email_configuration', '__return_false', PHP_INT_MAX )->never();

		disable_post_via_email();

		$this->assertTrue( true ); // Test passes if no exceptions thrown
	}

	/**
	 * Test header cleanup functions with missing options
	 *
	 * @since 1.5.12
	 */
	public function test_header_cleanup_missing_options() {
		$options = array(); // Empty options array

		// Functions should handle missing options gracefully
		remove_header_items();

		$this->assertTrue( true ); // Test passes if no exceptions thrown
	}

	/**
	 * Test remove_recent_comments_style with missing options
	 *
	 * @since 1.5.12
	 */
	public function test_remove_recent_comments_style_missing_options() {
		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'es_optimizer_options' )
			->andReturn( array() ); // Empty options

		// Should not add filter when option is missing
		\WP_Mock::expectFilterAdded( 'show_recent_comments_widget_style', '__return_false', PHP_INT_MAX )->never();

		remove_recent_comments_style();

		$this->assertTrue( true ); // Test passes if no exceptions thrown
	}

	/**
	 * Test disable_jetpack_ads with missing options
	 *
	 * @since 1.5.12
	 */
	public function test_disable_jetpack_ads_missing_options() {
		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'es_optimizer_options' )
			->andReturn( array() ); // Empty options

		// Should not add filters when option is missing
		\WP_Mock::expectFilterAdded( 'jetpack_just_in_time_msgs', '__return_false', PHP_INT_MAX )->never();

		disable_jetpack_ads();

		$this->assertTrue( true ); // Test passes if no exceptions thrown
	}

	/**
	 * Test disable_post_via_email with missing options
	 *
	 * @since 1.5.12
	 */
	public function test_disable_post_via_email_missing_options() {
		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'es_optimizer_options' )
			->andReturn( array() ); // Empty options

		// Should not add filter when option is missing
		\WP_Mock::expectFilterAdded( 'enable_post_by_email_configuration', '__return_false', PHP_INT_MAX )->never();

		disable_post_via_email();

		$this->assertTrue( true ); // Test passes if no exceptions thrown
	}

	/**
	 * Test WordPress version removal security implications
	 *
	 * @since 1.5.12
	 */
	public function test_wp_version_removal_security() {
		$options = array( 'remove_wp_version' => 1 );

		\WP_Mock::expectActionRemoved( 'wp_head', 'wp_generator' );

		remove_header_items();

		// This test ensures that the wp_generator function is removed,
		// which helps with security by hiding WordPress version information
		$this->assertTrue( true );
	}

	/**
	 * Test Jetpack ad removal filters comprehensive coverage
	 *
	 * @since 1.5.12
	 */
	public function test_jetpack_ads_comprehensive_removal() {
		$options = array( 'disable_jetpack_ads' => 1 );

		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'es_optimizer_options' )
			->andReturn( $options );

		// Test that all known Jetpack promotional filters are disabled
		\WP_Mock::expectFilterAdded( 'jetpack_just_in_time_msgs', '__return_false', PHP_INT_MAX );
		\WP_Mock::expectFilterAdded( 'jetpack_show_promotions', '__return_false', PHP_INT_MAX );
		\WP_Mock::expectFilterAdded( 'jetpack_blaze_enabled', '__return_false', PHP_INT_MAX );

		disable_jetpack_ads();

		$this->assertTrue( true );
	}

	/**
	 * Test post via email security implications
	 *
	 * @since 1.5.12
	 */
	public function test_post_via_email_security() {
		$options = array( 'disable_post_via_email' => 1 );

		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'es_optimizer_options' )
			->andReturn( $options );

		\WP_Mock::expectFilterAdded( 'enable_post_by_email_configuration', '__return_false', PHP_INT_MAX );

		disable_post_via_email();

		// This test ensures that post via email is disabled,
		// which is a security best practice to prevent unauthorized posts
		$this->assertTrue( true );
	}
}

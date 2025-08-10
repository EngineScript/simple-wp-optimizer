<?php
/**
 * Unit Tests for Simple WP Optimizer Options and Caching Functions
 *
 * Tests for option management, caching, and settings functionality.
 *
 * @package Simple_WP_Optimizer
 * @subpackage Tests
 * @since 1.5.12
 */

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
// phpcs:disable WordPress.WP.AlternativeFunctions.parse_url_parse_url
// phpcs:disable WordPress.WP.AlternativeFunctions.strip_tags_strip_tags

use WP_Mock\Tools\TestCase;

/**
 * Test class for options and caching functions
 */
class OptionsTest extends TestCase {

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
	 * Test es_optimizer_get_options with cached options
	 *
	 * @since 1.5.12
	 */
	public function test_get_options_caching() {
		$sample_options = TestHelper::get_sample_plugin_options();

		// Mock get_option to be called only once (caching test)
		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'es_optimizer_options', \Mockery::type( 'array' ) )
			->andReturn( $sample_options );

		// First call should hit the database
		$result1 = es_optimizer_get_options();
		$this->assertEquals( $sample_options, $result1 );

		// Second call should use cache (get_option should not be called again)
		$result2 = es_optimizer_get_options();
		$this->assertEquals( $sample_options, $result2 );
		$this->assertSame( $result1, $result2 );
	}

	/**
	 * Test es_optimizer_get_options with no existing options
	 *
	 * @since 1.5.12
	 */
	public function test_get_options_no_existing_options() {
		$default_options = es_optimizer_get_default_options();

		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'es_optimizer_options', $default_options )
			->andReturn( $default_options );

		$result = es_optimizer_get_options();
		
		$this->assertEquals( $default_options, $result );
	}

	/**
	 * Test es_optimizer_clear_options_cache
	 *
	 * @since 1.5.12
	 */
	public function test_clear_options_cache() {
		$sample_options = TestHelper::get_sample_plugin_options();

		\WP_Mock::userFunction( 'get_option' )
			->twice()
			->with( 'es_optimizer_options', \Mockery::type( 'array' ) )
			->andReturn( $sample_options );

		// First call to establish cache
		$result1 = es_optimizer_get_options();
		
		// Clear cache
		es_optimizer_clear_options_cache();
		
		// Second call should hit database again (cache cleared)
		$result2 = es_optimizer_get_options();
		
		$this->assertEquals( $sample_options, $result1 );
		$this->assertEquals( $sample_options, $result2 );
	}

	/**
	 * Test es_optimizer_init_settings
	 *
	 * @since 1.5.12
	 */
	public function test_init_settings() {
		\WP_Mock::userFunction( 'register_setting' )
			->once()
			->with(
				'es_optimizer_settings',
				'es_optimizer_options',
				\Mockery::type( 'array' )
			);

		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'es_optimizer_options' )
			->andReturn( false );

		\WP_Mock::userFunction( 'add_option' )
			->once()
			->with( 'es_optimizer_options', \Mockery::type( 'array' ) );

		es_optimizer_init_settings();

		$this->assertTrue( true ); // Test passes if no exceptions thrown
	}

	/**
	 * Test es_optimizer_init_settings with existing options
	 *
	 * @since 1.5.12
	 */
	public function test_init_settings_existing_options() {
		$existing_options = TestHelper::get_sample_plugin_options();

		\WP_Mock::userFunction( 'register_setting' )
			->once()
			->with(
				'es_optimizer_settings',
				'es_optimizer_options',
				\Mockery::type( 'array' )
			);

		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'es_optimizer_options' )
			->andReturn( $existing_options );

		// add_option should not be called if options already exist
		\WP_Mock::userFunction( 'add_option' )->never();

		es_optimizer_init_settings();

		$this->assertTrue( true ); // Test passes if no exceptions thrown
	}

	/**
	 * Test es_optimizer_add_settings_page
	 *
	 * @since 1.5.12
	 */
	public function test_add_settings_page() {
		$hook_suffix = 'settings_page_es-optimizer-settings';

		\WP_Mock::userFunction( 'add_options_page' )
			->once()
			->with(
				'WP Optimizer Settings',
				'WP Optimizer',
				'manage_options',
				'es-optimizer-settings',
				'es_optimizer_settings_page'
			)
			->andReturn( $hook_suffix );

		\WP_Mock::expectActionAdded( "load-{$hook_suffix}", 'es_optimizer_load_admin_assets' );

		es_optimizer_add_settings_page();

		$this->assertTrue( true ); // Test passes if no exceptions thrown
	}

	/**
	 * Test es_optimizer_load_admin_assets
	 *
	 * @since 1.5.12
	 */
	public function test_load_admin_assets() {
		\WP_Mock::expectActionAdded( 'admin_enqueue_scripts', 'es_optimizer_enqueue_admin_scripts' );

		es_optimizer_load_admin_assets();

		$this->assertTrue( true ); // Test passes if no exceptions thrown
	}

	/**
	 * Test es_optimizer_enqueue_admin_scripts
	 *
	 * @since 1.5.12
	 */
	public function test_enqueue_admin_scripts() {
		// Currently this function doesn't do anything, but test it exists
		es_optimizer_enqueue_admin_scripts();

		$this->assertTrue( true ); // Test passes if no exceptions thrown
	}

	/**
	 * Test es_optimizer_add_settings_link
	 *
	 * @since 1.5.12
	 */
	public function test_add_settings_link() {
		\WP_Mock::userFunction( 'admin_url' )
			->once()
			->with( 'options-general.php?page=es-optimizer-settings' )
			->andReturn( 'http://example.com/wp-admin/options-general.php?page=es-optimizer-settings' );

		\WP_Mock::userFunction( '__' )
			->once()
			->with( 'Settings', 'simple-wp-optimizer' )
			->andReturn( 'Settings' );

		$original_links = array(
			'deactivate' => '<a href="deactivate">Deactivate</a>',
		);

		$result = es_optimizer_add_settings_link( $original_links );

		$this->assertCount( 2, $result );
		$this->assertStringContains( 'Settings', $result[0] );
		$this->assertStringContains( 'options-general.php?page=es-optimizer-settings', $result[0] );
		$this->assertEquals( 'deactivate', array_keys( $result )[1] );
	}

	/**
	 * Test option validation edge cases
	 *
	 * @since 1.5.12
	 */
	public function test_validate_options_edge_cases() {
		\WP_Mock::userFunction( 'sanitize_text_field' )->andReturnUsing( function( $text ) {
			return trim( strip_tags( $text ) );
		});
		\WP_Mock::userFunction( 'wp_unslash' )->andReturnUsing( function( $text ) {
			return $text;
		});
		\WP_Mock::userFunction( 'wp_verify_nonce' )->andReturn( true );
		\WP_Mock::userFunction( 'wp_parse_url' )->andReturnUsing( function( $url ) {
			return parse_url( $url );
		});
		\WP_Mock::userFunction( 'esc_url_raw' )->andReturnUsing( function( $url ) {
			return $url;
		});

		// Set up $_POST data
		$_POST['es_optimizer_settings_nonce'] = 'valid_nonce';

		// Test with empty input
		$result = es_optimizer_validate_options( array() );
		
		// All checkboxes should default to 0
		$checkboxes = array(
			'disable_emojis',
			'remove_jquery_migrate',
			'disable_classic_theme_styles',
			'remove_wp_version',
			'remove_wlw_manifest',
			'remove_shortlink',
			'remove_recent_comments_style',
			'enable_dns_prefetch',
			'disable_jetpack_ads',
			'disable_post_via_email',
		);

		foreach ( $checkboxes as $checkbox ) {
			$this->assertEquals( 0, $result[ $checkbox ], "Checkbox {$checkbox} should default to 0" );
		}

		// Clean up
		unset( $_POST['es_optimizer_settings_nonce'] );
	}

	/**
	 * Test default options values
	 *
	 * @since 1.5.12
	 */
	public function test_default_options_values() {
		$defaults = es_optimizer_get_default_options();

		// Test that critical security options are enabled by default
		$this->assertEquals( 1, $defaults['remove_wp_version'], 'WordPress version removal should be enabled by default for security' );
		$this->assertEquals( 1, $defaults['disable_post_via_email'], 'Post via email should be disabled by default for security' );

		// Test that performance options are enabled by default
		$this->assertEquals( 1, $defaults['disable_emojis'], 'Emoji disable should be enabled by default for performance' );
		$this->assertEquals( 1, $defaults['remove_jquery_migrate'], 'jQuery migrate removal should be enabled by default for performance' );

		// Test DNS prefetch domains format
		$this->assertIsString( $defaults['dns_prefetch_domains'] );
		$domains = explode( "\n", $defaults['dns_prefetch_domains'] );
		foreach ( $domains as $domain ) {
			$domain = trim( $domain );
			if ( ! empty( $domain ) ) {
				$this->assertStringStartsWith( 'https://', $domain, 'Default DNS prefetch domains should use HTTPS' );
			}
		}
	}
}

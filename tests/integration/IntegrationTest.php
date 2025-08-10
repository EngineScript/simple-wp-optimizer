<?php
/**
 * Integration Tests for Simple WP Optimizer
 *
 * Tests for WordPress hooks, admin functionality, and frontend output.
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
 * Test class for integration testing
 */
class IntegrationTest extends TestCase {

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
	 * Test that all WordPress hooks are properly registered
	 *
	 * @since 1.5.12
	 */
	public function test_wordpress_hooks_registration() {
		// Test admin_init hook for settings initialization
		\WP_Mock::expectActionAdded( 'admin_init', 'es_optimizer_init_settings' );

		// Test admin_menu hook for settings page
		\WP_Mock::expectActionAdded( 'admin_menu', 'es_optimizer_add_settings_page' );

		// Test init hooks for various optimizations
		\WP_Mock::expectActionAdded( 'init', 'disable_emojis' );
		\WP_Mock::expectActionAdded( 'init', 'remove_header_items' );
		\WP_Mock::expectActionAdded( 'init', 'remove_recent_comments_style' );
		\WP_Mock::expectActionAdded( 'init', 'disable_jetpack_ads' );
		\WP_Mock::expectActionAdded( 'init', 'disable_post_via_email' );

		// Test wp_head hook for DNS prefetch
		\WP_Mock::expectActionAdded( 'wp_head', 'add_dns_prefetch', 0 );

		// Test wp_default_scripts hook for jQuery migrate removal
		\WP_Mock::expectActionAdded( 'wp_default_scripts', 'remove_jquery_migrate' );

		// Test wp_enqueue_scripts hook for classic theme styles
		\WP_Mock::expectActionAdded( 'wp_enqueue_scripts', 'disable_classic_theme_styles', 100 );

		// Since hooks are registered when the plugin file is included,
		// we just need to verify they're registered correctly
		$this->assertTrue( true );
	}

	/**
	 * Test plugin settings link filter
	 *
	 * @since 1.5.12
	 */
	public function test_plugin_settings_link_filter() {
		\WP_Mock::userFunction( 'plugin_basename' )
			->once()
			->andReturn( 'simple-wp-optimizer/simple-wp-optimizer.php' );

		\WP_Mock::expectFilterAdded( 
			'plugin_action_links_simple-wp-optimizer/simple-wp-optimizer.php', 
			'es_optimizer_add_settings_link' 
		);

		$this->assertTrue( true );
	}

	/**
	 * Test complete DNS prefetch workflow
	 *
	 * @since 1.5.12
	 */
	public function test_dns_prefetch_complete_workflow() {
		// Test validation, saving, and output
		\WP_Mock::userFunction( 'wp_parse_url' )->andReturnUsing( function( $url ) {
			return parse_url( $url );
		});

		\WP_Mock::userFunction( 'esc_url_raw' )->andReturnUsing( function( $url ) {
			return $url;
		});

		// Test domain validation
		$valid_domains = 'https://fonts.googleapis.com' . "\n" . 'https://ajax.googleapis.com';
		$result = es_optimizer_validate_dns_domains( $valid_domains );
		
		$this->assertEquals( $valid_domains, $result );

		// Test output generation
		\WP_Mock::userFunction( 'is_admin' )->andReturn( false );
		\WP_Mock::userFunction( 'wp_doing_ajax' )->andReturn( false );
		\WP_Mock::userFunction( 'get_option' )->andReturn( array(
			'enable_dns_prefetch' => 1,
			'dns_prefetch_domains' => $valid_domains,
		));
		\WP_Mock::userFunction( 'esc_url' )->andReturnUsing( function( $url ) {
			return $url;
		});

		ob_start();
		add_dns_prefetch();
		$output = ob_get_clean();

		$this->assertStringContains( '<link rel="dns-prefetch"', $output );
		$this->assertStringContains( 'fonts.googleapis.com', $output );
		$this->assertStringContains( 'ajax.googleapis.com', $output );
	}

	/**
	 * Test emoji removal complete workflow
	 *
	 * @since 1.5.12
	 */
	public function test_emoji_removal_complete_workflow() {
		$options = array( 'disable_emojis' => 1 );

		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'es_optimizer_options' )
			->andReturn( $options );

		// Test that all emoji-related hooks are removed
		\WP_Mock::expectActionRemoved( 'wp_head', 'print_emoji_detection_script', 7 );
		\WP_Mock::expectActionRemoved( 'wp_print_styles', 'print_emoji_styles' );
		\WP_Mock::expectActionRemoved( 'admin_print_scripts', 'print_emoji_detection_script' );
		\WP_Mock::expectActionRemoved( 'admin_print_styles', 'print_emoji_styles' );
		\WP_Mock::expectFilterRemoved( 'the_content_feed', 'wp_staticize_emoji' );
		\WP_Mock::expectFilterRemoved( 'comment_text_rss', 'wp_staticize_emoji' );
		\WP_Mock::expectFilterRemoved( 'wp_mail', 'wp_staticize_emoji_for_email' );

		// Test that TinyMCE and DNS prefetch filters are added
		\WP_Mock::expectFilterAdded( 'tiny_mce_plugins', 'disable_emojis_tinymce' );
		\WP_Mock::expectFilterAdded( 'wp_resource_hints', 'disable_emojis_remove_dns_prefetch', 10, 2 );

		disable_emojis();

		// Test TinyMCE plugin removal
		$plugins = array( 'wpemoji', 'link', 'image' );
		$filtered_plugins = disable_emojis_tinymce( $plugins );
		
		$this->assertNotContains( 'wpemoji', $filtered_plugins );
		$this->assertContains( 'link', $filtered_plugins );
		$this->assertContains( 'image', $filtered_plugins );
	}

	/**
	 * Test settings page complete workflow
	 *
	 * @since 1.5.12
	 */
	public function test_settings_page_complete_workflow() {
		// Test settings page creation
		$hook_suffix = 'settings_page_es-optimizer-settings';

		\WP_Mock::userFunction( 'add_options_page' )
			->once()
			->andReturn( $hook_suffix );

		\WP_Mock::expectActionAdded( "load-{$hook_suffix}", 'es_optimizer_load_admin_assets' );

		es_optimizer_add_settings_page();

		// Test admin assets loading
		\WP_Mock::expectActionAdded( 'admin_enqueue_scripts', 'es_optimizer_enqueue_admin_scripts' );

		es_optimizer_load_admin_assets();

		$this->assertTrue( true );
	}

	/**
	 * Test option validation complete workflow with mixed input
	 *
	 * @since 1.5.12
	 */
	public function test_option_validation_complete_workflow() {
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
		\WP_Mock::userFunction( 'esc_html' )->andReturnUsing( function( $text ) {
			return htmlspecialchars( $text );
		});
		\WP_Mock::userFunction( 'esc_html__' )->andReturnUsing( function( $text ) {
			return $text;
		});
		\WP_Mock::userFunction( 'add_settings_error' );

		// Set up $_POST data
		$_POST['es_optimizer_settings_nonce'] = 'valid_nonce';

		$input = array(
			'disable_emojis'        => '1',
			'remove_jquery_migrate' => '', // Should become 0
			'enable_dns_prefetch'   => '1',
			'dns_prefetch_domains'  => "https://fonts.googleapis.com\nhttp://invalid.com\nhttps://valid.example.com",
		);

		$result = es_optimizer_validate_options( $input );

		// Test checkbox validation
		$this->assertEquals( 1, $result['disable_emojis'] );
		$this->assertEquals( 0, $result['remove_jquery_migrate'] );
		$this->assertEquals( 1, $result['enable_dns_prefetch'] );

		// Test DNS domain filtering (invalid domains should be removed)
		$expected_domains = "https://fonts.googleapis.com\nhttps://valid.example.com";
		$this->assertEquals( $expected_domains, $result['dns_prefetch_domains'] );

		// Clean up
		unset( $_POST['es_optimizer_settings_nonce'] );
	}

	/**
	 * Test header cleanup integration
	 *
	 * @since 1.5.12
	 */
	public function test_header_cleanup_integration() {
		$options = array(
			'remove_wp_version'           => 1,
			'remove_wlw_manifest'         => 1,
			'remove_shortlink'            => 1,
			'remove_recent_comments_style' => 1,
		);

		// Test header items removal
		\WP_Mock::expectActionRemoved( 'wp_head', 'wp_generator' );
		\WP_Mock::expectActionRemoved( 'wp_head', 'wlwmanifest_link' );
		\WP_Mock::expectActionRemoved( 'wp_head', 'wp_shortlink_wp_head', 10 );

		remove_header_items();

		// Test recent comments style removal
		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'es_optimizer_options' )
			->andReturn( $options );

		\WP_Mock::expectFilterAdded( 'show_recent_comments_widget_style', '__return_false', PHP_INT_MAX );

		remove_recent_comments_style();

		$this->assertTrue( true );
	}

	/**
	 * Test plugin activation workflow
	 *
	 * @since 1.5.12
	 */
	public function test_plugin_activation_workflow() {
		// Test default options creation
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

		// Test that default options include all required settings
		$defaults = es_optimizer_get_default_options();
		
		$required_options = array(
			'disable_emojis',
			'remove_jquery_migrate',
			'disable_classic_theme_styles',
			'remove_wp_version',
			'remove_wlw_manifest',
			'remove_shortlink',
			'remove_recent_comments_style',
			'enable_dns_prefetch',
			'dns_prefetch_domains',
			'disable_jetpack_ads',
			'disable_post_via_email',
		);

		foreach ( $required_options as $option ) {
			$this->assertArrayHasKey( $option, $defaults );
		}

		$this->assertTrue( true );
	}

	/**
	 * Test performance optimization integration
	 *
	 * @since 1.5.12
	 */
	public function test_performance_optimization_integration() {
		// Test jQuery migrate removal
		\WP_Mock::userFunction( 'is_admin' )->andReturn( false );

		$scripts = new stdClass();
		$scripts->registered = array(
			'jquery' => new stdClass(),
		);
		$scripts->registered['jquery']->deps = array( 'jquery-core', 'jquery-migrate' );

		remove_jquery_migrate( $scripts );

		$this->assertNotContains( 'jquery-migrate', $scripts->registered['jquery']->deps );

		// Test classic theme styles removal
		\WP_Mock::userFunction( 'wp_deregister_style' )
			->once()
			->with( 'classic-theme-styles' );

		\WP_Mock::userFunction( 'wp_dequeue_style' )
			->once()
			->with( 'classic-theme-styles' );

		disable_classic_theme_styles();

		$this->assertTrue( true );
	}

	/**
	 * Test security feature integration
	 *
	 * @since 1.5.12
	 */
	public function test_security_feature_integration() {
		// Test that security-focused options are enabled by default
		$defaults = es_optimizer_get_default_options();

		$this->assertEquals( 1, $defaults['remove_wp_version'], 'WordPress version should be hidden for security' );
		$this->assertEquals( 1, $defaults['disable_post_via_email'], 'Post via email should be disabled for security' );

		// Test DNS domain security validation
		$insecure_domains = array(
			'http://insecure.com',           // HTTP not HTTPS
			'javascript:alert(1)',           // XSS attempt
			'https://example.com/file.js',   // File path
			'https://localhost',             // Local address
		);

		foreach ( $insecure_domains as $domain ) {
			$result = es_optimizer_validate_single_domain( $domain );
			$this->assertFalse( $result['valid'], "Domain {$domain} should be rejected for security" );
		}

		$this->assertTrue( true );
	}
}

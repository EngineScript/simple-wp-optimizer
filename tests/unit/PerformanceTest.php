<?php
/**
 * Unit Tests for Simple WP Optimizer Performance Functions
 *
 * Tests for DNS prefetch, emoji removal, jQuery migrate removal, and other performance optimizations.
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
 * Test class for performance optimization functions
 */
class PerformanceTest extends TestCase {

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
	 * Test add_dns_prefetch when enabled
	 *
	 * @since 1.5.12
	 */
	public function test_add_dns_prefetch_enabled() {
		\WP_Mock::userFunction( 'is_admin' )->andReturn( false );
		\WP_Mock::userFunction( 'wp_doing_ajax' )->andReturn( false );

		$options = array(
			'enable_dns_prefetch'  => 1,
			'dns_prefetch_domains' => implode( "\n", TestHelper::get_sample_dns_prefetch_domains() ),
		);

		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'es_optimizer_options' )
			->andReturn( $options );

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
	 * Test add_dns_prefetch when disabled
	 *
	 * @since 1.5.12
	 */
	public function test_add_dns_prefetch_disabled() {
		\WP_Mock::userFunction( 'is_admin' )->andReturn( false );
		\WP_Mock::userFunction( 'wp_doing_ajax' )->andReturn( false );

		$options = array(
			'enable_dns_prefetch' => 0,
		);

		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'es_optimizer_options' )
			->andReturn( $options );

		ob_start();
		add_dns_prefetch();
		$output = ob_get_clean();

		$this->assertEmpty( $output );
	}

	/**
	 * Test add_dns_prefetch skipped on admin pages
	 *
	 * @since 1.5.12
	 */
	public function test_add_dns_prefetch_skipped_admin() {
		\WP_Mock::userFunction( 'is_admin' )->andReturn( true );

		// get_option should not be called when skipped
		\WP_Mock::userFunction( 'get_option' )->never();

		ob_start();
		add_dns_prefetch();
		$output = ob_get_clean();

		$this->assertEmpty( $output );
	}

	/**
	 * Test add_dns_prefetch skipped during AJAX
	 *
	 * @since 1.5.12
	 */
	public function test_add_dns_prefetch_skipped_ajax() {
		\WP_Mock::userFunction( 'is_admin' )->andReturn( false );
		\WP_Mock::userFunction( 'wp_doing_ajax' )->andReturn( true );

		// get_option should not be called when skipped
		\WP_Mock::userFunction( 'get_option' )->never();

		ob_start();
		add_dns_prefetch();
		$output = ob_get_clean();

		$this->assertEmpty( $output );
	}

	/**
	 * Test add_dns_prefetch with invalid domains filtered out
	 *
	 * @since 1.5.12
	 */
	public function test_add_dns_prefetch_filters_invalid_domains() {
		\WP_Mock::userFunction( 'is_admin' )->andReturn( false );
		\WP_Mock::userFunction( 'wp_doing_ajax' )->andReturn( false );

		$mixed_domains = array_merge(
			TestHelper::get_sample_dns_prefetch_domains(),
			TestHelper::get_invalid_dns_prefetch_domains()
		);

		$options = array(
			'enable_dns_prefetch'  => 1,
			'dns_prefetch_domains' => implode( "\n", $mixed_domains ),
		);

		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'es_optimizer_options' )
			->andReturn( $options );

		\WP_Mock::userFunction( 'esc_url' )->andReturnUsing( function( $url ) {
			return $url;
		});

		ob_start();
		add_dns_prefetch();
		$output = ob_get_clean();

		// Should contain valid domains
		$this->assertStringContains( 'fonts.googleapis.com', $output );
		
		// Should not contain invalid domains
		$this->assertStringNotContains( '/path/to/file.js', $output );
		$this->assertStringNotContains( 'javascript:alert', $output );
	}

	/**
	 * Test add_dns_prefetch static caching
	 *
	 * @since 1.5.12
	 */
	public function test_add_dns_prefetch_static_caching() {
		\WP_Mock::userFunction( 'is_admin' )->andReturn( false );
		\WP_Mock::userFunction( 'wp_doing_ajax' )->andReturn( false );

		$options = array(
			'enable_dns_prefetch'  => 1,
			'dns_prefetch_domains' => 'https://fonts.googleapis.com',
		);

		// get_option should only be called once due to static caching
		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'es_optimizer_options' )
			->andReturn( $options );

		\WP_Mock::userFunction( 'esc_url' )->andReturnUsing( function( $url ) {
			return $url;
		});

		// First call
		ob_start();
		add_dns_prefetch();
		$output1 = ob_get_clean();

		// Second call - should use cache
		ob_start();
		add_dns_prefetch();
		$output2 = ob_get_clean();

		$this->assertEquals( $output1, $output2 );
		$this->assertStringContains( 'fonts.googleapis.com', $output1 );
	}

	/**
	 * Test disable_emojis function when enabled
	 *
	 * @since 1.5.12
	 */
	public function test_disable_emojis_enabled() {
		$options = array( 'disable_emojis' => 1 );

		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'es_optimizer_options' )
			->andReturn( $options );

		\WP_Mock::expectActionRemoved( 'wp_head', 'print_emoji_detection_script', 7 );
		\WP_Mock::expectActionRemoved( 'wp_print_styles', 'print_emoji_styles' );
		\WP_Mock::expectActionRemoved( 'admin_print_scripts', 'print_emoji_detection_script' );
		\WP_Mock::expectActionRemoved( 'admin_print_styles', 'print_emoji_styles' );
		\WP_Mock::expectFilterRemoved( 'the_content_feed', 'wp_staticize_emoji' );
		\WP_Mock::expectFilterRemoved( 'comment_text_rss', 'wp_staticize_emoji' );
		\WP_Mock::expectFilterRemoved( 'wp_mail', 'wp_staticize_emoji_for_email' );
		\WP_Mock::expectFilterAdded( 'tiny_mce_plugins', 'disable_emojis_tinymce' );
		\WP_Mock::expectFilterAdded( 'wp_resource_hints', 'disable_emojis_remove_dns_prefetch', 10, 2 );

		disable_emojis();

		$this->assertTrue( true ); // Test passes if no exceptions thrown
	}

	/**
	 * Test disable_emojis function when disabled
	 *
	 * @since 1.5.12
	 */
	public function test_disable_emojis_disabled() {
		$options = array( 'disable_emojis' => 0 );

		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'es_optimizer_options' )
			->andReturn( $options );

		// No actions or filters should be modified
		\WP_Mock::expectActionRemoved( 'wp_head', 'print_emoji_detection_script', 7 )->never();

		disable_emojis();

		$this->assertTrue( true ); // Test passes if no exceptions thrown
	}

	/**
	 * Test disable_emojis_tinymce function
	 *
	 * @since 1.5.12
	 */
	public function test_disable_emojis_tinymce() {
		$plugins = array( 'wpemoji', 'other_plugin', 'another_plugin' );
		
		$result = disable_emojis_tinymce( $plugins );
		
		$this->assertNotContains( 'wpemoji', $result );
		$this->assertContains( 'other_plugin', $result );
		$this->assertContains( 'another_plugin', $result );
	}

	/**
	 * Test disable_emojis_tinymce with non-array input
	 *
	 * @since 1.5.12
	 */
	public function test_disable_emojis_tinymce_non_array() {
		$result = disable_emojis_tinymce( null );
		
		$this->assertIsArray( $result );
		$this->assertEmpty( $result );
	}

	/**
	 * Test disable_emojis_remove_dns_prefetch function
	 *
	 * @since 1.5.12
	 */
	public function test_disable_emojis_remove_dns_prefetch() {
		\WP_Mock::userFunction( 'apply_filters' )
			->once()
			->with( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/' )
			->andReturn( 'https://s.w.org/images/core/emoji/2/svg/' );

		$urls = array(
			'https://fonts.googleapis.com',
			'https://s.w.org/images/core/emoji/2/svg/',
			'https://ajax.googleapis.com',
		);

		$result = disable_emojis_remove_dns_prefetch( $urls, 'dns-prefetch' );

		$expected = array(
			'https://fonts.googleapis.com',
			'https://ajax.googleapis.com',
		);

		$this->assertEquals( $expected, array_values( $result ) );
	}

	/**
	 * Test disable_emojis_remove_dns_prefetch with non-dns-prefetch relation
	 *
	 * @since 1.5.12
	 */
	public function test_disable_emojis_remove_dns_prefetch_other_relation() {
		$urls = array(
			'https://fonts.googleapis.com',
			'https://s.w.org/images/core/emoji/2/svg/',
		);

		$result = disable_emojis_remove_dns_prefetch( $urls, 'preload' );

		// Should return unchanged when not dns-prefetch
		$this->assertEquals( $urls, $result );
	}

	/**
	 * Test remove_jquery_migrate when enabled
	 *
	 * @since 1.5.12
	 */
	public function test_remove_jquery_migrate_enabled() {
		$options = array( 'remove_jquery_migrate' => 1 );

		\WP_Mock::userFunction( 'is_admin' )->andReturn( false );

		// Mock WP_Scripts object
		$scripts = new stdClass();
		$scripts->registered = array(
			'jquery' => new stdClass(),
		);
		$scripts->registered['jquery']->deps = array( 'jquery-core', 'jquery-migrate' );

		// Call the function
		remove_jquery_migrate( $scripts );

		// Check that jquery-migrate was removed from dependencies
		$this->assertNotContains( 'jquery-migrate', $scripts->registered['jquery']->deps );
		$this->assertContains( 'jquery-core', $scripts->registered['jquery']->deps );
	}

	/**
	 * Test remove_jquery_migrate when disabled
	 *
	 * @since 1.5.12
	 */
	public function test_remove_jquery_migrate_disabled() {
		$options = array( 'remove_jquery_migrate' => 0 );

		\WP_Mock::userFunction( 'is_admin' )->andReturn( false );

		// Mock WP_Scripts object
		$scripts = new stdClass();
		$scripts->registered = array(
			'jquery' => new stdClass(),
		);
		$scripts->registered['jquery']->deps = array( 'jquery-core', 'jquery-migrate' );

		// Call the function
		remove_jquery_migrate( $scripts );

		// Dependencies should remain unchanged
		$this->assertContains( 'jquery-migrate', $scripts->registered['jquery']->deps );
		$this->assertContains( 'jquery-core', $scripts->registered['jquery']->deps );
	}

	/**
	 * Test remove_jquery_migrate on admin pages (should not run)
	 *
	 * @since 1.5.12
	 */
	public function test_remove_jquery_migrate_admin() {
		$options = array( 'remove_jquery_migrate' => 1 );

		\WP_Mock::userFunction( 'is_admin' )->andReturn( true );

		// Mock WP_Scripts object
		$scripts = new stdClass();
		$scripts->registered = array(
			'jquery' => new stdClass(),
		);
		$scripts->registered['jquery']->deps = array( 'jquery-core', 'jquery-migrate' );

		// Call the function
		remove_jquery_migrate( $scripts );

		// Dependencies should remain unchanged on admin
		$this->assertContains( 'jquery-migrate', $scripts->registered['jquery']->deps );
	}

	/**
	 * Test disable_classic_theme_styles when enabled
	 *
	 * @since 1.5.12
	 */
	public function test_disable_classic_theme_styles_enabled() {
		$options = array( 'disable_classic_theme_styles' => 1 );

		\WP_Mock::userFunction( 'wp_deregister_style' )
			->once()
			->with( 'classic-theme-styles' );

		\WP_Mock::userFunction( 'wp_dequeue_style' )
			->once()
			->with( 'classic-theme-styles' );

		disable_classic_theme_styles();

		$this->assertTrue( true ); // Test passes if no exceptions thrown
	}

	/**
	 * Test disable_classic_theme_styles when disabled
	 *
	 * @since 1.5.12
	 */
	public function test_disable_classic_theme_styles_disabled() {
		$options = array( 'disable_classic_theme_styles' => 0 );

		// Functions should not be called when disabled
		\WP_Mock::userFunction( 'wp_deregister_style' )->never();
		\WP_Mock::userFunction( 'wp_dequeue_style' )->never();

		disable_classic_theme_styles();

		$this->assertTrue( true ); // Test passes if no exceptions thrown
	}

	/**
	 * Test DNS prefetch with empty domains
	 *
	 * @since 1.5.12
	 */
	public function test_add_dns_prefetch_empty_domains() {
		\WP_Mock::userFunction( 'is_admin' )->andReturn( false );
		\WP_Mock::userFunction( 'wp_doing_ajax' )->andReturn( false );

		$options = array(
			'enable_dns_prefetch'  => 1,
			'dns_prefetch_domains' => '',
		);

		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'es_optimizer_options' )
			->andReturn( $options );

		ob_start();
		add_dns_prefetch();
		$output = ob_get_clean();

		$this->assertEmpty( $output );
	}
}

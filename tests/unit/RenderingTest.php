<?php
/**
 * Unit Tests for Simple WP Optimizer Rendering Functions
 *
 * Tests for admin page rendering, form generation, and HTML output functions.
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
 * Test class for rendering functions
 */
class RenderingTest extends TestCase {

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
	 * Test es_optimizer_settings_page with proper permissions
	 *
	 * @since 1.5.12
	 */
	public function test_settings_page_with_permissions() {
		\WP_Mock::userFunction( 'current_user_can' )
			->once()
			->with( 'manage_options' )
			->andReturn( true );

		\WP_Mock::userFunction( 'settings_fields' )
			->once()
			->with( 'es_optimizer_settings' );

		\WP_Mock::userFunction( 'wp_nonce_field' )
			->once()
			->with( 'es_optimizer_settings_action', 'es_optimizer_settings_nonce' );

		// Mock the get_options function
		\WP_Mock::userFunction( 'get_option' )
			->once()
			->andReturn( TestHelper::get_sample_plugin_options() );

		// Mock WordPress translation functions
		\WP_Mock::userFunction( 'esc_html__' )->andReturnUsing( function( $text ) {
			return $text;
		});

		\WP_Mock::userFunction( 'esc_html' )->andReturnUsing( function( $text ) {
			return htmlspecialchars( $text );
		});

		\WP_Mock::userFunction( 'esc_attr' )->andReturnUsing( function( $text ) {
			return htmlspecialchars( $text, ENT_QUOTES );
		});

		\WP_Mock::userFunction( 'esc_textarea' )->andReturnUsing( function( $text ) {
			return htmlspecialchars( $text );
		});

		\WP_Mock::userFunction( 'checked' )->andReturnUsing( function( $checked, $current ) {
			return $checked === $current ? 'checked="checked"' : '';
		});

		ob_start();
		es_optimizer_settings_page();
		$output = ob_get_clean();

		$this->assertStringContains( 'WP Optimizer Settings', $output );
		$this->assertStringContains( '<form method="post"', $output );
		$this->assertStringContains( 'EngineScript', $output );
	}

	/**
	 * Test es_optimizer_settings_page without permissions
	 *
	 * @since 1.5.12
	 */
	public function test_settings_page_without_permissions() {
		\WP_Mock::userFunction( 'current_user_can' )
			->once()
			->with( 'manage_options' )
			->andReturn( false );

		\WP_Mock::userFunction( 'esc_html__' )
			->once()
			->with( 'You do not have sufficient permissions to access this page.', 'simple-wp-optimizer' )
			->andReturn( 'You do not have sufficient permissions to access this page.' );

		\WP_Mock::userFunction( 'wp_die' )
			->once()
			->with( 'You do not have sufficient permissions to access this page.' );

		es_optimizer_settings_page();

		$this->assertTrue( true ); // Test passes if wp_die is called
	}

	/**
	 * Test es_optimizer_render_performance_options
	 *
	 * @since 1.5.12
	 */
	public function test_render_performance_options() {
		$options = TestHelper::get_sample_plugin_options();

		\WP_Mock::userFunction( 'esc_html__' )->andReturnUsing( function( $text ) {
			return $text;
		});

		\WP_Mock::userFunction( 'esc_html' )->andReturnUsing( function( $text ) {
			return htmlspecialchars( $text );
		});

		\WP_Mock::userFunction( 'esc_attr' )->andReturnUsing( function( $text ) {
			return htmlspecialchars( $text, ENT_QUOTES );
		});

		\WP_Mock::userFunction( 'checked' )->andReturnUsing( function( $checked, $current ) {
			return $checked === $current ? 'checked="checked"' : '';
		});

		ob_start();
		es_optimizer_render_performance_options( $options );
		$output = ob_get_clean();

		// Check that performance options are rendered
		$this->assertStringContains( 'Disable WordPress Emojis', $output );
		$this->assertStringContains( 'Remove jQuery Migrate', $output );
		$this->assertStringContains( 'Disable Classic Theme Styles', $output );
		$this->assertStringContains( 'disable_emojis', $output );
		$this->assertStringContains( 'remove_jquery_migrate', $output );
	}

	/**
	 * Test es_optimizer_render_header_options
	 *
	 * @since 1.5.12
	 */
	public function test_render_header_options() {
		$options = TestHelper::get_sample_plugin_options();

		\WP_Mock::userFunction( 'esc_html__' )->andReturnUsing( function( $text ) {
			return $text;
		});

		\WP_Mock::userFunction( 'esc_html' )->andReturnUsing( function( $text ) {
			return htmlspecialchars( $text );
		});

		\WP_Mock::userFunction( 'esc_attr' )->andReturnUsing( function( $text ) {
			return htmlspecialchars( $text, ENT_QUOTES );
		});

		\WP_Mock::userFunction( 'checked' )->andReturnUsing( function( $checked, $current ) {
			return $checked === $current ? 'checked="checked"' : '';
		});

		ob_start();
		es_optimizer_render_header_options( $options );
		$output = ob_get_clean();

		// Check that header cleanup options are rendered
		$this->assertStringContains( 'Remove WordPress Version', $output );
		$this->assertStringContains( 'Remove WLW Manifest', $output );
		$this->assertStringContains( 'Remove Shortlink', $output );
		$this->assertStringContains( 'Remove Recent Comments Style', $output );
	}

	/**
	 * Test es_optimizer_render_additional_options
	 *
	 * @since 1.5.12
	 */
	public function test_render_additional_options() {
		$options = TestHelper::get_sample_plugin_options();

		\WP_Mock::userFunction( 'esc_html__' )->andReturnUsing( function( $text ) {
			return $text;
		});

		\WP_Mock::userFunction( 'esc_html' )->andReturnUsing( function( $text ) {
			return htmlspecialchars( $text );
		});

		\WP_Mock::userFunction( 'esc_attr' )->andReturnUsing( function( $text ) {
			return htmlspecialchars( $text, ENT_QUOTES );
		});

		\WP_Mock::userFunction( 'esc_textarea' )->andReturnUsing( function( $text ) {
			return htmlspecialchars( $text );
		});

		\WP_Mock::userFunction( 'checked' )->andReturnUsing( function( $checked, $current ) {
			return $checked === $current ? 'checked="checked"' : '';
		});

		ob_start();
		es_optimizer_render_additional_options( $options );
		$output = ob_get_clean();

		// Check that additional options are rendered
		$this->assertStringContains( 'Enable DNS Prefetch', $output );
		$this->assertStringContains( 'DNS Prefetch Domains', $output );
		$this->assertStringContains( 'Disable Jetpack Ads', $output );
		$this->assertStringContains( 'Disable Post via Email', $output );
		$this->assertStringContains( '<textarea', $output );
	}

	/**
	 * Test es_optimizer_render_checkbox_option
	 *
	 * @since 1.5.12
	 */
	public function test_render_checkbox_option() {
		$options = array( 'test_option' => 1 );

		\WP_Mock::userFunction( 'esc_html' )->andReturnUsing( function( $text ) {
			return htmlspecialchars( $text );
		});

		\WP_Mock::userFunction( 'esc_attr' )->andReturnUsing( function( $text ) {
			return htmlspecialchars( $text, ENT_QUOTES );
		});

		\WP_Mock::userFunction( 'checked' )->andReturnUsing( function( $checked, $current ) {
			return $checked === $current ? 'checked="checked"' : '';
		});

		ob_start();
		es_optimizer_render_checkbox_option( 
			$options, 
			'test_option', 
			'Test Option Title', 
			'Test option description' 
		);
		$output = ob_get_clean();

		$this->assertStringContains( 'Test Option Title', $output );
		$this->assertStringContains( 'Test option description', $output );
		$this->assertStringContains( 'es_optimizer_options[test_option]', $output );
		$this->assertStringContains( 'type="checkbox"', $output );
		$this->assertStringContains( 'checked="checked"', $output );
	}

	/**
	 * Test es_optimizer_render_checkbox_option unchecked
	 *
	 * @since 1.5.12
	 */
	public function test_render_checkbox_option_unchecked() {
		$options = array( 'test_option' => 0 );

		\WP_Mock::userFunction( 'esc_html' )->andReturnUsing( function( $text ) {
			return htmlspecialchars( $text );
		});

		\WP_Mock::userFunction( 'esc_attr' )->andReturnUsing( function( $text ) {
			return htmlspecialchars( $text, ENT_QUOTES );
		});

		\WP_Mock::userFunction( 'checked' )->andReturnUsing( function( $checked, $current ) {
			return $checked === $current ? 'checked="checked"' : '';
		});

		ob_start();
		es_optimizer_render_checkbox_option( 
			$options, 
			'test_option', 
			'Test Option Title', 
			'Test option description' 
		);
		$output = ob_get_clean();

		$this->assertStringContains( 'Test Option Title', $output );
		$this->assertStringNotContains( 'checked="checked"', $output );
	}

	/**
	 * Test es_optimizer_render_textarea_option
	 *
	 * @since 1.5.12
	 */
	public function test_render_textarea_option() {
		$options = array( 'test_textarea' => 'Test content' );

		\WP_Mock::userFunction( 'esc_html' )->andReturnUsing( function( $text ) {
			return htmlspecialchars( $text );
		});

		\WP_Mock::userFunction( 'esc_attr' )->andReturnUsing( function( $text ) {
			return htmlspecialchars( $text, ENT_QUOTES );
		});

		\WP_Mock::userFunction( 'esc_textarea' )->andReturnUsing( function( $text ) {
			return htmlspecialchars( $text );
		});

		ob_start();
		es_optimizer_render_textarea_option( 
			$options, 
			'test_textarea', 
			'Test Textarea Title', 
			'Test textarea description' 
		);
		$output = ob_get_clean();

		$this->assertStringContains( 'Test Textarea Title', $output );
		$this->assertStringContains( 'Test textarea description', $output );
		$this->assertStringContains( 'es_optimizer_options[test_textarea]', $output );
		$this->assertStringContains( '<textarea', $output );
		$this->assertStringContains( 'Test content', $output );
	}

	/**
	 * Test es_optimizer_render_textarea_option with empty content
	 *
	 * @since 1.5.12
	 */
	public function test_render_textarea_option_empty() {
		$options = array();

		\WP_Mock::userFunction( 'esc_html' )->andReturnUsing( function( $text ) {
			return htmlspecialchars( $text );
		});

		\WP_Mock::userFunction( 'esc_attr' )->andReturnUsing( function( $text ) {
			return htmlspecialchars( $text, ENT_QUOTES );
		});

		ob_start();
		es_optimizer_render_textarea_option( 
			$options, 
			'test_textarea', 
			'Test Textarea Title', 
			'Test textarea description' 
		);
		$output = ob_get_clean();

		$this->assertStringContains( 'Test Textarea Title', $output );
		$this->assertStringContains( '<textarea', $output );
		$this->assertStringNotContains( 'Test content', $output );
	}

	/**
	 * Test checkbox option security - proper escaping
	 *
	 * @since 1.5.12
	 */
	public function test_checkbox_option_security() {
		$options = array( 'malicious_option' => 1 );

		\WP_Mock::userFunction( 'esc_html' )->andReturnUsing( function( $text ) {
			return htmlspecialchars( $text );
		});

		\WP_Mock::userFunction( 'esc_attr' )->andReturnUsing( function( $text ) {
			return htmlspecialchars( $text, ENT_QUOTES );
		});

		\WP_Mock::userFunction( 'checked' )->andReturnUsing( function( $checked, $current ) {
			return $checked === $current ? 'checked="checked"' : '';
		});

		$malicious_title = '<script>alert("xss")</script>Test';
		$malicious_description = '<img src=x onerror=alert("xss")>Description';

		ob_start();
		es_optimizer_render_checkbox_option( 
			$options, 
			'malicious_option', 
			$malicious_title, 
			$malicious_description 
		);
		$output = ob_get_clean();

		// Check that script tags are escaped
		$this->assertStringNotContains( '<script>', $output );
		$this->assertStringNotContains( 'onerror=', $output );
		$this->assertStringContains( '&lt;script&gt;', $output );
		$this->assertStringContains( '&lt;img', $output );
	}

	/**
	 * Test textarea option security - proper escaping
	 *
	 * @since 1.5.12
	 */
	public function test_textarea_option_security() {
		$malicious_content = '<script>alert("xss")</script>';
		$options = array( 'test_textarea' => $malicious_content );

		\WP_Mock::userFunction( 'esc_html' )->andReturnUsing( function( $text ) {
			return htmlspecialchars( $text );
		});

		\WP_Mock::userFunction( 'esc_attr' )->andReturnUsing( function( $text ) {
			return htmlspecialchars( $text, ENT_QUOTES );
		});

		\WP_Mock::userFunction( 'esc_textarea' )->andReturnUsing( function( $text ) {
			return htmlspecialchars( $text );
		});

		ob_start();
		es_optimizer_render_textarea_option( 
			$options, 
			'test_textarea', 
			'Test Title', 
			'Test description' 
		);
		$output = ob_get_clean();

		// Check that script content is properly escaped
		$this->assertStringNotContains( '<script>', $output );
		$this->assertStringContains( '&lt;script&gt;', $output );
	}
}

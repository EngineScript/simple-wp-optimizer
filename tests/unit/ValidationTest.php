<?php
/**
 * Unit Tests for Simple WP Optimizer Validation Functions
 *
 * Tests for input validation, option validation, and security features.
 *
 * @package Simple_WP_Optimizer
 * @subpackage Tests
 * @since 1.5.12
 */

use WP_Mock\Tools\TestCase;

/**
 * Test class for validation functions
 */
class ValidationTest extends TestCase {

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
	 * Test es_optimizer_validate_single_domain with valid HTTPS domains
	 *
	 * @since 1.5.12
	 */
	public function test_validate_single_domain_valid_https() {
		\WP_Mock::userFunction( 'wp_parse_url' )->andReturnUsing( function( $url ) {
			return parse_url( $url );
		});

		\WP_Mock::userFunction( 'esc_url_raw' )->andReturnUsing( function( $url ) {
			return $url;
		});

		$valid_domains = array(
			'https://fonts.googleapis.com',
			'https://ajax.googleapis.com',
			'https://cdnjs.cloudflare.com',
			'https://www.google-analytics.com',
		);

		foreach ( $valid_domains as $domain ) {
			$result = es_optimizer_validate_single_domain( $domain );
			
			$this->assertTrue( $result['valid'], "Domain {$domain} should be valid" );
			$this->assertEquals( $domain, $result['domain'] );
		}
	}

	/**
	 * Test es_optimizer_validate_single_domain with invalid domains
	 *
	 * @since 1.5.12
	 */
	public function test_validate_single_domain_invalid_domains() {
		\WP_Mock::userFunction( 'wp_parse_url' )->andReturnUsing( function( $url ) {
			return parse_url( $url );
		});

		$invalid_domains = array(
			'http://example.com',              // HTTP not HTTPS
			'not-a-url',                       // Invalid format
			'//example.com',                   // No scheme
			'https://example.com/path',        // Has path
			'https://example.com?query=1',     // Has query
			'https://example.com#fragment',    // Has fragment
			'https://localhost',               // Localhost
			'https://127.0.0.1',              // Local IP
		);

		foreach ( $invalid_domains as $domain ) {
			$result = es_optimizer_validate_single_domain( $domain );
			
			$this->assertFalse( $result['valid'], "Domain {$domain} should be invalid" );
			$this->assertArrayHasKey( 'error', $result );
		}
	}

	/**
	 * Test es_optimizer_validate_dns_domains with mixed valid/invalid domains
	 *
	 * @since 1.5.12
	 */
	public function test_validate_dns_domains_mixed() {
		\WP_Mock::userFunction( 'wp_parse_url' )->andReturnUsing( function( $url ) {
			return parse_url( $url );
		});

		\WP_Mock::userFunction( 'esc_url_raw' )->andReturnUsing( function( $url ) {
			return $url;
		});

		\WP_Mock::userFunction( 'add_settings_error' )->times( 1 );
		\WP_Mock::userFunction( 'esc_html' )->andReturnUsing( function( $text ) {
			return htmlspecialchars( $text );
		});
		\WP_Mock::userFunction( 'esc_html__' )->andReturnUsing( function( $text ) {
			return $text;
		});

		$input = "https://fonts.googleapis.com\nhttp://invalid.com\nhttps://valid.example.com\ninvalid-url";
		
		$result = es_optimizer_validate_dns_domains( $input );
		
		$expected_domains = array(
			'https://fonts.googleapis.com',
			'https://valid.example.com',
		);
		
		$this->assertEquals( implode( "\n", $expected_domains ), $result );
	}

	/**
	 * Test es_optimizer_validate_options with valid input
	 *
	 * @since 1.5.12
	 */
	public function test_validate_options_valid_input() {
		// Mock WordPress functions
		\WP_Mock::userFunction( 'sanitize_text_field' )->andReturnUsing( function( $text ) {
			return trim( strip_tags( $text ) );
		});
		\WP_Mock::userFunction( 'wp_unslash' )->andReturnUsing( function( $text ) {
			return $text;
		});
		\WP_Mock::userFunction( 'wp_verify_nonce' )->andReturn( true );
		\WP_Mock::userFunction( 'get_option' )->andReturn( TestHelper::get_sample_plugin_options() );
		\WP_Mock::userFunction( 'wp_parse_url' )->andReturnUsing( function( $url ) {
			return parse_url( $url );
		});
		\WP_Mock::userFunction( 'esc_url_raw' )->andReturnUsing( function( $url ) {
			return $url;
		});

		// Set up $_POST data
		$_POST['es_optimizer_settings_nonce'] = 'valid_nonce';

		$input = array(
			'disable_emojis'        => '1',
			'remove_jquery_migrate' => '1',
			'enable_dns_prefetch'   => '1',
			'dns_prefetch_domains'  => 'https://fonts.googleapis.com',
		);

		$result = es_optimizer_validate_options( $input );

		$this->assertEquals( 1, $result['disable_emojis'] );
		$this->assertEquals( 1, $result['remove_jquery_migrate'] );
		$this->assertEquals( 1, $result['enable_dns_prefetch'] );
		$this->assertEquals( 'https://fonts.googleapis.com', $result['dns_prefetch_domains'] );

		// Clean up
		unset( $_POST['es_optimizer_settings_nonce'] );
	}

	/**
	 * Test es_optimizer_validate_options with failed nonce
	 *
	 * @since 1.5.12
	 */
	public function test_validate_options_failed_nonce() {
		\WP_Mock::userFunction( 'sanitize_text_field' )->andReturnUsing( function( $text ) {
			return trim( strip_tags( $text ) );
		});
		\WP_Mock::userFunction( 'wp_unslash' )->andReturnUsing( function( $text ) {
			return $text;
		});
		\WP_Mock::userFunction( 'wp_verify_nonce' )->andReturn( false );
		\WP_Mock::userFunction( 'add_settings_error' )->times( 1 );
		\WP_Mock::userFunction( 'esc_html__' )->andReturnUsing( function( $text ) {
			return $text;
		});
		\WP_Mock::userFunction( 'get_option' )->andReturn( TestHelper::get_sample_plugin_options() );

		// Set up $_POST data
		$_POST['es_optimizer_settings_nonce'] = 'invalid_nonce';

		$input = array(
			'disable_emojis' => '1',
		);

		$result = es_optimizer_validate_options( $input );

		// Should return current options unchanged
		$this->assertEquals( TestHelper::get_sample_plugin_options(), $result );

		// Clean up
		unset( $_POST['es_optimizer_settings_nonce'] );
	}

	/**
	 * Test es_optimizer_show_domain_rejection_notice
	 *
	 * @since 1.5.12
	 */
	public function test_show_domain_rejection_notice() {
		\WP_Mock::userFunction( 'esc_html' )->andReturnUsing( function( $text ) {
			return htmlspecialchars( $text );
		});
		\WP_Mock::userFunction( 'esc_html__' )->andReturnUsing( function( $text ) {
			return $text;
		});
		\WP_Mock::userFunction( 'add_settings_error' )->times( 1 )->with(
			'es_optimizer_options',
			'dns_prefetch_security',
			\Mockery::type( 'string' ),
			'warning'
		);

		$rejected_domains = array(
			'http://invalid.com (HTTPS required)',
			'invalid-url (invalid format)',
		);

		es_optimizer_show_domain_rejection_notice( $rejected_domains );

		$this->assertTrue( true ); // Test passes if no exceptions thrown
	}

	/**
	 * Test es_optimizer_get_default_options structure
	 *
	 * @since 1.5.12
	 */
	public function test_get_default_options_structure() {
		$defaults = es_optimizer_get_default_options();

		$this->assertIsArray( $defaults );
		
		// Check required options exist
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
			$this->assertArrayHasKey( $option, $defaults, "Default options should include {$option}" );
		}

		// Check boolean options are integers
		$boolean_options = array(
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

		foreach ( $boolean_options as $option ) {
			$this->assertContains( $defaults[ $option ], array( 0, 1 ), "Option {$option} should be 0 or 1" );
		}

		// Check DNS prefetch domains is a string
		$this->assertIsString( $defaults['dns_prefetch_domains'] );
	}

	/**
	 * Test domain validation edge cases
	 *
	 * @since 1.5.12
	 */
	public function test_validate_single_domain_edge_cases() {
		\WP_Mock::userFunction( 'wp_parse_url' )->andReturnUsing( function( $url ) {
			return parse_url( $url );
		});

		\WP_Mock::userFunction( 'esc_url_raw' )->andReturnUsing( function( $url ) {
			return $url;
		});

		// Test domain with custom port
		$domain_with_port = 'https://example.com:8080';
		$result = es_optimizer_validate_single_domain( $domain_with_port );
		$this->assertTrue( $result['valid'] );
		$this->assertEquals( $domain_with_port, $result['domain'] );

		// Test domain with default HTTPS port (should be cleaned)
		$domain_default_port = 'https://example.com:443';
		$result = es_optimizer_validate_single_domain( $domain_default_port );
		$this->assertTrue( $result['valid'] );
		$this->assertEquals( 'https://example.com', $result['domain'] );

		// Test empty domain
		$result = es_optimizer_validate_single_domain( '' );
		$this->assertFalse( $result['valid'] );

		// Test whitespace only
		$result = es_optimizer_validate_single_domain( '   ' );
		$this->assertFalse( $result['valid'] );
	}
}

<?php
/**
 * Test Helper Class
 *
 * Provides utility methods for testing Simple WP Optimizer plugin.
 *
 * @package Simple_WP_Optimizer
 * @subpackage Tests
 * @since 1.5.12
 */

/**
 * Test Helper Class
 *
 * Contains utility methods and common test fixtures.
 */
class TestHelper {

	/**
	 * Get sample DNS prefetch domains for testing
	 *
	 * @since 1.5.12
	 * @return array Array of sample domains
	 */
	public static function get_sample_dns_prefetch_domains() {
		return array(
			'//fonts.googleapis.com',
			'//ajax.googleapis.com',
			'//cdnjs.cloudflare.com',
			'//code.jquery.com',
			'//www.google-analytics.com',
		);
	}

	/**
	 * Get invalid DNS prefetch domains for testing
	 *
	 * @since 1.5.12
	 * @return array Array of invalid domains
	 */
	public static function get_invalid_dns_prefetch_domains() {
		return array(
			'/path/to/file.js',           // File path
			'//example.com/script.js',    // URL with path
			'http://example.com/style.css', // Full URL with path
			'//example.com?query=1',      // URL with query
			'javascript:alert(1)',        // XSS attempt
			'<script>alert(1)</script>',  // Script tag
			'',                           // Empty string
			'   ',                        // Whitespace only
		);
	}

	/**
	 * Get sample plugin options for testing
	 *
	 * @since 1.5.12
	 * @return array Array of plugin options
	 */
	public static function get_sample_plugin_options() {
		return array(
			'dns_prefetch_enable'     => '1',
			'dns_prefetch_domains'    => implode( "\n", self::get_sample_dns_prefetch_domains() ),
			'remove_query_strings'    => '1',
			'disable_emojis'          => '1',
			'remove_shortlink'        => '1',
			'disable_xmlrpc'          => '1',
			'remove_wlwmanifest'      => '1',
			'remove_rsd_link'         => '1',
			'remove_feed_links'       => '1',
			'remove_version'          => '1',
			'disable_dashicons'       => '1',
			'compress_html'           => '1',
			'remove_comments'         => '1',
			'lazy_load_images'        => '1',
			'minify_css'              => '1',
			'minify_js'               => '1',
			'defer_js'                => '1',
			'critical_css'            => 'body { margin: 0; }',
		);
	}

	/**
	 * Get sample HTML content for testing
	 *
	 * @since 1.5.12
	 * @return string Sample HTML content
	 */
	public static function get_sample_html() {
		return '<!DOCTYPE html>
<html>
<head>
	<title>Test Page</title>
	<link rel="stylesheet" href="/wp-content/themes/theme/style.css?ver=1.0">
	<script src="/wp-content/plugins/plugin/script.js?ver=2.0"></script>
</head>
<body>
	<h1>Test Page</h1>
	<p>This is a test page with some content.</p>
	<img src="image.jpg" alt="Test Image" />
	<!-- This is a comment -->
</body>
</html>';
	}

	/**
	 * Get minified HTML for comparison
	 *
	 * @since 1.5.12
	 * @return string Minified HTML
	 */
	public static function get_minified_html() {
		return '<!DOCTYPE html><html><head><title>Test Page</title><link rel="stylesheet" href="/wp-content/themes/theme/style.css?ver=1.0"><script src="/wp-content/plugins/plugin/script.js?ver=2.0"></script></head><body><h1>Test Page</h1><p>This is a test page with some content.</p><img src="image.jpg" alt="Test Image" /></body></html>';
	}

	/**
	 * Get sample CSS for testing
	 *
	 * @since 1.5.12
	 * @return string Sample CSS content
	 */
	public static function get_sample_css() {
		return 'body {
	margin: 0;
	padding: 0;
	font-family: Arial, sans-serif;
}

.container {
	max-width: 1200px;
	margin: 0 auto;
	padding: 20px;
}

/* Comment */
.header {
	background-color: #f0f0f0;
	border-bottom: 1px solid #ccc;
}';
	}

	/**
	 * Get minified CSS for comparison
	 *
	 * @since 1.5.12
	 * @return string Minified CSS
	 */
	public static function get_minified_css() {
		return 'body{margin:0;padding:0;font-family:Arial,sans-serif}.container{max-width:1200px;margin:0 auto;padding:20px}.header{background-color:#f0f0f0;border-bottom:1px solid #ccc}';
	}

	/**
	 * Get sample JavaScript for testing
	 *
	 * @since 1.5.12
	 * @return string Sample JavaScript content
	 */
	public static function get_sample_js() {
		return 'function testFunction() {
	var message = "Hello World";
	console.log(message);
	
	// Comment
	if (true) {
		return message;
	}
}

// Another comment
testFunction();';
	}

	/**
	 * Get minified JavaScript for comparison
	 *
	 * @since 1.5.12
	 * @return string Minified JavaScript
	 */
	public static function get_minified_js() {
		return 'function testFunction(){var message="Hello World";console.log(message);if(true){return message}}testFunction();';
	}

	/**
	 * Mock WordPress get_option function
	 *
	 * @since 1.5.12
	 * @param string $option  Option name.
	 * @param mixed  $default Default value.
	 * @return mixed Option value or default
	 */
	public static function mock_get_option( $option, $default = false ) {
		$options = self::get_sample_plugin_options();
		return isset( $options[ $option ] ) ? $options[ $option ] : $default;
	}

	/**
	 * Create a mock admin user for testing
	 *
	 * @since 1.5.12
	 * @return object Mock user object
	 */
	public static function create_mock_admin_user() {
		return (object) array(
			'ID'         => 1,
			'user_login' => 'admin',
			'user_email' => 'admin@example.com',
			'user_role'  => 'administrator',
		);
	}

	/**
	 * Reset WP_Mock between tests
	 *
	 * @since 1.5.12
	 */
	public static function reset_wp_mock() {
		WP_Mock::tearDown();
		WP_Mock::setUp();
	}

	/**
	 * Assert that a string contains valid HTML
	 *
	 * @since 1.5.12
	 * @param string $html HTML string to validate.
	 * @return bool True if valid HTML
	 */
	public static function is_valid_html( $html ) {
		$dom = new DOMDocument();
		libxml_use_internal_errors( true );
		$result = $dom->loadHTML( $html );
		libxml_clear_errors();
		return $result;
	}

	/**
	 * Assert that a string is valid CSS
	 *
	 * @since 1.5.12
	 * @param string $css CSS string to validate.
	 * @return bool True if valid CSS
	 */
	public static function is_valid_css( $css ) {
		// Basic CSS validation - check for balanced braces
		$open_braces  = substr_count( $css, '{' );
		$close_braces = substr_count( $css, '}' );
		return $open_braces === $close_braces;
	}

	/**
	 * Assert that a string is valid JavaScript
	 *
	 * @since 1.5.12
	 * @param string $js JavaScript string to validate.
	 * @return bool True if valid JavaScript
	 */
	public static function is_valid_js( $js ) {
		// Basic JS validation - check for balanced braces and parentheses
		$open_braces  = substr_count( $js, '{' );
		$close_braces = substr_count( $js, '}' );
		$open_parens  = substr_count( $js, '(' );
		$close_parens = substr_count( $js, ')' );
		
		return $open_braces === $close_braces && $open_parens === $close_parens;
	}
}

<?php
/**
 * Test Fixtures for Simple WP Optimizer
 *
 * Contains sample data and configuration for testing.
 *
 * @package Simple_WP_Optimizer
 * @subpackage Tests
 * @since 1.5.12
 */

/**
 * Sample options data for testing
 */
return array(
	'valid_options' => array(
		'disable_emojis'               => 1,
		'remove_jquery_migrate'        => 1,
		'disable_classic_theme_styles' => 1,
		'remove_wp_version'            => 1,
		'remove_wlw_manifest'          => 1,
		'remove_shortlink'             => 1,
		'remove_recent_comments_style' => 1,
		'enable_dns_prefetch'          => 1,
		'dns_prefetch_domains'         => "https://fonts.googleapis.com\nhttps://ajax.googleapis.com\nhttps://cdnjs.cloudflare.com",
		'disable_jetpack_ads'          => 1,
		'disable_post_via_email'       => 1,
	),

	'minimal_options' => array(
		'disable_emojis'      => 0,
		'enable_dns_prefetch' => 1,
		'dns_prefetch_domains' => 'https://fonts.googleapis.com',
	),

	'security_focused_options' => array(
		'remove_wp_version'      => 1,
		'disable_post_via_email' => 1,
		'disable_jetpack_ads'    => 1,
	),

	'performance_focused_options' => array(
		'disable_emojis'               => 1,
		'remove_jquery_migrate'        => 1,
		'disable_classic_theme_styles' => 1,
		'enable_dns_prefetch'          => 1,
		'dns_prefetch_domains'         => "https://fonts.googleapis.com\nhttps://fonts.gstatic.com",
	),

	'invalid_options' => array(
		'disable_emojis'       => 'invalid_value',
		'dns_prefetch_domains' => "http://insecure.com\njavascript:alert(1)\nhttps://valid.com",
	),

	'empty_options' => array(),

	'dns_domains' => array(
		'valid' => array(
			'https://fonts.googleapis.com',
			'https://fonts.gstatic.com',
			'https://ajax.googleapis.com',
			'https://apis.google.com',
			'https://www.google-analytics.com',
			'https://cdnjs.cloudflare.com',
			'https://code.jquery.com',
			'https://stackpath.bootstrapcdn.com',
		),

		'invalid' => array(
			'http://insecure.com',                    // HTTP not HTTPS
			'//protocol-relative.com',               // No protocol
			'not-a-url',                             // Invalid format
			'https://example.com/path/file.js',      // Has path
			'https://example.com?query=value',       // Has query
			'https://example.com#fragment',          // Has fragment
			'javascript:alert("xss")',               // XSS attempt
			'<script>alert("xss")</script>',         // Script injection
			'https://localhost',                     // Localhost
			'https://127.0.0.1',                    // Local IP
			'https://192.168.1.1',                  // Private IP
			'',                                      // Empty string
			'   ',                                   // Whitespace only
		),

		'mixed' => array(
			'https://fonts.googleapis.com',          // Valid
			'http://insecure.com',                   // Invalid - HTTP
			'https://ajax.googleapis.com',           // Valid
			'not-a-url',                             // Invalid - format
			'https://cdnjs.cloudflare.com',         // Valid
			'javascript:alert(1)',                   // Invalid - XSS
		),
	),

	'html_samples' => array(
		'basic' => '<!DOCTYPE html><html><head><title>Test</title></head><body><h1>Test Page</h1><p>Content</p></body></html>',
		
		'with_comments' => '<!DOCTYPE html>
<html>
<head>
	<!-- This is a comment -->
	<title>Test Page</title>
	<link rel="stylesheet" href="style.css">
</head>
<body>
	<h1>Test Page</h1>
	<!-- Another comment -->
	<p>This is test content.</p>
</body>
</html>',

		'with_scripts' => '<!DOCTYPE html>
<html>
<head>
	<title>Test Page</title>
	<script src="script.js?ver=1.0"></script>
</head>
<body>
	<h1>Test Page</h1>
	<script>
		console.log("Hello World");
	</script>
</body>
</html>',

		'with_styles' => '<!DOCTYPE html>
<html>
<head>
	<title>Test Page</title>
	<link rel="stylesheet" href="style.css?ver=1.0">
	<style>
		body { margin: 0; padding: 0; }
		.container { max-width: 1200px; }
	</style>
</head>
<body>
	<div class="container">
		<h1>Test Page</h1>
	</div>
</body>
</html>',
	),

	'css_samples' => array(
		'basic' => 'body { margin: 0; padding: 0; }',
		
		'with_comments' => '/* Reset styles */
body {
	margin: 0;
	padding: 0;
	font-family: Arial, sans-serif;
}

/* Container styles */
.container {
	max-width: 1200px;
	margin: 0 auto;
	padding: 20px;
}',

		'minified' => 'body{margin:0;padding:0;font-family:Arial,sans-serif}.container{max-width:1200px;margin:0 auto;padding:20px}',
	),

	'javascript_samples' => array(
		'basic' => 'console.log("Hello World");',
		
		'with_comments' => '// Initialize application
function init() {
	var message = "Hello World";
	console.log(message);
	
	/* Multi-line comment
	   with more details */
	return message;
}

// Call initialization
init();',

		'minified' => 'function init(){var message="Hello World";console.log(message);return message}init();',
	),

	'wordpress_hooks' => array(
		'actions' => array(
			'admin_init',
			'admin_menu',
			'init',
			'wp_head',
			'wp_default_scripts',
			'wp_enqueue_scripts',
		),

		'filters' => array(
			'tiny_mce_plugins',
			'wp_resource_hints',
			'show_recent_comments_widget_style',
			'jetpack_just_in_time_msgs',
			'jetpack_show_promotions',
			'jetpack_blaze_enabled',
			'enable_post_by_email_configuration',
		),

		'removed_actions' => array(
			'wp_head' => array(
				'print_emoji_detection_script',
				'wp_generator',
				'wlwmanifest_link',
				'wp_shortlink_wp_head',
			),
			'wp_print_styles' => array(
				'print_emoji_styles',
			),
			'admin_print_scripts' => array(
				'print_emoji_detection_script',
			),
			'admin_print_styles' => array(
				'print_emoji_styles',
			),
		),

		'removed_filters' => array(
			'the_content_feed' => array(
				'wp_staticize_emoji',
			),
			'comment_text_rss' => array(
				'wp_staticize_emoji',
			),
			'wp_mail' => array(
				'wp_staticize_emoji_for_email',
			),
		),
	),

	'security_test_data' => array(
		'xss_attempts' => array(
			'<script>alert("xss")</script>',
			'<img src=x onerror=alert("xss")>',
			'javascript:alert("xss")',
			'<svg onload=alert("xss")>',
			'"><script>alert("xss")</script>',
		),

		'malicious_domains' => array(
			'javascript:alert(1)',
			'data:text/html,<script>alert(1)</script>',
			'https://evil.com/steal-data.js',
			'https://localhost/../../etc/passwd',
		),

		'safe_content' => array(
			'Normal text content',
			'https://fonts.googleapis.com',
			'Valid domain name',
			'12345',
		),
	),
);

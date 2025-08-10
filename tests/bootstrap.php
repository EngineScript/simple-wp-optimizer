<?php
/**
 * Test bootstrap file for Simple WP Optimizer
 *
 * This file sets up the WordPress testing environment and initializes WP_Mock.
 *
 * @package Simple_WP_Optimizer
 * @subpackage Tests
 * @since 1.5.12
 */

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
// phpcs:disable WordPress.WP.AlternativeFunctions.parse_url_parse_url
// phpcs:disable WordPress.WP.AlternativeFunctions.strip_tags_strip_tags

// Define test constants
if ( ! defined( 'SIMPLE_WP_OPTIMIZER_TESTING' ) ) {
	define( 'SIMPLE_WP_OPTIMIZER_TESTING', true );
}

// Define WordPress constants for testing
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/tmp/wordpress/' );
}

if ( ! defined( 'WP_CONTENT_DIR' ) ) {
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
}

if ( ! defined( 'WP_CONTENT_URL' ) ) {
	define( 'WP_CONTENT_URL', 'http://example.org/wp-content' );
}

if ( ! defined( 'WP_PLUGIN_DIR' ) ) {
	define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
}

if ( ! defined( 'WP_PLUGIN_URL' ) ) {
	define( 'WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins' );
}

if ( ! defined( 'WPINC' ) ) {
	define( 'WPINC', 'wp-includes' );
}

// Plugin specific constants
if ( ! defined( 'SIMPLE_WP_OPTIMIZER_VERSION' ) ) {
	define( 'SIMPLE_WP_OPTIMIZER_VERSION', '1.5.12' );
}

if ( ! defined( 'SIMPLE_WP_OPTIMIZER_PLUGIN_URL' ) ) {
	define( 'SIMPLE_WP_OPTIMIZER_PLUGIN_URL', WP_PLUGIN_URL . '/simple-wp-optimizer/' );
}

if ( ! defined( 'SIMPLE_WP_OPTIMIZER_PLUGIN_DIR' ) ) {
	define( 'SIMPLE_WP_OPTIMIZER_PLUGIN_DIR', WP_PLUGIN_DIR . '/simple-wp-optimizer/' );
}

// Set up autoloader
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Initialize WP_Mock
WP_Mock::setUsePatchwork( true );
WP_Mock::bootstrap();

// Load helper functions
require_once __DIR__ . '/helpers/TestHelper.php';

// Mock WordPress functions that are commonly used
WP_Mock::userFunction( 'wp_nonce_field', array(
	'return' => '<input type="hidden" id="_wpnonce" name="_wpnonce" value="12345" /><input type="hidden" name="_wp_http_referer" value="/wp-admin/" />',
) );

WP_Mock::userFunction( 'wp_verify_nonce', array(
	'return' => true,
) );

WP_Mock::userFunction( 'current_user_can', array(
	'return' => true,
) );

WP_Mock::userFunction( 'sanitize_text_field', array(
	'return' => function( $str ) {
		return trim( strip_tags( $str ) );
	},
) );

WP_Mock::userFunction( 'esc_html', array(
	'return' => function( $text ) {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	},
) );

WP_Mock::userFunction( 'esc_attr', array(
	'return' => function( $text ) {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	},
) );

WP_Mock::userFunction( 'esc_url', array(
	'return' => function( $url ) {
		return filter_var( $url, FILTER_SANITIZE_URL );
	},
) );

WP_Mock::userFunction( 'wp_kses_post', array(
	'return' => function( $data ) {
		return strip_tags( $data, '<p><br><strong><em><a><ul><ol><li><h1><h2><h3><h4><h5><h6>' );
	},
) );

WP_Mock::userFunction( '__', array(
	'return' => function( $text, $domain = 'default' ) {
		return $text;
	},
) );

WP_Mock::userFunction( '_e', array(
	'return' => function( $text, $domain = 'default' ) {
		echo $text;
	},
) );

WP_Mock::userFunction( 'plugin_dir_path', array(
	'return' => function( $file ) {
		return SIMPLE_WP_OPTIMIZER_PLUGIN_DIR;
	},
) );

WP_Mock::userFunction( 'plugin_dir_url', array(
	'return' => function( $file ) {
		return SIMPLE_WP_OPTIMIZER_PLUGIN_URL;
	},
) );

// Mock common WordPress constants
if ( ! defined( 'MINUTE_IN_SECONDS' ) ) {
	define( 'MINUTE_IN_SECONDS', 60 );
}

if ( ! defined( 'HOUR_IN_SECONDS' ) ) {
	define( 'HOUR_IN_SECONDS', 3600 );
}

if ( ! defined( 'DAY_IN_SECONDS' ) ) {
	define( 'DAY_IN_SECONDS', 86400 );
}

if ( ! defined( 'WEEK_IN_SECONDS' ) ) {
	define( 'WEEK_IN_SECONDS', 604800 );
}

if ( ! defined( 'MONTH_IN_SECONDS' ) ) {
	define( 'MONTH_IN_SECONDS', 2592000 );
}

if ( ! defined( 'YEAR_IN_SECONDS' ) ) {
	define( 'YEAR_IN_SECONDS', 31536000 );
}

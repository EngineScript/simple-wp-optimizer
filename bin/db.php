<?php
/**
 * Fallback MySQL database driver for WordPress tests
 * 
 * This is a simplified version of the wp-mysqli driver, used as a fallback
 * when the download from the original source fails.
 * 
 * @package Simple_WP_Optimizer
 */

if ( ! defined( 'WP_USE_EXT_MYSQL' ) ) {
	define( 'WP_USE_EXT_MYSQL', false );
}

// Ensure mysqli extension is available
if ( ! function_exists( 'mysqli_connect' ) ) {
	trigger_error( 'This PHP installation does not have the mysqli extension enabled. Please enable it or contact your hosting provider.', E_USER_ERROR );
}

// Force using mysqli
if ( defined( 'WP_USE_EXT_MYSQL' ) && WP_USE_EXT_MYSQL ) {
	$GLOBALS['wpdb'] = new wpdb( DB_USER, DB_PASSWORD, DB_NAME, DB_HOST );
} else {
	// Nothing to do - WordPress 3.9+ uses mysqli by default
}

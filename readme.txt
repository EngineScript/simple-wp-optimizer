=== EngineScript: Simple WP Optimization ===
Contributors: enginescript
Tags: optimization, performance, cleanup
Requires at least: 6.0
Tested up to: 6.8
Stable tag: 1.5.7
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Optimizes WordPress by removing unnecessary features and scripts to improve performance.

== Description ==

Simple WP Optimizer removes unnecessary WordPress features and scripts to optimize your site's performance. 
It helps reduce page load times and improves overall site speed by disabling unused functionality.

Key features:
* Disable XML-RPC
* Disable JSON REST API for non-logged users
* Remove jQuery Migrate
* Remove unnecessary header meta
* Disable auto-embeds
* Disable emoji support
* Remove Gutenberg CSS

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/simple-wp-optimizer` directory.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the Settings page to configure the optimization options.

== Frequently Asked Questions ==

= Will this plugin break my site? =

The optimizations are carefully selected to be safe for most sites. You can enable/disable specific optimizations as needed.

= Do I need technical knowledge to use this plugin? =

No, the plugin has a simple interface where you can toggle features on and off.

== Changelog ==

= 1.5.7 =
* **SECURITY ENHANCEMENT**: Added WordPress nonce protection for CSRF security in all form submissions
* **SECURITY ENHANCEMENT**: Enhanced DNS prefetch security with HTTPS-only domain enforcement
* **SECURITY ENHANCEMENT**: Added private IP and localhost blocking for DNS prefetch to prevent SSRF attacks
* **SECURITY ENHANCEMENT**: Implemented comprehensive domain validation with multi-layer security checks
* Added user-friendly error messages for rejected domains with proper HTML escaping
* Added security event notifications for administrators when domains are rejected for security reasons
* Strengthened form security with proper wp_nonce_field() and wp_verify_nonce() implementation
* Improved DNS prefetch domain validation with enhanced URL parsing and filtering
* Enhanced error handling with proper WordPress admin notices and comprehensive escaping
* Updated help text to clearly indicate HTTPS requirement for DNS prefetch domains
* Fixed potential XSS vulnerability in error message display through proper HTML escaping
* Improved domain validation to prevent bypass of security checks and information disclosure
* Better user experience with informative, security-focused error messages
* Comprehensive input validation preventing malicious domain submissions and attacks

= 1.5.6 =
* Enhanced GitHub Actions workflows for comprehensive plugin testing and security analysis
* Added PHPStan WordPress static analysis with proper WordPress stubs configuration
* Integrated WordPress Vulnerability Scanner and comprehensive security scanning
* Fixed PHPStan static analysis errors: register_setting(), remove_action(), wp_print_link_tag() replacement, disable_emojis_tinymce() logic
* Enhanced security implementation with comprehensive OWASP-compliant documentation
* Added detailed security implementation notes following WordPress best practices
* Improved input validation with strict type checking for all user inputs
* Enhanced output escaping with context-appropriate WordPress functions (esc_html, esc_attr, esc_url, esc_textarea)
* Added proper capability checks for all admin functions using current_user_can('manage_options')
* Implemented secure coding patterns and multi-layer domain validation for DNS prefetch
* Added comprehensive code documentation with security explanations and best practices
* Fixed potential security vulnerabilities with proper WordPress coding standards
* Improved code structure and organization for better maintainability
* Enhanced development workflow with reliable testing and WordPress stubs support

= 1.5.5 =
* Enhanced code quality with comprehensive static analysis fixes
* Fixed register_setting function to use proper array parameters instead of string callback
* Corrected remove_action function calls to use proper parameter count (2-3 parameters)
* Replaced non-existent wp_print_link_tag function with proper HTML output using esc_url()
* Improved disable_emojis_tinymce function logic to eliminate unreachable code
* Added comprehensive PHPStan WordPress static analysis with proper WordPress stubs
* Enhanced security scanning with WordPress-specific vulnerability patterns
* Improved workflow reliability by removing problematic external dependencies
* Added Composer support with WordPress stubs for better development experience
* Updated code to pass PHPStan level 5 analysis with zero errors

= 1.5.5 =
* Added compatibility with WordPress 6.8
* Fixed text domain to comply with WordPress.org standards (changed from 'simple-wp-optimizer-enginescript' to 'Simple-WP-Optimizer')
* Updated all internationalization function calls with proper text domain
* Fixed missing text domain parameter in translation functions
* Resolved issues with WordPress plugin check requirements
* Fixed issue template formatting for automated GitHub issue creation
* Made the plugin fully compatible with the WordPress Plugin Check tool
* Improved documentation and code comments

== Upgrade Notice ==

= 1.5.7 =
SECURITY UPDATE: Important security enhancements including CSRF protection and DNS prefetch security. Update recommended.

= 1.5.6 =
Major security and code quality improvements with PHPStan analysis and WordPress compliance.

= 1.5.4 =
* Security enhancements and code optimization

= 1.5.3 =
* Added compatibility with WordPress 6.3
* Fixed minor issues

= 1.5.2 =
* Performance improvements
* Bug fixes

== Upgrade Notice ==

= 1.5.6 =
Major security enhancement update with comprehensive OWASP-compliant security implementation and enhanced GitHub Actions workflows. This update includes detailed security documentation, enhanced input validation, improved output escaping, secure coding patterns, and comprehensive static analysis fixes. Recommended for all users to ensure optimal security posture and code quality.

= 1.5.5 =
This update includes significant code quality improvements with comprehensive static analysis fixes and enhanced security scanning. The plugin now passes PHPStan level 5 analysis with zero errors and includes improved WordPress API compliance. Enhanced development workflow with proper WordPress stubs and more reliable testing.

= 1.5.5 =
WordPress 6.8 compatibility and internationalization fixes. Passes all Plugin Check tests.

= 1.5.4 =
This update includes security enhancements and code optimization.

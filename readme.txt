=== EngineScript: Simple WP Optimization ===
Contributors: enginescript
Tags: optimization, performance, cleanup
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 1.5.5
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

= 1.5.5 =
* Added compatibility with WordPress 6.8
* Fixed text domain to comply with WordPress.org standards (changed from 'simple-wp-optimizer-enginescript' to 'Simple-WP-Optimizer')
* Updated all internationalization function calls with proper text domain
* Fixed missing text domain parameter in translation functions
* Resolved issues with WordPress plugin check requirements
* Fixed issue template formatting for automated GitHub issue creation
* Made the plugin fully compatible with the WordPress Plugin Check tool
* Improved documentation and code comments

= 1.5.4 =
* Security enhancements and code optimization

= 1.5.3 =
* Added compatibility with WordPress 6.3
* Fixed minor issues

= 1.5.2 =
* Performance improvements
* Bug fixes

== Upgrade Notice ==

= 1.5.5 =
This update adds compatibility with WordPress 6.8 and fixes text domain issues for better internationalization. The plugin now fully complies with WordPress.org plugin directory standards and passes all WordPress Plugin Check tests.

= 1.5.4 =
This update includes security enhancements and code optimization.

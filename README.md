# Simple WP Optimizer

![GitHub License](https://img.shields.io/github/license/EngineScript/Simple-WP-Optimizer)
![WordPress Plugin Version](https://img.shields.io/badge/version-1.5.4-blue)
![WordPress Plugin Required PHP Version](https://img.shields.io/badge/php-7.4--8.4-green)
![WordPress Plugin: Tested WP Version](https://img.shields.io/badge/wordpress-5.0--6.4-green)

A lightweight WordPress plugin designed to optimize your website by removing unnecessary scripts, styles, and header elements that can slow down your site.

## Features

- **Header Cleanup:** Remove WordPress version, WLW manifest links, and shortlinks
- **Script Optimization:** Disable WordPress emojis and remove jQuery Migrate
- **Style Optimization:** Remove inline styles from recent comments widget and disable classic theme styles
- **DNS Prefetching:** Add DNS prefetch for common external domains to improve load times
- **Jetpack Optimization:** Remove Jetpack advertisements and promotions

## Installation

### Manual Installation

1. Download the latest release from the [releases page](https://github.com/EngineScript/Simple-WP-Optimizer/releases)
2. Upload the plugin files to the `/wp-content/plugins/simple-wp-optimizer` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Configure the plugin settings via the 'WP Optimizer' menu

### Using Composer

```bash
composer require enginescript/simple-wp-optimizer
```

## Usage

1. Navigate to the WP Optimizer menu in your WordPress admin dashboard (under Settings)
2. Enable the optimization features you want to use
3. Configure the DNS Prefetch domains if needed
4. Save your changes

## Screenshots

1. **Settings Page:** Configure which optimizations to enable
2. **Header Cleanup Options:** Remove unnecessary elements from WordPress headers
3. **Performance Options:** Disable emojis and jQuery Migrate
4. **DNS Prefetch Configuration:** Add domains for DNS prefetching

## Frequently Asked Questions

### Will this plugin work with my theme?

Simple WP Optimizer is designed to be compatible with most WordPress themes. The optimizations focus on removing unnecessary WordPress elements rather than modifying theme functionality.

### What does "Remove jQuery Migrate" do?

jQuery Migrate is a script that helps maintain backward compatibility with older jQuery code. Modern themes and plugins generally don't need it, so removing it can improve load time without affecting functionality in most cases.

### What does "Disable WordPress Emojis" do?

This option removes emoji-related scripts and styles that WordPress adds by default. Most websites don't need these resources, so removing them can reduce HTTP requests and improve page load time.

### Will removing the WordPress version improve security?

Yes, hiding the WordPress version can provide a minor security benefit by making it slightly more difficult for potential attackers to identify vulnerability targets based on your WordPress version.

## Development

### Requirements

- PHP 7.4 or higher
- WordPress 5.6 or higher
- Composer (for development and testing)

### Setting up the development environment

1. Clone this repository: `git clone https://github.com/EngineScript/Simple-WP-Optimizer.git`
2. Install dependencies: `composer install`
3. Set up the test environment: `bin/install-wp-tests.sh wordpress_test root '' localhost latest`
4. Run tests: `composer test`

### PHP 8.x Compatibility

This plugin is fully compatible with PHP versions 7.4 through 8.2. For testing with PHP 8.x, we provide a custom PHPUnit runner script that helps avoid common compatibility issues between PHPUnit and newer PHP versions:

```bash
# For PHP 7.4 (standard testing)
composer test

# For PHP 8.0, 8.1, and 8.2 (using the custom runner)
composer test:php8
```

The custom runner automatically detects your PHP version and applies the appropriate compatibility settings.

## Development & Maintenance

### Automated WordPress Compatibility

This repository uses the [WordPress Version Checker](https://github.com/skaut/wordpress-version-checker) GitHub Action to automatically monitor WordPress compatibility. When a new WordPress version is released, the action:

1. Creates a pull request to update the "Tested up to" version in readme.txt
2. Adds "automated" and "documentation" labels to the PR
3. Assigns repository maintainers for review

This ensures our plugin stays up-to-date with the latest WordPress versions without manual intervention.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the project
2. Create your feature branch: `git checkout -b feature/new-optimization`
3. Commit your changes: `git commit -m 'Add some new optimization'`
4. Push to the branch: `git push origin feature/new-optimization`
5. Open a Pull Request

## License

This project is licensed under the GPL-2.0+ License - see the [LICENSE](LICENSE) file for details.

## Changelog

See [CHANGELOG.MD](CHANGELOG.MD) for a list of changes in each release.

## Credits

- Developed by [EngineScript](https://github.com/EngineScript)
- Special thanks to all contributors

## Support

For support, please open an issue in the GitHub repository or contact us via [support@enginescript.com](mailto:support@enginescript.com).
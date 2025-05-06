# PHPUnit Compatibility Guide

This document provides guidance on which PHPUnit version to use based on your PHP version.

## PHP and PHPUnit Version Compatibility Matrix

| PHP Version | PHPUnit Version | Notes |
|-------------|----------------|-------|
| 7.4         | 7.5.x          | Original WordPress compatibility version |
| 8.0         | 9.5.x          | Use custom runner script (`run-phpunit.php`) |
| 8.1         | 9.5.x          | Use custom runner script (`run-phpunit.php`) |
| 8.2         | 9.5.x          | Use custom runner script (`run-phpunit.php`) |

## How to Run Tests

### For PHP 7.4

```bash
composer test
```

### For PHP 8.0, 8.1, 8.2

```bash
composer test:php8
```

## Troubleshooting

If you encounter errors when running PHPUnit on PHP 8.x, try the following:

1. Make sure you're using the right PHPUnit version for your PHP version
2. Use the custom runner script: `php run-phpunit.php`
3. Ensure the file `tests/php8-compatibility.php` exists
4. Clear any cached files by running `rm -rf vendor && composer install`

## GitHub Actions

When running tests in GitHub Actions, the workflow will automatically detect the PHP version and use the appropriate PHPUnit version and execution method.

## Common Errors and Solutions

### Error: "Fatal error: Cannot use positional argument after named argument"

This occurs on PHP 8.0+ with older PHPUnit versions. Solution: Use PHPUnit 9.x.

### Error: "Fatal error: Cannot acquire reference to $GLOBALS"

This occurs on PHPUnit 7.x with PHP 8.1+. Solution: Use the custom runner script that includes compatibility fixes.

### Error: "--no-deprecations is not a recognized option"

This option doesn't exist in older PHPUnit versions. The custom runner script will avoid using this flag.

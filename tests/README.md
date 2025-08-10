# Testing Infrastructure for Simple WP Optimizer

## Overview

This directory contains comprehensive unit and integration tests for the Simple WP Optimizer WordPress plugin. The testing infrastructure is built using PHPUnit with WP_Mock for WordPress-specific testing.

## Test Structure

```
tests/
├── bootstrap.php           # Test environment setup
├── unit/                   # Unit tests for individual functions
│   ├── ValidationTest.php  # Input validation and security tests
│   ├── OptionsTest.php     # Options management and caching tests
│   ├── RenderingTest.php   # Admin page and form rendering tests
│   ├── PerformanceTest.php # Performance optimization tests
│   └── HeaderCleanupTest.php # Header cleanup and feature tests
├── integration/            # Integration tests for WordPress hooks
│   └── IntegrationTest.php # WordPress integration and workflow tests
├── fixtures/               # Test data and sample content
│   └── test-data.php       # Reusable test fixtures
└── helpers/                # Test utility functions
    └── TestHelper.php      # Common test methods and data
```

## Test Coverage

### Functions Tested (18 total)

#### Core Functions
1. `es_optimizer_init_settings()` - Settings initialization
2. `es_optimizer_get_default_options()` - Default option values
3. `es_optimizer_get_options()` - Option retrieval with caching
4. `es_optimizer_clear_options_cache()` - Cache management
5. `es_optimizer_validate_options()` - Input validation and security
6. `es_optimizer_validate_dns_domains()` - DNS domain validation
7. `es_optimizer_validate_single_domain()` - Individual domain validation
8. `es_optimizer_show_domain_rejection_notice()` - Admin notices

#### Admin Interface Functions
9. `es_optimizer_add_settings_page()` - Settings page creation
10. `es_optimizer_load_admin_assets()` - Conditional asset loading
11. `es_optimizer_enqueue_admin_scripts()` - Admin script enqueuing
12. `es_optimizer_settings_page()` - Settings page rendering
13. `es_optimizer_render_performance_options()` - Performance option rendering
14. `es_optimizer_render_header_options()` - Header cleanup option rendering
15. `es_optimizer_render_additional_options()` - Additional feature rendering
16. `es_optimizer_render_checkbox_option()` - Checkbox field rendering
17. `es_optimizer_render_textarea_option()` - Textarea field rendering
18. `es_optimizer_add_settings_link()` - Plugin action links

#### Performance Functions (tested via integration)
- `add_dns_prefetch()` - DNS prefetch optimization
- `disable_emojis()` - Emoji removal
- `disable_emojis_tinymce()` - TinyMCE emoji removal
- `disable_emojis_remove_dns_prefetch()` - Emoji DNS prefetch removal
- `remove_jquery_migrate()` - jQuery migrate removal
- `disable_classic_theme_styles()` - Classic theme styles removal
- `remove_header_items()` - WordPress header cleanup
- `remove_recent_comments_style()` - Recent comments style removal
- `disable_jetpack_ads()` - Jetpack advertisement removal
- `disable_post_via_email()` - Post via email disabling

## Security Testing

The test suite includes comprehensive security validation:

### XSS Prevention
- Input sanitization validation
- Output escaping verification
- Script injection prevention

### DNS Prefetch Security
- HTTPS-only domain enforcement
- Local address rejection
- Path/query parameter filtering
- XSS attempt detection

### CSRF Protection
- Nonce verification testing
- Form validation security

## Performance Testing

### Caching Tests
- Option caching functionality
- Static cache validation
- Cache clearing verification

### Optimization Tests
- DNS prefetch performance
- Resource removal validation
- Hook modification testing

## Running Tests

### Prerequisites
```bash
composer install
```

### Run All Tests
```bash
composer test
```

### Run Unit Tests Only
```bash
composer test:unit
```

### Run Integration Tests Only
```bash
composer test:integration
```

### Generate Coverage Report
```bash
composer test:coverage
```

### Watch Mode (for development)
```bash
composer test:watch
```

### Coverage Threshold Check
```bash
composer coverage:check
```

## Test Configuration

### PHPUnit Configuration (phpunit.xml)
- **Unit Test Suite**: Fast isolated tests
- **Integration Test Suite**: WordPress hook testing
- **Coverage Reporting**: HTML, Clover, and Text formats
- **Strict Testing**: Warnings treated as failures
- **Memory Limit**: 512MB for comprehensive testing

### Coverage Requirements
- **Minimum Coverage**: 80%
- **Strict Coverage**: Enabled
- **Coverage Formats**: HTML (reports/), Clover (coverage.xml), Text (console)

## CI/CD Integration

### GitHub Actions Workflow
- **PHP Versions**: 7.4 - 8.4
- **Test Matrix**: Multi-version compatibility
- **Code Quality**: PHPCS, PHPMD, PHPStan integration
- **Coverage Upload**: Codecov integration

### Quality Gates
- All tests must pass
- 80% minimum code coverage
- PHPCS WordPress coding standards compliance
- PHPMD complexity analysis
- PHPStan static analysis

## Mock Framework

### WP_Mock Integration
- WordPress function mocking
- Hook expectation testing
- Admin function simulation
- Security function mocking

### Test Helpers
- Sample data generation
- Mock user creation
- HTML/CSS/JS validation
- WordPress mock functions

## Test Data Fixtures

### Sample Options
- Valid configuration sets
- Security-focused options
- Performance-focused options
- Invalid input scenarios

### DNS Domain Sets
- Valid HTTPS domains
- Invalid/malicious domains
- Mixed validation scenarios

### Security Test Data
- XSS injection attempts
- Malicious domain patterns
- Safe content validation

## Best Practices

### Test Organization
- One test class per source file function group
- Descriptive test method names
- Clear test documentation
- Isolated test environments

### Security Testing
- Always test with malicious input
- Verify proper escaping
- Validate sanitization
- Test authorization checks

### Performance Testing
- Test caching mechanisms
- Validate optimization effects
- Verify hook modifications
- Test conditional loading

## Maintenance

### Adding New Tests
1. Create test in appropriate directory (unit/ or integration/)
2. Extend TestCase from WP_Mock\Tools\TestCase
3. Follow naming convention: `Test` suffix
4. Include @since version tags
5. Add test to CI workflow if needed

### Updating Test Data
1. Modify fixtures in tests/fixtures/
2. Update TestHelper class methods
3. Regenerate coverage reports
4. Verify all tests pass

### Coverage Monitoring
- Review coverage reports regularly
- Maintain 80%+ coverage threshold
- Add tests for new features
- Remove tests for deprecated features

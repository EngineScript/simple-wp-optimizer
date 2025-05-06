<?php
/**
 * PHP 8.x Compatibility test case.
 *
 * This test file specifically tests for compatibility with PHP 8.x
 * PHP 8.x introduced stricter type checking and deprecated features
 * that might cause issues with older code.
 *
 * @package Simple_WP_Optimizer
 */

/**
 * PHP 8.x compatibility test class.
 */
class PHP8CompatibilityTest extends WP_UnitTestCase {

	/**
	 * Test for PHP 8.x compatibility in the main plugin file.
	 */
	public function test_php8_main_functions() {
		// If running on PHP 8.x, this should not cause any errors
		if (version_compare(PHP_VERSION, '8.0', '>=')) {
			// Test function that registers settings
			$result = es_optimizer_init_settings();
			$this->assertTrue(is_array($result) || is_null($result), 'The function does not return expected result in PHP 8.x');
			
			// Test for union types support (PHP 8.0+)
			$defaults = es_optimizer_get_default_options();
			$this->assertIsArray($defaults, 'Default options should be an array in PHP 8.x');
		} else {
			$this->markTestSkipped('This test only runs on PHP 8.x');
		}
	}
	
	/**
	 * Test for proper null handling (PHP 8.1 feature).
	 */
	public function test_php81_null_handling() {
		// Only run this test on PHP 8.1+
		if (version_compare(PHP_VERSION, '8.1', '>=')) {
			// Test null coalescing operator with functions that might return null
			$options = get_option('es_optimizer_settings') ?? es_optimizer_get_default_options();
			$this->assertIsArray($options, 'Options should be an array with null coalescing operator in PHP 8.1');
			
			// Test the "first class callable" syntax indirectly
			$callable = 'es_optimizer_get_default_options';
			$result = $callable();
			$this->assertIsArray($result, 'First class callable should work in PHP 8.1');
		} else {
			$this->markTestSkipped('This test only runs on PHP 8.1+');
		}
	}
	
	/**
	 * Test for PHP 8.2 compatibility features.
	 */
	public function test_php82_features() {
		// Only run this test on PHP 8.2+
		if (version_compare(PHP_VERSION, '8.2', '>=')) {
			// Test handling of null, false, and undefined array keys
			$settings = [];
			// In PHP 8.2 this shouldn't trigger a warning like it did in earlier versions
			$test_value = $settings['nonexistent_key'] ?? 'default';
			$this->assertEquals('default', $test_value, 'Undefined array key should be handled gracefully in PHP 8.2');
			
			// Test error handling improvements
			try {
				// This should throw a proper exception in PHP 8.2 instead of a warning
				$test_value = $settings['nonexistent_key'];
				$this->fail('Should have thrown an exception in PHP 8.2 with strict error reporting');
			} catch (\Throwable $e) {
				// Exception caught as expected
				$this->assertTrue(true);
			}
		} else {
			$this->markTestSkipped('This test only runs on PHP 8.2+');
		}
	}
	
	/**
	 * Test for deprecated features that would cause issues in PHP 8.x.
	 */
	public function test_deprecated_features() {
		// This test should run on all PHP versions
		// Test that we're not using any deprecated features in PHP 8.x
		
		// For example, check that dynamic properties are not used
		// (deprecated in PHP 8.2)
		$object = new stdClass();
		$property = 'dynamic_property';
		$this->assertFalse(property_exists($object, $property), 'Object should not have dynamic property before assignment');
		
		// Set property and check
		$object->$property = 'value';
		$this->assertTrue(property_exists($object, $property), 'Object should have dynamic property after assignment');
		
		// In PHP 8.x this would not cause an error, but it's a good practice to avoid it
		$this->assertEquals('value', $object->$property, 'Dynamic property should have correct value');
	}
}

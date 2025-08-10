<?php
/**
 * Coverage Threshold Checker
 *
 * Simple script to check if code coverage meets the minimum threshold.
 * This is a CLI script and does not require WordPress escaping functions.
 *
 * @package Simple_WP_Optimizer
 * @subpackage Tests
 * @since 1.5.12
 */

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
// phpcs:disable WordPress.WP.AlternativeFunctions.parse_url_parse_url
// phpcs:disable WordPress.WP.AlternativeFunctions.strip_tags_strip_tags

<?php
/**
 * Coverage Threshold Checker
 *
 * Simple script to check if code coverage meets the minimum threshold.
 * This is a CLI script and does not require WordPress escaping functions.
 *
 * @package Simple_WP_Optimizer
 * @subpackage Tests
 * @since 1.5.12
 */

// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
// phpcs:disable WordPress.WP.AlternativeFunctions.parse_url_parse_url
// phpcs:disable WordPress.WP.AlternativeFunctions.strip_tags_strip_tags
// phpcs:disable WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_error_reporting

/**
 * Check coverage threshold from PHPUnit clover XML output
 *
 * @param string $clover_file Path to clover XML file.
 * @param float  $threshold   Minimum coverage percentage required.
 * @return bool True if coverage meets threshold.
 */
function check_coverage_threshold( $clover_file = 'coverage.xml', $threshold = 80.0 ) {
	if ( ! file_exists( $clover_file ) ) {
		fwrite( STDERR, sprintf( "Coverage file not found: %s\n", $clover_file ) );
		return false;
	}

	$xml = simplexml_load_file( $clover_file );
	if ( ! $xml ) {
		fwrite( STDERR, "Failed to parse coverage XML file\n" );
		return false;
	}

	$metrics = $xml->project->metrics;
	if ( ! $metrics ) {
		fwrite( STDERR, "No metrics found in coverage file\n" );
		return false;
	}

	$covered_statements = (float) $metrics['coveredstatements'];
	$total_statements   = (float) $metrics['statements'];

	if ( $total_statements === 0.0 ) {
		fwrite( STDERR, "No statements found to check coverage\n" );
		return false;
	}

	$coverage_percentage = ( $covered_statements / $total_statements ) * 100;

	fwrite( 
		STDOUT,
		sprintf(
			"Code Coverage: %.2f%% (%d/%d statements)\n", 
			$coverage_percentage, 
			(int) $covered_statements, 
			(int) $total_statements 
		)
	);

	if ( $coverage_percentage >= $threshold ) {
		fwrite( 
			STDOUT,
			sprintf( "✅ Coverage meets threshold (%.2f%% >= %.2f%%)\n", $coverage_percentage, $threshold )
		);
		return true;
	} else {
		fwrite( 
			STDERR,
			sprintf( "❌ Coverage below threshold (%.2f%% < %.2f%%)\n", $coverage_percentage, $threshold )
		);
		return false;
	}
}

// Run the coverage check
$threshold = isset( $argv[1] ) ? (float) $argv[1] : 80.0;
$clover_file = isset( $argv[2] ) ? $argv[2] : 'coverage.xml';

$success = check_coverage_threshold( $clover_file, $threshold );
exit( $success ? 0 : 1 );

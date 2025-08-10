<?php
/**
 * Coverage Threshold Checker
 *
 * Simple script to check if code coverage meets the minimum threshold.
 *
 * @package Simple_WP_Optimizer
 * @subpackage Tests
 * @since 1.5.12
 */

/**
 * Check coverage threshold from PHPUnit clover XML output
 *
 * @param string $clover_file Path to clover XML file.
 * @param float  $threshold   Minimum coverage percentage required.
 * @return bool True if coverage meets threshold.
 */
function check_coverage_threshold( $clover_file = 'coverage.xml', $threshold = 80.0 ) {
	if ( ! file_exists( $clover_file ) ) {
		echo "Coverage file not found: {$clover_file}\n";
		return false;
	}

	$xml = simplexml_load_file( $clover_file );
	if ( ! $xml ) {
		echo "Failed to parse coverage XML file\n";
		return false;
	}

	$metrics = $xml->project->metrics;
	if ( ! $metrics ) {
		echo "No metrics found in coverage file\n";
		return false;
	}

	$covered_statements = (float) $metrics['coveredstatements'];
	$total_statements   = (float) $metrics['statements'];

	if ( $total_statements === 0.0 ) {
		echo "No statements found to check coverage\n";
		return false;
	}

	$coverage_percentage = ( $covered_statements / $total_statements ) * 100;

	printf( "Code Coverage: %.2f%% (%d/%d statements)\n", 
		$coverage_percentage, 
		(int) $covered_statements, 
		(int) $total_statements 
	);

	if ( $coverage_percentage >= $threshold ) {
		printf( "✅ Coverage meets threshold (%.2f%% >= %.2f%%)\n", $coverage_percentage, $threshold );
		return true;
	} else {
		printf( "❌ Coverage below threshold (%.2f%% < %.2f%%)\n", $coverage_percentage, $threshold );
		return false;
	}
}

// Run the coverage check
$threshold = isset( $argv[1] ) ? (float) $argv[1] : 80.0;
$clover_file = isset( $argv[2] ) ? $argv[2] : 'coverage.xml';

$success = check_coverage_threshold( $clover_file, $threshold );
exit( $success ? 0 : 1 );

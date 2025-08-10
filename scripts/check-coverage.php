#!/usr/bin/env php
<?php
/**
 * Simple coverage check script for Simple WP Optimizer
 * 
 * Checks that code coverage meets the minimum threshold of 80%
 * 
 * @package Simple_WP_Optimizer
 * @subpackage Tests
 * @since 1.5.12
 */

// Check if coverage.xml exists
if (!file_exists('coverage.xml')) {
    echo "❌ Coverage report not found. Run 'composer test:coverage' first.\n";
    exit(1);
}

// Parse the clover XML coverage report
$xml = simplexml_load_file('coverage.xml');

if (!$xml) {
    echo "❌ Failed to parse coverage report.\n";
    exit(1);
}

// Extract coverage metrics
$metrics = $xml->project->metrics;
$covered = (int) $metrics['coveredstatements'];
$statements = (int) $metrics['statements'];

if ($statements === 0) {
    echo "❌ No statements found in coverage report.\n";
    exit(1);
}

$coverage = ($covered / $statements) * 100;
$threshold = 80.0;

echo "📊 Code Coverage Report\n";
echo "========================\n";
echo sprintf("Total Statements: %d\n", $statements);
echo sprintf("Covered Statements: %d\n", $covered);
echo sprintf("Coverage: %.2f%%\n", $coverage);
echo sprintf("Threshold: %.1f%%\n", $threshold);
echo "\n";

if ($coverage >= $threshold) {
    echo sprintf("✅ Coverage of %.2f%% meets the required threshold of %.1f%%\n", $coverage, $threshold);
    exit(0);
} else {
    echo sprintf("❌ Coverage of %.2f%% is below the required threshold of %.1f%%\n", $coverage, $threshold);
    echo sprintf("Need to improve coverage by %.2f%% to meet requirements.\n", $threshold - $coverage);
    exit(1);
}

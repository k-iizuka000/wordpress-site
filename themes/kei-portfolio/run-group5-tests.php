<?php
/**
 * Group 5 Test Execution Script
 * Execute Custom Post Types and Archive Tests
 * 
 * Usage: php run-group5-tests.php
 * 
 * @package Kei_Portfolio
 */

// Ensure we're in a WordPress environment
if ( ! defined( 'ABSPATH' ) ) {
    // Try to locate WordPress installation
    $wp_config_paths = array(
        __DIR__ . '/wp-config.php',
        __DIR__ . '/../wp-config.php',
        __DIR__ . '/../../wp-config.php',
        __DIR__ . '/../../../wp-config.php',
    );
    
    $wp_config_found = false;
    foreach ( $wp_config_paths as $config_path ) {
        if ( file_exists( $config_path ) ) {
            require_once $config_path;
            $wp_config_found = true;
            break;
        }
    }
    
    if ( ! $wp_config_found ) {
        die( "WordPress configuration not found. Please run this script from within a WordPress environment.\n" );
    }
}

// Load WordPress
if ( ! function_exists( 'wp' ) ) {
    require_once ABSPATH . 'wp-load.php';
}

// Load test framework
require_once __DIR__ . '/tests/bootstrap.php';

// Load test classes
require_once __DIR__ . '/tests/CustomPostTypeTest.php';
require_once __DIR__ . '/tests/ArchiveTest.php';
require_once __DIR__ . '/tests/ProjectMetaFieldsTest.php';
require_once __DIR__ . '/tests/SecurityTest.php';
require_once __DIR__ . '/tests/PerformanceTest.php';
require_once __DIR__ . '/tests/TestRunner.php';

echo "=== WordPress Theme kei-portfolio Group 5 Tests ===\n";
echo "Testing Custom Post Types, Archive functionality, Security, and Performance\n";
echo "Started at: " . date( 'Y-m-d H:i:s' ) . "\n\n";

// Validate test environment
echo "Validating test environment...\n";
$validations = TestRunner::validate_test_environment();

foreach ( $validations as $check => $result ) {
    $status = $result ? '✓' : '✗';
    echo "  {$status} " . str_replace( '_', ' ', ucwords( $check, '_' ) ) . "\n";
}

$all_valid = array_reduce( $validations, function( $carry, $item ) { 
    return $carry && $item; 
}, true );

if ( ! $all_valid ) {
    die( "\nTest environment validation failed. Please check the requirements above.\n" );
}

echo "\nTest environment validated successfully.\n\n";

// Get test statistics
$stats = TestRunner::get_test_statistics();
echo "Test Statistics:\n";
echo "  - Total Test Files: {$stats['total_test_files']}\n";
echo "  - Total Test Methods: {$stats['total_test_methods']}\n";
echo "  - Test Groups: " . implode( ', ', $stats['test_groups'] ) . "\n\n";

// Run all tests
echo "Executing tests...\n\n";

try {
    $results = TestRunner::run_all_tests();
    $report = TestRunner::generate_report( $results );
    
    echo $report . "\n";
    
    // Save report to file
    $report_file = __DIR__ . '/test-reports/group5-test-report-' . date( 'Y-m-d-H-i-s' ) . '.txt';
    $report_dir = dirname( $report_file );
    
    if ( ! is_dir( $report_dir ) ) {
        mkdir( $report_dir, 0755, true );
    }
    
    file_put_contents( $report_file, $report );
    echo "\nTest report saved to: {$report_file}\n";
    
    // Check coverage requirements
    $coverage_requirements = TestRunner::check_coverage_requirements();
    echo "\n=== Coverage Requirements Check ===\n";
    
    foreach ( $coverage_requirements as $area => $requirement ) {
        echo "• " . ucwords( str_replace( '_', ' ', $area ) ) . ": {$requirement['description']}\n";
        echo "  Required tests: " . implode( ', ', $requirement['tests'] ) . "\n";
    }
    
    echo "\nTest execution completed successfully.\n";
    
} catch ( Exception $e ) {
    echo "Error during test execution: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit( 1 );
}

echo "\n=== Test Execution Summary ===\n";
echo "All Group 5 tests have been executed.\n";
echo "Please review the test report above for detailed results.\n";
echo "For continuous integration, check that all tests pass before deployment.\n";

// Return appropriate exit code
$overall_success = true;
foreach ( $results as $group ) {
    if ( $group['summary']['failed'] > 0 || $group['summary']['errors'] > 0 ) {
        $overall_success = false;
        break;
    }
}

exit( $overall_success ? 0 : 1 );
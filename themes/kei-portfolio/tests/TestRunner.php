<?php
/**
 * Test Runner and Test Suite Coordinator
 * Coordinates running Group 5 tests (Custom Post Types and Archive)
 * 
 * @package Kei_Portfolio
 * @subpackage Tests
 * @group test-runner
 */

class TestRunner {
    
    /**
     * Test groups configuration
     */
    private static $test_groups = array(
        'custom-post-types' => array(
            'CustomPostTypeTest',
            'ProjectMetaFieldsTest',
            'ArchiveTest',
        ),
        'security' => array(
            'SecurityTest',
        ),
        'performance' => array(
            'PerformanceTest',
        ),
    );
    
    /**
     * Run all Group 5 tests
     */
    public static function run_all_tests() {
        $results = array();
        
        foreach ( self::$test_groups as $group => $test_classes ) {
            $results[$group] = self::run_test_group( $group, $test_classes );
        }
        
        return $results;
    }
    
    /**
     * Run specific test group
     */
    public static function run_test_group( $group_name, $test_classes ) {
        $group_results = array(
            'group' => $group_name,
            'tests' => array(),
            'summary' => array(
                'total' => 0,
                'passed' => 0,
                'failed' => 0,
                'errors' => 0,
                'execution_time' => 0,
            ),
        );
        
        $start_time = microtime( true );
        
        foreach ( $test_classes as $test_class ) {
            $class_results = self::run_test_class( $test_class );
            $group_results['tests'][$test_class] = $class_results;
            
            // Update summary
            $group_results['summary']['total'] += $class_results['summary']['total'];
            $group_results['summary']['passed'] += $class_results['summary']['passed'];
            $group_results['summary']['failed'] += $class_results['summary']['failed'];
            $group_results['summary']['errors'] += $class_results['summary']['errors'];
        }
        
        $group_results['summary']['execution_time'] = microtime( true ) - $start_time;
        
        return $group_results;
    }
    
    /**
     * Run tests for a specific class
     */
    public static function run_test_class( $test_class ) {
        $results = array(
            'class' => $test_class,
            'methods' => array(),
            'summary' => array(
                'total' => 0,
                'passed' => 0,
                'failed' => 0,
                'errors' => 0,
            ),
        );
        
        if ( ! class_exists( $test_class ) ) {
            $results['summary']['errors'] = 1;
            $results['error'] = "Class {$test_class} not found";
            return $results;
        }
        
        $reflection = new ReflectionClass( $test_class );
        $methods = $reflection->getMethods( ReflectionMethod::IS_PUBLIC );
        
        foreach ( $methods as $method ) {
            if ( strpos( $method->name, 'test_' ) === 0 || $method->getDocComment() && strpos( $method->getDocComment(), '@test' ) !== false ) {
                $method_result = self::run_test_method( $test_class, $method->name );
                $results['methods'][$method->name] = $method_result;
                
                $results['summary']['total']++;
                if ( $method_result['status'] === 'passed' ) {
                    $results['summary']['passed']++;
                } elseif ( $method_result['status'] === 'failed' ) {
                    $results['summary']['failed']++;
                } else {
                    $results['summary']['errors']++;
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Run a specific test method
     */
    public static function run_test_method( $test_class, $method_name ) {
        $result = array(
            'method' => $method_name,
            'status' => 'unknown',
            'message' => '',
            'execution_time' => 0,
        );
        
        try {
            $start_time = microtime( true );
            
            // This is a simplified test runner - in a real implementation,
            // you would use PHPUnit's test runner
            $instance = new $test_class();
            
            if ( method_exists( $instance, 'setUp' ) ) {
                $instance->setUp();
            }
            
            $instance->$method_name();
            
            if ( method_exists( $instance, 'tearDown' ) ) {
                $instance->tearDown();
            }
            
            $result['status'] = 'passed';
            $result['execution_time'] = microtime( true ) - $start_time;
            
        } catch ( Exception $e ) {
            $result['status'] = 'failed';
            $result['message'] = $e->getMessage();
            $result['execution_time'] = microtime( true ) - $start_time;
        }
        
        return $result;
    }
    
    /**
     * Generate test report
     */
    public static function generate_report( $results ) {
        $report = array();
        $report[] = "=== WordPress Theme kei-portfolio Test Report ===";
        $report[] = "Generated: " . date( 'Y-m-d H:i:s' );
        $report[] = "Test Framework: PHPUnit";
        $report[] = "Test Group: Group 5 - Custom Post Types and Archive";
        $report[] = "";
        
        $overall_totals = array(
            'total' => 0,
            'passed' => 0,
            'failed' => 0,
            'errors' => 0,
            'execution_time' => 0,
        );
        
        foreach ( $results as $group_name => $group_results ) {
            $report[] = "## Test Group: {$group_name}";
            $report[] = sprintf( 
                "Tests: %d | Passed: %d | Failed: %d | Errors: %d | Time: %.2fs",
                $group_results['summary']['total'],
                $group_results['summary']['passed'],
                $group_results['summary']['failed'],
                $group_results['summary']['errors'],
                $group_results['summary']['execution_time']
            );
            
            // Update overall totals
            $overall_totals['total'] += $group_results['summary']['total'];
            $overall_totals['passed'] += $group_results['summary']['passed'];
            $overall_totals['failed'] += $group_results['summary']['failed'];
            $overall_totals['errors'] += $group_results['summary']['errors'];
            $overall_totals['execution_time'] += $group_results['summary']['execution_time'];
            
            // Test class details
            foreach ( $group_results['tests'] as $class_name => $class_results ) {
                $report[] = "";
                $report[] = "### {$class_name}";
                $report[] = sprintf(
                    "Tests: %d | Passed: %d | Failed: %d | Errors: %d",
                    $class_results['summary']['total'],
                    $class_results['summary']['passed'],
                    $class_results['summary']['failed'],
                    $class_results['summary']['errors']
                );
                
                // Show failed tests
                if ( isset( $class_results['methods'] ) ) {
                    foreach ( $class_results['methods'] as $method_name => $method_result ) {
                        if ( $method_result['status'] === 'failed' ) {
                            $report[] = "  ✗ {$method_name}: {$method_result['message']}";
                        } elseif ( $method_result['status'] === 'passed' ) {
                            $report[] = "  ✓ {$method_name}";
                        }
                    }
                }
            }
            
            $report[] = "";
        }
        
        // Overall summary
        $report[] = "=== Overall Summary ===";
        $report[] = sprintf(
            "Total Tests: %d | Passed: %d | Failed: %d | Errors: %d | Time: %.2fs",
            $overall_totals['total'],
            $overall_totals['passed'],
            $overall_totals['failed'],
            $overall_totals['errors'],
            $overall_totals['execution_time']
        );
        
        $success_rate = $overall_totals['total'] > 0 ? 
            ( $overall_totals['passed'] / $overall_totals['total'] ) * 100 : 0;
            
        $report[] = sprintf( "Success Rate: %.1f%%", $success_rate );
        
        // Quality assessment
        $report[] = "";
        $report[] = "=== Quality Assessment ===";
        
        if ( $success_rate >= 95 ) {
            $report[] = "🟢 Excellent - All tests passing with high reliability";
        } elseif ( $success_rate >= 90 ) {
            $report[] = "🟡 Good - Most tests passing, minor issues detected";
        } elseif ( $success_rate >= 80 ) {
            $report[] = "🟠 Acceptable - Some failures detected, requires attention";
        } else {
            $report[] = "🔴 Poor - Significant issues detected, requires immediate attention";
        }
        
        // Performance assessment
        if ( $overall_totals['execution_time'] < 30 ) {
            $report[] = "⚡ Performance - Fast test execution";
        } elseif ( $overall_totals['execution_time'] < 60 ) {
            $report[] = "⏱️  Performance - Moderate test execution time";
        } else {
            $report[] = "🐌 Performance - Slow test execution, optimization needed";
        }
        
        return implode( "\n", $report );
    }
    
    /**
     * Check test coverage requirements
     */
    public static function check_coverage_requirements() {
        $requirements = array(
            'custom_post_type_registration' => array(
                'description' => 'Project post type registration and configuration',
                'tests' => array(
                    'test_project_post_type_exists',
                    'test_project_post_type_parameters',
                    'test_project_post_type_supports',
                ),
                'coverage' => 0,
            ),
            'taxonomy_registration' => array(
                'description' => 'Technology and Industry taxonomy registration',
                'tests' => array(
                    'test_technology_taxonomy_exists',
                    'test_industry_taxonomy_exists',
                    'test_taxonomy_association',
                ),
                'coverage' => 0,
            ),
            'archive_functionality' => array(
                'description' => 'Archive page functionality and display',
                'tests' => array(
                    'test_project_archive_page_exists',
                    'test_archive_page_query',
                    'test_archive_template_file_loading',
                ),
                'coverage' => 0,
            ),
            'security' => array(
                'description' => 'XSS, SQL injection, and data sanitization protection',
                'tests' => array(
                    'test_xss_protection_in_project_content',
                    'test_sql_injection_protection',
                    'test_meta_field_sanitization',
                ),
                'coverage' => 0,
            ),
            'performance' => array(
                'description' => 'Query performance and scalability',
                'tests' => array(
                    'test_archive_query_performance',
                    'test_meta_query_performance',
                    'test_taxonomy_query_performance',
                ),
                'coverage' => 0,
            ),
        );
        
        return $requirements;
    }
    
    /**
     * Validate test environment
     */
    public static function validate_test_environment() {
        $validations = array();
        
        // Check WordPress test framework
        $validations['wordpress_test_framework'] = class_exists( 'WP_UnitTestCase' );
        
        // Check required post types
        $validations['project_post_type'] = post_type_exists( 'project' );
        
        // Check required taxonomies  
        $validations['technology_taxonomy'] = taxonomy_exists( 'technology' );
        $validations['industry_taxonomy'] = taxonomy_exists( 'industry' );
        
        // Check template files
        $template_dir = get_template_directory();
        $validations['archive_template'] = file_exists( $template_dir . '/archive-project.php' );
        $validations['post_types_file'] = file_exists( $template_dir . '/inc/post-types.php' );
        
        // Check PHP extensions
        $validations['reflection_extension'] = class_exists( 'ReflectionClass' );
        $validations['mysqli_extension'] = extension_loaded( 'mysqli' );
        
        return $validations;
    }
    
    /**
     * Get test statistics
     */
    public static function get_test_statistics() {
        $stats = array(
            'total_test_files' => 0,
            'total_test_methods' => 0,
            'coverage_areas' => array(),
            'test_groups' => array_keys( self::$test_groups ),
        );
        
        foreach ( self::$test_groups as $group => $classes ) {
            $stats['total_test_files'] += count( $classes );
            
            foreach ( $classes as $class ) {
                if ( class_exists( $class ) ) {
                    $reflection = new ReflectionClass( $class );
                    $methods = $reflection->getMethods( ReflectionMethod::IS_PUBLIC );
                    
                    foreach ( $methods as $method ) {
                        if ( strpos( $method->name, 'test_' ) === 0 || 
                             ( $method->getDocComment() && strpos( $method->getDocComment(), '@test' ) !== false ) ) {
                            $stats['total_test_methods']++;
                        }
                    }
                }
            }
        }
        
        return $stats;
    }
}
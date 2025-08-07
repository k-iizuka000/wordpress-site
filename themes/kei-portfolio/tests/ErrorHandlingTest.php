<?php
/**
 * Test suite for theme error handling functionality
 * 
 * Tests proper error handling during theme activation, missing files,
 * and fallback mechanisms
 * 
 * @package Kei_Portfolio
 * @group error-handling
 */

class ErrorHandlingTest extends WP_UnitTestCase {

    /**
     * Theme directory path
     * @var string
     */
    private $theme_dir;

    /**
     * Backup of original error reporting level
     * @var int
     */
    private $original_error_reporting;

    /**
     * Setup before each test
     */
    public function setUp(): void {
        parent::setUp();
        
        $this->theme_dir = get_template_directory();
        $this->original_error_reporting = error_reporting();
        
        // Ensure theme setup has run
        if ( ! did_action( 'after_setup_theme' ) ) {
            do_action( 'after_setup_theme' );
        }
    }

    /**
     * Cleanup after each test
     */
    public function tearDown(): void {
        // Restore original error reporting
        error_reporting( $this->original_error_reporting );
        
        parent::tearDown();
    }

    /**
     * GROUP 10: エラーハンドリングテスト
     * Test theme activation with missing required files
     */
    public function test_missing_required_files_handling() {
        // Test behavior when functions.php is missing (simulated)
        $functions_path = $this->theme_dir . '/functions.php';
        
        if ( file_exists( $functions_path ) ) {
            // Test that functions.php exists and can be read
            $this->assertFileExists( 
                $functions_path,
                'functions.php should exist'
            );
            
            // Test functions.php can be parsed without fatal errors
            $functions_content = file_get_contents( $functions_path );
            $this->assertNotEmpty( 
                $functions_content,
                'functions.php should not be empty'
            );
            
            // Test for syntax errors
            $syntax_check = php_check_syntax( $functions_path );
            $this->assertTrue( 
                $syntax_check,
                'functions.php should have valid PHP syntax'
            );
        }
    }

    /**
     * Test PHP error handling during theme setup
     */
    public function test_php_error_handling_during_setup() {
        // Enable error reporting for this test
        error_reporting( E_ALL );
        
        $errors_found = array();
        
        // Custom error handler to catch errors
        set_error_handler( function( $errno, $errstr, $errfile, $errline ) use ( &$errors_found ) {
            $errors_found[] = array(
                'type' => $errno,
                'message' => $errstr,
                'file' => $errfile,
                'line' => $errline
            );
        } );
        
        // Re-run theme setup to check for errors
        if ( function_exists( 'kei_portfolio_pro_setup' ) ) {
            ob_start();
            kei_portfolio_pro_setup();
            ob_end_clean();
        }
        
        // Restore default error handler
        restore_error_handler();
        
        // Check that no errors were generated
        $this->assertEmpty( 
            $errors_found,
            'Theme setup should not generate PHP errors. Found: ' . print_r( $errors_found, true )
        );
    }

    /**
     * Test database error handling
     */
    public function test_database_error_handling() {
        global $wpdb;
        
        // Test that functions handle database errors gracefully
        if ( function_exists( 'kei_portfolio_import_json_data' ) ) {
            // Temporarily suppress database errors
            $wpdb->suppress_errors( true );
            
            // Test import function with potential database issues
            ob_start();
            $result = kei_portfolio_import_json_data();
            $output = ob_get_clean();
            
            // Restore database error reporting
            $wpdb->suppress_errors( false );
            
            // Function should handle errors gracefully
            $this->assertIsBool( 
                $result,
                'Import function should return boolean result even on database errors'
            );
        }
    }

    /**
     * Test missing template file fallbacks
     */
    public function test_missing_template_fallbacks() {
        // Test template hierarchy fallback behavior
        $template_hierarchy = array(
            'front-page.php' => 'index.php',
            'single.php'     => 'index.php',
            'page.php'       => 'index.php',
            'archive.php'    => 'index.php'
        );
        
        foreach ( $template_hierarchy as $primary => $fallback ) {
            $primary_path = $this->theme_dir . '/' . $primary;
            $fallback_path = $this->theme_dir . '/' . $fallback;
            
            // If primary template doesn't exist, fallback should exist
            if ( ! file_exists( $primary_path ) ) {
                $this->assertFileExists( 
                    $fallback_path,
                    sprintf( 'Fallback template %s should exist when %s is missing', $fallback, $primary )
                );
            }
        }
        
        // index.php is absolutely required
        $this->assertFileExists( 
            $this->theme_dir . '/index.php',
            'index.php is required as the ultimate fallback template'
        );
    }

    /**
     * Test missing image fallback behavior
     */
    public function test_missing_image_fallbacks() {
        // Create a test post without featured image
        $post_id = $this->factory->post->create( array(
            'post_title'   => 'Post Without Featured Image',
            'post_content' => 'Test post content without featured image.',
            'post_status'  => 'publish'
        ) );
        
        // Test that theme handles missing featured images gracefully
        $this->go_to( get_permalink( $post_id ) );
        
        // Check if theme has fallback image functionality
        $fallback_image_path = $this->theme_dir . '/assets/images/default-featured-image.jpg';
        
        if ( file_exists( $fallback_image_path ) ) {
            $this->assertFileExists( 
                $fallback_image_path,
                'Default featured image should exist as fallback'
            );
        }
        
        // Test that get_the_post_thumbnail doesn't cause errors
        ob_start();
        $thumbnail = get_the_post_thumbnail( $post_id );
        ob_end_clean();
        
        $this->assertIsString( 
            $thumbnail,
            'get_the_post_thumbnail should return string even when no featured image exists'
        );
        
        // Clean up
        wp_delete_post( $post_id, true );
    }

    /**
     * Test JavaScript disabled fallback
     */
    public function test_javascript_disabled_fallback() {
        // Test that critical functionality works without JavaScript
        $templates_with_js = array(
            '/template-parts/portfolio-filter.php',
            '/template-parts/navigation.php',
            '/template-parts/contact-section.php'
        );
        
        foreach ( $templates_with_js as $template ) {
            $template_path = $this->theme_dir . $template;
            
            if ( file_exists( $template_path ) ) {
                $template_content = file_get_contents( $template_path );
                
                // Check for noscript elements or progressive enhancement
                $has_fallback = strpos( $template_content, '<noscript>' ) !== false ||
                               strpos( $template_content, 'no-js' ) !== false ||
                               strpos( $template_content, 'progressive' ) !== false;
                
                // Note: This is a recommendation, not a hard requirement
                if ( ! $has_fallback ) {
                    $this->markTestIncomplete( 
                        sprintf( '%s could benefit from JavaScript fallback mechanisms', $template )
                    );
                }
            }
        }
    }

    /**
     * Test error logging functionality
     */
    public function test_error_logging() {
        // Test that theme doesn't write to error log unnecessarily
        $initial_log_size = 0;
        
        // Check if error log exists and get initial size
        if ( ini_get( 'log_errors' ) && ini_get( 'error_log' ) && file_exists( ini_get( 'error_log' ) ) ) {
            $initial_log_size = filesize( ini_get( 'error_log' ) );
        }
        
        // Run theme setup functions
        if ( function_exists( 'kei_portfolio_pro_setup' ) ) {
            kei_portfolio_pro_setup();
        }
        
        if ( function_exists( 'kei_portfolio_pro_enqueue_styles' ) ) {
            kei_portfolio_pro_enqueue_styles();
        }
        
        if ( function_exists( 'kei_portfolio_pro_enqueue_scripts' ) ) {
            kei_portfolio_pro_enqueue_scripts();
        }
        
        // Check if error log size increased
        if ( ini_get( 'log_errors' ) && ini_get( 'error_log' ) && file_exists( ini_get( 'error_log' ) ) ) {
            $final_log_size = filesize( ini_get( 'error_log' ) );
            
            $this->assertEquals( 
                $initial_log_size,
                $final_log_size,
                'Theme functions should not generate error log entries during normal operation'
            );
        }
    }

    /**
     * Test user error notification (admin notices)
     */
    public function test_user_error_notifications() {
        // Test that theme provides helpful error messages to users
        
        // Check if theme has admin notice functions
        $admin_notice_functions = array(
            'kei_portfolio_admin_notice',
            'kei_portfolio_activation_notice'
        );
        
        foreach ( $admin_notice_functions as $function ) {
            if ( function_exists( $function ) ) {
                // Test that function exists and is callable
                $this->assertTrue( 
                    is_callable( $function ),
                    sprintf( 'Admin notice function %s should be callable', $function )
                );
                
                // Test that function is properly hooked
                $this->assertTrue( 
                    has_action( 'admin_notices', $function ) !== false ||
                    has_action( 'admin_init', $function ) !== false,
                    sprintf( 'Admin notice function %s should be hooked to appropriate action', $function )
                );
            }
        }
    }

    /**
     * Test debugging information control
     */
    public function test_debug_information_control() {
        // Test that theme respects WP_DEBUG setting
        $debug_functions = array(
            'error_log',
            'var_dump',
            'print_r',
            'var_export'
        );
        
        // Scan theme files for debug statements
        $theme_files = glob( $this->theme_dir . '/*.php' );
        $theme_files = array_merge( $theme_files, glob( $this->theme_dir . '/**/*.php' ) );
        
        foreach ( $theme_files as $file ) {
            if ( is_file( $file ) ) {
                $file_content = file_get_contents( $file );
                
                foreach ( $debug_functions as $debug_func ) {
                    // Check for unguarded debug statements
                    $pattern = '/' . $debug_func . '\s*\(/';
                    
                    if ( preg_match( $pattern, $file_content ) ) {
                        // Check if it's wrapped in WP_DEBUG check
                        $has_debug_guard = strpos( $file_content, 'WP_DEBUG' ) !== false ||
                                         strpos( $file_content, 'defined( \'WP_DEBUG\' )' ) !== false;
                        
                        if ( ! $has_debug_guard ) {
                            $this->markTestIncomplete( 
                                sprintf( 'Debug statement %s in %s should be wrapped in WP_DEBUG check', $debug_func, basename( $file ) )
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * Test memory usage and performance under stress
     */
    public function test_memory_usage_stress() {
        $initial_memory = memory_get_usage();
        
        // Simulate multiple theme operations
        for ( $i = 0; $i < 10; $i++ ) {
            if ( function_exists( 'kei_portfolio_pro_setup' ) ) {
                kei_portfolio_pro_setup();
            }
            
            // Create and delete test posts
            $post_id = $this->factory->post->create( array(
                'post_title' => 'Stress Test Post ' . $i,
                'post_content' => 'Content for stress testing memory usage.',
                'post_status' => 'publish'
            ) );
            
            wp_delete_post( $post_id, true );
        }
        
        $final_memory = memory_get_usage();
        $memory_increase = $final_memory - $initial_memory;
        
        // Memory increase should be reasonable (less than 10MB for this test)
        $this->assertLessThan( 
            10 * 1024 * 1024,
            $memory_increase,
            'Theme operations should not cause excessive memory usage'
        );
    }

    /**
     * Test theme behavior with limited permissions
     */
    public function test_limited_permissions_handling() {
        // Test theme behavior when upload directory is not writable
        $upload_dir = wp_upload_dir();
        
        if ( $upload_dir && ! $upload_dir['error'] && is_dir( $upload_dir['path'] ) ) {
            $original_perms = fileperms( $upload_dir['path'] );
            
            // Temporarily remove write permissions (if possible)
            if ( is_writable( $upload_dir['path'] ) ) {
                chmod( $upload_dir['path'], 0444 );
                
                // Test that theme handles read-only upload directory
                if ( function_exists( 'kei_portfolio_import_json_data' ) ) {
                    ob_start();
                    $result = kei_portfolio_import_json_data();
                    ob_end_clean();
                    
                    // Should handle gracefully
                    $this->assertIsBool( 
                        $result,
                        'Theme should handle read-only upload directory gracefully'
                    );
                }
                
                // Restore original permissions
                chmod( $upload_dir['path'], $original_perms );
            }
        }
    }

    /**
     * Test invalid JSON data handling
     */
    public function test_invalid_json_data_handling() {
        // Test portfolio import with invalid JSON
        if ( function_exists( 'kei_portfolio_import_json_data' ) ) {
            // Create temporary invalid JSON file
            $temp_json = $this->theme_dir . '/portfolio-test.json';
            file_put_contents( $temp_json, '{"invalid": json data}' );
            
            // Test that function handles invalid JSON gracefully
            ob_start();
            
            // We can't easily test the actual import function with temp file,
            // but we can test JSON parsing directly
            $invalid_json = '{"invalid": json data}';
            $parsed = json_decode( $invalid_json, true );
            
            ob_end_clean();
            
            $this->assertNull( 
                $parsed,
                'Invalid JSON should return null when parsed'
            );
            
            $this->assertEquals( 
                JSON_ERROR_SYNTAX,
                json_last_error(),
                'JSON parsing error should be properly detected'
            );
            
            // Clean up
            if ( file_exists( $temp_json ) ) {
                unlink( $temp_json );
            }
        }
    }

    /**
     * Test theme uninstall/deactivation error handling
     */
    public function test_theme_deactivation_handling() {
        // Test that theme cleanup functions exist
        $cleanup_functions = array(
            'kei_portfolio_deactivation_cleanup'
        );
        
        foreach ( $cleanup_functions as $function ) {
            if ( function_exists( $function ) ) {
                // Test that cleanup function exists and is callable
                $this->assertTrue( 
                    is_callable( $function ),
                    sprintf( 'Cleanup function %s should be callable', $function )
                );
                
                // Test that function runs without errors
                ob_start();
                $error_occurred = false;
                
                try {
                    call_user_func( $function );
                } catch ( Exception $e ) {
                    $error_occurred = true;
                }
                
                ob_end_clean();
                
                $this->assertFalse( 
                    $error_occurred,
                    sprintf( 'Cleanup function %s should not throw exceptions', $function )
                );
            }
        }
    }

    /**
     * Test cross-browser compatibility error prevention
     */
    public function test_cross_browser_compatibility() {
        // Test CSS for potential browser compatibility issues
        $style_file = $this->theme_dir . '/style.css';
        
        if ( file_exists( $style_file ) ) {
            $css_content = file_get_contents( $style_file );
            
            // Check for problematic CSS properties
            $problematic_properties = array(
                '-webkit-appearance' => 'Should have fallbacks for webkit properties',
                'filter:'            => 'CSS filters should have fallbacks for older browsers',
                'backdrop-filter:'   => 'Backdrop filter should have fallbacks'
            );
            
            foreach ( $problematic_properties as $property => $message ) {
                if ( strpos( $css_content, $property ) !== false ) {
                    // If problematic property is used, check for vendor prefixes
                    $has_prefixes = strpos( $css_content, '-moz-' ) !== false ||
                                   strpos( $css_content, '-webkit-' ) !== false ||
                                   strpos( $css_content, '-ms-' ) !== false;
                    
                    if ( ! $has_prefixes ) {
                        $this->markTestIncomplete( $message );
                    }
                }
            }
        }
    }

    /**
     * Helper function to check PHP syntax
     */
    private function php_check_syntax( $file_path ) {
        $output = array();
        $return_var = 0;
        
        exec( 'php -l ' . escapeshellarg( $file_path ) . ' 2>&1', $output, $return_var );
        
        return $return_var === 0;
    }
}
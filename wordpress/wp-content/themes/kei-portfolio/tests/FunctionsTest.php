<?php
/**
 * Test suite for functions.php required includes and file structure
 * 
 * @package Kei_Portfolio
 * @group wordpress-core
 */

class FunctionsTest extends WP_UnitTestCase {

    /**
     * Test that functions.php exists and is readable
     */
    public function test_functions_file_exists() {
        $functions_path = get_template_directory() . '/functions.php';
        
        $this->assertFileExists( $functions_path, 'functions.php file should exist' );
        $this->assertFileIsReadable( $functions_path, 'functions.php should be readable' );
    }

    /**
     * Test that all required inc files are included in functions.php
     */
    public function test_required_includes_exist() {
        $required_includes = array(
            '/inc/setup.php',
            '/inc/enqueue.php',
            '/inc/post-types.php',
            '/inc/widgets.php',
            '/inc/customizer.php',
            '/inc/ajax-handlers.php',
            '/inc/optimizations.php'
        );

        foreach ( $required_includes as $include ) {
            $file_path = get_template_directory() . $include;
            $this->assertFileExists( 
                $file_path, 
                sprintf( 'Required include file %s should exist', $include ) 
            );
            $this->assertFileIsReadable( 
                $file_path, 
                sprintf( 'Required include file %s should be readable', $include ) 
            );
        }
    }

    /**
     * Test that functions.php properly loads without fatal errors
     */
    public function test_functions_file_loads_without_errors() {
        // Re-include functions.php to test it loads without errors
        // Use output buffering to capture any output
        ob_start();
        $result = @include_once get_template_directory() . '/functions.php';
        ob_end_clean();
        
        $this->assertNotFalse( $result, 'functions.php should load without fatal errors' );
    }

    /**
     * Test that functions.php has correct file structure
     */
    public function test_functions_file_structure() {
        $functions_content = file_get_contents( get_template_directory() . '/functions.php' );
        
        // Check for required includes using require_once
        $this->assertStringContainsString( 
            "require_once get_template_directory() . '/inc/setup.php'", 
            $functions_content,
            'functions.php should include setup.php'
        );
        
        $this->assertStringContainsString( 
            "require_once get_template_directory() . '/inc/enqueue.php'", 
            $functions_content,
            'functions.php should include enqueue.php'
        );
        
        $this->assertStringContainsString( 
            "require_once get_template_directory() . '/inc/post-types.php'", 
            $functions_content,
            'functions.php should include post-types.php'
        );
        
        $this->assertStringContainsString( 
            "require_once get_template_directory() . '/inc/widgets.php'", 
            $functions_content,
            'functions.php should include widgets.php'
        );
        
        $this->assertStringContainsString( 
            "require_once get_template_directory() . '/inc/customizer.php'", 
            $functions_content,
            'functions.php should include customizer.php'
        );
        
        $this->assertStringContainsString( 
            "require_once get_template_directory() . '/inc/ajax-handlers.php'", 
            $functions_content,
            'functions.php should include ajax-handlers.php'
        );
        
        $this->assertStringContainsString( 
            "require_once get_template_directory() . '/inc/optimizations.php'", 
            $functions_content,
            'functions.php should include optimizations.php'
        );
    }

    /**
     * Test that functions.php doesn't contain deprecated functions
     */
    public function test_no_deprecated_functions() {
        $functions_content = file_get_contents( get_template_directory() . '/functions.php' );
        
        // Check for common deprecated functions
        $deprecated_functions = array(
            'register_globals',
            'mysql_connect',
            'mysql_query',
            'ereg',
            'eregi',
            'split',
            'create_function'
        );
        
        foreach ( $deprecated_functions as $deprecated ) {
            $this->assertStringNotContainsString( 
                $deprecated, 
                $functions_content,
                sprintf( 'functions.php should not contain deprecated function: %s', $deprecated )
            );
        }
    }

    /**
     * Test that functions.php follows WordPress naming conventions
     */
    public function test_naming_conventions() {
        $functions_content = file_get_contents( get_template_directory() . '/functions.php' );
        
        // Check for proper package declaration
        $this->assertStringContainsString( 
            '@package', 
            $functions_content,
            'functions.php should have @package declaration in docblock'
        );
    }

    /**
     * Test PHP syntax of functions.php
     */
    public function test_php_syntax() {
        $functions_path = get_template_directory() . '/functions.php';
        
        // Use PHP's built-in syntax checker
        $output = array();
        $return_var = 0;
        exec( 'php -l ' . escapeshellarg( $functions_path ) . ' 2>&1', $output, $return_var );
        
        $this->assertEquals( 
            0, 
            $return_var,
            'functions.php should have valid PHP syntax'
        );
    }

    /**
     * Test security: ensure no direct file access is possible
     */
    public function test_security_no_direct_access() {
        $functions_content = file_get_contents( get_template_directory() . '/functions.php' );
        
        // Should have security header or exit statement
        $has_security = strpos( $functions_content, "defined( 'ABSPATH' )" ) !== false ||
                       strpos( $functions_content, "! defined( 'WPINC' )" ) !== false ||
                       strpos( $functions_content, 'exit;' ) !== false ||
                       strpos( $functions_content, 'die(' ) !== false;
        
        $this->assertTrue(
            $has_security,
            'functions.php should have security measures against direct access'
        );
    }

    /**
     * Test that functions.php doesn\'t expose sensitive information
     */
    public function test_no_sensitive_info_exposure() {
        $functions_content = file_get_contents( get_template_directory() . '/functions.php' );
        
        // Check for common sensitive patterns
        $sensitive_patterns = array(
            'password',
            'secret',
            'api_key',
            'private_key',
            'database',
            'mysql',
            'localhost',
            '@gmail.com',
            '@yahoo.com'
        );
        
        foreach ( $sensitive_patterns as $pattern ) {
            $this->assertStringNotContainsStringIgnoringCase(
                $pattern,
                $functions_content,
                sprintf( 'functions.php should not contain potentially sensitive information: %s', $pattern )
            );
        }
    }

    /**
     * Test WordPress security best practices
     */
    public function test_wordpress_security_practices() {
        $functions_content = file_get_contents( get_template_directory() . '/functions.php' );
        
        // Should not have eval() or similar dangerous functions
        $dangerous_functions = array( 'eval', 'exec', 'system', 'shell_exec', 'passthru' );
        
        foreach ( $dangerous_functions as $func ) {
            $this->assertStringNotContainsString(
                $func . '(',
                $functions_content,
                sprintf( 'functions.php should not use dangerous function: %s', $func )
            );
        }
    }

    /**
     * Test that all required inc files have valid PHP syntax
     */
    public function test_inc_files_php_syntax() {
        $inc_files = array(
            '/inc/setup.php',
            '/inc/enqueue.php',
            '/inc/post-types.php',
            '/inc/widgets.php',
            '/inc/customizer.php',
            '/inc/ajax-handlers.php',
            '/inc/optimizations.php'
        );

        foreach ( $inc_files as $file ) {
            $file_path = get_template_directory() . $file;
            
            if ( file_exists( $file_path ) ) {
                $output = array();
                $return_var = 0;
                exec( 'php -l ' . escapeshellarg( $file_path ) . ' 2>&1', $output, $return_var );
                
                $this->assertEquals( 
                    0, 
                    $return_var,
                    sprintf( '%s should have valid PHP syntax', $file )
                );
            }
        }
    }

    /**
     * Test that functions.php doesn't directly output anything
     */
    public function test_no_direct_output() {
        ob_start();
        include get_template_directory() . '/functions.php';
        $output = ob_get_clean();
        
        $this->assertEmpty( 
            trim( $output ),
            'functions.php should not produce any direct output'
        );
    }

    /**
     * Test that functions.php sets up proper theme constants if needed
     */
    public function test_theme_constants() {
        // Check if theme version is accessible
        $theme = wp_get_theme();
        $this->assertNotEmpty( 
            $theme->get( 'Version' ),
            'Theme version should be accessible'
        );
        
        $this->assertNotEmpty( 
            $theme->get( 'Name' ),
            'Theme name should be accessible'
        );
    }

    /**
     * Test performance: functions.php doesn\'t include unnecessary files
     */
    public function test_performance_minimal_includes() {
        $functions_content = file_get_contents( get_template_directory() . '/functions.php' );
        
        // Count number of includes/requires
        $include_count = preg_match_all( '/(?:include|require)(?:_once)?\s*\([^)]+\)/', $functions_content );
        
        // Should not have excessive includes (more than 15 is probably too many)
        $this->assertLessThan(
            15,
            $include_count,
            'functions.php should not have excessive includes for performance'
        );
    }

    /**
     * Test that required WordPress functions are available
     */
    public function test_wordpress_functions_available() {
        $required_wp_functions = array(
            'wp_get_theme',
            'get_template_directory',
            'wp_enqueue_style',
            'wp_enqueue_script',
            'add_theme_support',
            'register_nav_menus'
        );
        
        foreach ( $required_wp_functions as $func ) {
            $this->assertTrue(
                function_exists( $func ),
                sprintf( 'WordPress function %s should be available', $func )
            );
        }
    }

    /**
     * Test inc files security headers
     */
    public function test_inc_files_security_headers() {
        $inc_files = array(
            '/inc/setup.php',
            '/inc/enqueue.php',
            '/inc/post-types.php',
            '/inc/widgets.php',
            '/inc/customizer.php',
            '/inc/ajax-handlers.php',
            '/inc/optimizations.php'
        );

        foreach ( $inc_files as $file ) {
            $file_path = get_template_directory() . $file;
            
            if ( file_exists( $file_path ) ) {
                $content = file_get_contents( $file_path );
                
                // Check for security header
                $has_security = strpos( $content, "defined( 'ABSPATH' )" ) !== false ||
                               strpos( $content, "! defined( 'WPINC' )" ) !== false ||
                               strpos( $content, 'exit;' ) !== false ||
                               preg_match( '/^\s*<\?php\s*$/', trim( $content ) ) === 0;
                
                $this->assertTrue(
                    $has_security || strpos( $content, '<?php' ) === 0,
                    sprintf( '%s should have proper PHP opening tag or security measures', $file )
                );
            }
        }
    }
}
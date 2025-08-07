<?php
/**
 * WordPress Coding Standards Test Suite
 * 
 * Tests all PHP files for WordPress coding standards compliance.
 * This test suite is part of Group 1 (PHP Syntax Check) and is designed
 * for parallel execution with complete independence.
 * 
 * @package KeiPortfolio
 * @subpackage Tests
 * @group group1
 * @group php-standards
 * @group independent
 * @author Claude Code
 * @since 1.0.0
 */

use PHPUnit\Framework\TestCase;

class PhpStandardTest extends TestCase {
    /**
     * Theme directory path
     * 
     * @var string
     */
    private $theme_dir;

    /**
     * PHP_CodeSniffer path
     * 
     * @var string
     */
    private $phpcs_bin;

    /**
     * Set up test environment
     */
    protected function setUp(): void {
        parent::setUp();
        $this->theme_dir = dirname( __DIR__ );
        
        // Find phpcs binary
        $possible_paths = [
            dirname( __DIR__ ) . '/vendor/bin/phpcs',
            dirname( dirname( dirname( dirname( __DIR__ ) ) ) ) . '/vendor/bin/phpcs',
            'phpcs' // Global installation
        ];
        
        foreach ( $possible_paths as $path ) {
            if ( $this->isExecutable( $path ) ) {
                $this->phpcs_bin = $path;
                break;
            }
        }
    }

    /**
     * Test WordPress coding standards compliance
     * 
     * @dataProvider phpFilesProvider
     */
    public function testWordPressCodingStandards( $file ) {
        if ( empty( $this->phpcs_bin ) ) {
            $this->markTestSkipped( 'PHP_CodeSniffer not installed. Run: composer require --dev squizlabs/php_codesniffer wp-coding-standards/wpcs' );
        }

        $output = [];
        $return_var = 0;
        
        // Run PHPCS with WordPress standards
        $command = sprintf(
            '%s --standard=WordPress --report=json --ignore=*/vendor/*,*/node_modules/*,*/tests/* %s 2>&1',
            escapeshellcmd( $this->phpcs_bin ),
            escapeshellarg( $file )
        );
        
        exec( $command, $output, $return_var );
        
        $json_output = implode( '', $output );
        $result = json_decode( $json_output, true );
        
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            // If not JSON, check if it's an error message
            if ( $return_var !== 0 ) {
                // Check for common PHPCS installation issues
                $output_string = implode( "\n", $output );
                if ( strpos( $output_string, 'WordPress standard' ) !== false || 
                     strpos( $output_string, 'not installed' ) !== false ) {
                    $this->markTestSkipped( "PHPCS WordPress standards not properly configured: {$output_string}" );
                }
                $this->fail( "PHPCS failed for {$file}: " . $output_string );
            }
            return;
        }
        
        // Check for errors and warnings
        $errors = $result['files'][$file]['errors'] ?? 0;
        $warnings = $result['files'][$file]['warnings'] ?? 0;
        
        $this->assertEquals( 
            0, 
            $errors, 
            "WordPress coding standard errors in {$file}:\n" . $this->formatMessages( $result, $file, 'ERROR' )
        );
        
        // Log warnings but don't fail the test
        if ( $warnings > 0 && getenv( 'SHOW_PHPCS_WARNINGS' ) ) {
            echo "\nWarnings in {$file}:\n" . $this->formatMessages( $result, $file, 'WARNING' );
        }
    }

    /**
     * Test file naming conventions
     * 
     * @dataProvider phpFilesProvider
     */
    public function testFileNamingConventions( $file ) {
        $filename = basename( $file );
        
        // WordPress files should use hyphens, not underscores (except for template files)
        if ( ! in_array( $filename, [ 'functions.php', 'index.php', 'header.php', 'footer.php' ], true ) ) {
            // Check if it's not a template file (which can use underscores)
            if ( strpos( $filename, 'template_' ) !== 0 && strpos( $filename, '_template.php' ) === false ) {
                $this->assertDoesNotMatchRegularExpression(
                    '/[A-Z]/',
                    $filename,
                    "Filename should be lowercase: {$filename}"
                );
            }
        }
    }

    /**
     * Test WordPress file headers
     */
    public function testFileHeaders() {
        // Test main theme files have proper headers
        $main_files = [
            'functions.php',
            'index.php',
            'header.php',
            'footer.php',
            'front-page.php',
            '404.php'
        ];
        
        foreach ( $main_files as $file ) {
            $file_path = $this->theme_dir . '/' . $file;
            
            if ( ! file_exists( $file_path ) ) {
                continue;
            }
            
            $content = file_get_contents( $file_path );
            
            // Check for file-level documentation
            $this->assertMatchesRegularExpression(
                '/^<\?php\s+\/\*\*/',
                $content,
                "File {$file} should start with a documentation block"
            );
        }
    }

    /**
     * Test WordPress function naming conventions
     * 
     * @dataProvider phpFilesProvider
     */
    public function testFunctionNamingConventions( $file ) {
        $content = file_get_contents( $file );
        
        // Extract function names
        preg_match_all( '/function\s+([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s*\(/', $content, $matches );
        
        if ( empty( $matches[1] ) ) {
            return;
        }
        
        foreach ( $matches[1] as $function_name ) {
            // Skip magic methods
            if ( strpos( $function_name, '__' ) === 0 ) {
                continue;
            }
            
            // WordPress functions should use lowercase with underscores
            $this->assertMatchesRegularExpression(
                '/^[a-z][a-z0-9_]*$/',
                $function_name,
                "Function name '{$function_name}' doesn't follow WordPress naming convention in {$file}"
            );
            
            // Check for theme prefix (except for WordPress hooks)
            $wp_hooks = [ 'setup', 'init', 'admin_init', 'wp_enqueue_scripts', 'admin_enqueue_scripts' ];
            if ( ! in_array( $function_name, $wp_hooks, true ) ) {
                // Theme functions should be prefixed
                $this->assertStringStartsWith(
                    'kei_portfolio_',
                    $function_name,
                    "Function '{$function_name}' should be prefixed with 'kei_portfolio_' in {$file}"
                );
            }
        }
    }

    /**
     * Test WordPress hook usage
     * 
     * @dataProvider phpFilesProvider
     */
    public function testHookUsage( $file ) {
        $content = file_get_contents( $file );
        
        // Check add_action and add_filter usage
        preg_match_all( '/add_(action|filter)\s*\(\s*[\'"]([^\'"]+)[\'"]/', $content, $matches );
        
        if ( empty( $matches[2] ) ) {
            return;
        }
        
        foreach ( $matches[2] as $hook_name ) {
            // Custom hooks should be prefixed
            $wp_core_hooks = $this->getWpCoreHooks();
            
            if ( ! in_array( $hook_name, $wp_core_hooks, true ) ) {
                $this->assertStringStartsWith(
                    'kei_portfolio_',
                    $hook_name,
                    "Custom hook '{$hook_name}' should be prefixed with 'kei_portfolio_' in {$file}"
                );
            }
        }
    }

    /**
     * Test WordPress database queries
     * 
     * @dataProvider phpFilesProvider
     */
    public function testDatabaseQueries( $file ) {
        $content = file_get_contents( $file );
        
        // Check for direct database queries
        if ( strpos( $content, '$wpdb' ) !== false ) {
            // Ensure prepare() is used with queries
            if ( preg_match( '/\$wpdb->(get_results|get_row|get_var|query)\s*\([^)]*\$/', $content ) ) {
                $this->assertMatchesRegularExpression(
                    '/\$wpdb->prepare/',
                    $content,
                    "Direct database queries should use \$wpdb->prepare() in {$file}"
                );
            }
        }
        
        // Check for SQL injection vulnerabilities
        $this->assertDoesNotMatchRegularExpression(
            '/\$wpdb->(get_results|get_row|get_var|query)\s*\(\s*["\'].*\$_(GET|POST|REQUEST|COOKIE)/',
            $content,
            "Potential SQL injection vulnerability in {$file}"
        );
    }

    /**
     * Test WordPress nonce usage
     * 
     * @dataProvider phpFilesProvider
     */
    public function testNonceUsage( $file ) {
        $content = file_get_contents( $file );
        
        // Check forms have nonces
        if ( strpos( $content, '<form' ) !== false ) {
            // Check for wp_nonce_field
            if ( strpos( $content, 'method="post"' ) !== false || strpos( $content, "method='post'" ) !== false ) {
                $this->assertStringContainsString(
                    'wp_nonce_field',
                    $content,
                    "POST form should include wp_nonce_field() in {$file}"
                );
            }
        }
        
        // Check AJAX handlers verify nonces
        if ( strpos( $content, 'wp_ajax_' ) !== false ) {
            $this->assertMatchesRegularExpression(
                '/(check_ajax_referer|wp_verify_nonce)/',
                $content,
                "AJAX handler should verify nonce in {$file}"
            );
        }
    }

    /**
     * Test WordPress escaping functions
     * 
     * @dataProvider phpFilesProvider
     */
    public function testOutputEscaping( $file ) {
        $content = file_get_contents( $file );
        
        // Check for unescaped output
        if ( preg_match( '/echo\s+\$[^;]+;/', $content, $matches ) ) {
            // Check if it's escaped
            $escape_functions = [
                'esc_html',
                'esc_attr',
                'esc_url',
                'esc_js',
                'wp_kses',
                'wp_kses_post',
                'esc_textarea',
                'esc_html__',
                'esc_attr__',
                'esc_html_e',
                'esc_attr_e'
            ];
            
            $has_escaping = false;
            foreach ( $escape_functions as $func ) {
                if ( strpos( $matches[0], $func ) !== false ) {
                    $has_escaping = true;
                    break;
                }
            }
            
            if ( ! $has_escaping ) {
                // Check if it's a safe WordPress function
                $safe_functions = [ 'get_header', 'get_footer', 'get_sidebar', 'get_template_part' ];
                $is_safe = false;
                
                foreach ( $safe_functions as $func ) {
                    if ( strpos( $matches[0], $func ) !== false ) {
                        $is_safe = true;
                        break;
                    }
                }
                
                if ( ! $is_safe ) {
                    $this->assertTrue(
                        $has_escaping,
                        "Potentially unescaped output in {$file}: {$matches[0]}"
                    );
                }
            }
        }
    }

    /**
     * Test WordPress text domain usage
     * 
     * @dataProvider phpFilesProvider
     */
    public function testTextDomain( $file ) {
        $content = file_get_contents( $file );
        
        // Check translation functions use correct text domain
        $translation_functions = [
            '__',
            '_e',
            '_x',
            '_ex',
            '_n',
            '_nx',
            'esc_html__',
            'esc_html_e',
            'esc_attr__',
            'esc_attr_e'
        ];
        
        foreach ( $translation_functions as $func ) {
            if ( preg_match_all( "/{$func}\s*\([^,]+,\s*['\"]([^'\"]+)['\"]/", $content, $matches ) ) {
                foreach ( $matches[1] as $text_domain ) {
                    $this->assertEquals(
                        'kei-portfolio',
                        $text_domain,
                        "Translation function should use 'kei-portfolio' text domain, found '{$text_domain}' in {$file}"
                    );
                }
            }
        }
    }

    /**
     * Test WordPress conditional checks
     * 
     * @dataProvider phpFilesProvider
     */
    public function testWordPressConditionalChecks( $file ) {
        $content = file_get_contents( $file );
        
        // Check if file uses WordPress functions but doesn't check if WordPress is loaded
        if ( strpos( basename( $file ), 'functions.php' ) !== false || 
             strpos( $file, '/inc/' ) !== false ) {
            
            // Check for direct WordPress function calls without proper checks
            $wp_functions = [ 'add_action', 'add_filter', 'wp_enqueue_script', 'wp_enqueue_style' ];
            $has_wp_functions = false;
            
            foreach ( $wp_functions as $func ) {
                if ( strpos( $content, $func . '(' ) !== false ) {
                    $has_wp_functions = true;
                    break;
                }
            }
            
            if ( $has_wp_functions ) {
                // Should have WordPress availability check or be loaded within WordPress context
                $has_wp_check = strpos( $content, 'defined( \'ABSPATH\' )' ) !== false ||
                               strpos( $content, 'function_exists(' ) !== false ||
                               strpos( $content, 'class_exists(' ) !== false;
                
                $this->assertTrue(
                    $has_wp_check,
                    "File {$file} uses WordPress functions but doesn't check if WordPress is available"
                );
            }
        }
    }

    /**
     * Test code indentation and formatting
     * 
     * @dataProvider phpFilesProvider
     */
    public function testCodeFormatting( $file ) {
        $content = file_get_contents( $file );
        $lines = explode( "\n", $content );
        
        foreach ( $lines as $line_num => $line ) {
            // Check for mixed tabs and spaces (WordPress uses tabs)
            if ( preg_match( '/^\t+ /', $line ) || preg_match( '/^ +\t/', $line ) ) {
                $this->fail(
                    "Mixed tabs and spaces found in {$file} on line " . ($line_num + 1) . 
                    ": WordPress coding standards require consistent indentation"
                );
            }
            
            // Check for trailing whitespace
            if ( preg_match( '/\s+$/', $line ) && trim( $line ) !== '' ) {
                $this->fail(
                    "Trailing whitespace found in {$file} on line " . ($line_num + 1)
                );
            }
        }
    }

    /**
     * Test WordPress global variable usage
     * 
     * @dataProvider phpFilesProvider
     */
    public function testGlobalVariableUsage( $file ) {
        $content = file_get_contents( $file );
        
        // Check for proper global declarations
        if ( strpos( $content, '$wpdb' ) !== false ) {
            $this->assertMatchesRegularExpression(
                '/global\s+\$wpdb;/',
                $content,
                "File {$file} uses \$wpdb but doesn't declare it as global"
            );
        }
        
        if ( strpos( $content, '$wp_query' ) !== false ) {
            $this->assertMatchesRegularExpression(
                '/global\s+\$wp_query;/',
                $content,
                "File {$file} uses \$wp_query but doesn't declare it as global"
            );
        }
        
        if ( strpos( $content, '$post' ) !== false && strpos( $content, 'global $post' ) === false ) {
            // Check if $post is used in a context where it should be global
            if ( preg_match( '/\$post->(ID|post_title|post_content|post_excerpt)/', $content ) ) {
                $this->assertMatchesRegularExpression(
                    '/global\s+\$post;/',
                    $content,
                    "File {$file} uses \$post object properties but doesn't declare it as global"
                );
            }
        }
    }

    /**
     * Test WordPress capability checks
     * 
     * @dataProvider phpFilesProvider
     */
    public function testCapabilityChecks( $file ) {
        $content = file_get_contents( $file );
        
        // Check if admin functions have capability checks
        $admin_functions = [
            'add_options_page',
            'add_menu_page',
            'add_submenu_page',
            'wp_ajax_'
        ];
        
        foreach ( $admin_functions as $func ) {
            if ( strpos( $content, $func ) !== false ) {
                $this->assertMatchesRegularExpression(
                    '/(current_user_can|user_can|is_admin)\s*\(/',
                    $content,
                    "File {$file} uses admin functions but doesn't check user capabilities"
                );
                break;
            }
        }
    }

    /**
     * Format PHPCS messages for output
     * 
     * @param array  $result   PHPCS result
     * @param string $file     File path
     * @param string $type     Message type (ERROR or WARNING)
     * @return string
     */
    private function formatMessages( $result, $file, $type ) {
        if ( ! isset( $result['files'][$file]['messages'] ) ) {
            return '';
        }
        
        $output = '';
        foreach ( $result['files'][$file]['messages'] as $message ) {
            if ( $message['type'] === $type ) {
                $output .= sprintf(
                    "  Line %d: %s\n",
                    $message['line'],
                    $message['message']
                );
            }
        }
        
        return $output;
    }

    /**
     * Check if command is executable
     * 
     * @param string $command Command to check
     * @return bool
     */
    private function isExecutable( $command ) {
        if ( file_exists( $command ) && is_executable( $command ) ) {
            return true;
        }
        
        // Check if it's a global command
        $output = [];
        $return_var = 0;
        exec( "which {$command} 2>/dev/null", $output, $return_var );
        
        return $return_var === 0;
    }

    /**
     * Get list of WordPress core hooks
     * 
     * @return array
     */
    private function getWpCoreHooks() {
        // This is a subset of common WordPress hooks
        return [
            'init',
            'admin_init',
            'wp_enqueue_scripts',
            'admin_enqueue_scripts',
            'wp_head',
            'wp_footer',
            'after_setup_theme',
            'widgets_init',
            'customize_register',
            'pre_get_posts',
            'template_redirect',
            'wp_ajax_nopriv_',
            'wp_ajax_',
            'save_post',
            'the_content',
            'the_title',
            'wp_loaded'
        ];
    }

    /**
     * Provide all PHP files in the theme
     * 
     * @return array
     */
    public function phpFilesProvider() {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                dirname( __DIR__ ),
                RecursiveDirectoryIterator::SKIP_DOTS
            )
        );
        
        foreach ( $iterator as $file ) {
            // Skip vendor, node_modules, and tests directories
            if ( strpos( $file->getPathname(), '/vendor/' ) !== false ||
                 strpos( $file->getPathname(), '/node_modules/' ) !== false ||
                 strpos( $file->getPathname(), '/tests/' ) !== false ) {
                continue;
            }
            
            if ( $file->isFile() && $file->getExtension() === 'php' ) {
                $files[] = [ $file->getPathname() ];
            }
        }
        
        return $files;
    }
}
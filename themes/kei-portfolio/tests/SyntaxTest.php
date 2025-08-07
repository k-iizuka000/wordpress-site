<?php
/**
 * PHP Syntax Check Test Suite
 * 
 * Tests all PHP files in the theme for syntax errors.
 * This test suite is part of Group 1 (PHP Syntax Check) and is designed
 * for parallel execution with complete independence.
 * 
 * @package KeiPortfolio
 * @subpackage Tests
 * @group group1
 * @group php-syntax
 * @group independent
 * @author Claude Code
 * @since 1.0.0
 */

use PHPUnit\Framework\TestCase;

class SyntaxTest extends TestCase {
    /**
     * Theme directory path
     * 
     * @var string
     */
    private $theme_dir;

    /**
     * Set up test environment
     */
    protected function setUp(): void {
        parent::setUp();
        $this->theme_dir = dirname( __DIR__ );
    }

    /**
     * Test all PHP files for syntax errors
     * 
     * @dataProvider phpFilesProvider
     */
    public function testPhpSyntax( $file ) {
        $output = [];
        $return_var = 0;
        
        // Execute PHP lint check
        exec( "php -l {$file} 2>&1", $output, $return_var );
        
        // Assert no syntax errors
        $this->assertEquals( 
            0, 
            $return_var, 
            "Syntax error in file: {$file}\n" . implode( "\n", $output ) 
        );
        
        // Verify output contains "No syntax errors detected"
        $output_string = implode( "\n", $output );
        $this->assertStringContainsString( 
            'No syntax errors detected', 
            $output_string,
            "Unexpected output for file: {$file}" 
        );
    }

    /**
     * Test critical theme files exist and are readable
     * Based on test design document requirements for all theme files
     */
    public function testCriticalFilesExist() {
        $critical_files = [
            // Core theme files
            'functions.php',
            'index.php',
            'style.css',
            'header.php',
            'footer.php',
            'front-page.php',
            '404.php',
            'archive-project.php',
            'single-project.php',
            
            // Page templates
            'page-templates/page-about.php',
            'page-templates/page-portfolio.php',
            'page-templates/page-skills.php',
            'page-templates/page-contact.php',
            
            // Inc files
            'inc/setup.php',
            'inc/enqueue.php',
            'inc/customizer.php',
            'inc/widgets.php',
            'inc/post-types.php',
            'inc/ajax-handlers.php',
            'inc/optimizations.php'
        ];

        foreach ( $critical_files as $file ) {
            $file_path = $this->theme_dir . '/' . $file;
            
            $this->assertFileExists( 
                $file_path, 
                "Critical file missing: {$file}" 
            );
            
            $this->assertFileIsReadable( 
                $file_path, 
                "Critical file not readable: {$file}" 
            );
            
            // Additional check for minimum file size (not empty)
            $this->assertGreaterThan(
                0,
                filesize( $file_path ),
                "Critical file is empty: {$file}"
            );
        }
    }

    /**
     * Test that PHP files don't contain common syntax issues
     * 
     * @dataProvider phpFilesProvider
     */
    public function testCommonSyntaxIssues( $file ) {
        $content = file_get_contents( $file );
        
        // Check for PHP short tags (not recommended in WordPress)
        $this->assertDoesNotMatchRegularExpression( 
            '/<\?(?!php|=)/', 
            $content, 
            "Short PHP tag found in: {$file}" 
        );
        
        // Check for proper file ending (no closing PHP tag in pure PHP files)
        if ( $this->isPurePHPFile( $content ) ) {
            $trimmed_content = rtrim( $content );
            $this->assertDoesNotMatchRegularExpression( 
                '/\?>$/', 
                $trimmed_content, 
                "Closing PHP tag found in pure PHP file: {$file}" 
            );
        }
        
        // Check for BOM (Byte Order Mark)
        $this->assertStringNotStartsWith( 
            "\xEF\xBB\xBF", 
            $content, 
            "BOM detected in file: {$file}" 
        );
    }

    /**
     * Test PHP version compatibility
     * 
     * @dataProvider phpFilesProvider
     */
    public function testPhpVersionCompatibility( $file ) {
        $content = file_get_contents( $file );
        
        // Check for PHP 7.4+ features (WordPress minimum is PHP 7.4)
        // This is a basic check; for comprehensive compatibility testing, use PHPCompatibility
        
        // Check for typed properties (PHP 7.4+)
        if ( preg_match( '/(?:public|private|protected)\s+(?:int|string|bool|float|array|object)\s+\$/', $content ) ) {
            $this->assertTrue( 
                version_compare( PHP_VERSION, '7.4.0', '>=' ),
                "Typed properties used but PHP version < 7.4 in: {$file}" 
            );
        }
        
        // Check for arrow functions (PHP 7.4+)
        if ( strpos( $content, 'fn(' ) !== false || strpos( $content, 'fn (' ) !== false ) {
            $this->assertTrue( 
                version_compare( PHP_VERSION, '7.4.0', '>=' ),
                "Arrow functions used but PHP version < 7.4 in: {$file}" 
            );
        }
    }

    /**
     * Test for debug code left in files
     * 
     * @dataProvider phpFilesProvider
     */
    public function testNoDebugCode( $file ) {
        $content = file_get_contents( $file );
        
        // List of debug functions that shouldn't be in production
        $debug_functions = [
            'var_dump',
            'print_r',
            'debug_backtrace',
            'debug_print_backtrace',
            'var_export'
        ];
        
        foreach ( $debug_functions as $func ) {
            $this->assertStringNotContainsString( 
                $func . '(',
                $content,
                "Debug function '{$func}' found in: {$file}" 
            );
        }
        
        // Check for error display settings
        $this->assertDoesNotMatchRegularExpression( 
            '/error_reporting\s*\(\s*E_ALL/', 
            $content,
            "Development error_reporting found in: {$file}" 
        );
        
        $this->assertStringNotContainsString( 
            'ini_set(\'display_errors\'',
            $content,
            "Display errors setting found in: {$file}" 
        );
    }

    /**
     * Test for WordPress deprecated functions
     * 
     * @dataProvider phpFilesProvider
     */
    public function testNoDeprecatedFunctions( $file ) {
        $content = file_get_contents( $file );
        
        // Common deprecated WordPress functions
        $deprecated_functions = [
            'get_theme_data',           // Use wp_get_theme()
            'get_themes',                // Use wp_get_themes()
            'get_current_theme',         // Use wp_get_theme()
            'clean_pre',                 // Use wpautop()
            'add_custom_background',     // Use add_theme_support('custom-background')
            'add_custom_image_header',   // Use add_theme_support('custom-header')
            'wp_convert_bytes_to_hr',    // Use size_format()
            'mysql_escape_string',       // Use $wpdb->prepare()
            'wp_specialchars',           // Use esc_html()
            'attribute_escape',          // Use esc_attr()
            'clean_url',                 // Use esc_url()
        ];
        
        foreach ( $deprecated_functions as $func ) {
            $this->assertStringNotContainsString( 
                $func . '(',
                $content,
                "Deprecated function '{$func}' found in: {$file}" 
            );
        }
    }

    /**
     * Test template parts files exist
     */
    public function testTemplatePartsExist() {
        $template_parts = [
            // About section template parts
            'template-parts/about/hero.php',
            'template-parts/about/engineer-history.php',
            'template-parts/about/personality-section.php',
            'template-parts/about/cycling-passion.php',
            
            // Portfolio section template parts
            'template-parts/portfolio/portfolio-hero.php',
            'template-parts/portfolio/featured-projects.php',
            'template-parts/portfolio/all-projects.php',
            'template-parts/portfolio/technical-approach.php',
            
            // Skills section template parts
            'template-parts/skills/skills-hero.php',
            'template-parts/skills/programming-languages.php',
            'template-parts/skills/frameworks-tools.php',
            'template-parts/skills/specialized-skills.php',
            'template-parts/skills/learning-approach.php',
            
            // Contact section template parts
            'template-parts/contact/hero.php',
            'template-parts/contact/contact-form.php',
            'template-parts/contact/contact-info.php'
        ];

        foreach ( $template_parts as $template_part ) {
            $file_path = $this->theme_dir . '/' . $template_part;
            
            $this->assertFileExists(
                $file_path,
                "Template part missing: {$template_part}"
            );
            
            $this->assertFileIsReadable(
                $file_path,
                "Template part not readable: {$template_part}"
            );
        }
    }

    /**
     * Test PHP file encoding (UTF-8 without BOM)
     * 
     * @dataProvider phpFilesProvider
     */
    public function testFileEncoding( $file ) {
        $content = file_get_contents( $file );
        
        // Check for BOM (Byte Order Mark)
        $this->assertStringNotStartsWith(
            "\xEF\xBB\xBF",
            $content,
            "BOM detected in file: {$file} (should be UTF-8 without BOM)"
        );
        
        // Check if content is valid UTF-8
        $this->assertTrue(
            mb_check_encoding( $content, 'UTF-8' ),
            "File is not valid UTF-8: {$file}"
        );
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
                $this->getThemeDirectory(),
                RecursiveDirectoryIterator::SKIP_DOTS 
            )
        );
        
        foreach ( $iterator as $file ) {
            // Skip vendor and node_modules directories
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

    /**
     * Get theme directory
     * 
     * @return string
     */
    private function getThemeDirectory() {
        return dirname( __DIR__ );
    }

    /**
     * Check if file is pure PHP (no HTML)
     * 
     * @param string $content File content
     * @return bool
     */
    private function isPurePHPFile( $content ) {
        // Remove PHP tags and comments
        $tokens = token_get_all( $content );
        $has_html = false;
        
        foreach ( $tokens as $token ) {
            if ( is_array( $token ) ) {
                if ( $token[0] === T_INLINE_HTML && trim( $token[1] ) !== '' ) {
                    $has_html = true;
                    break;
                }
            }
        }
        
        return ! $has_html;
    }
}
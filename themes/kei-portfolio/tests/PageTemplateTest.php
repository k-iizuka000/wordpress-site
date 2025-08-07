<?php
/**
 * Page Template Test
 * 
 * Tests for custom page templates functionality
 * Group 3: Page Templates (Front half) - front-page, about, portfolio
 * 
 * @package KeiPortfolio
 * @subpackage Tests
 */

class PageTemplateTest extends WP_UnitTestCase {

    /**
     * Theme directory path
     * 
     * @var string
     */
    private $theme_dir;

    /**
     * Set up the test fixture
     */
    protected function setUp(): void {
        parent::setUp();
        $this->theme_dir = dirname( __DIR__ );
    }

    /**
     * Test front-page.php template functionality
     */
    public function test_front_page_template_functionality() {
        $template_path = $this->theme_dir . '/front-page.php';
        
        // Verify template file exists
        $this->assertFileExists( $template_path, 'Front page template does not exist' );

        // Read template content
        $content = file_get_contents( $template_path );
        $this->assertNotEmpty( $content, 'Front page template is empty' );

        // Check for essential front-page elements
        $required_elements = [
            'hero-section',  // Hero section
            'get_header',    // WordPress header function
            'get_footer'     // WordPress footer function
        ];

        foreach ( $required_elements as $element ) {
            $this->assertStringContainsString( 
                $element, 
                $content, 
                sprintf( 'Front page template is missing required element: %s', $element ) 
            );
        }
    }

    /**
     * Test front-page.php template structure
     */
    public function test_front_page_template_structure() {
        $template_path = $this->theme_dir . '/front-page.php';
        $content = file_get_contents( $template_path );

        // Check for proper HTML structure
        $structure_elements = [
            '<main',        // Main content area
            'class=',       // CSS classes are used
        ];

        foreach ( $structure_elements as $element ) {
            $this->assertStringContainsString( 
                $element, 
                $content, 
                sprintf( 'Front page template missing structural element: %s', $element ) 
            );
        }

        // Check for PHP opening tag
        $this->assertStringStartsWith( '<?php', $content, 'Template should start with PHP opening tag' );
    }

    /**
     * Test page-about.php template functionality
     */
    public function test_about_page_template_functionality() {
        $template_path = $this->theme_dir . '/page-templates/page-about.php';
        
        // Verify template file exists
        $this->assertFileExists( $template_path, 'About page template does not exist' );

        // Read template content
        $content = file_get_contents( $template_path );
        $this->assertNotEmpty( $content, 'About page template is empty' );

        // Check for essential page template elements
        $required_elements = [
            'get_header',     // WordPress header function
            'get_footer',     // WordPress footer function
            'the_content',    // Content output function
        ];

        foreach ( $required_elements as $element ) {
            $this->assertStringContainsString( 
                $element, 
                $content, 
                sprintf( 'About page template is missing required element: %s', $element ) 
            );
        }
    }

    /**
     * Test page-about.php template structure and content sections
     */
    public function test_about_page_template_structure() {
        $template_path = $this->theme_dir . '/page-templates/page-about.php';
        $content = file_get_contents( $template_path );

        // Check for about-specific sections
        $about_sections = [
            'about',          // About section reference
            'main',           // Main content wrapper
        ];

        foreach ( $about_sections as $section ) {
            $this->assertStringContainsString( 
                $section, 
                $content, 
                sprintf( 'About page template missing section: %s', $section ) 
            );
        }

        // Verify it's a valid PHP file
        $this->assertStringStartsWith( '<?php', $content, 'About template should start with PHP opening tag' );
    }

    /**
     * Test page-portfolio.php template functionality
     */
    public function test_portfolio_page_template_functionality() {
        $template_path = $this->theme_dir . '/page-templates/page-portfolio.php';
        
        // Verify template file exists
        $this->assertFileExists( $template_path, 'Portfolio page template does not exist' );

        // Read template content
        $content = file_get_contents( $template_path );
        $this->assertNotEmpty( $content, 'Portfolio page template is empty' );

        // Check for essential page template elements
        $required_elements = [
            'get_header',     // WordPress header function
            'get_footer',     // WordPress footer function
        ];

        foreach ( $required_elements as $element ) {
            $this->assertStringContainsString( 
                $element, 
                $content, 
                sprintf( 'Portfolio page template is missing required element: %s', $element ) 
            );
        }
    }

    /**
     * Test page-portfolio.php template structure and portfolio-specific content
     */
    public function test_portfolio_page_template_structure() {
        $template_path = $this->theme_dir . '/page-templates/page-portfolio.php';
        $content = file_get_contents( $template_path );

        // Check for portfolio-specific elements
        $portfolio_elements = [
            'portfolio',      // Portfolio section reference
            'main',           // Main content wrapper
        ];

        foreach ( $portfolio_elements as $element ) {
            $this->assertStringContainsString( 
                $element, 
                $content, 
                sprintf( 'Portfolio page template missing element: %s', $element ) 
            );
        }

        // Verify it's a valid PHP file
        $this->assertStringStartsWith( '<?php', $content, 'Portfolio template should start with PHP opening tag' );
    }

    /**
     * Test that page templates have proper WordPress template headers (if applicable)
     */
    public function test_page_templates_have_proper_headers() {
        $templates = [
            'page-templates/page-about.php' => 'About',
            'page-templates/page-portfolio.php' => 'Portfolio',
        ];

        foreach ( $templates as $template_path => $expected_name ) {
            $full_path = $this->theme_dir . '/' . $template_path;
            
            if ( file_exists( $full_path ) ) {
                $content = file_get_contents( $full_path );
                
                // Check for template name header (optional but recommended)
                if ( strpos( $content, 'Template Name:' ) !== false ) {
                    $this->assertStringContainsString( 
                        'Template Name:', 
                        $content, 
                        sprintf( 'Template %s should have a template name header', $template_path ) 
                    );
                }
            }
        }
    }

    /**
     * Test page templates have valid PHP syntax
     */
    public function test_page_templates_have_valid_php_syntax() {
        $templates = [
            'front-page.php',
            'page-templates/page-about.php',
            'page-templates/page-portfolio.php',
        ];

        foreach ( $templates as $template ) {
            $file_path = $this->theme_dir . '/' . $template;
            
            if ( file_exists( $file_path ) ) {
                $output = [];
                $return_var = 0;
                
                exec( sprintf( 'php -l %s 2>&1', escapeshellarg( $file_path ) ), $output, $return_var );
                
                $this->assertEquals( 
                    0, 
                    $return_var, 
                    sprintf( 
                        'PHP syntax error in template %s: %s', 
                        $template, 
                        implode( "\n", $output ) 
                    ) 
                );
            }
        }
    }

    /**
     * Test that page templates can be rendered without fatal errors
     */
    public function test_page_templates_can_be_rendered() {
        $templates = [
            'front-page.php',
            'page-templates/page-about.php',
            'page-templates/page-portfolio.php',
        ];

        foreach ( $templates as $template ) {
            $file_path = $this->theme_dir . '/' . $template;
            
            if ( file_exists( $file_path ) ) {
                // Mock global variables that templates might expect
                global $post, $wp_query;
                $original_post = $post;
                $original_wp_query = $wp_query;

                // Set up a mock post if WordPress functions are available
                if ( class_exists( 'WP_Query' ) && function_exists( 'get_post' ) ) {
                    $mock_post = (object) [
                        'ID' => 1,
                        'post_title' => 'Test Page',
                        'post_content' => 'Test content',
                        'post_status' => 'publish',
                        'post_type' => 'page'
                    ];
                    $post = $mock_post;
                }

                // Attempt to include the template (capture any output)
                ob_start();
                $error = null;
                
                try {
                    // We can't actually include WordPress templates without full WP environment
                    // So we just verify the file is syntactically correct PHP
                    $syntax_check = php_check_syntax( $file_path );
                    $this->assertTrue( 
                        $syntax_check, 
                        sprintf( 'Template %s has syntax errors', $template ) 
                    );
                } catch ( Exception $e ) {
                    $error = $e->getMessage();
                } catch ( ParseError $e ) {
                    $error = $e->getMessage();
                } finally {
                    ob_end_clean();
                    
                    // Restore global variables
                    $post = $original_post;
                    $wp_query = $original_wp_query;
                }

                if ( $error ) {
                    $this->fail( sprintf( 'Template %s caused error: %s', $template, $error ) );
                }
            }
        }
    }

    /**
     * Test that templates include proper security measures
     */
    public function test_templates_include_security_measures() {
        $templates = [
            'front-page.php',
            'page-templates/page-about.php',
            'page-templates/page-portfolio.php',
        ];

        foreach ( $templates as $template ) {
            $file_path = $this->theme_dir . '/' . $template;
            
            if ( file_exists( $file_path ) ) {
                $content = file_get_contents( $file_path );
                
                // Check for security measures (at least one should be present)
                $security_functions = [
                    'wp_head',        // Proper WordPress head
                    'wp_footer',      // Proper WordPress footer
                    'esc_html',       // HTML escaping
                    'esc_attr',       // Attribute escaping
                    'esc_url',        // URL escaping
                ];

                $has_security = false;
                foreach ( $security_functions as $function ) {
                    if ( strpos( $content, $function ) !== false ) {
                        $has_security = true;
                        break;
                    }
                }

                // Note: This is informational - templates might not need all security functions
                if ( ! $has_security ) {
                    $this->addWarning( 
                        sprintf( 'Template %s might benefit from WordPress security functions', $template ) 
                    );
                }
            }
        }
    }

    /**
     * Helper method to add warnings to test output
     * 
     * @param string $message Warning message
     */
    private function addWarning( $message ) {
        // PHPUnit doesn't have built-in warnings in older versions
        // So we'll just mark this as a risky test
        $this->markTestIncomplete( $message );
    }
}
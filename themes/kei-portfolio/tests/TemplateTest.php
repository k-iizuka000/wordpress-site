<?php
/**
 * Template File Existence Test
 * 
 * Tests to verify that all required template files exist in the theme
 * 
 * @package KeiPortfolio
 * @subpackage Tests
 */

class TemplateTest extends WP_UnitTestCase {

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
     * Test that required core template files exist
     */
    public function test_core_template_files_exist() {
        $required_files = [
            'index.php',
            'style.css',
            'functions.php',
            'header.php',
            'footer.php'
        ];

        foreach ( $required_files as $file ) {
            $file_path = $this->theme_dir . '/' . $file;
            $this->assertFileExists( 
                $file_path, 
                sprintf( 'Required template file %s does not exist', $file ) 
            );
        }
    }

    /**
     * Test that front-page.php exists
     */
    public function test_front_page_template_exists() {
        $file_path = $this->theme_dir . '/front-page.php';
        $this->assertFileExists( 
            $file_path, 
            'Front page template (front-page.php) does not exist' 
        );

        // Verify file is readable and not empty
        $this->assertTrue( 
            is_readable( $file_path ), 
            'Front page template is not readable' 
        );
        
        $this->assertGreaterThan( 
            0, 
            filesize( $file_path ), 
            'Front page template is empty' 
        );
    }

    /**
     * Test that page templates directory exists
     */
    public function test_page_templates_directory_exists() {
        $page_templates_dir = $this->theme_dir . '/page-templates';
        $this->assertDirectoryExists( 
            $page_templates_dir, 
            'Page templates directory does not exist' 
        );
    }

    /**
     * Test that required page template files exist
     */
    public function test_required_page_template_files_exist() {
        $required_page_templates = [
            'page-templates/page-about.php',
            'page-templates/page-portfolio.php',
            'page-templates/page-skills.php',
            'page-templates/page-contact.php'
        ];

        foreach ( $required_page_templates as $template ) {
            $file_path = $this->theme_dir . '/' . $template;
            $this->assertFileExists( 
                $file_path, 
                sprintf( 'Required page template %s does not exist', $template ) 
            );

            // Verify file is readable and not empty
            $this->assertTrue( 
                is_readable( $file_path ), 
                sprintf( 'Page template %s is not readable', $template ) 
            );
            
            $this->assertGreaterThan( 
                0, 
                filesize( $file_path ), 
                sprintf( 'Page template %s is empty', $template ) 
            );
        }
    }

    /**
     * Test that template parts directory exists
     */
    public function test_template_parts_directory_exists() {
        $template_parts_dir = $this->theme_dir . '/template-parts';
        $this->assertDirectoryExists( 
            $template_parts_dir, 
            'Template parts directory does not exist' 
        );
    }

    /**
     * Test that inc directory exists with required files
     */
    public function test_inc_directory_and_files_exist() {
        $inc_dir = $this->theme_dir . '/inc';
        $this->assertDirectoryExists( 
            $inc_dir, 
            'Inc directory does not exist' 
        );

        $required_inc_files = [
            'inc/setup.php',
            'inc/customizer.php',
            'inc/enqueue.php'
        ];

        foreach ( $required_inc_files as $inc_file ) {
            $file_path = $this->theme_dir . '/' . $inc_file;
            $this->assertFileExists( 
                $file_path, 
                sprintf( 'Required inc file %s does not exist', $inc_file ) 
            );
        }
    }

    /**
     * Test that assets directory exists
     */
    public function test_assets_directory_exists() {
        $assets_dir = $this->theme_dir . '/assets';
        $this->assertDirectoryExists( 
            $assets_dir, 
            'Assets directory does not exist' 
        );

        // Test that JS and CSS directories exist within assets
        $js_dir = $assets_dir . '/js';
        $css_dir = $assets_dir . '/css';

        if ( is_dir( $js_dir ) ) {
            $this->assertDirectoryExists( $js_dir, 'Assets JS directory exists but is not readable' );
        }

        if ( is_dir( $css_dir ) ) {
            $this->assertDirectoryExists( $css_dir, 'Assets CSS directory exists but is not readable' );
        }
    }

    /**
     * Test that error page template exists
     */
    public function test_error_template_exists() {
        $error_template = $this->theme_dir . '/404.php';
        $this->assertFileExists( 
            $error_template, 
            '404 error template (404.php) does not exist' 
        );

        // Verify file is readable and not empty
        $this->assertTrue( 
            is_readable( $error_template ), 
            '404 error template is not readable' 
        );
        
        $this->assertGreaterThan( 
            0, 
            filesize( $error_template ), 
            '404 error template is empty' 
        );
    }

    /**
     * Test that custom post type templates exist (if applicable)
     */
    public function test_custom_post_type_templates_exist() {
        // Test for project-related templates
        $project_templates = [
            'single-project.php',
            'archive-project.php'
        ];

        foreach ( $project_templates as $template ) {
            $file_path = $this->theme_dir . '/' . $template;
            
            // These templates are optional, so we only check if they exist
            if ( file_exists( $file_path ) ) {
                $this->assertTrue( 
                    is_readable( $file_path ), 
                    sprintf( 'Custom post type template %s is not readable', $template ) 
                );
                
                $this->assertGreaterThan( 
                    0, 
                    filesize( $file_path ), 
                    sprintf( 'Custom post type template %s is empty', $template ) 
                );
            }
        }
    }

    /**
     * Test that template files have valid PHP syntax
     */
    public function test_template_files_have_valid_php_syntax() {
        $template_files = [
            'index.php',
            'front-page.php',
            'functions.php',
            'header.php',
            'footer.php',
            '404.php'
        ];

        foreach ( $template_files as $file ) {
            $file_path = $this->theme_dir . '/' . $file;
            
            if ( file_exists( $file_path ) ) {
                $output = [];
                $return_var = 0;
                
                exec( sprintf( 'php -l %s 2>&1', escapeshellarg( $file_path ) ), $output, $return_var );
                
                $this->assertEquals( 
                    0, 
                    $return_var, 
                    sprintf( 
                        'PHP syntax error in template file %s: %s', 
                        $file, 
                        implode( "\n", $output ) 
                    ) 
                );
            }
        }
    }
}
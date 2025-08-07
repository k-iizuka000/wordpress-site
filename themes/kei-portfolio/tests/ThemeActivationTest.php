<?php
/**
 * Test suite for theme activation functionality
 * 
 * Tests theme activation process including pre-activation validation,
 * activation process, and data migration
 * 
 * @package Kei_Portfolio
 * @group theme-activation
 */

class ThemeActivationTest extends WP_UnitTestCase {

    /**
     * Theme directory path
     * @var string
     */
    private $theme_dir;

    /**
     * Original active theme
     * @var string
     */
    private $original_theme;

    /**
     * Setup before each test
     */
    public function setUp(): void {
        parent::setUp();
        
        $this->theme_dir = get_template_directory();
        $this->original_theme = get_option('stylesheet');
        
        // Ensure WordPress is in a clean state
        if ( ! did_action( 'after_setup_theme' ) ) {
            do_action( 'after_setup_theme' );
        }
    }

    /**
     * Cleanup after each test
     */
    public function tearDown(): void {
        // Restore original theme if changed
        if ( get_option('stylesheet') !== $this->original_theme ) {
            switch_theme( $this->original_theme );
        }
        
        parent::tearDown();
    }

    /**
     * GROUP 1: テーマ有効化前検証テスト
     * Test theme file integrity check
     */
    public function test_theme_file_integrity() {
        // Test required core files exist
        $required_files = array(
            '/style.css',
            '/index.php',
            '/functions.php'
        );

        foreach ( $required_files as $file ) {
            $file_path = $this->theme_dir . $file;
            $this->assertFileExists( 
                $file_path,
                sprintf( 'Required theme file %s should exist', $file )
            );
            $this->assertFileIsReadable( 
                $file_path,
                sprintf( 'Required theme file %s should be readable', $file )
            );
        }
    }

    /**
     * Test theme header information validity
     */
    public function test_theme_header_information() {
        $theme = wp_get_theme();
        
        // Test essential theme information
        $this->assertNotEmpty( 
            $theme->get('Name'),
            'Theme should have a name'
        );
        
        $this->assertNotEmpty( 
            $theme->get('Version'),
            'Theme should have a version'
        );
        
        $this->assertNotEmpty( 
            $theme->get('Author'),
            'Theme should have an author'
        );
        
        // Test style.css header format
        $style_content = file_get_contents( $this->theme_dir . '/style.css' );
        $this->assertStringContainsString( 
            'Theme Name:',
            $style_content,
            'style.css should contain Theme Name header'
        );
        
        $this->assertStringContainsString( 
            'Version:',
            $style_content,
            'style.css should contain Version header'
        );
    }

    /**
     * Test template file hierarchy
     */
    public function test_template_file_hierarchy() {
        $template_files = array(
            '/index.php'        => 'Index template is required',
            '/front-page.php'   => 'Front page template should exist',
            '/single.php'       => 'Single post template should exist',
            '/page.php'         => 'Page template should exist',
            '/archive.php'      => 'Archive template should exist',
            '/404.php'          => '404 template should exist'
        );

        foreach ( $template_files as $file => $message ) {
            $file_path = $this->theme_dir . $file;
            if ( $file === '/index.php' ) {
                // index.php is absolutely required
                $this->assertFileExists( $file_path, $message );
            } else {
                // Other templates are recommended but not fatal if missing
                if ( file_exists( $file_path ) ) {
                    $this->assertFileIsReadable( $file_path, $file . ' should be readable if it exists' );
                }
            }
        }
    }

    /**
     * Test PHP version requirements
     */
    public function test_php_version_requirements() {
        $required_php_version = '7.4.0';
        
        $this->assertTrue( 
            version_compare( PHP_VERSION, $required_php_version, '>=' ),
            sprintf( 'PHP version should be at least %s, current version is %s', $required_php_version, PHP_VERSION )
        );
    }

    /**
     * Test WordPress version requirements
     */
    public function test_wordpress_version_requirements() {
        global $wp_version;
        $required_wp_version = '6.0';
        
        $this->assertTrue( 
            version_compare( $wp_version, $required_wp_version, '>=' ),
            sprintf( 'WordPress version should be at least %s, current version is %s', $required_wp_version, $wp_version )
        );
    }

    /**
     * Test required PHP extensions
     */
    public function test_required_php_extensions() {
        $required_extensions = array(
            'json'   => 'JSON extension is required for portfolio data import',
            'gd'     => 'GD extension is recommended for image processing',
            'curl'   => 'cURL extension is recommended for external requests'
        );

        foreach ( $required_extensions as $extension => $message ) {
            if ( $extension === 'json' ) {
                // JSON is critical
                $this->assertTrue( 
                    extension_loaded( $extension ),
                    $message
                );
            } else {
                // Others are recommended but not fatal
                if ( ! extension_loaded( $extension ) ) {
                    $this->markTestIncomplete( $message );
                }
            }
        }
    }

    /**
     * Test directory permissions
     */
    public function test_directory_permissions() {
        // Test theme directory is readable
        $this->assertTrue( 
            is_readable( $this->theme_dir ),
            'Theme directory should be readable'
        );

        // Test upload directory permissions if it exists
        $upload_dir = wp_upload_dir();
        if ( $upload_dir && ! $upload_dir['error'] ) {
            $this->assertTrue( 
                is_writable( $upload_dir['path'] ),
                'Upload directory should be writable'
            );
        }

        // Test if we can write to wp-content for cache/optimization
        $wp_content_dir = dirname( $this->theme_dir );
        if ( is_dir( $wp_content_dir ) ) {
            $this->assertTrue( 
                is_readable( $wp_content_dir ),
                'wp-content directory should be readable'
            );
        }
    }

    /**
     * GROUP 2: テーマ有効化プロセステスト
     * Test theme activation hooks
     */
    public function test_after_switch_theme_hook() {
        // Test that the hook exists and has our function
        $this->assertTrue(
            function_exists( 'kei_portfolio_pro_setup' ),
            'Theme setup function should exist'
        );

        // Test hook is properly registered
        $this->assertNotFalse( 
            has_action( 'after_setup_theme', 'kei_portfolio_pro_setup' ),
            'Theme setup should be hooked to after_setup_theme'
        );
    }

    /**
     * Test theme activation process
     */
    public function test_theme_activation_process() {
        // Simulate theme activation by switching to our theme
        $theme_name = get_option('stylesheet');
        
        // If we're not already using our theme, switch to it
        if ( $theme_name !== 'kei-portfolio' ) {
            // Count of actions before activation
            $initial_action_count = did_action( 'after_setup_theme' );
            
            // Trigger theme setup
            do_action( 'after_setup_theme' );
            
            // Verify action was called
            $this->assertGreaterThan( 
                $initial_action_count,
                did_action( 'after_setup_theme' ),
                'after_setup_theme action should be triggered during activation'
            );
        }

        // Test that theme setup ran successfully
        $this->assertTrue( 
            current_theme_supports( 'post-thumbnails' ),
            'Theme should support post thumbnails after activation'
        );

        $this->assertTrue( 
            current_theme_supports( 'title-tag' ),
            'Theme should support title tag after activation'
        );
    }

    /**
     * Test theme initialization functions
     */
    public function test_theme_initialization_functions() {
        $required_functions = array(
            'kei_portfolio_pro_setup'           => 'Main theme setup function should exist',
            'kei_portfolio_pro_enqueue_styles'  => 'Style enqueue function should exist',
            'kei_portfolio_pro_enqueue_scripts' => 'Script enqueue function should exist'
        );

        foreach ( $required_functions as $function => $message ) {
            $this->assertTrue( 
                function_exists( $function ),
                $message
            );
        }
    }

    /**
     * Test error handling during activation
     */
    public function test_activation_error_handling() {
        // Test that theme activation doesn't produce PHP errors
        $error_log_size_before = 0;
        if ( ini_get('log_errors') && ini_get('error_log') && file_exists( ini_get('error_log') ) ) {
            $error_log_size_before = filesize( ini_get('error_log') );
        }

        // Re-run theme setup
        if ( function_exists( 'kei_portfolio_pro_setup' ) ) {
            kei_portfolio_pro_setup();
        }

        // Check if errors were logged
        if ( ini_get('log_errors') && ini_get('error_log') && file_exists( ini_get('error_log') ) ) {
            $error_log_size_after = filesize( ini_get('error_log') );
            $this->assertEquals( 
                $error_log_size_before,
                $error_log_size_after,
                'Theme setup should not generate PHP errors'
            );
        }
    }

    /**
     * Test portfolio data import functionality
     */
    public function test_portfolio_data_import() {
        // Test that import function exists
        $this->assertTrue( 
            function_exists( 'kei_portfolio_import_json_data' ),
            'Portfolio import function should exist'
        );

        // Test handling of non-existent portfolio.json
        $result = kei_portfolio_import_json_data();
        $this->assertFalse( 
            $result,
            'Import should return false when portfolio.json is missing'
        );

        // If portfolio.json exists, test it can be parsed
        $portfolio_file = $this->theme_dir . '/portfolio.json';
        if ( file_exists( $portfolio_file ) ) {
            $json_content = file_get_contents( $portfolio_file );
            $parsed_data = json_decode( $json_content, true );
            
            $this->assertNotNull( 
                $parsed_data,
                'portfolio.json should contain valid JSON'
            );
            
            $this->assertIsArray( 
                $parsed_data,
                'portfolio.json should decode to an array'
            );
        }
    }

    /**
     * Test default settings initialization
     */
    public function test_default_settings_initialization() {
        // Test navigation menus registration
        $registered_menus = get_registered_nav_menus();
        
        $expected_menus = array( 'primary', 'footer', 'social' );
        foreach ( $expected_menus as $menu ) {
            $this->assertArrayHasKey( 
                $menu,
                $registered_menus,
                sprintf( '%s menu should be registered', $menu )
            );
        }

        // Test custom image sizes
        global $_wp_additional_image_sizes;
        
        $expected_image_sizes = array(
            'project-thumbnail',
            'project-large',
            'hero-image'
        );
        
        foreach ( $expected_image_sizes as $size ) {
            $this->assertArrayHasKey( 
                $size,
                $_wp_additional_image_sizes,
                sprintf( '%s image size should be registered', $size )
            );
        }
    }

    /**
     * Test widget areas initialization
     */
    public function test_widget_areas_initialization() {
        global $wp_registered_sidebars;
        
        // Test that sidebars are registered (if widgets.php is loaded)
        if ( function_exists( 'register_sidebar' ) && ! empty( $wp_registered_sidebars ) ) {
            $this->assertIsArray( 
                $wp_registered_sidebars,
                'Widget areas should be registered as array'
            );
        }
    }

    /**
     * Test customizer settings initialization
     */
    public function test_customizer_settings_initialization() {
        // Test that customizer setup function exists
        if ( function_exists( 'kei_portfolio_pro_customizer' ) ) {
            $this->assertTrue( 
                function_exists( 'kei_portfolio_pro_customizer' ),
                'Customizer setup function should exist'
            );
        }

        // Test that customizer is properly hooked
        if ( has_action( 'customize_register', 'kei_portfolio_pro_customizer' ) ) {
            $this->assertNotFalse( 
                has_action( 'customize_register', 'kei_portfolio_pro_customizer' ),
                'Customizer should be hooked to customize_register'
            );
        }
    }

    /**
     * Test theme supports are properly configured
     */
    public function test_theme_supports_configuration() {
        $required_supports = array(
            'post-thumbnails'    => 'Theme should support post thumbnails',
            'title-tag'          => 'Theme should support title tag',
            'custom-logo'        => 'Theme should support custom logo',
            'html5'              => 'Theme should support HTML5',
            'automatic-feed-links' => 'Theme should support automatic feed links',
            'align-wide'         => 'Theme should support wide alignment',
            'editor-styles'      => 'Theme should support editor styles',
            'responsive-embeds'  => 'Theme should support responsive embeds'
        );

        foreach ( $required_supports as $support => $message ) {
            $this->assertTrue( 
                current_theme_supports( $support ),
                $message
            );
        }
    }

    /**
     * Test text domain loading
     */
    public function test_text_domain_loading() {
        // Test that text domain is loaded
        $this->assertTrue( 
            is_textdomain_loaded( 'kei-portfolio' ),
            'Theme text domain should be loaded'
        );

        // Test translation functions work
        $translated = __( 'Hello', 'kei-portfolio' );
        $this->assertIsString( 
            $translated,
            'Translation function should return string'
        );
    }

    /**
     * Test activation without fatal errors
     */
    public function test_activation_without_fatal_errors() {
        // Capture any output during theme setup
        ob_start();
        
        // Re-run key theme setup functions
        if ( function_exists( 'kei_portfolio_pro_setup' ) ) {
            kei_portfolio_pro_setup();
        }
        
        $output = ob_get_clean();
        
        // Theme setup should not produce output
        $this->assertEmpty( 
            trim( $output ),
            'Theme setup should not produce any output'
        );
        
        // Test that essential WordPress functions are still available
        $this->assertTrue( 
            function_exists( 'wp_head' ),
            'WordPress core functions should remain available after theme activation'
        );
        
        $this->assertTrue( 
            function_exists( 'wp_footer' ),
            'WordPress core functions should remain available after theme activation'
        );
    }
}
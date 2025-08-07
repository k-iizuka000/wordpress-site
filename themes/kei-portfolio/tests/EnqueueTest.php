<?php
/**
 * Test suite for asset enqueue functionality
 * 
 * @package Kei_Portfolio
 * @group wordpress-core
 */

class EnqueueTest extends WP_UnitTestCase {

    /**
     * Setup before each test
     */
    public function setUp(): void {
        parent::setUp();
        
        // Reset global variables
        global $wp_scripts, $wp_styles;
        $wp_scripts = new WP_Scripts();
        $wp_styles = new WP_Styles();
        
        // Set up theme
        if ( ! did_action( 'after_setup_theme' ) ) {
            do_action( 'after_setup_theme' );
        }
    }

    /**
     * Test that main enqueue function exists
     */
    public function test_enqueue_function_exists() {
        $this->assertTrue( 
            function_exists( 'kei_portfolio_pro_scripts' ),
            'kei_portfolio_pro_scripts function should exist'
        );
    }

    /**
     * Test that contact scripts function exists
     */
    public function test_contact_enqueue_function_exists() {
        $this->assertTrue( 
            function_exists( 'kei_portfolio_contact_scripts' ),
            'kei_portfolio_contact_scripts function should exist'
        );
    }

    /**
     * Test enqueue scripts hook is registered
     */
    public function test_enqueue_scripts_hook() {
        $this->assertNotFalse( 
            has_action( 'wp_enqueue_scripts', 'kei_portfolio_pro_scripts' ),
            'Main scripts should be hooked to wp_enqueue_scripts'
        );
        
        $this->assertNotFalse( 
            has_action( 'wp_enqueue_scripts', 'kei_portfolio_contact_scripts' ),
            'Contact scripts should be hooked to wp_enqueue_scripts'
        );
    }

    /**
     * Test main stylesheet is enqueued
     */
    public function test_main_stylesheet_enqueued() {
        // Run the enqueue function
        do_action( 'wp_enqueue_scripts' );
        
        global $wp_styles;
        $this->assertTrue( 
            $wp_styles->query( 'kei-portfolio-style' ),
            'Main theme stylesheet should be enqueued'
        );
        
        // Check the stylesheet URL
        $style = $wp_styles->registered['kei-portfolio-style'];
        $this->assertStringContainsString( 
            'style.css',
            $style->src,
            'Main stylesheet should point to style.css'
        );
    }

    /**
     * Test Google Fonts are enqueued
     */
    public function test_google_fonts_enqueued() {
        do_action( 'wp_enqueue_scripts' );
        
        global $wp_styles;
        $this->assertTrue( 
            $wp_styles->query( 'kei-portfolio-fonts' ),
            'Google Fonts should be enqueued'
        );
        
        $fonts = $wp_styles->registered['kei-portfolio-fonts'];
        $this->assertStringContainsString( 
            'fonts.googleapis.com',
            $fonts->src,
            'Google Fonts URL should be correct'
        );
        $this->assertStringContainsString( 
            'Pacifico',
            $fonts->src,
            'Pacifico font should be included'
        );
        $this->assertStringContainsString( 
            'Noto+Sans+JP',
            $fonts->src,
            'Noto Sans JP font should be included'
        );
    }

    /**
     * Test Remix Icon is enqueued
     */
    public function test_remix_icon_enqueued() {
        do_action( 'wp_enqueue_scripts' );
        
        global $wp_styles;
        $this->assertTrue( 
            $wp_styles->query( 'remixicon' ),
            'Remix Icon should be enqueued'
        );
        
        $remixicon = $wp_styles->registered['remixicon'];
        $this->assertStringContainsString( 
            'remixicon',
            $remixicon->src,
            'Remix Icon URL should be correct'
        );
        $this->assertEquals( 
            '3.5.0',
            $remixicon->ver,
            'Remix Icon version should be 3.5.0'
        );
    }

    /**
     * Test navigation JavaScript is enqueued
     */
    public function test_navigation_script_enqueued() {
        do_action( 'wp_enqueue_scripts' );
        
        global $wp_scripts;
        $this->assertTrue( 
            $wp_scripts->query( 'kei-portfolio-navigation' ),
            'Navigation script should be enqueued'
        );
        
        $nav_script = $wp_scripts->registered['kei-portfolio-navigation'];
        $this->assertStringContainsString( 
            '/assets/js/navigation.js',
            $nav_script->src,
            'Navigation script path should be correct'
        );
        $this->assertTrue( 
            $nav_script->args === 1 || $nav_script->args === true,
            'Navigation script should be loaded in footer'
        );
    }

    /**
     * Test main JavaScript is enqueued
     */
    public function test_main_script_enqueued() {
        do_action( 'wp_enqueue_scripts' );
        
        global $wp_scripts;
        $this->assertTrue( 
            $wp_scripts->query( 'kei-portfolio-script' ),
            'Main script should be enqueued'
        );
        
        $main_script = $wp_scripts->registered['kei-portfolio-script'];
        $this->assertStringContainsString( 
            '/assets/js/main.js',
            $main_script->src,
            'Main script path should be correct'
        );
        
        // Check dependencies
        $this->assertContains( 
            'kei-portfolio-navigation',
            $main_script->deps,
            'Main script should depend on navigation script'
        );
        
        $this->assertTrue( 
            $main_script->args === 1 || $main_script->args === true,
            'Main script should be loaded in footer'
        );
    }

    /**
     * Test script localization
     */
    public function test_script_localization() {
        do_action( 'wp_enqueue_scripts' );
        
        global $wp_scripts;
        $main_script = $wp_scripts->registered['kei-portfolio-script'];
        
        $this->assertNotEmpty( 
            $main_script->extra,
            'Main script should have localization data'
        );
        
        $this->assertArrayHasKey( 
            'data',
            $main_script->extra,
            'Localization data should be present'
        );
        
        // Check localized data contains expected values
        $localized_data = $main_script->extra['data'];
        $this->assertStringContainsString( 
            'keiPortfolio',
            $localized_data,
            'Localized object should be named keiPortfolio'
        );
        $this->assertStringContainsString( 
            'ajaxUrl',
            $localized_data,
            'AJAX URL should be localized'
        );
        $this->assertStringContainsString( 
            'nonce',
            $localized_data,
            'Nonce should be localized'
        );
    }

    /**
     * Test comment reply script conditional loading
     */
    public function test_comment_reply_script() {
        // Create a post and go to its single page
        $post_id = $this->factory->post->create();
        $this->go_to( get_permalink( $post_id ) );
        
        // Enable comments and threaded comments
        update_option( 'thread_comments', 1 );
        update_post_meta( $post_id, '_comments_open', 'open' );
        
        // Clear scripts and re-run enqueue
        global $wp_scripts;
        $wp_scripts = new WP_Scripts();
        
        do_action( 'wp_enqueue_scripts' );
        
        if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
            $this->assertTrue( 
                $wp_scripts->query( 'comment-reply' ),
                'Comment reply script should be enqueued on single posts with comments open'
            );
        }
    }

    /**
     * Test contact page specific scripts
     */
    public function test_contact_page_scripts() {
        // Create a contact page
        $page_id = $this->factory->post->create( array(
            'post_type' => 'page',
            'post_name' => 'contact',
            'post_status' => 'publish'
        ) );
        
        // Go to contact page
        $this->go_to( get_permalink( $page_id ) );
        
        // Clear scripts and re-run enqueue
        global $wp_scripts;
        $wp_scripts = new WP_Scripts();
        
        do_action( 'wp_enqueue_scripts' );
        
        // Check if main script has additional localization for contact page
        if ( is_page( 'contact' ) ) {
            $main_script = $wp_scripts->registered['kei-portfolio-script'];
            
            if ( isset( $main_script->extra['data'] ) ) {
                $localized_data = $main_script->extra['data'];
                $this->assertStringContainsString( 
                    'kei_portfolio_ajax',
                    $localized_data,
                    'Contact page should have AJAX localization'
                );
            }
        }
    }

    /**
     * Test script versions
     */
    public function test_script_versions() {
        do_action( 'wp_enqueue_scripts' );
        
        global $wp_scripts, $wp_styles;
        $theme_version = wp_get_theme()->get( 'Version' );
        
        // Check main stylesheet version
        $style = $wp_styles->registered['kei-portfolio-style'];
        $this->assertEquals( 
            $theme_version,
            $style->ver,
            'Main stylesheet should use theme version'
        );
        
        // Check navigation script version
        $nav_script = $wp_scripts->registered['kei-portfolio-navigation'];
        $this->assertEquals( 
            $theme_version,
            $nav_script->ver,
            'Navigation script should use theme version'
        );
        
        // Check main script version
        $main_script = $wp_scripts->registered['kei-portfolio-script'];
        $this->assertEquals( 
            $theme_version,
            $main_script->ver,
            'Main script should use theme version'
        );
    }

    /**
     * Test no duplicate enqueues
     */
    public function test_no_duplicate_enqueues() {
        // Run enqueue twice
        do_action( 'wp_enqueue_scripts' );
        do_action( 'wp_enqueue_scripts' );
        
        global $wp_scripts, $wp_styles;
        
        // Count occurrences - should only be registered once
        $style_count = 0;
        $script_count = 0;
        
        foreach ( $wp_styles->registered as $handle => $style ) {
            if ( $handle === 'kei-portfolio-style' ) {
                $style_count++;
            }
        }
        
        foreach ( $wp_scripts->registered as $handle => $script ) {
            if ( $handle === 'kei-portfolio-script' ) {
                $script_count++;
            }
        }
        
        $this->assertEquals( 1, $style_count, 'Main stylesheet should only be registered once' );
        $this->assertEquals( 1, $script_count, 'Main script should only be registered once' );
    }

    /**
     * Test enqueue function doesn't produce errors
     */
    public function test_enqueue_no_errors() {
        // Capture any errors or warnings
        $error_level = error_reporting( E_ALL );
        
        ob_start();
        $errors = array();
        set_error_handler( function( $errno, $errstr ) use ( &$errors ) {
            $errors[] = $errstr;
            return true;
        } );
        
        do_action( 'wp_enqueue_scripts' );
        
        restore_error_handler();
        ob_end_clean();
        error_reporting( $error_level );
        
        $this->assertEmpty( 
            $errors,
            'Enqueue functions should not produce any errors or warnings'
        );
    }

    /**
     * Test correct hook priorities
     */
    public function test_hook_priorities() {
        global $wp_filter;
        
        if ( isset( $wp_filter['wp_enqueue_scripts'] ) ) {
            $main_priority = false;
            $contact_priority = false;
            
            foreach ( $wp_filter['wp_enqueue_scripts'] as $priority => $hooks ) {
                foreach ( $hooks as $hook ) {
                    if ( isset( $hook['function'] ) ) {
                        if ( $hook['function'] === 'kei_portfolio_pro_scripts' ) {
                            $main_priority = $priority;
                        }
                        if ( $hook['function'] === 'kei_portfolio_contact_scripts' ) {
                            $contact_priority = $priority;
                        }
                    }
                }
            }
            
            $this->assertNotFalse( $main_priority, 'Main scripts should be hooked' );
            $this->assertNotFalse( $contact_priority, 'Contact scripts should be hooked' );
            $this->assertGreaterThan( 
                $main_priority,
                $contact_priority,
                'Contact scripts should run after main scripts (higher priority number)'
            );
        }
    }

    /**
     * Test that required JavaScript files exist
     */
    public function test_javascript_files_exist() {
        $js_files = array(
            '/assets/js/navigation.js',
            '/assets/js/main.js'
        );
        
        foreach ( $js_files as $file ) {
            $file_path = get_template_directory() . $file;
            $this->assertFileExists( 
                $file_path,
                sprintf( 'JavaScript file %s should exist', $file )
            );
        }
    }

    /**
     * Test nonce generation
     */
    public function test_nonce_generation() {
        do_action( 'wp_enqueue_scripts' );
        
        global $wp_scripts;
        $main_script = $wp_scripts->registered['kei-portfolio-script'];
        
        if ( isset( $main_script->extra['data'] ) ) {
            $localized_data = $main_script->extra['data'];
            
            // Extract nonce value from localized data
            if ( preg_match( '/"nonce":"([^"]+)"/', $localized_data, $matches ) ) {
                $nonce = $matches[1];
                
                // Verify the nonce is valid
                $this->assertNotEmpty( $nonce, 'Nonce should not be empty' );
                $this->assertEquals( 10, strlen( $nonce ), 'Nonce should be 10 characters long' );
            }
        }
    }

    /**
     * Test security: Scripts are loaded with proper integrity
     */
    public function test_script_security() {
        do_action( 'wp_enqueue_scripts' );
        
        global $wp_scripts, $wp_styles;
        
        // Test that external resources use HTTPS
        $external_styles = array( 'kei-portfolio-fonts', 'remixicon' );
        
        foreach ( $external_styles as $handle ) {
            if ( isset( $wp_styles->registered[ $handle ] ) ) {
                $style = $wp_styles->registered[ $handle ];
                $this->assertStringStartsWith(
                    'https://',
                    $style->src,
                    sprintf( 'External stylesheet %s should use HTTPS', $handle )
                );
            }
        }
    }

    /**
     * Test performance: Critical resources are optimized
     */
    public function test_performance_optimizations() {
        do_action( 'wp_enqueue_scripts' );
        
        global $wp_scripts, $wp_styles;
        
        // Test that JavaScript files are loaded in footer (non-blocking)
        $js_handles = array( 'kei-portfolio-navigation', 'kei-portfolio-script' );
        
        foreach ( $js_handles as $handle ) {
            if ( isset( $wp_scripts->registered[ $handle ] ) ) {
                $script = $wp_scripts->registered[ $handle ];
                $this->assertTrue(
                    $script->args === true || $script->args === 1,
                    sprintf( 'JavaScript %s should be loaded in footer for better performance', $handle )
                );
            }
        }
        
        // Test that CSS has proper version for cache busting
        $main_style = $wp_styles->registered['kei-portfolio-style'];
        $this->assertNotEmpty(
            $main_style->ver,
            'Main stylesheet should have version for cache busting'
        );
    }

    /**
     * Test resource loading efficiency
     */
    public function test_resource_loading_efficiency() {
        do_action( 'wp_enqueue_scripts' );
        
        global $wp_scripts, $wp_styles;
        
        // Count total enqueued resources
        $total_styles = count( $wp_styles->queue );
        $total_scripts = count( $wp_scripts->queue );
        
        // Should not load excessive resources
        $this->assertLessThan(
            10,
            $total_styles,
            'Should not enqueue excessive stylesheets'
        );
        
        $this->assertLessThan(
            8,
            $total_scripts,
            'Should not enqueue excessive scripts'
        );
    }

    /**
     * Test external CDN resource availability
     */
    public function test_external_resource_fallbacks() {
        do_action( 'wp_enqueue_scripts' );
        
        global $wp_styles;
        
        // Check Google Fonts
        if ( isset( $wp_styles->registered['kei-portfolio-fonts'] ) ) {
            $fonts = $wp_styles->registered['kei-portfolio-fonts'];
            
            // Should use display=swap for better performance
            $this->assertStringContainsString(
                'display=swap',
                $fonts->src,
                'Google Fonts should use display=swap for better performance'
            );
        }
        
        // Check Remix Icon CDN
        if ( isset( $wp_styles->registered['remixicon'] ) ) {
            $remixicon = $wp_styles->registered['remixicon'];
            
            // Should use a reliable CDN
            $this->assertStringContainsString(
                'cdn.jsdelivr.net',
                $remixicon->src,
                'Should use reliable CDN for external resources'
            );
        }
    }

    /**
     * Test conditional loading security
     */
    public function test_conditional_loading_security() {
        // Test comment reply script is only loaded when needed
        
        // First test: No comments, no threaded comments
        update_option( 'thread_comments', 0 );
        $this->go_to( home_url() );
        
        global $wp_scripts;
        $wp_scripts = new WP_Scripts();
        
        do_action( 'wp_enqueue_scripts' );
        
        $this->assertFalse(
            $wp_scripts->query( 'comment-reply' ),
            'Comment reply script should not be loaded when not needed'
        );
    }

    /**
     * Test localization data security
     */
    public function test_localization_data_security() {
        do_action( 'wp_enqueue_scripts' );
        
        global $wp_scripts;
        $main_script = $wp_scripts->registered['kei-portfolio-script'];
        
        if ( isset( $main_script->extra['data'] ) ) {
            $localized_data = $main_script->extra['data'];
            
            // Should not contain sensitive information
            $this->assertStringNotContainsString(
                'password',
                strtolower( $localized_data ),
                'Localized data should not contain passwords'
            );
            
            $this->assertStringNotContainsString(
                'secret',
                strtolower( $localized_data ),
                'Localized data should not contain secrets'
            );
            
            $this->assertStringNotContainsString(
                'private',
                strtolower( $localized_data ),
                'Localized data should not contain private information'
            );
        }
    }

    /**
     * Test asset file existence and readability
     */
    public function test_asset_file_security() {
        $asset_files = array(
            '/assets/js/navigation.js',
            '/assets/js/main.js',
            '/style.css'
        );
        
        foreach ( $asset_files as $file ) {
            $file_path = get_template_directory() . $file;
            
            if ( file_exists( $file_path ) ) {
                // Check file permissions
                $perms = substr( sprintf( '%o', fileperms( $file_path ) ), -4 );
                
                // Should not be executable (644 or 664 is fine, 755/777 is not)
                $this->assertNotContains(
                    $perms,
                    array( '0755', '0777', '0755', '0775' ),
                    sprintf( 'Asset file %s should not be executable', $file )
                );
                
                // Check file content for potential security issues
                $content = file_get_contents( $file_path );
                
                if ( pathinfo( $file, PATHINFO_EXTENSION ) === 'js' ) {
                    // JavaScript should not contain eval or similar dangerous functions
                    $this->assertStringNotContainsString(
                        'eval(',
                        $content,
                        sprintf( 'JavaScript file %s should not contain eval()', $file )
                    );
                }
            }
        }
    }

    /**
     * Test dependency management
     */
    public function test_dependency_management() {
        do_action( 'wp_enqueue_scripts' );
        
        global $wp_scripts;
        
        // Test that main script properly depends on navigation script
        $main_script = $wp_scripts->registered['kei-portfolio-script'];
        $this->assertContains(
            'kei-portfolio-navigation',
            $main_script->deps,
            'Main script should depend on navigation script'
        );
        
        // Test dependency chain doesn\'t create circular dependencies
        $nav_script = $wp_scripts->registered['kei-portfolio-navigation'];
        $this->assertNotContains(
            'kei-portfolio-script',
            $nav_script->deps,
            'Navigation script should not depend on main script (circular dependency)'
        );
    }

    /**
     * Test WordPress coding standards compliance
     */
    public function test_wordpress_coding_standards() {
        do_action( 'wp_enqueue_scripts' );
        
        global $wp_scripts, $wp_styles;
        
        // Test handle naming follows WordPress conventions (lowercase, hyphens)
        $theme_handles = array( 'kei-portfolio-style', 'kei-portfolio-navigation', 'kei-portfolio-script' );
        
        foreach ( $theme_handles as $handle ) {
            $this->assertEquals(
                strtolower( $handle ),
                $handle,
                sprintf( 'Handle %s should be lowercase', $handle )
            );
            
            $this->assertStringNotContainsString(
                '_',
                $handle,
                sprintf( 'Handle %s should use hyphens, not underscores', $handle )
            );
        }
    }

    /**
     * Test responsive CSS media queries in main stylesheet
     * @group css-styling
     */
    public function test_responsive_breakpoints_in_stylesheet() {
        $style_path = get_template_directory() . '/style.css';
        
        if ( file_exists( $style_path ) ) {
            $style_content = file_get_contents( $style_path );
            
            // Test for common responsive breakpoints
            $breakpoints = array(
                '@media.*max-width.*768px', // Mobile
                '@media.*max-width.*1024px', // Tablet  
                '@media.*min-width.*769px', // Desktop
            );
            
            $breakpoint_found = false;
            foreach ( $breakpoints as $breakpoint ) {
                if ( preg_match( '/' . $breakpoint . '/i', $style_content ) ) {
                    $breakpoint_found = true;
                    break;
                }
            }
            
            $this->assertTrue(
                $breakpoint_found,
                'Main stylesheet should contain responsive media queries'
            );
        } else {
            $this->markTestSkipped( 'Main stylesheet not found' );
        }
    }

    /**
     * Test stylesheet contains required WordPress theme information
     * @group css-styling
     */
    public function test_stylesheet_theme_headers() {
        $style_path = get_template_directory() . '/style.css';
        
        if ( file_exists( $style_path ) ) {
            $style_content = file_get_contents( $style_path );
            
            // Required WordPress theme headers
            $required_headers = array(
                'Theme Name:',
                'Version:',
                'Author:',
                'Description:'
            );
            
            foreach ( $required_headers as $header ) {
                $this->assertStringContainsString(
                    $header,
                    $style_content,
                    sprintf( 'Stylesheet should contain %s header', $header )
                );
            }
        } else {
            $this->fail( 'Main stylesheet (style.css) should exist' );
        }
    }

    /**
     * Test for modern CSS layout techniques
     * @group css-styling
     */
    public function test_modern_css_layout() {
        $style_path = get_template_directory() . '/style.css';
        
        if ( file_exists( $style_path ) ) {
            $style_content = file_get_contents( $style_path );
            
            $modern_properties = array(
                'display\s*:\s*grid',
                'display\s*:\s*flex',
                'grid-template',
                'flex-wrap',
                'justify-content',
                'align-items',
            );
            
            $modern_found = false;
            foreach ( $modern_properties as $property ) {
                if ( preg_match( '/' . $property . '/i', $style_content ) ) {
                    $modern_found = true;
                    break;
                }
            }
            
            $this->assertTrue(
                $modern_found,
                'Stylesheet should use modern CSS layout techniques (Grid or Flexbox)'
            );
        } else {
            $this->markTestSkipped( 'Main stylesheet not found for modern CSS test' );
        }
    }
}
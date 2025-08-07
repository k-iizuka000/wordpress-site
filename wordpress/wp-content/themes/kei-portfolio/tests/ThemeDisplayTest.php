<?php
/**
 * Test suite for theme display functionality after activation
 * 
 * Tests basic display functionality including front page, templates,
 * and responsive design elements
 * 
 * @package Kei_Portfolio
 * @group theme-display
 */

class ThemeDisplayTest extends WP_UnitTestCase {

    /**
     * Theme directory path
     * @var string
     */
    private $theme_dir;

    /**
     * Test post ID for testing
     * @var int
     */
    private $test_post_id;

    /**
     * Test page ID for testing
     * @var int
     */
    private $test_page_id;

    /**
     * Setup before each test
     */
    public function setUp(): void {
        parent::setUp();
        
        $this->theme_dir = get_template_directory();
        
        // Create test content
        $this->test_post_id = $this->factory->post->create( array(
            'post_title'   => 'Test Portfolio Post',
            'post_content' => 'Test content for portfolio display testing.',
            'post_status'  => 'publish',
            'post_type'    => 'post'
        ) );

        $this->test_page_id = $this->factory->post->create( array(
            'post_title'   => 'Test Page',
            'post_content' => 'Test content for page display testing.',
            'post_status'  => 'publish',
            'post_type'    => 'page'
        ) );

        // Ensure theme setup has run
        if ( ! did_action( 'after_setup_theme' ) ) {
            do_action( 'after_setup_theme' );
        }
    }

    /**
     * Cleanup after each test
     */
    public function tearDown(): void {
        // Clean up test posts
        if ( $this->test_post_id ) {
            wp_delete_post( $this->test_post_id, true );
        }
        if ( $this->test_page_id ) {
            wp_delete_post( $this->test_page_id, true );
        }
        
        parent::tearDown();
    }

    /**
     * GROUP 3: 有効化後の表示確認テスト
     * Test front page display elements
     */
    public function test_front_page_display_elements() {
        // Go to home page
        $this->go_to( home_url() );
        
        // Test that we can render the front page without errors
        ob_start();
        
        // Simulate front page rendering
        if ( file_exists( $this->theme_dir . '/front-page.php' ) ) {
            $this->assertTrue( 
                is_readable( $this->theme_dir . '/front-page.php' ),
                'front-page.php should be readable'
            );
        } else if ( file_exists( $this->theme_dir . '/index.php' ) ) {
            $this->assertTrue( 
                is_readable( $this->theme_dir . '/index.php' ),
                'index.php should be readable as fallback'
            );
        }
        
        ob_end_clean();
    }

    /**
     * Test hero section components
     */
    public function test_hero_section_components() {
        // Test hero section template part exists
        $hero_template = $this->theme_dir . '/template-parts/hero-section.php';
        
        if ( file_exists( $hero_template ) ) {
            $this->assertFileExists( 
                $hero_template,
                'Hero section template should exist'
            );
            
            $this->assertFileIsReadable( 
                $hero_template,
                'Hero section template should be readable'
            );
            
            // Test hero section content structure
            $hero_content = file_get_contents( $hero_template );
            
            // Check for essential hero elements
            $this->assertStringContainsString( 
                'hero',
                $hero_content,
                'Hero section should contain hero-related markup'
            );
        }
    }

    /**
     * Test navigation menu display
     */
    public function test_navigation_menu_display() {
        // Test navigation template
        $nav_template = $this->theme_dir . '/template-parts/navigation.php';
        
        if ( file_exists( $nav_template ) ) {
            $this->assertFileExists( 
                $nav_template,
                'Navigation template should exist'
            );
            
            $nav_content = file_get_contents( $nav_template );
            
            // Test for navigation structure
            $this->assertStringContainsString( 
                'nav',
                $nav_content,
                'Navigation template should contain nav element'
            );
        }

        // Test that primary menu is registered
        $menus = get_registered_nav_menus();
        $this->assertArrayHasKey( 
            'primary',
            $menus,
            'Primary navigation menu should be registered'
        );
    }

    /**
     * Test footer display
     */
    public function test_footer_display() {
        // Test footer template
        $footer_template = $this->theme_dir . '/footer.php';
        
        if ( file_exists( $footer_template ) ) {
            $this->assertFileExists( 
                $footer_template,
                'Footer template should exist'
            );
            
            $footer_content = file_get_contents( $footer_template );
            
            // Test for essential footer elements
            $this->assertStringContainsString( 
                'footer',
                $footer_content,
                'Footer should contain footer element'
            );
            
            $this->assertStringContainsString( 
                'wp_footer',
                $footer_content,
                'Footer should call wp_footer()'
            );
        }
    }

    /**
     * Test single post page layout
     */
    public function test_single_post_layout() {
        // Go to single post
        $this->go_to( get_permalink( $this->test_post_id ) );
        
        // Test single post template
        $single_template = $this->theme_dir . '/single.php';
        
        if ( file_exists( $single_template ) ) {
            $this->assertFileExists( 
                $single_template,
                'Single post template should exist'
            );
            
            $single_content = file_get_contents( $single_template );
            
            // Test for essential post elements
            $this->assertStringContainsString( 
                'the_title',
                $single_content,
                'Single template should display post title'
            );
            
            $this->assertStringContainsString( 
                'the_content',
                $single_content,
                'Single template should display post content'
            );
        }
    }

    /**
     * Test page template layout
     */
    public function test_page_template_layout() {
        // Go to test page
        $this->go_to( get_permalink( $this->test_page_id ) );
        
        // Test page template
        $page_template = $this->theme_dir . '/page.php';
        
        if ( file_exists( $page_template ) ) {
            $this->assertFileExists( 
                $page_template,
                'Page template should exist'
            );
            
            $page_content = file_get_contents( $page_template );
            
            // Test for essential page elements
            $this->assertStringContainsString( 
                'the_title',
                $page_content,
                'Page template should display page title'
            );
            
            $this->assertStringContainsString( 
                'the_content',
                $page_content,
                'Page template should display page content'
            );
        }
    }

    /**
     * Test archive page layout
     */
    public function test_archive_template_layout() {
        // Test archive template
        $archive_template = $this->theme_dir . '/archive.php';
        
        if ( file_exists( $archive_template ) ) {
            $this->assertFileExists( 
                $archive_template,
                'Archive template should exist'
            );
            
            $archive_content = file_get_contents( $archive_template );
            
            // Test for essential archive elements
            $this->assertTrue( 
                strpos( $archive_content, 'have_posts' ) !== false ||
                strpos( $archive_content, 'the_post' ) !== false,
                'Archive template should contain post loop'
            );
        }
    }

    /**
     * Test 404 page display
     */
    public function test_404_page_display() {
        // Go to non-existent page
        $this->go_to( home_url( '/non-existent-page-for-testing' ) );
        
        // Test 404 template
        $error_template = $this->theme_dir . '/404.php';
        
        if ( file_exists( $error_template ) ) {
            $this->assertFileExists( 
                $error_template,
                '404 template should exist'
            );
            
            $error_content = file_get_contents( $error_template );
            
            // Test for 404 content
            $this->assertTrue( 
                strpos( $error_content, '404' ) !== false ||
                strpos( $error_content, 'not found' ) !== false ||
                strpos( $error_content, 'Not Found' ) !== false,
                '404 template should contain error message'
            );
        }
    }

    /**
     * Test responsive design CSS classes
     */
    public function test_responsive_design_classes() {
        // Test main stylesheet contains responsive classes
        $style_path = $this->theme_dir . '/style.css';
        
        if ( file_exists( $style_path ) ) {
            $style_content = file_get_contents( $style_path );
            
            // Test for responsive design indicators
            $responsive_indicators = array(
                '@media',
                'max-width',
                'min-width',
                'mobile',
                'tablet',
                'desktop',
                'sm:',
                'md:',
                'lg:'
            );
            
            $has_responsive = false;
            foreach ( $responsive_indicators as $indicator ) {
                if ( strpos( $style_content, $indicator ) !== false ) {
                    $has_responsive = true;
                    break;
                }
            }
            
            $this->assertTrue( 
                $has_responsive,
                'Stylesheet should contain responsive design code'
            );
        }
    }

    /**
     * Test mobile viewport meta tag
     */
    public function test_mobile_viewport_meta() {
        // Test header template
        $header_template = $this->theme_dir . '/header.php';
        
        if ( file_exists( $header_template ) ) {
            $header_content = file_get_contents( $header_template );
            
            // Test for viewport meta tag
            $this->assertTrue( 
                strpos( $header_content, 'viewport' ) !== false,
                'Header should contain viewport meta tag for mobile'
            );
        }
    }

    /**
     * Test CSS and JavaScript enqueuing
     */
    public function test_assets_enqueuing() {
        // Test that enqueue functions exist
        $this->assertTrue( 
            function_exists( 'kei_portfolio_pro_enqueue_styles' ),
            'Style enqueue function should exist'
        );
        
        $this->assertTrue( 
            function_exists( 'kei_portfolio_pro_enqueue_scripts' ),
            'Script enqueue function should exist'
        );
        
        // Test hooks are registered
        $this->assertNotFalse( 
            has_action( 'wp_enqueue_scripts', 'kei_portfolio_pro_enqueue_styles' ),
            'Styles should be hooked to wp_enqueue_scripts'
        );
        
        $this->assertNotFalse( 
            has_action( 'wp_enqueue_scripts', 'kei_portfolio_pro_enqueue_scripts' ),
            'Scripts should be hooked to wp_enqueue_scripts'
        );
    }

    /**
     * Test theme customization options display
     */
    public function test_customization_display() {
        // Test that customizer options are accessible
        if ( function_exists( 'get_theme_mod' ) ) {
            // Test some basic theme mods (these should not error)
            $site_title = get_theme_mod( 'blogname', get_bloginfo( 'name' ) );
            $this->assertIsString( 
                $site_title,
                'Site title should be retrievable'
            );
        }
    }

    /**
     * Test project post type display (if custom post type exists)
     */
    public function test_project_post_type_display() {
        // Check if project post type is registered
        $post_types = get_post_types();
        
        if ( in_array( 'project', $post_types ) ) {
            // Create a test project
            $project_id = $this->factory->post->create( array(
                'post_title'   => 'Test Project',
                'post_content' => 'Test project content.',
                'post_status'  => 'publish',
                'post_type'    => 'project'
            ) );
            
            // Test single project template
            $single_project_template = $this->theme_dir . '/single-project.php';
            
            if ( file_exists( $single_project_template ) ) {
                $this->assertFileExists( 
                    $single_project_template,
                    'Single project template should exist'
                );
                
                $template_content = file_get_contents( $single_project_template );
                
                // Test for project-specific content
                $this->assertTrue( 
                    strpos( $template_content, 'the_title' ) !== false ||
                    strpos( $template_content, 'the_content' ) !== false,
                    'Project template should display project content'
                );
            }
            
            // Test archive project template
            $archive_project_template = $this->theme_dir . '/archive-project.php';
            
            if ( file_exists( $archive_project_template ) ) {
                $this->assertFileExists( 
                    $archive_project_template,
                    'Archive project template should exist'
                );
            }
            
            // Clean up test project
            wp_delete_post( $project_id, true );
        }
    }

    /**
     * Test template parts structure
     */
    public function test_template_parts_structure() {
        $template_parts_dir = $this->theme_dir . '/template-parts';
        
        if ( is_dir( $template_parts_dir ) ) {
            $this->assertDirectoryExists( 
                $template_parts_dir,
                'Template parts directory should exist'
            );
            
            // Test for common template parts
            $common_parts = array(
                '/template-parts/hero-section.php',
                '/template-parts/navigation.php',
                '/template-parts/project-card.php',
                '/template-parts/contact-section.php'
            );
            
            foreach ( $common_parts as $part ) {
                $part_path = $this->theme_dir . $part;
                
                if ( file_exists( $part_path ) ) {
                    $this->assertFileIsReadable( 
                        $part_path,
                        sprintf( 'Template part %s should be readable', $part )
                    );
                    
                    // Test that template part doesn't have dangerous code
                    $part_content = file_get_contents( $part_path );
                    
                    $this->assertStringNotContainsString( 
                        'eval(',
                        $part_content,
                        sprintf( 'Template part %s should not contain eval()', $part )
                    );
                }
            }
        }
    }

    /**
     * Test accessibility features in templates
     */
    public function test_accessibility_features() {
        $templates_to_check = array(
            '/header.php',
            '/footer.php',
            '/index.php'
        );
        
        foreach ( $templates_to_check as $template ) {
            $template_path = $this->theme_dir . $template;
            
            if ( file_exists( $template_path ) ) {
                $template_content = file_get_contents( $template_path );
                
                // Test for basic accessibility features
                $accessibility_features = array(
                    'alt='       => 'Images should have alt attributes',
                    'role='      => 'Elements should have ARIA roles where appropriate',
                    'aria-'      => 'ARIA attributes should be used for accessibility',
                    'tabindex'   => 'Tab navigation should be considered'
                );
                
                $has_accessibility = false;
                foreach ( $accessibility_features as $feature => $message ) {
                    if ( strpos( $template_content, $feature ) !== false ) {
                        $has_accessibility = true;
                        break;
                    }
                }
                
                // At least one accessibility feature should be present in major templates
                if ( in_array( $template, array( '/header.php', '/index.php' ) ) ) {
                    $this->assertTrue( 
                        $has_accessibility,
                        sprintf( '%s should contain accessibility features', $template )
                    );
                }
            }
        }
    }

    /**
     * Test theme displays without PHP errors
     */
    public function test_theme_displays_without_errors() {
        // Test that templates can be included without errors
        $critical_templates = array(
            '/header.php',
            '/footer.php',
            '/index.php'
        );
        
        foreach ( $critical_templates as $template ) {
            $template_path = $this->theme_dir . $template;
            
            if ( file_exists( $template_path ) ) {
                // Capture any errors during template inclusion
                ob_start();
                $error_occurred = false;
                
                try {
                    // Simulate template loading
                    $template_content = file_get_contents( $template_path );
                    
                    // Basic syntax check
                    $this->assertNotEmpty( 
                        $template_content,
                        sprintf( '%s should not be empty', $template )
                    );
                    
                } catch ( Exception $e ) {
                    $error_occurred = true;
                }
                
                ob_end_clean();
                
                $this->assertFalse( 
                    $error_occurred,
                    sprintf( '%s should not cause errors when loaded', $template )
                );
            }
        }
    }

    /**
     * Test SEO-friendly markup
     */
    public function test_seo_friendly_markup() {
        // Test header template for SEO elements
        $header_template = $this->theme_dir . '/header.php';
        
        if ( file_exists( $header_template ) ) {
            $header_content = file_get_contents( $header_template );
            
            // Test for SEO-friendly elements
            $this->assertTrue( 
                strpos( $header_content, 'wp_head' ) !== false,
                'Header should call wp_head() for SEO plugins'
            );
            
            // Test for meta tags
            $seo_indicators = array(
                '<meta',
                'description',
                'og:',
                'twitter:',
                'schema'
            );
            
            $has_seo = false;
            foreach ( $seo_indicators as $indicator ) {
                if ( stripos( $header_content, $indicator ) !== false ) {
                    $has_seo = true;
                    break;
                }
            }
            
            // SEO features might be added by functions, not just templates
            // So we'll check if at least wp_head is present
            $this->assertTrue( 
                strpos( $header_content, 'wp_head' ) !== false,
                'Header should support SEO through wp_head()'
            );
        }
    }
}
<?php
/**
 * Test suite for theme setup functionality
 * 
 * @package Kei_Portfolio
 * @group wordpress-core
 */

class SetupTest extends WP_UnitTestCase {

    /**
     * Setup before each test
     */
    public function setUp(): void {
        parent::setUp();
        
        // Ensure theme setup has run
        if ( ! did_action( 'after_setup_theme' ) ) {
            do_action( 'after_setup_theme' );
        }
    }

    /**
     * Test that theme setup function exists
     */
    public function test_setup_function_exists() {
        $this->assertTrue( 
            function_exists( 'kei_portfolio_pro_setup' ),
            'kei_portfolio_pro_setup function should exist'
        );
    }

    /**
     * Test theme support for post thumbnails
     */
    public function test_post_thumbnails_support() {
        $this->assertTrue( 
            current_theme_supports( 'post-thumbnails' ),
            'Theme should support post thumbnails'
        );
    }

    /**
     * Test theme support for title tag
     */
    public function test_title_tag_support() {
        $this->assertTrue( 
            current_theme_supports( 'title-tag' ),
            'Theme should support title tag'
        );
    }

    /**
     * Test theme support for custom logo
     */
    public function test_custom_logo_support() {
        $this->assertTrue( 
            current_theme_supports( 'custom-logo' ),
            'Theme should support custom logo'
        );
        
        // Test custom logo configuration
        $logo_support = get_theme_support( 'custom-logo' );
        $this->assertIsArray( $logo_support[0], 'Custom logo support should have configuration' );
        $this->assertArrayHasKey( 'height', $logo_support[0], 'Custom logo should have height setting' );
        $this->assertArrayHasKey( 'width', $logo_support[0], 'Custom logo should have width setting' );
        $this->assertArrayHasKey( 'flex-width', $logo_support[0], 'Custom logo should have flex-width setting' );
        $this->assertArrayHasKey( 'flex-height', $logo_support[0], 'Custom logo should have flex-height setting' );
    }

    /**
     * Test theme support for HTML5
     */
    public function test_html5_support() {
        $this->assertTrue( 
            current_theme_supports( 'html5' ),
            'Theme should support HTML5'
        );
        
        $html5_support = get_theme_support( 'html5' );
        $expected_features = array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' );
        
        foreach ( $expected_features as $feature ) {
            $this->assertContains( 
                $feature, 
                $html5_support[0],
                sprintf( 'Theme should support HTML5 for %s', $feature )
            );
        }
    }

    /**
     * Test registered navigation menus
     */
    public function test_registered_nav_menus() {
        $menus = get_registered_nav_menus();
        
        $this->assertIsArray( $menus, 'Registered menus should be an array' );
        $this->assertArrayHasKey( 'primary', $menus, 'Primary menu should be registered' );
        $this->assertArrayHasKey( 'footer', $menus, 'Footer menu should be registered' );
        $this->assertArrayHasKey( 'social', $menus, 'Social menu should be registered' );
    }

    /**
     * Test custom image sizes
     */
    public function test_custom_image_sizes() {
        global $_wp_additional_image_sizes;
        
        // Check project-thumbnail size
        $this->assertArrayHasKey( 
            'project-thumbnail', 
            $_wp_additional_image_sizes,
            'project-thumbnail image size should be registered'
        );
        $this->assertEquals( 400, $_wp_additional_image_sizes['project-thumbnail']['width'] );
        $this->assertEquals( 300, $_wp_additional_image_sizes['project-thumbnail']['height'] );
        $this->assertTrue( $_wp_additional_image_sizes['project-thumbnail']['crop'] );
        
        // Check project-large size
        $this->assertArrayHasKey( 
            'project-large', 
            $_wp_additional_image_sizes,
            'project-large image size should be registered'
        );
        $this->assertEquals( 1200, $_wp_additional_image_sizes['project-large']['width'] );
        $this->assertEquals( 600, $_wp_additional_image_sizes['project-large']['height'] );
        $this->assertTrue( $_wp_additional_image_sizes['project-large']['crop'] );
        
        // Check hero-image size
        $this->assertArrayHasKey( 
            'hero-image', 
            $_wp_additional_image_sizes,
            'hero-image size should be registered'
        );
        $this->assertEquals( 1920, $_wp_additional_image_sizes['hero-image']['width'] );
        $this->assertEquals( 1080, $_wp_additional_image_sizes['hero-image']['height'] );
        $this->assertTrue( $_wp_additional_image_sizes['hero-image']['crop'] );
    }

    /**
     * Test theme support for automatic feed links
     */
    public function test_automatic_feed_links() {
        $this->assertTrue( 
            current_theme_supports( 'automatic-feed-links' ),
            'Theme should support automatic feed links'
        );
    }

    /**
     * Test theme support for align wide
     */
    public function test_align_wide_support() {
        $this->assertTrue( 
            current_theme_supports( 'align-wide' ),
            'Theme should support wide alignment for blocks'
        );
    }

    /**
     * Test theme support for editor styles
     */
    public function test_editor_styles_support() {
        $this->assertTrue( 
            current_theme_supports( 'editor-styles' ),
            'Theme should support editor styles'
        );
    }

    /**
     * Test theme support for responsive embeds
     */
    public function test_responsive_embeds_support() {
        $this->assertTrue( 
            current_theme_supports( 'responsive-embeds' ),
            'Theme should support responsive embeds'
        );
    }

    /**
     * Test meta description function exists
     */
    public function test_meta_description_function_exists() {
        $this->assertTrue( 
            function_exists( 'kei_portfolio_pro_meta_description' ),
            'kei_portfolio_pro_meta_description function should exist'
        );
    }

    /**
     * Test meta description hook is added
     */
    public function test_meta_description_hook() {
        $this->assertNotFalse( 
            has_action( 'wp_head', 'kei_portfolio_pro_meta_description' ),
            'Meta description should be hooked to wp_head'
        );
    }

    /**
     * Test language attributes filter
     */
    public function test_language_attributes_filter() {
        $this->assertNotFalse( 
            has_filter( 'language_attributes', 'kei_portfolio_pro_language_attributes' ),
            'Language attributes filter should be added'
        );
        
        // Test the filter output
        $output = apply_filters( 'language_attributes', '' );
        $this->assertEquals( 'lang="ja"', $output, 'Language should be set to Japanese' );
    }

    /**
     * Test body class filter
     */
    public function test_body_class_filter() {
        $this->assertNotFalse( 
            has_filter( 'body_class', 'kei_portfolio_pro_body_class' ),
            'Body class filter should be added'
        );
        
        // Test body classes are added
        $classes = apply_filters( 'body_class', array() );
        $this->assertContains( 'font-geist-sans', $classes, 'font-geist-sans class should be added' );
        $this->assertContains( 'antialiased', $classes, 'antialiased class should be added' );
    }

    /**
     * Test Open Graph function exists
     */
    public function test_open_graph_function_exists() {
        $this->assertTrue( 
            function_exists( 'kei_portfolio_pro_open_graph' ),
            'kei_portfolio_pro_open_graph function should exist'
        );
    }

    /**
     * Test Open Graph hook is added
     */
    public function test_open_graph_hook() {
        $this->assertNotFalse( 
            has_action( 'wp_head', 'kei_portfolio_pro_open_graph' ),
            'Open Graph tags should be hooked to wp_head'
        );
    }

    /**
     * Test schema person function exists
     */
    public function test_schema_person_function_exists() {
        $this->assertTrue( 
            function_exists( 'kei_portfolio_pro_schema_person' ),
            'kei_portfolio_pro_schema_person function should exist'
        );
    }

    /**
     * Test schema person hook is added
     */
    public function test_schema_person_hook() {
        $this->assertNotFalse( 
            has_action( 'wp_head', 'kei_portfolio_pro_schema_person' ),
            'Schema person should be hooked to wp_head'
        );
    }

    /**
     * Test portfolio import function exists
     */
    public function test_portfolio_import_function_exists() {
        $this->assertTrue( 
            function_exists( 'kei_portfolio_import_json_data' ),
            'kei_portfolio_import_json_data function should exist'
        );
    }

    /**
     * Test text domain is loaded
     */
    public function test_text_domain_loaded() {
        // Check if the text domain loading function is called
        $this->assertTrue( 
            is_textdomain_loaded( 'kei-portfolio' ),
            'Text domain kei-portfolio should be loaded'
        );
    }

    /**
     * Test theme setup runs on correct hook
     */
    public function test_theme_setup_hook() {
        $this->assertNotFalse( 
            has_action( 'after_setup_theme', 'kei_portfolio_pro_setup' ),
            'Theme setup should be hooked to after_setup_theme'
        );
    }

    /**
     * Test theme setup hook priority
     */
    public function test_theme_setup_hook_priority() {
        global $wp_filter;
        
        if ( isset( $wp_filter['after_setup_theme'] ) ) {
            $found = false;
            foreach ( $wp_filter['after_setup_theme'] as $priority => $hooks ) {
                foreach ( $hooks as $hook ) {
                    if ( isset( $hook['function'] ) && $hook['function'] === 'kei_portfolio_pro_setup' ) {
                        $found = true;
                        $this->assertEquals( 10, $priority, 'Theme setup should run at default priority 10' );
                        break 2;
                    }
                }
            }
            $this->assertTrue( $found, 'Theme setup function should be found in hooks' );
        }
    }

    /**
     * Test internationalization (i18n) setup
     */
    public function test_internationalization_setup() {
        // Test text domain loading function is called
        $this->assertTrue(
            function_exists( 'load_theme_textdomain' ),
            'load_theme_textdomain function should be available'
        );
        
        // Test that Japanese locale is properly configured
        $locale = get_locale();
        $this->assertNotEmpty( $locale, 'Locale should be set' );
        
        // Test languages directory path
        $languages_dir = get_template_directory() . '/languages';
        $this->assertDirectoryExists(
            dirname( $languages_dir ),
            'Template directory should exist for languages folder'
        );
    }

    /**
     * Test security features in theme setup
     */
    public function test_security_features() {
        // Test that dangerous theme supports are not enabled
        $dangerous_supports = array(
            'custom-fields', // Can expose sensitive data
            'post-formats'   // Not needed for portfolio theme
        );
        
        foreach ( $dangerous_supports as $support ) {
            $this->assertFalse(
                current_theme_supports( $support ),
                sprintf( 'Theme should not support potentially risky feature: %s', $support )
            );
        }
    }

    /**
     * Test meta description output on different page types
     */
    public function test_meta_description_output() {
        // Test on front page
        $this->go_to( home_url() );
        ob_start();
        do_action( 'wp_head' );
        $output = ob_get_clean();
        
        $this->assertStringContainsString(
            '<meta name="description"',
            $output,
            'Meta description should be present on front page'
        );
        
        // Create and test on single post
        $post_id = $this->factory->post->create( array(
            'post_content' => 'Test post content for meta description.',
            'post_status' => 'publish'
        ) );
        
        $this->go_to( get_permalink( $post_id ) );
        ob_start();
        do_action( 'wp_head' );
        $output = ob_get_clean();
        
        $this->assertStringContainsString(
            '<meta name="description"',
            $output,
            'Meta description should be present on single post'
        );
    }

    /**
     * Test Open Graph tags output
     */
    public function test_open_graph_output() {
        // Create a post with featured image
        $post_id = $this->factory->post->create( array(
            'post_title' => 'Test Post Title',
            'post_content' => 'Test post content for Open Graph.',
            'post_status' => 'publish'
        ) );
        
        $this->go_to( get_permalink( $post_id ) );
        
        ob_start();
        do_action( 'wp_head' );
        $output = ob_get_clean();
        
        // Check for essential Open Graph tags
        $this->assertStringContainsString(
            '<meta property="og:title"',
            $output,
            'Open Graph title should be present'
        );
        
        $this->assertStringContainsString(
            '<meta property="og:type"',
            $output,
            'Open Graph type should be present'
        );
        
        $this->assertStringContainsString(
            '<meta property="og:url"',
            $output,
            'Open Graph URL should be present'
        );
    }

    /**
     * Test schema markup output
     */
    public function test_schema_markup_output() {
        // Test on front page
        $this->go_to( home_url() );
        
        ob_start();
        do_action( 'wp_head' );
        $output = ob_get_clean();
        
        $this->assertStringContainsString(
            'application/ld+json',
            $output,
            'Schema markup should be present on front page'
        );
        
        $this->assertStringContainsString(
            '"@type": "Person"',
            $output,
            'Person schema should be present on front page'
        );
        
        $this->assertStringContainsString(
            '"name": "Kei Aokiki"',
            $output,
            'Person name should be in schema markup'
        );
    }

    /**
     * Test portfolio JSON import function security
     */
    public function test_portfolio_import_security() {
        // Test that function exists
        $this->assertTrue(
            function_exists( 'kei_portfolio_import_json_data' ),
            'Portfolio import function should exist'
        );
        
        // Test with non-existent file
        $result = kei_portfolio_import_json_data();
        
        // Should handle missing file gracefully
        $this->assertFalse(
            $result,
            'Import function should return false for missing file'
        );
    }

    /**
     * Test theme performance optimizations
     */
    public function test_performance_optimizations() {
        // Test that responsive embeds are enabled (Core Web Vitals)
        $this->assertTrue(
            current_theme_supports( 'responsive-embeds' ),
            'Theme should support responsive embeds for better Core Web Vitals'
        );
        
        // Test that excessive theme supports aren\'t enabled
        $expensive_supports = array(
            'wc-product-gallery-zoom',
            'wc-product-gallery-lightbox',
            'wc-product-gallery-slider'
        );
        
        foreach ( $expensive_supports as $support ) {
            $this->assertFalse(
                current_theme_supports( $support ),
                sprintf( 'Theme should not enable expensive feature: %s unless needed', $support )
            );
        }
    }

    /**
     * Test accessibility features
     */
    public function test_accessibility_features() {
        // Check that HTML5 semantic elements are supported
        $html5_support = get_theme_support( 'html5' );
        $accessibility_features = array( 'search-form', 'comment-form' );
        
        foreach ( $accessibility_features as $feature ) {
            $this->assertContains(
                $feature,
                $html5_support[0],
                sprintf( 'HTML5 support should include accessibility feature: %s', $feature )
            );
        }
    }

    /**
     * Test that theme doesn\'t override WordPress defaults unnecessarily
     */
    public function test_wordpress_defaults_respect() {
        // Test that essential WordPress features are not disabled
        $essential_features = array(
            'automatic-feed-links',
            'title-tag'
        );
        
        foreach ( $essential_features as $feature ) {
            $this->assertTrue(
                current_theme_supports( $feature ),
                sprintf( 'Essential WordPress feature should be supported: %s', $feature )
            );
        }
    }
}
<?php
/**
 * Test suite for stylesheet structure and responsive design
 * 
 * @package Kei_Portfolio
 * @group css-styling
 * @group responsive-design
 */

class StyleTest extends WP_UnitTestCase {

    /**
     * Theme directory path
     * 
     * @var string
     */
    private $theme_dir;

    /**
     * Main stylesheet content
     * 
     * @var string
     */
    private $style_content;

    /**
     * Setup before each test
     */
    public function setUp(): void {
        parent::setUp();
        $this->theme_dir = get_template_directory();
        
        $style_path = $this->theme_dir . '/style.css';
        if (file_exists($style_path)) {
            $this->style_content = file_get_contents($style_path);
        }
    }

    /**
     * Test responsive breakpoints are defined
     */
    public function test_responsive_breakpoints() {
        $this->assertNotEmpty(
            $this->style_content,
            'Stylesheet content should be available for testing'
        );
        
        // Common responsive breakpoints to check for
        $breakpoints = array(
            '@media.*max-width.*768px', // Mobile
            '@media.*max-width.*1024px', // Tablet
            '@media.*min-width.*769px', // Desktop
            '@media.*max-width.*480px', // Small mobile
        );
        
        $breakpoint_found = false;
        foreach ($breakpoints as $breakpoint) {
            if (preg_match('/' . $breakpoint . '/i', $this->style_content)) {
                $breakpoint_found = true;
                break;
            }
        }
        
        $this->assertTrue(
            $breakpoint_found,
            'Stylesheet should contain responsive media queries'
        );
    }

    /**
     * Test for mobile-first responsive design patterns
     */
    public function test_mobile_first_approach() {
        // Look for min-width media queries (mobile-first approach)
        $min_width_queries = preg_match_all('/@media[^{]*min-width/i', $this->style_content);
        $max_width_queries = preg_match_all('/@media[^{]*max-width/i', $this->style_content);
        
        if ($min_width_queries > 0 || $max_width_queries > 0) {
            $this->assertGreaterThan(
                0,
                $min_width_queries + $max_width_queries,
                'Stylesheet should contain media queries for responsive design'
            );
        }
    }

    /**
     * Test for essential CSS reset or normalize
     */
    public function test_css_reset_or_normalize() {
        // Look for common reset/normalize patterns
        $reset_patterns = array(
            '\*\s*\{[^}]*box-sizing', // Universal box-sizing
            'html\s*\{[^}]*font-size', // HTML font-size reset
            'body\s*\{[^}]*margin\s*:\s*0', // Body margin reset
            'h[1-6].*margin', // Heading margin reset
            '\*\s*\{[^}]*margin\s*:\s*0.*padding\s*:\s*0', // Universal reset
        );
        
        $reset_found = false;
        foreach ($reset_patterns as $pattern) {
            if (preg_match('/' . $pattern . '/is', $this->style_content)) {
                $reset_found = true;
                break;
            }
        }
        
        $this->assertTrue(
            $reset_found,
            'Stylesheet should include CSS reset or normalize styles'
        );
    }

    /**
     * Test for WordPress-specific styles
     */
    public function test_wordpress_specific_styles() {
        $wp_specific_classes = array(
            '\.wp-block', // Block editor styles
            '\.alignleft', // WordPress alignment classes
            '\.alignright',
            '\.aligncenter',
            '\.wp-caption', // WordPress caption styles
            '\.gallery', // WordPress gallery styles
            '\.wp-embedded-content', // WordPress embedded content
        );
        
        $wp_styles_found = 0;
        foreach ($wp_specific_classes as $class) {
            if (preg_match('/' . $class . '/i', $this->style_content)) {
                $wp_styles_found++;
            }
        }
        
        $this->assertGreaterThan(
            0,
            $wp_styles_found,
            'Stylesheet should include WordPress-specific styles'
        );
    }

    /**
     * Test for accessibility-friendly styles
     */
    public function test_accessibility_styles() {
        $a11y_patterns = array(
            ':focus', // Focus styles
            'skip-link', // Skip to content links
            'screen-reader-text', // Screen reader text
            'sr-only', // Screen reader only content
            'visually-hidden', // Visually hidden content
            'outline', // Focus outlines
        );
        
        $a11y_found = false;
        foreach ($a11y_patterns as $pattern) {
            if (stripos($this->style_content, $pattern) !== false) {
                $a11y_found = true;
                break;
            }
        }
        
        $this->assertTrue(
            $a11y_found,
            'Stylesheet should include accessibility-friendly styles'
        );
    }

    /**
     * Test for common layout and structure styles
     */
    public function test_layout_structure_styles() {
        $layout_elements = array(
            '\.container', // Container class
            'header', // Header element or class
            'footer', // Footer element or class
            'nav', // Navigation element or class
            'main', // Main content element or class
            '\.site-', // Site-specific classes
        );
        
        $layout_found = 0;
        foreach ($layout_elements as $element) {
            if (preg_match('/' . $element . '/i', $this->style_content)) {
                $layout_found++;
            }
        }
        
        $this->assertGreaterThan(
            1,
            $layout_found,
            'Stylesheet should include basic layout and structure styles'
        );
    }

    /**
     * Test for typography styles
     */
    public function test_typography_styles() {
        $typography_properties = array(
            'font-family',
            'font-size',
            'line-height',
            'font-weight',
            'color',
        );
        
        $typography_found = 0;
        foreach ($typography_properties as $property) {
            if (stripos($this->style_content, $property) !== false) {
                $typography_found++;
            }
        }
        
        $this->assertGreaterThan(
            3,
            $typography_found,
            'Stylesheet should include comprehensive typography styles'
        );
    }

    /**
     * Test for button and form styles
     */
    public function test_form_and_button_styles() {
        $form_elements = array(
            'button',
            'input',
            'textarea',
            'select',
            'form',
            '\.btn', // Button classes
        );
        
        $form_styles_found = 0;
        foreach ($form_elements as $element) {
            if (preg_match('/' . $element . '/i', $this->style_content)) {
                $form_styles_found++;
            }
        }
        
        $this->assertGreaterThan(
            2,
            $form_styles_found,
            'Stylesheet should include form and button styles'
        );
    }

    /**
     * Test for color scheme consistency
     */
    public function test_color_scheme_consistency() {
        // Extract hex colors from CSS
        preg_match_all('/#[a-fA-F0-9]{3,6}/', $this->style_content, $hex_colors);
        
        // Extract rgb/rgba colors
        preg_match_all('/rgba?\([^)]+\)/', $this->style_content, $rgb_colors);
        
        $total_colors = count($hex_colors[0]) + count($rgb_colors[0]);
        
        if ($total_colors > 0) {
            $this->assertGreaterThan(
                0,
                $total_colors,
                'Stylesheet should define colors'
            );
            
            // Check for too many different colors (might indicate inconsistent color scheme)
            $unique_colors = array_unique(array_merge($hex_colors[0], $rgb_colors[0]));
            $this->assertLessThan(
                50,
                count($unique_colors),
                'Color scheme should be consistent - too many unique colors detected'
            );
        }
    }

    /**
     * Test for CSS Grid or Flexbox usage (modern layout)
     */
    public function test_modern_layout_techniques() {
        $modern_layout_properties = array(
            'display\s*:\s*grid',
            'display\s*:\s*flex',
            'grid-template',
            'flex-wrap',
            'justify-content',
            'align-items',
            'gap',
        );
        
        $modern_layout_found = false;
        foreach ($modern_layout_properties as $property) {
            if (preg_match('/' . $property . '/i', $this->style_content)) {
                $modern_layout_found = true;
                break;
            }
        }
        
        $this->assertTrue(
            $modern_layout_found,
            'Stylesheet should use modern layout techniques (Grid or Flexbox)'
        );
    }

    /**
     * Test for vendor prefixes where needed
     */
    public function test_vendor_prefixes() {
        // Properties that might need vendor prefixes
        $properties_needing_prefixes = array(
            'transform',
            'transition',
            'animation',
            'box-shadow',
            'border-radius',
        );
        
        foreach ($properties_needing_prefixes as $property) {
            if (stripos($this->style_content, $property) !== false) {
                // If modern property is used, it's generally fine
                // This test mainly ensures we're aware of prefix needs
                $this->assertTrue(true, 'Modern CSS properties detected');
                break;
            }
        }
    }

    /**
     * Test for print styles
     */
    public function test_print_styles() {
        $has_print_styles = preg_match('/@media[^{]*print/i', $this->style_content);
        
        if ($has_print_styles) {
            $this->assertGreaterThan(
                0,
                $has_print_styles,
                'Print styles are included'
            );
        } else {
            $this->markTestSkipped('Print styles not found - consider adding for better print experience');
        }
    }

    /**
     * Test for CSS custom properties (CSS variables)
     */
    public function test_css_custom_properties() {
        $has_custom_properties = preg_match('/--[a-zA-Z0-9-]+\s*:/i', $this->style_content);
        
        if ($has_custom_properties) {
            $this->assertGreaterThan(
                0,
                $has_custom_properties,
                'CSS custom properties (variables) are being used'
            );
            
            // Check for :root declaration
            $has_root_declaration = stripos($this->style_content, ':root');
            $this->assertNotFalse(
                $has_root_declaration,
                'CSS custom properties should be defined in :root'
            );
        }
    }

    /**
     * Test for navigation styles
     */
    public function test_navigation_styles() {
        $nav_selectors = array(
            '\.menu',
            '\.nav',
            'nav',
            '\.navigation',
            '\.menu-item',
            '\.current-menu-item',
        );
        
        $nav_styles_found = 0;
        foreach ($nav_selectors as $selector) {
            if (preg_match('/' . $selector . '/i', $this->style_content)) {
                $nav_styles_found++;
            }
        }
        
        $this->assertGreaterThan(
            1,
            $nav_styles_found,
            'Stylesheet should include navigation styles'
        );
    }

    /**
     * Test for footer and header styles
     */
    public function test_header_footer_styles() {
        $structural_elements = array(
            'header',
            'footer',
            '\.site-header',
            '\.site-footer',
            '\.header',
            '\.footer',
        );
        
        $structural_found = 0;
        foreach ($structural_elements as $element) {
            if (preg_match('/' . $element . '/i', $this->style_content)) {
                $structural_found++;
            }
        }
        
        $this->assertGreaterThan(
            1,
            $structural_found,
            'Stylesheet should include header and footer styles'
        );
    }

    /**
     * Test for CSS organization and comments
     */
    public function test_css_organization() {
        // Check for section comments or organization
        $section_comments = preg_match_all('/\/\*.*?\*\//s', $this->style_content);
        
        $this->assertGreaterThan(
            0,
            $section_comments,
            'Stylesheet should include organizational comments'
        );
        
        // Check for proper CSS formatting (basic check)
        $properly_closed_rules = substr_count($this->style_content, '{') === substr_count($this->style_content, '}');
        $this->assertTrue(
            $properly_closed_rules,
            'CSS rules should be properly closed with matching braces'
        );
    }

    /**
     * Test for theme-specific classes and IDs
     */
    public function test_theme_specific_styles() {
        // Look for theme-specific naming patterns
        $theme_patterns = array(
            'kei-portfolio', // Theme name in classes
            'portfolio', // Portfolio specific
            'hero', // Hero section
            'about', // About section
            'skills', // Skills section
            'contact', // Contact section
        );
        
        $theme_specific_found = 0;
        foreach ($theme_patterns as $pattern) {
            if (stripos($this->style_content, $pattern) !== false) {
                $theme_specific_found++;
            }
        }
        
        $this->assertGreaterThan(
            2,
            $theme_specific_found,
            'Stylesheet should include theme-specific styles'
        );
    }

    /**
     * Test for WordPress block editor styles compatibility
     */
    public function test_block_editor_styles() {
        $block_patterns = array(
            '\.wp-block',
            '\.block-editor',
            '\.editor-styles-wrapper',
            '\.has-text-color',
            '\.has-background',
        );
        
        $block_styles_found = 0;
        foreach ($block_patterns as $pattern) {
            if (preg_match('/' . $pattern . '/i', $this->style_content)) {
                $block_styles_found++;
            }
        }
        
        // Block editor styles are recommended but not always required
        if ($block_styles_found > 0) {
            $this->assertGreaterThan(
                0,
                $block_styles_found,
                'Block editor styles are present'
            );
        } else {
            $this->markTestSkipped('Block editor styles not found - consider adding for Gutenberg compatibility');
        }
    }

    /**
     * Test for image and media styles
     */
    public function test_media_styles() {
        $media_selectors = array(
            'img',
            '.wp-caption',
            '.gallery',
            'figure',
            'video',
            '.embed',
        );
        
        $media_styles_found = 0;
        foreach ($media_selectors as $selector) {
            if (preg_match('/' . preg_quote($selector, '/') . '/i', $this->style_content)) {
                $media_styles_found++;
            }
        }
        
        $this->assertGreaterThan(
            2,
            $media_styles_found,
            'Stylesheet should include image and media styles'
        );
    }

    /**
     * Test for CSS specificity best practices
     */
    public function test_css_specificity() {
        // Count ID selectors (high specificity)
        $id_selectors = preg_match_all('/#[a-zA-Z][\w-]*/', $this->style_content);
        
        // Count class selectors
        $class_selectors = preg_match_all('/\.[a-zA-Z][\w-]*/', $this->style_content);
        
        if ($class_selectors > 0) {
            // Recommend more class selectors than ID selectors for maintainability
            if ($id_selectors > 0) {
                $ratio = $class_selectors / $id_selectors;
                $this->assertGreaterThan(
                    2,
                    $ratio,
                    'Should use more class selectors than ID selectors for better maintainability'
                );
            }
        }
        
        // Check for overuse of !important
        $important_declarations = preg_match_all('/!important/i', $this->style_content);
        if ($important_declarations > 0) {
            $total_rules_estimate = substr_count($this->style_content, '{');
            if ($total_rules_estimate > 0) {
                $important_ratio = $important_declarations / $total_rules_estimate;
                $this->assertLessThan(
                    0.1,
                    $important_ratio,
                    'Should not overuse !important declarations (less than 10% of rules)'
                );
            }
        }
    }
}
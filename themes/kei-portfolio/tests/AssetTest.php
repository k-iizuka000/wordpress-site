<?php
/**
 * Test suite for CSS/JS asset files existence and integrity
 * 
 * @package Kei_Portfolio
 * @group css-styling
 * @group assets
 */

class AssetTest extends WP_UnitTestCase {

    /**
     * Theme directory path
     * 
     * @var string
     */
    private $theme_dir;

    /**
     * Setup before each test
     */
    public function setUp(): void {
        parent::setUp();
        $this->theme_dir = get_template_directory();
    }

    /**
     * Test that main stylesheet exists and has correct structure
     */
    public function test_main_stylesheet_exists() {
        $style_path = $this->theme_dir . '/style.css';
        
        $this->assertFileExists(
            $style_path,
            'Main stylesheet (style.css) should exist in theme root'
        );
        
        $this->assertFileIsReadable(
            $style_path,
            'Main stylesheet should be readable'
        );
        
        // Check file is not empty
        $file_size = filesize($style_path);
        $this->assertGreaterThan(
            0,
            $file_size,
            'Main stylesheet should not be empty'
        );
    }

    /**
     * Test main stylesheet has required WordPress theme headers
     */
    public function test_main_stylesheet_headers() {
        $style_path = $this->theme_dir . '/style.css';
        $style_content = file_get_contents($style_path);
        
        // Required WordPress theme headers
        $required_headers = array(
            'Theme Name',
            'Version',
            'Author',
            'Description'
        );
        
        foreach ($required_headers as $header) {
            $this->assertStringContainsString(
                $header . ':',
                $style_content,
                sprintf('Stylesheet should contain %s header', $header)
            );
        }
        
        // Check for proper CSS comment format
        $this->assertStringStartsWith(
            '/*',
            $style_content,
            'Stylesheet should start with CSS comment block for theme headers'
        );
    }

    /**
     * Test JavaScript files exist
     */
    public function test_javascript_files_exist() {
        $js_files = array(
            '/assets/js/main.js',
            '/assets/js/navigation.js'
        );
        
        foreach ($js_files as $js_file) {
            $file_path = $this->theme_dir . $js_file;
            
            $this->assertFileExists(
                $file_path,
                sprintf('JavaScript file %s should exist', $js_file)
            );
            
            $this->assertFileIsReadable(
                $file_path,
                sprintf('JavaScript file %s should be readable', $js_file)
            );
            
            // Check file is not empty
            $file_size = filesize($file_path);
            $this->assertGreaterThan(
                0,
                $file_size,
                sprintf('JavaScript file %s should not be empty', $js_file)
            );
        }
    }

    /**
     * Test optional JavaScript files (if they exist)
     */
    public function test_optional_javascript_files() {
        $optional_js_files = array(
            '/assets/js/contact-form.js',
            '/assets/js/portfolio-filter.js',
            '/assets/js/technical-approach.js'
        );
        
        foreach ($optional_js_files as $js_file) {
            $file_path = $this->theme_dir . $js_file;
            
            // If file exists, it should be readable and not empty
            if (file_exists($file_path)) {
                $this->assertFileIsReadable(
                    $file_path,
                    sprintf('Optional JavaScript file %s should be readable if it exists', $js_file)
                );
                
                $file_size = filesize($file_path);
                $this->assertGreaterThan(
                    0,
                    $file_size,
                    sprintf('Optional JavaScript file %s should not be empty if it exists', $js_file)
                );
            }
        }
    }

    /**
     * Test CSS files in assets directory
     */
    public function test_css_assets_directory() {
        $css_dir = $this->theme_dir . '/assets/css';
        
        // CSS directory may not exist, but if it does, test its contents
        if (is_dir($css_dir)) {
            $this->assertFileExists(
                $css_dir,
                'CSS assets directory should exist if referenced'
            );
            
            // Find CSS files in the directory
            $css_files = glob($css_dir . '/*.css');
            
            foreach ($css_files as $css_file) {
                $this->assertFileIsReadable(
                    $css_file,
                    sprintf('CSS file %s should be readable', basename($css_file))
                );
                
                // Check file is not empty
                $file_size = filesize($css_file);
                $this->assertGreaterThan(
                    0,
                    $file_size,
                    sprintf('CSS file %s should not be empty', basename($css_file))
                );
            }
        }
    }

    /**
     * Test JavaScript syntax validity
     */
    public function test_javascript_syntax_validity() {
        $js_files = array(
            '/assets/js/main.js',
            '/assets/js/navigation.js'
        );
        
        foreach ($js_files as $js_file) {
            $file_path = $this->theme_dir . $js_file;
            
            if (file_exists($file_path)) {
                $js_content = file_get_contents($file_path);
                
                // Basic syntax checks
                $this->assertStringNotContainsString(
                    'console.log(',
                    $js_content,
                    sprintf('JavaScript file %s should not contain console.log in production', $js_file)
                );
                
                // Check for proper JavaScript structure
                if (!empty(trim($js_content))) {
                    // Should not have obvious syntax errors (basic check)
                    $unclosed_braces = substr_count($js_content, '{') - substr_count($js_content, '}');
                    $this->assertEquals(
                        0,
                        $unclosed_braces,
                        sprintf('JavaScript file %s should have balanced braces', $js_file)
                    );
                    
                    $unclosed_parens = substr_count($js_content, '(') - substr_count($js_content, ')');
                    $this->assertEquals(
                        0,
                        $unclosed_parens,
                        sprintf('JavaScript file %s should have balanced parentheses', $js_file)
                    );
                }
            }
        }
    }

    /**
     * Test CSS syntax validity (basic checks)
     */
    public function test_css_syntax_validity() {
        $style_path = $this->theme_dir . '/style.css';
        $style_content = file_get_contents($style_path);
        
        // Remove comment blocks for analysis
        $style_without_comments = preg_replace('/\/\*.*?\*\//s', '', $style_content);
        
        if (!empty(trim($style_without_comments))) {
            // Basic CSS syntax checks
            $unclosed_braces = substr_count($style_without_comments, '{') - substr_count($style_without_comments, '}');
            $this->assertEquals(
                0,
                $unclosed_braces,
                'Main stylesheet should have balanced braces'
            );
            
            // Check for common CSS structure
            $this->assertMatchesRegularExpression(
                '/[a-zA-Z0-9\-\.\#\s,]+\s*\{[^}]*\}/',
                $style_without_comments,
                'Main stylesheet should contain valid CSS rules'
            );
        }
    }

    /**
     * Test asset file permissions
     */
    public function test_asset_file_permissions() {
        $asset_files = array(
            '/style.css',
            '/assets/js/main.js',
            '/assets/js/navigation.js'
        );
        
        foreach ($asset_files as $asset_file) {
            $file_path = $this->theme_dir . $asset_file;
            
            if (file_exists($file_path)) {
                $this->assertTrue(
                    is_readable($file_path),
                    sprintf('Asset file %s should be readable', $asset_file)
                );
                
                // Check file is not writable by others (security)
                $permissions = fileperms($file_path);
                $this->assertFalse(
                    ($permissions & 0x0002), // Check if others can write
                    sprintf('Asset file %s should not be writable by others', $asset_file)
                );
            }
        }
    }

    /**
     * Test for presence of source maps (development vs production)
     */
    public function test_source_maps_handling() {
        $js_files = array(
            '/assets/js/main.js',
            '/assets/js/navigation.js'
        );
        
        foreach ($js_files as $js_file) {
            $file_path = $this->theme_dir . $js_file;
            
            if (file_exists($file_path)) {
                $js_content = file_get_contents($file_path);
                
                // In production, source maps should not be included
                if (defined('WP_DEBUG') && !WP_DEBUG) {
                    $this->assertStringNotContainsString(
                        '//# sourceMappingURL=',
                        $js_content,
                        sprintf('JavaScript file %s should not include source maps in production', $js_file)
                    );
                }
            }
        }
    }

    /**
     * Test asset file modification times (caching considerations)
     */
    public function test_asset_modification_times() {
        $asset_files = array(
            '/style.css' => 'main stylesheet',
            '/assets/js/main.js' => 'main JavaScript',
            '/assets/js/navigation.js' => 'navigation JavaScript'
        );
        
        $theme_version = wp_get_theme()->get('Version');
        
        foreach ($asset_files as $asset_file => $description) {
            $file_path = $this->theme_dir . $asset_file;
            
            if (file_exists($file_path)) {
                $file_time = filemtime($file_path);
                
                $this->assertNotFalse(
                    $file_time,
                    sprintf('Should be able to get modification time for %s', $description)
                );
                
                // File should not be from the future
                $this->assertLessThanOrEqual(
                    time(),
                    $file_time,
                    sprintf('%s modification time should not be in the future', ucfirst($description))
                );
            }
        }
    }

    /**
     * Test minified versions exist (if applicable)
     */
    public function test_minified_versions() {
        $assets_to_check = array(
            '/assets/js/main.js' => '/assets/js/main.min.js',
            '/assets/js/navigation.js' => '/assets/js/navigation.min.js'
        );
        
        foreach ($assets_to_check as $original => $minified) {
            $original_path = $this->theme_dir . $original;
            $minified_path = $this->theme_dir . $minified;
            
            // If original exists and minified exists, test minified
            if (file_exists($original_path) && file_exists($minified_path)) {
                $this->assertFileIsReadable(
                    $minified_path,
                    sprintf('Minified file %s should be readable if it exists', $minified)
                );
                
                $original_size = filesize($original_path);
                $minified_size = filesize($minified_path);
                
                $this->assertLessThanOrEqual(
                    $original_size,
                    $minified_size,
                    sprintf('Minified file %s should not be larger than original', $minified)
                );
            }
        }
    }

    /**
     * Test SCSS/SASS source files (if using preprocessors)
     */
    public function test_scss_source_files() {
        $scss_directories = array(
            '/assets/scss',
            '/src/scss',
            '/sass'
        );
        
        foreach ($scss_directories as $scss_dir) {
            $scss_path = $this->theme_dir . $scss_dir;
            
            if (is_dir($scss_path)) {
                // If SCSS directory exists, check for main file
                $main_scss_files = array(
                    $scss_path . '/style.scss',
                    $scss_path . '/main.scss',
                    $scss_path . '/_main.scss'
                );
                
                $found_main_scss = false;
                foreach ($main_scss_files as $main_scss) {
                    if (file_exists($main_scss)) {
                        $found_main_scss = true;
                        
                        $this->assertFileIsReadable(
                            $main_scss,
                            'Main SCSS file should be readable'
                        );
                        
                        // Basic SCSS syntax check
                        $scss_content = file_get_contents($main_scss);
                        $this->assertNotEmpty(
                            trim($scss_content),
                            'Main SCSS file should not be empty'
                        );
                        
                        break;
                    }
                }
                
                // If SCSS directory exists, we expect at least one main SCSS file
                $this->assertTrue(
                    $found_main_scss,
                    'SCSS directory exists but no main SCSS file found'
                );
            }
        }
    }

    /**
     * Test theme screenshot and other theme assets
     */
    public function test_theme_screenshot() {
        $screenshot_files = array(
            '/screenshot.png',
            '/screenshot.jpg'
        );
        
        $screenshot_exists = false;
        foreach ($screenshot_files as $screenshot_file) {
            $screenshot_path = $this->theme_dir . $screenshot_file;
            
            if (file_exists($screenshot_path)) {
                $screenshot_exists = true;
                
                $this->assertFileIsReadable(
                    $screenshot_path,
                    'Theme screenshot should be readable'
                );
                
                // Check image dimensions (WordPress recommends 1200x900)
                if (function_exists('getimagesize')) {
                    $image_info = getimagesize($screenshot_path);
                    if ($image_info !== false) {
                        $this->assertGreaterThanOrEqual(
                            600,
                            $image_info[0],
                            'Screenshot width should be at least 600px'
                        );
                        $this->assertGreaterThanOrEqual(
                            450,
                            $image_info[1],
                            'Screenshot height should be at least 450px'
                        );
                    }
                }
                break;
            }
        }
        
        // Screenshot is recommended but not required
        if (!$screenshot_exists) {
            $this->markTestIncomplete('Theme screenshot not found - recommended for theme distribution');
        }
    }
}
<?php
/**
 * PHPUnit Bootstrap File
 * 
 * Set up the testing environment for the kei-portfolio theme
 * 
 * @package KeiPortfolio
 * @subpackage Tests
 */

// Define test environment constants
define( 'WP_TESTS_PHPUNIT_BOOTSTRAP', true );
define( 'THEME_TESTS_DIR', __DIR__ );
define( 'THEME_DIR', dirname( __DIR__ ) );

// Define minimal WordPress constants first
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', dirname( dirname( __DIR__ ) ) . '/wordpress/' );
}

if ( ! defined( 'WP_CONTENT_DIR' ) ) {
    define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
}

if ( ! defined( 'WP_PLUGIN_DIR' ) ) {
    define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
}

// Mock essential WordPress functions before loading composer
if ( ! function_exists( 'get_template_directory' ) ) {
    function get_template_directory() {
        return THEME_DIR;
    }
}

if ( ! function_exists( 'get_template_directory_uri' ) ) {
    function get_template_directory_uri() {
        return 'http://localhost/wp-content/themes/kei-portfolio';
    }
}

if ( ! function_exists( 'add_action' ) ) {
    function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
        // Mock implementation
    }
}

if ( ! function_exists( 'add_filter' ) ) {
    function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
        // Mock implementation
    }
}

if ( ! function_exists( 'wp_enqueue_script' ) ) {
    function wp_enqueue_script( $handle, $src = '', $deps = array(), $ver = false, $in_footer = false ) {
        // Mock implementation
    }
}

if ( ! function_exists( 'wp_enqueue_style' ) ) {
    function wp_enqueue_style( $handle, $src = '', $deps = array(), $ver = false, $media = 'all' ) {
        // Mock implementation
    }
}

if ( ! function_exists( 'register_post_type' ) ) {
    function register_post_type( $post_type, $args = array() ) {
        // Mock implementation
        return new stdClass();
    }
}

if ( ! function_exists( 'add_theme_support' ) ) {
    function add_theme_support( $feature ) {
        // Mock implementation
    }
}

if ( ! function_exists( '__' ) ) {
    function __( $text, $domain = 'default' ) {
        return $text;
    }
}

if ( ! function_exists( 'esc_html' ) ) {
    function esc_html( $text ) {
        return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
    }
}

if ( ! function_exists( 'esc_attr' ) ) {
    function esc_attr( $text ) {
        return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
    }
}

if ( ! function_exists( 'esc_url' ) ) {
    function esc_url( $url ) {
        return filter_var( $url, FILTER_SANITIZE_URL );
    }
}

if ( ! function_exists( 'wp_kses_post' ) ) {
    function wp_kses_post( $content ) {
        return strip_tags( $content, '<p><a><strong><em><ul><ol><li><blockquote><h1><h2><h3><h4><h5><h6>' );
    }
}

// Composer autoloader
$composer_autoload = dirname( __DIR__ ) . '/vendor/autoload.php';
if ( file_exists( $composer_autoload ) ) {
    require_once $composer_autoload;
}

// WordPress test suite bootstrap (optional - for integration tests)
$wp_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $wp_tests_dir ) {
    // Try common locations
    $possible_paths = [
        dirname( dirname( dirname( dirname( dirname( __DIR__ ) ) ) ) ) . '/wordpress-tests-lib',
        '/tmp/wordpress-tests-lib',
        dirname( dirname( dirname( dirname( dirname( __DIR__ ) ) ) ) ) . '/tests/phpunit',
    ];
    
    foreach ( $possible_paths as $path ) {
        if ( file_exists( $path . '/includes/bootstrap.php' ) ) {
            $wp_tests_dir = $path;
            break;
        }
    }
}

// If WordPress test suite is available, load it
if ( $wp_tests_dir && file_exists( $wp_tests_dir . '/includes/bootstrap.php' ) ) {
    // Give access to tests_add_filter() function
    require_once $wp_tests_dir . '/includes/functions.php';
    
    /**
     * Manually load the theme
     */
    function _manually_load_theme() {
        // Load the theme functions
        require THEME_DIR . '/functions.php';
    }
    
    tests_add_filter( 'muplugins_loaded', '_manually_load_theme' );
    
    // Start up the WP testing environment
    require $wp_tests_dir . '/includes/bootstrap.php';
    
    echo "WordPress test environment loaded.\n";
} else {
    // Minimal bootstrap for syntax and standards tests (no WordPress required)
    echo "Running in standalone mode (no WordPress test suite).\n";
    echo "To run WordPress integration tests, set WP_TESTS_DIR environment variable.\n\n";
}

// Load test helper functions if available
$test_helpers = THEME_TESTS_DIR . '/test-helpers.php';
if ( file_exists( $test_helpers ) ) {
    require_once $test_helpers;
}

// Custom test case base class
if ( ! class_exists( 'WP_UnitTestCase' ) ) {
    /**
     * Basic test case for standalone testing
     */
    class WP_UnitTestCase extends PHPUnit\Framework\TestCase {
        /**
         * Set up the test fixture
         */
        protected function setUp(): void {
            parent::setUp();
        }
        
        /**
         * Tear down the test fixture
         */
        protected function tearDown(): void {
            parent::tearDown();
        }
    }
}

// Custom AJAX test case base class
if ( ! class_exists( 'WP_Ajax_UnitTestCase' ) ) {
    /**
     * Basic AJAX test case for standalone testing
     */
    class WP_Ajax_UnitTestCase extends WP_UnitTestCase {
        protected $_last_response = '';
        
        /**
         * Simulate AJAX call
         * 
         * @param string $action AJAX action
         */
        protected function _handleAjax( $action ) {
            // Mock implementation for standalone testing
            $this->_last_response = json_encode( [ 'success' => true ] );
        }
    }
}

// Exception for AJAX testing
if ( ! class_exists( 'WPAjaxDieContinueException' ) ) {
    class WPAjaxDieContinueException extends Exception {}
}

echo "Theme test bootstrap completed.\n";
echo "Theme directory: " . THEME_DIR . "\n";
echo "Tests directory: " . THEME_TESTS_DIR . "\n\n";
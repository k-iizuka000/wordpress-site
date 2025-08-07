<?php
/**
 * Test Helper Functions
 * 
 * テスト用の共通ヘルパー関数とモック関数を提供
 * 
 * @package KeiPortfolio
 * @subpackage Tests
 */

/**
 * WordPress関数のモック実装
 * WordPressテストスイートが利用できない場合に使用
 */

// WordPress Core Functions Mocks
if ( ! function_exists( 'get_template_directory' ) ) {
    function get_template_directory() {
        return THEME_DIR;
    }
}

if ( ! function_exists( 'get_template_part' ) ) {
    function get_template_part( $slug, $name = null ) {
        $template_path = THEME_DIR . "/template-parts/{$slug}";
        if ( $name ) {
            $template_path .= "-{$name}";
        }
        $template_path .= '.php';
        
        return file_exists( $template_path );
    }
}

if ( ! function_exists( 'is_page_template' ) ) {
    function is_page_template( $template = '' ) {
        return true; // モック実装
    }
}

if ( ! function_exists( 'get_page_template_slug' ) ) {
    function get_page_template_slug( $post_id ) {
        return 'page-templates/page-skills.php'; // モック実装
    }
}

if ( ! function_exists( 'have_posts' ) ) {
    function have_posts() {
        return true; // モック実装
    }
}

if ( ! function_exists( 'the_post' ) ) {
    function the_post() {
        return true; // モック実装
    }
}

if ( ! function_exists( 'the_title' ) ) {
    function the_title() {
        echo 'Test Project Title';
    }
}

if ( ! function_exists( 'the_content' ) ) {
    function the_content() {
        echo 'Test project content';
    }
}

if ( ! function_exists( 'get_field' ) ) {
    function get_field( $field_name ) {
        $mock_fields = [
            'project_period' => '2024年1月〜3月',
            'project_role' => 'フルスタック開発者',
            'team_size' => '3',
            'project_challenges' => 'テストの課題',
            'project_solutions' => 'テストの解決策',
            'technical_details' => 'テストの技術詳細',
            'project_achievements' => 'テストの成果',
            'client_testimonial' => 'テストのお客様の声',
            'github_url' => 'https://github.com/test/project'
        ];
        
        return isset( $mock_fields[ $field_name ] ) ? $mock_fields[ $field_name ] : null;
    }
}

if ( ! function_exists( 'has_post_thumbnail' ) ) {
    function has_post_thumbnail() {
        return true; // モック実装
    }
}

if ( ! function_exists( 'the_post_thumbnail' ) ) {
    function the_post_thumbnail( $size = 'post-thumbnail', $attr = array() ) {
        echo '<img src="test-image.jpg" alt="Test" />';
    }
}

if ( ! function_exists( 'get_the_terms' ) ) {
    function get_the_terms( $post_id, $taxonomy ) {
        return [
            (object) [ 'name' => 'PHP', 'slug' => 'php' ],
            (object) [ 'name' => 'JavaScript', 'slug' => 'javascript' ]
        ];
    }
}

if ( ! function_exists( 'wp_get_post_terms' ) ) {
    function wp_get_post_terms( $post_id, $taxonomy, $args = array() ) {
        return [ 1, 2, 3 ]; // モック実装
    }
}

if ( ! function_exists( 'get_post_type_archive_link' ) ) {
    function get_post_type_archive_link( $post_type ) {
        return '/project/';
    }
}

if ( ! function_exists( 'get_previous_post' ) ) {
    function get_previous_post() {
        return (object) [
            'ID' => 123,
            'post_title' => 'Previous Project'
        ];
    }
}

if ( ! function_exists( 'get_next_post' ) ) {
    function get_next_post() {
        return (object) [
            'ID' => 125,
            'post_title' => 'Next Project'
        ];
    }
}

if ( ! function_exists( 'get_permalink' ) ) {
    function get_permalink( $post_id = null ) {
        return 'http://test.local/project/' . $post_id . '/';
    }
}

if ( ! function_exists( 'wp_reset_postdata' ) ) {
    function wp_reset_postdata() {
        return true;
    }
}

if ( ! function_exists( 'wp_delete_post' ) ) {
    function wp_delete_post( $post_id, $force_delete = false ) {
        return true;
    }
}

/**
 * テストヘルパー関数
 */

/**
 * テンプレートファイルの存在チェック
 * 
 * @param string $template_path テンプレートファイルのパス
 * @return bool
 */
function template_file_exists( $template_path ) {
    $full_path = THEME_DIR . '/' . ltrim( $template_path, '/' );
    return file_exists( $full_path ) && is_readable( $full_path );
}

/**
 * テンプレートファイルの内容取得
 * 
 * @param string $template_path テンプレートファイルのパス
 * @return string|false
 */
function get_template_content( $template_path ) {
    $full_path = THEME_DIR . '/' . ltrim( $template_path, '/' );
    
    if ( ! file_exists( $full_path ) ) {
        return false;
    }
    
    return file_get_contents( $full_path );
}

/**
 * テンプレートファイルの構文チェック
 * 
 * @param string $template_path テンプレートファイルのパス
 * @return array ['valid' => bool, 'errors' => array]
 */
function check_template_syntax( $template_path ) {
    $full_path = THEME_DIR . '/' . ltrim( $template_path, '/' );
    
    if ( ! file_exists( $full_path ) ) {
        return [
            'valid' => false,
            'errors' => [ 'File does not exist' ]
        ];
    }
    
    $output = [];
    $return_var = 0;
    exec( "php -l '{$full_path}' 2>&1", $output, $return_var );
    
    return [
        'valid' => $return_var === 0,
        'errors' => $return_var === 0 ? [] : $output
    ];
}

/**
 * WordPress関数の使用チェック
 * 
 * @param string $template_path テンプレートファイルのパス
 * @param array $functions チェックする関数名の配列
 * @return array 関数名をキーとした使用状況の配列
 */
function check_wp_functions_usage( $template_path, $functions ) {
    $content = get_template_content( $template_path );
    
    if ( $content === false ) {
        return [];
    }
    
    $usage = [];
    foreach ( $functions as $function ) {
        $usage[ $function ] = strpos( $content, $function ) !== false;
    }
    
    return $usage;
}

/**
 * エスケープ関数の使用チェック
 * 
 * @param string $template_path テンプレートファイルのパス
 * @return array エスケープ関数の使用状況
 */
function check_escape_functions_usage( $template_path ) {
    $content = get_template_content( $template_path );
    
    if ( $content === false ) {
        return [
            'has_output' => false,
            'has_escape' => false,
            'escape_functions' => []
        ];
    }
    
    $escape_functions = [
        'esc_html',
        'esc_attr',
        'esc_url',
        'wp_kses_post',
        'esc_js',
        'esc_textarea'
    ];
    
    $has_output = strpos( $content, 'echo' ) !== false || strpos( $content, 'print' ) !== false;
    $found_functions = [];
    
    foreach ( $escape_functions as $func ) {
        if ( strpos( $content, $func . '(' ) !== false ) {
            $found_functions[] = $func;
        }
    }
    
    return [
        'has_output' => $has_output,
        'has_escape' => ! empty( $found_functions ),
        'escape_functions' => $found_functions
    ];
}

/**
 * テンプレート階層のチェック
 * 
 * @param string $template_type テンプレートタイプ
 * @return array 利用可能なテンプレートファイル
 */
function check_template_hierarchy( $template_type ) {
    $hierarchies = [
        'single-project' => [
            'single-project.php',
            'single.php',
            'singular.php',
            'index.php'
        ],
        'page-skills' => [
            'page-templates/page-skills.php',
            'page-skills.php',
            'page.php',
            'singular.php',
            'index.php'
        ],
        'page-contact' => [
            'page-templates/page-contact.php',
            'page-contact.php',
            'page.php',
            'singular.php',
            'index.php'
        ]
    ];
    
    if ( ! isset( $hierarchies[ $template_type ] ) ) {
        return [];
    }
    
    $available = [];
    foreach ( $hierarchies[ $template_type ] as $template ) {
        if ( template_file_exists( $template ) ) {
            $available[] = $template;
        }
    }
    
    return $available;
}

/**
 * PHPUnit用のアサーションヘルパー
 */
class TestAssertionHelpers {
    
    /**
     * テンプレートファイルの存在をアサート
     */
    public static function assertTemplateExists( $test_case, $template_path, $message = '' ) {
        $test_case->assertFileExists(
            THEME_DIR . '/' . ltrim( $template_path, '/' ),
            $message ?: "Template {$template_path} should exist"
        );
    }
    
    /**
     * テンプレートファイルの構文をアサート
     */
    public static function assertTemplateValidSyntax( $test_case, $template_path, $message = '' ) {
        $result = check_template_syntax( $template_path );
        $test_case->assertTrue(
            $result['valid'],
            $message ?: "Template {$template_path} should have valid syntax: " . implode( ', ', $result['errors'] )
        );
    }
    
    /**
     * WordPress関数の使用をアサート
     */
    public static function assertUsesWpFunction( $test_case, $template_path, $function_name, $message = '' ) {
        $usage = check_wp_functions_usage( $template_path, [ $function_name ] );
        $test_case->assertTrue(
            isset( $usage[ $function_name ] ) && $usage[ $function_name ],
            $message ?: "Template {$template_path} should use function {$function_name}"
        );
    }
    
    /**
     * エスケープ関数の使用をアサート
     */
    public static function assertUsesEscapeFunctions( $test_case, $template_path, $message = '' ) {
        $usage = check_escape_functions_usage( $template_path );
        
        if ( $usage['has_output'] ) {
            $test_case->assertTrue(
                $usage['has_escape'],
                $message ?: "Template {$template_path} with output should use escape functions"
            );
        }
    }
}

/**
 * WordPress Test Suite互換のファクトリ関数
 */
if ( ! class_exists( 'WP_UnitTest_Factory' ) && ! isset( $GLOBALS['factory'] ) ) {
    
    class MockFactory {
        public $post;
        
        public function __construct() {
            $this->post = new MockPostFactory();
        }
    }
    
    class MockPostFactory {
        public function create( $args = array() ) {
            static $post_id = 1000;
            return ++$post_id;
        }
        
        public function create_many( $count, $args = array() ) {
            $ids = [];
            for ( $i = 0; $i < $count; $i++ ) {
                $ids[] = $this->create( $args );
            }
            return $ids;
        }
    }
    
    // グローバルファクトリの設定
    $GLOBALS['factory'] = new MockFactory();
}

/**
 * WordPress環境の検出
 */
function is_wordpress_available() {
    return function_exists( 'add_action' ) && 
           function_exists( 'wp_enqueue_script' ) &&
           ! defined( 'WP_TESTS_PHPUNIT_BOOTSTRAP' );
}

/**
 * テスト環境情報の出力
 */
function display_test_environment_info() {
    echo "\n=== Test Environment Info ===\n";
    echo "Theme Directory: " . THEME_DIR . "\n";
    echo "Tests Directory: " . THEME_TESTS_DIR . "\n";
    echo "WordPress Available: " . ( is_wordpress_available() ? 'Yes' : 'No' ) . "\n";
    echo "PHP Version: " . PHP_VERSION . "\n";
    echo "PHPUnit Bootstrap: " . ( defined( 'WP_TESTS_PHPUNIT_BOOTSTRAP' ) ? 'Yes' : 'No' ) . "\n";
    echo "==============================\n\n";
}
<?php
/**
 * 404エラー修正機能のテストランナー
 * 
 * Usage: php run-404-fix-tests.php
 */

// テスト環境の独立性確保
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', dirname( __FILE__ ) . '/../../../' );
}

// WordPressテスト環境の設定
$wp_tests_dir = getenv('WP_TESTS_DIR') ?: '/tmp/wordpress-tests-lib';
$wp_core_dir = getenv('WP_CORE_DIR') ?: '/tmp/wordpress/';

// テストDBプレフィックスを設定して本番DBとの分離を確保
if ( ! defined( 'WP_TESTS_TABLE_PREFIX' ) ) {
    define( 'WP_TESTS_TABLE_PREFIX', 'wptests_' . uniqid() . '_' );
}

if (!file_exists($wp_tests_dir . '/includes/functions.php')) {
    echo "WordPress test environment not found.\n";
    echo "Please install WordPress test environment first:\n";
    echo "bash bin/install-wp-tests.sh wordpress_test root '' localhost latest\n";
    exit(1);
}

// WordPressテストブートストラップ
require_once $wp_tests_dir . '/includes/functions.php';

/**
 * テーマの読み込み
 */
function _manually_load_theme() {
    // テーマのfunctions.phpを読み込み
    require dirname(__FILE__) . '/functions.php';
}
tests_add_filter('muplugins_loaded', '_manually_load_theme');

require $wp_tests_dir . '/includes/bootstrap.php';

// テストクラスの読み込み
require_once dirname(__FILE__) . '/tests/test-page-creator.php';

/**
 * 簡易テストランナー
 */
class Simple_Test_Runner {
    private $passed = 0;
    private $failed = 0;
    private $errors = array();

    public function run_tests() {
        echo "=== 404エラー修正機能テスト開始 ===\n\n";
        
        // テスト環境の初期化
        $this->setup_test_environment();

        try {
            // ページ作成機能のテスト
            $this->run_test_class('Test_Page_Creator');
            
            // CLIスクリプトのテスト
            $this->run_test_class('Test_Create_Pages_CLI');
            
            // 管理画面ツールのテスト
            $this->run_test_class('Test_Admin_Tools');
        } finally {
            // テスト環境のクリーンアップ
            $this->cleanup_test_environment();
        }

        $this->show_results();
    }
    
    private function setup_test_environment() {
        // テスト用のトランジェントをクリア
        if ( function_exists( 'delete_transient' ) ) {
            delete_transient( 'kei_portfolio_pages_check' );
        }
        
        // テスト用オプションをクリア
        if ( function_exists( 'delete_option' ) ) {
            delete_option( 'kei_portfolio_pages_created' );
        }
    }
    
    private function cleanup_test_environment() {
        // テスト終了時のクリーンアップ
        if ( function_exists( 'delete_transient' ) ) {
            delete_transient( 'kei_portfolio_pages_check' );
        }
        
        if ( function_exists( 'delete_option' ) ) {
            delete_option( 'kei_portfolio_pages_created' );
        }
    }

    private function run_test_class($class_name) {
        echo "--- {$class_name} テスト ---\n";
        
        if (!class_exists($class_name)) {
            echo "エラー: クラス {$class_name} が見つかりません\n";
            $this->failed++;
            return;
        }

        $test_instance = new $class_name();
        $reflection = new ReflectionClass($class_name);
        
        // setUpメソッドがあれば実行
        if (method_exists($test_instance, 'setUp')) {
            $test_instance->setUp();
        }

        $test_methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        
        foreach ($test_methods as $method) {
            if (strpos($method->getName(), 'test_') === 0) {
                $this->run_single_test($test_instance, $method->getName());
            }
        }
        
        // tearDownメソッドがあれば実行
        if (method_exists($test_instance, 'tearDown')) {
            $test_instance->tearDown();
        }
        
        echo "\n";
    }

    private function run_single_test($instance, $method_name) {
        try {
            $instance->$method_name();
            echo "✓ {$method_name}\n";
            $this->passed++;
        } catch (Exception $e) {
            echo "✗ {$method_name}: " . $e->getMessage() . "\n";
            $this->failed++;
            $this->errors[] = array(
                'test' => $method_name,
                'error' => $e->getMessage()
            );
        }
    }

    private function show_results() {
        echo "=== テスト結果 ===\n";
        echo "成功: {$this->passed}\n";
        echo "失敗: {$this->failed}\n";
        echo "合計: " . ($this->passed + $this->failed) . "\n\n";

        if (!empty($this->errors)) {
            echo "=== エラー詳細 ===\n";
            foreach ($this->errors as $error) {
                echo "- {$error['test']}: {$error['error']}\n";
            }
        }

        if ($this->failed === 0) {
            echo "🎉 全てのテストが成功しました！\n";
            exit(0);
        } else {
            echo "❌ 一部のテストが失敗しました。\n";
            exit(1);
        }
    }
}

/**
 * 基本的なアサーション機能
 */
if (!class_exists('WP_UnitTestCase')) {
    class WP_UnitTestCase {
        protected function assertTrue($condition, $message = '') {
            if (!$condition) {
                throw new Exception($message ?: 'Expected true, got false');
            }
        }

        protected function assertFalse($condition, $message = '') {
            if ($condition) {
                throw new Exception($message ?: 'Expected false, got true');
            }
        }

        protected function assertEquals($expected, $actual, $message = '') {
            if ($expected !== $actual) {
                throw new Exception($message ?: "Expected '{$expected}', got '{$actual}'");
            }
        }

        protected function assertNotNull($value, $message = '') {
            if ($value === null) {
                throw new Exception($message ?: 'Expected not null, got null');
            }
        }

        protected function assertIsInt($value, $message = '') {
            if (!is_int($value)) {
                throw new Exception($message ?: 'Expected integer, got ' . gettype($value));
            }
        }

        protected function assertIsArray($value, $message = '') {
            if (!is_array($value)) {
                throw new Exception($message ?: 'Expected array, got ' . gettype($value));
            }
        }

        protected function assertNotEmpty($value, $message = '') {
            if (empty($value)) {
                throw new Exception($message ?: 'Expected not empty, got empty');
            }
        }

        protected function assertArrayHasKey($key, $array, $message = '') {
            if (!array_key_exists($key, $array)) {
                throw new Exception($message ?: "Expected array to have key '{$key}'");
            }
        }

        protected function assertGreaterThan($expected, $actual, $message = '') {
            if ($actual <= $expected) {
                throw new Exception($message ?: "Expected {$actual} to be greater than {$expected}");
            }
        }

        protected function markTestSkipped($message) {
            echo "SKIPPED: {$message}\n";
        }

        // ファクトリメソッドのモック
        public $factory;

        public function __construct() {
            $this->factory = new stdClass();
            $this->factory->user = new stdClass();
            $this->factory->user->create = function($args = array()) {
                // テスト環境用の一意なユーザーID
                return 1000 + rand(1, 999);
            };
            
            // テスト開始時のクリーンアップ
            $this->cleanup_test_data();
        }
        
        protected function cleanup_test_data() {
            // 各テストで使用されるテストデータのクリーンアップ
            // この関数は個別のテストクラスでオーバーライド可能
        }

        public function setUp() {}
        public function tearDown() {}
    }
}

// WordPressの基本関数のモック（テスト環境で利用できない場合）
if (!function_exists('wp_insert_post')) {
    function wp_insert_post($args, $wp_error = false) {
        return rand(1, 1000); // ダミーのポストID
    }
}

if (!function_exists('get_page_by_path')) {
    function get_page_by_path($slug) {
        // テスト用のダミーページオブジェクト
        $page = new stdClass();
        $page->ID = rand(1, 1000);
        $page->post_title = ucfirst(str_replace('-', ' ', $slug));
        $page->post_name = $slug;
        return $page;
    }
}

// テスト実行
$runner = new Simple_Test_Runner();
$runner->run_tests();
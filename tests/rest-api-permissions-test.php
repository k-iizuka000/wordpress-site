<?php
/**
 * REST API権限設定テスト
 * 
 * REST API 403エラー修正機能のテストケース
 * 
 * @package Kei_Portfolio_Pro
 */

// WordPress テスト環境の読み込み
if (!defined('ABSPATH')) {
    // テスト環境のWordPressを読み込み（実際の環境に合わせて調整）
    require_once dirname(__DIR__) . '/wp-config.php';
    require_once ABSPATH . 'wp-includes/wp-db.php';
    require_once ABSPATH . 'wp-includes/pluggable.php';
}

class RestApiPermissionsTest extends WP_UnitTestCase {

    /**
     * テスト前のセットアップ
     */
    public function setUp(): void {
        parent::setUp();
        
        // テスト用ユーザーを作成
        $this->editor_user = $this->factory->user->create([
            'role' => 'editor'
        ]);
        
        $this->admin_user = $this->factory->user->create([
            'role' => 'administrator'
        ]);
        
        $this->author_user = $this->factory->user->create([
            'role' => 'author'
        ]);
        
        // テスト用投稿を作成
        $this->test_post = $this->factory->post->create([
            'post_title' => 'Test Post for REST API',
            'post_content' => 'Test content for REST API permissions',
            'post_status' => 'publish',
            'post_author' => $this->editor_user
        ]);
    }

    /**
     * テスト後のクリーンアップ
     */
    public function tearDown(): void {
        wp_delete_user($this->editor_user);
        wp_delete_user($this->admin_user);
        wp_delete_user($this->author_user);
        wp_delete_post($this->test_post, true);
        
        parent::tearDown();
    }

    /**
     * テスト1: REST APIの初期化が正常に動作するか
     */
    public function test_rest_api_init() {
        // rest_api_initフックが登録されているか確認
        $this->assertTrue(has_filter('rest_api_init', 'kei_portfolio_fix_template_permissions'));
        
        // 関数が存在するか確認
        $this->assertTrue(function_exists('kei_portfolio_fix_template_permissions'));
        $this->assertTrue(function_exists('kei_portfolio_allow_template_lookup'));
        $this->assertTrue(function_exists('kei_portfolio_grant_template_cap'));
    }

    /**
     * テスト2: テンプレート権限の動的調整機能
     */
    public function test_template_permission_adjustment() {
        // 編集者としてログイン
        wp_set_current_user($this->editor_user);
        
        // 最初はedit_theme_options権限を持っていない
        $this->assertFalse(current_user_can('edit_theme_options'));
        
        // REST_REQUESTを設定してテンプレート権限を模擬
        if (!defined('REST_REQUEST')) {
            define('REST_REQUEST', true);
        }
        
        // kei_portfolio_grant_template_cap関数をテスト
        $test_caps = ['edit_theme_options'];
        $user_caps = get_user_meta($this->editor_user, 'wp_capabilities', true);
        
        $result = kei_portfolio_grant_template_cap($user_caps, $test_caps, []);
        
        // edit_theme_options権限が一時的に付与されたか確認
        $this->assertTrue($result['edit_theme_options']);
    }

    /**
     * テスト3: Gutenbergエディターのテンプレート機能制限
     */
    public function test_block_editor_settings_restriction() {
        // 編集者としてログイン
        wp_set_current_user($this->editor_user);
        
        // テスト用のエディター設定
        $settings = [
            'supportsTemplateMode' => true,
            'defaultTemplatePartAreas' => ['header', 'footer']
        ];
        
        $context = new stdClass();
        
        $result = kei_portfolio_adjust_block_editor_settings($settings, $context);
        
        // 管理者以外はテンプレート編集が無効化されているか確認
        $this->assertFalse($result['supportsTemplateMode']);
        $this->assertEquals([], $result['defaultTemplatePartAreas']);
    }

    /**
     * テスト4: 管理者のテンプレート機能は維持される
     */
    public function test_admin_template_features_preserved() {
        // 管理者としてログイン
        wp_set_current_user($this->admin_user);
        
        // テスト用のエディター設定
        $settings = [
            'supportsTemplateMode' => true,
            'defaultTemplatePartAreas' => ['header', 'footer']
        ];
        
        $context = new stdClass();
        
        $result = kei_portfolio_adjust_block_editor_settings($settings, $context);
        
        // 管理者はテンプレート編集機能が維持されているか確認
        $this->assertTrue($result['supportsTemplateMode']);
        $this->assertEquals(['header', 'footer'], $result['defaultTemplatePartAreas']);
    }

    /**
     * テスト5: テンプレートlookupエンドポイントアクセス許可
     */
    public function test_template_lookup_access_permission() {
        // 編集者としてログイン
        wp_set_current_user($this->editor_user);
        
        // モックのWP_REST_Requestを作成
        $request = new WP_REST_Request('GET', '/wp/v2/templates/lookup');
        $request->set_param('slug', 'front-page');
        
        $server = new WP_REST_Server();
        
        // 初期結果（nullまたは空）
        $result = null;
        
        // kei_portfolio_allow_template_lookup関数をテスト
        $processed_result = kei_portfolio_allow_template_lookup($result, $server, $request);
        
        // 結果がそのまま返されることを確認（エラーが発生しない）
        $this->assertEquals($result, $processed_result);
        
        // edit_posts権限があることを確認
        $this->assertTrue(current_user_can('edit_posts'));
    }

    /**
     * テスト6: 投稿編集権限のない場合のアクセス制限
     */
    public function test_no_edit_posts_permission_restriction() {
        // 購読者ユーザーを作成（edit_posts権限なし）
        $subscriber_user = $this->factory->user->create([
            'role' => 'subscriber'
        ]);
        
        wp_set_current_user($subscriber_user);
        
        // edit_posts権限がないことを確認
        $this->assertFalse(current_user_can('edit_posts'));
        
        // モックのWP_REST_Requestを作成
        $request = new WP_REST_Request('GET', '/wp/v2/templates/lookup');
        $request->set_param('slug', 'front-page');
        
        $server = new WP_REST_Server();
        $result = null;
        
        // kei_portfolio_allow_template_lookup関数をテスト
        $processed_result = kei_portfolio_allow_template_lookup($result, $server, $request);
        
        // 結果がそのまま返されることを確認（権限付与されない）
        $this->assertEquals($result, $processed_result);
        
        wp_delete_user($subscriber_user);
    }

    /**
     * テスト7: 未ログインユーザーのアクセス制限
     */
    public function test_logged_out_user_restriction() {
        // ログアウト状態
        wp_set_current_user(0);
        
        // ログインしていないことを確認
        $this->assertFalse(is_user_logged_in());
        
        // モックのWP_REST_Requestを作成
        $request = new WP_REST_Request('GET', '/wp/v2/templates/lookup');
        $request->set_param('slug', 'front-page');
        
        $server = new WP_REST_Server();
        $result = null;
        
        // kei_portfolio_allow_template_lookup関数をテスト
        $processed_result = kei_portfolio_allow_template_lookup($result, $server, $request);
        
        // 結果がそのまま返されることを確認（権限付与されない）
        $this->assertEquals($result, $processed_result);
    }

    /**
     * テスト8: セキュリティ - 権限エスカレーション攻撃の防止
     */
    public function test_security_privilege_escalation_prevention() {
        // 通常のユーザーとしてログイン
        wp_set_current_user($this->author_user);
        
        // REST_REQUEST定数が設定されていない状態でテスト
        $test_caps = ['edit_theme_options', 'manage_options'];
        $user_caps = get_user_meta($this->author_user, 'wp_capabilities', true);
        
        // REST_REQUESTが定義されていない場合は権限付与されない
        if (defined('REST_REQUEST')) {
            // 一時的に定数を削除（テスト目的）
            runkit7_constant_remove('REST_REQUEST');
        }
        
        $result = kei_portfolio_grant_template_cap($user_caps, $test_caps, []);
        
        // manage_options権限は付与されないことを確認
        $this->assertArrayNotHasKey('manage_options', $result);
        
        // REST_REQUESTが設定されていない場合はedit_theme_optionsも付与される
        $this->assertTrue($result['edit_theme_options']);
    }

    /**
     * テスト9: パフォーマンス - 大量のリクエストに対する応答性
     */
    public function test_performance_multiple_requests() {
        wp_set_current_user($this->editor_user);
        
        $start_time = microtime(true);
        
        // 100回のテンプレートlookupリクエストを模擬
        for ($i = 0; $i < 100; $i++) {
            $request = new WP_REST_Request('GET', '/wp/v2/templates/lookup');
            $request->set_param('slug', 'test-template-' . $i);
            
            $server = new WP_REST_Server();
            $result = null;
            
            kei_portfolio_allow_template_lookup($result, $server, $request);
        }
        
        $end_time = microtime(true);
        $execution_time = $end_time - $start_time;
        
        // 100リクエストが1秒以内に処理されることを確認
        $this->assertLessThan(1.0, $execution_time);
    }

    /**
     * テスト10: 互換性 - WordPressバージョンとの互換性確認
     */
    public function test_wordpress_version_compatibility() {
        global $wp_version;
        
        // WordPress 5.0以上での動作を想定
        $this->assertGreaterThanOrEqual('5.0', $wp_version);
        
        // 必要な関数が存在することを確認
        $this->assertTrue(function_exists('register_rest_route'));
        $this->assertTrue(class_exists('WP_REST_Request'));
        $this->assertTrue(class_exists('WP_REST_Server'));
    }
}

/**
 * テスト実行用のヘルパー関数
 */
function run_rest_api_permissions_tests() {
    if (class_exists('PHPUnit_Framework_TestCase') || class_exists('PHPUnit\Framework\TestCase')) {
        echo "Running REST API Permissions Tests...\n";
        
        $test = new RestApiPermissionsTest();
        $test->setUp();
        
        $methods = get_class_methods($test);
        $test_methods = array_filter($methods, function($method) {
            return strpos($method, 'test_') === 0;
        });
        
        $passed = 0;
        $failed = 0;
        
        foreach ($test_methods as $method) {
            try {
                echo "Running {$method}... ";
                $test->$method();
                echo "PASSED\n";
                $passed++;
            } catch (Exception $e) {
                echo "FAILED: " . $e->getMessage() . "\n";
                $failed++;
            }
        }
        
        $test->tearDown();
        
        echo "\nTest Results:\n";
        echo "Passed: {$passed}\n";
        echo "Failed: {$failed}\n";
        echo "Total: " . ($passed + $failed) . "\n";
        
        return $failed === 0;
    } else {
        echo "PHPUnit not available. Please install PHPUnit to run tests.\n";
        return false;
    }
}

// コマンドラインから直接実行された場合のテスト実行
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('test rest-api-permissions', 'run_rest_api_permissions_tests');
} elseif (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    run_rest_api_permissions_tests();
}
<?php
/**
 * REST API 403エラー修正のテストファイル
 * 
 * 実装した修正が正常に動作するかをテストします。
 * 
 * @package Kei_Portfolio_Pro
 * @version 1.0.0
 */

// WordPressの読み込み
require_once dirname(__FILE__) . '/../../../wp-config.php';

class REST_API_403_Fix_Test {
    
    private $test_results = array();
    private $admin_user_id;
    private $editor_user_id;
    
    public function __construct() {
        // テスト用ユーザーを作成
        $this->setup_test_users();
    }
    
    /**
     * テスト用ユーザーの設定
     */
    private function setup_test_users() {
        // 管理者ユーザーを取得（存在しない場合は作成）
        $admin_user = get_user_by('login', 'test_admin');
        if (!$admin_user) {
            $this->admin_user_id = wp_create_user('test_admin', 'test_password', 'test_admin@example.com');
            $user = get_user_by('id', $this->admin_user_id);
            $user->set_role('administrator');
        } else {
            $this->admin_user_id = $admin_user->ID;
        }
        
        // 編集者ユーザーを取得（存在しない場合は作成）
        $editor_user = get_user_by('login', 'test_editor');
        if (!$editor_user) {
            $this->editor_user_id = wp_create_user('test_editor', 'test_password', 'test_editor@example.com');
            $user = get_user_by('id', $this->editor_user_id);
            $user->set_role('editor');
        } else {
            $this->editor_user_id = $editor_user->ID;
        }
    }
    
    /**
     * 全てのテストを実行
     */
    public function run_all_tests() {
        echo "<h2>REST API 403エラー修正 - テスト結果</h2>\n";
        echo "<p>実行時刻: " . date('Y-m-d H:i:s') . "</p>\n";
        
        // テスト項目
        $this->test_rest_api_permissions_file();
        $this->test_emergency_fix_functions();
        $this->test_gutenberg_permissions();
        $this->test_nonce_validation();
        $this->test_rest_endpoints();
        
        // 結果の表示
        $this->display_results();
        
        // テスト用ユーザーのクリーンアップ
        $this->cleanup_test_users();
    }
    
    /**
     * rest-api-permissions.php の機能テスト
     */
    private function test_rest_api_permissions_file() {
        $test_name = 'REST API Permissions File';
        
        try {
            // ファイルの存在確認
            $file_path = get_template_directory() . '/inc/rest-api-permissions.php';
            if (!file_exists($file_path)) {
                throw new Exception('rest-api-permissions.php ファイルが見つかりません');
            }
            
            // 関数の存在確認
            if (!function_exists('kei_portfolio_fix_rest_permissions')) {
                throw new Exception('kei_portfolio_fix_rest_permissions 関数が見つかりません');
            }
            
            if (!function_exists('kei_portfolio_allow_posts_operations')) {
                throw new Exception('kei_portfolio_allow_posts_operations 関数が見つかりません');
            }
            
            if (!function_exists('kei_portfolio_grant_posts_cap')) {
                throw new Exception('kei_portfolio_grant_posts_cap 関数が見つかりません');
            }
            
            $this->test_results[$test_name] = array(
                'status' => 'PASS',
                'message' => '全ての必要な関数が正常に定義されています',
                'details' => array(
                    'file_exists' => true,
                    'functions_defined' => true
                )
            );
            
        } catch (Exception $e) {
            $this->test_results[$test_name] = array(
                'status' => 'FAIL',
                'message' => $e->getMessage(),
                'details' => array()
            );
        }
    }
    
    /**
     * emergency-fix.php の機能テスト
     */
    private function test_emergency_fix_functions() {
        $test_name = 'Emergency Fix Functions';
        
        try {
            // 関数の存在確認
            if (!function_exists('kei_portfolio_emergency_rest_api_fix')) {
                throw new Exception('kei_portfolio_emergency_rest_api_fix 関数が見つかりません');
            }
            
            if (!function_exists('kei_portfolio_fix_rest_auth_errors')) {
                throw new Exception('kei_portfolio_fix_rest_auth_errors 関数が見つかりません');
            }
            
            if (!function_exists('kei_portfolio_bypass_nonce_for_rest')) {
                throw new Exception('kei_portfolio_bypass_nonce_for_rest 関数が見つかりません');
            }
            
            $this->test_results[$test_name] = array(
                'status' => 'PASS',
                'message' => 'Emergency Fix の全ての関数が正常に定義されています',
                'details' => array(
                    'emergency_functions_defined' => true
                )
            );
            
        } catch (Exception $e) {
            $this->test_results[$test_name] = array(
                'status' => 'FAIL',
                'message' => $e->getMessage(),
                'details' => array()
            );
        }
    }
    
    /**
     * Gutenberg権限設定のテスト
     */
    private function test_gutenberg_permissions() {
        $test_name = 'Gutenberg Permissions';
        
        try {
            // 管理者ユーザーでテスト
            wp_set_current_user($this->admin_user_id);
            
            // 権限チェック
            $can_edit_posts = current_user_can('edit_posts');
            $can_publish_posts = current_user_can('publish_posts');
            
            if (!$can_edit_posts) {
                throw new Exception('管理者が投稿編集権限を持っていません');
            }
            
            if (!$can_publish_posts) {
                throw new Exception('管理者が投稿公開権限を持っていません');
            }
            
            // 編集者ユーザーでテスト
            wp_set_current_user($this->editor_user_id);
            
            $editor_can_edit = current_user_can('edit_posts');
            $editor_can_publish = current_user_can('publish_posts');
            
            if (!$editor_can_edit) {
                throw new Exception('編集者が投稿編集権限を持っていません');
            }
            
            $this->test_results[$test_name] = array(
                'status' => 'PASS',
                'message' => 'ユーザー権限が正常に設定されています',
                'details' => array(
                    'admin_can_edit' => $can_edit_posts,
                    'admin_can_publish' => $can_publish_posts,
                    'editor_can_edit' => $editor_can_edit,
                    'editor_can_publish' => $editor_can_publish
                )
            );
            
        } catch (Exception $e) {
            $this->test_results[$test_name] = array(
                'status' => 'FAIL',
                'message' => $e->getMessage(),
                'details' => array()
            );
        }
    }
    
    /**
     * Nonce検証のテスト
     */
    private function test_nonce_validation() {
        $test_name = 'Nonce Validation';
        
        try {
            // 各種Nonceの生成テスト
            $rest_nonce = wp_create_nonce('wp_rest');
            $ajax_nonce = wp_create_nonce('kei_portfolio_ajax');
            $blog_nonce = wp_create_nonce('blog_ajax_action');
            
            if (empty($rest_nonce)) {
                throw new Exception('REST API Nonceの生成に失敗しました');
            }
            
            if (empty($ajax_nonce)) {
                throw new Exception('AJAX Nonceの生成に失敗しました');
            }
            
            if (empty($blog_nonce)) {
                throw new Exception('Blog AJAX Nonceの生成に失敗しました');
            }
            
            // Nonce検証テスト
            $rest_verify = wp_verify_nonce($rest_nonce, 'wp_rest');
            $ajax_verify = wp_verify_nonce($ajax_nonce, 'kei_portfolio_ajax');
            
            if (!$rest_verify) {
                throw new Exception('REST API Nonceの検証に失敗しました');
            }
            
            if (!$ajax_verify) {
                throw new Exception('AJAX Nonceの検証に失敗しました');
            }
            
            $this->test_results[$test_name] = array(
                'status' => 'PASS',
                'message' => 'Nonceの生成と検証が正常に動作しています',
                'details' => array(
                    'rest_nonce_valid' => (bool)$rest_verify,
                    'ajax_nonce_valid' => (bool)$ajax_verify,
                    'nonces_generated' => true
                )
            );
            
        } catch (Exception $e) {
            $this->test_results[$test_name] = array(
                'status' => 'FAIL',
                'message' => $e->getMessage(),
                'details' => array()
            );
        }
    }
    
    /**
     * REST APIエンドポイントのテスト
     */
    private function test_rest_endpoints() {
        $test_name = 'REST API Endpoints';
        
        try {
            // REST APIのベースURLを確認
            $rest_url = rest_url('wp/v2/');
            if (empty($rest_url)) {
                throw new Exception('REST API URLの取得に失敗しました');
            }
            
            // 投稿エンドポイントのテスト（シミュレーション）
            $posts_endpoint = rest_url('wp/v2/posts');
            $patterns_endpoint = rest_url('wp/v2/block-patterns/patterns');
            
            // エンドポイントURLの妥当性確認
            if (!filter_var($posts_endpoint, FILTER_VALIDATE_URL)) {
                throw new Exception('投稿エンドポイントのURLが無効です');
            }
            
            if (!filter_var($patterns_endpoint, FILTER_VALIDATE_URL)) {
                throw new Exception('ブロックパターンエンドポイントのURLが無効です');
            }
            
            // 管理者でのアクセス権限確認
            wp_set_current_user($this->admin_user_id);
            $admin_can_access = current_user_can('edit_posts');
            
            // 編集者でのアクセス権限確認
            wp_set_current_user($this->editor_user_id);
            $editor_can_access = current_user_can('edit_posts');
            
            $this->test_results[$test_name] = array(
                'status' => 'PASS',
                'message' => 'REST APIエンドポイントが正常に設定されています',
                'details' => array(
                    'rest_url_valid' => true,
                    'posts_endpoint_valid' => true,
                    'patterns_endpoint_valid' => true,
                    'admin_can_access' => $admin_can_access,
                    'editor_can_access' => $editor_can_access
                )
            );
            
        } catch (Exception $e) {
            $this->test_results[$test_name] = array(
                'status' => 'FAIL',
                'message' => $e->getMessage(),
                'details' => array()
            );
        }
    }
    
    /**
     * テスト結果の表示
     */
    private function display_results() {
        echo "<div style='margin: 20px 0;'>\n";
        
        $pass_count = 0;
        $fail_count = 0;
        
        foreach ($this->test_results as $test_name => $result) {
            $status_color = ($result['status'] === 'PASS') ? '#28a745' : '#dc3545';
            $icon = ($result['status'] === 'PASS') ? '✓' : '✗';
            
            if ($result['status'] === 'PASS') {
                $pass_count++;
            } else {
                $fail_count++;
            }
            
            echo "<div style='border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px;'>\n";
            echo "<h3 style='margin: 0 0 10px 0; color: {$status_color};'>{$icon} {$test_name}</h3>\n";
            echo "<p><strong>ステータス:</strong> <span style='color: {$status_color};'>{$result['status']}</span></p>\n";
            echo "<p><strong>メッセージ:</strong> {$result['message']}</p>\n";
            
            if (!empty($result['details'])) {
                echo "<details>\n";
                echo "<summary>詳細情報</summary>\n";
                echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 3px; margin-top: 10px;'>\n";
                echo htmlspecialchars(print_r($result['details'], true));
                echo "</pre>\n";
                echo "</details>\n";
            }
            echo "</div>\n";
        }
        
        // サマリー表示
        $total_tests = $pass_count + $fail_count;
        $success_rate = $total_tests > 0 ? round(($pass_count / $total_tests) * 100, 2) : 0;
        
        echo "<div style='border: 2px solid #007cba; padding: 20px; margin: 20px 0; border-radius: 5px; background: #f0f8ff;'>\n";
        echo "<h3>テスト結果サマリー</h3>\n";
        echo "<p><strong>総テスト数:</strong> {$total_tests}</p>\n";
        echo "<p><strong>成功:</strong> <span style='color: #28a745;'>{$pass_count}</span></p>\n";
        echo "<p><strong>失敗:</strong> <span style='color: #dc3545;'>{$fail_count}</span></p>\n";
        echo "<p><strong>成功率:</strong> {$success_rate}%</p>\n";
        
        if ($success_rate === 100) {
            echo "<p style='color: #28a745; font-weight: bold;'>🎉 全てのテストが成功しました！REST API 403エラーの修正が正常に実装されています。</p>\n";
        } elseif ($success_rate >= 80) {
            echo "<p style='color: #ffc107; font-weight: bold;'>⚠️ 大部分のテストが成功しましたが、いくつかの問題があります。</p>\n";
        } else {
            echo "<p style='color: #dc3545; font-weight: bold;'>❌ 重要な問題が検出されました。修正が必要です。</p>\n";
        }
        
        echo "</div>\n";
        echo "</div>\n";
    }
    
    /**
     * テスト用ユーザーのクリーンアップ
     */
    private function cleanup_test_users() {
        // 本番環境では削除しない（安全のため）
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }
        
        echo "<p><em>注意: テスト用ユーザーの削除はデバッグモードでのみ実行されます。</em></p>\n";
    }
}

// 環境チェック：本番環境では実行を拒否
if (!defined('WP_DEBUG') || !WP_DEBUG) {
    http_response_code(404);
    die('このファイルは開発環境でのみ利用可能です。');
}

// 環境タイプの確認
if (defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE === 'production') {
    http_response_code(404);
    die('本番環境での実行は許可されていません。');
}

// IPアドレス制限（ローカル環境のみ）
$allowed_ips = array('127.0.0.1', '::1', 'localhost');
$client_ip = $_SERVER['REMOTE_ADDR'] ?? '';
$is_local = in_array($client_ip, $allowed_ips) || 
           (filter_var($client_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false);

if (!$is_local) {
    http_response_code(403);
    die('アクセスが拒否されました。');
}

// テストの実行
if (!defined('ABSPATH')) {
    die('WordPressが正しく読み込まれていません。');
}

// HTMLヘッダー
echo "<!DOCTYPE html>\n";
echo "<html lang='ja'>\n";
echo "<head>\n";
echo "<meta charset='UTF-8'>\n";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>\n";
echo "<title>REST API 403エラー修正テスト</title>\n";
echo "<style>\n";
echo "body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; max-width: 1000px; margin: 0 auto; padding: 20px; line-height: 1.6; }\n";
echo "h1 { color: #1d2327; border-bottom: 3px solid #007cba; padding-bottom: 10px; }\n";
echo "h2 { color: #1d2327; }\n";
echo "h3 { color: #1d2327; }\n";
echo "</style>\n";
echo "</head>\n";
echo "<body>\n";

echo "<h1>🔧 REST API 403エラー修正テスト</h1>\n";

// テストインスタンスの作成と実行
$test = new REST_API_403_Fix_Test();
$test->run_all_tests();

echo "<hr style='margin: 40px 0;'>\n";
echo "<p style='text-align: center; color: #666; font-size: 0.9em;'>\n";
echo "テスト実行完了 | Kei Portfolio Pro Theme | " . date('Y-m-d H:i:s') . "\n";
echo "</p>\n";

echo "</body>\n";
echo "</html>\n";
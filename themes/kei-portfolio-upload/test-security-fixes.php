<?php
/**
 * セキュリティ修正のテストスクリプト
 * 
 * @package Kei_Portfolio_Pro
 * @version 1.0.0
 * @since 2025-08-09
 */

// WordPressの読み込み（テスト用に簡略化）
if (!defined('ABSPATH')) {
    // テスト実行用にget_template_directory関数をエミュレート
    function get_template_directory() {
        return dirname(__FILE__);
    }
}

/**
 * セキュリティ修正テストクラス
 */
class Kei_Portfolio_Security_Fix_Test {
    
    private $test_results = array();
    private $passed_tests = 0;
    private $failed_tests = 0;
    
    /**
     * すべてのテストを実行
     */
    public function run_all_tests() {
        echo "=== Kei Portfolio セキュリティ修正テスト ===\n";
        echo "実行日時: " . date('Y-m-d H:i:s') . "\n\n";
        
        // 1. Nonce バイパス機能の削除テスト
        $this->test_nonce_bypass_removal();
        
        // 2. フルパス記録の修正テスト
        $this->test_full_path_logging_fix();
        
        // 3. 過度な権限付与の削除テスト
        $this->test_excessive_permission_removal();
        
        // 4. セキュアNonce機能のテスト
        $this->test_secure_nonce_implementation();
        
        // 5. セキュリティヘッダーのテスト
        $this->test_security_headers();
        
        // 結果の表示
        $this->display_results();
        
        return $this->failed_tests === 0;
    }
    
    /**
     * Nonce バイパス機能の削除をテスト
     */
    private function test_nonce_bypass_removal() {
        $test_name = "Nonce バイパス機能の削除";
        
        // emergency-fix.php ファイルの内容を確認
        $emergency_fix_file = get_template_directory() . '/emergency-fix.php';
        
        if (file_exists($emergency_fix_file)) {
            $content = file_get_contents($emergency_fix_file);
            
            // バイパス機能が削除されているかチェック
            $bypass_function_exists = strpos($content, 'kei_portfolio_bypass_nonce_for_rest') !== false;
            $dangerous_bypass_logic = strpos($content, 'return 1; // Nonce検証成功を示す') !== false;
            
            if (!$bypass_function_exists && !$dangerous_bypass_logic) {
                $this->add_test_result($test_name, true, "Nonceバイパス機能が正常に削除されています");
            } else {
                $this->add_test_result($test_name, false, "Nonceバイパス機能が残存している可能性があります");
            }
        } else {
            $this->add_test_result($test_name, false, "emergency-fix.phpファイルが見つかりません");
        }
    }
    
    /**
     * フルパス記録の修正をテスト
     */
    private function test_full_path_logging_fix() {
        $test_name = "フルパス記録の修正";
        
        $emergency_fix_file = get_template_directory() . '/emergency-fix.php';
        
        if (file_exists($emergency_fix_file)) {
            $content = file_get_contents($emergency_fix_file);
            
            // 危険なフルパス記録パターンをチェック
            $dangerous_patterns = array(
                'error_log("Emergency Fix: Headers already sent at $filename:$linenum")',
                'error_log("Emergency Fix: Log directory not writable: $log_dir")',
                'error_log(\'Emergency Fix: Log file - \' . $log_path)'
            );
            
            $found_dangerous_patterns = 0;
            foreach ($dangerous_patterns as $pattern) {
                if (strpos($content, $pattern) !== false) {
                    $found_dangerous_patterns++;
                }
            }
            
            if ($found_dangerous_patterns === 0) {
                $this->add_test_result($test_name, true, "フルパス記録が適切に修正されています");
            } else {
                $this->add_test_result($test_name, false, "フルパス記録の危険なパターンが {$found_dangerous_patterns} 件残存しています");
            }
        } else {
            $this->add_test_result($test_name, false, "emergency-fix.phpファイルが見つかりません");
        }
    }
    
    /**
     * 過度な権限付与の削除をテスト
     */
    private function test_excessive_permission_removal() {
        $test_name = "過度な権限付与の削除";
        
        $permissions_file = get_template_directory() . '/inc/rest-api-permissions.php';
        
        if (file_exists($permissions_file)) {
            $content = file_get_contents($permissions_file);
            
            // edit_others_posts権限が削除されているかチェック
            $has_edit_others_posts = strpos($content, "'edit_others_posts'") !== false;
            
            if (!$has_edit_others_posts) {
                $this->add_test_result($test_name, true, "edit_others_posts権限が削除されています");
            } else {
                $this->add_test_result($test_name, false, "edit_others_posts権限が残存しています");
            }
        } else {
            $this->add_test_result($test_name, false, "rest-api-permissions.phpファイルが見つかりません");
        }
    }
    
    /**
     * セキュアNonce実装をテスト
     */
    private function test_secure_nonce_implementation() {
        $test_name = "セキュアNonce機能の実装";
        
        $secure_nonce_file = get_template_directory() . '/inc/secure-nonce-handler.php';
        
        if (file_exists($secure_nonce_file)) {
            $content = file_get_contents($secure_nonce_file);
            
            // 重要なセキュア機能が実装されているかチェック
            $required_features = array(
                'class Kei_Portfolio_Secure_Nonce',
                'authenticate_rest_request',
                'verify_ajax_nonce',
                'verify_form_nonce',
                'set_security_headers'
            );
            
            $implemented_features = 0;
            foreach ($required_features as $feature) {
                if (strpos($content, $feature) !== false) {
                    $implemented_features++;
                }
            }
            
            if ($implemented_features === count($required_features)) {
                $this->add_test_result($test_name, true, "セキュアNonce機能が完全に実装されています");
            } else {
                $missing = count($required_features) - $implemented_features;
                $this->add_test_result($test_name, false, "{$missing} 個の必須機能が不足しています");
            }
        } else {
            $this->add_test_result($test_name, false, "secure-nonce-handler.phpファイルが見つかりません");
        }
    }
    
    /**
     * セキュリティヘッダーのテスト
     */
    private function test_security_headers() {
        $test_name = "セキュリティヘッダーの実装";
        
        $secure_nonce_file = get_template_directory() . '/inc/secure-nonce-handler.php';
        
        if (file_exists($secure_nonce_file)) {
            $content = file_get_contents($secure_nonce_file);
            
            // セキュリティヘッダーが設定されているかチェック
            $security_headers = array(
                'X-Content-Type-Options: nosniff',
                'X-Frame-Options: SAMEORIGIN',
                'X-XSS-Protection: 1; mode=block',
                'Referrer-Policy: strict-origin-when-cross-origin'
            );
            
            $implemented_headers = 0;
            foreach ($security_headers as $header) {
                if (strpos($content, $header) !== false) {
                    $implemented_headers++;
                }
            }
            
            if ($implemented_headers === count($security_headers)) {
                $this->add_test_result($test_name, true, "全てのセキュリティヘッダーが実装されています");
            } else {
                $missing = count($security_headers) - $implemented_headers;
                $this->add_test_result($test_name, false, "{$missing} 個のセキュリティヘッダーが不足しています");
            }
        } else {
            $this->add_test_result($test_name, false, "secure-nonce-handler.phpファイルが見つかりません");
        }
    }
    
    /**
     * テスト結果を追加
     */
    private function add_test_result($test_name, $passed, $message) {
        $this->test_results[] = array(
            'name' => $test_name,
            'passed' => $passed,
            'message' => $message
        );
        
        if ($passed) {
            $this->passed_tests++;
        } else {
            $this->failed_tests++;
        }
    }
    
    /**
     * テスト結果を表示
     */
    private function display_results() {
        echo "\n=== テスト結果 ===\n";
        
        foreach ($this->test_results as $result) {
            $status = $result['passed'] ? '[PASS]' : '[FAIL]';
            echo "{$status} {$result['name']}: {$result['message']}\n";
        }
        
        echo "\n=== サマリー ===\n";
        echo "合格: {$this->passed_tests}\n";
        echo "不合格: {$this->failed_tests}\n";
        echo "合計: " . count($this->test_results) . "\n";
        
        if ($this->failed_tests === 0) {
            echo "\n✅ 全てのセキュリティ修正が正常に完了しています！\n";
        } else {
            echo "\n❌ {$this->failed_tests} 件の問題が見つかりました。修正を確認してください。\n";
        }
    }
}

// テスト実行（コマンドラインから直接実行された場合のみ）
if (php_sapi_name() === 'cli' && isset($argv) && basename($argv[0]) === basename(__FILE__)) {
    $test = new Kei_Portfolio_Security_Fix_Test();
    $success = $test->run_all_tests();
    
    // 終了コード設定
    exit($success ? 0 : 1);
}
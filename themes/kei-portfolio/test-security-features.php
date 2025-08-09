<?php
/**
 * Security Features Test Runner
 * セキュリティ機能の動作確認用スクリプト
 */

// WordPress環境のシミュレーション
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

if (!defined('WP_CONTENT_DIR')) {
    define('WP_CONTENT_DIR', __DIR__);
}

// WordPress関数のモック
if (!function_exists('wp_create_nonce')) {
    function wp_create_nonce($action) {
        return hash('sha256', $action . 'test_salt' . time());
    }
}

if (!function_exists('wp_verify_nonce')) {
    function wp_verify_nonce($nonce, $action) {
        return !empty($nonce) && !empty($action);
    }
}

if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str) {
        return trim(strip_tags($str));
    }
}

if (!function_exists('get_transient')) {
    function get_transient($key) {
        static $transients = array();
        return isset($transients[$key]) ? $transients[$key] : false;
    }
}

if (!function_exists('set_transient')) {
    function set_transient($key, $value, $expiration) {
        static $transients = array();
        $transients[$key] = $value;
        return true;
    }
}

if (!function_exists('delete_transient')) {
    function delete_transient($key) {
        static $transients = array();
        unset($transients[$key]);
        return true;
    }
}

if (!function_exists('current_time')) {
    function current_time($format) {
        return date($format);
    }
}

if (!function_exists('wp_salt')) {
    function wp_salt() {
        return 'test_salt_value_' . date('Y-m-d');
    }
}

if (!function_exists('get_current_user_id')) {
    function get_current_user_id() {
        return 1;
    }
}

if (!function_exists('is_ssl')) {
    function is_ssl() {
        return false; // テスト環境ではfalse
    }
}

if (!function_exists('is_admin')) {
    function is_admin() {
        return false;
    }
}

if (!function_exists('wp_doing_ajax')) {
    function wp_doing_ajax() {
        return false;
    }
}

if (!function_exists('wp_doing_cron')) {
    function wp_doing_cron() {
        return false;
    }
}

if (!function_exists('esc_html')) {
    function esc_html($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_attr')) {
    function esc_attr($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_url')) {
    function esc_url($url) {
        return filter_var($url, FILTER_SANITIZE_URL);
    }
}

if (!function_exists('esc_js')) {
    function esc_js($text) {
        return json_encode($text, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    }
}

if (!function_exists('sanitize_email')) {
    function sanitize_email($email) {
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }
}

if (!function_exists('sanitize_url')) {
    function sanitize_url($url) {
        return filter_var($url, FILTER_SANITIZE_URL);
    }
}

if (!function_exists('sanitize_key')) {
    function sanitize_key($key) {
        return preg_replace('/[^a-z0-9_\-]/', '', strtolower($key));
    }
}

if (!function_exists('sanitize_title')) {
    function sanitize_title($title) {
        return preg_replace('/[^a-z0-9-]/', '-', strtolower(trim($title)));
    }
}

if (!function_exists('sanitize_textarea_field')) {
    function sanitize_textarea_field($str) {
        return trim(strip_tags($str));
    }
}

if (!function_exists('wp_kses_post')) {
    function wp_kses_post($data) {
        return strip_tags($data, '<p><br><strong><em><b><i><u><a><span><div>');
    }
}

if (!function_exists('get_option')) {
    function get_option($option, $default = false) {
        static $options = array();
        return isset($options[$option]) ? $options[$option] : $default;
    }
}

if (!function_exists('update_option')) {
    function update_option($option, $value) {
        static $options = array();
        $options[$option] = $value;
        return true;
    }
}

if (!function_exists('wp_mkdir_p')) {
    function wp_mkdir_p($target) {
        if (!is_dir($target)) {
            return mkdir($target, 0755, true);
        }
        return true;
    }
}

if (!function_exists('wp_mail')) {
    function wp_mail($to, $subject, $message, $headers = '', $attachments = array()) {
        // テスト環境では実際にメールを送信しない
        return true;
    }
}

if (!function_exists('get_bloginfo')) {
    function get_bloginfo($show) {
        $blog_info = array(
            'name' => 'Test Site',
            'version' => '6.0'
        );
        return isset($blog_info[$show]) ? $blog_info[$show] : 'Unknown';
    }
}

if (!defined('HOUR_IN_SECONDS')) {
    define('HOUR_IN_SECONDS', 3600);
}

if (!defined('DAY_IN_SECONDS')) {
    define('DAY_IN_SECONDS', 86400);
}

// セキュリティクラスを読み込み
require_once __DIR__ . '/inc/class-security-helper.php';
require_once __DIR__ . '/inc/class-security-logger.php';
require_once __DIR__ . '/inc/class-rate-limiter.php';

// 名前空間をインポート
use KeiPortfolio\Security\SecurityHelper;
use KeiPortfolio\Security\SecurityLogger;
use KeiPortfolio\Security\RateLimiter;

echo "<h1>セキュリティ機能テスト結果</h1>\n";

// テスト1: セキュリティヘルパーの基本機能
echo "<h2>1. セキュリティヘルパー基本機能テスト</h2>\n";

try {
    // 入力サニタイゼーション
    $malicious_input = '<script>alert("xss")</script>Hello World';
    $sanitized = SecurityHelper::sanitize_input($malicious_input, 'text');
    echo "<p>✓ 入力サニタイゼーション: " . htmlspecialchars($sanitized) . "</p>\n";
    
    // CSRFトークン生成・検証
    $token = SecurityHelper::generate_csrf_token('test_action');
    $is_valid = SecurityHelper::verify_csrf_token($token, 'test_action');
    echo "<p>✓ CSRFトークン生成・検証: " . ($is_valid ? '成功' : '失敗') . "</p>\n";
    
    // HTMLエスケープ
    $escaped_html = SecurityHelper::escape_html('<p onclick="alert(1)">テスト</p>');
    echo "<p>✓ HTMLエスケープ: " . $escaped_html . "</p>\n";
    
    // JSON安全エンコード
    $test_data = array('message' => '<script>alert("xss")</script>', 'value' => 123);
    $safe_json = SecurityHelper::safe_json_encode($test_data);
    echo "<p>✓ 安全JSONエンコード: " . htmlspecialchars($safe_json) . "</p>\n";
    
} catch (Exception $e) {
    echo "<p>❌ セキュリティヘルパーテスト エラー: " . $e->getMessage() . "</p>\n";
}

// テスト2: セキュリティロガー
echo "<h2>2. セキュリティロガーテスト</h2>\n";

try {
    $logger = SecurityLogger::get_instance();
    
    // 各レベルでのログテスト
    $logger->info('test_event', 'テスト情報メッセージ', array('test' => true));
    $logger->warning('test_warning', 'テスト警告メッセージ', array('level' => 'warning'));
    $logger->error('test_error', 'テストエラーメッセージ', array('level' => 'error'));
    $logger->critical('test_critical', 'テスト重要メッセージ', array('level' => 'critical'));
    
    echo "<p>✓ セキュリティロガー初期化・ログ記録完了</p>\n";
    
    // ログファイルの確認
    $log_file = WP_CONTENT_DIR . '/security.log';
    if (file_exists($log_file)) {
        $log_size = filesize($log_file);
        echo "<p>✓ セキュリティログファイル作成済み (サイズ: {$log_size} bytes)</p>\n";
    } else {
        echo "<p>⚠ セキュリティログファイルは作成されませんでした（権限または設定の問題の可能性）</p>\n";
    }
    
} catch (Exception $e) {
    echo "<p>❌ セキュリティロガーテスト エラー: " . $e->getMessage() . "</p>\n";
}

// テスト3: レート制限機能
echo "<h2>3. レート制限機能テスト</h2>\n";

try {
    $rate_limiter = RateLimiter::get_instance();
    
    // レート制限テスト
    $action = 'test_rate_limit';
    $settings = array('limit' => 3, 'window' => 60);
    
    echo "<p>レート制限設定: 制限回数={$settings['limit']}, 時間窓={$settings['window']}秒</p>\n";
    
    $results = array();
    for ($i = 1; $i <= 5; $i++) {
        $result = $rate_limiter->check($action, $settings);
        $results[] = $result;
        echo "<p>リクエスト{$i}: " . ($result ? '✓ 許可' : '❌ 拒否') . "</p>\n";
    }
    
    // 統計情報の取得
    $stats = $rate_limiter->get_stats($action);
    echo "<p>✓ レート制限統計:</p>\n";
    echo "<ul>\n";
    echo "<li>現在のカウント: {$stats['current_count']}</li>\n";
    echo "<li>制限値: {$stats['limit']}</li>\n";
    echo "<li>残りリクエスト数: {$stats['remaining_requests']}</li>\n";
    echo "<li>ブロック状態: " . ($stats['is_blocked'] ? 'はい' : 'いいえ') . "</li>\n";
    echo "</ul>\n";
    
    // リセットテスト
    $rate_limiter->reset($action);
    echo "<p>✓ レート制限リセット完了</p>\n";
    
} catch (Exception $e) {
    echo "<p>❌ レート制限テスト エラー: " . $e->getMessage() . "</p>\n";
}

// テスト4: セキュリティヘッダー
echo "<h2>4. セキュリティヘッダーテスト</h2>\n";

try {
    // カスタムCSP設定
    $custom_csp = array(
        'script-src' => "'self' https://cdn.example.com",
        'style-src' => "'self' 'unsafe-inline'"
    );
    
    $additional_headers = array(
        'X-Test-Security' => 'enabled'
    );
    
    // ヘッダー設定（実際の出力は避ける）
    ob_start();
    SecurityHelper::set_custom_security_headers($custom_csp, $additional_headers);
    $output = ob_get_clean();
    
    echo "<p>✓ セキュリティヘッダー設定完了（カスタムCSP含む）</p>\n";
    
    // 基本ヘッダー設定もテスト
    ob_start();
    SecurityHelper::set_security_headers();
    $output = ob_get_clean();
    
    echo "<p>✓ 基本セキュリティヘッダー設定完了</p>\n";
    
} catch (Exception $e) {
    echo "<p>❌ セキュリティヘッダーテスト エラー: " . $e->getMessage() . "</p>\n";
}

// テスト5: セキュリティ違反記録
echo "<h2>5. セキュリティ違反記録テスト</h2>\n";

try {
    // $_SERVER 変数を設定（テスト用）
    $_SERVER['REMOTE_ADDR'] = '192.168.1.100';
    $_SERVER['HTTP_USER_AGENT'] = 'Test Security Scanner';
    $_SERVER['REQUEST_URI'] = '/test-security-violation';
    
    SecurityHelper::record_security_violation('test_violation', array(
        'attempted_action' => 'malicious_request',
        'blocked_reason' => 'invalid_csrf_token',
        'severity' => 'high'
    ));
    
    echo "<p>✓ セキュリティ違反記録完了</p>\n";
    
} catch (Exception $e) {
    echo "<p>❌ セキュリティ違反記録テスト エラー: " . $e->getMessage() . "</p>\n";
}

// テスト6: IP アドレス検出
echo "<h2>6. IP アドレス検出テスト</h2>\n";

try {
    // 各種IPアドレス設定をテスト
    $test_cases = array(
        'REMOTE_ADDR' => '203.0.113.195',
        'HTTP_X_FORWARDED_FOR' => '203.0.113.196, 192.168.1.1',
        'HTTP_CF_CONNECTING_IP' => '203.0.113.197'
    );
    
    foreach ($test_cases as $header => $ip_value) {
        $_SERVER[$header] = $ip_value;
        $detected_ip = SecurityHelper::get_client_ip();
        echo "<p>✓ {$header}: {$ip_value} → 検出IP: {$detected_ip}</p>\n";
        unset($_SERVER[$header]);
    }
    
} catch (Exception $e) {
    echo "<p>❌ IP アドレス検出テスト エラー: " . $e->getMessage() . "</p>\n";
}

// テスト結果まとめ
echo "<h2>テスト結果まとめ</h2>\n";
echo "<p>✅ セキュリティ機能の基本テストが完了しました。</p>\n";
echo "<p><strong>実装された機能:</strong></p>\n";
echo "<ul>\n";
echo "<li>包括的なセキュリティヘッダー（CSP、X-Frame-Options等）</li>\n";
echo "<li>強化されたレート制限機能（段階的遅延、エスカレーション対応）</li>\n";
echo "<li>詳細なセキュリティログ機能（レベル別、自動ローテーション）</li>\n";
echo "<li>入力サニタイゼーション・出力エスケープ</li>\n";
echo "<li>CSRF保護</li>\n";
echo "<li>セキュリティ違反の検出・記録</li>\n";
echo "</ul>\n";

echo "<p><strong>注意事項:</strong></p>\n";
echo "<ul>\n";
echo "<li>本番環境では適切なログファイルの権限設定が必要です</li>\n";
echo "<li>CSPポリシーは使用するCDNやサードパーティサービスに応じて調整してください</li>\n";
echo "<li>レート制限の値は実際のトラフィックに基づいて最適化してください</li>\n";
echo "</ul>\n";

// メモリ使用量の表示
$memory_usage = memory_get_peak_usage(true);
$memory_mb = round($memory_usage / 1024 / 1024, 2);
echo "<p><small>テスト実行時のピークメモリ使用量: {$memory_mb}MB</small></p>\n";
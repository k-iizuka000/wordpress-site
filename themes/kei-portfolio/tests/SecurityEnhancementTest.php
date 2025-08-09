<?php
/**
 * Security Enhancement Test
 * セキュリティ強化機能のテスト
 *
 * @package KeiPortfolio
 */

use PHPUnit\Framework\TestCase;

class SecurityEnhancementTest extends TestCase {
    
    private $security_helper;
    private $security_logger;
    private $rate_limiter;
    
    public function setUp(): void {
        parent::setUp();
        
        // WordPressテスト環境のセットアップ
        if (!defined('ABSPATH')) {
            define('ABSPATH', dirname(__DIR__) . '/');
        }
        
        // テスト用のセキュリティクラスを初期化
        require_once dirname(__DIR__) . '/inc/class-security-helper.php';
        require_once dirname(__DIR__) . '/inc/class-security-logger.php';
        require_once dirname(__DIR__) . '/inc/class-rate-limiter.php';
        
        // WordPressのコア関数をモック
        $this->mockWordPressFunctions();
        
        $this->security_helper = KeiPortfolio\Security\SecurityHelper::class;
        $this->security_logger = KeiPortfolio\Security\SecurityLogger::get_instance();
        $this->rate_limiter = KeiPortfolio\Security\RateLimiter::get_instance();
    }
    
    /**
     * WordPress関数のモック
     */
    private function mockWordPressFunctions() {
        if (!function_exists('wp_create_nonce')) {
            function wp_create_nonce($action) {
                return hash('sha256', $action . 'test_nonce_salt' . time());
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
                return false; // テスト環境では常にfalse
            }
        }
        
        if (!function_exists('set_transient')) {
            function set_transient($key, $value, $expiration) {
                return true; // テスト環境では常にtrue
            }
        }
        
        if (!function_exists('current_time')) {
            function current_time($format) {
                return date($format);
            }
        }
        
        if (!function_exists('wp_salt')) {
            function wp_salt() {
                return 'test_salt_value';
            }
        }
        
        if (!function_exists('get_current_user_id')) {
            function get_current_user_id() {
                return 1;
            }
        }
        
        if (!function_exists('error_log')) {
            // error_logは既存の関数なのでオーバーライドしない
        }
        
        if (!defined('WP_CONTENT_DIR')) {
            define('WP_CONTENT_DIR', '/tmp');
        }
    }
    
    /**
     * セキュリティヘッダーのテスト
     */
    public function testSecurityHeaders() {
        // セキュリティヘッダーが正しく生成されることをテスト
        ob_start();
        $this->security_helper::set_custom_security_headers();
        $headers = xdebug_get_headers();
        ob_end_clean();
        
        // CSPヘッダーが存在することを確認
        $csp_found = false;
        foreach ($headers as $header) {
            if (strpos($header, 'Content-Security-Policy:') === 0) {
                $csp_found = true;
                // 基本的なディレクティブが含まれていることを確認
                $this->assertStringContains("default-src 'self'", $header);
                $this->assertStringContains("object-src 'none'", $header);
                break;
            }
        }
        $this->assertTrue($csp_found, 'CSPヘッダーが設定されていません');
    }
    
    /**
     * 入力値サニタイゼーションのテスト
     */
    public function testInputSanitization() {
        // テキストのサニタイゼーション
        $malicious_text = '<script>alert("xss")</script>Hello World';
        $sanitized = $this->security_helper::sanitize_input($malicious_text, 'text');
        $this->assertStringNotContains('<script>', $sanitized);
        $this->assertStringContains('Hello World', $sanitized);
        
        // 整数のサニタイゼーション
        $number_string = '123abc';
        $sanitized_int = $this->security_helper::sanitize_input($number_string, 'int');
        $this->assertEquals(123, $sanitized_int);
        $this->assertTrue(is_int($sanitized_int));
        
        // 配列のサニタイゼーション
        $malicious_array = ['<script>alert("xss")</script>', 'safe_value', '123'];
        $sanitized_array = $this->security_helper::sanitize_input($malicious_array, 'array');
        $this->assertIsArray($sanitized_array);
        $this->assertStringNotContains('<script>', $sanitized_array[0]);
        $this->assertEquals('safe_value', $sanitized_array[1]);
    }
    
    /**
     * CSRFトークン生成・検証のテスト
     */
    public function testCSRFProtection() {
        $action = 'test_action';
        
        // トークン生成
        $token = $this->security_helper::generate_csrf_token($action);
        $this->assertNotEmpty($token);
        $this->assertTrue(is_string($token));
        
        // トークン検証
        $is_valid = $this->security_helper::verify_csrf_token($token, $action);
        $this->assertTrue($is_valid);
        
        // 無効なトークンのテスト
        $invalid_token = 'invalid_token';
        $is_invalid = $this->security_helper::verify_csrf_token($invalid_token, $action);
        $this->assertFalse($is_invalid);
    }
    
    /**
     * レート制限のテスト
     */
    public function testRateLimiting() {
        $action = 'test_action';
        $settings = array('limit' => 3, 'window' => 60);
        
        // 初回リクエストは成功
        $result1 = $this->rate_limiter->check($action, $settings);
        $this->assertTrue($result1, 'First request should pass');
        
        // 2回目のリクエストも成功
        $result2 = $this->rate_limiter->check($action, $settings);
        $this->assertTrue($result2, 'Second request should pass');
        
        // 3回目のリクエストも成功
        $result3 = $this->rate_limiter->check($action, $settings);
        $this->assertTrue($result3, 'Third request should pass');
        
        // 統計情報の取得テスト
        $stats = $this->rate_limiter->get_stats($action);
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('current_count', $stats);
        $this->assertArrayHasKey('limit', $stats);
        $this->assertArrayHasKey('remaining_requests', $stats);
    }
    
    /**
     * セキュリティロガーのテスト
     */
    public function testSecurityLogging() {
        $event_type = 'test_event';
        $message = 'Test security message';
        $details = array('key' => 'value', 'number' => 123);
        
        // ログ記録のテスト（例外が発生しないことを確認）
        try {
            $this->security_logger->info($event_type, $message, $details);
            $this->security_logger->warning($event_type, $message, $details);
            $this->security_logger->error($event_type, $message, $details);
            $this->security_logger->critical($event_type, $message, $details);
            $this->assertTrue(true, 'Logging methods executed without exceptions');
        } catch (Exception $e) {
            $this->fail('Logging should not throw exceptions: ' . $e->getMessage());
        }
    }
    
    /**
     * セキュリティ設定のテスト
     */
    public function testSecurityConfiguration() {
        // レート制限設定のテスト
        $action = 'config_test';
        $custom_settings = array(
            'limit' => 5,
            'window' => 120,
            'block_duration' => 600
        );
        
        $this->rate_limiter->set_action_limits($action, $custom_settings);
        
        // 設定が正しく保存されたかは実際のWordPress環境でのみテスト可能
        $this->assertTrue(true, 'Action limits set without error');
    }
    
    /**
     * HTMLエスケープのテスト
     */
    public function testHTMLEscaping() {
        $malicious_html = '<script>alert("xss")</script><p>Safe content</p>';
        
        // HTML全体のエスケープ
        $escaped_html = $this->security_helper::escape_html($malicious_html);
        $this->assertStringNotContains('<script>', $escaped_html);
        $this->assertStringContains('&lt;script&gt;', $escaped_html);
        
        // 属性値のエスケープ
        $malicious_attr = 'value" onclick="alert(\'xss\')"';
        $escaped_attr = $this->security_helper::escape_attr($malicious_attr);
        $this->assertStringNotContains('onclick=', $escaped_attr);
        
        // JavaScript値のエスケープ
        $malicious_js = 'value"; alert("xss"); //';
        $escaped_js = $this->security_helper::escape_js($malicious_js);
        $this->assertStringNotContains('alert(', $escaped_js);
    }
    
    /**
     * JSON出力の安全性テスト
     */
    public function testSafeJSONOutput() {
        $data = array(
            'message' => '<script>alert("xss")</script>',
            'number' => 123,
            'boolean' => true,
            'array' => array('item1', 'item2')
        );
        
        $json = $this->security_helper::safe_json_encode($data);
        $this->assertIsString($json);
        $this->assertStringNotContains('<script>', $json);
        
        // JSONが有効であることを確認
        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertEquals($data['number'], $decoded['number']);
        $this->assertEquals($data['boolean'], $decoded['boolean']);
    }
    
    /**
     * IP アドレス取得のテスト
     */
    public function testClientIPDetection() {
        // テスト用の環境変数を設定
        $_SERVER['REMOTE_ADDR'] = '192.168.1.100';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.195, 192.168.1.100';
        
        $ip = $this->security_helper::get_client_ip();
        $this->assertNotEmpty($ip);
        $this->assertTrue(filter_var($ip, FILTER_VALIDATE_IP) !== false);
    }
    
    /**
     * セキュリティヘッダーの統合テスト
     */
    public function testSecurityHeadersIntegration() {
        // カスタムCSP設定のテスト
        $custom_csp = array(
            'script-src' => "'self' https://trusted-cdn.com",
            'style-src' => "'self' 'unsafe-inline'"
        );
        
        $additional_headers = array(
            'X-Custom-Security' => 'enabled'
        );
        
        // ヘッダー設定が例外を投げないことを確認
        try {
            $this->security_helper::set_custom_security_headers($custom_csp, $additional_headers);
            $this->assertTrue(true, 'Custom security headers set without error');
        } catch (Exception $e) {
            $this->fail('Setting custom security headers should not throw exceptions: ' . $e->getMessage());
        }
    }
    
    /**
     * セキュリティ違反記録のテスト
     */
    public function testSecurityViolationRecording() {
        $violation_type = 'test_violation';
        $details = array(
            'attempted_action' => 'malicious_request',
            'blocked_reason' => 'invalid_token'
        );
        
        // 違反記録が例外を投げないことを確認
        try {
            $this->security_helper::record_security_violation($violation_type, $details);
            $this->assertTrue(true, 'Security violation recorded without error');
        } catch (Exception $e) {
            $this->fail('Recording security violation should not throw exceptions: ' . $e->getMessage());
        }
    }
    
    public function tearDown(): void {
        // テスト後のクリーンアップ
        if (isset($_SERVER['REMOTE_ADDR'])) {
            unset($_SERVER['REMOTE_ADDR']);
        }
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            unset($_SERVER['HTTP_X_FORWARDED_FOR']);
        }
        
        parent::tearDown();
    }
}
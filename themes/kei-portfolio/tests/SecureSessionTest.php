<?php
/**
 * SecureSessionクラスのPHPUnit テストファイル
 * 
 * @package Kei_Portfolio_Pro
 */

require_once 'bootstrap.php';

use PHPUnit\Framework\TestCase;

/**
 * SecureSessionクラスのテスト
 */
class SecureSessionTest extends TestCase {
    
    private $secure_session;
    
    public function setUp(): void {
        // テスト環境設定
        if (!defined('COOKIEPATH')) {
            define('COOKIEPATH', '/');
        }
        if (!defined('COOKIE_DOMAIN')) {
            define('COOKIE_DOMAIN', '');
        }
        
        // WordPress関数のモック
        if (!function_exists('is_ssl')) {
            function is_ssl() {
                return false;
            }
        }
        
        if (!function_exists('wp_generate_password')) {
            function wp_generate_password($length, $special_chars = true) {
                $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                if ($special_chars) {
                    $chars .= '!@#$%^&*()';
                }
                return substr(str_shuffle($chars), 0, $length);
            }
        }
        
        // クラスファイルの読み込み
        require_once dirname(__DIR__) . '/inc/class-secure-session.php';
        
        // インスタンス取得
        $this->secure_session = \KeiPortfolio\Security\SecureSession::get_instance();
    }
    
    public function tearDown(): void {
        // セッションクリーンアップ
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
    
    /**
     * クラスのシングルトンパターンテスト
     */
    public function testSingleton() {
        $instance1 = \KeiPortfolio\Security\SecureSession::get_instance();
        $instance2 = \KeiPortfolio\Security\SecureSession::get_instance();
        
        $this->assertSame($instance1, $instance2, 'SecureSessionはシングルトンパターンを実装している必要があります');
    }
    
    /**
     * セッション情報取得テスト
     */
    public function testGetSessionInfo() {
        $session_info = $this->secure_session->get_session_info();
        
        $this->assertIsArray($session_info, 'セッション情報は配列で返される必要があります');
        $this->assertArrayHasKey('session_status', $session_info);
        $this->assertArrayHasKey('php_version', $session_info);
        $this->assertArrayHasKey('is_ssl', $session_info);
        $this->assertArrayHasKey('session_active', $session_info);
    }
    
    /**
     * セッション値の設定・取得・削除テスト
     */
    public function testSessionOperations() {
        // セッション開始のモック（テスト環境では手動で開始）
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        
        $test_key = 'test_key';
        $test_value = 'test_value_' . time();
        
        // 設定テスト
        $set_result = $this->secure_session->set($test_key, $test_value);
        $this->assertTrue($set_result, 'セッション値の設定が成功する必要があります');
        
        // 取得テスト
        $retrieved_value = $this->secure_session->get($test_key);
        $this->assertEquals($test_value, $retrieved_value, '設定した値が正しく取得される必要があります');
        
        // デフォルト値テスト
        $default_value = 'default';
        $non_existent_value = $this->secure_session->get('non_existent_key', $default_value);
        $this->assertEquals($default_value, $non_existent_value, '存在しないキーではデフォルト値が返される必要があります');
        
        // 削除テスト
        $remove_result = $this->secure_session->remove($test_key);
        $this->assertTrue($remove_result, 'セッション値の削除が成功する必要があります');
        
        // 削除確認
        $deleted_value = $this->secure_session->get($test_key);
        $this->assertNull($deleted_value, '削除された値は取得できない必要があります');
    }
    
    /**
     * 保護されたキーのセキュリティテスト
     */
    public function testProtectedKeys() {
        // セッション開始のモック
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        
        $protected_keys = ['user_agent', 'ip_address', 'session_token', 'session_start_time'];
        
        foreach ($protected_keys as $key) {
            $result = $this->secure_session->set($key, 'malicious_value');
            $this->assertFalse($result, "保護されたキー「{$key}」の設定は拒否される必要があります");
        }
    }
    
    /**
     * セッショントークンテスト
     */
    public function testSessionToken() {
        // セッション開始のモック
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
            // テスト用にトークンを手動設定
            $_SESSION['session_token'] = wp_generate_password(32, false);
        }
        
        $token = $this->secure_session->get_token();
        $this->assertNotNull($token, 'セッショントークンが取得される必要があります');
        $this->assertIsString($token, 'セッショントークンは文字列である必要があります');
        
        // トークン検証テスト
        $valid_result = $this->secure_session->verify_token($token);
        $this->assertTrue($valid_result, '有効なトークンの検証は成功する必要があります');
        
        // 無効なトークンテスト
        $invalid_result = $this->secure_session->verify_token('invalid_token');
        $this->assertFalse($invalid_result, '無効なトークンの検証は失敗する必要があります');
    }
    
    /**
     * セッション非アクティブ時の動作テスト
     */
    public function testInactiveSession() {
        // セッションを確実に停止
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        
        // 非アクティブ状態でのテスト
        $result = $this->secure_session->set('test_key', 'test_value');
        $this->assertFalse($result, 'セッション非アクティブ時の設定は失敗する必要があります');
        
        $value = $this->secure_session->get('test_key', 'default');
        $this->assertEquals('default', $value, 'セッション非アクティブ時はデフォルト値が返される必要があります');
        
        $remove_result = $this->secure_session->remove('test_key');
        $this->assertFalse($remove_result, 'セッション非アクティブ時の削除は失敗する必要があります');
        
        $token = $this->secure_session->get_token();
        $this->assertNull($token, 'セッション非アクティブ時はトークンは取得できない必要があります');
        
        $verify_result = $this->secure_session->verify_token('any_token');
        $this->assertFalse($verify_result, 'セッション非アクティブ時はトークン検証は失敗する必要があります');
    }
    
    /**
     * エラーハンドリングテスト
     */
    public function testErrorHandling() {
        // セッション開始のモック
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        
        // 空のキーでのテスト
        $empty_key_result = $this->secure_session->set('', 'value');
        $this->assertTrue($empty_key_result, '空のキーでも設定は成功する必要があります（制限なし）');
        
        // nullキーでのテスト（型エラーを起こさないか確認）
        $null_value = $this->secure_session->get(null);
        $this->assertNull($null_value, 'nullキーでも例外が発生しない必要があります');
    }
}
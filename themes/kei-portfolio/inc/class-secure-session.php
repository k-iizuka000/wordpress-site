<?php
namespace KeiPortfolio\Security;

/**
 * セキュアなセッション管理クラス
 * 
 * @package Kei_Portfolio_Pro
 * @since 1.0.0
 */
class SecureSession {
    
    /**
     * セッション設定定数
     */
    private const SESSION_TIMEOUT = 3600; // セッションタイムアウト1時間
    
    private static $instance = null;
    
    /**
     * シングルトンパターンによるインスタンス取得
     * 
     * @return SecureSession
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * プライベートコンストラクタ（シングルトンパターン）
     */
    private function __construct() {
        // WordPress環境でのみフックを追加
        if (function_exists('add_action')) {
            add_action('init', [$this, 'start_secure_session'], 1);
            add_action('wp_logout', [$this, 'destroy_session']);
            add_action('wp_login', [$this, 'regenerate_session']);
        }
    }
    
    /**
     * セキュアなセッション開始
     */
    public function start_secure_session() {
        // WordPressがすでにセッションを開始している可能性をチェック
        if (headers_sent()) {
            error_log('SecureSession: Headers already sent, cannot start session');
            return;
        }
        
        // PHPバージョンチェック
        if (version_compare(PHP_VERSION, '7.1.0', '<')) {
            error_log('SecureSession: PHP 7.1.0 or higher required');
            return;
        }
        
        // セッションが開始されていない場合のみ開始
        if (session_status() === PHP_SESSION_NONE) {
            // セッション設定
            $session_config = [
                'lifetime' => 0,
                'path' => defined('COOKIEPATH') ? COOKIEPATH : '/',
                'domain' => defined('COOKIE_DOMAIN') ? COOKIE_DOMAIN : '',
                'secure' => function_exists('is_ssl') ? is_ssl() : false,
                'httponly' => true,
                'samesite' => 'Lax'
            ];
            
            // PHP 7.3以上の場合
            if (version_compare(PHP_VERSION, '7.3.0', '>=')) {
                session_set_cookie_params($session_config);
            } else {
                // 旧バージョン対応
                session_set_cookie_params(
                    $session_config['lifetime'],
                    $session_config['path'],
                    $session_config['domain'],
                    $session_config['secure'],
                    $session_config['httponly']
                );
            }
            
            // セッション名をカスタマイズ（セキュリティ向上）
            session_name('kei_portfolio_session');
            
            // セッション開始
            try {
                if (@session_start()) {
                    // セッション固定攻撃を防ぐためのランダムな検証トークン
                    if (!isset($_SESSION['session_token'])) {
                        if (function_exists('wp_generate_password')) {
                            $_SESSION['session_token'] = wp_generate_password(32, false);
                        } else {
                            // WordPress関数が利用できない場合の代替実装
                            $_SESSION['session_token'] = bin2hex(random_bytes(16));
                        }
                    }
                    
                    // セッション有効期限の設定
                    if (!isset($_SESSION['session_start_time'])) {
                        $_SESSION['session_start_time'] = time();
                    }
                    
                    // セッションタイムアウト後にセッションを無効化
                    if (isset($_SESSION['session_start_time']) && (time() - $_SESSION['session_start_time'] > self::SESSION_TIMEOUT)) {
                        $this->destroy_session();
                        return;
                    }
                    
                    // User AgentとIPアドレスの検証（セッションハイジャック対策）
                    $current_user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
                    $current_ip = $this->get_client_ip();
                    
                    if (isset($_SESSION['user_agent']) && isset($_SESSION['ip_address'])) {
                        if ($_SESSION['user_agent'] !== $current_user_agent || $_SESSION['ip_address'] !== $current_ip) {
                            $this->destroy_session();
                            error_log('SecureSession: Session hijack attempt detected');
                            return;
                        }
                    } else {
                        $_SESSION['user_agent'] = $current_user_agent;
                        $_SESSION['ip_address'] = $current_ip;
                    }
                    
                } else {
                    error_log('SecureSession: Failed to start session');
                }
            } catch (\Exception $e) {
                error_log('SecureSession: Session start failed: ' . $e->getMessage());
            }
        }
    }
    
    /**
     * セッション破棄
     */
    public function destroy_session() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            // セッションデータをクリア
            $_SESSION = array();
            
            // セッションクッキーを削除
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            
            // セッションを破棄
            session_destroy();
        }
    }
    
    /**
     * セッションID再生成
     */
    public function regenerate_session() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            // セッションIDを再生成（古いセッションファイルを削除）
            session_regenerate_id(true);
            
            // 新しいセッショントークンを生成
            if (function_exists('wp_generate_password')) {
                $_SESSION['session_token'] = wp_generate_password(32, false);
            } else {
                $_SESSION['session_token'] = bin2hex(random_bytes(16));
            }
            $_SESSION['session_start_time'] = time();
        }
    }
    
    /**
     * セッション値の安全な取得
     * 
     * @param string $key セッションキー
     * @param mixed $default デフォルト値
     * @return mixed セッション値
     */
    public function get($key, $default = null) {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return $default;
        }
        
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }
    
    /**
     * セッション値の安全な設定
     * 
     * @param string $key セッションキー
     * @param mixed $value セッション値
     * @return bool 設定成功かどうか
     */
    public function set($key, $value) {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }
        
        // 危険なキーワードをチェック
        $dangerous_keys = ['user_agent', 'ip_address', 'session_token', 'session_start_time'];
        if (in_array($key, $dangerous_keys)) {
            error_log('SecureSession: Attempt to modify protected session key: ' . $key);
            return false;
        }
        
        $_SESSION[$key] = $value;
        return true;
    }
    
    /**
     * セッション値の削除
     * 
     * @param string $key セッションキー
     * @return bool 削除成功かどうか
     */
    public function remove($key) {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }
        
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
            return true;
        }
        
        return false;
    }
    
    /**
     * セッショントークンの検証
     * 
     * @param string $token 検証するトークン
     * @return bool トークンが有効かどうか
     */
    public function verify_token($token) {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }
        
        return isset($_SESSION['session_token']) && hash_equals($_SESSION['session_token'], $token);
    }
    
    /**
     * セッショントークンの取得
     * 
     * @return string|null セッショントークン
     */
    public function get_token() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return null;
        }
        
        return isset($_SESSION['session_token']) ? $_SESSION['session_token'] : null;
    }
    
    /**
     * クライアントIPアドレスの取得
     * 
     * @return string IPアドレス
     */
    private function get_client_ip() {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 
                   'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
    }
    
    /**
     * セッションのクリーンアップ（期限切れセッションの削除）
     */
    public function cleanup_expired_sessions() {
        // この機能はサーバーのセッションGCに依存
        // 必要に応じてカスタムクリーンアップロジックを実装
        if (function_exists('session_gc')) {
            @session_gc();
        }
    }
    
    /**
     * セッション状態の診断情報を取得
     * 
     * @return array 診断情報
     */
    public function get_session_info() {
        return [
            'session_status' => session_status(),
            'session_id' => session_id(),
            'session_name' => session_name(),
            'cookie_params' => session_get_cookie_params(),
            'php_version' => PHP_VERSION,
            'is_ssl' => is_ssl(),
            'session_active' => session_status() === PHP_SESSION_ACTIVE
        ];
    }
}

// WordPress環境でのみ自動インスタンス化
if (function_exists('add_action')) {
    SecureSession::get_instance();
}
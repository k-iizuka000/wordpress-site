<?php
/**
 * Security Helper Class
 * セキュリティ関連のヘルパー機能を提供
 *
 * @package KeiPortfolio
 * @version 2.0
 */

namespace KeiPortfolio\Security;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class SecurityHelper {
    
    /**
     * レート制限設定定数
     */
    private const RATE_LIMIT_DEFAULT = 60;     // デフォルト60回/時間
    private const RATE_LIMIT_WINDOW = 3600;    // デフォルトウィンドウ1時間
    
    /**
     * セキュリティログのインスタンス
     */
    private static $logger = null;
    
    /**
     * リクエストパラメータを安全に取得
     *
     * @param string $param_name パラメータ名
     * @param mixed  $default    デフォルト値
     * @param string $type       データタイプ（text, int, email, url, array）
     * @param string $method     HTTP メソッド（GET, POST, REQUEST）
     * @return mixed 安全化されたパラメータ値
     */
    public static function get_request_param($param_name, $default = '', $type = 'text', $method = 'REQUEST') {
        $value = null;
        
        switch (strtoupper($method)) {
            case 'GET':
                $value = isset($_GET[$param_name]) ? $_GET[$param_name] : $default;
                break;
            case 'POST':
                $value = isset($_POST[$param_name]) ? $_POST[$param_name] : $default;
                break;
            case 'REQUEST':
            default:
                $value = isset($_REQUEST[$param_name]) ? $_REQUEST[$param_name] : $default;
                break;
        }
        
        return self::sanitize_input($value, $type);
    }
    
    /**
     * 入力値を安全化
     *
     * @param mixed  $value 入力値
     * @param string $type  データタイプ
     * @return mixed 安全化された値
     */
    public static function sanitize_input($value, $type = 'text') {
        if (is_null($value)) {
            return null;
        }
        
        switch ($type) {
            case 'int':
            case 'integer':
                return intval($value);
                
            case 'float':
            case 'number':
                return floatval($value);
                
            case 'bool':
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
                
            case 'email':
                return sanitize_email($value);
                
            case 'url':
                return sanitize_url($value);
                
            case 'key':
                return sanitize_key($value);
                
            case 'slug':
                return sanitize_title($value);
                
            case 'textarea':
                return sanitize_textarea_field($value);
                
            case 'html':
                return wp_kses_post($value);
                
            case 'array':
                if (!is_array($value)) {
                    return array();
                }
                return array_map(function($item) {
                    return sanitize_text_field($item);
                }, $value);
                
            case 'text':
            default:
                return sanitize_text_field($value);
        }
    }
    
    /**
     * CSRFトークンを生成・検証
     *
     * @param string $action アクション名
     * @return string CSRFトークン
     */
    public static function generate_csrf_token($action = 'default_action') {
        return wp_create_nonce($action);
    }
    
    /**
     * CSRFトークンを検証
     *
     * @param string $token  検証するトークン
     * @param string $action アクション名
     * @return bool 検証結果
     */
    public static function verify_csrf_token($token, $action = 'default_action') {
        return wp_verify_nonce($token, $action);
    }
    
    /**
     * JSON出力用のデータを安全化
     *
     * @param mixed $data 出力するデータ
     * @return string JSONエンコード済み安全なデータ
     */
    public static function safe_json_encode($data) {
        return json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * HTMLエスケープ用のヘルパー関数
     *
     * @param string $value エスケープする値
     * @return string エスケープ済みの値
     */
    public static function escape_html($value) {
        return esc_html($value);
    }
    
    /**
     * HTML属性エスケープ用のヘルパー関数
     *
     * @param string $value エスケープする値
     * @return string エスケープ済みの値
     */
    public static function escape_attr($value) {
        return esc_attr($value);
    }
    
    /**
     * URL エスケープ用のヘルパー関数
     *
     * @param string $url エスケープするURL
     * @return string エスケープ済みのURL
     */
    public static function escape_url($url) {
        return esc_url($url);
    }
    
    /**
     * JavaScript エスケープ用のヘルパー関数
     *
     * @param string $value エスケープする値
     * @return string エスケープ済みの値
     */
    public static function escape_js($value) {
        return esc_js($value);
    }
    
    /**
     * 許可されたHTML要素とお属性を定義
     *
     * @return array 許可されたHTML要素の配列
     */
    public static function get_allowed_html() {
        return array(
            'p' => array(),
            'br' => array(),
            'strong' => array(),
            'em' => array(),
            'b' => array(),
            'i' => array(),
            'u' => array(),
            'span' => array(
                'class' => array(),
                'style' => array()
            ),
            'div' => array(
                'class' => array(),
                'style' => array()
            ),
            'a' => array(
                'href' => array(),
                'class' => array(),
                'target' => array(),
                'rel' => array()
            ),
            'mark' => array(
                'class' => array()
            )
        );
    }
    
    /**
     * セキュリティログを記録
     *
     * @param string $message ログメッセージ
     * @param string $level   ログレベル（info, warning, error）
     * @param array  $context 追加コンテキスト情報
     */
    public static function log_security_event($message, $level = 'info', $context = array()) {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }
        
        $log_entry = array(
            'timestamp' => current_time('mysql'),
            'level' => $level,
            'message' => $message,
            'ip_hash' => self::hash_ip(),  // IPアドレスの代わりにハッシュ化された識別子を使用
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '',
            'context' => $context
        );
        
        if (WP_DEBUG && WP_DEBUG_LOG) {
            error_log('Security Event: ' . json_encode($log_entry, JSON_UNESCAPED_UNICODE));
        } else {
            error_log('Security Event: ' . $level . ' level event in ' . __CLASS__);
        }
    }
    
    /**
     * クライアントIPアドレスを取得（改善版）
     * IPv4/IPv6対応、プライベートIP適切な処理
     *
     * @return string IPアドレス
     */
    public static function get_client_ip() {
        $ip_keys = array(
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_X_FORWARDED_FOR',     // Standard proxy header
            'HTTP_X_FORWARDED',         // Alternative proxy header
            'HTTP_FORWARDED_FOR',       // RFC 7239 alternative
            'HTTP_FORWARDED',           // RFC 7239 standard
            'REMOTE_ADDR'               // Direct connection
        );
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                $ip_list = explode(',', $_SERVER[$key]);
                foreach ($ip_list as $ip) {
                    $ip = trim($ip);
                    // IPv4とIPv6の両方を適切に検証
                    if (filter_var($ip, FILTER_VALIDATE_IP, 
                        FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 | 
                        FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        // フォールバック: REMOTE_ADDRから取得（プライベートIPも許可）
        return isset($_SERVER['REMOTE_ADDR']) ? 
               filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP) ?: '0.0.0.0' : 
               '0.0.0.0';
    }
    
    /**
     * IPアドレスをハッシュ化（プライバシー保護）
     *
     * @param string $ip IPアドレス（省略時は自動取得）
     * @return string ハッシュ化されたIP識別子
     */
    public static function hash_ip($ip = null) {
        if (is_null($ip)) {
            $ip = self::get_client_ip();
        }
        
        // WordPressのソルトを使用してセキュアにハッシュ化
        $hashed_ip = hash('sha256', $ip . wp_salt());
        
        // 識別に十分な16文字のプレフィックスを返す
        return substr($hashed_ip, 0, 16);
    }
    
    /**
     * レート制限チェック
     *
     * @param string $key    レート制限キー
     * @param int    $limit  制限回数
     * @param int    $window 時間窓（秒）
     * @return bool 制限内かどうか
     */
    public static function check_rate_limit($key, $limit = self::RATE_LIMIT_DEFAULT, $window = self::RATE_LIMIT_WINDOW) {
        // IPアドレスをハッシュ化してプライバシー保護
        $cache_key = 'rate_limit_' . md5($key . self::hash_ip());
        $current_count = get_transient($cache_key);
        
        if ($current_count === false) {
            set_transient($cache_key, 1, $window);
            return true;
        }
        
        if ($current_count >= $limit) {
            self::log_security_event(
                'Rate limit exceeded',
                'warning',
                array(
                    'key' => $key,
                    'limit' => $limit,
                    'current_count' => $current_count,
                    'ip_hash' => self::hash_ip()  // IPの代わりにハッシュを記録
                )
            );
            return false;
        }
        
        set_transient($cache_key, $current_count + 1, $window);
        return true;
    }
    
    /**
     * 包括的なセキュリティヘッダーを設定
     */
    public static function set_security_headers() {
        if (!is_admin() && !wp_doing_ajax() && !wp_doing_cron()) {
            // CSP設定
            $csp = "default-src 'self'; ";
            $csp .= "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; ";
            $csp .= "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; ";
            $csp .= "img-src 'self' data: https: http:; ";
            $csp .= "font-src 'self' data: https://fonts.gstatic.com; ";
            $csp .= "connect-src 'self'; ";
            $csp .= "frame-ancestors 'self'; ";
            $csp .= "form-action 'self'; ";
            $csp .= "base-uri 'self'; ";
            $csp .= "object-src 'none';";
            
            header("Content-Security-Policy: " . $csp);
            header("X-Content-Type-Options: nosniff");
            header("X-Frame-Options: SAMEORIGIN");
            header("X-XSS-Protection: 1; mode=block");
            header("Referrer-Policy: strict-origin-when-cross-origin");
            header("Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=(), usb=()");
            header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
        }
    }
    
    /**
     * セキュリティヘッダーのカスタマイズ可能版
     * 
     * @param array $custom_csp カスタムCSPディレクティブ
     * @param array $additional_headers 追加ヘッダー
     */
    public static function set_custom_security_headers($custom_csp = array(), $additional_headers = array()) {
        if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
            return;
        }
        
        // デフォルトCSP設定
        $default_csp = array(
            'default-src' => "'self'",
            'script-src' => "'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
            'style-src' => "'self' 'unsafe-inline' https://fonts.googleapis.com",
            'img-src' => "'self' data: https: http:",
            'font-src' => "'self' data: https://fonts.gstatic.com",
            'connect-src' => "'self'",
            'frame-ancestors' => "'self'",
            'form-action' => "'self'",
            'base-uri' => "'self'",
            'object-src' => "'none'"
        );
        
        // カスタム設定をマージ
        $csp_directives = array_merge($default_csp, $custom_csp);
        
        // CSP文字列を生成
        $csp_parts = array();
        foreach ($csp_directives as $directive => $value) {
            $csp_parts[] = $directive . ' ' . $value;
        }
        $csp = implode('; ', $csp_parts) . ';';
        
        // 基本的なセキュリティヘッダー
        $default_headers = array(
            'Content-Security-Policy' => $csp,
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'geolocation=(), microphone=(), camera=(), payment=(), usb=()',
        );
        
        // HTTPS環境でのみSTSヘッダーを追加
        if (is_ssl()) {
            $default_headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains; preload';
        }
        
        // 追加ヘッダーをマージ
        $all_headers = array_merge($default_headers, $additional_headers);
        
        // ヘッダーを設定
        foreach ($all_headers as $header => $value) {
            if (!empty($value)) {
                header($header . ': ' . $value);
            }
        }
    }
    
    /**
     * セッションベースのレート制限（強化版）
     * 
     * @param string $action アクション名
     * @param int $limit 制限回数
     * @param int $window 時間窓（秒）
     * @param bool $use_session セッション使用の可否
     * @return bool 制限内かどうか
     */
    public static function enhanced_rate_limit($action, $limit = 10, $window = 60, $use_session = true) {
        // IP + アクション + セッション（オプション）でキーを生成
        $key_parts = array(
            'rate_limit',
            $action,
            hash('sha256', self::get_client_ip() . wp_salt())
        );
        
        if ($use_session) {
            $session_id = session_id();
            if (empty($session_id)) {
                session_start();
                $session_id = session_id();
            }
            $key_parts[] = $session_id;
        }
        
        $cache_key = implode('_', $key_parts);
        $current_count = get_transient($cache_key);
        
        if ($current_count === false) {
            set_transient($cache_key, 1, $window);
            return true;
        }
        
        if ($current_count >= $limit) {
            self::log_security_event(
                'Enhanced rate limit exceeded',
                'warning',
                array(
                    'action' => $action,
                    'limit' => $limit,
                    'current_count' => $current_count,
                    'window' => $window,
                    'use_session' => $use_session
                )
            );
            
            // 一定回数を超えた場合、ブロック期間を延長
            if ($current_count >= $limit * 2) {
                set_transient($cache_key . '_blocked', true, $window * 5);
            }
            
            return false;
        }
        
        // ブロック状態のチェック
        if (get_transient($cache_key . '_blocked')) {
            self::log_security_event(
                'Access blocked due to excessive rate limit violations',
                'error',
                array('action' => $action)
            );
            return false;
        }
        
        set_transient($cache_key, $current_count + 1, $window);
        return true;
    }
    
    /**
     * セキュリティ違反の記録とアラート
     * 
     * @param string $violation_type 違反タイプ
     * @param array $details 詳細情報
     */
    public static function record_security_violation($violation_type, $details = array()) {
        $violation_data = array(
            'timestamp' => current_time('mysql'),
            'type' => $violation_type,
            'ip' => hash('sha256', self::get_client_ip() . wp_salt()),
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? substr(sanitize_text_field($_SERVER['HTTP_USER_AGENT']), 0, 255) : '',
            'request_uri' => isset($_SERVER['REQUEST_URI']) ? sanitize_text_field($_SERVER['REQUEST_URI']) : '',
            'details' => $details
        );
        
        // セキュリティログファイルに記録
        $log_entry = json_encode($violation_data, JSON_UNESCAPED_UNICODE);
        
        // 本番環境では適切なログファイルパスを使用
        $log_file = WP_CONTENT_DIR . '/security.log';
        if (is_writable(dirname($log_file))) {
            error_log(date('[Y-m-d H:i:s] ') . $log_entry . PHP_EOL, 3, $log_file);
        }
        
        // WP_DEBUGが有効な場合はPHPエラーログにも記録
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Security Violation: ' . $log_entry);
        }
        
        // 重大な違反の場合は管理者に通知（オプション）
        if (in_array($violation_type, array('brute_force', 'sql_injection', 'xss_attempt'))) {
            self::notify_admin_security_violation($violation_type, $violation_data);
        }
    }
    
    /**
     * 管理者にセキュリティ違反を通知
     * 
     * @param string $violation_type 違反タイプ
     * @param array $violation_data 違反データ
     */
    private static function notify_admin_security_violation($violation_type, $violation_data) {
        // 短時間での重複通知を防ぐ
        $notification_key = 'security_notification_' . $violation_type . '_' . date('Y-m-d-H');
        if (get_transient($notification_key)) {
            return;
        }
        
        $admin_email = get_option('admin_email');
        if (!$admin_email) {
            return;
        }
        
        $subject = sprintf(
            '[%s] セキュリティ違反の検出: %s',
            get_bloginfo('name'),
            $violation_type
        );
        
        $message = sprintf(
            "セキュリティ違反が検出されました。\n\n"
            . "タイプ: %s\n"
            . "時刻: %s\n"
            . "IP (ハッシュ化): %s\n"
            . "リクエストURI: %s\n"
            . "詳細: %s\n\n"
            . "必要に応じて適切なセキュリティ対策を実施してください。",
            $violation_type,
            $violation_data['timestamp'],
            $violation_data['ip'],
            $violation_data['request_uri'],
            json_encode($violation_data['details'], JSON_UNESCAPED_UNICODE)
        );
        
        wp_mail($admin_email, $subject, $message);
        
        // 1時間に1回のみ通知
        set_transient($notification_key, true, HOUR_IN_SECONDS);
    }
    
    /**
     * セキュリティヘルパーの初期化
     */
    public static function init() {
        // セキュリティヘッダーの設定
        add_action('send_headers', array(__CLASS__, 'set_security_headers'), 1);
        
        // WordPress固有のセキュリティ強化
        add_action('init', array(__CLASS__, 'enhance_wordpress_security'));
    }
    
    /**
     * WordPress固有のセキュリティ強化
     */
    public static function enhance_wordpress_security() {
        // XMLRPCを無効化（必要に応じて）
        add_filter('xmlrpc_enabled', '__return_false');
        
        // ファイル編集を無効化
        if (!defined('DISALLOW_FILE_EDIT')) {
            define('DISALLOW_FILE_EDIT', true);
        }
        
        // バージョン情報の削除
        remove_action('wp_head', 'wp_generator');
        
        // WLW Manifest の削除
        remove_action('wp_head', 'wlwmanifest_link');
        
        // RSD Link の削除
        remove_action('wp_head', 'rsd_link');
    }
}
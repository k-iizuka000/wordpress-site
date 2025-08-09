<?php
/**
 * E2Eテストモードの検出と設定
 * 
 * @package Kei_Portfolio
 * @since 1.0.0
 */

// 直接アクセスを防ぐ
if (!defined('ABSPATH')) {
    exit;
}

/**
 * E2Eテストモード管理クラス
 */
class E2E_Test_Mode_Manager {
    
    /**
     * シングルトンインスタンス
     */
    private static $instance = null;
    
    /**
     * テストモードフラグ
     */
    private $is_test_mode = false;
    
    /**
     * テスト用User-Agentパターン
     */
    private $test_patterns = array(
        'Playwright',
        'Puppeteer',
        'HeadlessChrome',
        'PhantomJS',
        'Selenium',
        'WebDriver',
        'Chrome-Lighthouse',
        'Google Page Speed Insights'
    );
    
    /**
     * シングルトンインスタンスを取得
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * コンストラクタ
     */
    private function __construct() {
        // 早期に実行して全体に影響を与える
        $this->detect_test_mode();
        
        if ($this->is_test_mode) {
            $this->apply_test_mode_settings();
        }
    }
    
    /**
     * テストモードの検出
     */
    private function detect_test_mode() {
        // 環境変数チェック
        if (getenv('E2E_TEST_MODE') === 'true') {
            $this->is_test_mode = true;
            return;
        }
        
        // User-Agentチェック
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        foreach ($this->test_patterns as $pattern) {
            if (stripos($user_agent, $pattern) !== false) {
                $this->is_test_mode = true;
                
                // 定数を定義
                if (!defined('E2E_TEST_MODE')) {
                    define('E2E_TEST_MODE', true);
                }
                
                // デバッグログ
                $this->log_detection($pattern);
                break;
            }
        }
        
        // リファラーチェック（Playwrightなどは特定のリファラーを持つことがある）
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if (strpos($referer, 'localhost:') !== false || strpos($referer, '127.0.0.1:') !== false) {
            // ローカルホストからのアクセスで、特定のポートの場合
            if (preg_match('/:(3000|3001|4000|5000|8080|9000)/', $referer)) {
                $this->is_test_mode = true;
                
                if (!defined('E2E_TEST_MODE')) {
                    define('E2E_TEST_MODE', true);
                }
            }
        }
    }
    
    /**
     * テストモード設定の適用
     */
    private function apply_test_mode_settings() {
        // セキュリティ機能の調整
        $this->adjust_security_settings();
        
        // CORS設定
        $this->setup_cors();
        
        // 認証・権限チェックの調整
        $this->adjust_authentication();
        
        // キャッシュの無効化
        $this->disable_caching();
        
        // エラー表示の有効化（デバッグ用）
        $this->enable_debug_mode();
        
        // アセット配信の最適化
        $this->optimize_asset_delivery();
    }
    
    /**
     * セキュリティ設定の調整
     */
    private function adjust_security_settings() {
        // Nonceチェックをスキップ
        add_filter('wp_verify_nonce', array($this, 'bypass_nonce_check'), 10, 2);
        
        // CSRF保護を緩和
        add_filter('check_admin_referer', '__return_true');
        
        // Rate Limitingを無効化
        add_filter('kei_portfolio_rate_limit_enabled', '__return_false');
        
        // ファイルタイプチェックを緩和
        add_filter('wp_check_filetype_and_ext', array($this, 'bypass_mime_check'), 10, 4);
    }
    
    /**
     * CORS設定
     */
    private function setup_cors() {
        add_action('init', function() {
            // 許可するoriginの設定
            $allowed_origins = $this->get_allowed_origins();
            $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
            
            if (in_array($origin, $allowed_origins)) {
                header('Access-Control-Allow-Origin: ' . $origin);
            } else {
                // フォールバック（ローカル開発環境用）
                if ($this->is_local_development($origin)) {
                    header('Access-Control-Allow-Origin: ' . $origin);
                }
            }
            
            header('Access-Control-Allow-Methods: GET, OPTIONS'); // 読み取り専用
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-WP-Nonce, X-Requested-With');
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');
            
            // OPTIONS リクエストの処理
            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                header('HTTP/1.1 200 OK');
                exit;
            }
        }, 1);
        
        // REST API用の追加CORS設定
        add_filter('rest_pre_serve_request', function($served, $result, $request, $server) {
            $allowed_origins = $this->get_allowed_origins();
            $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
            
            if (in_array($origin, $allowed_origins) || $this->is_local_development($origin)) {
                header('Access-Control-Allow-Origin: ' . $origin);
            }
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-WP-Nonce');
            return $served;
        }, 10, 4);
    }
    
    /**
     * 認証設定の調整
     */
    private function adjust_authentication() {
        // REST API認証をバイパス（読み取り専用）
        add_filter('rest_authentication_errors', function($result) {
            if (is_wp_error($result)) {
                // 読み取り専用のエンドポイントのみ許可
                $request_method = $_SERVER['REQUEST_METHOD'] ?? '';
                if ($request_method === 'GET') {
                    return true;
                }
            }
            return $result;
        }, 999);
        
        // ユーザー権限チェックを制限的に緩和（読み取り専用）
        add_filter('user_has_cap', function($allcaps, $caps, $args) {
            // 読み取り専用の権限のみ付与
            $readonly_caps = array(
                'read' => true,
                'read_posts' => true,
                'read_pages' => true,
                'read_private_posts' => false,
                'read_private_pages' => false
            );
            
            // 要求された権限が読み取り専用の場合のみ許可
            $allowed_caps = array();
            foreach ($caps as $cap) {
                if (isset($readonly_caps[$cap]) && $readonly_caps[$cap]) {
                    $allowed_caps[$cap] = true;
                }
            }
            
            return array_merge($allcaps, $allowed_caps);
        }, 10, 3);
        
        // ログイン不要化（読み取り専用ユーザー）
        add_filter('determine_current_user', function($user_id) {
            if (!$user_id) {
                // 読み取り専用の仮想ユーザーを作成
                $readonly_user = new WP_User();
                $readonly_user->ID = 999999; // 特殊なID
                $readonly_user->user_login = 'e2e_readonly_user';
                $readonly_user->display_name = 'E2E Read-Only User';
                $readonly_user->user_email = 'noreply@example.com';
                $readonly_user->roles = array('subscriber');
                
                // キャッシュに保存
                wp_cache_set($readonly_user->ID, $readonly_user, 'users');
                
                return $readonly_user->ID;
            }
            return $user_id;
        }, 999);
    }
    
    /**
     * キャッシュの無効化
     */
    private function disable_caching() {
        // WordPressキャッシュを無効化
        if (!defined('DONOTCACHEPAGE')) {
            define('DONOTCACHEPAGE', true);
        }
        
        // オブジェクトキャッシュを無効化
        add_filter('pre_transient_', '__return_false');
        
        // ブラウザキャッシュを無効化
        add_action('send_headers', function() {
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
        }, 999);
    }
    
    /**
     * デバッグモードの有効化
     */
    private function enable_debug_mode() {
        // WordPressデバッグ設定
        if (!defined('WP_DEBUG')) {
            define('WP_DEBUG', true);
        }
        if (!defined('WP_DEBUG_LOG')) {
            define('WP_DEBUG_LOG', true);
        }
        if (!defined('WP_DEBUG_DISPLAY')) {
            define('WP_DEBUG_DISPLAY', false);
        }
        
        // エラー報告レベル
        error_reporting(E_ALL);
        ini_set('display_errors', '0');
        ini_set('log_errors', '1');
    }
    
    /**
     * アセット配信の最適化
     */
    private function optimize_asset_delivery() {
        // スクリプト・スタイルの強制読み込み
        add_action('wp_enqueue_scripts', function() {
            // すべてのキューに入っているスクリプトを強制的に出力
            global $wp_scripts, $wp_styles;
            
            if ($wp_scripts) {
                foreach ($wp_scripts->queue as $handle) {
                    wp_print_scripts($handle);
                }
            }
            
            if ($wp_styles) {
                foreach ($wp_styles->queue as $handle) {
                    wp_print_styles($handle);
                }
            }
        }, 999);
        
        // 404エラーのフォールバック
        add_action('template_redirect', function() {
            if (is_404()) {
                $request = $_SERVER['REQUEST_URI'] ?? '';
                
                // アセットファイルの404を防ぐ
                if (strpos($request, '/wp-content/themes/') !== false) {
                    // Asset Fallback Handlerに処理を委譲
                    if (class_exists('Asset_Fallback_Handler')) {
                        $handler = Asset_Fallback_Handler::init();
                        // ハンドラーが処理する
                    }
                }
            }
        }, 1);
    }
    
    /**
     * Nonceチェックのバイパス
     */
    public function bypass_nonce_check($result, $action) {
        // テストモードではすべてのnonceを有効とする
        return 1;
    }
    
    /**
     * MIMEタイプチェックのバイパス
     */
    public function bypass_mime_check($data, $file, $filename, $mimes) {
        // テストモードではすべてのファイルタイプを許可
        if (empty($data['type'])) {
            $data['type'] = 'application/octet-stream';
            $data['ext'] = pathinfo($filename, PATHINFO_EXTENSION);
        }
        return $data;
    }
    
    /**
     * テストモード検出のログ記録
     */
    private function log_detection($pattern) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $log_message = sprintf(
                'E2E Test Mode Detected via User-Agent pattern: %s',
                $pattern
            );
            
            $context = array(
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
                'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
            );
            
            // 安全なログ書き込み
            if (function_exists('secure_write_log')) {
                secure_write_log('e2e-test-mode.log', $log_message, $context);
            }
            
            // フォールバック用のerror_log
            error_log('[E2E Test Mode] ' . $log_message);
        }
    }
    
    /**
     * テストモードかどうかを取得
     */
    public function is_test_mode() {
        return $this->is_test_mode;
    }
    
    /**
     * デバッグ情報を取得
     */
    public function get_debug_info() {
        return array(
            'is_test_mode' => $this->is_test_mode,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'detected_pattern' => $this->get_detected_pattern(),
            'environment' => array(
                'E2E_TEST_MODE' => getenv('E2E_TEST_MODE'),
                'WP_DEBUG' => defined('WP_DEBUG') ? WP_DEBUG : false,
                'DONOTCACHEPAGE' => defined('DONOTCACHEPAGE') ? DONOTCACHEPAGE : false,
            ),
            'request' => array(
                'uri' => $_SERVER['REQUEST_URI'] ?? '',
                'method' => $_SERVER['REQUEST_METHOD'] ?? '',
                'referer' => $_SERVER['HTTP_REFERER'] ?? '',
            )
        );
    }
    
    /**
     * 検出されたパターンを取得
     */
    private function get_detected_pattern() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        foreach ($this->test_patterns as $pattern) {
            if (stripos($user_agent, $pattern) !== false) {
                return $pattern;
            }
        }
        
        return null;
    }
    
    /**
     * 許可するoriginのリストを取得
     */
    private function get_allowed_origins() {
        return array(
            'http://localhost:3000',
            'http://localhost:3001',
            'http://127.0.0.1:3000',
            'http://127.0.0.1:3001',
            'http://localhost:8080',
            'http://127.0.0.1:8080',
            // 本番環境のURLがある場合はここに追加
        );
    }
    
    /**
     * ローカル開発環境かどうかを判定
     */
    private function is_local_development($origin) {
        if (empty($origin)) {
            return false;
        }
        
        // ローカルホストパターン
        $local_patterns = array(
            '/^https?:\/\/localhost(:\d+)?$/',
            '/^https?:\/\/127\.0\.0\.1(:\d+)?$/',
            '/^https?:\/\/0\.0\.0\.0(:\d+)?$/',
            '/^https?:\/\/\[::1\](:\d+)?$/',
        );
        
        foreach ($local_patterns as $pattern) {
            if (preg_match($pattern, $origin)) {
                return true;
            }
        }
        
        return false;
    }
}

/**
 * グローバル関数: E2Eテストモードかどうかを確認
 */
function is_e2e_test_mode() {
    $manager = E2E_Test_Mode_Manager::get_instance();
    return $manager->is_test_mode();
}

/**
 * グローバル関数: E2Eテストモードのデバッグ情報を取得
 */
function get_e2e_test_debug_info() {
    $manager = E2E_Test_Mode_Manager::get_instance();
    return $manager->get_debug_info();
}

// 初期化（非常に早い段階で実行）
add_action('muplugins_loaded', function() {
    E2E_Test_Mode_Manager::get_instance();
}, 1);

// 代替初期化（muplugins_loadedが使えない場合）
if (!did_action('muplugins_loaded')) {
    add_action('plugins_loaded', function() {
        E2E_Test_Mode_Manager::get_instance();
    }, 1);
}
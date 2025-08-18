<?php
/**
 * 環境別設定管理
 * 開発環境と本番環境で異なる設定を適用
 *
 * @package Kei_Portfolio_Pro
 * @version 1.0.0
 * @since 2025-08-09
 */

// 直接アクセスを防止
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 環境設定管理クラス
 */
class Kei_Portfolio_Environment_Config {
    
    private static $instance = null;
    private $environment_type = null;
    private $is_development = null;
    private $is_production = null;
    private $config = array();
    
    /**
     * シングルトンパターン
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
        $this->detect_environment();
        $this->setup_config();
        $this->init_hooks();
    }
    
    /**
     * 環境の検出
     */
    private function detect_environment() {
        // WP_ENVIRONMENT_TYPE 定数をチェック
        if (defined('WP_ENVIRONMENT_TYPE')) {
            $this->environment_type = WP_ENVIRONMENT_TYPE;
        }
        // WP_DEBUG の状態から推測
        else if (defined('WP_DEBUG') && WP_DEBUG) {
            $this->environment_type = 'development';
        }
        // ホスト名から推測
        else {
            $host = $_SERVER['HTTP_HOST'] ?? '';
            // 本番ドメイン(.devなど)が "開発" と誤認されないよう、
            // ローカル系ホスト名のみを開発判定に使用する
            if (
                strpos($host, 'localhost') !== false ||
                strpos($host, '127.0.0.1') !== false ||
                strpos($host, '.local') !== false ||
                strpos($host, '.test') !== false
            ) {
                $this->environment_type = 'development';
            } else {
                $this->environment_type = 'production';
            }
        }
        
        // フラグを設定
        $this->is_development = ($this->environment_type === 'development');
        $this->is_production = ($this->environment_type === 'production');
        
        // ログ記録（開発環境のみ）
        if ($this->is_development) {
            error_log("Environment Config: Detected environment type - {$this->environment_type}");
        }
    }
    
    /**
     * 環境別設定の初期化
     */
    private function setup_config() {
        if ($this->is_development) {
            $this->config = array(
                // デバッグ設定
                'debug_enabled' => true,
                'error_display' => false, // セキュリティ上、画面表示は無効
                'error_logging' => true,
                'debug_log_file' => WP_CONTENT_DIR . '/debug.log',
                
                // テスト設定
                'test_files_enabled' => true,
                'emergency_fixes_enabled' => true,
                'admin_notices_enabled' => true,
                
                // パフォーマンス設定
                'cache_enabled' => false,
                'asset_minification' => false,
                'script_debug' => true,
                
                // セキュリティ設定
                'strict_security' => false,
                'ip_whitelist' => array('127.0.0.1', '::1', 'localhost'),
                'allow_file_edit' => true,
                
                // ログレベル
                'log_level' => 'debug',
                'performance_logging' => true,
            );
        } else {
            $this->config = array(
                // 本番環境設定
                'debug_enabled' => false,
                'error_display' => false,
                'error_logging' => true,
                'debug_log_file' => WP_CONTENT_DIR . '/logs/error.log',
                
                // テスト設定
                'test_files_enabled' => false,
                'emergency_fixes_enabled' => false,
                'admin_notices_enabled' => false,
                
                // パフォーマンス設定
                'cache_enabled' => true,
                'asset_minification' => true,
                'script_debug' => false,
                
                // セキュリティ設定
                'strict_security' => true,
                'ip_whitelist' => array(),
                'allow_file_edit' => false,
                
                // ログレベル
                'log_level' => 'error',
                'performance_logging' => false,
            );
        }
    }
    
    /**
     * フックの初期化
     */
    private function init_hooks() {
        add_action('init', array($this, 'apply_environment_settings'), 1);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_environment_assets'), 5);
        
        if ($this->is_development) {
            add_action('admin_notices', array($this, 'development_admin_notice'));
        }
    }
    
    /**
     * 環境設定の適用
     */
    public function apply_environment_settings() {
        // PHP設定の適用
        if ($this->get_config('error_display')) {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
        } else {
            ini_set('display_errors', 0);
            ini_set('display_startup_errors', 0);
        }
        
        if ($this->get_config('error_logging')) {
            ini_set('log_errors', 1);
            $log_file = $this->get_config('debug_log_file');
            if ($log_file) {
                ini_set('error_log', $log_file);
            }
        }
        
        // メモリ制限の設定
        if ($this->is_development) {
            ini_set('memory_limit', '512M');
        } else {
            ini_set('memory_limit', '256M');
        }
        
        // ログ記録
        $this->log('Environment settings applied', 'info');
    }
    
    /**
     * 環境別アセットの読み込み
     */
    public function enqueue_environment_assets() {
        if ($this->is_development) {
            // 開発環境専用のスタイル・スクリプト（存在チェック付き）
            $dev_css_path = get_template_directory() . '/assets/css/dev-debug.css';
            $dev_js_path  = get_template_directory() . '/assets/js/dev-debug.js';

            if (file_exists($dev_css_path)) {
                wp_enqueue_style(
                    'kei-portfolio-dev-style',
                    get_template_directory_uri() . '/assets/css/dev-debug.css',
                    array(),
                    time() // キャッシュ無効化
                );
            }

            $dev_js_enqueued = false;
            if (file_exists($dev_js_path)) {
                wp_enqueue_script(
                    'kei-portfolio-dev-script',
                    get_template_directory_uri() . '/assets/js/dev-debug.js',
                    array('jquery'),
                    time(),
                    true
                );
                $dev_js_enqueued = true;
            }

            // 開発環境情報をJavaScriptに渡す（スクリプトが存在する場合のみ）
            if ($dev_js_enqueued) {
                wp_localize_script('kei-portfolio-dev-script', 'keiPortfolioDev', array(
                    'environment' => $this->environment_type,
                    'debug' => $this->get_config('debug_enabled'),
                    'ajaxUrl' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('kei_portfolio_dev_nonce')
                ));
            }
        }
    }
    
    /**
     * 開発環境の管理者通知
     */
    public function development_admin_notice() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        echo '<div class="notice notice-info is-dismissible">';
        echo '<p><strong>開発環境で実行中</strong></p>';
        echo '<p>現在、開発環境の設定で動作しています。テストファイルやデバッグ機能が有効になっています。</p>';
        echo '<p><small>環境タイプ: ' . esc_html($this->environment_type) . '</small></p>';
        echo '</div>';
    }
    
    /**
     * 設定値の取得
     */
    public function get_config($key, $default = null) {
        return isset($this->config[$key]) ? $this->config[$key] : $default;
    }
    
    /**
     * 環境タイプの取得
     */
    public function get_environment_type() {
        return $this->environment_type;
    }
    
    /**
     * 開発環境かどうかの判定
     */
    public function is_development() {
        return $this->is_development;
    }
    
    /**
     * 本番環境かどうかの判定
     */
    public function is_production() {
        return $this->is_production;
    }
    
    /**
     * IPアドレスが許可リストに含まれるかチェック
     */
    public function is_ip_allowed($ip = null) {
        if ($ip === null) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        }
        
        $whitelist = $this->get_config('ip_whitelist', array());
        
        // 開発環境では追加のIPチェック
        if ($this->is_development) {
            $development_ips = array('127.0.0.1', '::1', 'localhost');
            $whitelist = array_merge($whitelist, $development_ips);
            
            // プライベートIPアドレスを許可
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                return true;
            }
        }
        
        return in_array($ip, $whitelist);
    }
    
    /**
     * テストファイルへのアクセス可否判定
     */
    public function can_access_test_files() {
        if (!$this->get_config('test_files_enabled')) {
            return false;
        }
        
        return $this->is_ip_allowed();
    }
    
    /**
     * 緊急修正機能の有効性判定
     */
    public function can_use_emergency_fixes() {
        return $this->get_config('emergency_fixes_enabled');
    }
    
    /**
     * ログ記録（環境別レベル制御）
     */
    public function log($message, $level = 'info') {
        $allowed_levels = array('debug', 'info', 'warning', 'error');
        $current_level = $this->get_config('log_level', 'error');
        
        // ログレベルの優先度チェック
        $level_priority = array_flip($allowed_levels);
        if (isset($level_priority[$level]) && isset($level_priority[$current_level])) {
            if ($level_priority[$level] < $level_priority[$current_level]) {
                return;
            }
        }
        
        $prefix = 'Environment Config';
        error_log("{$prefix} [{$level}]: {$message}");
    }
    
    /**
     * 設定情報のデバッグ出力
     */
    public function debug_config() {
        if (!$this->is_development) {
            return;
        }
        
        $this->log('Configuration dump: ' . json_encode($this->config), 'debug');
    }
}

/**
 * グローバル関数
 */

/**
 * 環境設定インスタンスの取得
 */
function kei_portfolio_get_env_config() {
    return Kei_Portfolio_Environment_Config::get_instance();
}

/**
 * 開発環境かどうかの判定
 */
function kei_portfolio_is_development() {
    return kei_portfolio_get_env_config()->is_development();
}

/**
 * 本番環境かどうかの判定
 */
function kei_portfolio_is_production() {
    return kei_portfolio_get_env_config()->is_production();
}

/**
 * テストファイルアクセス可否の判定
 */
function kei_portfolio_can_access_tests() {
    return kei_portfolio_get_env_config()->can_access_test_files();
}

/**
 * 環境設定値の取得
 */
function kei_portfolio_get_env_setting($key, $default = null) {
    return kei_portfolio_get_env_config()->get_config($key, $default);
}

// 環境設定の初期化
Kei_Portfolio_Environment_Config::get_instance();

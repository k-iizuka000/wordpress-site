<?php
/**
 * Blog Config Class
 * ブログ機能の設定値一元管理クラス
 * 
 * @package KeiPortfolio
 * @since 1.0.0
 */

namespace KeiPortfolio\Blog;

use KeiPortfolio\Blog\Blog_Logger;

/**
 * ブログ機能のための設定管理クラス
 */
class Blog_Config {
    
    /**
     * キャッシュ設定定数
     */
    private const CACHE_TTL_DEFAULT = 3600;   // 1時間
    private const CACHE_TTL_SHORT = 600;      // 10分
    private const CACHE_TTL_LONG = 86400;     // 24時間
    private const CACHE_TTL_MIN = 300;        // 5分（最小値）
    private const CACHE_TTL_MAX = 86400;      // 24時間（最大値）
    
    /**
     * シングルトンインスタンス
     * @var Blog_Config|null
     */
    private static $instance = null;
    
    /**
     * 設定のキャッシュ
     * @var array
     */
    private $config_cache = [];
    
    /**
     * デフォルト設定値
     * @var array
     */
    private $default_config = [
        // ログ設定
        'logging' => [
            'enabled' => true,
            'level' => 'info',
            'max_file_size' => 5242880,      // 5MB
            'max_files' => 5,
            'enable_email_notifications' => false,
            'email_notification_level' => 'error',
        ],
        
        // パフォーマンス設定
        'performance' => [
            'monitoring_enabled' => false,
            'thresholds' => [
                'page_load_time' => 2.0,
                'query_time' => 0.1,
                'query_count' => 50,
                'memory_usage' => 134217728,   // 128MB
                'memory_peak' => 268435456,    // 256MB
            ],
            'enable_debug_output' => false,
            'enable_daily_reports' => true,
        ],
        
        // ブログ表示設定
        'display' => [
            'posts_per_page' => 10,
            'excerpt_length' => 150,
            'show_featured_image' => true,
            'show_author' => true,
            'show_date' => true,
            'show_categories' => true,
            'show_tags' => true,
            'enable_comments' => true,
            'enable_social_sharing' => true,
            'related_posts_count' => 3,
        ],
        
        // SEO設定
        'seo' => [
            'enable_schema_markup' => true,
            'enable_ogp_tags' => true,
            'enable_twitter_cards' => true,
            'enable_breadcrumbs' => true,
            'enable_auto_meta_description' => true,
            'meta_description_length' => 160,
            'enable_xml_sitemap' => true,
        ],
        
        // キャッシュ設定
        'cache' => [
            'enabled' => true,
            'ttl' => self::CACHE_TTL_DEFAULT, // 1時間
            'enable_object_cache' => true,
            'enable_page_cache' => false,
            'exclude_logged_in_users' => true,
            'cache_group' => 'kei_portfolio_blog',
        ],
        
        // セキュリティ設定
        'security' => [
            'enable_honeypot' => true,
            'enable_rate_limiting' => true,
            'max_requests_per_minute' => 60,
            'enable_ip_blocking' => false,
            'blocked_ips' => [],
            'enable_comment_moderation' => true,
            'auto_approve_registered_users' => true,
        ],
        
        // 通知設定
        'notifications' => [
            'admin_email_on_new_comment' => true,
            'admin_email_on_new_post' => false,
            'user_email_on_comment_reply' => true,
            'performance_alerts' => false,
            'security_alerts' => true,
        ],
        
        // 統合設定
        'integrations' => [
            'google_analytics_id' => '',
            'facebook_pixel_id' => '',
            'twitter_username' => '',
            'enable_amp' => false,
            'enable_pwa' => false,
        ],
        
        // 高度な設定
        'advanced' => [
            'enable_lazy_loading' => true,
            'enable_image_optimization' => true,
            'enable_critical_css' => false,
            'enable_minification' => false,
            'enable_gzip_compression' => true,
            'enable_browser_caching' => true,
            'maintenance_mode' => false,
        ]
    ];
    
    /**
     * 設定キーのバリデーションルール
     * @var array
     */
    private $validation_rules = [
        'posts_per_page' => ['type' => 'integer', 'min' => 1, 'max' => 50],
        'excerpt_length' => ['type' => 'integer', 'min' => 50, 'max' => 500],
        'related_posts_count' => ['type' => 'integer', 'min' => 0, 'max' => 10],
        'cache_ttl' => ['type' => 'integer', 'min' => self::CACHE_TTL_MIN, 'max' => self::CACHE_TTL_MAX],
        'max_requests_per_minute' => ['type' => 'integer', 'min' => 10, 'max' => 1000],
        'meta_description_length' => ['type' => 'integer', 'min' => 120, 'max' => 200],
    ];
    
    /**
     * ログインスタンス
     * @var Blog_Logger
     */
    private $logger;
    
    /**
     * 設定が変更されたかどうか
     * @var bool
     */
    private $config_modified = false;
    
    /**
     * シングルトンパターンでインスタンス取得
     * 
     * @return Blog_Config インスタンス
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
        $this->logger = Blog_Logger::get_instance();
        $this->load_configuration();
        $this->setup_hooks();
    }
    
    /**
     * 設定の読み込み
     * 
     * @return void
     */
    private function load_configuration() {
        // データベースから保存された設定を読み込み
        $saved_config = get_option('blog_config', []);
        
        // デフォルト設定とマージ
        $this->config_cache = $this->merge_configs($this->default_config, $saved_config);
        
        // 環境変数による設定のオーバーライド
        $this->apply_environment_overrides();
        
        // 設定の検証
        $this->validate_configuration();
        
        $this->logger->debug('Blog configuration loaded', [
            'config_keys' => array_keys($this->config_cache),
            'source' => 'database'
        ]);
    }
    
    /**
     * 設定のマージ
     * 
     * @param array $default デフォルト設定
     * @param array $saved 保存された設定
     * @return array マージされた設定
     */
    private function merge_configs($default, $saved) {
        if (empty($saved)) {
            return $default;
        }
        
        $merged = $default;
        
        foreach ($saved as $section => $values) {
            if (isset($merged[$section]) && is_array($values)) {
                $merged[$section] = array_merge($merged[$section], $values);
            } else {
                $merged[$section] = $values;
            }
        }
        
        return $merged;
    }
    
    /**
     * 環境変数による設定のオーバーライド
     * 
     * @return void
     */
    private function apply_environment_overrides() {
        // 環境別設定の適用
        if (defined('WP_ENVIRONMENT_TYPE')) {
            switch (WP_ENVIRONMENT_TYPE) {
                case 'development':
                    $this->config_cache['logging']['enabled'] = true;
                    $this->config_cache['logging']['level'] = 'debug';
                    $this->config_cache['performance']['monitoring_enabled'] = true;
                    $this->config_cache['performance']['enable_debug_output'] = true;
                    $this->config_cache['cache']['enabled'] = false;
                    break;
                    
                case 'staging':
                    $this->config_cache['logging']['enabled'] = true;
                    $this->config_cache['logging']['level'] = 'info';
                    $this->config_cache['performance']['monitoring_enabled'] = true;
                    $this->config_cache['seo']['enable_xml_sitemap'] = false;
                    break;
                    
                case 'production':
                    $this->config_cache['logging']['level'] = 'warning';
                    $this->config_cache['performance']['enable_debug_output'] = false;
                    $this->config_cache['cache']['enabled'] = true;
                    $this->config_cache['advanced']['enable_minification'] = true;
                    $this->config_cache['advanced']['enable_gzip_compression'] = true;
                    break;
            }
        }
        
        // WP_DEBUG設定の考慮
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $this->config_cache['logging']['enabled'] = true;
            $this->config_cache['logging']['level'] = 'debug';
            $this->config_cache['performance']['enable_debug_output'] = true;
        }
        
        // 特定の環境変数による個別オーバーライド
        $env_mappings = [
            'BLOG_CACHE_ENABLED' => 'cache.enabled',
            'BLOG_CACHE_TTL' => 'cache.ttl',
            'BLOG_POSTS_PER_PAGE' => 'display.posts_per_page',
            'BLOG_PERFORMANCE_MONITORING' => 'performance.monitoring_enabled',
            'BLOG_MAINTENANCE_MODE' => 'advanced.maintenance_mode'
        ];
        
        foreach ($env_mappings as $env_var => $config_path) {
            if (defined($env_var)) {
                $this->set_config_by_path($config_path, constant($env_var));
            }
        }
    }
    
    /**
     * 設定パスによる値の設定
     * 
     * @param string $path 設定パス（例: 'cache.enabled'）
     * @param mixed $value 設定値
     * @return void
     */
    private function set_config_by_path($path, $value) {
        $keys = explode('.', $path);
        $config = &$this->config_cache;
        
        foreach ($keys as $key) {
            if (!isset($config[$key])) {
                $config[$key] = [];
            }
            $config = &$config[$key];
        }
        
        $config = $value;
    }
    
    /**
     * 設定の検証
     * 
     * @return void
     */
    private function validate_configuration() {
        $errors = [];
        
        foreach ($this->validation_rules as $config_key => $rules) {
            $value = $this->get_by_key($config_key);
            
            if ($value === null) {
                continue; // 設定が存在しない場合はスキップ
            }
            
            // 型チェック
            if (isset($rules['type'])) {
                switch ($rules['type']) {
                    case 'integer':
                        if (!is_numeric($value) || !is_int($value + 0)) {
                            $errors[] = "Config '{$config_key}' must be an integer";
                        }
                        $value = intval($value);
                        break;
                        
                    case 'boolean':
                        if (!is_bool($value)) {
                            $errors[] = "Config '{$config_key}' must be a boolean";
                        }
                        break;
                        
                    case 'string':
                        if (!is_string($value)) {
                            $errors[] = "Config '{$config_key}' must be a string";
                        }
                        break;
                }
            }
            
            // 範囲チェック
            if (isset($rules['min']) && $value < $rules['min']) {
                $errors[] = "Config '{$config_key}' must be at least {$rules['min']}";
            }
            
            if (isset($rules['max']) && $value > $rules['max']) {
                $errors[] = "Config '{$config_key}' must be at most {$rules['max']}";
            }
        }
        
        if (!empty($errors)) {
            $this->logger->warning('Configuration validation errors', $errors);
            
            // 重要な設定エラーがある場合はデフォルト値にフォールバック
            foreach ($errors as $error) {
                if (strpos($error, 'must be') !== false) {
                    $this->logger->error('Critical configuration error, applying defaults', ['error' => $error]);
                    // 必要に応じてデフォルト値を適用する処理
                }
            }
        }
    }
    
    /**
     * WordPress フックの設定
     * 
     * @return void
     */
    private function setup_hooks() {
        // 設定保存時の処理
        add_action('update_option_blog_config', [$this, 'on_config_update'], 10, 3);
        
        // 管理画面での設定ページ追加
        add_action('admin_menu', [$this, 'add_admin_menu']);
        
        // AJAX エンドポイントの設定
        add_action('wp_ajax_blog_update_config', [$this, 'handle_ajax_config_update']);
        add_action('wp_ajax_blog_reset_config', [$this, 'handle_ajax_config_reset']);
        add_action('wp_ajax_blog_export_config', [$this, 'handle_ajax_config_export']);
        add_action('wp_ajax_blog_import_config', [$this, 'handle_ajax_config_import']);
        
        // シャットダウン時の処理
        add_action('shutdown', [$this, 'save_if_modified']);
        
        // REST API エンドポイントの登録
        add_action('rest_api_init', [$this, 'register_rest_endpoints']);
    }
    
    /**
     * 設定値の取得
     * 
     * @param string $section 設定セクション
     * @param string|null $key 設定キー（nullの場合はセクション全体）
     * @param mixed $default デフォルト値
     * @return mixed 設定値
     */
    public function get($section, $key = null, $default = null) {
        if (!isset($this->config_cache[$section])) {
            return $default;
        }
        
        if ($key === null) {
            return $this->config_cache[$section];
        }
        
        return $this->config_cache[$section][$key] ?? $default;
    }
    
    /**
     * キーによる設定値の取得（フラット）
     * 
     * @param string $key 設定キー
     * @param mixed $default デフォルト値
     * @return mixed 設定値
     */
    private function get_by_key($key, $default = null) {
        foreach ($this->config_cache as $section => $values) {
            if (isset($values[$key])) {
                return $values[$key];
            }
        }
        return $default;
    }
    
    /**
     * 設定値の設定
     * 
     * @param string $section 設定セクション
     * @param string|array $key 設定キーまたは設定配列
     * @param mixed $value 設定値
     * @return bool 設定の成功/失敗
     */
    public function set($section, $key, $value = null) {
        if (!isset($this->config_cache[$section])) {
            $this->config_cache[$section] = [];
        }
        
        if (is_array($key)) {
            // 配列で複数の設定を一度に更新
            foreach ($key as $k => $v) {
                if ($this->validate_single_config($section, $k, $v)) {
                    $this->config_cache[$section][$k] = $v;
                    $this->config_modified = true;
                }
            }
        } else {
            // 単一の設定を更新
            if ($this->validate_single_config($section, $key, $value)) {
                $this->config_cache[$section][$key] = $value;
                $this->config_modified = true;
                
                $this->logger->debug("Configuration updated: {$section}.{$key}", [
                    'old_value' => $this->config_cache[$section][$key] ?? null,
                    'new_value' => $value
                ]);
                
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 単一設定の検証
     * 
     * @param string $section セクション
     * @param string $key キー
     * @param mixed $value 値
     * @return bool 検証結果
     */
    private function validate_single_config($section, $key, $value) {
        // セクション別の検証ロジック
        switch ($section) {
            case 'logging':
                return $this->validate_logging_config($key, $value);
                
            case 'performance':
                return $this->validate_performance_config($key, $value);
                
            case 'display':
                return $this->validate_display_config($key, $value);
                
            case 'security':
                return $this->validate_security_config($key, $value);
                
            default:
                // 基本的な型チェック
                return $this->validate_basic_type($key, $value);
        }
    }
    
    /**
     * ログ設定の検証
     * 
     * @param string $key キー
     * @param mixed $value 値
     * @return bool 検証結果
     */
    private function validate_logging_config($key, $value) {
        switch ($key) {
            case 'enabled':
            case 'enable_email_notifications':
                return is_bool($value);
                
            case 'level':
                return in_array($value, ['debug', 'info', 'warning', 'error', 'critical']);
                
            case 'max_file_size':
            case 'max_files':
                return is_numeric($value) && $value > 0;
                
            case 'email_notification_level':
                return in_array($value, ['warning', 'error', 'critical']);
                
            default:
                return true;
        }
    }
    
    /**
     * パフォーマンス設定の検証
     * 
     * @param string $key キー
     * @param mixed $value 値
     * @return bool 検証結果
     */
    private function validate_performance_config($key, $value) {
        switch ($key) {
            case 'monitoring_enabled':
            case 'enable_debug_output':
            case 'enable_daily_reports':
                return is_bool($value);
                
            case 'thresholds':
                return is_array($value) && $this->validate_performance_thresholds($value);
                
            default:
                return true;
        }
    }
    
    /**
     * パフォーマンス閾値の検証
     * 
     * @param array $thresholds 閾値設定
     * @return bool 検証結果
     */
    private function validate_performance_thresholds($thresholds) {
        $required_keys = ['page_load_time', 'query_time', 'query_count', 'memory_usage', 'memory_peak'];
        
        foreach ($required_keys as $key) {
            if (!isset($thresholds[$key]) || !is_numeric($thresholds[$key]) || $thresholds[$key] <= 0) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * 表示設定の検証
     * 
     * @param string $key キー
     * @param mixed $value 値
     * @return bool 検証結果
     */
    private function validate_display_config($key, $value) {
        switch ($key) {
            case 'posts_per_page':
            case 'excerpt_length':
            case 'related_posts_count':
                return is_numeric($value) && $value >= 0;
                
            case 'show_featured_image':
            case 'show_author':
            case 'show_date':
            case 'show_categories':
            case 'show_tags':
            case 'enable_comments':
            case 'enable_social_sharing':
                return is_bool($value);
                
            default:
                return true;
        }
    }
    
    /**
     * セキュリティ設定の検証
     * 
     * @param string $key キー
     * @param mixed $value 値
     * @return bool 検証結果
     */
    private function validate_security_config($key, $value) {
        switch ($key) {
            case 'enable_honeypot':
            case 'enable_rate_limiting':
            case 'enable_ip_blocking':
            case 'enable_comment_moderation':
            case 'auto_approve_registered_users':
                return is_bool($value);
                
            case 'max_requests_per_minute':
                return is_numeric($value) && $value > 0 && $value <= 1000;
                
            case 'blocked_ips':
                return is_array($value) && $this->validate_ip_list($value);
                
            default:
                return true;
        }
    }
    
    /**
     * IPアドレスリストの検証
     * 
     * @param array $ips IPアドレス配列
     * @return bool 検証結果
     */
    private function validate_ip_list($ips) {
        foreach ($ips as $ip) {
            if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * 基本的な型の検証
     * 
     * @param string $key キー
     * @param mixed $value 値
     * @return bool 検証結果
     */
    private function validate_basic_type($key, $value) {
        if (isset($this->validation_rules[$key])) {
            $rules = $this->validation_rules[$key];
            
            if (isset($rules['type'])) {
                switch ($rules['type']) {
                    case 'integer':
                        return is_numeric($value);
                    case 'boolean':
                        return is_bool($value);
                    case 'string':
                        return is_string($value);
                    case 'array':
                        return is_array($value);
                }
            }
        }
        
        return true;
    }
    
    /**
     * 設定の保存
     * 
     * @return bool 保存の成功/失敗
     */
    public function save() {
        $result = update_option('blog_config', $this->config_cache);
        
        if ($result) {
            $this->config_modified = false;
            $this->logger->info('Blog configuration saved to database');
        } else {
            $this->logger->error('Failed to save blog configuration to database');
        }
        
        return $result;
    }
    
    /**
     * 変更がある場合に設定を保存
     * 
     * @return void
     */
    public function save_if_modified() {
        if ($this->config_modified) {
            $this->save();
        }
    }
    
    /**
     * 設定のリセット
     * 
     * @param string|null $section 特定セクションのみリセット（nullの場合は全体）
     * @return bool リセットの成功/失敗
     */
    public function reset($section = null) {
        if ($section === null) {
            $this->config_cache = $this->default_config;
            $this->logger->info('All blog configuration reset to defaults');
        } elseif (isset($this->default_config[$section])) {
            $this->config_cache[$section] = $this->default_config[$section];
            $this->logger->info("Blog configuration section '{$section}' reset to defaults");
        } else {
            return false;
        }
        
        $this->config_modified = true;
        return $this->save();
    }
    
    /**
     * 設定のエクスポート
     * 
     * @param bool $include_sensitive 機密情報を含むかどうか
     * @return array エクスポートデータ
     */
    public function export_config($include_sensitive = false) {
        $export_data = [
            'export_timestamp' => current_time('Y-m-d H:i:s'),
            'wp_version' => get_bloginfo('version'),
            'theme_version' => wp_get_theme()->get('Version'),
            'config' => $this->config_cache
        ];
        
        if (!$include_sensitive) {
            // 機密情報を除外
            unset($export_data['config']['integrations']['google_analytics_id']);
            unset($export_data['config']['integrations']['facebook_pixel_id']);
            unset($export_data['config']['security']['blocked_ips']);
        }
        
        return $export_data;
    }
    
    /**
     * 設定のインポート
     * 
     * @param array $import_data インポートデータ
     * @param bool $merge 既存設定とマージするかどうか
     * @return bool インポートの成功/失敗
     */
    public function import_config($import_data, $merge = true) {
        if (!is_array($import_data) || !isset($import_data['config'])) {
            $this->logger->error('Invalid import data format');
            return false;
        }
        
        $import_config = $import_data['config'];
        
        // 設定の検証
        if (!$this->validate_import_config($import_config)) {
            $this->logger->error('Import configuration failed validation');
            return false;
        }
        
        if ($merge) {
            $this->config_cache = $this->merge_configs($this->config_cache, $import_config);
        } else {
            $this->config_cache = $this->merge_configs($this->default_config, $import_config);
        }
        
        $this->config_modified = true;
        $success = $this->save();
        
        if ($success) {
            $this->logger->info('Blog configuration imported successfully', [
                'merge_mode' => $merge,
                'import_timestamp' => $import_data['export_timestamp'] ?? 'unknown'
            ]);
        }
        
        return $success;
    }
    
    /**
     * インポート設定の検証
     * 
     * @param array $config 設定配列
     * @return bool 検証結果
     */
    private function validate_import_config($config) {
        // 基本構造の検証
        $required_sections = ['logging', 'performance', 'display', 'seo', 'cache'];
        
        foreach ($required_sections as $section) {
            if (!isset($config[$section]) || !is_array($config[$section])) {
                return false;
            }
        }
        
        // 各セクションの詳細検証
        foreach ($config as $section => $values) {
            if (!is_array($values)) {
                continue;
            }
            
            foreach ($values as $key => $value) {
                if (!$this->validate_single_config($section, $key, $value)) {
                    $this->logger->warning("Invalid config in import: {$section}.{$key}", [
                        'value' => $value
                    ]);
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * 設定更新時の処理
     * 
     * @param mixed $old_value 古い値
     * @param mixed $value 新しい値
     * @param string $option オプション名
     * @return void
     */
    public function on_config_update($old_value, $value, $option) {
        $this->logger->info('Blog configuration updated via WordPress options', [
            'option' => $option,
            'changes' => $this->calculate_config_diff($old_value, $value)
        ]);
        
        // キャッシュの更新
        $this->config_cache = $value;
        
        // 設定変更に基づく処理の実行
        $this->handle_config_changes($old_value, $value);
    }
    
    /**
     * 設定差分の計算
     * 
     * @param array $old_config 古い設定
     * @param array $new_config 新しい設定
     * @return array 差分情報
     */
    private function calculate_config_diff($old_config, $new_config) {
        $changes = [];
        
        foreach ($new_config as $section => $values) {
            if (!isset($old_config[$section])) {
                $changes[$section] = 'added';
                continue;
            }
            
            foreach ($values as $key => $value) {
                $old_value = $old_config[$section][$key] ?? null;
                if ($old_value !== $value) {
                    $changes["{$section}.{$key}"] = [
                        'from' => $old_value,
                        'to' => $value
                    ];
                }
            }
        }
        
        return $changes;
    }
    
    /**
     * 設定変更の処理
     * 
     * @param array $old_config 古い設定
     * @param array $new_config 新しい設定
     * @return void
     */
    private function handle_config_changes($old_config, $new_config) {
        // キャッシュ設定が変更された場合
        if (($old_config['cache']['enabled'] ?? false) !== ($new_config['cache']['enabled'] ?? false)) {
            if ($new_config['cache']['enabled']) {
                $this->logger->info('Blog cache enabled');
            } else {
                $this->logger->info('Blog cache disabled - clearing existing cache');
                $this->clear_all_cache();
            }
        }
        
        // パフォーマンス監視設定の変更
        if (($old_config['performance']['monitoring_enabled'] ?? false) !== ($new_config['performance']['monitoring_enabled'] ?? false)) {
            $this->logger->info('Performance monitoring setting changed', [
                'enabled' => $new_config['performance']['monitoring_enabled']
            ]);
        }
        
        // セキュリティ設定の変更
        if (($old_config['advanced']['maintenance_mode'] ?? false) !== ($new_config['advanced']['maintenance_mode'] ?? false)) {
            if ($new_config['advanced']['maintenance_mode']) {
                $this->logger->warning('Maintenance mode enabled');
            } else {
                $this->logger->info('Maintenance mode disabled');
            }
        }
    }
    
    /**
     * キャッシュのクリア
     * 
     * @return void
     */
    private function clear_all_cache() {
        $cache_group = $this->get('cache', 'cache_group', 'kei_portfolio_blog');
        wp_cache_flush_group($cache_group);
        
        // オブジェクトキャッシュのクリア
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
    }
    
    /**
     * 管理メニューの追加
     * 
     * @return void
     */
    public function add_admin_menu() {
        add_options_page(
            'Blog Configuration',
            'Blog Config',
            'manage_options',
            'blog-config',
            [$this, 'render_admin_page']
        );
    }
    
    /**
     * 管理ページのレンダリング
     * 
     * @return void
     */
    public function render_admin_page() {
        // 簡単な管理ページHTML（実際の実装では詳細なUIを構築）
        ?>
        <div class="wrap">
            <h1>Blog Configuration</h1>
            <div id="blog-config-app">
                <p>Blog configuration management interface would be implemented here.</p>
                <p>Current config sections: <?php echo esc_html(implode(', ', array_keys($this->config_cache))); ?></p>
            </div>
        </div>
        <?php
    }
    
    /**
     * AJAX 設定更新のハンドリング
     * 
     * @return void
     */
    public function handle_ajax_config_update() {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'blog_config_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $section = sanitize_text_field($_POST['section'] ?? '');
        $key = sanitize_text_field($_POST['key'] ?? '');
        $value = $_POST['value'] ?? null;
        
        // 値のサニタイゼーション
        $value = $this->sanitize_config_value($key, $value);
        
        if ($this->set($section, $key, $value)) {
            wp_send_json_success('Configuration updated successfully');
        } else {
            wp_send_json_error('Failed to update configuration');
        }
    }
    
    /**
     * 設定値のサニタイゼーション
     * 
     * @param string $key キー
     * @param mixed $value 値
     * @return mixed サニタイゼーション済みの値
     */
    private function sanitize_config_value($key, $value) {
        if (is_string($value)) {
            return sanitize_text_field($value);
        } elseif (is_array($value)) {
            return array_map('sanitize_text_field', $value);
        } elseif (is_bool($value)) {
            return (bool) $value;
        } elseif (is_numeric($value)) {
            return is_float($value) ? floatval($value) : intval($value);
        }
        
        return $value;
    }
    
    /**
     * REST API エンドポイントの登録
     * 
     * @return void
     */
    public function register_rest_endpoints() {
        register_rest_route('blog/v1', '/config', [
            'methods' => 'GET',
            'callback' => [$this, 'rest_get_config'],
            'permission_callback' => function() {
                return current_user_can('manage_options');
            }
        ]);
        
        register_rest_route('blog/v1', '/config', [
            'methods' => 'POST',
            'callback' => [$this, 'rest_update_config'],
            'permission_callback' => function() {
                return current_user_can('manage_options');
            }
        ]);
    }
    
    /**
     * REST API 設定取得
     * 
     * @param WP_REST_Request $request リクエスト
     * @return WP_REST_Response レスポンス
     */
    public function rest_get_config($request) {
        $section = $request->get_param('section');
        
        if ($section) {
            $config = $this->get($section);
        } else {
            $config = $this->config_cache;
        }
        
        return rest_ensure_response([
            'success' => true,
            'data' => $config
        ]);
    }
    
    /**
     * REST API 設定更新
     * 
     * @param WP_REST_Request $request リクエスト
     * @return WP_REST_Response レスポンス
     */
    public function rest_update_config($request) {
        $section = $request->get_param('section');
        $key = $request->get_param('key');
        $value = $request->get_param('value');
        
        if (empty($section) || empty($key)) {
            return rest_ensure_response([
                'success' => false,
                'message' => 'Section and key are required'
            ]);
        }
        
        if ($this->set($section, $key, $value)) {
            return rest_ensure_response([
                'success' => true,
                'message' => 'Configuration updated successfully'
            ]);
        } else {
            return rest_ensure_response([
                'success' => false,
                'message' => 'Failed to update configuration'
            ]);
        }
    }
    
    /**
     * 全設定の取得
     * 
     * @return array 全設定
     */
    public function get_all_config() {
        return $this->config_cache;
    }
    
    /**
     * デフォルト設定の取得
     * 
     * @return array デフォルト設定
     */
    public function get_default_config() {
        return $this->default_config;
    }
    
    /**
     * 設定が変更されているかの確認
     * 
     * @return bool 変更されているかどうか
     */
    public function is_modified() {
        return $this->config_modified;
    }
}
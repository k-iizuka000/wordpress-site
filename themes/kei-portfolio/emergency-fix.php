<?php
/**
 * 緊急修正パッチ
 * クリティカルエラーを一時的に回避
 *
 * @package Kei_Portfolio_Pro
 * @version 1.0.0
 * @since 2025-08-08
 */

// 直接アクセスを防止
if (!defined('ABSPATH')) {
    exit;
}

// ログファイルパス定数の定義（設定可能）
if (!defined('KEI_PORTFOLIO_DEBUG_LOG')) {
    define('KEI_PORTFOLIO_DEBUG_LOG', apply_filters('kei_portfolio_debug_log_path', WP_CONTENT_DIR . '/debug.log'));
}

/**
 * 1. エラーハンドリングの強化
 * セッション開始前にheaderが送信されているかチェック
 */
add_action('init', 'kei_portfolio_emergency_session_fix', 1);

function kei_portfolio_emergency_session_fix() {
    // セッションが既に開始されているかチェック
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    
    // ヘッダーが既に送信されているかチェック
    if (headers_sent($filename, $linenum)) {
        error_log("Emergency Fix: Headers already sent at $filename:$linenum");
        return;
    }
    
    try {
        // セキュアなセッション設定
        $session_config = [
            'cookie_httponly' => true,
            'cookie_secure' => is_ssl(),
            'cookie_samesite' => 'Lax',
            'use_strict_mode' => true,
            'cookie_lifetime' => 0, // ブラウザ終了時に削除
            'gc_maxlifetime' => 1440, // 24分
            'gc_probability' => 1,
            'gc_divisor' => 100
        ];
        
        // WordPressのクッキー設定と整合性を保つ
        if (defined('COOKIEPATH')) {
            $session_config['cookie_path'] = COOKIEPATH;
        }
        if (defined('COOKIEDOMAIN')) {
            $session_config['cookie_domain'] = COOKIEDOMAIN;
        }
        
        // セッション開始
        if (session_start($session_config)) {
            error_log('Emergency Fix: Session started successfully');
        } else {
            error_log('Emergency Fix: Session start failed');
        }
        
    } catch (Exception $e) {
        error_log('Emergency Fix: Session start exception - ' . $e->getMessage());
    } catch (Error $e) {
        error_log('Emergency Fix: Session start fatal error - ' . $e->getMessage());
    }
}

/**
 * 2. PHPメモリ制限の一時的な増加
 */
add_action('init', 'kei_portfolio_emergency_memory_fix', 2);

function kei_portfolio_emergency_memory_fix() {
    // WordPress定数が未定義の場合のみ設定
    if (!defined('WP_MEMORY_LIMIT')) {
        define('WP_MEMORY_LIMIT', '256M');
        error_log('Emergency Fix: WP_MEMORY_LIMIT set to 256M');
    }
    
    if (!defined('WP_MAX_MEMORY_LIMIT')) {
        define('WP_MAX_MEMORY_LIMIT', '512M');
        error_log('Emergency Fix: WP_MAX_MEMORY_LIMIT set to 512M');
    }
    
    // 現在のメモリ使用量をログに記録
    $memory_usage = memory_get_usage(true);
    $memory_peak = memory_get_peak_usage(true);
    error_log(sprintf(
        'Emergency Fix: Memory usage - Current: %s, Peak: %s, Limit: %s',
        size_format($memory_usage),
        size_format($memory_peak),
        ini_get('memory_limit')
    ));
    
    // メモリ不足の警告レベルを設定（80%使用時）
    $memory_limit = wp_convert_hr_to_bytes(ini_get('memory_limit'));
    if ($memory_usage > ($memory_limit * 0.8)) {
        error_log('Emergency Fix: WARNING - Memory usage is over 80% of limit');
    }
}

/**
 * 3. デバッグモード有効化（開発環境のみ）
 */
add_action('init', 'kei_portfolio_emergency_debug_setup', 3);

function kei_portfolio_emergency_debug_setup() {
    // デバッグ環境の判定を強化
    $is_debug_env = (
        (defined('WP_DEBUG') && WP_DEBUG) ||
        (defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE === 'development') ||
        (isset($_SERVER['HTTP_HOST']) && (
            strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ||
            strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false ||
            strpos($_SERVER['HTTP_HOST'], '.local') !== false ||
            strpos($_SERVER['HTTP_HOST'], '.dev') !== false
        ))
    );
    
    if ($is_debug_env) {
        // エラー表示を無効化（セキュリティ上の理由）
        ini_set('display_errors', 0);
        ini_set('display_startup_errors', 0);
        
        // エラーログを有効化
        ini_set('log_errors', 1);
        
        // ログファイルパスの設定
        $log_path = defined('KEI_PORTFOLIO_DEBUG_LOG') ? KEI_PORTFOLIO_DEBUG_LOG : WP_CONTENT_DIR . '/debug.log';
        
        // ログディレクトリが書き込み可能かチェック
        $log_dir = dirname($log_path);
        if (!is_writable($log_dir)) {
            error_log("Emergency Fix: Log directory not writable: $log_dir");
            // 代替ログパスを使用
            $log_path = sys_get_temp_dir() . '/wp-emergency-debug.log';
        }
        
        ini_set('error_log', $log_path);
        
        // デバッグ情報をログに記録
        error_log('Emergency Fix: Debug mode activated');
        error_log('Emergency Fix: Environment - ' . (defined('WP_ENVIRONMENT_TYPE') ? WP_ENVIRONMENT_TYPE : 'undefined'));
        error_log('Emergency Fix: WordPress version - ' . get_bloginfo('version'));
        error_log('Emergency Fix: PHP version - ' . PHP_VERSION);
        error_log('Emergency Fix: Log file - ' . $log_path);
        
        // 重要なPHP設定を記録
        $php_settings = [
            'max_execution_time' => ini_get('max_execution_time'),
            'max_input_vars' => ini_get('max_input_vars'),
            'post_max_size' => ini_get('post_max_size'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'memory_limit' => ini_get('memory_limit'),
        ];
        
        error_log('Emergency Fix: PHP Settings - ' . json_encode($php_settings));
    }
}

/**
 * 4. WordPress関連のエラー処理強化
 */
add_action('init', 'kei_portfolio_emergency_wp_error_handling', 4);

function kei_portfolio_emergency_wp_error_handling() {
    // クリティカルなWordPressエラーをキャッチ
    add_action('wp_loaded', function() {
        // 必要な関数が存在するかチェック
        $required_functions = [
            'wp_enqueue_style',
            'wp_enqueue_script',
            'get_template_directory',
            'get_template_directory_uri',
            'wp_localize_script'
        ];
        
        foreach ($required_functions as $function) {
            if (!function_exists($function)) {
                error_log("Emergency Fix: Critical WordPress function missing: $function");
            }
        }
        
        // 重要な定数の存在確認
        $required_constants = [
            'ABSPATH',
            'WP_CONTENT_DIR',
            'WP_CONTENT_URL'
        ];
        
        foreach ($required_constants as $constant) {
            if (!defined($constant)) {
                error_log("Emergency Fix: Critical WordPress constant missing: $constant");
            }
        }
        
        // テーマディレクトリの存在確認
        $theme_dir = get_template_directory();
        if (!is_dir($theme_dir)) {
            error_log("Emergency Fix: Theme directory not found: $theme_dir");
        }
        
        // 重要なテーマファイルの存在確認
        $critical_files = [
            'functions.php',
            'index.php',
            'style.css'
        ];
        
        foreach ($critical_files as $file) {
            $file_path = $theme_dir . '/' . $file;
            if (!file_exists($file_path)) {
                error_log("Emergency Fix: Critical theme file missing: $file_path");
            }
        }
    });
}

/**
 * 5. 緊急時の安全措置
 * 重大なエラーが発生した場合の代替処理
 */
add_action('wp_head', 'kei_portfolio_emergency_safety_check', 1);

function kei_portfolio_emergency_safety_check() {
    // ヘッダーが正常に読み込まれているかチェック
    if (!did_action('wp_head')) {
        error_log('Emergency Fix: wp_head action may not be working properly');
    }
    
    // 必要最小限のスタイルが読み込まれているかチェック
    global $wp_styles;
    if (empty($wp_styles->registered)) {
        error_log('Emergency Fix: No styles registered - potential CSS loading issue');
    }
    
    // JavaScriptエラーの検出用（フロントエンド）
    if (defined('WP_DEBUG') && WP_DEBUG) {
        echo "\n<!-- Emergency Fix: Debug mode active -->\n";
        echo "<script>\n";
        echo "window.addEventListener('error', function(e) {\n";
        echo "    console.error('Emergency Fix - JS Error:', e.message, 'at', e.filename + ':' + e.lineno);\n";
        echo "});\n";
        echo "window.addEventListener('unhandledrejection', function(e) {\n";
        echo "    console.error('Emergency Fix - Promise Rejection:', e.reason);\n";
        echo "});\n";
        echo "</script>\n";
    }
}

/**
 * 6. 緊急時の管理者通知
 * 重大なエラーを管理者に通知
 */
add_action('admin_notices', 'kei_portfolio_emergency_admin_notice');

function kei_portfolio_emergency_admin_notice() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // 緊急修正が有効であることを通知
    echo '<div class="notice notice-warning is-dismissible">';
    echo '<p><strong>緊急修正パッチが有効です</strong></p>';
    echo '<p>サイトの安定性向上のため緊急修正パッチが適用されています。問題が解決したら無効化することをお勧めします。</p>';
    echo '<p><small>詳細: /themes/kei-portfolio/emergency-fix.php</small></p>';
    echo '</div>';
    
    // エラーログファイルの存在確認と通知
    $log_file = defined('KEI_PORTFOLIO_DEBUG_LOG') ? KEI_PORTFOLIO_DEBUG_LOG : WP_CONTENT_DIR . '/debug.log';
    if (file_exists($log_file) && filesize($log_file) > 0) {
        echo '<div class="notice notice-info is-dismissible">';
        echo '<p><strong>デバッグログが記録されています</strong></p>';
        echo '<p>エラーの詳細は debug.log ファイルをご確認ください。</p>';
        echo '<p><small>ファイルサイズ: ' . size_format(filesize($log_file)) . '</small></p>';
        echo '</div>';
    }
}

// 緊急修正パッチの読み込み完了をログに記録
error_log('Emergency Fix: Patch loaded successfully - ' . date('Y-m-d H:i:s'));
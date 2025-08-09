<?php
/**
 * Security features implementation
 * 
 * セキュリティヘッダーの設定とCSP実装
 * - Content Security Policy (CSP)
 * - セキュリティヘッダーの追加
 * - XSSプロテクション
 * 
 * @package Kei_Portfolio_Pro
 * @since 1.0.0
 */

// 直接アクセスを防ぐ
if (!defined('ABSPATH')) {
    exit;
}

/**
 * セキュリティヘッダーの設定
 * 
 * サーバーサイドでセキュリティヘッダーを送信する
 * JavaScriptによるCSPメタタグよりも確実に適用される
 */
add_action('send_headers', 'kei_portfolio_send_security_headers');

function kei_portfolio_send_security_headers() {
    // 管理画面では異なるCSPポリシーが必要な場合があるため除外
    if (is_admin()) {
        return;
    }

    // Content Security Policy (CSP)
    $csp_policy = [
        "default-src 'self'",
        "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://www.googletagmanager.com https://www.google-analytics.com",
        "style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://fonts.googleapis.com https://cdn.jsdelivr.net",
        "img-src 'self' data: https: http:",
        "font-src 'self' data: https://fonts.gstatic.com https://cdn.jsdelivr.net",
        "connect-src 'self' https://www.google-analytics.com",
        "object-src 'none'",
        "base-uri 'self'",
        "frame-ancestors 'self'",
        "form-action 'self'"
    ];

    // CSPポリシーをフィルターで変更可能にする
    $csp_policy = apply_filters('kei_portfolio_csp_policy', $csp_policy);
    
    // CSPヘッダーを送信
    header("Content-Security-Policy: " . implode('; ', $csp_policy));

    // その他のセキュリティヘッダー
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: SAMEORIGIN");
    header("X-XSS-Protection: 1; mode=block");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
    
    // HTTPS環境でのみHSTSを有効化
    if (is_ssl()) {
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
    }
}

/**
 * 開発環境での緩和されたCSPポリシー
 */
add_filter('kei_portfolio_csp_policy', 'kei_portfolio_dev_csp_policy');

function kei_portfolio_dev_csp_policy($csp_policy) {
    // WP_DEBUGが有効の場合はより緩和されたポリシーを適用
    if (defined('WP_DEBUG') && WP_DEBUG) {
        $csp_policy = array_map(function($policy) {
            // 開発環境でのホットリロードやデバッグツールに対応
            if (strpos($policy, 'script-src') === 0) {
                return "script-src 'self' 'unsafe-inline' 'unsafe-eval' https: http: localhost:* ws: wss:";
            }
            if (strpos($policy, 'connect-src') === 0) {
                return "connect-src 'self' https: http: ws: wss: localhost:*";
            }
            return $policy;
        }, $csp_policy);
    }
    
    return $csp_policy;
}

/**
 * Ajax非同期リクエストの検証強化
 */
add_action('wp_ajax_*', 'kei_portfolio_verify_ajax_request', 1);
add_action('wp_ajax_nopriv_*', 'kei_portfolio_verify_ajax_request', 1);

function kei_portfolio_verify_ajax_request() {
    // リファラーの検証
    if (!wp_get_referer()) {
        wp_die(__('不正なリクエストです。', 'kei-portfolio'), 403);
    }

    // User-Agentの基本検証
    if (!isset($_SERVER['HTTP_USER_AGENT']) || empty($_SERVER['HTTP_USER_AGENT'])) {
        wp_die(__('不正なリクエストです。', 'kei-portfolio'), 403);
    }

    // X-Requested-Withヘッダーの確認（Ajax判定）
    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
        $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
        // kei-portfolio独自のヘッダーも確認
        if (!isset($_SERVER['HTTP_X_KEIPORTFOLIO_REQUEST'])) {
            wp_die(__('Ajax以外のアクセスは許可されていません。', 'kei-portfolio'), 403);
        }
    }
}

/**
 * フォーム送信時のCSRF対策強化
 */
add_action('wp_loaded', 'kei_portfolio_enhance_form_security');

function kei_portfolio_enhance_form_security() {
    // コメントフォームのnonce追加
    add_filter('comment_form_field_comment', 'kei_portfolio_add_comment_nonce');
    
    // 検索フォームのnonce追加
    add_filter('get_search_form', 'kei_portfolio_add_search_nonce');
}

/**
 * コメントフォームにnonce追加
 */
function kei_portfolio_add_comment_nonce($comment_field) {
    $nonce_field = wp_nonce_field('comment_form_nonce', 'comment_nonce', true, false);
    return $comment_field . $nonce_field;
}

/**
 * 検索フォームにnonce追加
 */
function kei_portfolio_add_search_nonce($form) {
    $nonce_field = wp_nonce_field('search_form_nonce', 'search_nonce', true, false);
    // フォーム内の最初のinputの前にnonce fieldを挿入
    $form = str_replace('<input', $nonce_field . '<input', $form);
    return $form;
}

/**
 * コメント送信時のnonce検証
 */
add_action('pre_comment_on_post', 'kei_portfolio_verify_comment_nonce');

function kei_portfolio_verify_comment_nonce() {
    if (!isset($_POST['comment_nonce']) || 
        !wp_verify_nonce($_POST['comment_nonce'], 'comment_form_nonce')) {
        wp_die(__('セキュリティチェックに失敗しました。', 'kei-portfolio'));
    }
}

/**
 * ファイルアップロードのセキュリティ強化
 */
add_filter('upload_mimes', 'kei_portfolio_restrict_upload_mimes');

function kei_portfolio_restrict_upload_mimes($mimes) {
    // 危険な拡張子を除去
    unset($mimes['exe']);
    unset($mimes['php']);
    unset($mimes['js']);
    unset($mimes['swf']);
    
    // 管理者以外はより厳しく制限
    if (!current_user_can('manage_options')) {
        // 管理者以外は画像とPDFのみ許可
        $allowed_mimes = [
            'jpg|jpeg|jpe' => 'image/jpeg',
            'gif' => 'image/gif',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf'
        ];
        return $allowed_mimes;
    }
    
    return $mimes;
}

/**
 * ファイルアップロード前のセキュリティチェック
 */
add_filter('wp_handle_upload_prefilter', 'kei_portfolio_security_check_upload');

function kei_portfolio_security_check_upload($file) {
    // ファイル名のサニタイズ
    $file['name'] = sanitize_file_name($file['name']);
    
    // ファイルサイズ制限（管理者以外は5MB）
    if (!current_user_can('manage_options')) {
        $max_size = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $max_size) {
            $file['error'] = __('ファイルサイズが大きすぎます（最大5MB）。', 'kei-portfolio');
            return $file;
        }
    }
    
    // ファイル内容の検証（基本的なマルウェア検出）
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        // 申告されたMIMEタイプと実際のMIMEタイプが一致するかチェック
        $allowed_mime_types = get_allowed_mime_types();
        if (!in_array($mime_type, $allowed_mime_types, true)) {
            $file['error'] = __('許可されていないファイル形式です。', 'kei-portfolio');
            return $file;
        }
    }
    
    return $file;
}

/**
 * セキュリティログの記録
 */
function kei_portfolio_log_security_event($event, $details = []) {
    if (!defined('WP_DEBUG') || !WP_DEBUG) {
        return;
    }
    
    $log_entry = [
        'timestamp' => current_time('mysql'),
        'event' => $event,
        'user_id' => get_current_user_id(),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'details' => $details
    ];
    
    error_log('SECURITY_EVENT: ' . json_encode($log_entry));
}

/**
 * 不正アクセスの検出と記録
 */
add_action('wp_login_failed', 'kei_portfolio_log_failed_login');

function kei_portfolio_log_failed_login($username) {
    kei_portfolio_log_security_event('login_failed', [
        'username' => $username,
        'referer' => wp_get_referer()
    ]);
}

/**
 * 管理者権限の変更を記録
 */
add_action('set_user_role', 'kei_portfolio_log_role_change', 10, 3);

function kei_portfolio_log_role_change($user_id, $role, $old_roles) {
    if (in_array('administrator', $old_roles) || $role === 'administrator') {
        kei_portfolio_log_security_event('role_change', [
            'target_user_id' => $user_id,
            'new_role' => $role,
            'old_roles' => $old_roles
        ]);
    }
}

/**
 * プラグイン/テーマのアクティブ化を記録
 */
add_action('activated_plugin', 'kei_portfolio_log_plugin_activation');
add_action('switch_theme', 'kei_portfolio_log_theme_switch');

function kei_portfolio_log_plugin_activation($plugin) {
    kei_portfolio_log_security_event('plugin_activated', [
        'plugin' => $plugin
    ]);
}

function kei_portfolio_log_theme_switch($new_name) {
    kei_portfolio_log_security_event('theme_switched', [
        'new_theme' => $new_name
    ]);
}
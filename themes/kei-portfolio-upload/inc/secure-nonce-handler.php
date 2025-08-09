<?php
/**
 * セキュアなNonce処理ハンドラー
 * 適切なCSRF保護とNonce検証を提供
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
 * セキュアなNonce処理クラス
 */
class Kei_Portfolio_Secure_Nonce {
    
    /**
     * Nonce アクション名の定数
     */
    const NONCE_ACTION_REST_API = 'kei_portfolio_rest_api';
    const NONCE_ACTION_FORM = 'kei_portfolio_form';
    const NONCE_ACTION_AJAX = 'kei_portfolio_ajax';
    
    /**
     * 初期化
     */
    public static function init() {
        // REST API用のNonce設定
        add_action('rest_api_init', array(__CLASS__, 'setup_rest_nonce'));
        
        // AJAX用のNonce設定
        add_action('wp_enqueue_scripts', array(__CLASS__, 'localize_nonce_data'));
        
        // 管理画面でのNonce設定
        add_action('admin_enqueue_scripts', array(__CLASS__, 'localize_admin_nonce_data'));
    }
    
    /**
     * REST API用のNonce生成と検証設定
     */
    public static function setup_rest_nonce() {
        // REST APIリクエストでの適切なNonce検証
        add_filter('rest_authentication_errors', array(__CLASS__, 'authenticate_rest_request'), 10, 1);
    }
    
    /**
     * REST APIリクエストの認証
     * 
     * @param WP_Error|null|true $result 認証結果
     * @return WP_Error|null|true
     */
    public static function authenticate_rest_request($result) {
        // 既にエラーがある場合はそのまま返す
        if (is_wp_error($result)) {
            return $result;
        }
        
        // GETリクエストはNonce検証をスキップ
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            return $result;
        }
        
        // ログインユーザーの場合のみNonce検証を実行
        if (is_user_logged_in()) {
            $nonce = null;
            
            // リクエストヘッダーからNonceを取得
            if (isset($_SERVER['HTTP_X_WP_NONCE'])) {
                $nonce = $_SERVER['HTTP_X_WP_NONCE'];
            } elseif (isset($_REQUEST['_wpnonce'])) {
                $nonce = $_REQUEST['_wpnonce'];
            }
            
            // Nonce検証
            if ($nonce && wp_verify_nonce($nonce, 'wp_rest')) {
                return $result;
            } else {
                // デバッグログ記録
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('Kei Portfolio Security: Invalid or missing nonce for REST API request');
                }
                
                return new WP_Error(
                    'rest_cookie_invalid_nonce',
                    __('Cookie nonce is invalid'),
                    array('status' => 403)
                );
            }
        }
        
        return $result;
    }
    
    /**
     * フロントエンド用のNonce情報をローカライズ
     */
    public static function localize_nonce_data() {
        // メインスクリプトが存在する場合のみローカライズ
        if (wp_script_is('kei-portfolio-script', 'registered')) {
            wp_localize_script('kei-portfolio-script', 'keiPortfolioSecurity', array(
                'restNonce' => wp_create_nonce('wp_rest'),
                'ajaxNonce' => wp_create_nonce(self::NONCE_ACTION_AJAX),
                'formNonce' => wp_create_nonce(self::NONCE_ACTION_FORM),
                'restUrl' => rest_url('wp/v2/'),
                'ajaxUrl' => admin_url('admin-ajax.php')
            ));
        }
    }
    
    /**
     * 管理画面用のNonce情報をローカライズ
     */
    public static function localize_admin_nonce_data() {
        // Gutenbergエディター用のNonce設定
        if (wp_script_is('wp-editor', 'registered')) {
            wp_localize_script('wp-editor', 'keiPortfolioAdminSecurity', array(
                'restNonce' => wp_create_nonce('wp_rest'),
                'adminNonce' => wp_create_nonce('kei_portfolio_admin'),
                'restUrl' => rest_url('wp/v2/'),
                'currentUser' => get_current_user_id()
            ));
        }
    }
    
    /**
     * AJAX リクエストのNonce検証
     * 
     * @param string $action AJAX アクション名
     * @return bool 検証成功時true、失敗時false
     */
    public static function verify_ajax_nonce($action = null) {
        $nonce_action = $action ? $action : self::NONCE_ACTION_AJAX;
        
        if (!isset($_REQUEST['_wpnonce'])) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Kei Portfolio Security: Missing nonce in AJAX request');
            }
            return false;
        }
        
        $verified = wp_verify_nonce($_REQUEST['_wpnonce'], $nonce_action);
        
        if (!$verified) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Kei Portfolio Security: Invalid nonce in AJAX request');
            }
        }
        
        return $verified !== false;
    }
    
    /**
     * フォームのNonce検証
     * 
     * @param string $action フォームアクション名
     * @return bool 検証成功時true、失敗時false
     */
    public static function verify_form_nonce($action = null) {
        $nonce_action = $action ? $action : self::NONCE_ACTION_FORM;
        
        if (!isset($_REQUEST['_wpnonce'])) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Kei Portfolio Security: Missing nonce in form submission');
            }
            return false;
        }
        
        $verified = wp_verify_nonce($_REQUEST['_wpnonce'], $nonce_action);
        
        if (!$verified) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Kei Portfolio Security: Invalid nonce in form submission');
            }
        }
        
        return $verified !== false;
    }
    
    /**
     * 管理画面でのNonce検証
     * 
     * @return bool 検証成功時true、失敗時false
     */
    public static function verify_admin_nonce() {
        if (!isset($_REQUEST['_wpnonce'])) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Kei Portfolio Security: Missing nonce in admin request');
            }
            return false;
        }
        
        $verified = wp_verify_nonce($_REQUEST['_wpnonce'], 'kei_portfolio_admin');
        
        if (!$verified) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Kei Portfolio Security: Invalid nonce in admin request');
            }
        }
        
        return $verified !== false;
    }
    
    /**
     * セキュリティヘッダーの設定
     */
    public static function set_security_headers() {
        // CSRF保護を強化するためのヘッダー設定
        if (!headers_sent()) {
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');
            header('X-XSS-Protection: 1; mode=block');
            header('Referrer-Policy: strict-origin-when-cross-origin');
        }
    }
}

// クラス初期化
add_action('init', array('Kei_Portfolio_Secure_Nonce', 'init'));

// セキュリティヘッダーの設定
add_action('send_headers', array('Kei_Portfolio_Secure_Nonce', 'set_security_headers'));

// セキュアNonceハンドラーの読み込み完了をログに記録
if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('Kei Portfolio Security: Secure nonce handler loaded successfully - ' . date('Y-m-d H:i:s'));
}
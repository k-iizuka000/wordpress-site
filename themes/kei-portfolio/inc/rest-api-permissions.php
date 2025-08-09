<?php
/**
 * REST API権限設定の調整
 * 
 * ブロックエディターのテンプレート機能に対する権限を安全に管理し、
 * 適切なスコープ制御とセキュリティを確保します。
 * 
 * @package Kei_Portfolio_Pro
 */

// 直接アクセスを防ぐ
if (!defined('ABSPATH')) {
    exit;
}

/**
 * REST APIのテンプレートエンドポイント権限を調整
 * 
 * ブロックエディターのテンプレート機能が正常に動作するよう、
 * 必要最小限の権限を安全に付与します。
 * 
 * @since 1.0.0
 * @return void
 */
add_filter('rest_api_init', 'kei_portfolio_fix_template_permissions', 10);

function kei_portfolio_fix_template_permissions() {
    // templates/lookupエンドポイントの権限チェックを調整
    add_filter('rest_pre_dispatch', 'kei_portfolio_allow_template_lookup', 10, 3);
}

/**
 * テンプレートlookupエンドポイントへのアクセスを許可
 * 
 * templates/lookupエンドポイントに対して適切な権限チェックを行い、
 * スコープを制限した一時的な権限付与を実施します。
 * 
 * @since 1.0.0
 * @param mixed           $result REST APIの事前ディスパッチ結果
 * @param WP_REST_Server  $server REST APIサーバーインスタンス
 * @param WP_REST_Request $request RESTリクエストオブジェクト
 * @return mixed 変更されたディスパッチ結果
 */
function kei_portfolio_allow_template_lookup($result, $server, $request) {
    // エラーハンドリング: 必要なオブジェクトの存在確認
    if (!is_object($request) || !method_exists($request, 'get_route')) {
        return $result;
    }
    
    $route = $request->get_route();
    
    // templates/lookupエンドポイントの場合
    if (strpos($route, '/wp/v2/templates/lookup') !== false) {
        // セキュリティチェック: ログインユーザーで投稿編集権限があれば許可
        if (is_user_logged_in() && current_user_can('edit_posts')) {
            // デバッグログ記録
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Kei Portfolio: Granting temporary template permissions for templates/lookup endpoint');
            }
            
            // 権限チェックを一時的にパスする
            add_filter('user_has_cap', 'kei_portfolio_grant_template_cap', 10, 3);
            
            // リクエスト処理後にフィルターを削除してスコープを制御
            add_action('rest_request_finished', function() {
                remove_filter('user_has_cap', 'kei_portfolio_grant_template_cap', 10);
                
                // デバッグログ記録
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('Kei Portfolio: Removed temporary template permissions after request completion');
                }
            }, 10);
        }
    }
    
    return $result;
}

/**
 * 一時的にテンプレート権限を付与
 * 
 * REST APIコンテキストでのみ、templates/lookupエンドポイント用の
 * 最小限の権限を一時的に付与します。
 * 
 * @since 1.0.0
 * @param array $allcaps ユーザーの全権限配列
 * @param array $caps    チェック対象の権限配列
 * @param array $args    権限チェックの引数配列
 * @return array 修正された権限配列
 */
function kei_portfolio_grant_template_cap($allcaps, $caps, $args) {
    // REST APIコンテキストの再確認（セキュリティ強化）
    if (!defined('REST_REQUEST') || !REST_REQUEST) {
        return $allcaps;
    }
    
    // 現在のリクエストがtemplates/lookupエンドポイントかどうかを確認
    $current_request = $_SERVER['REQUEST_URI'] ?? '';
    if (strpos($current_request, '/wp/v2/templates/lookup') === false) {
        return $allcaps;
    }
    
    // edit_theme_options権限のみを一時的に付与（スコープ制限）
    if (in_array('edit_theme_options', $caps)) {
        $allcaps['edit_theme_options'] = true;
        
        // デバッグログ記録
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Kei Portfolio: Temporary edit_theme_options capability granted for templates/lookup');
        }
    }
    
    return $allcaps;
}

/**
 * Gutenbergエディターのテンプレート機能を制限
 * 
 * 管理者以外のユーザーに対してテンプレート編集機能を制限し、
 * セキュリティリスクを軽減します。
 * 
 * @since 1.0.0
 * @return void
 */
add_filter('block_editor_settings_all', 'kei_portfolio_adjust_block_editor_settings', 10, 2);

/**
 * ブロックエディターの設定を調整してテンプレート機能を制限
 * 
 * @since 1.0.0
 * @param array   $settings エディター設定配列
 * @param mixed   $context  エディターのコンテキスト
 * @return array 調整された設定配列
 */
function kei_portfolio_adjust_block_editor_settings($settings, $context) {
    // エラーハンドリング: 設定配列の確認
    if (!is_array($settings)) {
        return $settings;
    }
    
    // 管理者以外はテンプレート編集を無効化
    if (!current_user_can('manage_options')) {
        $settings['supportsTemplateMode'] = false;
        $settings['defaultTemplatePartAreas'] = [];
        
        // デバッグログ記録
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Kei Portfolio: Template editing disabled for non-administrator user');
        }
    }
    
    return $settings;
}
<?php
/**
 * パフォーマンス修正のテストスクリプト
 * 
 * @package Kei_Portfolio_Pro
 * @version 1.0.0
 */

// 直接アクセスを防止
if (!defined('ABSPATH')) {
    exit;
}

/**
 * セッション管理のテスト
 */
function test_session_management() {
    echo "<h2>セッション管理テスト</h2>\n";
    
    // PHPセッションが開始されていないことを確認
    $php_session_active = (session_status() === PHP_SESSION_ACTIVE);
    echo "<p>PHPセッション状態: " . ($php_session_active ? '有効（問題あり）' : '無効（正常）') . "</p>\n";
    
    // Transient APIによるセッション確認
    $user_id = get_current_user_id();
    $session_key = 'kei_portfolio_session_' . ($user_id ?: 'guest_' . wp_hash($_SERVER['REMOTE_ADDR'] ?? ''));
    $session_data = get_transient($session_key);
    
    echo "<p>Transientセッション状態: " . ($session_data ? '有効（正常）' : '無効') . "</p>\n";
    
    if ($session_data) {
        echo "<pre>セッションデータ:\n" . print_r($session_data, true) . "</pre>\n";
    }
    
    return !$php_session_active && $session_data;
}

/**
 * アセットファイル配信のテスト
 */
function test_asset_serving() {
    echo "<h2>アセットファイル配信テスト</h2>\n";
    
    $assets_to_test = [
        '/assets/css/style.css',
        '/assets/js/main.js',
        '/assets/images/hero-bg.jpg'
    ];
    
    $theme_uri = get_template_directory_uri();
    $theme_dir = get_template_directory();
    
    $test_results = [];
    
    foreach ($assets_to_test as $asset) {
        $file_path = $theme_dir . $asset;
        $file_url = $theme_uri . $asset;
        
        $exists = file_exists($file_path);
        $test_results[] = [
            'asset' => $asset,
            'exists' => $exists,
            'url' => $file_url
        ];
        
        echo "<p>アセット: {$asset} - " . ($exists ? '存在する' : '存在しない') . "</p>\n";
    }
    
    return $test_results;
}

/**
 * .htaccess設定のテスト
 */
function test_htaccess_config() {
    echo "<h2>.htaccess設定テスト</h2>\n";
    
    $htaccess_file = get_template_directory() . '/.htaccess';
    $exists = file_exists($htaccess_file);
    
    echo "<p>.htaccessファイル: " . ($exists ? '存在する' : '存在しない') . "</p>\n";
    
    if ($exists) {
        $content = file_get_contents($htaccess_file);
        $has_location = strpos($content, '<Location') !== false;
        $has_directory = strpos($content, '<Directory') !== false;
        $has_cache_headers = strpos($content, 'mod_expires') !== false;
        
        echo "<p>Locationディレクティブ使用: " . ($has_location ? 'あり（要改善）' : 'なし（正常）') . "</p>\n";
        echo "<p>Directoryディレクティブ使用: " . ($has_directory ? 'あり（正常）' : 'なし') . "</p>\n";
        echo "<p>キャッシュヘッダー設定: " . ($has_cache_headers ? 'あり（正常）' : 'なし') . "</p>\n";
        
        return !$has_location && $has_directory && $has_cache_headers;
    }
    
    return false;
}

/**
 * Transientクリーンアップのテスト
 */
function test_transient_cleanup() {
    echo "<h2>Transientクリーンアップテスト</h2>\n";
    
    $scheduled = wp_next_scheduled('kei_portfolio_cleanup_sessions');
    echo "<p>クリーンアップスケジュール: " . ($scheduled ? 'セットされている（正常）' : 'セットされていない') . "</p>\n";
    
    if ($scheduled) {
        $next_run = date('Y-m-d H:i:s', $scheduled);
        echo "<p>次回実行: {$next_run}</p>\n";
    }
    
    return $scheduled !== false;
}

/**
 * メイン実行
 */
if (current_user_can('manage_options')) {
    echo "<style>body { font-family: Arial, sans-serif; margin: 20px; } pre { background: #f0f0f0; padding: 10px; }</style>\n";
    echo "<h1>パフォーマンス修正テスト結果</h1>\n";
    
    $session_ok = test_session_management();
    $assets = test_asset_serving();
    $htaccess_ok = test_htaccess_config();
    $cleanup_ok = test_transient_cleanup();
    
    echo "<h2>総合結果</h2>\n";
    echo "<ul>\n";
    echo "<li>セッション管理: " . ($session_ok ? '✅ 正常' : '❌ 要修正') . "</li>\n";
    echo "<li>.htaccess最適化: " . ($htaccess_ok ? '✅ 正常' : '❌ 要修正') . "</li>\n";
    echo "<li>クリーンアップスケジュール: " . ($cleanup_ok ? '✅ 正常' : '❌ 要修正') . "</li>\n";
    echo "</ul>\n";
    
    $overall_status = $session_ok && $htaccess_ok && $cleanup_ok;
    echo "<p><strong>全体的な状態: " . ($overall_status ? '✅ パフォーマンス修正完了' : '❌ 修正が必要') . "</strong></p>\n";
    
} else {
    echo "<p>このテストを実行するには管理者権限が必要です。</p>\n";
}
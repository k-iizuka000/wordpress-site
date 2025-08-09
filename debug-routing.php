<?php
/**
 * WordPress ルーティングデバッグスクリプト
 */

require_once('/var/www/html/wp-load.php');

echo "=== WordPress ルーティングデバッグ ===\n";

// リライトルールの確認
global $wp_rewrite;
$wp_rewrite->init();

echo "リライトルール:\n";
$rules = get_option('rewrite_rules');
if (is_array($rules)) {
    $page_rules = 0;
    foreach($rules as $pattern => $replacement) {
        if (strpos($replacement, 'pagename=') !== false) {
            echo "$pattern => $replacement\n";
            $page_rules++;
        }
    }
    echo "固定ページ用ルール数: $page_rules\n";
} else {
    echo "リライトルールが設定されていません\n";
}

// URL解決テスト
echo "\nURL解決テスト:\n";
$test_urls = array('/about/', '/skills/', '/portfolio/', '/contact/');

foreach ($test_urls as $test_url) {
    echo "テストURL: $test_url\n";
    
    // クエリ変数の解析
    $wp = new WP();
    $wp->parse_request($test_url);
    
    if (isset($wp->query_vars['pagename'])) {
        echo "  ページ名: " . $wp->query_vars['pagename'] . "\n";
        $page = get_page_by_path($wp->query_vars['pagename']);
        if ($page) {
            echo "  ページID: " . $page->ID . "\n";
            echo "  ステータス: " . $page->post_status . "\n";
        } else {
            echo "  ページが見つかりません\n";
        }
    } else {
        echo "  ページ名が解析されませんでした\n";
        echo "  クエリ変数: " . json_encode($wp->query_vars) . "\n";
    }
    echo "\n";
}

echo "=== デバッグ完了 ===\n";
?>
<?php
// WordPressをロード
$wp_load_path = dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php';
if (\!file_exists($wp_load_path)) {
    die("WordPress環境が見つかりません。\n");
}
require_once $wp_load_path;

// page-creator.phpをロード
require_once dirname(__FILE__) . '/inc/page-creator.php';

// ページ作成クラスのインスタンス化
if (class_exists('\KeiPortfolio\PageCreator')) {
    $creator = new \KeiPortfolio\PageCreator();
    
    echo "固定ページの作成を開始します...\n";
    
    // 全ページを作成
    $result = $creator->create_all_pages();
    
    if (is_wp_error($result)) {
        echo "エラー: " . $result->get_error_message() . "\n";
    } else {
        echo "固定ページの作成が完了しました！\n";
        echo "作成されたページ:\n";
        foreach ($result as $page_slug => $page_id) {
            $page = get_post($page_id);
            if ($page) {
                echo "  - {$page->post_title} (/{$page_slug})\n";
            }
        }
    }
} else {
    echo "PageCreatorクラスが見つかりません。\n";
}

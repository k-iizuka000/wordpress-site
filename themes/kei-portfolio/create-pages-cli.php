<?php
/**
 * CLIから固定ページを作成するスクリプト
 * 
 * 使用方法:
 * php create-pages-cli.php
 * 
 * または Docker環境で:
 * docker exec kei-portfolio-dev php /var/www/html/wp-content/themes/kei-portfolio/create-pages-cli.php
 */

// WordPressをロード
$wp_load_path = dirname(dirname(__DIR__)) . '/wordpress/wp-load.php';
if (!file_exists($wp_load_path)) {
    die("WordPress環境が見つかりません。\n");
}
require_once $wp_load_path;

// page-creator.phpをロード
require_once dirname(__FILE__) . '/inc/page-creator.php';

// ページ作成関数を実行
echo "固定ページの作成を開始します...\n";

$result = kei_portfolio_create_all_pages();

if (is_wp_error($result)) {
    echo "エラー: " . $result->get_error_message() . "\n";
    exit(1);
} else {
    echo "固定ページの作成が完了しました！\n";
    echo "作成されたページ:\n";
    foreach ($result as $page_slug => $page_id) {
        $page = get_post($page_id);
        if ($page) {
            echo "  - {$page->post_title} (/{$page_slug})\n";
        }
    }
    echo "\nサイトURL: " . home_url() . "\n";
    exit(0);
}
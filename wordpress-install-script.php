<?php
/**
 * WordPress初期セットアップスクリプト
 * Dockerコンテナ内で実行してWordPressの初期設定と固定ページを作成
 */

// WordPress環境のセットアップ
define('WP_USE_THEMES', false);
require_once('/var/www/html/wp-load.php');
require_once('/var/www/html/wp-admin/includes/upgrade.php');

echo "=== WordPress 初期セットアップ開始 ===\n";

// WordPressが既にインストールされているかチェック
if (is_blog_installed()) {
    echo "WordPress は既にインストールされています。\n";
} else {
    // WordPressのテーブルを作成
    wp_install('Kei Portfolio', 'admin', 'admin@example.com', true, '', 'admin123');
    echo "✓ WordPress基本テーブル作成完了\n";
}

// 基本設定
update_option('blogname', 'Kei Portfolio');
update_option('blogdescription', '10年以上の経験を持つフルスタックエンジニアのポートフォリオ');
update_option('siteurl', 'http://localhost:8090');
update_option('home', 'http://localhost:8090');
update_option('template', 'kei-portfolio');
update_option('stylesheet', 'kei-portfolio');

echo "✓ 基本設定完了\n";

// 固定ページ作成
$pages = array(
    'about' => array(
        'title' => 'About',
        'content' => 'アバウトページの内容です。',
        'template' => 'page-about.php'
    ),
    'skills' => array(
        'title' => 'Skills',
        'content' => 'スキルページの内容です。',
        'template' => 'page-skills.php'
    ),
    'portfolio' => array(
        'title' => 'Portfolio',
        'content' => 'ポートフォリオページの内容です。',
        'template' => 'page-portfolio.php'
    ),
    'contact' => array(
        'title' => 'Contact',
        'content' => 'コンタクトページの内容です。',
        'template' => 'page-contact.php'
    )
);

foreach ($pages as $slug => $page_data) {
    $page_id = wp_insert_post(array(
        'post_title' => $page_data['title'],
        'post_content' => $page_data['content'],
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_name' => $slug,
        'post_author' => 1
    ));
    
    if ($page_id && !is_wp_error($page_id)) {
        // ページテンプレートを設定
        update_post_meta($page_id, '_wp_page_template', $page_data['template']);
        echo "✓ 固定ページ '{$page_data['title']}' (ID: {$page_id}) を作成\n";
    } else {
        echo "✗ 固定ページ '{$page_data['title']}' の作成に失敗\n";
    }
}

// パーマリンク設定
update_option('permalink_structure', '/%postname%/');

echo "✓ パーマリンク設定完了\n";

// テーマの有効化
switch_theme('kei-portfolio');

echo "✓ テーマ 'kei-portfolio' を有効化\n";

echo "=== WordPress 初期セットアップ完了 ===\n";
echo "管理画面URL: http://localhost:8090/wp-admin/\n";
echo "ユーザー: admin\n";
echo "パスワード: admin123\n";
?>
<?php
/**
 * WordPressの固定ページを作成するスクリプト
 * Docker環境のWordPressに接続して固定ページを作成します
 */

// WordPress環境をロード
$wordpress_path = '/var/www/html';
if (!file_exists($wordpress_path . '/wp-config.php')) {
    echo "WordPress環境が見つかりません。Dockerコンテナ内で実行してください。\n";
    exit(1);
}

// WordPress環境をロード
require_once($wordpress_path . '/wp-config.php');
require_once($wordpress_path . '/wp-includes/wp-db.php');
require_once($wordpress_path . '/wp-includes/pluggable.php');
require_once($wordpress_path . '/wp-admin/includes/post.php');

// WordPressを初期化
if (!function_exists('wp_insert_post')) {
    echo "WordPress関数が利用できません。\n";
    exit(1);
}

echo "=== WordPress固定ページ作成 ===\n";
echo "実行日時: " . date('Y-m-d H:i:s') . "\n\n";

// 作成する固定ページの定義
$pages_to_create = [
    [
        'post_title' => 'About',
        'post_name' => 'about',
        'post_content' => '[about-content]', // テンプレートで置き換え
        'post_type' => 'page',
        'post_status' => 'publish',
        'comment_status' => 'closed',
        'ping_status' => 'closed'
    ],
    [
        'post_title' => 'Skills',
        'post_name' => 'skills',
        'post_content' => '[skills-content]',
        'post_type' => 'page',
        'post_status' => 'publish',
        'comment_status' => 'closed',
        'ping_status' => 'closed'
    ],
    [
        'post_title' => 'Contact',
        'post_name' => 'contact',
        'post_content' => '[contact-content]',
        'post_type' => 'page',
        'post_status' => 'publish',
        'comment_status' => 'closed',
        'ping_status' => 'closed'
    ],
    [
        'post_title' => 'Portfolio',
        'post_name' => 'portfolio',
        'post_content' => '[portfolio-content]',
        'post_type' => 'page',
        'post_status' => 'publish',
        'comment_status' => 'closed',
        'ping_status' => 'closed'
    ]
];

$created_pages = [];
$errors = [];

foreach ($pages_to_create as $page_data) {
    echo "作成中: {$page_data['post_title']} ページ\n";
    
    // 既存ページをチェック
    $existing_page = get_page_by_path($page_data['post_name']);
    if ($existing_page) {
        echo "  既に存在します (ID: {$existing_page->ID})\n";
        $created_pages[] = [
            'title' => $page_data['post_title'],
            'slug' => $page_data['post_name'],
            'id' => $existing_page->ID,
            'status' => 'already_exists'
        ];
        continue;
    }
    
    // ページを作成
    $page_id = wp_insert_post($page_data);
    
    if (is_wp_error($page_id)) {
        $error_message = $page_id->get_error_message();
        echo "  エラー: {$error_message}\n";
        $errors[] = [
            'title' => $page_data['post_title'],
            'error' => $error_message
        ];
    } else {
        echo "  作成成功 (ID: {$page_id})\n";
        
        // ページテンプレートを設定（もしあれば）
        $template_file = 'page-' . $page_data['post_name'] . '.php';
        if (file_exists(get_template_directory() . '/' . $template_file)) {
            update_post_meta($page_id, '_wp_page_template', $template_file);
            echo "  テンプレート設定: {$template_file}\n";
        }
        
        $created_pages[] = [
            'title' => $page_data['post_title'],
            'slug' => $page_data['post_name'],
            'id' => $page_id,
            'status' => 'created'
        ];
    }
    echo "\n";
}

// 結果を保存
$results = [
    'created_pages' => $created_pages,
    'errors' => $errors,
    'timestamp' => date('Y-m-d H:i:s')
];

file_put_contents('/tmp/page-creation-results.json', json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "=== 作成結果サマリー ===\n";
echo "作成/確認完了: " . count($created_pages) . " ページ\n";
echo "エラー: " . count($errors) . " ページ\n\n";

if (count($created_pages) > 0) {
    echo "作成されたページ:\n";
    foreach ($created_pages as $page) {
        echo "- {$page['title']} (/{$page['slug']}/) [ID: {$page['id']}] - {$page['status']}\n";
    }
}

if (count($errors) > 0) {
    echo "\nエラーページ:\n";
    foreach ($errors as $error) {
        echo "- {$error['title']}: {$error['error']}\n";
    }
}

echo "\n固定ページ作成が完了しました。\n";
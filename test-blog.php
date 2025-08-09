<?php
/**
 * ブログ機能テストスクリプト
 */

require_once('/var/www/html/wp-load.php');

echo "=== ブログ機能テスト開始 ===\n";

// テストブログ投稿の作成
$test_post = array(
    'post_title' => 'テストブログ投稿',
    'post_content' => 'これはブログ機能のテスト投稿です。この投稿を使用してブログの動作を確認します。<p>段落テストです。</p><ul><li>リストアイテム1</li><li>リストアイテム2</li></ul>',
    'post_status' => 'publish',
    'post_type' => 'post',
    'post_author' => 1,
    'post_category' => array(1), // 未分類カテゴリ
    'tags_input' => 'テスト,ブログ,WordPress'
);

$post_id = wp_insert_post($test_post);
if ($post_id && !is_wp_error($post_id)) {
    echo "✓ テストブログ投稿作成成功 (ID: $post_id)\n";
    
    // 投稿メタデータの確認
    echo "  - パーマリンク: " . get_permalink($post_id) . "\n";
    echo "  - 投稿日時: " . get_the_date('Y-m-d H:i:s', $post_id) . "\n";
} else {
    echo "✗ テストブログ投稿作成失敗\n";
    if (is_wp_error($post_id)) {
        echo "  エラー: " . $post_id->get_error_message() . "\n";
    }
}

echo "\nブログアーカイブの確認:\n";
$posts = get_posts(array('numberposts' => 10, 'post_status' => 'publish'));
echo "公開投稿数: " . count($posts) . "\n";
if (count($posts) > 0) {
    foreach($posts as $post) {
        setup_postdata($post);
        echo "- " . $post->post_title . " (ID: " . $post->ID . ", 日時: " . $post->post_date . ")\n";
    }
    wp_reset_postdata();
} else {
    echo "投稿が見つかりませんでした。\n";
}

// ブログクラスのテスト
echo "\nブログデータクラスのテスト:\n";
if (class_exists('KeiPortfolio\Blog\Blog_Data')) {
    echo "✓ Blog_Data クラスが存在します\n";
    try {
        $blog_data = KeiPortfolio\Blog\Blog_Data::get_instance();
        $recent_posts = $blog_data->get_recent_posts(3);
        echo "✓ 最新投稿取得成功: " . count($recent_posts) . "件\n";
    } catch (Exception $e) {
        echo "✗ Blog_Data インスタンス化失敗: " . $e->getMessage() . "\n";
    }
} else {
    echo "✗ Blog_Data クラスが見つかりません\n";
}

// 最適化ブログデータクラスのテスト
if (class_exists('KeiPortfolio\Blog\OptimizedBlogData')) {
    echo "✓ OptimizedBlogData クラスが存在します\n";
    try {
        $optimized_blog = KeiPortfolio\Blog\OptimizedBlogData::get_instance();
        $optimized_posts = $optimized_blog->get_recent_posts_cached(3);
        echo "✓ 最適化投稿取得成功: " . count($optimized_posts) . "件\n";
    } catch (Exception $e) {
        echo "✗ OptimizedBlogData 取得失敗: " . $e->getMessage() . "\n";
    }
} else {
    echo "✗ OptimizedBlogData クラスが見つかりません\n";
}

echo "\n=== ブログ機能テスト完了 ===\n";
?>
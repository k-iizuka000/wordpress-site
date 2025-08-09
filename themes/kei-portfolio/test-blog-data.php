<?php
/**
 * Blog Data Class テストスクリプト
 * 
 * このファイルは開発・テスト用途でのみ使用
 * 本番環境では削除すること
 * 
 * @package Kei_Portfolio
 * @since 1.0.0
 */

// WordPress環境でのみ実行
if (!defined('ABSPATH')) {
    // WordPress外からの直接実行用の設定（開発環境のみ）
    require_once('../../../wp-load.php');
}

/**
 * Blog_Data クラスのテスト実行
 */
function test_blog_data_class() {
    echo "<h2>Blog_Data クラステスト開始</h2>\n";
    echo "<p>実行時間: " . date('Y-m-d H:i:s') . "</p>\n";
    echo "<hr>\n";
    
    // クラスが存在するかチェック
    if (!class_exists('Blog_Data')) {
        echo "<p style='color: red;'><strong>エラー:</strong> Blog_Data クラスが見つかりません</p>\n";
        return;
    }
    
    try {
        // インスタンス取得
        $blog_data = Blog_Data::get_instance();
        echo "<p style='color: green;'>✓ Blog_Data インスタンス取得成功</p>\n";
        
        // データ利用可能性チェック
        if ($blog_data->is_data_available()) {
            echo "<p style='color: green;'>✓ データ利用可能</p>\n";
        } else {
            echo "<p style='color: orange;'>⚠ データが利用できません（WordPressの状態を確認してください）</p>\n";
        }
        
        // 1. 最新投稿取得テスト
        echo "<h3>1. 最新投稿取得テスト</h3>\n";
        $recent_posts = $blog_data->get_recent_posts(array('count' => 3));
        
        if (is_wp_error($recent_posts)) {
            echo "<p style='color: red;'>エラー: " . $blog_data->get_error_message($recent_posts) . "</p>\n";
        } else {
            echo "<p>取得件数: " . count($recent_posts) . "</p>\n";
            if (!empty($recent_posts)) {
                echo "<ul>\n";
                foreach ($recent_posts as $post) {
                    echo "<li>" . esc_html($post->post_title) . " (ID: {$post->ID})</li>\n";
                }
                echo "</ul>\n";
            } else {
                echo "<p>投稿が見つかりませんでした</p>\n";
            }
        }
        
        // 2. カテゴリー統計取得テスト
        echo "<h3>2. カテゴリー統計取得テスト</h3>\n";
        $category_stats = $blog_data->get_category_stats();
        
        if (is_wp_error($category_stats)) {
            echo "<p style='color: red;'>エラー: " . $blog_data->get_error_message($category_stats) . "</p>\n";
        } else {
            echo "<p>カテゴリー数: " . count($category_stats) . "</p>\n";
            if (!empty($category_stats)) {
                echo "<ul>\n";
                foreach (array_slice($category_stats, 0, 5) as $category) {
                    echo "<li>" . esc_html($category['name']) . " (投稿数: {$category['count']})</li>\n";
                }
                echo "</ul>\n";
            } else {
                echo "<p>カテゴリーが見つかりませんでした</p>\n";
            }
        }
        
        // 3. 人気投稿取得テスト
        echo "<h3>3. 人気投稿取得テスト</h3>\n";
        $popular_posts = $blog_data->get_popular_posts(3);
        
        if (is_wp_error($popular_posts)) {
            echo "<p style='color: red;'>エラー: " . $blog_data->get_error_message($popular_posts) . "</p>\n";
        } else {
            echo "<p>取得件数: " . count($popular_posts) . "</p>\n";
            if (!empty($popular_posts)) {
                echo "<ul>\n";
                foreach ($popular_posts as $post) {
                    $views = $blog_data->get_post_views($post->ID);
                    echo "<li>" . esc_html($post->post_title) . " (ビュー数: {$views})</li>\n";
                }
                echo "</ul>\n";
            } else {
                echo "<p>ビュー数のある投稿が見つかりませんでした</p>\n";
            }
        }
        
        // 4. 関連投稿取得テスト（最新投稿がある場合のみ）
        if (!empty($recent_posts) && !is_wp_error($recent_posts)) {
            $test_post_id = $recent_posts[0]->ID;
            
            echo "<h3>4. 関連投稿取得テスト（投稿ID: {$test_post_id}）</h3>\n";
            $related_posts = $blog_data->get_related_posts($test_post_id, 3);
            
            if (is_wp_error($related_posts)) {
                echo "<p style='color: red;'>エラー: " . $blog_data->get_error_message($related_posts) . "</p>\n";
            } else {
                echo "<p>取得件数: " . count($related_posts) . "</p>\n";
                if (!empty($related_posts)) {
                    echo "<ul>\n";
                    foreach ($related_posts as $post) {
                        echo "<li>" . esc_html($post->post_title) . " (ID: {$post->ID})</li>\n";
                    }
                    echo "</ul>\n";
                } else {
                    echo "<p>関連投稿が見つかりませんでした</p>\n";
                }
            }
        }
        
        // 5. ビューカウントテスト
        if (!empty($recent_posts) && !is_wp_error($recent_posts)) {
            $test_post_id = $recent_posts[0]->ID;
            
            echo "<h3>5. ビューカウントテスト（投稿ID: {$test_post_id}）</h3>\n";
            $before_views = $blog_data->get_post_views($test_post_id);
            echo "<p>現在のビュー数: {$before_views}</p>\n";
            
            // テスト用のビューカウント（管理者以外で実行される場合のみ）
            if (!current_user_can('manage_options')) {
                $result = $blog_data->count_post_views($test_post_id);
                
                if (is_wp_error($result)) {
                    echo "<p style='color: red;'>エラー: " . $blog_data->get_error_message($result) . "</p>\n";
                } else {
                    $after_views = $blog_data->get_post_views($test_post_id);
                    echo "<p>カウント後のビュー数: {$after_views}</p>\n";
                    
                    if ($after_views > $before_views) {
                        echo "<p style='color: green;'>✓ ビューカウント正常動作</p>\n";
                    }
                }
            } else {
                echo "<p style='color: blue;'>ℹ 管理者ユーザーのため、ビューカウントはスキップされました</p>\n";
            }
        }
        
        // 6. キャッシュクリアテスト
        echo "<h3>6. キャッシュクリアテスト</h3>\n";
        $blog_data->clear_cache();
        echo "<p style='color: green;'>✓ キャッシュクリア実行完了</p>\n";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'><strong>例外エラー:</strong> " . esc_html($e->getMessage()) . "</p>\n";
        echo "<p><strong>スタックトレース:</strong></p>\n";
        echo "<pre>" . esc_html($e->getTraceAsString()) . "</pre>\n";
    }
    
    echo "<hr>\n";
    echo "<h2>Blog_Data クラステスト完了</h2>\n";
    echo "<p>メモリ使用量: " . size_format(memory_get_peak_usage(true)) . "</p>\n";
}

// HTMLヘッダー出力
if (!wp_doing_ajax() && !wp_doing_cron()) {
    ?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <title>Blog_Data クラステスト - <?php bloginfo('name'); ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                max-width: 800px;
                margin: 0 auto;
                padding: 20px;
                line-height: 1.6;
            }
            h2 { color: #333; border-bottom: 2px solid #0073aa; }
            h3 { color: #666; margin-top: 30px; }
            ul { background: #f9f9f9; padding: 15px; margin: 10px 0; }
            pre { background: #f1f1f1; padding: 10px; overflow-x: auto; }
            .notice { padding: 10px; margin: 10px 0; border-left: 4px solid #0073aa; }
        </style>
    </head>
    <body>
        <div class="notice">
            <p><strong>注意:</strong> このファイルは開発・テスト用途でのみ使用してください。本番環境では削除してください。</p>
        </div>
        
        <?php test_blog_data_class(); ?>
        
        <hr>
        <p><a href="<?php echo home_url(); ?>">&larr; サイトに戻る</a></p>
    </body>
    </html>
    <?php
} else {
    // AJAX または Cron での実行
    test_blog_data_class();
}
?>
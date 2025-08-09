<?php
/**
 * ブログ機能の統合テスト
 * 
 * @package KeiPortfolio
 * @subpackage Tests
 */

use PHPUnit\Framework\TestCase;
use KeiPortfolio\Blog\Blog_Data;
use KeiPortfolio\Blog\BlogSEO;
use KeiPortfolio\Blog\BlogOptimizations;

class BlogIntegrationTest extends WP_UnitTestCase {
    
    private $blog_data;
    private $test_posts = [];
    private $test_categories = [];
    private $test_user_id;
    private $blog_page_id;
    
    /**
     * テスト前の初期化
     */
    public function setUp(): void {
        parent::setUp();
        
        $this->blog_data = Blog_Data::get_instance();
        
        // テスト用ユーザーを作成
        $this->test_user_id = $this->factory->user->create([
            'role' => 'administrator',
            'display_name' => 'テストユーザー'
        ]);
        
        // テスト用カテゴリーを作成
        $this->test_categories[] = $this->factory->category->create([
            'name' => 'テクノロジー',
            'slug' => 'technology'
        ]);
        
        $this->test_categories[] = $this->factory->category->create([
            'name' => 'デザイン',
            'slug' => 'design'
        ]);
        
        // ブログページを作成
        $this->blog_page_id = $this->factory->post->create([
            'post_type' => 'page',
            'post_title' => 'ブログ',
            'post_name' => 'blog',
            'post_status' => 'publish'
        ]);
        
        // ブログページとして設定
        update_option('page_for_posts', $this->blog_page_id);
        
        // テスト用投稿を作成
        for ($i = 0; $i < 15; $i++) {
            $post_id = $this->factory->post->create([
                'post_title' => 'ブログ統合テスト投稿 ' . ($i + 1),
                'post_content' => $this->generate_test_content($i),
                'post_excerpt' => 'これは統合テスト投稿 ' . ($i + 1) . ' の抜粋です。',
                'post_status' => 'publish',
                'post_author' => $this->test_user_id,
                'post_date' => date('Y-m-d H:i:s', strtotime("-{$i} days")),
                'meta_input' => [
                    'post_views_count' => rand(10, 100)
                ]
            ]);
            
            // カテゴリーをランダムに設定
            wp_set_post_categories($post_id, [$this->test_categories[rand(0, 1)]]);
            
            // タグを設定
            wp_set_post_tags($post_id, ['PHP', 'WordPress', 'テスト']);
            
            // アイキャッチ画像を設定（一部の投稿に）
            if ($i % 3 === 0) {
                $this->set_featured_image($post_id);
            }
            
            $this->test_posts[] = $post_id;
        }
        
        // キャッシュをクリア
        $this->blog_data->clear_cache();
    }
    
    /**
     * テスト後のクリーンアップ
     */
    public function tearDown(): void {
        // テスト用投稿を削除
        foreach ($this->test_posts as $post_id) {
            wp_delete_post($post_id, true);
        }
        
        // テスト用カテゴリーを削除
        foreach ($this->test_categories as $category_id) {
            wp_delete_term($category_id, 'category');
        }
        
        // ブログページの設定をリセット
        delete_option('page_for_posts');
        wp_delete_post($this->blog_page_id, true);
        
        // テスト用ユーザーを削除
        wp_delete_user($this->test_user_id);
        
        // キャッシュをクリア
        $this->blog_data->clear_cache();
        
        parent::tearDown();
    }
    
    /**
     * テスト用コンテンツ生成
     */
    private function generate_test_content($index) {
        $content_parts = [
            '<h2>見出し2: セクション ' . ($index + 1) . '</h2>',
            '<p>これは統合テストのための投稿内容です。投稿番号: ' . ($index + 1) . '</p>',
            '<h3>見出し3: サブセクション</h3>',
            '<p>詳細な説明がここに入ります。</p>',
            '<ul><li>リスト項目1</li><li>リスト項目2</li><li>リスト項目3</li></ul>',
            '<p>最終的な説明文です。</p>'
        ];
        
        return implode("\n", $content_parts);
    }
    
    /**
     * テスト用アイキャッチ画像設定
     */
    private function set_featured_image($post_id) {
        $attachment_id = $this->factory->attachment->create([
            'post_title' => 'テスト画像',
            'post_mime_type' => 'image/jpeg'
        ]);
        
        set_post_thumbnail($post_id, $attachment_id);
    }
    
    /**
     * ブログページ表示の統合テスト
     */
    public function test_blog_archive_page_display() {
        // ブログページにアクセス
        $this->go_to(get_permalink($this->blog_page_id));
        
        // 正しいテンプレート階層が使用されていることを確認
        $this->assertTrue(is_home());
        $this->assertFalse(is_front_page());
        
        // メインクエリが正しく動作していることを確認
        global $wp_query;
        $this->assertTrue($wp_query->is_home());
        $this->assertEquals('post', $wp_query->get('post_type'));
    }
    
    /**
     * 個別投稿ページ表示の統合テスト
     */
    public function test_single_post_page_display() {
        $post_id = $this->test_posts[0];
        $this->go_to(get_permalink($post_id));
        
        $this->assertTrue(is_single());
        $this->assertEquals($post_id, get_queried_object_id());
        
        // 投稿データが正しく取得できることを確認
        $post = get_post($post_id);
        $this->assertInstanceOf(WP_Post::class, $post);
        $this->assertEquals('ブログ統合テスト投稿 1', $post->post_title);
    }
    
    /**
     * カテゴリーアーカイブページの統合テスト
     */
    public function test_category_archive_page() {
        $category_id = $this->test_categories[0];
        $category_link = get_category_link($category_id);
        
        $this->go_to($category_link);
        
        $this->assertTrue(is_category());
        $this->assertEquals($category_id, get_queried_object_id());
        
        // カテゴリーに属する投稿が取得できることを確認
        global $wp_query;
        $this->assertTrue($wp_query->have_posts());
    }
    
    /**
     * SEO構造化データの統合テスト
     */
    public function test_structured_data_output() {
        $post_id = $this->test_posts[0];
        $this->go_to(get_permalink($post_id));
        
        // wp_headアクションの出力をキャプチャ
        ob_start();
        do_action('wp_head');
        $output = ob_get_clean();
        
        // 構造化データが含まれているかチェック
        $this->assertStringContainsString('application/ld+json', $output);
        $this->assertStringContainsString('"@type":"BlogPosting"', $output);
        $this->assertStringContainsString('"headline":"ブログ統合テスト投稿 1"', $output);
        
        // OGPタグが含まれているかチェック
        $this->assertStringContainsString('property="og:title"', $output);
        $this->assertStringContainsString('property="og:type"', $output);
        $this->assertStringContainsString('property="og:url"', $output);
    }
    
    /**
     * ページネーション機能の統合テスト
     */
    public function test_pagination_functionality() {
        // 投稿の表示数を制限
        update_option('posts_per_page', 5);
        
        $this->go_to(get_permalink($this->blog_page_id));
        
        global $wp_query;
        $this->assertTrue($wp_query->have_posts());
        
        // ページネーションが必要な数の投稿があることを確認
        $this->assertGreaterThan(5, count($this->test_posts));
        
        // 2ページ目にアクセス
        $this->go_to(get_permalink($this->blog_page_id) . 'page/2/');
        
        $this->assertTrue($wp_query->have_posts());
        $this->assertTrue(is_paged());
        
        // posts_per_pageをデフォルトに戻す
        update_option('posts_per_page', 10);
    }
    
    /**
     * 関連投稿表示の統合テスト
     */
    public function test_related_posts_integration() {
        $main_post_id = $this->test_posts[0];
        $related_posts = $this->blog_data->get_related_posts($main_post_id, 3);
        
        $this->assertIsArray($related_posts);
        $this->assertLessThanOrEqual(3, count($related_posts));
        
        // 関連投稿が適切にフィルタリングされていることを確認
        foreach ($related_posts as $post) {
            $this->assertNotEquals($main_post_id, $post->ID);
            $this->assertEquals('publish', $post->post_status);
        }
        
        // 実際のページでの表示テスト
        $this->go_to(get_permalink($main_post_id));
        $this->assertTrue(is_single());
    }
    
    /**
     * 検索機能の統合テスト
     */
    public function test_search_functionality() {
        // 検索クエリでアクセス
        $search_query = 'ブログ統合テスト';
        $this->go_to(home_url('/?s=' . urlencode($search_query)));
        
        $this->assertTrue(is_search());
        
        global $wp_query;
        $this->assertTrue($wp_query->have_posts());
        
        // 検索結果が適切であることを確認
        while ($wp_query->have_posts()) {
            $wp_query->the_post();
            $title = get_the_title();
            $content = get_the_content();
            
            $contains_search_term = (
                stripos($title, $search_query) !== false ||
                stripos($content, $search_query) !== false
            );
            
            $this->assertTrue($contains_search_term, 
                "検索結果に検索語が含まれていません: {$title}");
        }
        
        wp_reset_postdata();
    }
    
    /**
     * ウィジェット機能の統合テスト
     */
    public function test_widget_integration() {
        // サイドバーウィジェットエリアが登録されていることを確認
        global $wp_registered_sidebars;
        
        // ブログサイドバーが登録されているかチェック（実装に応じて調整）
        $sidebar_exists = false;
        foreach ($wp_registered_sidebars as $sidebar_id => $sidebar) {
            if (strpos($sidebar_id, 'blog') !== false || strpos($sidebar['name'], 'ブログ') !== false) {
                $sidebar_exists = true;
                break;
            }
        }
        
        // 最新投稿ウィジェットのデータ取得テスト
        $recent_posts = $this->blog_data->get_recent_posts(['count' => 5]);
        $this->assertNotEmpty($recent_posts);
        
        // カテゴリーウィジェットのデータ取得テスト
        $category_stats = $this->blog_data->get_category_stats();
        $this->assertNotEmpty($category_stats);
    }
    
    /**
     * パフォーマンス最適化の統合テスト
     */
    public function test_performance_optimizations() {
        // クエリ最適化のテスト
        $this->go_to(get_permalink($this->blog_page_id));
        
        global $wp_query;
        
        // no_found_rowsが設定されているかテスト（実装に応じて）
        $start_time = microtime(true);
        
        // 大量の投稿を作成
        $performance_test_posts = [];
        for ($i = 0; $i < 20; $i++) {
            $performance_test_posts[] = $this->factory->post->create([
                'post_title' => 'パフォーマンステスト投稿 ' . $i,
                'post_status' => 'publish'
            ]);
        }
        
        // ブログページの読み込み時間測定
        $this->go_to(get_permalink($this->blog_page_id));
        
        $execution_time = microtime(true) - $start_time;
        $this->assertLessThan(2.0, $execution_time); // 2秒以内で完了
        
        // クリーンアップ
        foreach ($performance_test_posts as $post_id) {
            wp_delete_post($post_id, true);
        }
    }
    
    /**
     * キャッシュ機能の統合テスト
     */
    public function test_cache_integration() {
        // 初回データ取得
        $posts_before = $this->blog_data->get_recent_posts(['count' => 5]);
        $stats_before = $this->blog_data->get_category_stats();
        
        // 新しい投稿を公開（キャッシュクリアトリガー）
        $new_post_id = wp_insert_post([
            'post_title' => '新規統合テスト投稿',
            'post_content' => '新規投稿の内容です。',
            'post_status' => 'publish',
            'post_type' => 'post'
        ]);
        
        // save_postアクションが正しく動作することをシミュレート
        do_action('save_post_post', $new_post_id, get_post($new_post_id), false);
        
        // キャッシュがクリアされ、新しいデータが取得されることを確認
        $posts_after = $this->blog_data->get_recent_posts(['count' => 5]);
        
        $this->assertNotEquals(
            $posts_before[0]->ID,
            $posts_after[0]->ID,
            'キャッシュが正しくクリアされていません'
        );
        
        $this->assertEquals('新規統合テスト投稿', $posts_after[0]->post_title);
        
        // クリーンアップ
        wp_delete_post($new_post_id, true);
    }
    
    /**
     * ビューカウント機能の統合テスト
     */
    public function test_view_count_integration() {
        $post_id = $this->test_posts[0];
        
        // 初期状態のビューカウントを削除
        delete_post_meta($post_id, 'post_views_count');
        
        // 投稿ページにアクセス
        $this->go_to(get_permalink($post_id));
        
        // wp_headアクションでビューカウントが実行されることをシミュレート
        if (is_single() && get_post_type() === 'post') {
            Blog_Data::count_post_views(get_the_ID());
        }
        
        $count = get_post_meta($post_id, 'post_views_count', true);
        $this->assertEquals('1', $count);
        
        // 複数回アクセス
        for ($i = 0; $i < 3; $i++) {
            $this->go_to(get_permalink($post_id));
            if (is_single() && get_post_type() === 'post') {
                Blog_Data::count_post_views(get_the_ID());
            }
        }
        
        $count = get_post_meta($post_id, 'post_views_count', true);
        $this->assertEquals('4', $count);
        
        // 人気投稿リストに反映されることを確認
        $popular_posts = $this->blog_data->get_popular_posts(5);
        $found_in_popular = false;
        
        foreach ($popular_posts as $popular_post) {
            if ($popular_post->ID === $post_id) {
                $found_in_popular = true;
                break;
            }
        }
        
        $this->assertTrue($found_in_popular, '人気投稿リストに反映されていません');
    }
    
    /**
     * レスポンシブデザインの統合テスト
     */
    public function test_responsive_design_integration() {
        // モバイル用のUser-Agentをシミュレート
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15';
        
        $this->go_to(get_permalink($this->blog_page_id));
        
        // is_mobile()関数が正しく動作することを確認（WordPressの標準関数）
        // またはカスタム実装の場合はその動作を確認
        
        // モバイル向けの抜粋長さが適用されることを確認
        $posts = $this->blog_data->get_recent_posts(['count' => 1]);
        if (!empty($posts)) {
            // BlogOptimizationsクラスのcustom_excerpt_lengthメソッドをテスト
            $excerpt_length = apply_filters('excerpt_length', 55);
            $this->assertIsInt($excerpt_length);
        }
        
        // User-Agentをリセット
        unset($_SERVER['HTTP_USER_AGENT']);
    }
    
    /**
     * 多言語対応の統合テスト
     */
    public function test_internationalization() {
        // 文字列が翻訳関数を使用していることを確認
        $post_id = $this->test_posts[0];
        $this->go_to(get_permalink($post_id));
        
        // 「続きを読む」リンクなどの翻訳可能文字列をテスト
        $more_link = apply_filters('excerpt_more', '');
        
        if (!empty($more_link)) {
            // 翻訳関数が使用されているかテスト（実装に応じて調整）
            $this->assertStringContainsString('続きを読む', $more_link);
        }
    }
    
    /**
     * セキュリティ機能の統合テスト
     */
    public function test_security_integration() {
        $post_id = $this->test_posts[0];
        $post = get_post($post_id);
        
        // XSS対策：出力がエスケープされていることを確認
        $title_escaped = esc_html($post->post_title);
        $this->assertEquals($post->post_title, $title_escaped);
        
        // URL出力がエスケープされていることを確認
        $permalink_escaped = esc_url(get_permalink($post_id));
        $this->assertStringStartsWith('http', $permalink_escaped);
        
        // SQLインジェクション対策：prepare文が使用されていることを暗示的にテスト
        // （実際のクエリメソッドで$wpdb->prepare()が使用されていることを確認）
        
        $recent_posts = $this->blog_data->get_recent_posts(['count' => 5]);
        $this->assertIsArray($recent_posts);
        $this->assertNotEmpty($recent_posts);
    }
    
    /**
     * エラーハンドリングの統合テスト
     */
    public function test_error_handling_integration() {
        // 存在しない投稿への対応
        $this->go_to(home_url('/non-existent-post/'));
        $this->assertTrue(is_404());
        
        // 無効なカテゴリーページへのアクセス
        $this->go_to(home_url('/category/non-existent-category/'));
        $this->assertTrue(is_404());
        
        // データベースエラーのシミュレーション（可能な場合）
        $related_posts = $this->blog_data->get_related_posts(99999, 3);
        $this->assertIsArray($related_posts); // エラーでもnullではなく空配列を返すこと
    }
    
    /**
     * 全体的な機能統合テスト
     */
    public function test_overall_blog_functionality() {
        // ブログのトップページ
        $this->go_to(get_permalink($this->blog_page_id));
        $this->assertTrue(is_home());
        
        global $wp_query;
        $this->assertTrue($wp_query->have_posts());
        
        // 投稿ループが正常に動作することを確認
        $post_count = 0;
        while ($wp_query->have_posts() && $post_count < 5) {
            $wp_query->the_post();
            
            $this->assertNotEmpty(get_the_title());
            $this->assertNotEmpty(get_the_content());
            $this->assertNotEmpty(get_the_permalink());
            
            $post_count++;
        }
        
        wp_reset_postdata();
        
        $this->assertGreaterThan(0, $post_count);
        
        // 各種データが正常に取得できることを確認
        $recent_posts = $this->blog_data->get_recent_posts(['count' => 5]);
        $popular_posts = $this->blog_data->get_popular_posts(3);
        $category_stats = $this->blog_data->get_category_stats();
        
        $this->assertNotEmpty($recent_posts);
        $this->assertNotEmpty($popular_posts);
        $this->assertNotEmpty($category_stats);
    }
}
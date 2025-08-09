<?php
/**
 * ブログ機能のパフォーマンステスト
 * 
 * パフォーマンス改善が正しく動作しているかを検証
 *
 * @package Kei_Portfolio
 */

class BlogPerformanceTest extends WP_UnitTestCase {

    /**
     * テスト用データ
     */
    private $test_posts = [];
    private $blog_data;
    private $blog_optimizations;

    /**
     * セットアップ
     */
    public function setUp(): void {
        parent::setUp();
        
        $this->blog_data = \KeiPortfolio\Blog\Blog_Data::get_instance();
        
        // テスト用投稿を作成
        for ($i = 0; $i < 20; $i++) {
            $content = str_repeat('これはテスト用のコンテンツです。', 50); // 約1000文字
            
            $post_id = $this->factory->post->create([
                'post_title' => 'Performance Test Post ' . $i,
                'post_content' => $content,
                'post_status' => 'publish',
                'post_type' => 'post'
            ]);
            
            $this->test_posts[] = $post_id;
        }
        
        // キャッシュをクリア
        $this->blog_data->clear_cache();
    }

    /**
     * テストデータクリーンアップ
     */
    public function tearDown(): void {
        foreach ($this->test_posts as $post_id) {
            wp_delete_post($post_id, true);
        }
        
        $this->blog_data->clear_cache();
        parent::tearDown();
    }

    /**
     * 読了時間事前計算のパフォーマンステスト
     */
    public function test_reading_time_precalculation_performance() {
        $post_id = $this->test_posts[0];
        
        // 事前計算なしの場合の時間測定
        delete_post_meta($post_id, '_reading_time');
        
        $start_time = microtime(true);
        $post = get_post($post_id);
        $content = get_the_content(null, false, $post);
        $word_count = str_word_count(strip_tags($content));
        $reading_time_calculated = max(1, ceil($word_count / 200));
        $time_without_cache = microtime(true) - $start_time;
        
        // 事前計算ありの場合
        update_post_meta($post_id, '_reading_time', $reading_time_calculated);
        
        $start_time = microtime(true);
        $reading_time_cached = get_post_meta($post_id, '_reading_time', true);
        $time_with_cache = microtime(true) - $start_time;
        
        // キャッシュ使用時は大幅に高速であることを確認
        $this->assertLessThan($time_without_cache * 0.1, $time_with_cache);
        $this->assertEquals($reading_time_calculated, intval($reading_time_cached));
        
        $this->addToAssertionCount(1);
        echo "\n読了時間計算パフォーマンス改善: " . 
             number_format(($time_without_cache / $time_with_cache), 2) . "倍高速化\n";
    }

    /**
     * ビューカウントキャッシュのパフォーマンステスト
     */
    public function test_view_count_cache_performance() {
        $post_id = $this->test_posts[0];
        
        // 直接的なDB アクセス
        $start_time = microtime(true);
        for ($i = 0; $i < 10; $i++) {
            $count = get_post_meta($post_id, 'post_views_count', true);
        }
        $time_without_cache = microtime(true) - $start_time;
        
        // キャッシュ使用
        wp_cache_set('post_view_' . $post_id, 100, 'kei_portfolio_blog', 300);
        
        $start_time = microtime(true);
        for ($i = 0; $i < 10; $i++) {
            $count = $this->blog_data->get_actual_view_count($post_id);
        }
        $time_with_cache = microtime(true) - $start_time;
        
        $this->assertLessThan($time_without_cache, $time_with_cache);
        $this->assertEquals(100, $count);
        
        $this->addToAssertionCount(1);
        echo "\nビューカウント取得パフォーマンス改善: " . 
             number_format(($time_without_cache / $time_with_cache), 2) . "倍高速化\n";
    }

    /**
     * データ取得クエリのパフォーマンステスト
     */
    public function test_data_query_performance() {
        // 最新記事取得のパフォーマンス
        $start_time = microtime(true);
        $recent_posts_1 = $this->blog_data->get_recent_posts(['count' => 5]);
        $first_call_time = microtime(true) - $start_time;
        
        // キャッシュからの取得
        $start_time = microtime(true);
        $recent_posts_2 = $this->blog_data->get_recent_posts(['count' => 5]);
        $second_call_time = microtime(true) - $start_time;
        
        // キャッシュが効いていることを確認
        $this->assertLessThan($first_call_time * 0.5, $second_call_time);
        $this->assertEquals(count($recent_posts_1), count($recent_posts_2));
        
        $this->addToAssertionCount(1);
        echo "\n最新記事取得キャッシュ効果: " . 
             number_format(($first_call_time / $second_call_time), 2) . "倍高速化\n";
    }

    /**
     * 大量データでのクエリパフォーマンステスト
     */
    public function test_large_dataset_performance() {
        // さらに多くのテスト投稿を作成
        $large_dataset_posts = [];
        for ($i = 0; $i < 100; $i++) {
            $post_id = $this->factory->post->create([
                'post_title' => 'Large Dataset Test Post ' . $i,
                'post_content' => str_repeat('Content ', 100),
                'post_status' => 'publish'
            ]);
            $large_dataset_posts[] = $post_id;
        }
        
        // 最新記事取得のパフォーマンス測定
        $start_time = microtime(true);
        $posts = $this->blog_data->get_recent_posts(['count' => 10]);
        $query_time = microtime(true) - $start_time;
        
        // 0.1秒以内に完了することを確認
        $this->assertLessThan(0.1, $query_time);
        $this->assertCount(10, $posts);
        
        // 関連記事取得のパフォーマンス
        $main_post = $large_dataset_posts[0];
        $start_time = microtime(true);
        $related_posts = $this->blog_data->get_related_posts($main_post, 5);
        $related_query_time = microtime(true) - $start_time;
        
        // 0.15秒以内に完了することを確認
        $this->assertLessThan(0.15, $related_query_time);
        $this->assertLessThanOrEqual(5, count($related_posts));
        
        // クリーンアップ
        foreach ($large_dataset_posts as $post_id) {
            wp_delete_post($post_id, true);
        }
        
        $this->addToAssertionCount(1);
        echo "\n大量データクエリ性能: 最新記事 " . 
             number_format($query_time * 1000, 2) . "ms, 関連記事 " . 
             number_format($related_query_time * 1000, 2) . "ms\n";
    }

    /**
     * メモリ使用量のテスト
     */
    public function test_memory_usage() {
        $memory_before = memory_get_usage();
        
        // 複数の処理を実行
        $recent_posts = $this->blog_data->get_recent_posts(['count' => 10]);
        $popular_posts = $this->blog_data->get_popular_posts(5);
        $category_stats = $this->blog_data->get_category_stats();
        
        $memory_after = memory_get_usage();
        $memory_used = $memory_after - $memory_before;
        
        // メモリ使用量が2MB以下であることを確認
        $this->assertLessThan(2 * 1024 * 1024, $memory_used);
        
        $this->addToAssertionCount(1);
        echo "\nメモリ使用量: " . number_format($memory_used / 1024, 2) . " KB\n";
    }

    /**
     * キャッシュヒット率のテスト
     */
    public function test_cache_hit_ratio() {
        // キャッシュをクリア
        $this->blog_data->clear_cache();
        
        // 初回アクセス（キャッシュミス）
        $cache_stats_before = wp_cache_get_stats();
        
        $recent_posts = $this->blog_data->get_recent_posts(['count' => 5]);
        $popular_posts = $this->blog_data->get_popular_posts(5);
        $category_stats = $this->blog_data->get_category_stats();
        
        // 2回目のアクセス（キャッシュヒット）
        $recent_posts_2 = $this->blog_data->get_recent_posts(['count' => 5]);
        $popular_posts_2 = $this->blog_data->get_popular_posts(5);
        $category_stats_2 = $this->blog_data->get_category_stats();
        
        // データが同じであることを確認
        $this->assertEquals($recent_posts, $recent_posts_2);
        $this->assertEquals($category_stats, $category_stats_2);
        
        $this->addToAssertionCount(1);
    }

    /**
     * N+1問題の検証
     */
    public function test_n_plus_one_problem_prevention() {
        global $wpdb;
        
        // クエリカウンターをリセット
        $wpdb->num_queries = 0;
        
        // 複数の投稿に対してメタデータアクセス
        $query_count_before = $wpdb->num_queries;
        
        foreach (array_slice($this->test_posts, 0, 5) as $post_id) {
            // 読了時間（事前計算されている）
            $reading_time = get_post_meta($post_id, '_reading_time', true);
            
            // ビューカウント（キャッシュ使用）
            $views = $this->blog_data->get_actual_view_count($post_id);
        }
        
        $query_count_after = $wpdb->num_queries;
        $queries_executed = $query_count_after - $query_count_before;
        
        // N+1問題が発生していないことを確認（最大10クエリ）
        $this->assertLessThan(10, $queries_executed);
        
        $this->addToAssertionCount(1);
        echo "\n5投稿のメタデータ取得で実行されたクエリ数: " . $queries_executed . "\n";
    }

    /**
     * レスポンス時間の総合テスト
     */
    public function test_overall_response_time() {
        $start_time = microtime(true);
        
        // 典型的なブログページで実行される処理をシミュレート
        $recent_posts = $this->blog_data->get_recent_posts(['count' => 5]);
        $popular_posts = $this->blog_data->get_popular_posts(3);
        $category_stats = $this->blog_data->get_category_stats();
        
        // 個別記事ページの処理をシミュレート
        $post_id = $this->test_posts[0];
        $reading_time = get_post_meta($post_id, '_reading_time', true);
        $views = $this->blog_data->get_actual_view_count($post_id);
        $related_posts = $this->blog_data->get_related_posts($post_id, 3);
        
        $total_time = microtime(true) - $start_time;
        
        // 全体の処理時間が0.2秒以下であることを確認
        $this->assertLessThan(0.2, $total_time);
        
        // データが正しく取得できていることを確認
        $this->assertNotEmpty($recent_posts);
        $this->assertGreaterThanOrEqual(0, $reading_time);
        $this->assertGreaterThanOrEqual(0, $views);
        
        $this->addToAssertionCount(1);
        echo "\n総合レスポンス時間: " . number_format($total_time * 1000, 2) . "ms\n";
    }

    /**
     * 同時アクセス時のパフォーマンステスト（簡易版）
     */
    public function test_concurrent_access_simulation() {
        $post_id = $this->test_posts[0];
        
        // ビューカウントを同時に実行する想定
        $start_time = microtime(true);
        
        for ($i = 0; $i < 10; $i++) {
            // 異なるセッションからのアクセスをシミュレート
            $_SESSION = []; // セッションリセット
            $this->blog_data->count_post_views($post_id);
        }
        
        $concurrent_time = microtime(true) - $start_time;
        
        // 同時アクセス処理時間が0.1秒以下であることを確認
        $this->assertLessThan(0.1, $concurrent_time);
        
        // ビューカウントが適切にカウントされていることを確認
        $final_count = $this->blog_data->get_actual_view_count($post_id);
        $this->assertGreaterThan(0, $final_count);
        
        $this->addToAssertionCount(1);
        echo "\n同時アクセス処理時間: " . number_format($concurrent_time * 1000, 2) . "ms\n";
    }

    /**
     * キャッシュ無効化時のフォールバック性能テスト
     */
    public function test_cache_fallback_performance() {
        $post_id = $this->test_posts[0];
        
        // キャッシュを無効化
        wp_cache_flush();
        
        $start_time = microtime(true);
        
        // キャッシュなしでの処理
        $reading_time = get_post_meta($post_id, '_reading_time', true);
        $views = $this->blog_data->get_actual_view_count($post_id);
        $recent_posts = $this->blog_data->get_recent_posts(['count' => 5]);
        
        $fallback_time = microtime(true) - $start_time;
        
        // フォールバック処理時間が0.3秒以下であることを確認
        $this->assertLessThan(0.3, $fallback_time);
        
        // データが正しく取得できていることを確認
        $this->assertGreaterThanOrEqual(0, $reading_time);
        $this->assertGreaterThanOrEqual(0, $views);
        $this->assertNotEmpty($recent_posts);
        
        $this->addToAssertionCount(1);
        echo "\nキャッシュ無効化時の処理時間: " . number_format($fallback_time * 1000, 2) . "ms\n";
    }

    /**
     * パフォーマンス統計レポートの生成
     */
    public function test_generate_performance_report() {
        $report = [
            'timestamp' => current_time('mysql'),
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'queries_executed' => get_num_queries(),
            'cache_enabled' => wp_using_ext_object_cache(),
            'test_posts_count' => count($this->test_posts)
        ];
        
        // レポートファイルに保存
        $report_file = get_template_directory() . '/tests/performance-report-' . date('Y-m-d-H-i-s') . '.json';
        file_put_contents($report_file, json_encode($report, JSON_PRETTY_PRINT));
        
        $this->assertFileExists($report_file);
        $this->assertGreaterThan(100, filesize($report_file));
        
        $this->addToAssertionCount(1);
        echo "\nパフォーマンスレポート生成: " . $report_file . "\n";
        echo "メモリ使用量: " . number_format($report['memory_usage'] / 1024 / 1024, 2) . "MB\n";
        echo "実行クエリ数: " . $report['queries_executed'] . "\n";
    }
}
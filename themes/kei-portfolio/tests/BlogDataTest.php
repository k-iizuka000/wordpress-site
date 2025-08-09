<?php
/**
 * Blog_Dataクラスのユニットテスト
 * 
 * @package KeiPortfolio
 * @subpackage Tests
 */

use PHPUnit\Framework\TestCase;
use KeiPortfolio\Blog\Blog_Data;

require_once dirname(__FILE__) . '/TestHelperClass.php';

class BlogDataTest extends WP_UnitTestCase {
    
    private $blog_data;
    private $test_posts = [];
    private $test_categories = [];
    private $test_tags = [];
    
    /**
     * グローバル状態をリセット（TestHelperClassを使用）
     */
    private function resetGlobalState() {
        TestHelperClass::resetGlobalState();
    }
    
    /**
     * テスト前の初期化
     */
    public function setUp(): void {
        parent::setUp();
        
        // グローバル状態をリセット
        $this->resetGlobalState();
        
        // Blog_Dataクラスのインスタンスを取得
        $this->blog_data = Blog_Data::get_instance();
        
        // テスト用カテゴリーを作成
        $this->test_categories[] = $this->factory->category->create([
            'name' => 'テクノロジー',
            'slug' => 'technology'
        ]);
        
        $this->test_categories[] = $this->factory->category->create([
            'name' => 'チュートリアル', 
            'slug' => 'tutorial'
        ]);
        
        // テスト用タグを作成
        $this->test_tags[] = $this->factory->tag->create([
            'name' => 'PHP',
            'slug' => 'php'
        ]);
        
        $this->test_tags[] = $this->factory->tag->create([
            'name' => 'WordPress',
            'slug' => 'wordpress'
        ]);
        
        // テスト用投稿を作成
        for ($i = 0; $i < 10; $i++) {
            $post_id = $this->factory->post->create([
                'post_title' => 'テスト投稿 ' . ($i + 1),
                'post_content' => 'テスト投稿の内容 ' . ($i + 1) . ' です。この投稿は自動テストで生成されました。',
                'post_status' => 'publish',
                'post_date' => date('Y-m-d H:i:s', strtotime("-{$i} days"))
            ]);
            
            // カテゴリーを設定
            wp_set_post_categories($post_id, [$this->test_categories[0]]);
            
            // タグを設定
            wp_set_post_tags($post_id, ['PHP', 'WordPress']);
            
            // ビューカウントのメタデータを設定（一部の投稿に）
            if ($i < 5) {
                update_post_meta($post_id, 'post_views_count', (10 - $i) * 5);
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
        
        // テスト用タグを削除
        foreach ($this->test_tags as $tag_id) {
            wp_delete_term($tag_id, 'post_tag');
        }
        
        // キャッシュをクリア
        $this->blog_data->clear_cache();
        
        // グローバル状態をリセット
        $this->resetGlobalState();
        
        // プロパティをリセット
        $this->test_posts = [];
        $this->test_categories = [];
        $this->test_tags = [];
        
        parent::tearDown();
    }
    
    /**
     * シングルトンパターンのテスト
     */
    public function test_singleton_pattern() {
        $instance1 = Blog_Data::get_instance();
        $instance2 = Blog_Data::get_instance();
        
        $this->assertSame($instance1, $instance2);
        $this->assertInstanceOf(Blog_Data::class, $instance1);
    }
    
    /**
     * 最新投稿取得のテスト（基本機能）
     */
    public function test_get_recent_posts_basic() {
        $posts = $this->blog_data->get_recent_posts(['count' => 5]);
        
        // より詳細なアサーション
        $this->assertIsArray($posts, 'get_recent_posts should return an array');
        $this->assertCount(5, $posts, 'Should return exactly 5 posts as requested');
        $this->assertNotEmpty($posts, 'Posts array should not be empty');
        
        // 各投稿のプロパティをチェック
        foreach ($posts as $index => $post) {
            $this->assertInstanceOf('WP_Post', $post, "Post at index {$index} should be WP_Post instance");
            $this->assertIsInt($post->ID, "Post ID should be integer at index {$index}");
            $this->assertIsString($post->post_title, "Post title should be string at index {$index}");
            $this->assertNotEmpty($post->post_title, "Post title should not be empty at index {$index}");
            $this->assertEquals('publish', $post->post_status, "Post should be published at index {$index}");
        }
        
        // 最新の投稿が最初に来ることを確認（日付順）
        $this->assertEquals('テスト投稿 1', $posts[0]->post_title, 'Most recent post should be first');
        $this->assertEquals('テスト投稿 5', $posts[4]->post_title, 'Fifth most recent post should be last');
        
        // 日付順（降順）の確認
        for ($i = 0; $i < count($posts) - 1; $i++) {
            $current_date = strtotime($posts[$i]->post_date);
            $next_date = strtotime($posts[$i + 1]->post_date);
            $this->assertGreaterThanOrEqual($next_date, $current_date, "Posts should be in descending date order (index {$i} vs " . ($i + 1) . ")");
        }
    }
    
    /**
     * データプロバイダー：投稿数のテストケース
     */
    public function postCountProvider() {
        return [
            'default_count' => [[], 5], // デフォルト値
            'small_count' => [['count' => 3], 3],
            'large_count' => [['count' => 8], 8],
            'zero_count' => [['count' => 0], 0],
            'negative_count' => [['count' => -1], 0], // 負の値は0として扱われる
        ];
    }
    
    /**
     * 最新投稿取得のテスト（様々な投稿数）
     * @dataProvider postCountProvider
     */
    public function test_get_recent_posts_various_counts($args, $expected_count) {
        $posts = $this->blog_data->get_recent_posts($args);
        
        $this->assertIsArray($posts, 'Should return array');
        
        if ($expected_count > 0) {
            $actual_count = min($expected_count, count($this->test_posts));
            $this->assertCount($actual_count, $posts, "Should return {$actual_count} posts");
            
            foreach ($posts as $index => $post) {
                $this->assertInstanceOf('WP_Post', $post, "Post at index {$index} should be WP_Post");
                $this->assertEquals('publish', $post->post_status, "Post should be published");
            }
        } else {
            $this->assertEmpty($posts, 'Should return empty array for zero or negative count');
        }
    }
    
    /**
     * 最新投稿取得のテスト（カテゴリーフィルター）
     */
    public function test_get_recent_posts_with_category_filter() {
        $posts = $this->blog_data->get_recent_posts([
            'count' => 3,
            'category' => 'technology'
        ]);
        
        $this->assertIsArray($posts);
        $this->assertLessThanOrEqual(3, count($posts));
        
        // すべての投稿がテクノロジーカテゴリーに属していることを確認
        foreach ($posts as $post) {
            $categories = wp_get_post_categories($post->ID);
            $this->assertContains($this->test_categories[0], $categories);
        }
    }
    
    /**
     * キャッシュ機能のテスト（TestHelperClass使用）
     */
    public function test_cache_functionality() {
        $cache_test = TestHelperClass::testCacheEffectiveness(
            $this,
            [$this->blog_data, 'get_recent_posts'],
            [['count' => 5]],
            2.0  // tolerance
        );
        
        // 詳細なアサーション
        $this->assertArrayHasKey('cold_cache', $cache_test, 'Should have cold cache results');
        $this->assertArrayHasKey('warm_cache', $cache_test, 'Should have warm cache results');
        $this->assertArrayHasKey('improvement_ratio', $cache_test, 'Should have improvement ratio');
        
        // 結果の詳細検証
        $cold_posts = $cache_test['cold_cache']['result'];
        $warm_posts = $cache_test['warm_cache']['result'];
        
        $this->assertCount(5, $cold_posts, 'Cold cache should return 5 posts');
        $this->assertCount(5, $warm_posts, 'Warm cache should return 5 posts');
        
        // 投稿IDの順序が同じことを確認
        for ($i = 0; $i < count($cold_posts); $i++) {
            $this->assertEquals(
                $cold_posts[$i]->ID, 
                $warm_posts[$i]->ID,
                "Post ID at index {$i} should be the same in both cache states"
            );
        }
        
        TestHelperClass::logTestInfo('cache_functionality', [
            'cold_cache_time' => $cache_test['cold_cache']['execution_time'],
            'warm_cache_time' => $cache_test['warm_cache']['execution_time'],
            'improvement_ratio' => $cache_test['improvement_ratio']
        ]);
    }
    
    /**
     * 人気投稿取得のテスト
     */
    public function test_get_popular_posts() {
        $posts = $this->blog_data->get_popular_posts(3);
        
        $this->assertIsArray($posts);
        $this->assertLessThanOrEqual(3, count($posts));
        
        // ビューカウントでソートされていることを確認
        if (count($posts) > 1) {
            $first_views = (int)get_post_meta($posts[0]->ID, 'post_views_count', true);
            $second_views = (int)get_post_meta($posts[1]->ID, 'post_views_count', true);
            
            $this->assertGreaterThanOrEqual($second_views, $first_views);
        }
    }
    
    /**
     * 関連投稿取得のテスト
     */
    public function test_get_related_posts() {
        $main_post_id = $this->test_posts[0];
        $related_posts = $this->blog_data->get_related_posts($main_post_id, 3);
        
        $this->assertIsArray($related_posts);
        $this->assertLessThanOrEqual(3, count($related_posts));
        
        // メイン投稿は含まれていないことを確認
        foreach ($related_posts as $post) {
            $this->assertNotEquals($main_post_id, $post->ID);
        }
        
        // 同じカテゴリーまたはタグを持つことを確認（設計上すべて同じカテゴリー・タグを持つ）
        if (!empty($related_posts)) {
            $main_categories = wp_get_post_categories($main_post_id);
            $related_categories = wp_get_post_categories($related_posts[0]->ID);
            
            // カテゴリーに共通点があることを確認
            $common_categories = array_intersect($main_categories, $related_categories);
            $this->assertNotEmpty($common_categories);
        }
    }
    
    /**
     * カテゴリー統計取得のテスト
     */
    public function test_get_category_stats() {
        $stats = $this->blog_data->get_category_stats();
        
        $this->assertIsArray($stats);
        $this->assertNotEmpty($stats);
        
        // 統計の構造を確認
        foreach ($stats as $stat) {
            $this->assertArrayHasKey('id', $stat);
            $this->assertArrayHasKey('name', $stat);
            $this->assertArrayHasKey('slug', $stat);
            $this->assertArrayHasKey('count', $stat);
            $this->assertArrayHasKey('url', $stat);
            
            $this->assertIsInt($stat['id']);
            $this->assertIsString($stat['name']);
            $this->assertIsString($stat['slug']);
            $this->assertIsInt($stat['count']);
            $this->assertIsString($stat['url']);
        }
        
        // 投稿数でソートされていることを確認（降順）
        if (count($stats) > 1) {
            $this->assertGreaterThanOrEqual($stats[1]['count'], $stats[0]['count']);
        }
    }
    
    /**
     * ビューカウント機能のテスト
     */
    public function test_count_post_views() {
        $post_id = $this->test_posts[0];
        
        // 初期状態のビューカウントを削除
        delete_post_meta($post_id, 'post_views_count');
        
        // 初期状態
        $count = get_post_meta($post_id, 'post_views_count', true);
        $this->assertEmpty($count);
        
        // ビューカウント実行
        Blog_Data::count_post_views($post_id);
        
        $count = get_post_meta($post_id, 'post_views_count', true);
        $this->assertEquals('1', $count);
        
        // 再度カウント
        Blog_Data::count_post_views($post_id);
        
        $count = get_post_meta($post_id, 'post_views_count', true);
        $this->assertEquals('2', $count);
        
        // 複数回カウント
        for ($i = 0; $i < 5; $i++) {
            Blog_Data::count_post_views($post_id);
        }
        
        $count = get_post_meta($post_id, 'post_views_count', true);
        $this->assertEquals('7', $count);
    }
    
    /**
     * キャッシュクリア機能のテスト
     */
    public function test_clear_cache() {
        // データを取得してキャッシュに保存
        $posts_before = $this->blog_data->get_recent_posts(['count' => 3]);
        $stats_before = $this->blog_data->get_category_stats();
        
        // 新しい投稿を追加
        $new_post_id = $this->factory->post->create([
            'post_title' => '新しいテスト投稿',
            'post_status' => 'publish',
            'post_date' => date('Y-m-d H:i:s')
        ]);
        
        // キャッシュクリア前は古いデータが取得される
        $posts_cached = $this->blog_data->get_recent_posts(['count' => 3]);
        $this->assertEquals(count($posts_before), count($posts_cached));
        
        // キャッシュクリア
        $this->blog_data->clear_cache();
        
        // キャッシュクリア後は新しいデータが取得される
        $posts_after = $this->blog_data->get_recent_posts(['count' => 3]);
        
        // 新しい投稿が最初に来ることを確認
        $this->assertEquals('新しいテスト投稿', $posts_after[0]->post_title);
        
        // クリーンアップ
        wp_delete_post($new_post_id, true);
    }
    
    /**
     * エラーハンドリングのテスト
     */
    public function test_error_handling() {
        // 存在しない投稿IDで関連投稿を取得
        $related_posts = $this->blog_data->get_related_posts(99999, 3);
        $this->assertIsArray($related_posts);
        
        // 存在しないカテゴリーでフィルタリング
        $posts = $this->blog_data->get_recent_posts([
            'count' => 5,
            'category' => 'nonexistent-category'
        ]);
        $this->assertIsArray($posts);
    }
    
    /**
     * データプロバイダー：パフォーマンステスト用データセット
     */
    public function performanceDataProvider() {
        return [
            'small_dataset' => [10, 5, 2.0], // 投稿数, 取得数, 最大実行時間(秒)
            'medium_dataset' => [25, 10, 3.0],
            'large_dataset' => [50, 20, 5.0],
        ];
    }
    
    /**
     * パフォーマンステスト（様々なデータセットサイズ）
     * @dataProvider performanceDataProvider
     */
    public function test_performance_with_various_datasets($dataset_size, $fetch_count, $max_time) {
        // 大量のテストデータを作成
        $large_dataset_posts = [];
        for ($i = 0; $i < $dataset_size; $i++) {
            $large_dataset_posts[] = $this->factory->post->create([
                'post_title' => 'パフォーマンステスト投稿 ' . $i . '_' . wp_rand(),
                'post_status' => 'publish',
                'post_date' => date('Y-m-d H:i:s', strtotime("-{$i} minutes"))
            ]);
        }
        
        // キャッシュをクリアして公平なテスト環境を作る
        $this->blog_data->clear_cache();
        
        // メモリ使用量の測定開始
        $memory_before = memory_get_usage(true);
        
        // クエリ実行時間を測定
        $start_time = microtime(true);
        $posts = $this->blog_data->get_recent_posts(['count' => $fetch_count]);
        $execution_time = microtime(true) - $start_time;
        
        $memory_after = memory_get_usage(true);
        $memory_increase = $memory_after - $memory_before;
        
        // アサーション
        $this->assertIsArray($posts, 'Should return array');
        $this->assertLessThan($max_time, $execution_time, "Query should execute within {$max_time} seconds for {$dataset_size} posts");
        $this->assertLessThanOrEqual($fetch_count, count($posts), "Should not exceed requested count");
        $this->assertLessThan(20 * 1024 * 1024, $memory_increase, "Memory increase should be less than 20MB");
        
        // 結果の品質確認
        if (!empty($posts)) {
            $this->assertInstanceOf('WP_Post', $posts[0], 'First result should be WP_Post');
            $this->assertEquals('publish', $posts[0]->post_status, 'Posts should be published');
        }
        
        // クリーンアップ
        foreach ($large_dataset_posts as $post_id) {
            wp_delete_post($post_id, true);
        }
    }
    
    /**
     * データ型検証のテスト
     */
    public function test_data_type_validation() {
        $posts = $this->blog_data->get_recent_posts(['count' => 3]);
        
        foreach ($posts as $post) {
            $this->assertInstanceOf(WP_Post::class, $post);
            $this->assertIsInt($post->ID);
            $this->assertIsString($post->post_title);
            $this->assertIsString($post->post_content);
        }
        
        $stats = $this->blog_data->get_category_stats();
        
        foreach ($stats as $stat) {
            $this->assertIsArray($stat);
            $this->assertIsInt($stat['id']);
            $this->assertIsString($stat['name']);
            $this->assertIsString($stat['slug']);
            $this->assertIsInt($stat['count']);
            $this->assertIsString($stat['url']);
        }
    }
}
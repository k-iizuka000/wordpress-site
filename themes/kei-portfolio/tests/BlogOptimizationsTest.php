<?php
/**
 * BlogOptimizationsクラスのテストケース
 *
 * @package KeiPortfolio
 * @subpackage Tests
 */

namespace KeiPortfolio\Tests;

use WP_UnitTestCase;
use KeiPortfolio\Blog\BlogOptimizations;

/**
 * ブログ最適化機能のテスト
 */
class BlogOptimizationsTest extends WP_UnitTestCase {
    
    /**
     * テスト用投稿データ
     * @var array
     */
    private $test_posts = [];
    
    /**
     * ブログ最適化インスタンス
     * @var BlogOptimizations
     */
    private $blog_optimizations;
    
    /**
     * テスト前のセットアップ
     */
    public function setUp(): void {
        parent::setUp();
        
        // テスト用投稿を作成
        for ($i = 0; $i < 5; $i++) {
            $post_id = $this->factory->post->create([
                'post_title' => 'テスト投稿 ' . $i,
                'post_content' => 'テスト用のコンテンツです。' . str_repeat('サンプルテキスト。', 10),
                'post_status' => 'publish',
                'post_type' => 'post'
            ]);
            
            $this->test_posts[] = $post_id;
            
            // アイキャッチ画像を設定
            $attachment_id = $this->factory->attachment->create([
                'file' => 'test-image-' . $i . '.jpg',
                'post_parent' => $post_id,
                'post_mime_type' => 'image/jpeg'
            ]);
            set_post_thumbnail($post_id, $attachment_id);
        }
        
        $this->blog_optimizations = new BlogOptimizations();
    }
    
    /**
     * テスト後のクリーンアップ
     */
    public function tearDown(): void {
        // テストデータの削除
        foreach ($this->test_posts as $post_id) {
            wp_delete_post($post_id, true);
        }
        
        parent::tearDown();
    }
    
    /**
     * カスタム抜粋長さのテスト
     */
    public function test_custom_excerpt_length() {
        // デスクトップでのテスト
        $length = apply_filters('excerpt_length', 55);
        $this->assertTrue($length >= 25 && $length <= 30);
    }
    
    /**
     * AVIF画像サポートのテスト
     */
    public function test_avif_support() {
        // AVIFタイプの添付ファイルを作成
        $attachment_id = $this->factory->attachment->create([
            'file' => 'test-avif.avif',
            'post_mime_type' => 'image/avif'
        ]);
        
        $attachment = get_post($attachment_id);
        
        // AVIF属性の追加テスト
        $attributes = [
            'src' => 'test-avif.avif',
            'alt' => 'Test AVIF Image'
        ];
        
        $optimized_attributes = $this->blog_optimizations->add_avif_support($attributes, $attachment, 'large');
        
        $this->assertEquals('lazy', $optimized_attributes['loading']);
        $this->assertEquals('async', $optimized_attributes['decoding']);
        $this->assertArrayHasKey('fetchpriority', $optimized_attributes);
        
        wp_delete_attachment($attachment_id, true);
    }
    
    /**
     * 抜粋「続きを読む」リンクのテスト
     */
    public function test_custom_excerpt_more() {
        global $post;
        $post = get_post($this->test_posts[0]);
        setup_postdata($post);
        
        $more_text = apply_filters('excerpt_more', '...');
        
        $this->assertStringContainsString('続きを読む', $more_text);
        $this->assertStringContainsString(get_permalink($this->test_posts[0]), $more_text);
        $this->assertStringContainsString('read-more', $more_text);
        
        wp_reset_postdata();
    }
    
    /**
     * クエリ最適化のテスト
     */
    public function test_optimize_blog_queries() {
        // ホームページクエリのテスト
        $query = new \WP_Query([
            'post_type' => 'post',
            'is_main_query' => true
        ]);
        
        // is_home()をシミュレート
        $query->is_home = true;
        
        $this->blog_optimizations->optimize_blog_queries($query);
        
        $this->assertTrue($query->get('no_found_rows'));
        $this->assertFalse($query->get('update_post_meta_cache'));
        $this->assertFalse($query->get('update_post_term_cache'));
        
        $posts_per_page = $query->get('posts_per_page');
        $this->assertTrue($posts_per_page === 6 || $posts_per_page === 9);
    }
    
    /**
     * キャッシュクリア機能のテスト
     */
    public function test_cache_clearing() {
        $post = get_post($this->test_posts[0]);
        
        // キャッシュデータを設定
        wp_cache_set('recent_blog_posts', ['test_data'], 'kei_portfolio_blog');
        wp_cache_set('blog_categories', ['test_categories'], 'kei_portfolio_blog');
        
        // キャッシュクリアを実行
        $this->blog_optimizations->clear_blog_cache('publish', 'draft', $post);
        
        // キャッシュがクリアされているかチェック
        $this->assertFalse(wp_cache_get('recent_blog_posts', 'kei_portfolio_blog'));
        $this->assertFalse(wp_cache_get('blog_categories', 'kei_portfolio_blog'));
    }
    
    /**
     * Speculation Rules APIのテスト
     */
    public function test_speculation_rules_output() {
        // ホームページをシミュレート
        global $wp_query;
        $wp_query->is_home = true;
        
        ob_start();
        $this->blog_optimizations->add_speculation_rules();
        $output = ob_get_clean();
        
        if (!empty($output)) {
            $this->assertStringContainsString('<script type="speculationrules">', $output);
            $this->assertStringContainsString('prerender', $output);
        }
        
        // 後始末
        $wp_query->is_home = false;
    }
    
    /**
     * コンテンツ画像最適化のテスト
     */
    public function test_optimize_content_images() {
        $content = '<p>テストコンテンツ</p><img src="test-image.jpg" alt="Test Image"><p>続きのテキスト</p>';
        
        // 個別記事ページをシミュレート
        global $wp_query;
        $wp_query->is_single = true;
        
        $optimized_content = $this->blog_optimizations->optimize_content_images($content);
        
        // 元のコンテンツが保持されているか確認
        $this->assertStringContainsString('テストコンテンツ', $optimized_content);
        $this->assertStringContainsString('test-image.jpg', $optimized_content);
        
        // 後始末
        $wp_query->is_single = false;
    }
    
    /**
     * エラーハンドリングのテスト
     */
    public function test_error_handling() {
        // 無効な添付ファイルでのテスト
        $invalid_attachment = new \stdClass();
        $invalid_attachment->ID = 99999; // 存在しないID
        
        $attributes = ['src' => 'invalid.jpg'];
        
        // エラーが発生しないことを確認
        $result = $this->blog_optimizations->add_avif_support($attributes, $invalid_attachment, 'large');
        $this->assertIsArray($result);
        $this->assertEquals($attributes, $result);
    }
    
    /**
     * パフォーマンステスト
     */
    public function test_performance() {
        // 大量データでの処理時間テスト
        $start_time = microtime(true);
        
        // 複数の投稿に対して最適化処理を実行
        foreach ($this->test_posts as $post_id) {
            $post = get_post($post_id);
            $this->blog_optimizations->clear_blog_cache('publish', 'draft', $post);
        }
        
        $execution_time = microtime(true) - $start_time;
        
        // 0.1秒以内に完了することを確認
        $this->assertLessThan(0.1, $execution_time, 'パフォーマンス最適化処理が遅すぎます');
    }
}
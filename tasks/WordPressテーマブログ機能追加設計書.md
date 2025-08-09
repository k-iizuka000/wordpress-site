# WordPressテーマ ブログ機能追加設計書

## 概要
kei-portfolioテーマ（ポートフォリオサイト）にブログ投稿機能を追加するための詳細実装設計書

### 文書バージョン
- バージョン: 2.0
- 更新日: 2025年1月8日
- 対象WordPressバージョン: 6.7以降
- PHP要件: 8.0以降（推奨8.2）

## 現状分析

### 既存構造
- **テーマ**: kei-portfolio（カスタムポートフォリオテーマ）
- **カスタム投稿タイプ**: project（プロジェクト/実績用）
- **タクソノミー**: technology（技術スタック）、industry（業界）
- **主要機能**: ポートフォリオ表示、プロジェクト管理、キャッシュ機能

## 推奨実装方法

### 1. ファイル構造

```
themes/kei-portfolio/
├── home.php              # ブログ投稿一覧ページ【新規作成】
├── single.php            # 個別ブログ記事ページ【新規作成】
├── archive.php           # カテゴリー・タグ・日付アーカイブ【新規作成】
├── search.php            # 検索結果ページ【新規作成（オプション）】
├── template-parts/
│   └── blog/            # ブログ関連のテンプレートパーツ【新規作成】
│       ├── hero.php     # ブログセクションのヒーローエリア
│       ├── post-list.php # 投稿一覧表示
│       ├── post-card.php # 投稿カード（グリッド/リスト用）
│       ├── sidebar.php   # サイドバー（カテゴリー、最新記事等）
│       └── pagination.php # ページネーション
└── page-blog.php         # ブログトップページ（固定ページ用）【新規作成】
```

### 2. テンプレート階層の活用

#### 必須テンプレート
1. **home.php**
   - ブログ投稿インデックステンプレート
   - WordPress設定で「投稿ページ」として指定された固定ページで使用
   - 投稿一覧、ページネーション、サイドバーを含む

2. **single.php**
   - 個別ブログ記事表示用
   - 記事本文、投稿者情報、関連記事、コメントセクション
   - ソーシャルシェアボタン

3. **archive.php**
   - カテゴリー、タグ、著者、日付別アーカイブ
   - フィルタリング機能
   - 一覧表示形式（グリッド/リスト切り替え）

#### フォールバック階層
```
個別投稿: single.php → singular.php → index.php
ブログ一覧: home.php → index.php
アーカイブ: category.php → archive.php → index.php
```

### 3. 2025年ベストプラクティス（検証済み）

#### A. ブロックエディタ・FSE対応
- **ブロックパターンの活用**: 再利用可能なブログレイアウトパターンを作成
- **テンプレートパーツ**: header/footer/sidebarをブロックベースで構築
- **theme.json活用**: グローバルスタイル設定とブロック設定の一元管理
- **クラシックテーマとの互換性維持**: 段階的な移行が可能な設計

#### B. コンテンツ分離戦略
- **ポートフォリオ（project）**: 実績・作品展示用
- **ブログ（post）**: 技術記事、チュートリアル、お知らせ用
- 明確なナビゲーション分離
- 相互リンクで回遊性向上

#### C. パフォーマンス最適化（詳細実装）
```php
// inc/blog-optimizations.php として新規作成
<?php
/**
 * ブログ機能のパフォーマンス最適化
 */

namespace KeiPortfolio\Blog;

class BlogOptimizations {
    
    public function __construct() {
        $this->init_hooks();
    }
    
    private function init_hooks() {
        // 画像最適化
        add_filter('wp_lazy_loading_enabled', '__return_true');
        add_filter('wp_get_attachment_image_attributes', [$this, 'add_avif_support'], 10, 3);
        
        // コンテンツ最適化
        add_filter('excerpt_length', [$this, 'custom_excerpt_length']);
        add_filter('excerpt_more', [$this, 'custom_excerpt_more']);
        
        // クエリ最適化
        add_action('pre_get_posts', [$this, 'optimize_blog_queries']);
        
        // Speculation Rules API（先読み）
        add_action('wp_head', [$this, 'add_speculation_rules']);
        
        // キャッシュ戦略
        add_action('transition_post_status', [$this, 'clear_blog_cache'], 10, 3);
    }
    
    public function add_avif_support($attributes, $attachment, $size) {
        // AVIF形式のサポート
        $mime_type = get_post_mime_type($attachment->ID);
        if ($mime_type === 'image/avif') {
            $attributes['loading'] = 'lazy';
            $attributes['decoding'] = 'async';
        }
        return $attributes;
    }
    
    public function custom_excerpt_length() {
        return is_mobile() ? 20 : 30;
    }
    
    public function custom_excerpt_more() {
        return '... <a href="' . get_permalink() . '" class="read-more">' . __('続きを読む', 'kei-portfolio') . '</a>';
    }
    
    public function optimize_blog_queries($query) {
        if (!is_admin() && $query->is_main_query()) {
            if (is_home() || is_archive()) {
                $query->set('posts_per_page', 9);
                $query->set('no_found_rows', true); // カウントクエリをスキップ
                $query->set('update_post_meta_cache', false);
                $query->set('update_post_term_cache', false);
            }
        }
    }
    
    public function add_speculation_rules() {
        if (is_home() || is_archive()) {
            ?>
            <script type="speculationrules">
            {
                "prerender": [{
                    "source": "list",
                    "urls": ["/blog/*"]
                }]
            }
            </script>
            <?php
        }
    }
    
    public function clear_blog_cache($new_status, $old_status, $post) {
        if ($post->post_type === 'post' && $new_status === 'publish') {
            wp_cache_delete('recent_blog_posts', 'kei_portfolio');
            wp_cache_delete('blog_categories', 'kei_portfolio');
        }
    }
}

// インスタンス化
new BlogOptimizations();
```

#### D. モバイルファースト設計（詳細実装）
```css
/* assets/css/blog-mobile.css */

/* モバイルファースト基本設定 */
.blog-container {
    width: 100%;
    max-width: 100vw;
    overflow-x: hidden;
    padding: 1rem;
}

/* タッチフレンドリーな要素 */
.blog-card,
.pagination a,
.category-filter button {
    min-height: 44px;
    min-width: 44px;
    padding: 12px 16px;
    touch-action: manipulation; /* ダブルタップズーム防止 */
}

/* 読みやすいタイポグラフィ */
.blog-content {
    font-size: 16px;
    line-height: 1.6;
    -webkit-text-size-adjust: 100%; /* iOS文字サイズ自動調整防止 */
}

/* レスポンシブグリッド */
.blog-grid {
    display: grid;
    gap: 1rem;
    grid-template-columns: 1fr;
}

@media (min-width: 640px) {
    .blog-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }
}

@media (min-width: 1024px) {
    .blog-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 2rem;
    }
    
    .blog-container {
        padding: 2rem;
    }
}

/* スワイプ対応カルーセル */
.featured-posts {
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    -webkit-overflow-scrolling: touch;
}

.featured-post-item {
    scroll-snap-align: start;
    flex: 0 0 85%;
}
```

#### E. SEO対策とAI統合（2025年最新）
```php
// inc/blog-seo.php
<?php
namespace KeiPortfolio\Blog;

class BlogSEO {
    
    public function __construct() {
        add_action('wp_head', [$this, 'add_structured_data']);
        add_action('wp_head', [$this, 'add_ogp_tags']);
        add_filter('wp_sitemaps_posts_query_args', [$this, 'customize_sitemap']);
        
        // AI機能統合用フック
        add_filter('the_content', [$this, 'add_voice_search_optimization']);
    }
    
    public function add_structured_data() {
        if (is_single() && get_post_type() === 'post') {
            $post = get_post();
            $schema = [
                '@context' => 'https://schema.org',
                '@type' => 'BlogPosting',
                'headline' => get_the_title(),
                'datePublished' => get_the_date('c'),
                'dateModified' => get_the_modified_date('c'),
                'author' => [
                    '@type' => 'Person',
                    'name' => get_the_author()
                ],
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => get_bloginfo('name'),
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => get_theme_mod('custom_logo')
                    ]
                ],
                'description' => get_the_excerpt(),
                'image' => get_the_post_thumbnail_url(null, 'large')
            ];
            
            echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
        }
    }
    
    public function add_ogp_tags() {
        if (is_single() || is_page()) {
            ?>
            <meta property="og:title" content="<?php echo esc_attr(get_the_title()); ?>" />
            <meta property="og:type" content="article" />
            <meta property="og:url" content="<?php echo esc_url(get_permalink()); ?>" />
            <meta property="og:description" content="<?php echo esc_attr(get_the_excerpt()); ?>" />
            <?php if (has_post_thumbnail()) : ?>
                <meta property="og:image" content="<?php echo esc_url(get_the_post_thumbnail_url(null, 'large')); ?>" />
            <?php endif;
        }
    }
    
    public function add_voice_search_optimization($content) {
        // 音声検索用のFAQセクション自動生成（AI活用候補）
        if (is_single() && get_post_type() === 'post') {
            // ヘッダータグからFAQ形式の構造化データを生成
            $faq_schema = $this->generate_faq_from_headers($content);
            if ($faq_schema) {
                $content .= '<script type="application/ld+json">' . json_encode($faq_schema) . '</script>';
            }
        }
        return $content;
    }
    
    private function generate_faq_from_headers($content) {
        // H2, H3タグから質問形式のコンテンツを抽出
        preg_match_all('/<h[23]>(.*?)<\/h[23]>/i', $content, $headers);
        if (empty($headers[1])) {
            return null;
        }
        
        $faq_items = [];
        foreach ($headers[1] as $header) {
            if (strpos($header, '？') !== false || strpos($header, '?') !== false) {
                $faq_items[] = [
                    '@type' => 'Question',
                    'name' => strip_tags($header),
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Answer content here' // AIで生成可能
                    ]
                ];
            }
        }
        
        if (!empty($faq_items)) {
            return [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => $faq_items
            ];
        }
        
        return null;
    }
}
```

### 4. 実装手順

#### Phase 1: 基本構造（必須）
1. テンプレートファイル作成
   - home.php
   - single.php
   - archive.php

2. テンプレートパーツ作成
   - template-parts/blog/配下のファイル

3. メニュー統合
   - header.phpにブログリンク追加

#### Phase 2: 機能拡張（推奨）
1. サイドバーウィジェット
   - 最新記事
   - カテゴリー一覧
   - タグクラウド
   - 検索フォーム

2. 関連記事表示
   - カテゴリーベース
   - タグベース

3. コメント機能
   - スパム対策（Akismet連携）
   - 承認制

#### Phase 3: 高度な機能（オプション）
1. Ajax無限スクロール
2. 記事のお気に入り機能
3. 読了時間表示
4. 目次自動生成
5. ソーシャルシェア統計

### 5. スタイリング指針

```css
/* ブログセクション専用スタイル */
.blog-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.blog-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
}

.blog-card {
    border-radius: 8px;
    overflow: hidden;
    transition: transform 0.3s;
}

.blog-card:hover {
    transform: translateY(-5px);
}
```

### 6. セキュリティ考慮事項

- XSS対策: `esc_html()`, `esc_url()`, `wp_kses()`の適切な使用
- SQLインジェクション対策: `$wpdb->prepare()`の使用
- CSRF対策: nonce検証
- ファイルアップロード制限
- コメントスパム対策

### 7. 既存機能との統合

#### キャッシュ機能の活用（Portfolio_Dataクラス拡張）
```php
// inc/class-blog-data.php
<?php
namespace KeiPortfolio\Blog;

use Portfolio_Data;

/**
 * ブログデータ管理クラス
 * Portfolio_Dataクラスの設計パターンを踏襲
 */
class Blog_Data {
    
    private static $instance = null;
    private $cache_group = 'kei_portfolio_blog';
    private $cache_expiration = 3600; // 1時間
    
    /**
     * シングルトンパターンでインスタンス取得
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // キャッシュクリアフック
        add_action('save_post_post', [$this, 'clear_cache']);
        add_action('deleted_post', [$this, 'clear_cache']);
        add_action('switch_theme', [$this, 'clear_cache']);
    }
    
    /**
     * 最新の投稿を取得（キャッシュ付き）
     */
    public function get_recent_posts($args = []) {
        $defaults = [
            'count' => 5,
            'category' => '',
            'exclude_sticky' => false
        ];
        
        $args = wp_parse_args($args, $defaults);
        $cache_key = 'recent_posts_' . md5(serialize($args));
        
        $posts = wp_cache_get($cache_key, $this->cache_group);
        
        if (false === $posts) {
            $query_args = [
                'post_type' => 'post',
                'posts_per_page' => $args['count'],
                'post_status' => 'publish',
                'orderby' => 'date',
                'order' => 'DESC'
            ];
            
            if (!empty($args['category'])) {
                $query_args['category_name'] = $args['category'];
            }
            
            if ($args['exclude_sticky']) {
                $query_args['ignore_sticky_posts'] = true;
            }
            
            $posts = get_posts($query_args);
            wp_cache_set($cache_key, $posts, $this->cache_group, $this->cache_expiration);
        }
        
        return $posts;
    }
    
    /**
     * 人気の投稿を取得（ビュー数ベース）
     */
    public function get_popular_posts($count = 5) {
        $cache_key = 'popular_posts_' . $count;
        $posts = wp_cache_get($cache_key, $this->cache_group);
        
        if (false === $posts) {
            $posts = get_posts([
                'post_type' => 'post',
                'posts_per_page' => $count,
                'meta_key' => 'post_views_count',
                'orderby' => 'meta_value_num',
                'order' => 'DESC'
            ]);
            
            wp_cache_set($cache_key, $posts, $this->cache_group, $this->cache_expiration);
        }
        
        return $posts;
    }
    
    /**
     * 関連投稿を取得
     */
    public function get_related_posts($post_id, $count = 3) {
        $cache_key = 'related_posts_' . $post_id . '_' . $count;
        $posts = wp_cache_get($cache_key, $this->cache_group);
        
        if (false === $posts) {
            $categories = wp_get_post_categories($post_id);
            $tags = wp_get_post_tags($post_id, ['fields' => 'ids']);
            
            $posts = get_posts([
                'post_type' => 'post',
                'posts_per_page' => $count,
                'post__not_in' => [$post_id],
                'category__in' => $categories,
                'tag__in' => $tags,
                'orderby' => 'rand'
            ]);
            
            wp_cache_set($cache_key, $posts, $this->cache_group, $this->cache_expiration);
        }
        
        return $posts;
    }
    
    /**
     * カテゴリー統計を取得
     */
    public function get_category_stats() {
        $cache_key = 'category_stats';
        $stats = wp_cache_get($cache_key, $this->cache_group);
        
        if (false === $stats) {
            $categories = get_categories([
                'orderby' => 'count',
                'order' => 'DESC',
                'hide_empty' => true
            ]);
            
            $stats = [];
            foreach ($categories as $category) {
                $stats[] = [
                    'id' => $category->term_id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'count' => $category->count,
                    'url' => get_category_link($category->term_id)
                ];
            }
            
            wp_cache_set($cache_key, $stats, $this->cache_group, $this->cache_expiration);
        }
        
        return $stats;
    }
    
    /**
     * キャッシュをクリア
     */
    public function clear_cache() {
        // グループ全体のキャッシュをクリア
        wp_cache_delete_multiple(
            ['recent_posts', 'popular_posts', 'related_posts', 'category_stats'],
            $this->cache_group
        );
        
        // 既存のPortfolio_Dataキャッシュもクリア（相互参照がある場合）
        $portfolio_data = Portfolio_Data::get_instance();
        $portfolio_data->clear_cache();
    }
    
    /**
     * 投稿ビュー数をカウント
     */
    public static function count_post_views($post_id) {
        if (!is_single()) {
            return;
        }
        
        $count_key = 'post_views_count';
        $count = get_post_meta($post_id, $count_key, true);
        
        if ($count == '') {
            $count = 0;
            delete_post_meta($post_id, $count_key);
            add_post_meta($post_id, $count_key, '0');
        } else {
            $count++;
            update_post_meta($post_id, $count_key, $count);
        }
    }
}

// ビューカウント用フック
add_action('wp_head', function() {
    if (is_single() && get_post_type() === 'post') {
        Blog_Data::count_post_views(get_the_ID());
    }
});
```

### 8. テスト戦略（詳細）

#### ユニットテスト設計
```php
// tests/BlogDataTest.php
<?php
namespace KeiPortfolio\Tests;

use WP_UnitTestCase;
use KeiPortfolio\Blog\Blog_Data;

class BlogDataTest extends WP_UnitTestCase {
    
    private $blog_data;
    private $test_posts = [];
    
    public function setUp(): void {
        parent::setUp();
        $this->blog_data = Blog_Data::get_instance();
        
        // テスト用投稿作成
        for ($i = 0; $i < 10; $i++) {
            $this->test_posts[] = $this->factory->post->create([
                'post_title' => 'Test Post ' . $i,
                'post_content' => 'Test content ' . $i,
                'post_status' => 'publish'
            ]);
        }
    }
    
    public function tearDown(): void {
        // テストデータクリーンアップ
        foreach ($this->test_posts as $post_id) {
            wp_delete_post($post_id, true);
        }
        
        // キャッシュクリア
        $this->blog_data->clear_cache();
        
        parent::tearDown();
    }
    
    /**
     * 最新投稿取得のテスト
     */
    public function test_get_recent_posts() {
        $posts = $this->blog_data->get_recent_posts(['count' => 5]);
        
        $this->assertCount(5, $posts);
        $this->assertEquals('Test Post 9', $posts[0]->post_title);
    }
    
    /**
     * キャッシュ機能のテスト
     */
    public function test_cache_functionality() {
        // 初回取得（キャッシュなし）
        $start_time = microtime(true);
        $posts1 = $this->blog_data->get_recent_posts(['count' => 5]);
        $first_call_time = microtime(true) - $start_time;
        
        // 2回目取得（キャッシュあり）
        $start_time = microtime(true);
        $posts2 = $this->blog_data->get_recent_posts(['count' => 5]);
        $second_call_time = microtime(true) - $start_time;
        
        // キャッシュが効いているか確認
        $this->assertLessThan($first_call_time, $second_call_time);
        $this->assertEquals($posts1, $posts2);
    }
    
    /**
     * 関連投稿取得のテスト
     */
    public function test_get_related_posts() {
        // カテゴリー付き投稿を作成
        $category_id = $this->factory->category->create(['name' => 'Test Category']);
        
        $main_post = $this->factory->post->create([
            'post_title' => 'Main Post',
            'post_category' => [$category_id]
        ]);
        
        $related_posts = [];
        for ($i = 0; $i < 3; $i++) {
            $related_posts[] = $this->factory->post->create([
                'post_title' => 'Related Post ' . $i,
                'post_category' => [$category_id]
            ]);
        }
        
        $results = $this->blog_data->get_related_posts($main_post, 3);
        
        $this->assertLessThanOrEqual(3, count($results));
        
        // クリーンアップ
        wp_delete_post($main_post, true);
        foreach ($related_posts as $post_id) {
            wp_delete_post($post_id, true);
        }
        wp_delete_term($category_id, 'category');
    }
    
    /**
     * ビューカウントのテスト
     */
    public function test_post_view_count() {
        $post_id = $this->test_posts[0];
        
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
    }
}
```

#### 統合テスト設計
```php
// tests/BlogIntegrationTest.php
<?php
class BlogIntegrationTest extends WP_UnitTestCase {
    
    /**
     * ブログページの表示テスト
     */
    public function test_blog_archive_page_display() {
        // ブログページを作成
        $blog_page_id = $this->factory->post->create([
            'post_type' => 'page',
            'post_title' => 'Blog',
            'post_status' => 'publish'
        ]);
        
        // ブログページとして設定
        update_option('page_for_posts', $blog_page_id);
        
        // ページにアクセス
        $this->go_to(get_permalink($blog_page_id));
        
        // 正しいテンプレートが使用されているか
        $this->assertTrue(is_home());
        $this->assertFalse(is_front_page());
        
        // クリーンアップ
        wp_delete_post($blog_page_id, true);
        delete_option('page_for_posts');
    }
    
    /**
     * SEO構造化データのテスト
     */
    public function test_structured_data_output() {
        $post_id = $this->factory->post->create([
            'post_title' => 'SEO Test Post',
            'post_content' => 'Test content for SEO',
            'post_status' => 'publish'
        ]);
        
        $this->go_to(get_permalink($post_id));
        
        ob_start();
        do_action('wp_head');
        $output = ob_get_clean();
        
        // 構造化データが含まれているか
        $this->assertStringContainsString('"@type":"BlogPosting"', $output);
        $this->assertStringContainsString('"headline":"SEO Test Post"', $output);
        
        wp_delete_post($post_id, true);
    }
}
```

#### パフォーマンステスト
```php
// tests/BlogPerformanceTest.php
<?php
class BlogPerformanceTest extends WP_UnitTestCase {
    
    /**
     * クエリパフォーマンステスト
     */
    public function test_query_performance() {
        // 大量のテストデータ作成
        $post_ids = [];
        for ($i = 0; $i < 100; $i++) {
            $post_ids[] = $this->factory->post->create([
                'post_status' => 'publish'
            ]);
        }
        
        // クエリ実行時間測定
        $start_time = microtime(true);
        
        $query = new WP_Query([
            'post_type' => 'post',
            'posts_per_page' => 10,
            'no_found_rows' => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false
        ]);
        
        $execution_time = microtime(true) - $start_time;
        
        // 0.1秒以内に完了することを確認
        $this->assertLessThan(0.1, $execution_time);
        
        // クリーンアップ
        foreach ($post_ids as $post_id) {
            wp_delete_post($post_id, true);
        }
    }
}
```

### 9. テストチェックリスト

#### 機能テスト
- [ ] home.phpテンプレートの表示確認
- [ ] single.phpテンプレートの表示確認
- [ ] archive.phpテンプレートの表示確認
- [ ] ページネーション動作（前へ/次へ/番号）
- [ ] カテゴリーフィルタリング機能
- [ ] タグフィルタリング機能
- [ ] 検索機能（日本語対応）
- [ ] コメント投稿・表示機能
- [ ] 関連記事表示
- [ ] ソーシャルシェアボタン動作

#### パフォーマンステスト
- [ ] PageSpeed Insights モバイル: 90点以上
- [ ] PageSpeed Insights デスクトップ: 95点以上
- [ ] Core Web Vitals:
  - [ ] LCP (Largest Contentful Paint) < 2.5秒
  - [ ] FID (First Input Delay) < 100ms
  - [ ] CLS (Cumulative Layout Shift) < 0.1
- [ ] TTFB (Time to First Byte) < 600ms
- [ ] 画像遅延読み込み動作確認
- [ ] AVIFフォーマット対応確認
- [ ] キャッシュ機能動作確認

#### レスポンシブデザインテスト
- [ ] iPhone SE (375px)
- [ ] iPhone 14 Pro (390px)
- [ ] iPad (768px)
- [ ] iPad Pro (1024px)
- [ ] Desktop (1440px)
- [ ] Wide Desktop (1920px)

#### SEOテスト
- [ ] 構造化データ検証（Google Rich Results Test）
- [ ] OGPタグ検証（Facebook Debugger）
- [ ] XMLサイトマップ生成確認
- [ ] パンくずリスト表示
- [ ] メタディスクリプション表示
- [ ] canonical URL設定

#### アクセシビリティテスト
- [ ] WCAG 2.1 AA準拠
- [ ] キーボードナビゲーション
- [ ] スクリーンリーダー対応
- [ ] 色コントラスト比（4.5:1以上）
- [ ] フォーカスインジケーター表示
- [ ] ARIAラベル適切な設定

#### セキュリティテスト
- [ ] XSS脆弱性チェック
- [ ] SQLインジェクション対策確認
- [ ] CSRF対策（nonce検証）
- [ ] 入力値サニタイゼーション
- [ ] 出力エスケープ処理
- [ ] ファイルアップロード制限

#### ブラウザ互換性テスト
- [ ] Chrome (最新版)
- [ ] Firefox (最新版)
- [ ] Safari (最新版)
- [ ] Edge (最新版)
- [ ] Safari iOS (最新版)
- [ ] Chrome Android (最新版)

### 10. 実装スケジュール

#### Phase 1: 基礎実装（1週目）
- Day 1-2: テンプレートファイル作成
  - home.php, single.php, archive.php
  - 基本的なHTML構造とWordPressループ実装
- Day 3-4: テンプレートパーツ作成
  - template-parts/blog/配下のファイル群
  - 再利用可能なコンポーネント化
- Day 5: スタイリング基礎
  - blog.css, blog-mobile.css作成
  - レスポンシブデザイン実装

#### Phase 2: 機能実装（2週目）
- Day 6-7: Blog_Dataクラス実装
  - キャッシュ機能
  - データ取得メソッド群
- Day 8-9: SEO機能実装
  - 構造化データ
  - OGPタグ
  - 音声検索最適化
- Day 10: パフォーマンス最適化
  - Speculation Rules API
  - クエリ最適化
  - 画像最適化

#### Phase 3: テストと改善（3週目）
- Day 11-12: ユニットテスト実装
- Day 13: 統合テスト実装
- Day 14: パフォーマンステストと最適化
- Day 15: 最終調整とドキュメント作成

### 11. 納品物

#### コードファイル
1. **テンプレートファイル**
   - `/themes/kei-portfolio/home.php`
   - `/themes/kei-portfolio/single.php`
   - `/themes/kei-portfolio/archive.php`
   - `/themes/kei-portfolio/search.php`
   - `/themes/kei-portfolio/page-blog.php`

2. **テンプレートパーツ**
   - `/themes/kei-portfolio/template-parts/blog/hero.php`
   - `/themes/kei-portfolio/template-parts/blog/post-list.php`
   - `/themes/kei-portfolio/template-parts/blog/post-card.php`
   - `/themes/kei-portfolio/template-parts/blog/sidebar.php`
   - `/themes/kei-portfolio/template-parts/blog/pagination.php`
   - `/themes/kei-portfolio/template-parts/blog/related-posts.php`
   - `/themes/kei-portfolio/template-parts/blog/share-buttons.php`

3. **機能ファイル**
   - `/themes/kei-portfolio/inc/class-blog-data.php`
   - `/themes/kei-portfolio/inc/blog-optimizations.php`
   - `/themes/kei-portfolio/inc/blog-seo.php`
   - `/themes/kei-portfolio/inc/blog-widgets.php`

4. **スタイルシート**
   - `/themes/kei-portfolio/assets/css/blog.css`
   - `/themes/kei-portfolio/assets/css/blog-mobile.css`
   - `/themes/kei-portfolio/assets/css/blog-print.css`

5. **JavaScript**
   - `/themes/kei-portfolio/assets/js/blog.js`
   - `/themes/kei-portfolio/assets/js/blog-ajax.js`
   - `/themes/kei-portfolio/assets/js/blog-search.js`

6. **テストファイル**
   - `/themes/kei-portfolio/tests/BlogDataTest.php`
   - `/themes/kei-portfolio/tests/BlogIntegrationTest.php`
   - `/themes/kei-portfolio/tests/BlogPerformanceTest.php`
   - `/themes/kei-portfolio/tests/BlogSecurityTest.php`

#### ドキュメント
1. **管理者向けドキュメント**
   - `/docs/ブログ機能管理者ガイド.md`
   - ブログ投稿方法
   - カテゴリー・タグ管理
   - ウィジェット設定
   - キャッシュ管理

2. **開発者向けドキュメント**
   - `/docs/ブログ機能技術仕様書.md`
   - クラス構造
   - フック一覧
   - カスタマイズ方法
   - API仕様

3. **テストドキュメント**
   - `/tests/ブログ機能テスト結果レポート.md`
   - テストカバレッジレポート
   - パフォーマンステスト結果
   - セキュリティ監査結果

### 12. 今後の拡張可能性（2025-2026ロードマップ）

#### 2025年Q2-Q3
- **AI統合強化**
  - ChatGPT/Claude APIを使用した自動要約生成
  - AIによる関連記事レコメンデーション
  - コンテンツ生成アシスタント
  - 自動タグ付け機能

- **音声機能**
  - 音声入力によるコメント投稿
  - 記事の音声読み上げ機能
  - ポッドキャスト統合

#### 2025年Q4
- **ヘッドレスCMS化**
  - REST API完全対応
  - GraphQL エンドポイント追加
  - Next.js/Nuxt.jsフロントエンド対応

- **リアルタイムコラボレーション**
  - 複数ユーザー同時編集
  - コメント・注釈機能
  - バージョン管理強化

#### 2026年Q1
- **PWA完全対応**
  - オフライン閲覧
  - プッシュ通知
  - アプリライクなUX

- **Web3統合**
  - NFTコンテンツ対応
  - 暗号通貨での投げ銭機能
  - 分散型コンテンツ配信

- **グリーンウェブ対応**
  - カーボンフットプリント測定
  - エコモード実装
  - 持続可能なホスティング最適化

## まとめ

この詳細設計書に基づいて実装することで、2025年の最新のWordPressベストプラクティスに準拠した、高性能で拡張性の高いブログ機能を既存のkei-portfolioテーマに統合できます。

### 主な特徴
1. **最新技術の活用**: FSE対応、Speculation Rules API、AVIF画像形式対応
2. **パフォーマンス重視**: 多層キャッシング、クエリ最適化、遅延読み込み
3. **SEO最適化**: 構造化データ、音声検索対応、AI統合準備
4. **テスト駆動開発**: 包括的なテストスイートによる品質保証
5. **段階的実装**: 3週間で基本機能を実装し、順次拡張可能

### 成功指標
- PageSpeed Insights: モバイル90点以上、デスクトップ95点以上
- Core Web Vitals: すべての指標で「良好」評価
- テストカバレッジ: 80%以上
- WCAG 2.1 AA準拠
- セキュリティ監査: 脆弱性ゼロ

### 技術的負債の最小化
- モジュラー設計による保守性の確保
- 十分なコメントとドキュメント
- 継続的なテストとリファクタリング
- WordPress コーディング規約の厳格な遵守

この設計に従って実装することで、ユーザーエクスペリエンスとSEOパフォーマンスの両方で優れた成果を達成できます。
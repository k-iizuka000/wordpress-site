<?php
/**
 * ブログ機能のパフォーマンス最適化
 * 
 * 画像最適化、クエリ最適化、キャッシング戦略、Speculation Rules API対応など
 * 
 * @package Kei_Portfolio
 * @since 1.0.0
 */

namespace KeiPortfolio\Blog;

// キャッシュ設定定数
if (!defined('KEI_BLOG_CACHE_TTL')) {
    define('KEI_BLOG_CACHE_TTL', 3600); // 1時間
}

/**
 * Blog_Optimizations クラス
 * 
 * ブログのパフォーマンス最適化を担当
 */
class Blog_Optimizations {
    
    /**
     * コンストラクタ
     */
    public function __construct() {
        $this->init_hooks();
    }
    
    /**
     * フックの初期化
     */
    private function init_hooks() {
        // 画像最適化
        add_filter('wp_lazy_loading_enabled', '__return_true');
        add_filter('wp_get_attachment_image_attributes', [$this, 'add_image_optimizations'], 10, 3);
        add_action('wp_head', [$this, 'add_image_preload_hints']);
        
        // コンテンツ最適化
        add_filter('excerpt_length', [$this, 'custom_excerpt_length']);
        add_filter('excerpt_more', [$this, 'custom_excerpt_more']);
        
        // クエリ最適化
        add_action('pre_get_posts', [$this, 'optimize_blog_queries']);
        add_action('wp_head', [$this, 'add_resource_hints']);
        
        // Speculation Rules API（先読み）
        add_action('wp_head', [$this, 'add_speculation_rules']);
        
        // キャッシュ戦略
        add_action('transition_post_status', [$this, 'clear_blog_cache'], 10, 3);
        add_action('wp_footer', [$this, 'add_performance_monitoring']);
        
        // 遅延読み込みとプリロード
        add_action('wp_enqueue_scripts', [$this, 'optimize_script_loading']);
        add_filter('script_loader_tag', [$this, 'add_async_defer_attributes'], 10, 2);
        
        // WebP/AVIF対応
        add_filter('wp_generate_attachment_metadata', [$this, 'generate_next_gen_images'], 10, 2);
        
        // Service Worker for caching
        add_action('wp_head', [$this, 'add_service_worker']);
        
        // Critical CSS inline
        add_action('wp_head', [$this, 'inline_critical_css'], 1);
        
        // Database query optimization
        add_action('init', [$this, 'optimize_database_queries']);
        
        // Memory optimization
        add_action('wp_footer', [$this, 'cleanup_memory']);
        
        // 読了時間の事前計算
        add_action('save_post', [$this, 'calculate_reading_time_on_save'], 10, 2);
        add_action('wp_insert_post', [$this, 'calculate_reading_time_on_save'], 10, 2);
    }
    
    /**
     * 画像最適化の属性を追加
     */
    public function add_image_optimizations($attributes, $attachment, $size) {
        // AVIF/WebP形式のサポート
        $mime_type = get_post_mime_type($attachment->ID);
        
        // 遅延読み込みとデコード最適化
        $attributes['loading'] = $attributes['loading'] ?? 'lazy';
        $attributes['decoding'] = 'async';
        
        // Above-the-fold画像は即座に読み込み
        if ($this->is_above_fold_image($attachment->ID)) {
            $attributes['loading'] = 'eager';
            unset($attributes['loading']); // eager is default
        }
        
        // 画像サイズヒントを追加
        if (!isset($attributes['sizes'])) {
            $attributes['sizes'] = $this->generate_responsive_sizes();
        }
        
        // 画像の重要度を設定
        if ($this->is_critical_image($attachment->ID)) {
            $attributes['fetchpriority'] = 'high';
        }
        
        return $attributes;
    }
    
    /**
     * Above-the-fold画像かどうかを判定
     */
    private function is_above_fold_image($attachment_id) {
        // アイキャッチ画像は通常above-the-fold
        if (is_singular() && get_post_thumbnail_id() === $attachment_id) {
            return true;
        }
        
        // ヒーローセクションの画像
        $hero_image_id = get_theme_mod('blog_hero_image');
        if ($hero_image_id === $attachment_id) {
            return true;
        }
        
        return false;
    }
    
    /**
     * クリティカルな画像かどうかを判定
     */
    private function is_critical_image($attachment_id) {
        // ロゴやアイキャッチなどの重要な画像
        $critical_images = [
            get_theme_mod('custom_logo'),
            get_post_thumbnail_id(),
            get_theme_mod('blog_hero_image')
        ];
        
        return in_array($attachment_id, array_filter($critical_images));
    }
    
    /**
     * レスポンシブ画像のサイズ属性を生成
     */
    private function generate_responsive_sizes() {
        if (wp_is_mobile()) {
            return '(max-width: 767px) 100vw, (max-width: 1023px) 50vw, 33vw';
        }
        
        return '(max-width: 768px) 100vw, (max-width: 1024px) 50vw, 33vw';
    }
    
    /**
     * 画像のプリロードヒントを追加
     */
    public function add_image_preload_hints() {
        if (!is_singular('post')) {
            return;
        }
        
        // アイキャッチ画像のプリロード
        if (has_post_thumbnail()) {
            $image_url = get_the_post_thumbnail_url(null, 'large');
            echo '<link rel="preload" as="image" href="' . esc_url($image_url) . '" fetchpriority="high">' . "\n";
        }
        
        // ヒーロー画像のプリロード
        $hero_image_id = get_theme_mod('blog_hero_image');
        if ($hero_image_id) {
            $hero_url = wp_get_attachment_image_url($hero_image_id, 'large');
            echo '<link rel="preload" as="image" href="' . esc_url($hero_url) . '" fetchpriority="high">' . "\n";
        }
    }
    
    /**
     * カスタム抜粋文字数（デバイス対応）
     */
    public function custom_excerpt_length($length) {
        if (is_admin()) {
            return $length;
        }
        
        // モバイルデバイスでは短く
        if (wp_is_mobile()) {
            return 20;
        }
        
        // アーカイブページでは少し長く
        if (is_archive()) {
            return 25;
        }
        
        return 30;
    }
    
    /**
     * カスタム抜粋の続きを読む
     */
    public function custom_excerpt_more($more) {
        if (is_feed()) {
            return $more;
        }
        
        return '... <a href="' . get_permalink() . '" class="read-more" aria-label="' . get_the_title() . 'の続きを読む">' . __('続きを読む', 'kei-portfolio') . '</a>';
    }
    
    /**
     * ブログクエリの最適化
     */
    public function optimize_blog_queries($query) {
        try {
            // 基本的な検証
            if (!$query || is_admin() || !$query->is_main_query()) {
                return;
            }
            
            if (is_home() || is_archive()) {
                // パフォーマンス最適化のクエリ設定
                $query->set('posts_per_page', 9);
                $query->set('no_found_rows', false); // ページネーションに必要
                
                // メタデータが必要ない場合のみ最適化
                if (!$this->needs_full_post_data()) {
                    $query->set('update_post_meta_cache', false);
                    $query->set('update_post_term_cache', false);
                }
                
                // テンプレートで必要なデータを保持（ids のみだと表示に必要なデータが取得できない）
                // $query->set('fields', 'ids'); // この行を削除してフルデータを保持
            }
            
            // 検索クエリの最適化
            if (is_search()) {
                $query->set('posts_per_page', 12);
                $query->set('no_found_rows', true);
                
                // 検索対象を投稿とページに制限
                $query->set('post_type', ['post', 'page']);
                
                // 古すぎる投稿は除外（パフォーマンス向上）
                $query->set('date_query', [
                    [
                        'after' => '2020-01-01',
                        'inclusive' => true,
                    ]
                ]);
            }
        } catch (Exception $e) {
            error_log('optimize_blog_queries: Exception: ' . $e->getMessage());
        } catch (Error $e) {
            error_log('optimize_blog_queries: Fatal error: ' . $e->getMessage());
        }
    }
    
    /**
     * リソースヒントを追加
     */
    public function add_resource_hints() {
        // DNS prefetch
        echo '<link rel="dns-prefetch" href="//fonts.googleapis.com">' . "\n";
        echo '<link rel="dns-prefetch" href="//cdnjs.cloudflare.com">' . "\n";
        
        // Preconnect to critical origins
        echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
        
        // Module preload for modern browsers
        if ($this->supports_es_modules()) {
            echo '<link rel="modulepreload" href="' . get_template_directory_uri() . '/assets/js/modules/blog.js">' . "\n";
        }
    }
    
    /**
     * ES modulesサポートを確認
     */
    private function supports_es_modules() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // モダンブラウザの検出（簡易版）
        return !preg_match('/(MSIE|Trident|Edge\/1[2-7])/', $user_agent);
    }
    
    /**
     * Speculation Rules APIを追加
     */
    public function add_speculation_rules() {
        if (is_home() || is_archive()) {
            ?>
            <script type="speculationrules">
            {
                "prerender": [
                    {
                        "source": "document",
                        "where": {"href_matches": "/blog/*"},
                        "eagerness": "moderate"
                    }
                ],
                "prefetch": [
                    {
                        "source": "document",
                        "where": {"href_matches": "/*"},
                        "eagerness": "conservative"
                    }
                ]
            }
            </script>
            <?php
        }
        
        if (is_singular('post')) {
            // 関連記事の先読み
            ?>
            <script type="speculationrules">
            {
                "prefetch": [
                    {
                        "source": "list",
                        "urls": [
                            <?php
                            $blog_data = \KeiPortfolio\Blog\Blog_Data::get_instance();
                            $related_posts = $blog_data->get_related_posts(get_the_ID(), 3);
                            
                            $urls = array_map(function($post) {
                                return '"' . get_permalink($post->ID) . '"';
                            }, $related_posts);
                            
                            echo implode(',', $urls);
                            ?>
                        ]
                    }
                ]
            }
            </script>
            <?php
        }
    }
    
    /**
     * ブログキャッシュのクリア
     */
    public function clear_blog_cache($new_status, $old_status, $post) {
        if ($post->post_type === 'post' && $new_status === 'publish') {
            // キャッシュクリア
            wp_cache_delete('recent_blog_posts', 'kei_portfolio');
            wp_cache_delete('blog_categories', 'kei_portfolio');
            wp_cache_delete('popular_posts_blog', 'kei_portfolio');
            
            // オブジェクトキャッシュクリア（Redis/Memcachedがある場合）
            if (function_exists('wp_cache_flush_group')) {
                wp_cache_flush_group('kei_portfolio_blog');
            }
        }
    }
    
    /**
     * パフォーマンス監視コードを追加
     */
    public function add_performance_monitoring() {
        if (!WP_DEBUG || is_admin()) {
            return;
        }
        ?>
        <script>
        // Core Web Vitals monitoring
        if ('web-vital' in window) {
            import('https://unpkg.com/web-vitals@3/dist/web-vitals.js').then(({getCLS, getFID, getFCP, getLCP, getTTFB}) => {
                getCLS(console.log);
                getFID(console.log);
                getFCP(console.log);
                getLCP(console.log);
                getTTFB(console.log);
            });
        }
        
        // Performance observer for long tasks
        if ('PerformanceObserver' in window) {
            const observer = new PerformanceObserver((list) => {
                for (const entry of list.getEntries()) {
                    if (entry.duration > 50) {
                        console.warn('Long task detected:', entry.duration, 'ms');
                    }
                }
            });
            observer.observe({entryTypes: ['longtask']});
        }
        </script>
        <?php
    }
    
    /**
     * フルデータが必要かどうかを判定
     * 
     * @return bool
     */
    private function needs_full_post_data() {
        // アーカイブページでタイトルや抜粋が必要な場合は true
        // 管理画面やREST APIの場合も true
        return is_admin() || 
               defined('REST_REQUEST') || 
               is_archive() || 
               is_home();
    }
    
    /**
     * スクリプト読み込みの最適化
     */
    public function optimize_script_loading() {
        // ブログページでのみ
        if (!(is_home() || is_single() || is_archive())) {
            return;
        }
        
        // 非クリティカルなスクリプトの遅延読み込み
        wp_enqueue_script(
            'kei-portfolio-blog-lazy',
            get_template_directory_uri() . '/assets/js/blog-lazy.js',
            [],
            wp_get_theme()->get('Version'),
            true
        );
        
        // Service Workerの登録
        wp_add_inline_script('kei-portfolio-blog-lazy', $this->get_service_worker_script());
    }
    
    /**
     * スクリプトタグにasync/defer属性を追加
     */
    public function add_async_defer_attributes($tag, $handle) {
        // 非クリティカルなスクリプト
        $async_scripts = [
            'kei-portfolio-blog-lazy',
            'kei-portfolio-analytics'
        ];
        
        $defer_scripts = [
            'kei-portfolio-blog',
            'kei-portfolio-social-share'
        ];
        
        if (in_array($handle, $async_scripts)) {
            return str_replace('<script ', '<script async ', $tag);
        }
        
        if (in_array($handle, $defer_scripts)) {
            return str_replace('<script ', '<script defer ', $tag);
        }
        
        return $tag;
    }
    
    /**
     * 次世代画像形式（WebP/AVIF）を生成
     */
    public function generate_next_gen_images($metadata, $attachment_id) {
        if (!isset($metadata['file'])) {
            return $metadata;
        }
        
        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['basedir'] . '/' . $metadata['file'];
        
        // 画像ファイルのみ処理
        if (!wp_attachment_is_image($attachment_id)) {
            return $metadata;
        }
        
        // WebP変換（サーバーが対応している場合）
        if (function_exists('imagewebp')) {
            $this->convert_to_webp($file_path);
        }
        
        // AVIF変換（PHP 8.1+でサポート）
        if (function_exists('imageavif')) {
            $this->convert_to_avif($file_path);
        }
        
        return $metadata;
    }
    
    /**
     * WebP形式に変換
     */
    private function convert_to_webp($file_path) {
        $webp_path = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $file_path);
        
        if (file_exists($webp_path)) {
            return; // 既に存在する
        }
        
        $image_info = getimagesize($file_path);
        
        switch ($image_info['mime']) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($file_path);
                break;
            case 'image/png':
                $image = imagecreatefrompng($file_path);
                // 透明度を保持
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
                break;
            default:
                return;
        }
        
        if ($image) {
            imagewebp($image, $webp_path, 85); // 品質85%
            imagedestroy($image);
        }
    }
    
    /**
     * AVIF形式に変換
     */
    private function convert_to_avif($file_path) {
        if (!function_exists('imageavif')) {
            return;
        }
        
        $avif_path = preg_replace('/\.(jpg|jpeg|png)$/i', '.avif', $file_path);
        
        if (file_exists($avif_path)) {
            return;
        }
        
        $image_info = getimagesize($file_path);
        
        switch ($image_info['mime']) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($file_path);
                break;
            case 'image/png':
                $image = imagecreatefrompng($file_path);
                break;
            default:
                return;
        }
        
        if ($image) {
            imageavif($image, $avif_path, 80); // 品質80%
            imagedestroy($image);
        }
    }
    
    /**
     * Service Workerを追加
     */
    public function add_service_worker() {
        ?>
        <script>
        if ('serviceWorker' in navigator && 'caches' in window) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js').then(function(registration) {
                    console.log('SW registered: ', registration);
                }).catch(function(registrationError) {
                    console.log('SW registration failed: ', registrationError);
                });
            });
        }
        </script>
        <?php
    }
    
    /**
     * Service Workerのスクリプトを取得
     */
    private function get_service_worker_script() {
        return "
        // Service Worker registration with error handling
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/blog-sw.js', {scope: '/blog/'})
                    .then(function(registration) {
                        console.log('Blog SW registered');
                    })
                    .catch(function(error) {
                        console.log('Blog SW registration failed');
                    });
            });
        }";
    }
    
    /**
     * クリティカルCSSをインライン化
     */
    public function inline_critical_css() {
        if (!(is_home() || is_single())) {
            return;
        }
        
        $critical_css_file = get_template_directory() . '/assets/css/critical.css';
        
        if (file_exists($critical_css_file)) {
            echo '<style id="critical-css">';
            include $critical_css_file;
            echo '</style>';
            
            // 非クリティカルCSSを遅延読み込み
            echo '<link rel="preload" href="' . get_template_directory_uri() . '/assets/css/blog.css" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">';
            echo '<noscript><link rel="stylesheet" href="' . get_template_directory_uri() . '/assets/css/blog.css"></noscript>';
        }
    }
    
    /**
     * データベースクエリの最適化
     */
    public function optimize_database_queries() {
        // よく使用されるクエリ結果をキャッシュ
        add_action('wp_loaded', function() {
            if (is_home() || is_archive()) {
                // カテゴリー統計をキャッシュ
                wp_cache_add('blog_categories_list', get_categories([
                    'orderby' => 'count',
                    'order' => 'DESC',
                    'hide_empty' => true,
                    'number' => 10
                ]), 'kei_portfolio', KEI_BLOG_CACHE_TTL);
            }
        });
        
        // 不要なクエリを削減
        remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
        
        // 絵文字スクリプトを無効化（必要に応じて）
        if (!get_theme_mod('enable_emoji_support', false)) {
            remove_action('wp_head', 'print_emoji_detection_script', 7);
            remove_action('wp_print_styles', 'print_emoji_styles');
        }
    }
    
    /**
     * メモリ使用量の最適化
     */
    public function cleanup_memory() {
        if (!WP_DEBUG) {
            return;
        }
        
        // メモリ使用量をログ出力（デバッグ用）
        $memory_usage = memory_get_usage(true);
        $memory_peak = memory_get_peak_usage(true);
        
        error_log(sprintf(
            'Blog page memory usage: %s / Peak: %s',
            size_format($memory_usage),
            size_format($memory_peak)
        ));
        
        // 大きなオブジェクトをクリーンアップ
        global $wp_object_cache;
        if (method_exists($wp_object_cache, 'close')) {
            $wp_object_cache->close();
        }
    }
    
    /**
     * 画像の最適なフォーマットを検出
     */
    public function get_optimal_image_format($attachment_id) {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // AVIF対応ブラウザ
        if (strpos($user_agent, 'Chrome') !== false && 
            preg_match('/Chrome\/([0-9]+)/', $user_agent, $matches) && 
            intval($matches[1]) >= 85) {
            return 'avif';
        }
        
        // WebP対応ブラウザ
        if (strpos($user_agent, 'Chrome') !== false || 
            strpos($user_agent, 'Firefox') !== false ||
            strpos($user_agent, 'Safari') !== false) {
            return 'webp';
        }
        
        return 'original';
    }
    
    /**
     * 投稿保存時に読了時間を事前計算
     * 
     * @param int $post_id 投稿ID
     * @param \WP_Post $post 投稿オブジェクト
     */
    public function calculate_reading_time_on_save($post_id, $post = null) {
        // 自動保存やリビジョンは除外
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }
        
        // 投稿タイプがpostの場合のみ処理
        if (get_post_type($post_id) !== 'post') {
            return;
        }
        
        // 公開状態でない場合は処理しない
        if (get_post_status($post_id) !== 'publish') {
            return;
        }
        
        // 投稿オブジェクトを取得（渡されていない場合）
        if (!$post) {
            $post = get_post($post_id);
        }
        
        if (!$post) {
            return;
        }
        
        // コンテンツから読了時間を計算
        $reading_time = $this->calculate_reading_time($post->post_content);
        
        // メタデータとして保存
        update_post_meta($post_id, '_reading_time', $reading_time);
        
        // デバッグログ
        if (WP_DEBUG) {
            error_log("Reading time calculated for post {$post_id}: {$reading_time} minutes");
        }
    }
    
    /**
     * コンテンツから読了時間を計算
     * 
     * @param string $content 投稿コンテンツ
     * @return int 読了時間（分）
     */
    private function calculate_reading_time($content) {
        // HTMLタグを削除してプレーンテキストに変換
        $plain_content = wp_strip_all_tags($content);
        
        // 改行や余分なスペースを整理
        $plain_content = preg_replace('/\s+/', ' ', trim($plain_content));
        
        // 文字数ベースの計算（日本語対応）
        $char_count = mb_strlen($plain_content, 'UTF-8');
        
        // 日本語の場合：400文字/分（一般的な読書速度）
        // 英語の場合：200単語/分
        $japanese_chars = mb_strlen(preg_replace('/[a-zA-Z0-9\s\p{P}]/u', '', $plain_content), 'UTF-8');
        $english_words = str_word_count(preg_replace('/[^\x00-\x7F]/', '', $plain_content));
        
        // 読了時間計算
        $japanese_reading_time = $japanese_chars > 0 ? ceil($japanese_chars / 400) : 0;
        $english_reading_time = $english_words > 0 ? ceil($english_words / 200) : 0;
        
        // より長い方を採用し、最低1分に設定
        $reading_time = max(1, max($japanese_reading_time, $english_reading_time));
        
        return $reading_time;
    }
    
    /**
     * 読了時間を取得（キャッシュ付き）
     * 
     * @param int $post_id 投稿ID
     * @return int 読了時間（分）
     */
    public function get_reading_time($post_id) {
        $reading_time = get_post_meta($post_id, '_reading_time', true);
        
        // メタデータが存在しない場合は即座に計算
        if (empty($reading_time)) {
            $post = get_post($post_id);
            if ($post && $post->post_type === 'post') {
                $reading_time = $this->calculate_reading_time($post->post_content);
                update_post_meta($post_id, '_reading_time', $reading_time);
            } else {
                $reading_time = 1; // フォールバック
            }
        }
        
        return intval($reading_time);
    }
    
    /**
     * 全ての既存投稿の読了時間を一括計算（WP-CLIまたは管理画面用）
     */
    public function batch_calculate_reading_times() {
        if (!current_user_can('manage_options')) {
            return false;
        }
        
        $posts = get_posts([
            'post_type' => 'post',
            'post_status' => 'publish',
            'numberposts' => -1,
            'fields' => 'ids'
        ]);
        
        $updated_count = 0;
        
        foreach ($posts as $post_id) {
            $existing_time = get_post_meta($post_id, '_reading_time', true);
            
            // 既に計算済みの場合はスキップ
            if (!empty($existing_time)) {
                continue;
            }
            
            $post = get_post($post_id);
            if ($post) {
                $reading_time = $this->calculate_reading_time($post->post_content);
                update_post_meta($post_id, '_reading_time', $reading_time);
                $updated_count++;
            }
        }
        
        if (WP_DEBUG) {
            error_log("Batch reading time calculation completed: {$updated_count} posts updated");
        }
        
        return $updated_count;
    }
    
    /**
     * 最適化統計を取得（デバッグ用）
     */
    public function get_optimization_stats() {
        if (!WP_DEBUG) {
            return null;
        }
        
        return [
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'query_count' => get_num_queries(),
            'cache_hits' => wp_cache_get_stats(),
            'page_load_time' => timer_stop()
        ];
    }
}

// インスタンス化
new Blog_Optimizations();
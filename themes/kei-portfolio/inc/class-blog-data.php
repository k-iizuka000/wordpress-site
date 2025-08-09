<?php
/**
 * ブログデータ管理クラス
 * 
 * Portfolio_Dataクラスの設計パターンを踏襲してブログ機能のデータ管理を行う
 * キャッシュ機能、データ取得、パフォーマンス最適化を提供
 *
 * @package Kei_Portfolio
 * @since 1.0.0
 */

namespace KeiPortfolio\Blog;

/**
 * Blog_Data クラス
 * 
 * ブログ関連のデータ取得とキャッシング機能を提供
 */
class Blog_Data {
    
    /**
     * キャッシュ設定定数
     */
    private const CACHE_EXPIRATION_DEFAULT = 3600; // 1時間
    private const CACHE_EXPIRATION_SHORT = 600;    // 10分
    private const CACHE_EXPIRATION_LONG = 86400;   // 24時間
    
    /**
     * シングルトンインスタンス
     * 
     * @var Blog_Data|null
     */
    private static $instance = null;
    
    /**
     * キャッシュグループ名
     * 
     * @var string
     */
    private $cache_group = 'kei_portfolio_blog';
    
    /**
     * キャッシュ有効期限（秒）
     * 
     * @var int
     */
    private $cache_expiration = self::CACHE_EXPIRATION_DEFAULT;
    
    /**
     * デバッグモード
     * 
     * @var bool
     */
    private $debug_mode;
    
    /**
     * シングルトンパターンでインスタンス取得
     * 
     * @return Blog_Data
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * コンストラクタ（プライベート）
     */
    private function __construct() {
        $this->debug_mode = defined('WP_DEBUG') && WP_DEBUG;
        $this->init_hooks();
    }
    
    /**
     * フックの初期化
     */
    private function init_hooks() {
        // キャッシュクリアフック
        add_action('save_post_post', [$this, 'clear_cache_on_post_change']);
        add_action('deleted_post', [$this, 'clear_cache_on_post_change']);
        add_action('transition_post_status', [$this, 'clear_cache_on_status_change'], 10, 3);
        add_action('switch_theme', [$this, 'clear_cache']);
        
        // カテゴリー・タグ変更時のキャッシュクリア
        add_action('created_term', [$this, 'clear_cache_on_term_change']);
        add_action('edited_term', [$this, 'clear_cache_on_term_change']);
        add_action('deleted_term', [$this, 'clear_cache_on_term_change']);
        
        // 投稿ビューカウント
        add_action('wp_head', [$this, 'maybe_count_post_views']);
    }
    
    /**
     * 最新の投稿を取得（キャッシュ付き）
     * 
     * @param array $args 取得条件
     * @return array 投稿配列
     */
    public function get_recent_posts($args = []) {
        $defaults = [
            'count' => 5,
            'category' => '',
            'exclude_sticky' => false,
            'meta_query' => [],
            'date_query' => []
        ];
        
        $args = wp_parse_args($args, $defaults);
        $cache_key = 'recent_posts_' . md5(serialize($args));
        
        $posts = wp_cache_get($cache_key, $this->cache_group);
        
        if (false === $posts || $this->debug_mode) {
            $query_args = [
                'post_type' => 'post',
                'posts_per_page' => $args['count'],
                'post_status' => 'publish',
                'orderby' => 'date',
                'order' => 'DESC',
                'no_found_rows' => true,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false
            ];
            
            if (!empty($args['category'])) {
                if (is_numeric($args['category'])) {
                    $query_args['cat'] = $args['category'];
                } else {
                    $query_args['category_name'] = $args['category'];
                }
            }
            
            if ($args['exclude_sticky']) {
                $query_args['ignore_sticky_posts'] = true;
            }
            
            if (!empty($args['meta_query'])) {
                $query_args['meta_query'] = $args['meta_query'];
            }
            
            if (!empty($args['date_query'])) {
                $query_args['date_query'] = $args['date_query'];
            }
            
            $query = new \WP_Query($query_args);
            $posts = $query->posts;
            
            // キャッシュに保存
            if (!$this->debug_mode) {
                wp_cache_set($cache_key, $posts, $this->cache_group, $this->cache_expiration);
                
                // デバッグログ
                if ($this->debug_mode) {
                    $this->debug_log("Recent posts cached", ['cache_key' => $cache_key]);
                }
            }
            
            wp_reset_postdata();
        }
        
        return $posts;
    }
    
    /**
     * 人気の投稿を取得（ビュー数ベース）
     * 
     * @param int $count 取得件数
     * @param array $args 追加条件
     * @return array 投稿配列
     */
    public function get_popular_posts($count = 5, $args = []) {
        $cache_key = 'popular_posts_' . $count . '_' . md5(serialize($args));
        $posts = wp_cache_get($cache_key, $this->cache_group);
        
        if (false === $posts || $this->debug_mode) {
            $query_args = array_merge([
                'post_type' => 'post',
                'posts_per_page' => $count,
                'post_status' => 'publish',
                'meta_key' => 'post_views_count',
                'orderby' => 'meta_value_num',
                'order' => 'DESC',
                'no_found_rows' => true,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false
            ], $args);
            
            $query = new \WP_Query($query_args);
            $posts = $query->posts;
            
            // ビューカウントが0または存在しない投稿は除外
            $posts = array_filter($posts, function($post) {
                $views = get_post_meta($post->ID, 'post_views_count', true);
                return $views && $views > 0;
            });
            
            // 人気記事が少ない場合は最新記事で補完
            if (count($posts) < $count) {
                $recent_posts = $this->get_recent_posts([
                    'count' => $count - count($posts),
                    'post__not_in' => wp_list_pluck($posts, 'ID')
                ]);
                $posts = array_merge($posts, $recent_posts);
            }
            
            if (!$this->debug_mode) {
                wp_cache_set($cache_key, $posts, $this->cache_group, $this->cache_expiration);
            }
            
            wp_reset_postdata();
        }
        
        return $posts;
    }
    
    /**
     * 関連投稿を取得
     * 
     * @param int $post_id 基準となる投稿ID
     * @param int $count 取得件数
     * @return array 投稿配列
     */
    public function get_related_posts($post_id, $count = 3) {
        $cache_key = 'related_posts_' . $post_id . '_' . $count;
        $posts = wp_cache_get($cache_key, $this->cache_group);
        
        if (false === $posts || $this->debug_mode) {
            $categories = wp_get_post_categories($post_id);
            $tags = wp_get_post_tags($post_id, ['fields' => 'ids']);
            
            $query_args = [
                'post_type' => 'post',
                'posts_per_page' => $count * 2, // 多めに取得してランダム性を確保
                'post__not_in' => [$post_id],
                'post_status' => 'publish',
                'orderby' => 'rand',
                'no_found_rows' => true,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false
            ];
            
            // カテゴリーまたはタグが一致する投稿を取得
            if (!empty($categories) || !empty($tags)) {
                $tax_query = ['relation' => 'OR'];
                
                if (!empty($categories)) {
                    $tax_query[] = [
                        'taxonomy' => 'category',
                        'field'    => 'term_id',
                        'terms'    => $categories,
                    ];
                }
                
                if (!empty($tags)) {
                    $tax_query[] = [
                        'taxonomy' => 'post_tag',
                        'field'    => 'term_id',
                        'terms'    => $tags,
                    ];
                }
                
                $query_args['tax_query'] = $tax_query;
            }
            
            $query = new \WP_Query($query_args);
            $related_posts = $query->posts;
            
            // 指定件数に調整
            $posts = array_slice($related_posts, 0, $count);
            
            // 関連記事が足りない場合は最新記事で補完
            if (count($posts) < $count) {
                $additional_posts = $this->get_recent_posts([
                    'count' => $count - count($posts),
                    'post__not_in' => array_merge([$post_id], wp_list_pluck($posts, 'ID'))
                ]);
                $posts = array_merge($posts, $additional_posts);
            }
            
            if (!$this->debug_mode) {
                wp_cache_set($cache_key, $posts, $this->cache_group, $this->cache_expiration);
            }
            
            wp_reset_postdata();
        }
        
        return $posts;
    }
    
    /**
     * カテゴリー統計を取得
     * 
     * @return array カテゴリー統計配列
     */
    public function get_category_stats() {
        $cache_key = 'category_stats';
        $stats = wp_cache_get($cache_key, $this->cache_group);
        
        if (false === $stats || $this->debug_mode) {
            $categories = get_categories([
                'orderby' => 'count',
                'order' => 'DESC',
                'hide_empty' => true,
                'number' => 50 // 最大50カテゴリーまで取得
            ]);
            
            $stats = [];
            foreach ($categories as $category) {
                $stats[] = [
                    'id' => $category->term_id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'count' => $category->count,
                    'url' => get_category_link($category->term_id),
                    'description' => $category->description,
                    'parent' => $category->parent
                ];
            }
            
            if (!$this->debug_mode) {
                wp_cache_set($cache_key, $stats, $this->cache_group, $this->cache_expiration);
            }
        }
        
        return $stats;
    }
    
    /**
     * タグ統計を取得
     * 
     * @return array タグ統計配列
     */
    public function get_tag_stats() {
        $cache_key = 'tag_stats';
        $stats = wp_cache_get($cache_key, $this->cache_group);
        
        if (false === $stats || $this->debug_mode) {
            $tags = get_tags([
                'orderby' => 'count',
                'order' => 'DESC',
                'hide_empty' => true,
                'number' => 50
            ]);
            
            $stats = [];
            foreach ($tags as $tag) {
                $stats[] = [
                    'id' => $tag->term_id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                    'count' => $tag->count,
                    'url' => get_tag_link($tag->term_id),
                    'description' => $tag->description
                ];
            }
            
            if (!$this->debug_mode) {
                wp_cache_set($cache_key, $stats, $this->cache_group, $this->cache_expiration);
            }
        }
        
        return $stats;
    }
    
    /**
     * 月別アーカイブ統計を取得
     * 
     * @param int $limit 取得件数上限
     * @return array 月別統計配列
     */
    public function get_monthly_archives($limit = 12) {
        $cache_key = 'monthly_archives_' . $limit;
        $archives = wp_cache_get($cache_key, $this->cache_group);
        
        if (false === $archives || $this->debug_mode) {
            global $wpdb;
            
            // 安全なクエリ構築 - LIMITのみにプレースホルダーを使用
            $limit_safe = intval($limit);
            $query = "
                SELECT YEAR(post_date) as year, 
                       MONTH(post_date) as month, 
                       COUNT(*) as post_count
                FROM {$wpdb->posts} 
                WHERE post_status = 'publish' 
                AND post_type = 'post'
                GROUP BY YEAR(post_date), MONTH(post_date) 
                ORDER BY post_date DESC 
                LIMIT {$limit_safe}
            ";
            
            $results = $wpdb->get_results($query);
            
            $archives = [];
            foreach ($results as $result) {
                $archives[] = [
                    'year' => $result->year,
                    'month' => $result->month,
                    'count' => $result->post_count,
                    'url' => get_month_link($result->year, $result->month),
                    'label' => date_i18n('Y年n月', mktime(0, 0, 0, $result->month, 1, $result->year))
                ];
            }
            
            if (!$this->debug_mode) {
                wp_cache_set($cache_key, $archives, $this->cache_group, $this->cache_expiration);
            }
        }
        
        return $archives;
    }
    
    /**
     * 投稿統計を取得
     * 
     * @return array 統計データ配列
     */
    public function get_post_stats() {
        $cache_key = 'post_stats';
        $stats = wp_cache_get($cache_key, $this->cache_group);
        
        if (false === $stats || $this->debug_mode) {
            $posts_count = wp_count_posts('post');
            
            $stats = [
                'total_posts' => $posts_count->publish,
                'total_categories' => wp_count_terms(['taxonomy' => 'category', 'hide_empty' => true]),
                'total_tags' => wp_count_terms(['taxonomy' => 'post_tag', 'hide_empty' => true]),
                'first_post_date' => $this->get_first_post_date(),
                'last_post_date' => $this->get_last_post_date(),
                'total_views' => $this->get_total_post_views()
            ];
            
            if (!$this->debug_mode) {
                wp_cache_set($cache_key, $stats, $this->cache_group, $this->cache_expiration);
            }
        }
        
        return $stats;
    }
    
    /**
     * 最初の投稿日を取得
     * 
     * @return string|null
     */
    private function get_first_post_date() {
        global $wpdb;
        
        try {
            $date = $wpdb->get_var($wpdb->prepare("
                SELECT post_date 
                FROM {$wpdb->posts} 
                WHERE post_status = %s 
                AND post_type = %s 
                ORDER BY post_date ASC 
                LIMIT 1
            ", 'publish', 'post'));
            
            // データベースエラーをチェック
            if ($wpdb->last_error) {
                $this->debug_log('Database error in get_first_post_date', ['error' => $wpdb->last_error]);
                return null;
            }
            
            return $date;
        } catch (Exception $e) {
            $this->debug_log('Exception in get_first_post_date', ['message' => $e->getMessage()]);
            return null;
        }
    }
    
    /**
     * 最新の投稿日を取得
     * 
     * @return string|null
     */
    private function get_last_post_date() {
        global $wpdb;
        
        try {
            $date = $wpdb->get_var($wpdb->prepare("
                SELECT post_date 
                FROM {$wpdb->posts} 
                WHERE post_status = %s 
                AND post_type = %s 
                ORDER BY post_date DESC 
                LIMIT 1
            ", 'publish', 'post'));
            
            // データベースエラーをチェック
            if ($wpdb->last_error) {
                $this->debug_log('Database error in get_last_post_date', ['error' => $wpdb->last_error]);
                return null;
            }
            
            return $date;
        } catch (Exception $e) {
            $this->debug_log('Exception in get_last_post_date', ['message' => $e->getMessage()]);
            return null;
        }
    }
    
    /**
     * 総閲覧数を取得
     * 
     * @return int
     */
    private function get_total_post_views() {
        global $wpdb;
        
        try {
            $total = $wpdb->get_var($wpdb->prepare("
                SELECT SUM(CAST(pm.meta_value AS UNSIGNED)) 
                FROM {$wpdb->postmeta} pm
                INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                WHERE pm.meta_key = %s 
                AND p.post_status = %s
                AND p.post_type = %s
            ", 'post_views_count', 'publish', 'post'));
            
            // データベースエラーをチェック
            if ($wpdb->last_error) {
                $this->debug_log('Database error in get_total_post_views', ['error' => $wpdb->last_error]);
                return 0;
            }
            
            return $total ? intval($total) : 0;
        } catch (Exception $e) {
            $this->debug_log('Exception in get_total_post_views', ['message' => $e->getMessage()]);
            return 0;
        }
    }
    
    /**
     * 投稿ビュー数をカウント（シングルページのみ）
     */
    public function maybe_count_post_views() {
        if (is_single() && get_post_type() === 'post') {
            $this->count_post_views(get_the_ID());
        }
    }
    
    /**
     * 投稿ビュー数をカウント（強化版）
     * 
     * @param int $post_id 投稿ID
     */
    public function count_post_views($post_id) {
        // ボットや管理者は除外
        if ($this->is_bot() || current_user_can('edit_posts')) {
            return;
        }
        
        // 同一セッション内での重複カウントを防ぐ
        if ($this->is_duplicate_view($post_id)) {
            return;
        }
        
        $count_key = 'post_views_count';
        $cache_key = 'post_view_' . $post_id;
        
        // キャッシュから現在のカウントを取得
        $cached_count = wp_cache_get($cache_key, $this->cache_group);
        
        if ($cached_count === false) {
            // キャッシュがない場合はDBから取得
            $count = get_post_meta($post_id, $count_key, true);
            $count = $count ? intval($count) : 0;
        } else {
            $count = intval($cached_count);
        }
        
        $new_count = $count + 1;
        
        // キャッシュを更新（短期間）
        wp_cache_set($cache_key, $new_count, $this->cache_group, 300); // 5分
        
        // 10カウント毎にDBを更新（書き込み頻度を削減）
        if ($new_count % 10 === 0 || $count === 0) {
            update_post_meta($post_id, $count_key, $new_count);
            
            // 人気記事キャッシュをクリア
            $this->clear_popular_posts_cache();
            
            if ($this->debug_mode) {
                $this->debug_log('View count updated in database', ['post_id' => $post_id, 'count' => $new_count]);
            }
        } else {
            // バックグラウンドで後ほどDBを更新するためのフラグ
            $pending_key = 'pending_view_update_' . $post_id;
            wp_cache_set($pending_key, $new_count, $this->cache_group, self::CACHE_EXPIRATION_DEFAULT);
        }
        
        // セッションに記録（重複防止用）
        $this->mark_view_session($post_id);
        
        if ($this->debug_mode) {
            $this->debug_log('View count updated (cached)', ['post_id' => $post_id, 'count' => $new_count]);
        }
    }
    
    /**
     * 重複ビューをチェック
     * 
     * @param int $post_id 投稿ID
     * @return bool
     */
    private function is_duplicate_view($post_id) {
        if (!session_id()) {
            session_start();
        }
        
        $session_key = 'viewed_posts';
        $viewed_posts = $_SESSION[$session_key] ?? [];
        
        // 30分以内の閲覧は重複とみなす
        $timeout = 30 * 60; // 30分
        $now = time();
        
        foreach ($viewed_posts as $viewed_post_id => $timestamp) {
            if ($now - $timestamp > $timeout) {
                unset($viewed_posts[$viewed_post_id]);
            }
        }
        
        if (isset($viewed_posts[$post_id])) {
            return true; // 重複
        }
        
        return false;
    }
    
    /**
     * セッションにビューを記録
     * 
     * @param int $post_id 投稿ID
     */
    private function mark_view_session($post_id) {
        if (!session_id()) {
            session_start();
        }
        
        $session_key = 'viewed_posts';
        if (!isset($_SESSION[$session_key])) {
            $_SESSION[$session_key] = [];
        }
        
        $_SESSION[$session_key][$post_id] = time();
        
        // セッション配列が大きくなりすぎないよう制限
        if (count($_SESSION[$session_key]) > 100) {
            // 古いものから削除
            asort($_SESSION[$session_key]);
            $_SESSION[$session_key] = array_slice($_SESSION[$session_key], -50, null, true);
        }
    }
    
    /**
     * 人気記事キャッシュをクリア
     */
    private function clear_popular_posts_cache() {
        $cache_keys = [
            'popular_posts_3_' . md5('a:0:{}'),
            'popular_posts_5_' . md5('a:0:{}'),
            'popular_posts_10_' . md5('a:0:{}'),
        ];
        
        foreach ($cache_keys as $key) {
            wp_cache_delete($key, $this->cache_group);
        }
    }
    
    /**
     * ビューカウントの実際の値を取得（キャッシュと DB の両方を考慮）
     * 
     * @param int $post_id 投稿ID
     * @return int ビュー数
     */
    public function get_actual_view_count($post_id) {
        $cache_key = 'post_view_' . $post_id;
        $cached_count = wp_cache_get($cache_key, $this->cache_group);
        
        if ($cached_count !== false) {
            return intval($cached_count);
        }
        
        // キャッシュにない場合はDBから取得
        $count = get_post_meta($post_id, 'post_views_count', true);
        return $count ? intval($count) : 0;
    }
    
    /**
     * ペンディング中のビューカウント更新を処理
     */
    public function process_pending_view_updates() {
        // CronやWP-CLIで実行される想定
        global $wpdb;
        
        // ペンディング中の更新を取得
        $cache_keys = wp_cache_get('pending_view_keys', $this->cache_group) ?: [];
        
        if (empty($cache_keys)) {
            return 0;
        }
        
        $updated_count = 0;
        
        foreach ($cache_keys as $post_id) {
            $pending_key = 'pending_view_update_' . $post_id;
            $pending_count = wp_cache_get($pending_key, $this->cache_group);
            
            if ($pending_count !== false) {
                update_post_meta($post_id, 'post_views_count', intval($pending_count));
                wp_cache_delete($pending_key, $this->cache_group);
                $updated_count++;
            }
        }
        
        // ペンディングキーリストをクリア
        wp_cache_delete('pending_view_keys', $this->cache_group);
        
        if ($this->debug_mode && $updated_count > 0) {
            $this->debug_log('Processed pending view count updates', ['count' => $updated_count]);
        }
        
        return $updated_count;
    }
    
    /**
     * ボット判定
     * 
     * @return bool
     */
    private function is_bot() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $bots = [
            'googlebot', 'bingbot', 'slurp', 'duckduckbot',
            'baiduspider', 'yandexbot', 'facebookexternalhit',
            'twitterbot', 'rogerbot', 'linkedinbot', 'whatsapp',
            'crawler', 'spider', 'bot', 'archiver', 'wget', 'curl'
        ];
        
        $user_agent_lower = strtolower($user_agent);
        
        foreach ($bots as $bot) {
            if (stripos($user_agent_lower, $bot) !== false) {
                return true;
            }
        }
        
        // IPアドレスベースの検出（主要な検索エンジンのIP範囲）
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        if ($this->is_search_engine_ip($ip)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * 検索エンジンのIPかどうかを判定
     * 
     * @param string $ip IPアドレス
     * @return bool
     */
    private function is_search_engine_ip($ip) {
        // Google, Bing などの既知のIPレンジをチェック
        // 簡易実装（本格的にはより詳細なIP範囲チェックが必要）
        $search_engine_patterns = [
            '/^66\.249\./', // Google
            '/^207\.46\./', // Bing
            '/^40\.77\./',  // Bing
        ];
        
        foreach ($search_engine_patterns as $pattern) {
            if (preg_match($pattern, $ip)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * キャッシュをクリア
     * 
     * @param array $keys 特定のキーのみクリアする場合
     */
    public function clear_cache($keys = []) {
        if (empty($keys)) {
            // すべてのキャッシュをクリア
            $all_keys = [
                'recent_posts', 'popular_posts', 'related_posts', 
                'category_stats', 'tag_stats', 'monthly_archives', 'post_stats'
            ];
            
            foreach ($all_keys as $key) {
                wp_cache_delete($key, $this->cache_group);
            }
            
            // パターンマッチングで削除（可能な場合）
            if (function_exists('wp_cache_flush_group')) {
                wp_cache_flush_group($this->cache_group);
            }
        } else {
            foreach ($keys as $key) {
                wp_cache_delete($key, $this->cache_group);
            }
        }
        
        // 既存のPortfolio_Dataキャッシュもクリア（相互参照がある場合）
        if (class_exists('Portfolio_Data')) {
            $portfolio_data = \Portfolio_Data::get_instance();
            if (method_exists($portfolio_data, 'clear_cache')) {
                $portfolio_data->clear_cache();
            }
        }
        
        if ($this->debug_mode) {
            $this->debug_log('Cache cleared', ['keys' => $keys ?: $all_keys]);
        }
    }
    
    /**
     * 投稿変更時のキャッシュクリア
     * 
     * @param int $post_id 投稿ID
     */
    public function clear_cache_on_post_change($post_id) {
        if (get_post_type($post_id) === 'post') {
            $this->clear_cache();
        }
    }
    
    /**
     * 投稿ステータス変更時のキャッシュクリア
     * 
     * @param string $new_status 新しいステータス
     * @param string $old_status 古いステータス
     * @param \WP_Post $post 投稿オブジェクト
     */
    public function clear_cache_on_status_change($new_status, $old_status, $post) {
        if ($post->post_type === 'post' && ($new_status === 'publish' || $old_status === 'publish')) {
            $this->clear_cache();
        }
    }
    
    /**
     * タームの変更時のキャッシュクリア
     * 
     * @param int $term_id タームID
     */
    public function clear_cache_on_term_change($term_id) {
        $term = get_term($term_id);
        if ($term && in_array($term->taxonomy, ['category', 'post_tag'])) {
            $this->clear_cache(['category_stats', 'tag_stats']);
        }
    }
    
    /**
     * キャッシュ統計を取得（デバッグ用）
     * 
     * @return array
     */
    public function get_cache_stats() {
        if (!$this->debug_mode) {
            return ['error' => 'Debug mode is not enabled'];
        }
        
        $stats = [
            'cache_group' => $this->cache_group,
            'cache_expiration' => $this->cache_expiration,
            'cached_keys' => []
        ];
        
        // 主要なキャッシュキーの存在確認
        $test_keys = [
            'recent_posts_' . md5(serialize(['count' => 5])),
            'popular_posts_5_' . md5('a:0:{}'),
            'category_stats',
            'tag_stats',
            'post_stats'
        ];
        
        foreach ($test_keys as $key) {
            $cached_data = wp_cache_get($key, $this->cache_group);
            $stats['cached_keys'][$key] = $cached_data !== false;
        }
        
        return $stats;
    }
    
    /**
     * セキュアなデバッグログ出力
     * 
     * @param string $message ログメッセージ
     * @param array $context 追加のコンテキスト情報
     */
    private function debug_log($message, $context = []) {
        if (!$this->debug_mode) {
            return;
        }
        
        if (WP_DEBUG && WP_DEBUG_LOG) {
            // 本番環境でない場合のみ詳細な情報をログに出力
            $log_data = [
                'class' => __CLASS__,
                'message' => $message,
                'context' => $context
            ];
            error_log('Blog_Data: ' . json_encode($log_data, JSON_UNESCAPED_UNICODE));
        } else {
            // 本番環境では最小限の情報のみ
            error_log('Blog_Data: ' . $message . ' in ' . __CLASS__);
        }
    }
}

// インスタンス化（functions.phpから読み込まれる場合）
if (!function_exists('get_blog_data_instance')) {
    function get_blog_data_instance() {
        return \KeiPortfolio\Blog\Blog_Data::get_instance();
    }
}

// ビューカウント用フックの設定（functions.phpで呼び出される）
$blog_data = \KeiPortfolio\Blog\Blog_Data::get_instance();
<?php
/**
 * 最適化されたブログデータクラス
 * 
 * N+1問題の解決、バッチ処理、効率的なSQLクエリによる
 * パフォーマンス最適化されたブログデータ管理
 *
 * @package Kei_Portfolio
 * @since 1.1.0
 */

namespace KeiPortfolio\Blog;

/**
 * OptimizedBlogData クラス
 * 
 * パフォーマンスを重視したブログデータ取得とキャッシング機能を提供
 */
class OptimizedBlogData {
    
    /**
     * キャッシュ設定定数
     */
    private const CACHE_EXPIRATION_DEFAULT = 3600; // 1時間
    private const CACHE_EXPIRATION_SHORT = 600;    // 10分
    private const CACHE_EXPIRATION_LONG = 86400;   // 24時間
    
    /**
     * シングルトンインスタンス
     * 
     * @var OptimizedBlogData|null
     */
    private static $instance = null;
    
    /**
     * キャッシュグループ名（バージョン2）
     * 
     * @var string
     */
    private $cache_group = 'kei_portfolio_blog_v2';
    
    /**
     * キャッシュ有効期限（秒）
     * 
     * @var int
     */
    private $cache_expiration = self::CACHE_EXPIRATION_DEFAULT;
    
    /**
     * シングルトンパターンでインスタンス取得
     * 
     * @return OptimizedBlogData
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
    }
    
    /**
     * バッチ取得によるN+1問題の解決
     * 
     * @param array $post_ids 投稿IDの配列
     * @return array 投稿IDをキーとしたメタデータ配列
     */
    public function get_posts_with_metadata($post_ids) {
        global $wpdb;
        
        if (empty($post_ids)) {
            return [];
        }
        
        // 入力値の検証
        $post_ids = array_filter(array_map('intval', $post_ids));
        if (empty($post_ids)) {
            return [];
        }
        
        // キャッシュチェック
        $cache_key = 'posts_meta_' . md5(serialize($post_ids));
        $cached = wp_cache_get($cache_key, $this->cache_group);
        
        if (false !== $cached) {
            return $cached;
        }
        
        try {
            // 安全なクエリ構築 - プレースホルダーを正しく使用
            // SQLインジェクション対策：安全なプレースホルダー実装
            $post_ids_placeholders = implode(',', array_fill(0, count($post_ids), '%d'));
            $meta_keys = ['post_views_count', '_thumbnail_id', 'reading_time'];
            $meta_keys_placeholders = "'" . implode("','", array_map('esc_sql', $meta_keys)) . "'";

            $query = $wpdb->prepare(
                "SELECT post_id, meta_key, meta_value 
                 FROM {$wpdb->postmeta} 
                 WHERE post_id IN ($post_ids_placeholders)
                 AND meta_key IN ($meta_keys_placeholders)",
                $post_ids
            );
            
            $metadata = $wpdb->get_results($query);
            
            // データベースエラーをチェック
            if ($wpdb->last_error) {
                error_log('OptimizedBlogData get_posts_with_metadata: Database error: ' . $wpdb->last_error);
                return [];
            }
            
            // メタデータを投稿IDごとに整理
            $organized_data = [];
            foreach ($metadata as $meta) {
                if (!isset($organized_data[$meta->post_id])) {
                    $organized_data[$meta->post_id] = [];
                }
                $organized_data[$meta->post_id][$meta->meta_key] = $meta->meta_value;
            }
            
            // キャッシュ保存
            wp_cache_set($cache_key, $organized_data, $this->cache_group, $this->cache_expiration);
            
            return $organized_data;
            
        } catch (Exception $e) {
            error_log('OptimizedBlogData get_posts_with_metadata: Exception: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * 最適化されたアーカイブデータ取得
     * 
     * @param int $year 年
     * @param int|null $month 月（nullの場合は年全体）
     * @return array 投稿データ配列
     */
    public function get_archive_data($year, $month = null) {
        global $wpdb;
        
        // 入力値の検証
        $year = intval($year);
        if ($year < 1900 || $year > 2100) {
            return [];
        }
        
        if ($month !== null) {
            $month = intval($month);
            if ($month < 1 || $month > 12) {
                return [];
            }
        }
        
        $cache_key = sprintf('archive_%s_%s', $year, $month ?: 'all');
        $cached = wp_cache_get($cache_key, $this->cache_group);
        
        if (false !== $cached) {
            return $cached;
        }
        
        try {
            // インデックス活用のための日付範囲指定
            if ($month) {
                // 指定月の開始日と終了日を計算
                $start_date = sprintf('%04d-%02d-01', $year, $month);
                $end_date = date('Y-m-t', strtotime($start_date)); // 月末日を取得
            } else {
                // 年全体の場合
                $start_date = sprintf('%04d-01-01', $year);
                $end_date = sprintf('%04d-12-31', $year);
            }
            
            // インデックスを活用できるクエリ構築
            $where_conditions = [
                $wpdb->prepare("post_type = %s", 'post'),
                $wpdb->prepare("post_status = %s", 'publish'),
                $wpdb->prepare("post_date >= %s", $start_date),
                $wpdb->prepare("post_date <= %s", $end_date . ' 23:59:59')
            ];
            
            $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
            
            // サブクエリを使用してビュー数を取得（LEFT JOINで最適化）
            $query = "
                SELECT 
                    p.ID, p.post_title, p.post_date, p.post_excerpt, p.post_name,
                    COALESCE(pm.meta_value, '0') as views
                FROM {$wpdb->posts} p
                LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'post_views_count'
                {$where_clause}
                ORDER BY p.post_date DESC
            ";
            
            $results = $wpdb->get_results($query);
            
            // データベースエラーをチェック
            if ($wpdb->last_error) {
                error_log('OptimizedBlogData get_archive_data: Database error: ' . $wpdb->last_error);
                return [];
            }
            
            // ビューカウントを整数に変換
            foreach ($results as $result) {
                $result->views = intval($result->views);
            }
            
            wp_cache_set($cache_key, $results, $this->cache_group, $this->cache_expiration);
            
            return $results;
            
        } catch (Exception $e) {
            error_log('OptimizedBlogData get_archive_data: Exception: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * 最適化されたカテゴリー別投稿取得
     * 
     * @param int $category_id カテゴリーID
     * @param int $limit 取得件数
     * @param int $offset オフセット
     * @return array 投稿データ配列
     */
    public function get_posts_by_category_optimized($category_id, $limit = 10, $offset = 0) {
        global $wpdb;
        
        // 入力値の検証
        $category_id = intval($category_id);
        $limit = intval($limit);
        $offset = intval($offset);
        
        if ($category_id <= 0 || $limit <= 0 || $limit > 100) {
            return [];
        }
        
        $cache_key = sprintf('posts_cat_%d_limit_%d_offset_%d', $category_id, $limit, $offset);
        $cached = wp_cache_get($cache_key, $this->cache_group);
        
        if (false !== $cached) {
            return $cached;
        }
        
        try {
            // 最適化されたJOINクエリ
            $query = $wpdb->prepare("
                SELECT 
                    p.ID, p.post_title, p.post_date, p.post_excerpt, p.post_name,
                    COALESCE(pm.meta_value, '0') as views
                FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
                INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'post_views_count'
                WHERE p.post_type = %s 
                AND p.post_status = %s 
                AND tt.taxonomy = %s 
                AND tt.term_id = %d
                ORDER BY p.post_date DESC
                LIMIT %d OFFSET %d
            ", 'post', 'publish', 'category', $category_id, $limit, $offset);
            
            $results = $wpdb->get_results($query);
            
            // データベースエラーをチェック
            if ($wpdb->last_error) {
                error_log('OptimizedBlogData get_posts_by_category_optimized: Database error: ' . $wpdb->last_error);
                return [];
            }
            
            // ビューカウントを整数に変換
            foreach ($results as $result) {
                $result->views = intval($result->views);
            }
            
            wp_cache_set($cache_key, $results, $this->cache_group, $this->cache_expiration);
            
            return $results;
            
        } catch (Exception $e) {
            error_log('OptimizedBlogData get_posts_by_category_optimized: Exception: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * 最適化された検索結果取得
     * 
     * @param string $search_term 検索語
     * @param int $limit 取得件数
     * @param int $offset オフセット
     * @return array 投稿データ配列
     */
    public function get_search_results_optimized($search_term, $limit = 10, $offset = 0) {
        global $wpdb;
        
        // 入力値の検証とサニタイズ
        $search_term = trim($search_term);
        if (empty($search_term) || strlen($search_term) < 2) {
            return [];
        }
        
        $limit = intval($limit);
        $offset = intval($offset);
        
        if ($limit <= 0 || $limit > 100) {
            return [];
        }
        
        $cache_key = sprintf('search_%s_limit_%d_offset_%d', md5($search_term), $limit, $offset);
        $cached = wp_cache_get($cache_key, $this->cache_group);
        
        if (false !== $cached) {
            return $cached;
        }
        
        try {
            // セキュアな検索クエリ（MATCH AGAINSTを使用できる場合）
            $search_like = '%' . $wpdb->esc_like($search_term) . '%';
            
            $query = $wpdb->prepare("
                SELECT 
                    p.ID, p.post_title, p.post_date, p.post_excerpt, p.post_name, p.post_content,
                    COALESCE(pm.meta_value, '0') as views,
                    CASE 
                        WHEN p.post_title LIKE %s THEN 3
                        WHEN p.post_excerpt LIKE %s THEN 2
                        ELSE 1
                    END as relevance_score
                FROM {$wpdb->posts} p
                LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'post_views_count'
                WHERE p.post_type = %s 
                AND p.post_status = %s
                AND (p.post_title LIKE %s OR p.post_content LIKE %s OR p.post_excerpt LIKE %s)
                ORDER BY relevance_score DESC, p.post_date DESC
                LIMIT %d OFFSET %d
            ", 
                $search_like, $search_like, 'post', 'publish', 
                $search_like, $search_like, $search_like, 
                $limit, $offset
            );
            
            $results = $wpdb->get_results($query);
            
            // データベースエラーをチェック
            if ($wpdb->last_error) {
                error_log('OptimizedBlogData get_search_results_optimized: Database error: ' . $wpdb->last_error);
                return [];
            }
            
            // ビューカウントを整数に変換し、検索語をハイライト
            foreach ($results as $result) {
                $result->views = intval($result->views);
                $result->relevance_score = intval($result->relevance_score);
            }
            
            wp_cache_set($cache_key, $results, $this->cache_group, self::CACHE_EXPIRATION_SHORT); // 検索結果は短めにキャッシュ
            
            return $results;
            
        } catch (Exception $e) {
            error_log('OptimizedBlogData get_search_results_optimized: Exception: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * 最適化されたタグクラウド取得
     * 
     * @param int $limit 取得するタグ数
     * @return array タグデータ配列
     */
    public function get_optimized_tag_cloud($limit = 30) {
        global $wpdb;
        
        $limit = intval($limit);
        if ($limit <= 0 || $limit > 100) {
            $limit = 30;
        }
        
        $cache_key = 'optimized_tag_cloud_' . $limit;
        $cached = wp_cache_get($cache_key, $this->cache_group);
        
        if (false !== $cached) {
            return $cached;
        }
        
        try {
            // 使用頻度の高いタグを効率的に取得
            $query = $wpdb->prepare("
                SELECT 
                    t.term_id, t.name, t.slug, tt.count,
                    ROUND(LOG10(tt.count + 1) * 5) as weight
                FROM {$wpdb->terms} t
                INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
                WHERE tt.taxonomy = %s 
                AND tt.count > 0
                ORDER BY tt.count DESC, t.name ASC
                LIMIT %d
            ", 'post_tag', $limit);
            
            $results = $wpdb->get_results($query);
            
            // データベースエラーをチェック
            if ($wpdb->last_error) {
                error_log('OptimizedBlogData get_optimized_tag_cloud: Database error: ' . $wpdb->last_error);
                return [];
            }
            
            // 重みを正規化
            if (!empty($results)) {
                $max_count = max(array_column($results, 'count'));
                $min_count = min(array_column($results, 'count'));
                
                foreach ($results as $result) {
                    $result->count = intval($result->count);
                    $result->weight = intval($result->weight);
                    $result->url = get_tag_link($result->term_id);
                    
                    // 相対的な重み計算（1-5の範囲）
                    if ($max_count > $min_count) {
                        $result->relative_weight = 1 + 4 * (($result->count - $min_count) / ($max_count - $min_count));
                    } else {
                        $result->relative_weight = 3;
                    }
                    $result->relative_weight = round($result->relative_weight);
                }
            }
            
            wp_cache_set($cache_key, $results, $this->cache_group, $this->cache_expiration);
            
            return $results;
            
        } catch (Exception $e) {
            error_log('OptimizedBlogData get_optimized_tag_cloud: Exception: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * キャッシュをクリア
     * 
     * @param array $keys 特定のキーのみクリアする場合
     */
    public function clear_cache($keys = []) {
        if (empty($keys)) {
            // wp_cache_flush_groupが利用可能な場合は使用
            if (function_exists('wp_cache_flush_group')) {
                wp_cache_flush_group($this->cache_group);
            } else {
                // パターンベースのキャッシュクリア
                global $wpdb;
                $wpdb->query($wpdb->prepare(
                    "DELETE FROM {$wpdb->options} 
                     WHERE option_name LIKE %s",
                    '_transient_' . $this->cache_group . '%'
                ));
                
                // オブジェクトキャッシュもクリア（Redis/Memcache対応）
                if (wp_using_ext_object_cache()) {
                    $common_patterns = [
                        'posts_meta_', 'archive_', 'posts_cat_', 'search_', 'optimized_tag_cloud_'
                    ];
                    foreach ($common_patterns as $pattern) {
                        // 既存の主要キーのみクリア（ループを削除）
                        wp_cache_delete($pattern, $this->cache_group);
                    }
                }
            }
        } else {
            foreach ($keys as $key) {
                wp_cache_delete($key, $this->cache_group);
            }
        }
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('OptimizedBlogData: Cache cleared');
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
     * パフォーマンス統計を取得（デバッグ用）
     * 
     * @return array
     */
    public function get_performance_stats() {
        global $wpdb;
        
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return ['error' => 'Debug mode is not enabled'];
        }
        
        $stats = [
            'cache_group' => $this->cache_group,
            'cache_expiration' => $this->cache_expiration,
            'db_queries' => $wpdb->num_queries,
            'timestamp' => current_time('mysql')
        ];
        
        return $stats;
    }
}

// インスタンス化関数
if (!function_exists('get_optimized_blog_data_instance')) {
    /**
     * OptimizedBlogDataインスタンスを取得
     * 
     * @return OptimizedBlogData
     */
    function get_optimized_blog_data_instance() {
        return \KeiPortfolio\Blog\OptimizedBlogData::get_instance();
    }
}
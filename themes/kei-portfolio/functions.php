<?php
/**
 * Kei Portfolio Pro functions and definitions
 *
 * @package Kei_Portfolio_Pro
 */

// 緊急修正パッチの読み込み（最優先）
require_once get_template_directory() . '/emergency-fix.php';

// テーマファイルの分割読み込み
require_once get_template_directory() . '/inc/setup.php';
require_once get_template_directory() . '/inc/enqueue.php';
require_once get_template_directory() . '/inc/post-types.php';
require_once get_template_directory() . '/inc/widgets.php';
require_once get_template_directory() . '/inc/customizer.php';
require_once get_template_directory() . '/inc/ajax-handlers.php';
require_once get_template_directory() . '/inc/optimizations.php';
require_once get_template_directory() . '/inc/page-creator.php';
require_once get_template_directory() . '/inc/sample-data.php';
require_once get_template_directory() . '/inc/class-portfolio-data.php';
require_once get_template_directory() . '/inc/class-blog-data.php';
require_once get_template_directory() . '/inc/class-optimized-blog-data.php';

// セキュリティ機能クラスの読み込み
require_once get_template_directory() . '/inc/security.php';
require_once get_template_directory() . '/inc/class-security-helper.php';
require_once get_template_directory() . '/inc/class-security-logger.php';
require_once get_template_directory() . '/inc/class-rate-limiter.php';
require_once get_template_directory() . '/inc/class-secure-session.php';

// パフォーマンス監視クラス
require_once get_template_directory() . '/inc/class-memory-manager.php';

// 新しいブログ機能クラスの読み込み
require_once get_template_directory() . '/inc/class-blog-logger.php';
require_once get_template_directory() . '/inc/class-blog-performance-monitor.php';
require_once get_template_directory() . '/inc/class-blog-config.php';

// ブログ関連機能ファイルの読み込み（存在チェック付き）
if (file_exists(get_template_directory() . '/inc/blog-optimizations.php')) {
    require_once get_template_directory() . '/inc/blog-optimizations.php';
}
if (file_exists(get_template_directory() . '/inc/blog-seo.php')) {
    require_once get_template_directory() . '/inc/blog-seo.php';
}
if (file_exists(get_template_directory() . '/inc/blog-widgets.php')) {
    require_once get_template_directory() . '/inc/blog-widgets.php';
}

// Portfolio Data キャッシュクリア機能
add_action('wp_ajax_clear_portfolio_cache', 'kei_portfolio_clear_cache_ajax');
add_action('wp_ajax_nopriv_clear_portfolio_cache', 'kei_portfolio_clear_cache_ajax');

/**
 * Portfolio Dataキャッシュクリア AJAX Handler
 */
function kei_portfolio_clear_cache_ajax() {
    // 管理者権限チェック
    if (!current_user_can('manage_options')) {
        wp_die(__('権限がありません。', 'kei-portfolio'));
    }
    
    // Nonceチェック
    if (!check_ajax_referer('kei_portfolio_cache_nonce', 'nonce', false)) {
        wp_send_json_error(__('セキュリティチェックに失敗しました。', 'kei-portfolio'));
        return;
    }
    
    // キャッシュクリア実行
    $portfolio_data = Portfolio_Data::get_instance();
    $portfolio_data->clear_cache();
    
    wp_send_json_success(__('ポートフォリオデータのキャッシュをクリアしました。', 'kei-portfolio'));
}

/**
 * 管理画面にキャッシュクリアボタンを追加
 */
add_action('admin_bar_menu', 'kei_portfolio_add_cache_clear_button', 999);

function kei_portfolio_add_cache_clear_button($wp_admin_bar) {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $wp_admin_bar->add_menu(array(
        'id'    => 'portfolio-cache-clear',
        'title' => __('ポートフォリオキャッシュクリア', 'kei-portfolio'),
        'href'  => '#',
        'meta'  => array(
            'onclick' => 'keiPortfolioClearCache(); return false;',
        ),
    ));
}

/**
 * キャッシュクリア用JavaScript（セキュア実装）
 */
add_action('wp_enqueue_scripts', 'kei_portfolio_enqueue_cache_clear_script');
add_action('admin_enqueue_scripts', 'kei_portfolio_enqueue_cache_clear_script');

function kei_portfolio_enqueue_cache_clear_script() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // インラインスクリプトとしてEnqueue
    wp_add_inline_script('jquery', '
        function keiPortfolioClearCache() {
            if (confirm("ポートフォリオデータのキャッシュをクリアしますか？")) {
                if (!window.keiPortfolioCache || !window.keiPortfolioCache.nonce) {
                    alert("セキュリティトークンが無効です。ページを再読み込みしてください。");
                    return;
                }
                
                jQuery.ajax({
                    url: window.keiPortfolioCache.ajaxurl,
                    type: "POST",
                    data: {
                        action: "clear_portfolio_cache",
                        nonce: window.keiPortfolioCache.nonce
                    },
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader("X-WP-Nonce", window.keiPortfolioCache.nonce);
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.data);
                            location.reload();
                        } else {
                            alert("エラー: " + (response.data || "不明なエラー"));
                        }
                    },
                    error: function(xhr, status, error) {
                        let errorMsg = "キャッシュクリアに失敗しました。";
                        if (xhr.status === 403) {
                            errorMsg = "アクセスが拒否されました。ページを再読み込みしてください。";
                        }
                        alert(errorMsg);
                    }
                });
            }
        }
    ');
    
    // Cache clear用のデータをlocalize
    wp_localize_script('jquery', 'keiPortfolioCache', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('kei_portfolio_cache_nonce'),
    ));
}

/**
 * セキュリティ機能の初期化
 */
add_action('after_setup_theme', 'kei_portfolio_init_security_features', 1);

function kei_portfolio_init_security_features() {
    // セキュリティヘルパーの初期化
    \KeiPortfolio\Security\SecurityHelper::init();
    
    // セキュリティロガーの初期化
    $security_logger = \KeiPortfolio\Security\SecurityLogger::get_instance();
    
    // レート制限機能の初期化
    $rate_limiter = \KeiPortfolio\Security\RateLimiter::get_instance();
    
    // セキュリティロガーに初期化完了を記録
    $security_logger->info('security_init', 'Security features initialized successfully', array(
        'wp_debug' => defined('WP_DEBUG') && WP_DEBUG,
        'environment' => defined('WP_ENVIRONMENT_TYPE') ? WP_ENVIRONMENT_TYPE : 'unknown',
        'ssl_enabled' => is_ssl(),
        'php_version' => PHP_VERSION,
        'wp_version' => get_bloginfo('version')
    ));
}

/**
 * 新しいブログ機能クラスの初期化
 */
add_action('after_setup_theme', 'kei_portfolio_init_blog_classes', 5);

function kei_portfolio_init_blog_classes() {
    // クラスのインスタンス化（シングルトンパターン）- 完全修飾名使用
    $logger = \KeiPortfolio\Blog\Blog_Logger::get_instance();
    $performance_monitor = \KeiPortfolio\Blog\Blog_Performance_Monitor::get_instance();
    $config = \KeiPortfolio\Blog\Blog_Config::get_instance();
    $optimized_blog_data = \KeiPortfolio\Blog\OptimizedBlogData::get_instance();
    
    // パフォーマンス監視クラスの初期化
    $memory_manager = \KeiPortfolio\Performance\MemoryManager::get_instance();
    
    // 初期化完了をログに記録
    $logger->info('Blog feature classes initialized successfully', [
        'wp_debug' => defined('WP_DEBUG') && WP_DEBUG,
        'environment' => defined('WP_ENVIRONMENT_TYPE') ? WP_ENVIRONMENT_TYPE : 'unknown',
        'php_version' => PHP_VERSION,
        'wp_version' => get_bloginfo('version')
    ]);
}

/**
 * ブログ機能の初期化
 */
add_action('after_setup_theme', 'kei_portfolio_init_blog_features');

function kei_portfolio_init_blog_features() {
    // ブログ用メニュー位置の登録
    register_nav_menus(array(
        'blog-header' => __('ブログヘッダーメニュー', 'kei-portfolio'),
        'blog-footer' => __('ブログフッターメニュー', 'kei-portfolio'),
        'blog-sidebar' => __('ブログサイドバーメニュー', 'kei-portfolio'),
    ));
    
    // 投稿フォーマットのサポート
    add_theme_support('post-formats', array(
        'aside',
        'gallery',
        'video',
        'audio',
        'quote',
        'link'
    ));
    
    // 投稿サムネイルのサポート（既存の確認）
    add_theme_support('post-thumbnails');
    
    // エディタースタイルのサポート
    add_theme_support('editor-styles');
    add_editor_style('assets/css/blog-editor.css');
    
    // レスポンシブ埋め込みのサポート
    add_theme_support('responsive-embeds');
}

/**
 * 読了時間計算と保存機能 - N+1問題対策
 */
 
/**
 * 読了時間を計算してメタデータに保存
 *
 * @param int $post_id 投稿ID
 * @return int 読了時間（分）
 */
function calculate_and_save_reading_time($post_id) {
    // 投稿が存在するかチェック
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'post') {
        return 0;
    }
    
    // 本文内容を取得
    $content = apply_filters('the_content', $post->post_content);
    
    // HTMLタグを削除してテキストのみを抽出
    $text_content = wp_strip_all_tags($content);
    
    // 日本語と英語の文字数を計算
    $japanese_chars = mb_strlen(preg_replace('/[a-zA-Z0-9\s\p{P}]/u', '', $text_content));
    $english_words = str_word_count(preg_replace('/[^\x00-\x7F]+/', ' ', $text_content));
    
    // 読了時間を計算（日本語：400文字/分、英語：200語/分）
    $japanese_reading_time = $japanese_chars / 400;
    $english_reading_time = $english_words / 200;
    $total_reading_time = max(1, ceil($japanese_reading_time + $english_reading_time));
    
    // メタデータとして保存
    update_post_meta($post_id, '_reading_time', $total_reading_time);
    update_post_meta($post_id, '_reading_time_updated', current_time('timestamp'));
    
    return $total_reading_time;
}

/**
 * 読了時間を取得（キャッシュ機能付き）
 *
 * @param int $post_id 投稿ID
 * @param bool $force_recalculate 強制再計算
 * @return int 読了時間（分）
 */
function get_reading_time($post_id, $force_recalculate = false) {
    // 既存の読了時間を確認
    $reading_time = get_post_meta($post_id, '_reading_time', true);
    $last_updated = get_post_meta($post_id, '_reading_time_updated', true);
    $post_modified = get_post_modified_time('U', true, $post_id);
    
    // 再計算が必要な条件をチェック
    $needs_recalculation = $force_recalculate || 
                          empty($reading_time) || 
                          empty($last_updated) || 
                          $post_modified > $last_updated;
    
    if ($needs_recalculation) {
        $reading_time = calculate_and_save_reading_time($post_id);
    }
    
    return (int) $reading_time;
}

/**
 * 投稿保存時に読了時間を自動計算
 */
add_action('save_post_post', 'auto_calculate_reading_time_on_save', 10, 1);

function auto_calculate_reading_time_on_save($post_id) {
    // 自動保存やリビジョンを除外
    if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
        return;
    }
    
    // 読了時間を再計算
    calculate_and_save_reading_time($post_id);
}

/**
 * 非同期で読了時間を保存するためのカスタムアクション
 */
add_action('kei_portfolio_save_reading_time', 'async_save_reading_time', 10, 2);

function async_save_reading_time($post_id, $reading_time) {
    update_post_meta($post_id, '_reading_time', $reading_time);
    update_post_meta($post_id, '_reading_time_updated', current_time('timestamp'));
}

/**
 * 既存の投稿の読了時間を一括計算するWP-CLIコマンド
 * 使用方法: wp eval 'bulk_calculate_reading_times();'
 */
function bulk_calculate_reading_times($limit = 100, $offset = 0) {
    $posts = get_posts([
        'post_type' => 'post',
        'post_status' => 'publish',
        'numberposts' => $limit,
        'offset' => $offset,
        'fields' => 'ids'
    ]);
    
    $count = 0;
    foreach ($posts as $post_id) {
        calculate_and_save_reading_time($post_id);
        $count++;
    }
    
    if (defined('WP_CLI') && WP_CLI) {
        WP_CLI::success("Updated reading time for {$count} posts (offset: {$offset})");
    }
    
    return $count;
}

/**
 * ブログ用ウィジェットエリアの登録
 */
add_action('widgets_init', 'kei_portfolio_register_blog_widgets');

function kei_portfolio_register_blog_widgets() {
    // メインサイドバー
    register_sidebar(array(
        'name'          => __('ブログサイドバー', 'kei-portfolio'),
        'id'            => 'blog-sidebar',
        'description'   => __('ブログページのサイドバーウィジェットエリア', 'kei-portfolio'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
    
    // ブログフッター（3カラム）
    for ($i = 1; $i <= 3; $i++) {
        register_sidebar(array(
            'name'          => sprintf(__('ブログフッター %d', 'kei-portfolio'), $i),
            'id'            => 'blog-footer-' . $i,
            'description'   => sprintf(__('ブログフッターの%dカラム目', 'kei-portfolio'), $i),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h4 class="widget-title">',
            'after_title'   => '</h4>',
        ));
    }
}

/**
 * Ajax用のnonce設定とセキュリティ強化（統一化）
 */
function kei_portfolio_enqueue_scripts() {
    // 既存のスクリプト読み込み処理
    
    // Nonce情報をJavaScriptに渡す（統一設定）
    wp_localize_script('jquery', 'keiPortfolioAjax', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonces' => array(
            'default' => wp_create_nonce('kei_portfolio_ajax'),
            'blog' => wp_create_nonce('blog_ajax_action'),
            'search' => wp_create_nonce('search_ajax_action'),
            'loadMore' => wp_create_nonce('load_more_posts'),
            'share' => wp_create_nonce('track_share'),
            'instantSearch' => wp_create_nonce('blog_instant_search'),
            'cache' => wp_create_nonce('kei_portfolio_cache_nonce')
        ),
        // 後方互換性のため個別プロパティも維持（削除予定）
        'nonce' => wp_create_nonce('kei_portfolio_ajax'),
        'blogNonce' => wp_create_nonce('blog_ajax_action'),
        'searchNonce' => wp_create_nonce('search_ajax_action'),
        'loadMoreNonce' => wp_create_nonce('load_more_posts'),
        'shareNonce' => wp_create_nonce('track_share'),
        'instantSearchNonce' => wp_create_nonce('blog_instant_search')
    ));
}
add_action('wp_enqueue_scripts', 'kei_portfolio_enqueue_scripts', 5);

/**
 * Ajax処理でのnonce検証関数（共通）
 */
function kei_portfolio_verify_nonce($action, $nonce_field = 'nonce') {
    if (!isset($_POST[$nonce_field])) {
        wp_send_json_error(array('message' => 'セキュリティトークンが送信されていません'));
        return false;
    }
    
    if (!wp_verify_nonce($_POST[$nonce_field], $action)) {
        wp_send_json_error(array('message' => 'セキュリティチェックに失敗しました'));
        return false;
    }
    
    return true;
}

/**
 * Ajax処理でのnonce検証（ブログ機能用）
 */
function handle_blog_ajax_request() {
    // レート制限チェック
    $rate_limiter = \KeiPortfolio\Security\RateLimiter::get_instance();
    if (!$rate_limiter->check('blog_ajax', array('limit' => 20, 'window' => 60))) {
        wp_send_json_error(array('message' => 'リクエストが制限されています。しばらく待ってから再度お試しください。'));
        return;
    }
    
    // Nonce検証
    if (!kei_portfolio_verify_nonce('blog_ajax_action')) {
        return;
    }
    
    // アクションのサニタイゼーション
    $action = sanitize_text_field($_POST['blog_action']);
    
    switch ($action) {
        case 'load_more':
            handle_load_more_posts();
            break;
        case 'filter_posts':
            handle_filter_posts();
            break;
        case 'search_posts':
            handle_search_posts();
            break;
        default:
            wp_send_json_error(array('message' => '無効なアクションです'));
    }
}
add_action('wp_ajax_blog_action', 'handle_blog_ajax_request');
add_action('wp_ajax_nopriv_blog_action', 'handle_blog_ajax_request');

/**
 * インスタント検索のAjaxハンドラー
 */
function handle_blog_instant_search() {
    // レート制限チェック（検索は頻繁なのでより制限を緩く）
    $rate_limiter = \KeiPortfolio\Security\RateLimiter::get_instance();
    if (!$rate_limiter->check('instant_search', array('limit' => 30, 'window' => 60))) {
        wp_send_json_error(array('message' => '検索リクエストが制限されています。しばらく待ってから再度お試しください。'));
        return;
    }
    
    // Nonce検証
    if (!kei_portfolio_verify_nonce('blog_instant_search')) {
        return;
    }
    
    // 検索クエリのサニタイゼーション
    $query = sanitize_text_field($_POST['query']);
    $query = substr($query, 0, 100); // 長さ制限
    
    if (strlen($query) < 3) {
        wp_send_json_error(array('message' => '検索語句は3文字以上入力してください'));
        return;
    }
    
    // 検索実行
    $search_results = new WP_Query(array(
        's' => $query,
        'posts_per_page' => 5,
        'post_type' => 'post',
        'post_status' => 'publish',
        'no_found_rows' => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false
    ));
    
    $results = array();
    if ($search_results->have_posts()) {
        while ($search_results->have_posts()) {
            $search_results->the_post();
            $results[] = array(
                'title' => get_the_title(),
                'excerpt' => get_the_excerpt(),
                'url' => get_permalink(),
                'date' => get_the_date()
            );
        }
    }
    wp_reset_postdata();
    
    wp_send_json_success($results);
}
add_action('wp_ajax_blog_instant_search', 'handle_blog_instant_search');
add_action('wp_ajax_nopriv_blog_instant_search', 'handle_blog_instant_search');

/**
 * さらなる投稿読み込みのAjaxハンドラー
 */
function handle_load_more_posts() {
    // レート制限チェック
    $rate_limiter = \KeiPortfolio\Security\RateLimiter::get_instance();
    if (!$rate_limiter->check('load_more_posts', array('limit' => 15, 'window' => 60))) {
        wp_send_json_error(array('message' => '読み込みリクエストが制限されています。しばらく待ってから再度お試しください。'));
        return;
    }
    
    // Nonce検証
    if (!kei_portfolio_verify_nonce('load_more_posts')) {
        return;
    }
    
    $page = intval($_POST['page']);
    $category = sanitize_text_field($_POST['category'] ?? '');
    $search = sanitize_text_field($_POST['search'] ?? '');
    
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => get_option('posts_per_page', 10),
        'paged' => $page,
        'post_status' => 'publish'
    );
    
    if (!empty($category) && $category !== 'all') {
        $args['category_name'] = $category;
    }
    
    if (!empty($search)) {
        $args['s'] = $search;
    }
    
    $query = new WP_Query($args);
    
    if ($query->have_posts()) {
        ob_start();
        while ($query->have_posts()) {
            $query->the_post();
            get_template_part('template-parts/blog/post-card');
        }
        $html = ob_get_clean();
        wp_reset_postdata();
        
        wp_send_json_success(array(
            'html' => $html,
            'current_page' => $page,
            'max_pages' => $query->max_num_pages,
            'posts_count' => $query->post_count
        ));
    } else {
        wp_send_json_error(array('message' => 'これ以上の投稿はありません'));
    }
}
add_action('wp_ajax_load_more_posts', 'handle_load_more_posts');
add_action('wp_ajax_nopriv_load_more_posts', 'handle_load_more_posts');

/**
 * シェア追跡のAjaxハンドラー
 */
function handle_track_share() {
    // レート制限チェック
    $rate_limiter = \KeiPortfolio\Security\RateLimiter::get_instance();
    if (!$rate_limiter->check('track_share', array('limit' => 10, 'window' => 60))) {
        wp_send_json_error(array('message' => 'シェア追跡リクエストが制限されています。'));
        return;
    }
    
    // Nonce検証
    if (!kei_portfolio_verify_nonce('track_share')) {
        return;
    }
    
    $type = sanitize_text_field($_POST['type']);
    $url = esc_url_raw($_POST['url']);
    
    // 統計の更新（例：カスタムテーブルやメタデータ）
    $allowed_types = array('twitter', 'facebook', 'line', 'copy');
    if (in_array($type, $allowed_types, true)) {
        // シェア統計を更新
        $post_id = url_to_postid($url);
        if ($post_id) {
            $share_count = get_post_meta($post_id, "_share_count_{$type}", true) ?: 0;
            update_post_meta($post_id, "_share_count_{$type}", $share_count + 1);
        }
        wp_send_json_success();
    } else {
        wp_send_json_error(array('message' => '無効なシェアタイプです'));
    }
}
add_action('wp_ajax_track_share', 'handle_track_share');
add_action('wp_ajax_nopriv_track_share', 'handle_track_share');

/**
 * ブログ関連スタイル・スクリプトのエンキュー
 */
add_action('wp_enqueue_scripts', 'kei_portfolio_enqueue_blog_assets');

function kei_portfolio_enqueue_blog_assets() {
    // ブログページでのみ読み込み
    if (is_home() || is_single() || is_archive() || is_search() || is_category() || is_tag() || is_author() || is_date()) {
        
        // ブログ用CSS（ファイル存在チェック付き）
        $blog_css = get_template_directory() . '/assets/css/blog.css';
        if (file_exists($blog_css)) {
            wp_enqueue_style(
                'kei-portfolio-blog',
                get_template_directory_uri() . '/assets/css/blog.css',
                array('kei-portfolio-style'),
                wp_get_theme()->get('Version')
            );
        }
        
        // モバイル用CSS（ファイル存在チェック付き）
        $blog_mobile_css = get_template_directory() . '/assets/css/blog-mobile.css';
        if (file_exists($blog_mobile_css)) {
            wp_enqueue_style(
                'kei-portfolio-blog-mobile',
                get_template_directory_uri() . '/assets/css/blog-mobile.css',
                array('kei-portfolio-blog'),
                wp_get_theme()->get('Version'),
                '(max-width: 768px)'
            );
        }
        
        // プリント用CSS（ファイル存在チェック付き）
        $blog_print_css = get_template_directory() . '/assets/css/blog-print.css';
        if (file_exists($blog_print_css)) {
            wp_enqueue_style(
                'kei-portfolio-blog-print',
                get_template_directory_uri() . '/assets/css/blog-print.css',
                array('kei-portfolio-blog'),
                wp_get_theme()->get('Version'),
                'print'
            );
        }
        
        // ブログ用JavaScript（ファイル存在チェック付き）
        $blog_js = get_template_directory() . '/assets/js/blog.js';
        if (file_exists($blog_js)) {
            wp_enqueue_script(
                'kei-portfolio-blog',
                get_template_directory_uri() . '/assets/js/blog.js',
                array('jquery'),
                wp_get_theme()->get('Version'),
                true
            );
        }
        
        // AJAX用JavaScript（検索・フィルタリング）
        if (is_home() || is_archive()) {
            $blog_ajax_js = get_template_directory() . '/assets/js/blog-ajax.js';
            if (file_exists($blog_ajax_js)) {
                wp_enqueue_script(
                    'kei-portfolio-blog-ajax',
                    get_template_directory_uri() . '/assets/js/blog-ajax.js',
                    array('jquery', 'kei-portfolio-blog'),
                    wp_get_theme()->get('Version'),
                    true
                );
                
                // AJAX用データのローカライズ（統一化されたnoncesを使用）
                wp_localize_script('kei-portfolio-blog-ajax', 'blogAjax', array(
                    'ajaxurl' => admin_url('admin-ajax.php'),
                    'nonces' => array(
                        'blog' => wp_create_nonce('blog_ajax_action'),
                        'loadMore' => wp_create_nonce('load_more_posts'),
                        'search' => wp_create_nonce('search_ajax_action'),
                        'instantSearch' => wp_create_nonce('blog_instant_search')
                    ),
                    // 後方互換性のため既存のnonceも維持
                    'nonce' => wp_create_nonce('blog_ajax_action'),
                    'current_page' => get_query_var('paged') ? get_query_var('paged') : 1,
                    'max_pages' => 1, // クエリから動的に設定される
                    'loading' => __('読み込み中...', 'kei-portfolio'),
                    'error' => __('エラーが発生しました。', 'kei-portfolio'),
                    'no_posts' => __('投稿が見つかりませんでした。', 'kei-portfolio'),
                    'security_error' => __('セキュリティエラーが発生しました。ページを再読み込みしてください。', 'kei-portfolio'),
                ));
                
                // Blog.js でも同じデータを使用するように統一
                wp_localize_script('kei-portfolio-blog', 'blogAjax', array(
                    'ajaxurl' => admin_url('admin-ajax.php'),
                    'nonces' => array(
                        'blog' => wp_create_nonce('blog_ajax_action'),
                        'loadMore' => wp_create_nonce('load_more_posts'),
                        'search' => wp_create_nonce('search_ajax_action'),
                        'instantSearch' => wp_create_nonce('blog_instant_search')
                    ),
                    // 後方互換性のため既存のnonceも維持
                    'nonce' => wp_create_nonce('blog_ajax_action'),
                    'loading' => __('読み込み中...', 'kei-portfolio'),
                    'error' => __('エラーが発生しました。', 'kei-portfolio'),
                    'no_posts' => __('投稿が見つかりませんでした。', 'kei-portfolio'),
                    'security_error' => __('セキュリティエラーが発生しました。ページを再読み込みしてください。', 'kei-portfolio'),
                ));
            }
        }
        
        // 検索ページ用JavaScript
        if (is_search()) {
            $blog_search_js = get_template_directory() . '/assets/js/blog-search.js';
            if (file_exists($blog_search_js)) {
                wp_enqueue_script(
                    'kei-portfolio-blog-search',
                    get_template_directory_uri() . '/assets/js/blog-search.js',
                    array('jquery'),
                    wp_get_theme()->get('Version'),
                    true
                );
            }
        }
    }
}

/**
 * ブログ関連のフックとフィルター設定
 */
add_action('init', 'kei_portfolio_init_blog_hooks');

function kei_portfolio_init_blog_hooks() {
    // 投稿の抜粋文字数を調整
    add_filter('excerpt_length', 'kei_portfolio_custom_excerpt_length', 999);
    
    // 抜粋の「...」をカスタマイズ
    add_filter('excerpt_more', 'kei_portfolio_custom_excerpt_more');
    
    // 投稿一覧のクエリを調整
    add_action('pre_get_posts', 'kei_portfolio_modify_main_query');
    
    // 投稿のビュー数をカウント
    add_action('wp_head', 'kei_portfolio_track_post_views');
    
    // RSS フィードの改善
    add_filter('the_content_feed', 'kei_portfolio_rss_post_thumbnail');
    
    // 関連記事用のキャッシュクリア
    add_action('save_post', 'kei_portfolio_clear_related_posts_cache');
    add_action('deleted_post', 'kei_portfolio_clear_related_posts_cache');
    
    // 読了時間の事前計算
    add_action('save_post', 'kei_portfolio_calculate_reading_time_on_save');
    add_action('wp_insert_post', 'kei_portfolio_calculate_reading_time_on_save');
}

/**
 * カスタム抜粋文字数
 */
function kei_portfolio_custom_excerpt_length($length) {
    if (is_admin()) {
        return $length;
    }
    
    if (wp_is_mobile()) {
        return 20;
    }
    
    return 30;
}

/**
 * カスタム抜粋の続きを読む
 */
function kei_portfolio_custom_excerpt_more($more) {
    if (is_feed()) {
        return $more;
    }
    
    return ' ... <a href="' . get_permalink() . '" class="read-more">' . __('続きを読む', 'kei-portfolio') . '</a>';
}

/**
 * メインクエリの調整
 */
function kei_portfolio_modify_main_query($query) {
    if (is_admin() || !$query->is_main_query()) {
        return;
    }
    
    // ブログ一覧ページの投稿数を調整
    if (is_home() && !is_front_page()) {
        $query->set('posts_per_page', get_option('blog_posts_per_page', 9));
    }
    
    // 検索結果のパフォーマンス最適化
    if (is_search()) {
        $query->set('posts_per_page', 12);
        $query->set('no_found_rows', true);
    }
    
    // アーカイブページの最適化
    if (is_archive()) {
        $query->set('posts_per_page', 12);
    }
}

/**
 * 投稿ビュー数の追跡（管理者除外でより正確な統計）
 */
function kei_portfolio_track_post_views() {
    try {
        // 管理者のアクセスは除外してより正確な統計を取得
        if (is_single() && get_post_type() === 'post' && !is_user_logged_in() && !is_admin()) {
            global $post;
            
            // $postオブジェクトが存在するかチェック
            if (!$post || !isset($post->ID)) {
                error_log('kei_portfolio_track_post_views: Invalid post object');
                return;
            }
            
            $post_id = intval($post->ID);
            if ($post_id <= 0) {
                error_log('kei_portfolio_track_post_views: Invalid post ID: ' . $post_id);
                return;
            }
            
            // 同一セッションでの重複カウントを防ぐ（SecureSessionクラス使用）
            $secure_session = \KeiPortfolio\Security\SecureSession::get_instance();
            $session_key = 'viewed_post_' . $post_id;
            if ($secure_session->get($session_key)) {
                return;
            }
            
            $views = get_post_meta($post_id, '_post_views_count', true);
            
            if (empty($views) || !is_numeric($views)) {
                $views = 0;
            } else {
                $views = intval($views);
            }
            
            $views++;
            
            // メタデータ更新の成功を確認
            $result = update_post_meta($post_id, '_post_views_count', $views);
            if (false === $result) {
                error_log('kei_portfolio_track_post_views: Failed to update post meta for post ID: ' . $post_id);
            }
            
            // SecureSessionクラスを使用してセッションに記録
            $secure_session->set($session_key, true);
            
            // キャッシュ更新
            wp_cache_delete('popular_posts_blog', 'kei_portfolio');
        }
    } catch (Exception $e) {
        error_log('kei_portfolio_track_post_views: Exception: ' . $e->getMessage());
    } catch (Error $e) {
        error_log('kei_portfolio_track_post_views: Fatal error: ' . $e->getMessage());
    }
}

/**
 * RSSフィードに投稿サムネイルを追加
 */
function kei_portfolio_rss_post_thumbnail($content) {
    global $post;
    
    if (has_post_thumbnail($post->ID)) {
        $content = '<div>' . get_the_post_thumbnail($post->ID, 'medium') . '</div>' . $content;
    }
    
    return $content;
}

/**
 * 関連記事キャッシュのクリア
 */
function kei_portfolio_clear_related_posts_cache($post_id) {
    if (get_post_type($post_id) === 'post') {
        wp_cache_delete('related_posts_' . $post_id, 'kei_portfolio');
        wp_cache_delete('recent_blog_posts', 'kei_portfolio');
        wp_cache_delete('popular_posts_blog', 'kei_portfolio');
        wp_cache_delete("reading_time_$post_id", 'kei_portfolio');
    }
}

/**
 * 読了時間を非同期で保存するフック
 */
add_action('kei_portfolio_save_reading_time', 'kei_portfolio_async_save_reading_time', 10, 2);

function kei_portfolio_async_save_reading_time($post_id, $reading_time) {
    if (!$post_id || !$reading_time) {
        return;
    }
    
    // 投稿が存在するかチェック
    if (!get_post($post_id)) {
        return;
    }
    
    // メタデータを更新
    $result = update_post_meta($post_id, '_reading_time', absint($reading_time));
    
    if (WP_DEBUG && WP_DEBUG_LOG) {
        if ($result) {
            error_log("Reading time saved for post {$post_id}: {$reading_time} minutes");
        } else {
            error_log("Failed to save reading time for post {$post_id}");
        }
    }
}

/**
 * 投稿保存時に読了時間を事前計算
 */
function kei_portfolio_calculate_reading_time_on_save($post_id) {
    // 自動保存の場合は処理しない
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // リビジョンの場合は処理しない
    if (wp_is_post_revision($post_id)) {
        return;
    }
    
    // ブログ投稿のみを対象とする
    if (get_post_type($post_id) !== 'post') {
        return;
    }
    
    // 投稿ステータスが公開済みまたは下書きの場合のみ処理
    $post_status = get_post_status($post_id);
    if (!in_array($post_status, ['publish', 'draft', 'future'])) {
        return;
    }
    
    // 投稿内容を取得
    $post = get_post($post_id);
    if (!$post || empty($post->post_content)) {
        return;
    }
    
    // 読了時間を計算
    $content = wp_strip_all_tags($post->post_content);
    $char_count = mb_strlen($content, 'UTF-8');
    $word_count = preg_match_all('/[\p{Han}\p{Hiragana}\p{Katakana}]/u', $content) + 
                 str_word_count($content);
    
    // 日本語の場合は400文字/分、英語の場合は200語/分で計算
    $reading_time = max(1, ceil(($char_count * 0.4 + $word_count) / 200));
    
    // メタデータを更新
    update_post_meta($post_id, '_reading_time', $reading_time);
    
    // キャッシュをクリア
    wp_cache_delete("reading_time_$post_id", 'kei_portfolio');
    
    if (WP_DEBUG && WP_DEBUG_LOG) {
        error_log("Reading time pre-calculated for post {$post_id}: {$reading_time} minutes");
    }
}

/**
 * ブログ用カスタマイザー設定
 */
add_action('customize_register', 'kei_portfolio_blog_customizer');

function kei_portfolio_blog_customizer($wp_customize) {
    // ブログセクション
    $wp_customize->add_section('blog_settings', array(
        'title' => __('ブログ設定', 'kei-portfolio'),
        'priority' => 35,
        'description' => __('ブログ関連の表示設定を行います。', 'kei-portfolio'),
    ));
    
    // 1ページあたりの投稿数
    $wp_customize->add_setting('blog_posts_per_page', array(
        'default' => 9,
        'sanitize_callback' => 'absint',
        'transport' => 'refresh',
    ));
    
    $wp_customize->add_control('blog_posts_per_page', array(
        'label' => __('1ページあたりの投稿数', 'kei-portfolio'),
        'description' => __('ブログ一覧ページに表示する投稿数を設定します。', 'kei-portfolio'),
        'section' => 'blog_settings',
        'type' => 'number',
        'input_attrs' => array(
            'min' => 1,
            'max' => 24,
            'step' => 1,
        ),
    ));
    
    // サイドバーの表示/非表示
    $wp_customize->add_setting('blog_show_sidebar', array(
        'default' => true,
        'sanitize_callback' => 'wp_validate_boolean',
        'transport' => 'refresh',
    ));
    
    $wp_customize->add_control('blog_show_sidebar', array(
        'label' => __('サイドバーを表示', 'kei-portfolio'),
        'description' => __('ブログページでサイドバーを表示するかどうかを設定します。', 'kei-portfolio'),
        'section' => 'blog_settings',
        'type' => 'checkbox',
    ));
    
    // 関連記事の表示数
    $wp_customize->add_setting('blog_related_posts_count', array(
        'default' => 3,
        'sanitize_callback' => 'absint',
        'transport' => 'refresh',
    ));
    
    $wp_customize->add_control('blog_related_posts_count', array(
        'label' => __('関連記事の表示数', 'kei-portfolio'),
        'description' => __('個別記事ページに表示する関連記事の数を設定します。', 'kei-portfolio'),
        'section' => 'blog_settings',
        'type' => 'number',
        'input_attrs' => array(
            'min' => 0,
            'max' => 12,
            'step' => 1,
        ),
    ));
}
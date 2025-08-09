<?php
/**
 * ブログページの自動作成と設定
 * 
 * WordPressのブログ投稿表示用ページを自動的に作成し、
 * REST API 403エラー修正のための適切な設定を行う
 * 
 * 主な機能：
 * - ブログページの自動作成
 * - WordPress表示設定の自動調整
 * - パーマリンク設定の確認と警告
 * - ブログページ設定の自動修正
 * - 管理画面での設定状況通知
 * 
 * @package Kei_Portfolio_Pro
 * @since 1.0.0
 * @version 2.0.0 - REST API対応強化
 */

// 直接アクセスを防ぐ
if (!defined('ABSPATH')) {
    exit;
}

// 定数定義
define('KEI_PORTFOLIO_MIN_POSTS_PER_PAGE', 5);
define('KEI_PORTFOLIO_MAX_POSTS_PER_PAGE', 20);
define('KEI_PORTFOLIO_DEFAULT_POSTS_PER_PAGE', 10);

/**
 * ブログページの自動作成と設定
 * REST API 403エラー対策を含む包括的な設定
 * 
 * @return array 作成・設定結果の詳細情報
 */
function kei_portfolio_create_blog_page() {
    $results = array(
        'blog_page_created' => false,
        'blog_page_id' => 0,
        'settings_updated' => array(),
        'errors' => array()
    );
    
    try {
        // 既存のブログページを確認
        $blog_page = get_page_by_path('blog');
        
        if (!$blog_page) {
            // ブログページが存在しない場合は作成
            $blog_page_args = array(
                'post_title'    => __('Blog', 'kei-portfolio'),
                'post_name'     => 'blog',
                'post_status'   => 'publish',
                'post_type'     => 'page',
                'post_content'  => '<!-- wp:paragraph --><p>' . __('こちらはブログページです。投稿記事がここに表示されます。', 'kei-portfolio') . '</p><!-- /wp:paragraph -->',
                'post_author'   => get_current_user_id() ?: 1,
                'comment_status' => 'closed',
                'ping_status'   => 'closed',
                'menu_order'    => 2,
                'meta_input'    => array(
                    '_wp_page_template' => 'default',
                    '_kei_portfolio_auto_created' => current_time('mysql'),
                    '_kei_portfolio_page_type' => 'blog'
                )
            );
            
            $blog_page_id = wp_insert_post($blog_page_args);
            
            if ($blog_page_id && !is_wp_error($blog_page_id)) {
                $results['blog_page_created'] = true;
                $results['blog_page_id'] = $blog_page_id;
                
                // ページ作成成功をログに記録
                kei_portfolio_log_blog_action('Blog page created successfully', array(
                    'page_id' => $blog_page_id,
                    'page_url' => get_permalink($blog_page_id)
                ));
            } else {
                $error_message = is_wp_error($blog_page_id) ? $blog_page_id->get_error_message() : 'Unknown error';
                $results['errors'][] = 'Failed to create blog page: ' . $error_message;
                kei_portfolio_log_blog_action('Blog page creation failed', array('error' => $error_message), 'error');
                return $results;
            }
        } else {
            $results['blog_page_id'] = $blog_page->ID;
            
            // ページが下書きや非公開の場合は公開する
            if ($blog_page->post_status !== 'publish') {
                $update_result = wp_update_post(array(
                    'ID' => $blog_page->ID,
                    'post_status' => 'publish'
                ));
                
                if (!is_wp_error($update_result)) {
                    $results['settings_updated'][] = 'Blog page published';
                    kei_portfolio_log_blog_action('Blog page published', array('page_id' => $blog_page->ID));
                }
            }
        }
        
        // WordPress表示設定の自動調整
        $blog_settings_result = kei_portfolio_configure_blog_settings($results['blog_page_id']);
        $results['settings_updated'] = array_merge($results['settings_updated'], $blog_settings_result['updates']);
        $results['errors'] = array_merge($results['errors'], $blog_settings_result['errors']);
        
        // パーマリンク設定の確認
        kei_portfolio_check_and_warn_permalink();
        
        // キャッシュのクリア
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
        
        // リライトルールの更新（ブログページが作成または変更された場合のみ）
        if ($results['blog_page_created'] || !empty($results['settings_updated'])) {
            flush_rewrite_rules();
        }
        
    } catch (Exception $e) {
        $results['errors'][] = 'Exception in blog page creation: ' . $e->getMessage();
        kei_portfolio_log_blog_action('Blog page creation exception', array('error' => $e->getMessage()), 'error');
    }
    
    return $results;
}

/**
 * WordPress表示設定の自動調整
 * 
 * @param int $blog_page_id ブログページID
 * @return array 設定結果
 */
function kei_portfolio_configure_blog_settings($blog_page_id) {
    $results = array(
        'updates' => array(),
        'errors' => array()
    );
    
    try {
        // 投稿ページとしてブログページを設定
        $current_page_for_posts = get_option('page_for_posts');
        if ($current_page_for_posts != $blog_page_id) {
            update_option('page_for_posts', $blog_page_id);
            $results['updates'][] = 'page_for_posts updated to: ' . $blog_page_id;
        }
        
        // ホームページ表示設定を「固定ページ」に変更
        $show_on_front = get_option('show_on_front');
        if ($show_on_front !== 'page') {
            update_option('show_on_front', 'page');
            $results['updates'][] = 'show_on_front set to: page';
        }
        
        // フロントページの設定を確認・設定
        $front_page_id = get_option('page_on_front');
        if (!$front_page_id) {
            // フロントページが設定されていない場合、適切なページを探して設定
            $front_page = kei_portfolio_find_front_page();
            if ($front_page) {
                update_option('page_on_front', $front_page->ID);
                $results['updates'][] = 'page_on_front set to: ' . $front_page->ID . ' (' . $front_page->post_title . ')';
            } else {
                $results['errors'][] = 'No suitable front page found';
            }
        }
        
        // 投稿の表示数を適切に設定
        $posts_per_page = get_option('posts_per_page');
        if ($posts_per_page < KEI_PORTFOLIO_MIN_POSTS_PER_PAGE || $posts_per_page > KEI_PORTFOLIO_MAX_POSTS_PER_PAGE) {
            update_option('posts_per_page', KEI_PORTFOLIO_DEFAULT_POSTS_PER_PAGE);
            $results['updates'][] = 'posts_per_page set to: ' . KEI_PORTFOLIO_DEFAULT_POSTS_PER_PAGE;
        }
        
        // RSS設定の確認
        $rss_use_excerpt = get_option('rss_use_excerpt');
        if (!$rss_use_excerpt) {
            update_option('rss_use_excerpt', 1);
            $results['updates'][] = 'RSS excerpt enabled';
        }
        
    } catch (Exception $e) {
        $results['errors'][] = 'Exception in blog settings configuration: ' . $e->getMessage();
    }
    
    return $results;
}

/**
 * 適切なフロントページを検索
 * 
 * @return WP_Post|null フロントページのPostオブジェクト
 */
function kei_portfolio_find_front_page() {
    $candidates = array('front-page', 'home', 'index', 'main');
    
    foreach ($candidates as $candidate) {
        $page = get_page_by_path($candidate);
        if ($page && $page->post_status === 'publish') {
            return $page;
        }
    }
    
    // 候補が見つからない場合、最も古い公開済みページを取得
    $pages = get_pages(array(
        'sort_column' => 'post_date',
        'sort_order' => 'ASC',
        'number' => 1,
        'post_status' => 'publish'
    ));
    
    return !empty($pages) ? $pages[0] : null;
}

/**
 * ブログ関連のアクションをログに記録
 * 
 * @param string $message ログメッセージ
 * @param array $data 追加データ
 * @param string $level ログレベル
 */
function kei_portfolio_log_blog_action($message, $data = array(), $level = 'info') {
    if (WP_DEBUG && WP_DEBUG_LOG) {
        $log_entry = array(
            'timestamp' => current_time('mysql'),
            'level' => $level,
            'message' => $message,
            'data' => $data,
            'user_id' => get_current_user_id(),
            'ip_address' => wp_hash($_SERVER['REMOTE_ADDR'] ?? 'unknown')
        );
        
        error_log('Kei Portfolio Blog: ' . wp_json_encode($log_entry));
    }
}

// テーマ有効化時に実行
add_action('after_switch_theme', 'kei_portfolio_create_blog_page');

// WordPress初期化完了後にも検証を実行
add_action('wp_loaded', 'kei_portfolio_delayed_blog_check', 20);

/**
 * 遅延ブログ設定チェック
 * WordPressが完全に初期化された後に実行
 */
function kei_portfolio_delayed_blog_check() {
    // 管理画面でのみ実行
    if (is_admin() && !wp_doing_ajax()) {
        kei_portfolio_validate_blog_settings();
    }
}

/**
 * ブログページ設定の検証と修正
 * 
 * 定期的にブログページの設定を確認し、
 * 必要に応じて修正を行う（1時間に1回のみ実行）
 */
function kei_portfolio_validate_blog_settings() {
    // transientを使用して1時間に1回のみ検証を実行
    if (get_transient('kei_portfolio_blog_validation_done')) {
        return;
    }
    
    // 投稿ページの設定を確認
    $page_for_posts = get_option('page_for_posts');
    
    if (!$page_for_posts) {
        // 投稿ページが設定されていない場合
        kei_portfolio_create_blog_page();
    } else {
        // 設定されているページが存在するか確認
        $blog_page = get_post($page_for_posts);
        if (!$blog_page || $blog_page->post_status !== 'publish') {
            // ページが存在しないか非公開の場合は再作成
            kei_portfolio_create_blog_page();
        }
    }
    
    // 1時間のtransientを設定
    set_transient('kei_portfolio_blog_validation_done', true, HOUR_IN_SECONDS);
}

// 管理画面の初期化時に設定を検証
add_action('admin_init', 'kei_portfolio_validate_blog_settings');

/**
 * ブログページへのリダイレクト
 * 
 * /blog へのアクセスを正しいブログページにリダイレクト
 */
function kei_portfolio_blog_redirect() {
    if (is_404()) {
        $request_uri = $_SERVER['REQUEST_URI'];
        
        // /blog へのアクセスの場合
        if (strpos($request_uri, '/blog') === 0) {
            $page_for_posts = get_option('page_for_posts');
            if ($page_for_posts) {
                $blog_url = get_permalink($page_for_posts);
                if ($blog_url) {
                    wp_redirect($blog_url, 301);
                    exit;
                }
            }
        }
    }
}
add_action('template_redirect', 'kei_portfolio_blog_redirect');

/**
 * ブログメニュー項目の自動追加
 */
function kei_portfolio_add_blog_to_menu($items, $args) {
    // プライマリメニューのみに追加
    if ($args->theme_location === 'primary') {
        $page_for_posts = get_option('page_for_posts');
        
        if ($page_for_posts) {
            // メニューにブログが含まれているか確認
            $blog_in_menu = false;
            foreach ($items as $item) {
                if ($item->object_id == $page_for_posts) {
                    $blog_in_menu = true;
                    break;
                }
            }
            
            // ブログがメニューにない場合は追加
            if (!$blog_in_menu) {
                $blog_page = get_post($page_for_posts);
                if ($blog_page) {
                    $items .= '<li class="menu-item menu-item-type-post_type menu-item-object-page">';
                    $items .= '<a href="' . get_permalink($page_for_posts) . '">Blog</a>';
                    $items .= '</li>';
                }
            }
        }
    }
    
    return $items;
}
// コメントアウト: 自動追加が不要な場合はこの行をコメント解除
// add_filter('wp_nav_menu_items', 'kei_portfolio_add_blog_to_menu', 10, 2);

/**
 * 管理画面にブログ設定の詳細通知を表示
 */
function kei_portfolio_blog_admin_notice() {
    // 権限チェック
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // 各種設定の状態をチェック
    $page_for_posts = get_option('page_for_posts');
    $show_on_front = get_option('show_on_front');
    $page_on_front = get_option('page_on_front');
    $permalink_structure = get_option('permalink_structure');
    
    $issues = array();
    
    // ブログページの設定チェック
    if (!$page_for_posts) {
        $issues[] = array(
            'type' => 'error',
            'message' => __('ブログページ（投稿ページ）が設定されていません。', 'kei-portfolio'),
            'action_text' => __('自動作成する', 'kei-portfolio'),
            'action_url' => wp_nonce_url(add_query_arg('kei_create_blog_page', '1'), 'kei_create_blog_page')
        );
    } else {
        // 設定されているページが存在するかチェック
        $blog_page = get_post($page_for_posts);
        if (!$blog_page) {
            $issues[] = array(
                'type' => 'error',
                'message' => __('設定されているブログページが存在しません。', 'kei-portfolio'),
                'action_text' => __('再作成する', 'kei-portfolio'),
                'action_url' => wp_nonce_url(add_query_arg('kei_create_blog_page', '1'), 'kei_create_blog_page')
            );
        } elseif ($blog_page->post_status !== 'publish') {
            $issues[] = array(
                'type' => 'warning',
                'message' => __('ブログページが公開されていません。', 'kei-portfolio'),
                'action_text' => __('公開する', 'kei-portfolio'),
                'action_url' => wp_nonce_url(add_query_arg('kei_publish_blog_page', $blog_page->ID), 'kei_publish_blog_page')
            );
        }
    }
    
    // 表示設定のチェック
    if ($show_on_front !== 'page') {
        $issues[] = array(
            'type' => 'warning',
            'message' => __('ホームページ表示が「最新の投稿」になっています。ポートフォリオサイトでは「固定ページ」を推奨します。', 'kei-portfolio'),
            'action_text' => __('表示設定を開く', 'kei-portfolio'),
            'action_url' => admin_url('options-reading.php')
        );
    }
    
    if ($show_on_front === 'page' && !$page_on_front) {
        $issues[] = array(
            'type' => 'warning',
            'message' => __('ホームページが設定されていません。', 'kei-portfolio'),
            'action_text' => __('表示設定を開く', 'kei-portfolio'),
            'action_url' => admin_url('options-reading.php')
        );
    }
    
    // 問題がある場合は通知を表示
    if (!empty($issues)) {
        foreach ($issues as $issue) {
            $notice_class = 'notice notice-' . $issue['type'] . ' is-dismissible';
            ?>
            <div class="<?php echo esc_attr($notice_class); ?>">
                <p><strong><?php _e('Kei Portfolio設定:', 'kei-portfolio'); ?></strong> <?php echo esc_html($issue['message']); ?></p>
                <p>
                    <a href="<?php echo esc_url($issue['action_url']); ?>" class="button button-primary"><?php echo esc_html($issue['action_text']); ?></a>
                    <a href="<?php echo admin_url('options-reading.php'); ?>" class="button button-secondary"><?php _e('表示設定を確認', 'kei-portfolio'); ?></a>
                </p>
            </div>
            <?php
        }
    }
    
    // 成功メッセージの表示
    if (isset($_GET['kei_blog_created']) && $_GET['kei_blog_created']) {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('ブログページが正常に作成・設定されました。', 'kei-portfolio'); ?></p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'kei_portfolio_blog_admin_notice');

/**
 * 管理画面からのブログページ作成リクエストの処理
 */
function kei_portfolio_handle_admin_blog_creation() {
    // ブログページ作成のリクエスト処理
    if (isset($_GET['kei_create_blog_page'])) {
        if (!isset($_GET['_wpnonce'])) {
            return;
        }
        if (!wp_verify_nonce($_GET['_wpnonce'], 'kei_create_blog_page')) {
            wp_die(__('セキュリティチェックに失敗しました。', 'kei-portfolio'));
        }
        if (current_user_can('manage_options')) {
            $result = kei_portfolio_create_blog_page();
            
            // 結果に基づいてリダイレクト
            $redirect_args = array('kei_blog_created' => 1);
            if (!empty($result['errors'])) {
                $redirect_args['kei_blog_errors'] = 1;
            }
            
            wp_redirect(add_query_arg($redirect_args, admin_url('options-reading.php')));
            exit;
        }
    }
    
    // ブログページ公開のリクエスト処理
    if (isset($_GET['kei_publish_blog_page'])) {
        if (!isset($_GET['_wpnonce'])) {
            return;
        }
        if (!wp_verify_nonce($_GET['_wpnonce'], 'kei_publish_blog_page')) {
            wp_die(__('セキュリティチェックに失敗しました。', 'kei-portfolio'));
        }
        if (current_user_can('manage_options')) {
            $page_id = intval($_GET['kei_publish_blog_page']);
            wp_update_post(array(
                'ID' => $page_id,
                'post_status' => 'publish'
            ));
            
            wp_redirect(add_query_arg('kei_blog_published', 1, admin_url('options-reading.php')));
            exit;
        }
    }
}
add_action('admin_init', 'kei_portfolio_handle_admin_blog_creation');

/**
 * パーマリンク設定の確認と警告（REST API対応強化版）
 */
function kei_portfolio_check_permalink_structure() {
    $permalink_structure = get_option('permalink_structure');
    
    // パーマリンクが基本設定の場合は警告
    if (empty($permalink_structure)) {
        add_action('admin_notices', 'kei_portfolio_permalink_warning_notice');
    }
    
    // 推奨パーマリンク構造でない場合も警告
    $recommended_structures = array('/%postname%/', '/%year%/%monthnum%/%postname%/', '/blog/%postname%/');
    if (!empty($permalink_structure) && !in_array($permalink_structure, $recommended_structures)) {
        add_action('admin_notices', 'kei_portfolio_permalink_recommendation_notice');
    }
}

/**
 * パーマリンク基本設定の警告通知
 */
function kei_portfolio_permalink_warning_notice() {
    if (current_user_can('manage_options')) {
        ?>
        <div class="notice notice-error is-dismissible" data-dismissible="permalink-basic-warning">
            <h3><?php _e('重要: パーマリンク設定の問題', 'kei-portfolio'); ?></h3>
            <p><?php _e('現在のパーマリンク設定が「基本」になっています。これにより以下の問題が発生する可能性があります:', 'kei-portfolio'); ?></p>
            <ul>
                <li><?php _e('WordPress REST APIが正常に動作しない', 'kei-portfolio'); ?></li>
                <li><?php _e('Gutenbergエディターでエラーが発生する', 'kei-portfolio'); ?></li>
                <li><?php _e('プラグインの一部機能が制限される', 'kei-portfolio'); ?></li>
                <li><?php _e('SEO効果が低下する', 'kei-portfolio'); ?></li>
            </ul>
            <p>
                <a href="<?php echo admin_url('options-permalink.php'); ?>" class="button button-primary"><?php _e('今すぐ修正する', 'kei-portfolio'); ?></a>
                <button type="button" class="button button-secondary" onclick="keiPortfolioDismissNotice('permalink-basic-warning')"><?php _e('後で修正する', 'kei-portfolio'); ?></button>
            </p>
        </div>
        <?php
    }
}

/**
 * パーマリンク推奨設定の通知
 */
function kei_portfolio_permalink_recommendation_notice() {
    if (current_user_can('manage_options')) {
        $current_structure = get_option('permalink_structure');
        ?>
        <div class="notice notice-info is-dismissible" data-dismissible="permalink-recommendation">
            <h4><?php _e('パーマリンク設定の推奨事項', 'kei-portfolio'); ?></h4>
            <p><?php printf(__('現在の設定: %s', 'kei-portfolio'), '<code>' . esc_html($current_structure) . '</code>'); ?></p>
            <p><?php _e('より良いSEO効果とユーザビリティのため、以下の設定を推奨します:', 'kei-portfolio'); ?></p>
            <ul>
                <li><strong><?php _e('投稿名', 'kei-portfolio'); ?></strong>: <code>/%postname%/</code> <?php _e('(最も推奨)', 'kei-portfolio'); ?></li>
                <li><strong><?php _e('日付と投稿名', 'kei-portfolio'); ?></strong>: <code>/%year%/%monthnum%/%postname%/</code></li>
            </ul>
            <p><a href="<?php echo admin_url('options-permalink.php'); ?>" class="button button-secondary"><?php _e('設定を確認する', 'kei-portfolio'); ?></a></p>
        </div>
        <?php
    }
}

/**
 * パーマリンク設定確認と即座の警告表示
 */
function kei_portfolio_check_and_warn_permalink() {
    $permalink_structure = get_option('permalink_structure');
    
    if (empty($permalink_structure)) {
        // セッション通知として警告を保存
        set_transient('kei_portfolio_permalink_warning', 1, 300); // 5分間
    }
}

/**
 * 管理画面用のJavaScript（通知の非表示機能）
 */
function kei_portfolio_admin_notice_scripts() {
    wp_enqueue_script('kei-portfolio-admin', '', array('jquery'), '1.0.0', true);
    wp_localize_script('kei-portfolio-admin', 'keiPortfolioAdmin', array(
        'nonce' => wp_create_nonce('kei_portfolio_dismiss_notice'),
        'ajaxurl' => admin_url('admin-ajax.php')
    ));
    ?>
    <script type="text/javascript">
    function keiPortfolioDismissNotice(noticeType) {
        var notice = document.querySelector('[data-dismissible="' + noticeType + '"]');
        if (notice) {
            notice.style.display = 'none';
            // Ajax で非表示状態を保存
            jQuery.post(keiPortfolioAdmin.ajaxurl, {
                action: 'kei_portfolio_dismiss_notice',
                notice_type: noticeType,
                nonce: keiPortfolioAdmin.nonce
            });
        }
    }
    </script>
    <?php
}
add_action('admin_footer', 'kei_portfolio_admin_notice_scripts');

/**
 * Ajax: 管理通知の非表示処理
 */
function kei_portfolio_dismiss_notice_handler() {
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'kei_portfolio_dismiss_notice')) {
        wp_die('Security check failed');
    }
    
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }
    
    $notice_type = sanitize_text_field($_POST['notice_type'] ?? '');
    if ($notice_type) {
        update_user_meta(get_current_user_id(), 'kei_portfolio_dismissed_' . $notice_type, 1);
    }
    
    wp_die(); // Ajax終了
}
add_action('wp_ajax_kei_portfolio_dismiss_notice', 'kei_portfolio_dismiss_notice_handler');

add_action('admin_init', 'kei_portfolio_check_permalink_structure');
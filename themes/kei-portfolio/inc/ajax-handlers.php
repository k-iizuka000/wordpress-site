<?php
/**
 * AJAX処理ハンドラー
 *
 * @package Kei_Portfolio
 */

// SecurityHelperクラスを読み込み
require_once get_template_directory() . '/inc/class-security-helper.php';
use KeiPortfolio\Security\SecurityHelper;

/**
 * Contact Form AJAX Handler
 */
function kei_portfolio_handle_contact_form() {
    // Nonce verification
    if ( ! wp_verify_nonce( $_POST['contact_nonce'], 'kei_portfolio_contact' ) ) {
        wp_send_json_error( 'セキュリティエラーが発生しました。' );
    }

    // Sanitize form data
    $name = sanitize_text_field( $_POST['contact_name'] );
    $email = sanitize_email( $_POST['contact_email'] );
    $subject = sanitize_text_field( $_POST['contact_subject'] );
    $message = sanitize_textarea_field( $_POST['contact_message'] );
    $privacy_agreement = isset( $_POST['privacy_agreement'] ) ? true : false;

    // Basic validation
    if ( empty( $name ) || empty( $email ) || empty( $subject ) || empty( $message ) ) {
        wp_send_json_error( '必須項目が入力されていません。' );
    }

    if ( ! is_email( $email ) ) {
        wp_send_json_error( 'メールアドレスの形式が正しくありません。' );
    }

    if ( ! $privacy_agreement ) {
        wp_send_json_error( 'プライバシーポリシーへの同意が必要です。' );
    }

    if ( strlen( $message ) > 2000 ) {
        wp_send_json_error( 'メッセージは2000文字以内で入力してください。' );
    }

    // Prepare email content
    $admin_email = get_option( 'admin_email' );
    $site_name = get_bloginfo( 'name' );
    $email_subject = sprintf( '[%s] お問い合わせ: %s', $site_name, $subject );
    
    $email_message = "お問い合わせフォームからメッセージが送信されました。\n\n";
    $email_message .= "お名前: " . $name . "\n";
    $email_message .= "メールアドレス: " . $email . "\n";
    $email_message .= "件名: " . $subject . "\n";
    $email_message .= "メッセージ:\n" . $message . "\n\n";
    $email_message .= "送信日時: " . current_time( 'Y-m-d H:i:s' ) . "\n";
    // セキュリティ強化: IPアドレスをハッシュ化してプライバシーを保護
    $email_message .= "送信者識別子: " . SecurityHelper::hash_ip() . "\n";

    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'Reply-To: ' . $name . ' <' . $email . '>',
        'From: ' . $site_name . ' <noreply@' . $_SERVER['SERVER_NAME'] . '>'
    );

    // Send email
    $mail_sent = wp_mail( $admin_email, $email_subject, $email_message, $headers );

    if ( $mail_sent ) {
        // セキュリティログ（プライバシー保護版）
        SecurityHelper::log_security_event(
            'Contact form submission successful',
            'info',
            array(
                'email_domain' => substr($email, strpos($email, '@')),  // ドメインのみ記録
                'subject_length' => strlen($subject),
                'message_length' => strlen($message)
            )
        );
        
        wp_send_json_success( 'お問い合わせを受け付けました。24時間以内にご返信いたします。' );
    } else {
        // セキュリティログ（エラー時もプライバシー保護）
        SecurityHelper::log_security_event(
            'Contact form email send failed',
            'error',
            array(
                'email_domain' => substr($email, strpos($email, '@')),  // ドメインのみ記録
                'subject_length' => strlen($subject)
            )
        );
        
        wp_send_json_error( 'メールの送信に失敗しました。しばらく時間をおいて再度お試しください。' );
    }
}

/**
 * ブログ用AJAX処理 - セキュリティ強化版
 */

/**
 * セキュリティ検証を統一したヘルパー関数
 */
function kei_portfolio_verify_blog_ajax_security($action_name = 'kei_portfolio_ajax') {
    // Nonceの検証（POST・GET両方対応）
    $nonce = '';
    if (isset($_POST['nonce'])) {
        $nonce = sanitize_text_field($_POST['nonce']);
    } elseif (isset($_GET['nonce'])) {
        $nonce = sanitize_text_field($_GET['nonce']);
    }
    
    if (empty($nonce) || !wp_verify_nonce($nonce, $action_name)) {
        wp_send_json_error(array(
            'message' => __('セキュリティ検証に失敗しました。ページを再読み込みしてください。', 'kei-portfolio'),
            'error_code' => 'invalid_nonce'
        ));
        return false;
    }
    
    // カスタムヘッダーの検証（CSRF対策）
    if (!empty($_SERVER['HTTP_X_KEIPORTFOLIO_REQUEST']) && $_SERVER['HTTP_X_KEIPORTFOLIO_REQUEST'] !== 'blog-ajax') {
        wp_send_json_error(array(
            'message' => __('不正なリクエストです。', 'kei-portfolio'),
            'error_code' => 'invalid_request'
        ));
        return false;
    }
    
    // リファラーチェック（追加のセキュリティ層）
    if (!empty($_SERVER['HTTP_REFERER'])) {
        $referer_host = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
        $site_host = parse_url(home_url(), PHP_URL_HOST);
        
        if ($referer_host !== $site_host) {
            wp_send_json_error(array(
                'message' => __('不正なリファラーです。', 'kei-portfolio'),
                'error_code' => 'invalid_referer'
            ));
            return false;
        }
    }
    
    return true;
}

/**
 * 無限スクロール用投稿読み込みAJAXハンドラー
 */
function kei_portfolio_load_more_posts() {
    // セキュリティ検証
    if (!kei_portfolio_verify_blog_ajax_security()) {
        return;
    }
    
    // パラメータの取得とサニタイズ
    $page = isset($_POST['page']) ? absint($_POST['page']) : 1;
    $posts_per_page = isset($_POST['posts_per_page']) ? absint($_POST['posts_per_page']) : 9;
    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
    $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    $tag = isset($_POST['tag']) ? sanitize_text_field($_POST['tag']) : '';
    
    // バリデーション
    if ($page < 1 || $page > 100) { // ページ数の上限設定
        wp_send_json_error(array(
            'message' => __('無効なページ番号です。', 'kei-portfolio'),
            'error_code' => 'invalid_page'
        ));
        return;
    }
    
    if ($posts_per_page < 1 || $posts_per_page > 50) { // 投稿数の上限設定
        wp_send_json_error(array(
            'message' => __('無効な投稿数です。', 'kei-portfolio'),
            'error_code' => 'invalid_posts_per_page'
        ));
        return;
    }
    
    // クエリ引数の構築
    $args = array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'paged' => $page,
        'posts_per_page' => $posts_per_page,
        'no_found_rows' => false, // ページネーション用
    );
    
    // カテゴリーフィルター
    if (!empty($category) && $category !== 'all') {
        $args['category_name'] = $category;
    }
    
    // 検索クエリ
    if (!empty($search)) {
        $args['s'] = $search;
    }
    
    // タグフィルター
    if (!empty($tag)) {
        $args['tag'] = $tag;
    }
    
    // クエリ実行
    $query = new WP_Query($args);
    
    if ($query->have_posts()) {
        ob_start();
        
        while ($query->have_posts()) {
            $query->the_post();
            
            // 投稿カードのテンプレートを読み込み
            get_template_part('template-parts/blog/post-card');
        }
        
        $html = ob_get_clean();
        wp_reset_postdata();
        
        // レスポンスデータ
        wp_send_json_success(array(
            'html' => $html,
            'current_page' => $page,
            'max_pages' => $query->max_num_pages,
            'posts_count' => $query->post_count,
            'found_posts' => $query->found_posts,
        ));
    } else {
        wp_send_json_error(array(
            'message' => __('投稿が見つかりませんでした。', 'kei-portfolio'),
            'error_code' => 'no_posts_found'
        ));
    }
    
    wp_reset_postdata();
}

/**
 * インスタント検索AJAXハンドラー
 */
function kei_portfolio_blog_instant_search() {
    // セキュリティ検証
    if (!kei_portfolio_verify_blog_ajax_security()) {
        return;
    }
    
    // 検索クエリの取得とサニタイズ
    $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
    
    // バリデーション
    if (strlen($query) < 2) {
        wp_send_json_error(array(
            'message' => __('検索キーワードは2文字以上で入力してください。', 'kei-portfolio'),
            'error_code' => 'query_too_short'
        ));
        return;
    }
    
    if (strlen($query) > 100) {
        wp_send_json_error(array(
            'message' => __('検索キーワードは100文字以内で入力してください。', 'kei-portfolio'),
            'error_code' => 'query_too_long'
        ));
        return;
    }
    
    // 検索実行
    $args = array(
        'post_type' => 'post',
        'post_status' => 'publish',
        's' => $query,
        'posts_per_page' => 10,
        'no_found_rows' => true,
    );
    
    $search_query = new WP_Query($args);
    $results = array();
    
    if ($search_query->have_posts()) {
        while ($search_query->have_posts()) {
            $search_query->the_post();
            
            $results[] = array(
                'id' => get_the_ID(),
                'title' => get_the_title(),
                'url' => get_permalink(),
                'excerpt' => wp_trim_words(get_the_excerpt(), 15, '...'),
                'date' => get_the_date(),
                'thumbnail' => has_post_thumbnail() ? get_the_post_thumbnail_url(null, 'thumbnail') : '',
            );
        }
        wp_reset_postdata();
        
        wp_send_json_success($results);
    } else {
        wp_send_json_success(array());
    }
}

/**
 * シェア数取得AJAXハンドラー
 */
function kei_portfolio_get_share_counts() {
    // セキュリティ検証
    if (!kei_portfolio_verify_blog_ajax_security()) {
        return;
    }
    
    $url = isset($_POST['url']) ? esc_url_raw($_POST['url']) : '';
    
    if (empty($url)) {
        wp_send_json_error(array(
            'message' => __('URLが指定されていません。', 'kei-portfolio'),
            'error_code' => 'missing_url'
        ));
        return;
    }
    
    // URLの検証（自サイトのURLかチェック）
    $site_url = home_url();
    if (strpos($url, $site_url) !== 0) {
        wp_send_json_error(array(
            'message' => __('無効なURLです。', 'kei-portfolio'),
            'error_code' => 'invalid_url'
        ));
        return;
    }
    
    // シェア数の取得（簡単な実装例）
    $post_id = url_to_postid($url);
    $share_count = 0;
    
    if ($post_id) {
        $share_count = get_post_meta($post_id, '_share_count', true);
        $share_count = $share_count ? absint($share_count) : 0;
    }
    
    wp_send_json_success(array(
        'total' => $share_count,
        'post_id' => $post_id,
    ));
}

/**
 * シェア追跡AJAXハンドラー
 */
function kei_portfolio_track_share() {
    // セキュリティ検証
    if (!kei_portfolio_verify_blog_ajax_security()) {
        return;
    }
    
    $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';
    $url = isset($_POST['url']) ? esc_url_raw($_POST['url']) : '';
    
    // バリデーション
    $allowed_types = array('facebook', 'twitter', 'linkedin', 'email', 'copy');
    if (!in_array($type, $allowed_types)) {
        wp_send_json_error(array(
            'message' => __('無効なシェアタイプです。', 'kei-portfolio'),
            'error_code' => 'invalid_share_type'
        ));
        return;
    }
    
    if (empty($url)) {
        wp_send_json_error(array(
            'message' => __('URLが指定されていません。', 'kei-portfolio'),
            'error_code' => 'missing_url'
        ));
        return;
    }
    
    // シェア数の更新
    $post_id = url_to_postid($url);
    if ($post_id) {
        $current_count = get_post_meta($post_id, '_share_count', true);
        $current_count = $current_count ? absint($current_count) : 0;
        $new_count = $current_count + 1;
        
        update_post_meta($post_id, '_share_count', $new_count);
        
        // タイプ別シェア数も記録
        $type_key = '_share_count_' . $type;
        $type_count = get_post_meta($post_id, $type_key, true);
        $type_count = $type_count ? absint($type_count) : 0;
        update_post_meta($post_id, $type_key, $type_count + 1);
        
        wp_send_json_success(array(
            'message' => __('シェアを記録しました。', 'kei-portfolio'),
            'total_shares' => $new_count,
        ));
    } else {
        wp_send_json_error(array(
            'message' => __('投稿が見つかりません。', 'kei-portfolio'),
            'error_code' => 'post_not_found'
        ));
    }
}

/**
 * パフォーマンス統計送信AJAXハンドラー
 */
function kei_portfolio_blog_ajax_performance() {
    // セキュリティ検証
    if (!kei_portfolio_verify_blog_ajax_security()) {
        return;
    }
    
    $data = isset($_POST['data']) ? $_POST['data'] : array();
    
    // データのサニタイズ
    $performance_data = array(
        'requestCount' => isset($data['requestCount']) ? absint($data['requestCount']) : 0,
        'totalLoadTime' => isset($data['totalLoadTime']) ? floatval($data['totalLoadTime']) : 0,
        'failedRequests' => isset($data['failedRequests']) ? absint($data['failedRequests']) : 0,
        'avgLoadTime' => isset($data['avgLoadTime']) ? floatval($data['avgLoadTime']) : 0,
        'successRate' => isset($data['successRate']) ? floatval($data['successRate']) : 0,
    );
    
    // パフォーマンス統計ログ（プライバシー保護版）
    SecurityHelper::log_security_event(
        'Blog Ajax Performance Statistics',
        'info',
        $performance_data
    );
    
    wp_send_json_success(array(
        'message' => __('パフォーマンス統計を記録しました。', 'kei-portfolio'),
    ));
}

// Register AJAX handlers
add_action( 'wp_ajax_kei_portfolio_contact_submit', 'kei_portfolio_handle_contact_form' );
add_action( 'wp_ajax_nopriv_kei_portfolio_contact_submit', 'kei_portfolio_handle_contact_form' );

// ブログAJAXハンドラーの登録
add_action('wp_ajax_load_more_posts', 'kei_portfolio_load_more_posts');
add_action('wp_ajax_nopriv_load_more_posts', 'kei_portfolio_load_more_posts');

add_action('wp_ajax_blog_instant_search', 'kei_portfolio_blog_instant_search');
add_action('wp_ajax_nopriv_blog_instant_search', 'kei_portfolio_blog_instant_search');

add_action('wp_ajax_get_share_counts', 'kei_portfolio_get_share_counts');
add_action('wp_ajax_nopriv_get_share_counts', 'kei_portfolio_get_share_counts');

add_action('wp_ajax_track_share', 'kei_portfolio_track_share');
add_action('wp_ajax_nopriv_track_share', 'kei_portfolio_track_share');

add_action('wp_ajax_blog_ajax_performance', 'kei_portfolio_blog_ajax_performance');
add_action('wp_ajax_nopriv_blog_ajax_performance', 'kei_portfolio_blog_ajax_performance');
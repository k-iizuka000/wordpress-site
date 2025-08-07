<?php
/**
 * 固定ページ自動作成機能
 *
 * @package Kei_Portfolio
 */

/**
 * 固定ページの作成設定
 */
function kei_portfolio_get_page_configurations() {
    return array(
        'about' => array(
            'title'    => 'About',
            'slug'     => 'about',
            'content'  => '<h2>About Me</h2><p>私についてのページです。こちらでは私の経歴、スキル、経験について詳しく説明します。</p>',
            'template' => 'page-about.php',
            'menu_order' => 1,
            'meta_description' => '10年以上のシステム開発経験を持つフルスタックエンジニアKei Aokikiについて。',
        ),
        'skills' => array(
            'title'    => 'Skills',
            'slug'     => 'skills',
            'content'  => '<h2>Technical Skills</h2><p>私の技術スキルとエンジニアとしての専門性をご紹介します。</p>',
            'template' => 'page-skills.php',
            'menu_order' => 2,
            'meta_description' => 'Java/Spring Boot、JavaScript、React、PHPなど幅広い技術スキルをご紹介。',
        ),
        'contact' => array(
            'title'    => 'Contact',
            'slug'     => 'contact',
            'content'  => '<h2>Contact Me</h2><p>お仕事のご相談、お問い合わせは以下のフォームからお気軽にご連絡ください。</p>',
            'template' => 'page-contact.php',
            'menu_order' => 4,
            'meta_description' => 'お仕事のご相談・お問い合わせはこちらから。迅速にご対応いたします。',
        ),
        'portfolio' => array(
            'title'    => 'Portfolio',
            'slug'     => 'portfolio',
            'content'  => '<h2>My Portfolio</h2><p>これまでに手がけたプロジェクトや作品をご紹介します。</p>',
            'template' => 'page-portfolio.php',
            'menu_order' => 3,
            'meta_description' => '実績あるシステム開発プロジェクトとポートフォリオをご紹介。',
        ),
    );
}

/**
 * 固定ページを作成する関数
 *
 * @param array $page_config ページ設定配列
 * @return int|WP_Error ページIDまたはエラー
 */
function kei_portfolio_create_page( $page_config ) {
    // ページが既に存在するかチェック
    $existing_page = get_page_by_path( $page_config['slug'] );
    if ( $existing_page ) {
        return $existing_page->ID;
    }

    // ページデータの準備
    $page_data = array(
        'post_title'     => sanitize_text_field( $page_config['title'] ),
        'post_content'   => wp_kses_post( $page_config['content'] ),
        'post_status'    => 'publish',
        'post_type'      => 'page',
        'post_name'      => sanitize_title( $page_config['slug'] ),
        'menu_order'     => isset( $page_config['menu_order'] ) ? absint( $page_config['menu_order'] ) : 0,
        'comment_status' => 'closed',
        'ping_status'    => 'closed',
    );

    // ページの作成
    $page_id = wp_insert_post( $page_data, true );

    if ( is_wp_error( $page_id ) ) {
        return $page_id;
    }

    // テンプレートの設定
    if ( isset( $page_config['template'] ) && ! empty( $page_config['template'] ) ) {
        update_post_meta( $page_id, '_wp_page_template', $page_config['template'] );
    }

    // SEO用のメタディスクリプションを設定
    if ( isset( $page_config['meta_description'] ) ) {
        update_post_meta( $page_id, '_kei_meta_description', sanitize_text_field( $page_config['meta_description'] ) );
    }

    return $page_id;
}

/**
 * 全ての固定ページを作成
 *
 * @return array 作成結果の配列
 */
function kei_portfolio_create_all_pages() {
    $page_configurations = kei_portfolio_get_page_configurations();
    $results = array();
    $errors = array();

    foreach ( $page_configurations as $key => $config ) {
        $result = kei_portfolio_create_page( $config );
        
        if ( is_wp_error( $result ) ) {
            $errors[ $key ] = $result->get_error_message();
        } else {
            $results[ $key ] = array(
                'id'      => $result,
                'title'   => $config['title'],
                'slug'    => $config['slug'],
                'status'  => 'created',
            );
        }
    }

    // ログの記録
    $log_message = 'Fixed pages creation attempted: ' . count( $page_configurations ) . ' pages';
    if ( ! empty( $results ) ) {
        $log_message .= ', ' . count( $results ) . ' successful';
    }
    if ( ! empty( $errors ) ) {
        $log_message .= ', ' . count( $errors ) . ' errors';
    }

    error_log( '[Kei Portfolio] ' . $log_message );

    if ( ! empty( $errors ) ) {
        error_log( '[Kei Portfolio] Page creation errors: ' . wp_json_encode( $errors ) );
    }

    return array(
        'success' => $results,
        'errors'  => $errors,
    );
}

/**
 * Primary Navigationメニューにページを追加
 *
 * @param array $page_results 作成されたページの情報
 */
function kei_portfolio_add_pages_to_menu( $page_results ) {
    // プライマリメニューの取得または作成
    $menu_name = 'Primary Navigation';
    $menu = wp_get_nav_menu_object( $menu_name );
    
    if ( ! $menu ) {
        // メニューが存在しない場合は作成
        $menu_id = wp_create_nav_menu( $menu_name );
        if ( is_wp_error( $menu_id ) ) {
            error_log( '[Kei Portfolio] Failed to create primary menu: ' . $menu_id->get_error_message() );
            return false;
        }
        $menu = wp_get_nav_menu_object( $menu_id );
        
        // プライマリメニューの位置に設定
        $locations = get_theme_mod( 'nav_menu_locations' );
        $locations['primary'] = $menu_id;
        set_theme_mod( 'nav_menu_locations', $locations );
    }

    // 既存のメニュー項目を取得
    $existing_items = wp_get_nav_menu_items( $menu->term_id );
    $existing_slugs = array();
    
    if ( $existing_items ) {
        foreach ( $existing_items as $item ) {
            if ( $item->object === 'page' ) {
                $page = get_post( $item->object_id );
                if ( $page ) {
                    $existing_slugs[] = $page->post_name;
                }
            }
        }
    }

    // ページ設定を取得してメニューに追加
    $page_configurations = kei_portfolio_get_page_configurations();
    
    foreach ( $page_results['success'] as $key => $page_info ) {
        // 既にメニューに存在するかチェック
        if ( in_array( $page_info['slug'], $existing_slugs ) ) {
            continue;
        }

        $menu_item_data = array(
            'menu-item-title'     => $page_info['title'],
            'menu-item-object-id' => $page_info['id'],
            'menu-item-object'    => 'page',
            'menu-item-status'    => 'publish',
            'menu-item-type'      => 'post_type',
            'menu-item-position'  => isset( $page_configurations[ $key ]['menu_order'] ) 
                                     ? $page_configurations[ $key ]['menu_order'] : 0,
        );

        $menu_item_id = wp_update_nav_menu_item( $menu->term_id, 0, $menu_item_data );
        
        if ( is_wp_error( $menu_item_id ) ) {
            error_log( '[Kei Portfolio] Failed to add menu item for page: ' . $page_info['title'] );
        }
    }

    return true;
}

/**
 * テーマアクティベーション時の固定ページ作成
 */
function kei_portfolio_create_pages_on_activation() {
    // 既に実行済みかチェック
    $pages_created = get_option( 'kei_portfolio_pages_created', false );
    if ( $pages_created ) {
        return;
    }

    // 固定ページの作成
    $creation_results = kei_portfolio_create_all_pages();
    
    // メニューへの追加
    if ( ! empty( $creation_results['success'] ) ) {
        kei_portfolio_add_pages_to_menu( $creation_results );
    }

    // フラグを設定して再実行を防ぐ
    update_option( 'kei_portfolio_pages_created', true );
    
    // 作成結果をオプションとして保存（デバッグ用）
    update_option( 'kei_portfolio_page_creation_log', $creation_results );
}

/**
 * 手動でページ作成を実行する関数（管理画面用）
 */
function kei_portfolio_manual_create_pages() {
    // 管理者権限チェック
    if ( ! current_user_can( 'manage_options' ) ) {
        return false;
    }

    // 固定ページの作成
    $creation_results = kei_portfolio_create_all_pages();
    
    // メニューへの追加
    if ( ! empty( $creation_results['success'] ) ) {
        kei_portfolio_add_pages_to_menu( $creation_results );
    }

    return $creation_results;
}

// テーマアクティベーション時のフック
add_action( 'after_switch_theme', 'kei_portfolio_create_pages_on_activation' );
<?php
/**
 * スクリプトとスタイルの登録・エンキュー
 *
 * @package Kei_Portfolio
 */

/**
 * スクリプトとスタイルの登録・エンキュー
 */
function kei_portfolio_pro_scripts() {
    // メインスタイルシート
    wp_enqueue_style( 
        'kei-portfolio-style', 
        get_stylesheet_uri(), 
        array(), 
        wp_get_theme()->get( 'Version' ) 
    );

    // Google Fonts - Reactのlayout.tsxから移行
    wp_enqueue_style( 
        'kei-portfolio-fonts', 
        'https://fonts.googleapis.com/css2?family=Pacifico:wght@400&family=Noto+Sans+JP:wght@400;500;700;900&display=swap', 
        array(), 
        null 
    );
    
    // Remix Icon - Reactで使用していたアイコンフォント
    wp_enqueue_style( 
        'remixicon', 
        'https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css', 
        array(), 
        '3.5.0' 
    );

    // ナビゲーションJavaScript（ReactのuseStateから変換）
    wp_enqueue_script( 
        'kei-portfolio-navigation', 
        get_template_directory_uri() . '/assets/js/navigation.js', 
        array(), 
        wp_get_theme()->get( 'Version' ), 
        true 
    );

    // メインJavaScript
    wp_enqueue_script( 
        'kei-portfolio-script', 
        get_template_directory_uri() . '/assets/js/main.js', 
        array('kei-portfolio-navigation'), 
        wp_get_theme()->get( 'Version' ), 
        true 
    );

    // ローカライズ（AJAXなど用）
    wp_localize_script( 'kei-portfolio-script', 'keiPortfolio', array(
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'kei-portfolio-nonce' ),
    ) );

    // コメント返信スクリプト
    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}
add_action( 'wp_enqueue_scripts', 'kei_portfolio_pro_scripts' );

/**
 * Enqueue Contact Form Scripts
 */
function kei_portfolio_contact_scripts() {
    if ( is_page_template( 'page-templates/template-contact.php' ) || is_page( 'contact' ) ) {
        // Localize script for AJAX
        wp_localize_script( 'kei-portfolio-script', 'kei_portfolio_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'kei_portfolio_contact_nonce' )
        ) );
    }
}
add_action( 'wp_enqueue_scripts', 'kei_portfolio_contact_scripts', 11 );
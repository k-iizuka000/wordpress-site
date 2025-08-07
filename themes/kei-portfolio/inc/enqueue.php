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

    // Tailwind CSS CDN（修正: JavaScript版として読み込み）
    // 修正前（2025-08-07）:
    // wp_enqueue_style( 'tailwindcss', 'https://cdn.tailwindcss.com', array(), '3.4.0' );
    wp_enqueue_script( 
        'tailwindcss', 
        'https://cdn.tailwindcss.com', 
        array(), 
        '3.4.0', 
        false // headタグ内で読み込む
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
 * Tailwind CSS基本設定の追加（フェーズ2実装）
 */
function kei_portfolio_tailwind_config() {
    ?>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    },
                    fontFamily: {
                        'noto': ['Noto Sans JP', 'sans-serif'],
                        'pacifico': ['Pacifico', 'cursive'],
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.5s ease-out',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(20px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' },
                        },
                    },
                }
            }
        }
    </script>
    <?php
}
add_action( 'wp_head', 'kei_portfolio_tailwind_config', 5 );

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
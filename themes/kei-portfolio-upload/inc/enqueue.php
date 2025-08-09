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

    // Tailwind CSS CDN（JavaScript版として読み込み、優先度高）
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

    // ブログ用スタイルシートの条件付き読み込み
    if ( is_home() || is_archive() || is_single() || is_category() || is_tag() || is_date() || is_author() || is_search() ) {
        // ブログ基本スタイル
        wp_enqueue_style( 
            'kei-portfolio-blog', 
            get_template_directory_uri() . '/assets/css/blog.css', 
            array( 'kei-portfolio-style' ), 
            wp_get_theme()->get( 'Version' ) 
        );

        // ブログモバイル最適化スタイル
        wp_enqueue_style( 
            'kei-portfolio-blog-mobile', 
            get_template_directory_uri() . '/assets/css/blog-mobile.css', 
            array( 'kei-portfolio-blog' ), 
            wp_get_theme()->get( 'Version' ) 
        );

        // ブログメイン機能 JavaScript
        wp_enqueue_script( 
            'kei-portfolio-blog', 
            get_template_directory_uri() . '/assets/js/blog.js', 
            array( 'jquery', 'kei-portfolio-script' ), 
            wp_get_theme()->get( 'Version' ), 
            true 
        );

        // 共通ユーティリティクラス（依存関係の最上位）
        wp_enqueue_script( 
            'kei-portfolio-utils', 
            get_template_directory_uri() . '/assets/js/utils.js', 
            array(), 
            wp_get_theme()->get( 'Version' ), 
            true 
        );

        // ブログAjax機能 JavaScript
        wp_enqueue_script( 
            'kei-portfolio-blog-ajax', 
            get_template_directory_uri() . '/assets/js/blog-ajax.js', 
            array( 'jquery', 'kei-portfolio-blog', 'kei-portfolio-utils' ), 
            wp_get_theme()->get( 'Version' ), 
            true 
        );

        // セキュアブログマネージャー JavaScript
        wp_enqueue_script( 
            'secure-blog', 
            get_template_directory_uri() . '/assets/js/secure-blog.js', 
            array( 'kei-portfolio-blog-ajax', 'kei-portfolio-utils' ), 
            wp_get_theme()->get( 'Version' ), 
            true 
        );

        // ブログ機能共通のローカライズデータ
        $blog_localize_data = array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'kei_portfolio_ajax' ),
            'loadMoreNonce' => wp_create_nonce( 'load_more_posts' ),
            'current_page' => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1,
            'max_pages' => $GLOBALS['wp_query']->max_num_pages ?? 1,
            // Ajax リトライ設定（フィルターフックでカスタマイズ可能）
            'retryConfig' => array(
                'maxRetries' => apply_filters('kei_portfolio_ajax_max_retries', 3),
                'retryDelay' => apply_filters('kei_portfolio_ajax_retry_delay', 1000),
                'exponentialBackoff' => apply_filters('kei_portfolio_ajax_exponential_backoff', true),
                'timeoutMs' => apply_filters('kei_portfolio_ajax_timeout', 15000),
            ),
            // パフォーマンス設定
            'performance' => array(
                'memoryOptimization' => apply_filters('kei_portfolio_memory_optimization', true),
                'debounceDelay' => apply_filters('kei_portfolio_debounce_delay', 300),
                'throttleLimit' => apply_filters('kei_portfolio_throttle_limit', 100),
                'cleanupInterval' => apply_filters('kei_portfolio_cleanup_interval', 300000), // 5分
            ),
            'security' => array(
                'enabled' => true,
                'xssProtection' => true,
                'csrfProtection' => true,
                'inputValidation' => true
            ),
            'texts' => array(
                'loading' => __('読み込み中...', 'kei-portfolio'),
                'error' => __('エラーが発生しました', 'kei-portfolio'),
                'success' => __('処理が完了しました', 'kei-portfolio'),
                'invalidInput' => __('入力内容に誤りがあります', 'kei-portfolio'),
                'securityError' => __('セキュリティエラーが発生しました', 'kei-portfolio'),
                'noMorePosts' => __('これ以上の投稿はありません', 'kei-portfolio'),
                'loadMore' => __('さらに読み込む', 'kei-portfolio'),
                'retry' => __('再試行', 'kei-portfolio')
            )
        );

        // 各スクリプトにローカライズデータを設定
        wp_localize_script( 'kei-portfolio-blog', 'blogAjax', $blog_localize_data );
        wp_localize_script( 'kei-portfolio-blog-ajax', 'blogAjax', $blog_localize_data );
        wp_localize_script( 'secure-blog', 'keiPortfolioAjax', $blog_localize_data );
        
        // 検索ページ専用のスタイルとスクリプト
        if ( is_search() ) {
            // 検索ページ用スタイル
            wp_enqueue_style( 
                'kei-portfolio-search-styles', 
                get_template_directory_uri() . '/assets/css/search.css', 
                array( 'kei-portfolio-blog' ), 
                wp_get_theme()->get( 'Version' ) 
            );
            
            // 検索ページ用JavaScript
            wp_enqueue_script( 
                'kei-portfolio-search', 
                get_template_directory_uri() . '/assets/js/search.js', 
                array( 'kei-portfolio-script' ), 
                wp_get_theme()->get( 'Version' ), 
                true 
            );
            
            // 検索ページ用のローカライズデータ
            wp_localize_script( 'kei-portfolio-search', 'keiSearchData', array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'kei-search-nonce' ),
                'searchQuery' => get_search_query(),
                'resultsCount' => $GLOBALS['wp_query']->found_posts ?? 0,
                'currentView' => isset($_GET['view']) && in_array($_GET['view'], ['grid', 'list']) ? $_GET['view'] : 'list',
                'texts' => array(
                    'searching' => __('検索中...', 'kei-portfolio'),
                    'loading' => __('読み込み中...', 'kei-portfolio'),
                    'noQuery' => __('検索キーワードを入力してください', 'kei-portfolio'),
                    'historyCleared' => __('検索履歴をクリアしました', 'kei-portfolio'),
                    'historyFailed' => __('検索履歴のクリアに失敗しました', 'kei-portfolio')
                )
            ));
        }
    }

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
 * エラーハンドリング付きで安全に設定
 */
function kei_portfolio_tailwind_config() {
    ?>
    <script>
        // Tailwind設定をグローバルに定義（CDN読み込み前でも安全）
        window.tailwindConfig = {
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
        };

        // Tailwindの存在確認とエラーハンドリング
        function applyTailwindConfig() {
            if (typeof tailwind !== 'undefined' && tailwind.config) {
                try {
                    tailwind.config = window.tailwindConfig;
                    console.log('Tailwind CSS config applied successfully');
                    return true;
                } catch (error) {
                    console.warn('Failed to apply Tailwind config:', error);
                    return false;
                }
            }
            return false;
        }

        // 即座に試行
        if (!applyTailwindConfig()) {
            // DOMContentLoaded後に再試行
            document.addEventListener('DOMContentLoaded', function() {
                let attempts = 0;
                const maxAttempts = 50; // 最大5秒間待機
                
                function retryConfig() {
                    if (applyTailwindConfig()) {
                        return; // 成功した場合は終了
                    }
                    
                    if (attempts < maxAttempts) {
                        attempts++;
                        setTimeout(retryConfig, 100);
                    } else {
                        console.warn('Tailwind CSS not found after maximum attempts. Using fallback styles.');
                    }
                }
                
                retryConfig();
            });
        }
    </script>
    <?php
}
add_action( 'wp_head', 'kei_portfolio_tailwind_config', 15 );

/**
 * TailwindスクリプトはSynchronous読み込みのまま（設定適用の確実性のため）
 * 代わりにconfig設定を確実に後で実行する
 */

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
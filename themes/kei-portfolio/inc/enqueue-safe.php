<?php
/**
 * スクリプトとスタイルの安全な登録・エンキュー
 * ファイル存在チェック機能付き
 *
 * @package Kei_Portfolio
 */

/**
 * スクリプトとスタイルの登録・エンキュー（安全版）
 */
function kei_portfolio_pro_scripts_safe() {
    /**
     * ファイル存在チェック付きエンキュー関数
     * 
     * @param string $type 'style' または 'script'
     * @param string $handle ハンドル名
     * @param string $file_path テーマディレクトリからの相対パス
     * @param array $deps 依存関係
     * @param mixed $ver バージョン番号
     * @param mixed $extra スタイルの場合はmedia、スクリプトの場合はin_footer
     * @return bool エンキューの成功/失敗
     */
    // ファイル存在チェックのキャッシュ
    static $enqueue_file_cache = array();
    
    $safe_enqueue = function($type, $handle, $file_path, $deps = array(), $ver = false, $extra = null) use (&$enqueue_file_cache) {
        // ファイルシステムパス（file_exists用）
        $filesystem_path = get_template_directory() . $file_path;
        
        // キャッシュをチェック
        if (!isset($enqueue_file_cache[$filesystem_path])) {
            $enqueue_file_cache[$filesystem_path] = file_exists($filesystem_path);
        }
        
        // ファイルが存在する場合のみエンキュー
        if ($enqueue_file_cache[$filesystem_path]) {
            $url = get_template_directory_uri() . $file_path;
            $version = $ver ?: wp_get_theme()->get('Version');
            
            if ($type === 'style') {
                $media = $extra ?: 'all';
                wp_enqueue_style($handle, $url, $deps, $version, $media);
            } else {
                $in_footer = $extra !== false ? $extra : true;
                wp_enqueue_script($handle, $url, $deps, $version, $in_footer);
            }
            return true;
        }
        
        // デバッグモードの場合はログ出力
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("Kei Portfolio: Asset file not found - {$handle}: {$file_path}");
        }
        return false;
    };
    
    // メインスタイルシート（常に存在）
    wp_enqueue_style( 
        'kei-portfolio-style', 
        get_stylesheet_uri(), 
        array(), 
        wp_get_theme()->get('Version')
    );

    // Google Fonts（外部リソース）
    wp_enqueue_style( 
        'kei-portfolio-fonts', 
        'https://fonts.googleapis.com/css2?family=Pacifico:wght@400&family=Noto+Sans+JP:wght@400;500;700;900&display=swap', 
        array(), 
        null 
    );

    // Tailwind CSS CDN（外部リソース）
    wp_enqueue_script( 
        'tailwindcss', 
        'https://cdn.tailwindcss.com', 
        array(), 
        '3.4.0', 
        false // headタグ内で読み込む
    );
    
    // Remix Icon（外部リソース）
    wp_enqueue_style( 
        'remixicon', 
        'https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css', 
        array(), 
        '3.5.0' 
    );

    // ローカルアセット（存在チェック付き）
    $safe_enqueue('script', 'kei-portfolio-navigation', '/assets/js/navigation.js', array(), false, true);
    $safe_enqueue('script', 'kei-portfolio-script', '/assets/js/main.js', array('kei-portfolio-navigation'), false, true);

    // ブログ関連アセット（条件付き読み込み）
    if (is_home() || is_archive() || is_single() || is_category() || is_tag() || is_date() || is_author() || is_search()) {
        // ブログ基本スタイル
        $safe_enqueue('style', 'kei-portfolio-blog', '/assets/css/blog.css', array('kei-portfolio-style'));
        
        // ブログモバイル最適化スタイル
        $safe_enqueue('style', 'kei-portfolio-blog-mobile', '/assets/css/blog-mobile.css', 
            array('kei-portfolio-blog'), false, '(max-width: 768px)');
        
        // プリント用CSS
        $safe_enqueue('style', 'kei-portfolio-blog-print', '/assets/css/blog-print.css', 
            array('kei-portfolio-blog'), false, 'print');
        
        // ブログ関連JavaScript
        $safe_enqueue('script', 'kei-portfolio-blog', '/assets/js/blog.js', 
            array('jquery', 'kei-portfolio-script'), false, true);
        
        $safe_enqueue('script', 'kei-portfolio-utils', '/assets/js/utils.js', 
            array(), false, true);
        
        $safe_enqueue('script', 'kei-portfolio-blog-ajax', '/assets/js/blog-ajax.js', 
            array('jquery', 'kei-portfolio-blog', 'kei-portfolio-utils'), false, true);
        
        $safe_enqueue('script', 'secure-blog', '/assets/js/secure-blog.js', 
            array('kei-portfolio-blog-ajax', 'kei-portfolio-utils'), false, true);
        
        // ブログ機能共通のローカライズデータ
        $blog_localize_data = array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('kei_portfolio_ajax'),
            'loadMoreNonce' => wp_create_nonce('load_more_posts'),
            'current_page' => get_query_var('paged') ? get_query_var('paged') : 1,
            'max_pages' => isset($GLOBALS['wp_query']) ? $GLOBALS['wp_query']->max_num_pages : 1,
            // Ajax リトライ設定
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
                'cleanupInterval' => apply_filters('kei_portfolio_cleanup_interval', 300000),
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

        // 各スクリプトにローカライズデータを設定（存在する場合のみ）
        if (wp_script_is('kei-portfolio-blog', 'enqueued')) {
            wp_localize_script('kei-portfolio-blog', 'blogAjax', $blog_localize_data);
        }
        if (wp_script_is('kei-portfolio-blog-ajax', 'enqueued')) {
            wp_localize_script('kei-portfolio-blog-ajax', 'blogAjax', $blog_localize_data);
        }
        if (wp_script_is('secure-blog', 'enqueued')) {
            wp_localize_script('secure-blog', 'keiPortfolioAjax', $blog_localize_data);
        }
        
        // 検索ページ専用のスタイルとスクリプト
        if (is_search()) {
            // 検索ページ用スタイル
            $safe_enqueue('style', 'kei-portfolio-search-styles', '/assets/css/search.css', 
                array('kei-portfolio-blog'));
            
            // 検索ページ用JavaScript
            if ($safe_enqueue('script', 'kei-portfolio-search', '/assets/js/search.js', 
                array('kei-portfolio-script'), false, true)) {
                
                // 検索ページ用のローカライズデータ
                wp_localize_script('kei-portfolio-search', 'keiSearchData', array(
                    'ajaxUrl' => admin_url('admin-ajax.php'),
                    'nonce'   => wp_create_nonce('kei-search-nonce'),
                    'searchQuery' => get_search_query(),
                    'resultsCount' => isset($GLOBALS['wp_query']) ? $GLOBALS['wp_query']->found_posts : 0,
                    'currentView' => isset($_GET['view']) && in_array($_GET['view'], array('grid', 'list')) ? $_GET['view'] : 'list',
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
    }

    // メインスクリプトのローカライズ（存在する場合のみ）
    if (wp_script_is('kei-portfolio-script', 'enqueued')) {
        wp_localize_script('kei-portfolio-script', 'keiPortfolio', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('kei-portfolio-nonce'),
        ));
    }

    // コメント返信スクリプト
    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
    
    // 開発モードでのデバッグアセット（存在する場合のみ）
    if (defined('WP_DEBUG') && WP_DEBUG) {
        $debug_css_path = '/assets/css/dev-debug.css';
        $debug_js_path = '/assets/js/dev-debug.js';
        
        $debug_css_file = get_template_directory() . $debug_css_path;
        $debug_js_file = get_template_directory() . $debug_js_path;
        
        // デバッグファイルの存在チェックをキャッシュ
        if (!isset($enqueue_file_cache[$debug_css_file])) {
            $enqueue_file_cache[$debug_css_file] = file_exists($debug_css_file);
        }
        if (!isset($enqueue_file_cache[$debug_js_file])) {
            $enqueue_file_cache[$debug_js_file] = file_exists($debug_js_file);
        }
        
        // デバッグCSS（ファイル更新時刻をバージョンとして使用）
        if ($enqueue_file_cache[$debug_css_file]) {
            wp_enqueue_style(
                'kei-portfolio-dev-debug',
                get_template_directory_uri() . $debug_css_path,
                array(),
                filemtime($debug_css_file)
            );
        }
        
        // デバッグJS（ファイル更新時刻をバージョンとして使用）
        if ($enqueue_file_cache[$debug_js_file]) {
            wp_enqueue_script(
                'kei-portfolio-dev-debug',
                get_template_directory_uri() . $debug_js_path,
                array('jquery'),
                filemtime($debug_js_file),
                true
            );
        }
    }
}

// 既存のアクションを削除して新しい関数に置き換え
remove_action('wp_enqueue_scripts', 'kei_portfolio_pro_scripts');
add_action('wp_enqueue_scripts', 'kei_portfolio_pro_scripts_safe');

/**
 * Tailwind CSS基本設定の追加（既存のまま維持）
 */
function kei_portfolio_tailwind_config() {
    ?>
    <script>
        // Tailwind設定をグローバルに定義
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
                const maxAttempts = 50;
                
                function retryConfig() {
                    if (applyTailwindConfig()) {
                        return;
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
add_action('wp_head', 'kei_portfolio_tailwind_config', 15);

/**
 * Contact Form Scripts（既存のまま維持）
 */
function kei_portfolio_contact_scripts() {
    if (is_page_template('page-templates/template-contact.php') || is_page('contact')) {
        // Localize script for AJAX
        if (wp_script_is('kei-portfolio-script', 'enqueued')) {
            wp_localize_script('kei-portfolio-script', 'kei_portfolio_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('kei_portfolio_contact_nonce')
            ));
        }
    }
}
add_action('wp_enqueue_scripts', 'kei_portfolio_contact_scripts', 11);
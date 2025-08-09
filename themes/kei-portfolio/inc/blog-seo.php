<?php
/**
 * ブログSEO機能実装
 * 
 * 構造化データ、OGPタグ、音声検索最適化、AIを意識したSEO対策
 * 
 * @package Kei_Portfolio
 * @since 1.0.0
 */

namespace KeiPortfolio\Blog;

/**
 * Blog_SEO クラス
 * 
 * ブログのSEO最適化を担当
 */
class Blog_SEO {
    
    /**
     * コンストラクタ
     */
    public function __construct() {
        $this->init_hooks();
    }
    
    /**
     * フックの初期化
     */
    private function init_hooks() {
        // メタタグとOGP
        add_action('wp_head', [$this, 'add_meta_tags'], 1);
        add_action('wp_head', [$this, 'add_ogp_tags'], 2);
        add_action('wp_head', [$this, 'add_twitter_cards'], 3);
        
        // 構造化データ
        add_action('wp_head', [$this, 'add_structured_data'], 10);
        
        // XMLサイトマップの改善
        add_filter('wp_sitemaps_posts_query_args', [$this, 'customize_sitemap'], 10, 2);
        add_filter('wp_sitemaps_posts_pre_url_list', [$this, 'customize_sitemap_urls'], 10, 3);
        
        // RSSフィードの改善
        add_action('rss2_head', [$this, 'add_rss_elements']);
        add_filter('the_content_feed', [$this, 'enhance_feed_content']);
        
        // パフォーマンス向上
        add_action('wp_head', [$this, 'add_preload_hints'], 5);
        add_action('wp_head', [$this, 'add_dns_prefetch'], 6);
        
        // 音声検索・AI最適化
        add_filter('the_content', [$this, 'add_voice_search_optimization']);
        add_action('wp_head', [$this, 'add_faq_structured_data']);
        
        // パンくずリスト構造化データ
        add_action('wp_head', [$this, 'add_breadcrumb_structured_data']);
    }
    
    /**
     * メタタグを追加
     */
    public function add_meta_tags() {
        if (!is_singular('post')) {
            return;
        }
        
        global $post;
        
        // メタディスクリプション
        $description = '';
        if (has_excerpt()) {
            $description = wp_trim_words(get_the_excerpt(), 25, '...');
        } else {
            $description = wp_trim_words(strip_tags(get_the_content()), 25, '...');
        }
        
        if ($description) {
            echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
        }
        
        // キーワード（タグから生成）
        $tags = get_the_tags();
        if ($tags) {
            $keywords = array_map(function($tag) {
                return $tag->name;
            }, array_slice($tags, 0, 10)); // 最大10個まで
            
            echo '<meta name="keywords" content="' . esc_attr(implode(', ', $keywords)) . '">' . "\n";
        }
        
        // 著者情報
        echo '<meta name="author" content="' . esc_attr(get_the_author()) . '">' . "\n";
        
        // 公開日・更新日
        echo '<meta name="article:published_time" content="' . esc_attr(get_the_date('c')) . '">' . "\n";
        echo '<meta name="article:modified_time" content="' . esc_attr(get_the_modified_date('c')) . '">' . "\n";
        
        // 読了時間（概算）
        $content = get_the_content();
        $word_count = str_word_count(strip_tags($content));
        $reading_time = max(1, ceil($word_count / 200));
        echo '<meta name="twitter:data1" content="約' . $reading_time . '分">' . "\n";
        echo '<meta name="twitter:label1" content="読了時間">' . "\n";
        
        // カテゴリー情報
        $categories = get_the_category();
        if ($categories) {
            $primary_category = $categories[0];
            echo '<meta name="article:section" content="' . esc_attr($primary_category->name) . '">' . "\n";
        }
        
        // canonical URL
        echo '<link rel="canonical" href="' . esc_url(get_permalink()) . '">' . "\n";
    }
    
    /**
     * OGPタグを追加
     */
    public function add_ogp_tags() {
        if (!is_singular('post') && !is_home()) {
            return;
        }
        
        // 基本のOGPタグ
        echo '<meta property="og:locale" content="' . esc_attr(get_locale()) . '">' . "\n";
        echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";
        
        if (is_singular('post')) {
            // 個別記事
            echo '<meta property="og:type" content="article">' . "\n";
            echo '<meta property="og:title" content="' . esc_attr(get_the_title()) . '">' . "\n";
            echo '<meta property="og:url" content="' . esc_url(get_permalink()) . '">' . "\n";
            
            // 説明文
            $description = '';
            if (has_excerpt()) {
                $description = wp_trim_words(get_the_excerpt(), 30, '...');
            } else {
                $description = wp_trim_words(strip_tags(get_the_content()), 30, '...');
            }
            
            if ($description) {
                echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
            }
            
            // 画像
            if (has_post_thumbnail()) {
                $image_url = get_the_post_thumbnail_url(null, 'large');
                $image_id = get_post_thumbnail_id();
                $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
                
                echo '<meta property="og:image" content="' . esc_url($image_url) . '">' . "\n";
                echo '<meta property="og:image:alt" content="' . esc_attr($image_alt ?: get_the_title()) . '">' . "\n";
                
                // 画像のメタデータ
                $image_data = wp_get_attachment_metadata($image_id);
                if ($image_data) {
                    echo '<meta property="og:image:width" content="' . esc_attr($image_data['width']) . '">' . "\n";
                    echo '<meta property="og:image:height" content="' . esc_attr($image_data['height']) . '">' . "\n";
                }
            }
            
            // 記事固有の情報
            echo '<meta property="article:author" content="' . esc_attr(get_the_author()) . '">' . "\n";
            echo '<meta property="article:published_time" content="' . esc_attr(get_the_date('c')) . '">' . "\n";
            echo '<meta property="article:modified_time" content="' . esc_attr(get_the_modified_date('c')) . '">' . "\n";
            
            // カテゴリーとタグ
            $categories = get_the_category();
            foreach ($categories as $category) {
                echo '<meta property="article:section" content="' . esc_attr($category->name) . '">' . "\n";
            }
            
            $tags = get_the_tags();
            if ($tags) {
                foreach (array_slice($tags, 0, 5) as $tag) {
                    echo '<meta property="article:tag" content="' . esc_attr($tag->name) . '">' . "\n";
                }
            }
            
        } elseif (is_home()) {
            // ブログトップ
            echo '<meta property="og:type" content="website">' . "\n";
            echo '<meta property="og:title" content="' . esc_attr(get_bloginfo('name')) . ' - ブログ">' . "\n";
            echo '<meta property="og:url" content="' . esc_url(home_url('/')) . '">' . "\n";
            echo '<meta property="og:description" content="' . esc_attr(get_bloginfo('description')) . '">' . "\n";
            
            // サイトのロゴまたはデフォルト画像
            $custom_logo_id = get_theme_mod('custom_logo');
            if ($custom_logo_id) {
                $logo_url = wp_get_attachment_image_url($custom_logo_id, 'large');
                echo '<meta property="og:image" content="' . esc_url($logo_url) . '">' . "\n";
            }
        }
    }
    
    /**
     * Twitter Cardsタグを追加
     */
    public function add_twitter_cards() {
        if (!is_singular('post')) {
            return;
        }
        
        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
        
        // Twitterアカウント（カスタマイザーで設定可能）
        $twitter_handle = get_theme_mod('social_twitter_handle', '');
        if ($twitter_handle) {
            echo '<meta name="twitter:site" content="@' . esc_attr(ltrim($twitter_handle, '@')) . '">' . "\n";
            echo '<meta name="twitter:creator" content="@' . esc_attr(ltrim($twitter_handle, '@')) . '">' . "\n";
        }
        
        echo '<meta name="twitter:title" content="' . esc_attr(get_the_title()) . '">' . "\n";
        
        // 説明文
        $description = '';
        if (has_excerpt()) {
            $description = wp_trim_words(get_the_excerpt(), 25, '...');
        } else {
            $description = wp_trim_words(strip_tags(get_the_content()), 25, '...');
        }
        
        if ($description) {
            echo '<meta name="twitter:description" content="' . esc_attr($description) . '">' . "\n";
        }
        
        // 画像
        if (has_post_thumbnail()) {
            $image_url = get_the_post_thumbnail_url(null, 'large');
            echo '<meta name="twitter:image" content="' . esc_url($image_url) . '">' . "\n";
            
            $image_alt = get_post_meta(get_post_thumbnail_id(), '_wp_attachment_image_alt', true);
            echo '<meta name="twitter:image:alt" content="' . esc_attr($image_alt ?: get_the_title()) . '">' . "\n";
        }
    }
    
    /**
     * 構造化データを追加
     */
    public function add_structured_data() {
        if (!is_singular('post')) {
            return;
        }
        
        $post = get_post();
        $author_data = get_userdata($post->post_author);
        
        // BlogPosting構造化データ
        $structured_data = [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => get_the_title(),
            'description' => $this->get_post_description(),
            'datePublished' => get_the_date('c'),
            'dateModified' => get_the_modified_date('c'),
            'author' => [
                '@type' => 'Person',
                'name' => $author_data->display_name,
                'url' => get_author_posts_url($author_data->ID),
                'description' => $author_data->description,
                'image' => [
                    '@type' => 'ImageObject',
                    'url' => get_avatar_url($author_data->ID, ['size' => 96])
                ]
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => get_bloginfo('name'),
                'url' => home_url('/'),
                'description' => get_bloginfo('description')
            ],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => get_permalink()
            ],
            'url' => get_permalink(),
            'wordCount' => str_word_count(strip_tags(get_the_content())),
            'commentCount' => get_comments_number(),
            'articleSection' => $this->get_primary_category(),
            'keywords' => $this->get_post_keywords(),
            'inLanguage' => get_locale()
        ];
        
        // 画像情報
        if (has_post_thumbnail()) {
            $image_id = get_post_thumbnail_id();
            $image_data = wp_get_attachment_metadata($image_id);
            $image_url = get_the_post_thumbnail_url(null, 'large');
            
            $structured_data['image'] = [
                '@type' => 'ImageObject',
                'url' => $image_url,
                'width' => $image_data['width'] ?? null,
                'height' => $image_data['height'] ?? null,
                'caption' => get_post_meta($image_id, '_wp_attachment_image_alt', true) ?: get_the_title()
            ];
        }
        
        // パブリッシャーのロゴ
        $custom_logo_id = get_theme_mod('custom_logo');
        if ($custom_logo_id) {
            $logo_data = wp_get_attachment_metadata($custom_logo_id);
            $structured_data['publisher']['logo'] = [
                '@type' => 'ImageObject',
                'url' => wp_get_attachment_image_url($custom_logo_id, 'full'),
                'width' => $logo_data['width'] ?? null,
                'height' => $logo_data['height'] ?? null
            ];
        }
        
        // 読了時間
        $content = get_the_content();
        $word_count = str_word_count(strip_tags($content));
        $reading_time = max(1, ceil($word_count / 200));
        
        $structured_data['timeRequired'] = 'PT' . $reading_time . 'M';
        
        // アクセシビリティ対応
        $structured_data['accessibilityFeature'] = [
            'alternativeText',
            'readingOrder',
            'structuralNavigation'
        ];
        
        $this->output_structured_data($structured_data);
    }
    
    /**
     * FAQページ構造化データを追加
     */
    public function add_faq_structured_data() {
        if (!is_singular('post')) {
            return;
        }
        
        $content = get_the_content();
        $faq_items = $this->extract_faq_from_content($content);
        
        if (empty($faq_items)) {
            return;
        }
        
        $faq_structured_data = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $faq_items
        ];
        
        $this->output_structured_data($faq_structured_data);
    }
    
    /**
     * パンくずリスト構造化データ
     */
    public function add_breadcrumb_structured_data() {
        if (!is_singular('post')) {
            return;
        }
        
        $breadcrumbs = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => []
        ];
        
        // ホーム
        $breadcrumbs['itemListElement'][] = [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'ホーム',
            'item' => home_url('/')
        ];
        
        // ブログトップ
        $blog_page_id = get_option('page_for_posts');
        if ($blog_page_id) {
            $breadcrumbs['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => get_the_title($blog_page_id),
                'item' => get_permalink($blog_page_id)
            ];
        } else {
            $breadcrumbs['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => 'ブログ',
                'item' => home_url('/blog/')
            ];
        }
        
        // カテゴリー
        $categories = get_the_category();
        if ($categories) {
            $category = $categories[0];
            $breadcrumbs['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $category->name,
                'item' => get_category_link($category->term_id)
            ];
        }
        
        // 現在のページ
        $breadcrumbs['itemListElement'][] = [
            '@type' => 'ListItem',
            'position' => count($breadcrumbs['itemListElement']) + 1,
            'name' => get_the_title(),
            'item' => get_permalink()
        ];
        
        $this->output_structured_data($breadcrumbs);
    }
    
    /**
     * XMLサイトマップのカスタマイズ
     */
    public function customize_sitemap($args, $post_type) {
        if ($post_type === 'post') {
            $args['meta_query'] = [
                [
                    'key' => '_sitemap_exclude',
                    'compare' => 'NOT EXISTS'
                ]
            ];
        }
        
        return $args;
    }
    
    /**
     * サイトマップURLのカスタマイズ
     */
    public function customize_sitemap_urls($url_list, $post_type, $page_num) {
        if ($post_type !== 'post') {
            return $url_list;
        }
        
        foreach ($url_list as &$url_item) {
            $post_id = url_to_postid($url_item['loc']);
            if ($post_id) {
                // 最終更新日を更新日で上書き
                $url_item['lastmod'] = get_the_modified_date('c', $post_id);
                
                // 画像情報を追加
                if (has_post_thumbnail($post_id)) {
                    $url_item['images'] = [
                        [
                            'loc' => get_the_post_thumbnail_url($post_id, 'large'),
                            'caption' => get_the_title($post_id)
                        ]
                    ];
                }
            }
        }
        
        return $url_list;
    }
    
    /**
     * プリロードヒントを追加
     */
    public function add_preload_hints() {
        if (!is_singular('post')) {
            return;
        }
        
        // アイキャッチ画像のプリロード
        if (has_post_thumbnail()) {
            $image_url = get_the_post_thumbnail_url(null, 'large');
            echo '<link rel="preload" as="image" href="' . esc_url($image_url) . '">' . "\n";
        }
        
        // 重要なCSS/JSファイルのプリロード
        $theme_uri = get_template_directory_uri();
        echo '<link rel="preload" as="style" href="' . $theme_uri . '/assets/css/blog.css">' . "\n";
        echo '<link rel="preload" as="script" href="' . $theme_uri . '/assets/js/blog.js">' . "\n";
    }
    
    /**
     * DNS prefetchを追加
     */
    public function add_dns_prefetch() {
        // 外部リソースのDNS prefetch
        echo '<link rel="dns-prefetch" href="//fonts.googleapis.com">' . "\n";
        echo '<link rel="dns-prefetch" href="//cdnjs.cloudflare.com">' . "\n";
        
        // ソーシャルメディアのprefetch
        echo '<link rel="dns-prefetch" href="//twitter.com">' . "\n";
        echo '<link rel="dns-prefetch" href="//facebook.com">' . "\n";
        echo '<link rel="dns-prefetch" href="//linkedin.com">' . "\n";
    }
    
    /**
     * 音声検索最適化
     */
    public function add_voice_search_optimization($content) {
        if (!is_singular('post')) {
            return $content;
        }
        
        // FAQ形式のセクションを検出して最適化
        $faq_pattern = '/<h[2-3][^>]*>(.*?)(?:\?|？)(.*?)<\/h[2-3]>/i';
        
        if (preg_match_all($faq_pattern, $content, $matches)) {
            foreach ($matches[0] as $i => $match) {
                $question = strip_tags($matches[1][$i]);
                $replacement = str_replace(
                    $match,
                    $match . '<!-- FAQ Question: ' . esc_attr($question) . ' -->',
                    $match
                );
                $content = str_replace($match, $replacement, $content);
            }
        }
        
        return $content;
    }
    
    /**
     * RSSフィードの要素を追加
     */
    public function add_rss_elements() {
        echo '<atom:link href="' . esc_url(get_feed_link()) . '" rel="self" type="application/rss+xml" />' . "\n";
        echo '<language>' . get_bloginfo_rss('language') . '</language>' . "\n";
        echo '<copyright>' . date('Y') . ' ' . get_bloginfo('name') . '</copyright>' . "\n";
        echo '<managingEditor>' . get_option('admin_email') . ' (' . get_bloginfo('name') . ')</managingEditor>' . "\n";
        echo '<webMaster>' . get_option('admin_email') . ' (' . get_bloginfo('name') . ')</webMaster>' . "\n";
        echo '<ttl>60</ttl>' . "\n";
    }
    
    /**
     * フィードコンテンツの改善
     */
    public function enhance_feed_content($content) {
        global $post;
        
        // アイキャッチ画像を追加
        if (has_post_thumbnail()) {
            $thumbnail = get_the_post_thumbnail($post->ID, 'medium', [
                'style' => 'max-width:100%;height:auto;'
            ]);
            $content = $thumbnail . $content;
        }
        
        // カテゴリー情報を追加
        $categories = get_the_category();
        if ($categories) {
            $category_list = '<p><strong>カテゴリー:</strong> ';
            $category_names = array_map(function($cat) {
                return $cat->name;
            }, $categories);
            $category_list .= implode(', ', $category_names) . '</p>';
            
            $content = $category_list . $content;
        }
        
        // 元記事へのリンクを追加
        $content .= '<p><a href="' . get_permalink() . '">元記事を読む</a></p>';
        
        return $content;
    }
    
    /**
     * ヘルパーメソッド群
     */
    
    /**
     * 投稿の説明文を取得
     */
    private function get_post_description() {
        if (has_excerpt()) {
            return wp_trim_words(get_the_excerpt(), 30, '...');
        } else {
            return wp_trim_words(strip_tags(get_the_content()), 30, '...');
        }
    }
    
    /**
     * 主要カテゴリーを取得
     */
    private function get_primary_category() {
        $categories = get_the_category();
        return $categories ? $categories[0]->name : '';
    }
    
    /**
     * 投稿のキーワードを取得
     */
    private function get_post_keywords() {
        $keywords = [];
        
        $tags = get_the_tags();
        if ($tags) {
            foreach (array_slice($tags, 0, 10) as $tag) {
                $keywords[] = $tag->name;
            }
        }
        
        return implode(', ', $keywords);
    }
    
    /**
     * コンテンツからFAQを抽出
     */
    private function extract_faq_from_content($content) {
        $faq_items = [];
        
        // H2, H3タグから質問形式のコンテンツを抽出
        preg_match_all('/<h[23][^>]*>(.*?)(?:\?|？)(.*?)<\/h[23]>(.*?)<p>(.*?)<\/p>/si', $content, $matches);
        
        if (!empty($matches[1])) {
            foreach ($matches[1] as $i => $question) {
                $clean_question = strip_tags($question . $matches[2][$i]);
                $clean_answer = strip_tags($matches[4][$i]);
                
                if (strlen($clean_question) > 10 && strlen($clean_answer) > 10) {
                    $faq_items[] = [
                        '@type' => 'Question',
                        'name' => $clean_question,
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => $clean_answer
                        ]
                    ];
                }
            }
        }
        
        return $faq_items;
    }
    
    /**
     * 構造化データを出力
     */
    private function output_structured_data($data) {
        echo '<script type="application/ld+json">' . "\n";
        echo wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        echo "\n" . '</script>' . "\n";
    }
}

// インスタンス化
new Blog_SEO();
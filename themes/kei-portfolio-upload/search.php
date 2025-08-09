<?php
/**
 * Template for displaying search results pages
 * Optimized for Japanese search with highlighted results
 *
 * @package KeiPortfolio
 * @version 2.0
 */

// SecurityHelperを使用
use KeiPortfolio\Security\SecurityHelper;

get_header();

// Get search query and sanitize - XSS対策強化
$search_query = SecurityHelper::get_request_param('s', '', 'text', 'GET');
$search_query_escaped = SecurityHelper::escape_html($search_query);

// Get view mode (grid/list) from URL parameter or default to list for search - XSS対策済み
$view_mode = SecurityHelper::get_request_param('view', 'list', 'text', 'GET');
if (!in_array($view_mode, ['grid', 'list'], true)) {
    $view_mode = 'list';
}

// Get post type filter - XSS対策済み
$post_type_filter = SecurityHelper::get_request_param('post_type', 'any', 'text', 'GET');
if (!in_array($post_type_filter, ['post', 'project', 'any'], true)) {
    $post_type_filter = 'any';
}

// Get sort order - XSS対策済み
$sort_order = SecurityHelper::get_request_param('sort', 'relevance', 'text', 'GET');
if (!in_array($sort_order, ['relevance', 'date_desc', 'date_asc', 'title'], true)) {
    $sort_order = 'relevance';
}

// Function to highlight search terms in content
function highlight_search_terms($content, $search_terms) {
    if (empty($search_terms) || empty($content)) {
        return $content;
    }
    
    // Convert search terms to array if it's a string
    if (is_string($search_terms)) {
        // Handle Japanese and English search terms
        $terms = array_filter(array_map('trim', preg_split('/[\s　]+/u', $search_terms)));
    } else {
        $terms = $search_terms;
    }
    
    $highlighted_content = $content;
    
    foreach ($terms as $term) {
        if (strlen($term) < 2) continue; // Skip single characters
        
        // Escape special regex characters
        $escaped_term = preg_quote($term, '/');
        
        // Case-insensitive highlighting with proper Unicode support
        $highlighted_content = preg_replace(
            '/(' . $escaped_term . ')/ui',
            '<mark class="search-highlight">$1</mark>',
            $highlighted_content
        );
    }
    
    return $highlighted_content;
}

// Function to generate search excerpt with context
function generate_search_excerpt($content, $search_terms, $length = 200) {
    $content = strip_tags($content);
    
    if (empty($search_terms)) {
        return wp_trim_words($content, $length, '...');
    }
    
    // Find the position of the first search term
    $terms = array_filter(array_map('trim', preg_split('/[\s　]+/u', $search_terms)));
    $first_position = false;
    
    foreach ($terms as $term) {
        $pos = mb_stripos($content, $term);
        if ($pos !== false && ($first_position === false || $pos < $first_position)) {
            $first_position = $pos;
        }
    }
    
    if ($first_position !== false) {
        // Extract context around the search term
        $start = max(0, $first_position - 100);
        $excerpt = mb_substr($content, $start, $length);
        
        if ($start > 0) {
            $excerpt = '...' . $excerpt;
        }
        
        if (mb_strlen($content) > $start + $length) {
            $excerpt .= '...';
        }
        
        return $excerpt;
    }
    
    return wp_trim_words($content, $length, '...');
}
?>

<main id="primary" class="site-main search-page">
    <div class="container mx-auto px-4 py-8">

        <!-- Search Header -->
        <header class="search-header mb-8">
            <div class="search-title-section mb-6">
                <h1 class="search-title text-3xl md:text-4xl font-bold mb-4">
                    <?php if (!empty($search_query)) : ?>
                        「<?php echo $search_query_escaped; ?>」の検索結果
                    <?php else : ?>
                        検索結果
                    <?php endif; ?>
                </h1>
                
                <?php if (!empty($search_query)) : ?>
                    <!-- Search Statistics -->
                    <div class="search-stats text-lg text-gray-600 mb-4">
                        <?php
                        global $wp_query;
                        $result_count = $wp_query->found_posts;
                        
                        if ($result_count > 0) {
                            printf(
                                _n(
                                    '%s件の結果が見つかりました',
                                    '%s件の結果が見つかりました',
                                    $result_count,
                                    'kei-portfolio'
                                ),
                                '<strong>' . number_format_i18n($result_count) . '</strong>'
                            );
                        } else {
                            echo '検索条件に一致する結果が見つかりませんでした';
                        }
                        ?>
                    </div>
                    
                    <!-- Search Suggestions -->
                    <?php if ($wp_query->found_posts == 0) : ?>
                        <div class="search-suggestions bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                            <h3 class="font-semibold text-blue-800 mb-2">検索のヒント:</h3>
                            <ul class="text-sm text-blue-700 space-y-1">
                                <li>• 異なるキーワードを試してみてください</li>
                                <li>• より一般的な用語を使用してみてください</li>
                                <li>• スペルや表記をチェックしてください</li>
                                <li>• ひらがな、カタカナ、漢字の違いを試してみてください</li>
                            </ul>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Search Form -->
            <div class="search-form-section mb-6">
                <form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
                    <div class="flex gap-2">
                        <label for="search-field" class="sr-only">検索キーワード</label>
                        <input type="search" 
                               id="search-field"
                               name="s" 
                               value="<?php echo SecurityHelper::escape_attr($search_query); ?>" 
                               placeholder="キーワードを入力..."
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               aria-describedby="search-help"
                               maxlength="200"
                               data-nonce="<?php echo SecurityHelper::escape_attr(SecurityHelper::generate_csrf_token('search_form')); ?>">
                        <button type="submit" 
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            検索
                        </button>
                    </div>
                    <small id="search-help" class="text-xs text-gray-500 mt-1 block">
                        複数のキーワードはスペースで区切ってください
                    </small>
                </form>
            </div>

            <!-- Search Controls -->
            <?php if (!empty($search_query) && $wp_query->found_posts > 0) : ?>
                <div class="search-controls bg-white p-4 rounded-lg shadow-sm border">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        
                        <!-- Post Type Filter -->
                        <div class="post-type-filter">
                            <label for="post-type-filter" class="block text-sm font-medium text-gray-700 mb-2">
                                投稿タイプで絞り込み
                            </label>
                            <select id="post-type-filter" class="form-select text-sm border-gray-300 rounded-md">
                                <option value="any" <?php selected($post_type_filter, 'any'); ?>>すべて</option>
                                <option value="post" <?php selected($post_type_filter, 'post'); ?>>ブログ記事</option>
                                <?php if (post_type_exists('project')) : ?>
                                    <option value="project" <?php selected($post_type_filter, 'project'); ?>>プロジェクト</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Sort Options -->
                        <div class="sort-section">
                            <label for="sort-results" class="block text-sm font-medium text-gray-700 mb-2">
                                並び替え
                            </label>
                            <select id="sort-results" class="form-select text-sm border-gray-300 rounded-md">
                                <option value="relevance" <?php selected($sort_order, 'relevance'); ?>>関連度順</option>
                                <option value="date_desc" <?php selected($sort_order, 'date_desc'); ?>>新しい順</option>
                                <option value="date_asc" <?php selected($sort_order, 'date_asc'); ?>>古い順</option>
                                <option value="title" <?php selected($sort_order, 'title'); ?>>タイトル順</option>
                            </select>
                        </div>

                        <!-- View Mode Toggle -->
                        <div class="view-toggle-section">
                            <label class="block text-sm font-medium text-gray-700 mb-2">表示形式</label>
                            <div class="view-toggle flex bg-gray-100 rounded-lg p-1">
                                <button type="button" 
                                        class="view-toggle-btn <?php echo $view_mode === 'grid' ? 'active' : ''; ?>" 
                                        data-view="grid"
                                        aria-pressed="<?php echo $view_mode === 'grid' ? 'true' : 'false'; ?>">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                    </svg>
                                    <span class="sr-only">グリッド表示</span>
                                </button>
                                <button type="button" 
                                        class="view-toggle-btn <?php echo $view_mode === 'list' ? 'active' : ''; ?>" 
                                        data-view="list"
                                        aria-pressed="<?php echo $view_mode === 'list' ? 'true' : 'false'; ?>">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="sr-only">リスト表示</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </header>

        <!-- Search Results -->
        <div class="search-content">
            <?php if (have_posts()) : ?>
                
                <!-- Results Container -->
                <div class="search-results-container <?php echo $view_mode === 'grid' ? 'posts-grid' : 'posts-list'; ?>" 
                     id="search-results-container" 
                     data-view="<?php echo SecurityHelper::escape_attr($view_mode); ?>"
                     data-search-query="<?php echo SecurityHelper::escape_attr($search_query); ?>">
                    
                    <?php while (have_posts()) : the_post(); ?>
                        
                        <article id="post-<?php the_ID(); ?>" <?php post_class('search-result-card'); ?>>
                            
                            <?php if ($view_mode === 'grid') : ?>
                                <!-- Grid View -->
                                <div class="result-card-content">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <div class="result-thumbnail">
                                            <a href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
                                                <?php the_post_thumbnail('medium', ['class' => 'w-full h-48 object-cover']); ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="result-content p-4">
                                        <!-- Post Type Badge -->
                                        <div class="post-type-badge mb-2">
                                            <span class="inline-block bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded">
                                                <?php 
                                                $post_type_obj = get_post_type_object(get_post_type());
                                                echo $post_type_obj ? $post_type_obj->labels->singular_name : get_post_type();
                                                ?>
                                            </span>
                                        </div>
                                        
                                        <!-- Title with highlighting -->
                                        <h3 class="result-title text-lg font-semibold mb-2">
                                            <a href="<?php the_permalink(); ?>" class="text-blue-600 hover:text-blue-800">
                                                <?php echo wp_kses(highlight_search_terms(get_the_title(), $search_query), SecurityHelper::get_allowed_html()); ?>
                                            </a>
                                        </h3>
                                        
                                        <!-- Excerpt with highlighting -->
                                        <div class="result-excerpt text-gray-600 text-sm mb-3">
                                            <?php 
                                            $excerpt = generate_search_excerpt(get_the_content(), $search_query, 150);
                                            echo wp_kses(highlight_search_terms($excerpt, $search_query), SecurityHelper::get_allowed_html());
                                            ?>
                                        </div>
                                        
                                        <!-- Meta information -->
                                        <div class="result-meta text-xs text-gray-500">
                                            <time datetime="<?php echo get_the_date('c'); ?>">
                                                <?php echo get_the_date(); ?>
                                            </time>
                                            
                                            <?php if (get_post_type() === 'post') : ?>
                                                <?php $categories = get_the_category(); ?>
                                                <?php if (!empty($categories)) : ?>
                                                    <span class="separator mx-2">•</span>
                                                    <?php echo esc_html($categories[0]->name); ?>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                            <?php else : ?>
                                <!-- List View -->
                                <div class="result-card-content flex">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <div class="result-thumbnail flex-shrink-0 mr-4">
                                            <a href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
                                                <?php the_post_thumbnail('thumbnail', ['class' => 'w-20 h-20 object-cover rounded']); ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="result-content flex-1">
                                        <!-- Post Type Badge -->
                                        <div class="post-type-badge mb-1">
                                            <span class="inline-block bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded">
                                                <?php 
                                                $post_type_obj = get_post_type_object(get_post_type());
                                                echo $post_type_obj ? $post_type_obj->labels->singular_name : get_post_type();
                                                ?>
                                            </span>
                                        </div>
                                        
                                        <!-- Title with highlighting -->
                                        <h3 class="result-title text-xl font-semibold mb-2">
                                            <a href="<?php the_permalink(); ?>" class="text-blue-600 hover:text-blue-800">
                                                <?php echo wp_kses(highlight_search_terms(get_the_title(), $search_query), SecurityHelper::get_allowed_html()); ?>
                                            </a>
                                        </h3>
                                        
                                        <!-- Excerpt with highlighting -->
                                        <div class="result-excerpt text-gray-600 mb-3">
                                            <?php 
                                            $excerpt = generate_search_excerpt(get_the_content(), $search_query, 200);
                                            echo wp_kses(highlight_search_terms($excerpt, $search_query), SecurityHelper::get_allowed_html());
                                            ?>
                                        </div>
                                        
                                        <!-- Meta information -->
                                        <div class="result-meta text-sm text-gray-500">
                                            <time datetime="<?php echo get_the_date('c'); ?>">
                                                <?php echo get_the_date(); ?>
                                            </time>
                                            
                                            <?php if (get_post_type() === 'post') : ?>
                                                <?php $categories = get_the_category(); ?>
                                                <?php if (!empty($categories)) : ?>
                                                    <span class="separator mx-2">•</span>
                                                    <a href="<?php echo get_category_link($categories[0]->term_id); ?>" 
                                                       class="text-blue-600 hover:text-blue-800">
                                                        <?php echo esc_html($categories[0]->name); ?>
                                                    </a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            
                                            <?php if (get_the_author()) : ?>
                                                <span class="separator mx-2">•</span>
                                                <span>by <?php the_author(); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                        </article>
                        
                    <?php endwhile; ?>
                    
                </div>

                <!-- Search Pagination -->
                <div class="search-pagination mt-8">
                    <?php get_template_part('template-parts/blog/pagination'); ?>
                </div>

            <?php elseif (!empty($search_query)) : ?>
                
                <!-- No Results Found -->
                <div class="no-search-results text-center py-12">
                    <div class="max-w-md mx-auto">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        
                        <h2 class="text-2xl font-semibold text-gray-600 mb-2">
                            「<?php echo $search_query_escaped; ?>」に一致する結果が見つかりませんでした
                        </h2>
                        
                        <p class="text-gray-500 mb-6">
                            検索条件を変更して再度お試しください。
                        </p>
                        
                        <!-- Alternative Search Suggestions -->
                        <div class="alternative-searches bg-gray-50 rounded-lg p-6 mb-6">
                            <h3 class="font-semibold mb-3">おすすめのキーワード:</h3>
                            <div class="flex flex-wrap gap-2 justify-center">
                                <?php
                                // Get popular tags or categories as suggestions
                                $popular_terms = get_terms([
                                    'taxonomy' => 'category',
                                    'orderby' => 'count',
                                    'order' => 'DESC',
                                    'number' => 6,
                                    'hide_empty' => true
                                ]);
                                
                                if (!empty($popular_terms)) {
                                    foreach ($popular_terms as $term) {
                                        echo '<a href="' . SecurityHelper::escape_url(home_url('/?s=' . urlencode($term->name))) . '" 
                                              class="inline-block bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm hover:bg-blue-200 transition-colors">' 
                                              . SecurityHelper::escape_html($term->name) . '</a>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <a href="<?php echo esc_url(home_url('/')); ?>" 
                               class="inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                ホームに戻る
                            </a>
                            
                            <br>
                            
                            <a href="<?php echo esc_url(get_post_type_archive_link('post')); ?>" 
                               class="inline-block text-blue-600 hover:text-blue-800 underline">
                                すべての投稿を見る
                            </a>
                        </div>
                    </div>
                </div>

            <?php else : ?>
                
                <!-- Empty Search -->
                <div class="empty-search text-center py-12">
                    <div class="max-w-md mx-auto">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        
                        <h2 class="text-2xl font-semibold text-gray-600 mb-2">
                            検索キーワードを入力してください
                        </h2>
                        
                        <p class="text-gray-500">
                            お探しのコンテンツを見つけるためのキーワードを入力してください。
                        </p>
                    </div>
                </div>

            <?php endif; ?>
        </div>

    </div>
</main>

<!-- Search JavaScript with Enhanced Security -->
<script type="text/javascript">
// 検索データをJSON形式で安全に受け渡し - XSS対策強化版
var searchData = {
    query: <?php echo SecurityHelper::safe_json_encode($search_query); ?>,
    nonce: <?php echo SecurityHelper::safe_json_encode(SecurityHelper::generate_csrf_token('search_ajax')); ?>,
    postTypeFilter: <?php echo SecurityHelper::safe_json_encode($post_type_filter); ?>,
    viewMode: <?php echo SecurityHelper::safe_json_encode($view_mode); ?>,
    sortOrder: <?php echo SecurityHelper::safe_json_encode($sort_order); ?>,
    foundPosts: <?php echo absint($wp_query->found_posts); ?>,
    ajaxUrl: <?php echo SecurityHelper::safe_json_encode(admin_url('admin-ajax.php')); ?>,
    config: {
        minQueryLength: 2,
        searchDelay: 500,
        animateResults: true,
        enableRealTimeSearch: false
    }
};

document.addEventListener('DOMContentLoaded', function() {
    // フォームのセキュリティ強化
    const searchForm = document.querySelector('.search-form');
    if (searchForm) {
        const searchInput = searchForm.querySelector('input[name="s"]');
        if (searchInput) {
            // 入力値の検証とサニタイゼーション
            searchInput.addEventListener('input', function() {
                const value = this.value;
                // XSS対策：HTMLタグとスクリプトを除去
                const sanitized = value
                    .replace(/<[^>]*>/g, '')
                    .replace(/javascript:/gi, '')
                    .replace(/on\w+\s*=/gi, '');
                
                if (value !== sanitized) {
                    this.value = sanitized;
                    console.warn('Potentially dangerous input sanitized');
                }
            });
            
            // 最大長制限の強制
            searchInput.addEventListener('paste', function(e) {
                const clipboardData = e.clipboardData.getData('text');
                if (clipboardData.length > 200) {
                    e.preventDefault();
                    this.value = clipboardData.substring(0, 200);
                }
            });
        }
    }
    
    // レート制限の実装（クライアント側）
    let searchTimeout;
    function throttleSearch(callback, delay = 1000) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(callback, delay);
    }
    
    // セキュリティ設定の初期化確認
    console.log('Search security measures initialized');
    
    // 旧SearchManagerとの互換性チェック
    if (typeof SearchManager !== 'undefined') {
        console.log('Search functionality initialized successfully');
    }
});
</script>

<?php
get_footer();
?>
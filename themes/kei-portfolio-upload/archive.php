<?php
/**
 * Template for displaying archive pages
 * Handles category, tag, author, and date archives
 *
 * @package KeiPortfolio
 * @version 2.0
 */

// SecurityHelperを使用
use KeiPortfolio\Security\SecurityHelper;

get_header();

// Get archive type and relevant data
$archive_title = get_the_archive_title();
$archive_description = get_the_archive_description();
$archive_type = '';
$filter_options = [];

if (is_category()) {
    $archive_type = 'category';
    $current_category = get_queried_object();
    $filter_options = get_categories(['hide_empty' => true]);
} elseif (is_tag()) {
    $archive_type = 'tag';
    $current_tag = get_queried_object();
    $filter_options = get_tags(['hide_empty' => true]);
} elseif (is_author()) {
    $archive_type = 'author';
    $current_author = get_queried_object();
} elseif (is_date()) {
    $archive_type = 'date';
    if (is_year()) {
        $archive_type = 'year';
    } elseif (is_month()) {
        $archive_type = 'month';
    } elseif (is_day()) {
        $archive_type = 'day';
    }
}

// Get view mode (grid/list) from URL parameter or default to grid - XSS対策済み
$view_mode = SecurityHelper::get_request_param('view', 'grid', 'text', 'GET');
if (!in_array($view_mode, ['grid', 'list'], true)) {
    $view_mode = 'grid';
}

// Get sort order from URL parameter - XSS対策済み
$sort_order = SecurityHelper::get_request_param('sort', 'date_desc', 'text', 'GET');
if (!in_array($sort_order, ['date_desc', 'date_asc', 'title', 'popular'], true)) {
    $sort_order = 'date_desc';
}

// カテゴリーフィルタの安全な処理
$category_filter = SecurityHelper::get_request_param('category', '', 'text', 'GET');
?>

<main id="primary" class="site-main archive-page">
    <div class="container mx-auto px-4 py-8">

        <!-- Archive Header -->
        <header class="archive-header mb-8">
            <div class="archive-title-section mb-6">
                <h1 class="archive-title text-3xl md:text-4xl font-bold mb-4">
                    <?php echo wp_kses_post($archive_title); ?>
                </h1>
                
                <?php if ($archive_description) : ?>
                    <div class="archive-description text-lg text-gray-600 mb-4">
                        <?php echo wp_kses_post($archive_description); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Post count -->
                <div class="archive-stats text-sm text-gray-500 mb-6">
                    <?php
                    global $wp_query;
                    $post_count = $wp_query->found_posts;
                    printf(
                        _n(
                            '%s件の投稿があります',
                            '%s件の投稿があります',
                            $post_count,
                            'kei-portfolio'
                        ),
                        number_format_i18n($post_count)
                    );
                    ?>
                </div>
            </div>

            <!-- Archive Controls -->
            <div class="archive-controls bg-white p-4 rounded-lg shadow-sm border">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    
                    <!-- Filtering Options -->
                    <?php if (!empty($filter_options) && in_array($archive_type, ['category', 'tag'])) : ?>
                        <div class="filter-section">
                            <label for="archive-filter" class="block text-sm font-medium text-gray-700 mb-2">
                                <?php echo $archive_type === 'category' ? 'カテゴリー' : 'タグ'; ?>で絞り込み
                            </label>
                            <select id="archive-filter" class="form-select text-sm border-gray-300 rounded-md"
                                    data-base-url="<?php echo SecurityHelper::escape_attr(home_url('/')); ?>"
                                    data-all-posts-url="<?php echo SecurityHelper::escape_attr(get_post_type_archive_link('post')); ?>">
                                <option value="">すべて表示</option>
                                <?php foreach ($filter_options as $option) : ?>
                                    <option value="<?php echo esc_attr($option->slug); ?>" 
                                            <?php selected($option->term_id, get_queried_object_id()); ?>>
                                        <?php echo esc_html($option->name); ?>
                                        (<?php echo $option->count; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <!-- Sort Options -->
                    <div class="sort-section">
                        <label for="sort-posts" class="block text-sm font-medium text-gray-700 mb-2">
                            並び替え
                        </label>
                        <select id="sort-posts" class="form-select text-sm border-gray-300 rounded-md">
                            <option value="date_desc" <?php selected($sort_order, 'date_desc'); ?>>新しい順</option>
                            <option value="date_asc" <?php selected($sort_order, 'date_asc'); ?>>古い順</option>
                            <option value="title" <?php selected($sort_order, 'title'); ?>>タイトル順</option>
                            <?php if (get_option('kei_portfolio_enable_post_views', false)) : ?>
                                <option value="popular" <?php selected($sort_order, 'popular'); ?>>人気順</option>
                            <?php endif; ?>
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
        </header>

        <!-- Archive Content -->
        <div class="archive-content">
            <?php if (have_posts()) : ?>
                
                <!-- Posts Container -->
                <div class="posts-container <?php echo $view_mode === 'grid' ? 'posts-grid' : 'posts-list'; ?>" 
                     id="posts-container" 
                     data-view="<?php echo SecurityHelper::escape_attr($view_mode); ?>"
                     data-home-url="<?php echo SecurityHelper::escape_attr(home_url('/')); ?>"
                     data-posts-url="<?php echo SecurityHelper::escape_attr(get_post_type_archive_link('post')); ?>">
                    
                    <?php while (have_posts()) : the_post(); ?>
                        
                        <?php if ($view_mode === 'grid') : ?>
                            <!-- Grid View -->
                            <article id="post-<?php the_ID(); ?>" <?php post_class('post-card grid-card'); ?>>
                                <?php get_template_part('template-parts/blog/post-card', 'grid'); ?>
                            </article>
                            
                        <?php else : ?>
                            <!-- List View -->
                            <article id="post-<?php the_ID(); ?>" <?php post_class('post-card list-card'); ?>>
                                <?php get_template_part('template-parts/blog/post-card', 'list'); ?>
                            </article>
                        <?php endif; ?>
                        
                    <?php endwhile; ?>
                    
                </div>

                <!-- Pagination -->
                <div class="archive-pagination mt-8">
                    <?php get_template_part('template-parts/blog/pagination'); ?>
                </div>

            <?php else : ?>
                
                <!-- No Posts Found -->
                <div class="no-posts-found text-center py-12">
                    <div class="max-w-md mx-auto">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        
                        <h2 class="text-2xl font-semibold text-gray-600 mb-2">
                            投稿が見つかりませんでした
                        </h2>
                        
                        <p class="text-gray-500 mb-6">
                            <?php
                            if (is_category()) {
                                echo 'このカテゴリーには投稿がありません。';
                            } elseif (is_tag()) {
                                echo 'このタグには投稿がありません。';
                            } elseif (is_author()) {
                                echo 'この著者による投稿がありません。';
                            } elseif (is_date()) {
                                echo 'この期間の投稿がありません。';
                            } else {
                                echo '条件に一致する投稿がありません。';
                            }
                            ?>
                        </p>
                        
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

            <?php endif; ?>
        </div>

        <!-- Archive Sidebar -->
        <?php if (is_active_sidebar('blog-sidebar')) : ?>
            <aside class="archive-sidebar mt-12">
                <h2 class="text-xl font-semibold mb-6">関連情報</h2>
                <div class="sidebar-widgets">
                    <?php dynamic_sidebar('blog-sidebar'); ?>
                </div>
            </aside>
        <?php endif; ?>

    </div>
</main>

<!-- Archive JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // View toggle functionality
    const viewToggleBtns = document.querySelectorAll('.view-toggle-btn');
    const postsContainer = document.getElementById('posts-container');
    
    viewToggleBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const newView = this.dataset.view;
            const currentUrl = new URL(window.location);
            
            // Update URL parameter
            currentUrl.searchParams.set('view', newView);
            
            // Update page without reload (if history API is available)
            if (history.replaceState) {
                history.replaceState({}, '', currentUrl);
            }
            
            // Update container class
            postsContainer.className = postsContainer.className.replace(
                /(posts-grid|posts-list)/, 
                newView === 'grid' ? 'posts-grid' : 'posts-list'
            );
            postsContainer.dataset.view = newView;
            
            // Update button states
            viewToggleBtns.forEach(b => {
                b.classList.remove('active');
                b.setAttribute('aria-pressed', 'false');
            });
            this.classList.add('active');
            this.setAttribute('aria-pressed', 'true');
        });
    });
    
    // Sort functionality
    const sortSelect = document.getElementById('sort-posts');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const currentUrl = new URL(window.location);
            currentUrl.searchParams.set('sort', this.value);
            window.location.href = currentUrl.toString();
        });
    }
    
    // Filter functionality - XSS対策強化
    const filterSelect = document.getElementById('archive-filter');
    if (filterSelect) {
        filterSelect.addEventListener('change', function() {
            const baseUrl = filterSelect.dataset.baseUrl;
            const allPostsUrl = filterSelect.dataset.allPostsUrl;
            
            if (this.value && baseUrl) {
                // カテゴリー名をさらにエスケープ
                const categorySlug = encodeURIComponent(this.value.replace(/[^a-zA-Z0-9\-_]/g, ''));
                window.location.href = baseUrl + 'category/' + categorySlug + '/';
            } else if (allPostsUrl) {
                window.location.href = allPostsUrl;
            }
        });
    }
});
</script>

<?php
get_footer();
?>
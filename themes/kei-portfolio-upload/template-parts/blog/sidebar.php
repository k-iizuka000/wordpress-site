<?php
/**
 * ブログサイドバーテンプレートパーツ
 * 
 * カテゴリー、最新記事、人気記事、タグクラウド、検索フォーム等の
 * サイドバーウィジェットを表示するテンプレートパーツ
 * 
 * @param array $args パラメータ配列
 *   - show_search (bool): 検索フォーム表示フラグ
 *   - show_recent_posts (bool): 最新記事表示フラグ
 *   - recent_posts_count (int): 最新記事表示数
 *   - show_popular_posts (bool): 人気記事表示フラグ
 *   - popular_posts_count (int): 人気記事表示数
 *   - show_categories (bool): カテゴリー一覧表示フラグ
 *   - show_tags (bool): タグクラウド表示フラグ
 *   - show_archive (bool): アーカイブ表示フラグ
 *   - show_meta (bool): メタ情報表示フラグ
 *   - sidebar_id (string): カスタムサイドバーID
 * 
 * @package kei-portfolio
 * @since 1.0.0
 */

// デフォルトパラメータ設定
$defaults = array(
    'show_search' => true,
    'show_recent_posts' => true,
    'recent_posts_count' => 5,
    'show_popular_posts' => true,
    'popular_posts_count' => 5,
    'show_categories' => true,
    'show_tags' => true,
    'show_archive' => true,
    'show_meta' => false,
    'sidebar_id' => 'blog-sidebar'
);

$args = wp_parse_args($args ?? array(), $defaults);

// カスタムサイドバーがある場合は優先して表示
if (is_active_sidebar($args['sidebar_id'])) :
?>
    <aside class="blog-sidebar" role="complementary" aria-label="<?php esc_attr_e('サイドバー', 'kei-portfolio'); ?>">
        <div class="sidebar-content">
            <?php dynamic_sidebar($args['sidebar_id']); ?>
        </div>
    </aside>
<?php 
    return;
endif;

// デフォルトサイドバーコンテンツを表示
?>
<aside class="blog-sidebar" role="complementary" aria-label="<?php esc_attr_e('サイドバー', 'kei-portfolio'); ?>">
    <div class="sidebar-content">
        
        <?php if ($args['show_search']) : ?>
            <!-- 検索フォーム -->
            <div class="sidebar-widget sidebar-search">
                <h3 class="widget-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                    </svg>
                    <?php esc_html_e('検索', 'kei-portfolio'); ?>
                </h3>
                
                <div class="widget-content">
                    <form role="search" method="get" class="sidebar-search-form" action="<?php echo esc_url(home_url('/')); ?>">
                        <div class="search-form-wrapper">
                            <label for="sidebar-search-input" class="screen-reader-text">
                                <?php esc_html_e('記事を検索', 'kei-portfolio'); ?>
                            </label>
                            <input type="search" 
                                   id="sidebar-search-input"
                                   class="search-input" 
                                   placeholder="<?php esc_attr_e('キーワードで検索...', 'kei-portfolio'); ?>"
                                   value="<?php echo esc_attr(get_search_query()); ?>" 
                                   name="s"
                                   required>
                            <button type="submit" class="search-button" aria-label="<?php esc_attr_e('検索実行', 'kei-portfolio'); ?>">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <?php 
        // 最新記事
        if ($args['show_recent_posts']) :
            $recent_posts = get_posts(array(
                'numberposts' => $args['recent_posts_count'],
                'post_status' => 'publish',
                'orderby' => 'date',
                'order' => 'DESC',
                'suppress_filters' => false
            ));
            
            if (!empty($recent_posts)) :
        ?>
            <div class="sidebar-widget sidebar-recent-posts">
                <h3 class="widget-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M13,9H18.5L13,3.5V9M6,2H14L20,8V20A2,2 0 0,1 18,22H6C4.89,22 4,21.1 4,20V4C4,2.89 4.89,2 6,2M15,18V16H6V18H15M18,14V12H6V14H18Z"/>
                    </svg>
                    <?php esc_html_e('最新記事', 'kei-portfolio'); ?>
                </h3>
                
                <div class="widget-content">
                    <ul class="recent-posts-list" role="list">
                        <?php foreach ($recent_posts as $post) : setup_postdata($post); ?>
                            <li class="recent-post-item" role="listitem">
                                <article class="recent-post">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <div class="recent-post__thumbnail">
                                            <a href="<?php the_permalink(); ?>" 
                                               class="recent-post__thumbnail-link"
                                               aria-hidden="true"
                                               tabindex="-1">
                                                <?php the_post_thumbnail('thumbnail', array(
                                                    'class' => 'recent-post__image',
                                                    'loading' => 'lazy'
                                                )); ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="recent-post__content">
                                        <h4 class="recent-post__title">
                                            <a href="<?php the_permalink(); ?>" 
                                               class="recent-post__title-link"
                                               rel="bookmark">
                                                <?php the_title(); ?>
                                            </a>
                                        </h4>
                                        
                                        <div class="recent-post__meta">
                                            <time class="recent-post__date" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                                <?php echo esc_html(get_the_date('Y.m.d')); ?>
                                            </time>
                                            
                                            <?php 
                                            $comment_count = get_comments_number();
                                            if ($comment_count > 0) : 
                                            ?>
                                                <span class="recent-post__comments">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                        <path d="M9,22A1,1 0 0,1 8,21V18H4A2,2 0 0,1 2,16V4C2,2.89 2.9,2 4,2H20A2,2 0 0,1 22,4V16A2,2 0 0,1 20,18H13.9L10.2,21.71C10,21.9 9.75,22 9.5,22V22H9Z"/>
                                                    </svg>
                                                    <?php echo esc_html($comment_count); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </article>
                            </li>
                        <?php endforeach; wp_reset_postdata(); ?>
                    </ul>
                </div>
            </div>
        <?php 
            endif;
        endif; 

        // 人気記事
        if ($args['show_popular_posts']) :
            $popular_posts = get_posts(array(
                'numberposts' => $args['popular_posts_count'],
                'post_status' => 'publish',
                'meta_key' => 'post_views_count',
                'orderby' => 'meta_value_num',
                'order' => 'DESC',
                'suppress_filters' => false
            ));
            
            if (!empty($popular_posts)) :
        ?>
            <div class="sidebar-widget sidebar-popular-posts">
                <h3 class="widget-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12,17.27L18.18,21L16.54,13.97L22,9.24L14.81,8.62L12,2L9.19,8.62L2,9.24L7.45,13.97L5.82,21L12,17.27Z"/>
                    </svg>
                    <?php esc_html_e('人気記事', 'kei-portfolio'); ?>
                </h3>
                
                <div class="widget-content">
                    <ol class="popular-posts-list" role="list">
                        <?php $rank = 1; foreach ($popular_posts as $post) : setup_postdata($post); ?>
                            <li class="popular-post-item" role="listitem">
                                <div class="popular-post__rank" aria-label="<?php printf(esc_attr__('%d位', 'kei-portfolio'), $rank); ?>">
                                    <?php echo esc_html($rank); ?>
                                </div>
                                
                                <article class="popular-post">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <div class="popular-post__thumbnail">
                                            <a href="<?php the_permalink(); ?>" 
                                               class="popular-post__thumbnail-link"
                                               aria-hidden="true"
                                               tabindex="-1">
                                                <?php the_post_thumbnail('thumbnail', array(
                                                    'class' => 'popular-post__image',
                                                    'loading' => 'lazy'
                                                )); ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="popular-post__content">
                                        <h4 class="popular-post__title">
                                            <a href="<?php the_permalink(); ?>" 
                                               class="popular-post__title-link"
                                               rel="bookmark">
                                                <?php the_title(); ?>
                                            </a>
                                        </h4>
                                        
                                        <div class="popular-post__meta">
                                            <?php 
                                            $view_count = get_post_meta(get_the_ID(), 'post_views_count', true);
                                            if ($view_count) :
                                            ?>
                                                <span class="popular-post__views">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                        <path d="M12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12A3,3 0 0,0 12,9M12,17A5,5 0 0,1 7,12A5,5 0 0,1 12,7A5,5 0 0,1 17,12A5,5 0 0,1 12,17M12,4.5C7,4.5 2.73,7.61 1,12C2.73,16.39 7,19.5 12,19.5C17,19.5 21.27,16.39 23,12C21.27,7.61 17,4.5 12,4.5Z"/>
                                                    </svg>
                                                    <?php echo number_format_i18n($view_count); ?>
                                                </span>
                                            <?php endif; ?>
                                            
                                            <time class="popular-post__date" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                                <?php echo esc_html(get_the_date('Y.m.d')); ?>
                                            </time>
                                        </div>
                                    </div>
                                </article>
                            </li>
                        <?php $rank++; endforeach; wp_reset_postdata(); ?>
                    </ol>
                </div>
            </div>
        <?php 
            endif;
        endif; 

        // カテゴリー一覧
        if ($args['show_categories']) :
            $categories = get_categories(array(
                'orderby' => 'count',
                'order' => 'DESC',
                'hide_empty' => true
            ));
            
            if (!empty($categories)) :
        ?>
            <div class="sidebar-widget sidebar-categories">
                <h3 class="widget-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12,2A2,2 0 0,1 14,4V8A2,2 0 0,1 12,10H10V12H12A2,2 0 0,1 14,14V18A2,2 0 0,1 12,20H4A2,2 0 0,1 2,18V14A2,2 0 0,1 4,12H6V10H4A2,2 0 0,1 2,8V4A2,2 0 0,1 4,2H12M4,4V8H12V4H4M4,14V18H12V14H4M22,15V17H18V19H16V15H18V13H22V15Z"/>
                    </svg>
                    <?php esc_html_e('カテゴリー', 'kei-portfolio'); ?>
                </h3>
                
                <div class="widget-content">
                    <ul class="categories-list" role="list">
                        <?php foreach ($categories as $category) : ?>
                            <li class="category-item" role="listitem">
                                <a href="<?php echo esc_url(get_category_link($category->term_id)); ?>" 
                                   class="category-link <?php echo is_category($category->term_id) ? 'is-active' : ''; ?>"
                                   <?php if (is_category($category->term_id)) : ?>aria-current="page"<?php endif; ?>>
                                    <span class="category-name"><?php echo esc_html($category->name); ?></span>
                                    <span class="category-count"><?php echo esc_html($category->count); ?></span>
                                </a>
                                
                                <?php if ($category->description) : ?>
                                    <div class="category-description">
                                        <?php echo esc_html($category->description); ?>
                                    </div>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php 
            endif;
        endif; ?>

        <?php 
        // タグクラウド
        if ($args['show_tags']) :
            $tags = get_tags(array(
                'orderby' => 'count',
                'order' => 'DESC',
                'number' => 20,
                'hide_empty' => true
            ));
            
            if (!empty($tags)) :
        ?>
            <div class="sidebar-widget sidebar-tags">
                <h3 class="widget-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M5.5,9A1.5,1.5 0 0,0 7,7.5A1.5,1.5 0 0,0 5.5,6A1.5,1.5 0 0,0 4,7.5A1.5,1.5 0 0,0 5.5,9M17.41,11.58C17.77,11.94 18,12.44 18,13C18,13.56 17.77,14.06 17.41,14.42L12.42,19.41C12.06,19.77 11.56,20 11,20C10.44,20 9.94,19.77 9.58,19.41L2.59,12.42C2.22,12.06 2,11.56 2,11V4C2,2.89 2.89,2 4,2H11C11.56,2 12.06,2.22 12.42,2.59L19.41,9.58C19.77,9.94 20,10.44 20,11C20,11.56 19.77,12.06 19.41,12.42L17.41,11.58Z"/>
                    </svg>
                    <?php esc_html_e('タグ', 'kei-portfolio'); ?>
                </h3>
                
                <div class="widget-content">
                    <div class="tags-cloud" role="group" aria-label="<?php esc_attr_e('タグクラウド', 'kei-portfolio'); ?>">
                        <?php foreach ($tags as $tag) : 
                            $font_size = min(1.2, max(0.8, ($tag->count / 10) + 0.8));
                        ?>
                            <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>" 
                               class="tag-link <?php echo is_tag($tag->term_id) ? 'is-active' : ''; ?>"
                               style="font-size: <?php echo esc_attr($font_size); ?>em;"
                               title="<?php printf(esc_attr__('%s (%d記事)', 'kei-portfolio'), $tag->name, $tag->count); ?>"
                               <?php if (is_tag($tag->term_id)) : ?>aria-current="page"<?php endif; ?>>
                                <?php echo esc_html($tag->name); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php 
            endif;
        endif; ?>

        <?php 
        // アーカイブ
        if ($args['show_archive']) :
            $archives = wp_get_archives(array(
                'type' => 'monthly',
                'format' => 'custom',
                'show_post_count' => true,
                'echo' => false,
                'limit' => 12
            ));
            
            if ($archives) :
        ?>
            <div class="sidebar-widget sidebar-archives">
                <h3 class="widget-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M19,3H18V1H16V3H8V1H6V3H5A2,2 0 0,0 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5A2,2 0 0,0 19,3M19,19H5V8H19V19Z"/>
                    </svg>
                    <?php esc_html_e('アーカイブ', 'kei-portfolio'); ?>
                </h3>
                
                <div class="widget-content">
                    <ul class="archives-list" role="list">
                        <?php echo $archives; ?>
                    </ul>
                </div>
            </div>
        <?php 
            endif;
        endif; ?>

        <?php 
        // メタ情報（管理者のみ）
        if ($args['show_meta'] && current_user_can('edit_posts')) :
        ?>
            <div class="sidebar-widget sidebar-meta">
                <h3 class="widget-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12,15.5A3.5,3.5 0 0,1 8.5,12A3.5,3.5 0 0,1 12,8.5A3.5,3.5 0 0,1 15.5,12A3.5,3.5 0 0,1 12,15.5M19.43,12.97C19.47,12.65 19.5,12.33 19.5,12C19.5,11.67 19.47,11.34 19.43,11L21.54,9.37C21.73,9.22 21.78,8.95 21.66,8.73L19.66,5.27C19.54,5.05 19.27,4.96 19.05,5.05L16.56,6.05C16.04,5.66 15.5,5.32 14.87,5.07L14.5,2.42C14.46,2.18 14.25,2 14,2H10C9.75,2 9.54,2.18 9.5,2.42L9.13,5.07C8.5,5.32 7.96,5.66 7.44,6.05L4.95,5.05C4.73,4.96 4.46,5.05 4.34,5.27L2.34,8.73C2.22,8.95 2.27,9.22 2.46,9.37L4.57,11C4.53,11.34 4.5,11.67 4.5,12C4.5,12.33 4.53,12.65 4.57,12.97L2.46,14.63C2.27,14.78 2.22,15.05 2.34,15.27L4.34,18.73C4.46,18.95 4.73,19.03 4.95,18.95L7.44,17.94C7.96,18.34 8.5,18.68 9.13,18.93L9.5,21.58C9.54,21.82 9.75,22 10,22H14C14.25,22 14.46,21.82 14.5,21.58L14.87,18.93C15.5,18.68 16.04,18.34 16.56,17.94L19.05,18.95C19.27,19.03 19.54,18.95 19.66,18.73L21.66,15.27C21.78,15.05 21.73,14.78 21.54,14.63L19.43,12.97Z"/>
                    </svg>
                    <?php esc_html_e('メタ情報', 'kei-portfolio'); ?>
                </h3>
                
                <div class="widget-content">
                    <ul class="meta-list" role="list">
                        <li role="listitem">
                            <a href="<?php echo esc_url(wp_login_url()); ?>">
                                <?php esc_html_e('ログイン', 'kei-portfolio'); ?>
                            </a>
                        </li>
                        <li role="listitem">
                            <a href="<?php echo esc_url(admin_url()); ?>">
                                <?php esc_html_e('管理画面', 'kei-portfolio'); ?>
                            </a>
                        </li>
                        <li role="listitem">
                            <a href="<?php echo esc_url(get_bloginfo('rss2_url')); ?>">
                                <?php esc_html_e('記事フィード', 'kei-portfolio'); ?>
                            </a>
                        </li>
                        <li role="listitem">
                            <a href="<?php echo esc_url(get_bloginfo('comments_rss2_url')); ?>">
                                <?php esc_html_e('コメントフィード', 'kei-portfolio'); ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

    </div>
</aside>

<style>
/* サイドバーのベーススタイル */
.blog-sidebar {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 0;
    overflow: hidden;
}

.sidebar-content {
    display: flex;
    flex-direction: column;
    gap: 0;
}

/* ウィジェット共通スタイル */
.sidebar-widget {
    background: white;
    border-bottom: 1px solid #e9ecef;
    padding: 1.5rem;
    transition: background-color 0.2s ease;
}

.sidebar-widget:last-child {
    border-bottom: none;
    border-radius: 0 0 12px 12px;
}

.sidebar-widget:first-child {
    border-radius: 12px 12px 0 0;
}

.sidebar-widget:only-child {
    border-radius: 12px;
}

.sidebar-widget:hover {
    background: #fafbfc;
}

.widget-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    border-bottom: 2px solid #007cba;
    padding-bottom: 0.5rem;
}

.widget-title svg {
    color: #007cba;
    flex-shrink: 0;
}

.widget-content {
    line-height: 1.6;
}

/* 検索フォーム */
.sidebar-search .search-form-wrapper {
    display: flex;
    background: #f8f9fa;
    border-radius: 8px;
    overflow: hidden;
    border: 2px solid #e9ecef;
    transition: border-color 0.2s ease;
}

.sidebar-search .search-form-wrapper:focus-within {
    border-color: #007cba;
}

.sidebar-search .search-input {
    flex: 1;
    border: none;
    background: transparent;
    padding: 0.75rem;
    font-size: 0.875rem;
    outline: none;
}

.sidebar-search .search-input::placeholder {
    color: #6c757d;
}

.sidebar-search .search-button {
    background: #007cba;
    border: none;
    color: white;
    padding: 0.75rem;
    cursor: pointer;
    transition: background-color 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.sidebar-search .search-button:hover {
    background: #005a87;
}

/* 最新記事 */
.recent-posts-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.recent-post {
    display: flex;
    gap: 0.75rem;
    align-items: flex-start;
}

.recent-post__thumbnail {
    flex-shrink: 0;
    width: 60px;
    height: 60px;
    border-radius: 6px;
    overflow: hidden;
}

.recent-post__thumbnail-link {
    display: block;
    width: 100%;
    height: 100%;
}

.recent-post__image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.2s ease;
}

.recent-post:hover .recent-post__image {
    transform: scale(1.05);
}

.recent-post__content {
    flex: 1;
    min-width: 0;
}

.recent-post__title {
    font-size: 0.875rem;
    line-height: 1.4;
    margin: 0 0 0.5rem;
    font-weight: 500;
}

.recent-post__title-link {
    color: #333;
    text-decoration: none;
    transition: color 0.2s ease;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.recent-post__title-link:hover {
    color: #007cba;
}

.recent-post__meta {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.75rem;
    color: #6c757d;
}

.recent-post__comments {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

/* 人気記事 */
.popular-posts-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.popular-post-item {
    display: flex;
    gap: 0.75rem;
    align-items: flex-start;
}

.popular-post__rank {
    background: #007cba;
    color: white;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 600;
    flex-shrink: 0;
}

.popular-post {
    display: flex;
    gap: 0.75rem;
    align-items: flex-start;
    flex: 1;
}

.popular-post__thumbnail {
    flex-shrink: 0;
    width: 50px;
    height: 50px;
    border-radius: 4px;
    overflow: hidden;
}

.popular-post__thumbnail-link {
    display: block;
    width: 100%;
    height: 100%;
}

.popular-post__image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.2s ease;
}

.popular-post:hover .popular-post__image {
    transform: scale(1.05);
}

.popular-post__content {
    flex: 1;
    min-width: 0;
}

.popular-post__title {
    font-size: 0.8125rem;
    line-height: 1.4;
    margin: 0 0 0.375rem;
    font-weight: 500;
}

.popular-post__title-link {
    color: #333;
    text-decoration: none;
    transition: color 0.2s ease;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.popular-post__title-link:hover {
    color: #007cba;
}

.popular-post__meta {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.6875rem;
    color: #6c757d;
}

.popular-post__views {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

/* カテゴリー一覧 */
.categories-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.category-link {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.5rem;
    background: #f8f9fa;
    border-radius: 6px;
    text-decoration: none;
    color: #333;
    transition: all 0.2s ease;
    border: 1px solid #e9ecef;
}

.category-link:hover,
.category-link.is-active {
    background: #007cba;
    color: white;
    border-color: #007cba;
}

.category-name {
    font-weight: 500;
}

.category-count {
    background: rgba(0, 0, 0, 0.1);
    color: inherit;
    padding: 0.125rem 0.375rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.category-link.is-active .category-count {
    background: rgba(255, 255, 255, 0.2);
}

.category-description {
    font-size: 0.75rem;
    color: #6c757d;
    margin-top: 0.25rem;
    padding-left: 0.5rem;
    line-height: 1.4;
}

/* タグクラウド */
.tags-cloud {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    line-height: 1.2;
}

.tag-link {
    background: #f8f9fa;
    color: #666;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    text-decoration: none;
    font-size: 0.75rem;
    font-weight: 500;
    transition: all 0.2s ease;
    border: 1px solid #e9ecef;
}

.tag-link:hover,
.tag-link.is-active {
    background: #007cba;
    color: white;
    border-color: #007cba;
    transform: translateY(-1px);
}

/* アーカイブ */
.archives-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
}

.archives-list li {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.archives-list a {
    color: #333;
    text-decoration: none;
    font-size: 0.875rem;
    padding: 0.375rem 0.5rem;
    border-radius: 4px;
    transition: all 0.2s ease;
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.archives-list a:hover {
    background: #f8f9fa;
    color: #007cba;
}

/* メタ情報 */
.meta-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.meta-list a {
    color: #333;
    text-decoration: none;
    padding: 0.5rem;
    background: #f8f9fa;
    border-radius: 6px;
    transition: all 0.2s ease;
    border: 1px solid #e9ecef;
    font-size: 0.875rem;
}

.meta-list a:hover {
    background: #007cba;
    color: white;
    border-color: #007cba;
}

/* レスポンシブ対応 */
@media (max-width: 768px) {
    .blog-sidebar {
        margin-top: 2rem;
    }
    
    .sidebar-widget {
        padding: 1.25rem;
    }
    
    .widget-title {
        font-size: 1rem;
    }
    
    .recent-post__thumbnail,
    .popular-post__thumbnail {
        width: 50px;
        height: 50px;
    }
}

@media (max-width: 480px) {
    .sidebar-widget {
        padding: 1rem;
    }
    
    .recent-post,
    .popular-post {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .recent-post__thumbnail,
    .popular-post__thumbnail {
        width: 100%;
        height: 120px;
        aspect-ratio: 16/9;
    }
}

/* アクセシビリティ向上 */
@media (prefers-reduced-motion: reduce) {
    .sidebar-widget,
    .search-button,
    .recent-post__image,
    .popular-post__image,
    .category-link,
    .tag-link,
    .meta-list a,
    .archives-list a,
    .recent-post__title-link,
    .popular-post__title-link {
        transition: none;
    }
    
    .recent-post:hover .recent-post__image,
    .popular-post:hover .popular-post__image {
        transform: none;
    }
    
    .tag-link:hover {
        transform: none;
    }
}

/* フォーカススタイル */
.search-input:focus,
.search-button:focus,
.recent-post__title-link:focus,
.popular-post__title-link:focus,
.category-link:focus,
.tag-link:focus,
.archives-list a:focus,
.meta-list a:focus {
    outline: 2px solid #007cba;
    outline-offset: 2px;
}

/* 高コントラストモード */
@media (prefers-contrast: high) {
    .sidebar-widget {
        border: 2px solid #333;
    }
    
    .search-form-wrapper,
    .category-link,
    .tag-link,
    .meta-list a {
        border: 2px solid currentColor;
    }
    
    .popular-post__rank {
        border: 2px solid white;
    }
}

/* プリントスタイル */
@media print {
    .blog-sidebar {
        background: white;
        box-shadow: none;
        border: 1px solid #ccc;
    }
    
    .sidebar-search,
    .sidebar-meta {
        display: none;
    }
    
    .widget-title svg {
        display: none;
    }
    
    .recent-post__thumbnail,
    .popular-post__thumbnail {
        display: none;
    }
    
    .category-link,
    .tag-link,
    .archives-list a,
    .recent-post__title-link,
    .popular-post__title-link {
        color: black !important;
        background: white !important;
        border: 1px solid #ccc !important;
    }
}

/* ダークモード対応 */
@media (prefers-color-scheme: dark) {
    .blog-sidebar {
        background: #1a1a1a;
        color: #e0e0e0;
    }
    
    .sidebar-widget {
        background: #2a2a2a;
        border-color: #444;
    }
    
    .sidebar-widget:hover {
        background: #333;
    }
    
    .widget-title {
        color: #f0f0f0;
    }
    
    .search-form-wrapper {
        background: #333;
        border-color: #555;
    }
    
    .search-input {
        color: #e0e0e0;
    }
    
    .search-input::placeholder {
        color: #aaa;
    }
    
    .recent-post__title-link,
    .popular-post__title-link {
        color: #e0e0e0;
    }
    
    .recent-post__title-link:hover,
    .popular-post__title-link:hover {
        color: #4db8e8;
    }
    
    .category-link,
    .tag-link,
    .archives-list a,
    .meta-list a {
        background: #333;
        color: #e0e0e0;
        border-color: #555;
    }
    
    .category-link:hover,
    .tag-link:hover,
    .archives-list a:hover,
    .meta-list a:hover {
        background: #4db8e8;
        color: #1a1a1a;
        border-color: #4db8e8;
    }
}
</style>
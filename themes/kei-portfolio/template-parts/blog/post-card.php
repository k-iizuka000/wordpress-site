<?php
/**
 * 投稿カードテンプレートパーツ
 * 
 * 個別の投稿をカード形式で表示するテンプレートパーツ
 * グリッドレイアウト、リストレイアウト両方に対応
 * 
 * @param array $args パラメータ配列
 *   - layout (string): 'grid'|'list'|'featured' - カードレイアウトタイプ
 *   - show_thumbnail (bool): アイキャッチ画像表示フラグ
 *   - thumbnail_size (string): アイキャッチ画像サイズ
 *   - show_excerpt (bool): 抜粋表示フラグ
 *   - excerpt_length (int): 抜粋の文字数
 *   - show_meta (bool): メタ情報表示フラグ
 *   - show_author (bool): 投稿者表示フラグ
 *   - show_date (bool): 投稿日表示フラグ
 *   - show_category (bool): カテゴリー表示フラグ
 *   - show_tags (bool): タグ表示フラグ
 *   - show_comments (bool): コメント数表示フラグ
 *   - show_read_time (bool): 読了時間表示フラグ
 *   - show_views (bool): ビュー数表示フラグ
 *   - post_id (int): 投稿ID（指定されない場合は現在の投稿）
 * 
 * @package kei-portfolio
 * @since 1.0.0
 */

// デフォルトパラメータ設定
$defaults = array(
    'layout' => 'grid',
    'show_thumbnail' => true,
    'thumbnail_size' => 'medium_large',
    'show_excerpt' => true,
    'excerpt_length' => 120,
    'show_meta' => true,
    'show_author' => true,
    'show_date' => true,
    'show_category' => true,
    'show_tags' => false,
    'show_comments' => true,
    'show_read_time' => true,
    'show_views' => false,
    'post_id' => null
);

$args = wp_parse_args($args ?? array(), $defaults);

// 投稿IDの設定
$post_id = $args['post_id'] ? $args['post_id'] : get_the_ID();
$post = get_post($post_id);

if (!$post) {
    return;
}

// 投稿データ設定
setup_postdata($post);

// レイアウト用のCSSクラス
$card_class = 'post-card post-card--' . esc_attr($args['layout']);

// メタデータの取得
$categories = get_the_category($post_id);
$tags = get_the_tags($post_id);
$comment_count = get_comments_number($post_id);
$post_date = get_the_date('', $post_id);
$post_date_gmt = get_the_date('c', $post_id);
$author_id = $post->post_author;
$author_name = get_the_author_meta('display_name', $author_id);
$author_avatar = get_avatar_url($author_id, array('size' => 40));

// 読了時間の計算
$reading_time = 0;
if ($args['show_read_time']) {
    $word_count = str_word_count(strip_tags(get_post_field('post_content', $post_id)));
    $reading_time = ceil($word_count / 200); // 1分間に200語として計算
}

// ビュー数の取得
$view_count = 0;
if ($args['show_views']) {
    $view_count = get_post_meta($post_id, 'post_views_count', true) ?: 0;
}

// 抜粋の取得・調整
$excerpt = '';
if ($args['show_excerpt']) {
    $excerpt = get_the_excerpt($post_id);
    if ($args['excerpt_length'] && strlen($excerpt) > $args['excerpt_length']) {
        $excerpt = mb_substr($excerpt, 0, $args['excerpt_length']) . '...';
    }
}

// スティッキー投稿判定
$is_sticky = is_sticky($post_id);
?>

<div class="<?php echo esc_attr($card_class); ?>" data-post-id="<?php echo esc_attr($post_id); ?>">
    
    <?php if ($is_sticky && (is_home() || is_front_page())) : ?>
        <div class="post-card__sticky-badge" aria-label="<?php esc_attr_e('注目記事', 'kei-portfolio'); ?>">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M17,4A2,2 0 0,1 19,6V10.5L22,12L19,13.5V18A2,2 0 0,1 17,20H7A2,2 0 0,1 5,18V13.5L2,12L5,10.5V6A2,2 0 0,1 7,4H17M7,6V18H17V6H7Z"/>
            </svg>
            <span class="sticky-text"><?php esc_html_e('注目', 'kei-portfolio'); ?></span>
        </div>
    <?php endif; ?>

    <?php if ($args['show_thumbnail'] && has_post_thumbnail($post_id)) : ?>
        <div class="post-card__thumbnail" role="img" aria-labelledby="post-title-<?php echo esc_attr($post_id); ?>">
            <a href="<?php echo esc_url(get_permalink($post_id)); ?>" 
               class="post-card__thumbnail-link"
               aria-hidden="true" 
               tabindex="-1">
                <?php echo get_the_post_thumbnail($post_id, $args['thumbnail_size'], array(
                    'class' => 'post-card__image',
                    'loading' => 'lazy',
                    'decoding' => 'async'
                )); ?>
            </a>
            
            <?php if ($args['show_category'] && !empty($categories)) : ?>
                <div class="post-card__category-overlay">
                    <?php foreach (array_slice($categories, 0, 2) as $category) : ?>
                        <a href="<?php echo esc_url(get_category_link($category->term_id)); ?>" 
                           class="post-card__category-tag"
                           style="background-color: <?php echo esc_attr(get_term_meta($category->term_id, 'category_color', true) ?: '#007cba'); ?>">
                            <?php echo esc_html($category->name); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="post-card__content">
        
        <?php if ($args['show_meta'] && ($args['show_date'] || $args['show_author'] || $args['show_read_time'] || $args['show_views'])) : ?>
            <div class="post-card__meta" role="group" aria-label="<?php esc_attr_e('投稿メタ情報', 'kei-portfolio'); ?>">
                
                <?php if ($args['show_date']) : ?>
                    <time class="post-card__date" 
                          datetime="<?php echo esc_attr($post_date_gmt); ?>"
                          title="<?php echo esc_attr($post_date); ?>">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M19,3H18V1H16V3H8V1H6V3H5A2,2 0 0,0 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V5A2,2 0 0,0 19,3M19,19H5V8H19V19Z"/>
                        </svg>
                        <?php echo esc_html(get_the_date('Y.m.d', $post_id)); ?>
                    </time>
                <?php endif; ?>

                <?php if ($args['show_author']) : ?>
                    <div class="post-card__author">
                        <img src="<?php echo esc_url($author_avatar); ?>" 
                             alt="<?php echo esc_attr($author_name); ?>"
                             class="post-card__author-avatar"
                             width="20" 
                             height="20"
                             loading="lazy">
                        <span class="post-card__author-name">
                            <a href="<?php echo esc_url(get_author_posts_url($author_id)); ?>" 
                               class="post-card__author-link">
                                <?php echo esc_html($author_name); ?>
                            </a>
                        </span>
                    </div>
                <?php endif; ?>

                <?php if ($args['show_read_time'] && $reading_time > 0) : ?>
                    <div class="post-card__read-time" title="<?php esc_attr_e('推定読了時間', 'kei-portfolio'); ?>">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M12,20A8,8 0 0,0 20,12A8,8 0 0,0 12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22C6.47,22 2,17.5 2,12A10,10 0 0,1 12,2M12.5,7V12.25L17,14.92L16.25,16.15L11,13V7H12.5Z"/>
                        </svg>
                        <?php printf(_n('%d分', '%d分', $reading_time, 'kei-portfolio'), $reading_time); ?>
                    </div>
                <?php endif; ?>

                <?php if ($args['show_views'] && $view_count > 0) : ?>
                    <div class="post-card__views" title="<?php esc_attr_e('閲覧数', 'kei-portfolio'); ?>">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12A3,3 0 0,0 12,9M12,17A5,5 0 0,1 7,12A5,5 0 0,1 12,7A5,5 0 0,1 17,12A5,5 0 0,1 12,17M12,4.5C7,4.5 2.73,7.61 1,12C2.73,16.39 7,19.5 12,19.5C17,19.5 21.27,16.39 23,12C21.27,7.61 17,4.5 12,4.5Z"/>
                        </svg>
                        <?php echo number_format_i18n($view_count); ?>
                    </div>
                <?php endif; ?>

                <?php if ($args['show_comments'] && $comment_count > 0) : ?>
                    <div class="post-card__comments">
                        <a href="<?php echo esc_url(get_comments_link($post_id)); ?>" 
                           class="post-card__comments-link"
                           title="<?php esc_attr_e('コメントを見る', 'kei-portfolio'); ?>">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M9,22A1,1 0 0,1 8,21V18H4A2,2 0 0,1 2,16V4C2,2.89 2.9,2 4,2H20A2,2 0 0,1 22,4V16A2,2 0 0,1 20,18H13.9L10.2,21.71C10,21.9 9.75,22 9.5,22V22H9Z"/>
                            </svg>
                            <?php printf(_n('%dコメント', '%dコメント', $comment_count, 'kei-portfolio'), $comment_count); ?>
                        </a>
                    </div>
                <?php endif; ?>

            </div>
        <?php endif; ?>

        <header class="post-card__header">
            <h2 class="post-card__title" id="post-title-<?php echo esc_attr($post_id); ?>">
                <a href="<?php echo esc_url(get_permalink($post_id)); ?>" 
                   class="post-card__title-link"
                   rel="bookmark">
                    <?php echo esc_html(get_the_title($post_id)); ?>
                </a>
            </h2>
        </header>

        <?php if ($args['show_excerpt'] && $excerpt) : ?>
            <div class="post-card__excerpt">
                <p><?php echo esc_html($excerpt); ?></p>
            </div>
        <?php endif; ?>

        <?php if (!$args['show_thumbnail'] && $args['show_category'] && !empty($categories)) : ?>
            <div class="post-card__categories">
                <?php foreach (array_slice($categories, 0, 3) as $category) : ?>
                    <a href="<?php echo esc_url(get_category_link($category->term_id)); ?>" 
                       class="post-card__category-link">
                        <?php echo esc_html($category->name); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($args['show_tags'] && !empty($tags)) : ?>
            <div class="post-card__tags">
                <span class="post-card__tags-label" aria-hidden="true">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M5.5,9A1.5,1.5 0 0,0 7,7.5A1.5,1.5 0 0,0 5.5,6A1.5,1.5 0 0,0 4,7.5A1.5,1.5 0 0,0 5.5,9M17.41,11.58C17.77,11.94 18,12.44 18,13C18,13.56 17.77,14.06 17.41,14.42L12.42,19.41C12.06,19.77 11.56,20 11,20C10.44,20 9.94,19.77 9.58,19.41L2.59,12.42C2.22,12.06 2,11.56 2,11V4C2,2.89 2.89,2 4,2H11C11.56,2 12.06,2.22 12.42,2.59L19.41,9.58C19.77,9.94 20,10.44 20,11C20,11.56 19.77,12.06 19.41,12.42L17.41,11.58Z"/>
                    </svg>
                </span>
                
                <div class="post-card__tags-list">
                    <?php foreach (array_slice($tags, 0, 4) as $tag) : ?>
                        <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>" 
                           class="post-card__tag-link">
                            <?php echo esc_html($tag->name); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <footer class="post-card__footer">
            <a href="<?php echo esc_url(get_permalink($post_id)); ?>" 
               class="post-card__read-more"
               aria-label="<?php printf(esc_attr__('%sの記事を読む', 'kei-portfolio'), get_the_title($post_id)); ?>">
                <?php esc_html_e('続きを読む', 'kei-portfolio'); ?>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M4,11V13H16L10.5,18.5L11.92,19.92L19.84,12L11.92,4.08L10.5,5.5L16,11H4Z"/>
                </svg>
            </a>
        </footer>

    </div>
</div>

<style>
/* 投稿カードのベーススタイル */
.post-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    position: relative;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.post-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

/* スティッキー投稿バッジ */
.post-card__sticky-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: #ff6b35;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.25rem;
    z-index: 2;
    box-shadow: 0 2px 8px rgba(255, 107, 53, 0.3);
}

.sticky-text {
    line-height: 1;
}

/* サムネイル */
.post-card__thumbnail {
    position: relative;
    overflow: hidden;
    aspect-ratio: 16/9;
    flex-shrink: 0;
}

.post-card__thumbnail-link {
    display: block;
    height: 100%;
    width: 100%;
}

.post-card__image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.post-card:hover .post-card__image {
    transform: scale(1.05);
}

.post-card__category-overlay {
    position: absolute;
    bottom: 0.75rem;
    left: 0.75rem;
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.post-card__category-tag {
    background: #007cba;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.post-card__category-tag:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    color: white;
}

/* コンテンツエリア */
.post-card__content {
    padding: 1.5rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}

/* メタ情報 */
.post-card__meta {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
    font-size: 0.8125rem;
    color: #666;
    flex-wrap: wrap;
}

.post-card__date,
.post-card__author,
.post-card__read-time,
.post-card__views,
.post-card__comments {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.post-card__author-avatar {
    border-radius: 50%;
    border: 2px solid #f0f0f0;
}

.post-card__author-link,
.post-card__comments-link {
    color: #666;
    text-decoration: none;
    transition: color 0.2s ease;
}

.post-card__author-link:hover,
.post-card__comments-link:hover {
    color: #007cba;
}

/* タイトル */
.post-card__header {
    margin-bottom: 1rem;
}

.post-card__title {
    font-size: 1.25rem;
    line-height: 1.4;
    margin: 0;
    font-weight: 600;
}

.post-card__title-link {
    color: #333;
    text-decoration: none;
    transition: color 0.2s ease;
}

.post-card__title-link:hover {
    color: #007cba;
}

/* 抜粋 */
.post-card__excerpt {
    margin-bottom: 1rem;
    flex: 1;
}

.post-card__excerpt p {
    color: #666;
    line-height: 1.6;
    margin: 0;
}

/* カテゴリー（サムネイルなしの場合） */
.post-card__categories {
    margin-bottom: 1rem;
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.post-card__category-link {
    background: #f8f9fa;
    color: #007cba;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    text-decoration: none;
    transition: all 0.2s ease;
    border: 1px solid #e9ecef;
}

.post-card__category-link:hover {
    background: #007cba;
    color: white;
    border-color: #007cba;
}

/* タグ */
.post-card__tags {
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.post-card__tags-label {
    color: #999;
    flex-shrink: 0;
}

.post-card__tags-list {
    display: flex;
    gap: 0.375rem;
    flex-wrap: wrap;
}

.post-card__tag-link {
    background: #f1f3f4;
    color: #5f6368;
    padding: 0.125rem 0.5rem;
    border-radius: 12px;
    font-size: 0.6875rem;
    text-decoration: none;
    transition: all 0.2s ease;
}

.post-card__tag-link:hover {
    background: #e8eaed;
    color: #333;
}

/* フッター */
.post-card__footer {
    margin-top: auto;
}

.post-card__read-more {
    color: #007cba;
    text-decoration: none;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
    font-size: 0.875rem;
}

.post-card__read-more:hover {
    color: #005a87;
    gap: 0.75rem;
}

/* リストレイアウト */
.post-card--list {
    flex-direction: row;
    align-items: stretch;
}

.post-card--list .post-card__thumbnail {
    width: 300px;
    aspect-ratio: 4/3;
    flex-shrink: 0;
}

.post-card--list .post-card__content {
    padding: 2rem;
    flex: 1;
}

.post-card--list .post-card__title {
    font-size: 1.5rem;
    margin-bottom: 1rem;
}

.post-card--list .post-card__excerpt {
    font-size: 1rem;
}

/* フィーチャーレイアウト */
.post-card--featured {
    grid-column: 1 / -1;
    flex-direction: row;
    min-height: 400px;
}

.post-card--featured .post-card__thumbnail {
    width: 50%;
    aspect-ratio: 16/10;
}

.post-card--featured .post-card__content {
    padding: 3rem;
    width: 50%;
    justify-content: center;
}

.post-card--featured .post-card__title {
    font-size: 2rem;
    line-height: 1.3;
}

.post-card--featured .post-card__excerpt {
    font-size: 1.125rem;
}

/* レスポンシブ対応 */
@media (max-width: 1024px) {
    .post-card--list {
        flex-direction: column;
    }
    
    .post-card--list .post-card__thumbnail {
        width: 100%;
        aspect-ratio: 16/9;
    }
    
    .post-card--list .post-card__content {
        padding: 1.5rem;
    }
    
    .post-card--featured {
        flex-direction: column;
        min-height: auto;
    }
    
    .post-card--featured .post-card__thumbnail,
    .post-card--featured .post-card__content {
        width: 100%;
    }
    
    .post-card--featured .post-card__content {
        padding: 2rem;
    }
    
    .post-card--featured .post-card__title {
        font-size: 1.5rem;
    }
}

@media (max-width: 768px) {
    .post-card {
        border-radius: 8px;
    }
    
    .post-card__content {
        padding: 1.25rem;
    }
    
    .post-card__title {
        font-size: 1.125rem;
    }
    
    .post-card__meta {
        gap: 0.75rem;
        font-size: 0.75rem;
    }
    
    .post-card--featured .post-card__content {
        padding: 1.5rem;
    }
    
    .post-card--featured .post-card__title {
        font-size: 1.375rem;
    }
}

@media (max-width: 480px) {
    .post-card__content {
        padding: 1rem;
    }
    
    .post-card__meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .post-card__sticky-badge {
        top: 0.75rem;
        right: 0.75rem;
        font-size: 0.6875rem;
        padding: 0.125rem 0.375rem;
    }
}

/* アクセシビリティ向上 */
@media (prefers-reduced-motion: reduce) {
    .post-card,
    .post-card__image,
    .post-card__category-tag,
    .post-card__read-more,
    .post-card__author-link,
    .post-card__comments-link,
    .post-card__category-link,
    .post-card__tag-link,
    .post-card__title-link {
        transition: none;
    }
    
    .post-card:hover {
        transform: none;
    }
    
    .post-card:hover .post-card__image {
        transform: none;
    }
}

/* フォーカススタイル */
.post-card__title-link:focus,
.post-card__read-more:focus,
.post-card__author-link:focus,
.post-card__comments-link:focus,
.post-card__category-tag:focus,
.post-card__category-link:focus,
.post-card__tag-link:focus {
    outline: 2px solid #007cba;
    outline-offset: 2px;
}

.post-card__thumbnail-link:focus {
    outline: 3px solid #007cba;
    outline-offset: 3px;
}

/* 高コントラストモード */
@media (prefers-contrast: high) {
    .post-card {
        border: 2px solid #333;
    }
    
    .post-card__category-tag,
    .post-card__category-link,
    .post-card__tag-link {
        border: 1px solid currentColor;
    }
}

/* プリントスタイル */
@media print {
    .post-card {
        box-shadow: none;
        border: 1px solid #ccc;
        break-inside: avoid;
    }
    
    .post-card__sticky-badge,
    .post-card__category-overlay,
    .post-card__read-more {
        display: none;
    }
    
    .post-card__title-link,
    .post-card__author-link,
    .post-card__comments-link,
    .post-card__category-link,
    .post-card__tag-link {
        color: black !important;
        text-decoration: underline;
    }
}

/* ダークモード対応 */
@media (prefers-color-scheme: dark) {
    .post-card {
        background: #1a1a1a;
        color: #e0e0e0;
        border: 1px solid #333;
    }
    
    .post-card__title-link {
        color: #f0f0f0;
    }
    
    .post-card__title-link:hover {
        color: #4db8e8;
    }
    
    .post-card__excerpt p {
        color: #b0b0b0;
    }
    
    .post-card__meta {
        color: #888;
    }
    
    .post-card__author-link:hover,
    .post-card__comments-link:hover,
    .post-card__read-more {
        color: #4db8e8;
    }
    
    .post-card__read-more:hover {
        color: #66c2e8;
    }
    
    .post-card__category-link {
        background: #2a2a2a;
        color: #4db8e8;
        border-color: #444;
    }
    
    .post-card__category-link:hover {
        background: #4db8e8;
        color: #1a1a1a;
    }
    
    .post-card__tag-link {
        background: #2a2a2a;
        color: #b0b0b0;
    }
    
    .post-card__tag-link:hover {
        background: #3a3a3a;
        color: #e0e0e0;
    }
}
</style>

<?php wp_reset_postdata(); ?>
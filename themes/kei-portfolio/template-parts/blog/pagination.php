<?php
/**
 * ブログページネーションテンプレートパーツ
 * 
 * ページネーション機能を提供するテンプレートパーツ
 * 数値ページネーション、前後ページリンク、無限スクロール対応
 * 
 * @param array $args パラメータ配列
 *   - query (WP_Query): クエリオブジェクト
 *   - type (string): 'numbers'|'prev_next'|'load_more' - ページネーションタイプ
 *   - show_numbers (bool): ページ番号表示フラグ
 *   - show_prev_next (bool): 前後リンク表示フラグ
 *   - show_first_last (bool): 最初・最後のページリンク表示フラグ
 *   - max_pages (int): 表示する最大ページ数
 *   - prev_text (string): 前ページのテキスト
 *   - next_text (string): 次ページのテキスト
 *   - first_text (string): 最初のページのテキスト
 *   - last_text (string): 最後のページのテキスト
 *   - load_more_text (string): もっと見るボタンのテキスト
 *   - before_number (string): ページ番号の前に表示するテキスト
 *   - after_number (string): ページ番号の後に表示するテキスト
 * 
 * @package kei-portfolio
 * @since 1.0.0
 */

// デフォルトパラメータ設定
$defaults = array(
    'query' => null,
    'type' => 'numbers',
    'show_numbers' => true,
    'show_prev_next' => true,
    'show_first_last' => true,
    'max_pages' => 5,
    'prev_text' => __('前のページ', 'kei-portfolio'),
    'next_text' => __('次のページ', 'kei-portfolio'),
    'first_text' => __('最初', 'kei-portfolio'),
    'last_text' => __('最後', 'kei-portfolio'),
    'load_more_text' => __('もっと見る', 'kei-portfolio'),
    'before_number' => '',
    'after_number' => ''
);

$args = wp_parse_args($args ?? array(), $defaults);

// クエリオブジェクトの設定
if ($args['query'] instanceof WP_Query) {
    $query = $args['query'];
} else {
    global $wp_query;
    $query = $wp_query;
}

// ページネーション情報の取得
$current_page = max(1, get_query_var('paged') ?: 1);
$total_pages = $query->max_num_pages;

// 1ページしかない場合は表示しない
if ($total_pages <= 1) {
    return;
}

// ページネーションのベースURL
$base_url = get_pagenum_link(1);
$base_url = remove_query_arg(array('paged'), $base_url);

// 表示するページ番号の計算
$start_page = max(1, $current_page - floor($args['max_pages'] / 2));
$end_page = min($total_pages, $start_page + $args['max_pages'] - 1);

// 開始ページを調整
if ($end_page - $start_page + 1 < $args['max_pages']) {
    $start_page = max(1, $end_page - $args['max_pages'] + 1);
}

// ページネーションタイプに応じた処理
if ($args['type'] === 'load_more') :
    // Load Moreボタン形式
?>
    <div class="blog-pagination blog-pagination--load-more" role="navigation" aria-label="<?php esc_attr_e('ページネーション', 'kei-portfolio'); ?>">
        <?php if ($current_page < $total_pages) : ?>
            <div class="pagination-load-more">
                <button type="button" 
                        class="load-more-button" 
                        data-page="<?php echo esc_attr($current_page + 1); ?>"
                        data-max-pages="<?php echo esc_attr($total_pages); ?>"
                        data-base-url="<?php echo esc_url($base_url); ?>"
                        aria-label="<?php esc_attr_e('次のページを読み込む', 'kei-portfolio'); ?>">
                    <span class="load-more-text"><?php echo esc_html($args['load_more_text']); ?></span>
                    <span class="load-more-spinner" style="display: none;" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12,1A11,11,0,1,0,23,12,11,11,0,0,0,12,1Zm0,19a8,8,0,1,1,8-8A8,8,0,0,1,12,20Z" opacity=".25"/>
                            <path d="M12,4a8,8,0,0,1,7.89,6.7A1.53,1.53,0,0,0,21.38,12h0a1.5,1.5,0,0,0,1.48-1.75,11,11,0,0,0-21.72,0A1.5,1.5,0,0,0,2.62,12h0a1.53,1.53,0,0,0,1.49-1.3A8,8,0,0,1,12,4Z">
                                <animateTransform attributeName="transform" dur="0.75s" repeatCount="indefinite" type="rotate" values="0 12 12;360 12 12"/>
                            </path>
                        </svg>
                    </span>
                </button>
            </div>
        <?php endif; ?>
        
        <div class="pagination-info">
            <span class="current-info">
                <?php printf(
                    __('%1$d / %2$d ページ', 'kei-portfolio'),
                    '<span class="current-page">' . number_format_i18n($current_page) . '</span>',
                    '<span class="total-pages">' . number_format_i18n($total_pages) . '</span>'
                ); ?>
            </span>
        </div>
    </div>

<?php elseif ($args['type'] === 'prev_next') : ?>
    <!-- 前後ページのみ -->
    <nav class="blog-pagination blog-pagination--prev-next" role="navigation" aria-label="<?php esc_attr_e('ページネーション', 'kei-portfolio'); ?>">
        <div class="pagination-links">
            
            <?php if ($current_page > 1) : ?>
                <a href="<?php echo esc_url(get_pagenum_link($current_page - 1)); ?>" 
                   class="pagination-link pagination-prev"
                   rel="prev"
                   aria-label="<?php printf(esc_attr__('%dページ目へ', 'kei-portfolio'), $current_page - 1); ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M15.41,7.41L14,6L8,12L14,18L15.41,16.59L10.83,12L15.41,7.41Z"/>
                    </svg>
                    <span class="pagination-text"><?php echo esc_html($args['prev_text']); ?></span>
                </a>
            <?php else : ?>
                <span class="pagination-link pagination-prev is-disabled" aria-disabled="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M15.41,7.41L14,6L8,12L14,18L15.41,16.59L10.83,12L15.41,7.41Z"/>
                    </svg>
                    <span class="pagination-text"><?php echo esc_html($args['prev_text']); ?></span>
                </span>
            <?php endif; ?>

            <span class="pagination-current" aria-current="page" aria-label="<?php printf(esc_attr__('現在のページ: %d / %d', 'kei-portfolio'), $current_page, $total_pages); ?>">
                <?php printf(__('%1$d / %2$d', 'kei-portfolio'), $current_page, $total_pages); ?>
            </span>

            <?php if ($current_page < $total_pages) : ?>
                <a href="<?php echo esc_url(get_pagenum_link($current_page + 1)); ?>" 
                   class="pagination-link pagination-next"
                   rel="next"
                   aria-label="<?php printf(esc_attr__('%dページ目へ', 'kei-portfolio'), $current_page + 1); ?>">
                    <span class="pagination-text"><?php echo esc_html($args['next_text']); ?></span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M10,6L8.59,7.41L13.17,12L8.59,16.59L10,18L16,12L10,6Z"/>
                    </svg>
                </a>
            <?php else : ?>
                <span class="pagination-link pagination-next is-disabled" aria-disabled="true">
                    <span class="pagination-text"><?php echo esc_html($args['next_text']); ?></span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M10,6L8.59,7.41L13.17,12L8.59,16.59L10,18L16,12L10,6Z"/>
                    </svg>
                </span>
            <?php endif; ?>

        </div>
    </nav>

<?php else : ?>
    <!-- 数値ページネーション -->
    <nav class="blog-pagination blog-pagination--numbers" role="navigation" aria-label="<?php esc_attr_e('ページネーション', 'kei-portfolio'); ?>">
        <div class="pagination-links">
            
            <?php 
            // 最初のページリンク
            if ($args['show_first_last'] && $current_page > 2 && $start_page > 1) : 
            ?>
                <a href="<?php echo esc_url(get_pagenum_link(1)); ?>" 
                   class="pagination-link pagination-first"
                   aria-label="<?php esc_attr_e('最初のページへ', 'kei-portfolio'); ?>">
                    <?php echo esc_html($args['first_text']); ?>
                </a>
                
                <?php if ($start_page > 2) : ?>
                    <span class="pagination-ellipsis" aria-hidden="true">...</span>
                <?php endif; ?>
            <?php endif; ?>

            <?php 
            // 前のページリンク
            if ($args['show_prev_next'] && $current_page > 1) : 
            ?>
                <a href="<?php echo esc_url(get_pagenum_link($current_page - 1)); ?>" 
                   class="pagination-link pagination-prev"
                   rel="prev"
                   aria-label="<?php printf(esc_attr__('%dページ目へ', 'kei-portfolio'), $current_page - 1); ?>">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M15.41,7.41L14,6L8,12L14,18L15.41,16.59L10.83,12L15.41,7.41Z"/>
                    </svg>
                    <span class="screen-reader-text"><?php echo esc_html($args['prev_text']); ?></span>
                </a>
            <?php endif; ?>

            <?php 
            // ページ番号
            if ($args['show_numbers']) :
                for ($i = $start_page; $i <= $end_page; $i++) :
                    if ($i == $current_page) :
            ?>
                        <span class="pagination-link pagination-current is-current" 
                              aria-current="page"
                              aria-label="<?php printf(esc_attr__('現在のページ: %d', 'kei-portfolio'), $i); ?>">
                            <?php echo esc_html($args['before_number'] . number_format_i18n($i) . $args['after_number']); ?>
                        </span>
            <?php   else : ?>
                        <a href="<?php echo esc_url(get_pagenum_link($i)); ?>" 
                           class="pagination-link pagination-number"
                           aria-label="<?php printf(esc_attr__('%dページ目へ', 'kei-portfolio'), $i); ?>">
                            <?php echo esc_html($args['before_number'] . number_format_i18n($i) . $args['after_number']); ?>
                        </a>
            <?php   
                    endif;
                endfor;
            endif; 
            ?>

            <?php 
            // 次のページリンク
            if ($args['show_prev_next'] && $current_page < $total_pages) : 
            ?>
                <a href="<?php echo esc_url(get_pagenum_link($current_page + 1)); ?>" 
                   class="pagination-link pagination-next"
                   rel="next"
                   aria-label="<?php printf(esc_attr__('%dページ目へ', 'kei-portfolio'), $current_page + 1); ?>">
                    <span class="screen-reader-text"><?php echo esc_html($args['next_text']); ?></span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M10,6L8.59,7.41L13.17,12L8.59,16.59L10,18L16,12L10,6Z"/>
                    </svg>
                </a>
            <?php endif; ?>

            <?php 
            // 最後のページリンク
            if ($args['show_first_last'] && $current_page < $total_pages - 1 && $end_page < $total_pages) : 
            ?>
                <?php if ($end_page < $total_pages - 1) : ?>
                    <span class="pagination-ellipsis" aria-hidden="true">...</span>
                <?php endif; ?>
                
                <a href="<?php echo esc_url(get_pagenum_link($total_pages)); ?>" 
                   class="pagination-link pagination-last"
                   aria-label="<?php esc_attr_e('最後のページへ', 'kei-portfolio'); ?>">
                    <?php echo esc_html($args['last_text']); ?>
                </a>
            <?php endif; ?>

        </div>

        <!-- ページ情報 -->
        <div class="pagination-info">
            <span class="page-info">
                <?php printf(
                    __('全 %1$s 件中 %2$s - %3$s 件を表示', 'kei-portfolio'),
                    '<strong>' . number_format_i18n($query->found_posts) . '</strong>',
                    '<strong>' . number_format_i18n((($current_page - 1) * $query->get('posts_per_page')) + 1) . '</strong>',
                    '<strong>' . number_format_i18n(min($current_page * $query->get('posts_per_page'), $query->found_posts)) . '</strong>'
                ); ?>
            </span>
        </div>
    </nav>
<?php endif; ?>

<style>
/* ページネーションのベーススタイル */
.blog-pagination {
    margin: 3rem 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.pagination-links {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    justify-content: center;
}

/* 共通リンクスタイル */
.pagination-link {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 44px;
    min-height: 44px;
    padding: 0.5rem;
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    color: #495057;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s ease;
    position: relative;
    font-size: 0.875rem;
}

.pagination-link:hover {
    background: #007cba;
    border-color: #007cba;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 124, 186, 0.2);
}

.pagination-link:active {
    transform: translateY(0);
    box-shadow: 0 1px 4px rgba(0, 124, 186, 0.2);
}

.pagination-link.is-current {
    background: #007cba;
    border-color: #007cba;
    color: white;
    font-weight: 600;
}

.pagination-link.is-disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}

/* 前後リンク */
.pagination-prev,
.pagination-next {
    padding: 0.5rem 1rem;
    gap: 0.5rem;
}

.pagination-first,
.pagination-last {
    padding: 0.5rem 1rem;
    font-size: 0.8125rem;
}

/* 省略記号 */
.pagination-ellipsis {
    padding: 0.5rem;
    color: #6c757d;
    font-weight: 500;
    user-select: none;
}

/* ページ情報 */
.pagination-info {
    text-align: center;
    color: #6c757d;
    font-size: 0.875rem;
    line-height: 1.5;
}

.page-info strong,
.current-info .current-page,
.current-info .total-pages {
    color: #007cba;
    font-weight: 600;
}

/* Load Moreスタイル */
.blog-pagination--load-more {
    text-align: center;
}

.pagination-load-more {
    margin-bottom: 1rem;
}

.load-more-button {
    background: linear-gradient(135deg, #007cba 0%, #005a87 100%);
    color: white;
    border: none;
    padding: 1rem 2rem;
    border-radius: 50px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    box-shadow: 0 4px 15px rgba(0, 124, 186, 0.3);
    position: relative;
    overflow: hidden;
}

.load-more-button::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s ease;
}

.load-more-button:hover::before {
    left: 100%;
}

.load-more-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 124, 186, 0.4);
}

.load-more-button:active {
    transform: translateY(0);
}

.load-more-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.load-more-text {
    transition: opacity 0.2s ease;
}

.load-more-button.is-loading .load-more-text {
    opacity: 0.5;
}

.load-more-spinner svg {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* 前後のみページネーション */
.blog-pagination--prev-next .pagination-links {
    justify-content: space-between;
    max-width: 400px;
    width: 100%;
}

.blog-pagination--prev-next .pagination-current {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 0.5rem 1rem;
    font-weight: 600;
    color: #495057;
}

/* レスポンシブ対応 */
@media (max-width: 768px) {
    .blog-pagination {
        margin: 2rem 0;
    }
    
    .pagination-links {
        gap: 0.375rem;
    }
    
    .pagination-link {
        min-width: 40px;
        min-height: 40px;
        font-size: 0.8125rem;
    }
    
    .pagination-prev,
    .pagination-next {
        padding: 0.375rem 0.75rem;
    }
    
    .pagination-first,
    .pagination-last {
        display: none;
    }
    
    .load-more-button {
        padding: 0.875rem 1.5rem;
        font-size: 0.9375rem;
    }
    
    .pagination-info {
        font-size: 0.8125rem;
    }
}

@media (max-width: 480px) {
    .pagination-links {
        flex-wrap: wrap;
        max-width: 100%;
    }
    
    .pagination-link {
        min-width: 36px;
        min-height: 36px;
        font-size: 0.75rem;
    }
    
    .pagination-prev .pagination-text,
    .pagination-next .pagination-text {
        display: none;
    }
    
    .blog-pagination--prev-next .pagination-links {
        flex-direction: row;
        justify-content: space-between;
    }
    
    .blog-pagination--prev-next .pagination-current {
        order: -1;
        width: 100%;
        text-align: center;
        margin-bottom: 1rem;
    }
    
    .load-more-button {
        width: 100%;
        max-width: 300px;
        justify-content: center;
    }
}

/* アクセシビリティ向上 */
@media (prefers-reduced-motion: reduce) {
    .pagination-link,
    .load-more-button {
        transition: none;
        transform: none;
    }
    
    .pagination-link:hover,
    .pagination-link:active,
    .load-more-button:hover,
    .load-more-button:active {
        transform: none;
    }
    
    .load-more-button::before {
        display: none;
    }
    
    .load-more-spinner svg {
        animation: none;
    }
}

/* フォーカススタイル */
.pagination-link:focus,
.load-more-button:focus {
    outline: 3px solid #007cba;
    outline-offset: 2px;
}

/* 高コントラストモード */
@media (prefers-contrast: high) {
    .pagination-link {
        border-width: 3px;
        font-weight: 600;
    }
    
    .pagination-link:hover,
    .pagination-link.is-current {
        border-color: currentColor;
    }
    
    .load-more-button {
        border: 3px solid white;
    }
}

/* プリントスタイル */
@media print {
    .blog-pagination {
        display: none;
    }
}

/* ダークモード対応 */
@media (prefers-color-scheme: dark) {
    .pagination-link {
        background: #2a2a2a;
        border-color: #555;
        color: #e0e0e0;
    }
    
    .pagination-link:hover {
        background: #4db8e8;
        border-color: #4db8e8;
        color: #1a1a1a;
    }
    
    .pagination-link.is-current {
        background: #4db8e8;
        border-color: #4db8e8;
        color: #1a1a1a;
    }
    
    .pagination-ellipsis {
        color: #aaa;
    }
    
    .pagination-info {
        color: #aaa;
    }
    
    .page-info strong,
    .current-info .current-page,
    .current-info .total-pages {
        color: #4db8e8;
    }
    
    .blog-pagination--prev-next .pagination-current {
        background: #333;
        border-color: #555;
        color: #e0e0e0;
    }
    
    .load-more-button {
        background: linear-gradient(135deg, #4db8e8 0%, #369bc7 100%);
        box-shadow: 0 4px 15px rgba(77, 184, 232, 0.3);
    }
    
    .load-more-button:hover {
        box-shadow: 0 6px 20px rgba(77, 184, 232, 0.4);
    }
}

/* カスタムプロパティ（CSS変数） */
:root {
    --pagination-primary-color: #007cba;
    --pagination-hover-color: #005a87;
    --pagination-border-color: #e9ecef;
    --pagination-text-color: #495057;
    --pagination-disabled-opacity: 0.5;
    --pagination-border-radius: 8px;
    --pagination-spacing: 0.5rem;
    --pagination-min-touch-target: 44px;
}

/* カスタムプロパティを使用したスタイル */
.pagination-link {
    border-color: var(--pagination-border-color);
    color: var(--pagination-text-color);
    border-radius: var(--pagination-border-radius);
    gap: var(--pagination-spacing);
    min-width: var(--pagination-min-touch-target);
    min-height: var(--pagination-min-touch-target);
}

.pagination-link:hover {
    background: var(--pagination-primary-color);
    border-color: var(--pagination-primary-color);
}

.pagination-link.is-current {
    background: var(--pagination-primary-color);
    border-color: var(--pagination-primary-color);
}

.pagination-link.is-disabled {
    opacity: var(--pagination-disabled-opacity);
}

/* ページネーションのアニメーション */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.blog-pagination {
    animation: fadeInUp 0.5s ease-out;
}

/* JavaScript制御用のクラス */
.pagination-loading .pagination-link {
    pointer-events: none;
    opacity: 0.6;
}

.pagination-loading .load-more-button {
    pointer-events: none;
}

.pagination-ajax-loading .pagination-links {
    position: relative;
}

.pagination-ajax-loading .pagination-links::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(2px);
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--pagination-border-radius);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load Moreボタンの処理
    const loadMoreButton = document.querySelector('.load-more-button');
    if (loadMoreButton) {
        loadMoreButton.addEventListener('click', function() {
            const button = this;
            const nextPage = parseInt(button.dataset.page);
            const maxPages = parseInt(button.dataset.maxPages);
            const baseUrl = button.dataset.baseUrl;
            
            if (nextPage > maxPages) {
                return;
            }
            
            // ローディング状態に変更
            button.classList.add('is-loading');
            button.disabled = true;
            
            const loadingText = button.querySelector('.load-more-text');
            const spinner = button.querySelector('.load-more-spinner');
            const originalText = loadingText.textContent;
            
            loadingText.textContent = '読み込み中...';
            spinner.style.display = 'inline-block';
            
            // AJAX リクエスト
            const url = nextPage === 1 ? baseUrl : `${baseUrl}page/${nextPage}/`;
            
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newPosts = doc.querySelectorAll('.blog-posts__item');
                
                if (newPosts.length > 0) {
                    // 投稿を追加
                    const container = document.querySelector('.blog-posts__grid, .blog-posts__list');
                    if (container) {
                        newPosts.forEach(post => {
                            // フェードインアニメーション
                            post.style.opacity = '0';
                            post.style.transform = 'translateY(20px)';
                            container.appendChild(post);
                            
                            // アニメーション実行
                            requestAnimationFrame(() => {
                                post.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                                post.style.opacity = '1';
                                post.style.transform = 'translateY(0)';
                            });
                        });
                    }
                    
                    // 次のページ番号を更新
                    button.dataset.page = nextPage + 1;
                    
                    // 最後のページに達した場合はボタンを非表示
                    if (nextPage >= maxPages) {
                        button.style.display = 'none';
                    }
                    
                    // ページ情報を更新
                    const pageInfo = document.querySelector('.current-page');
                    if (pageInfo) {
                        pageInfo.textContent = nextPage;
                    }
                    
                    // Google Analytics イベント送信（利用可能な場合）
                    if (typeof gtag !== 'undefined') {
                        gtag('event', 'load_more_posts', {
                            'event_category': 'engagement',
                            'event_label': `Page ${nextPage}`,
                            'value': nextPage
                        });
                    }
                }
            })
            .catch(error => {
                console.error('Load more error:', error);
                
                // エラー表示
                loadingText.textContent = 'エラーが発生しました';
                setTimeout(() => {
                    loadingText.textContent = originalText;
                    button.classList.remove('is-loading');
                    button.disabled = false;
                    spinner.style.display = 'none';
                }, 3000);
                
                return;
            })
            .finally(() => {
                // ローディング状態を解除
                button.classList.remove('is-loading');
                button.disabled = false;
                loadingText.textContent = originalText;
                spinner.style.display = 'none';
            });
        });
    }
    
    // ページネーションリンクのクリック時の処理（オプション）
    const paginationLinks = document.querySelectorAll('.pagination-link:not(.is-current):not(.is-disabled)');
    paginationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // ローディング状態を表示
            document.querySelector('.blog-pagination')?.classList.add('pagination-ajax-loading');
            
            // プリロード処理（オプション）
            if (typeof fetch !== 'undefined') {
                const href = this.href;
                if (href) {
                    fetch(href, { method: 'HEAD' }); // プリフェッチ
                }
            }
        });
    });
    
    // 無限スクロール（オプション）
    if (loadMoreButton && window.IntersectionObserver) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !loadMoreButton.disabled) {
                    // 自動的に次のページを読み込む
                    loadMoreButton.click();
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '100px'
        });
        
        observer.observe(loadMoreButton);
    }
    
    // キーボードナビゲーション（矢印キー）
    document.addEventListener('keydown', function(e) {
        if (e.target.closest('.blog-pagination')) {
            const currentLink = document.querySelector('.pagination-current');
            let targetLink = null;
            
            if (e.key === 'ArrowLeft') {
                targetLink = document.querySelector('.pagination-prev');
            } else if (e.key === 'ArrowRight') {
                targetLink = document.querySelector('.pagination-next');
            }
            
            if (targetLink && !targetLink.classList.contains('is-disabled')) {
                e.preventDefault();
                targetLink.click();
            }
        }
    });
    
    // URLのハッシュ更新（履歴管理）
    const updateUrlHash = (page) => {
        if (history.pushState && page > 1) {
            const newUrl = `${window.location.pathname}?page=${page}${window.location.hash}`;
            history.pushState({ page }, '', newUrl);
        }
    };
    
    // ブラウザの戻る/進むボタン対応
    window.addEventListener('popstate', function(e) {
        if (e.state && e.state.page) {
            location.reload(); // 簡単な実装：ページを再読み込み
        }
    });
});
</script>
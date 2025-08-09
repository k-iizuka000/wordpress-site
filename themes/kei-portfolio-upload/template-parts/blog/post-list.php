<?php
/**
 * ブログ投稿一覧テンプレートパーツ
 * 
 * 複数の投稿を一覧表示するためのテンプレートパーツ
 * グリッド表示とリスト表示に対応
 * 
 * @param array $args パラメータ配列
 *   - layout (string): 'grid'|'list'|'masonry' - レイアウトタイプ
 *   - columns (int): グリッド表示の列数（デフォルト: 3）
 *   - show_excerpt (bool): 抜粋表示フラグ
 *   - excerpt_length (int): 抜粋の文字数
 *   - show_meta (bool): メタ情報表示フラグ
 *   - show_thumbnail (bool): アイキャッチ画像表示フラグ
 *   - thumbnail_size (string): アイキャッチ画像サイズ
 *   - show_author (bool): 投稿者表示フラグ
 *   - show_date (bool): 投稿日表示フラグ
 *   - show_category (bool): カテゴリー表示フラグ
 *   - show_tags (bool): タグ表示フラグ
 *   - show_comments (bool): コメント数表示フラグ
 *   - posts_per_page (int): 表示する投稿数
 *   - post_query (WP_Query): カスタムクエリオブジェクト
 * 
 * @package kei-portfolio
 * @since 1.0.0
 */

// デフォルトパラメータ設定
$defaults = array(
    'layout' => 'grid',
    'columns' => 3,
    'show_excerpt' => true,
    'excerpt_length' => 120,
    'show_meta' => true,
    'show_thumbnail' => true,
    'thumbnail_size' => 'medium_large',
    'show_author' => true,
    'show_date' => true,
    'show_category' => true,
    'show_tags' => false,
    'show_comments' => true,
    'posts_per_page' => -1,
    'post_query' => null
);

$args = wp_parse_args($args ?? array(), $defaults);

// クエリオブジェクトの設定
if ($args['post_query'] instanceof WP_Query) {
    $query = $args['post_query'];
} else {
    global $wp_query;
    $query = $wp_query;
}

// レイアウト用のCSSクラス
$layout_class = 'blog-posts-' . esc_attr($args['layout']);
$grid_columns_class = $args['layout'] === 'grid' ? 'grid-cols-' . intval($args['columns']) : '';

// 投稿が存在しない場合の処理
if (!$query->have_posts()) :
?>
    <div class="blog-posts-empty" role="region" aria-label="<?php esc_attr_e('検索結果なし', 'kei-portfolio'); ?>">
        <div class="empty-state">
            <div class="empty-state__icon" aria-hidden="true">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
            </div>
            <h2 class="empty-state__title"><?php esc_html_e('投稿が見つかりませんでした', 'kei-portfolio'); ?></h2>
            <p class="empty-state__description">
                <?php if (is_search()) : ?>
                    <?php printf(__('「%s」に一致する投稿は見つかりませんでした。', 'kei-portfolio'), '<strong>' . esc_html(get_search_query()) . '</strong>'); ?>
                    <br>
                    <?php esc_html_e('別のキーワードで検索してみてください。', 'kei-portfolio'); ?>
                <?php elseif (is_category() || is_tag()) : ?>
                    <?php esc_html_e('このカテゴリー・タグには投稿がありません。', 'kei-portfolio'); ?>
                <?php else : ?>
                    <?php esc_html_e('現在、公開されている投稿はありません。', 'kei-portfolio'); ?>
                <?php endif; ?>
            </p>
            
            <div class="empty-state__actions">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn--primary">
                    <?php esc_html_e('ホームに戻る', 'kei-portfolio'); ?>
                </a>
                
                <?php if (is_search() || is_category() || is_tag()) : ?>
                    <a href="<?php echo esc_url(get_permalink(get_option('page_for_posts'))); ?>" class="btn btn--secondary">
                        <?php esc_html_e('すべての投稿を見る', 'kei-portfolio'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php 
    return;
endif;

// レイアウト切り替えボタン（オプション）
if (apply_filters('kei_portfolio_show_layout_switcher', true) && (is_home() || is_archive())) :
?>
    <div class="blog-layout-switcher" role="toolbar" aria-label="<?php esc_attr_e('表示レイアウト切り替え', 'kei-portfolio'); ?>">
        <span class="layout-switcher__label"><?php esc_html_e('表示:', 'kei-portfolio'); ?></span>
        
        <div class="layout-switcher__buttons">
            <button type="button" 
                    class="layout-switcher__button <?php echo $args['layout'] === 'grid' ? 'is-active' : ''; ?>"
                    data-layout="grid"
                    aria-pressed="<?php echo $args['layout'] === 'grid' ? 'true' : 'false'; ?>"
                    title="<?php esc_attr_e('グリッド表示', 'kei-portfolio'); ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M3 3v8h8V3H3zm6 6H5V5h4v4zm-6 4v8h8v-8H3zm6 6H5v-4h4v4zm4-16v8h8V3h-8zm6 6h-4V5h4v4zm-6 4v8h8v-8h-8zm6 6h-4v-4h4v4z"/>
                </svg>
                <span class="screen-reader-text"><?php esc_html_e('グリッド表示', 'kei-portfolio'); ?></span>
            </button>
            
            <button type="button" 
                    class="layout-switcher__button <?php echo $args['layout'] === 'list' ? 'is-active' : ''; ?>"
                    data-layout="list"
                    aria-pressed="<?php echo $args['layout'] === 'list' ? 'true' : 'false'; ?>"
                    title="<?php esc_attr_e('リスト表示', 'kei-portfolio'); ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 4h14v-2H7v2zm0 4h14v-2H7v2zM7 7v2h14V7H7z"/>
                </svg>
                <span class="screen-reader-text"><?php esc_html_e('リスト表示', 'kei-portfolio'); ?></span>
            </button>
        </div>
    </div>
<?php endif; ?>

<div class="blog-posts <?php echo esc_attr($layout_class); ?> <?php echo esc_attr($grid_columns_class); ?>" 
     role="main" 
     aria-label="<?php esc_attr_e('ブログ投稿一覧', 'kei-portfolio'); ?>">
    
    <?php if ($args['layout'] === 'grid' || $args['layout'] === 'masonry') : ?>
        <div class="blog-posts__grid" 
             data-columns="<?php echo esc_attr($args['columns']); ?>"
             data-layout="<?php echo esc_attr($args['layout']); ?>">
    <?php else : ?>
        <div class="blog-posts__list">
    <?php endif; ?>

        <?php while ($query->have_posts()) : $query->the_post(); ?>
            
            <article id="post-<?php the_ID(); ?>" 
                     <?php post_class('blog-posts__item'); ?>
                     role="article"
                     aria-labelledby="post-title-<?php the_ID(); ?>">
                
                <?php
                // 投稿カードテンプレートパーツを呼び出し
                get_template_part('template-parts/blog/post-card', '', array_merge($args, array(
                    'post_id' => get_the_ID(),
                    'layout' => $args['layout']
                )));
                ?>
                
            </article>

        <?php endwhile; ?>

    </div>
</div>

<?php
// 読み込み中インジケーター（Ajax読み込み用）
if (apply_filters('kei_portfolio_enable_infinite_scroll', false)) :
?>
    <div class="blog-loading" style="display: none;" aria-hidden="true">
        <div class="loading-spinner">
            <div class="spinner"></div>
            <span class="loading-text"><?php esc_html_e('読み込み中...', 'kei-portfolio'); ?></span>
        </div>
    </div>

    <div class="blog-load-more" style="display: none;">
        <button type="button" class="load-more-btn" data-page="1">
            <?php esc_html_e('もっと見る', 'kei-portfolio'); ?>
        </button>
    </div>
<?php endif; ?>

<style>
/* ブログ投稿一覧のスタイル */
.blog-posts {
    margin: 2rem 0;
}

/* 空の状態 */
.blog-posts-empty {
    padding: 4rem 0;
}

.empty-state {
    text-align: center;
    max-width: 600px;
    margin: 0 auto;
    padding: 2rem;
}

.empty-state__icon {
    color: #666;
    margin-bottom: 1.5rem;
}

.empty-state__title {
    font-size: 1.5rem;
    color: #333;
    margin-bottom: 1rem;
}

.empty-state__description {
    color: #666;
    line-height: 1.6;
    margin-bottom: 2rem;
}

.empty-state__actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.btn {
    display: inline-flex;
    align-items: center;
    padding: 0.75rem 1.5rem;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s ease;
    border: none;
    cursor: pointer;
    font-size: 0.875rem;
}

.btn--primary {
    background-color: #007cba;
    color: white;
}

.btn--primary:hover {
    background-color: #005a87;
    color: white;
}

.btn--secondary {
    background-color: transparent;
    color: #007cba;
    border: 2px solid #007cba;
}

.btn--secondary:hover {
    background-color: #007cba;
    color: white;
}

/* レイアウト切り替え */
.blog-layout-switcher {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.layout-switcher__label {
    font-weight: 500;
    color: #333;
}

.layout-switcher__buttons {
    display: flex;
    gap: 0.25rem;
    background: white;
    border-radius: 6px;
    padding: 0.25rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.layout-switcher__button {
    background: transparent;
    border: none;
    padding: 0.5rem;
    border-radius: 4px;
    cursor: pointer;
    color: #666;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.layout-switcher__button:hover {
    background: #f0f0f0;
    color: #333;
}

.layout-switcher__button.is-active {
    background: #007cba;
    color: white;
}

/* グリッドレイアウト */
.blog-posts-grid .blog-posts__grid {
    display: grid;
    gap: 2rem;
}

.blog-posts-grid.grid-cols-1 .blog-posts__grid {
    grid-template-columns: 1fr;
}

.blog-posts-grid.grid-cols-2 .blog-posts__grid {
    grid-template-columns: repeat(2, 1fr);
}

.blog-posts-grid.grid-cols-3 .blog-posts__grid {
    grid-template-columns: repeat(3, 1fr);
}

.blog-posts-grid.grid-cols-4 .blog-posts__grid {
    grid-template-columns: repeat(4, 1fr);
}

/* リストレイアウト */
.blog-posts-list .blog-posts__list {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.blog-posts-list .blog-posts__item {
    max-width: none;
}

/* Masonryレイアウト */
.blog-posts-masonry .blog-posts__grid {
    columns: var(--masonry-columns, 3);
    column-gap: 2rem;
}

.blog-posts-masonry .blog-posts__item {
    break-inside: avoid;
    margin-bottom: 2rem;
}

/* 読み込み中インジケーター */
.blog-loading {
    text-align: center;
    padding: 2rem;
}

.loading-spinner {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
}

.spinner {
    width: 24px;
    height: 24px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #007cba;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.loading-text {
    color: #666;
}

.blog-load-more {
    text-align: center;
    margin: 2rem 0;
}

.load-more-btn {
    background: #007cba;
    color: white;
    border: none;
    padding: 0.75rem 2rem;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    transition: background-color 0.2s ease;
}

.load-more-btn:hover {
    background: #005a87;
}

.load-more-btn:disabled {
    background: #ccc;
    cursor: not-allowed;
}

/* レスポンシブ対応 */
@media (max-width: 1024px) {
    .blog-posts-grid.grid-cols-4 .blog-posts__grid,
    .blog-posts-grid.grid-cols-3 .blog-posts__grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .blog-posts-masonry .blog-posts__grid {
        columns: 2;
    }
}

@media (max-width: 768px) {
    .blog-posts {
        margin: 1rem 0;
    }
    
    .blog-posts-grid .blog-posts__grid {
        gap: 1.5rem;
    }
    
    .blog-posts-list .blog-posts__list {
        gap: 1.5rem;
    }
    
    .blog-posts-grid.grid-cols-2 .blog-posts__grid,
    .blog-posts-grid.grid-cols-3 .blog-posts__grid,
    .blog-posts-grid.grid-cols-4 .blog-posts__grid {
        grid-template-columns: 1fr;
    }
    
    .blog-posts-masonry .blog-posts__grid {
        columns: 1;
    }
    
    .blog-layout-switcher {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 0.75rem;
    }
    
    .layout-switcher__buttons {
        align-self: stretch;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .empty-state {
        padding: 1rem;
    }
    
    .empty-state__title {
        font-size: 1.25rem;
    }
    
    .empty-state__actions {
        flex-direction: column;
        align-items: center;
    }
    
    .btn {
        width: 100%;
        max-width: 300px;
        justify-content: center;
    }
}

/* アクセシビリティ向上 */
@media (prefers-reduced-motion: reduce) {
    .spinner {
        animation: none;
    }
    
    .btn,
    .layout-switcher__button {
        transition: none;
    }
}

/* フォーカススタイル */
.btn:focus,
.layout-switcher__button:focus,
.load-more-btn:focus {
    outline: 2px solid #007cba;
    outline-offset: 2px;
}

/* 高コントラストモード */
@media (prefers-contrast: high) {
    .blog-layout-switcher {
        border: 2px solid currentColor;
    }
    
    .layout-switcher__button {
        border: 1px solid currentColor;
    }
    
    .btn {
        border: 2px solid currentColor;
    }
}

/* プリントスタイル */
@media print {
    .blog-layout-switcher,
    .blog-loading,
    .blog-load-more {
        display: none;
    }
    
    .blog-posts-grid .blog-posts__grid,
    .blog-posts-list .blog-posts__list {
        break-inside: avoid;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // レイアウト切り替え機能
    const layoutButtons = document.querySelectorAll('.layout-switcher__button');
    const postsContainer = document.querySelector('.blog-posts');
    
    layoutButtons.forEach(button => {
        button.addEventListener('click', function() {
            const newLayout = this.dataset.layout;
            
            // アクティブ状態を更新
            layoutButtons.forEach(btn => {
                btn.classList.remove('is-active');
                btn.setAttribute('aria-pressed', 'false');
            });
            
            this.classList.add('is-active');
            this.setAttribute('aria-pressed', 'true');
            
            // レイアウトクラスを更新
            postsContainer.className = postsContainer.className.replace(/blog-posts-\w+/g, '');
            postsContainer.classList.add('blog-posts-' + newLayout);
            
            // ローカルストレージに設定を保存
            localStorage.setItem('blog-layout-preference', newLayout);
        });
    });
    
    // 保存されたレイアウト設定を復元
    const savedLayout = localStorage.getItem('blog-layout-preference');
    if (savedLayout) {
        const savedButton = document.querySelector(`[data-layout="${savedLayout}"]`);
        if (savedButton) {
            savedButton.click();
        }
    }
    
    // 無限スクロール機能（オプション）
    if (document.querySelector('.load-more-btn')) {
        const loadMoreBtn = document.querySelector('.load-more-btn');
        const loadingIndicator = document.querySelector('.blog-loading');
        
        loadMoreBtn.addEventListener('click', function() {
            const currentPage = parseInt(this.dataset.page);
            const nextPage = currentPage + 1;
            
            // 読み込み中状態に切り替え
            this.style.display = 'none';
            loadingIndicator.style.display = 'block';
            
            // AJAX リクエスト（実装例）
            fetch(`${window.location.href}?paged=${nextPage}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newPosts = doc.querySelectorAll('.blog-posts__item');
                
                if (newPosts.length > 0) {
                    const container = document.querySelector('.blog-posts__grid, .blog-posts__list');
                    newPosts.forEach(post => container.appendChild(post));
                    
                    this.dataset.page = nextPage;
                    this.style.display = 'block';
                } else {
                    this.style.display = 'none';
                }
                
                loadingIndicator.style.display = 'none';
            })
            .catch(error => {
                console.error('Error loading more posts:', error);
                loadingIndicator.style.display = 'none';
                this.style.display = 'block';
            });
        });
    }
});
</script>
<?php
/**
 * 関連記事表示コンポーネント
 * 
 * カテゴリーとタグに基づいて関連記事を表示
 * 
 * @package Kei_Portfolio
 * @since 1.0.0
 */

// 現在の投稿でない場合は何も表示しない
if (!is_single() || get_post_type() !== 'post') {
    return;
}

$current_post_id = get_the_ID();
$categories = wp_get_post_categories($current_post_id);
$tags = wp_get_post_tags($current_post_id, ['fields' => 'ids']);

// 関連記事取得のクエリ
$related_posts = new WP_Query([
    'post_type' => 'post',
    'posts_per_page' => 6,
    'post__not_in' => [$current_post_id],
    'orderby' => 'rand',
    'post_status' => 'publish',
    'tax_query' => [
        'relation' => 'OR',
        [
            'taxonomy' => 'category',
            'field'    => 'term_id',
            'terms'    => $categories,
            'operator' => 'IN',
        ],
        [
            'taxonomy' => 'post_tag',
            'field'    => 'term_id',
            'terms'    => $tags,
            'operator' => 'IN',
        ],
    ],
    'no_found_rows' => true,
    'update_post_meta_cache' => false,
]);

// 関連記事が少ない場合は最新記事で補完
if ($related_posts->post_count < 3) {
    $additional_posts = new WP_Query([
        'post_type' => 'post',
        'posts_per_page' => 6 - $related_posts->post_count,
        'post__not_in' => array_merge([$current_post_id], wp_list_pluck($related_posts->posts, 'ID')),
        'orderby' => 'date',
        'order' => 'DESC',
        'post_status' => 'publish',
        'no_found_rows' => true,
        'update_post_meta_cache' => false,
    ]);
    
    // 投稿をマージ
    $all_related_posts = array_merge($related_posts->posts, $additional_posts->posts);
} else {
    $all_related_posts = $related_posts->posts;
}

// 関連記事が存在しない場合は何も表示しない
if (empty($all_related_posts)) {
    wp_reset_postdata();
    return;
}
?>

<section class="related-posts bg-gray-50 rounded-lg p-8 mt-8" aria-label="関連記事">
    <div class="related-posts-header text-center mb-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-3">
            <i class="ri-links-line mr-2 text-blue-600"></i>
            関連記事
        </h2>
        <p class="text-gray-600">
            この記事に関連するおすすめの記事をご紹介します
        </p>
    </div>
    
    <div class="related-posts-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php
        foreach ($all_related_posts as $post) :
            setup_postdata($post);
            ?>
            
            <article class="related-post-card bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden group">
                
                <!-- アイキャッチ画像 -->
                <div class="related-post-thumbnail relative overflow-hidden">
                    <?php if (has_post_thumbnail()) : ?>
                        <a href="<?php the_permalink(); ?>" class="block">
                            <?php
                            the_post_thumbnail('medium', [
                                'class' => 'w-full h-32 object-cover group-hover:scale-105 transition-transform duration-300',
                                'loading' => 'lazy',
                                'decoding' => 'async'
                            ]);
                            ?>
                        </a>
                    <?php else : ?>
                        <!-- デフォルト画像 -->
                        <a href="<?php the_permalink(); ?>" class="block bg-gradient-to-br from-blue-100 to-indigo-100 h-32 flex items-center justify-center group-hover:scale-105 transition-transform duration-300">
                            <i class="ri-article-line text-3xl text-blue-400"></i>
                        </a>
                    <?php endif; ?>
                    
                    <!-- カテゴリーラベル -->
                    <?php
                    $post_categories = get_the_category();
                    if ($post_categories) :
                        $primary_category = $post_categories[0];
                        ?>
                        <div class="absolute top-2 left-2">
                            <a href="<?php echo esc_url(get_category_link($primary_category->term_id)); ?>" 
                               class="inline-block bg-blue-600 text-white text-xs font-medium px-2 py-1 rounded hover:bg-blue-700 transition-colors">
                                <?php echo esc_html($primary_category->name); ?>
                            </a>
                        </div>
                        <?php
                    endif;
                    ?>
                </div>
                
                <!-- カード本文 -->
                <div class="related-post-content p-4">
                    
                    <!-- 投稿日とメタ情報 -->
                    <div class="related-post-meta flex items-center text-gray-500 text-xs mb-2">
                        <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                            <i class="ri-calendar-line mr-1"></i>
                            <?php echo esc_html(get_the_date()); ?>
                        </time>
                        
                        <!-- 閲覧数（もしあれば） -->
                        <?php
                        $views = get_post_meta(get_the_ID(), 'post_views_count', true);
                        if ($views) :
                            ?>
                            <span class="mx-2">•</span>
                            <span>
                                <i class="ri-eye-line mr-1"></i>
                                <?php echo esc_html(number_format($views)); ?>
                            </span>
                            <?php
                        endif;
                        ?>
                    </div>
                    
                    <!-- タイトル -->
                    <h3 class="related-post-title text-base font-semibold text-gray-800 mb-2 leading-tight">
                        <a href="<?php the_permalink(); ?>" 
                           class="hover:text-blue-600 transition-colors line-clamp-2">
                            <?php the_title(); ?>
                        </a>
                    </h3>
                    
                    <!-- 抜粋 -->
                    <div class="related-post-excerpt text-gray-600 text-sm leading-relaxed mb-3 line-clamp-2">
                        <?php
                        $excerpt = get_the_excerpt();
                        if ($excerpt) {
                            echo wp_trim_words($excerpt, 20, '...');
                        } else {
                            echo wp_trim_words(get_the_content(), 20, '...');
                        }
                        ?>
                    </div>
                    
                    <!-- 続きを読むリンク -->
                    <div class="related-post-footer">
                        <a href="<?php the_permalink(); ?>" 
                           class="inline-flex items-center text-blue-600 font-medium text-sm hover:text-blue-700 transition-colors">
                            続きを読む
                            <i class="ri-arrow-right-line ml-1"></i>
                        </a>
                    </div>
                    
                </div>
                
            </article>
            
            <?php
        endforeach;
        wp_reset_postdata();
        ?>
    </div>
    
    <!-- もっと見るリンク -->
    <div class="related-posts-more text-center mt-8">
        <a href="<?php echo esc_url(get_permalink(get_option('page_for_posts'))); ?>" 
           class="inline-flex items-center bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 transition-colors">
            <i class="ri-article-line mr-2"></i>
            すべての記事を見る
        </a>
    </div>
    
</section>

<style>
/* Line-clamp utility for text truncation */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<?php
// 関連記事のパフォーマンス向上のためのJavaScript
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 関連記事カードのホバーエフェクト強化
    const relatedCards = document.querySelectorAll('.related-post-card');
    
    relatedCards.forEach(card => {
        const link = card.querySelector('.related-post-title a');
        const readMoreLink = card.querySelector('.related-post-footer a');
        
        // カード全体をクリック可能にする
        card.addEventListener('click', function(e) {
            if (e.target.tagName.toLowerCase() !== 'a' && link) {
                link.click();
            }
        });
        
        // カードにキーボードアクセシビリティを追加
        card.setAttribute('tabindex', '0');
        card.setAttribute('role', 'article');
        
        card.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                if (link) {
                    link.click();
                }
            }
        });
    });
});
</script>
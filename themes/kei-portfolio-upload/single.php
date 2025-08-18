<?php
/**
 * 個別投稿（ブログ記事）テンプレート
 * 
 * single.phpはポストタイプがpostの場合のsingle-project.phpの代替として
 * ブログ記事の個別ページを表示
 * 
 * @package Kei_Portfolio
 * @since 1.0.0
 */

get_header(); 

// MemoryManagerでメモリ使用状況をチェック
if (class_exists('\KeiPortfolio\Performance\MemoryManager')) {
    $memory_manager = \KeiPortfolio\Performance\MemoryManager::get_instance();
    $memory_stats = $memory_manager->check_memory_usage();
    
    // デバッグモードでのみHTMLコメントを出力
    if (WP_DEBUG && current_user_can('manage_options')) {
        echo sprintf(
            '<!-- Single.php Memory Usage: %s (%.1f%%) -->%s',
            $memory_stats['formatted']['current'],
            $memory_stats['percentage'] * 100,
            PHP_EOL
        );
    }
}
?>

<main id="main" class="site-main">
    <div class="blog-single-container max-w-4xl mx-auto px-4 py-8">
        
        <?php
        while (have_posts()) :
            the_post();
            
            // 投稿タイプをチェック（postのみ処理）
            if (get_post_type() !== 'post') {
                // project等の他の投稿タイプの場合は専用テンプレートにリダイレクト
                get_template_part('single', get_post_type());
                break;
            }
            ?>
            
            <article id="post-<?php the_ID(); ?>" <?php post_class('blog-article'); ?>>
                
                <!-- 記事ヘッダー -->
                <header class="article-header mb-8">
                    
                    <!-- カテゴリーラベル -->
                    <?php
                    $categories = get_the_category();
                    if ($categories) :
                        ?>
                        <div class="article-categories mb-4">
                            <?php
                            foreach ($categories as $category) :
                                ?>
                                <a href="<?php echo esc_url(get_category_link($category->term_id)); ?>" 
                                   class="inline-block bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full hover:bg-blue-200 transition-colors mr-2">
                                    <?php echo esc_html($category->name); ?>
                                </a>
                                <?php
                            endforeach;
                            ?>
                        </div>
                        <?php
                    endif;
                    ?>
                    
                    <!-- タイトル -->
                    <h1 class="article-title text-3xl lg:text-4xl font-bold text-gray-800 mb-6 leading-tight">
                        <?php the_title(); ?>
                    </h1>
                    
                    <!-- メタ情報 -->
                    <div class="article-meta flex flex-wrap items-center text-gray-600 text-sm gap-4 mb-6">
                        
                        <!-- 公開日 -->
                        <div class="flex items-center">
                            <i class="ri-calendar-line mr-2"></i>
                            <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                <?php echo esc_html(get_the_date()); ?>
                            </time>
                        </div>
                        
                        <!-- 更新日（公開日と異なる場合のみ表示） -->
                        <?php if (get_the_modified_date() !== get_the_date()) : ?>
                            <div class="flex items-center">
                                <i class="ri-refresh-line mr-2"></i>
                                <span>更新: </span>
                                <time datetime="<?php echo esc_attr(get_the_modified_date('c')); ?>">
                                    <?php echo esc_html(get_the_modified_date()); ?>
                                </time>
                            </div>
                        <?php endif; ?>
                        
                        <!-- 著者 -->
                        <div class="flex items-center">
                            <i class="ri-user-line mr-2"></i>
                            <a href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>" 
                               class="hover:text-blue-600 transition-colors">
                                <?php the_author(); ?>
                            </a>
                        </div>
                        
                        <!-- 読了時間（事前計算済み値のキャッシュ活用） -->
                        <?php
                        // 事前計算済み値のキャッシュ活用
                        $reading_time = get_post_meta(get_the_ID(), '_reading_time_cache', true);
                        if (empty($reading_time)) {
                            // キャッシュがない場合のみ計算
                            $content = get_the_content();
                            $plain_content = wp_strip_all_tags($content);
                            $char_count = mb_strlen($plain_content, 'UTF-8');
                            $reading_time = max(1, round($char_count / 400)); // 日本語400文字/分
                            update_post_meta(get_the_ID(), '_reading_time_cache', $reading_time);
                        }
                        ?>
                        <div class="flex items-center">
                            <i class="ri-time-line mr-2"></i>
                            <span>約<?php echo esc_html($reading_time); ?>分で読めます</span>
                        </div>
                        
                        <!-- 閲覧数（改良版キャッシュ対応） -->
                        <?php
                        $blog_data = \KeiPortfolio\Blog\Blog_Data::get_instance();
                        $views = $blog_data->get_actual_view_count(get_the_ID());
                        if ($views > 0) :
                            ?>
                            <div class="flex items-center">
                                <i class="ri-eye-line mr-2"></i>
                                <span><?php echo esc_html(number_format($views)); ?> views</span>
                            </div>
                            <?php
                        endif;
                        ?>
                        
                    </div>
                    
                    <!-- アイキャッチ画像 -->
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="article-thumbnail mb-8">
                            <?php
                            the_post_thumbnail('large', [
                                'class' => 'w-full rounded-lg shadow-lg object-cover',
                                'loading' => 'eager', // Above the fold画像なのでeager
                                'decoding' => 'async'
                            ]);
                            ?>
                        </div>
                    <?php endif; ?>
                    
                </header>
                
                <!-- 記事本文 -->
                <div class="article-content prose prose-lg max-w-none mb-8">
                    <?php
                    the_content();
                    
                    // ページ分割されている場合のページネーション
                    wp_link_pages([
                        'before' => '<div class="page-links bg-gray-50 p-4 rounded-lg mt-6"><strong>' . __('ページ:', 'kei-portfolio') . '</strong>',
                        'after'  => '</div>',
                        'link_before' => '<span class="inline-block bg-white border px-3 py-1 rounded mr-2 hover:bg-blue-50">',
                        'link_after'  => '</span>',
                    ]);
                    ?>
                </div>
                
                <!-- タグ -->
                <?php
                $tags = get_the_tags();
                if ($tags) :
                    ?>
                    <div class="article-tags mb-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">
                            <i class="ri-price-tag-3-line mr-2"></i>
                            タグ
                        </h3>
                        <div class="flex flex-wrap gap-2">
                            <?php
                            foreach ($tags as $tag) :
                                ?>
                                <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>" 
                                   class="inline-block bg-gray-100 text-gray-700 text-sm px-3 py-1 rounded-full hover:bg-gray-200 transition-colors">
                                    #<?php echo esc_html($tag->name); ?>
                                </a>
                                <?php
                            endforeach;
                            ?>
                        </div>
                    </div>
                    <?php
                endif;
                ?>
                
                <!-- ソーシャルシェアボタン -->
                <?php get_template_part('template-parts/blog/share-buttons'); ?>
                
                <!-- 著者情報 -->
                <div class="author-bio bg-gray-50 rounded-lg p-6 mb-8">
                    <div class="flex items-start">
                        <div class="author-avatar mr-4">
                            <?php echo get_avatar(get_the_author_meta('ID'), 64, '', get_the_author(), ['class' => 'rounded-full']); ?>
                        </div>
                        <div class="author-info flex-1">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">
                                <?php the_author(); ?>
                            </h3>
                            <?php
                            $author_bio = get_the_author_meta('description');
                            if ($author_bio) :
                                ?>
                                <p class="text-gray-600 text-sm leading-relaxed">
                                    <?php echo wp_kses_post($author_bio); ?>
                                </p>
                                <?php
                            else :
                                ?>
                                <p class="text-gray-600 text-sm leading-relaxed">
                                    フリーランスエンジニアとして最新技術のキャッチアップや既存知識の向上を中心に活動しています。明るく前向きな姿勢で、お客様の課題解決に取り組みます。
                                </p>
                                <?php
                            endif;
                            ?>
                        </div>
                    </div>
                </div>
                
            </article>
            
            <?php
        endwhile; // End of the loop.
        ?>
        
        <!-- 関連記事 -->
        <?php get_template_part('template-parts/blog/related-posts'); ?>
        
        <!-- コメント -->
        <?php
        // コメントが開放されているか、コメントが存在する場合に表示
        if (comments_open() || get_comments_number()) :
            ?>
            <div class="article-comments mt-8">
                <?php comments_template(); ?>
            </div>
            <?php
        endif;
        ?>
        
    </div>
</main>

<?php
get_footer();
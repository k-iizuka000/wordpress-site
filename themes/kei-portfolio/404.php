<?php
/**
 * 404エラーページ
 * React not-found.tsx を WordPress PHP に変換
 *
 * @package Kei_Portfolio
 */

get_header(); ?>

<div class="flex flex-col items-center justify-center h-screen text-center px-4">
    <h1 class="text-5xl md:text-5xl font-semibold text-gray-100">404</h1>
    <h1 class="text-2xl md:text-3xl font-semibold mt-6">お探しのページは見つかりませんでした</h1>
    <p class="mt-4 text-xl md:text-2xl text-gray-500 mb-8">ページが移動されたか、削除された可能性があります</p>
    
    <div class="flex flex-col sm:flex-row gap-4">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-4 rounded-full text-lg font-semibold transition-all transform hover:scale-105 text-center whitespace-nowrap">
            ホームページに戻る
        </a>
        <a href="<?php echo esc_url(get_post_type_archive_link('project')); ?>" class="border-2 border-gray-300 text-gray-600 hover:bg-gray-100 px-8 py-4 rounded-full text-lg font-semibold transition-all text-center whitespace-nowrap">
            制作実績を見る
        </a>
    </div>
    
    <!-- 最近のプロジェクト -->
    <section class="mt-16 max-w-4xl w-full">
        <h2 class="text-2xl font-bold text-gray-800 mb-8">最近のプロジェクト</h2>
        
        <?php
        $recent_projects = new WP_Query(array(
            'post_type' => 'project',
            'posts_per_page' => 3,
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        
        if ($recent_projects->have_posts()) : ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php while ($recent_projects->have_posts()) : $recent_projects->the_post(); ?>
                    <article class="bg-white rounded-xl p-6 shadow-md hover:shadow-lg transition-shadow">
                        <?php if (has_post_thumbnail()) : ?>
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail('medium', array('class' => 'w-full h-32 object-cover rounded-lg mb-4')); ?>
                            </a>
                        <?php endif; ?>
                        
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">
                            <a href="<?php the_permalink(); ?>" class="hover:text-blue-600 transition-colors">
                                <?php the_title(); ?>
                            </a>
                        </h3>
                        
                        <p class="text-gray-600 text-sm mb-3">
                            <?php echo wp_trim_words(get_the_excerpt(), 15); ?>
                        </p>
                        
                        <a href="<?php the_permalink(); ?>" class="text-blue-600 hover:text-blue-700 font-medium text-sm">
                            詳細を見る →
                        </a>
                    </article>
                <?php endwhile; ?>
            </div>
        <?php else : ?>
            <p class="text-gray-600">プロジェクトが登録されていません。</p>
        <?php endif;
        wp_reset_postdata(); ?>
    </section>
</div>

<?php get_footer(); ?>
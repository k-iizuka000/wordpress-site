<?php
/**
 * ブログ投稿一覧ページテンプレート
 * 
 * WordPressの「投稿ページ」として設定されたページで使用される
 * ブログ投稿の一覧表示、ページネーション、サイドバーを含む
 * 
 * @package Kei_Portfolio
 * @since 1.0.0
 */

get_header(); ?>

<main id="main" class="site-main">
    <div class="blog-container max-w-7xl mx-auto px-4 py-8">
        
        <?php
        // ブログヒーローエリア
        get_template_part('template-parts/blog/hero');
        ?>
        
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 mt-8">
            <!-- メインコンテンツエリア -->
            <div class="lg:col-span-3">
                
                <?php if (have_posts()) : ?>
                    
                    <header class="blog-archive-header mb-8">
                        <h1 class="text-3xl lg:text-4xl font-bold text-gray-800 mb-4">
                            ブログ記事一覧
                        </h1>
                        <p class="text-gray-600 text-lg">
                            技術記事、開発ノウハウ、日々の学びをシェアしています
                        </p>
                    </header>
                    
                    <?php
                    // 投稿一覧テンプレートパーツ
                    get_template_part('template-parts/blog/post-list');
                    ?>
                    
                    <?php
                    // ページネーション
                    get_template_part('template-parts/blog/pagination');
                    ?>
                    
                <?php else : ?>
                    
                    <div class="no-posts bg-gray-50 rounded-lg p-8 text-center">
                        <i class="ri-article-line text-4xl text-gray-400 mb-4"></i>
                        <h2 class="text-2xl font-semibold text-gray-700 mb-4">
                            記事がまだありません
                        </h2>
                        <p class="text-gray-600 mb-6">
                            最初のブログ記事を投稿してください。
                        </p>
                        <?php if (current_user_can('publish_posts')) : ?>
                            <a href="<?php echo esc_url(admin_url('post-new.php')); ?>" 
                               class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                                新しい記事を作成
                            </a>
                        <?php endif; ?>
                    </div>
                    
                <?php endif; ?>
                
            </div>
            
            <!-- サイドバー -->
            <div class="lg:col-span-1">
                <?php get_template_part('template-parts/blog/sidebar'); ?>
            </div>
            
        </div>
        
    </div>
</main>

<?php
get_footer();
<?php
/**
 * ブログヒーローセクション
 * 
 * ブログトップページのヒーローエリア
 * 
 * @package Kei_Portfolio
 * @since 1.0.0
 */

// カスタマイザー設定から値を取得
$hero_title = get_theme_mod('blog_hero_title', 'エンジニアの日々');
$hero_subtitle = get_theme_mod('blog_hero_subtitle', '技術ブログ・開発ノウハウ・学習記録');
$hero_description = get_theme_mod('blog_hero_description', '日々の開発で学んだ技術やノウハウ、興味深いツールやライブラリについて記録しています。皆様の開発に少しでもお役に立てれば幸いです。');
$show_search = get_theme_mod('blog_hero_show_search', true);
$show_categories = get_theme_mod('blog_hero_show_categories', true);
?>

<section class="blog-hero bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-8 mb-8">
    <div class="text-center max-w-3xl mx-auto">
        
        <!-- メインタイトル -->
        <h1 class="text-4xl lg:text-5xl font-bold text-gray-800 mb-4">
            <?php echo esc_html($hero_title); ?>
        </h1>
        
        <!-- サブタイトル -->
        <p class="text-xl text-blue-700 font-medium mb-6">
            <?php echo esc_html($hero_subtitle); ?>
        </p>
        
        <!-- 説明文 -->
        <p class="text-gray-600 text-lg leading-relaxed mb-8 max-w-2xl mx-auto">
            <?php echo esc_html($hero_description); ?>
        </p>
        
        <!-- 検索フォーム -->
        <?php if ($show_search) : ?>
            <div class="blog-search mb-6">
                <form role="search" method="get" class="search-form max-w-md mx-auto" action="<?php echo esc_url(home_url('/')); ?>">
                    <div class="relative">
                        <input type="search" 
                               class="search-field w-full px-4 py-3 pr-12 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="記事を検索..."
                               value="<?php echo esc_attr(get_search_query()); ?>" 
                               name="s" />
                        <button type="submit" 
                                class="search-submit absolute right-2 top-1/2 transform -translate-y-1/2 bg-blue-600 text-white p-2 rounded-full hover:bg-blue-700 transition-colors">
                            <i class="ri-search-line text-sm" aria-hidden="true"></i>
                            <span class="sr-only">検索</span>
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
        
        <!-- 人気カテゴリー -->
        <?php if ($show_categories) : ?>
            <div class="popular-categories">
                <h3 class="text-sm font-medium text-gray-700 mb-3">人気のカテゴリー</h3>
                <div class="flex flex-wrap justify-center gap-2">
                    <?php
                    $categories = get_categories([
                        'orderby' => 'count',
                        'order' => 'DESC',
                        'number' => 6,
                        'hide_empty' => true
                    ]);
                    
                    if ($categories) :
                        foreach ($categories as $category) :
                            ?>
                            <a href="<?php echo esc_url(get_category_link($category->term_id)); ?>" 
                               class="inline-block bg-white text-gray-700 text-sm font-medium px-4 py-2 rounded-full hover:bg-blue-100 hover:text-blue-700 transition-colors shadow-sm">
                                <?php echo esc_html($category->name); ?>
                                <span class="text-xs text-gray-500 ml-1">(<?php echo esc_html($category->count); ?>)</span>
                            </a>
                            <?php
                        endforeach;
                    endif;
                    ?>
                </div>
            </div>
        <?php endif; ?>
        
    </div>
</section>

<!-- パンくずナビ -->
<nav class="breadcrumb mb-6" aria-label="パンくず">
    <div class="flex items-center text-sm text-gray-600">
        <a href="<?php echo esc_url(home_url('/')); ?>" 
           class="hover:text-blue-600 transition-colors">
            <i class="ri-home-line mr-1"></i>
            ホーム
        </a>
        <i class="ri-arrow-right-s-line mx-2 text-gray-400"></i>
        <span class="text-gray-800 font-medium">ブログ</span>
    </div>
</nav>
<?php
/**
 * メインテンプレートファイル
 *
 * これは最も一般的なテンプレートファイルであり、
 * より具体的なテンプレートが存在しない場合のフォールバックとして機能します。
 *
 * @package Kei_Portfolio_Pro
 */

get_header(); ?>

<main id="main" class="site-main">
    <div class="container">
        <?php if ( have_posts() ) : ?>
            
            <?php if ( is_home() && ! is_front_page() ) : ?>
                <header class="page-header">
                    <h1 class="page-title"><?php single_post_title(); ?></h1>
                </header>
            <?php endif; ?>
            
            <div class="posts-grid grid grid-3">
                <?php
                while ( have_posts() ) :
                    the_post();
                    ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class( 'card' ); ?>>
                        <?php if ( has_post_thumbnail() ) : ?>
                            <div class="post-thumbnail">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail( 'medium' ); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <header class="entry-header">
                            <?php the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>
                            
                            <div class="entry-meta">
                                <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                                    <?php echo esc_html( get_the_date() ); ?>
                                </time>
                            </div>
                        </header>
                        
                        <div class="entry-summary">
                            <?php the_excerpt(); ?>
                        </div>
                        
                        <footer class="entry-footer">
                            <a href="<?php the_permalink(); ?>" class="read-more">
                                続きを読む →
                            </a>
                        </footer>
                    </article>
                <?php endwhile; ?>
            </div>
            
            <?php
            // ページネーション
            the_posts_pagination( array(
                'mid_size'  => 2,
                'prev_text' => '←',
                'next_text' => '→',
            ) );
            ?>
            
        <?php else : ?>
            
            <section class="no-results not-found">
                <header class="page-header">
                    <h1 class="page-title">見つかりませんでした</h1>
                </header>
                
                <div class="page-content">
                    <p>お探しのコンテンツは見つかりませんでした。検索をお試しください。</p>
                    <?php get_search_form(); ?>
                </div>
            </section>
            
        <?php endif; ?>
    </div>
</main>

<?php get_footer(); ?>
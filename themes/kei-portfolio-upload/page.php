<?php
/**
 * 汎用固定ページテンプレート
 *
 * @package Kei_Portfolio
 */

get_header(); ?>

    <main id="main" class="site-main">
        <div class="max-w-4xl mx-auto px-4 py-12">
            <?php
            while ( have_posts() ) :
                the_post();
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class( 'prose max-w-none' ); ?>>
                    <header class="page-header mb-8">
                        <?php the_title( '<h1 class="page-title text-4xl font-bold text-gray-800 mb-4">', '</h1>' ); ?>
                    </header>

                    <div class="page-content text-gray-700 leading-relaxed">
                        <?php
                        the_content();

                        wp_link_pages(
                            array(
                                'before' => '<div class="page-links mt-8 p-4 bg-gray-50 rounded-lg"><span class="page-links-title font-semibold text-gray-800">' . __( 'Pages:', 'kei-portfolio' ) . '</span>',
                                'after'  => '</div>',
                            )
                        );
                        ?>
                    </div>

                    <?php if ( get_edit_post_link() ) : ?>
                        <footer class="entry-footer mt-8 pt-4 border-t border-gray-200">
                            <?php
                            edit_post_link(
                                sprintf(
                                    wp_kses(
                                        /* translators: %s: Name of current post. Only visible to screen readers */
                                        __( 'Edit <span class="screen-reader-text">%s</span>', 'kei-portfolio' ),
                                        array(
                                            'span' => array(
                                                'class' => array(),
                                            ),
                                        )
                                    ),
                                    wp_kses_post( get_the_title() )
                                ),
                                '<span class="edit-link text-sm text-blue-600 hover:text-blue-800">',
                                '</span>'
                            );
                            ?>
                        </footer>
                    <?php endif; ?>
                </article>
                <?php
            endwhile; // End of the loop.
            ?>
        </div>
    </main><!-- #main -->

<?php
get_footer();
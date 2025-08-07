<?php
/**
 * About ページテンプレート
 *
 * @package Kei_Portfolio
 */

get_header(); ?>

    <main id="main" class="site-main">
        <div class="max-w-6xl mx-auto px-4 py-12">
            <?php
            while ( have_posts() ) :
                the_post();
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    
                    <!-- ページヘッダー -->
                    <header class="page-header text-center mb-16">
                        <h1 class="text-5xl font-bold text-gray-800 mb-4">
                            <?php echo esc_html( get_the_title() ); ?>
                        </h1>
                        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                            10年以上のシステム開発経験を持つフルスタックエンジニア
                        </p>
                    </header>

                    <!-- プロフィール セクション -->
                    <section class="mb-16">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                            <div class="space-y-6">
                                <h2 class="text-3xl font-bold text-gray-800 mb-6">Profile</h2>
                                <div class="prose prose-lg text-gray-700">
                                    <?php the_content(); ?>
                                </div>
                                
                                <!-- 基本情報 -->
                                <div class="bg-blue-50 p-6 rounded-lg">
                                    <h3 class="text-xl font-semibold text-gray-800 mb-4">基本情報</h3>
                                    <div class="space-y-2">
                                        <div class="flex items-center space-x-3">
                                            <i class="ri-user-line text-blue-600"></i>
                                            <span class="text-gray-700">Kei Aokiki</span>
                                        </div>
                                        <div class="flex items-center space-x-3">
                                            <i class="ri-briefcase-line text-blue-600"></i>
                                            <span class="text-gray-700">フルスタックエンジニア</span>
                                        </div>
                                        <div class="flex items-center space-x-3">
                                            <i class="ri-time-line text-blue-600"></i>
                                            <span class="text-gray-700">経験年数: 10年以上</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- プロフィール画像エリア -->
                            <div class="text-center">
                                <?php if ( has_post_thumbnail() ) : ?>
                                    <div class="inline-block">
                                        <?php the_post_thumbnail( 'large', array( 'class' => 'rounded-full w-64 h-64 object-cover mx-auto shadow-lg' ) ); ?>
                                    </div>
                                <?php else : ?>
                                    <div class="w-64 h-64 bg-gradient-to-br from-blue-100 to-blue-200 rounded-full mx-auto flex items-center justify-center shadow-lg">
                                        <i class="ri-user-line text-6xl text-blue-600"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </section>

                    <!-- 経験とアプローチ -->
                    <section class="mb-16">
                        <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">Experience & Approach</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                            
                            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                                <div class="text-center">
                                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="ri-code-line text-2xl text-blue-600"></i>
                                    </div>
                                    <h3 class="text-xl font-semibold text-gray-800 mb-3">フルスタック開発</h3>
                                    <p class="text-gray-600 text-sm leading-relaxed">
                                        フロントエンドからバックエンドまで幅広い技術スタックを駆使し、
                                        効率的で保守性の高いシステムを構築します。
                                    </p>
                                </div>
                            </div>

                            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                                <div class="text-center">
                                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="ri-robot-line text-2xl text-green-600"></i>
                                    </div>
                                    <h3 class="text-xl font-semibold text-gray-800 mb-3">自動化ツール開発</h3>
                                    <p class="text-gray-600 text-sm leading-relaxed">
                                        業務プロセスの効率化を目指し、カスタムツールの開発で
                                        生産性向上に貢献します。
                                    </p>
                                </div>
                            </div>

                            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                                <div class="text-center">
                                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <i class="ri-team-line text-2xl text-purple-600"></i>
                                    </div>
                                    <h3 class="text-xl font-semibous text-gray-800 mb-3">協調性重視</h3>
                                    <p class="text-gray-600 text-sm leading-relaxed">
                                        明るく前向きな姿勢で、チームの一員として
                                        お客様の課題解決に取り組みます。
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- CTA セクション -->
                    <section class="text-center bg-gradient-to-r from-blue-50 to-indigo-50 py-16 px-6 rounded-lg">
                        <h2 class="text-3xl font-bold text-gray-800 mb-4">
                            お仕事のご相談
                        </h2>
                        <p class="text-lg text-gray-600 mb-8 max-w-2xl mx-auto">
                            プロジェクトのご相談やお見積もりなど、お気軽にお問い合わせください。
                            迅速かつ丁寧にご対応いたします。
                        </p>
                        <a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" 
                           class="inline-flex items-center px-8 py-4 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-lg">
                            <i class="ri-mail-line mr-2"></i>
                            お問い合わせはこちら
                        </a>
                    </section>

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
<?php
/**
 * Skills ページテンプレート
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
                            10年以上のシステム開発で培った技術スキルをご紹介します
                        </p>
                    </header>

                    <!-- ページコンテンツ -->
                    <div class="prose max-w-none mb-12">
                        <?php the_content(); ?>
                    </div>

                    <!-- プログラミング言語 -->
                    <section class="mb-16">
                        <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">Programming Languages</h2>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                            
                            <?php
                            $programming_languages = array(
                                array( 'name' => 'Java', 'level' => 95, 'icon' => 'ri-file-code-line', 'color' => 'bg-red-500' ),
                                array( 'name' => 'JavaScript', 'level' => 90, 'icon' => 'ri-javascript-line', 'color' => 'bg-yellow-500' ),
                                array( 'name' => 'TypeScript', 'level' => 85, 'icon' => 'ri-code-s-slash-line', 'color' => 'bg-blue-600' ),
                                array( 'name' => 'PHP', 'level' => 80, 'icon' => 'ri-code-line', 'color' => 'bg-purple-600' ),
                                array( 'name' => 'Python', 'level' => 75, 'icon' => 'ri-file-code-line', 'color' => 'bg-green-600' ),
                                array( 'name' => 'Go', 'level' => 70, 'icon' => 'ri-code-line', 'color' => 'bg-cyan-500' ),
                                array( 'name' => 'SQL', 'level' => 85, 'icon' => 'ri-database-2-line', 'color' => 'bg-gray-600' ),
                                array( 'name' => 'Shell', 'level' => 80, 'icon' => 'ri-terminal-line', 'color' => 'bg-black' ),
                            );

                            foreach ( $programming_languages as $lang ) :
                            ?>
                                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 text-center">
                                    <div class="flex items-center justify-center w-12 h-12 <?php echo esc_attr( $lang['color'] ); ?> rounded-lg mx-auto mb-3">
                                        <i class="<?php echo esc_attr( $lang['icon'] ); ?> text-white text-xl"></i>
                                    </div>
                                    <h3 class="font-semibold text-gray-800 mb-2"><?php echo esc_html( $lang['name'] ); ?></h3>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="<?php echo esc_attr( $lang['color'] ); ?> h-2 rounded-full" style="width: <?php echo esc_attr( $lang['level'] ); ?>%"></div>
                                    </div>
                                    <span class="text-xs text-gray-600 mt-1 block"><?php echo esc_html( $lang['level'] ); ?>%</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <!-- フレームワーク・ライブラリ -->
                    <section class="mb-16">
                        <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">Frameworks & Libraries</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            
                            <?php
                            $frameworks = array(
                                array( 'name' => 'Spring Boot', 'category' => 'Backend', 'icon' => 'ri-leaf-line', 'color' => 'bg-green-500' ),
                                array( 'name' => 'React', 'category' => 'Frontend', 'icon' => 'ri-reactjs-line', 'color' => 'bg-blue-500' ),
                                array( 'name' => 'Next.js', 'category' => 'Frontend', 'icon' => 'ri-window-line', 'color' => 'bg-black' ),
                                array( 'name' => 'Node.js', 'category' => 'Backend', 'icon' => 'ri-nodejs-line', 'color' => 'bg-green-600' ),
                                array( 'name' => 'Express.js', 'category' => 'Backend', 'icon' => 'ri-server-line', 'color' => 'bg-gray-700' ),
                                array( 'name' => 'WordPress', 'category' => 'CMS', 'icon' => 'ri-wordpress-line', 'color' => 'bg-blue-600' ),
                                array( 'name' => 'Laravel', 'category' => 'Backend', 'icon' => 'ri-code-s-slash-line', 'color' => 'bg-red-500' ),
                                array( 'name' => 'Vue.js', 'category' => 'Frontend', 'icon' => 'ri-vuejs-line', 'color' => 'bg-green-500' ),
                                array( 'name' => 'Tailwind CSS', 'category' => 'Frontend', 'icon' => 'ri-palette-line', 'color' => 'bg-cyan-500' ),
                            );

                            foreach ( $frameworks as $framework ) :
                            ?>
                                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex items-center justify-center w-12 h-12 <?php echo esc_attr( $framework['color'] ); ?> rounded-lg">
                                            <i class="<?php echo esc_attr( $framework['icon'] ); ?> text-white text-xl"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-gray-800"><?php echo esc_html( $framework['name'] ); ?></h3>
                                            <span class="text-sm text-gray-600"><?php echo esc_html( $framework['category'] ); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <!-- データベース・インフラ -->
                    <section class="mb-16">
                        <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">Database & Infrastructure</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            
                            <?php
                            $infrastructure = array(
                                array( 'name' => 'MySQL', 'category' => 'Database', 'icon' => 'ri-database-2-line', 'color' => 'bg-blue-600' ),
                                array( 'name' => 'PostgreSQL', 'category' => 'Database', 'icon' => 'ri-database-2-line', 'color' => 'bg-blue-500' ),
                                array( 'name' => 'MongoDB', 'category' => 'NoSQL', 'icon' => 'ri-database-line', 'color' => 'bg-green-600' ),
                                array( 'name' => 'Redis', 'category' => 'Cache', 'icon' => 'ri-database-line', 'color' => 'bg-red-500' ),
                                array( 'name' => 'Docker', 'category' => 'Container', 'icon' => 'ri-ship-line', 'color' => 'bg-blue-500' ),
                                array( 'name' => 'AWS', 'category' => 'Cloud', 'icon' => 'ri-cloud-line', 'color' => 'bg-orange-500' ),
                                array( 'name' => 'Git', 'category' => 'Version Control', 'icon' => 'ri-git-branch-line', 'color' => 'bg-gray-700' ),
                                array( 'name' => 'GitHub', 'category' => 'Platform', 'icon' => 'ri-github-line', 'color' => 'bg-black' ),
                                array( 'name' => 'Linux', 'category' => 'OS', 'icon' => 'ri-terminal-line', 'color' => 'bg-yellow-600' ),
                            );

                            foreach ( $infrastructure as $infra ) :
                            ?>
                                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex items-center justify-center w-12 h-12 <?php echo esc_attr( $infra['color'] ); ?> rounded-lg">
                                            <i class="<?php echo esc_attr( $infra['icon'] ); ?> text-white text-xl"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-gray-800"><?php echo esc_html( $infra['name'] ); ?></h3>
                                            <span class="text-sm text-gray-600"><?php echo esc_html( $infra['category'] ); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <!-- 学習アプローチ -->
                    <section class="mb-16">
                        <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">Learning Approach</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            
                            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 p-8 rounded-lg">
                                <div class="flex items-center space-x-4 mb-6">
                                    <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center">
                                        <i class="ri-book-open-line text-2xl text-white"></i>
                                    </div>
                                    <h3 class="text-2xl font-bold text-gray-800">継続的学習</h3>
                                </div>
                                <p class="text-gray-700 leading-relaxed">
                                    技術の進歩に合わせて新しいフレームワークやツールを学習し、
                                    常に最新のベストプラクティスを追求しています。
                                </p>
                            </div>

                            <div class="bg-gradient-to-br from-green-50 to-emerald-50 p-8 rounded-lg">
                                <div class="flex items-center space-x-4 mb-6">
                                    <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center">
                                        <i class="ri-tools-line text-2xl text-white"></i>
                                    </div>
                                    <h3 class="text-2xl font-bold text-gray-800">実践重視</h3>
                                </div>
                                <p class="text-gray-700 leading-relaxed">
                                    理論だけでなく実際のプロジェクトで技術を活用し、
                                    実践的なスキルと問題解決能力を磨いています。
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- CTA セクション -->
                    <section class="text-center bg-gradient-to-r from-blue-50 to-indigo-50 py-16 px-6 rounded-lg">
                        <h2 class="text-3xl font-bold text-gray-800 mb-4">
                            技術相談・プロジェクトのご依頼
                        </h2>
                        <p class="text-lg text-gray-600 mb-8 max-w-2xl mx-auto">
                            これらの技術を活用して、あなたのプロジェクトを成功に導きます。
                            お気軽にお声がけください。
                        </p>
                        <div class="space-x-4">
                            <a href="<?php echo esc_url( home_url( '/portfolio' ) ); ?>" 
                               class="inline-flex items-center px-6 py-3 bg-white text-blue-600 font-semibold rounded-lg hover:bg-gray-50 transition-colors border-2 border-blue-600">
                                <i class="ri-folder-line mr-2"></i>
                                実績を見る
                            </a>
                            <a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" 
                               class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-lg">
                                <i class="ri-mail-line mr-2"></i>
                                お問い合わせ
                            </a>
                        </div>
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
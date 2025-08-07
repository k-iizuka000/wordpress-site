<?php
/**
 * Skills ページテンプレート
 *
 * @package Kei_Portfolio
 */

get_header(); 

// Portfolio_Dataクラスのインスタンスを取得
$portfolio_data = Portfolio_Data::get_instance();
$skills_data = $portfolio_data->get_skills_data();
$summary_data = $portfolio_data->get_summary_data();

// エラーハンドリング
$has_skills = !is_wp_error($skills_data);
$has_summary = !is_wp_error($summary_data);
?>

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
                            <?php if ($has_summary && isset($summary_data['totalExperience'])) : ?>
                                <?php echo esc_html($summary_data['totalExperience']); ?>のシステム開発で培った技術スキルをご紹介します
                            <?php else : ?>
                                10年以上のシステム開発で培った技術スキルをご紹介します
                            <?php endif; ?>
                        </p>
                    </header>

                    <!-- ページコンテンツ -->
                    <div class="prose max-w-none mb-12">
                        <?php the_content(); ?>
                    </div>

                    <!-- フロントエンドスキル -->
                    <?php if ($has_skills && isset($skills_data['frontend']) && !empty($skills_data['frontend'])) : ?>
                    <section class="mb-16">
                        <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">Frontend Skills</h2>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                            <?php
                            // スキルアイコンと色のマッピング
                            $skill_mapping = array(
                                'JavaScript' => array('level' => 90, 'icon' => 'ri-javascript-line', 'color' => 'bg-yellow-500'),
                                'Vue.js' => array('level' => 85, 'icon' => 'ri-vuejs-line', 'color' => 'bg-green-500'),
                                'React' => array('level' => 80, 'icon' => 'ri-reactjs-line', 'color' => 'bg-blue-500'),
                                'HTML5' => array('level' => 95, 'icon' => 'ri-html5-line', 'color' => 'bg-orange-500'),
                                'CSS3' => array('level' => 90, 'icon' => 'ri-css3-line', 'color' => 'bg-blue-600'),
                                'JSP' => array('level' => 85, 'icon' => 'ri-file-code-line', 'color' => 'bg-red-500'),
                                'FullCalendar' => array('level' => 75, 'icon' => 'ri-calendar-line', 'color' => 'bg-purple-500')
                            );

                            foreach ($skills_data['frontend'] as $skill_name) : 
                                if (!is_string($skill_name) || empty($skill_name)) continue;
                                
                                $skill_data = isset($skill_mapping[$skill_name]) ? 
                                    $skill_mapping[$skill_name] : 
                                    array('level' => 70, 'icon' => 'ri-code-line', 'color' => 'bg-gray-500');
                            ?>
                                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 text-center">
                                    <div class="flex items-center justify-center w-12 h-12 <?php echo esc_attr($skill_data['color']); ?> rounded-lg mx-auto mb-3">
                                        <i class="<?php echo esc_attr($skill_data['icon']); ?> text-white text-xl"></i>
                                    </div>
                                    <h3 class="font-semibold text-gray-800 mb-2"><?php echo esc_html($skill_name); ?></h3>
                                    <div class="w-full bg-gray-200 rounded-full h-2" role="progressbar" aria-valuenow="<?php echo esc_attr($skill_data['level']); ?>" aria-valuemin="0" aria-valuemax="100" aria-label="<?php echo esc_attr($skill_name); ?> スキルレベル">
                                        <div class="<?php echo esc_attr($skill_data['color']); ?> h-2 rounded-full transition-all duration-1000 ease-out" 
                                             style="width: <?php echo esc_attr($skill_data['level']); ?>%"></div>
                                    </div>
                                    <span class="text-xs text-gray-600 mt-1 block"><?php echo esc_html($skill_data['level']); ?>%</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    <?php endif; ?>

                    <!-- バックエンドスキル -->
                    <?php if ($has_skills && isset($skills_data['backend']) && !empty($skills_data['backend'])) : ?>
                    <section class="mb-16">
                        <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">Backend Skills</h2>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                            <?php
                            // バックエンドスキルのマッピング
                            $backend_mapping = array(
                                'Java' => array('level' => 95, 'icon' => 'ri-file-code-line', 'color' => 'bg-red-500'),
                                'Spring Boot' => array('level' => 90, 'icon' => 'ri-leaf-line', 'color' => 'bg-green-500'),
                                'Python' => array('level' => 85, 'icon' => 'ri-file-code-line', 'color' => 'bg-blue-600'),
                                'SQL' => array('level' => 90, 'icon' => 'ri-database-2-line', 'color' => 'bg-gray-600'),
                                'Node.js' => array('level' => 80, 'icon' => 'ri-nodejs-line', 'color' => 'bg-green-600'),
                                'COBOL' => array('level' => 70, 'icon' => 'ri-code-line', 'color' => 'bg-blue-800'),
                                'Ruby' => array('level' => 75, 'icon' => 'ri-code-line', 'color' => 'bg-red-600'),
                                'PHP' => array('level' => 80, 'icon' => 'ri-code-line', 'color' => 'bg-purple-600')
                            );

                            foreach ($skills_data['backend'] as $skill_name) : 
                                if (!is_string($skill_name) || empty($skill_name)) continue;
                                
                                $skill_data = isset($backend_mapping[$skill_name]) ? 
                                    $backend_mapping[$skill_name] : 
                                    array('level' => 70, 'icon' => 'ri-code-line', 'color' => 'bg-gray-500');
                            ?>
                                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 text-center">
                                    <div class="flex items-center justify-center w-12 h-12 <?php echo esc_attr($skill_data['color']); ?> rounded-lg mx-auto mb-3">
                                        <i class="<?php echo esc_attr($skill_data['icon']); ?> text-white text-xl"></i>
                                    </div>
                                    <h3 class="font-semibold text-gray-800 mb-2"><?php echo esc_html($skill_name); ?></h3>
                                    <div class="w-full bg-gray-200 rounded-full h-2" role="progressbar" aria-valuenow="<?php echo esc_attr($skill_data['level']); ?>" aria-valuemin="0" aria-valuemax="100" aria-label="<?php echo esc_attr($skill_name); ?> スキルレベル">
                                        <div class="<?php echo esc_attr($skill_data['color']); ?> h-2 rounded-full transition-all duration-1000 ease-out" 
                                             style="width: <?php echo esc_attr($skill_data['level']); ?>%"></div>
                                    </div>
                                    <span class="text-xs text-gray-600 mt-1 block"><?php echo esc_html($skill_data['level']); ?>%</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    <?php endif; ?>

                    <!-- その他のスキル -->
                    <?php if ($has_skills && isset($skills_data['other']) && !empty($skills_data['other'])) : ?>
                    <section class="mb-16">
                        <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">Tools & Infrastructure</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php
                            // その他のスキルのマッピング
                            $other_mapping = array(
                                'Git' => array('category' => 'Version Control', 'icon' => 'ri-git-branch-line', 'color' => 'bg-red-500'),
                                'AWS' => array('category' => 'Cloud Platform', 'icon' => 'ri-cloud-line', 'color' => 'bg-orange-500'),
                                'Docker' => array('category' => 'Containerization', 'icon' => 'ri-ship-line', 'color' => 'bg-blue-500'),
                                'Gradle' => array('category' => 'Build Tool', 'icon' => 'ri-settings-3-line', 'color' => 'bg-green-600'),
                                'JUnit' => array('category' => 'Testing', 'icon' => 'ri-test-tube-line', 'color' => 'bg-red-600'),
                                'Maven' => array('category' => 'Build Tool', 'icon' => 'ri-hammer-line', 'color' => 'bg-blue-600'),
                                'SVN' => array('category' => 'Version Control', 'icon' => 'ri-git-repository-line', 'color' => 'bg-gray-600'),
                                'e2e' => array('category' => 'Testing', 'icon' => 'ri-checkbox-multiple-line', 'color' => 'bg-purple-600')
                            );

                            foreach ($skills_data['other'] as $skill_name) : 
                                if (!is_string($skill_name) || empty($skill_name)) continue;
                                
                                $skill_data = isset($other_mapping[$skill_name]) ? 
                                    $other_mapping[$skill_name] : 
                                    array('category' => 'Tool', 'icon' => 'ri-tools-line', 'color' => 'bg-gray-500');
                            ?>
                                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex items-center justify-center w-12 h-12 <?php echo esc_attr($skill_data['color']); ?> rounded-lg">
                                            <i class="<?php echo esc_attr($skill_data['icon']); ?> text-white text-xl"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-gray-800"><?php echo esc_html($skill_name); ?></h3>
                                            <span class="text-sm text-gray-600"><?php echo esc_html($skill_data['category']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    <?php endif; ?>

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
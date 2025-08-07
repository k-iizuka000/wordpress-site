<?php
/**
 * Portfolio ページテンプレート
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
                            これまでに手がけたプロジェクトと開発実績をご紹介します
                        </p>
                    </header>

                    <!-- ページコンテンツ -->
                    <div class="prose max-w-none mb-12">
                        <?php the_content(); ?>
                    </div>

                    <!-- フィルターボタン -->
                    <div class="flex flex-wrap justify-center gap-4 mb-12">
                        <button class="portfolio-filter-btn active px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors" data-filter="all">
                            すべて
                        </button>
                        <button class="portfolio-filter-btn px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors" data-filter="web-app">
                            Webアプリ
                        </button>
                        <button class="portfolio-filter-btn px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors" data-filter="automation">
                            自動化ツール
                        </button>
                        <button class="portfolio-filter-btn px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors" data-filter="api">
                            API・Backend
                        </button>
                        <button class="portfolio-filter-btn px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors" data-filter="frontend">
                            Frontend
                        </button>
                    </div>

                    <!-- プロジェクト一覧 -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" id="portfolio-grid">
                        
                        <?php
                        // 実績データの配列（実際のプロジェクトでは、カスタム投稿タイプ 'project' から取得）
                        $portfolio_projects = array(
                            array(
                                'title' => 'ECサイト管理システム',
                                'category' => 'web-app',
                                'description' => 'Spring Boot + React で構築した ECサイトの管理システム。在庫管理、注文処理、売上分析機能を実装。',
                                'technologies' => array('Spring Boot', 'React', 'MySQL', 'Docker'),
                                'image' => 'project-ecommerce.jpg',
                                'demo_url' => '#',
                                'github_url' => '#',
                            ),
                            array(
                                'title' => 'データ分析自動化ツール',
                                'category' => 'automation',
                                'description' => 'Python を使用したデータ収集・分析・レポート生成の自動化ツール。定期実行機能付き。',
                                'technologies' => array('Python', 'Pandas', 'PostgreSQL', 'Cron'),
                                'image' => 'project-automation.jpg',
                                'demo_url' => '#',
                                'github_url' => '#',
                            ),
                            array(
                                'title' => 'リアルタイムチャットAPI',
                                'category' => 'api',
                                'description' => 'Node.js + Socket.io で構築したリアルタイムチャット機能のREST API。認証・権限管理付き。',
                                'technologies' => array('Node.js', 'Socket.io', 'MongoDB', 'JWT'),
                                'image' => 'project-chat-api.jpg',
                                'demo_url' => '#',
                                'github_url' => '#',
                            ),
                            array(
                                'title' => 'ダッシュボード UI',
                                'category' => 'frontend',
                                'description' => 'Next.js + TypeScript で構築したモダンなダッシュボード。データ可視化とレスポンシブデザイン。',
                                'technologies' => array('Next.js', 'TypeScript', 'Tailwind CSS', 'Chart.js'),
                                'image' => 'project-dashboard.jpg',
                                'demo_url' => '#',
                                'github_url' => '#',
                            ),
                            array(
                                'title' => '予約管理システム',
                                'category' => 'web-app',
                                'description' => 'Laravel + Vue.js で構築した予約管理システム。カレンダー表示、メール通知機能付き。',
                                'technologies' => array('Laravel', 'Vue.js', 'MySQL', 'Redis'),
                                'image' => 'project-booking.jpg',
                                'demo_url' => '#',
                                'github_url' => '#',
                            ),
                            array(
                                'title' => 'ログ監視システム',
                                'category' => 'automation',
                                'description' => 'Go言語で構築したサーバーログの監視・アラート システム。Slack 連携機能付き。',
                                'technologies' => array('Go', 'Elasticsearch', 'Kibana', 'Docker'),
                                'image' => 'project-monitoring.jpg',
                                'demo_url' => '#',
                                'github_url' => '#',
                            ),
                        );

                        foreach ( $portfolio_projects as $project ) :
                        ?>
                            <div class="portfolio-item bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-shadow" data-category="<?php echo esc_attr( $project['category'] ); ?>">
                                
                                <!-- プロジェクト画像 -->
                                <div class="h-48 bg-gradient-to-br from-blue-100 to-indigo-100 flex items-center justify-center">
                                    <div class="text-center">
                                        <i class="ri-code-s-slash-line text-4xl text-blue-600 mb-2"></i>
                                        <p class="text-sm text-gray-600">プロジェクト画像</p>
                                    </div>
                                </div>

                                <!-- プロジェクト情報 -->
                                <div class="p-6">
                                    <h3 class="text-xl font-bold text-gray-800 mb-3">
                                        <?php echo esc_html( $project['title'] ); ?>
                                    </h3>
                                    
                                    <p class="text-gray-600 text-sm leading-relaxed mb-4">
                                        <?php echo esc_html( $project['description'] ); ?>
                                    </p>

                                    <!-- 技術スタック -->
                                    <div class="flex flex-wrap gap-2 mb-4">
                                        <?php foreach ( $project['technologies'] as $tech ) : ?>
                                            <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">
                                                <?php echo esc_html( $tech ); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>

                                    <!-- リンク -->
                                    <div class="flex space-x-4">
                                        <?php if ( ! empty( $project['demo_url'] ) && $project['demo_url'] !== '#' ) : ?>
                                            <a href="<?php echo esc_url( $project['demo_url'] ); ?>" 
                                               target="_blank" 
                                               rel="noopener noreferrer"
                                               class="flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 transition-colors">
                                                <i class="ri-external-link-line mr-1"></i>
                                                デモ
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ( ! empty( $project['github_url'] ) && $project['github_url'] !== '#' ) : ?>
                                            <a href="<?php echo esc_url( $project['github_url'] ); ?>" 
                                               target="_blank" 
                                               rel="noopener noreferrer"
                                               class="flex items-center px-4 py-2 bg-gray-700 text-white text-sm font-medium rounded hover:bg-gray-800 transition-colors">
                                                <i class="ri-github-line mr-1"></i>
                                                コード
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- プロジェクト実績サマリー -->
                    <section class="mt-20 mb-16">
                        <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">プロジェクト実績サマリー</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                            
                            <div class="text-center bg-blue-50 p-6 rounded-lg">
                                <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="ri-folder-line text-2xl text-white"></i>
                                </div>
                                <div class="text-3xl font-bold text-gray-800 mb-2">50+</div>
                                <div class="text-sm text-gray-600">プロジェクト数</div>
                            </div>

                            <div class="text-center bg-green-50 p-6 rounded-lg">
                                <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="ri-time-line text-2xl text-white"></i>
                                </div>
                                <div class="text-3xl font-bold text-gray-800 mb-2">10+</div>
                                <div class="text-sm text-gray-600">開発経験年数</div>
                            </div>

                            <div class="text-center bg-purple-50 p-6 rounded-lg">
                                <div class="w-16 h-16 bg-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="ri-code-line text-2xl text-white"></i>
                                </div>
                                <div class="text-3xl font-bold text-gray-800 mb-2">15+</div>
                                <div class="text-sm text-gray-600">習得技術数</div>
                            </div>

                            <div class="text-center bg-orange-50 p-6 rounded-lg">
                                <div class="w-16 h-16 bg-orange-600 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="ri-user-line text-2xl text-white"></i>
                                </div>
                                <div class="text-3xl font-bold text-gray-800 mb-2">100%</div>
                                <div class="text-sm text-gray-600">顧客満足度</div>
                            </div>
                        </div>
                    </section>

                    <!-- CTA セクション -->
                    <section class="text-center bg-gradient-to-r from-blue-50 to-indigo-50 py-16 px-6 rounded-lg">
                        <h2 class="text-3xl font-bold text-gray-800 mb-4">
                            新しいプロジェクトを始めませんか？
                        </h2>
                        <p class="text-lg text-gray-600 mb-8 max-w-2xl mx-auto">
                            これらの実績をもとに、あなたのアイデアを形にします。
                            お気軽にご相談ください。
                        </p>
                        <div class="space-x-4">
                            <a href="<?php echo esc_url( home_url( '/about' ) ); ?>" 
                               class="inline-flex items-center px-6 py-3 bg-white text-blue-600 font-semibold rounded-lg hover:bg-gray-50 transition-colors border-2 border-blue-600">
                                <i class="ri-user-line mr-2"></i>
                                詳しいプロフィール
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

<script>
// Portfolio filtering
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.portfolio-filter-btn');
    const portfolioItems = document.querySelectorAll('.portfolio-item');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filterValue = this.getAttribute('data-filter');
            
            // Update active button
            filterButtons.forEach(btn => {
                btn.classList.remove('active', 'bg-blue-600', 'text-white');
                btn.classList.add('bg-gray-200', 'text-gray-700');
            });
            
            this.classList.add('active', 'bg-blue-600', 'text-white');
            this.classList.remove('bg-gray-200', 'text-gray-700');
            
            // Filter items
            portfolioItems.forEach(item => {
                if (filterValue === 'all' || item.getAttribute('data-category') === filterValue) {
                    item.style.display = 'block';
                    item.style.opacity = '0';
                    setTimeout(() => {
                        item.style.opacity = '1';
                    }, 100);
                } else {
                    item.style.opacity = '0';
                    setTimeout(() => {
                        item.style.display = 'none';
                    }, 300);
                }
            });
        });
    });
});
</script>

<?php
get_footer();
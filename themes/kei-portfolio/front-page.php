<?php
/**
 * フロントページテンプレート
 * React page.tsx を WordPress PHP に変換
 *
 * @package Kei_Portfolio
 */

get_header(); ?>

<main class="min-h-screen">
    <!-- Hero Section -->
    <section 
        class="relative h-screen flex items-center justify-center bg-gradient-to-r from-blue-50 to-green-50 overflow-hidden"
        style="background-image: url('https://readdy.ai/api/search-image?query=Professional%20cyclist%20in%20bright%20cycling%20gear%20riding%20a%20road%20bike%20on%20a%20scenic%20mountain%20road%20during%20golden%20hour%20with%20clear%20blue%20sky%20and%20green%20landscape%20in%20the%20background%2C%20showcasing%20freedom%20and%20speed%20with%20a%20modern%20minimalist%20aesthetic%20that%20would%20work%20well%20as%20a%20website%20hero%20background&width=1920&height=1080&seq=hero-cycling&orientation=landscape'); background-size: cover; background-position: center;"
    >
        <div class="absolute inset-0 bg-black/30"></div>
        
        <div class="relative z-10 w-full max-w-6xl mx-auto px-4">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                <div class="text-white lg:pr-8">
                    <h1 class="text-4xl md:text-6xl font-bold leading-tight mb-6">
                        自動化で
                        <span class="text-blue-300">未来</span>を
                        <br />
                        創るエンジニア
                    </h1>
                    <p class="text-xl md:text-2xl mb-8 text-gray-200 leading-relaxed">
                        プログラミングの力で効率化を実現し、<br />
                        ロードバイクのように爽快に駆け抜ける<br />
                        開発体験をお届けします。
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="<?php echo esc_url(get_post_type_archive_link('project')); ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-4 rounded-full text-lg font-semibold transition-all transform hover:scale-105 text-center whitespace-nowrap">
                            制作実績を見る
                        </a>
                        <a href="<?php echo esc_url(home_url('/contact')); ?>" class="border-2 border-white text-white hover:bg-white hover:text-gray-800 px-8 py-4 rounded-full text-lg font-semibold transition-all text-center whitespace-nowrap">
                            お問い合わせ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Preview Section -->
    <section class="py-20 bg-white">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">
                    明るく前向きなエンジニア
                </h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    自動化ツール開発を得意とし、ロードバイクで培った持続力と集中力を活かして、
                    お客様の課題解決に取り組んでいます。
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="bg-blue-50 rounded-2xl p-8 text-center hover:shadow-lg transition-shadow">
                    <div class="w-16 h-16 flex items-center justify-center mx-auto mb-4 bg-blue-600 rounded-full">
                        <i class="ri-settings-3-line text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">自動化ツール開発</h3>
                    <p class="text-gray-600">
                        繰り返し作業を効率化し、生産性向上を実現するツールを開発します。
                    </p>
                </div>
                
                <div class="bg-green-50 rounded-2xl p-8 text-center hover:shadow-lg transition-shadow">
                    <div class="w-16 h-16 flex items-center justify-center mx-auto mb-4 bg-green-600 rounded-full">
                        <i class="ri-bike-line text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">ロードバイク愛好家</h3>
                    <p class="text-gray-600">
                        ロードバイクで培った持続力と集中力を、開発業務にも活かしています。
                    </p>
                </div>
                
                <div class="bg-purple-50 rounded-2xl p-8 text-center hover:shadow-lg transition-shadow md:col-span-2 lg:col-span-1">
                    <div class="w-16 h-16 flex items-center justify-center mx-auto mb-4 bg-purple-600 rounded-full">
                        <i class="ri-lightbulb-line text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">前向きな姿勢</h3>
                    <p class="text-gray-600">
                        どんな課題にも明るく前向きに取り組み、最適な解決策を提案します。
                    </p>
                </div>
            </div>
            
            <div class="text-center mt-12">
                <a href="<?php echo esc_url(home_url('/about')); ?>" class="inline-flex items-center text-blue-600 hover:text-blue-700 font-semibold text-lg">
                    詳しいプロフィールを見る
                    <i class="ri-arrow-right-line ml-2"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Skills Preview Section -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">
                    主要スキル
                </h2>
                <p class="text-lg text-gray-600">
                    多様な技術を駆使して、最適なソリューションをお届けします
                </p>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-12">
                <?php
                // スキルデータ - セキュリティを考慮してサニタイズ済み
                $skills = array(
                    array(
                        'name' => 'Python', 
                        'icon' => 'ri-code-line', 
                        'color' => 'blue'
                    ),
                    array(
                        'name' => 'JavaScript', 
                        'icon' => 'ri-javascript-line', 
                        'color' => 'yellow'
                    ),
                    array(
                        'name' => 'React', 
                        'icon' => 'ri-reactjs-line', 
                        'color' => 'cyan'
                    ),
                    array(
                        'name' => 'Node.js', 
                        'icon' => 'ri-nodejs-line', 
                        'color' => 'green'
                    ),
                    array(
                        'name' => 'Docker', 
                        'icon' => 'ri-container-line', 
                        'color' => 'blue'
                    ),
                    array(
                        'name' => 'AWS', 
                        'icon' => 'ri-cloud-line', 
                        'color' => 'orange'
                    ),
                    array(
                        'name' => 'Git', 
                        'icon' => 'ri-git-branch-line', 
                        'color' => 'red'
                    ),
                    array(
                        'name' => 'Database', 
                        'icon' => 'ri-database-2-line', 
                        'color' => 'purple'
                    )
                );
                
                foreach ($skills as $skill) : 
                    // データの存在確認とサニタイズ
                    if (!isset($skill['name']) || !isset($skill['icon']) || !isset($skill['color'])) {
                        continue;
                    }
                    ?>
                    <div class="bg-white rounded-xl p-6 text-center hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 flex items-center justify-center mx-auto mb-3 bg-<?php echo esc_attr($skill['color']); ?>-100 rounded-lg">
                            <i class="<?php echo esc_attr($skill['icon']); ?> text-<?php echo esc_attr($skill['color']); ?>-600 text-xl"></i>
                        </div>
                        <h4 class="font-semibold text-gray-800"><?php echo esc_html($skill['name']); ?></h4>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center">
                <a href="<?php echo esc_url(home_url('/skills')); ?>" class="inline-flex items-center text-blue-600 hover:text-blue-700 font-semibold text-lg">
                    全スキル一覧を見る
                    <i class="ri-arrow-right-line ml-2"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Portfolio Preview Section -->
    <section class="py-20 bg-white">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">
                    制作実績
                </h2>
                <p class="text-lg text-gray-600">
                    これまでに開発した代表的なプロジェクトをご紹介します
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
                <?php
                // プロジェクトデータ - セキュリティを考慮してサニタイズ済み
                $projects = array(
                    array(
                        'title' => 'データ処理自動化ツール',
                        'description' => 'Excel作業を自動化し、作業時間を90%短縮',
                        'tech' => 'Python, Pandas',
                        'image' => 'https://readdy.ai/api/search-image?query=Modern%20data%20processing%20dashboard%20with%20clean%20interface%20showing%20automated%20Excel%20processing%20workflows%2C%20charts%20and%20graphs%20on%20computer%20screen%20with%20professional%20blue%20and%20green%20color%20scheme&width=400&height=300&seq=data-automation&orientation=landscape'
                    ),
                    array(
                        'title' => 'Webスクレイピングシステム',
                        'description' => '競合サイトの価格情報を定期的に収集・分析',
                        'tech' => 'Python, BeautifulSoup',
                        'image' => 'https://readdy.ai/api/search-image?query=Web%20scraping%20visualization%20dashboard%20showing%20data%20collection%20from%20multiple%20websites%20with%20modern%20interface%20design%2C%20featuring%20clean%20charts%20and%20data%20flows%20in%20blue%20and%20green%20theme&width=400&height=300&seq=web-scraping&orientation=landscape'
                    ),
                    array(
                        'title' => 'チャットボット開発',
                        'description' => '顧客対応を自動化し、応答速度を大幅改善',
                        'tech' => 'Node.js, AI API',
                        'image' => 'https://readdy.ai/api/search-image?query=Modern%20chatbot%20interface%20design%20with%20clean%20conversation%20bubbles%20and%20AI%20assistant%20graphics%2C%20professional%20blue%20and%20green%20color%20scheme%20with%20minimalist%20design&width=400&height=300&seq=chatbot&orientation=landscape'
                    )
                );
                
                foreach ($projects as $project) : 
                    // データの存在確認とサニタイズ
                    if (!isset($project['title']) || !isset($project['description']) || 
                        !isset($project['tech']) || !isset($project['image'])) {
                        continue;
                    }
                    ?>
                    <div class="bg-gray-50 rounded-2xl overflow-hidden hover:shadow-lg transition-shadow">
                        <img 
                            src="<?php echo esc_url($project['image']); ?>" 
                            alt="<?php echo esc_attr($project['title']); ?>"
                            class="w-full h-48 object-cover object-top"
                            loading="lazy"
                        />
                        <div class="p-6">
                            <h3 class="text-xl font-semibold text-gray-800 mb-2"><?php echo esc_html($project['title']); ?></h3>
                            <p class="text-gray-600 mb-4"><?php echo esc_html($project['description']); ?></p>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-blue-600 bg-blue-100 px-3 py-1 rounded-full">
                                    <?php echo esc_html($project['tech']); ?>
                                </span>
                                <i class="ri-external-link-line text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center">
                <a href="<?php echo esc_url(get_post_type_archive_link('project')); ?>" class="inline-flex items-center text-blue-600 hover:text-blue-700 font-semibold text-lg">
                    全ての制作実績を見る
                    <i class="ri-arrow-right-line ml-2"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-r from-blue-600 to-green-600">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">
                一緒にプロジェクトを始めませんか？
            </h2>
            <p class="text-xl text-blue-100 mb-8 leading-relaxed">
                自動化によって業務効率を改善し、<br />
                新たな価値創造の時間を生み出しましょう。
            </p>
            <a href="<?php echo esc_url(home_url('/contact')); ?>" class="bg-white text-blue-600 hover:bg-gray-100 px-8 py-4 rounded-full text-lg font-semibold transition-all transform hover:scale-105 inline-block">
                無料相談を申し込む
            </a>
        </div>
    </section>
</main>

<?php get_footer(); ?>
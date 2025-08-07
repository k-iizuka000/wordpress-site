<?php
/**
 * Featured Projects Section
 * 
 * Displays featured portfolio projects with detailed information
 * Converted from React: app/portfolio/FeaturedProjects.tsx
 */

// Get featured projects from WordPress with input validation
$posts_per_page = 3;
$order = 'ASC';
$orderby = 'menu_order';

// Validate any GET parameters if they exist
if (isset($_GET['per_page'])) {
    $posts_per_page = intval($_GET['per_page']);
    $posts_per_page = max(1, min(10, $posts_per_page)); // Limit between 1-10
}

if (isset($_GET['order']) && in_array(strtoupper($_GET['order']), array('ASC', 'DESC'))) {
    $order = sanitize_text_field($_GET['order']);
}

if (isset($_GET['orderby'])) {
    $allowed_orderby = array('menu_order', 'date', 'title', 'rand');
    $orderby_input = sanitize_text_field($_GET['orderby']);
    if (in_array($orderby_input, $allowed_orderby)) {
        $orderby = $orderby_input;
    }
}

$featured_projects_query = new WP_Query(array(
    'post_type' => 'project',
    'posts_per_page' => $posts_per_page,
    'meta_key' => 'featured',
    'meta_value' => 'yes',
    'orderby' => $orderby,
    'order' => $order
));

// Fallback data if no projects found in database
$fallback_projects = array(
    array(
        'title' => 'データ処理自動化プラットフォーム',
        'client' => '製造業A社',
        'description' => 'Excel作業の完全自動化により、月間200時間の作業を10時間に短縮。複数のデータソースから情報を収集し、レポートを自動生成するシステムを構築。',
        'results' => array(
            '作業時間95%削減（200時間→10時間）',
            'ヒューマンエラー100%解消',
            '月間コスト50万円削減'
        ),
        'tech' => array('Python', 'Pandas', 'Openpyxl', 'SQLAlchemy', 'Tkinter'),
        'image' => 'https://readdy.ai/api/search-image?query=Advanced%20data%20processing%20dashboard%20with%20automated%20Excel%20workflow%20visualization%2C%20modern%20interface%20showing%20data%20transformation%20pipelines%2C%20charts%20and%20analytics%2C%20professional%20blue%20and%20green%20color%20scheme%20with%20clean%20modern%20design&width=600&height=400&seq=data-platform&orientation=landscape'
    ),
    array(
        'title' => 'Webスクレイピング価格監視システム',
        'client' => 'EC事業B社',
        'description' => '競合他社の価格情報を24時間監視し、価格変動を即座に通知。マーケット分析レポートを自動作成し、戦略的な価格設定をサポート。',
        'results' => array(
            '監視対象1000商品の自動化',
            '売上15%向上',
            'マーケット分析工数80%削減'
        ),
        'tech' => array('Python', 'Scrapy', 'BeautifulSoup', 'PostgreSQL', 'Celery'),
        'image' => 'https://readdy.ai/api/search-image?query=Web%20scraping%20monitoring%20dashboard%20showing%20price%20comparison%20charts%20and%20competitor%20analysis%20data%2C%20modern%20interface%20with%20real-time%20updates%2C%20clean%20blue%20and%20green%20themed%20design%20with%20data%20visualization%20elements&width=600&height=400&seq=scraping-system&orientation=landscape'
    ),
    array(
        'title' => 'AI チャットボット顧客サポートシステム',
        'client' => 'サービス業C社',
        'description' => '自然言語処理を活用したチャットボットで、よくある質問への対応を自動化。24時間対応により顧客満足度を大幅に向上。',
        'results' => array(
            '問い合わせ対応時間90%短縮',
            '顧客満足度85%→95%向上',
            'サポートコスト60%削減'
        ),
        'tech' => array('Node.js', 'OpenAI API', 'React', 'MongoDB', 'Socket.io'),
        'image' => 'https://readdy.ai/api/search-image?query=Modern%20AI%20chatbot%20interface%20with%20natural%20conversation%20flow%2C%20sleek%20chat%20bubbles%20and%20AI%20assistant%20graphics%2C%20professional%20design%20with%20blue%20and%20green%20accents%2C%20customer%20service%20automation%20visualization&width=600&height=400&seq=chatbot-system&orientation=landscape'
    )
);
?>

<section class="py-20 bg-white">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">
                注目プロジェクト
            </h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                特に大きな成果を上げた代表的なプロジェクトをご紹介します。
                どのプロジェクトも明確な課題解決と数値的な成果を実現しました。
            </p>
        </div>
        
        <div class="space-y-16">
            <?php 
            $projects_to_display = array();
            $index = 0;
            
            // Use WordPress projects if available, otherwise use fallback data
            if ($featured_projects_query->have_posts()) {
                while ($featured_projects_query->have_posts()) {
                    $featured_projects_query->the_post();
                    
                    // Get custom fields (adjust field names based on your setup)
                    $client = get_post_meta(get_the_ID(), 'client_name', true) ?: 'クライアント';
                    $results = get_post_meta(get_the_ID(), 'project_results', true);
                    $tech_terms = wp_get_post_terms(get_the_ID(), 'technology');
                    $tech = array();
                    foreach ($tech_terms as $term) {
                        $tech[] = $term->name;
                    }
                    
                    $project_data = array(
                        'title' => get_the_title(),
                        'client' => $client,
                        'description' => get_the_excerpt() ?: get_the_content(),
                        'results' => $results ? explode("\n", $results) : array('成果データを準備中'),
                        'tech' => $tech,
                        'image' => has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'large') : ''
                    );
                    
                    $projects_to_display[] = $project_data;
                }
                wp_reset_postdata();
            } else {
                // Use fallback data
                $projects_to_display = $fallback_projects;
            }
            
            foreach ($projects_to_display as $project) : 
                $is_reversed = ($index % 2 === 1);
            ?>
                <div class="bg-gray-50 rounded-3xl overflow-hidden hover:shadow-lg transition-shadow">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center <?php echo $is_reversed ? 'lg:grid-flow-col-dense' : ''; ?>">
                        <div class="p-8 lg:p-12 <?php echo $is_reversed ? 'lg:col-start-2' : ''; ?>">
                            <div class="flex items-center gap-2 mb-4">
                                <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-medium">
                                    <?php echo esc_html($project['client']); ?>
                                </span>
                                <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-medium">
                                    Featured
                                </span>
                            </div>
                            
                            <h3 class="text-2xl md:text-3xl font-bold text-gray-800 mb-4">
                                <?php echo esc_html($project['title']); ?>
                            </h3>
                            
                            <p class="text-gray-600 leading-relaxed mb-6">
                                <?php echo esc_html($project['description']); ?>
                            </p>
                            
                            <?php if (!empty($project['results'])) : ?>
                                <div class="mb-6">
                                    <h4 class="font-semibold text-gray-800 mb-3">主な成果</h4>
                                    <ul class="space-y-2">
                                        <?php foreach ($project['results'] as $result) : ?>
                                            <li class="flex items-start space-x-3">
                                                <div class="w-2 h-2 flex items-center justify-center mt-2">
                                                    <i class="ri-check-line text-green-600 text-sm"></i>
                                                </div>
                                                <span class="text-gray-600"><?php echo esc_html($result); ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($project['tech'])) : ?>
                                <div>
                                    <h4 class="font-semibold text-gray-800 mb-3">使用技術</h4>
                                    <div class="flex flex-wrap gap-2">
                                        <?php foreach ($project['tech'] as $tech) : ?>
                                            <span class="bg-white border border-gray-300 text-gray-700 px-3 py-1 rounded-full text-sm">
                                                <?php echo esc_html($tech); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="<?php echo $is_reversed ? 'lg:col-start-1 lg:row-start-1' : ''; ?>">
                            <?php if ($project['image']) : ?>
                                <img 
                                    src="<?php echo esc_url($project['image']); ?>" 
                                    alt="<?php echo esc_attr($project['title']); ?>"
                                    class="w-full h-80 lg:h-96 object-cover object-top"
                                    loading="lazy"
                                />
                            <?php else : ?>
                                <div class="w-full h-80 lg:h-96 bg-gradient-to-br from-blue-100 to-green-100 flex items-center justify-center">
                                    <div class="text-center text-gray-500">
                                        <i class="ri-image-line text-4xl mb-2"></i>
                                        <p>プロジェクト画像</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php 
                $index++;
            endforeach; 
            ?>
        </div>
    </div>
</section>
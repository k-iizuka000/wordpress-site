<?php
/**
 * Template Part: All Projects Section with Filtering
 * 
 * Displays a filterable list of all projects with category buttons
 * Converted from AllProjects.tsx
 */

// プロジェクトカテゴリー定義
$categories = [
    ['id' => 'all', 'name' => '全て', 'count' => 15],
    ['id' => 'automation', 'name' => '業務自動化', 'count' => 8],
    ['id' => 'web-app', 'name' => 'Webアプリ', 'count' => 4],
    ['id' => 'data-analysis', 'name' => 'データ分析', 'count' => 3]
];

// 全プロジェクトデータ（実際の実装では WP_Query やカスタムフィールドを使用）
$all_projects = [
    [
        'title' => '在庫管理自動化システム',
        'category' => 'automation',
        'client' => '小売業D社',
        'description' => 'バーコード読み取りによる在庫データの自動更新と発注システム',
        'period' => '3ヶ月',
        'tech' => ['Python', 'Flask', 'MySQL'],
        'image' => 'https://readdy.ai/api/search-image?query=Modern%20inventory%20management%20system%20interface%20with%20barcode%20scanning%20and%20automated%20stock%20tracking%2C%20clean%20dashboard%20design%20with%20blue%20and%20green%20color%20scheme%2C%20professional%20warehouse%20management%20visualization&width=400&height=300&seq=inventory-system&orientation=landscape'
    ],
    [
        'title' => '請求書処理自動化ツール',
        'category' => 'automation',
        'client' => '会計事務所E社',
        'description' => 'PDFから自動でデータを抽出し、会計ソフトへの入力を自動化',
        'period' => '2ヶ月',
        'tech' => ['Python', 'PyPDF2', 'OCR'],
        'image' => 'https://readdy.ai/api/search-image?query=Invoice%20processing%20automation%20interface%20showing%20PDF%20document%20analysis%20and%20data%20extraction%20workflow%2C%20modern%20accounting%20software%20integration%20with%20clean%20blue%20and%20green%20design%20theme&width=400&height=300&seq=invoice-automation&orientation=landscape'
    ],
    [
        'title' => 'タスク管理Webアプリ',
        'category' => 'web-app',
        'client' => 'スタートアップF社',
        'description' => 'チーム向けプロジェクト管理とタスク追跡システム',
        'period' => '4ヶ月',
        'tech' => ['React', 'Node.js', 'MongoDB'],
        'image' => 'https://readdy.ai/api/search-image?query=Modern%20task%20management%20web%20application%20interface%20with%20project%20boards%2C%20task%20cards%20and%20team%20collaboration%20features%2C%20clean%20UI%20design%20with%20blue%20and%20green%20color%20scheme%2C%20professional%20project%20management%20tool&width=400&height=300&seq=task-management&orientation=landscape'
    ],
    [
        'title' => '売上データ分析ダッシュボード',
        'category' => 'data-analysis',
        'client' => 'メーカーG社',
        'description' => '売上トレンドの可視化と予測分析機能を提供',
        'period' => '2ヶ月',
        'tech' => ['Python', 'Plotly', 'Pandas'],
        'image' => 'https://readdy.ai/api/search-image?query=Sales%20analytics%20dashboard%20with%20charts%2C%20graphs%20and%20trend%20analysis%20visualization%2C%20modern%20business%20intelligence%20interface%20with%20blue%20and%20green%20theme%2C%20professional%20data%20visualization%20design&width=400&height=300&seq=sales-dashboard&orientation=landscape'
    ],
    [
        'title' => 'メール配信自動化システム',
        'category' => 'automation',
        'client' => 'マーケティング会社H社',
        'description' => '顧客セグメント別の自動メール配信とA/Bテスト機能',
        'period' => '3ヶ月',
        'tech' => ['Python', 'Django', 'Celery'],
        'image' => 'https://readdy.ai/api/search-image?query=Email%20marketing%20automation%20system%20interface%20with%20customer%20segmentation%20and%20campaign%20management%2C%20modern%20design%20with%20automated%20workflow%20visualization%2C%20blue%20and%20green%20color%20scheme&width=400&height=300&seq=email-automation&orientation=landscape'
    ],
    [
        'title' => 'ECサイト構築',
        'category' => 'web-app',
        'client' => '雑貨店I社',
        'description' => '決済機能付きオンラインショップの開発',
        'period' => '5ヶ月',
        'tech' => ['Next.js', 'Stripe', 'Supabase'],
        'image' => 'https://readdy.ai/api/search-image?query=Modern%20e-commerce%20website%20interface%20with%20product%20catalog%2C%20shopping%20cart%20and%20payment%20integration%2C%20clean%20online%20store%20design%20with%20blue%20and%20green%20accents%2C%20professional%20retail%20web%20application&width=400&height=300&seq=ecommerce-site&orientation=landscape'
    ],
    [
        'title' => 'ログ分析システム',
        'category' => 'data-analysis',
        'client' => 'IT企業J社',
        'description' => 'サーバーログの自動解析とアラート通知システム',
        'period' => '2ヶ月',
        'tech' => ['Python', 'Elasticsearch', 'Kibana'],
        'image' => 'https://readdy.ai/api/search-image?query=Server%20log%20analysis%20system%20with%20real-time%20monitoring%20dashboard%2C%20alert%20notifications%20and%20data%20visualization%2C%20modern%20IT%20infrastructure%20monitoring%20interface%20with%20blue%20and%20green%20design&width=400&height=300&seq=log-analysis&orientation=landscape'
    ],
    [
        'title' => '予約管理システム',
        'category' => 'web-app',
        'client' => '美容サロンK社',
        'description' => 'オンライン予約とスタッフスケジュール管理システム',
        'period' => '3ヶ月',
        'tech' => ['React', 'Express', 'PostgreSQL'],
        'image' => 'https://readdy.ai/api/search-image?query=Appointment%20booking%20system%20interface%20with%20calendar%20scheduling%20and%20staff%20management%2C%20modern%20reservation%20platform%20design%20with%20clean%20blue%20and%20green%20theme%2C%20professional%20salon%20management%20application&width=400&height=300&seq=booking-system&orientation=landscape'
    ],
    [
        'title' => 'ファイル管理自動化',
        'category' => 'automation',
        'client' => 'デザイン事務所L社',
        'description' => 'プロジェクトファイルの自動分類と整理システム',
        'period' => '1ヶ月',
        'tech' => ['Python', 'Watchdog', 'Tkinter'],
        'image' => 'https://readdy.ai/api/search-image?query=File%20management%20automation%20system%20with%20automatic%20file%20organization%20and%20project%20folder%20structure%2C%20clean%20interface%20design%20showing%20file%20categorization%20with%20blue%20and%20green%20color%20scheme&width=400&height=300&seq=file-management&orientation=landscape'
    ]
];
?>

<section class="py-20 bg-gray-50" data-section="all-projects">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">
                全プロジェクト一覧
            </h2>
            <p class="text-lg text-gray-600">
                カテゴリー別にプロジェクトを絞り込んでご覧いただけます
            </p>
        </div>
        
        <!-- カテゴリーフィルター -->
        <div class="flex flex-wrap justify-center gap-4 mb-12" id="category-filters">
            <?php foreach ($categories as $category) : ?>
                <button
                    class="category-filter-btn px-6 py-3 rounded-full font-medium transition-all whitespace-nowrap cursor-pointer <?php echo $category['id'] === 'all' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100'; ?>"
                    data-category="<?php echo esc_attr($category['id']); ?>"
                >
                    <?php echo esc_html($category['name']); ?> (<?php echo esc_html($category['count']); ?>)
                </button>
            <?php endforeach; ?>
        </div>
        
        <!-- JavaScript無効環境でのフォールバック -->
        <noscript>
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-8 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="ri-information-line text-blue-400 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <strong>お知らせ:</strong> カテゴリーフィルター機能を使用するにはJavaScriptを有効にしてください。現在、全てのプロジェクトが表示されています。
                        </p>
                    </div>
                </div>
            </div>
        </noscript>
        
        <!-- プロジェクト一覧 -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" id="projects-grid">
            <?php foreach ($all_projects as $index => $project) : ?>
                <div 
                    class="project-card bg-white rounded-2xl overflow-hidden hover:shadow-lg transition-shadow" 
                    data-category="<?php echo esc_attr($project['category']); ?>"
                >
                    <img 
                        src="<?php echo esc_url($project['image']); ?>" 
                        alt="<?php echo esc_attr($project['title']); ?>"
                        class="w-full h-48 object-cover object-top"
                        loading="lazy"
                    />
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-3">
                            <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-medium">
                                <?php echo esc_html($project['client']); ?>
                            </span>
                            <span class="text-gray-500 text-sm"><?php echo esc_html($project['period']); ?></span>
                        </div>
                        
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">
                            <?php echo esc_html($project['title']); ?>
                        </h3>
                        <p class="text-gray-600 text-sm mb-4 leading-relaxed">
                            <?php echo esc_html($project['description']); ?>
                        </p>
                        
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($project['tech'] as $tech) : ?>
                                <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded text-xs">
                                    <?php echo esc_html($tech); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- 検索結果なしメッセージ -->
        <div class="text-center py-12 hidden" id="no-results">
            <i class="ri-folder-line text-4xl text-gray-400 mb-4"></i>
            <p class="text-gray-500">該当するプロジェクトが見つかりませんでした。</p>
        </div>
    </div>
</section>


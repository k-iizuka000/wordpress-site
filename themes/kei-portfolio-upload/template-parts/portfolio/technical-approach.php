<?php
/**
 * Template Part: Technical Approach Section
 * 
 * Displays development approach and values with progress bars
 * Converted from TechnicalApproach.tsx
 */

// 開発アプローチデータ
$approaches = [
    [
        'title' => '課題分析',
        'description' => 'クライアントの業務フローを詳細に分析し、自動化可能なポイントを特定します。現場の声を聞いて、真の課題を見つけ出します。',
        'icon' => 'ri-search-line',
        'color' => 'blue'
    ],
    [
        'title' => '最適技術選定',
        'description' => 'プロジェクトの要件に応じて最適な技術スタックを選定。保守性と拡張性を考慮した技術選択を行います。',
        'icon' => 'ri-tools-line',
        'color' => 'green'
    ],
    [
        'title' => '段階的実装',
        'description' => '小さな成功を積み重ねながら段階的に実装。クライアントと密にコミュニケーションを取りながら進めます。',
        'icon' => 'ri-stack-line',
        'color' => 'purple'
    ],
    [
        'title' => '品質保証',
        'description' => 'テスト駆動開発とコードレビューによる品質保証。長期間安定して動作するシステムを構築します。',
        'icon' => 'ri-shield-check-line',
        'color' => 'orange'
    ]
];

// 価値観データ
$values = [
    [
        'title' => '効率性',
        'description' => '無駄な作業を徹底的に排除し、最短ルートでゴールに到達',
        'percentage' => 95
    ],
    [
        'title' => '品質',
        'description' => '保守性と可読性を重視した高品質なコード',
        'percentage' => 90
    ],
    [
        'title' => 'コミュニケーション',
        'description' => 'クライアントとの密な連携と分かりやすい説明',
        'percentage' => 98
    ],
    [
        'title' => '継続的改善',
        'description' => '新しい技術の学習と既存システムの改善',
        'percentage' => 92
    ]
];
?>

<section class="py-20 bg-white" data-section="technical-approach">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">
                私の開発アプローチ
            </h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                ロードバイクで培った「継続する力」と「ゴールへの最短ルート」を見つける能力を、
                システム開発にも活かしています。
            </p>
        </div>
        
        <!-- 開発アプローチカード -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-20">
            <?php foreach ($approaches as $index => $approach) : ?>
                <div class="text-center approach-card" data-index="<?php echo $index; ?>">
                    <div class="w-16 h-16 flex items-center justify-center mx-auto mb-4 bg-<?php echo esc_attr($approach['color']); ?>-100 rounded-2xl">
                        <i class="<?php echo esc_attr($approach['icon']); ?> text-<?php echo esc_attr($approach['color']); ?>-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">
                        <?php echo esc_html($approach['title']); ?>
                    </h3>
                    <p class="text-gray-600 leading-relaxed">
                        <?php echo esc_html($approach['description']); ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- 価値観セクション -->
        <div class="bg-gradient-to-r from-blue-50 to-green-50 rounded-3xl p-8 md:p-12">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <h3 class="text-2xl md:text-3xl font-bold text-gray-800 mb-6">
                        私が大切にしている価値
                    </h3>
                    <p class="text-gray-600 leading-relaxed mb-8">
                        ロードバイクのように、明確な目標に向かって効率的に進む。
                        そんな開発スタイルで、クライアントの成功をサポートします。
                    </p>
                    
                    <div class="space-y-6">
                        <?php foreach ($values as $index => $value) : ?>
                            <div class="value-item" data-percentage="<?php echo esc_attr($value['percentage']); ?>">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-semibold text-gray-800">
                                        <?php echo esc_html($value['title']); ?>
                                    </span>
                                    <span class="text-blue-600 font-bold percentage-counter">
                                        0%
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div 
                                        class="progress-bar bg-gradient-to-r from-blue-600 to-green-600 h-2 rounded-full transition-all duration-1000"
                                        style="width: 0%"
                                    ></div>
                                </div>
                                <p class="text-gray-600 text-sm mt-1">
                                    <?php echo esc_html($value['description']); ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="relative">
                    <img 
                        src="https://readdy.ai/api/search-image?query=Professional%20cyclist%20on%20road%20bike%20showing%20determination%20and%20focus%2C%20modern%20cycling%20gear%2C%20bright%20natural%20outdoor%20setting%20with%20clean%20blue%20sky%2C%20representing%20efficiency%20and%20goal-oriented%20mindset%2C%20inspirational%20athletic%20performance&width=500&height=600&seq=development-mindset&orientation=portrait" 
                        alt="効率性と目標達成のイメージ"
                        class="w-full h-80 object-cover object-top rounded-2xl shadow-lg"
                        loading="lazy"
                    />
                    <div class="absolute top-4 right-4 bg-white/90 rounded-lg p-3">
                        <i class="ri-target-line text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- CTA セクション -->
        <div class="text-center mt-16">
            <h3 class="text-2xl font-bold text-gray-800 mb-4">
                一緒にプロジェクトを成功させましょう
            </h3>
            <p class="text-lg text-gray-600 mb-8 max-w-2xl mx-auto">
                あなたのビジネス課題を、自動化の力で解決します。
                まずはお気軽にご相談ください。
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a 
                    href="<?php echo esc_url(home_url('/contact')); ?>" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-4 rounded-full text-lg font-semibold transition-all transform hover:scale-105 whitespace-nowrap inline-block"
                >
                    無料相談を申し込む
                </a>
                <button 
                    class="border-2 border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white px-8 py-4 rounded-full text-lg font-semibold transition-all whitespace-nowrap cursor-pointer download-btn"
                    data-action="download-portfolio"
                >
                    実績資料をダウンロード
                </button>
            </div>
        </div>
    </div>
</section>
<?php
/**
 * Template part for displaying engineer history section
 * Used in: page-about.php
 */

// エンジニア経歴データ
$experiences = [
    [
        'period' => '2018年 - 2021年',
        'title' => 'システム開発会社',
        'role' => 'ソフトウェアエンジニア',
        'description' => 'Webアプリケーション開発に従事。Python、JavaScriptを中心とした開発経験を積む。チームリーダーとして後輩の指導も担当。',
        'skills' => ['Python', 'JavaScript', 'React', 'PostgreSQL'],
        'achievements' => [
            '社内業務効率化ツールを開発し、作業時間を60%短縮',
            '新人エンジニア5名の技術指導を担当',
            '品質改善プロジェクトでバグ発生率を30%削減'
        ]
    ],
    [
        'period' => '2021年 - 2023年',
        'title' => 'ITコンサルティング会社',
        'role' => 'シニアエンジニア',
        'description' => '大手企業の業務システム導入プロジェクトに参画。要件定義から運用まで一貫して担当し、クライアントとの調整も経験。',
        'skills' => ['AWS', 'Docker', 'Node.js', 'Vue.js'],
        'achievements' => [
            '月間処理量1000万件のシステムを安定稼働',
            'クライアント満足度調査で95%の高評価を獲得',
            '自動化により運用コストを40%削減'
        ]
    ],
    [
        'period' => '2023年 - 現在',
        'title' => 'フリーランス',
        'role' => 'フリーランスエンジニア',
        'description' => '自動化ツール開発を専門として独立。中小企業の業務効率化を中心に、幅広いプロジェクトに携わっています。',
        'skills' => ['Python', 'React', 'TypeScript', 'Supabase'],
        'achievements' => [
            '20社以上の業務効率化プロジェクトを完了',
            '平均70%の作業時間短縮を実現',
            'リピート率90%の高い顧客満足度を維持'
        ]
    ]
];
?>

<section class="py-20 bg-gray-50">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">
                エンジニアとしての歩み
            </h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto leading-relaxed">
                6年間のエンジニア経験を通じて、技術力だけでなく、
                チームワークやクライアントとのコミュニケーション能力も磨いてきました。
            </p>
        </div>
        
        <div class="space-y-8">
            <?php foreach ($experiences as $index => $exp) : ?>
                <div class="bg-white rounded-2xl p-8 shadow-md hover:shadow-lg transition-shadow">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <div class="lg:col-span-1">
                            <div class="text-blue-600 font-semibold text-lg mb-2">
                                <?php echo esc_html($exp['period']); ?>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-800 mb-2">
                                <?php echo esc_html($exp['title']); ?>
                            </h3>
                            <div class="text-gray-600 font-medium mb-4">
                                <?php echo esc_html($exp['role']); ?>
                            </div>
                            <p class="text-gray-600 leading-relaxed">
                                <?php echo esc_html($exp['description']); ?>
                            </p>
                        </div>
                        
                        <div class="lg:col-span-2">
                            <div class="mb-6">
                                <h4 class="font-semibold text-gray-800 mb-3">主要技術スキル</h4>
                                <div class="flex flex-wrap gap-2">
                                    <?php foreach ($exp['skills'] as $skill) : ?>
                                        <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-medium">
                                            <?php echo esc_html($skill); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div>
                                <h4 class="font-semibold text-gray-800 mb-3">主な実績・成果</h4>
                                <ul class="space-y-2">
                                    <?php foreach ($exp['achievements'] as $achievement) : ?>
                                        <li class="flex items-start space-x-3">
                                            <div class="w-2 h-2 flex items-center justify-center mt-2">
                                                <i class="ri-check-line text-green-600 text-sm"></i>
                                            </div>
                                            <span class="text-gray-600"><?php echo esc_html($achievement); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
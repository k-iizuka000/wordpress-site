<?php
/**
 * Template part for displaying personality section
 * Used in: page-about.php
 */

// 性格・価値観データ
$personalities = [
    [
        'title' => '明るく前向き',
        'description' => 'どんな困難な課題でも、必ず解決策があると信じて取り組みます。チーム内でもポジティブな雰囲気作りを心がけています。',
        'icon' => 'ri-sun-line',
        'color' => 'yellow'
    ],
    [
        'title' => 'コミュニケーション重視',
        'description' => 'クライアントとの対話を大切にし、要件をしっかりヒアリングして最適な提案をします。技術的な内容も分かりやすく説明します。',
        'icon' => 'ri-chat-smile-2-line',
        'color' => 'blue'
    ],
    [
        'title' => '継続的な学習',
        'description' => '新しい技術やツールに積極的に取り組み、常にスキルアップを心がけています。ロードバイクで培った継続力が活かされています。',
        'icon' => 'ri-book-open-line',
        'color' => 'green'
    ],
    [
        'title' => '品質へのこだわり',
        'description' => 'コードの可読性や保守性を重視し、長期的に安定して動作するシステムの構築を目指しています。',
        'icon' => 'ri-award-line',
        'color' => 'purple'
    ],
    [
        'title' => 'チームワーク',
        'description' => 'チームでの開発では、メンバーとの協力を大切にし、全体の生産性向上に貢献します。',
        'icon' => 'ri-team-line',
        'color' => 'orange'
    ],
    [
        'title' => '効率化思考',
        'description' => '無駄な作業を見つけ出し、自動化によって効率を改善することに喜びを感じます。時間の価値を最大化します。',
        'icon' => 'ri-rocket-line',
        'color' => 'red'
    ]
];

// カラークラス名をマッピング
function get_color_classes($color) {
    $color_classes = [
        'yellow' => [
            'bg' => 'bg-yellow-100',
            'text' => 'text-yellow-600'
        ],
        'blue' => [
            'bg' => 'bg-blue-100',
            'text' => 'text-blue-600'
        ],
        'green' => [
            'bg' => 'bg-green-100',
            'text' => 'text-green-600'
        ],
        'purple' => [
            'bg' => 'bg-purple-100',
            'text' => 'text-purple-600'
        ],
        'orange' => [
            'bg' => 'bg-orange-100',
            'text' => 'text-orange-600'
        ],
        'red' => [
            'bg' => 'bg-red-100',
            'text' => 'text-red-600'
        ]
    ];
    
    return isset($color_classes[$color]) ? $color_classes[$color] : $color_classes['blue'];
}
?>

<section class="py-20 bg-white">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">
                私の性格・価値観
            </h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto leading-relaxed">
                エンジニアとしての技術力だけでなく、人としての魅力も大切にしています。
                ロードバイクで培った精神力と明るい性格で、プロジェクトを成功に導きます。
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($personalities as $index => $item) : ?>
                <?php $color_classes = get_color_classes($item['color']); ?>
                <div class="bg-gray-50 rounded-2xl p-6 hover:shadow-lg transition-all transform hover:-translate-y-1">
                    <div class="w-14 h-14 flex items-center justify-center mx-auto mb-4 <?php echo esc_attr($color_classes['bg']); ?> rounded-xl">
                        <i class="<?php echo esc_attr($item['icon']); ?> <?php echo esc_attr($color_classes['text']); ?> text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-3 text-center">
                        <?php echo esc_html($item['title']); ?>
                    </h3>
                    <p class="text-gray-600 leading-relaxed text-center">
                        <?php echo esc_html($item['description']); ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
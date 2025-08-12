<?php
/**
 * Contact Info Template Part
 * 
 * @package Kei_Portfolio
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Contact methods data
$contact_methods = [
    [
        'title' => 'メール',
        'description' => '一般的なお問い合わせ',
        'contact' => 'contact@example.com',
        'icon' => 'ri-mail-line',
        'color' => 'blue'
    ],
    [
        'title' => '技術相談',
        'description' => '技術的な質問・相談',
        'contact' => 'tech@portfolio.com',
        'icon' => 'ri-code-line',
        'color' => 'green'
    ],
    [
        'title' => 'プロジェクト相談',
        'description' => 'プロジェクトのご依頼',
        'contact' => 'project@portfolio.com',
        'icon' => 'ri-briefcase-line',
        'color' => 'purple'
    ]
];

// Working style data
$working_style = [
    [
        'title' => '柔軟な働き方',
        'description' => 'リモートワーク中心で、必要に応じて現地での作業も可能です。',
        'icon' => 'ri-home-wifi-line'
    ],
    [
        'title' => '迅速なコミュニケーション',
        'description' => 'Slack、Teams、Zoomなどお客様の環境に合わせて対応します。',
        'icon' => 'ri-chat-3-line'
    ],
    [
        'title' => '継続的なサポート',
        'description' => '開発完了後も保守・運用サポートを提供いたします。',
        'icon' => 'ri-customer-service-2-line'
    ],
    [
        'title' => '透明性のある進行管理',
        'description' => '進捗状況を定期的にレポートし、常に状況を共有します。',
        'icon' => 'ri-line-chart-line'
    ]
];
?>

<section class="py-20 bg-gray-50">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">
                直接連絡・働き方について
            </h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                フォーム以外にも直接メールでご連絡いただけます。
                柔軟な働き方でお客様のニーズにお応えします。
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-20">
            <?php foreach ($contact_methods as $index => $method) : ?>
                <div class="bg-white rounded-2xl p-8 text-center shadow-md hover:shadow-lg transition-shadow">
                    <div class="w-16 h-16 flex items-center justify-center mx-auto mb-4 bg-<?php echo esc_attr($method['color']); ?>-100 rounded-full">
                        <i class="<?php echo esc_attr($method['icon']); ?> text-<?php echo esc_attr($method['color']); ?>-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-2"><?php echo esc_html($method['title']); ?></h3>
                    <p class="text-gray-600 mb-4"><?php echo esc_html($method['description']); ?></p>
                    <a 
                        href="mailto:<?php echo esc_attr($method['contact']); ?>"
                        class="text-<?php echo esc_attr($method['color']); ?>-600 hover:text-<?php echo esc_attr($method['color']); ?>-700 font-semibold transition-colors cursor-pointer"
                    >
                        <?php echo esc_html($method['contact']); ?>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="mb-20">
            <h3 class="text-2xl font-bold text-gray-800 text-center mb-12">
                働き方・サポート体制
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <?php foreach ($working_style as $index => $style) : ?>
                    <div class="flex items-start space-x-4 bg-white rounded-xl p-6 shadow-md">
                        <div class="w-12 h-12 flex items-center justify-center bg-blue-100 rounded-lg flex-shrink-0">
                            <i class="<?php echo esc_attr($style['icon']); ?> text-blue-600 text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800 mb-2"><?php echo esc_html($style['title']); ?></h4>
                            <p class="text-gray-600 leading-relaxed"><?php echo esc_html($style['description']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-blue-600 to-green-600 rounded-2xl p-8 text-center">
            <div class="max-w-4xl mx-auto">
                <h3 class="text-2xl md:text-3xl font-bold text-white mb-6">
                    ロードバイクのように、一緒に目標に向かって進みましょう
                </h3>
                <p class="text-xl text-blue-100 mb-8 leading-relaxed">
                    持続力と前向きな姿勢で、お客様のビジネス課題を解決いたします。<br />
                    まずは気軽にお話を聞かせてください。
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white/20 rounded-xl p-4">
                        <div class="text-2xl font-bold text-white mb-1">24時間</div>
                        <div class="text-blue-100 text-sm">以内にご返信</div>
                    </div>
                    <div class="bg-white/20 rounded-xl p-4">
                        <div class="text-2xl font-bold text-white mb-1">95%</div>
                        <div class="text-blue-100 text-sm">クライアント満足度</div>
                    </div>
                    <div class="bg-white/20 rounded-xl p-4">
                        <div class="text-2xl font-bold text-white mb-1">6年</div>
                        <div class="text-blue-100 text-sm">の開発経験</div>
                    </div>
                </div>
                
                <div class="mt-8 flex justify-center">
                    <div class="flex items-center space-x-2 text-white">
                        <i class="ri-bike-line text-xl"></i>
                        <span class="font-semibold">継続は力なり - Let's go together!</span>
                        <i class="ri-bike-line text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
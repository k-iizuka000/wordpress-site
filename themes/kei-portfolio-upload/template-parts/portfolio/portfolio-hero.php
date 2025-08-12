<?php
/**
 * Portfolio Hero Section
 * 
 * Displays the main hero section for the portfolio page with statistics
 * Converted from React: app/portfolio/PortfolioHero.tsx
 */

// Get hero settings from customizer or defaults
$hero_title = get_theme_mod('portfolio_hero_title', 'これまでの<span class="text-blue-300">案件一覧</span>');
$hero_description = get_theme_mod('portfolio_hero_description', '自動化の力で業務効率を革新し、お客様の時間を価値ある活動に変える。20社以上のプロジェクトで培った経験と実績をご紹介します。');
$background_image = get_theme_mod('portfolio_hero_background', 'https://readdy.ai/api/search-image?query=Modern%20developer%20workspace%20with%20multiple%20monitors%20showing%20code%20and%20automated%20systems%2C%20clean%20organized%20desk%20setup%20with%20programming%20tools%2C%20bright%20natural%20lighting%2C%20professional%20software%20development%20environment%20with%20blue%20and%20green%20accent%20colors%2C%20inspiring%20tech%20workspace&width=1920&height=800&seq=portfolio-hero&orientation=landscape');

// Sanitize customizer values for security
$hero_title = wp_kses_post($hero_title);
$hero_description = esc_html($hero_description);
$background_image = esc_url($background_image);

// Statistics data - can be made editable via customizer
$stats = array(
    array(
        'number' => '20+',
        'label' => '完了プロジェクト',
        'color' => 'text-blue-300'
    ),
    array(
        'number' => '70%',
        'label' => '平均作業時間短縮',
        'color' => 'text-green-300'
    ),
    array(
        'number' => '90%',
        'label' => '顧客リピート率',
        'color' => 'text-purple-300'
    )
);
?>

<section 
    class="relative py-24 bg-gradient-to-r from-blue-50 to-green-50"
    style="background-image: url('<?php echo $background_image; ?>'); background-size: cover; background-position: center;"
>
    <div class="absolute inset-0 bg-blue-900/70"></div>
    
    <div class="relative z-10 max-w-6xl mx-auto px-4">
        <div class="text-center text-white">
            <h1 class="text-4xl md:text-5xl font-bold leading-tight mb-6">
                <?php echo $hero_title; ?>
            </h1>
            <p class="text-xl leading-relaxed mb-8 text-gray-200 max-w-3xl mx-auto">
                <?php echo $hero_description; ?>
            </p>
            <div class="flex flex-wrap justify-center gap-6 mb-8">
                <?php foreach ($stats as $stat) : ?>
                    <div class="bg-white/20 rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold <?php echo esc_attr($stat['color']); ?> mb-1">
                            <?php echo esc_html($stat['number']); ?>
                        </div>
                        <div class="text-sm">
                            <?php echo esc_html($stat['label']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
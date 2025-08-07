<?php
/**
 * Template part for displaying contact hero section
 * 
 * @package kei-portfolio
 */
?>

<section 
    class="relative py-24 bg-gradient-to-r from-blue-50 to-green-50"
    style="background-image: url('https://readdy.ai/api/search-image?query=Modern%20professional%20workspace%20with%20laptop%20and%20coffee%20cup%20on%20clean%20desk%2C%20soft%20natural%20lighting%20through%20window%2C%20minimalist%20office%20environment%20with%20plants%2C%20creating%20welcoming%20atmosphere%20for%20business%20communication%20and%20collaboration%2C%20bright%20and%20clean%20aesthetic%20with%20blue%20and%20green%20accents&width=1920&height=800&seq=contact-workspace&orientation=landscape'); background-size: cover; background-position: center;">
    
    <div class="absolute inset-0 bg-blue-900/70"></div>
    
    <div class="relative z-10 max-w-4xl mx-auto px-4 text-center">
        <h1 class="text-4xl md:text-5xl font-bold text-white mb-6">
            <?php echo esc_html(get_theme_mod('contact_hero_title', 'お気軽にお問い合わせください')); ?>
        </h1>
        <p class="text-xl text-gray-200 leading-relaxed mb-8 max-w-3xl mx-auto">
            <?php echo esc_html(get_theme_mod('contact_hero_description', '自動化による業務効率化のご相談から、プロジェクトの詳細まで、どんなことでもお気軽にご相談ください。24時間以内にご返信いたします。')); ?>
        </p>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-12">
            <div class="bg-white/20 rounded-xl p-6 text-center">
                <div class="w-12 h-12 flex items-center justify-center mx-auto mb-3 bg-blue-600 rounded-lg">
                    <i class="ri-time-line text-white text-xl"></i>
                </div>
                <h3 class="font-semibold text-white mb-2">
                    <?php echo esc_html(get_theme_mod('contact_feature1_title', '迅速な対応')); ?>
                </h3>
                <p class="text-gray-200 text-sm">
                    <?php echo esc_html(get_theme_mod('contact_feature1_desc', '24時間以内にご返信')); ?>
                </p>
            </div>
            
            <div class="bg-white/20 rounded-xl p-6 text-center">
                <div class="w-12 h-12 flex items-center justify-center mx-auto mb-3 bg-green-600 rounded-lg">
                    <i class="ri-chat-smile-2-line text-white text-xl"></i>
                </div>
                <h3 class="font-semibold text-white mb-2">
                    <?php echo esc_html(get_theme_mod('contact_feature2_title', '親切な対応')); ?>
                </h3>
                <p class="text-gray-200 text-sm">
                    <?php echo esc_html(get_theme_mod('contact_feature2_desc', '分かりやすい説明を心がけます')); ?>
                </p>
            </div>
            
            <div class="bg-white/20 rounded-xl p-6 text-center">
                <div class="w-12 h-12 flex items-center justify-center mx-auto mb-3 bg-purple-600 rounded-lg">
                    <i class="ri-hand-heart-line text-white text-xl"></i>
                </div>
                <h3 class="font-semibold text-white mb-2">
                    <?php echo esc_html(get_theme_mod('contact_feature3_title', '無料相談')); ?>
                </h3>
                <p class="text-gray-200 text-sm">
                    <?php echo esc_html(get_theme_mod('contact_feature3_desc', '初回相談は完全無料です')); ?>
                </p>
            </div>
        </div>
    </div>
</section>
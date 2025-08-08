<?php
/**
 * About Hero Section
 * app/about/AboutHero.tsx から変換
 */
?>

<section 
    class="relative py-24 bg-gradient-to-r from-blue-50 to-green-50"
    style="
        background-image: url('https://readdy.ai/api/search-image?query=Professional%20software%20engineer%20working%20on%20laptop%20in%20a%20bright%20modern%20workspace%20with%20natural%20lighting%2C%20clean%20desk%20setup%20with%20plants%2C%20showing%20concentration%20and%20positive%20energy%2C%20minimalist%20background%20with%20blue%20and%20green%20accents%2C%20modern%20office%20environment&width=1920&height=800&seq=engineer-workspace&orientation=landscape');
        background-size: cover;
        background-position: center;
    "
>
    <div class="absolute inset-0 bg-blue-900/70"></div>
    
    <div class="relative z-10 max-w-6xl mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="text-white">
                <h1 class="text-4xl md:text-5xl font-bold leading-tight mb-6">
                    <?php echo esc_html__('明るく前向きな', 'kei-portfolio'); ?>
                    <br>
                    <span class="text-blue-300"><?php echo esc_html__('エンジニア', 'kei-portfolio'); ?></span><?php echo esc_html__('です', 'kei-portfolio'); ?>
                </h1>
                <p class="text-xl leading-relaxed mb-8 text-gray-200">
                    <?php echo esc_html__('こんにちは！フリーランスエンジニアとして活動している田中太郎です。プログラミングによる自動化とロードバイクが人生の大きな柱となっています。', 'kei-portfolio'); ?>
                </p>
                <div class="flex flex-wrap gap-4">
                    <div class="flex items-center space-x-2 bg-white/20 rounded-full px-4 py-2">
                        <i class="ri-code-line text-blue-300"></i>
                        <span class="text-sm font-medium"><?php echo esc_html__('自動化スペシャリスト', 'kei-portfolio'); ?></span>
                    </div>
                    <div class="flex items-center space-x-2 bg-white/20 rounded-full px-4 py-2">
                        <i class="ri-bike-line text-green-300"></i>
                        <span class="text-sm font-medium"><?php echo esc_html__('ロードバイク愛好家', 'kei-portfolio'); ?></span>
                    </div>
                    <div class="flex items-center space-x-2 bg-white/20 rounded-full px-4 py-2">
                        <i class="ri-heart-line text-red-300"></i>
                        <span class="text-sm font-medium"><?php echo esc_html__('ポジティブシンカー', 'kei-portfolio'); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-center">
                <div class="relative">
                    <img 
                        src="https://readdy.ai/api/search-image?query=Friendly%20professional%20engineer%20portrait%2C%20smiling%20confidently%2C%20wearing%20casual%20business%20attire%2C%20bright%20natural%20lighting%2C%20clean%20modern%20background%20with%20subtle%20blue%20and%20green%20elements%2C%20professional%20headshot%20style%20with%20warm%20approachable%20expression&width=400&height=500&seq=engineer-portrait&orientation=portrait" 
                        alt="<?php echo esc_attr__('エンジニアのプロフィール写真', 'kei-portfolio'); ?>"
                        class="w-80 h-96 object-cover object-top rounded-2xl shadow-2xl"
                    />
                    <div class="absolute -bottom-4 -right-4 bg-white rounded-xl p-3 shadow-lg">
                        <i class="ri-code-s-slash-line text-blue-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
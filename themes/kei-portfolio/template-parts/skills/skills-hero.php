<?php
/**
 * Skills Hero Section
 * 
 * Displays hero section for skills page with statistics and background image
 * 
 * @package kei-portfolio
 */
?>

<section 
    class="relative py-24 bg-gradient-to-r from-blue-50 to-green-50"
    style="
        background-image: url('https://readdy.ai/api/search-image?query=Modern%20developer%20workspace%20with%20multiple%20monitors%20displaying%20code%2C%20programming%20tools%20and%20development%20environments%2C%20clean%20desk%20setup%20with%20mechanical%20keyboard%20and%20modern%20equipment%2C%20bright%20natural%20lighting%20creating%20productive%20atmosphere%20with%20blue%20and%20green%20accent%20colors&width=1920&height=800&seq=skills-workspace&orientation=landscape');
        background-size: cover;
        background-position: center;
    "
>
    <div class="absolute inset-0 bg-blue-900/70"></div>
    
    <div class="relative z-10 max-w-6xl mx-auto px-4">
        <div class="text-center text-white">
            <h1 class="text-4xl md:text-6xl font-bold leading-tight mb-6">
                技術スキル一覧
            </h1>
            <p class="text-xl md:text-2xl mb-8 text-gray-200 max-w-4xl mx-auto leading-relaxed">
                6年間のエンジニア経験で培った幅広い技術スキルで、<br>
                あらゆる課題に対応できます
            </p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-12">
                <div class="bg-white/20 rounded-xl p-6 text-center">
                    <div class="text-3xl font-bold text-blue-300 mb-2">20+</div>
                    <div class="text-gray-200">習得技術</div>
                </div>
                <div class="bg-white/20 rounded-xl p-6 text-center">
                    <div class="text-3xl font-bold text-green-300 mb-2">50+</div>
                    <div class="text-gray-200">完了プロジェクト</div>
                </div>
                <div class="bg-white/20 rounded-xl p-6 text-center">
                    <div class="text-3xl font-bold text-yellow-300 mb-2">6年</div>
                    <div class="text-gray-200">エンジニア経験</div>
                </div>
            </div>
        </div>
    </div>
</section>
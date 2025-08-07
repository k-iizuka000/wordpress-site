<?php
/**
 * Cycling Passion Section
 * app/about/CyclingPassion.tsx から変換
 */

// サイクリングのメリット配列（PHPの場合は静的データとして定義）
$cycling_benefits = array(
    array(
        'title' => '持続力の向上',
        'description' => '長距離ライドで培った持続力が、長時間のプログラミング作業にも活かされています。集中力を維持して品質の高いコードを書けます。',
        'icon' => 'ri-battery-charge-line'
    ),
    array(
        'title' => '目標設定能力',
        'description' => 'レースやイベントに向けた計画的なトレーニングで、プロジェクトの進行管理やマイルストーン設定のスキルが身につきました。',
        'icon' => 'ri-flag-line'
    ),
    array(
        'title' => 'ストレス解消',
        'description' => '自然の中を走ることで心身をリフレッシュし、常にポジティブな状態でクライアントと向き合えます。創造性も高まります。',
        'icon' => 'ri-leaf-line'
    ),
    array(
        'title' => 'チーム精神',
        'description' => 'サイクリング仲間との協力やサポートを通じて、チームワークの重要性を学びました。開発チームでも活かされています。',
        'icon' => 'ri-group-line'
    )
);
?>

<section class="py-20 bg-white">
    <div class="max-w-6xl mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center mb-20">
            <div>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-6">
                    <?php echo esc_html__('ロードバイクへの情熱', 'kei-portfolio'); ?>
                </h2>
                <p class="text-lg text-gray-600 leading-relaxed mb-6">
                    <?php echo esc_html__('週末は愛車のロードバイクで山道や海沿いの道を走っています。2019年にロードバイクを始めてから、既に1万キロ以上を走破しました。', 'kei-portfolio'); ?>
                </p>
                <p class="text-lg text-gray-600 leading-relaxed mb-8">
                    <?php echo esc_html__('ロードバイクから学んだ「継続する力」「目標に向かって努力する姿勢」「チームワーク」は、エンジニアとしての仕事にも大きく活かされています。', 'kei-portfolio'); ?>
                </p>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-blue-50 rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold text-blue-600 mb-1">12,000+</div>
                        <div class="text-sm text-gray-600"><?php echo esc_html__('総走行距離（km）', 'kei-portfolio'); ?></div>
                    </div>
                    <div class="bg-green-50 rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold text-green-600 mb-1">50+</div>
                        <div class="text-sm text-gray-600"><?php echo esc_html__('参加イベント数', 'kei-portfolio'); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="relative">
                <img 
                    src="https://readdy.ai/api/search-image?query=Professional%20cyclist%20in%20bright%20cycling%20gear%20riding%20a%20carbon%20road%20bike%20on%20a%20scenic%20mountain%20road%20with%20beautiful%20landscape%20view%2C%20golden%20hour%20lighting%2C%20showing%20passion%20and%20determination%2C%20action%20shot%20with%20motion%20blur%20background%2C%20inspiring%20outdoor%20cycling%20scene&width=600&height=400&seq=cycling-passion&orientation=landscape" 
                    alt="<?php echo esc_attr__('ロードバイクでの走行シーン', 'kei-portfolio'); ?>"
                    class="w-full h-80 object-cover object-top rounded-2xl shadow-lg"
                />
                <div class="absolute top-4 left-4 bg-white/90 rounded-lg p-2">
                    <i class="ri-bike-line text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="mb-16">
            <h3 class="text-2xl font-bold text-gray-800 text-center mb-8">
                <?php echo esc_html__('ロードバイクから学んだこと', 'kei-portfolio'); ?>
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach ($cycling_benefits as $index => $benefit) : ?>
                    <div class="flex items-start space-x-4 p-6 bg-gray-50 rounded-xl">
                        <div class="w-12 h-12 flex items-center justify-center bg-blue-100 rounded-lg flex-shrink-0">
                            <i class="<?php echo esc_attr($benefit['icon']); ?> text-blue-600 text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800 mb-2"><?php echo esc_html($benefit['title']); ?></h4>
                            <p class="text-gray-600 leading-relaxed"><?php echo esc_html($benefit['description']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-blue-50 to-green-50 rounded-2xl p-8">
            <div class="text-center">
                <h3 class="text-2xl font-bold text-gray-800 mb-4">
                    <?php echo esc_html__('一緒にプロジェクトを走り抜けましょう', 'kei-portfolio'); ?>
                </h3>
                <p class="text-lg text-gray-600 mb-6 max-w-3xl mx-auto leading-relaxed">
                    <?php echo esc_html__('ロードバイクで培った持続力とポジティブな姿勢で、お客様のプロジェクトを最後まで責任持って完走いたします。どんな困難な道のりでも、一緒に乗り越えていきましょう！', 'kei-portfolio'); ?>
                </p>
                <div class="flex justify-center">
                    <div class="flex items-center space-x-2 text-blue-600 font-semibold">
                        <i class="ri-bike-line text-xl"></i>
                        <span><?php echo esc_html__('"継続は力なり"を信条に', 'kei-portfolio'); ?></span>
                        <i class="ri-bike-line text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
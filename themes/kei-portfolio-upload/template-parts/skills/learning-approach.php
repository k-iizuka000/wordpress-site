<?php
/**
 * Template Part: Learning Approach Section
 * 
 * 学習姿勢・継続的成長セクションを表示
 */

$learning_methods = array(
    array(
        'title' => '実践的な学習',
        'description' => '実際のプロジェクトを通じて技術を習得し、すぐに実務で活用できるスキルを身につけます。',
        'icon' => 'ri-hammer-line',
        'color' => 'blue'
    ),
    array(
        'title' => '継続的なアップデート',
        'description' => '技術トレンドを常にキャッチアップし、新しいツールやフレームワークを積極的に試します。',
        'icon' => 'ri-refresh-line',
        'color' => 'green'
    ),
    array(
        'title' => 'コミュニティ参加',
        'description' => '勉強会やカンファレンスに参加し、エンジニア同士の知識共有を大切にしています。',
        'icon' => 'ri-team-line',
        'color' => 'purple'
    ),
    array(
        'title' => 'ドキュメント重視',
        'description' => '学習内容をドキュメント化し、チームメンバーや将来の自分のために知識を整理します。',
        'icon' => 'ri-book-line',
        'color' => 'orange'
    )
);

$current_learning = array(
    array(
        'technology' => 'AI・機械学習',
        'progress' => 75,
        'description' => 'ChatGPT API、機械学習ライブラリを活用した自動化ツールの開発',
        'timeline' => '2024年1月〜現在'
    ),
    array(
        'technology' => 'クラウドネイティブ',
        'progress' => 60,
        'description' => 'Kubernetes、マイクロサービスアーキテクチャの習得',
        'timeline' => '2024年3月〜現在'
    ),
    array(
        'technology' => 'モバイル開発',
        'progress' => 45,
        'description' => 'React Native、Flutterによるクロスプラットフォーム開発',
        'timeline' => '2024年6月〜現在'
    ),
    array(
        'technology' => 'ブロックチェーン',
        'progress' => 30,
        'description' => 'スマートコントラクト、Web3技術の基礎学習',
        'timeline' => '2024年8月〜現在'
    )
);

$continuous_learning_points = array(
    array(
        'title' => '毎日の学習習慣',
        'description' => '平均2時間/日の技術学習を継続中'
    ),
    array(
        'title' => '実践を通じた習得',
        'description' => '学んだ技術を即座にプロジェクトで活用'
    ),
    array(
        'title' => '知識の共有',
        'description' => 'ブログ執筆や勉強会での発表を定期的に実施'
    )
);
?>

<section class="py-20 bg-gray-50">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">
                学習姿勢・継続的成長
            </h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                ロードバイクで培った継続力を活かし、常に新しい技術を学び続けています
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-20">
            <?php foreach ($learning_methods as $method_index => $method) : ?>
                <div class="bg-white rounded-2xl p-6 text-center hover:shadow-lg transition-all transform hover:-translate-y-1 learning-method-card">
                    <div class="w-14 h-14 flex items-center justify-center mx-auto mb-4 bg-<?php echo esc_attr($method['color']); ?>-100 rounded-xl">
                        <i class="<?php echo esc_attr($method['icon']); ?> text-<?php echo esc_attr($method['color']); ?>-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-3"><?php echo esc_html($method['title']); ?></h3>
                    <p class="text-gray-600 text-sm leading-relaxed"><?php echo esc_html($method['description']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="bg-white rounded-2xl p-8 shadow-sm mb-20">
            <div class="flex items-center space-x-3 mb-8">
                <div class="w-10 h-10 flex items-center justify-center bg-blue-100 rounded-lg">
                    <i class="ri-trending-up-line text-blue-600 text-lg"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800">現在学習中の技術</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <?php foreach ($current_learning as $learning_index => $item) : ?>
                    <div class="border-l-4 border-blue-400 pl-6 py-4 learning-progress-item">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-xl font-semibold text-gray-800"><?php echo esc_html($item['technology']); ?></h4>
                            <span class="text-blue-600 font-bold"><?php echo esc_html($item['progress']); ?>%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3 mb-3">
                            <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full transition-all duration-1000 progress-bar"
                                 data-progress="<?php echo esc_attr($item['progress']); ?>">
                            </div>
                        </div>
                        <p class="text-gray-600 mb-2"><?php echo esc_html($item['description']); ?></p>
                        <span class="text-sm text-gray-500"><?php echo esc_html($item['timeline']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="cycling-content">
                <h3 class="text-2xl md:text-3xl font-bold text-gray-800 mb-6">
                    ロードバイクから学んだ継続力
                </h3>
                <p class="text-lg text-gray-600 leading-relaxed mb-6">
                    ロードバイクでは、長距離を走り抜くために継続的なトレーニングが欠かせません。
                    この経験から「小さな積み重ね」の大切さを学びました。
                </p>
                <div class="space-y-4">
                    <?php foreach ($continuous_learning_points as $point_index => $point) : ?>
                        <div class="flex items-start space-x-3 learning-point">
                            <div class="w-6 h-6 flex items-center justify-center bg-green-100 rounded-full mt-1">
                                <i class="ri-check-line text-green-600 text-sm"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800"><?php echo esc_html($point['title']); ?></h4>
                                <p class="text-gray-600"><?php echo esc_html($point['description']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="relative cycling-image">
                <img src="https://readdy.ai/api/search-image?query=Professional%20cyclist%20training%20on%20road%20bike%20with%20determination%20and%20focus%2C%20studying%20route%20map%20or%20training%20plan%2C%20showing%20discipline%20and%20continuous%20improvement%20mindset%2C%20bright%20outdoor%20setting%20with%20modern%20cycling%20gear%20and%20technology&width=500&height=600&seq=learning-cyclist&orientation=portrait" 
                     alt="継続学習のイメージ"
                     class="w-full h-80 object-cover object-top rounded-2xl shadow-lg">
                <div class="absolute top-4 right-4 bg-white/90 rounded-lg p-3">
                    <i class="ri-book-open-line text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* アニメーション用スタイル */
.learning-method-card {
    opacity: 0;
    transform: translateY(30px);
    transition: all 0.6s ease-out;
}

.learning-method-card.animate {
    opacity: 1;
    transform: translateY(0);
}

.learning-progress-item {
    opacity: 0;
    transform: translateX(-20px);
    transition: all 0.6s ease-out;
}

.learning-progress-item.animate {
    opacity: 1;
    transform: translateX(0);
}

.progress-bar {
    width: 0%;
}

.progress-bar.animate {
    transition: width 2s ease-out;
}

.learning-point {
    opacity: 0;
    transform: translateX(-15px);
    transition: all 0.5s ease-out;
}

.learning-point.animate {
    opacity: 1;
    transform: translateX(0);
}

.cycling-content,
.cycling-image {
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.8s ease-out;
}

.cycling-content.animate,
.cycling-image.animate {
    opacity: 1;
    transform: translateY(0);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const element = entry.target;
                
                if (element.classList.contains('learning-method-card')) {
                    setTimeout(() => {
                        element.classList.add('animate');
                    }, parseInt(element.dataset.delay) || 0);
                } else if (element.classList.contains('learning-progress-item')) {
                    setTimeout(() => {
                        element.classList.add('animate');
                        // プログレスバーもアニメーション
                        const progressBar = element.querySelector('.progress-bar');
                        if (progressBar) {
                            progressBar.classList.add('animate');
                            progressBar.style.width = progressBar.dataset.progress + '%';
                        }
                    }, parseInt(element.dataset.delay) || 0);
                } else if (element.classList.contains('learning-point')) {
                    setTimeout(() => {
                        element.classList.add('animate');
                    }, parseInt(element.dataset.delay) || 0);
                } else if (element.classList.contains('cycling-content') || element.classList.contains('cycling-image')) {
                    setTimeout(() => {
                        element.classList.add('animate');
                    }, parseInt(element.dataset.delay) || 0);
                }
                
                observer.unobserve(element);
            }
        });
    }, {
        threshold: 0.1
    });

    // 学習方法カードにディレイを設定
    const methodCards = document.querySelectorAll('.learning-method-card');
    methodCards.forEach((card, index) => {
        card.dataset.delay = (index * 150);
        observer.observe(card);
    });

    // 学習進捗アイテムにディレイを設定
    const progressItems = document.querySelectorAll('.learning-progress-item');
    progressItems.forEach((item, index) => {
        item.dataset.delay = (index * 200);
        observer.observe(item);
    });

    // 継続学習ポイントにディレイを設定
    const learningPoints = document.querySelectorAll('.learning-point');
    learningPoints.forEach((point, index) => {
        point.dataset.delay = (index * 150);
        observer.observe(point);
    });

    // サイクリング関連セクション
    const cyclingContent = document.querySelector('.cycling-content');
    const cyclingImage = document.querySelector('.cycling-image');
    
    if (cyclingContent) {
        cyclingContent.dataset.delay = '0';
        observer.observe(cyclingContent);
    }
    
    if (cyclingImage) {
        cyclingImage.dataset.delay = '200';
        observer.observe(cyclingImage);
    }
});
</script>
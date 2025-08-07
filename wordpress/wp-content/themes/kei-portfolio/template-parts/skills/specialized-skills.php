<?php
/**
 * Template Part: Specialized Skills Section
 * 
 * 専門スキル・得意分野セクションを表示
 */

$specializations = array(
    array(
        'title' => '業務自動化',
        'description' => '繰り返し作業の効率化とヒューマンエラーの削減を実現',
        'icon' => 'ri-robot-line',
        'color' => 'blue',
        'techniques' => array(
            'Webブラウザ自動操作（Selenium）',
            'Excel・CSV自動処理（Python）',
            'メール・レポート自動送信',
            'データベース自動更新',
            'ファイル整理・バックアップ自動化'
        ),
        'achievements' => array(
            '作業時間90%削減を複数案件で達成',
            '月間1000時間の業務工数を200時間に短縮',
            'エラー率をほぼゼロまで削減'
        )
    ),
    array(
        'title' => 'データ分析・処理',
        'description' => '大量データから価値ある情報を抽出し、意思決定をサポート',
        'icon' => 'ri-bar-chart-line',
        'color' => 'green',
        'techniques' => array(
            'Python（Pandas、NumPy）での統計解析',
            'データクレンジング・前処理',
            '可視化（Matplotlib、Plotly）',
            '予測モデル構築（機械学習）',
            'レポート自動生成'
        ),
        'achievements' => array(
            '売上予測精度85%を達成',
            '顧客データ分析により売上15%向上に貢献',
            '月次レポート作成時間を1日から30分に短縮'
        )
    ),
    array(
        'title' => 'Webスクレイピング',
        'description' => 'Web上の情報を効率的に収集・分析するシステム構築',
        'icon' => 'ri-global-line',
        'color' => 'purple',
        'techniques' => array(
            'Beautiful Soup・Scrapyによるデータ抽出',
            'JavaScript実行サイト対応（Selenium）',
            'プロキシ・ヘッダー制御',
            'スケジュール実行・監視機能',
            'データベース自動保存'
        ),
        'achievements' => array(
            '競合サイトの価格情報を24時間監視',
            '1日10万件のデータ収集システム構築',
            'マーケット分析の精度向上に貢献'
        )
    ),
    array(
        'title' => 'API連携・統合',
        'description' => '異なるシステム間のデータ連携とワークフロー自動化',
        'icon' => 'ri-plug-line',
        'color' => 'orange',
        'techniques' => array(
            'REST API・GraphQL連携',
            'OAuth認証・トークン管理',
            '非同期処理・エラーハンドリング',
            'Webhook実装・リアルタイム連携',
            'データ変換・マッピング'
        ),
        'achievements' => array(
            '5つのシステム間でデータ自動連携',
            '手動データ入力を100%自動化',
            'システム間の処理時間を80%短縮'
        )
    )
);

$other_skills = array(
    array('name' => 'プロジェクト管理', 'icon' => 'ri-task-line'),
    array('name' => 'システム設計', 'icon' => 'ri-layout-line'),
    array('name' => 'パフォーマンス最適化', 'icon' => 'ri-speed-up-line'),
    array('name' => 'セキュリティ対策', 'icon' => 'ri-shield-check-line'),
    array('name' => 'ドキュメント作成', 'icon' => 'ri-file-text-line'),
    array('name' => 'ユーザビリティ改善', 'icon' => 'ri-user-heart-line'),
    array('name' => 'コードレビュー', 'icon' => 'ri-code-view-line'),
    array('name' => '技術選定・提案', 'icon' => 'ri-lightbulb-line')
);
?>

<section class="py-20 bg-white">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">
                専門スキル・得意分野
            </h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                自動化とデータ活用による業務効率化を中心とした専門的なスキルセット
            </p>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <?php foreach ($specializations as $spec_index => $spec) : ?>
                <div class="bg-gray-50 rounded-2xl p-8 hover:shadow-lg transition-all specialization-card">
                    <div class="flex items-center space-x-4 mb-6">
                        <div class="w-16 h-16 flex items-center justify-center bg-<?php echo esc_attr($spec['color']); ?>-100 rounded-2xl">
                            <i class="<?php echo esc_attr($spec['icon']); ?> text-<?php echo esc_attr($spec['color']); ?>-600 text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800"><?php echo esc_html($spec['title']); ?></h3>
                            <p class="text-gray-600"><?php echo esc_html($spec['description']); ?></p>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <h4 class="font-semibold text-gray-800 mb-3">主要技術・手法</h4>
                        <ul class="space-y-2">
                            <?php foreach ($spec['techniques'] as $technique_index => $technique) : ?>
                                <li class="flex items-start space-x-2">
                                    <div class="w-2 h-2 flex items-center justify-center mt-2">
                                        <i class="ri-check-line text-<?php echo esc_attr($spec['color']); ?>-600 text-sm"></i>
                                    </div>
                                    <span class="text-gray-600"><?php echo esc_html($technique); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold text-gray-800 mb-3">実績・成果</h4>
                        <div class="space-y-2">
                            <?php foreach ($spec['achievements'] as $achievement_index => $achievement) : ?>
                                <div class="bg-<?php echo esc_attr($spec['color']); ?>-50 rounded-lg p-3 border-l-4 border-<?php echo esc_attr($spec['color']); ?>-400">
                                    <span class="text-gray-700 font-medium"><?php echo esc_html($achievement); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="mt-16 bg-gradient-to-r from-blue-50 to-green-50 rounded-2xl p-8">
            <div class="text-center">
                <h3 class="text-2xl font-bold text-gray-800 mb-4">
                    その他の得意領域
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8">
                    <?php foreach ($other_skills as $skill_index => $skill) : ?>
                        <div class="bg-white rounded-xl p-4 text-center hover:shadow-md transition-shadow other-skill-card">
                            <div class="w-8 h-8 flex items-center justify-center mx-auto mb-2 text-blue-600">
                                <i class="<?php echo esc_attr($skill['icon']); ?> text-lg"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-700"><?php echo esc_html($skill['name']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* アニメーション用スタイル */
.specialization-card {
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.6s ease-out;
}

.specialization-card.animate {
    opacity: 1;
    transform: translateY(0);
}

.other-skill-card {
    opacity: 0;
    transform: scale(0.9);
    transition: all 0.4s ease-out;
}

.other-skill-card.animate {
    opacity: 1;
    transform: scale(1);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // スクロールアニメーション
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                if (entry.target.classList.contains('specialization-card')) {
                    setTimeout(() => {
                        entry.target.classList.add('animate');
                    }, entry.target.dataset.delay || 0);
                } else if (entry.target.classList.contains('other-skill-card')) {
                    setTimeout(() => {
                        entry.target.classList.add('animate');
                    }, entry.target.dataset.delay || 0);
                }
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1
    });

    // 専門スキルカードにディレイを設定
    const specializationCards = document.querySelectorAll('.specialization-card');
    specializationCards.forEach((card, index) => {
        card.dataset.delay = (index * 200);
        observer.observe(card);
    });

    // その他のスキルカードにディレイを設定
    const otherSkillCards = document.querySelectorAll('.other-skill-card');
    otherSkillCards.forEach((card, index) => {
        card.dataset.delay = (index * 100);
        observer.observe(card);
    });
});
</script>
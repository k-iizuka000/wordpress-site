<?php
/**
 * Template Part: Frameworks & Tools Section
 * 
 * フレームワーク・ツールセクションを表示
 */

$categories = array(
    array(
        'title' => 'フロントエンド',
        'icon' => 'ri-window-line',
        'color' => 'blue',
        'skills' => array(
            array('name' => 'React', 'level' => 90, 'description' => 'SPA開発、コンポーネント設計、状態管理'),
            array('name' => 'Vue.js', 'level' => 85, 'description' => 'リアクティブUI、コンポーネント開発'),
            array('name' => 'Next.js', 'level' => 88, 'description' => 'SSR/SSG、フルスタック開発'),
            array('name' => 'Tailwind CSS', 'level' => 92, 'description' => 'レスポンシブデザイン、UI/UX設計'),
            array('name' => 'HTML/CSS', 'level' => 95, 'description' => 'セマンティックマークアップ、レスポンシブ')
        )
    ),
    array(
        'title' => 'バックエンド',
        'icon' => 'ri-server-line',
        'color' => 'green',
        'skills' => array(
            array('name' => 'Node.js', 'level' => 87, 'description' => 'RESTful API、リアルタイム通信'),
            array('name' => 'Express.js', 'level' => 85, 'description' => 'Webサーバー構築、ミドルウェア開発'),
            array('name' => 'FastAPI', 'level' => 80, 'description' => '高速API開発、自動ドキュメント生成'),
            array('name' => 'Flask', 'level' => 75, 'description' => '軽量Webアプリケーション開発'),
            array('name' => 'Spring Boot', 'level' => 70, 'description' => 'エンタープライズアプリケーション開発')
        )
    ),
    array(
        'title' => 'データベース',
        'icon' => 'ri-database-line',
        'color' => 'purple',
        'skills' => array(
            array('name' => 'PostgreSQL', 'level' => 90, 'description' => '大規模データ管理、パフォーマンス最適化'),
            array('name' => 'MySQL', 'level' => 85, 'description' => 'Webアプリケーション、レプリケーション'),
            array('name' => 'MongoDB', 'level' => 75, 'description' => 'NoSQL、ドキュメントベースDB'),
            array('name' => 'Redis', 'level' => 80, 'description' => 'キャッシュ、セッション管理'),
            array('name' => 'Supabase', 'level' => 85, 'description' => 'BaaS、リアルタイムDB')
        )
    ),
    array(
        'title' => 'インフラ・DevOps',
        'icon' => 'ri-cloud-line',
        'color' => 'orange',
        'skills' => array(
            array('name' => 'AWS', 'level' => 82, 'description' => 'EC2、S3、RDS、Lambda'),
            array('name' => 'Docker', 'level' => 88, 'description' => 'コンテナ化、開発環境構築'),
            array('name' => 'Git', 'level' => 95, 'description' => 'バージョン管理、チーム開発'),
            array('name' => 'CI/CD', 'level' => 78, 'description' => 'GitHub Actions、自動デプロイ'),
            array('name' => 'Nginx', 'level' => 75, 'description' => 'Webサーバー、リバースプロキシ')
        )
    ),
    array(
        'title' => '自動化・データ処理',
        'icon' => 'ri-robot-line',
        'color' => 'red',
        'skills' => array(
            array('name' => 'Selenium', 'level' => 92, 'description' => 'Webブラウザ自動化、テスト自動化'),
            array('name' => 'Pandas', 'level' => 90, 'description' => 'データ分析、CSV/Excel処理'),
            array('name' => 'Beautiful Soup', 'level' => 88, 'description' => 'Webスクレイピング、データ抽出'),
            array('name' => 'Celery', 'level' => 75, 'description' => '非同期タスク処理、バッチ処理'),
            array('name' => 'Cron/Task Scheduler', 'level' => 85, 'description' => '定期実行、自動化ワークフロー')
        )
    ),
    array(
        'title' => '開発ツール',
        'icon' => 'ri-tools-line',
        'color' => 'cyan',
        'skills' => array(
            array('name' => 'VS Code', 'level' => 95, 'description' => 'エディタ、拡張機能活用'),
            array('name' => 'Postman', 'level' => 90, 'description' => 'API設計、テスト'),
            array('name' => 'Figma', 'level' => 70, 'description' => 'UI/UX設計、プロトタイピング'),
            array('name' => 'Slack/Teams', 'level' => 88, 'description' => 'チームコミュニケーション'),
            array('name' => 'Notion', 'level' => 85, 'description' => 'プロジェクト管理、ドキュメント作成')
        )
    )
);
?>

<section class="py-20 bg-gray-50">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">
                フレームワーク・ツール
            </h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                効率的な開発を実現するための豊富なフレームワーク・ツールの使用経験
            </p>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <?php foreach ($categories as $category_index => $category) : ?>
                <div class="bg-white rounded-2xl p-8 shadow-sm">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-10 h-10 flex items-center justify-center bg-<?php echo esc_attr($category['color']); ?>-100 rounded-lg">
                            <i class="<?php echo esc_attr($category['icon']); ?> text-<?php echo esc_attr($category['color']); ?>-600 text-lg"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800"><?php echo esc_html($category['title']); ?></h3>
                    </div>
                    
                    <div class="space-y-4">
                        <?php foreach ($category['skills'] as $skill_index => $skill) : ?>
                            <div class="border-l-4 border-gray-200 pl-4 py-2 hover:border-blue-400 transition-colors skill-item" 
                                 data-level="<?php echo esc_attr($skill['level']); ?>"
                                 data-color="<?php echo esc_attr($category['color']); ?>">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="font-semibold text-gray-800"><?php echo esc_html($skill['name']); ?></h4>
                                    <span class="text-<?php echo esc_attr($category['color']); ?>-600 font-bold text-sm"><?php echo esc_html($skill['level']); ?>%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                                    <div class="bg-gradient-to-r from-<?php echo esc_attr($category['color']); ?>-400 to-<?php echo esc_attr($category['color']); ?>-600 h-2 rounded-full transition-all duration-1000 skill-bar"
                                         data-width="<?php echo esc_attr($skill['level']); ?>%">
                                    </div>
                                </div>
                                <p class="text-gray-600 text-sm"><?php echo esc_html($skill['description']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<style>
/* スキルバーのアニメーション用スタイル */
.skill-bar {
    width: 0%;
}

.skill-bar.animate {
    transition: width 1.5s ease-out;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // スキルバーアニメーション
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const skillBars = entry.target.querySelectorAll('.skill-bar');
                skillBars.forEach(bar => {
                    setTimeout(() => {
                        bar.classList.add('animate');
                        bar.style.width = bar.dataset.width;
                    }, 200);
                });
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1
    });

    // 各セクションを監視
    const skillSections = document.querySelectorAll('.bg-white.rounded-2xl.p-8.shadow-sm');
    skillSections.forEach(section => {
        observer.observe(section);
    });
});
</script>
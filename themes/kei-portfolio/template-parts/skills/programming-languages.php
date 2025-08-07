<?php
/**
 * Programming Languages Section
 * 
 * Displays programming languages with skill levels and project experience
 * 
 * @package kei-portfolio
 */

// Programming languages data
$languages = array(
    array(
        'name' => 'Python',
        'level' => 95,
        'experience' => '4年',
        'description' => 'データ処理、自動化ツール開発、機械学習に特化。Pandas、NumPy、Seleniumなど豊富なライブラリ活用経験。',
        'icon' => 'ri-code-line',
        'color' => 'blue',
        'projects' => array('データ処理自動化', 'Webスクレイピング', '機械学習モデル')
    ),
    array(
        'name' => 'JavaScript',
        'level' => 90,
        'experience' => '5年',
        'description' => 'モダンなES6+構文を駆使したフロントエンド・バックエンド開発。React、Vue.js、Node.js環境での開発経験豊富。',
        'icon' => 'ri-javascript-line',
        'color' => 'yellow',
        'projects' => array('Webアプリケーション', 'API開発', 'SPA開発')
    ),
    array(
        'name' => 'TypeScript',
        'level' => 85,
        'experience' => '3年',
        'description' => '型安全な開発によるコード品質向上。大規模なReact・Node.jsプロジェクトでの開発経験。',
        'icon' => 'ri-code-s-slash-line',
        'color' => 'blue',
        'projects' => array('大規模Webアプリ', 'API設計', 'コンポーネント設計')
    ),
    array(
        'name' => 'Java',
        'level' => 75,
        'experience' => '2年',
        'description' => 'Spring Frameworkを使った企業向けシステム開発。オブジェクト指向設計とMVCアーキテクチャに精通。',
        'icon' => 'ri-cup-line',
        'color' => 'red',
        'projects' => array('企業システム', 'API開発', 'バッチ処理')
    ),
    array(
        'name' => 'SQL',
        'level' => 88,
        'experience' => '5年',
        'description' => '複雑なクエリ設計からデータベース設計まで。PostgreSQL、MySQL、SQLiteでの開発・運用経験。',
        'icon' => 'ri-database-2-line',
        'color' => 'green',
        'projects' => array('データベース設計', 'パフォーマンス最適化', 'データ分析')
    ),
    array(
        'name' => 'Go',
        'level' => 70,
        'experience' => '1年',
        'description' => '高パフォーマンスなAPIサーバー開発。並行処理とマイクロサービスアーキテクチャでの活用。',
        'icon' => 'ri-terminal-line',
        'color' => 'cyan',
        'projects' => array('APIサーバー', 'マイクロサービス', 'CLIツール')
    )
);
?>

<section class="py-20 bg-white">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">
                プログラミング言語
            </h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                業務で実際に使用している言語と、それぞれの習熟度・経験年数をご紹介します
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <?php foreach ($languages as $index => $lang) : ?>
            <div class="bg-gray-50 rounded-2xl p-8 hover:shadow-lg transition-all">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 flex items-center justify-center bg-<?php echo esc_attr($lang['color']); ?>-100 rounded-lg">
                            <i class="<?php echo esc_attr($lang['icon']); ?> text-<?php echo esc_attr($lang['color']); ?>-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800"><?php echo esc_html($lang['name']); ?></h3>
                            <span class="text-sm text-gray-500">経験年数: <?php echo esc_html($lang['experience']); ?></span>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold text-blue-600"><?php echo esc_html($lang['level']); ?>%</div>
                        <div class="text-xs text-gray-500">習熟度</div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div 
                            class="bg-gradient-to-r from-<?php echo esc_attr($lang['color']); ?>-500 to-<?php echo esc_attr($lang['color']); ?>-600 h-3 rounded-full transition-all duration-1000"
                            style="width: <?php echo absint($lang['level']); ?>%;"
                        ></div>
                    </div>
                </div>
                
                <p class="text-gray-600 leading-relaxed mb-4"><?php echo esc_html($lang['description']); ?></p>
                
                <div>
                    <h4 class="font-semibold text-gray-800 mb-2">主な活用プロジェクト</h4>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($lang['projects'] as $project_index => $project) : ?>
                        <span class="bg-<?php echo esc_attr($lang['color']); ?>-100 text-<?php echo esc_attr($lang['color']); ?>-700 px-3 py-1 rounded-full text-sm">
                            <?php echo esc_html($project); ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
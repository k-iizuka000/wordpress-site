<?php
/**
 * フロントページテンプレート
 * React page.tsx を WordPress PHP に変換
 *
 * @package Kei_Portfolio
 */

get_header(); 

// Portfolio_Dataクラスのインスタンスを取得
$portfolio_data = Portfolio_Data::get_instance();
$summary_data = $portfolio_data->get_summary_data();
$latest_projects = $portfolio_data->get_latest_projects();
$in_progress_projects = $portfolio_data->get_in_progress_projects();
$skills_data = $portfolio_data->get_skills_data();
// スキル統計（熟練度計算済み）
$skill_statistics = method_exists($portfolio_data, 'get_skills_statistics') ? $portfolio_data->get_skills_statistics() : array();

// エラーハンドリング
$has_summary = !is_wp_error($summary_data);
$has_projects = !is_wp_error($latest_projects);
$has_in_progress = !is_wp_error($in_progress_projects);
$has_skills = !is_wp_error($skills_data);
$has_skill_stats = is_array($skill_statistics) && !empty($skill_statistics);
?>

<main class="min-h-screen">
    <!-- Hero Section -->
    <section 
        class="relative h-screen flex items-center justify-center bg-gradient-to-r from-blue-50 to-green-50 overflow-hidden hero-section"
    >
        <div class="absolute inset-0 bg-black/30"></div>
        
        <div class="relative z-10 w-full max-w-6xl mx-auto px-4">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                <div class="text-white lg:pr-8">
                    <h1 class="text-4xl md:text-6xl font-bold leading-tight mb-6">
                        問題解決と品質向上を得意とするフルスタックエンジニア
                    </h1>
                    <p class="text-xl md:text-2xl mb-8 text-gray-200 leading-relaxed">
                        課題を整理し、最適なソリューションを提案・実装。品質とスピードの両立を追求するエンジニアです。
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="<?php echo esc_url(get_post_type_archive_link('project')); ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-4 rounded-full text-lg font-semibold transition-all transform hover:scale-105 text-center whitespace-nowrap">
                            案件一覧を見る
                        </a>
                        <a href="<?php echo esc_url(home_url('/contact')); ?>" class="border-2 border-white text-white hover:bg-white hover:text-gray-800 px-8 py-4 rounded-full text-lg font-semibold transition-all text-center whitespace-nowrap">
                            お問い合わせ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Preview Section -->
    <section class="py-20 bg-white">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">
                    明るく前向きなエンジニア
                </h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    AIや最新技術の活用を得意とし、ロードバイクで培った持続力と集中力を活かして、
                    お客様の課題解決に取り組んでいます。
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="bg-blue-50 rounded-2xl p-8 text-center hover:shadow-lg transition-shadow">
                    <div class="w-16 h-16 flex items-center justify-center mx-auto mb-4 bg-blue-600 rounded-full">
                        <i class="ri-settings-3-line text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">AI・最新技術キャッチアップ</h3>
                    <p class="text-gray-600">
                        AIや最新技術の動向を常に追いかけ、得られた知見を活かして開発や課題解決に取り組みます。
                    </p>
                </div>
                
                <div class="bg-green-50 rounded-2xl p-8 text-center hover:shadow-lg transition-shadow">
                    <div class="w-16 h-16 flex items-center justify-center mx-auto mb-4 bg-green-600 rounded-full">
                        <i class="ri-bike-line text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">ロードバイク愛好家</h3>
                    <p class="text-gray-600">
                        ロードバイクで培った持続力と集中力を、開発業務にも活かしています。
                    </p>
                </div>
                
                <div class="bg-purple-50 rounded-2xl p-8 text-center hover:shadow-lg transition-shadow md:col-span-2 lg:col-span-1">
                    <div class="w-16 h-16 flex items-center justify-center mx-auto mb-4 bg-purple-600 rounded-full">
                        <i class="ri-lightbulb-line text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-3">前向きな姿勢</h3>
                    <p class="text-gray-600">
                        どんな課題にも明るく前向きに取り組み、最適な解決策を提案します。
                    </p>
                </div>
            </div>
            
            <div class="text-center mt-12">
                <a href="<?php echo esc_url(home_url('/about')); ?>" class="inline-flex items-center text-blue-600 hover:text-blue-700 font-semibold text-lg">
                    詳しいプロフィールを見る
                    <i class="ri-arrow-right-line ml-2"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Skills Preview Section -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">
                    主要スキル
                </h2>
                <p class="text-lg text-gray-600">
                    多様な技術を駆使して、最適なソリューションをお届けします
                </p>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-12">
                <?php
                // portfolio.jsonの統計から熟練度順に表示
                if ($has_skill_stats) {
                    // 配列に詰め替え＆レベル降順でソート
                    $stats = array_values($skill_statistics);
                    usort($stats, function($a, $b) {
                        $la = isset($a['level']) ? (int)$a['level'] : 0;
                        $lb = isset($b['level']) ? (int)$b['level'] : 0;
                        return $lb <=> $la;
                    });

                    // 上位8件を表示
                    $top = array_slice($stats, 0, 8);

                    foreach ($top as $item) :
                        if (!isset($item['display_name'])) continue;
                        $display = $item['display_name'];
                        $category = isset($item['category']) ? $item['category'] : 'other';
                        $ui = function_exists('kei_portfolio_get_skill_ui') ? kei_portfolio_get_skill_ui($display, $category) : array('icon' => 'ri-code-line', 'color' => 'bg-gray-500');
                        $level = isset($item['level']) ? (int)$item['level'] : 0;
                        ?>
                        <div class="bg-white rounded-xl p-6 text-center hover:shadow-md transition-shadow">
                            <div class="w-12 h-12 flex items-center justify-center mx-auto mb-3 <?php echo esc_attr($ui['color']); ?> rounded-lg">
                                <i class="<?php echo esc_attr($ui['icon']); ?> text-white text-xl"></i>
                            </div>
                            <h4 class="font-semibold text-gray-800"><?php echo esc_html($display); ?></h4>
                        </div>
                    <?php endforeach;
                } else {
                    // フォールバック: portfolio.jsonが利用できない場合のデフォルトスキル
                    $default_skills = array(
                        array('name' => 'Java', 'icon' => 'ri-code-line', 'color' => 'red'),
                        array('name' => 'JavaScript', 'icon' => 'ri-javascript-line', 'color' => 'yellow'),
                        array('name' => 'Python', 'icon' => 'ri-code-line', 'color' => 'blue'),
                        array('name' => 'React', 'icon' => 'ri-reactjs-line', 'color' => 'cyan')
                    );
                    
                    foreach ($default_skills as $skill) : ?>
                        <div class="bg-white rounded-xl p-6 text-center hover:shadow-md transition-shadow">
                            <div class="w-12 h-12 flex items-center justify-center mx-auto mb-3 bg-<?php echo esc_attr($skill['color']); ?>-100 rounded-lg">
                                <i class="<?php echo esc_attr($skill['icon']); ?> text-<?php echo esc_attr($skill['color']); ?>-600 text-xl"></i>
                            </div>
                            <h4 class="font-semibold text-gray-800"><?php echo esc_html($skill['name']); ?></h4>
                        </div>
                    <?php endforeach;
                }
                ?>
            </div>
            
            <div class="text-center">
                <a href="<?php echo esc_url(home_url('/skills')); ?>" class="inline-flex items-center text-blue-600 hover:text-blue-700 font-semibold text-lg">
                    全スキル一覧を見る
                    <i class="ri-arrow-right-line ml-2"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Latest Projects Section -->
    <section class="py-20 bg-white">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">
                    最新の案件一覧
                </h2>
                <p class="text-lg text-gray-600">
                    これまでに開発した代表的なプロジェクトをご紹介します
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
                <?php
                if ($has_projects && !empty($latest_projects)) {
                    foreach ($latest_projects as $project) : 
                        if (!isset($project['title']) || !isset($project['description'])) {
                            continue;
                        }
                        
                        // 技術スタックの抽出
                        $tech_stack = '';
                        if (isset($project['technologies']) && is_array($project['technologies'])) {
                            $tech_names = array();
                            foreach (array_slice($project['technologies'], 0, 3) as $tech) {
                                if (is_array($tech) && isset($tech['name'])) {
                                    $tech_names[] = $tech['name'];
                                } elseif (is_string($tech)) {
                                    $tech_names[] = $tech;
                                }
                            }
                            $tech_stack = implode(', ', $tech_names);
                        }
                        
                        // プロジェクト期間の表示
                        $period_display = '';
                        if (isset($project['period']['start']) && isset($project['period']['end'])) {
                            $period_display = esc_html($project['period']['start']) . ' - ' . esc_html($project['period']['end']);
                        }
                        ?>
                        <div class="bg-gray-50 rounded-2xl overflow-hidden hover:shadow-lg transition-shadow">
                            <div class="h-48 bg-gradient-to-br from-blue-100 to-green-100 flex items-center justify-center">
                                <div class="text-center p-4">
                                    <i class="ri-code-s-slash-line text-4xl text-blue-600 mb-2"></i>
                                    <?php if ($period_display) : ?>
                                        <p class="text-sm text-gray-600"><?php echo $period_display; ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="p-6">
                                <h3 class="text-xl font-semibold text-gray-800 mb-2"><?php echo esc_html($project['title']); ?></h3>
                                <?php 
                                // 説明文の最初の100文字を抽出
                                if (function_exists('kei_portfolio_format_description')) {
                                    $description = kei_portfolio_format_description($project['description'], 'text');
                                } else {
                                    $description = is_array($project['description']) ? '' : strip_tags($project['description']);
                                }
                                $short_description = mb_strlen($description) > 100 ? mb_substr($description, 0, 100) . '...' : $description;
                                ?>
                                <p class="text-gray-600 mb-4"><?php echo esc_html($short_description); ?></p>
                                <div class="flex items-center justify-between">
                                    <?php if ($tech_stack) : ?>
                                        <span class="text-sm text-blue-600 bg-blue-100 px-3 py-1 rounded-full">
                                            <?php echo esc_html($tech_stack); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (isset($project['role'])) : ?>
                                        <span class="text-xs text-gray-500"><?php echo esc_html($project['role']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach;
                } else {
                    // フォールバック用のデフォルトプロジェクト
                    $default_projects = array(
                        array(
                            'title' => '通信会社向けWebサイト開発',
                            'description' => 'Spring Boot移行後の技術文書整備とプロセス改善',
                            'tech' => 'Java, Spring Boot'
                        ),
                        array(
                            'title' => '料理レシピ投稿サイト開発',
                            'description' => 'ユーザー・管理者機能の改修と品質向上',
                            'tech' => 'Python, PHP, Vue.js'
                        ),
                        array(
                            'title' => 'PSNサーバーサイド開発',
                            'description' => 'ログイン管理機能とテスト品質向上',
                            'tech' => 'Java, AWS, JUnit'
                        )
                    );
                    
                    foreach ($default_projects as $project) : ?>
                        <div class="bg-gray-50 rounded-2xl overflow-hidden hover:shadow-lg transition-shadow">
                            <div class="h-48 bg-gradient-to-br from-blue-100 to-green-100 flex items-center justify-center">
                                <i class="ri-code-s-slash-line text-4xl text-blue-600"></i>
                            </div>
                            <div class="p-6">
                                <h3 class="text-xl font-semibold text-gray-800 mb-2"><?php echo esc_html($project['title']); ?></h3>
                                <p class="text-gray-600 mb-4"><?php echo esc_html($project['description']); ?></p>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-blue-600 bg-blue-100 px-3 py-1 rounded-full">
                                        <?php echo esc_html($project['tech']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach;
                }
                ?>
            </div>
            
            <div class="text-center">
                <a href="<?php echo esc_url(get_post_type_archive_link('project')); ?>" class="inline-flex items-center text-blue-600 hover:text-blue-700 font-semibold text-lg">
                    全ての案件一覧を見る
                    <i class="ri-arrow-right-line ml-2"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- In Progress Projects Section -->
    <?php if ($has_in_progress && !empty($in_progress_projects)) : ?>
    <section class="py-20 bg-gray-50">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">
                    個人受注プロジェクト
                </h2>
                <p class="text-lg text-gray-600">
                    過去受注した案件をご紹介します
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php 
                $project_index = 0;
                foreach ($in_progress_projects as $project) : 
                    if (!isset($project['title']) || !isset($project['description'])) {
                        continue;
                    }
                    
                    $project_id = 'home-project-' . $project_index++;
                    
                    // 技術スタックの抽出
                    $tech_stack = '';
                    if (isset($project['technologies']) && is_array($project['technologies'])) {
                        $tech_stack = implode(', ', array_slice($project['technologies'], 0, 3));
                    }
                    ?>
                    <div class="bg-white rounded-2xl overflow-hidden hover:shadow-lg transition-shadow border-l-4 border-green-500">
                        <div class="h-48 bg-gradient-to-br from-green-100 to-blue-100 flex items-center justify-center">
                            <div class="text-center p-4">
                                <i class="ri-settings-3-line text-4xl text-green-600 mb-2 animate-spin-slow"></i>
                                
                            </div>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-semibold text-gray-800 mb-2"><?php echo esc_html($project['title']); ?></h3>
                            <div id="<?php echo esc_attr($project_id); ?>" class="text-gray-600 mb-4 js-collapsible clamped-2">
                                <?php 
                                if (function_exists('kei_portfolio_format_description')) {
                                    echo wp_kses_post(kei_portfolio_format_description($project['description'], 'html'));
                                } else {
                                    echo esc_html(is_array($project['description']) ? '' : $project['description']);
                                } 
                                ?>
                            </div>
                            <button type="button"
                                class="text-blue-600 text-sm hover:underline js-toggle mb-4"
                                data-target="<?php echo esc_attr($project_id); ?>"
                                aria-controls="<?php echo esc_attr($project_id); ?>"
                                aria-expanded="false"
                                hidden
                            >全て表示する</button>
                            <div class="flex items-center justify-between">
                                <?php if ($tech_stack) : ?>
                                    <span class="text-sm text-green-600 bg-green-100 px-3 py-1 rounded-full">
                                        <?php echo esc_html($tech_stack); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (isset($project['url']) && !empty($project['url'])) : ?>
                                    <a href="<?php echo esc_url($project['url']); ?>" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-gray-600">
                                        <i class="ri-external-link-line"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- CTA Section -->
    <!-- <section class="py-20 bg-gradient-to-r from-blue-600 to-green-600">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">
                一緒にプロジェクトを始めませんか？
            </h2>
            <p class="text-xl text-blue-100 mb-8 leading-relaxed">
                自動化によって業務効率を改善し、<br />
                新たな価値創造の時間を生み出しましょう。
            </p>
            <a href="<?php echo esc_url(home_url('/contact')); ?>" class="bg-white text-blue-600 hover:bg-gray-100 px-8 py-4 rounded-full text-lg font-semibold transition-all transform hover:scale-105 inline-block">
                無料相談を申し込む
            </a>
        </div>
    </section> -->
</main>

<style>
/* アコーディオン用スタイル */
.clamped-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<script>
// 個別プロジェクトのアコーディオン
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.js-collapsible').forEach(function(el) {
        var container = el.parentElement;
        var toggle = container ? container.querySelector('.js-toggle') : null;
        if (!toggle) return;

        // 初期判定：2行にクランプした状態で溢れているか
        var needsToggle = el.scrollHeight > el.clientHeight + 1;
        toggle.hidden = !needsToggle;

        toggle.addEventListener('click', function() {
            var expanded = this.getAttribute('aria-expanded') === 'true';
            if (expanded) {
                el.classList.add('clamped-2');
                this.setAttribute('aria-expanded', 'false');
                this.textContent = '全て表示する';
            } else {
                el.classList.remove('clamped-2');
                this.setAttribute('aria-expanded', 'true');
                this.textContent = '閉じる';
            }
        });
    });
});
</script>

<?php get_footer(); ?>

<?php
/**
 * Skill UI mapping helper
 *
 * スキル名とカテゴリに基づいて、表示用のアイコン/色/カテゴリラベルを返す。
 * テンプレートにハードコードを置かないための共通化ヘルパー。
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('kei_portfolio_get_skill_ui')) {
    /**
     * スキルのUI情報を取得
     * @param string $name 正規化済みスキル名
     * @param string $category frontend|backend|other
     * @return array { icon, color, category_label }
     */
    function kei_portfolio_get_skill_ui($name, $category = 'other') {
        // 共通デフォルト
        $default = array(
            'icon' => 'ri-code-line',
            'color' => 'bg-gray-500',
            'category_label' => ucfirst($category),
        );

        // カテゴリ別マッピング
        $frontend = array(
            'JavaScript'  => array('icon' => 'ri-javascript-line', 'color' => 'bg-yellow-500'),
            'Vue.js'      => array('icon' => 'ri-vuejs-line',        'color' => 'bg-green-500'),
            'React'       => array('icon' => 'ri-reactjs-line',      'color' => 'bg-blue-500'),
            'HTML5'       => array('icon' => 'ri-html5-line',        'color' => 'bg-orange-500'),
            'CSS3'        => array('icon' => 'ri-css3-line',         'color' => 'bg-blue-600'),
            'JSP'         => array('icon' => 'ri-file-code-line',    'color' => 'bg-red-500'),
            'FullCalendar'=> array('icon' => 'ri-calendar-line',     'color' => 'bg-purple-500'),
        );

        $backend = array(
            'Java'        => array('icon' => 'ri-file-code-line',    'color' => 'bg-red-500'),
            'Spring Boot' => array('icon' => 'ri-leaf-line',         'color' => 'bg-green-500'),
            'Python'      => array('icon' => 'ri-file-code-line',    'color' => 'bg-blue-600'),
            'SQL'         => array('icon' => 'ri-database-2-line',   'color' => 'bg-gray-600'),
            'Node.js'     => array('icon' => 'ri-nodejs-line',       'color' => 'bg-green-600'),
            'COBOL'       => array('icon' => 'ri-code-line',         'color' => 'bg-blue-800'),
            'Ruby'        => array('icon' => 'ri-code-line',         'color' => 'bg-red-600'),
            'PHP'         => array('icon' => 'ri-code-line',         'color' => 'bg-purple-600'),
            'Maven'       => array('icon' => 'ri-hammer-line',       'color' => 'bg-blue-600'),
            'Gradle'      => array('icon' => 'ri-settings-3-line',   'color' => 'bg-green-600'),
            'JUnit'       => array('icon' => 'ri-test-tube-line',    'color' => 'bg-red-600'),
        );

        $other = array(
            'Git'            => array('icon' => 'ri-git-branch-line',     'color' => 'bg-red-500',    'category_label' => 'Version Control'),
            'SVN'            => array('icon' => 'ri-git-repository-line', 'color' => 'bg-gray-600',   'category_label' => 'Version Control'),
            'AWS'            => array('icon' => 'ri-cloud-line',          'color' => 'bg-orange-500', 'category_label' => 'Cloud Platform'),
            'Docker'         => array('icon' => 'ri-ship-line',           'color' => 'bg-blue-500',   'category_label' => 'Containerization'),
            'e2e'            => array('icon' => 'ri-checkbox-multiple-line','color' => 'bg-purple-600','category_label' => 'Testing'),
            'Astro'          => array('icon' => 'ri-rocket-2-line',       'color' => 'bg-indigo-600', 'category_label' => 'Frontend Tool'),
            'AI/LLM'         => array('icon' => 'ri-brain-line',          'color' => 'bg-pink-600',   'category_label' => 'AI'),
            'pay.jp API'     => array('icon' => 'ri-secure-payment-line', 'color' => 'bg-teal-600',   'category_label' => 'Payment'),
            'Note API'       => array('icon' => 'ri-links-line',          'color' => 'bg-emerald-600','category_label' => 'API'),
            'ワークフロー自動化' => array('icon' => 'ri-flow-chart',          'color' => 'bg-sky-600',    'category_label' => 'Automation'),
        );

        $tables = array(
            'frontend' => $frontend,
            'backend'  => $backend,
            'other'    => $other,
        );

        $cat = isset($tables[$category]) ? $category : 'other';
        $table = $tables[$cat];

        if (isset($table[$name])) {
            $mapped = $table[$name];
            // カテゴリラベルのデフォルトを補完
            if (!isset($mapped['category_label'])) {
                $mapped['category_label'] = ucfirst($cat);
            }
            return $mapped;
        }

        // 未知のスキルはカテゴリに応じた穏当なデフォルト色
        $cat_colors = array(
            'frontend' => 'bg-gray-500',
            'backend'  => 'bg-gray-600',
            'other'    => 'bg-gray-500',
        );
        $default['color'] = $cat_colors[$cat];
        return $default;
    }
}


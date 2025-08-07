<?php
/**
 * シンプルテストランナー
 * PHPUnitがない環境でも基本的なテストを実行
 */

require_once __DIR__ . '/simple-bootstrap.php';

echo "=== WordPress テーマ kei-portfolio グループ4 テスト ===\n";
echo "実行時刻: " . date('Y-m-d H:i:s') . "\n\n";

// テスト結果格納
$test_results = [];

// 1. ファイル存在確認テスト
echo "=== ファイル存在確認テスト ===\n";

$template_files = [
    'page-templates/page-skills.php',
    'page-templates/page-contact.php', 
    'single-project.php'
];

$template_parts = [
    'template-parts/skills/skills-hero.php',
    'template-parts/skills/programming-languages.php',
    'template-parts/contact/hero.php',
    'template-parts/contact/contact-form.php',
    'template-parts/contact/contact-info.php'
];

foreach ($template_files as $file) {
    $exists = file_exists(THEME_DIR . '/' . $file);
    echo sprintf("%-40s: %s\n", $file, $exists ? '✓ 存在' : '✗ 不存在');
    $test_results["file_exists_{$file}"] = $exists;
}

echo "\n";

foreach ($template_parts as $part) {
    $exists = file_exists(THEME_DIR . '/' . $part);
    echo sprintf("%-50s: %s\n", $part, $exists ? '✓ 存在' : '✗ 不存在');
    $test_results["part_exists_{$part}"] = $exists;
}

// 2. 構文チェックテスト
echo "\n=== 構文チェックテスト ===\n";

$all_files = array_merge($template_files, $template_parts);
foreach ($all_files as $file) {
    $file_path = THEME_DIR . '/' . $file;
    if (!file_exists($file_path)) {
        echo sprintf("%-40s: - スキップ（ファイル不存在）\n", $file);
        continue;
    }
    
    $output = [];
    $return_var = 0;
    exec("php -l '{$file_path}' 2>&1", $output, $return_var);
    
    $is_valid = $return_var === 0;
    echo sprintf("%-40s: %s\n", $file, $is_valid ? '✓ 構文OK' : '✗ 構文エラー');
    if (!$is_valid) {
        echo "  エラー: " . implode("\n  ", $output) . "\n";
    }
    
    $test_results["syntax_{$file}"] = $is_valid;
}

// 3. テンプレート構造チェック
echo "\n=== テンプレート構造チェック ===\n";

// Skills テンプレートの構造チェック
$skills_template = THEME_DIR . '/page-templates/page-skills.php';
if (file_exists($skills_template)) {
    $content = file_get_contents($skills_template);
    
    $checks = [
        'template_name' => strpos($content, 'Template Name: Skills') !== false,
        'get_header' => strpos($content, 'get_header()') !== false,
        'get_footer' => strpos($content, 'get_footer()') !== false,
        'skills_hero' => strpos($content, 'skills/skills-hero') !== false,
        'programming_languages' => strpos($content, 'skills/programming-languages') !== false
    ];
    
    echo "Skills テンプレート:\n";
    foreach ($checks as $check => $result) {
        echo sprintf("  %-20s: %s\n", $check, $result ? '✓ OK' : '✗ NG');
        $test_results["skills_{$check}"] = $result;
    }
    echo "\n";
}

// Contact テンプレートの構造チェック
$contact_template = THEME_DIR . '/page-templates/page-contact.php';
if (file_exists($contact_template)) {
    $content = file_get_contents($contact_template);
    
    $checks = [
        'template_name' => strpos($content, 'Template Name: Contact') !== false,
        'get_header' => strpos($content, 'get_header()') !== false,
        'get_footer' => strpos($content, 'get_footer()') !== false,
        'contact_hero' => strpos($content, 'contact/hero') !== false,
        'contact_form' => strpos($content, 'contact/contact-form') !== false,
        'contact_info' => strpos($content, 'contact/contact-info') !== false
    ];
    
    echo "Contact テンプレート:\n";
    foreach ($checks as $check => $result) {
        echo sprintf("  %-20s: %s\n", $check, $result ? '✓ OK' : '✗ NG');
        $test_results["contact_{$check}"] = $result;
    }
    echo "\n";
}

// Single Project テンプレートの構造チェック
$project_template = THEME_DIR . '/single-project.php';
if (file_exists($project_template)) {
    $content = file_get_contents($project_template);
    
    $checks = [
        'get_header' => strpos($content, 'get_header()') !== false,
        'get_footer' => strpos($content, 'get_footer()') !== false,
        'have_posts' => strpos($content, 'have_posts()') !== false,
        'the_post' => strpos($content, 'the_post()') !== false,
        'the_title' => strpos($content, 'the_title()') !== false,
        'the_content' => strpos($content, 'the_content()') !== false,
        'project_header' => strpos($content, 'project-header') !== false,
        'get_field' => strpos($content, 'get_field(') !== false,
        'esc_html' => strpos($content, 'esc_html(') !== false,
        'wp_kses_post' => strpos($content, 'wp_kses_post(') !== false
    ];
    
    echo "Single Project テンプレート:\n";
    foreach ($checks as $check => $result) {
        echo sprintf("  %-20s: %s\n", $check, $result ? '✓ OK' : '✗ NG');
        $test_results["project_{$check}"] = $result;
    }
    echo "\n";
}

// 4. セキュリティチェック
echo "=== セキュリティチェック ===\n";

$security_files = [
    'page-templates/page-contact.php',
    'template-parts/contact/contact-form.php',
    'single-project.php'
];

foreach ($security_files as $file) {
    $file_path = THEME_DIR . '/' . $file;
    if (!file_exists($file_path)) {
        echo sprintf("%-40s: - スキップ（ファイル不存在）\n", $file);
        continue;
    }
    
    $content = file_get_contents($file_path);
    
    // エスケープ関数の使用確認
    $escape_functions = ['esc_html', 'esc_attr', 'esc_url', 'wp_kses_post'];
    $has_output = strpos($content, 'echo') !== false || strpos($content, 'print') !== false;
    $has_escape = false;
    $used_escapes = [];
    
    foreach ($escape_functions as $func) {
        if (strpos($content, $func . '(') !== false) {
            $has_escape = true;
            $used_escapes[] = $func;
        }
    }
    
    echo sprintf("%-40s:\n", $file);
    echo sprintf("  出力あり: %s\n", $has_output ? 'Yes' : 'No');
    echo sprintf("  エスケープあり: %s\n", $has_escape ? 'Yes (' . implode(', ', $used_escapes) . ')' : 'No');
    
    if ($has_output) {
        echo sprintf("  セキュリティ: %s\n", $has_escape ? '✓ OK' : '✗ 要改善');
        $test_results["security_{$file}"] = $has_escape;
    } else {
        echo "  セキュリティ: - 出力なし\n";
        $test_results["security_{$file}"] = true;
    }
    echo "\n";
}

// 結果サマリー
echo "=== テスト結果サマリー ===\n";

$total_tests = count($test_results);
$passed_tests = count(array_filter($test_results));
$failed_tests = $total_tests - $passed_tests;

echo "総テスト数: {$total_tests}\n";
echo "成功: {$passed_tests}\n";
echo "失敗: {$failed_tests}\n";

if ($failed_tests > 0) {
    echo "\n失敗したテスト:\n";
    foreach ($test_results as $test => $result) {
        if (!$result) {
            echo "  - {$test}\n";
        }
    }
}

$success_rate = ($passed_tests / $total_tests) * 100;
echo sprintf("\n成功率: %.1f%%\n", $success_rate);

if ($success_rate >= 90) {
    echo "\n🎉 優秀！ほぼ全てのテストが成功しています。\n";
} elseif ($success_rate >= 75) {
    echo "\n✓ 良好！多くのテストが成功しています。\n";
} elseif ($success_rate >= 50) {
    echo "\n⚠ 要改善！いくつかの問題があります。\n";
} else {
    echo "\n❌ 要修正！多くの問題があります。\n";
}

echo "\n実行完了: " . date('Y-m-d H:i:s') . "\n";

// 終了コード
exit($failed_tests > 0 ? 1 : 0);
<?php
/**
 * レスポンシブデザインとブラウザ互換性チェックスクリプト
 * CSSのメディアクエリとHTMLの構造を分析します
 */

echo "=== レスポンシブデザイン・ブラウザ互換性チェック ===\n";
echo "実行日時: " . date('Y-m-d H:i:s') . "\n\n";

$base_url = 'http://localhost:8090';
$test_pages = [
    'メインページ' => '/',
    'アバウト' => '/about/',
    'スキル' => '/skills/', 
    'コンタクト' => '/contact/',
    'ポートフォリオ' => '/portfolio/'
];

$compatibility_results = [];

foreach ($test_pages as $name => $path) {
    $url = $base_url . $path;
    echo "チェック中: {$name} ({$url})\n";
    
    // ページのHTMLを取得
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; ResponsiveCheck/1.0)');
    
    $html = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200) {
        echo "  エラー: HTTP {$http_code}\n\n";
        continue;
    }
    
    $result = [
        'page' => $name,
        'url' => $url,
        'checks' => []
    ];
    
    // 1. viewportメタタグの確認
    $has_viewport = preg_match('/<meta[^>]*name=["\']viewport["\'][^>]*>/i', $html);
    $result['checks']['viewport_meta'] = [
        'status' => $has_viewport ? 'OK' : 'NG',
        'description' => 'viewportメタタグの存在確認'
    ];
    echo "  viewport設定: " . ($has_viewport ? 'OK' : 'NG') . "\n";
    
    // 2. CSS読み込みの確認
    preg_match_all('/<link[^>]*stylesheet[^>]*>/i', $html, $css_links);
    $css_count = count($css_links[0]);
    $result['checks']['css_loading'] = [
        'status' => $css_count > 0 ? 'OK' : 'NG',
        'count' => $css_count,
        'description' => 'CSSファイルの読み込み確認'
    ];
    echo "  CSS読み込み: {$css_count}件 " . ($css_count > 0 ? 'OK' : 'NG') . "\n";
    
    // 3. JavaScript読み込みの確認
    preg_match_all('/<script[^>]*src=[^>]*>/i', $html, $js_links);
    $js_count = count($js_links[0]);
    $result['checks']['js_loading'] = [
        'status' => $js_count > 0 ? 'OK' : 'NG',
        'count' => $js_count,
        'description' => 'JavaScriptファイルの読み込み確認'
    ];
    echo "  JavaScript読み込み: {$js_count}件 " . ($js_count > 0 ? 'OK' : 'NG') . "\n";
    
    // 4. 画像のalt属性確認
    preg_match_all('/<img[^>]*>/i', $html, $images);
    $images_with_alt = 0;
    $total_images = count($images[0]);
    foreach ($images[0] as $img) {
        if (strpos($img, 'alt=') !== false) {
            $images_with_alt++;
        }
    }
    $result['checks']['image_alt'] = [
        'status' => $total_images == 0 ? 'N/A' : ($images_with_alt == $total_images ? 'OK' : 'WARNING'),
        'with_alt' => $images_with_alt,
        'total' => $total_images,
        'description' => '画像のalt属性設定確認'
    ];
    echo "  画像alt属性: {$images_with_alt}/{$total_images} " . 
         ($total_images == 0 ? 'N/A' : ($images_with_alt == $total_images ? 'OK' : 'WARNING')) . "\n";
    
    // 5. セマンティックHTML要素の使用確認
    $semantic_elements = ['header', 'nav', 'main', 'article', 'section', 'aside', 'footer'];
    $found_semantic = [];
    foreach ($semantic_elements as $element) {
        if (preg_match("/<{$element}[^>]*>/i", $html)) {
            $found_semantic[] = $element;
        }
    }
    $result['checks']['semantic_html'] = [
        'status' => count($found_semantic) >= 3 ? 'OK' : 'WARNING',
        'elements' => $found_semantic,
        'count' => count($found_semantic),
        'description' => 'セマンティックHTML要素の使用確認'
    ];
    echo "  セマンティックHTML: " . count($found_semantic) . "要素使用 " . 
         (count($found_semantic) >= 3 ? 'OK' : 'WARNING') . " (" . implode(', ', $found_semantic) . ")\n";
    
    // 6. フォーム要素のラベル確認
    preg_match_all('/<input[^>]*>/i', $html, $inputs);
    preg_match_all('/<label[^>]*>/i', $html, $labels);
    $input_count = count($inputs[0]);
    $label_count = count($labels[0]);
    $result['checks']['form_labels'] = [
        'status' => $input_count == 0 ? 'N/A' : ($label_count >= $input_count ? 'OK' : 'WARNING'),
        'inputs' => $input_count,
        'labels' => $label_count,
        'description' => 'フォーム要素のラベル設定確認'
    ];
    echo "  フォームラベル: {$label_count}ラベル/{$input_count}入力 " . 
         ($input_count == 0 ? 'N/A' : ($label_count >= $input_count ? 'OK' : 'WARNING')) . "\n";
    
    // 7. HTMLの構文確認（基本的なチェック）
    $html_errors = [];
    if (!preg_match('/<html[^>]*>/i', $html)) $html_errors[] = 'htmlタグなし';
    if (!preg_match('/<head[^>]*>/i', $html)) $html_errors[] = 'headタグなし';
    if (!preg_match('/<body[^>]*>/i', $html)) $html_errors[] = 'bodyタグなし';
    if (!preg_match('/<title[^>]*>/i', $html)) $html_errors[] = 'titleタグなし';
    
    $result['checks']['html_structure'] = [
        'status' => count($html_errors) == 0 ? 'OK' : 'NG',
        'errors' => $html_errors,
        'description' => 'HTML基本構造の確認'
    ];
    echo "  HTML構造: " . (count($html_errors) == 0 ? 'OK' : 'NG (' . implode(', ', $html_errors) . ')') . "\n";
    
    $compatibility_results[] = $result;
    echo "\n";
    
    // サーバー負荷軽減のため少し待機
    usleep(500000);
}

// CSSファイルの内容もチェック（メディアクエリなど）
echo "=== CSS詳細チェック ===\n";

// テーマのCSSファイルを確認
$theme_css_path = '/Users/kei/work/wordpress-site/themes/kei-portfolio/style.css';
if (file_exists($theme_css_path)) {
    echo "テーマCSS分析: style.css\n";
    $css_content = file_get_contents($theme_css_path);
    
    // メディアクエリの確認
    $media_queries = [];
    if (preg_match_all('/@media[^{]*\{[^}]*\}/s', $css_content, $matches)) {
        foreach ($matches[0] as $mq) {
            if (preg_match('/@media[^{]*\(([^)]+)\)/', $mq, $condition)) {
                $media_queries[] = trim($condition[1]);
            }
        }
    }
    
    echo "  メディアクエリ: " . count($media_queries) . "件発見\n";
    foreach ($media_queries as $mq) {
        echo "    - {$mq}\n";
    }
    
    // レスポンシブデザインキーワードの確認
    $responsive_keywords = ['flex', 'grid', 'max-width', 'min-width', 'rem', 'em', '%'];
    $found_keywords = [];
    foreach ($responsive_keywords as $keyword) {
        if (stripos($css_content, $keyword) !== false) {
            $found_keywords[] = $keyword;
        }
    }
    echo "  レスポンシブキーワード: " . implode(', ', $found_keywords) . "\n";
} else {
    echo "テーマCSSファイルが見つかりません: {$theme_css_path}\n";
}

// 結果をJSONで保存
$final_results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'page_results' => $compatibility_results,
    'css_analysis' => [
        'media_queries' => $media_queries ?? [],
        'responsive_keywords' => $found_keywords ?? []
    ]
];

file_put_contents('/Users/kei/work/wordpress-site/responsive-compatibility-results.json', 
                 json_encode($final_results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "\n=== 総合結果 ===\n";
$total_checks = 0;
$passed_checks = 0;

foreach ($compatibility_results as $page_result) {
    foreach ($page_result['checks'] as $check) {
        $total_checks++;
        if ($check['status'] === 'OK') {
            $passed_checks++;
        }
    }
}

echo "チェック項目: {$passed_checks}/{$total_checks} 通過\n";
echo "詳細結果は responsive-compatibility-results.json に保存されました。\n";
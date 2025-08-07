<?php
/**
 * 全ページアクセステストスクリプト
 * Docker環境内のWordPressページを確認します
 */

// テスト対象URL
$base_url = 'http://localhost:8090';
$test_pages = [
    'メインページ' => '/',
    'アバウト' => '/about/',
    'スキル' => '/skills/', 
    'コンタクト' => '/contact/',
    'ポートフォリオ' => '/portfolio/'
];

echo "=== WordPress ページアクセステスト ===\n";
echo "実行日時: " . date('Y-m-d H:i:s') . "\n\n";

$results = [];

foreach ($test_pages as $name => $path) {
    $url = $base_url . $path;
    echo "テスト中: {$name} ({$url})\n";
    
    // cURLでアクセステスト
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, 'WordPress Page Test Script');
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    $result = [
        'name' => $name,
        'url' => $url,
        'http_code' => $http_code,
        'status' => $http_code == 200 ? 'OK' : 'エラー',
        'error' => $error,
        'content_length' => strlen($response)
    ];
    
    // レスポンス内容の基本チェック
    if ($http_code == 200 && $response) {
        // HTMLタイトルを取得
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $response, $matches)) {
            $result['title'] = trim(strip_tags($matches[1]));
        }
        
        // 404エラーの内容が含まれていないかチェック
        if (stripos($response, '404') !== false || stripos($response, 'not found') !== false) {
            $result['status'] = '404エラーページ表示';
        }
        
        // WordPress固有のエラーをチェック
        if (stripos($response, 'fatal error') !== false || stripos($response, 'parse error') !== false) {
            $result['status'] = 'PHPエラー検出';
        }
    }
    
    $results[] = $result;
    
    echo "  ステータス: {$result['status']} (HTTP {$http_code})\n";
    if (isset($result['title'])) {
        echo "  タイトル: {$result['title']}\n";
    }
    if ($error) {
        echo "  エラー: {$error}\n";
    }
    echo "\n";
    
    // サーバー負荷軽減のため少し待機
    usleep(500000); // 0.5秒
}

// 結果をJSON形式でも保存
$json_results = json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
file_put_contents('/Users/kei/work/wordpress-site/page-access-test-results.json', $json_results);

echo "=== テスト結果サマリー ===\n";
$success_count = 0;
foreach ($results as $result) {
    echo "{$result['name']}: {$result['status']}\n";
    if ($result['status'] === 'OK') {
        $success_count++;
    }
}

echo "\n成功: {$success_count}/" . count($results) . " ページ\n";
echo "詳細な結果は page-access-test-results.json に保存されました。\n";
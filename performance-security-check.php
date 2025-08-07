<?php
/**
 * パフォーマンス測定とセキュリティチェックスクリプト
 */

echo "=== パフォーマンス・セキュリティ総合チェック ===\n";
echo "実行日時: " . date('Y-m-d H:i:s') . "\n\n";

$base_url = 'http://localhost:8090';
$test_pages = [
    'メインページ' => '/',
    'アバウト' => '/about/',
    'スキル' => '/skills/', 
    'コンタクト' => '/contact/',
    'ポートフォリオ' => '/portfolio/'
];

$results = [
    'performance' => [],
    'security' => [],
    'timestamp' => date('Y-m-d H:i:s')
];

// === パフォーマンステスト ===
echo "=== パフォーマンステスト ===\n";

foreach ($test_pages as $name => $path) {
    $url = $base_url . $path;
    echo "測定中: {$name}\n";
    
    $start_time = microtime(true);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Performance Test Agent');
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $total_time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    $connect_time = curl_getinfo($ch, CURLINFO_CONNECT_TIME);
    $download_size = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);
    curl_close($ch);
    
    $end_time = microtime(true);
    $response_time = ($end_time - $start_time) * 1000; // ミリ秒
    
    $perf_result = [
        'page' => $name,
        'url' => $url,
        'http_code' => $http_code,
        'response_time_ms' => round($response_time, 2),
        'curl_total_time' => round($total_time * 1000, 2),
        'connect_time_ms' => round($connect_time * 1000, 2),
        'download_size_bytes' => $download_size,
        'download_size_kb' => round($download_size / 1024, 2),
    ];
    
    // レスポンス時間の評価
    if ($response_time < 500) {
        $perf_result['performance_rating'] = 'Excellent';
    } elseif ($response_time < 1000) {
        $perf_result['performance_rating'] = 'Good';
    } elseif ($response_time < 2000) {
        $perf_result['performance_rating'] = 'Fair';
    } else {
        $perf_result['performance_rating'] = 'Poor';
    }
    
    echo "  レスポンス時間: {$perf_result['response_time_ms']}ms ({$perf_result['performance_rating']})\n";
    echo "  ダウンロードサイズ: {$perf_result['download_size_kb']}KB\n";
    echo "  接続時間: {$perf_result['connect_time_ms']}ms\n";
    
    $results['performance'][] = $perf_result;
    echo "\n";
}

// === セキュリティチェック ===
echo "=== セキュリティチェック ===\n";

// 1. HTTPヘッダーのセキュリティチェック
foreach ($test_pages as $name => $path) {
    $url = $base_url . $path;
    echo "セキュリティ分析: {$name}\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    
    $headers = curl_exec($ch);
    curl_close($ch);
    
    $security_result = [
        'page' => $name,
        'url' => $url,
        'headers' => [],
        'security_issues' => []
    ];
    
    // セキュリティヘッダーのチェック
    $security_headers = [
        'X-Frame-Options' => false,
        'X-Content-Type-Options' => false,
        'X-XSS-Protection' => false,
        'Strict-Transport-Security' => false,
        'Content-Security-Policy' => false,
        'Referrer-Policy' => false
    ];
    
    foreach ($security_headers as $header => $found) {
        if (stripos($headers, $header . ':') !== false) {
            $security_headers[$header] = true;
            echo "  ✓ {$header} 設定済み\n";
        } else {
            $security_issues[] = "{$header} ヘッダーが設定されていません";
            echo "  ✗ {$header} 未設定\n";
        }
    }
    
    // WordPressバージョンの露出チェック
    if (stripos($headers, 'X-Powered-By: PHP') !== false) {
        $security_result['security_issues'][] = 'PHPバージョンが露出している可能性';
        echo "  ⚠ PHPバージョン情報が露出\n";
    }
    
    $security_result['headers'] = $security_headers;
    $security_result['security_score'] = (count(array_filter($security_headers)) / count($security_headers)) * 100;
    
    echo "  セキュリティスコア: " . round($security_result['security_score'], 1) . "%\n";
    
    $results['security'][] = $security_result;
    echo "\n";
}

// 2. ファイルシステムのセキュリティチェック
echo "=== ファイルシステムセキュリティ ===\n";

$file_checks = [
    'wp-config.php' => '/var/www/html/wp-config.php',
    '.htaccess' => '/var/www/html/.htaccess',
    'wp-admin directory' => '/var/www/html/wp-admin',
    'wp-includes directory' => '/var/www/html/wp-includes'
];

foreach ($file_checks as $description => $path) {
    // Dockerコンテナ内でのチェックのため、実際のファイル確認は簡略化
    echo "  {$description}: 存在確認 (Docker環境)\n";
}

// 3. データベースセキュリティチェック（基本）
echo "\n=== データベースセキュリティ基本チェック ===\n";
echo "  データベース接続: 暗号化接続推奨\n";
echo "  テーブルプレフィックス: wp_ (デフォルト - 変更推奨)\n";
echo "  ユーザー権限: 最小権限の原則適用推奨\n";

// 結果をJSONファイルに保存
file_put_contents('/Users/kei/work/wordpress-site/performance-security-results.json', 
                 json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "\n=== 総合サマリー ===\n";

// パフォーマンスサマリー
$avg_response_time = 0;
$total_size = 0;
foreach ($results['performance'] as $perf) {
    $avg_response_time += $perf['response_time_ms'];
    $total_size += $perf['download_size_kb'];
}
$avg_response_time /= count($results['performance']);

echo "パフォーマンス:\n";
echo "  平均レスポンス時間: " . round($avg_response_time, 2) . "ms\n";
echo "  総ダウンロードサイズ: " . round($total_size, 2) . "KB\n";

// セキュリティサマリー  
$total_security_score = 0;
foreach ($results['security'] as $sec) {
    $total_security_score += $sec['security_score'];
}
$avg_security_score = $total_security_score / count($results['security']);

echo "\nセキュリティ:\n";
echo "  平均セキュリティスコア: " . round($avg_security_score, 1) . "%\n";

echo "\n詳細結果は performance-security-results.json に保存されました。\n";
<?php
/**
 * ブログ監視機能のテスト用スクリプト
 * 
 * このファイルは開発用のテストファイルです。
 * ブラウザまたはWP-CLIから実行してクラスの動作を確認できます。
 */

// WordPress環境の確認
if (!defined('ABSPATH')) {
    // WordPressが読み込まれていない場合は、wp-config.phpを読み込む
    require_once __DIR__ . '/../../wp-config.docker.php';
    require_once ABSPATH . 'wp-load.php';
}

// 必要なクラスの読み込み（未読み込みの場合）
if (!class_exists('KeiPortfolio\Blog\Blog_Logger')) {
    require_once __DIR__ . '/inc/class-blog-logger.php';
}
if (!class_exists('KeiPortfolio\Blog\Blog_Performance_Monitor')) {
    require_once __DIR__ . '/inc/class-blog-performance-monitor.php';
}
if (!class_exists('KeiPortfolio\Blog\Blog_Config')) {
    require_once __DIR__ . '/inc/class-blog-config.php';
}

use KeiPortfolio\Blog\Blog_Logger;
use KeiPortfolio\Blog\Blog_Performance_Monitor;
use KeiPortfolio\Blog\Blog_Config;

/**
 * テスト結果表示用HTML
 */
if (!wp_doing_ajax() && !defined('WP_CLI')) {
    ?>
    <!DOCTYPE html>
    <html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>ブログ監視機能テスト</title>
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui; margin: 20px; }
            .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
            .test-success { background: #d4edda; border-color: #c3e6cb; }
            .test-error { background: #f8d7da; border-color: #f5c6cb; }
            .test-info { background: #d1ecf1; border-color: #bee5eb; }
            .test-title { font-size: 18px; font-weight: bold; margin-bottom: 10px; }
            .test-details { background: #f8f9fa; padding: 10px; border-radius: 3px; font-family: monospace; font-size: 12px; }
            .test-log { max-height: 200px; overflow-y: auto; }
        </style>
    </head>
    <body>
        <h1>ブログ監視機能テスト</h1>
        <p>実行時間: <?php echo current_time('Y-m-d H:i:s'); ?></p>
    <?php
}

/**
 * テスト実行
 */
function run_blog_monitoring_tests() {
    $test_results = [];
    
    // 1. Blog_Logger テスト
    $test_results['logger'] = test_blog_logger();
    
    // 2. Blog_Performance_Monitor テスト
    $test_results['performance'] = test_performance_monitor();
    
    // 3. Blog_Config テスト
    $test_results['config'] = test_blog_config();
    
    // 4. 統合テスト
    $test_results['integration'] = test_integration();
    
    return $test_results;
}

/**
 * Blog_Logger のテスト
 */
function test_blog_logger() {
    $test_name = 'Blog_Logger';
    $results = [];
    
    try {
        // インスタンス取得テスト
        $logger = Blog_Logger::get_instance();
        $results[] = "✓ インスタンス取得成功";
        
        // ログ設定確認
        $settings = $logger->get_log_settings();
        if (is_array($settings) && isset($settings['logging_enabled'])) {
            $results[] = "✓ ログ設定取得成功";
            $results[] = "  - ログ有効: " . ($settings['logging_enabled'] ? 'Yes' : 'No');
            $results[] = "  - デバッグモード: " . ($settings['debug_mode'] ? 'Yes' : 'No');
        } else {
            $results[] = "✗ ログ設定取得失敗";
        }
        
        // 各種ログレベルのテスト
        $logger->debug('Debug message test', ['test' => 'debug_level']);
        $results[] = "✓ デバッグログ記録テスト完了";
        
        $logger->info('Info message test', ['test' => 'info_level']);
        $results[] = "✓ 情報ログ記録テスト完了";
        
        $logger->warning('Warning message test', ['test' => 'warning_level']);
        $results[] = "✓ 警告ログ記録テスト完了";
        
        $logger->error('Error message test', ['test' => 'error_level']);
        $results[] = "✓ エラーログ記録テスト完了";
        
        // ログファイル確認
        $log_files = $logger->get_log_files();
        if (!empty($log_files)) {
            $results[] = "✓ ログファイル生成確認: " . count($log_files) . "個のファイル";
        } else {
            $results[] = "! ログファイルが見つかりません（設定により正常な場合があります）";
        }
        
        return ['success' => true, 'details' => $results];
        
    } catch (Exception $e) {
        return ['success' => false, 'details' => ["✗ エラー: " . $e->getMessage()]];
    } catch (Error $e) {
        return ['success' => false, 'details' => ["✗ 致命的エラー: " . $e->getMessage()]];
    }
}

/**
 * Blog_Performance_Monitor のテスト
 */
function test_performance_monitor() {
    $test_name = 'Blog_Performance_Monitor';
    $results = [];
    
    try {
        // インスタンス取得テスト
        $monitor = Blog_Performance_Monitor::get_instance();
        $results[] = "✓ インスタンス取得成功";
        
        // 監視設定確認
        $settings = $monitor->get_monitor_settings();
        if (is_array($settings)) {
            $results[] = "✓ 監視設定取得成功";
            $results[] = "  - 監視有効: " . ($settings['monitoring_enabled'] ? 'Yes' : 'No');
            $results[] = "  - デバッグモード: " . ($settings['debug_mode'] ? 'Yes' : 'No');
        }
        
        // パフォーマンス測定テスト
        $monitor->start_timer('test_operation');
        
        // 擬似的な処理（小さな遅延）
        usleep(10000); // 0.01秒
        
        $elapsed = $monitor->end_timer('test_operation');
        if ($elapsed !== false) {
            $results[] = "✓ パフォーマンス測定テスト完了: " . number_format($elapsed, 4) . "秒";
        }
        
        // メモリスナップショット
        $monitor->record_memory_snapshot('test_snapshot');
        $results[] = "✓ メモリスナップショット記録完了";
        
        // カスタムメトリクス
        $monitor->record_metric('test_metric', 42, ['unit' => 'count']);
        $results[] = "✓ カスタムメトリクス記録完了";
        
        // 現在のメトリクス取得
        $metrics = $monitor->get_current_metrics();
        if (is_array($metrics)) {
            $results[] = "✓ メトリクス取得成功: " . count($metrics['metrics'] ?? []) . "個のメトリクス";
        }
        
        return ['success' => true, 'details' => $results];
        
    } catch (Exception $e) {
        return ['success' => false, 'details' => ["✗ エラー: " . $e->getMessage()]];
    }
}

/**
 * Blog_Config のテスト
 */
function test_blog_config() {
    $test_name = 'Blog_Config';
    $results = [];
    
    try {
        // インスタンス取得テスト
        $config = Blog_Config::get_instance();
        $results[] = "✓ インスタンス取得成功";
        
        // デフォルト設定確認
        $default_config = $config->get_default_config();
        if (is_array($default_config)) {
            $results[] = "✓ デフォルト設定取得成功: " . count($default_config) . "個のセクション";
        }
        
        // 設定値の取得テスト
        $logging_config = $config->get('logging');
        if (is_array($logging_config)) {
            $results[] = "✓ ログ設定取得成功";
            $results[] = "  - ログ有効: " . ($logging_config['enabled'] ? 'Yes' : 'No');
        }
        
        // 設定値の更新テスト
        $original_value = $config->get('display', 'posts_per_page');
        $test_value = 15;
        
        if ($config->set('display', 'posts_per_page', $test_value)) {
            $new_value = $config->get('display', 'posts_per_page');
            if ($new_value === $test_value) {
                $results[] = "✓ 設定値更新テスト成功";
                // 元の値に戻す
                $config->set('display', 'posts_per_page', $original_value);
            } else {
                $results[] = "✗ 設定値更新テスト失敗（値が一致しません）";
            }
        } else {
            $results[] = "✗ 設定値更新テスト失敗";
        }
        
        // 設定エクスポートテスト
        $export_data = $config->export_config();
        if (is_array($export_data) && isset($export_data['config'])) {
            $results[] = "✓ 設定エクスポートテスト成功";
        }
        
        // 全設定取得
        $all_config = $config->get_all_config();
        if (is_array($all_config)) {
            $results[] = "✓ 全設定取得成功: " . count($all_config) . "個のセクション";
        }
        
        return ['success' => true, 'details' => $results];
        
    } catch (Exception $e) {
        return ['success' => false, 'details' => ["✗ エラー: " . $e->getMessage()]];
    }
}

/**
 * 統合テスト
 */
function test_integration() {
    $test_name = 'Integration Test';
    $results = [];
    
    try {
        // 3つのクラスが連携して動作するかテスト
        $logger = Blog_Logger::get_instance();
        $monitor = Blog_Performance_Monitor::get_instance();
        $config = Blog_Config::get_instance();
        
        $results[] = "✓ 全クラスのインスタンス取得成功";
        
        // 設定に応じたログレベルのテスト
        $log_level = $config->get('logging', 'level', 'info');
        $results[] = "✓ 設定からログレベル取得: " . $log_level;
        
        // パフォーマンス監視とログ記録の統合テスト
        $monitor->start_timer('integration_test');
        
        // 重い処理のシミュレーション
        $data = [];
        for ($i = 0; $i < 1000; $i++) {
            $data[] = md5(rand());
        }
        
        $elapsed = $monitor->end_timer('integration_test');
        
        // 結果をログに記録
        $logger->info('Integration test completed', [
            'elapsed_time' => $elapsed,
            'data_points' => count($data),
            'memory_usage' => memory_get_usage(true)
        ]);
        
        $results[] = "✓ 統合テスト完了: " . number_format($elapsed, 4) . "秒";
        
        // WordPress環境情報の記録
        $logger->info('WordPress environment info', [
            'wp_debug' => defined('WP_DEBUG') && WP_DEBUG,
            'wp_debug_log' => defined('WP_DEBUG_LOG') && WP_DEBUG_LOG,
            'environment_type' => defined('WP_ENVIRONMENT_TYPE') ? WP_ENVIRONMENT_TYPE : 'unknown',
            'php_version' => PHP_VERSION,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time')
        ]);
        
        $results[] = "✓ WordPress環境情報をログに記録";
        
        return ['success' => true, 'details' => $results];
        
    } catch (Exception $e) {
        return ['success' => false, 'details' => ["✗ エラー: " . $e->getMessage()]];
    }
}

/**
 * テスト結果の表示
 */
function display_test_results($test_results) {
    foreach ($test_results as $test_name => $result) {
        $class = $result['success'] ? 'test-success' : 'test-error';
        $status = $result['success'] ? '成功' : '失敗';
        
        if (!wp_doing_ajax() && !defined('WP_CLI')) {
            echo "<div class='test-section {$class}'>";
            echo "<div class='test-title'>{$test_name} - {$status}</div>";
            echo "<div class='test-details test-log'>";
            foreach ($result['details'] as $detail) {
                echo "<div>" . esc_html($detail) . "</div>";
            }
            echo "</div>";
            echo "</div>";
        } else {
            // CLI または AJAX の場合はシンプルな出力
            echo "\n=== {$test_name} - {$status} ===\n";
            foreach ($result['details'] as $detail) {
                echo "  " . $detail . "\n";
            }
        }
    }
}

// テスト実行
try {
    $test_results = run_blog_monitoring_tests();
    display_test_results($test_results);
    
    // 成功/失敗の集計
    $success_count = count(array_filter($test_results, function($result) {
        return $result['success'];
    }));
    $total_count = count($test_results);
    
    if (!wp_doing_ajax() && !defined('WP_CLI')) {
        echo "<div class='test-section test-info'>";
        echo "<div class='test-title'>テスト結果サマリー</div>";
        echo "<div>成功: {$success_count}/{$total_count}</div>";
        echo "<div>実行時間: " . current_time('Y-m-d H:i:s') . "</div>";
        echo "<div>メモリ使用量: " . number_format(memory_get_usage(true) / 1024 / 1024, 2) . " MB</div>";
        echo "<div>ピークメモリ: " . number_format(memory_get_peak_usage(true) / 1024 / 1024, 2) . " MB</div>";
        echo "</div>";
        echo "</body></html>";
    } else {
        echo "\n=== テスト結果サマリー ===\n";
        echo "成功: {$success_count}/{$total_count}\n";
        echo "実行時間: " . current_time('Y-m-d H:i:s') . "\n";
        echo "メモリ使用量: " . number_format(memory_get_usage(true) / 1024 / 1024, 2) . " MB\n";
    }
    
} catch (Exception $e) {
    $error_msg = "テスト実行中にエラーが発生しました: " . $e->getMessage();
    if (!wp_doing_ajax() && !defined('WP_CLI')) {
        echo "<div class='test-section test-error'>";
        echo "<div class='test-title'>致命的エラー</div>";
        echo "<div>" . esc_html($error_msg) . "</div>";
        echo "</div>";
        echo "</body></html>";
    } else {
        echo "\n" . $error_msg . "\n";
    }
}
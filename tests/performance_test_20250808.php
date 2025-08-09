<?php
/**
 * パフォーマンステスト - レビュー指摘事項の修正検証
 * 
 * 実行コマンド: php performance_test_20250808.php
 */

// WordPressの環境を読み込み
require_once dirname(__DIR__) . '/wp-config.php';
require_once dirname(__DIR__) . '/wp-load.php';

class PerformanceTest {
    private $results = [];
    
    public function __construct() {
        echo "パフォーマンステスト開始: " . date('Y-m-d H:i:s') . "\n";
        echo "=================================================\n";
    }
    
    /**
     * キャッシュクリア処理のパフォーマンステスト
     */
    public function test_cache_clear_performance() {
        echo "\n1. キャッシュクリア処理のパフォーマンステスト\n";
        echo "-------------------------------------------------\n";
        
        $optimized_blog_data = new Optimized_Blog_Data();
        
        // テスト前にいくつかのキャッシュを作成
        for ($year = 2020; $year <= 2024; $year++) {
            for ($month = 1; $month <= 12; $month++) {
                wp_cache_set("archive_{$year}_{$month}", ['test_data'], $optimized_blog_data->get_cache_group(), 3600);
            }
        }
        
        // パフォーマンステスト実行
        $start_time = microtime(true);
        $start_memory = memory_get_usage(true);
        
        $optimized_blog_data->clear_cache();
        
        $end_time = microtime(true);
        $end_memory = memory_get_usage(true);
        
        $execution_time = ($end_time - $start_time) * 1000; // ミリ秒
        $memory_usage = $end_memory - $start_memory;
        
        echo "実行時間: " . number_format($execution_time, 2) . " ms\n";
        echo "メモリ使用量: " . number_format($memory_usage / 1024, 2) . " KB\n";
        
        $this->results['cache_clear'] = [
            'execution_time' => $execution_time,
            'memory_usage' => $memory_usage
        ];
        
        return $execution_time < 100; // 100ms以内を目標
    }
    
    /**
     * 読了時間計算のパフォーマンステスト
     */
    public function test_reading_time_performance() {
        echo "\n2. 読了時間計算のパフォーマンステスト\n";
        echo "-------------------------------------------------\n";
        
        // テスト用の投稿を取得
        $posts = get_posts([
            'post_type' => 'post',
            'post_status' => 'publish',
            'numberposts' => 5,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
        
        if (empty($posts)) {
            echo "テスト用の投稿が見つかりません\n";
            return false;
        }
        
        $total_time = 0;
        $test_count = 0;
        
        foreach ($posts as $post) {
            // 既存のキャッシュをクリア
            delete_post_meta($post->ID, '_reading_time_cache');
            
            $start_time = microtime(true);
            
            // 新しい最適化されたコードをシミュレート
            $reading_time = get_post_meta($post->ID, '_reading_time_cache', true);
            if (empty($reading_time)) {
                $content = $post->post_content;
                $plain_content = wp_strip_all_tags($content);
                $char_count = mb_strlen($plain_content, 'UTF-8');
                $reading_time = max(1, round($char_count / 400)); // 日本語400文字/分
                update_post_meta($post->ID, '_reading_time_cache', $reading_time);
            }
            
            $end_time = microtime(true);
            $execution_time = ($end_time - $start_time) * 1000; // ミリ秒
            
            echo "投稿ID {$post->ID}: " . number_format($execution_time, 2) . " ms (読了時間: {$reading_time}分)\n";
            
            $total_time += $execution_time;
            $test_count++;
        }
        
        $average_time = $total_time / $test_count;
        echo "平均実行時間: " . number_format($average_time, 2) . " ms\n";
        
        $this->results['reading_time'] = [
            'average_time' => $average_time,
            'test_count' => $test_count
        ];
        
        return $average_time < 10; // 10ms以内を目標
    }
    
    /**
     * データベースクエリ最適化のパフォーマンステスト
     */
    public function test_archive_query_performance() {
        echo "\n3. アーカイブクエリ最適化のパフォーマンステスト\n";
        echo "-------------------------------------------------\n";
        
        $optimized_blog_data = new Optimized_Blog_Data();
        
        // テスト対象の年月
        $test_cases = [
            [2024, null],  // 年全体
            [2024, 1],     // 特定月
            [2023, 6],     // 過去の月
        ];
        
        foreach ($test_cases as $case) {
            $year = $case[0];
            $month = $case[1];
            $case_name = $month ? "{$year}年{$month}月" : "{$year}年全体";
            
            // キャッシュをクリア
            $cache_key = sprintf('archive_%s_%s', $year, $month ?: 'all');
            wp_cache_delete($cache_key, $optimized_blog_data->get_cache_group());
            
            $start_time = microtime(true);
            
            $results = $optimized_blog_data->get_archive_data($year, $month);
            
            $end_time = microtime(true);
            $execution_time = ($end_time - $start_time) * 1000; // ミリ秒
            
            echo "{$case_name}: " . number_format($execution_time, 2) . " ms (" . ($results ? count($results) : 0) . "件)\n";
        }
        
        return true;
    }
    
    /**
     * 総合的なパフォーマンステスト結果を表示
     */
    public function show_summary() {
        echo "\n=================================================\n";
        echo "パフォーマンステスト結果サマリー\n";
        echo "=================================================\n";
        
        $all_passed = true;
        
        // キャッシュクリア処理
        if (isset($this->results['cache_clear'])) {
            $time = $this->results['cache_clear']['execution_time'];
            $status = $time < 100 ? '✅ 合格' : '❌ 要改善';
            echo "キャッシュクリア処理: " . number_format($time, 2) . " ms - {$status}\n";
            if ($time >= 100) $all_passed = false;
        }
        
        // 読了時間計算
        if (isset($this->results['reading_time'])) {
            $time = $this->results['reading_time']['average_time'];
            $status = $time < 10 ? '✅ 合格' : '❌ 要改善';
            echo "読了時間計算: " . number_format($time, 2) . " ms (平均) - {$status}\n";
            if ($time >= 10) $all_passed = false;
        }
        
        echo "\n総合結果: " . ($all_passed ? '✅ すべてのテストが合格' : '❌ 一部のテストで要改善項目あり') . "\n";
        echo "テスト完了: " . date('Y-m-d H:i:s') . "\n";
    }
}

// テスト実行
$test = new PerformanceTest();

try {
    $test->test_cache_clear_performance();
    $test->test_reading_time_performance();
    $test->test_archive_query_performance();
    $test->show_summary();
} catch (Exception $e) {
    echo "テスト実行中にエラーが発生しました: " . $e->getMessage() . "\n";
    exit(1);
}
<?php
/**
 * WordPress テスト用包括的ヘルパークラス
 * 
 * テストの独立性、信頼性、保守性を向上させるためのユーティリティクラス
 * 
 * @package KeiPortfolio
 * @subpackage Tests
 */

class TestHelperClass {
    
    /**
     * テスト用データファクトリ
     */
    public static function createTestPosts($count = 10, $post_type = 'post', $additional_args = []) {
        $factory = self::getFactory();
        $posts = [];
        
        $default_args = [
            'post_type' => $post_type,
            'post_status' => 'publish',
        ];
        
        for ($i = 0; $i < $count; $i++) {
            $args = array_merge($default_args, [
                'post_title' => 'Test Post ' . ($i + 1),
                'post_content' => 'Test content for post ' . ($i + 1),
                'post_date' => date('Y-m-d H:i:s', strtotime("-{$i} days")),
            ], $additional_args);
            
            $posts[] = $factory->post->create($args);
        }
        
        return $posts;
    }
    
    /**
     * テスト用タクソノミーターム作成
     */
    public static function createTestTerms($taxonomy, $terms_data) {
        $factory = self::getFactory();
        $created_terms = [];
        
        foreach ($terms_data as $term_data) {
            $args = array_merge([
                'taxonomy' => $taxonomy,
            ], $term_data);
            
            $created_terms[] = $factory->term->create($args);
        }
        
        return $created_terms;
    }
    
    /**
     * テスト用メタデータ設定
     */
    public static function setPostMeta($post_id, $meta_data) {
        foreach ($meta_data as $key => $value) {
            update_post_meta($post_id, $key, $value);
        }
    }
    
    /**
     * 投稿にタクソノミーを設定
     */
    public static function setPostTerms($post_id, $taxonomy, $terms) {
        wp_set_object_terms($post_id, $terms, $taxonomy);
    }
    
    /**
     * グローバル状態の完全リセット
     */
    public static function resetGlobalState() {
        // WordPress キャッシュクリア
        wp_cache_flush();
        
        // グローバル変数リセット
        global $wpdb, $wp_query, $post, $wp_the_query, $wp_rewrite;
        
        if (isset($wpdb->queries)) {
            $wpdb->queries = [];
        }
        
        $wp_query = null;
        $post = null;
        $wp_the_query = null;
        
        // WordPress 内部キャッシュクリア
        if (function_exists('clean_post_cache')) {
            clean_post_cache('');
        }
        if (function_exists('clean_term_cache')) {
            clean_term_cache('');
        }
        if (function_exists('clean_user_cache')) {
            clean_user_cache('');
        }
        
        // オプションキャッシュクリア
        if (function_exists('wp_cache_delete_multiple')) {
            wp_cache_delete_multiple([
                'alloptions',
                'notoptions'
            ], 'options');
        }
        
        // トランジェントクリア
        self::clearTransients();
    }
    
    /**
     * すべてのトランジェントをクリア
     */
    public static function clearTransients() {
        global $wpdb;
        
        // トランジェントを削除
        $wpdb->query(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%' OR option_name LIKE '_site_transient_%'"
        );
        
        // オブジェクトキャッシュからも削除
        wp_cache_flush();
    }
    
    /**
     * データベーストランザクション用ヘルパー
     */
    public static function startTransaction() {
        global $wpdb;
        $wpdb->query('START TRANSACTION');
    }
    
    public static function rollbackTransaction() {
        global $wpdb;
        $wpdb->query('ROLLBACK');
    }
    
    /**
     * テスト実行時間測定
     */
    public static function measureExecutionTime($callback, $args = []) {
        $start_time = microtime(true);
        $result = call_user_func_array($callback, $args);
        $execution_time = microtime(true) - $start_time;
        
        return [
            'result' => $result,
            'execution_time' => $execution_time,
            'memory_usage' => memory_get_peak_usage(true)
        ];
    }
    
    /**
     * メモリ使用量の測定
     */
    public static function measureMemoryUsage($callback, $args = []) {
        $memory_before = memory_get_usage(true);
        $result = call_user_func_array($callback, $args);
        $memory_after = memory_get_usage(true);
        
        return [
            'result' => $result,
            'memory_before' => $memory_before,
            'memory_after' => $memory_after,
            'memory_increase' => $memory_after - $memory_before,
            'peak_memory' => memory_get_peak_usage(true)
        ];
    }
    
    /**
     * データプロバイダー用ユーティリティ
     */
    public static function generateDataProvider($base_data, $variations) {
        $provider_data = [];
        
        foreach ($variations as $key => $variation) {
            $provider_data[$key] = array_merge($base_data, $variation);
        }
        
        return $provider_data;
    }
    
    /**
     * アサーションヘルパー
     */
    public static function assertPostStructure($test_case, $post, $expected_keys = []) {
        $test_case->assertInstanceOf('WP_Post', $post, 'Should be WP_Post instance');
        
        $default_keys = ['ID', 'post_title', 'post_content', 'post_status', 'post_date'];
        $keys_to_check = !empty($expected_keys) ? $expected_keys : $default_keys;
        
        foreach ($keys_to_check as $key) {
            $test_case->assertObjectHasAttribute($key, $post, "Post should have {$key} property");
        }
        
        $test_case->assertIsInt($post->ID, 'Post ID should be integer');
        $test_case->assertGreaterThan(0, $post->ID, 'Post ID should be positive');
        $test_case->assertIsString($post->post_title, 'Post title should be string');
        $test_case->assertIsString($post->post_content, 'Post content should be string');
        $test_case->assertIsString($post->post_status, 'Post status should be string');
    }
    
    /**
     * 配列構造のアサーション
     */
    public static function assertArrayStructure($test_case, $array, $expected_keys) {
        $test_case->assertIsArray($array, 'Should be array');
        
        foreach ($expected_keys as $key) {
            $test_case->assertArrayHasKey($key, $array, "Array should have key: {$key}");
        }
    }
    
    /**
     * パフォーマンスアサーション
     */
    public static function assertPerformance($test_case, $execution_time, $max_time, $memory_increase = null, $max_memory = null) {
        $test_case->assertIsFloat($execution_time, 'Execution time should be float');
        $test_case->assertGreaterThan(0, $execution_time, 'Execution time should be positive');
        $test_case->assertLessThan($max_time, $execution_time, "Execution should be less than {$max_time} seconds");
        
        if ($memory_increase !== null && $max_memory !== null) {
            $test_case->assertLessThan($max_memory, $memory_increase, "Memory increase should be less than {$max_memory} bytes");
        }
    }
    
    /**
     * SQLクエリ数の監視
     */
    private static $query_count_start = 0;
    
    public static function startQueryMonitoring() {
        self::$query_count_start = get_num_queries();
    }
    
    public static function getQueryCount() {
        return get_num_queries() - self::$query_count_start;
    }
    
    public static function assertQueryCount($test_case, $max_queries, $message = '') {
        $query_count = self::getQueryCount();
        $test_case->assertLessThanOrEqual(
            $max_queries, 
            $query_count, 
            $message ?: "Should execute no more than {$max_queries} queries, but executed {$query_count}"
        );
    }
    
    /**
     * キャッシュ効果のテスト
     */
    public static function testCacheEffectiveness($test_case, $callback, $args = [], $tolerance = 1.5) {
        // キャッシュをクリア
        wp_cache_flush();
        
        // 初回実行（コールドキャッシュ）
        $cold_result = self::measureExecutionTime($callback, $args);
        
        // 2回目実行（ウォームキャッシュ）
        $warm_result = self::measureExecutionTime($callback, $args);
        
        // 結果が同じことを確認
        $test_case->assertEquals(
            $cold_result['result'], 
            $warm_result['result'], 
            'Cached and non-cached results should be identical'
        );
        
        // 実行時間の確認（厳密すぎないように）
        $test_case->assertGreaterThan(0, $cold_result['execution_time'], 'Cold cache should take some time');
        $test_case->assertGreaterThan(0, $warm_result['execution_time'], 'Warm cache should take some time');
        
        // パフォーマンスが著しく悪化していないことを確認
        $test_case->assertLessThan(
            $cold_result['execution_time'] * $tolerance,
            $warm_result['execution_time'],
            'Warm cache should not be significantly slower than cold cache'
        );
        
        return [
            'cold_cache' => $cold_result,
            'warm_cache' => $warm_result,
            'improvement_ratio' => $cold_result['execution_time'] / $warm_result['execution_time']
        ];
    }
    
    /**
     * テスト環境の検証
     */
    public static function validateTestEnvironment() {
        $issues = [];
        
        // WordPressの基本関数チェック
        $required_functions = [
            'wp_cache_flush', 'get_posts', 'wp_insert_post', 
            'update_post_meta', 'wp_delete_post'
        ];
        
        foreach ($required_functions as $function) {
            if (!function_exists($function)) {
                $issues[] = "Required WordPress function '{$function}' is not available";
            }
        }
        
        // データベース接続チェック
        global $wpdb;
        if (!($wpdb instanceof wpdb)) {
            $issues[] = 'WordPress database connection not available';
        }
        
        // テストファクトリのチェック
        if (!self::getFactory()) {
            $issues[] = 'Test factory not available';
        }
        
        return empty($issues) ? true : $issues;
    }
    
    /**
     * ファクトリインスタンスを取得
     */
    private static function getFactory() {
        global $factory;
        
        if (isset($factory)) {
            return $factory;
        }
        
        // PHPUnitのファクトリがない場合はモックを作成
        if (!class_exists('WP_UnitTest_Factory')) {
            require_once dirname(__FILE__) . '/test-helpers.php';
            return $GLOBALS['factory'];
        }
        
        return null;
    }
    
    /**
     * 条件付きスキップヘルパー
     */
    public static function skipIfCondition($test_case, $condition, $message) {
        if ($condition) {
            $test_case->markTestSkipped($message);
        }
    }
    
    public static function skipIfWordPressNotAvailable($test_case) {
        if (!function_exists('wp_insert_post')) {
            $test_case->markTestSkipped('WordPress functions not available');
        }
    }
    
    public static function skipIfDatabaseNotAvailable($test_case) {
        global $wpdb;
        if (!($wpdb instanceof wpdb)) {
            $test_case->markTestSkipped('WordPress database not available');
        }
    }
    
    /**
     * データクリーンアップヘルパー
     */
    public static function cleanupPosts($post_ids) {
        foreach ((array) $post_ids as $post_id) {
            wp_delete_post($post_id, true);
        }
    }
    
    public static function cleanupTerms($term_ids, $taxonomy) {
        foreach ((array) $term_ids as $term_id) {
            wp_delete_term($term_id, $taxonomy);
        }
    }
    
    public static function cleanupUsers($user_ids) {
        foreach ((array) $user_ids as $user_id) {
            wp_delete_user($user_id);
        }
    }
    
    /**
     * テストログ出力
     */
    public static function logTestInfo($test_name, $info) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("TEST [{$test_name}]: " . (is_string($info) ? $info : print_r($info, true)));
        }
    }
}
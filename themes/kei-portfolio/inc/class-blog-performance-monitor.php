<?php
/**
 * Blog Performance Monitor Class
 * ブログ機能のパフォーマンス監視クラス
 * 
 * @package KeiPortfolio
 * @since 1.0.0
 */

namespace KeiPortfolio\Blog;

use KeiPortfolio\Blog\Blog_Logger;

/**
 * ブログ機能のパフォーマンス測定および監視クラス
 */
class Blog_Performance_Monitor {
    
    /**
     * シングルトンインスタンス
     * @var Blog_Performance_Monitor|null
     */
    private static $instance = null;
    
    /**
     * パフォーマンス測定データ
     * @var array
     */
    private $metrics = [];
    
    /**
     * 測定開始時刻
     * @var array
     */
    private $start_times = [];
    
    /**
     * データベースクエリの監視
     * @var array
     */
    private $query_monitor = [];
    
    /**
     * メモリ使用量の監視
     * @var array
     */
    private $memory_snapshots = [];
    
    /**
     * ログインスタンス
     * @var Blog_Logger
     */
    private $logger;
    
    /**
     * 監視が有効かどうか
     * @var bool
     */
    private $monitoring_enabled;
    
    /**
     * パフォーマンス閾値設定
     * @var array
     */
    private $thresholds = [
        'page_load_time' => 2.0,        // 2秒
        'query_time' => 0.1,            // 0.1秒
        'query_count' => 50,            // 50クエリ
        'memory_usage' => 134217728,    // 128MB
        'memory_peak' => 268435456,     // 256MB
    ];
    
    /**
     * シングルトンパターンでインスタンス取得
     * 
     * @return Blog_Performance_Monitor インスタンス
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * コンストラクタ
     */
    private function __construct() {
        $this->logger = Blog_Logger::get_instance();
        $this->monitoring_enabled = $this->is_monitoring_enabled();
        
        if ($this->monitoring_enabled) {
            $this->init_monitoring();
            $this->setup_hooks();
        }
    }
    
    /**
     * 監視の有効性チェック
     * 
     * @return bool 監視が有効かどうか
     */
    private function is_monitoring_enabled() {
        // デバッグモードまたは管理者がパフォーマンス監視を有効にしている場合
        return (defined('WP_DEBUG') && WP_DEBUG) || get_option('blog_enable_performance_monitoring', false);
    }
    
    /**
     * パフォーマンス監視の初期化
     * 
     * @return void
     */
    private function init_monitoring() {
        // 初期測定値の記録
        $this->start_timer('page_load');
        $this->record_memory_snapshot('init');
        
        // データベースクエリ監視の初期化
        if (defined('SAVEQUERIES') && SAVEQUERIES) {
            $this->init_query_monitoring();
        }
    }
    
    /**
     * WordPressフックの設定
     * 
     * @return void
     */
    private function setup_hooks() {
        // ページ読み込み完了時の処理
        add_action('shutdown', [$this, 'finalize_monitoring'], 999);
        
        // クエリ実行前後のフック
        add_action('pre_get_posts', [$this, 'start_query_monitoring']);
        add_action('wp_loaded', [$this, 'record_wp_loaded_metrics']);
        
        // AJAX リクエストの監視
        add_action('wp_ajax_blog_performance_data', [$this, 'handle_performance_data_request']);
        add_action('wp_ajax_nopriv_blog_performance_data', [$this, 'handle_performance_data_request']);
        
        // 管理画面でのパフォーマンスデータ表示
        add_action('wp_dashboard_setup', [$this, 'add_performance_dashboard_widget']);
        
        // 定期的なレポート生成
        add_action('blog_daily_performance_report', [$this, 'generate_daily_report']);
        
        // スケジュールされたイベントの設定
        if (!wp_next_scheduled('blog_daily_performance_report')) {
            wp_schedule_event(time(), 'daily', 'blog_daily_performance_report');
        }
    }
    
    /**
     * データベースクエリ監視の初期化
     * 
     * @return void
     */
    private function init_query_monitoring() {
        $this->query_monitor = [
            'start_count' => count($GLOBALS['wpdb']->queries ?? []),
            'start_time' => microtime(true),
            'slow_queries' => [],
            'duplicate_queries' => [],
        ];
    }
    
    /**
     * タイマーの開始
     * 
     * @param string $key タイマーのキー
     * @return void
     */
    public function start_timer($key) {
        $this->start_times[$key] = microtime(true);
        
        $this->logger->debug("Performance timer started: {$key}");
    }
    
    /**
     * タイマーの終了と測定
     * 
     * @param string $key タイマーのキー
     * @return float|false 経過時間（秒）
     */
    public function end_timer($key) {
        if (!isset($this->start_times[$key])) {
            $this->logger->warning("Performance timer '{$key}' was not started");
            return false;
        }
        
        $elapsed_time = microtime(true) - $this->start_times[$key];
        $this->metrics[$key] = $elapsed_time;
        
        // 閾値チェック
        $this->check_performance_threshold($key, $elapsed_time);
        
        $this->logger->debug("Performance timer ended: {$key}", [
            'elapsed_time' => $elapsed_time,
            'threshold' => $this->thresholds[$key] ?? 'N/A'
        ]);
        
        unset($this->start_times[$key]);
        
        return $elapsed_time;
    }
    
    /**
     * メモリ使用量のスナップショット記録
     * 
     * @param string $label スナップショットのラベル
     * @return void
     */
    public function record_memory_snapshot($label) {
        $this->memory_snapshots[$label] = [
            'usage' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true),
            'timestamp' => microtime(true)
        ];
        
        $this->logger->debug("Memory snapshot recorded: {$label}", $this->memory_snapshots[$label]);
    }
    
    /**
     * カスタムメトリクスの記録
     * 
     * @param string $key メトリクスのキー
     * @param mixed $value 値
     * @param array $context 追加のコンテキスト情報
     * @return void
     */
    public function record_metric($key, $value, array $context = []) {
        $this->metrics[$key] = [
            'value' => $value,
            'timestamp' => microtime(true),
            'context' => $context
        ];
        
        $this->logger->debug("Custom metric recorded: {$key}", [
            'value' => $value,
            'context' => $context
        ]);
    }
    
    /**
     * パフォーマンス閾値のチェック
     * 
     * @param string $key メトリクスのキー
     * @param mixed $value 測定値
     * @return void
     */
    private function check_performance_threshold($key, $value) {
        if (!isset($this->thresholds[$key])) {
            return;
        }
        
        $threshold = $this->thresholds[$key];
        
        if ($value > $threshold) {
            $this->logger->warning("Performance threshold exceeded: {$key}", [
                'value' => $value,
                'threshold' => $threshold,
                'excess_ratio' => $value / $threshold
            ]);
            
            // 重要なパフォーマンス問題の場合はエラーレベルで記録
            if ($value > ($threshold * 2)) {
                $this->logger->error("Severe performance issue detected: {$key}", [
                    'value' => $value,
                    'threshold' => $threshold,
                    'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
                ]);
            }
        }
    }
    
    /**
     * WordPress読み込み完了時のメトリクス記録
     * 
     * @return void
     */
    public function record_wp_loaded_metrics() {
        $this->record_memory_snapshot('wp_loaded');
        $this->end_timer('wp_init');
    }
    
    /**
     * クエリ監視の開始
     * 
     * @param WP_Query $query クエリオブジェクト
     * @return void
     */
    public function start_query_monitoring($query) {
        if (!$query->is_main_query() || is_admin()) {
            return;
        }
        
        $this->start_timer('main_query');
    }
    
    /**
     * 監視の完了処理
     * 
     * @return void
     */
    public function finalize_monitoring() {
        // ページ読み込み時間の記録
        $this->end_timer('page_load');
        
        // 最終メモリスナップショット
        $this->record_memory_snapshot('shutdown');
        
        // データベースクエリの分析
        $this->analyze_database_queries();
        
        // パフォーマンスレポートの生成
        $this->generate_performance_report();
        
        // デバッグモードの場合はHTMLコメントとして出力
        if (defined('WP_DEBUG') && WP_DEBUG && !wp_doing_ajax()) {
            $this->output_debug_info();
        }
    }
    
    /**
     * データベースクエリの分析
     * 
     * @return void
     */
    private function analyze_database_queries() {
        if (!defined('SAVEQUERIES') || !SAVEQUERIES) {
            return;
        }
        
        global $wpdb;
        
        if (empty($wpdb->queries)) {
            return;
        }
        
        $total_queries = count($wpdb->queries);
        $total_query_time = 0;
        $slow_queries = [];
        $duplicate_queries = [];
        $query_hash_map = [];
        
        foreach ($wpdb->queries as $query) {
            $query_time = floatval($query[1]);
            $query_sql = $query[0];
            $total_query_time += $query_time;
            
            // 遅いクエリの検出
            if ($query_time > $this->thresholds['query_time']) {
                $slow_queries[] = [
                    'sql' => $query_sql,
                    'time' => $query_time,
                    'stack' => $query[2] ?? ''
                ];
            }
            
            // 重複クエリの検出
            $query_hash = md5($query_sql);
            if (isset($query_hash_map[$query_hash])) {
                $duplicate_queries[$query_hash][] = $query;
            } else {
                $query_hash_map[$query_hash] = [$query];
            }
        }
        
        // クエリメトリクスの記録
        $this->record_metric('total_queries', $total_queries);
        $this->record_metric('total_query_time', $total_query_time);
        $this->record_metric('slow_queries_count', count($slow_queries));
        $this->record_metric('duplicate_queries_count', count($duplicate_queries));
        
        // 閾値チェック
        $this->check_performance_threshold('query_count', $total_queries);
        
        // 遅いクエリの警告
        foreach ($slow_queries as $slow_query) {
            $this->logger->warning('Slow database query detected', [
                'sql' => $slow_query['sql'],
                'execution_time' => $slow_query['time'],
                'stack_trace' => $slow_query['stack']
            ]);
        }
        
        // 重複クエリの警告
        foreach ($duplicate_queries as $hash => $queries) {
            if (count($queries) > 2) { // 2回以上重複した場合のみ警告
                $this->logger->warning('Duplicate database queries detected', [
                    'sql' => $queries[0][0],
                    'count' => count($queries),
                    'total_time' => array_sum(array_column($queries, 1))
                ]);
            }
        }
    }
    
    /**
     * パフォーマンスレポートの生成
     * 
     * @return array パフォーマンスレポート
     */
    private function generate_performance_report() {
        $report = [
            'timestamp' => current_time('Y-m-d H:i:s'),
            'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
            'user_id' => get_current_user_id(),
            'is_mobile' => wp_is_mobile(),
            'metrics' => $this->metrics,
            'memory_snapshots' => $this->memory_snapshots,
            'thresholds' => $this->thresholds,
            'server_info' => [
                'php_version' => PHP_VERSION,
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'opcache_enabled' => function_exists('opcache_get_status') && opcache_get_status()
            ]
        ];
        
        // メモリ使用量の分析
        if (!empty($this->memory_snapshots)) {
            $memory_usage = end($this->memory_snapshots)['usage'];
            $memory_peak = max(array_column($this->memory_snapshots, 'peak'));
            
            $this->check_performance_threshold('memory_usage', $memory_usage);
            $this->check_performance_threshold('memory_peak', $memory_peak);
            
            $report['memory_analysis'] = [
                'final_usage' => $memory_usage,
                'peak_usage' => $memory_peak,
                'usage_percentage' => ($memory_usage / $this->parse_memory_limit()) * 100
            ];
        }
        
        // 重要なパフォーマンス問題がある場合はエラーログに記録
        if ($this->has_critical_performance_issues($report)) {
            $this->logger->error('Critical performance issues detected', $report);
        } else {
            $this->logger->info('Performance report generated', $report);
        }
        
        return $report;
    }
    
    /**
     * メモリ制限の解析
     * 
     * @return int メモリ制限（バイト）
     */
    private function parse_memory_limit() {
        $memory_limit = ini_get('memory_limit');
        
        if ($memory_limit == -1) {
            return PHP_INT_MAX;
        }
        
        $unit = strtolower(substr($memory_limit, -1));
        $value = intval($memory_limit);
        
        switch ($unit) {
            case 'g':
                return $value * 1024 * 1024 * 1024;
            case 'm':
                return $value * 1024 * 1024;
            case 'k':
                return $value * 1024;
            default:
                return $value;
        }
    }
    
    /**
     * クリティカルなパフォーマンス問題があるかチェック
     * 
     * @param array $report パフォーマンスレポート
     * @return bool クリティカルな問題があるかどうか
     */
    private function has_critical_performance_issues($report) {
        $critical_issues = [];
        
        // ページ読み込み時間のチェック
        if (isset($report['metrics']['page_load']) && 
            $report['metrics']['page_load'] > ($this->thresholds['page_load_time'] * 2)) {
            $critical_issues[] = 'excessive_page_load_time';
        }
        
        // メモリ使用量のチェック
        if (isset($report['memory_analysis']['usage_percentage']) && 
            $report['memory_analysis']['usage_percentage'] > 90) {
            $critical_issues[] = 'excessive_memory_usage';
        }
        
        // クエリ数のチェック
        if (isset($report['metrics']['total_queries']['value']) && 
            $report['metrics']['total_queries']['value'] > ($this->thresholds['query_count'] * 2)) {
            $critical_issues[] = 'excessive_query_count';
        }
        
        return !empty($critical_issues);
    }
    
    /**
     * デバッグ情報のHTML出力
     * 
     * @return void
     */
    private function output_debug_info() {
        $debug_info = [
            'Performance Metrics' => $this->metrics,
            'Memory Snapshots' => $this->memory_snapshots,
            'Server Info' => [
                'PHP Version' => PHP_VERSION,
                'Memory Limit' => ini_get('memory_limit'),
                'Memory Usage' => number_format(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
                'Peak Memory' => number_format(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB',
            ]
        ];
        
        echo "\n<!-- Blog Performance Monitor Debug Info -->\n";
        echo "<!-- " . json_encode($debug_info, JSON_PRETTY_PRINT) . " -->\n";
    }
    
    /**
     * 日次パフォーマンスレポートの生成
     * 
     * @return void
     */
    public function generate_daily_report() {
        // 過去24時間のパフォーマンスデータを集計
        $report_data = $this->aggregate_daily_performance_data();
        
        // レポートファイルの保存
        $report_file = $this->save_daily_report($report_data);
        
        // 管理者への通知（必要に応じて）
        if ($this->should_notify_admin($report_data)) {
            $this->send_performance_alert_email($report_data);
        }
        
        $this->logger->info('Daily performance report generated', [
            'report_file' => $report_file,
            'data_points' => count($report_data['metrics'] ?? [])
        ]);
    }
    
    /**
     * 日次パフォーマンスデータの集計
     * 
     * @return array 集計データ
     */
    private function aggregate_daily_performance_data() {
        // 実装上、ここではサンプルデータを返します
        // 実際の実装では、データベースやファイルから過去24時間のデータを取得して集計します
        
        return [
            'date' => current_time('Y-m-d'),
            'summary' => [
                'total_requests' => 0,
                'avg_page_load_time' => 0.0,
                'avg_memory_usage' => 0,
                'total_slow_queries' => 0,
                'performance_alerts' => 0
            ],
            'metrics' => [],
            'top_slow_pages' => [],
            'recommendations' => []
        ];
    }
    
    /**
     * 日次レポートの保存
     * 
     * @param array $data レポートデータ
     * @return string|false 保存されたファイルパスまたはfalse
     */
    private function save_daily_report($data) {
        $upload_dir = wp_upload_dir();
        $reports_dir = trailingslashit($upload_dir['basedir']) . 'performance-reports/';
        
        if (!file_exists($reports_dir)) {
            wp_mkdir_p($reports_dir);
            
            // セキュリティファイルの作成
            file_put_contents($reports_dir . '.htaccess', 'Deny from all');
            file_put_contents($reports_dir . 'index.php', '<?php // Silence is golden');
        }
        
        $report_file = $reports_dir . 'performance-report-' . $data['date'] . '.json';
        
        $result = file_put_contents(
            $report_file,
            wp_json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
        
        return $result !== false ? $report_file : false;
    }
    
    /**
     * 管理者への通知が必要かどうかの判定
     * 
     * @param array $data レポートデータ
     * @return bool 通知が必要かどうか
     */
    private function should_notify_admin($data) {
        // パフォーマンス問題が検出された場合のみ通知
        return isset($data['summary']['performance_alerts']) && $data['summary']['performance_alerts'] > 0;
    }
    
    /**
     * パフォーマンス警告メールの送信
     * 
     * @param array $data レポートデータ
     * @return void
     */
    private function send_performance_alert_email($data) {
        $admin_email = get_option('admin_email');
        if (empty($admin_email)) {
            return;
        }
        
        $subject = sprintf('[%s] Performance Alert - Daily Report', get_bloginfo('name'));
        $message = sprintf(
            "Performance issues were detected on your website:\n\n" .
            "Date: %s\n" .
            "Performance Alerts: %d\n" .
            "Average Page Load Time: %.2f seconds\n" .
            "Total Slow Queries: %d\n\n" .
            "Please check your performance reports for detailed analysis.",
            $data['date'],
            $data['summary']['performance_alerts'],
            $data['summary']['avg_page_load_time'],
            $data['summary']['total_slow_queries']
        );
        
        wp_mail($admin_email, $subject, $message);
    }
    
    /**
     * ダッシュボードウィジェットの追加
     * 
     * @return void
     */
    public function add_performance_dashboard_widget() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        wp_add_dashboard_widget(
            'blog_performance_monitor',
            'Blog Performance Monitor',
            [$this, 'render_dashboard_widget']
        );
    }
    
    /**
     * ダッシュボードウィジェットのレンダリング
     * 
     * @return void
     */
    public function render_dashboard_widget() {
        $latest_metrics = $this->get_latest_performance_metrics();
        
        echo '<div class="blog-performance-widget">';
        echo '<h4>Current Performance Status</h4>';
        
        if (empty($latest_metrics)) {
            echo '<p>No performance data available yet.</p>';
        } else {
            echo '<ul>';
            foreach ($latest_metrics as $key => $value) {
                echo '<li><strong>' . esc_html(ucfirst(str_replace('_', ' ', $key))) . ':</strong> ' . esc_html($value) . '</li>';
            }
            echo '</ul>';
        }
        
        echo '<p><a href="' . admin_url('admin.php?page=blog-performance-monitor') . '">View Detailed Report</a></p>';
        echo '</div>';
    }
    
    /**
     * 最新のパフォーマンスメトリクスの取得
     * 
     * @return array メトリクスデータ
     */
    private function get_latest_performance_metrics() {
        // 実装上、現在のセッションのメトリクスまたは保存されたデータから取得
        return [
            'Status' => 'Monitoring Active',
            'Thresholds Set' => count($this->thresholds),
            'Monitoring Enabled' => $this->monitoring_enabled ? 'Yes' : 'No'
        ];
    }
    
    /**
     * AJAX パフォーマンスデータリクエストのハンドリング
     * 
     * @return void
     */
    public function handle_performance_data_request() {
        // セキュリティチェック
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'blog_performance_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $data_type = sanitize_text_field($_POST['data_type'] ?? '');
        
        switch ($data_type) {
            case 'current_metrics':
                wp_send_json_success($this->metrics);
                break;
                
            case 'memory_snapshots':
                wp_send_json_success($this->memory_snapshots);
                break;
                
            case 'thresholds':
                wp_send_json_success($this->thresholds);
                break;
                
            default:
                wp_send_json_error('Invalid data type requested');
        }
    }
    
    /**
     * 閾値の更新
     * 
     * @param array $new_thresholds 新しい閾値設定
     * @return bool 更新の成功/失敗
     */
    public function update_thresholds(array $new_thresholds) {
        $valid_keys = array_keys($this->thresholds);
        
        foreach ($new_thresholds as $key => $value) {
            if (in_array($key, $valid_keys) && is_numeric($value) && $value > 0) {
                $this->thresholds[$key] = floatval($value);
            }
        }
        
        // データベースに保存
        $result = update_option('blog_performance_thresholds', $this->thresholds);
        
        if ($result) {
            $this->logger->info('Performance thresholds updated', $this->thresholds);
        }
        
        return $result;
    }
    
    /**
     * 監視設定の取得
     * 
     * @return array 設定情報
     */
    public function get_monitor_settings() {
        return [
            'monitoring_enabled' => $this->monitoring_enabled,
            'thresholds' => $this->thresholds,
            'debug_mode' => defined('WP_DEBUG') && WP_DEBUG,
            'save_queries' => defined('SAVEQUERIES') && SAVEQUERIES
        ];
    }
    
    /**
     * 現在のメトリクス取得
     * 
     * @return array 現在のメトリクス
     */
    public function get_current_metrics() {
        return [
            'metrics' => $this->metrics,
            'memory_snapshots' => $this->memory_snapshots,
            'start_times' => array_keys($this->start_times),
            'monitoring_enabled' => $this->monitoring_enabled
        ];
    }
}
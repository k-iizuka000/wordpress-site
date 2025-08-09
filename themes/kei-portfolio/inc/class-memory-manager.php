<?php
namespace KeiPortfolio\Performance;

/**
 * メモリ管理クラス
 * 
 * WordPressサイトのメモリ使用量を監視し、最適化を行うクラス
 * 
 * @package KeiPortfolio
 * @subpackage Performance
 * @since 1.0.0
 */
class MemoryManager {
    
    /**
     * インスタンス格納用の静的プロパティ
     *
     * @var MemoryManager|null
     */
    private static $instance = null;
    
    /**
     * システムメモリ制限値
     *
     * @var int
     */
    private $memory_limit;
    
    /**
     * 警告しきい値（メモリ使用率）
     *
     * @var float
     */
    private $warning_threshold = 0.8; // 80%で警告
    
    /**
     * シングルトンインスタンス取得
     *
     * @return MemoryManager インスタンス
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * コンストラクタ（プライベート）
     */
    private function __construct() {
        $this->memory_limit = $this->get_memory_limit();
        
        // WordPressフックに登録
        add_action('shutdown', [$this, 'log_memory_usage']);
        add_action('wp_loaded', [$this, 'check_early_memory_usage']);
        
        // デバッグモードでのみアクションフック追加
        if (WP_DEBUG && WP_DEBUG_LOG) {
            add_action('wp_head', [$this, 'add_memory_debug_info']);
        }
    }
    
    /**
     * メモリ使用状況チェック
     *
     * @return array メモリ使用統計
     */
    public function check_memory_usage() {
        $usage = memory_get_usage(true);
        $peak = memory_get_peak_usage(true);
        $percentage = $usage / $this->memory_limit;
        
        // 警告しきい値チェック
        if ($percentage > $this->warning_threshold) {
            $this->handle_memory_warning($usage, $percentage);
        }
        
        return [
            'current' => $usage,
            'peak' => $peak,
            'limit' => $this->memory_limit,
            'percentage' => $percentage,
            'formatted' => [
                'current' => size_format($usage),
                'peak' => size_format($peak),
                'limit' => size_format($this->memory_limit),
                'percentage' => round($percentage * 100, 2) . '%'
            ]
        ];
    }
    
    /**
     * メモリ警告処理
     *
     * @param int   $usage メモリ使用量
     * @param float $percentage 使用率
     */
    private function handle_memory_warning($usage, $percentage) {
        // エラーログ出力
        if (WP_DEBUG && WP_DEBUG_LOG) {
            error_log(sprintf(
                '[Memory Warning] Using %s of %s (%.1f%%) - Request: %s',
                size_format($usage),
                size_format($this->memory_limit),
                $percentage * 100,
                $_SERVER['REQUEST_URI'] ?? 'Unknown'
            ));
        } else {
            error_log('[Memory Warning] High memory usage detected in ' . __CLASS__);
        }
        
        // ガベージコレクション実行
        if (function_exists('gc_collect_cycles')) {
            $collected = gc_collect_cycles();
            if ($collected > 0) {
                if (WP_DEBUG && WP_DEBUG_LOG) {
                    error_log(sprintf('[Memory Manager] Garbage collected %d cycles', $collected));
                } else {
                    error_log('[Memory Manager] Garbage collection executed in ' . __CLASS__);
                }
            }
        }
        
        // 緊急事態（95%以上）の場合の追加処理
        if ($percentage > 0.95) {
            $this->emergency_memory_cleanup();
        }
    }
    
    /**
     * 緊急メモリクリーンアップ
     */
    private function emergency_memory_cleanup() {
        // オブジェクトキャッシュをクリア（利用可能な場合）
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
        
        // グローバル変数のクリーンアップ
        global $wp_object_cache, $wp_query;
        
        if (isset($wp_object_cache) && method_exists($wp_object_cache, 'flush')) {
            $wp_object_cache->flush();
        }
        
        if (WP_DEBUG && WP_DEBUG_LOG) {
            error_log('[Memory Manager] Emergency cleanup executed');
        } else {
            error_log('Emergency cleanup executed in ' . __CLASS__);
        }
    }
    
    /**
     * メモリ制限取得
     *
     * @return int メモリ制限値（バイト）
     */
    private function get_memory_limit() {
        $memory_limit = ini_get('memory_limit');
        
        if ($memory_limit === '-1') {
            return PHP_INT_MAX; // 制限なし
        }
        
        if (preg_match('/^(\d+)(.)$/', $memory_limit, $matches)) {
            $value = (int)$matches[1];
            switch (strtoupper($matches[2])) {
                case 'G':
                    $value *= 1024;
                    break;
                case 'M':
                    $value *= 1024;
                    break;
                case 'K':
                    $value *= 1024;
                    break;
            }
            return $value;
        }
        
        return 134217728; // デフォルト128MB
    }
    
    /**
     * 早期メモリ使用量チェック（wp_loadedフック用）
     */
    public function check_early_memory_usage() {
        $stats = $this->check_memory_usage();
        
        if ($stats['percentage'] > 0.5) { // 50%超で注意ログ
            if (WP_DEBUG && WP_DEBUG_LOG) {
                error_log(sprintf(
                    '[Memory Info] Early usage: %s (%.1f%%) - Page: %s',
                    $stats['formatted']['current'],
                    $stats['percentage'] * 100,
                    get_the_title() ?: $_SERVER['REQUEST_URI'] ?? 'Unknown'
                ));
            } else {
                error_log('[Memory Manager] High early memory usage detected in ' . __CLASS__);
            }
        }
    }
    
    /**
     * メモリ使用ログ（shutdownフック用）
     */
    public function log_memory_usage() {
        if (WP_DEBUG && WP_DEBUG_LOG) {
            $stats = $this->check_memory_usage();
            
            error_log(sprintf(
                '[Memory Usage] Current: %s, Peak: %s, Limit: %s (%.1f%%) - Page: %s',
                $stats['formatted']['current'],
                $stats['formatted']['peak'],
                $stats['formatted']['limit'],
                $stats['percentage'] * 100,
                $_SERVER['REQUEST_URI'] ?? 'Unknown'
            ));
        }
    }
    
    /**
     * デバッグ情報をHTMLヘッダーに追加
     */
    public function add_memory_debug_info() {
        if (!current_user_can('manage_options')) {
            return; // 管理者のみ
        }
        
        $stats = $this->check_memory_usage();
        
        echo sprintf(
            '<!-- Memory Debug Info: Current: %s, Peak: %s, Usage: %s -->%s',
            $stats['formatted']['current'],
            $stats['formatted']['peak'],
            $stats['formatted']['percentage'],
            PHP_EOL
        );
    }
    
    /**
     * メモリ統計をAPIとして取得
     *
     * @return array フォーマット済みメモリ統計
     */
    public function get_formatted_stats() {
        $stats = $this->check_memory_usage();
        return $stats['formatted'];
    }
    
    /**
     * メモリ制限の健全性チェック
     *
     * @return bool メモリ制限が適切かどうか
     */
    public function is_memory_limit_healthy() {
        $recommended_limit = 256 * 1024 * 1024; // 256MB推奨
        return $this->memory_limit >= $recommended_limit;
    }
    
    /**
     * 推奨メモリ制限値を取得
     *
     * @return string 推奨制限値の文字列表現
     */
    public function get_recommended_memory_limit() {
        return '256M';
    }
}
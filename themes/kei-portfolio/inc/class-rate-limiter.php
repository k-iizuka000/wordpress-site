<?php
/**
 * Rate Limiter Class
 * 高度なレート制限機能を提供
 *
 * @package KeiPortfolio
 * @version 1.0
 */

namespace KeiPortfolio\Security;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RateLimiter {
    
    /**
     * シングルトンインスタンス
     */
    private static $instance = null;
    
    /**
     * セキュリティロガー
     */
    private $logger;
    
    /**
     * デフォルト設定
     */
    private $default_settings = array(
        'window' => 60,           // 時間窓（秒）
        'limit' => 10,            // 制限回数
        'block_duration' => 300,  // ブロック期間（秒）
        'progressive_delay' => true // 段階的遅延
    );
    
    /**
     * コンストラクタ
     */
    private function __construct() {
        $this->logger = SecurityLogger::get_instance();
    }
    
    /**
     * シングルトンインスタンスを取得
     * 
     * @return RateLimiter
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * レート制限をチェック
     * 
     * @param string $action アクション名
     * @param array $settings 設定（オプション）
     * @param string $identifier 識別子（デフォルト：IP）
     * @return bool 制限内かどうか
     */
    public function check($action, $settings = array(), $identifier = null) {
        $settings = array_merge($this->default_settings, $settings);
        
        if ($identifier === null) {
            $identifier = $this->get_client_identifier();
        }
        
        $key = $this->generate_key($action, $identifier);
        
        // 現在のブロック状態をチェック
        if ($this->is_blocked($key, $settings)) {
            $this->log_rate_limit_exceeded($action, $identifier, 'blocked');
            return false;
        }
        
        // 現在のリクエスト数を取得
        $current_count = $this->get_current_count($key, $settings['window']);
        
        // 制限を超えた場合
        if ($current_count >= $settings['limit']) {
            $this->handle_rate_limit_exceeded($key, $action, $identifier, $current_count, $settings);
            return false;
        }
        
        // カウントを増加
        $this->increment_count($key, $settings['window']);
        
        // 段階的遅延の適用
        if ($settings['progressive_delay']) {
            $this->apply_progressive_delay($current_count, $settings['limit']);
        }
        
        return true;
    }
    
    /**
     * 特定のアクションに対するレート制限を設定
     * 
     * @param string $action アクション名
     * @param array $settings 設定
     */
    public function set_action_limits($action, $settings) {
        $option_key = 'rate_limiter_' . $action;
        update_option($option_key, $settings);
    }
    
    /**
     * アクション固有の設定を取得
     * 
     * @param string $action アクション名
     * @return array 設定
     */
    private function get_action_settings($action) {
        $option_key = 'rate_limiter_' . $action;
        $saved_settings = get_option($option_key, array());
        return array_merge($this->default_settings, $saved_settings);
    }
    
    /**
     * クライアント識別子を生成
     * 
     * @return string 識別子
     */
    private function get_client_identifier() {
        $ip = $this->get_client_ip();
        $user_id = get_current_user_id();
        $session_id = session_id();
        
        // ユーザーがログインしている場合はユーザーIDも含める
        if ($user_id > 0) {
            return hash('sha256', $ip . '_' . $user_id . '_' . wp_salt());
        }
        
        // セッションIDが利用可能な場合は含める
        if (!empty($session_id)) {
            return hash('sha256', $ip . '_' . $session_id . '_' . wp_salt());
        }
        
        return hash('sha256', $ip . '_' . wp_salt());
    }
    
    /**
     * キーを生成
     * 
     * @param string $action アクション名
     * @param string $identifier 識別子
     * @return string キー
     */
    private function generate_key($action, $identifier) {
        return 'rate_limit_' . $action . '_' . $identifier;
    }
    
    /**
     * ブロック状態をチェック
     * 
     * @param string $key キー
     * @param array $settings 設定
     * @return bool ブロック中かどうか
     */
    private function is_blocked($key, $settings) {
        $block_key = $key . '_blocked';
        return get_transient($block_key) !== false;
    }
    
    /**
     * 現在のカウントを取得
     * 
     * @param string $key キー
     * @param int $window 時間窓
     * @return int 現在のカウント
     */
    private function get_current_count($key, $window) {
        $count = get_transient($key);
        return ($count !== false) ? intval($count) : 0;
    }
    
    /**
     * カウントを増加
     * 
     * @param string $key キー
     * @param int $window 時間窓
     */
    private function increment_count($key, $window) {
        $current_count = $this->get_current_count($key, $window);
        set_transient($key, $current_count + 1, $window);
    }
    
    /**
     * レート制限を超えた場合の処理
     * 
     * @param string $key キー
     * @param string $action アクション名
     * @param string $identifier 識別子
     * @param int $current_count 現在のカウント
     * @param array $settings 設定
     */
    private function handle_rate_limit_exceeded($key, $action, $identifier, $current_count, $settings) {
        // ブロック状態を設定
        $block_key = $key . '_blocked';
        $block_duration = $this->calculate_block_duration($current_count, $settings);
        set_transient($block_key, true, $block_duration);
        
        // 違反回数を記録
        $violation_key = $key . '_violations';
        $violations = get_transient($violation_key) ?: 0;
        set_transient($violation_key, $violations + 1, DAY_IN_SECONDS);
        
        // ログに記録
        $this->log_rate_limit_exceeded($action, $identifier, 'exceeded', array(
            'current_count' => $current_count,
            'limit' => $settings['limit'],
            'block_duration' => $block_duration,
            'total_violations' => $violations + 1
        ));
        
        // 段階的ペナルティの適用
        if ($violations >= 5) {
            $this->apply_escalated_penalty($key, $violations);
        }
    }
    
    /**
     * ブロック期間を計算（違反回数に応じて増加）
     * 
     * @param int $current_count 現在のカウント
     * @param array $settings 設定
     * @return int ブロック期間（秒）
     */
    private function calculate_block_duration($current_count, $settings) {
        $base_duration = $settings['block_duration'];
        $excess = $current_count - $settings['limit'];
        
        // 超過した分だけ期間を延長（最大10倍まで）
        $multiplier = min(1 + ($excess * 0.5), 10);
        return intval($base_duration * $multiplier);
    }
    
    /**
     * 段階的遅延を適用
     * 
     * @param int $current_count 現在のカウント
     * @param int $limit 制限回数
     */
    private function apply_progressive_delay($current_count, $limit) {
        if ($current_count >= ($limit * 0.8)) {
            $delay = min(($current_count / $limit) * 2, 5); // 最大5秒
            usleep($delay * 1000000); // マイクロ秒単位
        }
    }
    
    /**
     * エスカレートしたペナルティを適用
     * 
     * @param string $key キー
     * @param int $violations 違反回数
     */
    private function apply_escalated_penalty($key, $violations) {
        $extended_block_key = $key . '_extended_block';
        $extended_duration = min($violations * HOUR_IN_SECONDS, DAY_IN_SECONDS);
        
        set_transient($extended_block_key, true, $extended_duration);
        
        $this->logger->warning('rate_limit_escalation', 'Extended block applied due to repeated violations', array(
            'violations' => $violations,
            'extended_duration' => $extended_duration
        ));
    }
    
    /**
     * レート制限違反をログに記録
     * 
     * @param string $action アクション名
     * @param string $identifier 識別子
     * @param string $type タイプ
     * @param array $details 詳細
     */
    private function log_rate_limit_exceeded($action, $identifier, $type, $details = array()) {
        $this->logger->warning('rate_limit_' . $type, 'Rate limit exceeded for action: ' . $action, array_merge(array(
            'action' => $action,
            'identifier_hash' => hash('sha256', $identifier),
            'type' => $type
        ), $details));
    }
    
    /**
     * レート制限をリセット
     * 
     * @param string $action アクション名
     * @param string $identifier 識別子（オプション）
     */
    public function reset($action, $identifier = null) {
        if ($identifier === null) {
            $identifier = $this->get_client_identifier();
        }
        
        $key = $this->generate_key($action, $identifier);
        
        // 各種キーをクリア
        delete_transient($key);
        delete_transient($key . '_blocked');
        delete_transient($key . '_violations');
        delete_transient($key . '_extended_block');
        
        $this->logger->info('rate_limit_reset', 'Rate limit reset for action: ' . $action, array(
            'action' => $action,
            'identifier_hash' => hash('sha256', $identifier)
        ));
    }
    
    /**
     * アクションの統計情報を取得
     * 
     * @param string $action アクション名
     * @param string $identifier 識別子（オプション）
     * @return array 統計情報
     */
    public function get_stats($action, $identifier = null) {
        if ($identifier === null) {
            $identifier = $this->get_client_identifier();
        }
        
        $key = $this->generate_key($action, $identifier);
        $settings = $this->get_action_settings($action);
        
        return array(
            'current_count' => $this->get_current_count($key, $settings['window']),
            'limit' => $settings['limit'],
            'is_blocked' => $this->is_blocked($key, $settings),
            'violations' => get_transient($key . '_violations') ?: 0,
            'remaining_requests' => max(0, $settings['limit'] - $this->get_current_count($key, $settings['window'])),
            'reset_time' => time() + $settings['window']
        );
    }
    
    /**
     * クライアントIPアドレスを取得
     * 
     * @return string IPアドレス
     */
    private function get_client_ip() {
        $ip_keys = array(
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        );
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                $ip_list = explode(',', $_SERVER[$key]);
                $ip = trim(end($ip_list));
                
                if (filter_var($ip, FILTER_VALIDATE_IP,
                    FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
        
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
    }
    
    /**
     * 全体のレート制限統計を取得（管理者用）
     * 
     * @return array 統計情報
     */
    public function get_global_stats() {
        global $wpdb;
        
        // Transientからレート制限関連のデータを収集
        $rate_limit_keys = $wpdb->get_results(
            "SELECT option_name, option_value FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_rate_limit_%' 
             AND option_name NOT LIKE '%_timeout'"
        );
        
        $stats = array(
            'active_limits' => 0,
            'blocked_clients' => 0,
            'total_violations' => 0,
            'top_actions' => array()
        );
        
        $action_counts = array();
        
        foreach ($rate_limit_keys as $key_data) {
            $key_name = str_replace('_transient_', '', $key_data->option_name);
            
            if (strpos($key_name, '_blocked') !== false) {
                $stats['blocked_clients']++;
            } elseif (strpos($key_name, '_violations') !== false) {
                $stats['total_violations'] += intval($key_data->option_value);
            } else {
                $stats['active_limits']++;
                
                // アクション名を抽出
                if (preg_match('/rate_limit_([^_]+)_/', $key_name, $matches)) {
                    $action = $matches[1];
                    $action_counts[$action] = ($action_counts[$action] ?? 0) + 1;
                }
            }
        }
        
        // 上位5アクションを設定
        arsort($action_counts);
        $stats['top_actions'] = array_slice($action_counts, 0, 5, true);
        
        return $stats;
    }
}
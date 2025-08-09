<?php
/**
 * Security Logger Class
 * セキュリティイベントのログ管理クラス
 *
 * @package KeiPortfolio
 * @version 1.0
 */

namespace KeiPortfolio\Security;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class SecurityLogger {
    
    /**
     * シングルトンインスタンス
     */
    private static $instance = null;
    
    /**
     * ログファイルのパス
     */
    private $log_file;
    
    /**
     * ログレベル
     */
    const LOG_LEVELS = array(
        'info' => 1,
        'warning' => 2,
        'error' => 3,
        'critical' => 4
    );
    
    /**
     * コンストラクタ
     */
    private function __construct() {
        $this->log_file = WP_CONTENT_DIR . '/security.log';
        $this->init_log_file();
    }
    
    /**
     * シングルトンインスタンスを取得
     * 
     * @return SecurityLogger
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * ログファイルの初期化
     */
    private function init_log_file() {
        // ログディレクトリが存在しない場合は作成
        $log_dir = dirname($this->log_file);
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
        }
        
        // ログファイルが存在しない場合は作成
        if (!file_exists($this->log_file)) {
            touch($this->log_file);
        }
        
        // .htaccess でログファイルへの直接アクセスを拒否
        $htaccess_file = $log_dir . '/.htaccess';
        if (!file_exists($htaccess_file)) {
            $htaccess_content = "deny from all\n<Files \"*.log\">\n    deny from all\n</Files>";
            file_put_contents($htaccess_file, $htaccess_content);
        }
    }
    
    /**
     * セキュリティイベントをログに記録
     * 
     * @param string $event_type イベントタイプ
     * @param string $message メッセージ
     * @param string $level ログレベル（info, warning, error, critical）
     * @param array $details 詳細情報
     */
    public function log($event_type, $message, $level = 'info', $details = array()) {
        if (!$this->is_log_level_enabled($level)) {
            return;
        }
        
        $log_entry = array(
            'timestamp' => current_time('mysql'),
            'level' => $level,
            'event_type' => $event_type,
            'message' => $message,
            'ip_hash' => hash('sha256', $this->get_client_ip() . wp_salt()),
            'user_id' => get_current_user_id(),
            'user_agent' => $this->get_safe_user_agent(),
            'request_uri' => $this->get_safe_request_uri(),
            'referer' => $this->get_safe_referer(),
            'details' => $details
        );
        
        $this->write_log_entry($log_entry);
        
        // 重要度の高いイベントの場合は管理者に通知
        if (in_array($level, array('error', 'critical'))) {
            $this->notify_admin($log_entry);
        }
        
        // ログローテーション
        $this->rotate_log_if_needed();
    }
    
    /**
     * 情報レベルでログ記録
     * 
     * @param string $event_type イベントタイプ
     * @param string $message メッセージ
     * @param array $details 詳細情報
     */
    public function info($event_type, $message, $details = array()) {
        $this->log($event_type, $message, 'info', $details);
    }
    
    /**
     * 警告レベルでログ記録
     * 
     * @param string $event_type イベントタイプ
     * @param string $message メッセージ
     * @param array $details 詳細情報
     */
    public function warning($event_type, $message, $details = array()) {
        $this->log($event_type, $message, 'warning', $details);
    }
    
    /**
     * エラーレベルでログ記録
     * 
     * @param string $event_type イベントタイプ
     * @param string $message メッセージ
     * @param array $details 詳細情報
     */
    public function error($event_type, $message, $details = array()) {
        $this->log($event_type, $message, 'error', $details);
    }
    
    /**
     * 重要レベルでログ記録
     * 
     * @param string $event_type イベントタイプ
     * @param string $message メッセージ
     * @param array $details 詳細情報
     */
    public function critical($event_type, $message, $details = array()) {
        $this->log($event_type, $message, 'critical', $details);
    }
    
    /**
     * ログレベルが有効かチェック
     * 
     * @param string $level ログレベル
     * @return bool
     */
    private function is_log_level_enabled($level) {
        $min_level = get_option('security_log_level', 'info');
        return self::LOG_LEVELS[$level] >= self::LOG_LEVELS[$min_level];
    }
    
    /**
     * ログエントリをファイルに書き込み
     * 
     * @param array $log_entry ログエントリ
     */
    private function write_log_entry($log_entry) {
        $log_line = date('[Y-m-d H:i:s] ') . json_encode($log_entry, JSON_UNESCAPED_UNICODE) . PHP_EOL;
        
        if (is_writable($this->log_file)) {
            error_log($log_line, 3, $this->log_file);
        } else {
            // ログファイルに書き込めない場合はPHPエラーログに記録
            error_log('Security Log (file not writable): ' . $log_line);
        }
    }
    
    /**
     * 管理者に重要イベントを通知
     * 
     * @param array $log_entry ログエントリ
     */
    private function notify_admin($log_entry) {
        // 短時間での重複通知を防ぐ
        $notification_key = 'security_notification_' . $log_entry['event_type'] . '_' . date('Y-m-d-H');
        if (get_transient($notification_key)) {
            return;
        }
        
        $admin_email = get_option('admin_email');
        if (!$admin_email) {
            return;
        }
        
        $subject = sprintf(
            '[%s] セキュリティアラート: %s',
            get_bloginfo('name'),
            $log_entry['event_type']
        );
        
        $message = sprintf(
            "重要なセキュリティイベントが発生しました。\n\n"
            . "レベル: %s\n"
            . "イベントタイプ: %s\n"
            . "メッセージ: %s\n"
            . "時刻: %s\n"
            . "IP (ハッシュ化): %s\n"
            . "リクエストURI: %s\n"
            . "詳細: %s\n\n"
            . "必要に応じて適切なセキュリティ対策を実施してください。",
            strtoupper($log_entry['level']),
            $log_entry['event_type'],
            $log_entry['message'],
            $log_entry['timestamp'],
            $log_entry['ip_hash'],
            $log_entry['request_uri'],
            json_encode($log_entry['details'], JSON_UNESCAPED_UNICODE)
        );
        
        wp_mail($admin_email, $subject, $message);
        
        // 1時間に1回のみ通知
        set_transient($notification_key, true, HOUR_IN_SECONDS);
    }
    
    /**
     * ログローテーション
     */
    private function rotate_log_if_needed() {
        if (!file_exists($this->log_file)) {
            return;
        }
        
        $max_size = 10 * 1024 * 1024; // 10MB
        if (filesize($this->log_file) > $max_size) {
            $backup_file = $this->log_file . '.' . date('Y-m-d-H-i-s');
            rename($this->log_file, $backup_file);
            
            // 古いバックアップファイルを削除（30日以上古い）
            $this->cleanup_old_logs();
        }
    }
    
    /**
     * 古いログファイルをクリーンアップ
     */
    private function cleanup_old_logs() {
        $log_dir = dirname($this->log_file);
        $files = glob($log_dir . '/security.log.*');
        $cutoff_time = time() - (30 * 24 * 60 * 60); // 30日前
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff_time) {
                unlink($file);
            }
        }
    }
    
    /**
     * 安全なクライアントIP取得
     * 
     * @return string
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
     * 安全なUser-Agent取得
     * 
     * @return string
     */
    private function get_safe_user_agent() {
        return isset($_SERVER['HTTP_USER_AGENT']) 
            ? substr(sanitize_text_field($_SERVER['HTTP_USER_AGENT']), 0, 255) 
            : '';
    }
    
    /**
     * 安全なRequest URI取得
     * 
     * @return string
     */
    private function get_safe_request_uri() {
        return isset($_SERVER['REQUEST_URI']) 
            ? substr(sanitize_text_field($_SERVER['REQUEST_URI']), 0, 255) 
            : '';
    }
    
    /**
     * 安全なReferer取得
     * 
     * @return string
     */
    private function get_safe_referer() {
        return isset($_SERVER['HTTP_REFERER']) 
            ? substr(sanitize_text_field($_SERVER['HTTP_REFERER']), 0, 255) 
            : '';
    }
    
    /**
     * ログの検索・フィルタリング
     * 
     * @param array $filters フィルタ条件
     * @param int $limit 取得件数
     * @return array ログエントリの配列
     */
    public function get_logs($filters = array(), $limit = 100) {
        if (!file_exists($this->log_file) || !is_readable($this->log_file)) {
            return array();
        }
        
        $logs = array();
        $handle = fopen($this->log_file, 'r');
        
        if ($handle) {
            while (($line = fgets($handle)) !== false && count($logs) < $limit) {
                $log_data = $this->parse_log_line($line);
                if ($log_data && $this->matches_filters($log_data, $filters)) {
                    $logs[] = $log_data;
                }
            }
            fclose($handle);
        }
        
        return array_reverse($logs); // 最新が先頭になるように
    }
    
    /**
     * ログ行をパース
     * 
     * @param string $line ログ行
     * @return array|false パースされたログデータ
     */
    private function parse_log_line($line) {
        $pattern = '/\[([^\]]+)\] (.+)$/';
        if (preg_match($pattern, trim($line), $matches)) {
            $timestamp = $matches[1];
            $json_data = $matches[2];
            $data = json_decode($json_data, true);
            
            if ($data !== null) {
                return $data;
            }
        }
        return false;
    }
    
    /**
     * フィルタ条件にマッチするかチェック
     * 
     * @param array $log_data ログデータ
     * @param array $filters フィルタ条件
     * @return bool
     */
    private function matches_filters($log_data, $filters) {
        foreach ($filters as $key => $value) {
            if (isset($log_data[$key]) && $log_data[$key] !== $value) {
                return false;
            }
        }
        return true;
    }
}
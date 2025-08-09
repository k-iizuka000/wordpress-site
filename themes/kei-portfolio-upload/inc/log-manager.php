<?php
/**
 * ログ管理クラス - サイズ制限とローテーション機能付き
 * 
 * @package Kei_Portfolio
 * @since 1.0.0
 */

// 直接アクセスを防ぐ
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Log Manager Class
 * 
 * ログファイルのサイズ制限とローテーション機能を提供
 */
class Secure_Log_Manager {
    
    /**
     * シングルトンインスタンス
     */
    private static $instance = null;
    
    /**
     * ログファイルの最大サイズ（バイト）
     */
    private $max_log_size = 10485760; // 10MB
    
    /**
     * 保持するログファイルの最大数
     */
    private $max_log_files = 5;
    
    /**
     * ログディレクトリ
     */
    private $log_directory;
    
    /**
     * シングルトンインスタンスを取得
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
        $this->log_directory = WP_CONTENT_DIR;
    }
    
    /**
     * ログの安全な書き込み
     */
    public function write_log($filename, $message, $context = array()) {
        // ファイル名の正規化
        $filename = $this->sanitize_filename($filename);
        if (!$filename) {
            return false;
        }
        
        $log_file = $this->log_directory . '/' . $filename;
        
        // ファイルサイズチェックとローテーション
        $this->rotate_log_if_needed($log_file);
        
        // ログメッセージの作成
        $log_entry = $this->format_log_message($message, $context);
        
        // 排他制御でファイルに書き込み
        return $this->safe_write_to_file($log_file, $log_entry);
    }
    
    /**
     * ファイル名のサニタイズ
     */
    private function sanitize_filename($filename) {
        // 危険な文字を除去
        $filename = preg_replace('/[^a-zA-Z0-9\-_\.]/', '', $filename);
        
        // .log拡張子がない場合は追加
        if (!preg_match('/\.log$/', $filename)) {
            $filename .= '.log';
        }
        
        // 相対パスや親ディレクトリへの参照を防ぐ
        $filename = basename($filename);
        
        return $filename;
    }
    
    /**
     * ログローテーションの実行
     */
    private function rotate_log_if_needed($log_file) {
        if (!file_exists($log_file)) {
            return;
        }
        
        $file_size = filesize($log_file);
        
        if ($file_size >= $this->max_log_size) {
            $this->rotate_log_files($log_file);
        }
    }
    
    /**
     * ログファイルのローテーション実行
     */
    private function rotate_log_files($log_file) {
        $base_path = $log_file;
        
        // 古いローテーションファイルを削除
        for ($i = $this->max_log_files; $i >= 1; $i--) {
            $old_file = $base_path . '.' . $i;
            $new_file = $base_path . '.' . ($i + 1);
            
            if ($i >= $this->max_log_files) {
                // 最大数を超えるファイルは削除
                if (file_exists($old_file)) {
                    unlink($old_file);
                }
            } else {
                // ファイルをリネーム
                if (file_exists($old_file)) {
                    rename($old_file, $new_file);
                }
            }
        }
        
        // 現在のログファイルを .1 にリネーム
        if (file_exists($base_path)) {
            rename($base_path, $base_path . '.1');
        }
    }
    
    /**
     * ログメッセージのフォーマット
     */
    private function format_log_message($message, $context = array()) {
        $timestamp = date('Y-m-d H:i:s');
        
        // 機密情報を除去
        $safe_message = $this->sanitize_log_message($message);
        
        $log_entry = sprintf(
            "[%s] %s",
            $timestamp,
            $safe_message
        );
        
        // コンテキスト情報を追加
        if (!empty($context)) {
            $safe_context = $this->sanitize_context($context);
            $log_entry .= ' | Context: ' . json_encode($safe_context);
        }
        
        return $log_entry . PHP_EOL;
    }
    
    /**
     * ログメッセージの機密情報除去
     */
    private function sanitize_log_message($message) {
        // フルパスを除去
        $message = str_replace('/Users/kei/work/wordpress-site', '[PROJECT_ROOT]', $message);
        $message = str_replace('/var/www/html', '[WEB_ROOT]', $message);
        
        // 機密情報パターンをマスク
        $patterns = array(
            '/password[\'"\s]*[:=][\'"\s]*[^\s\'"]+/i' => 'password=[REDACTED]',
            '/token[\'"\s]*[:=][\'"\s]*[^\s\'"]+/i' => 'token=[REDACTED]',
            '/api[_\s]*key[\'"\s]*[:=][\'"\s]*[^\s\'"]+/i' => 'api_key=[REDACTED]',
            '/secret[\'"\s]*[:=][\'"\s]*[^\s\'"]+/i' => 'secret=[REDACTED]',
        );
        
        foreach ($patterns as $pattern => $replacement) {
            $message = preg_replace($pattern, $replacement, $message);
        }
        
        return $message;
    }
    
    /**
     * コンテキスト情報のサニタイズ
     */
    private function sanitize_context($context) {
        $safe_context = array();
        $sensitive_keys = array('password', 'token', 'secret', 'key', 'auth', 'credential');
        
        foreach ($context as $key => $value) {
            $lower_key = strtolower($key);
            $is_sensitive = false;
            
            foreach ($sensitive_keys as $sensitive_key) {
                if (strpos($lower_key, $sensitive_key) !== false) {
                    $is_sensitive = true;
                    break;
                }
            }
            
            if ($is_sensitive) {
                $safe_context[$key] = '[REDACTED]';
            } else {
                // 値もサニタイズ
                if (is_string($value)) {
                    $safe_context[$key] = $this->sanitize_log_message($value);
                } else {
                    $safe_context[$key] = $value;
                }
            }
        }
        
        return $safe_context;
    }
    
    /**
     * ファイルへの安全な書き込み
     */
    private function safe_write_to_file($log_file, $content) {
        // ディレクトリの存在確認
        $log_dir = dirname($log_file);
        if (!is_dir($log_dir)) {
            if (!wp_mkdir_p($log_dir)) {
                return false;
            }
        }
        
        // 排他制御でファイルに書き込み
        $result = file_put_contents($log_file, $content, FILE_APPEND | LOCK_EX);
        
        if ($result !== false) {
            // ファイルパーミッションを設定
            chmod($log_file, 0644);
            return true;
        }
        
        return false;
    }
    
    /**
     * ログファイルのクリーンアップ
     */
    public function cleanup_old_logs($days = 30) {
        $cutoff_time = time() - ($days * 24 * 60 * 60);
        
        $log_files = glob($this->log_directory . '/*.log*');
        
        foreach ($log_files as $log_file) {
            if (is_file($log_file) && filemtime($log_file) < $cutoff_time) {
                unlink($log_file);
            }
        }
    }
    
    /**
     * ログファイルの統計情報を取得
     */
    public function get_log_stats($filename) {
        $filename = $this->sanitize_filename($filename);
        $log_file = $this->log_directory . '/' . $filename;
        
        if (!file_exists($log_file)) {
            return null;
        }
        
        return array(
            'file_size' => filesize($log_file),
            'max_size' => $this->max_log_size,
            'last_modified' => filemtime($log_file),
            'lines' => $this->count_log_lines($log_file)
        );
    }
    
    /**
     * ログファイルの行数をカウント
     */
    private function count_log_lines($log_file) {
        $line_count = 0;
        
        if ($handle = fopen($log_file, 'r')) {
            while (fgets($handle) !== false) {
                $line_count++;
            }
            fclose($handle);
        }
        
        return $line_count;
    }
}

/**
 * グローバル関数：安全なログ書き込み
 */
function secure_write_log($filename, $message, $context = array()) {
    $manager = Secure_Log_Manager::get_instance();
    return $manager->write_log($filename, $message, $context);
}

/**
 * グローバル関数：ログクリーンアップ
 */
function cleanup_security_logs() {
    $manager = Secure_Log_Manager::get_instance();
    $manager->cleanup_old_logs(30);
}

// 定期的なクリーンアップ（1日1回）
add_action('wp_scheduled_delete', 'cleanup_security_logs');
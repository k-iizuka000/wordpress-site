<?php
/**
 * Blog Logger Class
 * 統一されたエラーログユーティリティクラス
 * 
 * @package KeiPortfolio
 * @since 1.0.0
 */

namespace KeiPortfolio\Blog;

/**
 * ブログ機能のための統一されたログ管理クラス
 */
class Blog_Logger {
    
    /**
     * シングルトンインスタンス
     * @var Blog_Logger|null
     */
    private static $instance = null;
    
    /**
     * ログレベル定数
     */
    const LEVEL_DEBUG = 'DEBUG';
    const LEVEL_INFO = 'INFO';
    const LEVEL_WARNING = 'WARNING';
    const LEVEL_ERROR = 'ERROR';
    const LEVEL_CRITICAL = 'CRITICAL';
    
    /**
     * ログファイルのパス
     * @var string
     */
    private $log_directory;
    
    /**
     * ログファイルの最大サイズ（バイト）
     * @var int
     */
    private $max_log_size = 5242880; // 5MB
    
    /**
     * ログローテーション設定
     * @var int
     */
    private $max_log_files = 5;
    
    /**
     * ログ有効/無効フラグ
     * @var bool
     */
    private $logging_enabled;
    
    /**
     * デバッグモード
     * @var bool
     */
    private $debug_mode;
    
    /**
     * シングルトンパターンでインスタンス取得
     * 
     * @return Blog_Logger インスタンス
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
        $this->init_logger();
        $this->setup_hooks();
    }
    
    /**
     * ログシステムの初期化
     * 
     * @return void
     */
    private function init_logger() {
        // WordPress環境に応じた設定
        $this->debug_mode = defined('WP_DEBUG') && WP_DEBUG;
        $this->logging_enabled = $this->debug_mode || $this->is_logging_enabled();
        
        // ログディレクトリの設定
        $upload_dir = wp_upload_dir();
        $this->log_directory = trailingslashit($upload_dir['basedir']) . 'blog-logs/';
        
        // ログディレクトリの作成
        if (!file_exists($this->log_directory)) {
            wp_mkdir_p($this->log_directory);
            
            // セキュリティ：.htaccessファイルを作成してアクセスを制限
            $htaccess_content = "# Blog Logger Security\nDeny from all";
            file_put_contents($this->log_directory . '.htaccess', $htaccess_content);
            
            // index.phpファイルを作成してディレクトリリスティングを防止
            file_put_contents($this->log_directory . 'index.php', '<?php // Silence is golden');
        }
    }
    
    /**
     * WordPressフックの設定
     * 
     * @return void
     */
    private function setup_hooks() {
        // WordPressのシャットダウンアクションでPHPエラーをキャッチ
        add_action('shutdown', [$this, 'catch_fatal_errors']);
        
        // WordPressエラーハンドリング
        add_action('wp_die_handler', [$this, 'log_wp_die']);
        
        // 定期的なログファイルクリーンアップ
        add_action('wp_scheduled_delete', [$this, 'cleanup_old_logs']);
        
        // AJAX エラーログ
        add_action('wp_ajax_nopriv_blog_error_log', [$this, 'handle_ajax_error_log']);
        add_action('wp_ajax_blog_error_log', [$this, 'handle_ajax_error_log']);
    }
    
    /**
     * ログの有効性チェック
     * 
     * @return bool ログが有効かどうか
     */
    private function is_logging_enabled() {
        // 本番環境では重要なエラーのみ記録
        if (defined('WP_ENVIRONMENT_TYPE')) {
            return WP_ENVIRONMENT_TYPE !== 'production' || get_option('blog_enable_error_logging', false);
        }
        
        return true;
    }
    
    /**
     * デバッグログの記録
     * 
     * @param mixed $message ログメッセージ
     * @param array $context 追加のコンテキスト情報
     * @return bool ログ記録の成功/失敗
     */
    public function debug($message, array $context = []) {
        if (!$this->debug_mode) {
            return false;
        }
        
        return $this->log(self::LEVEL_DEBUG, $message, $context);
    }
    
    /**
     * 情報ログの記録
     * 
     * @param mixed $message ログメッセージ
     * @param array $context 追加のコンテキスト情報
     * @return bool ログ記録の成功/失敗
     */
    public function info($message, array $context = []) {
        return $this->log(self::LEVEL_INFO, $message, $context);
    }
    
    /**
     * 警告ログの記録
     * 
     * @param mixed $message ログメッセージ
     * @param array $context 追加のコンテキスト情報
     * @return bool ログ記録の成功/失敗
     */
    public function warning($message, array $context = []) {
        return $this->log(self::LEVEL_WARNING, $message, $context);
    }
    
    /**
     * エラーログの記録
     * 
     * @param mixed $message ログメッセージ
     * @param array $context 追加のコンテキスト情報
     * @return bool ログ記録の成功/失敗
     */
    public function error($message, array $context = []) {
        $success = $this->log(self::LEVEL_ERROR, $message, $context);
        
        // 重要なエラーの場合は管理者に通知（本番環境のみ）
        if (!$this->debug_mode && $success) {
            $this->notify_admin_on_critical_error($message, $context);
        }
        
        return $success;
    }
    
    /**
     * クリティカルエラーログの記録
     * 
     * @param mixed $message ログメッセージ
     * @param array $context 追加のコンテキスト情報
     * @return bool ログ記録の成功/失敗
     */
    public function critical($message, array $context = []) {
        $success = $this->log(self::LEVEL_CRITICAL, $message, $context);
        
        // クリティカルエラーは常に管理者に通知
        if ($success) {
            $this->notify_admin_on_critical_error($message, $context);
        }
        
        return $success;
    }
    
    /**
     * メインのログ記録メソッド
     * 
     * @param string $level ログレベル
     * @param mixed $message ログメッセージ
     * @param array $context 追加のコンテキスト情報
     * @return bool ログ記録の成功/失敗
     */
    private function log($level, $message, array $context = []) {
        if (!$this->logging_enabled) {
            return false;
        }
        
        try {
            // メッセージの整形
            $formatted_message = $this->format_message($level, $message, $context);
            
            // ログファイルの決定
            $log_file = $this->get_log_file($level);
            
            // ログローテーション
            $this->rotate_log_if_needed($log_file);
            
            // ファイルに書き込み
            $bytes_written = file_put_contents(
                $log_file,
                $formatted_message . PHP_EOL,
                FILE_APPEND | LOCK_EX
            );
            
            return $bytes_written !== false;
            
        } catch (Exception $e) {
            // ログ記録でエラーが発生した場合はWordPressのerror_logに記録
            error_log('Blog_Logger error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * ログメッセージのフォーマット
     * 
     * @param string $level ログレベル
     * @param mixed $message メッセージ
     * @param array $context コンテキスト
     * @return string フォーマット済みメッセージ
     */
    private function format_message($level, $message, array $context = []) {
        $timestamp = current_time('Y-m-d H:i:s');
        $request_id = $this->get_request_id();
        $user_id = get_current_user_id();
        $ip_address = $this->get_client_ip();
        
        // メッセージの文字列化
        if (is_array($message) || is_object($message)) {
            $message = wp_json_encode($message, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
        
        // コンテキストの処理
        $context_str = '';
        if (!empty($context)) {
            $context_str = ' | Context: ' . wp_json_encode($context, JSON_UNESCAPED_UNICODE);
        }
        
        // スタックトレースの取得（エラーレベルの場合）
        $stack_trace = '';
        if (in_array($level, [self::LEVEL_ERROR, self::LEVEL_CRITICAL])) {
            $stack_trace = ' | Stack: ' . $this->get_stack_trace();
        }
        
        return sprintf(
            '[%s] [%s] [ID:%s] [User:%d] [IP:%s] %s%s%s',
            $timestamp,
            $level,
            $request_id,
            $user_id,
            $ip_address,
            $message,
            $context_str,
            $stack_trace
        );
    }
    
    /**
     * ログファイルのパス取得
     * 
     * @param string $level ログレベル
     * @return string ログファイルのパス
     */
    private function get_log_file($level) {
        $date = current_time('Y-m-d');
        $filename = strtolower($level) . '-' . $date . '.log';
        return $this->log_directory . $filename;
    }
    
    /**
     * ログローテーション
     * 
     * @param string $log_file ログファイルのパス
     * @return void
     */
    private function rotate_log_if_needed($log_file) {
        if (!file_exists($log_file) || filesize($log_file) < $this->max_log_size) {
            return;
        }
        
        // 古いローテーションファイルを削除
        for ($i = $this->max_log_files; $i > 1; $i--) {
            $old_file = $log_file . '.' . $i;
            $new_file = $log_file . '.' . ($i + 1);
            
            if (file_exists($old_file)) {
                if ($i === $this->max_log_files) {
                    unlink($old_file);
                } else {
                    rename($old_file, $new_file);
                }
            }
        }
        
        // 現在のファイルをローテーション
        rename($log_file, $log_file . '.1');
    }
    
    /**
     * リクエストIDの生成
     * 
     * @return string リクエストID
     */
    private function get_request_id() {
        static $request_id = null;
        
        if (null === $request_id) {
            $request_id = substr(md5(uniqid('', true)), 0, 8);
        }
        
        return $request_id;
    }
    
    /**
     * クライアントIPアドレスの取得
     * 
     * @return string IPアドレス
     */
    private function get_client_ip() {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) && !empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
                
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * スタックトレースの取得
     * 
     * @return string スタックトレース
     */
    private function get_stack_trace() {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
        $stack_lines = [];
        
        foreach ($trace as $item) {
            if (isset($item['file']) && isset($item['line'])) {
                $file = str_replace(ABSPATH, '', $item['file']);
                $stack_lines[] = "{$file}:{$item['line']}";
            }
        }
        
        return implode(' -> ', $stack_lines);
    }
    
    /**
     * 致命的なエラーをキャッチ
     * 
     * @return void
     */
    public function catch_fatal_errors() {
        $error = error_get_last();
        
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $this->critical('Fatal error occurred', [
                'type' => $error['type'],
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line']
            ]);
        }
    }
    
    /**
     * wp_dieをログに記録
     * 
     * @param mixed $handler エラーハンドラ
     * @return mixed
     */
    public function log_wp_die($handler) {
        $this->error('wp_die called', [
            'handler' => $handler,
            'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
        
        return $handler;
    }
    
    /**
     * 古いログファイルのクリーンアップ
     * 
     * @return void
     */
    public function cleanup_old_logs() {
        if (!is_dir($this->log_directory)) {
            return;
        }
        
        $files = glob($this->log_directory . '*.log*');
        $cutoff_time = time() - (30 * DAY_IN_SECONDS); // 30日前
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff_time) {
                unlink($file);
            }
        }
    }
    
    /**
     * AJAX エラーログハンドラ
     * 
     * @return void
     */
    public function handle_ajax_error_log() {
        // セキュリティチェック
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'blog_error_log_nonce')) {
            wp_die('Security check failed');
        }
        
        $level = sanitize_text_field($_POST['level'] ?? 'error');
        $message = sanitize_textarea_field($_POST['message'] ?? '');
        $context = $_POST['context'] ?? [];
        
        // 入力値のサニタイゼーション
        if (is_array($context)) {
            $context = array_map('sanitize_text_field', $context);
        }
        
        $this->log($level, $message, $context);
        
        wp_send_json_success('Log recorded');
    }
    
    /**
     * 管理者への通知（クリティカルエラー時）
     * 
     * @param mixed $message メッセージ
     * @param array $context コンテキスト
     * @return void
     */
    private function notify_admin_on_critical_error($message, array $context = []) {
        // 本番環境でのみ通知を送信
        if ($this->debug_mode) {
            return;
        }
        
        // メール送信の頻度制限（1時間に1回まで）
        $transient_key = 'blog_critical_error_notification';
        if (get_transient($transient_key)) {
            return;
        }
        
        set_transient($transient_key, true, HOUR_IN_SECONDS);
        
        // 管理者メールアドレス取得
        $admin_email = get_option('admin_email');
        if (empty($admin_email)) {
            return;
        }
        
        // メール内容の作成
        $subject = sprintf('[%s] Critical Blog Error', get_bloginfo('name'));
        $body = sprintf(
            "A critical error occurred on your website:\n\n" .
            "Message: %s\n" .
            "Time: %s\n" .
            "URL: %s\n" .
            "User: %s\n" .
            "Context: %s\n\n" .
            "Please check your error logs for more details.",
            is_string($message) ? $message : wp_json_encode($message),
            current_time('Y-m-d H:i:s'),
            $_SERVER['REQUEST_URI'] ?? 'Unknown',
            wp_get_current_user()->user_login ?? 'Guest',
            wp_json_encode($context)
        );
        
        // メール送信
        wp_mail($admin_email, $subject, $body);
    }
    
    /**
     * ログ設定の取得
     * 
     * @return array ログ設定
     */
    public function get_log_settings() {
        return [
            'logging_enabled' => $this->logging_enabled,
            'debug_mode' => $this->debug_mode,
            'log_directory' => $this->log_directory,
            'max_log_size' => $this->max_log_size,
            'max_log_files' => $this->max_log_files
        ];
    }
    
    /**
     * ログファイル一覧の取得
     * 
     * @return array ログファイル情報
     */
    public function get_log_files() {
        if (!is_dir($this->log_directory)) {
            return [];
        }
        
        $files = glob($this->log_directory . '*.log');
        $log_files = [];
        
        foreach ($files as $file) {
            $log_files[] = [
                'name' => basename($file),
                'size' => filesize($file),
                'modified' => filemtime($file),
                'readable' => is_readable($file)
            ];
        }
        
        return $log_files;
    }
    
    /**
     * 特定のログファイルの内容を取得
     * 
     * @param string $filename ファイル名
     * @param int $lines 取得する行数（末尾から）
     * @return string|false ログ内容またはfalse
     */
    public function get_log_content($filename, $lines = 100) {
        $file_path = $this->log_directory . basename($filename);
        
        if (!file_exists($file_path) || !is_readable($file_path)) {
            return false;
        }
        
        // セキュリティチェック：拡張子確認
        if (!preg_match('/\.log$/', $filename)) {
            return false;
        }
        
        // ファイル内容の取得（末尾から指定行数）
        $file_content = file($file_path);
        if ($file_content === false) {
            return false;
        }
        
        $content_lines = array_slice($file_content, -$lines);
        return implode('', $content_lines);
    }
    
    /**
     * ログレベルごとの統計情報取得
     * 
     * @param string $date 日付 (Y-m-d形式)
     * @return array 統計情報
     */
    public function get_log_statistics($date = null) {
        if (null === $date) {
            $date = current_time('Y-m-d');
        }
        
        $stats = [
            'debug' => 0,
            'info' => 0,
            'warning' => 0,
            'error' => 0,
            'critical' => 0
        ];
        
        foreach (array_keys($stats) as $level) {
            $log_file = $this->log_directory . $level . '-' . $date . '.log';
            if (file_exists($log_file)) {
                $stats[$level] = count(file($log_file));
            }
        }
        
        return $stats;
    }
}
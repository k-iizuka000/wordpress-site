<?php
/**
 * Portfolio Data Management Class
 * 
 * portfolio.jsonからデータを取得・管理するシングルトンクラス
 * キャッシュ機能付きでパフォーマンスを最適化
 *
 * @package Kei_Portfolio
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // セキュリティ：直接アクセスを防ぐ
}

class Portfolio_Data {
    
    /**
     * キャッシュ時間（秒）
     */
    const CACHE_DURATION = 3600; // 1時間
    
    /**
     * Transient キャッシュキー
     */
    const CACHE_KEY = 'portfolio_data_cache';
    
    /**
     * デフォルトプロジェクト表示数
     */
    const DEFAULT_PROJECT_LIMIT = 3;
    
    /**
     * シングルトンインスタンス
     * @var Portfolio_Data|null
     */
    private static $instance = null;
    
    /**
     * キャッシュされたポートフォリオデータ
     * @var array|null
     */
    private $portfolio_data = null;
    
    /**
     * portfolio.jsonファイルパス
     * @var string
     */
    private $json_file_path;
    
    /**
     * コンストラクタ（プライベート）
     */
    private function __construct() {
        $this->json_file_path = get_template_directory() . '/data/portfolio.json';
    }
    
    /**
     * インスタンス取得（シングルトンパターン）
     * 
     * @return Portfolio_Data
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * ポートフォリオデータを取得
     * 
     * @return array|WP_Error ポートフォリオデータまたはエラーオブジェクト
     */
    public function get_portfolio_data() {
        // Transient キャッシュから取得を試行
        $cached_data = get_transient(self::CACHE_KEY);
        if ($cached_data !== false) {
            $this->portfolio_data = $cached_data;
            return $cached_data;
        }
        
        // メモリキャッシュが存在する場合はそれを返す
        if ($this->portfolio_data !== null) {
            return $this->portfolio_data;
        }
        
        // ファイル存在チェック
        if (!file_exists($this->json_file_path)) {
            $error = new WP_Error(
                'file_not_found',
                'Portfolio JSONファイルが見つかりません'
            );
            $this->log_error('file_not_found', 'JSONファイルが存在しません');
            return $error;
        }
        
        // ファイル読み込み可能性チェック
        if (!is_readable($this->json_file_path)) {
            $error = new WP_Error(
                'file_not_readable',
                'Portfolio JSONファイルを読み込めません'
            );
            $this->log_error('file_not_readable', 'JSONファイルを読み込めません');
            return $error;
        }
        
        // ファイル読み込み
        $json_content = file_get_contents($this->json_file_path);
        
        if ($json_content === false) {
            $error = new WP_Error(
                'file_read_error',
                'Portfolio JSONファイルの読み込みに失敗しました'
            );
            $this->log_error('file_read_error', 'ファイルの読み込みに失敗しました');
            return $error;
        }
        
        // JSON解析
        $data = json_decode($json_content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error_msg = json_last_error_msg();
            $error = new WP_Error(
                'json_parse_error',
                'JSON解析エラー: ' . $error_msg
            );
            $this->log_error('json_parse_error', 'JSON解析に失敗: ' . $error_msg);
            return $error;
        }
        
        // データ構造の基本検証
        if (!$this->validate_portfolio_structure($data)) {
            $error = new WP_Error(
                'invalid_data_structure',
                'ポートフォリオデータの構造が無効です'
            );
            $this->log_error('invalid_data_structure', 'データ構造の検証に失敗');
            return $error;
        }
        
        // メモリキャッシュに保存
        $this->portfolio_data = $data;
        
        // Transient キャッシュに保存
        set_transient(self::CACHE_KEY, $data, self::CACHE_DURATION);
        
        // エラーログ機能追加
        $this->log_data_load_success();
        
        return $data;
    }
    
    /**
     * 自己紹介データを取得
     * 
     * @return array|WP_Error
     */
    public function get_about_data() {
        $data = $this->get_portfolio_data();
        
        if (is_wp_error($data)) {
            return $data;
        }
        
        return isset($data['about']) ? $data['about'] : array();
    }
    
    /**
     * スキルデータを取得
     * 
     * @return array|WP_Error
     */
    public function get_skills_data() {
        $data = $this->get_portfolio_data();
        
        if (is_wp_error($data)) {
            return $data;
        }
        
        return isset($data['skills']) ? $data['skills'] : array();
    }
    
    /**
     * サマリーデータを取得
     * 
     * @return array|WP_Error
     */
    public function get_summary_data() {
        $data = $this->get_portfolio_data();
        
        if (is_wp_error($data)) {
            return $data;
        }
        
        return isset($data['summary']) ? $data['summary'] : array();
    }
    
    /**
     * プロジェクトデータを取得
     * 
     * @param int $limit 取得件数制限（0で全件）
     * @return array|WP_Error
     */
    public function get_projects_data($limit = 0) {
        $data = $this->get_portfolio_data();
        
        if (is_wp_error($data)) {
            return $data;
        }
        
        $projects = isset($data['projects']) ? $data['projects'] : array();
        
        if ($limit > 0 && count($projects) > $limit) {
            return array_slice($projects, 0, $limit);
        }
        
        return $projects;
    }
    
    /**
     * 最新プロジェクトを取得（直近3件）
     * 
     * @return array|WP_Error
     */
    public function get_latest_projects() {
        return $this->get_projects_data(self::DEFAULT_PROJECT_LIMIT);
    }
    
    /**
     * 進行中プロジェクトデータを取得
     * 
     * @return array|WP_Error
     */
    public function get_in_progress_projects() {
        $data = $this->get_portfolio_data();
        
        if (is_wp_error($data)) {
            return $data;
        }
        
        return isset($data['inProgress']) ? $data['inProgress'] : array();
    }
    
    /**
     * コア技術データを取得
     * 
     * @return array|WP_Error
     */
    public function get_core_technologies() {
        $summary = $this->get_summary_data();
        
        if (is_wp_error($summary)) {
            return $summary;
        }
        
        return isset($summary['coreTechnologies']) ? $summary['coreTechnologies'] : array();
    }
    
    /**
     * データ構造の基本検証
     * 
     * @param array $data
     * @return bool
     */
    private function validate_portfolio_structure($data) {
        if (!is_array($data)) {
            return false;
        }
        
        // 必須セクションの存在チェック
        $required_sections = array('about', 'skills', 'summary', 'projects', 'inProgress');
        
        foreach ($required_sections as $section) {
            if (!isset($data[$section])) {
                return false;
            }
        }
        
        // 各セクションの基本構造チェック
        if (!is_array($data['skills']) || !is_array($data['projects']) || !is_array($data['inProgress'])) {
            return false;
        }
        
        return true;
    }
    
    /**
     * キャッシュをクリア
     */
    public function clear_cache() {
        $this->portfolio_data = null;
        delete_transient(self::CACHE_KEY);
    }
    
    /**
     * データロード成功ログ
     */
    private function log_data_load_success() {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[Portfolio Data] データロードが成功しました: ' . date('Y-m-d H:i:s'));
        }
    }
    
    /**
     * エラーログ機能
     * 
     * @param string $error_code エラーコード
     * @param string $error_message エラーメッセージ
     */
    private function log_error($error_code, $error_message) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf(
                '[Portfolio Data Error] %s: %s - %s',
                $error_code,
                $error_message,
                date('Y-m-d H:i:s')
            ));
        }
    }
    
    /**
     * エラーメッセージを安全に表示用に変換
     * 
     * @param WP_Error $error
     * @return string
     */
    public function get_error_message($error) {
        if (!is_wp_error($error)) {
            return '';
        }
        
        return esc_html($error->get_error_message());
    }
    
    /**
     * データが利用可能かチェック
     * 
     * @return bool
     */
    public function is_data_available() {
        $data = $this->get_portfolio_data();
        return !is_wp_error($data);
    }
}
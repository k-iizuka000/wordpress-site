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
     * ポートフォリオデータのファイル更新時刻キャッシュキー
     */
    const CACHE_MTIME_KEY = 'portfolio_data_mtime';
    /**
     * スキル統計キャッシュキー
     */
    const SKILLS_CACHE_KEY = 'portfolio_skills_statistics';
    /**
     * スキル統計の元データ更新時刻キャッシュキー
     */
    const SKILLS_CACHE_MTIME_KEY = 'portfolio_skills_statistics_mtime';
    
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
        // JSONの更新時刻を取得
        $current_mtime = file_exists($this->json_file_path) ? filemtime($this->json_file_path) : 0;

        // Transient キャッシュから取得を試行（mtime一致時のみ使用）
        $cached_data = get_transient(self::CACHE_KEY);
        $cached_mtime = get_transient(self::CACHE_MTIME_KEY);
        if ($cached_data !== false && $cached_mtime && (int)$cached_mtime === (int)$current_mtime) {
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
        
        // Transient キャッシュに保存（データとmtime）
        set_transient(self::CACHE_KEY, $data, self::CACHE_DURATION);
        if ($current_mtime) {
            set_transient(self::CACHE_MTIME_KEY, $current_mtime, self::CACHE_DURATION);
        }
        
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
     * スキルごとの統計情報を取得
     * - 使用回数（projects + inProgress）
     * - 使用期間（periodがあるprojectsのみ集計）
     * - 表示名、カテゴリ、レベルを付与
     *
     * @return array スキル統計情報の連想配列
     */
    public function get_skills_statistics() {
        // 元データのmtime取得
        $current_mtime = file_exists($this->json_file_path) ? filemtime($this->json_file_path) : 0;

        // キャッシュ確認（mtime一致時のみ）
        $cached = get_transient(self::SKILLS_CACHE_KEY);
        $cached_stats_mtime = get_transient(self::SKILLS_CACHE_MTIME_KEY);
        if ($cached !== false && $cached_stats_mtime && (int)$cached_stats_mtime === (int)$current_mtime) {
            return $cached;
        }

        $projects = $this->get_projects_data(0);
        $in_progress = $this->get_in_progress_projects();

        if (is_wp_error($projects)) {
            return array();
        }
        if (is_wp_error($in_progress)) {
            $in_progress = array();
        }

        $all_projects = array_merge($projects, $in_progress);

        $skill_stats = array();

        foreach ($all_projects as $project) {
            if (!isset($project['technologies'])) {
                continue;
            }

            $techs = $project['technologies'];
            if (!is_array($techs)) {
                continue;
            }

            // 期間（あれば集計対象）
            $start_ts = null;
            $end_ts = null;
            if (isset($project['period']) && is_array($project['period'])) {
                $start_ts = isset($project['period']['start']) ? strtotime($project['period']['start']) : null;
                $end_ts = isset($project['period']['end']) ? strtotime($project['period']['end']) : null;
            }

            foreach ($techs as $tech) {
                $name = null;
                if (is_string($tech)) {
                    $name = $tech;
                } elseif (is_array($tech) && isset($tech['name'])) {
                    $name = $tech['name'];
                }
                if (!$name) {
                    continue;
                }

                $normalized = $this->normalize_skill_name($name);
                if (!isset($skill_stats[$normalized])) {
                    $skill_stats[$normalized] = array(
                        'name' => $normalized,
                        'display_name' => $this->get_display_name($normalized),
                        'projects' => array(),
                        'first_used' => null,
                        'last_used' => null,
                        'usage_count' => 0,
                        'category' => $this->determine_skill_category($normalized),
                    );
                }

                $skill_stats[$normalized]['usage_count']++;

                // プロジェクトレコードを保存
                $skill_stats[$normalized]['projects'][] = array(
                    'title' => isset($project['title']) ? $project['title'] : '',
                    'start' => $start_ts,
                    'end' => $end_ts,
                );

                // 期間の最小/最大を更新（periodがある場合のみ）
                if ($start_ts && (!$skill_stats[$normalized]['first_used'] || $start_ts < $skill_stats[$normalized]['first_used'])) {
                    $skill_stats[$normalized]['first_used'] = $start_ts;
                }
                if ($end_ts && (!$skill_stats[$normalized]['last_used'] || $end_ts > $skill_stats[$normalized]['last_used'])) {
                    $skill_stats[$normalized]['last_used'] = $end_ts;
                }
            }
        }

        // 期間とレベルを計算
        foreach ($skill_stats as &$s) {
            $s['duration_years'] = $this->calculate_duration_years($s['first_used'], $s['last_used']);
            $s['level'] = $this->calculate_skill_level($s);
        }
        unset($s);

        // キャッシュ（1時間）
        set_transient(self::SKILLS_CACHE_KEY, $skill_stats, self::CACHE_DURATION);
        if ($current_mtime) {
            set_transient(self::SKILLS_CACHE_MTIME_KEY, $current_mtime, self::CACHE_DURATION);
        }

        return $skill_stats;
    }

    /**
     * スキル名を正規化
     * 例: "Java(Spring)" → "Java", "PHP(FuelPHP)" → "PHP"
     */
    private function normalize_skill_name($name) {
        if (!is_string($name)) {
            return '';
        }
        $trimmed = trim($name);
        // 括弧でのバリアントを除去
        $base = preg_replace('/\s*\(.*\)$/', '', $trimmed);
        // 一部表記揺れの標準化
        $map = array(
            'JavaScript/HTML/css' => 'JavaScript',
            'Ruby on Rails' => 'Ruby',
            'E2E' => 'e2e',
        );
        if (isset($map[$base])) {
            $base = $map[$base];
        }
        return $base;
    }

    /**
     * 表示名を取得（正規化後の人間可読名）
     */
    private function get_display_name($normalized) {
        // 必要に応じて表示名を変換
        $map = array(
            'e2e' => 'E2E',
        );
        return isset($map[$normalized]) ? $map[$normalized] : $normalized;
    }

    /**
     * 年数を計算（端数は四捨五入、期間不明は0）
     */
    private function calculate_duration_years($first_ts, $last_ts) {
        if (!$first_ts || !$last_ts || $last_ts < $first_ts) {
            return 0;
        }
        $seconds = $last_ts - $first_ts;
        $years = $seconds / (365 * 24 * 60 * 60);
        return (int) round($years);
    }

    /**
     * スキルレベルを簡易計算
     * usage_count と duration_years から算出（0-100）
     */
    private function calculate_skill_level($skill) {
        $usage = isset($skill['usage_count']) ? (int) $skill['usage_count'] : 0;
        $years = isset($skill['duration_years']) ? (int) $skill['duration_years'] : 0;
        // 重み付け: 使用回数×12 + 年数×10、上限100、最低40
        $score = ($usage * 12) + ($years * 10);
        $score = max(40, min(100, $score));
        return $score;
    }

    /**
     * スキルカテゴリを判定（frontend/backend/other）
     */
    private function determine_skill_category($name) {
        $frontend = array('JavaScript', 'Vue.js', 'React', 'HTML5', 'CSS3',  'Astro','FullCalendar');
        $backend  = array('Java', 'Spring', 'Python', 'SQL', 'Node.js', 'COBOL', 'JSP','Ruby', 'PHP', 'Maven', 'Gradle', 'JUnit','selenium_python','selenium_java','Struts2','pypika',);
        $other    = array('Git', 'AWS', 'Docker', 'SVN', 'E2E', 'AI/LLM', 'Astro', 'pay.jp API', 'Note API', 'ワークフロー自動化');

        if (in_array($name, $frontend, true)) return 'frontend';
        if (in_array($name, $backend, true)) return 'backend';
        if (in_array($name, $other, true)) return 'other';
        // 未知はotherにフォールバック
        return 'other';
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
        delete_transient(self::CACHE_MTIME_KEY);
        delete_transient(self::SKILLS_CACHE_KEY);
        delete_transient(self::SKILLS_CACHE_MTIME_KEY);
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

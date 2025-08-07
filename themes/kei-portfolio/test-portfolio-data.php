<?php
/**
 * Portfolio_Data クラスの簡易テスト
 * 
 * このファイルはテスト用です。本番環境では削除してください。
 */

// WordPressの設定を模擬
function get_template_directory() {
    return __DIR__;
}

// WP_Errorクラスの模擬
class WP_Error {
    private $code;
    private $message;
    
    public function __construct($code, $message) {
        $this->code = $code;
        $this->message = $message;
    }
    
    public function get_error_message() {
        return $this->message;
    }
}

function is_wp_error($thing) {
    return $thing instanceof WP_Error;
}

// Portfolio_Dataクラスを読み込み
require_once 'inc/class-portfolio-data.php';

echo "Portfolio Data テスト開始\n";
echo "==========================================\n";

// インスタンス取得
$portfolio = Portfolio_Data::get_instance();

// シングルトンテスト
$portfolio2 = Portfolio_Data::get_instance();
echo "シングルトンテスト: " . ($portfolio === $portfolio2 ? "OK" : "NG") . "\n";

// データ取得テスト
$data = $portfolio->get_portfolio_data();
if (is_wp_error($data)) {
    echo "エラー: " . $data->get_error_message() . "\n";
} else {
    echo "ポートフォリオデータ取得: OK\n";
}

// 各セクションのデータ取得テスト
$about = $portfolio->get_about_data();
echo "自己紹介データ取得: " . (is_wp_error($about) ? "NG - " . $about->get_error_message() : "OK") . "\n";

$skills = $portfolio->get_skills_data();
echo "スキルデータ取得: " . (is_wp_error($skills) ? "NG - " . $skills->get_error_message() : "OK") . "\n";

$summary = $portfolio->get_summary_data();
echo "サマリーデータ取得: " . (is_wp_error($summary) ? "NG - " . $summary->get_error_message() : "OK") . "\n";

$latest = $portfolio->get_latest_projects();
echo "最新プロジェクト取得: " . (is_wp_error($latest) ? "NG - " . $latest->get_error_message() : "OK (件数: " . count($latest) . ")") . "\n";

$inProgress = $portfolio->get_in_progress_projects();
echo "進行中プロジェクト取得: " . (is_wp_error($inProgress) ? "NG - " . $inProgress->get_error_message() : "OK (件数: " . count($inProgress) . ")") . "\n";

$coreTech = $portfolio->get_core_technologies();
echo "コア技術データ取得: " . (is_wp_error($coreTech) ? "NG - " . $coreTech->get_error_message() : "OK (件数: " . count($coreTech) . ")") . "\n";

// データ可用性テスト
echo "データ利用可能性: " . ($portfolio->is_data_available() ? "OK" : "NG") . "\n";

echo "==========================================\n";
echo "テスト完了\n";
?>
<?php
/**
 * SecureSessionクラス簡易テストスクリプト
 * 
 * コマンドラインから実行可能なテストファイル
 */

// エラー報告レベルを設定
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== SecureSession クラステスト開始 ===\n\n";

// 基本的なWordPress定数を定義（テスト用）
if (!defined('COOKIEPATH')) {
    define('COOKIEPATH', '/');
}
if (!defined('COOKIE_DOMAIN')) {
    define('COOKIE_DOMAIN', '');
}

// WordPress関数のモック
if (!function_exists('is_ssl')) {
    function is_ssl() {
        return false;
    }
}

if (!function_exists('wp_generate_password')) {
    function wp_generate_password($length, $special_chars = true) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        if ($special_chars) {
            $chars .= '!@#$%^&*()';
        }
        return substr(str_shuffle($chars), 0, $length);
    }
}

// ログ出力のモック
if (!function_exists('error_log')) {
    function error_log($message) {
        echo "[LOG] " . $message . "\n";
    }
}

$test_results = [];
$test_count = 0;
$passed_count = 0;

/**
 * テスト実行ヘルパー関数
 */
function run_test($test_name, $test_function) {
    global $test_results, $test_count, $passed_count;
    
    $test_count++;
    echo "テスト {$test_count}: {$test_name} ... ";
    
    try {
        $result = $test_function();
        if ($result) {
            echo "PASS\n";
            $passed_count++;
            $test_results[] = ['name' => $test_name, 'status' => 'PASS'];
        } else {
            echo "FAIL\n";
            $test_results[] = ['name' => $test_name, 'status' => 'FAIL'];
        }
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
        $test_results[] = ['name' => $test_name, 'status' => 'ERROR', 'error' => $e->getMessage()];
    }
}

// クラスファイルの読み込みテスト
run_test('クラスファイルの読み込み', function() {
    $class_file = __DIR__ . '/inc/class-secure-session.php';
    
    if (!file_exists($class_file)) {
        throw new Exception('クラスファイルが見つかりません: ' . $class_file);
    }
    
    require_once $class_file;
    
    if (!class_exists('KeiPortfolio\\Security\\SecureSession')) {
        throw new Exception('SecureSessionクラスが定義されていません');
    }
    
    return true;
});

// シングルトンパターンテスト
run_test('シングルトンパターン', function() {
    $instance1 = \KeiPortfolio\Security\SecureSession::get_instance();
    $instance2 = \KeiPortfolio\Security\SecureSession::get_instance();
    
    return $instance1 === $instance2;
});

// セッション情報取得テスト
run_test('セッション情報取得', function() {
    $secure_session = \KeiPortfolio\Security\SecureSession::get_instance();
    $session_info = $secure_session->get_session_info();
    
    if (!is_array($session_info)) {
        throw new Exception('セッション情報は配列である必要があります');
    }
    
    $required_keys = ['session_status', 'php_version', 'is_ssl', 'session_active'];
    foreach ($required_keys as $key) {
        if (!array_key_exists($key, $session_info)) {
            throw new Exception("必要なキー「{$key}」が見つかりません");
        }
    }
    
    return true;
});

// セッション開始テスト（テスト環境で手動開始）
run_test('セッション開始', function() {
    if (session_status() === PHP_SESSION_NONE) {
        if (!@session_start()) {
            throw new Exception('セッション開始に失敗しました');
        }
    }
    
    return session_status() === PHP_SESSION_ACTIVE;
});

// セッション操作テスト
run_test('セッション値設定・取得・削除', function() {
    $secure_session = \KeiPortfolio\Security\SecureSession::get_instance();
    
    // 設定テスト
    $test_key = 'test_key_' . time();
    $test_value = 'test_value_' . rand();
    
    if (!$secure_session->set($test_key, $test_value)) {
        throw new Exception('セッション値の設定に失敗');
    }
    
    // 取得テスト
    $retrieved_value = $secure_session->get($test_key);
    if ($retrieved_value !== $test_value) {
        throw new Exception('設定した値と取得した値が異なります');
    }
    
    // デフォルト値テスト
    $default = 'default_value';
    $non_existent = $secure_session->get('non_existent_key', $default);
    if ($non_existent !== $default) {
        throw new Exception('デフォルト値が正しく返されません');
    }
    
    // 削除テスト
    if (!$secure_session->remove($test_key)) {
        throw new Exception('セッション値の削除に失敗');
    }
    
    // 削除確認
    $deleted_value = $secure_session->get($test_key);
    if ($deleted_value !== null) {
        throw new Exception('削除された値がまだ存在します');
    }
    
    return true;
});

// 保護されたキーテスト
run_test('保護されたキーのセキュリティ', function() {
    $secure_session = \KeiPortfolio\Security\SecureSession::get_instance();
    $protected_keys = ['user_agent', 'ip_address', 'session_token', 'session_start_time'];
    
    foreach ($protected_keys as $key) {
        $result = $secure_session->set($key, 'malicious_value');
        if ($result) {
            throw new Exception("保護されたキー「{$key}」の設定が許可されました");
        }
    }
    
    return true;
});

// セッショントークンテスト
run_test('セッショントークン機能', function() {
    $secure_session = \KeiPortfolio\Security\SecureSession::get_instance();
    
    // テスト用にトークンを手動設定（実際のSecureSessionでは自動設定される）
    $_SESSION['session_token'] = wp_generate_password(32, false);
    
    $token = $secure_session->get_token();
    if (!$token) {
        throw new Exception('セッショントークンを取得できません');
    }
    
    // 有効なトークン検証
    if (!$secure_session->verify_token($token)) {
        throw new Exception('有効なトークンの検証に失敗');
    }
    
    // 無効なトークン検証
    if ($secure_session->verify_token('invalid_token')) {
        throw new Exception('無効なトークンが有効として判定されました');
    }
    
    return true;
});

// パフォーマンステスト
run_test('パフォーマンステスト（100回操作）', function() {
    $secure_session = \KeiPortfolio\Security\SecureSession::get_instance();
    
    $start_time = microtime(true);
    
    for ($i = 0; $i < 100; $i++) {
        $key = 'perf_test_' . $i;
        $value = 'value_' . $i;
        
        $secure_session->set($key, $value);
        $secure_session->get($key);
        $secure_session->remove($key);
    }
    
    $end_time = microtime(true);
    $execution_time = ($end_time - $start_time) * 1000; // ミリ秒
    
    echo sprintf(" (実行時間: %.2f ms) ", $execution_time);
    
    return $execution_time < 1000; // 1秒未満なら成功
});

// セッション破棄テスト
run_test('セッション破棄', function() {
    $secure_session = \KeiPortfolio\Security\SecureSession::get_instance();
    
    // テスト値を設定
    $secure_session->set('destroy_test', 'value');
    
    // セッション破棄
    $secure_session->destroy_session();
    
    // セッション状態確認
    return session_status() !== PHP_SESSION_ACTIVE;
});

// テスト結果の表示
echo "\n=== テスト結果 ===\n";
echo "実行されたテスト数: {$test_count}\n";
echo "成功したテスト数: {$passed_count}\n";
echo "成功率: " . number_format(($passed_count / $test_count) * 100, 1) . "%\n\n";

if ($passed_count === $test_count) {
    echo "✓ 全てのテストが成功しました！\n";
    exit(0);
} else {
    echo "✗ 失敗したテストがあります。詳細を確認してください。\n";
    
    foreach ($test_results as $result) {
        if ($result['status'] !== 'PASS') {
            echo "- {$result['name']}: {$result['status']}";
            if (isset($result['error'])) {
                echo " ({$result['error']})";
            }
            echo "\n";
        }
    }
    exit(1);
}
?>
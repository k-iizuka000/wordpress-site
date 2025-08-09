<?php
/**
 * SecureSessionクラステストファイル
 * 
 * 使用方法: WordPress環境でこのファイルを実行
 * 例：http://localhost/wp-content/themes/kei-portfolio/test-secure-session.php
 */

// WordPress環境を読み込み
require_once '../../../wp-config.php';

// セキュリティチェック（テスト環境のみで実行）
if (!defined('WP_DEBUG') || !WP_DEBUG) {
    die('このテストはデバッグモードでのみ実行可能です。');
}

// HTML出力開始
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecureSession テスト</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .test-section { background: #f4f4f4; padding: 15px; margin: 10px 0; border-left: 4px solid #0073aa; }
        .success { color: #008000; }
        .error { color: #ff0000; }
        .info { color: #0073aa; }
        pre { background: #fff; padding: 10px; border: 1px solid #ddd; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>SecureSession クラス テスト結果</h1>
    
    <?php
    // テスト開始
    echo '<div class="test-section">';
    echo '<h2>1. クラスの読み込みテスト</h2>';
    
    try {
        // クラスファイルが存在するかチェック
        $class_file = get_template_directory() . '/inc/class-secure-session.php';
        if (!file_exists($class_file)) {
            throw new Exception('SecureSessionクラスファイルが見つかりません: ' . $class_file);
        }
        echo '<p class="success">✓ クラスファイル存在確認: OK</p>';
        
        // クラスが定義されているかチェック
        require_once $class_file;
        if (!class_exists('KeiPortfolio\\Security\\SecureSession')) {
            throw new Exception('SecureSessionクラスが定義されていません');
        }
        echo '<p class="success">✓ クラス定義確認: OK</p>';
        
        // インスタンス作成テスト
        use KeiPortfolio\Security\SecureSession;
        $secure_session = SecureSession::get_instance();
        
        if (!$secure_session instanceof SecureSession) {
            throw new Exception('インスタンス作成に失敗しました');
        }
        echo '<p class="success">✓ インスタンス作成: OK</p>';
        
    } catch (Exception $e) {
        echo '<p class="error">✗ エラー: ' . $e->getMessage() . '</p>';
    }
    echo '</div>';
    
    // セッション情報表示
    echo '<div class="test-section">';
    echo '<h2>2. セッション状態テスト</h2>';
    
    try {
        $session_info = $secure_session->get_session_info();
        echo '<p class="info">セッション診断情報:</p>';
        echo '<pre>' . print_r($session_info, true) . '</pre>';
        
        // セッション状態チェック
        if ($session_info['session_active']) {
            echo '<p class="success">✓ セッション開始: OK</p>';
        } else {
            echo '<p class="error">✗ セッション未開始</p>';
        }
        
    } catch (Exception $e) {
        echo '<p class="error">✗ セッション状態取得エラー: ' . $e->getMessage() . '</p>';
    }
    echo '</div>';
    
    // セッション操作テスト
    echo '<div class="test-section">';
    echo '<h2>3. セッション操作テスト</h2>';
    
    try {
        // セッション値設定テスト
        $test_key = 'secure_session_test';
        $test_value = 'test_value_' . time();
        
        $set_result = $secure_session->set($test_key, $test_value);
        if ($set_result) {
            echo '<p class="success">✓ セッション値設定: OK</p>';
        } else {
            echo '<p class="error">✗ セッション値設定: 失敗</p>';
        }
        
        // セッション値取得テスト
        $retrieved_value = $secure_session->get($test_key);
        if ($retrieved_value === $test_value) {
            echo '<p class="success">✓ セッション値取得: OK (値: ' . htmlspecialchars($retrieved_value) . ')</p>';
        } else {
            echo '<p class="error">✗ セッション値取得: 失敗 (期待値: ' . htmlspecialchars($test_value) . ', 実際の値: ' . htmlspecialchars($retrieved_value) . ')</p>';
        }
        
        // セッショントークンテスト
        $token = $secure_session->get_token();
        if ($token) {
            echo '<p class="success">✓ セッショントークン取得: OK (長さ: ' . strlen($token) . '文字)</p>';
            
            // トークン検証テスト
            if ($secure_session->verify_token($token)) {
                echo '<p class="success">✓ セッショントークン検証: OK</p>';
            } else {
                echo '<p class="error">✗ セッショントークン検証: 失敗</p>';
            }
        } else {
            echo '<p class="error">✗ セッショントークン取得: 失敗</p>';
        }
        
        // セッション値削除テスト
        $remove_result = $secure_session->remove($test_key);
        if ($remove_result) {
            echo '<p class="success">✓ セッション値削除: OK</p>';
            
            // 削除確認
            $deleted_value = $secure_session->get($test_key);
            if ($deleted_value === null) {
                echo '<p class="success">✓ セッション値削除確認: OK</p>';
            } else {
                echo '<p class="error">✗ セッション値削除確認: 失敗 (値が残っています)</p>';
            }
        } else {
            echo '<p class="error">✗ セッション値削除: 失敗</p>';
        }
        
    } catch (Exception $e) {
        echo '<p class="error">✗ セッション操作エラー: ' . $e->getMessage() . '</p>';
    }
    echo '</div>';
    
    // セキュリティテスト
    echo '<div class="test-section">';
    echo '<h2>4. セキュリティ機能テスト</h2>';
    
    try {
        // 保護されたキーの設定テスト
        $protected_keys = ['user_agent', 'ip_address', 'session_token', 'session_start_time'];
        foreach ($protected_keys as $key) {
            $result = $secure_session->set($key, 'test_value');
            if (!$result) {
                echo '<p class="success">✓ 保護されたキー「' . $key . '」の設定防止: OK</p>';
            } else {
                echo '<p class="error">✗ 保護されたキー「' . $key . '」の設定防止: 失敗</p>';
            }
        }
        
        // 無効なトークンの検証テスト
        $invalid_token = 'invalid_token';
        if (!$secure_session->verify_token($invalid_token)) {
            echo '<p class="success">✓ 無効なトークンの検証防止: OK</p>';
        } else {
            echo '<p class="error">✗ 無効なトークンの検証防止: 失敗</p>';
        }
        
    } catch (Exception $e) {
        echo '<p class="error">✗ セキュリティテストエラー: ' . $e->getMessage() . '</p>';
    }
    echo '</div>';
    
    // WordPress統合テスト
    echo '<div class="test-section">';
    echo '<h2>5. WordPress統合テスト</h2>';
    
    try {
        // WordPress定数のチェック
        $wp_constants = ['COOKIEPATH', 'COOKIE_DOMAIN'];
        foreach ($wp_constants as $constant) {
            if (defined($constant)) {
                echo '<p class="success">✓ WordPress定数「' . $constant . '」: OK (値: ' . constant($constant) . ')</p>';
            } else {
                echo '<p class="error">✗ WordPress定数「' . $constant . '」: 未定義</p>';
            }
        }
        
        // SSL確認
        if (function_exists('is_ssl')) {
            $ssl_status = is_ssl() ? '有効' : '無効';
            echo '<p class="info">SSL状態: ' . $ssl_status . '</p>';
        }
        
        // WordPress関数の利用可能性
        $wp_functions = ['wp_generate_password', 'current_user_can', 'get_template_directory'];
        foreach ($wp_functions as $function) {
            if (function_exists($function)) {
                echo '<p class="success">✓ WordPress関数「' . $function . '」: 利用可能</p>';
            } else {
                echo '<p class="error">✗ WordPress関数「' . $function . '」: 利用不可</p>';
            }
        }
        
    } catch (Exception $e) {
        echo '<p class="error">✗ WordPress統合テストエラー: ' . $e->getMessage() . '</p>';
    }
    echo '</div>';
    
    // パフォーマンステスト
    echo '<div class="test-section">';
    echo '<h2>6. パフォーマンステスト</h2>';
    
    try {
        $start_time = microtime(true);
        
        // 1000回のセッション操作
        for ($i = 0; $i < 1000; $i++) {
            $secure_session->set('perf_test_' . $i, 'value_' . $i);
            $secure_session->get('perf_test_' . $i);
            $secure_session->remove('perf_test_' . $i);
        }
        
        $end_time = microtime(true);
        $execution_time = ($end_time - $start_time) * 1000; // ミリ秒
        
        echo '<p class="info">1000回のセッション操作実行時間: ' . number_format($execution_time, 2) . ' ms</p>';
        
        if ($execution_time < 100) {
            echo '<p class="success">✓ パフォーマンス: 良好</p>';
        } elseif ($execution_time < 500) {
            echo '<p class="info">✓ パフォーマンス: 普通</p>';
        } else {
            echo '<p class="error">✗ パフォーマンス: 改善が必要</p>';
        }
        
    } catch (Exception $e) {
        echo '<p class="error">✗ パフォーマンステストエラー: ' . $e->getMessage() . '</p>';
    }
    echo '</div>';
    
    echo '<div class="test-section">';
    echo '<h2>テスト完了</h2>';
    echo '<p class="info">SecureSessionクラスのテストが完了しました。</p>';
    echo '<p><strong>注意:</strong> このテストファイルは本番環境では使用しないでください。</p>';
    echo '</div>';
    ?>
</body>
</html>
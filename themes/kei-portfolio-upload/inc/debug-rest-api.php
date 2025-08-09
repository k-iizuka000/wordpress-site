<?php
/**
 * REST APIデバッグツール（開発環境のみ）
 * 
 * REST APIのエラーと権限チェックをログに記録し、
 * 403エラーの原因特定を支援する
 * 
 * 機能:
 * - 詳細なエラーログ記録
 * - 管理画面でのテスト機能
 * - 権限チェックのデバッグ
 * - 開発環境のみで有効化
 * 
 * @package Kei_Portfolio_Pro
 * @since 1.0.0
 */

// 直接アクセスを防ぐ
if (!defined('ABSPATH')) {
    exit;
}

// デバッグモードと本番環境でのみ有効化（セキュリティ強化）
if (defined('WP_DEBUG') && WP_DEBUG && 
    defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE !== 'production') {
    
    /**
     * パスサニタイズ関数
     */
    function kei_portfolio_sanitize_paths($data) {
        $json = json_encode($data);
        $json = str_replace(ABSPATH, '[WP_ROOT]/', $json);
        $json = str_replace(WP_CONTENT_DIR, '[WP_CONTENT]/', $json);
        $json = str_replace(get_home_path(), '[HOME_PATH]/', $json);
        return json_decode($json, true);
    }
    
    /**
     * REST APIエラーのログ記録
     */
    add_filter('rest_request_after_callbacks', 'kei_portfolio_log_rest_errors', 10, 3);
    
    function kei_portfolio_log_rest_errors($response, $handler, $request) {
        if (is_wp_error($response)) {
            $error_data = array(
                'timestamp' => current_time('mysql'),
                'route' => $request->get_route(),
                'method' => $request->get_method(),
                'params' => $request->get_params(),
                'error_code' => $response->get_error_code(),
                'error_message' => $response->get_error_message(),
                'user' => is_user_logged_in() ? wp_get_current_user()->user_login : 'anonymous',
                'user_id' => get_current_user_id(),
                'user_roles' => is_user_logged_in() ? wp_get_current_user()->roles : array(),
                'capabilities' => is_user_logged_in() ? array_keys(array_filter(wp_get_current_user()->allcaps)) : array(),
                'headers' => array(
                    'referer' => wp_get_referer(),
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                    'x_requested_with' => $_SERVER['HTTP_X_REQUESTED_WITH'] ?? 'not set',
                    'wp_nonce' => $_SERVER['HTTP_X_WP_NONCE'] ?? 'not provided'
                ),
                'server_info' => array(
                    'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
                    'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
                    'remote_addr_hash' => md5($_SERVER['REMOTE_ADDR'] ?? 'unknown')
                )
            );
            
            // エラーログに記録（パスをサニタイズ）
            $sanitized_data = kei_portfolio_sanitize_paths($error_data);
            error_log('[KEI-PORTFOLIO-DEBUG] REST API Error: ' . json_encode($sanitized_data, JSON_PRETTY_PRINT));
            
            // 403エラーの場合は追加の診断情報を記録
            if ($response->get_error_code() === 'rest_forbidden') {
                $diagnostic_info = array(
                    'message' => 'REST API 403 Forbidden - Detailed Analysis',
                    'route_analysis' => kei_portfolio_analyze_endpoint_requirements($request->get_route()),
                    'user_analysis' => kei_portfolio_analyze_user_permissions(),
                    'suggestions' => kei_portfolio_get_permission_suggestions($request->get_route())
                );
                
                $sanitized_diagnostic = kei_portfolio_sanitize_paths($diagnostic_info);
                error_log('[KEI-PORTFOLIO-DEBUG] REST API 403 Analysis: ' . json_encode($sanitized_diagnostic, JSON_PRETTY_PRINT));
            }
            
            // カスタムログファイルにも記録（開発時の確認用、パスをサニタイズ）
            kei_portfolio_write_debug_log($sanitized_data, 'rest_error');
        }
        return $response;
    }
    
    /**
     * エンドポイントの要求権限を分析
     */
    function kei_portfolio_analyze_endpoint_requirements($route) {
        $requirements = array();
        
        if (strpos($route, 'templates') !== false) {
            $requirements['endpoint_type'] = 'template';
            $requirements['required_caps'] = array('edit_theme_options');
            $requirements['description'] = 'Template management endpoint';
        } elseif (strpos($route, 'posts') !== false) {
            $requirements['endpoint_type'] = 'posts';
            $requirements['required_caps'] = array('edit_posts', 'publish_posts');
            $requirements['description'] = 'Posts management endpoint';
        } elseif (strpos($route, 'users') !== false) {
            $requirements['endpoint_type'] = 'users';
            $requirements['required_caps'] = array('list_users', 'edit_users');
            $requirements['description'] = 'User management endpoint';
        } else {
            $requirements['endpoint_type'] = 'unknown';
            $requirements['required_caps'] = array();
            $requirements['description'] = 'Unknown endpoint type';
        }
        
        return $requirements;
    }
    
    /**
     * ユーザー権限を分析
     */
    function kei_portfolio_analyze_user_permissions() {
        if (!is_user_logged_in()) {
            return array('status' => 'not_logged_in');
        }
        
        $user = wp_get_current_user();
        return array(
            'status' => 'logged_in',
            'user_login' => $user->user_login,
            'roles' => $user->roles,
            'key_capabilities' => array(
                'edit_posts' => user_can($user, 'edit_posts'),
                'edit_theme_options' => user_can($user, 'edit_theme_options'),
                'manage_options' => user_can($user, 'manage_options'),
                'publish_posts' => user_can($user, 'publish_posts')
            )
        );
    }
    
    /**
     * 権限に関する提案を生成
     */
    function kei_portfolio_get_permission_suggestions($route) {
        $suggestions = array();
        
        if (strpos($route, 'templates') !== false) {
            $suggestions[] = 'User needs edit_theme_options capability for template endpoints';
            $suggestions[] = 'Consider adding: $user->add_cap(\'edit_theme_options\')';
            $suggestions[] = 'Or use rest-api-permissions.php to grant temporary access';
        }
        
        if (strpos($route, 'posts') !== false) {
            $suggestions[] = 'User needs edit_posts and publish_posts capabilities';
            $suggestions[] = 'Check if user role has proper permissions';
        }
        
        $suggestions[] = 'Verify nonce is being sent with request';
        $suggestions[] = 'Check security.php for conflicting restrictions';
        
        return $suggestions;
    }
    
    /**
     * カスタムデバッグログファイルに書き込み（ローテーション機能付き）
     */
    function kei_portfolio_write_debug_log($data, $type) {
        $log_dir = WP_CONTENT_DIR . '/debug-logs';
        if (!is_dir($log_dir)) {
            wp_mkdir_p($log_dir);
        }
        
        // ログローテーション: 30日以上古いログを削除
        kei_portfolio_rotate_debug_logs($log_dir);
        
        $log_file = $log_dir . '/kei-portfolio-' . $type . '-' . date('Y-m-d') . '.log';
        $log_entry = date('Y-m-d H:i:s') . ' - ' . json_encode($data, JSON_PRETTY_PRINT) . "\n\n";
        
        file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * ログローテーション機能: 30日以上古いファイルを削除
     */
    function kei_portfolio_rotate_debug_logs($log_dir) {
        // 1日に1回のみ実行してパフォーマンスを保つ
        $rotation_check_file = $log_dir . '/.last_rotation';
        $today = date('Y-m-d');
        
        if (file_exists($rotation_check_file) && file_get_contents($rotation_check_file) === $today) {
            return;
        }
        
        $files = glob($log_dir . '/kei-portfolio-*.log');
        $cutoff_date = strtotime('-30 days');
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff_date) {
                unlink($file);
            }
        }
        
        file_put_contents($rotation_check_file, $today);
    }
    
    /**
     * 権限チェックのデバッグ
     */
    add_filter('user_has_cap', 'kei_portfolio_debug_capabilities', 10, 3);
    
    function kei_portfolio_debug_capabilities($allcaps, $caps, $args) {
        // REST APIリクエストの場合のみログ記録
        if (defined('REST_REQUEST') && REST_REQUEST) {
            // テンプレート関連の権限チェックを記録
            if (in_array('edit_theme_options', $caps) || in_array('edit_posts', $caps)) {
                $debug_data = array(
                    'timestamp' => current_time('mysql'),
                    'required_caps' => $caps,
                    'user_has_caps' => array_keys(array_filter($allcaps)),
                    'check_passed' => !empty(array_intersect($caps, array_keys(array_filter($allcaps)))),
                    'user' => is_user_logged_in() ? wp_get_current_user()->user_login : 'anonymous',
                    'context' => isset($args[0]) ? $args[0] : 'unknown'
                );
                
                error_log('[KEI-PORTFOLIO-DEBUG] Capability Check: ' . json_encode($debug_data, JSON_PRETTY_PRINT));
            }
        }
        return $allcaps;
    }
    
    /**
     * REST APIルートの登録状況を記録
     */
    add_action('rest_api_init', 'kei_portfolio_log_rest_routes', 999);
    
    function kei_portfolio_log_rest_routes() {
        // 初回のみ実行（繰り返しログを避ける）
        static $logged = false;
        if ($logged) return;
        $logged = true;
        
        $server = rest_get_server();
        $routes = $server->get_routes();
        
        // テンプレート関連のルートのみ記録
        $template_routes = array();
        foreach ($routes as $route => $handlers) {
            if (strpos($route, 'templates') !== false) {
                $template_routes[$route] = array(
                    'methods' => array_keys($handlers),
                    'endpoints_count' => count($handlers)
                );
            }
        }
        
        if (!empty($template_routes)) {
            error_log('[KEI-PORTFOLIO-DEBUG] Registered Template Routes: ' . json_encode($template_routes, JSON_PRETTY_PRINT));
        }
    }
    
    /**
     * Nonceの検証デバッグ
     */
    add_filter('wp_verify_nonce', 'kei_portfolio_debug_nonce', 10, 2);
    
    function kei_portfolio_debug_nonce($result, $action) {
        if (defined('REST_REQUEST') && REST_REQUEST) {
            if ($result === false) {
                error_log('[KEI-PORTFOLIO-DEBUG] Nonce Verification Failed: ' . json_encode(array(
                    'action' => $action,
                    'nonce' => $_REQUEST['_wpnonce'] ?? $_SERVER['HTTP_X_WP_NONCE'] ?? 'not provided',
                    'user' => is_user_logged_in() ? wp_get_current_user()->user_login : 'anonymous'
                ), JSON_PRETTY_PRINT));
            }
        }
        return $result;
    }
    
    /**
     * REST API認証のデバッグ
     */
    add_filter('rest_authentication_errors', 'kei_portfolio_debug_rest_auth', 999);
    
    function kei_portfolio_debug_rest_auth($result) {
        if (is_wp_error($result)) {
            error_log('[KEI-PORTFOLIO-DEBUG] REST Authentication Error: ' . json_encode(array(
                'error_code' => $result->get_error_code(),
                'error_message' => $result->get_error_message(),
                'user_logged_in' => is_user_logged_in(),
                'user' => is_user_logged_in() ? wp_get_current_user()->user_login : 'anonymous',
                'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
            ), JSON_PRETTY_PRINT));
        }
        return $result;
    }
    
    /**
     * 管理画面にデバッグ情報パネルを追加
     */
    add_action('admin_menu', 'kei_portfolio_add_debug_page');
    
    function kei_portfolio_add_debug_page() {
        // 管理者権限の再確認強化
        if (current_user_can('manage_options') && 
            (is_super_admin() || in_array('administrator', wp_get_current_user()->roles))) {
            add_submenu_page(
                'tools.php',
                'REST API Debug',
                'REST API Debug',
                'manage_options',
                'rest-api-debug',
                'kei_portfolio_render_debug_page'
            );
        }
    }
    
    /**
     * デバッグ情報ページの表示
     */
    function kei_portfolio_render_debug_page() {
        // デバッグページへのアクセスに追加確認
        if (!current_user_can('manage_options') || 
            (!is_super_admin() && !in_array('administrator', wp_get_current_user()->roles))) {
            wp_die(__('このページへのアクセス権限がありません。'));
        }
        
        // AJAX処理用のnonceを動的生成（セキュリティ強化）
        $ajax_nonce = wp_create_nonce('kei_rest_debug_' . time());
        $rest_nonce = wp_create_nonce('wp_rest_' . time());
        ?>
        <div class="wrap">
            <h1>REST API Debug Information</h1>
            <p><strong>注意:</strong> このツールは開発環境(WP_DEBUG=true)でのみ利用可能です。</p>
            
            <div class="notice notice-info">
                <p>このページでは、REST API 403エラーの診断情報を確認し、テスト機能を使用できます。</p>
            </div>
            
            <h2>Current User Information</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">ユーザー名</th>
                    <td><?php echo esc_html(wp_get_current_user()->user_login); ?></td>
                </tr>
                <tr>
                    <th scope="row">ユーザーロール</th>
                    <td><?php echo esc_html(implode(', ', wp_get_current_user()->roles)); ?></td>
                </tr>
                <tr>
                    <th scope="row">主要な権限</th>
                    <td>
                        <?php
                        $key_caps = array('edit_posts', 'edit_theme_options', 'manage_options', 'publish_posts');
                        foreach ($key_caps as $cap) {
                            $status = current_user_can($cap) ? '✓' : '✗';
                            $color = current_user_can($cap) ? 'green' : 'red';
                            echo '<span style="color:' . esc_attr($color) . '">';
                            echo esc_html($status . ' ' . $cap);
                            echo '</span><br>';
                        }
                        ?>
                    </td>
                </tr>
            </table>
            
            <h2>REST API Status</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">REST API有効</th>
                    <td><?php 
                        if (rest_get_server()) {
                            echo '<span style="color:green">' . esc_html('✓ Yes') . '</span>';
                        } else {
                            echo '<span style="color:red">' . esc_html('✗ No') . '</span>';
                        }
                    ?></td>
                </tr>
                <tr>
                    <th scope="row">パーマリンク構造</th>
                    <td><?php 
                    $permalink = get_option('permalink_structure');
                    echo $permalink ? esc_html($permalink) : '<span style="color:orange">' . esc_html('基本設定（推奨しない）') . '</span>';
                    ?></td>
                </tr>
                <tr>
                    <th scope="row">REST URL</th>
                    <td><?php echo esc_html(rest_url()); ?></td>
                </tr>
            </table>
            
            <h2>Template Endpoints</h2>
            <div style="background: #f9f9f9; padding: 10px; font-family: monospace; max-height: 200px; overflow-y: auto;">
                <?php
                $server = rest_get_server();
                $routes = $server->get_routes();
                $template_routes = array();
                foreach ($routes as $route => $handlers) {
                    if (strpos($route, 'templates') !== false) {
                        echo esc_html($route) . '<br>';
                    }
                }
                ?>
            </div>
            
            <h2>Debug Log Information</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">WordPressログ</th>
                    <td><?php echo esc_html(str_replace(ABSPATH, '[WP_ROOT]/', WP_CONTENT_DIR . '/debug.log')); ?></td>
                </tr>
                <tr>
                    <th scope="row">カスタムログ</th>
                    <td><?php echo esc_html(str_replace(ABSPATH, '[WP_ROOT]/', WP_CONTENT_DIR . '/debug-logs/')); ?></td>
                </tr>
            </table>
            
            <h2>REST API Tests</h2>
            <div class="test-section">
                <h3>Template Lookup Test</h3>
                <p>このテストは、Gutenbergで問題となっているtemplate/lookupエンドポイントをテストします。</p>
                <button id="test-template-lookup" class="button button-primary">Test Templates Lookup Endpoint</button>
                <div id="template-test-result" class="test-result" style="margin-top: 20px;"></div>
                
                <h3>Posts Endpoint Test</h3>
                <p>投稿エンドポイントの動作をテストします。</p>
                <button id="test-posts" class="button button-secondary">Test Posts Endpoint</button>
                <div id="posts-test-result" class="test-result" style="margin-top: 20px;"></div>
                
                <h3>Authentication Test</h3>
                <p>現在のユーザーの認証状態をテストします。</p>
                <button id="test-auth" class="button button-secondary">Test Authentication</button>
                <div id="auth-test-result" class="test-result" style="margin-top: 20px;"></div>
            </div>
            
            <style>
            .test-result {
                border: 1px solid #ddd;
                padding: 10px;
                background: #fff;
                border-radius: 4px;
                font-family: monospace;
                white-space: pre-wrap;
                max-height: 300px;
                overflow-y: auto;
            }
            .test-result.success { border-color: #00a32a; }
            .test-result.error { border-color: #d63638; }
            .test-section { margin-bottom: 30px; }
            </style>
            
            <script>
            jQuery(document).ready(function($) {
                // テンプレートlookupテスト
                $('#test-template-lookup').on('click', function() {
                    var $button = $(this);
                    var $result = $('#template-test-result');
                    
                    $button.prop('disabled', true).text('Testing...');
                    $result.removeClass('success error').html('テスト実行中...');
                    
                    $.ajax({
                        url: '<?php echo rest_url('wp/v2/templates/lookup'); ?>',
                        method: 'GET',
                        data: {
                            slug: 'front-page',
                            _locale: 'user'
                        },
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', '<?php echo $rest_nonce; ?>');
                        },
                        success: function(response) {
                            $result.addClass('success').html('✓ Success!\n\nResponse:\n' + JSON.stringify(response, null, 2));
                        },
                        error: function(xhr) {
                            $result.addClass('error').html('✗ Error ' + xhr.status + ': ' + xhr.statusText + '\n\nResponse:\n' + xhr.responseText);
                        },
                        complete: function() {
                            $button.prop('disabled', false).text('Test Templates Lookup Endpoint');
                        }
                    });
                });
                
                // 投稿エンドポイントテスト
                $('#test-posts').on('click', function() {
                    var $button = $(this);
                    var $result = $('#posts-test-result');
                    
                    $button.prop('disabled', true).text('Testing...');
                    $result.removeClass('success error').html('テスト実行中...');
                    
                    $.ajax({
                        url: '<?php echo rest_url('wp/v2/posts'); ?>',
                        method: 'GET',
                        data: { per_page: 1 },
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', '<?php echo $rest_nonce; ?>');
                        },
                        success: function(response) {
                            $result.addClass('success').html('✓ Success!\n\nPosts count: ' + response.length + '\n\nFirst post:\n' + JSON.stringify(response[0] || {}, null, 2));
                        },
                        error: function(xhr) {
                            $result.addClass('error').html('✗ Error ' + xhr.status + ': ' + xhr.statusText + '\n\nResponse:\n' + xhr.responseText);
                        },
                        complete: function() {
                            $button.prop('disabled', false).text('Test Posts Endpoint');
                        }
                    });
                });
                
                // 認証テスト
                $('#test-auth').on('click', function() {
                    var $button = $(this);
                    var $result = $('#auth-test-result');
                    
                    $button.prop('disabled', true).text('Testing...');
                    $result.removeClass('success error').html('テスト実行中...');
                    
                    $.ajax({
                        url: '<?php echo rest_url('wp/v2/users/me'); ?>',
                        method: 'GET',
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', '<?php echo $rest_nonce; ?>');
                        },
                        success: function(response) {
                            $result.addClass('success').html('✓ Authentication Success!\n\nUser: ' + response.name + '\nRoles: ' + response.roles.join(', ') + '\n\nCapabilities: ' + Object.keys(response.capabilities || {}).slice(0, 10).join(', ') + '...');
                        },
                        error: function(xhr) {
                            $result.addClass('error').html('✗ Authentication Error ' + xhr.status + ': ' + xhr.statusText + '\n\nResponse:\n' + xhr.responseText);
                        },
                        complete: function() {
                            $button.prop('disabled', false).text('Test Authentication');
                        }
                    });
                });
            });
            </script>
        </div>
        <?php
    }
}
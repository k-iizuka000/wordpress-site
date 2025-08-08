<?php
/**
 * 管理画面ツール
 *
 * @package Kei_Portfolio
 */

/**
 * 管理画面メニューに追加
 */
add_action('admin_menu', 'kei_portfolio_add_admin_menu');

function kei_portfolio_add_admin_menu() {
    add_theme_page(
        'ポートフォリオ設定',
        'ポートフォリオ設定',
        'manage_options',
        'kei-portfolio-settings',
        'kei_portfolio_settings_page'
    );
}

/**
 * 設定ページの表示
 */
function kei_portfolio_settings_page() {
    // 現在のページの存在確認
    $page_configurations = kei_portfolio_get_page_configurations();
    $page_status = array();
    
    foreach ($page_configurations as $key => $config) {
        $existing_page = get_page_by_path($config['slug']);
        $page_status[$key] = array(
            'exists' => $existing_page !== null,
            'id' => $existing_page ? $existing_page->ID : null,
            'title' => $config['title'],
            'slug' => $config['slug'],
            'url' => $existing_page ? get_permalink($existing_page->ID) : home_url('/' . $config['slug'] . '/')
        );
    }
    ?>
    <div class="wrap">
        <h1>ポートフォリオテーマ設定</h1>
        
        <?php if (isset($_GET['pages_created'])): ?>
            <div class="notice notice-success is-dismissible">
                <p>固定ページが正常に作成されました。</p>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="notice notice-error is-dismissible">
                <p>エラーが発生しました: <?php echo esc_html($_GET['error']); ?></p>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <h2>固定ページ管理</h2>
            <p>テーマに必要な固定ページの状態と管理を行います。</p>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ページ名</th>
                        <th>スラッグ</th>
                        <th>状態</th>
                        <th>アクション</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($page_status as $key => $status): ?>
                    <tr>
                        <td><strong><?php echo esc_html($status['title']); ?></strong></td>
                        <td><code><?php echo esc_html($status['slug']); ?></code></td>
                        <td>
                            <?php if ($status['exists']): ?>
                                <span class="dashicons dashicons-yes-alt" style="color: green;"></span>
                                <span style="color: green;">作成済み</span>
                            <?php else: ?>
                                <span class="dashicons dashicons-warning" style="color: orange;"></span>
                                <span style="color: orange;">未作成</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($status['exists']): ?>
                                <a href="<?php echo esc_url($status['url']); ?>" class="button button-small" target="_blank">
                                    表示
                                </a>
                                <a href="<?php echo esc_url(get_edit_post_link($status['id'])); ?>" class="button button-small">
                                    編集
                                </a>
                            <?php else: ?>
                                <span style="color: #666;">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div style="margin-top: 20px;">
                <form method="post" action="">
                    <?php wp_nonce_field('kei_portfolio_create_pages', 'kei_portfolio_nonce'); ?>
                    <input type="hidden" name="action" value="create_pages">
                    <button type="submit" class="button button-primary">
                        <span class="dashicons dashicons-plus-alt" style="margin-top: 3px; margin-right: 5px;"></span>
                        固定ページを作成・更新
                    </button>
                    <p class="description">
                        不足している固定ページを自動で作成します。既存のページは上書きされません。
                    </p>
                </form>
            </div>
        </div>
        
        <div class="card" style="margin-top: 20px;">
            <h2>パーマリンク設定</h2>
            <p>404エラーが発生する場合は、パーマリンクをリフレッシュしてください。</p>
            <a href="<?php echo admin_url('options-permalink.php'); ?>" class="button">
                <span class="dashicons dashicons-admin-links" style="margin-top: 3px; margin-right: 5px;"></span>
                パーマリンク設定へ
            </a>
        </div>
        
        <div class="card" style="margin-top: 20px;">
            <h2>システム情報</h2>
            <table class="form-table">
                <tr>
                    <th>テーマバージョン</th>
                    <td><?php echo esc_html(wp_get_theme()->get('Version')); ?></td>
                </tr>
                <tr>
                    <th>WordPress バージョン</th>
                    <td><?php echo esc_html(get_bloginfo('version')); ?></td>
                </tr>
                <tr>
                    <th>最後のページ作成チェック</th>
                    <td>
                        <?php 
                        $last_check = get_transient('kei_portfolio_pages_check');
                        if ($last_check) {
                            echo '24時間以内';
                        } else {
                            echo '未実行または24時間経過';
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>自動ページ作成</th>
                    <td>
                        <?php 
                        $pages_created = get_option('kei_portfolio_pages_created', false);
                        echo $pages_created ? '実行済み' : '未実行';
                        ?>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="card" style="margin-top: 20px;">
            <h2>トラブルシューティング</h2>
            <div style="margin-bottom: 10px;">
                <strong>404エラーが発生する場合:</strong>
                <ol>
                    <li>上記の「固定ページを作成・更新」ボタンをクリック</li>
                    <li>「パーマリンク設定へ」をクリックして、設定画面で「変更を保存」をクリック</li>
                    <li>キャッシュをクリア（キャッシュプラグインを使用している場合）</li>
                </ol>
            </div>
            
            <form method="post" action="" style="margin-top: 15px;">
                <?php wp_nonce_field('kei_portfolio_flush_rewrite', 'kei_portfolio_flush_nonce'); ?>
                <input type="hidden" name="action" value="flush_rewrite">
                <button type="submit" class="button button-secondary">
                    <span class="dashicons dashicons-update" style="margin-top: 3px; margin-right: 5px;"></span>
                    パーマリンクを強制リフレッシュ
                </button>
                <p class="description">URLの書き換えルールを強制的に更新します。</p>
            </form>
        </div>
    </div>
    
    <style>
    .card {
        background: #fff;
        padding: 20px;
        margin: 20px 0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.13);
    }
    .card h2 {
        margin-top: 0;
    }
    </style>
    <?php
}

/**
 * ページ作成処理
 */
add_action('admin_init', 'kei_portfolio_handle_admin_actions');

function kei_portfolio_handle_admin_actions() {
    // ページ作成処理
    if (isset($_POST['action']) && $_POST['action'] === 'create_pages') {
        if (!check_admin_referer('kei_portfolio_create_pages', 'kei_portfolio_nonce')) {
            return;
        }
        
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // ページ作成実行
        $creation_results = kei_portfolio_manual_create_pages();
        
        // パーマリンクをフラッシュ
        flush_rewrite_rules();
        
        // 結果に応じてリダイレクト
        if (!empty($creation_results['errors'])) {
            $error_message = implode(', ', $creation_results['errors']);
            wp_redirect(add_query_arg('error', urlencode($error_message), $_SERVER['REQUEST_URI']));
        } else {
            wp_redirect(add_query_arg('pages_created', '1', $_SERVER['REQUEST_URI']));
        }
        exit;
    }
    
    // パーマリンク強制リフレッシュ処理
    if (isset($_POST['action']) && $_POST['action'] === 'flush_rewrite') {
        if (!check_admin_referer('kei_portfolio_flush_rewrite', 'kei_portfolio_flush_nonce')) {
            return;
        }
        
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // パーマリンクをフラッシュ
        flush_rewrite_rules();
        
        // トランジェントもクリア
        delete_transient('kei_portfolio_pages_check');
        
        wp_redirect(add_query_arg('flushed', '1', $_SERVER['REQUEST_URI']));
        exit;
    }
}

/**
 * 管理画面にスタイルを追加
 */
add_action('admin_head', 'kei_portfolio_admin_styles');

function kei_portfolio_admin_styles() {
    $screen = get_current_screen();
    if ($screen && $screen->id === 'appearance_page_kei-portfolio-settings') {
        ?>
        <style>
        .kei-portfolio-admin {
            max-width: 1200px;
        }
        .status-created {
            color: #46b450;
        }
        .status-missing {
            color: #dc3232;
        }
        .dashicons {
            vertical-align: middle;
        }
        </style>
        <?php
    }
}
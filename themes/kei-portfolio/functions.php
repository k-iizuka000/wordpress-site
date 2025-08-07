<?php
/**
 * Kei Portfolio Pro functions and definitions
 *
 * @package Kei_Portfolio_Pro
 */

// テーマファイルの分割読み込み
require_once get_template_directory() . '/inc/setup.php';
require_once get_template_directory() . '/inc/enqueue.php';
require_once get_template_directory() . '/inc/post-types.php';
require_once get_template_directory() . '/inc/widgets.php';
require_once get_template_directory() . '/inc/customizer.php';
require_once get_template_directory() . '/inc/ajax-handlers.php';
require_once get_template_directory() . '/inc/optimizations.php';
require_once get_template_directory() . '/inc/page-creator.php';
require_once get_template_directory() . '/inc/sample-data.php';
require_once get_template_directory() . '/inc/class-portfolio-data.php';

// Portfolio Data キャッシュクリア機能
add_action('wp_ajax_clear_portfolio_cache', 'kei_portfolio_clear_cache_ajax');
add_action('wp_ajax_nopriv_clear_portfolio_cache', 'kei_portfolio_clear_cache_ajax');

/**
 * Portfolio Dataキャッシュクリア AJAX Handler
 */
function kei_portfolio_clear_cache_ajax() {
    // 管理者権限チェック
    if (!current_user_can('manage_options')) {
        wp_die(__('権限がありません。', 'kei-portfolio'));
    }
    
    // Nonceチェック
    if (!check_ajax_referer('kei_portfolio_cache_nonce', 'nonce', false)) {
        wp_send_json_error(__('セキュリティチェックに失敗しました。', 'kei-portfolio'));
        return;
    }
    
    // キャッシュクリア実行
    $portfolio_data = Portfolio_Data::get_instance();
    $portfolio_data->clear_cache();
    
    wp_send_json_success(__('ポートフォリオデータのキャッシュをクリアしました。', 'kei-portfolio'));
}

/**
 * 管理画面にキャッシュクリアボタンを追加
 */
add_action('admin_bar_menu', 'kei_portfolio_add_cache_clear_button', 999);

function kei_portfolio_add_cache_clear_button($wp_admin_bar) {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $wp_admin_bar->add_menu(array(
        'id'    => 'portfolio-cache-clear',
        'title' => __('ポートフォリオキャッシュクリア', 'kei-portfolio'),
        'href'  => '#',
        'meta'  => array(
            'onclick' => 'keiPortfolioClearCache(); return false;',
        ),
    ));
}

/**
 * キャッシュクリア用JavaScript
 */
add_action('wp_footer', 'kei_portfolio_cache_clear_js');
add_action('admin_footer', 'kei_portfolio_cache_clear_js');

function kei_portfolio_cache_clear_js() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <script type="text/javascript">
    function keiPortfolioClearCache() {
        if (confirm('ポートフォリオデータのキャッシュをクリアしますか？')) {
            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'clear_portfolio_cache',
                    nonce: '<?php echo wp_create_nonce('kei_portfolio_cache_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data);
                        location.reload();
                    } else {
                        alert('エラー: ' + response.data);
                    }
                },
                error: function() {
                    alert('キャッシュクリアに失敗しました。');
                }
            });
        }
    }
    </script>
    <?php
}
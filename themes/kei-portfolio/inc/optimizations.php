<?php
/**
 * パフォーマンス最適化設定
 *
 * @package Kei_Portfolio
 */

/**
 * パフォーマンス最適化: 不要なものを削除
 */
function kei_portfolio_pro_cleanup() {
    // 絵文字関連の削除
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_action( 'admin_print_styles', 'print_emoji_styles' );
    
    // oEmbed関連の削除（必要ない場合）
    remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
    remove_action( 'wp_head', 'wp_oembed_add_host_js' );
    
    // RSD、WLWマニフェストリンクの削除
    remove_action( 'wp_head', 'rsd_link' );
    remove_action( 'wp_head', 'wlwmanifest_link' );
    
    // ショートリンクの削除
    remove_action( 'wp_head', 'wp_shortlink_wp_head' );
    
    // WordPressバージョン情報の削除
    remove_action( 'wp_head', 'wp_generator' );
}
add_action( 'init', 'kei_portfolio_pro_cleanup' );

/**
 * 画像の遅延読み込み設定
 */
add_filter( 'wp_lazy_loading_enabled', '__return_true' );
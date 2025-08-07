<?php
/**
 * Simple Bootstrap File
 * PHPUnitなしでも動作する基本的なテスト環境セットアップ
 */

// Define test environment constants
define( 'WP_TESTS_PHPUNIT_BOOTSTRAP', true );
define( 'THEME_TESTS_DIR', __DIR__ . '/tests' );
define( 'THEME_DIR', __DIR__ );

echo "Simple bootstrap loaded.\n";
echo "Theme directory: " . THEME_DIR . "\n";
echo "Tests directory: " . THEME_TESTS_DIR . "\n\n";

// 基本的なWordPress関数のモック
if ( ! function_exists( 'add_action' ) ) {
    function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
        // Mock implementation
    }
}

if ( ! function_exists( 'add_filter' ) ) {
    function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
        // Mock implementation
    }
}

if ( ! function_exists( '__' ) ) {
    function __( $text, $domain = 'default' ) {
        return $text;
    }
}

if ( ! function_exists( 'esc_html' ) ) {
    function esc_html( $text ) {
        return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
    }
}

if ( ! function_exists( 'esc_attr' ) ) {
    function esc_attr( $text ) {
        return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
    }
}

if ( ! function_exists( 'esc_url' ) ) {
    function esc_url( $url ) {
        return filter_var( $url, FILTER_SANITIZE_URL );
    }
}

if ( ! function_exists( 'wp_kses_post' ) ) {
    function wp_kses_post( $content ) {
        return strip_tags( $content, '<p><a><strong><em><ul><ol><li><blockquote><h1><h2><h3><h4><h5><h6>' );
    }
}
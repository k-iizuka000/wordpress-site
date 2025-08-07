<?php
/**
 * カスタマイザーの設定
 *
 * @package Kei_Portfolio
 */

/**
 * カスタマイザーの設定
 */
function kei_portfolio_pro_customize_register( $wp_customize ) {
    // ヒーローセクション設定
    $wp_customize->add_section( 'kei_portfolio_hero', array(
        'title'       => __( 'Hero Section', 'kei-portfolio' ),
        'priority'    => 30,
        'description' => __( 'Customize the hero section on the front page.', 'kei-portfolio' ),
    ) );

    // ヒーロータイトル
    $wp_customize->add_setting( 'hero_title', array(
        'default'           => __( 'レガシーシステムを、次世代へ', 'kei-portfolio' ),
        'sanitize_callback' => 'sanitize_text_field',
    ) );

    $wp_customize->add_control( 'hero_title', array(
        'label'    => __( 'Hero Title', 'kei-portfolio' ),
        'section'  => 'kei_portfolio_hero',
        'type'     => 'text',
    ) );

    // ヒーローサブタイトル
    $wp_customize->add_setting( 'hero_subtitle', array(
        'default'           => __( '10年以上の経験を持つJava/Spring Boot専門家', 'kei-portfolio' ),
        'sanitize_callback' => 'sanitize_text_field',
    ) );

    $wp_customize->add_control( 'hero_subtitle', array(
        'label'    => __( 'Hero Subtitle', 'kei-portfolio' ),
        'section'  => 'kei_portfolio_hero',
        'type'     => 'text',
    ) );

    // ヒーローCTAテキスト
    $wp_customize->add_setting( 'hero_cta_text', array(
        'default'           => __( '実績を見る', 'kei-portfolio' ),
        'sanitize_callback' => 'sanitize_text_field',
    ) );

    $wp_customize->add_control( 'hero_cta_text', array(
        'label'    => __( 'CTA Button Text', 'kei-portfolio' ),
        'section'  => 'kei_portfolio_hero',
        'type'     => 'text',
    ) );

    // ヒーローCTAリンク
    $wp_customize->add_setting( 'hero_cta_link', array(
        'default'           => '#projects',
        'sanitize_callback' => 'esc_url_raw',
    ) );

    $wp_customize->add_control( 'hero_cta_link', array(
        'label'    => __( 'CTA Button Link', 'kei-portfolio' ),
        'section'  => 'kei_portfolio_hero',
        'type'     => 'url',
    ) );

    // フッター設定セクション（Reactのfooter.tsxから移行）
    $wp_customize->add_section( 'kei_portfolio_footer', array(
        'title'       => __( 'Footer Settings', 'kei-portfolio' ),
        'priority'    => 40,
        'description' => __( 'Customize the footer section.', 'kei-portfolio' ),
    ) );

    // フッター説明文
    $wp_customize->add_setting( 'footer_description', array(
        'default'           => __( 'フリーランスエンジニアとして、自動化ツールの開発を中心に活動しています。明るく前向きな姿勢で、お客様の課題解決に取り組みます。', 'kei-portfolio' ),
        'sanitize_callback' => 'sanitize_textarea_field',
    ) );

    $wp_customize->add_control( 'footer_description', array(
        'label'    => __( 'Footer Description', 'kei-portfolio' ),
        'section'  => 'kei_portfolio_footer',
        'type'     => 'textarea',
    ) );

    // コンタクトメール
    $wp_customize->add_setting( 'contact_email', array(
        'default'           => 'contact@portfolio.com',
        'sanitize_callback' => 'sanitize_email',
    ) );

    $wp_customize->add_control( 'contact_email', array(
        'label'    => __( 'Contact Email', 'kei-portfolio' ),
        'section'  => 'kei_portfolio_footer',
        'type'     => 'email',
    ) );

    // 趣味テキスト
    $wp_customize->add_setting( 'hobby_text', array(
        'default'           => __( 'ロードバイクでリフレッシュ中', 'kei-portfolio' ),
        'sanitize_callback' => 'sanitize_text_field',
    ) );

    $wp_customize->add_control( 'hobby_text', array(
        'label'    => __( 'Hobby Text', 'kei-portfolio' ),
        'section'  => 'kei_portfolio_footer',
        'type'     => 'text',
    ) );

    // ソーシャルリンク設定
    $wp_customize->add_setting( 'social_github', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ) );

    $wp_customize->add_control( 'social_github', array(
        'label'    => __( 'GitHub URL', 'kei-portfolio' ),
        'section'  => 'kei_portfolio_footer',
        'type'     => 'url',
    ) );

    $wp_customize->add_setting( 'social_linkedin', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ) );

    $wp_customize->add_control( 'social_linkedin', array(
        'label'    => __( 'LinkedIn URL', 'kei-portfolio' ),
        'section'  => 'kei_portfolio_footer',
        'type'     => 'url',
    ) );

    $wp_customize->add_setting( 'social_twitter', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ) );

    $wp_customize->add_control( 'social_twitter', array(
        'label'    => __( 'Twitter URL', 'kei-portfolio' ),
        'section'  => 'kei_portfolio_footer',
        'type'     => 'url',
    ) );
}
add_action( 'customize_register', 'kei_portfolio_pro_customize_register' );
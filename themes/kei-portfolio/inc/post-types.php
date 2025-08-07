<?php
/**
 * カスタム投稿タイプとタクソノミーの登録
 *
 * @package Kei_Portfolio
 */

/**
 * カスタム投稿タイプ: Projects
 */
function kei_portfolio_pro_register_post_types() {
    // プロジェクト（実績）投稿タイプ
    $labels = array(
        'name'                  => _x( 'Projects', 'Post type general name', 'kei-portfolio' ),
        'singular_name'         => _x( 'Project', 'Post type singular name', 'kei-portfolio' ),
        'menu_name'             => _x( 'Projects', 'Admin Menu text', 'kei-portfolio' ),
        'name_admin_bar'        => _x( 'Project', 'Add New on Toolbar', 'kei-portfolio' ),
        'add_new'               => __( 'Add New', 'kei-portfolio' ),
        'add_new_item'          => __( 'Add New Project', 'kei-portfolio' ),
        'new_item'              => __( 'New Project', 'kei-portfolio' ),
        'edit_item'             => __( 'Edit Project', 'kei-portfolio' ),
        'view_item'             => __( 'View Project', 'kei-portfolio' ),
        'all_items'             => __( 'All Projects', 'kei-portfolio' ),
        'search_items'          => __( 'Search Projects', 'kei-portfolio' ),
        'not_found'             => __( 'No projects found.', 'kei-portfolio' ),
        'not_found_in_trash'    => __( 'No projects found in Trash.', 'kei-portfolio' ),
        'featured_image'        => _x( 'Project Cover Image', 'Overrides the "Featured Image" phrase', 'kei-portfolio' ),
        'set_featured_image'    => _x( 'Set cover image', 'Overrides the "Set featured image" phrase', 'kei-portfolio' ),
        'remove_featured_image' => _x( 'Remove cover image', 'Overrides the "Remove featured image" phrase', 'kei-portfolio' ),
        'use_featured_image'    => _x( 'Use as cover image', 'Overrides the "Use as featured image" phrase', 'kei-portfolio' ),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'projects' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-portfolio',
        'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
        'show_in_rest'       => true, // Gutenbergエディタのサポート
    );

    register_post_type( 'project', $args );
}
add_action( 'init', 'kei_portfolio_pro_register_post_types' );

/**
 * カスタムタクソノミー: 技術スタック
 */
function kei_portfolio_pro_register_taxonomies() {
    // 技術スタックタクソノミー
    $labels = array(
        'name'              => _x( 'Technologies', 'taxonomy general name', 'kei-portfolio' ),
        'singular_name'     => _x( 'Technology', 'taxonomy singular name', 'kei-portfolio' ),
        'search_items'      => __( 'Search Technologies', 'kei-portfolio' ),
        'all_items'         => __( 'All Technologies', 'kei-portfolio' ),
        'parent_item'       => __( 'Parent Technology', 'kei-portfolio' ),
        'parent_item_colon' => __( 'Parent Technology:', 'kei-portfolio' ),
        'edit_item'         => __( 'Edit Technology', 'kei-portfolio' ),
        'update_item'       => __( 'Update Technology', 'kei-portfolio' ),
        'add_new_item'      => __( 'Add New Technology', 'kei-portfolio' ),
        'new_item_name'     => __( 'New Technology Name', 'kei-portfolio' ),
        'menu_name'         => __( 'Technologies', 'kei-portfolio' ),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'technology' ),
        'show_in_rest'      => true,
    );

    register_taxonomy( 'technology', array( 'project' ), $args );

    // 業界タクソノミー
    $industry_labels = array(
        'name'              => _x( 'Industries', 'taxonomy general name', 'kei-portfolio' ),
        'singular_name'     => _x( 'Industry', 'taxonomy singular name', 'kei-portfolio' ),
        'search_items'      => __( 'Search Industries', 'kei-portfolio' ),
        'all_items'         => __( 'All Industries', 'kei-portfolio' ),
        'edit_item'         => __( 'Edit Industry', 'kei-portfolio' ),
        'update_item'       => __( 'Update Industry', 'kei-portfolio' ),
        'add_new_item'      => __( 'Add New Industry', 'kei-portfolio' ),
        'new_item_name'     => __( 'New Industry Name', 'kei-portfolio' ),
        'menu_name'         => __( 'Industries', 'kei-portfolio' ),
    );

    $industry_args = array(
        'hierarchical'      => false,
        'labels'            => $industry_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'industry' ),
        'show_in_rest'      => true,
    );

    register_taxonomy( 'industry', array( 'project' ), $industry_args );
}
add_action( 'init', 'kei_portfolio_pro_register_taxonomies' );
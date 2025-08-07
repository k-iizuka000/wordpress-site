<?php
/**
 * テーマのセットアップ関数
 *
 * @package Kei_Portfolio
 */

/**
 * テーマのセットアップ
 */
function kei_portfolio_pro_setup() {
    // 翻訳ファイルのサポート
    load_theme_textdomain( 'kei-portfolio', get_template_directory() . '/languages' );

    // デフォルトの投稿とコメントのRSSフィードリンクを追加
    add_theme_support( 'automatic-feed-links' );

    // タイトルタグのサポート
    add_theme_support( 'title-tag' );

    // アイキャッチ画像のサポート
    add_theme_support( 'post-thumbnails' );
    
    // カスタム画像サイズの追加
    add_image_size( 'project-thumbnail', 400, 300, true );
    add_image_size( 'project-large', 1200, 600, true );
    add_image_size( 'hero-image', 1920, 1080, true );

    // ナビゲーションメニューの登録
    register_nav_menus( array(
        'primary'   => esc_html__( 'Primary Menu', 'kei-portfolio' ),
        'footer'    => esc_html__( 'Footer Menu', 'kei-portfolio' ),
        'social'    => esc_html__( 'Social Links Menu', 'kei-portfolio' ),
    ) );

    // HTML5マークアップのサポート
    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ) );

    // カスタムロゴのサポート
    add_theme_support( 'custom-logo', array(
        'height'      => 60,
        'width'       => 200,
        'flex-width'  => true,
        'flex-height' => true,
    ) );

    // ブロックエディタのワイドアラインメントサポート
    add_theme_support( 'align-wide' );

    // エディタースタイルの追加
    add_theme_support( 'editor-styles' );
    add_editor_style( 'editor-style.css' );

    // Core Web Vitals最適化
    add_theme_support( 'responsive-embeds' );
}
add_action( 'after_setup_theme', 'kei_portfolio_pro_setup' );

/**
 * SEO最適化: メタディスクリプション（Reactのlayout.tsxから移行）
 */
function kei_portfolio_pro_meta_description() {
    if ( is_singular() ) {
        $description = get_the_excerpt();
    } elseif ( is_archive() ) {
        $description = get_the_archive_description();
    } else {
        $description = get_bloginfo( 'description' );
    }
    
    if ( $description ) {
        echo '<meta name="description" content="' . esc_attr( wp_strip_all_tags( $description ) ) . '">' . "\n";
    }
}
add_action( 'wp_head', 'kei_portfolio_pro_meta_description' );

/**
 * 言語設定（Reactのlayout.tsxから移行）
 */
function kei_portfolio_pro_language_attributes( $output ) {
    return 'lang="ja"';
}
add_filter( 'language_attributes', 'kei_portfolio_pro_language_attributes' );

/**
 * ボディクラスにReactで使用していたフォント変数を追加
 */
function kei_portfolio_pro_body_class( $classes ) {
    $classes[] = 'font-geist-sans';
    $classes[] = 'antialiased';
    return $classes;
}
add_filter( 'body_class', 'kei_portfolio_pro_body_class' );

/**
 * Open Graph タグの追加
 */
function kei_portfolio_pro_open_graph() {
    if ( is_singular() ) {
        global $post;
        ?>
        <meta property="og:title" content="<?php echo esc_attr( get_the_title() ); ?>">
        <meta property="og:type" content="article">
        <meta property="og:url" content="<?php echo esc_url( get_permalink() ); ?>">
        <meta property="og:description" content="<?php echo esc_attr( wp_strip_all_tags( get_the_excerpt() ) ); ?>">
        <?php if ( has_post_thumbnail() ) : ?>
            <meta property="og:image" content="<?php echo esc_url( get_the_post_thumbnail_url( $post->ID, 'large' ) ); ?>">
        <?php endif; ?>
        <meta property="og:site_name" content="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
        <?php
    }
}
add_action( 'wp_head', 'kei_portfolio_pro_open_graph' );

/**
 * スキーマ（構造化データ）の追加
 */
function kei_portfolio_pro_schema_person() {
    if ( is_front_page() ) {
        ?>
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Person",
            "name": "Kei Aokiki",
            "jobTitle": "フルスタックエンジニア",
            "description": "10年以上のシステム開発経験を持つソフトウェアエンジニア。Java/Spring Bootを中心とした開発が得意。",
            "url": "<?php echo esc_url( home_url( '/' ) ); ?>",
            "sameAs": [
                "https://github.com/kei-aokiki",
                "https://linkedin.com/in/kei-aokiki",
                "https://twitter.com/kei_aokiki"
            ]
        }
        </script>
        <?php
    }
}
add_action( 'wp_head', 'kei_portfolio_pro_schema_person' );

/**
 * portfolio.jsonデータのインポート関数（管理画面で使用）
 */
function kei_portfolio_import_json_data() {
    $json_file = get_template_directory() . '/data/portfolio.json';
    
    if ( ! file_exists( $json_file ) ) {
        return false;
    }
    
    $json_content = file_get_contents( $json_file );
    $portfolio_data = json_decode( $json_content, true );
    
    if ( json_last_error() !== JSON_ERROR_NONE ) {
        return false;
    }
    
    return $portfolio_data;
}
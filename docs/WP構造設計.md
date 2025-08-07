# WordPress テーマ構造設計書

## 1. テーマディレクトリ構造

```
themes/kei-portfolio/
├── assets/
│   ├── css/
│   │   ├── tailwind.css        # Tailwindベース
│   │   └── custom.css          # カスタムスタイル
│   ├── js/
│   │   ├── main.js            # メインJavaScript
│   │   ├── navigation.js       # ナビゲーション制御
│   │   ├── contact-form.js     # フォーム処理
│   │   └── animations.js       # アニメーション
│   └── images/
│       └── defaults/           # デフォルト画像
├── components/
│   ├── hero-section.php        # ヒーローセクション
│   ├── project-card.php        # プロジェクトカード
│   ├── skill-card.php          # スキルカード
│   └── contact-form.php        # コンタクトフォーム
├── data/
│   └── portfolio.json          # ポートフォリオデータ
├── inc/
│   ├── customizer.php          # カスタマイザー設定
│   ├── template-functions.php  # テンプレート関数
│   ├── ajax-handlers.php       # AJAX処理
│   └── shortcodes.php          # ショートコード
├── page-templates/
│   ├── template-about.php      # Aboutページテンプレート
│   ├── template-skills.php     # Skillsページテンプレート
│   └── template-contact.php    # Contactページテンプレート
├── template-parts/
│   ├── content-project.php     # プロジェクトコンテンツ
│   ├── content-page.php        # ページコンテンツ
│   └── content-none.php        # コンテンツなし
├── languages/                  # 翻訳ファイル
├── 404.php                     # 404ページ
├── archive-project.php         # プロジェクトアーカイブ
├── footer.php                  # フッター
├── front-page.php             # フロントページ
├── functions.php              # テーマ関数
├── header.php                 # ヘッダー
├── index.php                  # インデックス
├── page.php                   # 固定ページ
├── single-project.php         # プロジェクト詳細
├── single.php                 # 投稿詳細
├── style.css                  # テーマスタイル（Tailwindビルド後）
└── screenshot.png             # テーマスクリーンショット
```

## 2. カスタム投稿タイプ定義

### Projects（プロジェクト）
```php
// functions.php または inc/post-types.php

register_post_type('project', array(
    'labels' => array(
        'name' => 'プロジェクト',
        'singular_name' => 'プロジェクト',
        'add_new' => '新規追加',
        'add_new_item' => '新規プロジェクトを追加',
        'edit_item' => 'プロジェクトを編集',
        'view_item' => 'プロジェクトを表示',
        'all_items' => 'すべてのプロジェクト',
    ),
    'public' => true,
    'has_archive' => true,
    'rewrite' => array('slug' => 'portfolio'),
    'supports' => array(
        'title',           // タイトル
        'editor',          // 本文
        'thumbnail',       // アイキャッチ画像
        'excerpt',         // 抜粋
        'custom-fields',   // カスタムフィールド
        'page-attributes'  // 順序
    ),
    'menu_icon' => 'dashicons-portfolio',
    'show_in_rest' => true, // Gutenbergサポート
));
```

### カスタムフィールド（ACF不使用の場合）
```php
// プロジェクトメタボックス
add_meta_box(
    'project_details',
    'プロジェクト詳細',
    'project_details_callback',
    'project',
    'normal',
    'high'
);

// フィールド内容
- project_url         // プロジェクトURL
- github_url         // GitHubリポジトリ
- completion_date    // 完成日
- client_name        // クライアント名
- project_duration   // 開発期間
- team_size         // チーム規模
```

## 3. カスタムタクソノミー定義

### Technology（技術スタック）
```php
register_taxonomy('technology', 'project', array(
    'labels' => array(
        'name' => '技術スタック',
        'singular_name' => '技術',
        'add_new_item' => '新規技術を追加',
    ),
    'hierarchical' => true,  // カテゴリー形式
    'show_in_rest' => true,
    'rewrite' => array('slug' => 'tech'),
));

// デフォルトターム
- フロントエンド
  - React
  - Vue.js
  - TypeScript
  - Tailwind CSS
- バックエンド
  - Node.js
  - Python
  - PHP
  - Java
- データベース
  - MySQL
  - PostgreSQL
  - MongoDB
- インフラ
  - AWS
  - Docker
  - Kubernetes
```

### Project Type（プロジェクトタイプ）
```php
register_taxonomy('project_type', 'project', array(
    'labels' => array(
        'name' => 'プロジェクトタイプ',
        'singular_name' => 'タイプ',
    ),
    'hierarchical' => false,  // タグ形式
    'show_in_rest' => true,
    'rewrite' => array('slug' => 'type'),
));

// ターム例
- Webアプリケーション
- 自動化ツール
- データ分析
- APIサービス
- モバイルアプリ
```

## 4. テンプレート階層

### 優先順位と対応

1. **フロントページ**
   - `front-page.php` → Reactの `/app/page.tsx`

2. **固定ページ**
   - `page-{slug}.php` → 各ページ専用テンプレート
   - `page-templates/template-{name}.php` → ページテンプレート選択式
   - `page.php` → デフォルト固定ページ

3. **プロジェクト（カスタム投稿）**
   - `single-project.php` → プロジェクト詳細
   - `archive-project.php` → プロジェクト一覧

4. **404エラー**
   - `404.php` → `/app/not-found.tsx`

## 5. functions.php 機能一覧

### 基本設定
```php
// テーマサポート
add_theme_support('post-thumbnails');
add_theme_support('title-tag');
add_theme_support('custom-logo');
add_theme_support('align-wide');
add_theme_support('responsive-embeds');
add_theme_support('html5', array('search-form', 'comment-form', 'gallery'));

// メニュー登録
register_nav_menus(array(
    'primary' => 'メインメニュー',
    'footer' => 'フッターメニュー',
    'mobile' => 'モバイルメニュー',
));
```

### スクリプト・スタイル登録
```php
function kei_portfolio_scripts() {
    // Tailwind CSS (ビルド済み)
    wp_enqueue_style('tailwind', get_template_directory_uri() . '/style.css', array(), '1.0.0');
    
    // Google Fonts
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Pacifico&family=Noto+Sans+JP:wght@400;500;700&display=swap');
    
    // Remix Icon
    wp_enqueue_style('remixicon', 'https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css');
    
    // メインJavaScript
    wp_enqueue_script('main-js', get_template_directory_uri() . '/assets/js/main.js', array(), '1.0.0', true);
    
    // AJAX用設定
    wp_localize_script('main-js', 'wp_ajax', array(
        'url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wp_ajax_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'kei_portfolio_scripts');
```

### カスタム関数
```php
// プロジェクト取得関数
function get_featured_projects($limit = 3) {
    return new WP_Query(array(
        'post_type' => 'project',
        'posts_per_page' => $limit,
        'meta_key' => 'featured',
        'meta_value' => 'yes',
        'orderby' => 'menu_order',
        'order' => 'ASC'
    ));
}

// スキルデータ取得
function get_skills_data() {
    $json_file = get_template_directory() . '/data/skills.json';
    if (file_exists($json_file)) {
        return json_decode(file_get_contents($json_file), true);
    }
    return array();
}

// パンくずリスト生成
function kei_portfolio_breadcrumbs() {
    // パンくずリスト実装
}
```

## 6. 必要なプラグイン一覧

### 必須プラグイン
1. **Advanced Custom Fields (ACF) Pro** - カスタムフィールド管理
2. **Contact Form 7** - お問い合わせフォーム
3. **WP Super Cache** - キャッシュ最適化

### 推奨プラグイン
1. **Yoast SEO** - SEO最適化
2. **WP Migrate DB** - データベース移行
3. **Query Monitor** - デバッグツール
4. **Regenerate Thumbnails** - サムネイル再生成
5. **WP Mail SMTP** - メール送信設定

### 開発用プラグイン
1. **Show Current Template** - 現在のテンプレート表示
2. **Theme Check** - テーマチェック
3. **Debug Bar** - デバッグバー

## 7. カスタマイザー設定項目

```php
// カスタマイザーセクション
1. サイト基本情報
   - サイトタイトル
   - キャッチフレーズ
   - サイトアイコン

2. ヒーローセクション
   - メインタイトル
   - サブタイトル
   - 背景画像
   - CTAボタンテキスト
   - CTAボタンリンク

3. プロフィール情報
   - 名前
   - 肩書き
   - 自己紹介文
   - プロフィール画像

4. SNSリンク
   - GitHub URL
   - LinkedIn URL
   - Twitter URL
   - その他SNS

5. カラー設定
   - プライマリカラー
   - セカンダリカラー
   - アクセントカラー

6. フォント設定
   - 見出しフォント
   - 本文フォント
```

## 8. AJAX処理

### コンタクトフォーム送信
```php
add_action('wp_ajax_submit_contact', 'handle_contact_submission');
add_action('wp_ajax_nopriv_submit_contact', 'handle_contact_submission');

function handle_contact_submission() {
    // Nonce検証
    check_ajax_referer('wp_ajax_nonce', 'nonce');
    
    // フォームデータ処理
    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $message = sanitize_textarea_field($_POST['message']);
    
    // メール送信
    $result = wp_mail(
        get_option('admin_email'),
        'お問い合わせ: ' . $name,
        $message,
        array('From: ' . $email)
    );
    
    // レスポンス
    wp_send_json_success(array(
        'message' => 'お問い合わせを受け付けました。'
    ));
}
```

### プロジェクトフィルター
```php
add_action('wp_ajax_filter_projects', 'handle_project_filter');
add_action('wp_ajax_nopriv_filter_projects', 'handle_project_filter');

function handle_project_filter() {
    $technology = sanitize_text_field($_POST['technology']);
    
    $args = array(
        'post_type' => 'project',
        'posts_per_page' => -1,
    );
    
    if ($technology && $technology !== 'all') {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'technology',
                'field' => 'slug',
                'terms' => $technology,
            )
        );
    }
    
    $projects = new WP_Query($args);
    
    ob_start();
    while ($projects->have_posts()) {
        $projects->the_post();
        get_template_part('components/project-card');
    }
    $html = ob_get_clean();
    
    wp_send_json_success(array('html' => $html));
}
```

## 9. パフォーマンス最適化設定

```php
// 不要な機能の削除
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'rsd_link');

// 画像の遅延読み込み
add_filter('wp_lazy_loading_enabled', '__return_true');

// スクリプトの遅延読み込み
add_filter('script_loader_tag', function($tag, $handle) {
    if (!is_admin()) {
        return str_replace(' src', ' defer src', $tag);
    }
    return $tag;
}, 10, 2);
```

## 10. セキュリティ設定

```php
// ログインページのカスタマイズ
add_action('login_enqueue_scripts', 'custom_login_styles');

// XMLRPCの無効化
add_filter('xmlrpc_enabled', '__return_false');

// ファイル編集の無効化
define('DISALLOW_FILE_EDIT', true);

// 投稿者アーカイブの無効化（ユーザー名露出防止）
add_action('template_redirect', function() {
    if (is_author()) {
        wp_redirect(home_url());
        exit;
    }
});
```
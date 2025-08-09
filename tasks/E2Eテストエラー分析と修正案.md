# E2Eテストエラー分析と修正案

## 1. エラー分析結果

### 1.1 発生しているエラーの詳細

#### 主要なエラー
1. **500 Internal Server Error**: CSS/JSファイルへのアクセス時に発生
2. **MIME Type エラー**: `text/html`として返されるため、CSS/JSとして認識されない
3. **net::ERR_ABORTED**: ファイル読み込みの中断

#### 影響を受けているページ
- `/` (トップページ)
- `/about/`
- `/skills/`
- `/contact/`
- `/portfolio/`

#### 影響を受けていないページ
- サイトマップXML関連のURL（正常動作）

### 1.2 根本原因

Web検索の結果から、以下の原因が特定されました：

1. **存在しないファイルへのアクセス**
   - `dev-debug.css` と `dev-debug.js` が存在しない
   - WordPressが404エラーページ（HTML）を返すため、MIME typeが`text/html`になる

2. **ファイル存在チェックの欠如**
   - `enqueue.php`でファイル存在確認をせずにエンキューしている
   - `file_exists()`使用時のパス指定ミス（URLとファイルシステムパスの混同）

3. **Dockerコンテナでの権限問題**
   - wp-contentディレクトリの所有者が`www-data`でない可能性
   - ファイルパーミッションの不適切な設定

## 2. 修正案

### 修正案1: ファイル存在チェックの追加（推奨）

**対象ファイル**: `/themes/kei-portfolio/inc/enqueue.php`

```php
<?php
/**
 * スクリプトとスタイルの安全な登録・エンキュー
 */
function kei_portfolio_pro_scripts() {
    // ファイル存在チェック用のヘルパー関数
    $enqueue_if_exists = function($type, $handle, $file_path, $deps = array(), $ver = false, $in_footer = true) {
        // ファイルシステムパス（file_exists用）
        $filesystem_path = get_template_directory() . $file_path;
        
        // ファイルが存在する場合のみエンキュー
        if (file_exists($filesystem_path)) {
            $url = get_template_directory_uri() . $file_path;
            
            if ($type === 'style') {
                wp_enqueue_style($handle, $url, $deps, $ver ?: wp_get_theme()->get('Version'));
            } else {
                wp_enqueue_script($handle, $url, $deps, $ver ?: wp_get_theme()->get('Version'), $in_footer);
            }
            return true;
        }
        
        // デバッグモードの場合はログ出力
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log("Kei Portfolio: Asset file not found: {$file_path}");
        }
        return false;
    };
    
    // メインスタイルシート（存在確認不要）
    wp_enqueue_style('kei-portfolio-style', get_stylesheet_uri(), array(), wp_get_theme()->get('Version'));
    
    // 外部リソース（CDN）
    wp_enqueue_style('kei-portfolio-fonts', 
        'https://fonts.googleapis.com/css2?family=Pacifico:wght@400&family=Noto+Sans+JP:wght@400;500;700;900&display=swap', 
        array(), null);
    
    wp_enqueue_script('tailwindcss', 'https://cdn.tailwindcss.com', array(), '3.4.0', false);
    
    wp_enqueue_style('remixicon', 
        'https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css', 
        array(), '3.5.0');
    
    // ローカルアセット（存在チェック付き）
    $enqueue_if_exists('script', 'kei-portfolio-navigation', '/assets/js/navigation.js', array());
    $enqueue_if_exists('script', 'kei-portfolio-script', '/assets/js/main.js', array('kei-portfolio-navigation'));
    
    // ブログ関連アセット
    if (is_home() || is_archive() || is_single() || is_category() || is_tag() || is_date() || is_author() || is_search()) {
        $enqueue_if_exists('style', 'kei-portfolio-blog', '/assets/css/blog.css', array('kei-portfolio-style'));
        $enqueue_if_exists('style', 'kei-portfolio-blog-mobile', '/assets/css/blog-mobile.css', array('kei-portfolio-blog'));
        $enqueue_if_exists('script', 'kei-portfolio-blog', '/assets/js/blog.js', array('jquery', 'kei-portfolio-script'));
        $enqueue_if_exists('script', 'kei-portfolio-utils', '/assets/js/utils.js', array());
        $enqueue_if_exists('script', 'kei-portfolio-blog-ajax', '/assets/js/blog-ajax.js', 
            array('jquery', 'kei-portfolio-blog', 'kei-portfolio-utils'));
        $enqueue_if_exists('script', 'secure-blog', '/assets/js/secure-blog.js', 
            array('kei-portfolio-blog-ajax', 'kei-portfolio-utils'));
        
        // ローカライズデータ（既存のコードを維持）
        // ...
    }
    
    // 検索ページ専用
    if (is_search()) {
        $enqueue_if_exists('style', 'kei-portfolio-search-styles', '/assets/css/search.css', array('kei-portfolio-blog'));
        $enqueue_if_exists('script', 'kei-portfolio-search', '/assets/js/search.js', array('kei-portfolio-script'));
        // ローカライズデータ（既存のコードを維持）
        // ...
    }
}
add_action('wp_enqueue_scripts', 'kei_portfolio_pro_scripts');
```

### 修正案2: デバッグファイルの削除と環境判定の改善

**対象ファイル**: `/themes/kei-portfolio/inc/environment-config.php`（既存の場合）

```php
<?php
/**
 * 環境設定と開発モード管理
 */
class Kei_Portfolio_Environment {
    
    /**
     * 開発モードかどうかを判定
     */
    public static function is_development_mode() {
        // 複数の条件で判定
        return (
            (defined('WP_DEBUG') && WP_DEBUG) ||
            (defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE === 'development') ||
            (defined('KEI_PORTFOLIO_DEV_MODE') && KEI_PORTFOLIO_DEV_MODE)
        );
    }
    
    /**
     * デバッグアセットをエンキュー（開発環境のみ）
     */
    public static function enqueue_debug_assets() {
        if (!self::is_development_mode()) {
            return;
        }
        
        $debug_css = get_template_directory() . '/assets/css/dev-debug.css';
        $debug_js = get_template_directory() . '/assets/js/dev-debug.js';
        
        // デバッグCSSが存在する場合のみ読み込み
        if (file_exists($debug_css)) {
            wp_enqueue_style(
                'kei-portfolio-dev-debug',
                get_template_directory_uri() . '/assets/css/dev-debug.css',
                array(),
                filemtime($debug_css) // ファイル更新時刻をバージョンとして使用
            );
        }
        
        // デバッグJSが存在する場合のみ読み込み
        if (file_exists($debug_js)) {
            wp_enqueue_script(
                'kei-portfolio-dev-debug',
                get_template_directory_uri() . '/assets/js/dev-debug.js',
                array('jquery'),
                filemtime($debug_js),
                true
            );
        }
    }
}

// デバッグアセットのエンキュー
add_action('wp_enqueue_scripts', array('Kei_Portfolio_Environment', 'enqueue_debug_assets'), 999);
```

### 修正案3: アセット提供用PHPプロキシ（高度な解決策）

**新規ファイル**: `/themes/kei-portfolio/asset-proxy.php`

```php
<?php
/**
 * アセットファイル提供用プロキシ
 * 500エラーを回避し、適切なMIME typeで提供
 */

// WordPressの読み込み
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

// セキュリティチェック
if (!isset($_GET['file'])) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

// ファイルパスの検証とサニタイズ
$requested_file = sanitize_text_field($_GET['file']);
$allowed_extensions = array('css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'woff', 'woff2', 'ttf', 'eot');

// 拡張子のチェック
$extension = strtolower(pathinfo($requested_file, PATHINFO_EXTENSION));
if (!in_array($extension, $allowed_extensions)) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

// ファイルパスの構築（テーマディレクトリ内に制限）
$file_path = get_template_directory() . '/assets/' . $requested_file;

// ディレクトリトラバーサル対策
$real_path = realpath($file_path);
$theme_path = realpath(get_template_directory());
if ($real_path === false || strpos($real_path, $theme_path) !== 0) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

// ファイル存在チェック
if (!file_exists($real_path) || !is_readable($real_path)) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

// MIME typeの設定
$mime_types = array(
    'css'   => 'text/css',
    'js'    => 'application/javascript',
    'jpg'   => 'image/jpeg',
    'jpeg'  => 'image/jpeg',
    'png'   => 'image/png',
    'gif'   => 'image/gif',
    'svg'   => 'image/svg+xml',
    'woff'  => 'font/woff',
    'woff2' => 'font/woff2',
    'ttf'   => 'font/ttf',
    'eot'   => 'application/vnd.ms-fontobject'
);

$mime_type = isset($mime_types[$extension]) ? $mime_types[$extension] : 'application/octet-stream';

// ヘッダーの送信
header("Content-Type: {$mime_type}");
header("Content-Length: " . filesize($real_path));
header("Cache-Control: public, max-age=31536000, immutable");
header("X-Content-Type-Options: nosniff");

// ファイルの出力
readfile($real_path);
exit;
```

### 修正案4: .htaccessによるMIME type強制設定

**対象ファイル**: `/themes/kei-portfolio/.htaccess`（新規作成）

```apache
# MIME typeの明示的な設定
<IfModule mod_mime.c>
    AddType text/css .css
    AddType application/javascript .js
    AddType application/x-javascript .js
    AddType text/javascript .js
    AddType image/svg+xml .svg
    AddType font/woff .woff
    AddType font/woff2 .woff2
    AddType application/vnd.ms-fontobject .eot
    AddType font/ttf .ttf
    AddType font/otf .otf
</IfModule>

# アクセス権限の設定
<FilesMatch "\.(css|js|jpg|jpeg|png|gif|svg|woff|woff2|ttf|eot|otf)$">
    Order Allow,Deny
    Allow from all
</FilesMatch>

# PHP実行の無効化（セキュリティ対策）
<FilesMatch "\.(php|php3|php4|php5|php7|phtml)$">
    Order Deny,Allow
    Deny from all
</FilesMatch>

# キャッシュ設定
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
    ExpiresByType font/woff "access plus 1 year"
    ExpiresByType font/woff2 "access plus 1 year"
</IfModule>
```

### 修正案5: Dockerコンテナの権限修正スクリプト

**新規ファイル**: `/fix-permissions.sh`

```bash
#!/bin/bash

# WordPress Docker コンテナの権限修正スクリプト

echo "WordPressディレクトリの権限を修正中..."

# wp-contentディレクトリの所有者をwww-dataに変更
docker exec wordpress-wordpress-1 chown -R www-data:www-data /var/www/html/wp-content/

# ディレクトリのパーミッションを755に設定
docker exec wordpress-wordpress-1 find /var/www/html/wp-content/ -type d -exec chmod 755 {} \;

# ファイルのパーミッションを644に設定
docker exec wordpress-wordpress-1 find /var/www/html/wp-content/ -type f -exec chmod 644 {} \;

# テーマディレクトリの権限を確認
docker exec wordpress-wordpress-1 ls -la /var/www/html/wp-content/themes/kei-portfolio/

echo "権限修正が完了しました。"
```

## 3. 推奨される実装順序

1. **即座に実装（修正案1）**: `enqueue.php`にファイル存在チェックを追加
2. **次に実装（修正案4）**: `.htaccess`でMIME typeを強制設定
3. **Docker環境の場合（修正案5）**: 権限修正スクリプトを実行
4. **必要に応じて（修正案2）**: 環境判定とデバッグアセットの管理を改善
5. **最終手段（修正案3）**: PHPプロキシによるアセット提供

## 4. テスト手順

### 修正後の確認項目

1. **ブラウザの開発者ツールで確認**
   - Networkタブで全てのアセットが200 OKで読み込まれているか
   - ConsoleタブでMIME typeエラーが解消されているか

2. **E2Eテストの再実行**
   ```bash
   npm run e2e
   ```

3. **手動テスト**
   - 各ページ（/, /about/, /skills/, /contact/, /portfolio/）にアクセス
   - スタイルが正しく適用されているか確認
   - JavaScriptの動作を確認

## 5. 追加の推奨事項

### パフォーマンス最適化

1. **アセットの結合と最小化**
   - 本番環境では複数のCSS/JSファイルを結合
   - minifyして配信サイズを削減

2. **CDNの活用**
   - 静的アセットをCDN経由で配信
   - ブラウザキャッシュの最適化

3. **遅延読み込み**
   - 必要なタイミングでのみアセットを読み込む
   - Critical CSSの実装

### セキュリティ強化

1. **CSPヘッダーの設定**
   - Content Security Policyでリソースの読み込み元を制限

2. **SRIの実装**
   - 外部リソースにSubresource Integrityを設定

3. **定期的な脆弱性スキャン**
   - 使用しているライブラリの脆弱性をチェック

## まとめ

E2Eテストで発生している500エラーとMIME typeエラーは、主に存在しないファイルへのアクセスとファイル存在チェックの欠如が原因です。修正案1の実装により、ほとんどの問題は解決されるはずです。Docker環境特有の権限問題がある場合は、修正案5も併せて実施してください。
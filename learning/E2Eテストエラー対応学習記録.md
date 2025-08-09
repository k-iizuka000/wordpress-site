# E2Eテストエラー対応 学習記録

## 日付: 2025-08-09

## 問題の概要
E2Eテスト（Puppeteer）実行時に、複数ページ（/, /about/, /skills/, /contact/, /portfolio/）でCSS/JSファイルが500 Internal Server ErrorとMIME typeエラーで読み込めない問題が発生。

## エラーの詳細
```
Refused to apply style from 'http://localhost:8090/wp-content/themes/kei-portfolio/style.css?ver=1.0.0' 
because its MIME type ('text/html') is not a supported stylesheet MIME type, 
and strict MIME checking is enabled.
```

## 原因分析

### 1. ファイル存在チェックの欠如
- `enqueue.php`でfile_exists()を使用せずにアセットをエンキュー
- 存在しないファイルへのアクセス時、WordPressが404エラーページ（HTML）を返す
- ブラウザがHTMLをCSS/JSとして解釈しようとしてMIME typeエラー発生

### 2. file_exists()とURLの混同
```php
// 誤った使い方
if (file_exists(get_template_directory_uri() . '/assets/css/style.css')) {
    // file_exists()はファイルシステムパスを期待するが、URLを渡している
}

// 正しい使い方
if (file_exists(get_template_directory() . '/assets/css/style.css')) {
    // get_template_directory()はファイルシステムパスを返す
    wp_enqueue_style('handle', get_template_directory_uri() . '/assets/css/style.css');
}
```

### 3. 開発環境専用ファイルの問題
- dev-debug.css/jsが本番環境に存在しない
- 環境判定なしに読み込もうとしてエラー

## 実装した解決策

### 1. 安全なエンキュー関数（enqueue-safe.php）

```php
/**
 * ファイル存在チェック付きエンキュー関数
 */
$safe_enqueue = function($type, $handle, $file_path, $deps = array(), $ver = false, $extra = null) {
    // ファイルシステムパス（file_exists用）
    $filesystem_path = get_template_directory() . $file_path;
    
    // ファイルが存在する場合のみエンキュー
    if (file_exists($filesystem_path)) {
        $url = get_template_directory_uri() . $file_path;
        $version = $ver ?: wp_get_theme()->get('Version');
        
        if ($type === 'style') {
            $media = $extra ?: 'all';
            wp_enqueue_style($handle, $url, $deps, $version, $media);
        } else {
            $in_footer = $extra !== false ? $extra : true;
            wp_enqueue_script($handle, $url, $deps, $version, $in_footer);
        }
        return true;
    }
    
    // デバッグモードの場合はログ出力
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log("Kei Portfolio: Asset file not found - {$handle}: {$file_path}");
    }
    return false;
};
```

### 2. .htaccessによるMIME type強制設定

```apache
# MIME typeの明示的な設定
<IfModule mod_mime.c>
    AddType text/css .css
    AddType application/javascript .js
    AddType image/svg+xml .svg
    AddType font/woff2 .woff2
</IfModule>

# セキュリティヘッダー
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set Cache-Control "public, max-age=31536000, immutable"
</IfModule>
```

### 3. 開発環境判定の改善

```php
// 開発モードでのデバッグアセット（存在する場合のみ）
if (defined('WP_DEBUG') && WP_DEBUG) {
    $debug_css_file = get_template_directory() . '/assets/css/dev-debug.css';
    if (file_exists($debug_css_file)) {
        wp_enqueue_style(
            'kei-portfolio-dev-debug',
            get_template_directory_uri() . '/assets/css/dev-debug.css',
            array(),
            filemtime($debug_css_file) // ファイル更新時刻をバージョンとして使用
        );
    }
}
```

## 学習ポイント

### WordPressの関数使い分け

| 関数 | 返り値 | 用途 |
|------|--------|------|
| get_template_directory() | /var/www/html/wp-content/themes/theme-name | file_exists(), require_once() |
| get_template_directory_uri() | http://example.com/wp-content/themes/theme-name | wp_enqueue_style(), wp_enqueue_script() |
| get_stylesheet_directory() | 子テーマのファイルシステムパス | 子テーマでのfile_exists() |
| get_stylesheet_directory_uri() | 子テーマのURL | 子テーマでのenqueue |

### Docker環境での権限管理

```bash
# 正しい権限設定
docker exec container-name chown -R www-data:www-data /var/www/html/wp-content/
docker exec container-name find /var/www/html/wp-content/ -type d -exec chmod 755 {} \;
docker exec container-name find /var/www/html/wp-content/ -type f -exec chmod 644 {} \;
```

### MIME typeエラーのデバッグ方法

1. **ブラウザの開発者ツール**
   - NetworkタブでResponse Headersを確認
   - Content-Typeが正しいか確認

2. **curlでの確認**
   ```bash
   curl -I http://localhost:8090/wp-content/themes/kei-portfolio/style.css
   ```

3. **WordPressデバッグログ**
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   define('WP_DEBUG_DISPLAY', false);
   ```

## 技術的負債と今後の課題

### 現在の技術的負債
1. **アセット管理の分散**: 複数ファイルに分散していて管理が煩雑
2. **ビルドプロセスの欠如**: minifyや結合が手動
3. **環境別設定の不足**: 開発/ステージング/本番の切り分けが不明確

### 改善提案

#### 短期的改善
- [ ] 全アセットファイルの存在確認を実装
- [ ] エラーログの監視強化
- [ ] アセットのバージョン管理改善

#### 中期的改善
- [ ] webpack/Viteによるビルドパイプライン構築
- [ ] 環境変数による設定管理（.env使用）
- [ ] アセットのCDN配信対応

#### 長期的改善
- [ ] TypeScriptの導入
- [ ] CSS-in-JSまたはCSS Modulesの検討
- [ ] パフォーマンス監視ツールの導入

## 参考リンク
- [WordPress Developer Resources - wp_enqueue_script()](https://developer.wordpress.org/reference/functions/wp_enqueue_script/)
- [MDN - MIME types](https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/MIME_types)
- [WordPress Codex - File Permissions](https://wordpress.org/support/article/changing-file-permissions/)

## まとめ
E2Eテストのエラーは、基本的なファイル存在チェックの欠如とWordPress関数の誤用が原因だった。file_exists()とget_template_directory()の正しい使い方を理解し、適切なエラーハンドリングを実装することで解決できた。今後は、より堅牢なアセット管理システムの構築が必要。
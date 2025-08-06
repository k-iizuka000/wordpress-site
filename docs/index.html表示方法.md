# index.htmlをWordPressサイトで表示する方法

## 方法1: WordPressのフロントページテンプレートとして使用（推奨）

### 手順
1. `index.html`の内容を`front-page.php`として保存
2. WordPressテーマディレクトリにアップロード
3. WordPress管理画面で設定

```bash
# ローカルで作業
cp index.html wp-theme/kei-portfolio/front-page.php

# front-page.phpの先頭に以下を追加
<?php
/**
 * フロントページテンプレート
 */
?>

# FTPでアップロード
/wp-content/themes/kei-portfolio/front-page.php
```

### WordPress管理画面での設定
1. 設定 → 表示設定
2. 「ホームページの表示」を「固定ページ」に変更
3. 「ホームページ」に任意のページを選択

## 方法2: 静的HTMLとWordPressの共存

### A. .htaccessを使用
```apache
# ルートディレクトリの.htaccessに追加
DirectoryIndex index.html index.php

# または特定のファイルを優先
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteRule ^$ /index.html [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
```

### B. サブディレクトリ構成
```
kei-aokiki.dev/
├── index.html          # 静的トップページ
├── portfolio.html      # その他の静的ページ
├── blog/              # WordPressをサブディレクトリに
│   └── (WordPress files)
```

## 方法3: WordPressテーマに組み込む（最も柔軟）

### 実装手順
1. **テーマファイルの準備**
```bash
# 現在のファイルをテーマディレクトリにコピー
cp index.html wp-theme/kei-portfolio/front-page.php
cp styles.css wp-theme/kei-portfolio/assets/css/main.css
cp script.js wp-theme/kei-portfolio/assets/js/main.js
```

2. **front-page.phpの修正**
```php
<?php
/**
 * トップページテンプレート
 */
get_header(); // WordPressのヘッダーを使う場合
?>

<!-- index.htmlの内容をここに -->
<!-- パスの修正が必要 -->
<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/assets/css/main.css">
<script src="<?php echo get_template_directory_uri(); ?>/assets/js/main.js"></script>

<?php get_footer(); // WordPressのフッターを使う場合 ?>
```

## 方法4: プラグインを使用

### Static HTML Output Plugins
- WP Static HTML Output
- Simply Static

これらのプラグインは逆のアプローチ（WordPressから静的HTMLを生成）

## 推奨される実装方法

### 短期的解決策（すぐに表示したい場合）
1. FTPで`index.html`、`styles.css`、`script.js`をルートディレクトリにアップロード
2. `.htaccess`でindex.htmlを優先表示

### 長期的解決策（運用を考慮）
1. 現在のHTMLをWordPressテーマに統合
2. コンテンツ管理をWordPress化
3. 動的な部分（ブログ、制作実績）をWordPressで管理

## 実装例

### .htaccessの設定例
```apache
# WordPressの前にindex.htmlをチェック
DirectoryIndex index.html index.php

# WordPressのルール
# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteRule ^index\.html$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
# END WordPress
```

## 注意点
- SEO的には1つのドメインに1つの統一されたシステムが理想
- 混在させる場合はURLの設計を慎重に
- 将来的にはWordPressテーマとして統合することを推奨
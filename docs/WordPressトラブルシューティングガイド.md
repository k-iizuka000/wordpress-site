# WordPressトラブルシューティングガイド

## 目次
1. [404エラーが再発した場合の対処法](#404エラーが再発した場合の対処法)
2. [パーマリンク設定の確認方法](#パーマリンク設定の確認方法)
3. [キャッシュクリアの方法](#キャッシュクリアの方法)
4. [データベース不整合の修正方法](#データベース不整合の修正方法)
5. [その他のよくある問題](#その他のよくある問題)

---

## 404エラーが再発した場合の対処法

### 症状
- 固定ページ（/about, /skills, /contact, /portfolio）にアクセスすると404エラー
- メニューリンクをクリックしても「ページが見つかりません」

### 原因分析のステップ

#### 1. ページの存在確認
1. 管理画面で **「固定ページ」** → **「固定ページ一覧」** を確認
2. 該当ページのステータスが「公開済み」であることを確認
3. スラッグ（URL）が正しいことを確認

#### 2. パーマリンク構造の確認
1. **「設定」** → **「パーマリンク」** にアクセス
2. 現在の設定を確認
3. **「変更を保存」** ボタンをクリック（.htaccessを再生成）

#### 3. テンプレートファイルの確認
```bash
# テーマディレクトリの確認
ls -la /path/to/wordpress/wp-content/themes/kei-portfolio/

# 必要なテンプレートファイルの存在確認
page-about.php
page-skills.php
page-contact.php
page-portfolio.php
```

### 解決手順

#### 手順1: パーマリンクの再設定
1. 管理画面 **「設定」** → **「パーマリンク」**
2. **「投稿名」** を選択
3. **「変更を保存」** をクリック
4. サイトをリロードして確認

#### 手順2: .htaccessファイルの確認
```bash
# .htaccessの内容を確認
cat /var/www/html/.htaccess

# 内容が以下のようになっているか確認:
# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
# END WordPress
```

#### 手順3: 固定ページの再作成
```php
<?php
// create-missing-pages.php
require_once('wp-config.php');

$pages = [
    ['title' => 'About', 'slug' => 'about'],
    ['title' => 'Skills', 'slug' => 'skills'],
    ['title' => 'Contact', 'slug' => 'contact'],
    ['title' => 'Portfolio', 'slug' => 'portfolio']
];

foreach ($pages as $page) {
    $existing = get_page_by_path($page['slug']);
    if (!$existing) {
        wp_insert_post([
            'post_title' => $page['title'],
            'post_name' => $page['slug'],
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_content' => '[' . strtolower($page['slug']) . '-content]'
        ]);
        echo "Created: {$page['title']}\n";
    }
}
?>
```

---

## パーマリンク設定の確認方法

### 推奨設定
- **投稿名**: `/%postname%/`
- **カテゴリベース**: 空欄または `blog`
- **タグベース**: 空欄または `tag`

### 設定手順
1. 管理画面 **「設定」** → **「パーマリンク」**
2. **「投稿名」** を選択
3. **「変更を保存」** をクリック

### 設定確認スクリプト
```php
<?php
// check-permalinks.php
require_once('wp-config.php');

echo "現在のパーマリンク設定:\n";
echo "構造: " . get_option('permalink_structure') . "\n";
echo "カテゴリベース: " . get_option('category_base') . "\n";
echo "タグベース: " . get_option('tag_base') . "\n";

echo "\n固定ページURL確認:\n";
$pages = get_pages();
foreach ($pages as $page) {
    echo "- {$page->post_title}: " . get_permalink($page->ID) . "\n";
}
?>
```

---

## キャッシュクリアの方法

### 1. ブラウザキャッシュのクリア
- **Chrome**: Ctrl+Shift+R または F12 → Network → Disable cache
- **Firefox**: Ctrl+Shift+R または F12 → Network → Disable cache
- **Safari**: Cmd+Option+R

### 2. WordPressキャッシュのクリア

#### オブジェクトキャッシュ
```php
<?php
// clear-cache.php
wp_cache_flush();
echo "オブジェクトキャッシュをクリアしました。\n";
?>
```

#### データベースキャッシュ
```sql
-- wp_optionsテーブルのキャッシュ関連をクリア
DELETE FROM wp_options WHERE option_name LIKE '%_transient_%';
DELETE FROM wp_options WHERE option_name LIKE '%_site_transient_%';
```

### 3. サーバーレベルキャッシュ

#### Apacheの場合
```bash
# Apache設定でキャッシュを無効化
<IfModule mod_headers.c>
    Header set Cache-Control "no-cache, no-store, must-revalidate"
    Header set Pragma "no-cache"
    Header set Expires 0
</IfModule>
```

#### Dockerコンテナの場合
```bash
# コンテナの再起動
docker-compose restart wordpress

# キャッシュを完全にクリア
docker-compose down
docker-compose up -d
```

---

## データベース不整合の修正方法

### 1. データベース接続の確認
```php
<?php
// db-check.php
require_once('wp-config.php');

global $wpdb;
$result = $wpdb->get_results("SHOW TABLES");

echo "データベース接続: " . ($result ? "OK" : "NG") . "\n";
echo "テーブル数: " . count($result) . "\n";

foreach ($result as $table) {
    $table_name = array_values((array)$table)[0];
    echo "- {$table_name}\n";
}
?>
```

### 2. 固定ページのデータ整合性チェック
```php
<?php
// page-integrity-check.php
require_once('wp-config.php');

global $wpdb;

echo "=== 固定ページデータ整合性チェック ===\n";

// 1. 公開済み固定ページの確認
$pages = $wpdb->get_results("
    SELECT ID, post_title, post_name, post_status 
    FROM {$wpdb->posts} 
    WHERE post_type = 'page' 
    AND post_status = 'publish'
");

echo "公開済み固定ページ:\n";
foreach ($pages as $page) {
    echo "- ID:{$page->ID} {$page->post_title} (/{$page->post_name}/)\n";
}

// 2. メタデータの確認
echo "\nメタデータチェック:\n";
foreach ($pages as $page) {
    $meta = $wpdb->get_results($wpdb->prepare("
        SELECT meta_key, meta_value 
        FROM {$wpdb->postmeta} 
        WHERE post_id = %d
    ", $page->ID));
    
    echo "- {$page->post_title}:\n";
    foreach ($meta as $m) {
        echo "  {$m->meta_key}: {$m->meta_value}\n";
    }
}

// 3. 重複チェック
echo "\n重複ページチェック:\n";
$duplicates = $wpdb->get_results("
    SELECT post_name, COUNT(*) as count 
    FROM {$wpdb->posts} 
    WHERE post_type = 'page' 
    AND post_status = 'publish'
    GROUP BY post_name 
    HAVING count > 1
");

if (empty($duplicates)) {
    echo "重複なし\n";
} else {
    foreach ($duplicates as $dup) {
        echo "- 重複スラッグ: {$dup->post_name} ({$dup->count}件)\n";
    }
}
?>
```

### 3. データベース修復コマンド
```sql
-- 1. テーブルの修復
REPAIR TABLE wp_posts;
REPAIR TABLE wp_postmeta;
REPAIR TABLE wp_options;

-- 2. オートインクリメントのリセット
ALTER TABLE wp_posts AUTO_INCREMENT = 1;

-- 3. 孤立メタデータの削除
DELETE pm FROM wp_postmeta pm 
LEFT JOIN wp_posts p ON p.ID = pm.post_id 
WHERE p.ID IS NULL;

-- 4. 重複スラッグの修正
UPDATE wp_posts SET post_name = CONCAT(post_name, '-', ID) 
WHERE post_name IN (
    SELECT temp.post_name FROM (
        SELECT post_name FROM wp_posts 
        WHERE post_type = 'page' 
        GROUP BY post_name HAVING COUNT(*) > 1
    ) temp
) AND ID NOT IN (
    SELECT temp.min_id FROM (
        SELECT MIN(ID) as min_id FROM wp_posts 
        WHERE post_type = 'page' 
        GROUP BY post_name
    ) temp
);
```

### 4. 自動修復スクリプト
```php
<?php
// auto-repair.php
require_once('wp-config.php');

global $wpdb;

echo "=== データベース自動修復 ===\n";

// 1. 必要なページが存在するかチェック
$required_pages = ['about', 'skills', 'contact', 'portfolio'];
$missing_pages = [];

foreach ($required_pages as $slug) {
    $page = $wpdb->get_row($wpdb->prepare("
        SELECT * FROM {$wpdb->posts} 
        WHERE post_name = %s 
        AND post_type = 'page' 
        AND post_status = 'publish'
    ", $slug));
    
    if (!$page) {
        $missing_pages[] = $slug;
    }
}

// 2. 不足ページの作成
if (!empty($missing_pages)) {
    echo "不足ページを作成中...\n";
    foreach ($missing_pages as $slug) {
        $title = ucfirst($slug);
        $page_id = wp_insert_post([
            'post_title' => $title,
            'post_name' => $slug,
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_content' => "[{$slug}-content]"
        ]);
        echo "- {$title} (ID: {$page_id})\n";
    }
} else {
    echo "すべての必要ページが存在します。\n";
}

// 3. パーマリンクの再生成
flush_rewrite_rules();
echo "パーマリンクを再生成しました。\n";

echo "修復完了。\n";
?>
```

---

## その他のよくある問題

### 1. テーマが表示されない
**症状**: サイトの見た目が崩れている、デフォルトテーマになっている

**対処法**:
```bash
# 1. テーマディレクトリの確認
ls -la wp-content/themes/kei-portfolio/

# 2. テーマのアクティベート
wp theme activate kei-portfolio

# 3. style.cssの確認
grep "Theme Name" wp-content/themes/kei-portfolio/style.css
```

### 2. JavaScript/CSSが読み込まれない
**症状**: スタイルや機能が正常に動作しない

**対処法**:
```php
<?php
// asset-check.php
require_once('wp-config.php');

// エンキューされたスクリプトとスタイルを確認
global $wp_scripts, $wp_styles;

echo "登録済みスクリプト:\n";
foreach ($wp_scripts->registered as $handle => $script) {
    echo "- {$handle}: {$script->src}\n";
}

echo "\n登録済みスタイル:\n";
foreach ($wp_styles->registered as $handle => $style) {
    echo "- {$handle}: {$style->src}\n";
}
?>
```

### 3. メモリ不足エラー
**症状**: "Fatal error: Allowed memory size exhausted"

**対処法**:
```php
// wp-config.phpに追加
ini_set('memory_limit', '256M');
define('WP_MEMORY_LIMIT', '256M');
```

### 4. プラグインの競合
**症状**: 機能が正常に動作しない、エラーが発生

**対処法**:
1. すべてのプラグインを無効化
2. 一つずつ有効化してテスト
3. 問題のプラグインを特定して削除または更新

---

## 緊急時の対応手順

### 1. サイトが完全にアクセス不能
```bash
# 1. Dockerコンテナの状態確認
docker-compose ps

# 2. コンテナの再起動
docker-compose restart

# 3. ログの確認
docker-compose logs wordpress
```

### 2. データベースが破損
```bash
# 1. データベースバックアップの復元
docker exec kei-portfolio-db mysql -u root -p kei_portfolio_dev < backup.sql

# 2. テーブルの修復
docker exec kei-portfolio-db mysqlcheck -u root -p --auto-repair kei_portfolio_dev
```

### 3. 管理画面にアクセスできない
```php
<?php
// emergency-admin.php
// 新しい管理者ユーザーを作成
require_once('wp-config.php');

$username = 'emergency';
$password = 'temp_password_123';
$email = 'admin@example.com';

$user_id = wp_create_user($username, $password, $email);
$user = new WP_User($user_id);
$user->set_role('administrator');

echo "緊急管理者ユーザー作成: {$username} / {$password}\n";
?>
```

---

## 予防メンテナンス

### 1. 定期バックアップ
```bash
# データベースバックアップ
docker exec kei-portfolio-db mysqldump -u root -p kei_portfolio_dev > backup_$(date +%Y%m%d).sql

# ファイルバックアップ  
tar -czf wordpress_files_$(date +%Y%m%d).tar.gz wordpress/
```

### 2. ログ監視
```bash
# エラーログの確認
tail -f wordpress/wp-content/debug.log

# Apacheエラーログ
docker logs kei-portfolio-dev
```

### 3. 定期チェックスクリプト
```php
<?php
// health-check.php
require_once('wp-config.php');

$checks = [
    'database_connection' => wp_db_check(),
    'pages_accessible' => check_pages_accessible(),
    'theme_active' => wp_get_theme()->get('Name') === 'Kei Portfolio',
    'memory_usage' => memory_get_usage(true),
    'disk_space' => disk_free_space('.')
];

foreach ($checks as $check => $result) {
    echo "{$check}: " . (is_bool($result) ? ($result ? 'OK' : 'NG') : $result) . "\n";
}
?>
```

---

最終更新日: 2025年8月7日
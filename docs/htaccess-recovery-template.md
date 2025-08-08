# WordPress .htaccess 復旧テンプレートと運用手順

## 概要
WordPressサイトで.htaccessファイルが破損した際の標準復旧テンプレートと運用手順書です。

## 1. 標準 .htaccess テンプレート

### 基本テンプレート（WordPress標準）
```apache
# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
# END WordPress
```

### セキュリティ強化版テンプレート
```apache
# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /

# セキュリティ設定
# wp-config.phpへの直接アクセス禁止
<Files wp-config.php>
order allow,deny
deny from all
</Files>

# xmlrpc.php攻撃対策
<Files xmlrpc.php>
order allow,deny
deny from all
</Files>

# 管理者以外のファイル編集禁止
<Files "*.php">
Order Deny,Allow
Deny from all
Allow from 127.0.0.1
</Files>

# WordPress標準リライトルール
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
# END WordPress
```

## 2. 復旧手順

### 緊急時の復旧手順（最優先）

1. **バックアップの確認**
   ```bash
   # 現在の.htaccessをバックアップ（破損していても）
   cp .htaccess .htaccess.backup.$(date +%Y%m%d_%H%M%S)
   ```

2. **基本テンプレートの復旧**
   ```bash
   # 新しい.htaccessファイルを作成
   cat > .htaccess << 'EOF'
# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
# END WordPress
EOF
   ```

3. **権限設定**
   ```bash
   chmod 644 .htaccess
   chown www-data:www-data .htaccess  # または適切なユーザー:グループ
   ```

4. **動作確認**
   - サイトトップページにアクセス
   - 固定ページへのアクセス確認
   - 管理画面へのアクセス確認

### WordPress管理画面からの復旧手順

1. **管理画面にログイン**
   - `/wp-admin/` にアクセス

2. **パーマリンク設定から復旧**
   - 設定 → パーマリンク設定
   - 「変更を保存」ボタンをクリック
   - WordPressが自動的に.htaccessを再生成

## 3. 運用チェックリスト

### 復旧前チェック
- [ ] サイトの症状確認（404エラー、ページが表示されない等）
- [ ] .htaccessファイルの存在確認
- [ ] Apache mod_rewriteモジュールの有効化確認
- [ ] ディレクトリ権限の確認

### 復旧後チェック
- [ ] トップページの表示確認
- [ ] 固定ページの表示確認
- [ ] 投稿ページの表示確認
- [ ] 管理画面アクセスの確認
- [ ] プラグインページの動作確認
- [ ] 画像ファイルの表示確認

## 4. トラブルシューティング

### よくある問題と対処法

#### 問題1: 500 Internal Server Errorが発生
**原因**: 構文エラーまたは不適切な設定
**対処法**:
```bash
# エラーログの確認
tail -f /var/log/apache2/error.log

# 基本テンプレートに戻す
cp .htaccess.backup.original .htaccess
```

#### 問題2: パーマリンクが動作しない
**原因**: mod_rewriteが無効または設定不足
**対処法**:
```bash
# mod_rewriteの有効化確認
apache2ctl -M | grep rewrite

# 有効化
a2enmod rewrite
systemctl restart apache2
```

#### 問題3: 管理画面にアクセスできない
**原因**: セキュリティ設定が強すぎる
**対処法**:
```bash
# 一時的に基本テンプレートに戻す
# その後、段階的にセキュリティ設定を追加
```

## 5. 予防策と定期メンテナンス

### バックアップ戦略
```bash
# 週次バックアップスクリプト例
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
cp .htaccess backups/.htaccess.backup.$DATE

# 古いバックアップの削除（30日以上前）
find backups/ -name ".htaccess.backup.*" -mtime +30 -delete
```

### 監視設定
- サイト監視ツールでの定期チェック
- ログ監視によるエラー検知
- パフォーマンス監視

## 6. 緊急連絡先とエスカレーション

### 対応レベル
1. **レベル1**: 基本復旧手順で解決
2. **レベル2**: 専門知識が必要（サーバー管理者に連絡）
3. **レベル3**: 緊急事態（すべての関係者に連絡）

### 文書管理
- 本テンプレートの更新履歴記録
- 復旧作業の実施記録
- 学習記録への技術的知見の蓄積

---

**最終更新日**: 2025-08-08  
**作成者**: WordPress運用チーム  
**レビュー周期**: 3ヶ月毎
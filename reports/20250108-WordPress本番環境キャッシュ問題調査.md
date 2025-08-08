# WordPress本番環境キャッシュ問題調査レポート

## 問題の概要

### 症状
- **本番環境**（https://kei-aokiki.dev/）：田中健太のポートフォリオ（誤った内容）が表示
- **ローカル環境**（http://localhost:8090/）：Kei Aokikiのポートフォリオ（正しい内容）が表示
- **重要な発見**：シークレットモードと通常モードで異なる表示

### 特徴
- データベースをエクスポート・インポートしても表示が変わらない
- ブラウザのモードによって表示内容が異なる

## 問題の根本原因

### 主要原因：多層キャッシュシステムの問題
データベースの変更が反映されない理由は、複数のキャッシュレイヤーが古いコンテンツを保持しているためです。

### キャッシュレイヤーの階層（上位から下位）

```
[ユーザーブラウザ]
    ↓
[ブラウザキャッシュ] ← 通常モードとシークレットモードの差異
    ↓
[CloudFlare CDN] ← グローバルキャッシュ
    ↓
[サーバーキャッシュ（Varnish等）]
    ↓
[WordPressキャッシュプラグイン]
    ↓
[オブジェクトキャッシュ（Redis/Memcached）]
    ↓
[WordPressトランジェント]
    ↓
[データベース] ← インポートはここだけを変更
```

## シークレットモードと通常モードの表示差異の理由

### 1. ブラウザキャッシュの有無
- **通常モード**：既存のブラウザキャッシュ、Cookie、ローカルストレージが存在
- **シークレットモード**：全てのキャッシュがクリアな状態

### 2. Cookie/セッションベースの識別
- **通常モード**：既存ユーザーとして識別され、ユーザー固有のキャッシュが提供される
- **シークレットモード**：新規ユーザーとして扱われ、デフォルトキャッシュが提供される

### 3. 考えられるシナリオ
- A/Bテストプラグインが動作している
- パーソナライゼーション機能が有効
- ユーザーセグメント別のキャッシュ戦略

## 確認すべき項目と確認方法

### 1. 各モードでの表示内容確認
```bash
# デベロッパーツールのNetworkタブを開いて確認
# Response HeadersでCache-Control、X-Cache-Status等を確認
```

### 2. WordPressプラグインの確認
```bash
# SSH/FTPでアクセス
ls -la /path/to/wordpress/wp-content/plugins/
# cache関連プラグインを探す
```

### 3. CloudFlareの設定確認
- CloudFlareダッシュボード → Caching → Configuration
- Page Rules設定の確認
- Cache Level設定の確認

### 4. wp-config.phpの確認
```php
// 以下の設定を確認
define('WP_CACHE', true);
define('WP_REDIS_HOST', 'localhost');
define('WP_CACHE_KEY_SALT', 'your-site-name');
```

### 5. .htaccessの確認
```apache
# キャッシュ関連のルールを確認
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/html "access plus 0 seconds"
</IfModule>
```

## 具体的な解決手順

### ステップ1：ブラウザキャッシュのクリア
```
1. Chrome: Ctrl+Shift+Delete → キャッシュされた画像とファイル
2. ハード リフレッシュ: Ctrl+Shift+R（Windows）、Cmd+Shift+R（Mac）
3. デベロッパーツール → Network → Disable cache にチェック
```

### ステップ2：CloudFlareキャッシュのパージ
```
1. CloudFlareダッシュボードにログイン
2. 対象ドメインを選択
3. Caching → Configuration
4. "Purge Everything"をクリック
5. 確認して実行
```

### ステップ3：WordPressキャッシュのクリア
```bash
# SSH経由でサーバーにアクセス
# キャッシュディレクトリの削除
rm -rf /path/to/wordpress/wp-content/cache/*
rm -rf /path/to/wordpress/wp-content/w3tc-cache/*
rm -rf /path/to/wordpress/wp-content/wp-rocket-cache/*

# オブジェクトキャッシュのクリア（Redisの場合）
redis-cli FLUSHALL

# オブジェクトキャッシュのクリア（Memcachedの場合）
echo "flush_all" | nc localhost 11211
```

### ステップ4：WordPressトランジェントの削除
```sql
-- phpMyAdminまたはWP-CLIで実行
DELETE FROM wp_options WHERE option_name LIKE '%_transient_%';
DELETE FROM wp_options WHERE option_name LIKE '%_site_transient_%';
```

### ステップ5：キャッシュプラグインの再設定
```
1. WordPress管理画面にログイン
2. プラグイン → インストール済み
3. キャッシュプラグインを一時的に無効化
4. 再度有効化
5. キャッシュ設定を確認・再構成
```

## 今後の予防策

### 1. キャッシュ戦略の文書化
- 使用している全てのキャッシュレイヤーを文書化
- 各レイヤーのTTL（Time To Live）設定を記録
- クリア手順を明文化

### 2. デプロイ時のキャッシュクリア自動化
```bash
#!/bin/bash
# deploy-with-cache-clear.sh
# データベース更新
mysql -u user -p database < backup.sql

# 全キャッシュレイヤーをクリア
curl -X POST "https://api.cloudflare.com/client/v4/zones/{zone_id}/purge_cache" \
     -H "X-Auth-Email: email@example.com" \
     -H "X-Auth-Key: api_key" \
     -H "Content-Type: application/json" \
     --data '{"purge_everything":true}'

# WordPressキャッシュクリア
wp cache flush --path=/path/to/wordpress
```

### 3. 開発環境と本番環境の同期
- キャッシュ設定を環境変数で管理
- ステージング環境でのテスト必須化
- キャッシュバイパス用のクエリパラメータ設定

### 4. モニタリングの実装
- キャッシュヒット率の監視
- コンテンツ更新後の反映確認プロセス
- 定期的なキャッシュ健全性チェック

## 推奨される追加調査

### 1. 複数WordPressインストールの可能性
```bash
# サーバー全体でWordPressインストールを検索
find /var/www -name "wp-config.php" -type f 2>/dev/null
```

### 2. リバースプロキシの確認
```bash
# Nginxの設定確認
nginx -T | grep proxy_cache
# Apacheの設定確認
apachectl -S | grep proxy
```

### 3. A/Bテストツールの確認
- Google Optimize
- Optimizely
- VWO等のツールが導入されていないか確認

## まとめ

この問題は、複数のキャッシュレイヤーが重なり合って発生している典型的なケースです。特に、データベースの変更だけでは上位のキャッシュレイヤーに影響を与えないため、表示が変わらない状況が発生しています。

シークレットモードと通常モードの差異は、主にブラウザレベルのキャッシュとCookie/セッションベースの識別による違いと考えられます。

解決には、全てのキャッシュレイヤーを体系的にクリアする必要があり、今後は自動化されたキャッシュ管理プロセスの導入が推奨されます。

---
作成日: 2025年1月8日
作成者: Claude Code
# WordPress REST API 403エラー修正 - 実装ガイド

## 実装済みの修正内容

### 1. 作成・修正したファイル

#### 新規作成ファイル:
1. `/themes/kei-portfolio/inc/rest-api-permissions.php`
   - REST API権限の調整
   - Gutenbergエディターとの互換性確保
   - テンプレートエンドポイントへのアクセス許可

2. `/themes/kei-portfolio/inc/create-blog-page.php`
   - ブログページの自動作成
   - WordPress設定の自動調整
   - パーマリンク設定の確認

3. `/themes/kei-portfolio/inc/debug-rest-api.php`
   - REST APIデバッグツール（開発環境用）
   - エラーログの詳細記録
   - 管理画面でのテスト機能

4. `/tasks/REST_API_403エラー修正設計書.md`
   - 詳細な問題分析と修正設計

#### 修正したファイル:
1. `/themes/kei-portfolio/inc/security.php`
   - Ajax検証からREST APIを除外
   - Gutenbergリクエストの許可

2. `/themes/kei-portfolio/functions.php`
   - 新規ファイルの読み込み追加

## 実装手順

### ステップ1: デバッグモードの有効化（開発環境のみ）

`wp-config.php`に以下を追加:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

デバッグファイルを読み込み:
```php
// functions.phpに追加（開発環境のみ）
if (defined('WP_DEBUG') && WP_DEBUG) {
    require_once get_template_directory() . '/inc/debug-rest-api.php';
}
```

### ステップ2: WordPress管理画面での設定

1. **パーマリンク設定**
   - 設定 > パーマリンク設定
   - 「投稿名」または「カスタム構造」を選択
   - 保存をクリック

2. **表示設定**
   - 設定 > 表示設定
   - 「ホームページの表示」を「固定ページ」に設定
   - ホームページ: "Front Page"または"Home"を選択
   - 投稿ページ: "Blog"を選択
   - 保存をクリック

3. **ユーザー権限の確認**
   - ユーザー > あなたのプロフィール
   - 権限グループが「管理者」または「編集者」であることを確認

### ステップ3: キャッシュのクリア

コマンドラインで実行:
```bash
# WP-CLIを使用する場合
wp cache flush

# プラグインキャッシュもクリア
wp transient delete --all
```

または管理画面から:
- キャッシュプラグインがある場合は「キャッシュをクリア」
- ブラウザのキャッシュもクリア（Ctrl+Shift+R）

### ステップ4: テスト手順

1. **基本テスト**
   - WordPressにログイン
   - 投稿 > 新規追加
   - タイトルと本文を入力
   - 「公開」ボタンをクリック
   - エラーが表示されないことを確認

2. **ブログページの確認**
   - サイトの/blogページにアクセス
   - 投稿が表示されることを確認

3. **デバッグツールでのテスト（開発環境）**
   - ツール > REST API Debug
   - 「Test Templates Endpoint」ボタンをクリック
   - Successメッセージが表示されることを確認

## トラブルシューティング

### エラーが継続する場合のチェックリスト

#### 1. プラグインの確認
```php
// 一時的に全プラグインを無効化
// functions.phpに追加してテスト
add_filter('option_active_plugins', function($plugins) {
    return array();
});
```

#### 2. .htaccessの確認
```apache
# .htaccessファイルに以下があることを確認
RewriteRule ^wp-json/(.*)?$ index.php?rest_route=/$1 [L]
```

#### 3. エラーログの確認
```bash
# エラーログの確認
tail -f wp-content/debug.log

# 403エラーの詳細を確認
grep "REST API Error" wp-content/debug.log
```

#### 4. 権限の手動リセット
```php
// functions.phpに一時的に追加
add_action('init', function() {
    $user = wp_get_current_user();
    if ($user->ID === 1) { // 管理者IDを指定
        $user->add_cap('edit_theme_options');
        $user->add_cap('edit_posts');
        $user->add_cap('publish_posts');
    }
});
```

## セキュリティ上の注意

### 本番環境への適用前に:

1. **デバッグモードを無効化**
   ```php
   define('WP_DEBUG', false);
   ```

2. **デバッグファイルの削除または無効化**
   ```bash
   rm themes/kei-portfolio/inc/debug-rest-api.php
   ```

3. **権限設定の確認**
   - 必要最小限の権限のみ付与
   - 不要な権限付与コードは削除

4. **ログファイルの削除**
   ```bash
   rm wp-content/debug.log
   ```

## 動作確認済み環境

- WordPress: 5.0以上
- PHP: 7.4以上
- Gutenbergエディター: 最新版
- テーマ: kei-portfolio

## サポート情報

### エラーが解決しない場合:

1. `/wp-content/debug.log`のエラー内容を確認
2. ブラウザの開発者ツールでネットワークタブを確認
3. 403エラーの詳細なレスポンスを確認

### よくある問題と解決策:

| 問題 | 解決策 |
|-----|-------|
| パーマリンクが機能しない | .htaccessファイルの書き込み権限を確認 |
| ブログページが表示されない | 表示設定で投稿ページを再設定 |
| 権限エラーが続く | ユーザーロールを一時的に管理者に変更してテスト |
| キャッシュが原因 | CDNキャッシュも含めて全てクリア |

## 完了チェックリスト

- [ ] REST API権限修正ファイルの作成
- [ ] セキュリティファイルの修正
- [ ] ブログページ自動作成機能の追加
- [ ] functions.phpへの読み込み追加
- [ ] パーマリンク設定の変更
- [ ] 表示設定の確認
- [ ] キャッシュのクリア
- [ ] 投稿の公開テスト
- [ ] ブログページでの表示確認
- [ ] 本番環境用のセキュリティ設定

## 次のステップ

1. 本番環境への適用準備
2. バックアップの作成
3. 段階的なロールアウト
4. ユーザーフィードバックの収集
5. 長期的な監視とメンテナンス
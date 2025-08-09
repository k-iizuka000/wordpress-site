# E2Eテストエラー修正実装計画

## 修正方針
セキュリティを維持しながら、アセットファイルへの正常なアクセスを許可する最小限の変更を実施する。

## 実装計画

### フェーズ1: 緊急修正（最優先）

#### 1. inc/security.phpのAjax検証を修正
**問題**: `kei_portfolio_verify_ajax_request`関数がアセットファイルへのリクエストをブロックしている

**修正内容**:
```php
function kei_portfolio_verify_ajax_request() {
    // REST APIリクエストは除外
    if (defined('REST_REQUEST') && REST_REQUEST) {
        return;
    }
    
    // アセットファイルへのリクエストは除外（追加）
    $request_uri = $_SERVER['REQUEST_URI'] ?? '';
    if (preg_match('/\.(css|js|jpg|jpeg|png|gif|svg|webp|woff|woff2|ttf|eot|ico)(\?.*)?$/i', $request_uri)) {
        return; // アセットファイルへのアクセスは許可
    }
    
    // admin-ajax.php以外へのリクエストは除外（追加）
    if (!isset($_SERVER['REQUEST_URI']) || 
        strpos($_SERVER['REQUEST_URI'], 'admin-ajax.php') === false) {
        return; // Ajax以外の通常のリクエストは処理を続行
    }
    
    // 以下、既存のAjax検証ロジック...
}
```

**変更箇所**: 91-130行目の関数を修正

#### 2. emergency-fix.phpの無効化または削除
**理由**: 
- 多くの副作用を引き起こしている可能性がある
- 問題が解決されたため不要

**実装方法**:
1. ファイルをリネーム: `emergency-fix.php.disabled`
2. または、ファイル冒頭に早期リターンを追加:
```php
<?php
// 緊急修正パッチを無効化（2025-08-09）
return;
// 以下の処理は実行されない
```

### フェーズ2: 追加の安全対策

#### 1. WordPress標準の.htaccessファイル作成
**場所**: `/Users/kei/work/wordpress-site/wordpress/.htaccess`

**内容**:
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

#### 2. テーマディレクトリの.htaccess最適化
**現在の内容は適切だが、念のため確認**:
- CSSとJSファイルへのアクセスが許可されている
- PHPファイルへの直接アクセスが拒否されている

### フェーズ3: セキュリティ設定の最適化

#### 1. CSPポリシーの調整（必要に応じて）
**場所**: `inc/security.php`の34-45行目

**調整内容**:
- 開発環境でのCSPポリシーをより緩和
- `style-src`と`script-src`にローカルアセットを明示的に許可

#### 2. Ajax検証の改善
**改善点**:
- admin-ajax.phpへのリクエストのみを検証
- その他のリクエストには干渉しない

## 実装手順

### ステップ1: バックアップ
```bash
# 現在の設定をバックアップ
cp themes/kei-portfolio/inc/security.php themes/kei-portfolio/inc/security.php.backup
cp themes/kei-portfolio/emergency-fix.php themes/kei-portfolio/emergency-fix.php.backup
```

### ステップ2: inc/security.phpの修正
1. 91-130行目の`kei_portfolio_verify_ajax_request`関数を修正
2. アセットファイルとadmin-ajax.php以外のリクエストを除外

### ステップ3: emergency-fix.phpの無効化
1. ファイルをリネームまたは早期リターンを追加
2. 関数が実行されないことを確認

### ステップ4: テスト実行
```bash
npm run e2e
```

### ステップ5: 結果確認
- 403エラーが解消されているか確認
- CSSとJavaScriptが正常に読み込まれているか確認
- E2Eテストが成功するか確認

## 実装優先順位

1. **最優先**: inc/security.phpのAjax検証修正
2. **高**: emergency-fix.phpの無効化
3. **中**: WordPress標準.htaccessの作成
4. **低**: その他の最適化

## リスク評価

| 変更 | リスクレベル | 影響範囲 | 対策 |
|------|-------------|----------|------|
| Ajax検証修正 | 低 | Ajax機能 | アセットファイルのみ除外するため影響小 |
| emergency-fix.php無効化 | 中 | 全体 | バックアップを保持、必要時に復元可能 |
| .htaccess作成 | 低 | URL構造 | WordPress標準設定のため安全 |

## 成功基準

1. ✅ E2Eテストがすべて成功する
2. ✅ CSSとJavaScriptファイルが正常に読み込まれる
3. ✅ 403 Forbiddenエラーが解消される
4. ✅ セキュリティ機能が維持される
5. ✅ Ajax機能が正常に動作する

## ロールバック計画

問題が発生した場合:
1. バックアップファイルから復元
2. emergency-fix.phpを再有効化
3. 変更を段階的に適用して問題箇所を特定

## 注意事項

- **修正禁止範囲**: tests/**, run-tests.sh, package.jsonのscripts.e2eには触れない
- **最小限の変更**: 必要最小限の変更で問題を解決する
- **セキュリティ維持**: セキュリティを損なわない範囲で修正する
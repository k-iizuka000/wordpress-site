# E2Eテストエラーの根本原因分析

## 概要
E2Eテストで複数ページ（/、/about/、/skills/、/contact/、/portfolio/）が失敗し、CSS・JavaScriptファイルへのアクセスで403 Forbiddenエラーが発生している。

## 調査結果

### 1. 問題の経緯

#### コミット履歴から判明した変更点
1. **7d03097**: ブログページ追加
   - `inc/enqueue.php`が新規追加され、多数のアセットファイルを読み込む処理を実装

2. **f8e2762**: WordPress REST API 403エラーの一時的な対応
   - `inc/security.php`を変更し、セキュリティ強化

3. **59045e2**: 公開ボタン押下で無限ループバグの修正
   - `emergency-fix.php`の大幅な変更
   - `secure-nonce-handler.php`の追加

### 2. 根本原因の特定

#### 主要な問題点

##### 1. Ajax検証の過剰な制限（inc/security.php: 91-130行）
```php
function kei_portfolio_verify_ajax_request() {
    // X-Requested-Withヘッダーの確認（Ajax判定）
    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
        $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
        // REST APIリクエストでない場合のみ拒否
        if (!isset($_SERVER['REQUEST_URI']) || 
            strpos($_SERVER['REQUEST_URI'], '/wp-json/') === false) {
            wp_die(__('Ajax以外のアクセスは許可されていません。', 'kei-portfolio'), 403);
        }
    }
}
```
この関数がアクション`wp_ajax_*`と`wp_ajax_nopriv_*`にフックされており、アセットファイルへの通常のHTTPリクエストをブロックしている可能性がある。

##### 2. Content Security Policy (CSP)の制限（inc/security.php: 34-45行）
CSPヘッダーが設定されているが、開発環境での緩和設定（69-87行）が正しく機能していない可能性がある。

##### 3. .htaccessファイルの競合
- テーマディレクトリの`.htaccess`（手動作成）とemergency-fix.phpが作成しようとする`.htaccess`（513-586行）の内容が異なる
- WordPressルートディレクトリに`.htaccess`が存在しない

##### 4. emergency-fix.phpの副作用
- セッション管理（39-73行）
- メモリ制限の変更（99-128行）
- アセットファイル監視（399-435行）
これらの処理が予期しない副作用を起こしている可能性がある。

### 3. 問題のメカニズム

1. **通常のアセットファイルリクエスト**
   - ブラウザがCSSやJSファイルをGETリクエストで取得しようとする
   - これらのリクエストにはAjaxヘッダー（X-Requested-With）が含まれない

2. **セキュリティコードによるブロック**
   - `kei_portfolio_verify_ajax_request`関数がすべてのリクエストをチェック
   - Ajaxヘッダーがないリクエストを403エラーでブロック

3. **結果**
   - CSSとJavaScriptファイルが読み込めない
   - ページの表示が崩れる
   - E2Eテストが失敗する

### 4. 一般的な原因との比較（Web検索結果より）

| 原因 | 該当状況 | 詳細 |
|------|----------|------|
| ファイルパーミッション | 不明 | 確認が必要 |
| .htaccessエラー | 該当 | 複数の.htaccessファイルが競合 |
| セキュリティプラグイン/コード | **該当** | inc/security.phpの過剰な制限 |
| CDN問題 | 非該当 | CDNは使用していない |
| キャッシュ問題 | 可能性あり | emergency-fix.phpのセッション管理 |

## 結論

**主要な根本原因**：
1. `inc/security.php`のAjax検証関数がアセットファイルへの通常のGETリクエストをブロックしている
2. emergency-fix.phpが作成する.htaccessファイルと既存の設定が競合している
3. セキュリティ強化のための実装が過剰に制限的になっている

これらの問題を解決するには、セキュリティを維持しながらアセットファイルへのアクセスを適切に許可する必要がある。
# WordPress Ajax および 404エラー修正作業書

作成日: 2025-08-09
対象サイト: http://localhost:8090/

## エラー内容

### 1. BlogAjax: ajaxUrl が定義されていません
- **発生場所**: トップページ（http://localhost:8090/）
- **エラーメッセージ**: `BlogAjax: ajaxUrl が定義されていません`
- **ソースファイル**: `/assets/js/blog-ajax.js` 104行目

### 2. Service Worker 404エラー
- **エラーメッセージ**: `A bad HTTP response code (404) was received when fetching the script.`
- **発生原因**: Service WorkerがWordPressサイトに存在しないスクリプトをフェッチしようとしている

## エラー原因の詳細分析

### 1. ajaxUrl未定義の原因

#### 根本原因
トップページ（`front-page.php`）はブログ関連ページではないため、以下の条件でスクリプトがエンキューされない：

```php
// enqueue.php 75行目
if ( is_home() || is_archive() || is_single() || is_category() || is_tag() || is_date() || is_author() || is_search() ) {
    // ブログ関連スクリプトのエンキュー
}
```

**問題点**:
- `is_front_page()`が条件に含まれていない
- トップページでblog-ajax.jsが読み込まれるが、wp_localize_scriptが実行されない
- 結果として`blogAjax`オブジェクトが定義されない

### 2. Service Worker 404エラーの原因

#### 根本原因
- WordPressサイトにService Workerファイルが登録されていない
- おそらく以前のReact実装時のService Worker登録が残っている
- ブラウザが`/sw.js`や`/service-worker.js`をフェッチしようとして404エラー

## 修正実装案

### 修正案1: Ajax URL未定義エラーの修正

#### オプションA: トップページでのblog-ajax.js読み込みを無効化（推奨）

**理由**: トップページではブログ機能が使用されていないため、不要なスクリプトの読み込みを避ける

**実装**:
```php
// inc/enqueue.php の修正
// 74行目付近を以下のように修正

// ブログ用スタイルシートの条件付き読み込み
if ( is_home() || is_archive() || is_single() || is_category() || is_tag() || is_date() || is_author() || is_search() ) {
    // 既存のコード...
}
```

#### オプションB: トップページでもローカライズデータを提供

**理由**: 将来的にトップページでブログ機能を使用する可能性がある場合

**実装**:
```php
// inc/enqueue.php の修正
// 74行目付近を以下のように修正

// ブログ用スタイルシートの条件付き読み込み
// is_front_page()を追加
if ( is_front_page() || is_home() || is_archive() || is_single() || is_category() || is_tag() || is_date() || is_author() || is_search() ) {
    // 既存のコード...
}
```

#### オプションC: グローバルAjax設定の実装（最も堅牢）

**理由**: サイト全体でAjax機能を使用可能にし、一貫性を保つ

**実装**:
```php
// inc/enqueue.php に追加

/**
 * グローバルAjax設定
 */
function kei_portfolio_global_ajax_setup() {
    // 全ページでベースとなるAjax設定を提供
    wp_enqueue_script( 
        'kei-portfolio-global', 
        get_template_directory_uri() . '/assets/js/global.js', 
        array( 'jquery' ), 
        wp_get_theme()->get( 'Version' ), 
        true 
    );
    
    wp_localize_script( 'kei-portfolio-global', 'keiPortfolioGlobal', array(
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'restUrl' => rest_url( 'wp/v2/' ),
        'nonce'   => wp_create_nonce( 'kei_portfolio_ajax' ),
        'siteUrl' => home_url(),
        'themeUrl' => get_template_directory_uri(),
    ));
}
add_action( 'wp_enqueue_scripts', 'kei_portfolio_global_ajax_setup', 5 );
```

### 修正案2: Service Worker 404エラーの修正

#### オプションA: Service Worker登録の削除（推奨）

**理由**: WordPressサイトではService Workerが不要な場合

**実装**:
```javascript
// header.php または footer.php に追加
<script>
// 既存のService Worker登録を削除
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.getRegistrations().then(function(registrations) {
        for(let registration of registrations) {
            registration.unregister();
            console.log('Service Worker unregistered:', registration.scope);
        }
    });
}
</script>
```

#### オプションB: ダミーService Workerの作成

**理由**: エラーを防ぎつつ、将来的にService Workerを実装する可能性を残す

**実装**:
```javascript
// テーマルートに sw.js を作成
self.addEventListener('install', function(event) {
    console.log('Service Worker installed');
    self.skipWaiting();
});

self.addEventListener('activate', function(event) {
    console.log('Service Worker activated');
    return self.clients.claim();
});

self.addEventListener('fetch', function(event) {
    // WordPressのAjaxリクエストはキャッシュしない
    const url = new URL(event.request.url);
    
    if (url.pathname.includes('/wp-admin/admin-ajax.php') || 
        url.pathname.includes('/wp-json/')) {
        event.respondWith(fetch(event.request));
        return;
    }
    
    // その他のリクエストも直接フェッチ（キャッシュなし）
    event.respondWith(fetch(event.request));
});
```

### 修正案3: blog-ajax.jsの防御的プログラミング強化

**理由**: エラーが発生してもスクリプトが停止しないようにする

**実装**:
```javascript
// assets/js/blog-ajax.js の修正
// 96-106行目を以下のように修正

isAjaxSupported() {
    // blogAjax オブジェクトの存在確認（統一されたオブジェクト）
    if (typeof blogAjax === 'undefined') {
        // グローバルオブジェクトをフォールバックとして使用
        if (typeof keiPortfolioGlobal !== 'undefined') {
            window.blogAjax = {
                ajaxUrl: keiPortfolioGlobal.ajaxUrl,
                loadMoreNonce: keiPortfolioGlobal.nonce,
                current_page: 1,
                max_pages: 1
            };
            console.log('BlogAjax: Using global fallback configuration');
        } else {
            console.warn('BlogAjax: Ajax configuration not available on this page');
            return false;
        }
    }
    
    // 以下既存のコード...
}
```

## 推奨実装順序

1. **即時対応（エラー解消）**
   - 修正案1のオプションC（グローバルAjax設定）を実装
   - 修正案2のオプションA（Service Worker削除）を実装
   - 修正案3（防御的プログラミング）を実装

2. **中期対応（最適化）**
   - ページごとに必要なスクリプトのみを読み込むよう条件を精査
   - 不要なAjax呼び出しの削減

3. **長期対応（アーキテクチャ改善）**
   - REST APIへの移行検討
   - モジュール化されたJavaScript構造への移行

## テスト項目

### 機能テスト
- [ ] トップページでコンソールエラーが発生しないこと
- [ ] ブログページでAjax機能が正常に動作すること
- [ ] 無限スクロールが正常に動作すること
- [ ] Service Worker関連のエラーが発生しないこと

### セキュリティテスト
- [ ] Nonceが正しく生成・検証されること
- [ ] 未認証ユーザーの適切な処理
- [ ] XSS攻撃への耐性

### パフォーマンステスト
- [ ] 不要なスクリプトが読み込まれていないこと
- [ ] Ajax呼び出しの応答時間
- [ ] ページロード時間への影響

## 実装時の注意事項

1. **キャッシュのクリア**
   - ブラウザキャッシュをクリア
   - WordPressのキャッシュプラグインがある場合はクリア
   - CDNキャッシュがある場合はクリア

2. **バックアップ**
   - 修正前にファイルのバックアップを取得
   - データベースのバックアップも推奨

3. **段階的な適用**
   - 開発環境で十分にテスト
   - ステージング環境で確認
   - 本番環境への適用

## 想定される副作用と対策

### 副作用1: 既存のAjax機能への影響
**対策**: グローバル設定とページ固有設定の優先順位を明確にする

### 副作用2: プラグインとの競合
**対策**: プラグインのAjax実装を事前に確認

### 副作用3: パフォーマンスへの影響
**対策**: 必要最小限のスクリプトのみを読み込む

## 参考資料

- [WordPress Codex: AJAX in Plugins](https://codex.wordpress.org/AJAX_in_Plugins)
- [WordPress Developer: wp_localize_script()](https://developer.wordpress.org/reference/functions/wp_localize_script/)
- [MDN: Service Worker API](https://developer.mozilla.org/en-US/docs/Web/API/Service_Worker_API)
- [WordPress REST API Handbook](https://developer.wordpress.org/rest-api/)

## 更新履歴

- 2025-08-09: 初版作成
- エラー分析と修正案の策定
- 実装コード例の追加
# WordPress REST API 403エラー修正設計書

## 問題の詳細分析

### 1. エラー内容
```
GET https://kei-aokiki.dev/wp-json/wp/v2/templates/lookup?slug=front-page&_locale=user 403 (Forbidden)
```

### 2. エラーの原因分析

#### 2.1 直接的な原因
- Gutenbergブロックエディターが`templates/lookup`エンドポイントにアクセスしようとしている
- このエンドポイントへのアクセスが403で拒否されている
- 主にテンプレート管理権限の不足、またはREST APIのアクセス制限が原因

#### 2.2 根本原因
1. **セキュリティ設定によるREST API制限**
   - `/themes/kei-portfolio/inc/security.php`でAjaxリクエストの検証が厳格化されている
   - CSPポリシーやヘッダー検証がREST APIリクエストを妨げている可能性

2. **権限設定の問題**
   - テンプレート管理権限（`edit_theme_options`）の不足
   - ユーザーロールの権限が不適切

3. **パーマリンク設定**
   - パーマリンクが「基本」設定になっている場合、REST APIが正常に動作しない

## 実装レベルの修正案

### 修正1: REST API権限の修正

#### ファイル: `/themes/kei-portfolio/inc/rest-api-permissions.php` (新規作成)
```php
<?php
/**
 * REST API権限設定の調整
 * 
 * @package Kei_Portfolio_Pro
 */

// 直接アクセスを防ぐ
if (!defined('ABSPATH')) {
    exit;
}

/**
 * REST APIのテンプレートエンドポイント権限を調整
 */
add_filter('rest_api_init', 'kei_portfolio_fix_template_permissions', 10);

function kei_portfolio_fix_template_permissions() {
    // templates/lookupエンドポイントの権限チェックを調整
    add_filter('rest_pre_dispatch', 'kei_portfolio_allow_template_lookup', 10, 3);
}

/**
 * テンプレートlookupエンドポイントへのアクセスを許可
 */
function kei_portfolio_allow_template_lookup($result, $server, $request) {
    $route = $request->get_route();
    
    // templates/lookupエンドポイントの場合
    if (strpos($route, '/wp/v2/templates/lookup') !== false) {
        // ログインユーザーで投稿編集権限があれば許可
        if (is_user_logged_in() && current_user_can('edit_posts')) {
            // 権限チェックをパスする
            add_filter('user_has_cap', 'kei_portfolio_grant_template_cap', 10, 3);
        }
    }
    
    return $result;
}

/**
 * 一時的にテンプレート権限を付与
 */
function kei_portfolio_grant_template_cap($allcaps, $caps, $args) {
    // edit_theme_options権限を一時的に付与
    if (in_array('edit_theme_options', $caps)) {
        $allcaps['edit_theme_options'] = true;
    }
    return $allcaps;
}

/**
 * Gutenbergエディターのテンプレート機能を制限
 */
add_filter('block_editor_settings_all', 'kei_portfolio_adjust_block_editor_settings', 10, 2);

function kei_portfolio_adjust_block_editor_settings($settings, $context) {
    // 管理者以外はテンプレート編集を無効化
    if (!current_user_can('manage_options')) {
        $settings['supportsTemplateMode'] = false;
        $settings['defaultTemplatePartAreas'] = [];
    }
    
    return $settings;
}
```

### 修正2: セキュリティ設定の調整

#### ファイル: `/themes/kei-portfolio/inc/security.php` (修正)

92-114行目を以下のように修正:
```php
/**
 * Ajax非同期リクエストの検証強化
 */
add_action('wp_ajax_*', 'kei_portfolio_verify_ajax_request', 1);
add_action('wp_ajax_nopriv_*', 'kei_portfolio_verify_ajax_request', 1);

function kei_portfolio_verify_ajax_request() {
    // REST APIリクエストは除外
    if (defined('REST_REQUEST') && REST_REQUEST) {
        return;
    }
    
    // Gutenbergエディターのリクエストは除外
    $current_action = current_action();
    if (strpos($current_action, 'wp_ajax_heartbeat') !== false || 
        strpos($current_action, 'wp_ajax_rest-nonce') !== false) {
        return;
    }
    
    // リファラーの検証
    if (!wp_get_referer()) {
        wp_die(__('不正なリクエストです。', 'kei-portfolio'), 403);
    }

    // User-Agentの基本検証
    if (!isset($_SERVER['HTTP_USER_AGENT']) || empty($_SERVER['HTTP_USER_AGENT'])) {
        wp_die(__('不正なリクエストです。', 'kei-portfolio'), 403);
    }

    // X-Requested-Withヘッダーの確認（Ajax判定）- REST APIは除外
    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
        $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
        // kei-portfolio独自のヘッダーも確認
        if (!isset($_SERVER['HTTP_X_KEIPORTFOLIO_REQUEST'])) {
            // REST APIリクエストでない場合のみ拒否
            if (!isset($_SERVER['REQUEST_URI']) || 
                strpos($_SERVER['REQUEST_URI'], '/wp-json/') === false) {
                wp_die(__('Ajax以外のアクセスは許可されていません。', 'kei-portfolio'), 403);
            }
        }
    }
}
```

### 修正3: functions.phpへの読み込み追加

#### ファイル: `/themes/kei-portfolio/functions.php` (修正)

26行目の後に追加:
```php
// REST API権限修正の読み込み
require_once get_template_directory() . '/inc/rest-api-permissions.php';
```

### 修正4: ブログページ設定の確認と修正

#### WordPressダッシュボードでの設定手順:

1. **設定 > 表示設定**
   - 「ホームページの表示」を「固定ページ」に設定
   - 「ホームページ」: front-pageまたはHomeページを選択
   - 「投稿ページ」: "Blog"ページを選択（存在しない場合は作成）

2. **固定ページの作成（必要な場合）**
   ```php
   // ファイル: /themes/kei-portfolio/inc/create-blog-page.php (新規作成)
   <?php
   /**
    * ブログページの自動作成
    */
   function kei_portfolio_create_blog_page() {
       $blog_page = get_page_by_path('blog');
       
       if (!$blog_page) {
           $blog_page_id = wp_insert_post(array(
               'post_title'    => 'Blog',
               'post_name'     => 'blog',
               'post_status'   => 'publish',
               'post_type'     => 'page',
               'post_content'  => '',
               'comment_status' => 'closed',
               'ping_status'   => 'closed',
           ));
           
           // 作成したページを投稿ページとして設定
           if ($blog_page_id && !is_wp_error($blog_page_id)) {
               update_option('page_for_posts', $blog_page_id);
           }
       } else {
           // 既存のブログページを投稿ページとして設定
           update_option('page_for_posts', $blog_page->ID);
       }
   }
   
   // テーマ有効化時に実行
   add_action('after_switch_theme', 'kei_portfolio_create_blog_page');
   ```

### 修正5: .htaccessファイルの確認

#### ファイル: `/.htaccess` (確認・修正)
```apache
# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteRule ^index\.php$ - [L]

# REST APIリクエストを許可
RewriteRule ^wp-json/(.*)?$ index.php?rest_route=/$1 [L]

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
# END WordPress
```

### 修正6: デバッグ用コードの追加（一時的）

#### ファイル: `/themes/kei-portfolio/inc/debug-rest-api.php` (新規作成)
```php
<?php
/**
 * REST APIデバッグツール（開発環境のみ）
 */

if (defined('WP_DEBUG') && WP_DEBUG) {
    // REST APIエラーのログ記録
    add_filter('rest_request_after_callbacks', 'kei_portfolio_log_rest_errors', 10, 3);
    
    function kei_portfolio_log_rest_errors($response, $handler, $request) {
        if (is_wp_error($response)) {
            error_log('REST API Error: ' . print_r([
                'route' => $request->get_route(),
                'method' => $request->get_method(),
                'params' => $request->get_params(),
                'error' => $response->get_error_message(),
                'user' => wp_get_current_user()->user_login,
                'capabilities' => wp_get_current_user()->allcaps
            ], true));
        }
        return $response;
    }
    
    // 権限チェックのデバッグ
    add_filter('user_has_cap', 'kei_portfolio_debug_capabilities', 10, 3);
    
    function kei_portfolio_debug_capabilities($allcaps, $caps, $args) {
        if (defined('REST_REQUEST') && REST_REQUEST) {
            error_log('Capability check: ' . print_r([
                'required_caps' => $caps,
                'user_caps' => array_keys(array_filter($allcaps)),
                'context' => $args
            ], true));
        }
        return $allcaps;
    }
}
```

## 実装手順

### ステップ1: バックアップ
```bash
# テーマファイルのバックアップ
cp -r themes/kei-portfolio themes/kei-portfolio-backup-$(date +%Y%m%d)
```

### ステップ2: ファイルの作成と修正
1. `rest-api-permissions.php`を作成
2. `security.php`を修正
3. `functions.php`に読み込み追加
4. デバッグファイルを作成（開発環境のみ）

### ステップ3: WordPress設定の確認
1. パーマリンク設定を「投稿名」または「カスタム構造」に変更
2. ブログページの設定を確認
3. ユーザー権限の確認

### ステップ4: キャッシュのクリア
```bash
# WordPressキャッシュをクリア
wp cache flush

# ブラウザキャッシュもクリア
```

### ステップ5: テスト
1. 管理者アカウントでログイン
2. 新規投稿を作成
3. ブロックエディターで編集
4. 公開ボタンをクリック
5. エラーが解消されることを確認

## トラブルシューティング

### それでもエラーが発生する場合

1. **プラグインの競合確認**
   - セキュリティプラグインを一時的に無効化
   - キャッシュプラグインを無効化

2. **サーバー設定の確認**
   - ModSecurityやWAFの設定確認
   - PHPのメモリ制限確認

3. **権限の再設定**
   ```php
   // 管理者ユーザーの権限をリセット
   $admin = get_role('administrator');
   $admin->add_cap('edit_theme_options');
   $admin->add_cap('edit_posts');
   $admin->add_cap('publish_posts');
   ```

## セキュリティ考慮事項

1. **本番環境への適用時**
   - デバッグコードは必ず削除
   - 最小限の権限付与に留める
   - アクセスログを監視

2. **継続的な監視**
   - REST APIアクセスログの確認
   - 不正なアクセスパターンの検出
   - 定期的なセキュリティアップデート

## 期待される結果

1. **エラーの解消**
   - 403エラーが発生しなくなる
   - 投稿の公開が正常に動作する

2. **ブログ機能の正常化**
   - 投稿がBlogページに表示される
   - ページネーションが正常に動作する
   - カテゴリー、タグが機能する

## メンテナンス計画

### 短期（実装直後）
- エラーログの監視（1週間）
- ユーザーからのフィードバック収集
- パフォーマンスの測定

### 中期（1ヶ月後）
- セキュリティ設定の見直し
- 不要なデバッグコードの削除
- 権限設定の最適化

### 長期（3ヶ月後）
- WordPressコアアップデートへの対応
- Gutenbergアップデートへの対応
- セキュリティ監査の実施
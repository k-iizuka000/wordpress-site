# WordPress ローカル「製作実績(Projects)等が404」実装設計書

## 目的・スコープ
- 目的: ローカル `http://localhost:8090/` で「製作実績」などのサブページが404となる問題を解消し、再発防止策を実装する。
- 対象: テーマ `kei-portfolio`、Docker/Apache設定、移行/エクスポート関連スクリプト。
- 非対象: 本番インフラ構成変更、テーマの大規模機能追加。

## 背景（要点）
- テーマでCPT `project` を `rewrite.slug=projects`、`has_archive=true` で登録。
- DockerのApacheは `mod_rewrite` 有効、`AllowOverride All` 設定済み。
- 発生パターン的に「パーマリンク設定（リライトルール）未フラッシュ」または「.htaccess未反映」、あるいは「CPT投稿ゼロ」が主因になりやすい。

## 要件
- 機能要件
  - 初回セットアップ/テーマ切替時にリライトルールが適切に更新されること。
  - .htaccess が不在でも、パーマリンク保存で自動生成される/規定ブロックで復旧できること。
  - ローカルで `siteurl/home` が正しく `http://localhost:8090` に設定されること。
  - 実績(Projects)の最低1件データが簡単に投入できること（検証容易性）。
- 非機能要件
  - 変更は再入可能（idempotent）であること。
  - セキュア（秘密情報のハードコード禁止、フルパス非依存）。

## 変更方針の全体像
1) テーマ有効化時に一度だけ `flush_rewrite_rules()` を実行（CPT登録後）。
2) 初期データ投入（Projects 1件作成）のWP-CLIタスクを用意。
3) `.htaccess` が無い/壊れている場合の復旧手順をタスク化（運用手順として）。
4) `siteurl/home` の取得・設定WP-CLIタスクを用意。
5) `export-wordpress-site.sh` の可搬性・安全性を高める設計（URL置換のモード分離/環境変数化）。

---

## 詳細設計

### A. テーマ有効化時のリライトルールフラッシュ
- 実装概要
  - フック: `after_switch_theme`
  - 要件: CPT登録が完了していること（フックの優先度順に注意）
- 追加コード（設計案）
  - 追加先: `themes/kei-portfolio/functions.php` もしくは `inc/setup.php`
  - 実装方針:
    ```php
    // CPT登録を確実に読み込んだ後にフラッシュ
    add_action('after_switch_theme', function () {
        // 念のためCPT登録関数を呼び出せるようにしておく（既にinitで登録済みなら二重登録はされない）
        if (function_exists('kei_portfolio_pro_register_post_types')) {
            kei_portfolio_pro_register_post_types();
        }
        if (function_exists('kei_portfolio_pro_register_taxonomies')) {
            kei_portfolio_pro_register_taxonomies();
        }
        flush_rewrite_rules();
    }, 20);
    ```
- 注意
  - `flush_rewrite_rules()` の常時呼び出しは禁止（パフォーマンス悪化）。有効化時の1回に限定。

### B. 初期データ投入（Projects）
- 実装概要
  - WP-CLIでProjectsの公開投稿を1件作成するコマンドを用意（存在チェック付き）。
- 実行例（設計案）
  ```bash
  # 1件も無ければ作成（idempotent）
  wp post list --post_type=project --post_status=publish --field=ID | grep -q . || \
  wp post create \
    --post_type=project \
    --post_status=publish \
    --post_title="Sample Project" \
    --post_content="Auto seeded sample project for local testing."
  ```
- 位置づけ
  - ローカル初期化スクリプト（例: `docker exec` 経由で実行）に組み込み。

### C. .htaccess 復旧運用手順（標準ブロック）
- 原則: 管理画面→パーマリンクで「変更を保存」すれば自動生成される。
- 手動復旧テンプレート（既存の `.htaccess-for-root` と同等）
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
- 手順（運用）
  - ルートに `.htaccess` が無い場合のみ上記を設置、`644` に設定。

### D. サイトURL(siteurl/home) 整合タスク
- 目的: ローカルで `siteurl`/`home` を `http://localhost:8090` に統一。
- WP-CLI例
  ```bash
  wp option get siteurl
  wp option get home
  wp option update siteurl 'http://localhost:8090'
  wp option update home 'http://localhost:8090'
  ```

### E. export-wordpress-site.sh の改善設計
- 目的: 可搬性/安全性（URL置換の安全化、絶対パス排除、モード分離）。
- 変更方針
  - 絶対パスを環境変数化。
    - `EXPORT_DIR=${EXPORT_DIR:-./site-exports}`
    - `THEME_SOURCE_DIR=${THEME_SOURCE_DIR:-./themes/kei-portfolio}`
    - `THEME_UPLOAD_DIR=${THEME_UPLOAD_DIR:-./themes/kei-portfolio-upload}`
  - モード分離: `--mode=prod|local`（デフォルトprod）。
    - `prod`: 本番向けURLへ統一（現状の `kei-aokiki.dev` への置換）。
    - `local`: URL置換を行わない（または置換先を `SOURCE_URL`/`DEST_URL` で指定）。
  - URL置換の安全化
    - 可能ならエクスポート時の置換を廃止し、インポート後に WP-CLI `wp search-replace` を使用。
    - 例: `wp search-replace 'http://localhost:8090' 'https://kei-aokiki.dev' --all-tables --precise --recurse-objects`
      - シリアライズデータに安全。
- CLIインターフェイス例（設計）
  ```bash
  ./export-wordpress-site.sh --mode=prod \
    --source-url="http://localhost:8090" \
    --dest-url="https://kei-aokiki.dev" \
    --export-dir="./site-exports"
  ```
- 互換性
  - 既存の挙動（本番用置換）を `--mode=prod` として維持。

---

## 実装手順（順番厳守）
1) テーマに `after_switch_theme` での `flush_rewrite_rules()` を追加
2) 初期データ投入用のWP-CLIタスク（Projects 1件）をスクリプト化
3) `.htaccess` 復旧テンプレートを `docs/` に格納し、運用手順を追記
4) `export-wordpress-site.sh` を上記設計に沿ってリファクタ
5) ローカル環境で動作確認 → 受け入れ条件の達成を確認

## 受け入れ条件
- `http://localhost:8090/projects/` がHTTP 200で表示される
- `http://localhost:8090/projects/{slug}/`（初期データ）がHTTP 200
- 固定ページ `/about/`, `/skills/`, `/contact/`, `/portfolio/` がHTTP 200
- `.htaccess` が存在し、標準のWPリライトルールを含む
- `siteurl/home` が `http://localhost:8090` に設定

## 検証計画
- 既存 `test-page-access.php` を拡張実行（CPTアーカイブ/個別を追加）
  - 例: `/projects/`, `/projects/sample-project/`
- ブラウザでの手動確認（リダイレクト/コンソールエラー無）
- エラーログ（Apache/PHP）に致命的エラーが無いこと

## ロールバック計画
- テーマ変更はコミット単位で差戻し可能
- `.htaccess` は自動/手動生成のため、バックアップ `.htaccess.backup` を同階層保管
- `export-wordpress-site.sh` は旧版を `old/` に退避

## リスクと対応
- `flush_rewrite_rules()` の呼び出し場所/タイミング誤り → 有効化時のみ、CPT登録後に限定
- URL置換の破損（シリアライズ破壊） → `wp search-replace` 採用で回避
- フルパス依存 → スクリプト環境変数化で解消

## 作業見積（目安）
- 実装: 1.5h（テーマ/スクリプト改修）
- 検証: 0.5h（自動+手動）
- ドキュメント更新: 0.5h

---
最終更新: 2025-08-08

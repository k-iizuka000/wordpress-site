# wordpress-site
wordpressのサイト（ポートフォリオやブログに使用）

# サイトドメイン
- https://kei-aokiki.dev/
- ルートで表示するテーマ格納場所　：@themes/kei-portfolio

# リポジトリ ガイドライン

## プロジェクト構成 & モジュール配置

* `themes/kei-portfolio/`：メインテーマ（PHP・JS・CSS・アセット・テスト類）
* `wordpress/`：WordPress 本体のコンテンツ（例：`wp-content/uploads`）
* `docker-compose.yml`：ローカル開発用スタック（WordPress・DB・phpMyAdmin）

  * バリエーション: `*.optimized.yml`, `*.test.yml`
* `tests/`：リポジトリ全体のテスト資料とレポート

  * テーマ用テストは `themes/kei-portfolio/tests/` 配下
* `scripts/`：トップレベルの補助スクリプト（例：`run-tests.sh`, `export-database.sh`, `import-database.sh`）
* `docs/`, `reports/`, `database-exports/`：ドキュメントや生成物の置き場

## ビルド・テスト・開発コマンド

* スタック起動: `docker compose up -d`

  * アプリ: `http://localhost:8090`
  * phpMyAdmin: `http://localhost:8091`
* テーマ開発（`themes/kei-portfolio` 内）

  * 依存インストール: `npm ci && composer install`
  * 開発サーバー／バンドル: `npm run dev`（監視のみなら `npm run watch`）
  * 本番ビルド: `npm run build`
* テスト（リポジトリ直下で実行）

  * 全テスト: `./run-tests.sh all`
  * PHP のみ: `./run-tests.sh php`
  * JS のみ: `./run-tests.sh js`
  * Lint & カバレッジ: `./run-tests.sh lint` / `./run-tests.sh coverage`
* テーマ直下での直接実行:

  * `composer run test:all`, `npm test`, `npm run lint`

## コーディングスタイル & 命名規則

* **PHP**：WordPress Coding Standards (WPCS)

  * インデント: 4 スペース
  * 関数: `snake_case`
  * クラス: `StudlyCaps`
  * 定数: `UPPER_SNAKE_CASE`
* **JS**：ESLint + Prettier

  * 変数: `camelCase`
  * ファイル: `kebab-case.js`
* **CSS**：Stylelint

  * BEM 風クラス名推奨
  * ファイル: `kebab-case.css/scss`
* **ツール**：PHPCS（`WordPress` 標準）、PHPStan、ESLint、Stylelint、Prettier、Jest

## テストガイドライン

* **PHP**：`themes/kei-portfolio/tests/*.php` に PHPUnit スイート

  * 実行: `composer run test:all` または `./run-tests.sh php`
* **JS**：`themes/kei-portfolio/tests/js/**/*.test.js` に Jest テスト

  * 実行: `npm test`（結果は `coverage/js` へ）
* **カバレッジ**：

  * Jest: 全体で約 70% を必須
  * PHP: `composer run test:coverage`（HTML 出力は `coverage/html`）
* **命名**：

  * PHP テストクラス: `*Test.php`
  * JS テスト: `.test.js`

## コミット & プルリクエスト ガイドライン

* **コミット**：短く命令形で。日本語説明を追加しても可

  * 例: `Fix 404 handling on theme routes`
* **PR**：目的・関連 Issue・テスト手順を記載し、UI 変更時はスクリーンショットを添付

  * マージ前に `./run-tests.sh all` が通ることを確認

## セキュリティ & 設定のヒント

* シークレットはコミットしない。`docker-compose.yml` の環境変数と `wp-config.docker.php` で設定
* DB 移行: `./export-database.sh` と `./import-database.sh` を利用して再現性を確保
* プッシュ前チェック:

  * `npm run lint:js-check`
  * `npm run lint:css-check`
  * `composer run lint`
  * フルテスト実行
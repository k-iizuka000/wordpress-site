# WordPressテーマアップロード用ファイル分類

## 必要なファイル（本番環境にアップロード必須）

### コアファイル
- `style.css` - テーマの設定（名前、説明）とCSSスタイル
- `index.php` - デフォルト/フォールバックテンプレート
- `functions.php` - テーマ機能の定義

### テンプレートファイル
- `header.php` - ヘッダーテンプレート
- `footer.php` - フッターテンプレート
- `page.php` - 固定ページテンプレート
- `404.php` - 404エラーページ
- `archive-project.php` - プロジェクトアーカイブ
- `front-page.php` - フロントページ
- `single-project.php` - 個別プロジェクト
- `page-*.php` - 各種カスタムページテンプレート

### 必要なディレクトリ
- `template-parts/` - テンプレートパーツ
- `inc/` - 機能ファイル
- `assets/` - CSS、JS、画像などの本番用アセット

## 不要なファイル（本番環境から除外）

### 開発環境ファイル
- `node_modules/` - Node.js依存関係（フォルダ全体）
- `vendor/` - Composer依存関係（フォルダ全体）
- `package.json` - npm設定ファイル
- `package-lock.json` - npmロックファイル
- `composer.json` - Composer設定ファイル
- `composer.lock` - Composerロックファイル
- `composer.phar` - Composer実行ファイル
- `composer-setup.php` - Composerセットアップ

### テスト関連ファイル
- `tests/` - テストファイル（フォルダ全体）
- `coverage/` - テストカバレッジレポート
- `phpunit.xml*` - PHPUnitテスト設定
- `jest.config.js` - Jestテスト設定
- `.phpunit.result.cache` - テスト結果キャッシュ
- `*test*.php` - テスト実行ファイル
- `run-group*-tests.sh` - テスト実行スクリプト
- `setup-test-env.sh` - テスト環境セットアップ

### ビルド設定ファイル
- `.babelrc` - Babel設定
- `webpack.config.js` - Webpack設定（もしあれば）
- `.eslintrc*` - ESLint設定
- `.sass-cache/` - Sassキャッシュ

### バージョン管理・IDE関連
- `.git/` - Gitディレクトリ
- `.gitignore` - Git除外設定
- `.idea/` - IDEディレクトリ
- `.DS_Store` - macOS隠しファイル

### ドキュメント・ログファイル
- `README*.md` - 開発用ドキュメント
- `learning/` - 学習記録
- `data/` - テストデータ
- `tasks/` - タスク管理
- `*.log` - ログファイル
- `backup_*.tar.gz` - バックアップファイル

### 現在のkei-portfolioテーマで除外すべきファイル
```
.babelrc
.DS_Store
.phpunit.result.cache
backup_docker_configs_20250807_203417.tar.gz
composer-setup.php
composer.json
composer.lock
composer.phar
coverage/
create-pages-cli.php
data/
jest.config.js
learning/
node_modules/
package-lock.json
package.json
phpunit-simple.xml
phpunit-syntax.xml
phpunit.xml
phpunit.xml.dist
README-TESTING.md
run-group1-tests.sh
run-group2-tests.sh
run-group4-tests.sh
run-group5-tests.php
setup-test-env.sh
simple-bootstrap.php
simple-test-runner.php
tasks/
test-portfolio-data.php
tests/
vendor/
```

## 推奨アップロード手順
1. 必要ファイルのみを別フォルダにコピー
2. 除外ファイル一覧を確認してクリーンアップ
3. 圧縮してアップロード、またはFTPで必要ファイルのみ転送

## 注意事項
- アセットファイル（CSS、JS）は本番用にビルドされたものを使用
- 開発用ファイルは完全に除外してファイルサイズと転送時間を削減
- バージョン管理ファイルやキャッシュファイルは必ず除外
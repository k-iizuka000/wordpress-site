# コードスタイルと規約

## PHP規約
- **コーディング標準**: WordPress Coding Standards (WPCS)
- **インデント**: 4スペース
- **命名規則**:
  - 関数: `snake_case`
  - クラス: `StudlyCaps`
  - 定数: `UPPER_SNAKE_CASE`
- **リンター**: PHPCS, PHPStan
- **フォーマッター**: PHPCBF

## JavaScript規約
- **スタイル**: ESLint + Prettier
- **命名規則**:
  - 変数/関数: `camelCase`
  - ファイル名: `kebab-case.js`
- **テストファイル**: `*.test.js`
- **モジュールシステム**: ES6 modules

## CSS規約
- **リンター**: Stylelint (@wordpress/stylelint-config)
- **命名規則**:
  - クラス名: BEM風推奨
  - ファイル名: `kebab-case.css/scss`
- **プリプロセッサ**: SCSS

## テスト規約
### PHP
- **フレームワーク**: PHPUnit 9.5
- **場所**: `themes/kei-portfolio/tests/*.php`
- **命名**: `*Test.php`
- **カバレッジ目標**: 未定義

### JavaScript
- **フレームワーク**: Jest
- **場所**: `themes/kei-portfolio/tests/js/**/*.test.js`
- **命名**: `*.test.js`
- **カバレッジ目標**: 全体で70%以上

## コミット規約
- 短く命令形で記述
- 日本語説明を追加可能
- 例: `Fix 404 handling on theme routes`

## プルリクエスト規約
- 目的、関連Issue、テスト手順を記載
- UI変更時はスクリーンショット添付
- マージ前に全テスト(`./run-tests.sh all`)通過を確認
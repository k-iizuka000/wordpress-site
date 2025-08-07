# kei-portfolio テーマ テスト実行ガイド

このドキュメントでは、kei-portfolioWordPressテーマのテスト環境のセットアップとテスト実行方法について説明します。

## 目次

- [テスト環境のセットアップ](#テスト環境のセットアップ)
- [PHPテスト（PHPUnit）](#phpテストphpunit)
- [JavaScriptテスト（Jest）](#javascriptテストjest)
- [Docker環境でのテスト](#docker環境でのテスト)
- [テスト設計と構成](#テスト設計と構成)
- [トラブルシューティング](#トラブルシューティング)

## テスト環境のセットアップ

### 自動セットアップスクリプト（推奨）

テーマディレクトリで以下のコマンドを実行してください：

```bash
# ローカル環境用（デフォルト）
./setup-test-env.sh

# Docker環境用
./setup-test-env.sh --docker

# 部分的なセットアップ
./setup-test-env.sh --skip-composer  # npmのみインストール
./setup-test-env.sh --skip-npm       # Composerのみインストール
```

### 手動セットアップ

自動セットアップスクリプトが使用できない場合の手順：

#### 1. Composer依存関係のインストール

```bash
# Composerがインストールされている場合
composer install

# Composerがない場合
curl -sS https://getcomposer.org/installer | php
php composer.phar install
```

#### 2. Node.js依存関係のインストール

```bash
npm install
```

#### 3. WordPressコーディング規約の設定

```bash
./vendor/bin/phpcs --config-set installed_paths vendor/wp-coding-standards/wpcs
```

## PHPテスト（PHPUnit）

### テストスイート構成

テストは以下のグループに分けられています：

| グループ | 内容 | WordPress依存 | 実行コマンド |
|---------|------|--------------|-------------|
| group1 | PHP構文・規約チェック | なし | `composer test:group1` |
| group2 | WordPress基本機能 | あり | `composer test:group2` |
| group3-4 | ページテンプレート | あり | `composer test:group3` |
| group5 | カスタム投稿タイプ | あり | `composer test:group5` |
| group9 | AJAX/API通信 | あり | `composer test:group9` |
| group10 | セキュリティ・パフォーマンス | あり | `composer test:group10` |

### 基本的なテスト実行

```bash
# 構文チェックのみ（独立実行可能）
composer test:syntax

# WordPressコーディング規約チェック
composer test:standards

# 基本テスト（構文 + 規約）
composer test

# 全テストスイート実行（WordPress Test Suite必要）
composer test:all
```

### 特定テストの実行

```bash
# 特定のテストファイル
./vendor/bin/phpunit tests/SyntaxTest.php

# 特定のテストメソッド
./vendor/bin/phpunit --filter testFunctionsSyntax tests/SyntaxTest.php

# 特定のテストグループ
./vendor/bin/phpunit --testsuite=group1-php-syntax
```

### カバレッジレポート生成

```bash
# HTMLレポート生成
composer test:coverage

# レポートの確認
open coverage/html/index.html  # macOS
```

### PHP静的解析

```bash
# PHPStan実行
composer analyze

# PHP構文チェック
composer lint:php

# WordPressコーディング規約チェック
composer lint:phpcs

# 規約違反の自動修正
composer lint:phpcs:fix
```

## JavaScriptテスト（Jest）

### 基本的なテスト実行

```bash
# 全Jestテスト実行
npm test

# カバレッジレポート付きテスト
npm run test:coverage

# 継続的テスト実行（ファイル変更を監視）
npm run test:watch
```

### 特定テストの実行

```bash
# 特定のテストファイル
npm run test:main             # main.js のテスト
npm run test:navigation       # navigation.js のテスト
npm run test:contact-form     # contact-form.js のテスト
npm run test:portfolio-filter # portfolio-filter.js のテスト
npm run test:technical-approach # technical-approach.js のテスト

# テストグループ実行
npm run test:group6           # main, navigation, portfolio-filter
npm run test:group7           # contact-form, technical-approach
```

### JavaScript コードチェック

```bash
# ESLint実行
npm run lint:js

# 自動修正
npm run lint:js-fix

# Prettier実行
npm run format

# 全リントツール実行
npm run lint
```

## Docker環境でのテスト

### 環境変数の設定

Docker環境では以下の環境変数が自動設定されます：

```bash
WP_TESTS_DIR="/tmp/wordpress-tests-lib"
WP_TESTS_DB_HOST="db-test"
WP_TESTS_DB_NAME="wp_test_suite"
WP_TESTS_DB_USER="wp_test_user"
WP_TESTS_DB_PASSWORD="wp_test_password"
```

### Docker内でのテスト実行

```bash
# Dockerコンテナにアクセス
docker-compose exec wordpress bash

# テーマディレクトリに移動
cd /var/www/html/wp-content/themes/kei-portfolio

# テストセットアップ
./setup-test-env.sh --docker

# テスト実行
composer test
npm test
```

### Docker Compose設定例

```yaml
services:
  db-test:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: rootpassword
      MYSQL_DATABASE: wp_test_suite
      MYSQL_USER: wp_test_user
      MYSQL_PASSWORD: wp_test_password
    volumes:
      - test_db_data:/var/lib/mysql

volumes:
  test_db_data:
```

## テスト設計と構成

### ディレクトリ構造

```
tests/
├── bootstrap.php           # PHPUnit bootstrap
├── test-helpers.php        # テストヘルパー関数
├── js/                     # Jestテスト
│   ├── setup.js           # Jest setup
│   ├── main.test.js       # main.js のテスト
│   ├── navigation.test.js # navigation.js のテスト
│   └── ...
├── SyntaxTest.php         # PHP構文テスト
├── FunctionsTest.php      # WordPress関数テスト
├── SecurityTest.php       # セキュリティテスト
└── ...
coverage/                  # カバレッジレポート
├── html/                  # PHP HTMLレポート
└── js/                    # JavaScript HTMLレポート
```

### テスト環境の特徴

#### 独立実行可能なテスト
- **group1**: PHP構文チェックとWordPressコーディング規約
- WordPress環境なしで実行可能
- CIパイプラインの最初のステップに適している

#### WordPress依存テスト
- **group2-10**: WordPress Test Suiteが必要
- データベース接続が必要
- 実際のWordPress環境での統合テスト

### カバレッジ設定

#### PHP（PHPUnit）
- 対象: テーマのPHPファイル
- 除外: vendor/, node_modules/, tests/, assets/
- 閾値: 各種メトリクスで70%以上

#### JavaScript（Jest）
- 対象: assets/js/**/*.js
- 除外: *.min.js, vendor/**
- 閾値: 各種メトリクスで70%以上

## CI/CD統合

### GitHub Actions設定例

```yaml
name: Tests

on: [push, pull_request]

jobs:
  php-tests:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.0'
          extensions: mysqli
          
      - name: Install dependencies
        run: composer install --no-interaction
        
      - name: Run syntax tests
        run: composer test:syntax
        
      - name: Run standards tests  
        run: composer test:standards

  js-tests:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup Node.js
        uses: actions/setup-node@v3
        with:
          node-version: '18'
          
      - name: Install dependencies
        run: npm ci
        
      - name: Run tests
        run: npm run test:ci
```

### 品質チェック用コマンド

```bash
# 全品質チェック実行
npm run ci                    # JavaScript品質チェック
composer check               # PHP基本チェック
composer check:full          # PHP全チェック

# 個別チェック
composer lint                # PHP構文・規約
npm run lint                 # JavaScript・CSS
composer analyze             # 静的解析
```

## トラブルシューティング

### よくある問題と解決方法

#### 1. Composer関連

**問題**: `composer: command not found`
```bash
# 解決方法: ローカルComposerを使用
curl -sS https://getcomposer.org/installer | php
php composer.phar install
```

**問題**: `Your requirements could not be resolved`
```bash
# 解決方法: Composerキャッシュクリア
composer clear-cache
composer update
```

#### 2. WordPress Test Suite関連

**問題**: `WordPress test environment not found`
```bash
# 解決方法: 環境変数設定
export WP_TESTS_DIR="/tmp/wordpress-tests-lib"

# または、WordPress Test Suiteなしでテスト実行
composer test:syntax  # 構文チェックのみ
```

**問題**: データベース接続エラー
```bash
# 解決方法: Docker環境の確認
docker-compose ps           # コンテナ状態確認
docker-compose up db-test   # テストDBコンテナ起動
```

#### 3. Jest関連

**問題**: `Cannot find module 'jest'`
```bash
# 解決方法: node_modules再インストール
rm -rf node_modules package-lock.json
npm install
```

**問題**: `ENOSPC: System limit for number of file watchers reached`
```bash
# 解決方法: Linux環境での設定
echo fs.inotify.max_user_watches=524288 | sudo tee -a /etc/sysctl.conf
sudo sysctl -p
```

#### 4. 権限問題

**問題**: `Permission denied: ./setup-test-env.sh`
```bash
# 解決方法: 実行権限付与
chmod +x setup-test-env.sh
```

**問題**: Docker内でのファイル権限エラー
```bash
# 解決方法: 適切なユーザーでコンテナ実行
docker-compose exec --user www-data wordpress bash
```

### ログとデバッグ

#### PHPUnit デバッグ

```bash
# 詳細出力
./vendor/bin/phpunit --verbose

# 特定のテストのみデバッグ
./vendor/bin/phpunit --debug --filter testSpecificFunction
```

#### Jest デバッグ

```bash
# 詳細出力
npm test -- --verbose

# 特定のテストファイルのみ
npm test tests/js/main.test.js -- --verbose
```

## 参考情報

### 関連ドキュメント

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Jest Documentation](https://jestjs.io/docs/getting-started)
- [WordPress Test Suite](https://make.wordpress.org/cli/handbook/misc/plugin-unit-tests/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)

### テストファイルの場所

- PHPテスト: `tests/*.php`
- JavaScriptテスト: `tests/js/*.test.js`
- 設定ファイル: `phpunit.xml`, `jest.config.js`
- セットアップスクリプト: `setup-test-env.sh`

### サポート

テスト実行で問題が発生した場合：

1. 最初に `./setup-test-env.sh` を再実行
2. 依存関係の再インストール（`composer install`, `npm install`）
3. このドキュメントのトラブルシューティングセクションを確認
4. ログファイルとエラーメッセージを確認して問題を特定
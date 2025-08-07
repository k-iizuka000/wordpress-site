# WordPress テーマ kei-portfolio Docker環境使用ガイド

## 概要

このガイドでは、WordPress テーマ「kei-portfolio」用のDocker化された完全なテスト・開発環境の使用方法を説明します。

## 前提条件

以下のソフトウェアがインストールされている必要があります：

- **Docker**: 20.10以上
- **Docker Compose**: 2.0以上  
- **Git**: 2.0以上
- **Make** (オプション): Makefile使用時

### システム要件

- **CPU**: 2コア以上推奨
- **メモリ**: 4GB以上推奨（テスト環境含む場合は8GB以上）
- **ディスク**: 10GB以上の空き容量

## クイックスタート

### 1. リポジトリのクローン

```bash
git clone https://github.com/kei-aokiki/kei-portfolio.git
cd kei-portfolio
```

### 2. 開発環境の起動

```bash
# 開発環境を起動
docker-compose up -d

# ログの確認
docker-compose logs -f wordpress
```

### 3. WordPressの初期設定

ブラウザで http://localhost:8080 にアクセスして、WordPress のセットアップを完了してください。

### 4. テーマの有効化

1. WordPress管理画面 (http://localhost:8080/wp-admin) にログイン
2. 外観 > テーマ で「Kei Portfolio Pro」テーマを有効化

## 詳細な使用方法

### 開発環境の管理

#### 環境の起動
```bash
# バックグラウンドで起動
docker-compose up -d

# フォアグラウンドで起動（ログ表示）
docker-compose up

# 特定のサービスのみ起動
docker-compose up -d wordpress db
```

#### 環境の停止
```bash
# すべてのサービスを停止
docker-compose down

# データベースも含めて完全に削除
docker-compose down -v

# イメージも削除
docker-compose down --rmi all
```

#### 環境の再構築
```bash
# イメージを再ビルドして起動
docker-compose up -d --build

# キャッシュを使わずに完全再ビルド
docker-compose build --no-cache
```

### アクセス方法

#### メインサービス
- **WordPress**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081  
- **MailHog**: http://localhost:8025
- **Nginx** (プロキシ経由): http://localhost

#### 管理画面
- **WordPress管理画面**: http://localhost:8080/wp-admin
- **データベース**: phpMyAdmin (http://localhost:8081)
  - サーバ: `db`
  - ユーザ名: `wp_user`
  - パスワード: `wp_password`

### テスト環境の使用

#### テスト環境の起動
```bash
# テスト環境専用でコンテナ起動
docker-compose -f docker-compose.test.yml up -d

# すべてのテスト実行
docker-compose -f docker-compose.test.yml run wordpress-test
```

#### 個別テスト実行
```bash
# PHPUnitテストのみ
docker-compose -f docker-compose.test.yml run phpunit

# Jestテストのみ  
docker-compose -f docker-compose.test.yml run jest

# リンターのみ
docker-compose -f docker-compose.test.yml --profile linting up
```

### テストスクリプトの使用

実行権限付きのスクリプト `run-tests.sh` を使用できます：

```bash
# すべてのテスト実行
./run-tests.sh all

# PHPテストのみ (詳細出力)
./run-tests.sh php --verbose

# JavaScriptテストのみ
./run-tests.sh js

# リンターのみ
./run-tests.sh lint

# カバレッジレポート生成
./run-tests.sh coverage

# パフォーマンステスト
./run-tests.sh performance

# アクセシビリティテスト
./run-tests.sh a11y

# WordPress Test Suite セットアップ
./run-tests.sh --setup

# クリーンアップ付きテスト実行
./run-tests.sh all --clean
```

## 開発ワークフロー

### 1. ファイル編集

テーマファイルはホストマシンで編集可能です：

```
wordpress/wp-content/themes/kei-portfolio/
├── functions.php
├── style.css
├── assets/
│   ├── js/
│   └── css/
├── template-parts/
└── page-templates/
```

### 2. アセットのビルド

Node.jsコンテナが自動的に変更を監視してビルドします：

```bash
# Node.js開発サーバの確認
docker-compose logs -f node

# 手動ビルド
docker-compose run node npm run build
```

### 3. デバッグ

#### PHP デバッグ
```bash
# WordPress コンテナに接続
docker-compose exec wordpress bash

# ログの確認
docker-compose exec wordpress tail -f /var/log/apache2/error.log

# WP-CLI の使用
docker-compose exec wordpress wp --info
```

#### JavaScript デバッグ
```bash
# Node.js コンテナに接続
docker-compose exec node sh

# ビルド状況確認
docker-compose exec node npm run build:dev
```

### 4. データベース操作

#### phpMyAdmin経由
1. http://localhost:8081 にアクセス
2. サーバ: `db`, ユーザ名: `wp_user`, パスワード: `wp_password`

#### コマンドライン経由
```bash
# データベースコンテナに接続
docker-compose exec db mariadb -u wp_user -p kei_portfolio_dev

# バックアップの作成
docker-compose exec db mariadb-dump -u wp_user -p kei_portfolio_dev > backup.sql

# バックアップの復元
docker-compose exec -T db mariadb -u wp_user -p kei_portfolio_dev < backup.sql
```

## トラブルシューティング

### よくある問題と解決方法

#### 1. ポートが既に使用されている
```bash
# 使用中のポートを確認
sudo lsof -i :8080

# docker-compose.yml でポートを変更
ports:
  - "8081:80"  # 8080 → 8081 に変更
```

#### 2. パーミッションエラー
```bash
# ファイル権限の修正
sudo chown -R $USER:$USER wordpress/wp-content/themes/kei-portfolio

# Docker内での権限確認
docker-compose exec wordpress ls -la /var/www/html/wp-content/themes/
```

#### 3. メモリ不足
```bash
# Docker のメモリ制限を確認・増加
docker system info | grep -i memory

# コンテナのリソース使用量確認
docker stats
```

#### 4. データベース接続エラー
```bash
# データベースコンテナの状況確認
docker-compose logs db

# 接続テスト
docker-compose exec wordpress wp db check
```

#### 5. アセットビルドエラー
```bash
# Node.jsコンテナの再起動
docker-compose restart node

# キャッシュクリア
docker-compose exec node npm cache clean --force

# node_modules の再構築
docker-compose run --rm node rm -rf node_modules && npm install
```

### ログの確認方法

```bash
# すべてのサービスのログ
docker-compose logs

# 特定のサービスのログ
docker-compose logs wordpress
docker-compose logs db

# リアルタイムログ監視
docker-compose logs -f --tail=100 wordpress

# エラーログのみ
docker-compose logs wordpress 2>&1 | grep -i error
```

### 開発環境のリセット

完全にクリーンな状態で開始したい場合：

```bash
# すべて停止・削除
docker-compose down -v --rmi all

# Dockerキャッシュクリア
docker system prune -a

# イメージ再ビルド
docker-compose build --no-cache

# 環境再起動
docker-compose up -d
```

## パフォーマンス最適化

### 1. ファイル同期の最適化

macOSでの高速化：
```yaml
# docker-compose.yml
volumes:
  - ./wordpress/wp-content/themes/kei-portfolio:/var/www/html/wp-content/themes/kei-portfolio:cached
```

### 2. メモリ割り当ての調整

Docker Desktop の設定で以下を調整：
- **メモリ**: 8GB以上
- **CPU**: 4コア以上
- **ディスク**: 20GB以上

### 3. 不要なサービスの無効化

必要に応じてサービスをコメントアウト：
```yaml
# docker-compose.yml
# phpmyadmin:  # 不要な場合はコメントアウト
# mailhog:     # メールテスト不要な場合
```

## CI/CD 設定

### GitHub Actions 例

```yaml
# .github/workflows/test.yml
name: Test WordPress Theme

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Build and test
        run: |
          docker-compose -f docker-compose.test.yml up --abort-on-container-exit
          
      - name: Upload coverage
        uses: codecov/codecov-action@v3
        with:
          file: ./coverage/clover.xml
```

### GitLab CI 例

```yaml
# .gitlab-ci.yml
test:
  stage: test
  services:
    - docker:dind
  before_script:
    - docker-compose -f docker-compose.test.yml build
  script:
    - docker-compose -f docker-compose.test.yml up --abort-on-container-exit
  artifacts:
    reports:
      junit: coverage/junit.xml
      coverage_report:
        coverage_format: cobertura
        path: coverage/cobertura.xml
```

## セキュリティ考慮事項

### 1. 本番環境での注意点

- `.env` ファイルでパスワードを管理
- `docker-compose.prod.yml` で本番設定を分離
- セキュリティヘッダーの設定確認

### 2. 開発環境でのセキュリティ

```bash
# デフォルトパスワードの変更
# docker-compose.yml の環境変数を変更

# 不要なポートの非公開化
# ports: セクションをコメントアウト

# ファイアウォール設定
sudo ufw deny 3306  # MySQLポートを外部から遮断
```

## サポートとリソース

### 公式ドキュメント
- [Docker Compose](https://docs.docker.com/compose/)
- [WordPress Developer Resources](https://developer.wordpress.org/)
- [PHPUnit](https://phpunit.de/documentation.html)
- [Jest](https://jestjs.io/docs/getting-started)

### 関連ファイル
- `Dockerfile`: マルチステージビルド定義
- `docker-compose.yml`: 開発環境設定
- `docker-compose.test.yml`: テスト環境設定
- `run-tests.sh`: テスト実行スクリプト
- `tests/テスト環境状況.md`: 環境詳細情報

### 問題報告
GitHub Issues: https://github.com/kei-aokiki/kei-portfolio/issues

---

**注意**: このガイドは開発・テスト環境での使用を想定しています。本番環境への適用前に、必ずセキュリティ設定の見直しとパフォーマンステストを実施してください。
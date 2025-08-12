# 推奨開発コマンド

## 環境構築
```bash
# Docker環境起動
docker compose up -d

# 依存関係インストール（themes/kei-portfolio内で実行）
npm ci
composer install
```

## 開発作業
```bash
# 開発サーバー起動（監視モード）
npm run dev
npm run watch  # 監視のみ

# 本番ビルド
npm run build
```

## テスト実行
### リポジトリルートから
```bash
./run-tests.sh all        # 全テスト実行
./run-tests.sh php        # PHPテストのみ
./run-tests.sh js         # JSテストのみ
./run-tests.sh lint       # Lintチェック
./run-tests.sh coverage   # カバレッジレポート生成
```

### テーマディレクトリから（themes/kei-portfolio）
```bash
# PHP関連
composer run test:all          # 全PHPテスト
composer run test:syntax       # 構文チェック
composer run test:standards    # WordPress標準チェック
composer run test:coverage     # カバレッジレポート生成
composer run lint              # PHP Lintチェック
composer run analyze           # PHPStan静的解析

# JavaScript関連
npm test                       # Jestテスト実行
npm run test:watch            # テスト監視モード
npm run test:coverage         # JSカバレッジレポート

# Lint & フォーマット
npm run lint                  # JS/CSS Lint実行（自動修正）
npm run lint:js-check         # JS Lintチェックのみ
npm run lint:css-check        # CSS Lintチェックのみ
npm run format                # Prettierフォーマット
npm run format:check          # フォーマットチェック
```

## タスク完了時の必須コマンド
```bash
# リポジトリルートで実行
./run-tests.sh all            # 全テスト実行

# themes/kei-portfolio内で実行
npm run lint:js-check         # JavaScript Lintチェック
npm run lint:css-check        # CSS Lintチェック
composer run lint             # PHP Lintチェック
```

## デプロイ関連
```bash
# サイトエクスポート（アップロード用ディレクトリ作成）
./export-wordpress-site.sh

# データベースエクスポート/インポート
./export-database.sh
./import-database.sh
```

## セキュリティ & 品質チェック
```bash
npm audit                     # npmセキュリティ監査
composer run analyze          # PHPStan静的解析（レベル6）
```

## Git操作
```bash
git status                    # 変更状況確認
git diff                      # 差分確認
git log --oneline -10        # 最近のコミット確認
```
# プロジェクト構造

## リポジトリルート構造
```
wordpress-site/
├── themes/
│   ├── kei-portfolio/        # メインテーマ（実ソース）
│   └── kei-portfolio-upload/  # アップロード用コピー
├── wordpress/                 # WordPress本体コンテンツ
├── docker/                    # Docker設定ファイル
├── database-exports/          # DBエクスポート
├── site-exports/              # サイトエクスポート
├── docs/                      # 恒久的ドキュメント
├── tasks/                     # 作業ドキュメント
├── tests/                     # テスト結果・レポート
├── reviews/                   # コードレビュー結果
├── reports/                   # 調査・分析レポート
├── learning/                  # 学習記録
├── scripts/                   # 補助スクリプト
└── data/                      # データファイル
```

## テーマ構造（themes/kei-portfolio）
```
kei-portfolio/
├── assets/                    # 静的リソース
│   ├── css/                  # スタイルシート
│   ├── js/                   # JavaScriptファイル
│   ├── images/               # 画像ファイル
│   └── dist/                 # ビルド成果物
├── inc/                       # PHP機能ファイル
│   ├── enqueue.php          # スクリプト/スタイル登録
│   ├── custom-post-types.php # カスタム投稿タイプ
│   └── ...
├── template-parts/            # 再利用可能テンプレート
├── page-templates/            # ページテンプレート
├── tests/                     # テストファイル
│   ├── js/                   # JavaScriptテスト
│   └── *.php                 # PHPテスト
├── vendor/                    # Composer依存関係
├── node_modules/              # npm依存関係
├── functions.php              # テーマ機能定義
├── style.css                  # テーマ情報
├── front-page.php            # フロントページ
├── single.php                # 個別投稿
├── archive.php               # アーカイブページ
├── page-*.php                # 各種固定ページ
└── single-project.php        # プロジェクト個別ページ
```

## 主要ファイル
- `docker-compose.yml`: Docker開発環境設定
- `package.json`: npm設定・スクリプト定義
- `composer.json`: PHP依存関係・コマンド定義
- `webpack.config.js`: Webpack設定
- `phpunit.xml`: PHPUnit設定
- `jest.config.js`: Jest設定
- `.stylelintrc.json`: Stylelint設定
- `CLAUDE.md`: プロジェクト指示書
- `README.md`: プロジェクト概要

## ドキュメント管理ルール
- **docs/**: 仕様書、API仕様など恒久的文書
- **tasks/**: 作業指示、実装計画など一時的文書
- **tests/**: テスト結果、実行ログ
- **reviews/**: コードレビュー結果（日付付き）
- **reports/**: 調査・分析結果
- **learning/**: 学習記録（追記形式）
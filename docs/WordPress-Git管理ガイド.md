# WordPress Git管理ガイド

## WordPressのディレクトリ構造

```
wordpress/
├── wp-admin/          # 管理画面（Git管理不要）
├── wp-includes/       # コアファイル（Git管理不要）
├── wp-content/        # カスタマイズ領域
│   ├── themes/        # テーマ（Git管理推奨）
│   ├── plugins/       # プラグイン（選択的にGit管理）
│   ├── uploads/       # メディアファイル（Git管理不要）
│   └── languages/     # 言語ファイル（Git管理不要）
├── wp-config.php      # 設定ファイル（Git管理不要、機密情報含む）
├── index.php          # Git管理不要
└── その他のコアファイル  # Git管理不要
```

## Git管理すべきファイル

### 1. **必ずGit管理すべきもの**
- `/wp-content/themes/あなたのテーマ/` - 自作テーマ
- カスタムプラグイン（自作のもの）
- プロジェクト独自の設定ファイル

### 2. **Git管理してはいけないもの**
- WordPressコアファイル（wp-admin/, wp-includes/）
- wp-config.php（データベース情報などの機密情報）
- /wp-content/uploads/（メディアファイル）
- 外部からインストールしたプラグイン
- キャッシュファイル

### 3. **管理方法による選択**
- **方法A: テーマのみ管理** - 最も一般的
- **方法B: wp-content全体を管理** - uploads除外必須
- **方法C: WordPress全体を管理** - 特殊なケース

## 推奨される運用方法

### 方法A: テーマディレクトリのみGit管理（推奨）

```bash
# レンタルサーバーからFTPでダウンロード後
cd /path/to/wordpress/wp-content/themes/your-theme
git init
git add .
git commit -m "Initial commit"
```

**メリット:**
- シンプルで管理しやすい
- デプロイが簡単
- チーム開発に適している

### 方法B: プロジェクト全体を管理

```bash
# プロジェクトルートで
git init
# .gitignoreを作成（後述）
git add .
git commit -m "Initial commit"
```

## .gitignore の設定

```gitignore
# WordPress コアファイル
/wp-admin/
/wp-includes/
/index.php
/license.txt
/readme.html
/wp-*.php
xmlrpc.php

# 設定ファイル
wp-config.php
.htaccess

# プラグイン（自作以外）
/wp-content/plugins/*
!/wp-content/plugins/your-custom-plugin/

# アップロードファイル
/wp-content/uploads/

# キャッシュ
/wp-content/cache/
/wp-content/advanced-cache.php
/wp-content/wp-cache-config.php

# ログファイル
*.log
error_log

# OS関連
.DS_Store
Thumbs.db

# エディタ
*.swp
*.swo
*~
.idea/
.vscode/
```

## デプロイ戦略

### 1. FTP/SFTP経由
```bash
# 変更したテーマファイルのみアップロード
/wp-content/themes/your-theme/
```

### 2. Git経由（サーバーにGitがある場合）
```bash
# サーバー上で
cd /path/to/wordpress/wp-content/themes/your-theme
git pull origin main
```

### 3. 自動デプロイ（CI/CD）
- GitHub Actions
- GitLab CI
- レンタルサーバーのWebhook機能

## データベースとメディアファイルの管理

### バックアップ戦略
1. **データベース**: 定期的にエクスポート
2. **メディアファイル**: 別途バックアップ
3. **プラグイン設定**: 管理画面の設定をドキュメント化

### 同期方法
- 本番→開発: データベースとuploadsをダウンロード
- 開発→本番: テーマファイルのみアップロード
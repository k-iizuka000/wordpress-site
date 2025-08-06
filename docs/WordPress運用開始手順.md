# WordPress運用開始手順

## 1. 現在の状況整理

### このプロジェクトの構成
```
/wordpress-site/              # Gitで管理するプロジェクトルート
├── .git/                    # Git管理
├── .gitignore              # 作成済み
├── index.html              # 現在のポートフォリオ（サンプル）
├── styles.css              # 現在のスタイル
├── script.js               # 現在のスクリプト
├── docs/                   # ドキュメント
├── wp-theme/               # テーマ開発用（作成済み）
└── wordpress/              # FTPでダウンロードするWordPress本体（未取得）
```

## 2. FTPでWordPressをダウンロード

```bash
# 1. wordpressディレクトリを作成
mkdir wordpress

# 2. FTPクライアント（FileZillaなど）でレンタルサーバーに接続
# 3. WordPressのファイルをwordpress/ディレクトリにダウンロード
```

## 3. Git管理の開始

### オプション1: テーマのみ管理（推奨）

```bash
# テーマ開発に集中する場合
cd wp-theme/kenta-portfolio
git init
git add .
git commit -m "Initial theme commit"

# リモートリポジトリに接続
git remote add origin [your-repo-url]
git push -u origin main
```

### オプション2: プロジェクト全体を管理

```bash
# プロジェクトルートで（現在のディレクトリ）
git add .
git commit -m "Initial project setup"
git push
```

## 4. 開発フロー

### A. ローカル開発環境
1. **MAMP/XAMPP** などでローカルWordPress環境を構築
2. テーマファイルを開発
3. Gitでコミット

### B. デプロイフロー
```
ローカル開発 → Git Push → FTPでテーマのみアップロード
```

## 5. 重要な注意点

### やってはいけないこと
- ❌ wp-config.phpをGitにコミット
- ❌ データベース情報を公開
- ❌ /uploads/の画像をGit管理
- ❌ WordPressコアファイルの編集

### やるべきこと
- ✅ テーマファイルのみ編集
- ✅ 定期的なバックアップ
- ✅ ステージング環境でテスト
- ✅ セキュリティプラグインの導入

## 6. 次のステップ

1. **WordPressをFTPでダウンロード**
2. **ローカル開発環境の構築**
3. **現在のHTMLをWordPressテーマ化**
4. **カスタム投稿タイプで制作実績を管理**
5. **ブログ機能の実装**

## 7. 将来的な拡張

- プラグイン開発 → `/wp-content/plugins/your-plugin/`
- 複数サイト管理 → マルチサイト機能
- API連携 → REST APIの活用
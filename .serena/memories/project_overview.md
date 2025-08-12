# プロジェクト概要

## プロジェクトの目的
- Kei Aokikiのポートフォリオサイト (https://kei-aokiki.dev/)
- WordPressをベースとしたポートフォリオとブログのためのサイト
- メインテーマ：`themes/kei-portfolio`

## テックスタック
### フロントエンド
- JavaScript (ES6+)
- CSS/SCSS
- Bootstrap 5.3
- Swiper 10.3
- Webpack 5 (バンドラー)

### バックエンド
- PHP 7.4+
- WordPress
- MySQL (Docker経由)

### 開発環境
- Docker Compose (WordPress + MySQL + phpMyAdmin)
- Node.js 18.0+
- npm 9.0+
- Composer (PHP依存管理)

## ローカル開発環境
- WordPressアプリ: http://localhost:8090
- phpMyAdmin: http://localhost:8091
- Docker起動: `docker compose up -d`

## ブランチ戦略
- メインブランチ: `main`
- 現在の作業ブランチ: `feature/update-skills-and-career`

## デプロイプロセス
- `export-wordpress-site.sh`でアップロード用ディレクトリ(`themes/kei-portfolio-upload`)にコピー
- 本番環境: https://kei-aokiki.dev/
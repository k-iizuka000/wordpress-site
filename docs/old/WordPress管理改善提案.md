# WordPress プロジェクト管理改善提案

## 現状分析

### 現在の管理方法
- `/wordpress/` ディレクトリに WordPress コアファイル全体を格納
- テーマは `/wordpress/wp-content/themes/kei-portfolio/` に配置
- `.gitignore` で一部除外しているが、wp-admin、wp-includes は Git 管理下

### 問題点
1. **管理しすぎ** - WordPress コアファイル（wp-admin、wp-includes）を Git で管理
2. **リポジトリサイズ** - 不要なファイルでリポジトリが肥大化
3. **更新の競合** - WordPress 自動更新と Git 管理が競合する可能性
4. **セキュリティリスク** - コアファイルの変更を誤って追跡する可能性

## 業界のベストプラクティス（2024年）

### 推奨される管理方法

#### 1. **最小限管理アプローチ**（推奨）
```
プロジェクトルート/
├── .gitignore
├── docker-compose.yml
├── wp-content/              # これのみ管理
│   ├── themes/
│   │   └── kei-portfolio/   # カスタムテーマのみ
│   ├── plugins/
│   │   └── my-custom-*/     # カスタムプラグインのみ
│   └── mu-plugins/          # 必須プラグイン
└── wp-config-sample.php     # テンプレートとして
```

#### 2. **Docker開発の場合の特別な考慮事項**
- WordPress コアは Docker イメージで管理
- ローカルにはカスタムコードのみ保持
- `docker-compose.yml` でボリュームマッピング

### 理想的な .gitignore

```gitignore
# WordPress Core - 絶対に管理しない
/wordpress/
!/wordpress/wp-content/

# wp-content内でも選択的に管理
/wordpress/wp-content/*
!/wordpress/wp-content/themes/
!/wordpress/wp-content/plugins/
!/wordpress/wp-content/mu-plugins/

# テーマ - カスタムテーマのみ
/wordpress/wp-content/themes/*
!/wordpress/wp-content/themes/kei-portfolio/

# デフォルトテーマは除外
/wordpress/wp-content/themes/twenty*/

# プラグイン - カスタムのみ
/wordpress/wp-content/plugins/*
!/wordpress/wp-content/plugins/my-custom-*/

# 絶対に管理しないもの
/wordpress/wp-content/uploads/
/wordpress/wp-content/cache/
wp-config.php
```

## 改善提案

### 即座に実施すべき改善（優先度: 高）

#### ステップ 1: Git 履歴から WordPress コアを削除
```bash
# Git履歴からコアファイルを削除（大幅なサイズ削減）
git filter-branch --tree-filter 'rm -rf wordpress/wp-admin wordpress/wp-includes' HEAD

# または BFG Repo-Cleaner を使用（より高速）
bfg --delete-folders "{wp-admin,wp-includes}" --no-blob-protection
```

#### ステップ 2: プロジェクト構造の再編成
```
wordpress-site/
├── .gitignore
├── docker-compose.yml
├── Dockerfile
├── themes/                    # wp-contentから移動
│   └── kei-portfolio/
├── plugins/                   # カスタムプラグイン用
├── config/
│   └── wp-config-docker.php
└── scripts/
    └── setup.sh               # 初期設定スクリプト
```

#### ステップ 3: Docker Compose の更新
```yaml
services:
  wordpress:
    volumes:
      # カスタムテーマのみマウント
      - ./themes/kei-portfolio:/var/www/html/wp-content/themes/kei-portfolio
      - ./plugins:/var/www/html/wp-content/plugins
      # WordPress コアは Docker イメージから
```

### 中期的な改善（優先度: 中）

1. **Composer 導入**
   - WordPress コア、プラグインを Composer で管理
   - `composer.json` で依存関係を定義

2. **環境変数の活用**
   - `.env` ファイルで設定管理
   - `wp-config.php` から機密情報を分離

3. **CI/CD パイプライン**
   - GitHub Actions で自動デプロイ
   - テスト自動化

### 長期的な改善（優先度: 低）

1. **Bedrock 構造への移行**
   - Roots.io の Bedrock で現代的な構成
   - より良いセキュリティとDX

2. **モノレポ構造**
   - テーマとプラグインを別リポジトリ化
   - Git サブモジュールで管理

## 結論

**現在の管理方法は確かに「管理しすぎ」です。**

### 推奨アクション
1. **WordPress コアファイルを Git 管理から除外**
2. **カスタムテーマ・プラグインのみを管理**
3. **Docker で WordPress コアを提供**

これにより：
- リポジトリサイズ: 約 90% 削減
- 管理の複雑さ: 大幅に簡素化
- セキュリティ: 向上
- 開発効率: 改善

### 移行の優先順位
1. まず `.gitignore` を更新して新規ファイルの追跡を停止
2. 次に Git 履歴をクリーンアップ
3. 最後にプロジェクト構造を再編成

この改善により、業界標準に準拠した、保守性の高い WordPress 開発環境が実現できます。
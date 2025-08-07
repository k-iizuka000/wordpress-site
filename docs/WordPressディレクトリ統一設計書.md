# WordPressディレクトリ統一設計書

作成日: 2025年8月7日  
作成者: Claude Code  
対象: kei-portfolioテーマのディレクトリ構成統一

## 1. 設計目的

WordPressサイトのディレクトリ構成を統一し、以下の課題を解決する：
- git管理対象外ディレクトリ（`wordpress/wp-content/themes/kei-portfolio/`）の使用停止
- 開発ディレクトリ（`themes/kei-portfolio/`）への一本化
- 重複ファイルの削除とメンテナンス性の向上

## 2. 現状分析

### 2.1 ディレクトリ構成

```
wordpress-site/
├── themes/kei-portfolio/              # git管理対象（メイン開発ディレクトリ）
│   ├── すべてのテーマファイル
│   └── create-pages-cli.php
│
├── wordpress/wp-content/themes/kei-portfolio/  # git管理対象外
│   └── create-pages-cli.php          # 重複ファイル（削除対象）
│
└── Dockerfile                         # 修正済み（themes/を参照）
```

### 2.2 参照状況

- **実行環境**: Dockerfileは`themes/kei-portfolio/`を参照（修正済み）
- **ドキュメント**: 30ファイルが旧パスを参照（過去の記録として保持）
- **git設定**: `.gitignore`で旧ディレクトリを除外設定中

## 3. 実装計画

### Phase 1: 即座の対応（リスク：低）

#### 1.1 不要ファイルの削除
```bash
# 重複ファイルの削除
rm -f /Users/kei/work/wordpress-site/wordpress/wp-content/themes/kei-portfolio/create-pages-cli.php

# ディレクトリが空の場合は削除
rmdir /Users/kei/work/wordpress-site/wordpress/wp-content/themes/kei-portfolio/ 2>/dev/null || true
```

#### 1.2 .gitignoreの更新
```diff
# 旧テーマディレクトリ（移動済み）
-/wordpress/wp-content/themes/kei-portfolio/
+# /wordpress/wp-content/themes/kei-portfolio/  # 廃止済み - 2025/08/07
```

### Phase 2: 開発環境の最適化（オプション）

#### 2.1 シンボリックリンクの設定
WordPressが期待する場所にテーマを配置しつつ、git管理を維持：

```bash
# シンボリックリンクの作成
cd /Users/kei/work/wordpress-site
ln -sf ../../themes/kei-portfolio wordpress/wp-content/themes/kei-portfolio

# 確認
ls -la wordpress/wp-content/themes/
```

**メリット:**
- WordPressの標準的なディレクトリ構造を維持
- git管理は`themes/`ディレクトリで継続
- 既存のWordPress管理画面からのアクセスが可能

**注意点:**
- Windowsでの互換性を考慮する必要がある
- Dockerビルド時はCOPYコマンドでファイルを配置

### Phase 3: Docker環境の確認（完了済み）

現在のDockerfile（88行目）:
```dockerfile
COPY themes/kei-portfolio/ ./wp-content/themes/kei-portfolio/
```

**状態**: ✅ 修正済み・動作確認済み

## 4. リスク評価と対策

### 4.1 低リスク項目

| 項目 | リスク内容 | 影響度 | 対策 |
|------|-----------|--------|------|
| ドキュメント参照 | 過去の文書が旧パスを参照 | 低 | 対策不要（記録として保持） |
| .gitignore | 不要なエントリ | なし | 任意でコメントアウト |

### 4.2 解決済みリスク

| 項目 | 状態 | 確認方法 |
|------|------|----------|
| Dockerビルド | ✅ 修正済み | `docker-compose build`で確認 |
| テーマファイル配置 | ✅ 正常 | コンテナ内で確認済み |

## 5. 実装手順

### 手順1: バックアップ（念のため）
```bash
# 作業前の状態を記録
git status > /tmp/git-status-before.txt
ls -la wordpress/wp-content/themes/ > /tmp/themes-before.txt
```

### 手順2: クリーンアップ実行
```bash
# 不要ファイルの削除
rm -f wordpress/wp-content/themes/kei-portfolio/create-pages-cli.php

# 空ディレクトリの削除
rmdir wordpress/wp-content/themes/kei-portfolio/ 2>/dev/null || echo "ディレクトリに他のファイルが存在"
```

### 手順3: git設定の更新
```bash
# .gitignoreの編集
# エディタで開いて該当行をコメントアウト
```

### 手順4: 動作確認
```bash
# Dockerビルドの確認
docker-compose build

# テーマの配置確認
docker-compose up -d
docker exec -it [container_name] ls -la /var/www/html/wp-content/themes/
```

## 6. 完了条件

- [ ] `wordpress/wp-content/themes/kei-portfolio/create-pages-cli.php`が削除されている
- [ ] Dockerビルドが正常に完了する
- [ ] テーマファイルが正しい場所に配置される
- [ ] 開発作業が`themes/kei-portfolio/`で継続できる

## 7. 今後の運用方針

### 開発ルール
1. **すべての開発作業は`themes/kei-portfolio/`で実施**
2. **`wordpress/wp-content/`配下への直接編集は禁止**
3. **Dockerビルド時に自動的に正しい場所へ配置**

### ディレクトリ構成の維持
- 定期的に`wordpress/wp-content/themes/`をチェック
- 不要なファイルが作成されていないか確認
- git管理対象は`themes/`ディレクトリのみ

## 8. 参考情報

### 関連ドキュメント
- `/reports/WordPressファイル構成調査結果_20250807.md` - 詳細な調査結果
- `/docs/テーマディレクトリ移動設計_20250807.md` - 初期移動設計
- `/reports/完全移行実装状況レポート_20250807.md` - 移行実装の記録

### 技術的背景
- WordPressは`wp-content/themes/`にテーマを期待
- Dockerコンテナ内では`/var/www/html/wp-content/themes/`に配置
- git管理とWordPress構造の両立が課題

---

この設計書に基づいて、WordPressサイトのディレクトリ構成を統一し、よりメンテナブルな構造を実現します。
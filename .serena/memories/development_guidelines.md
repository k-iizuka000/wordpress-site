# 開発ガイドライン

## 最重要原則（CLAUDE.mdより）
1. **確実性最優先**: 時間効率よりも確実性を常に優先する
2. **タスク厳守**: 指定されたタスク以外の作業は絶対に行わない
3. **順番厳守**: タスクは必ず順番通りに実行する
4. **曖昧さの排除**: 曖昧な機能リクエストを構造化された要件に変換
5. **学習記録**: 技術的教訓は「learning/学習記録.md」に追記

## 開発フロー
1. 依存関係インストール
   ```bash
   cd themes/kei-portfolio
   npm ci && composer install
   ```

2. Docker環境起動
   ```bash
   docker compose up -d
   ```

3. 開発作業
   - 既存ファイルの編集を優先（新規作成は最小限）
   - 既存のライブラリ/パターンを使用
   - コメントは追加しない（要求された場合のみ）

4. テスト実行
   ```bash
   ./run-tests.sh all
   ```

5. コード品質チェック
   ```bash
   npm run lint:js-check
   npm run lint:css-check
   composer run lint
   ```

## ファイル編集時の注意
- 周辺コードのスタイルを模倣
- 既存のインポート/ライブラリを確認
- セキュリティベストプラクティスに従う
- シークレット/キーをコミットしない

## テスト方針
### JavaScript
- Jest使用、カバレッジ70%以上
- `tests/js/**/*.test.js`に配置

### PHP
- PHPUnit使用
- WordPress Coding Standards準拠
- `tests/*.php`に配置

## ドキュメント作成
- ユーザー指示がない限り適切なディレクトリに作成
- ファイル名は日本語で内容が判別できるように
- 同一トピックは新規作成せず更新

## セキュリティ
- 環境変数でシークレット管理
- `wp-config.docker.php`で設定
- フルパス（個人情報）を含めない
- APIキー/トークンの確認必須

## システム情報
- OS: Darwin (macOS)
- 開発URL: http://localhost:8090
- phpMyAdmin: http://localhost:8091
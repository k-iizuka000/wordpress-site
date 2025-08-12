# タスク完了時チェックリスト

## 必須実行項目
1. **テスト実行**
   ```bash
   # リポジトリルートで
   ./run-tests.sh all
   ```

2. **Lintチェック**
   ```bash
   # themes/kei-portfolio内で
   npm run lint:js-check    # JavaScript
   npm run lint:css-check   # CSS
   composer run lint        # PHP
   ```

3. **PHPStan静的解析**（PHP変更時）
   ```bash
   composer run analyze
   ```

## セキュリティチェック（CLAUDE.md記載事項）
- [ ] フルパス（個人情報）の削除
- [ ] APIキー・トークンの確認
- [ ] パスワードのハードコーディング確認
- [ ] 機密ファイルの取り扱い確認
- [ ] 環境変数の適切な使用確認

## コード品質確認
- [ ] コメントは必要最小限か（不要なコメントは追加しない）
- [ ] 既存のコードスタイルに従っているか
- [ ] 既存のライブラリ/フレームワークを使用しているか
- [ ] BEM命名規則（CSS）、snake_case（PHP関数）、camelCase（JS）を守っているか

## 学習記録
- 技術的教訓や技術的負債が発生した場合は`learning/学習記録.md`に追記

## PR前の最終確認
- [ ] 全テストが通過している
- [ ] Lintエラーがない
- [ ] セキュリティチェック完了
- [ ] 不要なデバッグコード削除
- [ ] console.logやvar_dump削除
# WordPressテーマ「kei-portfolio」の包括的テスト実施プロンプト

## 背景情報
- **対象ディレクトリ**: `@wordpress/wp-content/themes/kei-portfolio/`
- **テーマ名**: kei-portfolio
- **目的**: WordPressテーマの全機能が正常に動作することを確認するための包括的なテスト

## ステップバイステップ実行手順

### ステップ0: テスト環境の事前確認（新規追加）

```
環境確認と代替手段の準備:

1. 利用可能なツールの確認（Bashで実行）:
   which phpunit && echo "PHPUnit: 利用可能" || echo "PHPUnit: 利用不可"
   which wp && echo "WP-CLI: 利用可能" || echo "WP-CLI: 利用不可"
   which npm && echo "npm: 利用可能" || echo "npm: 利用不可"
   which docker && echo "Docker: 利用可能" || echo "Docker: 利用不可"

2. テスト方法の決定:
   - PHPUnit利用可能 → PHPUnitでユニットテスト実行
   - PHPUnitなし + PHPあり → php -l での構文チェックのみ
   - WP-CLI利用可能 → WordPress CLIコマンドでテーマ検証
   - npmあり → package.jsonのスクリプトを確認・活用
   - Docker利用可能 → @wordpress/envの使用を検討

3. 環境に応じたテスト戦略を @tests/テスト環境状況.md に記録
```

### ステップ1: 初期準備とテスト環境の確認

```
以下のタスクを順番に実行してください:

1. TodoWriteツールを使用して、以下のタスクリストを作成:
   - [ ] テスト環境の確認と準備
   - [ ] テスト設計書の作成
   - [ ] PHPファイルの構文チェックとテスト
   - [ ] CSS/JSアセットの読み込みテスト
   - [ ] ページテンプレートの動作確認
   - [ ] AJAX機能のテスト
   - [ ] 画面遷移テストの実施
   - [ ] パフォーマンス最適化の確認
   - [ ] テスト結果レポートの作成
   - [ ] 発見された問題の修正
   - [ ] 修正後の再テスト実施

2. 現在のWordPress環境を確認:
   - `@wordpress/`ディレクトリにWordPressがインストールされているか確認
   - wp-config.phpの存在確認（※内容は読み込まない - セキュリティ配慮）
   - テスト可能な環境かどうかの判定
```

### ステップ2: テスト設計の実施

```
Taskツールを使用して、wordpress-php-test-designerエージェントに以下を依頼:

"WordPressテーマ kei-portfolio の包括的なテスト設計と戦略立案を行ってください。
現在の環境状況（@tests/テスト環境状況.md）を考慮し、以下の観点を含めてください:
1. PHPファイルの構文と機能テスト（環境に応じた実行方法）
2. CSS/JavaScriptの読み込みと動作テスト（ESLint/Stylelintの有無を考慮）
3. ページテンプレートごとの表示確認
4. カスタム投稿タイプ(project)の動作確認
5. AJAX通信（お問い合わせフォーム等）のテスト
6. レスポンシブデザインの確認
7. ナビゲーションとリンクの動作確認
8. セキュリティベストプラクティスの確認

テスト設計書を @tests/WordPressテーマテスト設計書.md として作成してください。"
```

### ステップ3: 自動テストコードの実装

```
Taskツールを使用して、wordpress-fullstack-developerエージェントに以下を依頼:

"WordPressテーマ kei-portfolio の自動テストコードを実装してください。
以下のテストファイルを作成してください:

1. PHPUnit用のテストファイル作成:
   - @wordpress/wp-content/themes/kei-portfolio/tests/ ディレクトリを作成
   - bootstrap.phpファイルを作成（WordPressテスト環境のセットアップ）
   - phpunit.xml.distファイルを作成（PHPUnit設定）

2. 基本的なテストケースの実装:
   a) PHP構文チェックテスト (tests/SyntaxTest.php):
      - 全PHPファイルの構文エラーチェック
      - functions.phpの必須関数の存在確認
      
   b) テンプレート存在確認テスト (tests/TemplateTest.php):
      - 必須テンプレートファイルの存在確認
      - カスタムページテンプレートの確認
      
   c) アセット読み込みテスト (tests/AssetTest.php):
      - CSS/JSファイルの存在確認
      - エンキュー関数の動作確認

3. JavaScript用のテストファイル作成:
   - tests/js/ディレクトリを作成
   - 各JSファイルに対応するテストを作成"
```

### ステップ3.5: 自動テストコードのレビュー

```
実装が完了したら、code-review-post-implementationエージェントでレビューを実施:

Taskツールでcode-review-post-implementationエージェントを使用:
   "作成されたテストコードのレビューを実施してください。
   以下の観点で評価してください:
   - テストカバレッジの適切性
   - テストコードの保守性
   - WordPressのテストベストプラクティスへの準拠
   - エッジケースの考慮
   
   レビュー結果を @reviews/[日時]-test-code-review.md に出力してください。"
```

### ステップ4: test-executorエージェントによる包括的テスト実行

```
Taskツールでtest-executorエージェントを使用して包括的なテストを実行:

"WordPressテーマ kei-portfolio に対して以下のテストを実行してください:
1. 環境状況（@tests/テスト環境状況.md）に基づいた適切なテスト方法の選択
2. 全PHPファイルの構文チェック（最低限実施）
3. PHPUnit利用可能な場合はユニットテスト実行
4. WordPress CLI利用可能な場合はテーマ検証コマンド実行
5. JavaScript/CSSの検証（利用可能なツールで）:
   - ESLint利用可能: npx eslint assets/js/
   - ESLintなし: 基本的な構文チェックのみ
   - Stylelint利用可能: npx stylelint '**/*.css'
   - Stylelintなし: CSSファイルの存在確認のみ
6. テスト結果を @tests/テスト実行結果.md に詳細に出力
7. カバレッジレポートの生成（可能な場合）"

環境に応じた代替実行方法:
- PHPUnitなし → php -l での構文チェックに切り替え
- WP-CLIなし → 手動での基本動作確認スクリプト作成
- npm/nodeなし → JavaScript/CSSの静的解析のみ
```

### ステップ5: 手動テストチェックリストの実行

```
以下の手動テストチェックリストを作成し、可能な範囲で自動化してください:

@tests/手動テストチェックリスト.md として以下を含める:

□ フロントページ (front-page.php)
  - ヒーローセクションの表示
  - ポートフォリオセクションの表示
  - スキルセクションの表示
  
□ アバウトページ (page-templates/page-about.php)
  - 各セクションの表示確認
  - 画像の読み込み確認
  
□ ポートフォリオページ (page-templates/page-portfolio.php)
  - プロジェクト一覧の表示
  - フィルタリング機能の動作
  - 技術アプローチセクション
  
□ コンタクトページ (page-templates/page-contact.php)
  - フォームの表示
  - AJAX送信機能の動作確認
  
□ プロジェクト詳細ページ (single-project.php)
  - カスタム投稿タイプの表示
  - メタデータの表示
  
□ アーカイブページ (archive-project.php)
  - プロジェクト一覧の表示
  - ページネーション機能
```

### ステップ6: テスト結果の検証と問題修正

```
以下を順番に実行:

1. test-executorエージェントの結果（@tests/テスト実行結果.md）を確認

2. 発見された問題に対して:
   - 問題の重要度を分類（Critical/High/Medium/Low）
   - 各問題に対する修正案を作成
   - TodoWriteツールで修正タスクリストを作成

3. 修正の実施:
   - Criticalな問題から順番に修正
   - 各修正後にテストを再実行
```

### ステップ7: コードレビューとセキュリティ監査

```
1. code-review-post-implementationエージェントを使用:
   "kei-portfolioテーマの全体的なコード品質レビューを実施し、
   以下の観点で評価してください:
   - WordPressコーディング規約への準拠
   - セキュリティベストプラクティス
   - パフォーマンス最適化
   - アクセシビリティ
   
   レビュー結果を @reviews/[日時]-theme-review.md に出力してください。"

2. security-audit-post-developmentエージェントを使用:
   "kei-portfolioテーマのセキュリティ監査を実施してください:
   - フルパス（個人情報）の検出と削除
   - ハードコードされた認証情報の検出
   - SQLインジェクション/XSS脆弱性のチェック
   - 環境変数の適切な使用確認
   - CLAUDE.mdのセキュリティチェック項目の確認
   
   監査結果を @reviews/[日時]-security-audit.md に出力してください。"

3. パフォーマンステストの実施:
   - ページ読み込み時間の測定
   - アセットの最適化確認
   - データベースクエリの効率性確認
```

### ステップ8: 最終テストレポートの作成

```
以下の内容を含む最終テストレポートを作成:

@reports/WordPressテーマテスト結果レポート.md として:

1. エグゼクティブサマリー
   - テスト実施期間
   - テスト範囲
   - 全体的な結果

2. テスト結果詳細
   - 実施したテスト項目と結果
   - 発見された問題と修正状況
   - 残存する既知の問題

3. パフォーマンステスト結果
   - ページ読み込み時間
   - リソース使用状況

4. 推奨事項
   - 今後の改善点
   - 追加テストの提案

5. 技術的負債の記録
   @learning/学習記録.md に以下を追記:
   - テスト中に発見された技術的負債
   - 今後の改善に向けた学習ポイント
```

### ステップ9: 継続的テストの設定

```
今後の開発のために、以下を設定:

1. pre-commitフックの設定（可能な場合）:
   - PHP構文チェック
   - コーディング規約チェック

2. テスト自動化スクリプトの作成:
   @wordpress/wp-content/themes/kei-portfolio/run-tests.sh として:
   - 全テストを順番に実行
   - 結果をレポートとして出力

3. CI/CD設定の提案書作成（将来の実装用）:
   @docs/CI-CD提案書.md
```

## 実行時の注意事項

1. **セキュリティ（CLAUDE.md準拠）**: 
   - データベース接続情報などの機密情報は絶対に出力しない
   - APIキーやトークンが含まれていないか常に確認
   - wp-config.phpの内容は読み込まない（存在確認のみ）
   - フルパス（個人情報）を出力に含めない

2. **順序の厳守**:
   - 各ステップは必ず順番通りに実行
   - 前のステップが完了してから次に進む

3. **エラーハンドリング**:
   - エラーが発生した場合は、その内容を記録
   - 修正を試みる前に原因を特定

4. **ドキュメント化**:
   - すべての作業を @docs/ 配下に記録
   - 問題と解決策を詳細に記載

5. **確実性の優先（CLAUDE.md最重要原則）**:
   - 時間効率よりも確実性を常に優先
   - 不明な点は推測せず、代替案を提示
   - 指定されたタスク以外の作業は行わない
   - タスクは必ず順番通りに実行

6. **エージェントの効果的な活用**:
   - wordpress-fullstack-developer: 実装タスク
   - wordpress-php-test-designer: テスト設計・戦略
   - test-executor: テスト実行（軽量・高速）
   - code-review-post-implementation: コードレビュー（自動実行）
   - security-audit-post-development: セキュリティ監査
   - document-manager-ja: ドキュメント管理

このプロンプトに従って作業を開始してください。各ステップの完了時には、TodoWriteツールでタスクを完了済みにマークし、次のステップに進んでください。
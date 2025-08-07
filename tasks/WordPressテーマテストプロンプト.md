# WordPressテーマ「kei-portfolio」の包括的テスト実施プロンプト

## 背景情報
- **対象ディレクトリ**: `@wordpress/wp-content/themes/kei-portfolio/`
- **テーマ名**: kei-portfolio
- **目的**: WordPressテーマの全機能が正常に動作することを確認するための包括的なテスト

## 実行手順
下記、think で実行せよ

### ステップ0: テスト環境の構築（test-environment-docker-architectエージェント使用）

```
Taskツールを使用して、test-environment-docker-architectエージェントに環境構築を依頼:

"WordPressテーマ kei-portfolio の完全なテスト環境をDocker化して構築してください。
以下の要件を満たす環境を作成してください:

【必要な環境とツール】
1. PHP環境（WordPress用）:
   - PHP 8.0以上（WordPress 6.x対応）
   - PHPUnit 9.5以上 + WordPress Test Suite
   - PHP CodeSniffer + WordPress Coding Standards
   - PHPStan（静的解析）
   - Composer（依存関係管理）

2. JavaScript/Node.js環境:
   - Node.js LTS版
   - Jest（ユニットテスト）
   - ESLint + @wordpress/eslint-plugin
   - Stylelint + @wordpress/stylelint-config
   - Webpack（ビルドツール）

3. WordPress環境:
   - WordPress最新版
   - WP-CLI
   - MySQL/MariaDB
   - テスト用データベース

4. 追加ツール:
   - Lighthouse（パフォーマンステスト）
   - axe-core / pa11y（アクセシビリティテスト）
   - @wordpress/env（WordPress開発環境）

【Docker構成の要件】
1. マルチステージビルドで最適化
2. 開発とテスト環境の分離
3. ホットリロード対応
4. ボリュームマウントによるコード同期
5. テストカバレッジレポートの出力設定

【出力ファイル】
- Dockerfile（マルチステージビルド）
- docker-compose.yml（開発環境）
- docker-compose.test.yml（テスト環境）
- phpunit.xml（PHPUnit設定）
- jest.config.js（Jest設定）
- .dockerignore
- run-tests.sh（テスト実行スクリプト）

【設定ファイル】
- composer.json（必要なPHPパッケージを含む）:
  * phpunit/phpunit ^9.5
  * yoast/phpunit-polyfills ^1.0
  * squizlabs/php_codesniffer ^3.7
  * wp-coding-standards/wpcs ^3.0
  * phpstan/phpstan ^1.10
  
- package.json（必要なnpmパッケージを含む）:
  * @wordpress/scripts
  * @wordpress/env
  * jest @testing-library/jest-dom
  * eslint prettier stylelint
  * lighthouse axe-core pa11y

【テストスクリプト】
composer.jsonとpackage.jsonに以下のスクリプトを設定:
- test:all - すべてのテストを実行
- test:php - PHPUnitテスト
- test:js - Jestテスト
- lint:php - PHPCS実行
- lint:js - ESLint実行
- lint:css - Stylelint実行
- coverage - カバレッジレポート生成

環境構築完了後、@tests/テスト環境状況.md に環境情報を出力してください。
また、@docs/Docker環境使用ガイド.md に使用方法のドキュメントを作成してください。"

注意: test-environment-docker-architectエージェントが、Docker化された完全なテスト環境を
構築し、すべての必要なツールとその設定を提供します。
```

### ステップ1: テスト計画の作成とタスク管理

```
ステップ0でDocker環境構築が完了した前提で、テスト計画を作成:

1. TodoWriteツールを使用して、以下のタスクリストを作成:
   - [ ] Docker環境の起動と動作確認
   - [ ] テスト設計書の作成
   - [ ] PHPファイルの構文チェックとユニットテスト
   - [ ] CSS/JSアセットの品質チェックとテスト
   - [ ] ページテンプレートの動作確認
   - [ ] AJAX機能のテスト
   - [ ] 画面遷移とナビゲーションテスト
   - [ ] パフォーマンス最適化の確認
   - [ ] セキュリティ監査の実施
   - [ ] テスト結果レポートの作成
   - [ ] 発見された問題の修正
   - [ ] 修正後の再テスト実施

2. Docker環境の起動と確認:
   # Docker環境の起動
   docker-compose up -d
   
   # コンテナの状態確認
   docker-compose ps
   
   # PHPコンテナ内でツール確認
   docker-compose exec wordpress composer show --dev
   docker-compose exec wordpress ./vendor/bin/phpcs -i
   
   # Node.jsコンテナ内でツール確認
   docker-compose exec node npm list --depth=0 --dev
   docker-compose exec node npx eslint --version
   docker-compose exec node npx jest --version

3. WordPress環境の確認（Dockerコンテナ内）:
   # WP-CLIでの確認
   docker-compose exec wordpress wp core version
   docker-compose exec wordpress wp theme list
   docker-compose exec wordpress wp theme get kei-portfolio
   
   # データベース接続確認
   docker-compose exec wordpress wp db check
```

### ステップ2: テスト設計の実施（並列処理グループ分けを含む）

```
Taskツールを使用して、wordpress-php-test-designerエージェントに以下を依頼:

"WordPressテーマ kei-portfolio の包括的なテスト設計と戦略立案を行ってください。
インストール済みの全ツール（@tests/テスト環境状況.md）を最大限活用し、以下の観点を含めてください:

【テスト観点】
1. PHPファイルの構文と機能テスト（PHPUnit、PHPStan、PHPCS使用）
2. CSS/JavaScriptの品質と動作テスト（ESLint、Stylelint、Jest使用）
3. ページテンプレートごとの表示確認
4. カスタム投稿タイプ(project)の動作確認
5. AJAX通信（お問い合わせフォーム等）のテスト
6. レスポンシブデザインの確認
7. ナビゲーションとリンクの動作確認
8. セキュリティベストプラクティスの確認

【重要：並列処理のためのグループ分け】
全テストケースを以下の基準で最大10グループに分割してください：
- グループ1: PHP構文チェック関連（すべてのPHPファイル）
- グループ2: WordPress基本機能テスト（functions.php、setup.php等）
- グループ3: ページテンプレートテスト（front-page、about、portfolio）
- グループ4: ページテンプレートテスト（skills、contact、single-project）
- グループ5: カスタム投稿タイプとアーカイブ関連
- グループ6: JavaScript機能テスト（main.js、navigation.js、portfolio-filter.js）
- グループ7: JavaScript機能テスト（contact-form.js、technical-approach.js）
- グループ8: CSS/スタイリングテスト
- グループ9: AJAX/API通信テスト
- グループ10: セキュリティとパフォーマンステスト

各グループは独立して並列実行可能な形で設計し、
グループ間の依存関係を最小化してください。

テスト設計書を @tests/WordPressテーマテスト設計書.md として作成してください。"
```

### ステップ3: 自動テストコードの並列実装（最大10件同時）

```
【重要】以下のタスクを最大10件の並列処理で実行します。
Taskツールを使用して、複数のwordpress-fullstack-developerエージェントを同時に起動してください。

並列タスク1-10を同時に実行:

タスク1: "グループ1のテストコードを実装してください（PHP構文チェック）"
タスク2: "グループ2のテストコードを実装してください（WordPress基本機能）"
タスク3: "グループ3のテストコードを実装してください（ページテンプレート前半）"
タスク4: "グループ4のテストコードを実装してください（ページテンプレート後半）"
タスク5: "グループ5のテストコードを実装してください（カスタム投稿タイプ）"
タスク6: "グループ6のテストコードを実装してください（JavaScript前半）"
タスク7: "グループ7のテストコードを実装してください（JavaScript後半）"
タスク8: "グループ8のテストコードを実装してください（CSS/スタイリング）"
タスク9: "グループ9のテストコードを実装してください（AJAX/API）"
タスク10: "グループ10のテストコードを実装してください（セキュリティ/パフォーマンス）"

各タスクの詳細共通指示:
"WordPressテーマ kei-portfolio の自動テストコードを実装してください。
テスト設計書（@tests/WordPressテーマテスト設計書.md）の該当グループに従って、
以下の構造でテストファイルを作成してください:

【全グループ共通の基本セットアップ】
1. テストディレクトリ構造:
   - @wordpress/wp-content/themes/kei-portfolio/tests/ ディレクトリを作成
   - bootstrap.phpファイルを作成（WordPressテスト環境のセットアップ）
   - phpunit.xml.distファイルを作成（PHPUnit設定）

【グループ別の実装内容】
グループ1（PHP構文チェック）:
   - tests/SyntaxTest.php: 全PHPファイルの構文エラーチェック
   - tests/PhpStandardTest.php: WordPressコーディング規約チェック

グループ2（WordPress基本機能）:
   - tests/FunctionsTest.php: functions.phpの必須関数の存在確認
   - tests/SetupTest.php: テーマセットアップの動作確認
   - tests/EnqueueTest.php: アセットエンキュー関数の動作確認

グループ3-4（ページテンプレート）:
   - tests/TemplateTest.php: 必須テンプレートファイルの存在確認
   - tests/PageTemplateTest.php: カスタムページテンプレートの確認
   - tests/TemplatePartsTest.php: テンプレートパーツの存在と構造確認

グループ5（カスタム投稿タイプ）:
   - tests/CustomPostTypeTest.php: projectタイプの登録確認
   - tests/ArchiveTest.php: アーカイブページの動作確認

グループ6-7（JavaScript）:
   - tests/js/: JavaScriptテストディレクトリ作成
   - 各JSファイルに対応するテストファイル作成
   - Jest設定ファイル（jest.config.js）の作成

グループ8（CSS/スタイリング）:
   - tests/AssetTest.php: CSS/JSファイルの存在確認
   - tests/StyleTest.php: スタイルシートの構造確認

グループ9（AJAX/API）:
   - tests/AjaxTest.php: AJAX通信のテスト
   - tests/ContactFormTest.php: お問い合わせフォームの動作確認

グループ10（セキュリティ/パフォーマンス）:
   - tests/SecurityTest.php: セキュリティチェック
   - tests/PerformanceTest.php: パフォーマンス測定"


注意: 各グループは上記の【グループ別の実装内容】に従って、
該当するテストファイルのみを実装してください。
並列実行のため、他のグループのファイルには触れないでください。"
```

### ステップ3.5: 自動テストコードの初回レビューと修正（並列処理）

```
実装が完了したら、以下を実行:

1. 初回レビュー（最大10件並列）:
Taskツールで10個のcode-review-post-implementationエージェントを並列起動:
   レビュータスク1: "グループ1（PHP構文チェック）のテストコードをレビューしてください"
   レビュータスク2: "グループ2（WordPress基本機能）のテストコードをレビューしてください"
   レビュータスク3: "グループ3（ページテンプレート前半）のテストコードをレビューしてください"
   レビュータスク4: "グループ4（ページテンプレート後半）のテストコードをレビューしてください"
   レビュータスク5: "グループ5（カスタム投稿タイプ）のテストコードをレビューしてください"
   レビュータスク6: "グループ6（JavaScript前半）のテストコードをレビューしてください"
   レビュータスク7: "グループ7（JavaScript後半）のテストコードをレビューしてください"
   レビュータスク8: "グループ8（CSS/スタイリング）のテストコードをレビューしてください"
   レビュータスク9: "グループ9（AJAX/API）のテストコードをレビューしてください"
   レビュータスク10: "グループ10（セキュリティ/パフォーマンス）のテストコードをレビューしてください"

   各レビューでの観点:
   - テストカバレッジの適切性
   - テストコードの保守性
   - WordPressのテストベストプラクティスへの準拠
   - エッジケースの考慮
   
   レビュー結果を @reviews/[日時]-test-code-review-group[N].md に出力

2. 問題の修正（最大10件並列）:
   レビューで指摘された問題を、優先度に応じて最大10件並列で修正:
   
   修正タスク1-10: 各グループの指摘事項を修正
   Taskツールでwordpress-fullstack-developerエージェントを並列起動:
   "グループ[N]のレビュー結果（@reviews/[日時]-test-code-review-group[N].md）に基づいて
   テストコードを修正してください。"

3. 再レビュー（必要に応じて）:
   修正が完了したグループに対して、再度code-review-post-implementationエージェントで
   最終確認を実施（最大10件並列）:
   
   "グループ[N]の修正後のテストコードを最終レビューしてください。
   修正が適切に行われているか確認し、
   結果を @reviews/[日時]-test-code-final-review-group[N].md に出力してください。"
```

### ステップ4: test-executorエージェントによる包括的テスト実行（Docker環境での並列実行）

```
Docker環境を使用して、Taskツールで複数のtest-executorエージェントを並列起動:

【並列実行パターン1: グループ別並列実行（推奨）】
10個のtest-executorエージェントを同時起動:
   実行タスク1: "グループ1のテストを実行（PHP構文チェック）"
   実行タスク2: "グループ2のテストを実行（WordPress基本機能）"
   実行タスク3: "グループ3のテストを実行（ページテンプレート前半）"
   実行タスク4: "グループ4のテストを実行（ページテンプレート後半）"
   実行タスク5: "グループ5のテストを実行（カスタム投稿タイプ）"
   実行タスク6: "グループ6のテストを実行（JavaScript前半）"
   実行タスク7: "グループ7のテストを実行（JavaScript後半）"
   実行タスク8: "グループ8のテストを実行（CSS/スタイリング）"
   実行タスク9: "グループ9のテストを実行（AJAX/API）"
   実行タスク10: "グループ10のテストを実行（セキュリティ/パフォーマンス）"

【並列実行パターン2: 統合実行（Docker環境でのリソース制約時）】
単一のtest-executorエージェントでDocker環境内で包括的なテストを実行:
"WordPressテーマ kei-portfolio に対してDocker環境で以下の完全なテストスイートを実行してください:

1. PHPテスト（Dockerコンテナ内でComposerスクリプト使用）:
   docker-compose exec wordpress composer test              # PHPUnitテスト実行
   docker-compose exec wordpress composer phpcs             # WordPress Coding Standards チェック
   docker-compose exec wordpress composer phpstan           # 静的解析の実行
   
2. JavaScript/CSSテスト（Dockerコンテナ内でnpmスクリプト使用）:
   docker-compose exec node npm run lint:js            # ESLintによるJavaScriptチェック
   docker-compose exec node npm run lint:css           # StylelintによるCSSチェック
   docker-compose exec node npm run test               # Jestによるユニットテスト
   
3. WordPress固有のテスト（DockerコンテナでWP-CLI使用）:
   docker-compose exec wordpress wp theme check kei-portfolio
   docker-compose exec wordpress wp plugin verify-checksums --all
   
4. 統合テスト（Dockerコンテナ内）:
   docker-compose exec wordpress npm run test:all    # すべてのテストを順次実行
   # または、専用のテストスクリプトを使用:
   ./run-tests.sh             # test-environment-docker-architectが作成したスクリプト
   
5. カバレッジレポート生成（Dockerコンテナ内）:
   docker-compose exec wordpress ./vendor/bin/phpunit --coverage-html coverage/php
   docker-compose exec node npx jest --coverage --coverageDirectory=coverage/js
   
6. パフォーマンステスト（Dockerコンテナ内）:
   # Lighthouseによるパフォーマンス計測
   docker-compose exec node npx lighthouse http://wordpress:80 --output json --output-path=/app/tests/lighthouse-report.json
   
7. セキュリティ監査（Dockerコンテナ内）:
   # Composer依存関係のセキュリティチェック
   docker-compose exec wordpress composer audit
   # npm依存関係のセキュリティチェック
   docker-compose exec node npm audit

テスト結果を @tests/テスト実行結果_[日時].md に詳細に出力してください。"

【並列実行時の注意事項】
- 各グループのテストは独立して実行可能
- エラーが発生したグループのみ再実行可能
- 結果は個別ファイルに出力し、最後に統合レポートを作成
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

### ステップ6: テスト結果の検証と問題修正（並列修正対応）

```
以下を順番に実行:

1. test-executorエージェントの結果（@tests/テスト実行結果.md）を確認

2. 発見された問題に対して:
   - 問題の重要度を分類（Critical/High/Medium/Low）
   - 各問題に対する修正案を作成
   - TodoWriteツールで修正タスクリストを作成

3. 修正の実施（最大10件並列）:
   - Criticalな問題を最優先でグループ化
   - 独立した問題は最大10件まで並列修正
   
   並列修正タスク（Taskツールでwordpress-fullstack-developerを並列起動）:
   修正タスク1-10: 各問題の修正を独立して実施
   "問題[N]（@tests/テスト実行結果.mdの該当箇所）を修正してください。
   修正内容を @docs/修正履歴_問題[N].md に記録してください。"
   
   - 各修正完了後、該当グループのテストを再実行（並列可能）
   - 依存関係がある修正は順次実行
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

## 実行時の注意事項（並列処理対応版）

1. **セキュリティ（CLAUDE.md準拠）**: 
   - データベース接続情報などの機密情報は絶対に出力しない
   - APIキーやトークンが含まれていないか常に確認
   - wp-config.phpの内容は読み込まない（存在確認のみ）
   - フルパス（個人情報）を出力に含めない

2. **順序の厳守と並列処理**:
   - 各ステップは必ず順番通りに実行
   - 前のステップが完了してから次に進む
   - ステップ内では最大10件の並列処理を活用
   - グループ間の依存関係に注意（独立したグループのみ並列実行）

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

6. **エージェントの効果的な活用（並列処理最適化）**:
   - wordpress-fullstack-developer: 実装タスク（最大10件並列起動）
   - wordpress-php-test-designer: テスト設計・戦略（グループ分け重視）
   - test-executor: テスト実行（軽量・高速、グループ別並列実行）
   - code-review-post-implementation: コードレビュー（グループ別並列レビュー）
   - security-audit-post-development: セキュリティ監査
   - document-manager-ja: ドキュメント管理
   
   【並列処理のベストプラクティス】
   - 独立したタスクは積極的に並列化
   - 10件を超える場合はバッチ分割
   - エラー発生時は該当グループのみ再実行
   - 並列実行の結果は個別ファイルに出力して後で統合

このプロンプトに従って作業を開始してください。各ステップの完了時には、TodoWriteツールでタスクを完了済みにマークし、次のステップに進んでください。
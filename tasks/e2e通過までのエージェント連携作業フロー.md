# 目的
- 指定コマンドでのE2Eテストを完全成功させるまで、失敗→原因調査→修正→再実行を高速反復する。
- 管理AIは実装を行わず、定義済みエージェント（@.claude/agents）を呼び出してオーケストレーションする。

## 対象コマンド
- `BASE_URL=http://localhost:8090 ./run-tests.sh e2e`

# 利用エージェントと役割
- `test-executor`（haiku）: テスト実行・結果要約・失敗診断・再実行プラン
- `wordpress-fullstack-developer`（sonnet）: 失敗原因の実装修正（PHP/JS/CSS）と必要なテスト追加
- `code-review-post-implementation`（opus）: 実装直後の自動コードレビュー（セキュリティ/品質/パフォーマンス）
- `security-audit-post-development`（opus）: 修正完了後の最終セキュリティ監査（必須5項目）
- `document-manager-ja`（haiku）: ドキュメント作成/更新、学習記録の追記
- `test-environment-docker-architect`（opus）: テスト環境/コンテナ問題の診断・改善（必要時）
- `wordpress-php-test-designer`（opus）: 複雑な失敗時のテスト戦略再設計（必要時）

# 前提・成果物
- 前提: テスト基盤が起動可能であること（Docker等）。起動不可・環境依存エラー時は `test-environment-docker-architect` を先行起動。
- 成果物:
  - テストログ: `tests/YYYYMMDD-hhmm-e2e-*.log`
  - レビュー: `reviews/YYYYMMDD-hhmm-e2e修正レビュー.md`
  - セキュリティ監査: `reports/YYYYMMDD-hhmm-セキュリティ監査.md`
  - 作業記録: 本ファイル（随時更新）
  - 学習記録: `learning/学習記録.md`（追記）

# 反復ワークフロー（管理AIによる呼び出し順）
1. テスト実行（in-progress）
   - Agent: `test-executor`
   - Action:
     - 固定コマンド `BASE_URL=http://localhost:8090 ./run-tests.sh e2e` を実行（他コマンド禁止）
     - 実行コマンド・開始/終了時刻・終了コードを明記
     - 標準出力の先頭/末尾50行を要約に併記し、フルログを `tests/YYYYMMDD-hhmm-e2e-run.log` に保存
     - 確認grep（`crawl-and-check` やE2Eサマリー行の検出）で「E2Eが実際に走った」ことを検証。未検出なら成功報告禁止
     - 失敗要約（ケース名/スタックトレース/推定原因/優先度）を出力
   - Exit 条件:
     - 失敗ゼロ → 手順4へ
     - 失敗あり → 手順2へ

2. 失敗トリアージ（優先度付け）
   - Agent: `document-manager-ja`
   - Action:
     - 本ファイルに失敗一覧と優先度（Critical/High/Medium/Low）、担当エージェント、想定修正範囲を追記
     - 環境起因（起動不可・接続失敗・依存解決）と判断した場合は `test-environment-docker-architect` を呼び出し
   - 分岐:
     - 環境問題 → 手順3a
     - 実装問題 → 手順3b

3a. 環境修正（必要時のみ）
   - Agent: `test-environment-docker-architect`
   - Action:
     - Docker/Compose/設定の診断と修正提案、必要な変更ファイルの提示
     - 実施後に `code-review-post-implementation` で差分チェック
   - 次へ: 手順1へ戻る

3b. 実装修正
   - Agent: `wordpress-fullstack-developer`
   - Action:
     - 失敗原因に対する最小修正＋必要なユニット/統合/JSテストの追加
     - 変更箇所のWordPressベストプラクティス遵守（セキュリティ/性能）
   - 次へ: 手順3c

3c. 変更直後のコードレビュー
   - Agent: `code-review-post-implementation`
   - Action:
     - 今回の差分限定でレビュー（セキュリティ/品質/パフォーマンス/CLAUDE.md準拠）
     - 指摘がCritical/Highの場合は 3b に差し戻し
   - 次へ: 手順1へ戻る

4. 合格確認（グリーン化の確定）
   - Agent: `test-executor`
   - Action:
     - E2Eを再実行してパスを確認（念のため2回連続実行推奨）
     - 成功サマリーを出力しログ保存
   - 次へ: 手順5

5. セキュリティ監査（必須）
   - Agent: `security-audit-post-development`
   - Action: プロジェクト横断の5項目を網羅的に確認
     - [ ] フルパス（個人情報）の削除
     - [ ] APIキー・トークンの確認
     - [ ] パスワードのハードコーディング確認
     - [ ] 機密ファイルの取り扱い確認
     - [ ] 環境変数の適切な使用確認
   - 重大指摘があれば 3b/3c に差し戻し、なければ手順6へ

6. ドキュメント確定と学習の記録
   - Agent: `document-manager-ja`
   - Action:
     - 本ファイルに最終サマリー、変更点、未解決の技術的負債を更新
     - `learning/学習記録.md` に今回の知見を追記

# 判断基準と再帰条件
- Done 条件: E2Eが全件成功し、セキュリティ監査が「合格」。
- 再実行条件: 失敗、レビュー重大指摘、監査指摘がひとつでもある場合は手順1から再試行。
- 収束支援: 同一失敗が3回以上再発する場合は `wordpress-php-test-designer` を招集し、テスト設計/アーキテクチャ見直しを実施。

# 例外・外部調査の扱い
- テスト失敗の原因が外部知識を要する場合、管理AIは「Gemini CLI ワークフロー」を起動し、要約→`gemini` 非対話実行→引用付きMarkdownの結果を取得。
- 外部回答は鵜呑みにせず、本ワークフローの「調査タスクの品質管理プロセス」に従い検証。

# ロギング/保存ルール
- すべてのテスト出力は `tests/` に日付入りで保存。
- レビュー・監査結果は `reviews/` と `reports/` に日付入りで保存。
- 同一トピックのドキュメントは新規作成せず更新する（本ファイル運用）。

# 注意
- 管理AIは実装を自ら行わない。常に該当エージェントを呼び出す。
- 最小修正を優先し、無関係な最適化やリファクタを避ける。
- セキュリティと確実性を最優先し、グリーン化後も監査を必ず通す。

# モデル選定ポリシー（スキル感と役割の整合）
- モデル能力の序列: `opus` > `sonnet` > `haiku`
- 選定方針: 役割優先でエージェントを選び、工程の性質に応じてモデルの重さを最適化（高速反復は軽量、網羅性/確実性は高能力）。

## 割当と意図
- `test-executor`（haiku）: テスト実行・ログ要約は反復回数が多く軽量最適。
- `wordpress-fullstack-developer`（sonnet）: 実装修正の主担当。速度×確実性のバランス。
- `wordpress-php-test-designer`（opus）: 設計/テスト戦略の高度判断。再発/複雑化時に招集。
- `code-review-post-implementation`（opus）: 差分の包括レビュー（セキュリティ/品質/性能）。
- `security-audit-post-development`（opus）: 最終セキュリティ監査（5項目必須チェック）。
- `document-manager-ja`（haiku）: ドキュメント更新は機動性重視。
- `test-environment-docker-architect`（opus）: 環境・Dockerの設計/診断は影響範囲が広く高能力が必要。

## エスカレーション基準（上位モデルへ切替）
- 再発: 同一失敗が2反復以上継続 → 実装修正を `sonnet` → `opus`（test-designer）に引き上げ。
- 高リスク領域: 認証/権限/Nonce、DBスキーマ、キャッシュ/並行性、セキュリティ要件 → 着手前に `opus` で設計見直し。
- 原因不明: 20分超または2回試行で根因未確定、スタックが多層 → `opus` でトリアージ。
- 環境不安定: ビルド/起動2回失敗、ヘルスチェック不良、ポート競合 → `test-environment-docker-architect`（opus）。
- フレーク: 同テストが2回の再実行で結果不一致 → `wordpress-php-test-designer`（opus）でテスト設計是正。
- レビュー/監査: 常に `opus` 固定（降格しない）。

## 運用ルール
- まず `haiku/sonnet` で高速反復し、必要条件を満たしたら `opus` に段階的に引き上げる。
- エスカレーション後は原因解消まで上位のまま運用し、安定後に通常ラインへ戻す。
- 外部知識が必要な場合のみ Gemini CLI ワークフローを使用し、結果は本フローの検証プロセスで必ず裏取りする。

# 実行ガードレール（違反時の扱い）
- `test-executor` が固定E2Eコマンド以外を実行した場合:
  - その結果は無効化し、ポリシー違反として記録
  - 同じエージェントに再試行を要求（固定コマンドの明示・証跡の添付を必須）
  - 2回連続で違反した場合は、管理AIが実行中止→`test-environment-docker-architect` に引継ぎ、環境/設定誤認がないかを診断
- 「E2E成功」の報告には、必ず以下の証跡が添付されていること:
  - 実行コマンド、終了コード
  - 確認grepの一致結果（E2Eスイートの実行痕跡）
  - ログ保存先パス

# 目的
- テーマ `themes/kei-portfolio` を対象に、サイト全画面が表示可能であること、かつ画面表示中にコンソールエラーが発生していないことを自動検証するE2Eテストを整備する。
- 追加で、表示中にサーバ（Docker内Apache/PHP）のログに `[error]` レベルの出力が発生していないことを検証する。

# 実行タイミング（ゲート）
- すべての開発・バグ修正が完了した時に実行（受け入れ判定ゲート）。

# 検証対象（全画面の定義）
- 既定: ベースURL配下の同一オリジンのHTMLページをクローリング（サイト内リンク追跡）。
  - 除外: `wp-admin`, `wp-login`, `logout` を含むURL、`mailto:`, `tel:`、外部ドメイン、同一URLの重複、アンカーのみのリンク。
  - 上限: 最大100ページ（無限ループ防止）。
- 補助: `sitemap.xml` が存在する場合はそこに列挙されたURLも優先的に対象化。
- 固定URL（初期セット）: `/`, `/about/`, `/skills/`, `/contact/`, `/portfolio/`（クローラが見つけられない場合のフォールバック）。

# 合否基準
- 各URLに対し以下を全て満たすこと。
  - HTTPステータス: 200（リダイレクトは最終到達が200でOK）
  - ブラウザコンソール: `error` なし、`pageerror`（未捕捉例外）なし、`requestfailed`（ネットワーク失敗）なし
  - Dockerコンテナ `kei-portfolio-dev` の `docker logs --since <開始時刻>` で `[error]` が検出されない
- サマリ判定: 対象URLすべてが合格であればE2E合格。

# 実行方法（予定）
- Puppeteerベースのスクリプトを `themes/kei-portfolio/tests/e2e/crawl-and-check.js` に配置。
- 実行コマンド（テーマ直下）:
  - `npm run e2e`（既定ベースURL: `http://localhost:8080`）
  - `BASE_URL=http://localhost:8090 npm run e2e`（simple構成利用時など）
- 出力: `tests/e2e/結果_YYYYMMDD-hhmm.json` に詳細結果を保存。

# 事前条件
- DockerでWordPressが起動済み（`docker-compose.optimized.yml` 既定のポート: 8080）。
- Node 18+ インストール済み、テーマで `npm ci` 済み（`puppeteer` 利用）。
- Docker CLI が利用可能（ログ取得のため）。

# 注意事項（セキュリティ）
- 結果出力にOSのフルパスや機密情報を含めない。
- APIキー/トークン/パスワードはスクリプト内にハードコードしない。
- 機密ファイルの参照や保存を行わない。
- 環境依存値は環境変数（`BASE_URL`等）で受け取る。

# 未確定点（要確認）
- 「全画面」の厳密範囲: クローリング方式で十分か、固定URLの追加指定は必要か。
- テスト対象に記事・カスタム投稿（`project`）のサンプル生成を含めるか（データ投入スクリプトの実行要否）。

# 次アクション
- 上記仕様でスクリプト実装 → npm/run-tests.sh へ統合 → 実行手順ドキュメント化。

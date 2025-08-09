使い分けの結論
- 普段は `./run-tests.sh e2e` を使う（推奨・一発で動くように面倒を吸収）
- 詳細デバッグやCIカスタム時は `themes/kei-portfolio/tests/e2e/crawl-and-check.js`（npm run e2e）を直接使う

それぞれの役割
- `./run-tests.sh e2e`: リポジトリ標準のテストランナー
  - ローカル/コンテナどちらでもテーマディレクトリを自動解決
  - `.git` 不在でも `HUSKY=0 npm ci` で依存導入を吸収
  - 他テスト（php/js/lint等）と同一の実行エントリで運用できる
- `crawl-and-check.js`（npm run e2e）: E2Eクローラ本体（Puppeteer）
  - クロール→ページのHTTP/コンソール/ネットワーク検証→Dockerログ[error]検出→JSON
出力
  - 変数を細かく調整したい、Puppeteerの実行パスを指定したい等のデバッグに向く

基本の使い方
- 8090（simple構成）のとき
  - 起動: `docker compose -f docker-compose.yml up -d`
  - 推奨: `BASE_URL=http://localhost:8090 ./run-tests.sh e2e`
- 8080（optimized構成）のとき
  - 起動: `docker compose -f docker-compose.optimized.yml up -d`
  - 推奨: `./run-tests.sh e2e`（BASE_URL未指定時の既定は8080）

直接実行したい場合（詳細デバッグ）
- 初回だけ依存導入: `cd themes/kei-portfolio && HUSKY=0 npm ci`
- 実行（8090）: `BASE_URL=http://localhost:8090 npm run e2e`
- 実行（8080）: `npm run e2e`
- Puppeteer起動で詰まるとき（macOS例）:
  - `PUPPETEER_EXECUTABLE_PATH="/Applications/Google Chrome.app/Contents/MacOS/G
oogle Chrome" BASE_URL=http://localhost:8090 npm run e2e`

環境変数の違い（覚えておくと便利）
- 共通: `BASE_URL`（例: http://localhost:8090）
- 直叩き時のみ便利
  - `MAX_PAGES`（既定100）
  - `DOCKER_CONTAINER`（既定 `kei-portfolio-dev`）
  - `PUPPETEER_EXECUTABLE_PATH`（システムChrome/Chromiumを指定）
- `./run-tests.sh e2e` は内部で `HUSKY=0 npm ci` 実行、テーマパス自動解決

出力と判定
- 出力: `themes/kei-portfolio/tests/e2e/結果_YYYYMMDD-hhmm.json`
- 合格条件:
  - 全ページOK（HTTP 200かつ console/page/requestエラー0）
  - `dockerErrorCount == 0`
- 終了コード: 合格 0 / 失敗 1（CIゲートに適用可）

どちらを使うべきかのガイド
- 手軽に回したい・運用に組み込みたい → `./run-tests.sh e2e`
- テストを細かく調整/調査したい（例: MAX_PAGESやChrome実行パスを切替）→ `npm run e2e`（= crawl-and-check.js）

セキュリティチェック
- フルパスの削除: 出力はテーマ配下の相対パスのみ。OK
- APIキー・トークン: 追加・露出なし。OK
- パスワードのハードコーディング: なし。OK
- 機密ファイルの取り扱い: なし。OK
- 環境変数の適切な使用: `BASE_URL` 等のみ利用。OK

＝＝＝＝＝＝＝＝＝＝＝＝＝＝＝


BASE_URL=http://localhost:8090 ./run-tests.sh e2e
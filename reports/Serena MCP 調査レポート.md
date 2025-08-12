# Serena MCP 調査レポート

- 目的: 「serena」というMCPをClaude Codeで利用するための導入方法を調査
- 依頼キーワード: serena / MCP / claude code / gemini cli

## 結論（要確認）
Geminiの検索結果では、公式の「Serena」MCPサーバは見つかりませんでした。名称が異なる可能性や、非公開/社内ツールの可能性があります。導入を確実に進めるため、以下のいずれかの追加情報の提供をお願いします。

- 公式リポジトリURL（GitHub等）
- パッケージ名（npm/PyPI など）
- 参照ドキュメントやブログ記事URL

情報が得られれば、正確な手順に置き換えて導入を行います。

## Gemini出力（要旨）

> The web search indicates that "Serena" is not a publicly available MCP server. The search result is from another AI model offering to create a plausible implementation of such a server, as no official tool under that name was found.
>
> Hypothetical 'Serena' MCP Server: Setup and Usage Guide（抜粋）
> - 想定リポジトリ例: `https://github.com/your-org/serena-mcp-server`
> - 前提: Node.js 18+、npm、Git
> - 導入（仮）: `npm i -D serena-mcp-server`、`package.json` に `mcp:start` スクリプト
> - 設定（仮）: `.serenarc.json`（port / projectDir / ignore / respectGitIgnore）
> - 連携（仮）: VS Code 設定で `claude.code.mcp.endpoint` を `http://localhost:3030` に設定
> - 注意: いずれも仮説であり、実在パッケージや公式手順ではない

## 調査の含意
- 現時点では正式名称の特定が最優先。
- 類似の既存MCPサーバ（例: ファイルブラウズ、検索、Git操作など）で代替できる可能性もあり。

## 次アクション（提案）
1) 「serena」の公式URL/パッケージ名の提示（ユーザー確認）
2) 確認後、導入ドキュメントを正確版に更新
3) プロジェクトへの導入（スクリプト/設定/動作確認）

## チェックリスト（初期要求の記録）
- [x] serena（存在調査）
- [x] MCP（文脈の確認）
- [x] claude code（連携ポイント確認）
- [x] gemini cli（外部調査の実行）


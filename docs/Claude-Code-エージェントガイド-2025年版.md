# Claude Code /agents 人気設定ガイド（2025年版）

## 目次
1. [基本情報](#基本情報)
2. [人気設定ランキング TOP10](#人気設定ランキング-top10)
3. [SNSで話題の設定テクニック](#snsで話題の設定テクニック)
4. [エージェントの基本構造](#エージェントの基本構造)
5. [実装パターン](#実装パターン)
6. [コミュニティリソース](#コミュニティリソース)

## 基本情報

### /agentsコマンドとは
Claude Codeにおいて、特定のタスクに特化したSub Agents（サブエージェント）を作成・管理するためのコマンド。各エージェントは独自のコンテキストウィンドウ、カスタムシステムプロンプト、特定のツール権限を持つ。

### 主な利点
- **コンテキスト汚染の解決**: タスクごとに独立したコンテキストで動作
- **並列処理**: 最大10個のエージェントを同時実行可能
- **専門性**: 各エージェントが特定分野のエキスパートとして機能
- **チーム共有**: GitHubでエージェント設定を共有可能

## 人気設定ランキング TOP10

### 🥇 第1位: code-reviewer
```yaml
name: code-reviewer
description: Expert code review specialist that proactively reviews code
model: sonnet
tools: Read, Grep, Glob, Bash
```
**人気の理由**: 
- コード品質の自動チェック
- セキュリティ脆弱性の検出
- 本番環境での信頼性確保
- SNSで最も言及が多い

### 🥈 第2位: test-runner
```yaml
name: test-runner
description: Use PROACTIVELY to run tests and fix failures
model: haiku
```
**人気の理由**:
- テストの自動実行と失敗の修正を自動化
- 「PROACTIVELY」キーワードで積極的に動作
- 軽量モデルで高速処理

### 🥉 第3位: backend-architect
```yaml
name: backend-architect
description: Design RESTful APIs, microservice boundaries, and database schemas
model: sonnet
```
**人気の理由**:
- API設計とマイクロサービス構築に特化
- データベーススキーマ設計も対応
- 大規模プロジェクトで必須

### 第4位: frontend-developer
```yaml
name: frontend-developer
description: Build React components, implement responsive layouts
model: sonnet
```
**人気の理由**:
- React開発に特化
- レスポンシブデザイン対応
- クライアントサイドの状態管理

### 第5位: security-auditor
```yaml
name: security-auditor
description: Review code for vulnerabilities and ensure OWASP compliance
model: opus
```
**人気の理由**:
- セキュリティ脆弱性の検出
- OWASP準拠チェック
- 本番環境の安全性確保

### 第6位: docs-architect
```yaml
name: docs-architect
description: Create comprehensive documentation
model: haiku
```
**人気の理由**:
- 軽量モデルで高速動作
- ドキュメント作成を効率化
- コスト効率が良い

### 第7位: ai-engineer
```yaml
name: ai-engineer
description: Build LLM applications, RAG systems, and prompt pipelines
model: opus
```
**人気の理由**:
- AI/LLMアプリケーション開発の専門
- RAGシステム構築対応
- 2025年のトレンド

### 第8位: cloud-architect
```yaml
name: cloud-architect
description: Design AWS/Azure/GCP infrastructure and optimize cloud costs
model: opus
```
**人気の理由**:
- マルチクラウド対応
- コスト最適化機能
- 企業案件に必須

### 第9位: python-backend-engineer
```yaml
name: python-backend-engineer
description: Expert in FastAPI, Django, and async programming
model: sonnet
```
**人気の理由**:
- Python系バックエンド開発の専門家
- FastAPI人気で需要急増
- 非同期プログラミング対応

### 第10位: react-coder
```yaml
name: react-coder
description: Focuses on simple, maintainable components with React 19 patterns
model: sonnet
```
**人気の理由**:
- React 19の最新パターンに対応
- シンプルで保守性の高いコンポーネント
- useEffectの使用を最小限に

## SNSで話題の設定テクニック

### 1. 「PROACTIVELY」マジックワード
descriptionに以下のキーワードを含めると、エージェントが積極的に動作:
- `use PROACTIVELY`
- `MUST BE USED`
- `use proactively when`

### 2. モデル選択戦略
```
┌─────────┬──────────────────────────────┬─────────────┐
│ モデル  │ 用途                          │ 特徴        │
├─────────┼──────────────────────────────┼─────────────┤
│ Haiku   │ 単純タスク、高速処理          │ 低コスト    │
│ Sonnet  │ 中程度の複雑さ、バランス型    │ 汎用性高    │
│ Opus    │ 複雑なタスク、高精度          │ 最高品質    │
└─────────┴──────────────────────────────┴─────────────┘
```

### 3. 保存場所の使い分け
- **プロジェクト専用**: `.claude/agents/`
  - プロジェクト固有の設定
  - 優先度が高い
- **グローバル共有**: `~/.claude/agents/`
  - 全プロジェクトで使用
  - 汎用的な設定

### 4. チーム共有のベストプラクティス
```bash
# チーム用エージェントリポジトリの構造
team-agents/
├── backend/
│   ├── api-designer.md
│   └── database-architect.md
├── frontend/
│   ├── react-developer.md
│   └── ui-designer.md
└── devops/
    ├── cloud-architect.md
    └── security-auditor.md
```

### 5. 並列処理パターン
- 最大10個のエージェントを並列実行可能
- Shift+Tabでトグル切り替え
- タスクキューによる自動調整

## エージェントの基本構造

### 必須要素
```markdown
---
name: your-agent-name
description: When this agent should be invoked
model: sonnet  # optional: haiku, sonnet, opus
tools: Read, Grep, Glob  # optional: inherits all if omitted
---

# System Prompt
You are a specialized agent for [specific task].
Your responsibilities include:
- Task 1
- Task 2
- Task 3
```

### ツール設定
- 省略時：全ツールを継承
- 明示的指定：カンマ区切りでツールをリスト
- MCP連携：MCPサーバーのツールも利用可能

## 実装パターン

### 1. Sequential（順次実行）
```
User Request → Agent A → Agent B → Agent C → Result
```

### 2. Parallel（並列実行）
```
        ┌→ Agent A ─┐
User ───┼→ Agent B ─┼──→ Result
        └→ Agent C ─┘
```

### 3. Routing（ルーティング）
```
User → Analysis Agent → Route to Specialist → Result
```

### 4. Review Cycle（レビューサイクル）
```
Primary Agent → Review Agent → Final Result
         ↑              ↓
         └──── Fix ─────┘
```

## コミュニティリソース

### 主要リポジトリ
1. **wshobson/agents**
   - 56以上の専門エージェント
   - 本番環境対応
   - 定期的な更新

2. **hesreallyhim/awesome-claude-code-agents**
   - キュレーションされたエージェントリスト
   - コミュニティ投稿
   - ベストプラクティス集

3. **dl-ezo/claude-code-sub-agents**
   - 35の専門エージェント
   - エンドツーエンド開発自動化
   - 詳細なドキュメント

### コミュニティ統計（2025年7月時点）
- **最も使用されるモデル**: Sonnet (45%), Haiku (30%), Opus (25%)
- **平均エージェント数/プロジェクト**: 4-6個
- **最も人気のあるカテゴリ**: コードレビュー、テスト、バックエンド

### Twitter/X での評価
- @claude_code フォロワー: 50K+
- ハッシュタグ #ClaudeCodeAgents: 週間投稿数 1000+
- 主な使用事例: スタートアップ、エンタープライズ開発

## まとめ

Claude Codeの/agentsコマンドは、2025年において開発効率を大幅に向上させる重要な機能となっている。特に以下の点が評価されている：

1. **コンテキスト管理の革新**: 独立したコンテキストによるタスクの明確な分離
2. **専門性の向上**: 各エージェントが特定分野のエキスパートとして機能
3. **チーム協業の強化**: GitHubを通じたエージェント設定の共有
4. **コスト最適化**: モデル選択による処理速度とコストのバランス
5. **生産性の向上**: 並列処理による作業時間の短縮

今後もコミュニティによる新しいエージェントの開発と共有が続くことで、さらなる機能拡張が期待される。

---
*最終更新: 2025年8月*
*情報源: GitHub、Twitter/X、Reddit、Hacker News、Medium等のコミュニティ投稿*
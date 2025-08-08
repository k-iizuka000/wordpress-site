# Tailwind CSS エラー修正設計書

## エラー内容
「Uncaught ReferenceError: tailwind is not defined」

## 原因分析
1. Tailwind CSS CDNスクリプトの読み込みタイミングが不適切
2. `tailwind.config`の設定がTailwind CSS読み込み前に実行されている
3. スクリプトの依存関係が明確に定義されていない

## 修正方針

### フェーズ1: 即時修正（優先度：高）
**グループ1: Tailwind CSS読み込み修正**
1. Tailwind CSS設定をインラインスクリプトとして修正
2. 読み込み順序の保証
3. エラーハンドリングの追加

### フェーズ2: 本番環境向け最適化（優先度：中）
**グループ2: Tailwind CSS ビルドプロセス導入**
1. package.jsonの設定
2. Tailwind CSS設定ファイルの作成
3. ビルドプロセスの構築
4. CDN依存からの脱却

### フェーズ3: パフォーマンス最適化（優先度：低）
**グループ3: CSS最適化**
1. 未使用CSSの削除
2. Critical CSSの抽出
3. CSS圧縮とキャッシュ戦略

## 実装計画

### グループ1: 即時修正（今回実施）

#### 1.1 inc/enqueue.phpの修正
```php
// 修正前：
wp_enqueue_script( 
    'tailwindcss', 
    'https://cdn.tailwindcss.com', 
    array(), 
    '3.4.0', 
    false
);

// 修正後：
// Tailwind CSS CDNをインラインで読み込み、設定も同時に行う
```

#### 1.2 Tailwind設定の統合
- Tailwind CSS読み込みと設定を一つのインラインスクリプトに統合
- window.onloadイベントでTailwindの存在確認

#### 1.3 フォールバック処理
- Tailwindが読み込めない場合の基本スタイル提供

### グループ2: 本番環境向け設定（将来実施）

#### 2.1 必要なファイル
- tailwind.config.js
- postcss.config.js
- src/tailwind.css
- package.json更新

#### 2.2 ビルドプロセス
```json
{
  "scripts": {
    "build:css": "tailwindcss -i ./src/tailwind.css -o ./assets/css/tailwind.css --minify",
    "watch:css": "tailwindcss -i ./src/tailwind.css -o ./assets/css/tailwind.css --watch"
  }
}
```

### グループ3: 最適化（オプション）

#### 3.1 PurgeCSS導入
- 未使用のTailwindクラスを削除
- ファイルサイズの大幅削減

#### 3.2 Critical CSS
- Above-the-foldのCSSをインライン化
- レンダリングブロッキングの解消

## テスト項目

### 必須テスト
1. [ ] Tailwind CSSクラスが正しく適用される
2. [ ] JavaScriptエラーが発生しない
3. [ ] 全ページでスタイルが崩れない
4. [ ] モバイル・デスクトップ両方で正常表示

### 性能テスト
1. [ ] ページ読み込み速度の測定
2. [ ] Lighthouse スコアの確認
3. [ ] ネットワーク帯域の使用量確認

## リスクと対策

### リスク
1. CDN障害時のスタイル崩れ
2. 初回読み込み時のレイアウトシフト
3. ブラウザキャッシュの問題

### 対策
1. フォールバックCSSの準備
2. Critical CSSの実装
3. バージョン管理とキャッシュバスティング

## 実施スケジュール

1. **即座に実施**: グループ1（即時修正）
2. **1週間以内**: テストと検証
3. **次回リリース**: グループ2（本番環境設定）
4. **将来的に検討**: グループ3（最適化）
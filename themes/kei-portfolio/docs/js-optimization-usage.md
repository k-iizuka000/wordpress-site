# JavaScript最適化 - 使用方法ガイド

## 概要

JavaScript最適化により、以下の機能が追加されました：

1. **共通ユーティリティクラス（Utils）**
2. **カスタマイズ可能なAjax設定**
3. **強化されたメモリリーク対策**
4. **パフォーマンステスト**

## 1. 共通ユーティリティクラス（Utils）

### 基本的な使用方法

```javascript
// デバウンス処理（300ms遅延）
const debouncedFunction = Utils.debounce(() => {
    console.log('実行されました');
}, 300);

// スロットル処理（100ms間隔）
const throttledFunction = Utils.throttle(() => {
    console.log('実行されました');
}, 100);

// メモリ使用量の取得
const memoryUsage = Utils.getMemoryUsage();
console.log('メモリ使用量:', memoryUsage);

// メモリクリーンアップ
const cleanedCount = Utils.cleanupMemory();
console.log('クリーンアップされた項目数:', cleanedCount);
```

### 安全なタイマー管理

```javascript
// 安全なsetTimeout（自動メモリ管理）
const timer = Utils.safeTimeout(() => {
    console.log('実行されました');
}, 1000);

// タイマーのキャンセル
timer.clear();

// 安全なsetInterval
const interval = Utils.safeInterval(() => {
    console.log('定期実行');
}, 1000);

// インターバルの停止
interval.clear();
```

### パフォーマンス測定

```javascript
// 関数のパフォーマンス測定
const result = await Utils.measurePerformance('テスト処理', async () => {
    // 測定したい処理
    await someAsyncFunction();
    return '結果';
});

console.log('実行時間:', result.executionTime);
console.log('結果:', result.result);
```

## 2. Ajax設定のカスタマイズ

### PHPでの設定変更

```php
// functions.php または適切なファイルに追加

// リトライ回数の変更（デフォルト: 3回）
add_filter('kei_portfolio_ajax_max_retries', function($default) {
    return 5; // 5回にリトライ
});

// リトライ間隔の変更（デフォルト: 1000ms）
add_filter('kei_portfolio_ajax_retry_delay', function($default) {
    return 2000; // 2秒間隔
});

// 指数バックオフの無効化（デフォルト: true）
add_filter('kei_portfolio_ajax_exponential_backoff', function($default) {
    return false; // 固定間隔リトライ
});

// タイムアウト時間の変更（デフォルト: 15000ms）
add_filter('kei_portfolio_ajax_timeout', function($default) {
    return 30000; // 30秒
});

// デバウンス遅延時間の変更（デフォルト: 300ms）
add_filter('kei_portfolio_debounce_delay', function($default) {
    return 500; // 500ms
});

// スロットル制限時間の変更（デフォルト: 100ms）
add_filter('kei_portfolio_throttle_limit', function($default) {
    return 200; // 200ms
});

// メモリクリーンアップ間隔の変更（デフォルト: 300000ms = 5分）
add_filter('kei_portfolio_cleanup_interval', function($default) {
    return 600000; // 10分
});
```

### 環境別設定の例

```php
// 開発環境での設定
if (WP_DEBUG) {
    // デバッグ時はより短い間隔で
    add_filter('kei_portfolio_ajax_max_retries', function() { return 2; });
    add_filter('kei_portfolio_ajax_retry_delay', function() { return 500; });
    add_filter('kei_portfolio_cleanup_interval', function() { return 60000; }); // 1分
}

// 本番環境での設定
if (!WP_DEBUG) {
    // 本番環境ではより安定した設定
    add_filter('kei_portfolio_ajax_max_retries', function() { return 5; });
    add_filter('kei_portfolio_ajax_timeout', function() { return 30000; });
    add_filter('kei_portfolio_cleanup_interval', function() { return 600000; }); // 10分
}
```

## 3. メモリリーク対策

### 自動クリーンアップ

```javascript
// 自動クリーンアップの設定（Utils クラスで自動実行）
// 以下の場合にクリーンアップが実行される：
// - ページ離脱時（beforeunload, pagehide）
// - 定期的な間隔（デフォルト: 5分）
// - メモリ使用量が閾値を超えた時

// 手動でのクリーンアップ
Utils.cleanupMemory();

// メモリ使用状況の監視
const usage = Utils.getMemoryUsage();
if (usage.activeTimers > 100) {
    console.warn('タイマーが多すぎます:', usage.activeTimers);
    Utils.cleanupMemory();
}
```

### イベントリスナーの管理

```javascript
// 既存のコードを修正する必要はありません
// SecureBlogManager と BlogAjaxManager は自動的に
// メモリリーク対策を適用します

// カスタムコードでの使用例
class MyComponent {
    constructor() {
        this.eventHandlers = new Map();
        this.timers = new Set();
    }
    
    addEventHandler(element, event, handler) {
        element.addEventListener(event, handler);
        this.eventHandlers.set(`${event}-${Date.now()}`, {
            element, event, handler
        });
    }
    
    cleanup() {
        // イベントハンドラーの削除
        this.eventHandlers.forEach(config => {
            config.element.removeEventListener(config.event, config.handler);
        });
        this.eventHandlers.clear();
        
        // タイマーのクリーンアップ
        this.timers.forEach(timerId => {
            clearTimeout(timerId);
        });
        this.timers.clear();
        
        // Utils クラスのクリーンアップ
        Utils.cleanupMemory();
    }
}
```

## 4. パフォーマンステスト

### テストの実行

1. ブラウザで以下のURLにアクセス：
   ```
   /wp-content/themes/kei-portfolio/tests/performance-test.html
   ```

2. 「🧪 テスト実行」ボタンをクリック

3. コンソール出力でテスト結果を確認

### テスト項目

- **デバウンス性能**: 関数作成時間、大量呼び出し処理時間
- **スロットル性能**: 関数作成時間、実行回数制限の確認
- **メモリ使用量**: メモリ増加量、クリーンアップ効果
- **メモリリーク検出**: タイマー管理、クリーンアップ機能
- **Ajax性能**: 並列リクエスト処理、平均実行時間
- **統合テスト**: 複合的な処理のパフォーマンス

### 結果の解釈

```javascript
// テスト結果は window.performanceTestResults で確認可能
const results = window.performanceTestResults;

// デバウンス性能
console.log('デバウンス作成時間:', results.debounce.creationTime);
console.log('大量呼び出し時間:', results.debounce.callTime);

// メモリ使用量
console.log('初期メモリ:', results.memory.initial);
console.log('クリーンアップ後:', results.memory.afterCleanup);

// エラー確認
if (results.errors.length > 0) {
    console.error('エラーが発生しました:', results.errors);
}
```

## 5. パフォーマンス基準値

### 推奨値

| 項目 | 推奨値 | 警告値 |
|------|--------|--------|
| デバウンス作成時間 | < 5ms | > 10ms |
| スロットル作成時間 | < 5ms | > 10ms |
| メモリクリーンアップ時間 | < 30ms | > 50ms |
| Ajax平均実行時間 | < 100ms | > 200ms |
| 統合テスト時間 | < 300ms | > 500ms |

### 最適化のヒント

1. **デバウンス遅延時間**: ユーザー体験を考慮して200-500msに設定
2. **スロットル制限時間**: スクロールイベントなら16-100ms（60fps基準）
3. **Ajax リトライ回数**: ネットワーク環境に応じて3-5回
4. **メモリクリーンアップ**: 5-10分間隔で自動実行

## 6. トラブルシューティング

### よくある問題

#### 1. Utils クラスが undefined

```javascript
// エラー: Utils is not defined

// 解決方法: スクリプトの読み込み順序を確認
// functions.php の enqueue.php で utils.js が
// 他のスクリプトより先に読み込まれているか確認
```

#### 2. デバウンス処理が効かない

```javascript
// 間違った使用方法
button.addEventListener('click', Utils.debounce(handleClick, 300));

// 正しい使用方法
const debouncedClick = Utils.debounce(handleClick, 300);
button.addEventListener('click', debouncedClick);
```

#### 3. メモリリークが発生している

```javascript
// ページ離脱時に手動クリーンアップを追加
window.addEventListener('beforeunload', () => {
    // カスタムクリーンアップ処理
    myComponent.cleanup();
    Utils.cleanupMemory();
});
```

## 7. 今後の拡張性

### カスタム機能の追加

```javascript
// Utils クラスの拡張例
Utils.customFunction = function(callback, options = {}) {
    // カスタム機能の実装
    const config = {
        timeout: options.timeout || 1000,
        retries: options.retries || 3,
        ...options
    };
    
    return function(...args) {
        // 実装内容
    };
};
```

### 新しいフィルターフックの追加

```php
// functions.php
add_filter('kei_portfolio_custom_setting', function($default) {
    return $customValue;
});

// enqueue.php
'customSetting' => apply_filters('kei_portfolio_custom_setting', $defaultValue),
```

この最適化により、JavaScriptのパフォーマンスが向上し、メモリリークが防止され、カスタマイズも柔軟に行えるようになりました。
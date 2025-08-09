/**
 * JavaScript パフォーマンステスト
 * 
 * テスト対象:
 * - Utils クラスのデバウンス・スロットル性能
 * - メモリ使用量の監視
 * - Ajax処理のパフォーマンス
 * - メモリリーク検出
 * 
 * @package Kei_Portfolio
 * @since 1.0.0
 * @version 1.0.0
 */

// テスト環境のセットアップ
if (typeof global !== 'undefined') {
    // Node.js 環境
    global.window = global.window || {};
    global.document = global.document || {
        readyState: 'complete',
        addEventListener: () => {},
        createElement: () => ({
            setAttribute: () => {},
            textContent: ''
        })
    };
    global.performance = global.performance || {
        now: () => Date.now(),
        memory: {
            usedJSHeapSize: 1000000,
            totalJSHeapSize: 2000000,
            jsHeapSizeLimit: 4000000
        }
    };
}

// テスト結果格納
const testResults = {
    debounce: {},
    throttle: {},
    memory: {},
    performance: {},
    errors: []
};

/**
 * パフォーマンステストスイート
 */
class PerformanceTestSuite {
    constructor() {
        this.testCount = 0;
        this.passedTests = 0;
        this.failedTests = 0;
        this.startTime = performance.now();
    }
    
    /**
     * テスト実行
     */
    async runAllTests() {
        console.log('🚀 JavaScript パフォーマンステスト開始');
        console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        
        try {
            // Utils クラスの初期化確認
            await this.testUtilsInitialization();
            
            // デバウンス性能テスト
            await this.testDebouncePerformance();
            
            // スロットル性能テスト
            await this.testThrottlePerformance();
            
            // メモリ使用量テスト
            await this.testMemoryUsage();
            
            // メモリリーク検出テスト
            await this.testMemoryLeakDetection();
            
            // クリーンアップ性能テスト
            await this.testCleanupPerformance();
            
            // Ajax モック性能テスト
            await this.testAjaxPerformance();
            
            // 統合テスト
            await this.testIntegration();
            
            this.showFinalResults();
            
        } catch (error) {
            console.error('テストスイート実行中にエラーが発生:', error);
            testResults.errors.push({
                test: 'TestSuite',
                error: error.message,
                timestamp: Date.now()
            });
        }
    }
    
    /**
     * Utils クラス初期化テスト
     */
    async testUtilsInitialization() {
        console.log('\n📦 Utils クラス初期化テスト');
        
        const startTime = performance.now();
        
        try {
            // Utils クラスが利用可能かチェック
            this.assert(typeof Utils !== 'undefined', 'Utils クラスが定義されている');
            this.assert(typeof Utils.debounce === 'function', 'debounce メソッドが存在する');
            this.assert(typeof Utils.throttle === 'function', 'throttle メソッドが存在する');
            this.assert(typeof Utils.getMemoryUsage === 'function', 'getMemoryUsage メソッドが存在する');
            this.assert(typeof Utils.cleanupMemory === 'function', 'cleanupMemory メソッドが存在する');
            
            const initTime = performance.now() - startTime;
            console.log(`✅ 初期化チェック完了 (${initTime.toFixed(2)}ms)`);
            
            testResults.performance.initialization = initTime;
            
        } catch (error) {
            console.error('❌ 初期化テスト失敗:', error.message);
            testResults.errors.push({
                test: 'Initialization',
                error: error.message,
                timestamp: Date.now()
            });
        }
    }
    
    /**
     * デバウンス性能テスト
     */
    async testDebouncePerformance() {
        console.log('\n⏱️  デバウンス性能テスト');
        
        const iterations = 1000;
        let executionCount = 0;
        
        const testFunction = () => {
            executionCount++;
        };
        
        const startTime = performance.now();
        const debouncedFunc = Utils.debounce(testFunction, 100);
        const creationTime = performance.now() - startTime;
        
        // 大量呼び出しテスト
        const callStartTime = performance.now();
        for (let i = 0; i < iterations; i++) {
            debouncedFunc();
        }
        const callTime = performance.now() - callStartTime;
        
        // 実行を待つ
        await this.wait(150);
        
        // 結果検証
        this.assert(executionCount === 1, `デバウンス機能が正常動作 (実行回数: ${executionCount})`);
        this.assert(creationTime < 5, `関数作成が高速 (${creationTime.toFixed(2)}ms < 5ms)`);
        this.assert(callTime < 50, `大量呼び出しが高速 (${callTime.toFixed(2)}ms < 50ms)`);
        
        testResults.debounce = {
            creationTime,
            callTime,
            executionCount,
            iterations
        };
        
        console.log(`✅ デバウンステスト完了 - 作成: ${creationTime.toFixed(2)}ms, 呼び出し: ${callTime.toFixed(2)}ms`);
    }
    
    /**
     * スロットル性能テスト
     */
    async testThrottlePerformance() {
        console.log('\n🚦 スロットル性能テスト');
        
        const iterations = 500;
        let executionCount = 0;
        
        const testFunction = () => {
            executionCount++;
        };
        
        const startTime = performance.now();
        const throttledFunc = Utils.throttle(testFunction, 50);
        const creationTime = performance.now() - startTime;
        
        // 短時間での大量呼び出し
        const callStartTime = performance.now();
        for (let i = 0; i < iterations; i++) {
            throttledFunc();
            if (i % 100 === 0) {
                await this.wait(10); // 小さな待機時間を挿入
            }
        }
        const callTime = performance.now() - callStartTime;
        
        await this.wait(100); // スロットル処理完了を待つ
        
        // 結果検証
        this.assert(executionCount > 1, `スロットル機能が動作 (実行回数: ${executionCount})`);
        this.assert(executionCount < iterations, `スロットルが効いている (${executionCount} < ${iterations})`);
        this.assert(creationTime < 5, `関数作成が高速 (${creationTime.toFixed(2)}ms < 5ms)`);
        
        testResults.throttle = {
            creationTime,
            callTime,
            executionCount,
            iterations
        };
        
        console.log(`✅ スロットルテスト完了 - 作成: ${creationTime.toFixed(2)}ms, 実行: ${executionCount}回`);
    }
    
    /**
     * メモリ使用量テスト
     */
    async testMemoryUsage() {
        console.log('\n💾 メモリ使用量テスト');
        
        const initialMemory = Utils.getMemoryUsage();
        
        // 大量のデバウンス関数を作成
        const functions = [];
        const startTime = performance.now();
        
        for (let i = 0; i < 100; i++) {
            functions.push(Utils.debounce(() => {}, 100));
        }
        
        const creationTime = performance.now() - startTime;
        const afterCreationMemory = Utils.getMemoryUsage();
        
        // メモリクリーンアップテスト
        const cleanupStartTime = performance.now();
        Utils.cleanupMemory();
        const cleanupTime = performance.now() - cleanupStartTime;
        const afterCleanupMemory = Utils.getMemoryUsage();
        
        // 結果検証
        this.assert(creationTime < 100, `大量関数作成が高速 (${creationTime.toFixed(2)}ms < 100ms)`);
        this.assert(cleanupTime < 50, `クリーンアップが高速 (${cleanupTime.toFixed(2)}ms < 50ms)`);
        
        testResults.memory = {
            initial: initialMemory,
            afterCreation: afterCreationMemory,
            afterCleanup: afterCleanupMemory,
            creationTime,
            cleanupTime
        };
        
        console.log(`✅ メモリテスト完了 - 作成: ${creationTime.toFixed(2)}ms, クリーンアップ: ${cleanupTime.toFixed(2)}ms`);
        
        if (initialMemory.usedJSHeapSize) {
            const memoryGrowth = afterCreationMemory.usedJSHeapSize - initialMemory.usedJSHeapSize;
            console.log(`📊 メモリ増加量: ${(memoryGrowth / 1024).toFixed(2)}KB`);
        }
    }
    
    /**
     * メモリリーク検出テスト
     */
    async testMemoryLeakDetection() {
        console.log('\n🔍 メモリリーク検出テスト');
        
        const startTime = performance.now();
        const initialMemory = Utils.getMemoryUsage();
        
        // 意図的にタイマーを大量作成
        const timers = [];
        for (let i = 0; i < 50; i++) {
            const timer = Utils.safeTimeout(() => {}, 10000); // 長時間タイマー
            timers.push(timer);
        }
        
        const afterCreationMemory = Utils.getMemoryUsage();
        
        // クリーンアップ実行
        Utils.cleanupMemory();
        const afterCleanupMemory = Utils.getMemoryUsage();
        
        const totalTime = performance.now() - startTime;
        
        // 結果検証
        this.assert(afterCreationMemory.activeTimers >= 50, `タイマーが正しく管理されている`);
        this.assert(afterCleanupMemory.activeTimers < afterCreationMemory.activeTimers, `クリーンアップでタイマーが削除された`);
        this.assert(totalTime < 200, `メモリリーク検出が高速 (${totalTime.toFixed(2)}ms < 200ms)`);
        
        testResults.memory.leakDetection = {
            initialTimers: initialMemory.activeTimers || 0,
            afterCreationTimers: afterCreationMemory.activeTimers || 0,
            afterCleanupTimers: afterCleanupMemory.activeTimers || 0,
            totalTime
        };
        
        console.log(`✅ メモリリーク検出テスト完了 (${totalTime.toFixed(2)}ms)`);
    }
    
    /**
     * クリーンアップ性能テスト
     */
    async testCleanupPerformance() {
        console.log('\n🧹 クリーンアップ性能テスト');
        
        // テストデータ作成
        const functions = [];
        for (let i = 0; i < 200; i++) {
            functions.push(Utils.debounce(() => {}, 100));
            functions.push(Utils.throttle(() => {}, 100));
        }
        
        // 複数回のクリーンアップ性能測定
        const cleanupTimes = [];
        for (let i = 0; i < 5; i++) {
            const startTime = performance.now();
            const cleanedCount = Utils.cleanupMemory();
            const cleanupTime = performance.now() - startTime;
            cleanupTimes.push(cleanupTime);
        }
        
        const avgCleanupTime = cleanupTimes.reduce((a, b) => a + b) / cleanupTimes.length;
        const maxCleanupTime = Math.max(...cleanupTimes);
        
        // 結果検証
        this.assert(avgCleanupTime < 30, `平均クリーンアップ時間が許容範囲 (${avgCleanupTime.toFixed(2)}ms < 30ms)`);
        this.assert(maxCleanupTime < 50, `最大クリーンアップ時間が許容範囲 (${maxCleanupTime.toFixed(2)}ms < 50ms)`);
        
        testResults.performance.cleanup = {
            times: cleanupTimes,
            average: avgCleanupTime,
            maximum: maxCleanupTime
        };
        
        console.log(`✅ クリーンアップ性能テスト完了 - 平均: ${avgCleanupTime.toFixed(2)}ms, 最大: ${maxCleanupTime.toFixed(2)}ms`);
    }
    
    /**
     * Ajax モック性能テスト
     */
    async testAjaxPerformance() {
        console.log('\n🌐 Ajax モック性能テスト');
        
        // Ajax処理のモック
        const mockAjaxRequest = async (delay = 100) => {
            return new Promise(resolve => {
                Utils.safeTimeout(() => {
                    resolve({ success: true, data: 'test' });
                }, delay);
            });
        };
        
        // 並列リクエストテスト
        const requestCount = 10;
        const startTime = performance.now();
        
        const promises = Array.from({ length: requestCount }, (_, i) => 
            Utils.measurePerformance(`request-${i}`, () => mockAjaxRequest(50))
        );
        
        const results = await Promise.all(promises);
        const totalTime = performance.now() - startTime;
        
        const avgExecutionTime = results.reduce((sum, result) => sum + result.executionTime, 0) / results.length;
        
        // 結果検証
        this.assert(results.length === requestCount, `全リクエストが完了 (${results.length}/${requestCount})`);
        this.assert(totalTime < 200, `並列処理が高速 (${totalTime.toFixed(2)}ms < 200ms)`);
        this.assert(avgExecutionTime < 100, `平均処理時間が許容範囲 (${avgExecutionTime.toFixed(2)}ms < 100ms)`);
        
        testResults.performance.ajax = {
            requestCount,
            totalTime,
            averageExecutionTime: avgExecutionTime,
            results
        };
        
        console.log(`✅ Ajax性能テスト完了 - 並列実行: ${totalTime.toFixed(2)}ms, 平均: ${avgExecutionTime.toFixed(2)}ms`);
    }
    
    /**
     * 統合テスト
     */
    async testIntegration() {
        console.log('\n🔄 統合テスト');
        
        const startTime = performance.now();
        
        // 複合的な処理をシミュレート
        let counter = 0;
        const debouncedCounter = Utils.debounce(() => counter++, 50);
        const throttledCounter = Utils.throttle(() => counter++, 25);
        
        // 複雑な処理パターンの実行
        for (let i = 0; i < 100; i++) {
            debouncedCounter();
            throttledCounter();
            
            if (i % 20 === 0) {
                await this.wait(10);
            }
        }
        
        await this.wait(100); // 処理完了を待つ
        
        const integrationTime = performance.now() - startTime;
        const finalMemory = Utils.getMemoryUsage();
        
        // 結果検証
        this.assert(counter > 0, `統合処理が実行された (カウンター: ${counter})`);
        this.assert(integrationTime < 300, `統合処理が高速 (${integrationTime.toFixed(2)}ms < 300ms)`);
        
        testResults.performance.integration = {
            executionTime: integrationTime,
            counter,
            finalMemoryUsage: finalMemory
        };
        
        console.log(`✅ 統合テスト完了 - 実行時間: ${integrationTime.toFixed(2)}ms, カウンター: ${counter}`);
    }
    
    /**
     * 最終結果表示
     */
    showFinalResults() {
        const totalTime = performance.now() - this.startTime;
        
        console.log('\n' + '━'.repeat(50));
        console.log('📊 テスト結果サマリー');
        console.log('━'.repeat(50));
        console.log(`総実行時間: ${totalTime.toFixed(2)}ms`);
        console.log(`テスト総数: ${this.testCount}`);
        console.log(`成功: ${this.passedTests}`);
        console.log(`失敗: ${this.failedTests}`);
        console.log(`成功率: ${((this.passedTests / this.testCount) * 100).toFixed(1)}%`);
        
        if (testResults.errors.length > 0) {
            console.log('\n❌ エラー詳細:');
            testResults.errors.forEach((error, index) => {
                console.log(`${index + 1}. [${error.test}] ${error.error}`);
            });
        }
        
        // 詳細結果をJSON形式で出力（デバッグ用）
        if (typeof window !== 'undefined') {
            window.performanceTestResults = testResults;
            console.log('\n📋 詳細結果は window.performanceTestResults で確認できます');
        }
        
        console.log('\n🏁 テスト完了\n');
    }
    
    /**
     * アサーション
     */
    assert(condition, message) {
        this.testCount++;
        if (condition) {
            this.passedTests++;
            console.log(`  ✅ ${message}`);
        } else {
            this.failedTests++;
            console.log(`  ❌ ${message}`);
            throw new Error(`アサーション失敗: ${message}`);
        }
    }
    
    /**
     * 待機関数
     */
    wait(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
}

// テスト実行
if (typeof window !== 'undefined') {
    // ブラウザ環境
    document.addEventListener('DOMContentLoaded', async () => {
        const testSuite = new PerformanceTestSuite();
        await testSuite.runAllTests();
    });
} else {
    // Node.js 環境（開発時）
    if (typeof module !== 'undefined' && module.exports) {
        module.exports = { PerformanceTestSuite, testResults };
    }
}

// 手動実行用
if (typeof window !== 'undefined') {
    window.runPerformanceTests = async () => {
        const testSuite = new PerformanceTestSuite();
        await testSuite.runAllTests();
    };
}
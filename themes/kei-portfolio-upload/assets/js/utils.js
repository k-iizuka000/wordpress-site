/**
 * 共通ユーティリティクラス
 * 
 * パフォーマンス最適化のための共通機能を提供:
 * - デバウンス処理
 * - スロットル処理
 * - メモリリーク防止機能
 * - パフォーマンス監視
 * 
 * @package Kei_Portfolio
 * @since 1.0.0
 * @version 1.0.0
 */

class Utils {
    // 静的プロパティでグローバル状態を管理
    static _timers = new Map();
    static _throttleCache = new Map();
    static _performanceMetrics = {
        debounceCount: 0,
        throttleCount: 0,
        memoryOptimizations: 0
    };

    /**
     * デバウンス処理
     * 連続する呼び出しを遅延させ、最後の呼び出しのみを実行
     * 
     * @param {Function} func 実行する関数
     * @param {number} wait 待機時間（ミリ秒）
     * @param {boolean} immediate 最初の呼び出しを即座に実行するか
     * @returns {Function} デバウンスされた関数
     */
    static debounce = (func, wait, immediate = false) => {
        if (typeof func !== 'function') {
            throw new TypeError('Expected a function');
        }
        
        if (wait < 0 || wait > 60000) {
            console.warn('Debounce wait time should be between 0-60000ms');
            wait = Math.max(0, Math.min(60000, wait));
        }

        const funcId = func.toString().slice(0, 50); // 関数の識別子
        
        return function executedFunction(...args) {
            const context = this;
            
            const later = () => {
                Utils._timers.delete(funcId);
                if (!immediate) {
                    func.apply(context, args);
                }
                Utils._performanceMetrics.debounceCount++;
            };
            
            const callNow = immediate && !Utils._timers.has(funcId);
            
            // 既存のタイマーをクリア
            if (Utils._timers.has(funcId)) {
                clearTimeout(Utils._timers.get(funcId));
            }
            
            // 新しいタイマーを設定
            const timerId = setTimeout(later, wait);
            Utils._timers.set(funcId, timerId);
            
            if (callNow) {
                func.apply(context, args);
                Utils._performanceMetrics.debounceCount++;
            }
        };
    }
    
    /**
     * スロットル処理
     * 指定された時間間隔で関数の実行を制限
     * 
     * @param {Function} func 実行する関数
     * @param {number} limit 実行間隔（ミリ秒）
     * @param {Object} options オプション設定
     * @returns {Function} スロットルされた関数
     */
    static throttle = (func, limit, options = {}) => {
        if (typeof func !== 'function') {
            throw new TypeError('Expected a function');
        }
        
        if (limit < 0 || limit > 60000) {
            console.warn('Throttle limit should be between 0-60000ms');
            limit = Math.max(0, Math.min(60000, limit));
        }

        const {
            leading = true,
            trailing = true
        } = options;
        
        const funcId = func.toString().slice(0, 50);
        
        return function throttledFunction(...args) {
            const context = this;
            const now = Date.now();
            
            if (!Utils._throttleCache.has(funcId)) {
                Utils._throttleCache.set(funcId, {
                    lastCall: 0,
                    timerId: null,
                    lastArgs: null,
                    lastContext: null
                });
            }
            
            const cache = Utils._throttleCache.get(funcId);
            const timeSinceLastCall = now - cache.lastCall;
            
            const executeFunction = () => {
                cache.lastCall = now;
                func.apply(context, args);
                Utils._performanceMetrics.throttleCount++;
            };
            
            if (timeSinceLastCall >= limit) {
                if (leading) {
                    executeFunction();
                } else if (trailing && !cache.timerId) {
                    cache.timerId = setTimeout(() => {
                        executeFunction();
                        cache.timerId = null;
                    }, limit - timeSinceLastCall);
                }
            } else if (trailing) {
                cache.lastArgs = args;
                cache.lastContext = context;
                
                if (!cache.timerId) {
                    cache.timerId = setTimeout(() => {
                        if (cache.lastArgs) {
                            func.apply(cache.lastContext, cache.lastArgs);
                            Utils._performanceMetrics.throttleCount++;
                        }
                        cache.timerId = null;
                        cache.lastArgs = null;
                        cache.lastContext = null;
                    }, limit - timeSinceLastCall);
                }
            }
        };
    }
    
    /**
     * メモリ使用量の監視
     * 
     * @returns {Object} メモリ使用状況
     */
    static getMemoryUsage() {
        const usage = {
            activeTimers: Utils._timers.size,
            throttleCacheSize: Utils._throttleCache.size,
            performanceMetrics: { ...Utils._performanceMetrics }
        };
        
        // Performance Memory API が利用可能な場合
        if (performance && performance.memory) {
            usage.jsHeapSizeLimit = performance.memory.jsHeapSizeLimit;
            usage.totalJSHeapSize = performance.memory.totalJSHeapSize;
            usage.usedJSHeapSize = performance.memory.usedJSHeapSize;
            usage.memoryUsagePercent = (usage.usedJSHeapSize / usage.jsHeapSizeLimit * 100).toFixed(2);
        }
        
        return usage;
    }
    
    /**
     * メモリクリーンアップ
     * 不要なタイマーとキャッシュをクリア
     */
    static cleanupMemory() {
        let cleanupCount = 0;
        
        // 期限切れのタイマーをクリア
        Utils._timers.forEach((timerId, funcId) => {
            clearTimeout(timerId);
            cleanupCount++;
        });
        Utils._timers.clear();
        
        // 古いスロットルキャッシュをクリア（5分以上前のもの）
        const now = Date.now();
        const expireTime = 5 * 60 * 1000; // 5分
        
        Utils._throttleCache.forEach((cache, funcId) => {
            if (now - cache.lastCall > expireTime) {
                if (cache.timerId) {
                    clearTimeout(cache.timerId);
                }
                Utils._throttleCache.delete(funcId);
                cleanupCount++;
            }
        });
        
        Utils._performanceMetrics.memoryOptimizations += cleanupCount;
        
        console.log(`Utils: メモリクリーンアップ完了 (${cleanupCount} 項目をクリア)`);
        return cleanupCount;
    }
    
    /**
     * リサイズイベント用の最適化されたリスナー
     * 
     * @param {Function} callback コールバック関数
     * @param {number} delay デバウンス遅延時間
     * @returns {Function} クリーンアップ関数
     */
    static onResize(callback, delay = 250) {
        const debouncedCallback = Utils.debounce(callback, delay);
        window.addEventListener('resize', debouncedCallback);
        
        return () => {
            window.removeEventListener('resize', debouncedCallback);
        };
    }
    
    /**
     * スクロールイベント用の最適化されたリスナー
     * 
     * @param {Function} callback コールバック関数
     * @param {number} limit スロットル制限時間
     * @returns {Function} クリーンアップ関数
     */
    static onScroll(callback, limit = 100) {
        const throttledCallback = Utils.throttle(callback, limit);
        window.addEventListener('scroll', throttledCallback, { passive: true });
        
        return () => {
            window.removeEventListener('scroll', throttledCallback);
        };
    }
    
    /**
     * パフォーマンス測定
     * 
     * @param {string} name 測定名
     * @param {Function} func 測定する関数
     * @returns {Promise} 測定結果
     */
    static async measurePerformance(name, func) {
        const startTime = performance.now();
        const startMemory = Utils.getMemoryUsage();
        
        try {
            const result = await func();
            const endTime = performance.now();
            const endMemory = Utils.getMemoryUsage();
            
            const metrics = {
                name,
                executionTime: endTime - startTime,
                memoryBefore: startMemory,
                memoryAfter: endMemory,
                result
            };
            
            console.log(`Performance [${name}]:`, {
                time: `${metrics.executionTime.toFixed(2)}ms`,
                memory: startMemory.usedJSHeapSize ? 
                    `${((endMemory.usedJSHeapSize - startMemory.usedJSHeapSize) / 1024).toFixed(2)}KB` : 
                    'N/A'
            });
            
            return metrics;
        } catch (error) {
            console.error(`Performance measurement failed for ${name}:`, error);
            throw error;
        }
    }
    
    /**
     * 安全なsetTimeout（メモリリーク防止）
     * 
     * @param {Function} func 実行する関数
     * @param {number} delay 遅延時間
     * @returns {Object} タイマー制御オブジェクト
     */
    static safeTimeout(func, delay) {
        const timerId = setTimeout(() => {
            func();
            Utils._timers.delete(timerId);
        }, delay);
        
        Utils._timers.set(timerId, timerId);
        
        return {
            id: timerId,
            clear: () => {
                clearTimeout(timerId);
                Utils._timers.delete(timerId);
            }
        };
    }
    
    /**
     * 安全なsetInterval（メモリリーク防止）
     * 
     * @param {Function} func 実行する関数
     * @param {number} interval 実行間隔
     * @returns {Object} インターバル制御オブジェクト
     */
    static safeInterval(func, interval) {
        const intervalId = setInterval(func, interval);
        Utils._timers.set(intervalId, intervalId);
        
        return {
            id: intervalId,
            clear: () => {
                clearInterval(intervalId);
                Utils._timers.delete(intervalId);
            }
        };
    }
    
    /**
     * ページ離脱時の自動クリーンアップ設定
     */
    static setupAutoCleanup() {
        const cleanup = () => {
            Utils.cleanupMemory();
        };
        
        // 複数のイベントでクリーンアップを実行
        window.addEventListener('beforeunload', cleanup);
        window.addEventListener('pagehide', cleanup);
        
        // 定期的なクリーンアップ（5分ごと）
        const cleanupInterval = Utils.safeInterval(() => {
            const usage = Utils.getMemoryUsage();
            if (usage.activeTimers > 50 || usage.throttleCacheSize > 100) {
                console.log('Utils: 定期メモリクリーンアップを実行');
                Utils.cleanupMemory();
            }
        }, 5 * 60 * 1000);
        
        // ページフォーカス時のヘルスチェック
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                const usage = Utils.getMemoryUsage();
                console.log('Utils: メモリ使用状況', usage);
            }
        });
        
        return {
            cleanup,
            cleanupInterval
        };
    }
    
    /**
     * パフォーマンス統計の取得
     * 
     * @returns {Object} 統計情報
     */
    static getPerformanceStats() {
        return {
            ...Utils._performanceMetrics,
            memoryUsage: Utils.getMemoryUsage(),
            timestamp: Date.now()
        };
    }
    
    /**
     * ユーティリティの初期化
     */
    static init() {
        console.log('Utils: 初期化開始');
        
        // 自動クリーンアップの設定
        Utils.setupAutoCleanup();
        
        // 初期メモリ使用量の記録
        const initialUsage = Utils.getMemoryUsage();
        console.log('Utils: 初期メモリ使用量', initialUsage);
        
        console.log('Utils: 初期化完了');
    }
}

// 自動初期化
if (typeof window !== 'undefined') {
    // DOM読み込み完了時に初期化
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', Utils.init);
    } else {
        Utils.init();
    }
}

// モジュールエクスポート（モジュールシステム対応）
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Utils;
}

// グローバル変数として公開
if (typeof window !== 'undefined') {
    window.Utils = Utils;
}
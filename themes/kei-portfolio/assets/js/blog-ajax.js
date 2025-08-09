/**
 * ブログ無限スクロール & Ajax機能
 * 
 * 機能一覧:
 * - 無限スクロール実装
 * - Ajax記事読み込み
 * - パフォーマンス最適化
 * - エラーハンドリング
 * - プログレッシブエンハンスメント
 * - インターセクション・オブザーバー対応
 * 
 * セキュリティ対策：
 * - DOMParserを使用した安全なHTML処理
 * - Ajax取得データの検証
 * - XSS攻撃対策
 * 
 * メモリリーク対策：
 * - イベントハンドラーの管理
 * - タイマーの適切なクリーンアップ
 * - Observerの適切な停止
 * 
 * @version 1.1.0 (Security Enhanced)
 * @since kei-portfolio 2.0
 */

(function($) {
    'use strict';

    /**
     * Ajax無限スクロール管理クラス
     */
    class BlogAjaxManager {
        constructor() {
            this.currentPage = parseInt(blogAjax.current_page) || 1;
            this.maxPages = parseInt(blogAjax.max_pages) || 1;
            this.isLoading = false;
            this.hasMorePosts = this.currentPage < this.maxPages;
            this.loadedPosts = new Set();
            this.retryCount = 0;
            
            // カスタマイズ可能なリトライ設定
            this.retryConfig = blogAjax.retryConfig || {
                maxRetries: 3,
                retryDelay: 1000,
                exponentialBackoff: true,
                timeoutMs: 15000
            };
            this.maxRetries = this.retryConfig.maxRetries;
            
            // メモリ管理用
            this.eventHandlers = new Map();
            this.timers = new Set();
            this.observers = new Set();
            
            // パフォーマンス監視
            this.performance = {
                requestCount: 0,
                totalLoadTime: 0,
                failedRequests: 0,
                lastRequestTime: 0
            };
            
            // インターセクション・オブザーバー
            this.observer = null;
            
            this.init();
        }

        /**
         * 初期化処理
         */
        init() {
            if (!this.isAjaxSupported()) {
                console.log('Ajax not supported, falling back to traditional pagination');
                return;
            }
            
            this.setupInfiniteScroll();
            this.bindEvents();
            this.initIntersectionObserver();
            this.createLoadingIndicator();
            this.createErrorIndicator();
            
            console.log('BlogAjaxManager initialized', {
                currentPage: this.currentPage,
                maxPages: this.maxPages,
                hasMorePosts: this.hasMorePosts
            });
        }

        /**
         * Ajax対応チェック
         * @returns {boolean} Ajax対応状況
         */
        isAjaxSupported() {
            // blogAjax オブジェクトの存在確認（統一されたオブジェクト）
            if (typeof blogAjax === 'undefined') {
                console.error('BlogAjax: blogAjax オブジェクトが定義されていません');
                return false;
            }
            
            // 必須プロパティの存在確認
            if (!blogAjax.ajaxUrl) {
                console.error('BlogAjax: ajaxUrl が定義されていません');
                return false;
            }
            
            if (!blogAjax.loadMoreNonce) {
                console.error('BlogAjax: loadMoreNonce が定義されていません');
                return false;
            }
            
            // jQuery Ajax の存在確認
            if (typeof $.ajax !== 'function') {
                console.error('BlogAjax: jQuery Ajax が利用できません');
                return false;
            }
            
            return true;
        }

        /**
         * イベントバインド（メモリ管理強化）
         */
        bindEvents() {
            // 手動読み込みボタン
            const loadMoreHandler = this.loadMorePosts.bind(this);
            $(document).on('click', '.load-more-posts', loadMoreHandler);
            this.eventHandlers.set('load-more-posts', { element: $(document), event: 'click', selector: '.load-more-posts', handler: loadMoreHandler });
            
            // 再試行ボタン
            const retryHandler = this.retryLoadPosts.bind(this);
            $(document).on('click', '.retry-load-posts', retryHandler);
            this.eventHandlers.set('retry-load-posts', { element: $(document), event: 'click', selector: '.retry-load-posts', handler: retryHandler });
            
            // スクロール停止検知
            const scrollHandler = Utils.debounce(this.handleScroll.bind(this), 100);
            $(window).on('scroll', scrollHandler);
            this.eventHandlers.set('scroll', { element: $(window), event: 'scroll', handler: scrollHandler });
            
            // ページの可視性変更
            const visibilityHandler = this.handleVisibilityChange.bind(this);
            $(document).on('visibilitychange', visibilityHandler);
            this.eventHandlers.set('visibilitychange', { element: $(document), event: 'visibilitychange', handler: visibilityHandler });
            
            // カスタムイベント
            const nearBottomHandler = this.handleNearBottom.bind(this);
            const forceLoadHandler = this.forceLoadMore.bind(this);
            $(document).on('blog:near-bottom', nearBottomHandler);
            $(document).on('blog:force-load', forceLoadHandler);
            this.eventHandlers.set('blog:near-bottom', { element: $(document), event: 'blog:near-bottom', handler: nearBottomHandler });
            this.eventHandlers.set('blog:force-load', { element: $(document), event: 'blog:force-load', handler: forceLoadHandler });
        }

        /**
         * 無限スクロール設定
         */
        setupInfiniteScroll() {
            // 従来のページネーションを隠す
            $('.pagination, .wp-pagenavi').hide();
            
            // 無限スクロール用のトリガーエリアを作成
            this.createScrollTrigger();
        }

        /**
         * スクロールトリガー作成
         */
        createScrollTrigger() {
            if ($('.infinite-scroll-trigger').length === 0) {
                const $trigger = $(`
                    <div class="infinite-scroll-trigger" id="infinite-scroll-trigger">
                        <div class="scroll-indicator">
                            <span class="scroll-text">スクロールして続きを読む</span>
                        </div>
                    </div>
                `);
                
                $('.blog-posts-container').after($trigger);
            }
        }

        /**
         * Intersection Observer初期化
         */
        initIntersectionObserver() {
            if (!('IntersectionObserver' in window)) {
                console.log('IntersectionObserver not supported, using scroll event');
                return;
            }
            
            const options = {
                root: null,
                rootMargin: '200px 0px',
                threshold: 0.1
            };
            
            this.observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && this.hasMorePosts && !this.isLoading) {
                        this.loadMorePosts();
                    }
                });
            }, options);
            
            // Observer管理に追加
            this.observers.add(this.observer);
            
            // トリガー要素の監視開始
            const trigger = document.getElementById('infinite-scroll-trigger');
            if (trigger) {
                this.observer.observe(trigger);
            }
        }

        /**
         * スクロール処理
         */
        handleScroll() {
            // Intersection Observerが利用可能な場合はスキップ
            if (this.observer) {
                return;
            }
            
            const $window = $(window);
            const $document = $(document);
            const scrollTop = $window.scrollTop();
            const windowHeight = $window.height();
            const documentHeight = $document.height();
            
            // ページ下部から200pxの位置で読み込み開始
            if (scrollTop + windowHeight >= documentHeight - 200) {
                if (this.hasMorePosts && !this.isLoading) {
                    this.loadMorePosts();
                }
            }
        }

        /**
         * ページ下部接近処理
         */
        handleNearBottom() {
            if (this.hasMorePosts && !this.isLoading) {
                this.loadMorePosts();
            }
        }

        /**
         * ページ可視性変更処理
         */
        handleVisibilityChange() {
            if (document.hidden) {
                // ページが非表示になった場合、パフォーマンス統計を送信
                this.sendPerformanceStats();
            }
        }

        /**
         * 記事の追加読み込み
         */
        loadMorePosts() {
            if (this.isLoading || !this.hasMorePosts) {
                return Promise.resolve();
            }
            
            this.isLoading = true;
            this.performance.requestCount++;
            this.performance.lastRequestTime = Date.now();
            
            // ローディングインディケーター表示
            this.showLoadingIndicator(true);
            
            const requestData = {
                action: 'load_more_posts',
                page: this.currentPage + 1,
                nonce: blogAjax.loadMoreNonce,
                ...this.getFilterParameters()
            };
            
            return $.ajax({
                url: blogAjax.ajaxUrl,
                type: 'POST',
                data: requestData,
                timeout: this.retryConfig.timeoutMs,
                beforeSend: this.beforeSend.bind(this),
                success: this.onLoadSuccess.bind(this),
                error: this.onLoadError.bind(this),
                complete: this.onLoadComplete.bind(this)
            });
        }

        /**
         * リクエスト送信前処理
         * @param {XMLHttpRequest} jqXHR - jQueryXHR オブジェクト
         */
        beforeSend(jqXHR) {
            // Nonce検証（送信前の最終確認）
            if (!blogAjax.loadMoreNonce) {
                console.error('BlogAjax: Nonce が無効です');
                jqXHR.abort();
                return false;
            }
            
            // セキュリティヘッダーの設定
            jqXHR.setRequestHeader('X-WP-Nonce', blogAjax.loadMoreNonce);
            jqXHR.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            
            // CSRF対策のためのカスタムヘッダー
            jqXHR.setRequestHeader('X-KeiPortfolio-Request', 'blog-ajax');
            
            // タイムスタンプ記録
            jqXHR.requestStartTime = Date.now();
            
            console.log(`Loading page ${this.currentPage + 1} of ${this.maxPages}`);
        }

        /**
         * 読み込み成功時の処理
         * @param {Object} response - サーバーレスポンス
         * @param {string} textStatus - ステータステキスト
         * @param {XMLHttpRequest} jqXHR - jQueryXHR オブジェクト
         */
        onLoadSuccess(response, textStatus, jqXHR) {
            // レスポンスの整合性チェック
            if (!response || typeof response !== 'object') {
                console.error('BlogAjax: 無効なレスポンス形式です');
                this.onLoadError(jqXHR, 'error', 'Invalid response format');
                return;
            }
            
            if (!response.success) {
                console.error('BlogAjax: サーバーエラー', response);
                this.onLoadError(jqXHR, 'error', response.data || 'Unknown server error');
                return;
            }
            
            // セキュリティ確認：期待されるデータ構造かチェック
            if (!response.data || typeof response.data !== 'object') {
                console.error('BlogAjax: レスポンスデータが不正です');
                this.onLoadError(jqXHR, 'error', 'Invalid response data');
                return;
            }
            
            const data = response.data;
            
            // パフォーマンス記録
            const loadTime = Date.now() - jqXHR.requestStartTime;
            this.performance.totalLoadTime += loadTime;
            
            // 新しい投稿を追加
            this.appendNewPosts(data.html);
            
            // ページ状態の更新
            this.currentPage = data.current_page;
            this.maxPages = data.max_pages;
            this.hasMorePosts = this.currentPage < this.maxPages;
            
            // 重複チェック
            this.checkForDuplicates();
            
            // トリガーの更新
            this.updateScrollTrigger();
            
            // カスタムイベント発火
            $(document).trigger('blog:posts-loaded', [data]);
            
            // 統計更新
            this.updateLoadingStats(data.posts_count || 0, loadTime);
            
            // リトライカウントリセット
            this.retryCount = 0;
            
            console.log(`Successfully loaded ${data.posts_count} more posts`);
        }

        /**
         * 読み込みエラー時の処理
         * @param {XMLHttpRequest} jqXHR - jQueryXHR オブジェクト
         * @param {string} textStatus - エラーステータス
         * @param {string} errorThrown - エラーメッセージ
         */
        onLoadError(jqXHR, textStatus, errorThrown) {
            this.performance.failedRequests++;
            
            console.error('Ajax load error:', {
                status: jqXHR.status,
                statusText: textStatus,
                error: errorThrown,
                response: jqXHR.responseText
            });
            
            // エラーメッセージの決定（セキュリティを考慮）
            let errorMessage = '投稿の読み込みでエラーが発生しました。';
            
            if (jqXHR.status === 0) {
                errorMessage = 'ネットワーク接続を確認してください。';
            } else if (jqXHR.status === 403) {
                errorMessage = 'アクセスが拒否されました。ページを再読み込みしてください。';
            } else if (jqXHR.status === 404) {
                errorMessage = 'ページが見つかりません。';
            } else if (jqXHR.status === 500) {
                errorMessage = 'サーバーエラーが発生しました。';
            } else if (jqXHR.status === 429) {
                errorMessage = 'リクエスト数が上限に達しました。しばらくお待ちください。';
            } else if (textStatus === 'timeout') {
                errorMessage = 'リクエストがタイムアウトしました。';
            } else if (textStatus === 'abort') {
                errorMessage = 'リクエストが中断されました。';
            }
            
            this.showError(errorMessage);
            
            // 自動リトライの実装
            if (this.retryCount < this.maxRetries) {
                this.scheduleRetry();
            } else {
                this.showManualRetryOption();
            }
        }

        /**
         * 読み込み完了時の処理
         */
        onLoadComplete() {
            this.isLoading = false;
            this.showLoadingIndicator(false);
            
            // メモリ最適化
            this.optimizeMemoryUsage();
        }

        /**
         * 新しい投稿をDOMに追加（セキュリティ強化）
         * @param {string} html - 投稿HTML
         */
        appendNewPosts(html) {
            if (typeof html !== 'string') return;
            
            const $container = $('.blog-posts-container');
            if (!$container.length) return;
            
            // DOMParserを使用して安全にHTMLを解析
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newPosts = doc.querySelectorAll('.blog-post-item');
            
            const $newPostsArray = [];
            
            // 既存投稿との重複チェック
            newPosts.forEach(post => {
                const postId = post.getAttribute('data-post-id');
                if (postId && !this.loadedPosts.has(postId)) {
                    this.loadedPosts.add(postId);
                    const clonedPost = post.cloneNode(true);
                    $container[0].appendChild(clonedPost);
                    $newPostsArray.push($(clonedPost));
                }
            });
            
            if ($newPostsArray.length > 0) {
                // アニメーション効果
                this.animateNewPosts($($newPostsArray.map(p => p[0])));
                
                // レイジーローディング画像の処理
                this.initLazyLoading($($newPostsArray.map(p => p[0])));
            }
        }

        /**
         * 新投稿のアニメーション
         * @param {jQuery} $posts - 新しい投稿要素
         */
        animateNewPosts($posts) {
            $posts.css({
                opacity: 0,
                transform: 'translateY(20px)'
            });
            
            $posts.each((index, post) => {
                $(post).delay(index * 100).animate({
                    opacity: 1
                }, {
                    duration: 400,
                    step: function(now) {
                        $(this).css('transform', `translateY(${20 * (1 - now)}px)`);
                    },
                    complete: function() {
                        $(this).css('transform', '');
                    }
                });
            });
        }

        /**
         * レイジーローディング初期化
         * @param {jQuery} $posts - 投稿要素
         */
        initLazyLoading($posts) {
            const $images = $posts.find('img[data-src]');
            
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            img.src = img.dataset.src;
                            img.classList.remove('lazy');
                            img.classList.add('loaded');
                            imageObserver.unobserve(img);
                        }
                    });
                });
                
                $images.each((index, img) => {
                    imageObserver.observe(img);
                });
            } else {
                // フォールバック: 即座に画像を読み込み
                $images.each((index, img) => {
                    img.src = img.dataset.src;
                    $(img).removeClass('lazy').addClass('loaded');
                });
            }
        }

        /**
         * フィルターパラメータの取得
         * @returns {Object} フィルターパラメータ
         */
        getFilterParameters() {
            const params = {};
            
            // アクティブなカテゴリーフィルター
            const activeCategory = $('.category-filter-btn.active').data('category');
            if (activeCategory && activeCategory !== 'all') {
                params.category = activeCategory;
            }
            
            // アクティブな検索クエリ
            const searchQuery = $('.blog-search-input').val().trim();
            if (searchQuery) {
                params.search = searchQuery;
            }
            
            // その他のフィルター（タグ、日付など）
            const activeTag = $('.tag-filter-btn.active').data('tag');
            if (activeTag) {
                params.tag = activeTag;
            }
            
            return params;
        }

        /**
         * 重複投稿チェック
         */
        checkForDuplicates() {
            const $posts = $('.blog-post-item');
            const seenIds = new Set();
            
            $posts.each((index, post) => {
                const postId = $(post).data('post-id');
                if (seenIds.has(postId)) {
                    $(post).remove();
                    console.warn(`Duplicate post removed: ${postId}`);
                } else {
                    seenIds.add(postId);
                }
            });
        }

        /**
         * スクロールトリガーの更新
         */
        updateScrollTrigger() {
            const $trigger = $('.infinite-scroll-trigger');
            
            if (this.hasMorePosts) {
                $trigger.show().find('.scroll-text').text(
                    `${this.currentPage}/${this.maxPages} ページ表示中 - スクロールして続きを読む`
                );
            } else {
                $trigger.hide();
                this.showEndMessage();
                
                // Intersection Observerの停止
                if (this.observer) {
                    this.observer.disconnect();
                }
            }
        }

        /**
         * 終了メッセージの表示
         */
        showEndMessage() {
            if ($('.infinite-scroll-end').length === 0) {
                const $endMessage = $(`
                    <div class="infinite-scroll-end">
                        <div class="end-message">
                            <span class="end-icon">✨</span>
                            <p>すべての記事を表示しました</p>
                            <button class="btn-back-to-top" onclick="window.scrollTo({top: 0, behavior: 'smooth'})">
                                ページトップへ戻る
                            </button>
                        </div>
                    </div>
                `);
                
                $('.blog-posts-container').after($endMessage);
            }
        }

        /**
         * ローディングインディケーター作成
         */
        createLoadingIndicator() {
            if ($('.infinite-scroll-loading').length === 0) {
                const $loading = $(`
                    <div class="infinite-scroll-loading" style="display: none;">
                        <div class="loading-content">
                            <div class="loading-spinner">
                                <div class="spinner-ring"></div>
                            </div>
                            <p class="loading-text">新しい記事を読み込んでいます...</p>
                            <div class="loading-progress">
                                <div class="progress-bar"></div>
                            </div>
                        </div>
                    </div>
                `);
                
                $('.infinite-scroll-trigger').before($loading);
            }
        }

        /**
         * エラーインディケーター作成
         */
        createErrorIndicator() {
            if ($('.infinite-scroll-error').length === 0) {
                const $error = $(`
                    <div class="infinite-scroll-error" style="display: none;">
                        <div class="error-content">
                            <div class="error-icon">⚠️</div>
                            <p class="error-message"></p>
                            <div class="error-actions">
                                <button class="btn-retry retry-load-posts">再試行</button>
                                <button class="btn-manual-load load-more-posts">手動読み込み</button>
                            </div>
                        </div>
                    </div>
                `);
                
                $('.infinite-scroll-trigger').before($error);
            }
        }

        /**
         * ローディングインディケーター表示制御
         * @param {boolean} show - 表示/非表示
         */
        showLoadingIndicator(show) {
            const $loading = $('.infinite-scroll-loading');
            
            if (show) {
                $loading.show();
                this.updateLoadingProgress();
            } else {
                $loading.hide();
            }
        }

        /**
         * ローディング進行状況更新
         */
        updateLoadingProgress() {
            const $progressBar = $('.loading-progress .progress-bar');
            const progress = (this.currentPage / this.maxPages) * 100;
            
            $progressBar.css('width', `${progress}%`);
        }

        /**
         * エラー表示
         * @param {string} message - エラーメッセージ
         */
        showError(message) {
            const $error = $('.infinite-scroll-error');
            $error.find('.error-message').text(message);
            $error.show();
        }

        /**
         * エラー非表示
         */
        hideError() {
            $('.infinite-scroll-error').hide();
        }

        /**
         * 自動リトライのスケジュール（カスタマイズ可能なタイマー管理強化）
         */
        scheduleRetry() {
            this.retryCount++;
            
            // 設定可能な遅延計算
            let delay = this.retryConfig.retryDelay;
            if (this.retryConfig.exponentialBackoff) {
                delay = Math.pow(2, this.retryCount) * this.retryConfig.retryDelay;
            }
            
            // 最大遅延時間の制限（30秒）
            delay = Math.min(delay, 30000);
            
            const retryTimer = Utils.safeTimeout(() => {
                console.log(`Retrying load (attempt ${this.retryCount}/${this.maxRetries}) after ${delay}ms`);
                this.loadMorePosts();
            }, delay);
            
            this.timers.add(retryTimer.id);
        }

        /**
         * 手動リトライオプション表示
         */
        showManualRetryOption() {
            this.showError(
                '記事の読み込みに失敗しました。手動で再試行するか、ページを更新してください。'
            );
        }

        /**
         * 強制読み込み
         */
        forceLoadMore() {
            this.retryCount = 0;
            this.hideError();
            this.loadMorePosts();
        }

        /**
         * リトライ処理
         */
        retryLoadPosts() {
            this.retryCount = 0;
            this.hideError();
            this.loadMorePosts();
        }

        /**
         * メモリ使用量の最適化
         */
        optimizeMemoryUsage() {
            // 画面外の画像を遅延読み込み状態に戻す（オプション）
            const $posts = $('.blog-post-item');
            const visibleThreshold = 50; // 表示する投稿数の閾値
            
            if ($posts.length > visibleThreshold) {
                $posts.slice(0, $posts.length - visibleThreshold).each(function() {
                    const $images = $(this).find('img');
                    $images.each(function() {
                        if (this.src && this.dataset.src !== this.src) {
                            this.dataset.src = this.src;
                            this.src = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
                            $(this).addClass('lazy');
                        }
                    });
                });
            }
        }

        /**
         * 読み込み統計の更新
         * @param {number} postsCount - 読み込んだ投稿数
         * @param {number} loadTime - 読み込み時間
         */
        updateLoadingStats(postsCount, loadTime) {
            const stats = {
                postsLoaded: postsCount,
                loadTime: loadTime,
                timestamp: Date.now(),
                page: this.currentPage
            };
            
            // ローカルストレージに統計保存
            const allStats = JSON.parse(localStorage.getItem('blog_loading_stats') || '[]');
            allStats.push(stats);
            
            // 最新50件のみ保持
            if (allStats.length > 50) {
                allStats.splice(0, allStats.length - 50);
            }
            
            localStorage.setItem('blog_loading_stats', JSON.stringify(allStats));
        }

        /**
         * パフォーマンス統計送信
         */
        sendPerformanceStats() {
            if (this.performance.requestCount === 0) return;
            
            const stats = {
                action: 'blog_ajax_performance',
                data: {
                    ...this.performance,
                    avgLoadTime: this.performance.totalLoadTime / this.performance.requestCount,
                    successRate: (this.performance.requestCount - this.performance.failedRequests) / this.performance.requestCount
                },
                nonce: blogAjax.nonce
            };
            
            // Navigator.sendBeacon が利用可能な場合はそれを使用
            if (navigator.sendBeacon) {
                const formData = new FormData();
                Object.keys(stats).forEach(key => {
                    formData.append(key, typeof stats[key] === 'object' ? JSON.stringify(stats[key]) : stats[key]);
                });
                
                navigator.sendBeacon(blogAjax.ajaxUrl, formData);
            } else {
                // フォールバック
                $.ajax({
                    url: blogAjax.ajaxUrl,
                    type: 'POST',
                    data: stats,
                    async: false
                });
            }
        }

        // デバウンス関数は Utils クラスに移動済み
        // 後方互換性のためのプロキシメソッド
        debounce(func, wait) {
            console.warn('BlogAjaxManager.debounce is deprecated. Use Utils.debounce instead.');
            return Utils.debounce(func, wait);
        }

        /**
         * 状態のリセット
         */
        reset() {
            this.currentPage = 1;
            this.hasMorePosts = this.currentPage < this.maxPages;
            this.isLoading = false;
            this.loadedPosts.clear();
            this.retryCount = 0;
            
            this.hideError();
            this.showLoadingIndicator(false);
            $('.infinite-scroll-end').remove();
            
            if (this.observer) {
                this.observer.disconnect();
                this.initIntersectionObserver();
            }
        }

        /**
         * 破棄処理（完全クリーンアップ）
         */
        destroy() {
            // 全てのObserverを停止
            this.observers.forEach(observer => {
                observer.disconnect();
            });
            this.observers.clear();
            
            if (this.observer) {
                this.observer.disconnect();
                this.observer = null;
            }
            
            // 全てのタイマーを安全にクリア
            this.timers.forEach(timerId => {
                clearTimeout(timerId);
            });
            this.timers.clear();
            
            // Utils クラスのクリーンアップも実行
            if (typeof Utils !== 'undefined') {
                Utils.cleanupMemory();
            }
            
            // 全てのイベントハンドラーを削除
            this.eventHandlers.forEach((config, key) => {
                if (config.selector) {
                    config.element.off(config.event, config.selector, config.handler);
                } else {
                    config.element.off(config.event, config.handler);
                }
            });
            this.eventHandlers.clear();
            
            // パフォーマンス統計送信
            this.sendPerformanceStats();
            
            // プロパティクリア
            this.loadedPosts.clear();
            this.isLoading = false;
        }
    }

    /**
     * DOM読み込み完了時の初期化
     */
    $(document).ready(function() {
        // BlogAjaxManagerのグローバル化
        window.blogAjaxManager = new BlogAjaxManager();
        
        // カスタムイベントリスナー
        $(document).on('blog:refresh', function() {
            window.blogAjaxManager.reset();
        });
        
        $(document).on('blog:posts-loaded', function(e, data) {
            console.log('Posts loaded event:', data);
            
            // 他のコンポーネントに通知
            if (window.blogManager) {
                window.blogManager.adjustCardHeights();
            }
        });
        
        // ページ離脱時の処理
        $(window).on('beforeunload', function() {
            if (window.blogAjaxManager) {
                window.blogAjaxManager.destroy();
            }
        });
        
        console.log('Blog Ajax JavaScript loaded successfully');
    });

})(jQuery);
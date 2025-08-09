/**
 * ブログ機能 メインJavaScript
 * 
 * インタラクション、アニメーション、ユーザビリティ向上機能
 * 
 * セキュリティ対策：
 * - HTMLの直接挿入を回避、DOMメソッドを使用
 * - textContentによる自動エスケープ
 * - URL検証とサニタイゼーション
 * 
 * メモリリーク対策：
 * - イベントハンドラーの適切な管理と削除
 * - タイマーとObserverのクリーンアップ
 * - ページ離脱時の完全クリーンアップ
 * 
 * @package Kei_Portfolio
 * @since 1.0.0
 * @version 1.1.0 (Security Enhanced)
 */

(function($) {
    'use strict';

    /**
     * ブログ機能の初期化
     */
    class BlogFunctions {
        constructor() {
            // インスタンスプロパティでイベントハンドラーを管理
            this.eventHandlers = new Map();
            this.observers = new Set();
            this.timers = new Set();
            this.searchTimeout = null;
            this.loading = false;
            this.page = 1;
            
            this.init();
        }

        init() {
            // DOM読み込み完了後に実行
            $(document).ready(() => {
                this.initViewToggle();
                this.initSmoothScrolling();
                this.initImageLazyLoading();
                this.initSearchEnhancement();
                this.initInfiniteScroll();
                this.initSocialShare();
                this.initTocGeneration();
                this.initReadingProgress();
                this.initAccessibility();
                this.initPerformanceOptimizations();
            });
        }

        /**
         * ビュー切り替え機能（グリッド/リスト）
         */
        initViewToggle() {
            const viewToggles = document.querySelectorAll('.view-toggle');
            const postsGrid = document.querySelector('.posts-grid');
            
            if (!viewToggles.length || !postsGrid) return;

            viewToggles.forEach(toggle => {
                toggle.addEventListener('click', (e) => {
                    e.preventDefault();
                    
                    const viewMode = toggle.dataset.view;
                    
                    // ボタンの状態を更新
                    viewToggles.forEach(btn => {
                        btn.classList.remove('bg-white', 'text-blue-600', 'shadow-sm');
                        btn.classList.add('text-gray-600');
                        btn.setAttribute('aria-pressed', 'false');
                    });
                    
                    toggle.classList.add('bg-white', 'text-blue-600', 'shadow-sm');
                    toggle.classList.remove('text-gray-600');
                    toggle.setAttribute('aria-pressed', 'true');
                    
                    // グリッドレイアウトを更新
                    this.updateGridLayout(postsGrid, viewMode);
                    
                    // 設定を保存
                    localStorage.setItem('blogViewMode', viewMode);
                });
            });

            // 保存されたビューモードを復元
            this.restoreViewMode(viewToggles, postsGrid);
        }

        /**
         * グリッドレイアウトを更新
         */
        updateGridLayout(grid, mode) {
            grid.classList.remove('grid-cols-1', 'md:grid-cols-2', 'lg:grid-cols-3', 'gap-6', 'gap-4');
            
            if (mode === 'list') {
                grid.classList.add('grid-cols-1', 'gap-4');
            } else {
                grid.classList.add('grid-cols-1', 'md:grid-cols-2', 'lg:grid-cols-3', 'gap-6');
            }
            
            // アニメーション効果
            grid.style.opacity = '0.7';
            setTimeout(() => {
                grid.style.opacity = '1';
            }, 150);
        }

        /**
         * 保存されたビューモードを復元
         */
        restoreViewMode(toggles, grid) {
            const savedMode = localStorage.getItem('blogViewMode');
            if (savedMode) {
                const targetToggle = document.querySelector(`[data-view="${savedMode}"]`);
                if (targetToggle) {
                    targetToggle.click();
                }
            }
        }

        /**
         * スムーズスクロール
         */
        initSmoothScrolling() {
            // アンカーリンクのスムーズスクロール
            $('a[href^="#"]:not([href="#"])').on('click', function(e) {
                e.preventDefault();
                
                const target = $($(this).attr('href'));
                if (target.length) {
                    $('html, body').animate({
                        scrollTop: target.offset().top - 100
                    }, 600, 'easeInOutQuart');
                }
            });

            // 「トップに戻る」ボタン
            this.addBackToTopButton();
        }

        /**
         * 「トップに戻る」ボタンを追加（イベントハンドラー管理強化）
         */
        addBackToTopButton() {
            if ($('#back-to-top').length) return;

            const backToTop = $('<button>', {
                id: 'back-to-top',
                class: 'fixed bottom-8 right-8 bg-blue-600 text-white p-3 rounded-full shadow-lg hover:bg-blue-700 transition-all duration-300 z-50 opacity-0 pointer-events-none',
                'aria-label': 'ページトップに戻る',
                html: '<i class="ri-arrow-up-line text-xl"></i>'
            });

            $('body').append(backToTop);

            // スクロール位置に応じて表示/非表示
            const scrollHandler = () => {
                if ($(window).scrollTop() > 300) {
                    backToTop.removeClass('opacity-0 pointer-events-none');
                } else {
                    backToTop.addClass('opacity-0 pointer-events-none');
                }
            };
            
            $(window).on('scroll', scrollHandler);
            this.eventHandlers.set(window, { event: 'scroll', handler: scrollHandler, element: $(window) });

            // クリックでトップに戻る
            const clickHandler = () => {
                $('html, body').animate({scrollTop: 0}, 600);
            };
            
            backToTop.on('click', clickHandler);
            this.eventHandlers.set(backToTop[0], { event: 'click', handler: clickHandler, element: backToTop });
        }

        /**
         * 画像の遅延読み込み強化
         */
        initImageLazyLoading() {
            // Intersection Observer による遅延読み込み
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            
                            // WebP対応チェック
                            this.loadOptimalImage(img);
                            
                            imageObserver.unobserve(img);
                        }
                    });
                });

                document.querySelectorAll('img[loading="lazy"]').forEach(img => {
                    imageObserver.observe(img);
                });
            }
        }

        /**
         * 最適な画像形式を読み込み
         */
        loadOptimalImage(img) {
            const originalSrc = img.src;
            
            // WebP対応チェック
            if (this.supportsWebP()) {
                const webpSrc = originalSrc.replace(/\.(jpg|jpeg|png)$/i, '.webp');
                
                // WebP版が存在するかチェック
                const testImg = new Image();
                testImg.onload = () => {
                    img.src = webpSrc;
                };
                testImg.onerror = () => {
                    // WebPが存在しない場合は元の画像を使用
                };
                testImg.src = webpSrc;
            }
        }

        /**
         * WebP対応チェック
         */
        supportsWebP() {
            const canvas = document.createElement('canvas');
            canvas.width = 1;
            canvas.height = 1;
            return canvas.toDataURL('image/webp').indexOf('webp') !== -1;
        }

        /**
         * 検索機能の強化（メモリリーク対策）
         */
        initSearchEnhancement() {
            const searchForms = document.querySelectorAll('.search-form');
            
            searchForms.forEach(form => {
                const input = form.querySelector('.search-field');
                if (!input) return;

                // リアルタイム検索の実装
                const inputHandler = (e) => {
                    clearTimeout(this.searchTimeout);
                    this.searchTimeout = setTimeout(() => {
                        this.performInstantSearch(e.target.value);
                    }, 500);
                    this.timers.add(this.searchTimeout);
                };
                
                input.addEventListener('input', inputHandler);
                this.eventHandlers.set(input, { event: 'input', handler: inputHandler });

                // 検索履歴の保存
                const submitHandler = (e) => {
                    this.saveSearchHistory(input.value);
                };
                
                form.addEventListener('submit', submitHandler);
                this.eventHandlers.set(form, { event: 'submit', handler: submitHandler });
            });
        }

        /**
         * インスタント検索（AJAX）（セキュリティ強化）
         */
        performInstantSearch(query) {
            if (query.length < 3) return;

            // 検索結果を表示するコンテナ
            let resultsContainer = document.querySelector('#instant-search-results');
            if (!resultsContainer) {
                resultsContainer = document.createElement('div');
                resultsContainer.id = 'instant-search-results';
                resultsContainer.className = 'absolute top-full left-0 right-0 bg-white shadow-lg border rounded-lg mt-1 z-50 max-h-96 overflow-y-auto';
                const searchForm = document.querySelector('.search-form');
                if (searchForm) {
                    searchForm.appendChild(resultsContainer);
                }
            }

            // Nonce検証とAjaxパラメータの確認（統一化されたオブジェクトを使用）
            const instantSearchNonce = window.keiPortfolioAjax?.nonces?.instantSearch || window.keiPortfolioAjax?.instantSearchNonce;
            if (!window.keiPortfolioAjax || !instantSearchNonce) {
                this.showErrorMessage(resultsContainer, 'セキュリティトークンが無効です');
                return;
            }

            // AJAX検索実行
            $.ajax({
                url: window.keiPortfolioAjax.ajaxUrl || '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    action: 'blog_instant_search',
                    query: this.sanitizeSearchQuery(query),
                    nonce: instantSearchNonce
                },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', instantSearchNonce);
                },
                success: (response) => {
                    if (response.success && Array.isArray(response.data)) {
                        this.displaySearchResults(resultsContainer, response.data);
                    } else {
                        this.showErrorMessage(resultsContainer, '検索に失敗しました');
                    }
                },
                error: (xhr, status, error) => {
                    let errorMsg = '検索中にエラーが発生しました';
                    if (xhr.status === 403) {
                        errorMsg = 'アクセスが拒否されました。ページを再読み込みしてください。';
                    } else if (xhr.status === 0) {
                        errorMsg = 'ネットワーク接続を確認してください。';
                    }
                    this.showErrorMessage(resultsContainer, errorMsg);
                }
            });
        }
        
        /**
         * 検索クエリをサニタイズ
         */
        sanitizeSearchQuery(query) {
            if (typeof query !== 'string') return '';
            return query.trim().substring(0, 100); // 長さ制限
        }
        
        /**
         * エラーメッセージを安全に表示
         */
        showErrorMessage(container, message) {
            // 既存のコンテンツを安全にクリア
            while (container.firstChild) {
                container.removeChild(container.firstChild);
            }
            const errorDiv = document.createElement('div');
            errorDiv.className = 'p-4 text-red-500';
            errorDiv.textContent = message; // 自動エスケープ
            container.appendChild(errorDiv);
        }

        /**
         * 検索結果を表示（セキュリティ強化：DOMメソッドを使用）
         */
        displaySearchResults(container, results) {
            // 既存の内容を安全にクリア
            while (container.firstChild) {
                container.removeChild(container.firstChild);
            }
            
            if (!results.length) {
                const noResultsDiv = document.createElement('div');
                noResultsDiv.className = 'p-4 text-gray-500';
                noResultsDiv.textContent = '検索結果が見つかりませんでした';
                container.appendChild(noResultsDiv);
                return;
            }

            // DOMメソッドを使用して安全にHTMLを生成
            results.forEach(post => {
                const resultDiv = document.createElement('div');
                resultDiv.className = 'p-3 hover:bg-gray-50 border-b';
                
                const linkElement = document.createElement('a');
                linkElement.href = this.sanitizeUrl(post.url);
                linkElement.className = 'block';
                
                const titleElement = document.createElement('h4');
                titleElement.className = 'font-medium text-gray-900 mb-1';
                titleElement.textContent = post.title; // 自動エスケープ
                
                const excerptElement = document.createElement('p');
                excerptElement.className = 'text-sm text-gray-600 line-clamp-2';
                excerptElement.textContent = post.excerpt; // 自動エスケープ
                
                linkElement.appendChild(titleElement);
                linkElement.appendChild(excerptElement);
                resultDiv.appendChild(linkElement);
                container.appendChild(resultDiv);
            });
        }
        
        /**
         * URLをサニタイズ
         */
        sanitizeUrl(url) {
            if (!url || typeof url !== 'string') return '#';
            // 基本的なURL検証（JavaScriptスキーマなどを防ぐ）
            if (url.match(/^(https?:\/\/|\/)/) && !url.match(/^javascript:/i)) {
                return url;
            }
            return '#';
        }

        /**
         * 検索履歴の保存
         */
        saveSearchHistory(query) {
            if (!query.trim()) return;

            const history = JSON.parse(localStorage.getItem('searchHistory') || '[]');
            history.unshift(query);
            
            // 重複削除と10件制限
            const uniqueHistory = [...new Set(history)].slice(0, 10);
            localStorage.setItem('searchHistory', JSON.stringify(uniqueHistory));
        }

        /**
         * 無限スクロール
         */
        initInfiniteScroll() {
            if (!document.querySelector('.blog-pagination')) return;

            const observer = new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting && !this.loading) {
                    this.loadMorePosts();
                }
            });

            const sentinel = document.createElement('div');
            sentinel.className = 'infinite-scroll-sentinel';
            const blogContainer = document.querySelector('.blog-container');
            if (blogContainer) {
                blogContainer.appendChild(sentinel);
                observer.observe(sentinel);
                this.observers.add(observer);
            }
        }

        /**
         * 追加の投稿を読み込み（セキュリティ強化）
         */
        loadMorePosts() {
            if (this.loading) return;
            
            this.loading = true;
            this.page++;

            // Nonce検証（統一化されたオブジェクトを使用）
            const loadMoreNonce = window.keiPortfolioAjax?.nonces?.loadMore || window.keiPortfolioAjax?.loadMoreNonce;
            if (!window.keiPortfolioAjax || !loadMoreNonce) {
                console.error('Blog Ajax: セキュリティトークンが無効です');
                this.loading = false;
                return;
            }

            $.ajax({
                url: window.keiPortfolioAjax.ajaxUrl || '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    action: 'load_more_posts',
                    page: this.page,
                    nonce: loadMoreNonce
                },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', loadMoreNonce);
                },
                success: (response) => {
                    if (response.success && response.data) {
                        // DOMParserを使用して安全にHTMLを処理
                        this.appendPostsSafely(response.data);
                        this.loading = false;
                    } else {
                        console.error('Blog Ajax: 投稿の読み込みに失敗しました', response);
                        $('.infinite-scroll-sentinel').hide();
                        this.loading = false;
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Blog Ajax Error:', {
                        status: xhr.status,
                        statusText: status,
                        error: error
                    });
                    this.loading = false;
                }
            });
        }
        
        /**
         * 投稿を安全に追加
         */
        appendPostsSafely(htmlString) {
            if (typeof htmlString !== 'string') return;
            
            const parser = new DOMParser();
            const doc = parser.parseFromString(htmlString, 'text/html');
            const newPosts = doc.querySelectorAll('.blog-post-item, .post-card');
            
            const postsGrid = document.querySelector('.posts-grid');
            if (postsGrid && newPosts.length > 0) {
                newPosts.forEach(post => {
                    if (post.nodeType === Node.ELEMENT_NODE) {
                        postsGrid.appendChild(post.cloneNode(true));
                    }
                });
            }
        }

        /**
         * ソーシャルシェア機能の強化
         */
        initSocialShare() {
            // シェア数の取得と表示
            this.updateShareCounts();

            // シェアボタンのクリック追跡
            $('.share-button').on('click', (e) => {
                const shareType = $(e.currentTarget).data('share');
                this.trackShare(shareType);
            });
        }

        /**
         * シェア数の更新
         */
        updateShareCounts() {
            if (!$('#share-counts').length) return;

            const postUrl = window.location.href;
            
            // Nonce検証（統一化されたオブジェクトを使用）
            const shareNonce = window.keiPortfolioAjax?.nonces?.share || window.keiPortfolioAjax?.shareNonce;
            if (!window.keiPortfolioAjax || !shareNonce) {
                console.error('Blog Ajax: セキュリティトークンが無効です');
                return;
            }

            $.ajax({
                url: window.keiPortfolioAjax.ajaxUrl || '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    action: 'get_share_counts',
                    url: postUrl,
                    nonce: shareNonce
                },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', shareNonce);
                },
                success: (response) => {
                    if (response.success) {
                        $('#total-shares').text(response.data.total);
                        $('#share-counts').show();
                    } else {
                        console.error('Blog Ajax: シェア数の取得に失敗しました', response);
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Blog Ajax: シェア数取得エラー', {
                        status: xhr.status,
                        error: error
                    });
                }
            });
        }

        /**
         * シェアの追跡
         */
        trackShare(type) {
            // Google Analytics等への送信
            if (typeof gtag !== 'undefined') {
                gtag('event', 'share', {
                    method: type,
                    content_type: 'article',
                    item_id: window.location.href
                });
            }

            // 自サイトの統計更新（統一化されたNonce検証付き）
            const trackShareNonce = window.keiPortfolioAjax?.nonces?.share || window.keiPortfolioAjax?.shareNonce;
            if (window.keiPortfolioAjax && trackShareNonce) {
                $.ajax({
                    url: window.keiPortfolioAjax.ajaxUrl || '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    data: {
                        action: 'track_share',
                        type: type,
                        url: window.location.href,
                        nonce: trackShareNonce
                    },
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', trackShareNonce);
                    },
                    error: function(xhr, status, error) {
                        console.error('Blog Ajax: シェア追跡エラー', {
                            status: xhr.status,
                            error: error
                        });
                    }
                });
            }
        }

        /**
         * 目次の自動生成
         */
        initTocGeneration() {
            const content = document.querySelector('.article-content');
            if (!content) return;

            const headings = content.querySelectorAll('h2, h3, h4');
            if (headings.length < 3) return;

            const toc = this.generateToc(headings);
            this.insertToc(content, toc);
        }

        /**
         * 目次DOM生成（安全なDOM操作）
         */
        generateToc(headings) {
            const tocContainer = document.createElement('div');
            tocContainer.className = 'table-of-contents bg-blue-50 p-6 rounded-lg mb-8 border border-blue-100';
            
            const tocTitle = document.createElement('h3');
            tocTitle.className = 'text-lg font-semibold text-gray-800 mb-4 flex items-center';
            
            const icon = document.createElement('i');
            icon.className = 'ri-list-unordered mr-2 text-blue-600';
            
            tocTitle.appendChild(icon);
            tocTitle.appendChild(document.createTextNode('目次'));
            
            const tocList = document.createElement('ul');
            tocList.className = 'space-y-2';

            headings.forEach((heading, index) => {
                const id = `heading-${index}`;
                heading.id = id;
                
                const level = parseInt(heading.tagName.charAt(1));
                const marginClass = level > 2 ? 'ml-4' : '';
                
                const listItem = document.createElement('li');
                listItem.className = marginClass;
                
                const link = document.createElement('a');
                link.href = `#${id}`;
                link.className = 'text-blue-700 hover:text-blue-900 text-sm leading-relaxed transition-colors';
                link.textContent = heading.textContent; // 自動エスケープ
                
                listItem.appendChild(link);
                tocList.appendChild(listItem);
            });

            tocContainer.appendChild(tocTitle);
            tocContainer.appendChild(tocList);
            
            return tocContainer;
        }

        /**
         * 目次をコンテンツに挿入
         */
        insertToc(content, tocElement) {
            const firstParagraph = content.querySelector('p');
            if (firstParagraph) {
                firstParagraph.parentNode.insertBefore(tocElement, firstParagraph.nextSibling);
            }
        }

        /**
         * 読書進行状況バー
         */
        initReadingProgress() {
            if (!document.querySelector('.article-content')) return;

            const progressBar = document.createElement('div');
            progressBar.className = 'reading-progress fixed top-0 left-0 h-1 bg-blue-600 z-50 transition-all duration-200';
            progressBar.style.width = '0%';
            document.body.appendChild(progressBar);

            window.addEventListener('scroll', () => {
                const article = document.querySelector('.article-content');
                if (!article) return;

                const articleTop = article.offsetTop;
                const articleHeight = article.offsetHeight;
                const scrollTop = window.pageYOffset;
                const windowHeight = window.innerHeight;

                const progress = Math.min(100, Math.max(0, 
                    ((scrollTop - articleTop + windowHeight) / articleHeight) * 100
                ));

                progressBar.style.width = `${progress}%`;
            });
        }

        /**
         * アクセシビリティの向上
         */
        initAccessibility() {
            // キーボードナビゲーション
            this.enhanceKeyboardNavigation();
            
            // フォーカスの可視化
            this.enhanceFocusVisibility();
            
            // ARIAラベルの動的設定
            this.setDynamicAriaLabels();
        }

        /**
         * キーボードナビゲーションの強化
         */
        enhanceKeyboardNavigation() {
            // 記事カードのキーボード操作
            $('.post-card').attr('tabindex', '0').on('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    $(this).find('a').first()[0].click();
                }
            });

            // ショートカットキー
            $(document).on('keydown', (e) => {
                // Ctrl+F で検索フィールドにフォーカス
                if (e.ctrlKey && e.key === 'f') {
                    e.preventDefault();
                    $('.search-field').first().focus();
                }
            });
        }

        /**
         * フォーカスの可視化強化
         */
        enhanceFocusVisibility() {
            $('a, button, input, [tabindex]').on('focus', function() {
                $(this).addClass('focus-visible');
            }).on('blur', function() {
                $(this).removeClass('focus-visible');
            });
        }

        /**
         * 動的ARIAラベルの設定
         */
        setDynamicAriaLabels() {
            // 記事の読了時間をaria-labelに追加
            $('.read-more').each(function() {
                const title = $(this).closest('.post-card').find('.post-title').text();
                $(this).attr('aria-label', `${title}の続きを読む`);
            });
        }

        /**
         * パフォーマンス最適化
         */
        initPerformanceOptimizations() {
            // Intersection Observer for animations
            this.initScrollAnimations();
            
            // Image loading optimization
            this.optimizeImageLoading();
            
            // Memory cleanup
            this.setupMemoryCleanup();
        }

        /**
         * スクロールアニメーション
         */
        initScrollAnimations() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('fade-in-up');
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.1,
                rootMargin: '50px'
            });

            document.querySelectorAll('.post-card, .sidebar-widget').forEach(el => {
                observer.observe(el);
            });
        }

        /**
         * 画像読み込みの最適化
         */
        optimizeImageLoading() {
            // Critical images preloading
            const criticalImages = document.querySelectorAll('img[loading="eager"]');
            criticalImages.forEach(img => {
                const link = document.createElement('link');
                link.rel = 'preload';
                link.as = 'image';
                link.href = img.src;
                document.head.appendChild(link);
            });
        }

        /**
         * メモリクリーンアップ強化
         */
        setupMemoryCleanup() {
            // ページ離脱時のクリーンアップ
            const beforeUnloadHandler = () => {
                this.cleanup();
            };
            
            window.addEventListener('beforeunload', beforeUnloadHandler);
            this.eventHandlers.set(window, { event: 'beforeunload', handler: beforeUnloadHandler });
        }
        
        /**
         * 完全なクリーンアップ処理
         */
        cleanup() {
            // 全てのタイマーをクリア
            this.timers.forEach(timer => {
                clearTimeout(timer);
            });
            this.timers.clear();
            
            // 全てのObserverを停止
            this.observers.forEach(observer => {
                observer.disconnect();
            });
            this.observers.clear();
            
            // 全てのイベントハンドラーを削除
            this.eventHandlers.forEach((config, element) => {
                if (config.element) {
                    config.element.off(config.event, config.handler);
                } else if (element.removeEventListener) {
                    element.removeEventListener(config.event, config.handler);
                }
            });
            this.eventHandlers.clear();
            
            // プロパティのクリア
            this.searchTimeout = null;
            this.loading = false;
        }
    }

    // ブログ機能の初期化
    new BlogFunctions();

})(jQuery);
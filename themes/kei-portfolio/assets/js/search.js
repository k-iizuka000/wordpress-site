/**
 * 検索機能の管理クラス
 * search.phpのインラインJavaScriptを外部ファイル化
 * 
 * @package Kei_Portfolio
 * @version 1.0.0
 */

class SearchManager {
    constructor() {
        this.searchForm = document.querySelector('.search-form');
        this.searchInput = document.querySelector('#search-field, .search-form input[type="search"]');
        this.resultsContainer = document.querySelector('.search-results-container');
        this.postTypeFilter = document.querySelector('#post-type-filter');
        this.sortSelect = document.querySelector('#sort-results');
        this.viewToggleButtons = document.querySelectorAll('.view-toggle-btn');
        this.loadingIndicator = null;
        
        // WordPress AJAX設定
        this.ajaxUrl = window.ajaxurl || '/wp-admin/admin-ajax.php';
        this.nonce = window.searchAjax?.nonce || '';
        
        // 設定
        this.config = {
            minQueryLength: 2,
            searchDelay: 500,
            enableRealTimeSearch: false,
            animateResults: true
        };
        
        // イベントリスナーの設定
        this.init();
    }
    
    init() {
        console.log('SearchManager初期化中...');
        
        // 基本的なイベントリスナー
        this.bindEvents();
        
        // URL パラメータからの初期状態設定
        this.setInitialState();
        
        // 検索ハイライトのアニメーション
        this.animateHighlights();
        
        console.log('SearchManager初期化完了');
    }
    
    bindEvents() {
        // 検索フォーム送信
        if (this.searchForm) {
            this.searchForm.addEventListener('submit', (e) => this.handleSearchSubmit(e));
        }
        
        // リアルタイム検索（オプション）
        if (this.searchInput && this.config.enableRealTimeSearch) {
            let searchTimeout;
            this.searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.performLiveSearch(e.target.value.trim());
                }, this.config.searchDelay);
            });
        }
        
        // 投稿タイプフィルター
        if (this.postTypeFilter) {
            this.postTypeFilter.addEventListener('change', (e) => {
                this.updateSearchFilter('post_type', e.target.value);
            });
        }
        
        // ソート順変更
        if (this.sortSelect) {
            this.sortSelect.addEventListener('change', (e) => {
                this.updateSearchFilter('sort', e.target.value);
            });
        }
        
        // ビュー切り替えボタン
        this.viewToggleButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleView(e.currentTarget);
            });
        });
        
        // キーボードナビゲーション
        document.addEventListener('keydown', (e) => this.handleKeyboardNavigation(e));
        
        // 検索ハイライトのクリックイベント
        this.bindHighlightEvents();
    }
    
    handleSearchSubmit(event) {
        const query = this.searchInput ? this.searchInput.value.trim() : '';
        
        if (query.length < this.config.minQueryLength) {
            event.preventDefault();
            this.showMessage(`検索キーワードは${this.config.minQueryLength}文字以上入力してください`, 'warning');
            this.searchInput?.focus();
            return false;
        }
        
        // バリデーション成功時は通常の送信を継続
        this.showLoadingState(true);
        return true;
    }
    
    async performLiveSearch(query) {
        if (query.length < this.config.minQueryLength) {
            return;
        }
        
        if (!this.nonce) {
            console.warn('AJAX nonce が設定されていません');
            return;
        }
        
        try {
            this.showLoadingState(true);
            
            const formData = new FormData();
            formData.append('action', 'live_search');
            formData.append('nonce', this.nonce);
            formData.append('search_query', query);
            formData.append('post_type', this.getCurrentFilter('post_type'));
            formData.append('sort_order', this.getCurrentFilter('sort'));
            
            const response = await fetch(this.ajaxUrl, {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (result.success) {
                this.updateResultsContainer(result.data);
                this.updateURL(query);
            } else {
                this.showMessage(result.data?.message || '検索に失敗しました', 'error');
            }
        } catch (error) {
            console.error('Live search failed:', error);
            this.showMessage('検索処理中にエラーが発生しました', 'error');
        } finally {
            this.showLoadingState(false);
        }
    }
    
    updateSearchFilter(filterType, value) {
        const url = new URL(window.location);
        const params = new URLSearchParams(url.search);
        
        if (value && value !== 'any' && value !== 'relevance') {
            params.set(filterType, value);
        } else {
            params.delete(filterType);
        }
        
        // 検索クエリを保持
        const currentQuery = this.searchInput?.value.trim();
        if (currentQuery) {
            params.set('s', currentQuery);
        }
        
        // ページリロード
        url.search = params.toString();
        window.location.href = url.toString();
    }
    
    toggleView(clickedButton) {
        const newView = clickedButton.dataset.view;
        
        // ボタン状態更新
        this.viewToggleButtons.forEach(btn => {
            btn.classList.remove('active');
            btn.setAttribute('aria-pressed', 'false');
        });
        
        clickedButton.classList.add('active');
        clickedButton.setAttribute('aria-pressed', 'true');
        
        // 結果コンテナのクラス更新
        if (this.resultsContainer) {
            this.resultsContainer.className = this.resultsContainer.className
                .replace(/posts-(grid|list)/, `posts-${newView}`);
            this.resultsContainer.dataset.view = newView;
        }
        
        // URL更新（オプション）
        this.updateURL(null, { view: newView });
        
        // アクセシビリティ告知
        this.announceToScreenReader(`表示形式を${newView === 'grid' ? 'グリッド' : 'リスト'}に変更しました`);
        
        // アニメーション
        if (this.config.animateResults) {
            this.animateViewChange();
        }
    }
    
    updateResultsContainer(data) {
        if (!this.resultsContainer || !data.html) {
            return;
        }
        
        // フェードアウト
        this.resultsContainer.style.opacity = '0';
        
        setTimeout(() => {
            this.resultsContainer.innerHTML = data.html;
            
            // 新しい検索ハイライトにイベントバインド
            this.bindHighlightEvents();
            
            // フェードイン
            this.resultsContainer.style.opacity = '1';
            
            // アニメーション
            if (this.config.animateResults) {
                this.animateNewResults();
            }
            
            // 統計更新
            this.updateSearchStats(data.found_posts);
            
        }, 150);
    }
    
    getCurrentFilter(filterType) {
        const url = new URL(window.location);
        const params = new URLSearchParams(url.search);
        return params.get(filterType) || '';
    }
    
    updateURL(query, additionalParams = {}) {
        const url = new URL(window.location);
        const params = new URLSearchParams(url.search);
        
        if (query !== null && query !== undefined) {
            if (query.trim()) {
                params.set('s', query.trim());
            } else {
                params.delete('s');
            }
        }
        
        // 追加パラメータの設定
        Object.entries(additionalParams).forEach(([key, value]) => {
            if (value && value !== 'any' && value !== 'relevance') {
                params.set(key, value);
            } else {
                params.delete(key);
            }
        });
        
        // 履歴に追加（検索結果ページでは）
        const newURL = `${url.pathname}?${params.toString()}`;
        if (window.location.pathname.includes('/search/') || params.has('s')) {
            window.history.replaceState(null, '', newURL);
        }
    }
    
    setInitialState() {
        const url = new URL(window.location);
        const params = new URLSearchParams(url.search);
        
        // ビュー状態設定
        const currentView = params.get('view') || 'list';
        const activeButton = document.querySelector(`.view-toggle-btn[data-view="${currentView}"]`);
        if (activeButton) {
            this.toggleView(activeButton);
        }
    }
    
    animateHighlights() {
        const highlights = document.querySelectorAll('.search-highlight');
        highlights.forEach((highlight, index) => {
            highlight.style.animationDelay = `${index * 0.1}s`;
            highlight.classList.add('highlight-animate');
        });
    }
    
    bindHighlightEvents() {
        const highlights = document.querySelectorAll('.search-highlight');
        highlights.forEach(highlight => {
            highlight.addEventListener('click', () => {
                highlight.classList.add('highlight-clicked');
                setTimeout(() => {
                    highlight.classList.remove('highlight-clicked');
                }, 300);
            });
        });
    }
    
    animateNewResults() {
        const resultCards = this.resultsContainer?.querySelectorAll('.search-result-card');
        if (!resultCards) return;
        
        resultCards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    }
    
    animateViewChange() {
        if (!this.resultsContainer) return;
        
        this.resultsContainer.style.transform = 'scale(0.98)';
        this.resultsContainer.style.opacity = '0.7';
        
        setTimeout(() => {
            this.resultsContainer.style.transition = 'transform 0.3s ease, opacity 0.3s ease';
            this.resultsContainer.style.transform = 'scale(1)';
            this.resultsContainer.style.opacity = '1';
        }, 50);
    }
    
    showLoadingState(isLoading) {
        if (isLoading) {
            if (!this.loadingIndicator) {
                this.createLoadingIndicator();
            }
            this.loadingIndicator?.classList.remove('hidden');
        } else {
            this.loadingIndicator?.classList.add('hidden');
        }
        
        // フォーム要素の無効化
        const formElements = this.searchForm?.querySelectorAll('input, select, button');
        formElements?.forEach(element => {
            element.disabled = isLoading;
        });
    }
    
    createLoadingIndicator() {
        this.loadingIndicator = document.createElement('div');
        this.loadingIndicator.className = 'search-loading fixed top-4 right-4 bg-blue-600 text-white px-4 py-2 rounded-lg shadow-lg z-50 hidden';
        this.loadingIndicator.innerHTML = `
            <div class="flex items-center space-x-2">
                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle>
                    <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" class="opacity-75"></path>
                </svg>
                <span>検索中...</span>
            </div>
        `;
        document.body.appendChild(this.loadingIndicator);
    }
    
    showMessage(message, type = 'info') {
        const messageContainer = document.createElement('div');
        const typeColors = {
            info: 'bg-blue-100 border-blue-300 text-blue-800',
            warning: 'bg-yellow-100 border-yellow-300 text-yellow-800',
            error: 'bg-red-100 border-red-300 text-red-800',
            success: 'bg-green-100 border-green-300 text-green-800'
        };
        
        messageContainer.className = `search-message fixed top-4 right-4 p-4 rounded-lg border ${typeColors[type]} shadow-lg z-50 max-w-sm`;
        messageContainer.textContent = message;
        messageContainer.setAttribute('role', type === 'error' ? 'alert' : 'status');
        messageContainer.setAttribute('aria-live', 'polite');
        
        document.body.appendChild(messageContainer);
        
        // 自動削除
        setTimeout(() => {
            messageContainer.remove();
        }, 5000);
    }
    
    updateSearchStats(foundPosts) {
        const statsElement = document.querySelector('.search-stats');
        if (statsElement && foundPosts !== undefined) {
            const statsText = foundPosts > 0 
                ? `<strong>${foundPosts.toLocaleString()}</strong>件の結果が見つかりました`
                : '検索条件に一致する結果が見つかりませんでした';
            statsElement.innerHTML = statsText;
        }
    }
    
    handleKeyboardNavigation(event) {
        // 検索結果のキーボードナビゲーション
        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            const focusableElements = this.resultsContainer?.querySelectorAll('a[href]');
            if (!focusableElements?.length) return;
            
            const currentIndex = Array.from(focusableElements).indexOf(document.activeElement);
            let nextIndex;
            
            if (event.key === 'ArrowDown') {
                nextIndex = currentIndex < focusableElements.length - 1 ? currentIndex + 1 : 0;
            } else {
                nextIndex = currentIndex > 0 ? currentIndex - 1 : focusableElements.length - 1;
            }
            
            event.preventDefault();
            focusableElements[nextIndex]?.focus();
        }
        
        // Escapeキーで検索フィールドにフォーカス
        if (event.key === 'Escape') {
            this.searchInput?.focus();
        }
    }
    
    announceToScreenReader(message) {
        const announcement = document.createElement('div');
        announcement.setAttribute('aria-live', 'polite');
        announcement.setAttribute('aria-atomic', 'true');
        announcement.className = 'sr-only';
        announcement.textContent = message;
        
        document.body.appendChild(announcement);
        
        setTimeout(() => {
            document.body.removeChild(announcement);
        }, 1000);
    }
    
    // 公開メソッド
    enableRealTimeSearch() {
        this.config.enableRealTimeSearch = true;
        this.bindEvents(); // 再バインド
    }
    
    disableRealTimeSearch() {
        this.config.enableRealTimeSearch = false;
    }
    
    updateConfig(newConfig) {
        this.config = { ...this.config, ...newConfig };
    }
}

// DOM読み込み完了時に初期化
document.addEventListener('DOMContentLoaded', () => {
    window.searchManager = new SearchManager();
    
    // デバッグモード用のグローバル露出
    if (window.location.search.includes('debug=search')) {
        console.log('Search Debug Mode Enabled');
        window.SearchManager = SearchManager;
    }
});

// エクスポート（モジュール環境用）
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SearchManager;
}
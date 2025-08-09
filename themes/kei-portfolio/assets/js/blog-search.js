/**
 * ブログ検索機能のJavaScript
 * search.phpのインラインJSを外部ファイル化
 * 
 * @package Kei_Portfolio
 * @version 1.0.0
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // View toggle functionality
    const viewToggleBtns = document.querySelectorAll('.view-toggle-btn');
    const resultsContainer = document.getElementById('search-results-container');
    
    if (resultsContainer && viewToggleBtns.length > 0) {
        viewToggleBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const newView = this.dataset.view;
                
                if (!newView) return;
                
                // URLを更新
                updateUrlParameter('view', newView);
                
                // コンテナのクラスを更新
                updateResultsView(resultsContainer, newView);
                
                // ボタンの状態を更新
                updateViewToggleButtons(viewToggleBtns, this);
                
                // アナリティクス追跡（もしGAが設定されている場合）
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'search_view_toggle', {
                        'event_category': 'engagement',
                        'event_label': newView
                    });
                }
            });
        });
    }
    
    // Sort functionality
    const sortSelect = document.getElementById('sort-results');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            const sortValue = this.value;
            
            if (!sortValue) return;
            
            // URLパラメータを更新してページを再読み込み
            updateUrlParameterAndReload('sort', sortValue);
            
            // ローディング表示
            showLoadingIndicator('検索結果を並び替え中...');
        });
    }
    
    // Post type filter functionality
    const postTypeFilter = document.getElementById('post-type-filter');
    if (postTypeFilter) {
        postTypeFilter.addEventListener('change', function() {
            const postType = this.value;
            
            if (!postType) return;
            
            // URLパラメータを更新してページを再読み込み
            updateUrlParameterAndReload('post_type', postType);
            
            // ローディング表示
            showLoadingIndicator('検索結果をフィルタリング中...');
        });
    }
    
    // Auto-focus search field if empty search
    const searchField = document.getElementById('search-field');
    if (searchField && !searchField.value.trim()) {
        searchField.focus();
    }
    
    // 検索フォームの submit イベント
    const searchForm = document.querySelector('.search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const searchInput = this.querySelector('input[name="s"]');
            const searchValue = searchInput.value.trim();
            
            if (!searchValue) {
                e.preventDefault();
                searchInput.focus();
                showNotification('検索キーワードを入力してください', 'warning');
                return false;
            }
            
            // ローディング表示
            showLoadingIndicator('検索中...');
        });
    }
    
    // 検索結果のハイライト機能強化
    enhanceSearchHighlights();
    
    // キーボードショートカット
    setupKeyboardShortcuts();
    
    // 検索統計の追跡
    trackSearchAnalytics();
});

/**
 * URLパラメータを更新（ページリロードなし）
 */
function updateUrlParameter(param, value) {
    try {
        const currentUrl = new URL(window.location);
        currentUrl.searchParams.set(param, value);
        
        if (history.replaceState) {
            history.replaceState({}, '', currentUrl);
        }
    } catch (error) {
        console.error('URL update failed:', error);
    }
}

/**
 * URLパラメータを更新してページを再読み込み
 */
function updateUrlParameterAndReload(param, value) {
    try {
        const currentUrl = new URL(window.location);
        currentUrl.searchParams.set(param, value);
        window.location.href = currentUrl.toString();
    } catch (error) {
        console.error('URL update and reload failed:', error);
        // フォールバック: 従来の方法
        window.location.search = updateQueryStringParameter(window.location.search, param, value);
    }
}

/**
 * 検索結果の表示形式を更新
 */
function updateResultsView(container, newView) {
    if (!container) return;
    
    // 既存のクラスを削除
    container.classList.remove('posts-grid', 'posts-list');
    
    // 新しいクラスを追加
    const newClass = newView === 'grid' ? 'posts-grid' : 'posts-list';
    container.classList.add(newClass);
    container.dataset.view = newView;
    
    // アニメーション効果
    container.style.opacity = '0.7';
    setTimeout(() => {
        container.style.opacity = '1';
    }, 150);
}

/**
 * ビュー切り替えボタンの状態を更新
 */
function updateViewToggleButtons(buttons, activeButton) {
    buttons.forEach(btn => {
        btn.classList.remove('active');
        btn.setAttribute('aria-pressed', 'false');
    });
    
    activeButton.classList.add('active');
    activeButton.setAttribute('aria-pressed', 'true');
}

/**
 * ローディングインジケーターを表示
 */
function showLoadingIndicator(message = '読み込み中...') {
    // 既存のローディング要素があれば削除
    const existingLoader = document.getElementById('search-loader');
    if (existingLoader) {
        existingLoader.remove();
    }
    
    // ローディング要素を作成
    const loader = document.createElement('div');
    loader.id = 'search-loader';
    loader.className = 'fixed top-0 left-0 w-full h-full bg-black bg-opacity-50 flex items-center justify-center z-50';
    loader.innerHTML = `
        <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
            <span class="text-gray-700">${message}</span>
        </div>
    `;
    
    document.body.appendChild(loader);
}

/**
 * 通知を表示
 */
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${getNotificationClass(type)}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // 3秒後に自動削除
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

/**
 * 通知タイプに応じたCSSクラスを取得
 */
function getNotificationClass(type) {
    switch (type) {
        case 'success':
            return 'bg-green-500 text-white';
        case 'warning':
            return 'bg-yellow-500 text-white';
        case 'error':
            return 'bg-red-500 text-white';
        default:
            return 'bg-blue-500 text-white';
    }
}

/**
 * クエリストリングパラメータを更新（レガシー対応）
 */
function updateQueryStringParameter(uri, key, value) {
    const re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
    const separator = uri.indexOf('?') !== -1 ? "&" : "?";
    
    if (uri.match(re)) {
        return uri.replace(re, '$1' + key + "=" + value + '$2');
    } else {
        return uri + separator + key + "=" + value;
    }
}

/**
 * 検索ハイライトの機能を強化
 */
function enhanceSearchHighlights() {
    const highlights = document.querySelectorAll('.search-highlight');
    
    highlights.forEach((highlight, index) => {
        // アニメーション効果
        highlight.style.animationDelay = `${index * 100}ms`;
        highlight.classList.add('highlight-animate');
        
        // ハイライト部分をクリックした時の動作
        highlight.addEventListener('click', function() {
            this.classList.add('highlight-clicked');
            setTimeout(() => {
                this.classList.remove('highlight-clicked');
            }, 300);
        });
    });
}

/**
 * キーボードショートカットを設定
 */
function setupKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl+F または Cmd+F で検索フィールドにフォーカス
        if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
            const searchField = document.getElementById('search-field');
            if (searchField) {
                e.preventDefault();
                searchField.focus();
                searchField.select();
            }
        }
        
        // Escキーで検索フィールドをクリア
        if (e.key === 'Escape') {
            const searchField = document.getElementById('search-field');
            if (searchField && document.activeElement === searchField) {
                searchField.value = '';
            }
        }
        
        // G/L キーでビュー切り替え
        if (!isInputFocused() && (e.key === 'g' || e.key === 'G')) {
            const gridBtn = document.querySelector('.view-toggle-btn[data-view="grid"]');
            if (gridBtn) gridBtn.click();
        }
        
        if (!isInputFocused() && (e.key === 'l' || e.key === 'L')) {
            const listBtn = document.querySelector('.view-toggle-btn[data-view="list"]');
            if (listBtn) listBtn.click();
        }
    });
}

/**
 * 入力フィールドにフォーカスがあるかチェック
 */
function isInputFocused() {
    const activeElement = document.activeElement;
    return activeElement && (
        activeElement.tagName === 'INPUT' || 
        activeElement.tagName === 'TEXTAREA' || 
        activeElement.tagName === 'SELECT'
    );
}

/**
 * 検索アナリティクスを追跡
 */
function trackSearchAnalytics() {
    // 検索クエリがある場合のみ
    const urlParams = new URLSearchParams(window.location.search);
    const searchQuery = urlParams.get('s');
    
    if (!searchQuery) return;
    
    // 検索結果数を取得
    const resultsContainer = document.getElementById('search-results-container');
    const resultCount = resultsContainer ? resultsContainer.children.length : 0;
    
    // Google Analytics 4 へのイベント送信（設定されている場合）
    if (typeof gtag !== 'undefined') {
        gtag('event', 'search', {
            'search_term': searchQuery,
            'search_results': resultCount
        });
    }
    
    // ローカルストレージに検索履歴を保存（プライバシーに配慮）
    try {
        const searchHistory = JSON.parse(localStorage.getItem('blog_search_history') || '[]');
        const searchEntry = {
            query: searchQuery,
            results: resultCount,
            timestamp: Date.now()
        };
        
        // 重複を避ける
        const existingIndex = searchHistory.findIndex(entry => entry.query === searchQuery);
        if (existingIndex !== -1) {
            searchHistory.splice(existingIndex, 1);
        }
        
        searchHistory.unshift(searchEntry);
        
        // 最新20件のみ保持
        searchHistory.splice(20);
        
        localStorage.setItem('blog_search_history', JSON.stringify(searchHistory));
    } catch (error) {
        console.log('Search history storage failed:', error);
    }
}

/**
 * 検索履歴を取得
 */
function getSearchHistory() {
    try {
        return JSON.parse(localStorage.getItem('blog_search_history') || '[]');
    } catch (error) {
        console.log('Search history retrieval failed:', error);
        return [];
    }
}

/**
 * 検索履歴をクリア
 */
function clearSearchHistory() {
    try {
        localStorage.removeItem('blog_search_history');
        showNotification('検索履歴をクリアしました', 'success');
    } catch (error) {
        console.log('Search history clear failed:', error);
        showNotification('検索履歴のクリアに失敗しました', 'error');
    }
}

// エクスポート（モジュールシステム対応）
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        updateUrlParameter,
        updateUrlParameterAndReload,
        showNotification,
        getSearchHistory,
        clearSearchHistory
    };
}
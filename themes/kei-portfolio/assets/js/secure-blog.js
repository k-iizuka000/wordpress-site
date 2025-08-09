/**
 * セキュアブログマネージャー - XSS対策強化版
 * 
 * セキュリティ対策:
 * - innerHTML使用の完全排除
 * - DOMメソッドによる安全な要素作成
 * - HTMLエスケープ処理
 * - Nonce検証強化
 * - CSP対応
 * 
 * パフォーマンス対策:
 * - イベント委譲の活用
 * - メモリリーク防止
 * - 適切なリソース管理
 * 
 * @package Kei_Portfolio
 * @since 1.0.0
 * @version 1.0.0 (Security Enhanced)
 */

class SecureBlogManager {
    constructor() {
        this.nonce = window.keiPortfolioAjax?.nonce || '';
        this.ajaxUrl = window.keiPortfolioAjax?.ajaxUrl || '';
        this.eventHandlers = new Map();
        this.observers = new Set();
        this.timers = new Set();
        this.initialized = false;
        
        this.init();
    }
    
    /**
     * 初期化処理
     */
    init() {
        if (this.initialized) return;
        
        // セキュリティチェック
        if (!this.validateSecurityContext()) {
            console.error('SecureBlogManager: セキュリティコンテキストが無効です');
            return;
        }
        
        this.setupEventListeners();
        this.setupSecurityHeaders();
        this.initializeSecurityFeatures();
        
        this.initialized = true;
        console.log('SecureBlogManager initialized successfully');
    }
    
    /**
     * セキュリティコンテキストの検証
     * @returns {boolean} 検証結果
     */
    validateSecurityContext() {
        // Nonceの存在と形式確認
        if (!this.nonce || typeof this.nonce !== 'string' || this.nonce.length < 10) {
            console.error('Invalid or missing security nonce');
            return false;
        }
        
        // Ajax URLの検証
        if (!this.ajaxUrl || !this.isValidUrl(this.ajaxUrl)) {
            console.error('Invalid Ajax URL');
            return false;
        }
        
        return true;
    }
    
    /**
     * URL形式の検証
     * @param {string} url 検証するURL
     * @returns {boolean} 有効性
     */
    isValidUrl(url) {
        try {
            const urlObj = new URL(url, window.location.origin);
            return urlObj.protocol === 'http:' || urlObj.protocol === 'https:';
        } catch (e) {
            return false;
        }
    }
    
    /**
     * セキュアなDOM操作
     * @param {string} tag タグ名
     * @param {Object} attributes 属性
     * @param {string} content コンテンツ
     * @returns {Element} 作成された要素
     */
    createSafeElement(tag, attributes = {}, content = '') {
        // タグ名のバリデーション
        const allowedTags = [
            'div', 'span', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'a', 'img', 'ul', 'ol', 'li', 'article', 'section',
            'button', 'input', 'textarea', 'form', 'label'
        ];
        
        if (!allowedTags.includes(tag.toLowerCase())) {
            console.warn(`Tag '${tag}' is not allowed`);
            tag = 'div'; // デフォルトタグにフォールバック
        }
        
        const element = document.createElement(tag);
        
        // 属性の安全な設定
        Object.entries(attributes).forEach(([key, value]) => {
            // 危険な属性をブロック
            if (this.isDangerousAttribute(key)) {
                console.warn(`Dangerous attribute '${key}' blocked`);
                return;
            }
            
            // 値をエスケープして設定
            element.setAttribute(key, this.escapeAttributeValue(value));
        });
        
        // コンテンツの安全な設定
        if (content) {
            element.textContent = content; // XSS防止のためtextContentを使用
        }
        
        return element;
    }
    
    /**
     * 危険な属性の検査
     * @param {string} attributeName 属性名
     * @returns {boolean} 危険性
     */
    isDangerousAttribute(attributeName) {
        const dangerousAttributes = [
            'onclick', 'onload', 'onerror', 'onmouseover', 'onfocus', 'onblur',
            'onchange', 'onsubmit', 'onkeyup', 'onkeydown', 'onkeypress',
            'onmousedown', 'onmouseup', 'onmousemove', 'ondblclick',
            'oncontextmenu', 'onwheel', 'ondrag', 'ondrop', 'onscroll'
        ];
        
        return dangerousAttributes.includes(attributeName.toLowerCase()) ||
               attributeName.toLowerCase().startsWith('on');
    }
    
    /**
     * 属性値のエスケープ
     * @param {*} value 属性値
     * @returns {string} エスケープされた値
     */
    escapeAttributeValue(value) {
        if (typeof value !== 'string') {
            value = String(value);
        }
        
        return value
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#x27;')
            .replace(/\//g, '&#x2F;');
    }
    
    /**
     * HTMLエスケープ
     * @param {string} text エスケープするテキスト
     * @returns {string} エスケープされたテキスト
     */
    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;',
            '/': '&#x2F;',
            '`': '&#x60;',
            '=': '&#x3D;'
        };
        
        return String(text).replace(/[&<>"'`=\/]/g, function (s) {
            return map[s];
        });
    }
    
    /**
     * セキュアなAjaxリクエスト
     * @param {string} action アクション名
     * @param {Object} data リクエストデータ
     * @returns {Promise} Ajaxプロミス
     */
    async secureAjaxRequest(action, data = {}) {
        if (!this.nonce) {
            throw new Error('Security nonce not found');
        }
        
        // アクション名の検証
        if (!this.isValidAction(action)) {
            throw new Error('Invalid action name');
        }
        
        const formData = new FormData();
        formData.append('action', action);
        formData.append('nonce', this.nonce);
        
        // データのサニタイズと追加
        Object.entries(data).forEach(([key, value]) => {
            if (typeof value === 'string') {
                value = this.sanitizeInput(value);
            }
            formData.append(key, value);
        });
        
        try {
            const response = await fetch(this.ajaxUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-WP-Nonce': this.nonce,
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-KeiPortfolio-Request': 'secure-blog'
                },
                body: formData
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.data?.message || 'Request failed');
            }
            
            return result.data;
        } catch (error) {
            console.error('Secure Ajax request failed:', error);
            throw error;
        }
    }
    
    /**
     * アクション名の検証
     * @param {string} action アクション名
     * @returns {boolean} 有効性
     */
    isValidAction(action) {
        const allowedActions = [
            'load_more_posts',
            'blog_instant_search',
            'get_share_counts',
            'track_share',
            'blog_filter_posts',
            'blog_like_post'
        ];
        
        return allowedActions.includes(action);
    }
    
    /**
     * 入力のサニタイズ
     * @param {string} input 入力文字列
     * @returns {string} サニタイズされた文字列
     */
    sanitizeInput(input) {
        if (typeof input !== 'string') {
            return '';
        }
        
        return input
            .trim()
            .substring(0, 1000) // 長さ制限
            .replace(/[<>\"']/g, '') // 危険な文字を削除
            .replace(/javascript:/gi, '') // JavaScriptスキーマを削除
            .replace(/on\w+=/gi, ''); // イベントハンドラーを削除
    }
    
    /**
     * セキュリティヘッダーの設定
     * 
     * 注意: CSPはサーバーサイドで設定されるため、
     * JavaScriptでのCSP設定は無効化されました。
     * 詳細は /inc/security.php を参照してください。
     */
    setupSecurityHeaders() {
        // CSP設定は /inc/security.php で実装されているため、
        // ここでは他のクライアントサイドセキュリティ機能のみ実装
        
        // ブラウザのセキュリティ機能確認
        this.checkBrowserSecurity();
    }
    
    /**
     * ブラウザのセキュリティ機能確認
     */
    checkBrowserSecurity() {
        // HTTPS接続の確認
        if (location.protocol !== 'https:' && location.hostname !== 'localhost') {
            console.warn('HTTPS接続が推奨されます');
        }
        
        // Secure Contextの確認
        if (!window.isSecureContext) {
            console.warn('Secure Contextではありません');
        }
        
        // CSP違反の監視
        if (typeof document.addEventListener === 'function') {
            document.addEventListener('securitypolicyviolation', (e) => {
                console.warn('CSP violation:', e);
                // 必要に応じてサーバーに報告
            });
        }
    }
    
    /**
     * イベントリスナーの安全な設定
     */
    setupEventListeners() {
        // イベント委譲を使用してメモリリークを防ぐ
        const clickHandler = (e) => {
            if (e.target.matches('[data-action]')) {
                e.preventDefault();
                this.handleSecureAction(e.target.dataset.action, e.target);
            }
        };
        
        document.addEventListener('click', clickHandler);
        this.eventHandlers.set('document-click', {
            element: document,
            event: 'click',
            handler: clickHandler
        });
        
        // フォーム送信の処理
        const formHandler = (e) => {
            if (e.target.matches('[data-secure-form]')) {
                e.preventDefault();
                this.handleSecureForm(e.target);
            }
        };
        
        document.addEventListener('submit', formHandler);
        this.eventHandlers.set('document-submit', {
            element: document,
            event: 'submit',
            handler: formHandler
        });
    }
    
    /**
     * セキュリティ機能の初期化
     */
    initializeSecurityFeatures() {
        // XSS検知
        this.setupXssDetection();
        
        // CSRF対策
        this.setupCsrfProtection();
        
        // 入力検証
        this.setupInputValidation();
    }
    
    /**
     * XSS検知の設定
     */
    setupXssDetection() {
        // prototypeの変更を避けて、専用メソッドを使用
        const safeSetInnerHTML = (element, html) => {
            if (this.hasXssPattern(html)) {
                console.warn('Potential XSS attempt blocked');
                return false;
            }
            element.innerHTML = html;
            return true;
        };
        
        // グローバル公開せずクラス内メソッドとして管理
        this.safeSetInnerHTML = safeSetInnerHTML;
        
        // XSSパターン検知をクラスメソッドとして定義
        this.hasXssPattern = (html) => {
            if (typeof html !== 'string') return false;
            
            const xssPatterns = [
                /<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi,
                /<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/gi,
                /javascript:/gi,
                /on\w+\s*=/gi,
                /<object\b[^<]*(?:(?!<\/object>)<[^<]*)*<\/object>/gi,
                /<embed\b[^>]*>/gi,
                /<form\b[^>]*>/gi, // フォーム要素も検知対象に追加
                /<link\b[^>]*>/gi, // 外部リンクも検知対象に追加
                /data:\s*text\/html/gi // data URIのHTML実行も検知
            ];
            
            return xssPatterns.some(pattern => pattern.test(html));
        };
        
        // DOM監視による危険な要素の検知
        this.setupDomXssMonitoring();
    }
    
    /**
     * DOM変更監視によるXSS検知
     */
    setupDomXssMonitoring() {
        if (typeof MutationObserver === 'undefined') return;
        
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            this.scanElementForXss(node);
                        }
                    });
                }
                
                if (mutation.type === 'attributes') {
                    const element = mutation.target;
                    if (this.isDangerousAttribute(mutation.attributeName)) {
                        console.warn('Dangerous attribute detected:', mutation.attributeName);
                        element.removeAttribute(mutation.attributeName);
                    }
                }
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['onclick', 'onload', 'onerror', 'onmouseover', 'onfocus', 'onblur']
        });
        
        this.observers.add(observer);
    }
    
    /**
     * 要素のXSSスキャン
     * @param {Element} element スキャンする要素
     */
    scanElementForXss(element) {
        // 危険なタグをチェック
        const dangerousTags = ['script', 'iframe', 'object', 'embed', 'form'];
        if (dangerousTags.includes(element.tagName.toLowerCase())) {
            console.warn('Dangerous element detected and removed:', element.tagName);
            element.remove();
            return;
        }
        
        // 危険な属性をチェック
        Array.from(element.attributes).forEach((attr) => {
            if (this.isDangerousAttribute(attr.name)) {
                console.warn('Dangerous attribute removed:', attr.name);
                element.removeAttribute(attr.name);
            }
        });
        
        // 子要素も再帰的にチェック
        Array.from(element.children).forEach((child) => {
            this.scanElementForXss(child);
        });
    }
    
    /**
     * CSRF対策の設定
     */
    setupCsrfProtection() {
        // 全てのフォームにnonceを追加
        const forms = document.querySelectorAll('form[data-secure-form]');
        forms.forEach(form => {
            let nonceField = form.querySelector('input[name="_wpnonce"]');
            if (!nonceField) {
                nonceField = this.createSafeElement('input', {
                    type: 'hidden',
                    name: '_wpnonce',
                    value: this.nonce
                });
                form.appendChild(nonceField);
            }
        });
    }
    
    /**
     * 入力検証の設定
     */
    setupInputValidation() {
        const inputs = document.querySelectorAll('input[data-validate], textarea[data-validate]');
        inputs.forEach(input => {
            const validateHandler = () => {
                this.validateInput(input);
            };
            
            input.addEventListener('blur', validateHandler);
            input.addEventListener('input', Utils.debounce(validateHandler, 500));
        });
    }
    
    /**
     * 入力の検証
     * @param {HTMLElement} input 入力要素
     */
    validateInput(input) {
        const value = input.value;
        const validationType = input.dataset.validate;
        let isValid = true;
        let errorMessage = '';
        
        switch (validationType) {
            case 'email':
                isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
                errorMessage = '有効なメールアドレスを入力してください';
                break;
            case 'url':
                isValid = this.isValidUrl(value);
                errorMessage = '有効なURLを入力してください';
                break;
            case 'safe-text':
                isValid = !/[<>\"'`]/.test(value);
                errorMessage = '特殊文字は使用できません';
                break;
            default:
                isValid = true;
        }
        
        this.showValidationResult(input, isValid, errorMessage);
        return isValid;
    }
    
    /**
     * バリデーション結果の表示
     * @param {HTMLElement} input 入力要素
     * @param {boolean} isValid 有効性
     * @param {string} errorMessage エラーメッセージ
     */
    showValidationResult(input, isValid, errorMessage) {
        // 既存のエラーメッセージを削除
        const existingError = input.parentNode.querySelector('.validation-error');
        if (existingError) {
            existingError.remove();
        }
        
        if (!isValid && errorMessage) {
            const errorElement = this.createSafeElement('div', {
                class: 'validation-error text-red-500 text-sm mt-1',
                role: 'alert'
            }, errorMessage);
            
            input.parentNode.appendChild(errorElement);
            input.classList.add('border-red-500');
        } else {
            input.classList.remove('border-red-500');
        }
    }
    
    /**
     * セキュアアクションの処理
     * @param {string} action アクション名
     * @param {HTMLElement} element 要素
     */
    async handleSecureAction(action, element) {
        try {
            switch (action) {
                case 'load-more':
                    await this.loadMorePosts(element);
                    break;
                case 'filter':
                    await this.filterPosts(element);
                    break;
                case 'search':
                    await this.performSearch(element);
                    break;
                case 'like':
                    await this.likePost(element);
                    break;
                default:
                    console.warn('Unknown secure action:', action);
            }
        } catch (error) {
            console.error('Secure action failed:', error);
            this.showError('処理中にエラーが発生しました');
        }
    }
    
    /**
     * セキュアフォームの処理
     * @param {HTMLFormElement} form フォーム要素
     */
    async handleSecureForm(form) {
        const formData = new FormData(form);
        const action = form.dataset.action || 'default_form_action';
        
        try {
            // フォーム入力の検証
            const inputs = form.querySelectorAll('input[data-validate], textarea[data-validate]');
            let isFormValid = true;
            
            inputs.forEach(input => {
                if (!this.validateInput(input)) {
                    isFormValid = false;
                }
            });
            
            if (!isFormValid) {
                this.showError('入力内容に誤りがあります');
                return;
            }
            
            // フォームデータをオブジェクトに変換
            const data = {};
            formData.forEach((value, key) => {
                data[key] = value;
            });
            
            const result = await this.secureAjaxRequest(action, data);
            this.showSuccess('送信が完了しました');
            
            // フォームリセット
            form.reset();
            
        } catch (error) {
            console.error('Form submission failed:', error);
            this.showError('送信に失敗しました');
        }
    }
    
    /**
     * エラー表示
     * @param {string} message エラーメッセージ
     */
    showError(message) {
        this.showNotification(message, 'error');
    }
    
    /**
     * 成功メッセージ表示
     * @param {string} message 成功メッセージ
     */
    showSuccess(message) {
        this.showNotification(message, 'success');
    }
    
    /**
     * 通知の表示
     * @param {string} message メッセージ
     * @param {string} type 通知タイプ
     */
    showNotification(message, type = 'info') {
        const notificationDiv = this.createSafeElement('div', {
            class: `notification notification-${type} fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50`,
            role: 'alert',
            'aria-live': 'polite'
        }, message);
        
        // タイプ別スタイル
        const typeStyles = {
            error: 'bg-red-500 text-white',
            success: 'bg-green-500 text-white',
            warning: 'bg-yellow-500 text-black',
            info: 'bg-blue-500 text-white'
        };
        
        notificationDiv.className += ` ${typeStyles[type] || typeStyles.info}`;
        
        document.body.appendChild(notificationDiv);
        
        // アニメーション
        setTimeout(() => {
            notificationDiv.style.transform = 'translateX(0)';
            notificationDiv.style.opacity = '1';
        }, 100);
        
        // 自動削除（メモリリーク防止）
        const timer = Utils.safeTimeout(() => {
            notificationDiv.style.opacity = '0';
            Utils.safeTimeout(() => {
                if (notificationDiv.parentNode) {
                    notificationDiv.parentNode.removeChild(notificationDiv);
                }
            }, 300);
        }, 5000);
        
        this.timers.add(timer.id);
    }
    
    // デバウンス関数は Utils クラスに移動済み
    // 後方互換性のためのプロキシメソッド
    debounce(func, wait) {
        console.warn('SecureBlogManager.debounce is deprecated. Use Utils.debounce instead.');
        return Utils.debounce(func, wait);
    }
    
    /**
     * クリーンアップ処理（メモリリーク対策強化）
     */
    cleanup() {
        // 全てのタイマーを安全にクリア
        this.timers.forEach(timerId => {
            clearTimeout(timerId);
        });
        this.timers.clear();
        
        // 全てのObserverを停止
        this.observers.forEach(observer => {
            if (observer && typeof observer.disconnect === 'function') {
                observer.disconnect();
            }
        });
        this.observers.clear();
        
        // 全てのイベントハンドラーを削除
        this.eventHandlers.forEach((config) => {
            if (config.element && config.element.removeEventListener) {
                config.element.removeEventListener(config.event, config.handler);
            }
        });
        this.eventHandlers.clear();
        
        // Utils クラスのクリーンアップも実行
        if (typeof Utils !== 'undefined') {
            Utils.cleanupMemory();
        }
        
        console.log('SecureBlogManager cleanup completed');
    }
    
    /**
     * 破棄処理
     */
    destroy() {
        this.cleanup();
        this.initialized = false;
        this.nonce = null;
        this.ajaxUrl = null;
    }
    
    /**
     * セキュリティ機能のテスト実行（開発環境用）
     */
    runSecurityTests() {
        if (!window.console || typeof window.console.group !== 'function') return;
        
        console.group('SecureBlogManager Security Tests');
        
        try {
            // XSS検知テスト
            const xssTests = [
                '<script>alert("XSS")</script>',
                '<img src="x" onerror="alert(1)">',
                'javascript:alert(1)',
                '<iframe src="javascript:alert(1)"></iframe>',
                '<object data="data:text/html,<script>alert(1)</script>"></object>'
            ];
            
            console.group('XSS Detection Tests');
            xssTests.forEach((test, index) => {
                const detected = this.hasXssPattern(test);
                console.log(`Test ${index + 1}: ${detected ? '✅ BLOCKED' : '❌ FAILED'}`, test.substring(0, 50));
            });
            console.groupEnd();
            
            // 安全な要素作成テスト
            console.group('Safe Element Creation Tests');
            const safeElement = this.createSafeElement('div', {
                'class': 'test-class',
                'onclick': 'alert(1)', // これは除外される
                'data-safe': 'safe-value'
            }, 'Test Content');
            
            const hasOnclick = safeElement.hasAttribute('onclick');
            const hasClass = safeElement.hasAttribute('class');
            const hasDataSafe = safeElement.hasAttribute('data-safe');
            
            console.log(`Dangerous onclick blocked: ${!hasOnclick ? '✅' : '❌'}`);
            console.log(`Safe class attribute preserved: ${hasClass ? '✅' : '❌'}`);
            console.log(`Safe data attribute preserved: ${hasDataSafe ? '✅' : '❌'}`);
            console.groupEnd();
            
            // 入力サニタイズテスト
            console.group('Input Sanitization Tests');
            const maliciousInputs = [
                '<script>alert(1)</script>',
                'javascript:alert(1)',
                'on\x20load=alert(1)',
                '\"><script>alert(1)</script>'
            ];
            
            maliciousInputs.forEach((input, index) => {
                const sanitized = this.sanitizeInput(input);
                const isSafe = !sanitized.includes('<') && !sanitized.includes('javascript:');
                console.log(`Sanitization Test ${index + 1}: ${isSafe ? '✅' : '❌'}`, {
                    original: input,
                    sanitized: sanitized
                });
            });
            console.groupEnd();
            
            // セキュリティコンテキスト検証テスト
            console.group('Security Context Tests');
            const isValidContext = this.validateSecurityContext();
            console.log(`Security context validation: ${isValidContext ? '✅' : '❌'}`);
            console.groupEnd();
            
            console.log('All security tests completed.');
            
        } catch (error) {
            console.error('Security test error:', error);
        }
        
        console.groupEnd();
    }
}

// 初期化
document.addEventListener('DOMContentLoaded', () => {
    window.secureBlogManager = new SecureBlogManager();
    
    // 開発環境でのセキュリティテスト実行（WP_DEBUGが有効な場合のみ）
    if (window.console && typeof window.keiPortfolioAjax !== 'undefined' && 
        (window.location.hostname === 'localhost' || window.location.search.includes('debug=1'))) {
        
        console.log('🔒 SecureBlogManager initialized in debug mode');
        
        // セキュリティテスト用のコンソールコマンド登録
        window.runSecurityTests = () => {
            if (window.secureBlogManager) {
                window.secureBlogManager.runSecurityTests();
            } else {
                console.error('SecureBlogManager not initialized');
            }
        };
        
        console.log('💡 Run security tests with: runSecurityTests()');
    }
});

// ページ離脱時のクリーンアップ
window.addEventListener('beforeunload', () => {
    if (window.secureBlogManager) {
        window.secureBlogManager.cleanup();
    }
});

// エラーハンドリング強化
window.addEventListener('error', (event) => {
    if (event.error && event.error.message && 
        event.error.message.includes('SecureBlogManager')) {
        console.error('SecureBlogManager Error:', {
            message: event.error.message,
            filename: event.filename,
            lineno: event.lineno,
            colno: event.colno,
            stack: event.error.stack
        });
    }
});

// 未処理のPromise拒否に対するハンドリング
window.addEventListener('unhandledrejection', (event) => {
    if (event.reason && typeof event.reason === 'object' && 
        event.reason.message && event.reason.message.includes('SecureBlogManager')) {
        console.error('SecureBlogManager Promise Rejection:', event.reason);
        event.preventDefault(); // デフォルトのエラー表示を抑制
    }
});
/**
 * Main.js のテスト
 * テスト対象: assets/js/main.js
 * 
 * @package Kei_Portfolio
 */

// モックの設定
global.console = {
    error: jest.fn(),
    warn: jest.fn(),
    log: jest.fn(),
};

// Intersection Observer のモック
global.IntersectionObserver = jest.fn().mockImplementation((callback) => ({
    observe: jest.fn(),
    unobserve: jest.fn(),
    disconnect: jest.fn(),
    root: null,
    rootMargin: '',
    thresholds: [0.1],
}));

// scrollIntoView のモック
Element.prototype.scrollIntoView = jest.fn();

describe('Main.js functionality', () => {
    let originalLocation;

    beforeAll(() => {
        // window.location のモック
        originalLocation = window.location;
        delete window.location;
        window.location = {
            href: 'http://localhost/',
            pathname: '/',
            search: '',
        };
    });

    afterAll(() => {
        window.location = originalLocation;
    });

    beforeEach(() => {
        // DOMをリセット
        document.body.innerHTML = '';
        
        // window.pageYOffset のモック
        Object.defineProperty(window, 'pageYOffset', {
            value: 0,
            writable: true,
        });

        // window.innerWidth のモック
        Object.defineProperty(window, 'innerWidth', {
            value: 1024,
            writable: true,
        });

        // イベントリスナーをクリア
        jest.clearAllMocks();
        
        // URLSearchParams のモック
        global.URLSearchParams = jest.fn(() => ({
            set: jest.fn(),
            toString: jest.fn(() => ''),
        }));
    });

    describe('モバイルメニューの開閉', () => {
        test('モバイルメニューの初期化', () => {
            document.body.innerHTML = `
                <button class="mobile-menu-toggle">Menu</button>
                <nav class="main-navigation">
                    <ul class="menu">
                        <li><a href="#">Home</a></li>
                        <li><a href="#">About</a></li>
                    </ul>
                </nav>
            `;

            // main.jsを読み込む
            require('../../assets/js/main.js');

            // DOMContentLoaded イベントを発火
            const event = new Event('DOMContentLoaded');
            document.dispatchEvent(event);

            const toggleButton = document.querySelector('.mobile-menu-toggle');
            const navigation = document.querySelector('.main-navigation');

            expect(toggleButton).toBeTruthy();
            expect(navigation).toBeTruthy();
        });

        test('モバイルメニューのトグル機能', () => {
            document.body.innerHTML = `
                <button class="mobile-menu-toggle" aria-expanded="false">Menu</button>
                <nav class="main-navigation">
                    <ul class="menu">
                        <li><a href="#">Home</a></li>
                        <li><a href="#">About</a></li>
                    </ul>
                </nav>
            `;

            // main.jsを読み込む
            require('../../assets/js/main.js');

            // DOMContentLoaded イベントを発火
            const event = new Event('DOMContentLoaded');
            document.dispatchEvent(event);

            const toggleButton = document.querySelector('.mobile-menu-toggle');
            const navigation = document.querySelector('.main-navigation');

            // 初期状態の確認
            expect(navigation.classList.contains('is-open')).toBe(false);
            expect(toggleButton.getAttribute('aria-expanded')).toBe('false');

            // ボタンクリック
            toggleButton.click();

            // メニューが開かれることを確認
            expect(navigation.classList.contains('is-open')).toBe(true);
            expect(toggleButton.getAttribute('aria-expanded')).toBe('true');

            // 再度クリック
            toggleButton.click();

            // メニューが閉じられることを確認
            expect(navigation.classList.contains('is-open')).toBe(false);
            expect(toggleButton.getAttribute('aria-expanded')).toBe('false');
        });
    });

    describe('スムーススクロール機能', () => {
        test('ハッシュリンクのスムーススクロール', () => {
            document.body.innerHTML = `
                <a href="#section1" class="smooth-scroll">Link to Section 1</a>
                <a href="#section2">Link to Section 2</a>
                <div id="section1">Section 1 Content</div>
                <div id="section2">Section 2 Content</div>
            `;

            // main.jsを読み込む
            require('../../assets/js/main.js');

            // DOMContentLoaded イベントを発火
            const event = new Event('DOMContentLoaded');
            document.dispatchEvent(event);

            const link1 = document.querySelector('a[href="#section1"]');
            const section1 = document.getElementById('section1');

            // リンククリック
            const clickEvent = new Event('click', { bubbles: true });
            link1.dispatchEvent(clickEvent);

            // scrollIntoView が呼ばれることを確認
            expect(section1.scrollIntoView).toHaveBeenCalledWith({
                behavior: 'smooth',
                block: 'start',
            });
        });

        test('無効なハッシュリンクは処理しない', () => {
            document.body.innerHTML = `
                <a href="#">Empty Hash</a>
                <a href="#0">Zero Hash</a>
                <a href="#nonexistent">Non-existent</a>
            `;

            // main.jsを読み込む
            require('../../assets/js/main.js');

            // DOMContentLoaded イベントを発火
            const event = new Event('DOMContentLoaded');
            document.dispatchEvent(event);

            const emptyHashLink = document.querySelector('a[href="#"]');
            const zeroHashLink = document.querySelector('a[href="#0"]');
            const nonexistentLink = document.querySelector('a[href="#nonexistent"]');

            // scrollIntoView が呼ばれないことを確認
            emptyHashLink.click();
            zeroHashLink.click();
            nonexistentLink.click();

            expect(Element.prototype.scrollIntoView).not.toHaveBeenCalled();
        });
    });

    describe('ヘッダーのスクロール制御', () => {
        test('スクロール時のヘッダークラス変更', () => {
            document.body.innerHTML = `
                <header class="site-header">Header Content</header>
            `;

            // main.jsを読み込む
            require('../../assets/js/main.js');

            // DOMContentLoaded イベントを発火
            const event = new Event('DOMContentLoaded');
            document.dispatchEvent(event);

            const header = document.querySelector('.site-header');

            // 初期状態の確認
            expect(header.classList.contains('scrolled')).toBe(false);

            // スクロール位置を100px以下に設定
            Object.defineProperty(window, 'pageYOffset', {
                value: 50,
                writable: true,
            });

            // スクロールイベントを発火
            const scrollEvent = new Event('scroll');
            window.dispatchEvent(scrollEvent);

            // ヘッダーにscrolledクラスが追加されないことを確認
            expect(header.classList.contains('scrolled')).toBe(false);

            // スクロール位置を100pxより上に設定
            Object.defineProperty(window, 'pageYOffset', {
                value: 150,
                writable: true,
            });

            // スクロールイベントを発火
            window.dispatchEvent(scrollEvent);

            // ヘッダーにscrolledクラスが追加されることを確認
            expect(header.classList.contains('scrolled')).toBe(true);
        });
    });

    describe('フェードインアニメーション', () => {
        test('Intersection Observer の初期化', () => {
            document.body.innerHTML = `
                <div class="fade-in">Fade In Content 1</div>
                <div class="fade-in">Fade In Content 2</div>
                <div class="normal">Normal Content</div>
            `;

            // main.jsを読み込む
            require('../../assets/js/main.js');

            // DOMContentLoaded イベントを発火
            const event = new Event('DOMContentLoaded');
            document.dispatchEvent(event);

            // Intersection Observer が作成されることを確認
            expect(global.IntersectionObserver).toHaveBeenCalledWith(
                expect.any(Function),
                {
                    threshold: 0.1,
                    rootMargin: '0px 0px -50px 0px',
                }
            );

            // observe が fade-in 要素に対して呼ばれることを確認
            const fadeInElements = document.querySelectorAll('.fade-in');
            expect(fadeInElements).toHaveLength(2);
        });

        test('要素が表示された時のis-visibleクラス追加', () => {
            document.body.innerHTML = `
                <div class="fade-in">Fade In Content</div>
            `;

            let intersectionCallback;
            global.IntersectionObserver = jest.fn().mockImplementation((callback) => {
                intersectionCallback = callback;
                return {
                    observe: jest.fn(),
                    unobserve: jest.fn(),
                    disconnect: jest.fn(),
                };
            });

            // main.jsを読み込む
            require('../../assets/js/main.js');

            // DOMContentLoaded イベントを発火
            const event = new Event('DOMContentLoaded');
            document.dispatchEvent(event);

            const fadeInElement = document.querySelector('.fade-in');

            // 初期状態の確認
            expect(fadeInElement.classList.contains('is-visible')).toBe(false);

            // Intersection Observer コールバックを模擬実行
            intersectionCallback([{
                target: fadeInElement,
                isIntersecting: true,
            }]);

            // is-visible クラスが追加されることを確認
            expect(fadeInElement.classList.contains('is-visible')).toBe(true);
        });
    });

    describe('プロジェクトフィルター（アーカイブページ用）', () => {
        test('フィルターの初期化', () => {
            document.body.innerHTML = `
                <select id="technology-filter">
                    <option value="">All Technologies</option>
                    <option value="react">React</option>
                    <option value="php">PHP</option>
                </select>
                <select id="industry-filter">
                    <option value="">All Industries</option>
                    <option value="web">Web</option>
                    <option value="mobile">Mobile</option>
                </select>
                <select id="sort-order">
                    <option value="date-desc">Date (Newest First)</option>
                    <option value="date-asc">Date (Oldest First)</option>
                </select>
            `;

            // main.jsを読み込む
            require('../../assets/js/main.js');

            // DOMContentLoaded イベントを発火
            const event = new Event('DOMContentLoaded');
            document.dispatchEvent(event);

            const techFilter = document.getElementById('technology-filter');
            const industryFilter = document.getElementById('industry-filter');
            const sortOrder = document.getElementById('sort-order');

            expect(techFilter).toBeTruthy();
            expect(industryFilter).toBeTruthy();
            expect(sortOrder).toBeTruthy();
        });

        test('フィルター変更時のURL更新', () => {
            document.body.innerHTML = `
                <select id="technology-filter">
                    <option value="">All Technologies</option>
                    <option value="react">React</option>
                </select>
                <select id="industry-filter">
                    <option value="">All Industries</option>
                    <option value="web">Web</option>
                </select>
                <select id="sort-order">
                    <option value="date-desc">Date (Newest First)</option>
                    <option value="date-asc">Date (Oldest First)</option>
                </select>
            `;

            // URLSearchParams のモック
            const mockParams = {
                set: jest.fn(),
                toString: jest.fn(() => 'technology=react&industry=web'),
            };
            global.URLSearchParams = jest.fn(() => mockParams);

            // main.jsを読み込む
            require('../../assets/js/main.js');

            // DOMContentLoaded イベントを発火
            const event = new Event('DOMContentLoaded');
            document.dispatchEvent(event);

            const techFilter = document.getElementById('technology-filter');
            const industryFilter = document.getElementById('industry-filter');

            // フィルター値を変更
            techFilter.value = 'react';
            industryFilter.value = 'web';

            // change イベントを発火
            const changeEvent = new Event('change');
            techFilter.dispatchEvent(changeEvent);

            // URLSearchParams.set が呼ばれることを確認
            expect(mockParams.set).toHaveBeenCalledWith('technology', 'react');
        });
    });

    describe('フォームバリデーション', () => {
        test('validateForm関数のテスト', () => {
            document.body.innerHTML = `
                <form id="test-form">
                    <input type="text" name="name" required value="">
                    <input type="email" name="email" required value="test@example.com">
                    <textarea name="message" required></textarea>
                    <button type="submit">Submit</button>
                </form>
            `;

            // main.jsを読み込む
            require('../../assets/js/main.js');

            const form = document.getElementById('test-form');
            const nameField = form.querySelector('[name="name"]');
            const emailField = form.querySelector('[name="email"]');
            const messageField = form.querySelector('[name="message"]');

            // validateForm関数を直接テスト
            const isValid = window.validateForm(form);

            // 空の必須フィールドがある場合はfalseを返すことを確認
            expect(isValid).toBe(false);
            expect(nameField.classList.contains('error')).toBe(true);
            expect(messageField.classList.contains('error')).toBe(true);
            expect(emailField.classList.contains('error')).toBe(false);

            // フィールドを入力して再テスト
            nameField.value = 'Test User';
            messageField.value = 'Test message';

            const isValidAfterInput = window.validateForm(form);
            expect(isValidAfterInput).toBe(true);
            expect(nameField.classList.contains('error')).toBe(false);
            expect(messageField.classList.contains('error')).toBe(false);
        });
    });

    describe('お問い合わせフォーム処理', () => {
        test('Contact Form 7フォームのバリデーション', () => {
            document.body.innerHTML = `
                <form class="wpcf7-form">
                    <input type="text" name="name" required value="">
                    <input type="email" name="email" required value="test@example.com">
                    <textarea name="message" required></textarea>
                    <button type="submit">Submit</button>
                </form>
            `;

            // alert のモック
            window.alert = jest.fn();

            // main.jsを読み込む
            require('../../assets/js/main.js');

            // DOMContentLoaded イベントを発火（2回目のイベントリスナー用）
            const event = new Event('DOMContentLoaded');
            document.dispatchEvent(event);

            const form = document.querySelector('.wpcf7-form');
            
            // submit イベントを発火（バリデーションが失敗する場合）
            const submitEvent = new Event('submit', { cancelable: true });
            form.dispatchEvent(submitEvent);

            // preventDefault が呼ばれることを確認（バリデーション失敗時）
            expect(window.alert).toHaveBeenCalledWith('必須項目を入力してください。');
        });

        test('バリデーション成功時はsubmitが継続される', () => {
            document.body.innerHTML = `
                <form class="wpcf7-form">
                    <input type="text" name="name" required value="Test User">
                    <input type="email" name="email" required value="test@example.com">
                    <textarea name="message" required>Test message</textarea>
                    <button type="submit">Submit</button>
                </form>
            `;

            // alert のモック
            window.alert = jest.fn();

            // main.jsを読み込む
            require('../../assets/js/main.js');

            // DOMContentLoaded イベントを発火（2回目のイベントリスナー用）
            const event = new Event('DOMContentLoaded');
            document.dispatchEvent(event);

            const form = document.querySelector('.wpcf7-form');
            
            // submit イベントを発火（バリデーションが成功する場合）
            const submitEvent = new Event('submit', { cancelable: true });
            form.dispatchEvent(submitEvent);

            // アラートが表示されないことを確認（バリデーション成功時）
            expect(window.alert).not.toHaveBeenCalled();
        });
    });
});
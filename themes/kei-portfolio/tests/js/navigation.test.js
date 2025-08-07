/**
 * Navigation.js のテスト
 * テスト対象: assets/js/navigation.js
 * 
 * @package Kei_Portfolio
 */

// モックの設定
global.console = {
    error: jest.fn(),
    warn: jest.fn(),
    log: jest.fn(),
};

describe('Navigation functionality', () => {
    beforeEach(() => {
        // DOMをリセット
        document.body.innerHTML = '';
        
        // window.innerWidth のモック
        Object.defineProperty(window, 'innerWidth', {
            value: 1024,
            writable: true,
        });

        // イベントリスナーをクリア
        jest.clearAllMocks();
    });

    describe('ナビゲーションの初期化', () => {
        test('必要な要素が存在しない場合は早期リターン', () => {
            document.body.innerHTML = `
                <div>No navigation elements</div>
            `;

            // navigation.jsを読み込む
            require('../../assets/js/navigation.js');

            // DOMContentLoaded イベントを発火
            const event = new Event('DOMContentLoaded');
            document.dispatchEvent(event);

            // エラーが発生しないことを確認
            expect(console.error).not.toHaveBeenCalled();
        });

        test('必要な要素が全て存在する場合の初期化', () => {
            document.body.innerHTML = `
                <button id="mobile-menu-toggle" aria-expanded="false">
                    <i id="mobile-menu-icon" class="ri-menu-line text-xl"></i>
                </button>
                <nav id="mobile-menu" class="hidden">
                    <ul>
                        <li><a href="/">Home</a></li>
                        <li><a href="/about">About</a></li>
                        <li><a href="/portfolio">Portfolio</a></li>
                    </ul>
                </nav>
            `;

            // navigation.jsを読み込む
            require('../../assets/js/navigation.js');

            // DOMContentLoaded イベントを発火
            const event = new Event('DOMContentLoaded');
            document.dispatchEvent(event);

            const toggleButton = document.getElementById('mobile-menu-toggle');
            const mobileMenu = document.getElementById('mobile-menu');
            const menuIcon = document.getElementById('mobile-menu-icon');

            expect(toggleButton).toBeTruthy();
            expect(mobileMenu).toBeTruthy();
            expect(menuIcon).toBeTruthy();
            expect(toggleButton.getAttribute('aria-expanded')).toBe('false');
            expect(mobileMenu.classList.contains('hidden')).toBe(true);
        });
    });

    describe('モバイルメニューの切り替え機能', () => {
        beforeEach(() => {
            document.body.innerHTML = `
                <button id="mobile-menu-toggle" aria-expanded="false">
                    <i id="mobile-menu-icon" class="ri-menu-line text-xl"></i>
                </button>
                <nav id="mobile-menu" class="hidden">
                    <ul>
                        <li><a href="/">Home</a></li>
                        <li><a href="/about">About</a></li>
                        <li><a href="/portfolio">Portfolio</a></li>
                    </ul>
                </nav>
            `;

            // navigation.jsを読み込む
            require('../../assets/js/navigation.js');

            // DOMContentLoaded イベントを発火
            const event = new Event('DOMContentLoaded');
            document.dispatchEvent(event);
        });

        test('メニューを開く', () => {
            const toggleButton = document.getElementById('mobile-menu-toggle');
            const mobileMenu = document.getElementById('mobile-menu');
            const menuIcon = document.getElementById('mobile-menu-icon');

            // 初期状態の確認
            expect(mobileMenu.classList.contains('hidden')).toBe(true);
            expect(toggleButton.getAttribute('aria-expanded')).toBe('false');
            expect(menuIcon.className).toBe('ri-menu-line text-xl');

            // ボタンをクリック
            toggleButton.click();

            // メニューが開かれることを確認
            expect(mobileMenu.classList.contains('hidden')).toBe(false);
            expect(toggleButton.getAttribute('aria-expanded')).toBe('true');
            expect(menuIcon.className).toBe('ri-close-line text-xl');
        });

        test('メニューを閉じる', () => {
            const toggleButton = document.getElementById('mobile-menu-toggle');
            const mobileMenu = document.getElementById('mobile-menu');
            const menuIcon = document.getElementById('mobile-menu-icon');

            // まずメニューを開く
            toggleButton.click();
            expect(mobileMenu.classList.contains('hidden')).toBe(false);

            // 再度ボタンをクリック
            toggleButton.click();

            // メニューが閉じられることを確認
            expect(mobileMenu.classList.contains('hidden')).toBe(true);
            expect(toggleButton.getAttribute('aria-expanded')).toBe('false');
            expect(menuIcon.className).toBe('ri-menu-line text-xl');
        });

        test('複数回のトグル操作', () => {
            const toggleButton = document.getElementById('mobile-menu-toggle');
            const mobileMenu = document.getElementById('mobile-menu');
            const menuIcon = document.getElementById('mobile-menu-icon');

            // 初期状態: 閉じている
            expect(mobileMenu.classList.contains('hidden')).toBe(true);

            // 1回目: 開く
            toggleButton.click();
            expect(mobileMenu.classList.contains('hidden')).toBe(false);
            expect(toggleButton.getAttribute('aria-expanded')).toBe('true');
            expect(menuIcon.className).toBe('ri-close-line text-xl');

            // 2回目: 閉じる
            toggleButton.click();
            expect(mobileMenu.classList.contains('hidden')).toBe(true);
            expect(toggleButton.getAttribute('aria-expanded')).toBe('false');
            expect(menuIcon.className).toBe('ri-menu-line text-xl');

            // 3回目: 再び開く
            toggleButton.click();
            expect(mobileMenu.classList.contains('hidden')).toBe(false);
            expect(toggleButton.getAttribute('aria-expanded')).toBe('true');
            expect(menuIcon.className).toBe('ri-close-line text-xl');
        });
    });

    describe('ウィンドウリサイズ処理', () => {
        beforeEach(() => {
            document.body.innerHTML = `
                <button id="mobile-menu-toggle" aria-expanded="false">
                    <i id="mobile-menu-icon" class="ri-menu-line text-xl"></i>
                </button>
                <nav id="mobile-menu" class="hidden">
                    <ul>
                        <li><a href="/">Home</a></li>
                        <li><a href="/about">About</a></li>
                        <li><a href="/portfolio">Portfolio</a></li>
                    </ul>
                </nav>
            `;

            // navigation.jsを読み込む
            require('../../assets/js/navigation.js');

            // DOMContentLoaded イベントを発火
            const event = new Event('DOMContentLoaded');
            document.dispatchEvent(event);
        });

        test('デスクトップサイズ（768px以上）でメニューが開いている場合、自動的に閉じる', () => {
            const toggleButton = document.getElementById('mobile-menu-toggle');
            const mobileMenu = document.getElementById('mobile-menu');
            const menuIcon = document.getElementById('mobile-menu-icon');

            // まずメニューを開く
            toggleButton.click();
            expect(mobileMenu.classList.contains('hidden')).toBe(false);
            expect(toggleButton.getAttribute('aria-expanded')).toBe('true');

            // ウィンドウサイズをデスクトップサイズに変更
            Object.defineProperty(window, 'innerWidth', {
                value: 1024,
                writable: true,
            });

            // リサイズイベントを発火
            const resizeEvent = new Event('resize');
            window.dispatchEvent(resizeEvent);

            // メニューが自動的に閉じられることを確認
            expect(mobileMenu.classList.contains('hidden')).toBe(true);
            expect(toggleButton.getAttribute('aria-expanded')).toBe('false');
            expect(menuIcon.className).toBe('ri-menu-line text-xl');
        });

        test('モバイルサイズ（768px未満）ではリサイズ時にメニューが閉じられない', () => {
            const toggleButton = document.getElementById('mobile-menu-toggle');
            const mobileMenu = document.getElementById('mobile-menu');

            // まずメニューを開く
            toggleButton.click();
            expect(mobileMenu.classList.contains('hidden')).toBe(false);

            // ウィンドウサイズをモバイルサイズに変更
            Object.defineProperty(window, 'innerWidth', {
                value: 600,
                writable: true,
            });

            // リサイズイベントを発火
            const resizeEvent = new Event('resize');
            window.dispatchEvent(resizeEvent);

            // メニューは開いたままであることを確認
            expect(mobileMenu.classList.contains('hidden')).toBe(false);
            expect(toggleButton.getAttribute('aria-expanded')).toBe('true');
        });

        test('メニューが閉じている場合はリサイズ時に何も起こらない', () => {
            const toggleButton = document.getElementById('mobile-menu-toggle');
            const mobileMenu = document.getElementById('mobile-menu');
            const menuIcon = document.getElementById('mobile-menu-icon');

            // メニューは初期状態で閉じている
            expect(mobileMenu.classList.contains('hidden')).toBe(true);

            // ウィンドウサイズをデスクトップサイズに変更
            Object.defineProperty(window, 'innerWidth', {
                value: 1024,
                writable: true,
            });

            // リサイズイベントを発火
            const resizeEvent = new Event('resize');
            window.dispatchEvent(resizeEvent);

            // 状態が変わらないことを確認
            expect(mobileMenu.classList.contains('hidden')).toBe(true);
            expect(toggleButton.getAttribute('aria-expanded')).toBe('false');
            expect(menuIcon.className).toBe('ri-menu-line text-xl');
        });
    });

    describe('モバイルメニューのリンククリック処理', () => {
        beforeEach(() => {
            document.body.innerHTML = `
                <button id="mobile-menu-toggle" aria-expanded="false">
                    <i id="mobile-menu-icon" class="ri-menu-line text-xl"></i>
                </button>
                <nav id="mobile-menu" class="hidden">
                    <ul>
                        <li><a href="/" class="home-link">Home</a></li>
                        <li><a href="/about" class="about-link">About</a></li>
                        <li><a href="/portfolio" class="portfolio-link">Portfolio</a></li>
                    </ul>
                </nav>
            `;

            // navigation.jsを読み込む
            require('../../assets/js/navigation.js');

            // DOMContentLoaded イベントを発火
            const event = new Event('DOMContentLoaded');
            document.dispatchEvent(event);
        });

        test('メニューが開いているときにリンクをクリックするとメニューが閉じる', () => {
            const toggleButton = document.getElementById('mobile-menu-toggle');
            const mobileMenu = document.getElementById('mobile-menu');
            const menuIcon = document.getElementById('mobile-menu-icon');
            const homeLink = document.querySelector('.home-link');

            // まずメニューを開く
            toggleButton.click();
            expect(mobileMenu.classList.contains('hidden')).toBe(false);
            expect(toggleButton.getAttribute('aria-expanded')).toBe('true');
            expect(menuIcon.className).toBe('ri-close-line text-xl');

            // リンクをクリック
            homeLink.click();

            // メニューが閉じられることを確認
            expect(mobileMenu.classList.contains('hidden')).toBe(true);
            expect(toggleButton.getAttribute('aria-expanded')).toBe('false');
            expect(menuIcon.className).toBe('ri-menu-line text-xl');
        });

        test('メニューが閉じているときにリンクをクリックしても状態は変わらない', () => {
            const toggleButton = document.getElementById('mobile-menu-toggle');
            const mobileMenu = document.getElementById('mobile-menu');
            const menuIcon = document.getElementById('mobile-menu-icon');
            const aboutLink = document.querySelector('.about-link');

            // メニューは初期状態で閉じている
            expect(mobileMenu.classList.contains('hidden')).toBe(true);

            // リンクをクリック
            aboutLink.click();

            // 状態が変わらないことを確認
            expect(mobileMenu.classList.contains('hidden')).toBe(true);
            expect(toggleButton.getAttribute('aria-expanded')).toBe('false');
            expect(menuIcon.className).toBe('ri-menu-line text-xl');
        });

        test('複数のリンクがそれぞれ正しく動作する', () => {
            const toggleButton = document.getElementById('mobile-menu-toggle');
            const mobileMenu = document.getElementById('mobile-menu');
            const homeLink = document.querySelector('.home-link');
            const aboutLink = document.querySelector('.about-link');
            const portfolioLink = document.querySelector('.portfolio-link');

            // メニューを開く
            toggleButton.click();
            expect(mobileMenu.classList.contains('hidden')).toBe(false);

            // Homeリンクをクリック
            homeLink.click();
            expect(mobileMenu.classList.contains('hidden')).toBe(true);

            // 再度メニューを開く
            toggleButton.click();
            expect(mobileMenu.classList.contains('hidden')).toBe(false);

            // Aboutリンクをクリック
            aboutLink.click();
            expect(mobileMenu.classList.contains('hidden')).toBe(true);

            // 再度メニューを開く
            toggleButton.click();
            expect(mobileMenu.classList.contains('hidden')).toBe(false);

            // Portfolioリンクをクリック
            portfolioLink.click();
            expect(mobileMenu.classList.contains('hidden')).toBe(true);
        });
    });

    describe('アクセシビリティ', () => {
        beforeEach(() => {
            document.body.innerHTML = `
                <button id="mobile-menu-toggle" aria-expanded="false">
                    <i id="mobile-menu-icon" class="ri-menu-line text-xl"></i>
                </button>
                <nav id="mobile-menu" class="hidden">
                    <ul>
                        <li><a href="/">Home</a></li>
                        <li><a href="/about">About</a></li>
                        <li><a href="/portfolio">Portfolio</a></li>
                    </ul>
                </nav>
            `;

            // navigation.jsを読み込む
            require('../../assets/js/navigation.js');

            // DOMContentLoaded イベントを発火
            const event = new Event('DOMContentLoaded');
            document.dispatchEvent(event);
        });

        test('aria-expanded属性が正しく更新される', () => {
            const toggleButton = document.getElementById('mobile-menu-toggle');

            // 初期状態
            expect(toggleButton.getAttribute('aria-expanded')).toBe('false');

            // メニューを開く
            toggleButton.click();
            expect(toggleButton.getAttribute('aria-expanded')).toBe('true');

            // メニューを閉じる
            toggleButton.click();
            expect(toggleButton.getAttribute('aria-expanded')).toBe('false');

            // 再度開く
            toggleButton.click();
            expect(toggleButton.getAttribute('aria-expanded')).toBe('true');
        });

        test('リサイズ時にaria-expanded属性も正しく更新される', () => {
            const toggleButton = document.getElementById('mobile-menu-toggle');

            // メニューを開く
            toggleButton.click();
            expect(toggleButton.getAttribute('aria-expanded')).toBe('true');

            // デスクトップサイズにリサイズ
            Object.defineProperty(window, 'innerWidth', {
                value: 1024,
                writable: true,
            });

            const resizeEvent = new Event('resize');
            window.dispatchEvent(resizeEvent);

            // aria-expanded属性が正しく更新されることを確認
            expect(toggleButton.getAttribute('aria-expanded')).toBe('false');
        });
    });
});
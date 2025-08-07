/**
 * Portfolio Filter のテスト
 * テスト対象: assets/js/portfolio-filter.js
 * 
 * @package Kei_Portfolio
 */

// モックの設定
global.console = {
    error: jest.fn(),
    warn: jest.fn(),
    log: jest.fn(),
};

describe('Portfolio filter functionality', () => {
    beforeEach(() => {
        // DOMをリセット
        document.body.innerHTML = '';
        
        // イベントリスナーをクリア
        jest.clearAllMocks();
    });

    describe('フィルター機能の初期化', () => {
        test('フィルターボタンとプロジェクトカードが存在しない場合', () => {
            document.body.innerHTML = `
                <div>No filter elements</div>
            `;

            // portfolio-filter.jsを読み込む
            require('../../assets/js/portfolio-filter.js');

            // DOMContentLoaded イベントを発火
            const event = new Event('DOMContentLoaded');
            document.dispatchEvent(event);

            // エラーが発生しないことを確認
            expect(console.error).not.toHaveBeenCalled();
        });

        test('フィルターボタンとプロジェクトカードが存在する場合の初期化', () => {
            document.body.innerHTML = `
                <div class="filter-container">
                    <button class="category-filter-btn bg-blue-600 text-white" data-category="all">All</button>
                    <button class="category-filter-btn bg-white text-gray-600 hover:bg-gray-100" data-category="web">Web</button>
                    <button class="category-filter-btn bg-white text-gray-600 hover:bg-gray-100" data-category="mobile">Mobile</button>
                </div>
                <div class="projects-container">
                    <div class="project-card" data-category="web" style="display: block;">Web Project 1</div>
                    <div class="project-card" data-category="mobile" style="display: block;">Mobile Project 1</div>
                    <div class="project-card" data-category="web" style="display: block;">Web Project 2</div>
                </div>
                <div id="no-results" class="hidden">No projects found</div>
            `;

            // portfolio-filter.jsを読み込む
            require('../../assets/js/portfolio-filter.js');

            // DOMContentLoaded イベントを発火
            const event = new Event('DOMContentLoaded');
            document.dispatchEvent(event);

            const filterButtons = document.querySelectorAll('.category-filter-btn');
            const projectCards = document.querySelectorAll('.project-card');
            const noResults = document.getElementById('no-results');

            expect(filterButtons).toHaveLength(3);
            expect(projectCards).toHaveLength(3);
            expect(noResults).toBeTruthy();
        });
    });

    describe('フィルター機能', () => {
        beforeEach(() => {
            document.body.innerHTML = `
                <div class="filter-container">
                    <button class="category-filter-btn bg-blue-600 text-white" data-category="all">All</button>
                    <button class="category-filter-btn bg-white text-gray-600 hover:bg-gray-100" data-category="web">Web</button>
                    <button class="category-filter-btn bg-white text-gray-600 hover:bg-gray-100" data-category="mobile">Mobile</button>
                    <button class="category-filter-btn bg-white text-gray-600 hover:bg-gray-100" data-category="design">Design</button>
                </div>
                <div class="projects-container">
                    <div class="project-card" data-category="web" style="display: block;">Web Project 1</div>
                    <div class="project-card" data-category="mobile" style="display: block;">Mobile Project 1</div>
                    <div class="project-card" data-category="web" style="display: block;">Web Project 2</div>
                    <div class="project-card" data-category="design" style="display: block;">Design Project 1</div>
                    <div class="project-card" data-category="mobile" style="display: block;">Mobile Project 2</div>
                </div>
                <div id="no-results" class="hidden">No projects found</div>
            `;

            // portfolio-filter.jsを読み込む
            require('../../assets/js/portfolio-filter.js');

            // DOMContentLoaded イベントを発火
            const event = new Event('DOMContentLoaded');
            document.dispatchEvent(event);
        });

        test('Allフィルター: 全てのプロジェクトが表示される', () => {
            const allButton = document.querySelector('[data-category="all"]');
            const projectCards = document.querySelectorAll('.project-card');
            const noResults = document.getElementById('no-results');

            // Allボタンをクリック
            allButton.click();

            // 全てのプロジェクトカードが表示されることを確認
            projectCards.forEach(card => {
                expect(card.style.display).toBe('block');
            });

            // "No results"メッセージが非表示であることを確認
            expect(noResults.classList.contains('hidden')).toBe(true);
        });

        test('Webフィルター: Webプロジェクトのみ表示される', () => {
            const webButton = document.querySelector('[data-category="web"]');
            const projectCards = document.querySelectorAll('.project-card');
            const noResults = document.getElementById('no-results');

            // Webボタンをクリック
            webButton.click();

            // Webプロジェクトのみ表示されることを確認
            projectCards.forEach(card => {
                if (card.dataset.category === 'web') {
                    expect(card.style.display).toBe('block');
                } else {
                    expect(card.style.display).toBe('none');
                }
            });

            // "No results"メッセージが非表示であることを確認
            expect(noResults.classList.contains('hidden')).toBe(true);
        });

        test('Mobileフィルター: Mobileプロジェクトのみ表示される', () => {
            const mobileButton = document.querySelector('[data-category="mobile"]');
            const projectCards = document.querySelectorAll('.project-card');
            const noResults = document.getElementById('no-results');

            // Mobileボタンをクリック
            mobileButton.click();

            // Mobileプロジェクトのみ表示されることを確認
            projectCards.forEach(card => {
                if (card.dataset.category === 'mobile') {
                    expect(card.style.display).toBe('block');
                } else {
                    expect(card.style.display).toBe('none');
                }
            });

            // "No results"メッセージが非表示であることを確認
            expect(noResults.classList.contains('hidden')).toBe(true);
        });

        test('Designフィルター: Designプロジェクトのみ表示される', () => {
            const designButton = document.querySelector('[data-category="design"]');
            const projectCards = document.querySelectorAll('.project-card');
            const noResults = document.getElementById('no-results');

            // Designボタンをクリック
            designButton.click();

            // Designプロジェクトのみ表示されることを確認
            projectCards.forEach(card => {
                if (card.dataset.category === 'design') {
                    expect(card.style.display).toBe('block');
                } else {
                    expect(card.style.display).toBe('none');
                }
            });

            // "No results"メッセージが非表示であることを確認
            expect(noResults.classList.contains('hidden')).toBe(true);
        });
    });

    describe('ボタンの状態管理', () => {
        beforeEach(() => {
            document.body.innerHTML = `
                <div class="filter-container">
                    <button class="category-filter-btn bg-blue-600 text-white" data-category="all">All</button>
                    <button class="category-filter-btn bg-white text-gray-600 hover:bg-gray-100" data-category="web">Web</button>
                    <button class="category-filter-btn bg-white text-gray-600 hover:bg-gray-100" data-category="mobile">Mobile</button>
                </div>
                <div class="projects-container">
                    <div class="project-card" data-category="web">Web Project 1</div>
                    <div class="project-card" data-category="mobile">Mobile Project 1</div>
                </div>
                <div id="no-results" class="hidden">No projects found</div>
            `;

            // portfolio-filter.jsを読み込む
            require('../../assets/js/portfolio-filter.js');

            // DOMContentLoaded イベントを発火
            const event = new Event('DOMContentLoaded');
            document.dispatchEvent(event);
        });

        test('ボタンクリック時のアクティブ状態変更', () => {
            const allButton = document.querySelector('[data-category="all"]');
            const webButton = document.querySelector('[data-category="web"]');
            const mobileButton = document.querySelector('[data-category="mobile"]');

            // 初期状態: Allボタンがアクティブ
            expect(allButton.classList.contains('bg-blue-600')).toBe(true);
            expect(allButton.classList.contains('text-white')).toBe(true);
            expect(webButton.classList.contains('bg-white')).toBe(true);
            expect(webButton.classList.contains('text-gray-600')).toBe(true);

            // Webボタンをクリック
            webButton.click();

            // Webボタンがアクティブになり、他のボタンが非アクティブになることを確認
            expect(webButton.classList.contains('bg-blue-600')).toBe(true);
            expect(webButton.classList.contains('text-white')).toBe(true);
            expect(webButton.classList.contains('bg-white')).toBe(false);
            expect(webButton.classList.contains('text-gray-600')).toBe(false);

            expect(allButton.classList.contains('bg-white')).toBe(true);
            expect(allButton.classList.contains('text-gray-600')).toBe(true);
            expect(allButton.classList.contains('bg-blue-600')).toBe(false);
            expect(allButton.classList.contains('text-white')).toBe(false);

            expect(mobileButton.classList.contains('bg-white')).toBe(true);
            expect(mobileButton.classList.contains('text-gray-600')).toBe(true);

            // Mobileボタンをクリック
            mobileButton.click();

            // Mobileボタンがアクティブになり、他のボタンが非アクティブになることを確認
            expect(mobileButton.classList.contains('bg-blue-600')).toBe(true);
            expect(mobileButton.classList.contains('text-white')).toBe(true);
            expect(mobileButton.classList.contains('bg-white')).toBe(false);
            expect(mobileButton.classList.contains('text-gray-600')).toBe(false);

            expect(webButton.classList.contains('bg-white')).toBe(true);
            expect(webButton.classList.contains('text-gray-600')).toBe(true);
            expect(webButton.classList.contains('bg-blue-600')).toBe(false);
            expect(webButton.classList.contains('text-white')).toBe(false);
        });
    });

    describe('結果なしメッセージの表示', () => {
        test('マッチするプロジェクトがない場合は結果なしメッセージを表示', () => {
            document.body.innerHTML = `
                <div class="filter-container">
                    <button class="category-filter-btn bg-blue-600 text-white" data-category="all">All</button>
                    <button class="category-filter-btn bg-white text-gray-600 hover:bg-gray-100" data-category="backend">Backend</button>
                </div>
                <div class="projects-container">
                    <div class="project-card" data-category="web">Web Project 1</div>
                    <div class="project-card" data-category="mobile">Mobile Project 1</div>
                </div>
                <div id="no-results" class="hidden">No projects found</div>
            `;

            // portfolio-filter.jsを読み込む
            require('../../assets/js/portfolio-filter.js');

            // DOMContentLoaded イベントを発火
            const event = new Event('DOMContentLoaded');
            document.dispatchEvent(event);

            const backendButton = document.querySelector('[data-category="backend"]');
            const projectCards = document.querySelectorAll('.project-card');
            const noResults = document.getElementById('no-results');

            // Backendボタンをクリック（該当するプロジェクトがない）
            backendButton.click();

            // 全てのプロジェクトが非表示になることを確認
            projectCards.forEach(card => {
                expect(card.style.display).toBe('none');
            });

            // "No results"メッセージが表示されることを確認
            expect(noResults.classList.contains('hidden')).toBe(false);
        });

        test('マッチするプロジェクトがある場合は結果なしメッセージを非表示', () => {
            document.body.innerHTML = `
                <div class="filter-container">
                    <button class="category-filter-btn bg-blue-600 text-white" data-category="all">All</button>
                    <button class="category-filter-btn bg-white text-gray-600 hover:bg-gray-100" data-category="web">Web</button>
                </div>
                <div class="projects-container">
                    <div class="project-card" data-category="web">Web Project 1</div>
                    <div class="project-card" data-category="mobile">Mobile Project 1</div>
                </div>
                <div id="no-results" class="">No projects found</div>
            `;

            // portfolio-filter.jsを読み込む
            require('../../assets/js/portfolio-filter.js');

            // DOMContentLoaded イベントを発火
            const event = new Event('DOMContentLoaded');
            document.dispatchEvent(event);

            const webButton = document.querySelector('[data-category="web"]');
            const noResults = document.getElementById('no-results');

            // Webボタンをクリック（該当するプロジェクトがある）
            webButton.click();

            // "No results"メッセージが非表示になることを確認
            expect(noResults.classList.contains('hidden')).toBe(true);
        });
    });

    describe('複数のフィルター操作', () => {
        beforeEach(() => {
            document.body.innerHTML = `
                <div class="filter-container">
                    <button class="category-filter-btn bg-blue-600 text-white" data-category="all">All</button>
                    <button class="category-filter-btn bg-white text-gray-600 hover:bg-gray-100" data-category="web">Web</button>
                    <button class="category-filter-btn bg-white text-gray-600 hover:bg-gray-100" data-category="mobile">Mobile</button>
                    <button class="category-filter-btn bg-white text-gray-600 hover:bg-gray-100" data-category="design">Design</button>
                </div>
                <div class="projects-container">
                    <div class="project-card" data-category="web">Web Project 1</div>
                    <div class="project-card" data-category="mobile">Mobile Project 1</div>
                    <div class="project-card" data-category="web">Web Project 2</div>
                    <div class="project-card" data-category="design">Design Project 1</div>
                </div>
                <div id="no-results" class="hidden">No projects found</div>
            `;

            // portfolio-filter.jsを読み込む
            require('../../assets/js/portfolio-filter.js');

            // DOMContentLoaded イベントを発火
            const event = new Event('DOMContentLoaded');
            document.dispatchEvent(event);
        });

        test('複数回のフィルター切り替え', () => {
            const allButton = document.querySelector('[data-category="all"]');
            const webButton = document.querySelector('[data-category="web"]');
            const mobileButton = document.querySelector('[data-category="mobile"]');
            const designButton = document.querySelector('[data-category="design"]');
            const projectCards = document.querySelectorAll('.project-card');

            // Web → Mobile → Design → All の順でフィルターを切り替え

            // 1. Webフィルター
            webButton.click();
            projectCards.forEach(card => {
                if (card.dataset.category === 'web') {
                    expect(card.style.display).toBe('block');
                } else {
                    expect(card.style.display).toBe('none');
                }
            });

            // 2. Mobileフィルター
            mobileButton.click();
            projectCards.forEach(card => {
                if (card.dataset.category === 'mobile') {
                    expect(card.style.display).toBe('block');
                } else {
                    expect(card.style.display).toBe('none');
                }
            });

            // 3. Designフィルター
            designButton.click();
            projectCards.forEach(card => {
                if (card.dataset.category === 'design') {
                    expect(card.style.display).toBe('block');
                } else {
                    expect(card.style.display).toBe('none');
                }
            });

            // 4. Allフィルター
            allButton.click();
            projectCards.forEach(card => {
                expect(card.style.display).toBe('block');
            });
        });
    });

    describe('エッジケース', () => {
        test('data-category属性がないプロジェクトカード', () => {
            document.body.innerHTML = `
                <div class="filter-container">
                    <button class="category-filter-btn bg-blue-600 text-white" data-category="all">All</button>
                    <button class="category-filter-btn bg-white text-gray-600 hover:bg-gray-100" data-category="web">Web</button>
                </div>
                <div class="projects-container">
                    <div class="project-card" data-category="web">Web Project 1</div>
                    <div class="project-card">Project without category</div>
                </div>
                <div id="no-results" class="hidden">No projects found</div>
            `;

            // portfolio-filter.jsを読み込む
            require('../../assets/js/portfolio-filter.js');

            // DOMContentLoaded イベントを発火
            const event = new Event('DOMContentLoaded');
            document.dispatchEvent(event);

            const webButton = document.querySelector('[data-category="web"]');
            const projectCards = document.querySelectorAll('.project-card');

            // Webフィルターをクリック
            webButton.click();

            // data-category属性がないカードは非表示になることを確認
            expect(projectCards[0].style.display).toBe('block'); // Web project
            expect(projectCards[1].style.display).toBe('none');  // No category project
        });

        test('no-results要素が存在しない場合', () => {
            document.body.innerHTML = `
                <div class="filter-container">
                    <button class="category-filter-btn bg-blue-600 text-white" data-category="all">All</button>
                    <button class="category-filter-btn bg-white text-gray-600 hover:bg-gray-100" data-category="backend">Backend</button>
                </div>
                <div class="projects-container">
                    <div class="project-card" data-category="web">Web Project 1</div>
                </div>
            `;

            // portfolio-filter.jsを読み込む
            require('../../assets/js/portfolio-filter.js');

            // DOMContentLoaded イベントを発火
            const event = new Event('DOMContentLoaded');
            document.dispatchEvent(event);

            const backendButton = document.querySelector('[data-category="backend"]');

            // エラーが発生しないことを確認
            expect(() => {
                backendButton.click();
            }).not.toThrow();
        });
    });
});
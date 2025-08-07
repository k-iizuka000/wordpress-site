# WordPress テーマ kei-portfolio テスト設計書

## 作成情報
- 作成日: 2025年8月7日
- 対象テーマ: kei-portfolio
- バージョン: 1.0.0
- 作成者: Claude Code

## 1. テスト概要

### 1.1 テスト目的
WordPressテーマ「kei-portfolio」の品質保証と信頼性向上のため、包括的な自動テストと手動テストを実施する。

### 1.2 テスト範囲
- PHPファイルの構文と機能
- JavaScript/CSSの品質と動作
- WordPressテーマ機能の正常動作
- レスポンシブデザイン
- セキュリティ
- パフォーマンス
- アクセシビリティ

### 1.3 利用ツール
- **PHP**: PHPUnit, PHPStan, PHP CodeSniffer (WPCS)
- **JavaScript**: Jest, ESLint (@wordpress/eslint-plugin)
- **CSS**: Stylelint (@wordpress/stylelint-config)
- **パフォーマンス**: Lighthouse
- **アクセシビリティ**: axe-core, pa11y
- **WordPress**: WP-CLI, WordPress Test Suite

## 2. 並列実行可能なテストグループ設計

### グループ1: PHP構文チェック関連
**独立性**: 完全独立（静的解析のみ）
**並列実行**: 可能
**実行時間目安**: 30秒

#### テストケース
```bash
# 1.1 PHP構文チェック（全PHPファイル）
find . -name "*.php" -exec php -l {} \;

# 1.2 WordPress Coding Standards準拠チェック
composer run lint:php

# 1.3 PHPStan静的解析（レベル6）
composer run analyze
```

#### 対象ファイル
- functions.php
- header.php, footer.php
- index.php, front-page.php
- 404.php, archive-project.php, single-project.php
- inc/*.php (全7ファイル)
- page-templates/*.php (全5ファイル)
- template-parts/**/*.php (全15ファイル)

### グループ2: WordPress基本機能テスト
**独立性**: DB接続必要
**並列実行**: 可能（専用DBインスタンス使用）
**実行時間目安**: 2分

#### テストケース
```php
// tests/test-theme-setup.php
class TestThemeSetup extends WP_UnitTestCase {
    public function test_theme_support() {
        // 2.1 テーマサポート機能の確認
        $this->assertTrue(current_theme_supports('post-thumbnails'));
        $this->assertTrue(current_theme_supports('custom-logo'));
        $this->assertTrue(current_theme_supports('title-tag'));
    }
    
    public function test_menus_registered() {
        // 2.2 メニュー登録の確認
        $menus = get_registered_nav_menus();
        $this->assertArrayHasKey('primary', $menus);
        $this->assertArrayHasKey('footer', $menus);
    }
    
    public function test_widgets_registered() {
        // 2.3 ウィジェットエリアの確認
        global $wp_registered_sidebars;
        $this->assertArrayHasKey('sidebar-1', $wp_registered_sidebars);
    }
}
```

#### 対象機能
- functions.php
- inc/setup.php
- inc/widgets.php
- inc/customizer.php

### グループ3: ページテンプレートテスト（前半）
**独立性**: DB接続必要、テストデータ依存
**並列実行**: 可能
**実行時間目安**: 3分

#### テストケース
```php
// tests/test-page-templates-1.php
class TestPageTemplates1 extends WP_UnitTestCase {
    public function test_front_page_rendering() {
        // 3.1 フロントページの表示確認
        $this->go_to('/');
        $this->assertTrue(is_front_page());
        ob_start();
        include(get_template_directory() . '/front-page.php');
        $output = ob_get_clean();
        $this->assertStringContainsString('hero-section', $output);
    }
    
    public function test_about_page_template() {
        // 3.2 Aboutページテンプレートの確認
        $page_id = $this->factory->post->create([
            'post_type' => 'page',
            'post_status' => 'publish',
            'page_template' => 'page-templates/page-about.php'
        ]);
        $this->go_to(get_permalink($page_id));
        $this->assertTrue(is_page_template('page-templates/page-about.php'));
    }
    
    public function test_portfolio_page_template() {
        // 3.3 Portfolioページテンプレートの確認
        $page_id = $this->factory->post->create([
            'post_type' => 'page',
            'post_status' => 'publish',
            'page_template' => 'page-templates/page-portfolio.php'
        ]);
        $this->go_to(get_permalink($page_id));
        $this->assertTrue(is_page_template('page-templates/page-portfolio.php'));
    }
}
```

#### 対象ファイル
- front-page.php
- page-templates/page-about.php
- page-templates/page-portfolio.php
- 関連template-parts

### グループ4: ページテンプレートテスト（後半）
**独立性**: DB接続必要、テストデータ依存
**並列実行**: 可能
**実行時間目安**: 3分

#### テストケース
```php
// tests/test-page-templates-2.php
class TestPageTemplates2 extends WP_UnitTestCase {
    public function test_skills_page_template() {
        // 4.1 Skillsページテンプレートの確認
        $page_id = $this->factory->post->create([
            'post_type' => 'page',
            'post_status' => 'publish',
            'page_template' => 'page-templates/page-skills.php'
        ]);
        $this->go_to(get_permalink($page_id));
        $this->assertTrue(is_page_template('page-templates/page-skills.php'));
    }
    
    public function test_contact_page_template() {
        // 4.2 Contactページテンプレートの確認
        $page_id = $this->factory->post->create([
            'post_type' => 'page',
            'post_status' => 'publish',
            'page_template' => 'page-templates/page-contact.php'
        ]);
        $this->go_to(get_permalink($page_id));
        $this->assertTrue(is_page_template('page-templates/page-contact.php'));
    }
    
    public function test_single_project_template() {
        // 4.3 プロジェクト詳細ページの確認
        $project_id = $this->factory->post->create([
            'post_type' => 'project',
            'post_status' => 'publish'
        ]);
        $this->go_to(get_permalink($project_id));
        $this->assertTrue(is_singular('project'));
    }
}
```

#### 対象ファイル
- page-templates/page-skills.php
- page-templates/page-contact.php
- single-project.php
- 404.php

### グループ5: カスタム投稿タイプとアーカイブ関連
**独立性**: DB接続必要
**並列実行**: 可能
**実行時間目安**: 2分

#### テストケース
```php
// tests/test-custom-post-types.php
class TestCustomPostTypes extends WP_UnitTestCase {
    public function test_project_post_type_exists() {
        // 5.1 カスタム投稿タイプ'project'の存在確認
        $this->assertTrue(post_type_exists('project'));
    }
    
    public function test_project_supports() {
        // 5.2 プロジェクト投稿タイプのサポート機能確認
        $supports = get_post_type_object('project')->supports;
        $this->assertContains('title', $supports);
        $this->assertContains('editor', $supports);
        $this->assertContains('thumbnail', $supports);
    }
    
    public function test_project_archive() {
        // 5.3 プロジェクトアーカイブページの確認
        $this->factory->post->create_many(5, [
            'post_type' => 'project',
            'post_status' => 'publish'
        ]);
        $this->go_to(get_post_type_archive_link('project'));
        $this->assertTrue(is_post_type_archive('project'));
    }
    
    public function test_project_taxonomies() {
        // 5.4 プロジェクトタクソノミーの確認
        $taxonomies = get_object_taxonomies('project');
        $this->assertContains('project_category', $taxonomies);
    }
}
```

#### 対象ファイル
- inc/post-types.php
- archive-project.php
- single-project.php

### グループ6: JavaScript機能テスト（ナビゲーション系）
**独立性**: 完全独立
**並列実行**: 可能
**実行時間目安**: 1分

#### テストケース
```javascript
// tests/js/test-navigation.js
describe('Navigation functionality', () => {
    // 6.1 メインナビゲーションの初期化テスト
    test('Main navigation initialization', () => {
        document.body.innerHTML = `
            <nav class="main-navigation">
                <button class="menu-toggle">Menu</button>
                <ul class="menu">
                    <li><a href="#">Home</a></li>
                    <li><a href="#">About</a></li>
                </ul>
            </nav>
        `;
        require('../../assets/js/navigation.js');
        expect(document.querySelector('.menu-toggle')).toBeTruthy();
    });
    
    // 6.2 モバイルメニューのトグル機能
    test('Mobile menu toggle', () => {
        const button = document.querySelector('.menu-toggle');
        button.click();
        expect(document.body.classList.contains('menu-open')).toBe(true);
    });
});

// tests/js/test-main.js
describe('Main.js functionality', () => {
    // 6.3 スムーズスクロール機能
    test('Smooth scroll initialization', () => {
        document.body.innerHTML = `
            <a href="#section" class="smooth-scroll">Link</a>
            <section id="section">Content</section>
        `;
        require('../../assets/js/main.js');
        const link = document.querySelector('.smooth-scroll');
        expect(link.onclick).toBeDefined();
    });
});

// tests/js/test-portfolio-filter.js
describe('Portfolio filter', () => {
    // 6.4 ポートフォリオフィルター機能
    test('Filter buttons functionality', () => {
        document.body.innerHTML = `
            <div class="filter-buttons">
                <button data-filter="all">All</button>
                <button data-filter="web">Web</button>
            </div>
            <div class="portfolio-items">
                <div class="item" data-category="web">Web Project</div>
                <div class="item" data-category="mobile">Mobile Project</div>
            </div>
        `;
        require('../../assets/js/portfolio-filter.js');
        const webButton = document.querySelector('[data-filter="web"]');
        webButton.click();
        const webItem = document.querySelector('[data-category="web"]');
        expect(webItem.style.display).not.toBe('none');
    });
});
```

#### 対象ファイル
- assets/js/main.js
- assets/js/navigation.js
- assets/js/portfolio-filter.js

### グループ7: JavaScript機能テスト（フォーム・インタラクション系）
**独立性**: 完全独立
**並列実行**: 可能
**実行時間目安**: 1分

#### テストケース
```javascript
// tests/js/test-contact-form.js
describe('Contact form', () => {
    // 7.1 フォームバリデーション
    test('Form validation', () => {
        document.body.innerHTML = `
            <form id="contact-form">
                <input type="text" name="name" required>
                <input type="email" name="email" required>
                <textarea name="message" required></textarea>
                <button type="submit">Send</button>
            </form>
        `;
        require('../../assets/js/contact-form.js');
        const form = document.getElementById('contact-form');
        const event = new Event('submit');
        form.dispatchEvent(event);
        expect(event.defaultPrevented).toBe(true);
    });
    
    // 7.2 AJAX送信機能
    test('AJAX submission', async () => {
        global.fetch = jest.fn(() =>
            Promise.resolve({
                ok: true,
                json: () => Promise.resolve({ success: true })
            })
        );
        
        const form = document.getElementById('contact-form');
        form.querySelector('[name="name"]').value = 'Test User';
        form.querySelector('[name="email"]').value = 'test@example.com';
        form.querySelector('[name="message"]').value = 'Test message';
        
        const event = new Event('submit');
        await form.dispatchEvent(event);
        expect(fetch).toHaveBeenCalled();
    });
});

// tests/js/test-technical-approach.js
describe('Technical approach interactions', () => {
    // 7.3 技術アプローチセクションのインタラクション
    test('Tab switching functionality', () => {
        document.body.innerHTML = `
            <div class="tech-tabs">
                <button class="tab-button" data-tab="frontend">Frontend</button>
                <button class="tab-button" data-tab="backend">Backend</button>
                <div class="tab-content" data-content="frontend">Frontend content</div>
                <div class="tab-content" data-content="backend" style="display:none;">Backend content</div>
            </div>
        `;
        require('../../assets/js/technical-approach.js');
        const backendTab = document.querySelector('[data-tab="backend"]');
        backendTab.click();
        const backendContent = document.querySelector('[data-content="backend"]');
        expect(backendContent.style.display).not.toBe('none');
    });
});
```

#### 対象ファイル
- assets/js/contact-form.js
- assets/js/technical-approach.js

### グループ8: CSS/スタイリングテスト
**独立性**: 完全独立
**並列実行**: 可能
**実行時間目安**: 30秒

#### テストケース
```bash
# 8.1 Stylelintによる CSS/SCSS 品質チェック
npm run lint:css

# 8.2 CSS構文チェック
find . -name "*.css" -exec npx stylelint {} \;

# 8.3 未使用CSSの検出
npx purgecss --css style.css --content '**/*.php' --output coverage/unused-css.json
```

#### チェック項目
- WordPress CSS コーディング規約準拠
- ベンダープレフィックスの適切な使用
- 重複ルールの検出
- カラーフォーマットの統一性
- セレクターの複雑性
- !important の使用箇所

### グループ9: AJAX/API通信テスト
**独立性**: DB接続必要、モックサーバー使用可
**並列実行**: 可能
**実行時間目安**: 2分

#### テストケース
```php
// tests/test-ajax-handlers.php
class TestAjaxHandlers extends WP_Ajax_UnitTestCase {
    public function test_contact_form_submission() {
        // 9.1 お問い合わせフォームのAJAX送信
        $_POST = [
            'action' => 'submit_contact_form',
            'nonce' => wp_create_nonce('contact_form_nonce'),
            'name' => 'Test User',
            'email' => 'test@example.com',
            'message' => 'Test message'
        ];
        
        try {
            $this->_handleAjax('submit_contact_form');
        } catch (WPAjaxDieContinueException $e) {
            // Expected
        }
        
        $response = json_decode($this->_last_response, true);
        $this->assertTrue($response['success']);
    }
    
    public function test_portfolio_filter_ajax() {
        // 9.2 ポートフォリオフィルターのAJAX処理
        $_POST = [
            'action' => 'filter_portfolio',
            'nonce' => wp_create_nonce('portfolio_filter_nonce'),
            'category' => 'web'
        ];
        
        try {
            $this->_handleAjax('filter_portfolio');
        } catch (WPAjaxDieContinueException $e) {
            // Expected
        }
        
        $response = json_decode($this->_last_response, true);
        $this->assertArrayHasKey('html', $response);
    }
    
    public function test_load_more_projects() {
        // 9.3 プロジェクトの追加読み込み
        $_POST = [
            'action' => 'load_more_projects',
            'nonce' => wp_create_nonce('load_more_nonce'),
            'page' => 2,
            'per_page' => 6
        ];
        
        try {
            $this->_handleAjax('load_more_projects');
        } catch (WPAjaxDieContinueException $e) {
            // Expected
        }
        
        $response = json_decode($this->_last_response, true);
        $this->assertArrayHasKey('posts', $response);
    }
}
```

#### 対象ファイル
- inc/ajax-handlers.php
- assets/js/contact-form.js
- assets/js/portfolio-filter.js

### グループ10: セキュリティとパフォーマンステスト
**独立性**: 一部DB接続必要
**並列実行**: 可能（リソース制限あり）
**実行時間目安**: 5分

#### セキュリティテストケース
```php
// tests/test-security.php
class TestSecurity extends WP_UnitTestCase {
    public function test_nonce_verification() {
        // 10.1 Nonce検証の確認
        $forms = [
            'contact_form' => 'contact_form_nonce',
            'portfolio_filter' => 'portfolio_filter_nonce'
        ];
        
        foreach ($forms as $form => $nonce_name) {
            $nonce = wp_create_nonce($nonce_name);
            $this->assertTrue(wp_verify_nonce($nonce, $nonce_name));
        }
    }
    
    public function test_data_sanitization() {
        // 10.2 データサニタイゼーションの確認
        $test_data = '<script>alert("XSS")</script>Test';
        $sanitized = sanitize_text_field($test_data);
        $this->assertStringNotContainsString('<script>', $sanitized);
    }
    
    public function test_sql_injection_prevention() {
        // 10.3 SQLインジェクション対策の確認
        global $wpdb;
        $unsafe_input = "'; DROP TABLE wp_posts; --";
        $safe_query = $wpdb->prepare("SELECT * FROM wp_posts WHERE post_title = %s", $unsafe_input);
        $this->assertStringNotContainsString('DROP TABLE', $safe_query);
    }
    
    public function test_file_upload_restrictions() {
        // 10.4 ファイルアップロード制限の確認
        $allowed_types = get_allowed_mime_types();
        $this->assertArrayNotHasKey('php', $allowed_types);
        $this->assertArrayNotHasKey('exe', $allowed_types);
    }
}
```

#### パフォーマンステストケース
```javascript
// tests/performance/lighthouse-config.js
module.exports = {
    ci: {
        collect: {
            url: [
                'http://localhost:8080/',
                'http://localhost:8080/about/',
                'http://localhost:8080/portfolio/',
                'http://localhost:8080/skills/',
                'http://localhost:8080/contact/'
            ],
            numberOfRuns: 3
        },
        assert: {
            assertions: {
                'categories:performance': ['error', {minScore: 0.9}],
                'categories:accessibility': ['error', {minScore: 0.9}],
                'categories:best-practices': ['error', {minScore: 0.9}],
                'categories:seo': ['error', {minScore: 0.9}],
                'first-contentful-paint': ['error', {maxNumericValue: 2000}],
                'interactive': ['error', {maxNumericValue: 3500}],
                'cumulative-layout-shift': ['error', {maxNumericValue: 0.1}]
            }
        }
    }
};
```

```bash
# 10.5 Lighthouseパフォーマンステスト実行
npx lighthouse-ci autorun --config=tests/performance/lighthouse-config.js

# 10.6 アクセシビリティテスト（axe-core）
npx axe http://localhost:8080/ --tags wcag2a,wcag2aa

# 10.7 アクセシビリティテスト（pa11y）
npx pa11y http://localhost:8080/ --standard WCAG2AA
```

## 3. テスト実行計画

### 3.1 並列実行コマンド
```bash
#!/bin/bash
# run-parallel-tests.sh

# 各グループを並列実行
(
    echo "Starting Group 1: PHP Syntax Check"
    ./run-tests.sh php-syntax
) &

(
    echo "Starting Group 2: WordPress Core Functions"
    ./run-tests.sh wp-core
) &

(
    echo "Starting Group 3: Page Templates (Part 1)"
    ./run-tests.sh templates-1
) &

(
    echo "Starting Group 4: Page Templates (Part 2)"
    ./run-tests.sh templates-2
) &

(
    echo "Starting Group 5: Custom Post Types"
    ./run-tests.sh custom-posts
) &

(
    echo "Starting Group 6: JavaScript Navigation"
    ./run-tests.sh js-nav
) &

(
    echo "Starting Group 7: JavaScript Forms"
    ./run-tests.sh js-forms
) &

(
    echo "Starting Group 8: CSS/Styling"
    ./run-tests.sh css
) &

(
    echo "Starting Group 9: AJAX/API"
    ./run-tests.sh ajax
) &

(
    echo "Starting Group 10: Security & Performance"
    ./run-tests.sh security-perf
) &

# すべてのバックグラウンドプロセスを待機
wait

echo "All test groups completed!"
```

### 3.2 テスト実行順序（順次実行の場合）
1. グループ1: PHP構文チェック（最も基本的なチェック）
2. グループ8: CSS/スタイリングテスト（静的解析）
3. グループ2: WordPress基本機能テスト
4. グループ5: カスタム投稿タイプテスト
5. グループ3-4: ページテンプレートテスト
6. グループ6-7: JavaScript機能テスト
7. グループ9: AJAX/API通信テスト
8. グループ10: セキュリティとパフォーマンステスト

### 3.3 Docker環境での実行
```yaml
# docker-compose.test.yml に追加
services:
  test-runner:
    build:
      context: .
      target: testing
    volumes:
      - ./tests:/app/tests
      - ./coverage:/app/coverage
    command: /app/run-parallel-tests.sh
    networks:
      - test-network
    depends_on:
      - db-test
      - redis-test
```

## 4. テストカバレッジ目標

### 4.1 コードカバレッジ
- **PHP全体**: 75%以上
- **主要機能（functions.php, inc/）**: 80%以上
- **JavaScript全体**: 75%以上
- **主要スクリプト（contact-form.js等）**: 80%以上

### 4.2 機能カバレッジ
- **ページテンプレート**: 100%（全ページ）
- **AJAX機能**: 100%（全エンドポイント）
- **カスタム投稿タイプ**: 100%
- **ナビゲーション**: 100%

## 5. 品質基準

### 5.1 パフォーマンス基準
- Lighthouse Performance Score: 90以上
- First Contentful Paint: 2秒以内
- Time to Interactive: 3.5秒以内
- Cumulative Layout Shift: 0.1以下

### 5.2 アクセシビリティ基準
- WCAG 2.1 レベルAA準拠
- Lighthouse Accessibility Score: 90以上
- キーボードナビゲーション: 全機能対応

### 5.3 セキュリティ基準
- XSS脆弱性: 0件
- SQLインジェクション脆弱性: 0件
- CSRF対策: 全フォームで実装
- 適切なデータサニタイゼーション: 100%

## 6. 継続的インテグレーション（CI）設定

### 6.1 GitHub Actions設定例
```yaml
name: Theme Tests

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]

jobs:
  test:
    runs-on: ubuntu-latest
    
    strategy:
      matrix:
        test-group: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Setup Test Environment
      run: docker-compose -f docker-compose.test.yml up -d
    
    - name: Run Test Group ${{ matrix.test-group }}
      run: |
        docker-compose -f docker-compose.test.yml exec -T wordpress-test \
          ./run-tests.sh group-${{ matrix.test-group }}
    
    - name: Upload Coverage
      uses: codecov/codecov-action@v2
      with:
        files: ./coverage/group-${{ matrix.test-group }}.xml
```

## 7. テスト結果レポート

### 7.1 レポート出力先
- **PHPUnit**: `/coverage/phpunit/`
- **Jest**: `/coverage/jest/`
- **Lighthouse**: `/lighthouse-reports/`
- **アクセシビリティ**: `/a11y-reports/`
- **統合レポート**: `/test-reports/summary.html`

### 7.2 レポート項目
- テスト実行日時
- 各グループの成功/失敗数
- コードカバレッジ率
- パフォーマンススコア
- 発見された問題の詳細
- 改善提案

## 8. トラブルシューティング

### 8.1 よくある問題と対処法

#### PHPテストが失敗する場合
```bash
# WordPress Test Suiteの再セットアップ
./run-tests.sh --setup

# データベースのリセット
docker-compose -f docker-compose.test.yml down -v
docker-compose -f docker-compose.test.yml up -d
```

#### JavaScriptテストが失敗する場合
```bash
# node_modulesの再インストール
rm -rf node_modules package-lock.json
npm install

# Jestキャッシュのクリア
npm run test -- --clearCache
```

#### パフォーマンステストが基準を満たさない場合
1. 画像の最適化確認
2. CSS/JSの圧縮確認
3. キャッシュ設定の確認
4. 不要なプラグインの無効化

## 9. メンテナンス計画

### 9.1 定期メンテナンス
- **毎週**: 依存関係の更新確認
- **毎月**: テストケースのレビューと更新
- **四半期**: パフォーマンス基準の見直し
- **半年**: セキュリティ監査の実施

### 9.2 テスト追加基準
新機能追加時は必ず以下を実施：
1. 対応するテストケースの作成
2. 既存テストへの影響確認
3. カバレッジ率の維持確認
4. CI/CDパイプラインでの検証

## 10. 参考資料

### 10.1 ドキュメント
- [WordPress Testing Documentation](https://make.wordpress.org/core/handbook/testing/)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Jest Documentation](https://jestjs.io/docs/getting-started)
- [Lighthouse CI Documentation](https://github.com/GoogleChrome/lighthouse-ci)

### 10.2 関連ファイル
- `/tests/テスト環境状況.md` - テスト環境の詳細
- `/run-tests.sh` - テスト実行スクリプト
- `/docker-compose.test.yml` - テスト用Docker設定
- `/wordpress/wp-content/themes/kei-portfolio/phpunit.xml` - PHPUnit設定
- `/wordpress/wp-content/themes/kei-portfolio/jest.config.js` - Jest設定

---

## 改訂履歴
- 2025-08-07: 初版作成 - 包括的テスト設計書の策定
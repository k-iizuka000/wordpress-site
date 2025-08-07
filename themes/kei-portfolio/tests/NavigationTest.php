<?php
/**
 * Navigation Test
 * ナビゲーションメニューのテスト
 *
 * @package Kei_Portfolio
 */

class NavigationTest extends WP_UnitTestCase {

    public function setUp(): void {
        parent::setUp();
        
        // テスト環境でテーマの必要ファイルを読み込み
        require_once get_template_directory() . '/inc/page-creator.php';
        
        // テストデータをクリア
        $this->cleanup_test_data();
    }

    public function tearDown(): void {
        // テスト後にクリーンアップ
        $this->cleanup_test_data();
        parent::tearDown();
    }

    /**
     * テストデータのクリーンアップ
     */
    private function cleanup_test_data() {
        // ページを削除
        $pages = get_posts(array(
            'post_type' => 'page',
            'post_status' => 'any',
            'numberposts' => -1,
        ));
        
        foreach ($pages as $page) {
            wp_delete_post($page->ID, true);
        }
        
        // メニューを削除
        $menus = wp_get_nav_menus();
        foreach ($menus as $menu) {
            wp_delete_nav_menu($menu->term_id);
        }
        
        // オプションの削除
        delete_option('kei_portfolio_pages_created');
        delete_option('kei_portfolio_page_creation_log');
        
        // テーマモッドの削除
        remove_theme_mod('nav_menu_locations');
    }

    /**
     * Primary Navigationメニュー作成テスト
     */
    public function test_create_primary_navigation_menu() {
        // ページを作成してメニューに追加
        $results = kei_portfolio_create_all_pages();
        $menu_result = kei_portfolio_add_pages_to_menu($results);
        
        $this->assertTrue($menu_result);
        
        // Primary Navigationメニューが作成されているか確認
        $menu = wp_get_nav_menu_object('Primary Navigation');
        $this->assertNotFalse($menu);
        $this->assertEquals('Primary Navigation', $menu->name);
    }

    /**
     * メニューアイテムの追加テスト
     */
    public function test_add_menu_items() {
        // ページを作成
        $results = kei_portfolio_create_all_pages();
        
        // メニューに追加
        $menu_result = kei_portfolio_add_pages_to_menu($results);
        $this->assertTrue($menu_result);
        
        // メニューアイテムを取得
        $menu = wp_get_nav_menu_object('Primary Navigation');
        $menu_items = wp_get_nav_menu_items($menu->term_id);
        
        $this->assertNotEmpty($menu_items);
        $this->assertCount(4, $menu_items);
        
        // 各ページがメニューに含まれているか確認
        $menu_page_titles = array();
        foreach ($menu_items as $item) {
            if ($item->object === 'page') {
                $menu_page_titles[] = $item->title;
            }
        }
        
        $expected_titles = array('About', 'Skills', 'Contact', 'Portfolio');
        foreach ($expected_titles as $title) {
            $this->assertContains($title, $menu_page_titles);
        }
    }

    /**
     * メニューの順序テスト
     */
    public function test_menu_item_order() {
        // ページを作成してメニューに追加
        $results = kei_portfolio_create_all_pages();
        kei_portfolio_add_pages_to_menu($results);
        
        // メニューアイテムを取得
        $menu = wp_get_nav_menu_object('Primary Navigation');
        $menu_items = wp_get_nav_menu_items($menu->term_id);
        
        // ページ設定から期待される順序を取得
        $page_configurations = kei_portfolio_get_page_configurations();
        $expected_order = array();
        
        foreach ($page_configurations as $config) {
            $expected_order[$config['menu_order']] = $config['title'];
        }
        ksort($expected_order);
        
        // メニューアイテムの順序を確認
        $actual_order = array();
        foreach ($menu_items as $item) {
            if ($item->object === 'page') {
                $actual_order[] = $item->title;
            }
        }
        
        // 期待される順序と実際の順序を比較
        $this->assertEquals(array_values($expected_order), $actual_order);
    }

    /**
     * 重複メニューアイテム防止テスト
     */
    public function test_prevent_duplicate_menu_items() {
        // 1回目の実行
        $results = kei_portfolio_create_all_pages();
        kei_portfolio_add_pages_to_menu($results);
        
        $menu = wp_get_nav_menu_object('Primary Navigation');
        $items_count_first = count(wp_get_nav_menu_items($menu->term_id));
        
        // 2回目の実行
        kei_portfolio_add_pages_to_menu($results);
        
        $items_count_second = count(wp_get_nav_menu_items($menu->term_id));
        
        // アイテム数が変わらないことを確認
        $this->assertEquals($items_count_first, $items_count_second);
    }

    /**
     * プライマリメニューの位置設定テスト
     */
    public function test_primary_menu_location_setting() {
        // ページとメニューを作成
        $results = kei_portfolio_create_all_pages();
        kei_portfolio_add_pages_to_menu($results);
        
        // メニューの位置が設定されているか確認
        $locations = get_theme_mod('nav_menu_locations');
        
        $this->assertIsArray($locations);
        $this->assertArrayHasKey('primary', $locations);
        
        // プライマリメニューが正しく設定されているか確認
        $menu = wp_get_nav_menu_object('Primary Navigation');
        $this->assertEquals($menu->term_id, $locations['primary']);
    }

    /**
     * メニューアイテムの属性テスト
     */
    public function test_menu_item_properties() {
        // ページとメニューを作成
        $results = kei_portfolio_create_all_pages();
        kei_portfolio_add_pages_to_menu($results);
        
        $menu = wp_get_nav_menu_object('Primary Navigation');
        $menu_items = wp_get_nav_menu_items($menu->term_id);
        
        foreach ($menu_items as $item) {
            if ($item->object === 'page') {
                // 基本属性の確認
                $this->assertEquals('page', $item->object);
                $this->assertEquals('post_type', $item->type);
                $this->assertEquals('publish', $item->post_status);
                
                // リンクされたページが存在することを確認
                $page = get_post($item->object_id);
                $this->assertNotNull($page);
                $this->assertEquals('page', $page->post_type);
                $this->assertEquals('publish', $page->post_status);
                
                // URLが正しく生成されることを確認
                $this->assertNotEmpty($item->url);
                $this->assertStringContainsString(home_url(), $item->url);
            }
        }
    }

    /**
     * 現在のページのハイライト表示確認テスト
     */
    public function test_current_page_highlight() {
        // ページとメニューを作成
        $results = kei_portfolio_create_all_pages();
        kei_portfolio_add_pages_to_menu($results);
        
        // Aboutページをカレントページとして設定
        $about_page = get_page_by_path('about');
        $this->go_to(get_permalink($about_page->ID));
        
        // メニューを取得
        $menu = wp_get_nav_menu_object('Primary Navigation');
        $menu_items = wp_get_nav_menu_items($menu->term_id);
        
        // カレントページのメニューアイテムを見つける
        $current_item = null;
        foreach ($menu_items as $item) {
            if ($item->object_id == $about_page->ID) {
                $current_item = $item;
                break;
            }
        }
        
        $this->assertNotNull($current_item);
        
        // wp_nav_menu()関数でのカレントページ判定をテスト
        // これは実際のテーマでのハイライト表示のテストに相当
        global $wp_query;
        $original_post = $wp_query->post;
        $wp_query->post = $about_page;
        
        // カレントページかどうかを判定する関数をテスト
        $is_current = ($current_item->object_id == get_queried_object_id());
        $this->assertTrue($is_current);
        
        // 元の状態に戻す
        $wp_query->post = $original_post;
    }

    /**
     * メニューのアクセシビリティ確認テスト
     */
    public function test_menu_accessibility() {
        // ページとメニューを作成
        $results = kei_portfolio_create_all_pages();
        kei_portfolio_add_pages_to_menu($results);
        
        $menu = wp_get_nav_menu_object('Primary Navigation');
        $menu_items = wp_get_nav_menu_items($menu->term_id);
        
        foreach ($menu_items as $item) {
            // メニューアイテムにタイトルがあることを確認
            $this->assertNotEmpty($item->title);
            
            // URLが有効であることを確認
            $this->assertNotEmpty($item->url);
            $this->assertStringStartsWith('http', $item->url);
            
            // target属性が適切に設定されているか確認
            if (!empty($item->target)) {
                $this->assertContains($item->target, array('_blank', '_self', '_parent', '_top'));
            }
        }
    }

    /**
     * メニューの国際化対応テスト
     */
    public function test_menu_internationalization() {
        // ページとメニューを作成
        $results = kei_portfolio_create_all_pages();
        kei_portfolio_add_pages_to_menu($results);
        
        $menu = wp_get_nav_menu_object('Primary Navigation');
        $menu_items = wp_get_nav_menu_items($menu->term_id);
        
        // 各メニューアイテムのタイトルが適切に設定されているか確認
        $expected_titles = array('About', 'Skills', 'Portfolio', 'Contact');
        $actual_titles = array();
        
        foreach ($menu_items as $item) {
            if ($item->object === 'page') {
                $actual_titles[] = $item->title;
            }
        }
        
        foreach ($expected_titles as $title) {
            $this->assertContains($title, $actual_titles);
        }
    }

    /**
     * メニューエラーハンドリングテスト
     */
    public function test_menu_error_handling() {
        // 空の結果でメニュー追加を試行
        $empty_results = array('success' => array(), 'errors' => array());
        $result = kei_portfolio_add_pages_to_menu($empty_results);
        
        // エラーが適切に処理されることを確認
        $this->assertTrue($result);
        
        // 無効なページIDでのテスト
        $invalid_results = array(
            'success' => array(
                'test' => array(
                    'id' => 99999, // 存在しないID
                    'title' => 'Invalid Page',
                    'slug' => 'invalid-page',
                )
            ),
            'errors' => array()
        );
        
        $result = kei_portfolio_add_pages_to_menu($invalid_results);
        $this->assertTrue($result); // エラーが発生しても関数は成功を返す
    }

    /**
     * メニューの削除テスト
     */
    public function test_menu_deletion() {
        // ページとメニューを作成
        $results = kei_portfolio_create_all_pages();
        kei_portfolio_add_pages_to_menu($results);
        
        // メニューが存在することを確認
        $menu = wp_get_nav_menu_object('Primary Navigation');
        $this->assertNotFalse($menu);
        
        // メニューを削除
        $deletion_result = wp_delete_nav_menu($menu->term_id);
        $this->assertTrue($deletion_result);
        
        // メニューが削除されたことを確認
        $deleted_menu = wp_get_nav_menu_object('Primary Navigation');
        $this->assertFalse($deleted_menu);
    }

    /**
     * 複数メニューの管理テスト
     */
    public function test_multiple_menu_management() {
        // Primary Navigationを作成
        $results = kei_portfolio_create_all_pages();
        kei_portfolio_add_pages_to_menu($results);
        
        // 別のメニューを作成
        $secondary_menu_id = wp_create_nav_menu('Secondary Navigation');
        $this->assertIsInt($secondary_menu_id);
        
        // 両方のメニューが存在することを確認
        $primary_menu = wp_get_nav_menu_object('Primary Navigation');
        $secondary_menu = wp_get_nav_menu_object('Secondary Navigation');
        
        $this->assertNotFalse($primary_menu);
        $this->assertNotFalse($secondary_menu);
        
        // メニューIDが異なることを確認
        $this->assertNotEquals($primary_menu->term_id, $secondary_menu->term_id);
    }
}
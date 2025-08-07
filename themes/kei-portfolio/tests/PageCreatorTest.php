<?php
/**
 * Page Creator Test
 * 固定ページ作成機能のテスト
 *
 * @package Kei_Portfolio
 */

class PageCreatorTest extends WP_UnitTestCase {

    public function setUp(): void {
        parent::setUp();
        
        // テスト環境でテーマの必要ファイルを読み込み
        require_once get_template_directory() . '/inc/page-creator.php';
        
        // テストデータをクリア
        delete_option( 'kei_portfolio_pages_created' );
        delete_option( 'kei_portfolio_page_creation_log' );
    }

    public function tearDown(): void {
        // テスト後にクリーンアップ
        $this->delete_test_pages();
        delete_option( 'kei_portfolio_pages_created' );
        delete_option( 'kei_portfolio_page_creation_log' );
        
        parent::tearDown();
    }

    /**
     * テスト用ページの削除
     */
    private function delete_test_pages() {
        $slugs = array( 'about', 'skills', 'contact', 'portfolio' );
        
        foreach ( $slugs as $slug ) {
            $page = get_page_by_path( $slug );
            if ( $page ) {
                wp_delete_post( $page->ID, true );
            }
        }
    }

    /**
     * ページ設定の取得テスト
     */
    public function test_get_page_configurations() {
        $configurations = kei_portfolio_get_page_configurations();
        
        $this->assertIsArray( $configurations );
        $this->assertCount( 4, $configurations );
        
        // 期待されるページが含まれているか確認
        $expected_pages = array( 'about', 'skills', 'contact', 'portfolio' );
        
        foreach ( $expected_pages as $page ) {
            $this->assertArrayHasKey( $page, $configurations );
            $this->assertArrayHasKey( 'title', $configurations[$page] );
            $this->assertArrayHasKey( 'slug', $configurations[$page] );
            $this->assertArrayHasKey( 'content', $configurations[$page] );
            $this->assertArrayHasKey( 'template', $configurations[$page] );
        }
    }

    /**
     * 単一ページ作成テスト
     */
    public function test_create_single_page() {
        $page_config = array(
            'title'    => 'Test About',
            'slug'     => 'test-about',
            'content'  => '<p>Test about content</p>',
            'template' => 'page-about.php',
            'menu_order' => 1,
            'meta_description' => 'Test meta description',
        );

        $page_id = kei_portfolio_create_page( $page_config );
        
        $this->assertIsInt( $page_id );
        $this->assertGreaterThan( 0, $page_id );
        
        // ページが正しく作成されているか確認
        $created_page = get_post( $page_id );
        $this->assertEquals( 'Test About', $created_page->post_title );
        $this->assertEquals( 'test-about', $created_page->post_name );
        $this->assertEquals( 'publish', $created_page->post_status );
        $this->assertEquals( 'page', $created_page->post_type );
        
        // テンプレートが正しく設定されているか確認
        $template = get_post_meta( $page_id, '_wp_page_template', true );
        $this->assertEquals( 'page-about.php', $template );
        
        // メタディスクリプションが設定されているか確認
        $meta_description = get_post_meta( $page_id, '_kei_meta_description', true );
        $this->assertEquals( 'Test meta description', $meta_description );
    }

    /**
     * 重複ページ作成防止テスト
     */
    public function test_prevent_duplicate_page_creation() {
        $page_config = array(
            'title'    => 'Test Duplicate',
            'slug'     => 'test-duplicate',
            'content'  => '<p>Test content</p>',
            'template' => 'page.php',
        );

        // 1回目の作成
        $page_id_1 = kei_portfolio_create_page( $page_config );
        $this->assertIsInt( $page_id_1 );
        
        // 2回目の作成（重複）
        $page_id_2 = kei_portfolio_create_page( $page_config );
        
        // 同じIDが返されることを確認（新しいページが作成されないこと）
        $this->assertEquals( $page_id_1, $page_id_2 );
        
        // データベースに1つしか存在しないことを確認
        $pages = get_posts( array(
            'post_type' => 'page',
            'name' => 'test-duplicate',
            'post_status' => 'any',
            'numberposts' => -1
        ) );
        
        $this->assertCount( 1, $pages );
    }

    /**
     * 全ページ作成テスト
     */
    public function test_create_all_pages() {
        $results = kei_portfolio_create_all_pages();
        
        $this->assertIsArray( $results );
        $this->assertArrayHasKey( 'success', $results );
        $this->assertArrayHasKey( 'errors', $results );
        
        // 成功したページが4つあることを確認
        $this->assertCount( 4, $results['success'] );
        $this->assertEmpty( $results['errors'] );
        
        // 各ページが実際に作成されているか確認
        $expected_pages = array( 'about', 'skills', 'contact', 'portfolio' );
        
        foreach ( $expected_pages as $slug ) {
            $page = get_page_by_path( $slug );
            $this->assertNotNull( $page, "Page '{$slug}' should exist" );
            $this->assertEquals( 'publish', $page->post_status );
            
            // 結果配列に含まれているか確認
            $this->assertArrayHasKey( $slug, $results['success'] );
            $this->assertEquals( $page->ID, $results['success'][$slug]['id'] );
        }
    }

    /**
     * 無効なページ設定での作成テスト
     */
    public function test_create_page_with_invalid_config() {
        // タイトルが空の場合
        $invalid_config = array(
            'title'    => '',
            'slug'     => 'empty-title',
            'content'  => '<p>Content</p>',
        );

        $result = kei_portfolio_create_page( $invalid_config );
        $this->assertInstanceOf( 'WP_Error', $result );
    }

    /**
     * テーマ有効化時の自動実行テスト
     */
    public function test_create_pages_on_activation() {
        // まず実行前の状態を確認
        $this->assertFalse( get_option( 'kei_portfolio_pages_created', false ) );
        
        // アクティベーション関数を実行
        kei_portfolio_create_pages_on_activation();
        
        // フラグが設定されているか確認
        $this->assertTrue( get_option( 'kei_portfolio_pages_created', false ) );
        
        // ページが作成されているか確認
        $expected_pages = array( 'about', 'skills', 'contact', 'portfolio' );
        
        foreach ( $expected_pages as $slug ) {
            $page = get_page_by_path( $slug );
            $this->assertNotNull( $page, "Page '{$slug}' should be created on theme activation" );
        }
        
        // 作成ログが保存されているか確認
        $creation_log = get_option( 'kei_portfolio_page_creation_log' );
        $this->assertIsArray( $creation_log );
        $this->assertArrayHasKey( 'success', $creation_log );
        $this->assertCount( 4, $creation_log['success'] );
    }

    /**
     * 重複実行防止テスト
     */
    public function test_prevent_duplicate_activation() {
        // 1回目の実行
        kei_portfolio_create_pages_on_activation();
        $page_count_before = $this->count_portfolio_pages();
        
        // 2回目の実行
        kei_portfolio_create_pages_on_activation();
        $page_count_after = $this->count_portfolio_pages();
        
        // ページ数が変わらないことを確認
        $this->assertEquals( $page_count_before, $page_count_after );
    }

    /**
     * メニュー自動作成テスト
     */
    public function test_add_pages_to_menu() {
        // まずページを作成
        $results = kei_portfolio_create_all_pages();
        
        // メニューに追加
        $menu_result = kei_portfolio_add_pages_to_menu( $results );
        $this->assertTrue( $menu_result );
        
        // Primary Navigation メニューが作成されているか確認
        $menu = wp_get_nav_menu_object( 'Primary Navigation' );
        $this->assertNotFalse( $menu );
        
        // メニューアイテムが追加されているか確認
        $menu_items = wp_get_nav_menu_items( $menu->term_id );
        $this->assertNotEmpty( $menu_items );
        
        // ページがメニューに含まれているか確認
        $menu_page_ids = array();
        foreach ( $menu_items as $item ) {
            if ( $item->object === 'page' ) {
                $menu_page_ids[] = $item->object_id;
            }
        }
        
        foreach ( $results['success'] as $page_info ) {
            $this->assertContains( $page_info['id'], $menu_page_ids );
        }
    }

    /**
     * 管理者権限でのマニュアル実行テスト
     */
    public function test_manual_create_pages_with_admin() {
        // 管理者ユーザーを作成してログイン
        $admin_user = $this->factory->user->create( array( 'role' => 'administrator' ) );
        wp_set_current_user( $admin_user );
        
        $results = kei_portfolio_manual_create_pages();
        
        $this->assertIsArray( $results );
        $this->assertArrayHasKey( 'success', $results );
        $this->assertCount( 4, $results['success'] );
    }

    /**
     * 権限なしでのマニュアル実行テスト
     */
    public function test_manual_create_pages_without_permission() {
        // 一般ユーザーを作成してログイン
        $user = $this->factory->user->create( array( 'role' => 'subscriber' ) );
        wp_set_current_user( $user );
        
        $result = kei_portfolio_manual_create_pages();
        
        $this->assertFalse( $result );
    }

    /**
     * ページテンプレートファイルの存在確認テスト
     */
    public function test_page_template_files_exist() {
        $configurations = kei_portfolio_get_page_configurations();
        
        foreach ( $configurations as $config ) {
            if ( isset( $config['template'] ) ) {
                $template_path = get_template_directory() . '/' . $config['template'];
                $this->assertFileExists( $template_path, "Template file {$config['template']} should exist" );
            }
        }
        
        // 汎用ページテンプレートも確認
        $page_template = get_template_directory() . '/page.php';
        $this->assertFileExists( $page_template, 'Generic page.php template should exist' );
    }

    /**
     * ページ設定データの妥当性テスト
     */
    public function test_page_configurations_validity() {
        $configurations = kei_portfolio_get_page_configurations();
        
        foreach ( $configurations as $key => $config ) {
            // 必須フィールドの確認
            $this->assertNotEmpty( $config['title'], "Title should not be empty for page: {$key}" );
            $this->assertNotEmpty( $config['slug'], "Slug should not be empty for page: {$key}" );
            $this->assertNotEmpty( $config['content'], "Content should not be empty for page: {$key}" );
            
            // スラッグのフォーマット確認
            $this->assertEquals( $config['slug'], sanitize_title( $config['slug'] ), "Slug should be properly formatted for page: {$key}" );
            
            // メニューオーダーが数値であることを確認
            if ( isset( $config['menu_order'] ) ) {
                $this->assertIsInt( $config['menu_order'], "Menu order should be integer for page: {$key}" );
            }
        }
    }

    /**
     * ポートフォリオページ数をカウントするヘルパー関数
     */
    private function count_portfolio_pages() {
        $pages = get_posts( array(
            'post_type' => 'page',
            'name' => array( 'about', 'skills', 'contact', 'portfolio' ),
            'post_status' => 'any',
            'numberposts' => -1
        ) );
        
        return count( $pages );
    }

    /**
     * エラーハンドリングテスト
     */
    public function test_error_handling_with_database_failure() {
        // データベースエラーを模擬するため、無効なデータでテスト
        $invalid_config = array(
            'title' => str_repeat( 'A', 300 ), // 長すぎるタイトル
            'slug' => 'test-invalid',
            'content' => '<script>alert("xss")</script>', // XSSテスト
        );

        $result = kei_portfolio_create_page( $invalid_config );
        
        // エラーが発生するか、適切にサニタイズされるかを確認
        if ( is_wp_error( $result ) ) {
            $this->assertInstanceOf( 'WP_Error', $result );
        } else {
            // 成功した場合はコンテンツがサニタイズされているか確認
            $page = get_post( $result );
            $this->assertNotContains( '<script>', $page->post_content );
        }
    }
}
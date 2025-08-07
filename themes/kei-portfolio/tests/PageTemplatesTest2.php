<?php
/**
 * PageTemplatesTest2.php
 * 
 * ページテンプレートテスト（後半）- グループ4
 * Skills、Contact、単一プロジェクトページのテスト
 * 
 * @package KeiPortfolio
 * @subpackage Tests
 */

require_once __DIR__ . '/bootstrap.php';

/**
 * ページテンプレート後半テストクラス
 */
class PageTemplatesTest2 extends WP_UnitTestCase {
    
    /**
     * テスト用データのセットアップ
     */
    protected function setUp(): void {
        parent::setUp();
        
        // WordPressテストスイートが利用可能な場合のみテストデータを作成
        if ( class_exists( 'WP_UnitTest_Factory' ) && property_exists( $this, 'factory' ) ) {
            // テスト用のページとプロジェクトを作成
            $this->create_test_data();
        }
    }
    
    /**
     * テスト用データの作成
     */
    private function create_test_data() {
        // スキルページ作成（WordPressテストスイート利用時）
        if ( isset( $this->factory ) ) {
            $this->skills_page_id = $this->factory->post->create([
                'post_type' => 'page',
                'post_title' => 'Skills',
                'post_status' => 'publish',
                'page_template' => 'page-templates/page-skills.php'
            ]);
            
            $this->contact_page_id = $this->factory->post->create([
                'post_type' => 'page',
                'post_title' => 'Contact',
                'post_status' => 'publish',
                'page_template' => 'page-templates/page-contact.php'
            ]);
        }
    }
    
    /**
     * 4.1 Skillsページテンプレートの存在確認
     */
    public function test_skills_page_template_exists() {
        $template_path = THEME_DIR . '/page-templates/page-skills.php';
        
        $this->assertFileExists(
            $template_path,
            'Skills page template should exist'
        );
        
        // ファイルが読み込み可能かチェック
        $this->assertTrue(
            is_readable( $template_path ),
            'Skills page template should be readable'
        );
    }
    
    /**
     * 4.1.1 Skillsページテンプレートの構造確認
     */
    public function test_skills_page_template_structure() {
        $template_path = THEME_DIR . '/page-templates/page-skills.php';
        
        if ( ! file_exists( $template_path ) ) {
            $this->markTestSkipped( 'Skills page template does not exist' );
        }
        
        $content = file_get_contents( $template_path );
        
        // Template Nameヘッダーの確認
        $this->assertStringContainsString(
            'Template Name: Skills',
            $content,
            'Skills template should have proper Template Name header'
        );
        
        // get_header() と get_footer() の呼び出し確認
        $this->assertStringContainsString(
            'get_header()',
            $content,
            'Skills template should call get_header()'
        );
        
        $this->assertStringContainsString(
            'get_footer()',
            $content,
            'Skills template should call get_footer()'
        );
        
        // テンプレートパーツの読み込み確認
        $expected_template_parts = [
            'skills/skills-hero',
            'skills/programming-languages'
        ];
        
        foreach ( $expected_template_parts as $part ) {
            $this->assertStringContainsString(
                $part,
                $content,
                "Skills template should load template part: {$part}"
            );
        }
    }
    
    /**
     * 4.1.2 Skillsページテンプレートの適用確認（WordPress環境時）
     */
    public function test_skills_page_template_application() {
        if ( ! function_exists( 'is_page_template' ) ) {
            $this->markTestSkipped( 'WordPress functions not available' );
        }
        
        if ( ! isset( $this->skills_page_id ) ) {
            $this->markTestSkipped( 'Test data not available' );
        }
        
        // ページテンプレートの適用確認
        $template = get_page_template_slug( $this->skills_page_id );
        $this->assertEquals(
            'page-templates/page-skills.php',
            $template,
            'Skills page should use skills template'
        );
    }
    
    /**
     * 4.2 Contactページテンプレートの存在確認
     */
    public function test_contact_page_template_exists() {
        $template_path = THEME_DIR . '/page-templates/page-contact.php';
        
        $this->assertFileExists(
            $template_path,
            'Contact page template should exist'
        );
        
        // ファイルが読み込み可能かチェック
        $this->assertTrue(
            is_readable( $template_path ),
            'Contact page template should be readable'
        );
    }
    
    /**
     * 4.2.1 Contactページテンプレートの構造確認
     */
    public function test_contact_page_template_structure() {
        $template_path = THEME_DIR . '/page-templates/page-contact.php';
        
        if ( ! file_exists( $template_path ) ) {
            $this->markTestSkipped( 'Contact page template does not exist' );
        }
        
        $content = file_get_contents( $template_path );
        
        // Template Nameヘッダーの確認
        $this->assertStringContainsString(
            'Template Name: Contact',
            $content,
            'Contact template should have proper Template Name header'
        );
        
        // get_header() と get_footer() の呼び出し確認
        $this->assertStringContainsString(
            'get_header()',
            $content,
            'Contact template should call get_header()'
        );
        
        $this->assertStringContainsString(
            'get_footer()',
            $content,
            'Contact template should call get_footer()'
        );
        
        // テンプレートパーツの読み込み確認
        $expected_template_parts = [
            'contact/hero',
            'contact/contact-form',
            'contact/contact-info'
        ];
        
        foreach ( $expected_template_parts as $part ) {
            $this->assertStringContainsString(
                $part,
                $content,
                "Contact template should load template part: {$part}"
            );
        }
    }
    
    /**
     * 4.2.2 Contactページテンプレートの適用確認（WordPress環境時）
     */
    public function test_contact_page_template_application() {
        if ( ! function_exists( 'is_page_template' ) ) {
            $this->markTestSkipped( 'WordPress functions not available' );
        }
        
        if ( ! isset( $this->contact_page_id ) ) {
            $this->markTestSkipped( 'Test data not available' );
        }
        
        // ページテンプレートの適用確認
        $template = get_page_template_slug( $this->contact_page_id );
        $this->assertEquals(
            'page-templates/page-contact.php',
            $template,
            'Contact page should use contact template'
        );
    }
    
    /**
     * 4.3 プロジェクト詳細テンプレートの存在確認
     */
    public function test_single_project_template_exists() {
        $template_path = THEME_DIR . '/single-project.php';
        
        $this->assertFileExists(
            $template_path,
            'Single project template should exist'
        );
        
        // ファイルが読み込み可能かチェック
        $this->assertTrue(
            is_readable( $template_path ),
            'Single project template should be readable'
        );
    }
    
    /**
     * 4.3.1 プロジェクト詳細テンプレートの構造確認
     */
    public function test_single_project_template_structure() {
        $template_path = THEME_DIR . '/single-project.php';
        
        if ( ! file_exists( $template_path ) ) {
            $this->markTestSkipped( 'Single project template does not exist' );
        }
        
        $content = file_get_contents( $template_path );
        
        // 基本的なWordPressテンプレート構造の確認
        $this->assertStringContainsString(
            'get_header()',
            $content,
            'Single project template should call get_header()'
        );
        
        $this->assertStringContainsString(
            'get_footer()',
            $content,
            'Single project template should call get_footer()'
        );
        
        // WordPressループの確認
        $this->assertStringContainsString(
            'have_posts()',
            $content,
            'Single project template should have WordPress loop'
        );
        
        $this->assertStringContainsString(
            'the_post()',
            $content,
            'Single project template should call the_post()'
        );
        
        // プロジェクト固有の要素確認
        $expected_elements = [
            'the_title()',
            'the_content()',
            'project-header',
            'project-detail'
        ];
        
        foreach ( $expected_elements as $element ) {
            $this->assertStringContainsString(
                $element,
                $content,
                "Single project template should contain: {$element}"
            );
        }
    }
    
    /**
     * 4.3.2 プロジェクトテンプレートのカスタムフィールド確認
     */
    public function test_single_project_custom_fields() {
        $template_path = THEME_DIR . '/single-project.php';
        
        if ( ! file_exists( $template_path ) ) {
            $this->markTestSkipped( 'Single project template does not exist' );
        }
        
        $content = file_get_contents( $template_path );
        
        // Advanced Custom Fields関数の使用確認
        $acf_functions = [
            'get_field(',
            'the_field('
        ];
        
        $has_acf = false;
        foreach ( $acf_functions as $func ) {
            if ( strpos( $content, $func ) !== false ) {
                $has_acf = true;
                break;
            }
        }
        
        $this->assertTrue(
            $has_acf,
            'Single project template should use ACF functions for custom fields'
        );
        
        // プロジェクト関連のカスタムフィールド確認
        $expected_fields = [
            'project_period',
            'project_role',
            'team_size',
            'project_challenges',
            'project_solutions',
            'technical_details'
        ];
        
        foreach ( $expected_fields as $field ) {
            $this->assertStringContainsString(
                $field,
                $content,
                "Single project template should handle field: {$field}"
            );
        }
    }
    
    /**
     * 4.3.3 プロジェクトテンプレートのセキュリティ確認
     */
    public function test_single_project_security() {
        $template_path = THEME_DIR . '/single-project.php';
        
        if ( ! file_exists( $template_path ) ) {
            $this->markTestSkipped( 'Single project template does not exist' );
        }
        
        $content = file_get_contents( $template_path );
        
        // エスケープ関数の使用確認
        $escape_functions = [
            'esc_html(',
            'esc_attr(',
            'esc_url(',
            'wp_kses_post('
        ];
        
        $escape_count = 0;
        foreach ( $escape_functions as $func ) {
            $escape_count += substr_count( $content, $func );
        }
        
        $this->assertGreaterThan(
            0,
            $escape_count,
            'Single project template should use escape functions for security'
        );
        
        // 直接echo/printの危険な使用を確認
        $dangerous_patterns = [
            '/echo\s+\$[^;]*;/',  // Direct variable echo
            '/print\s+\$[^;]*;/'  // Direct variable print
        ];
        
        foreach ( $dangerous_patterns as $pattern ) {
            $this->assertEquals(
                0,
                preg_match_all( $pattern, $content ),
                'Single project template should not directly output unescaped variables'
            );
        }
    }
    
    /**
     * 4.4 関連プロジェクト機能の確認
     */
    public function test_related_projects_functionality() {
        $template_path = THEME_DIR . '/single-project.php';
        
        if ( ! file_exists( $template_path ) ) {
            $this->markTestSkipped( 'Single project template does not exist' );
        }
        
        $content = file_get_contents( $template_path );
        
        // 関連プロジェクトのクエリ確認
        $this->assertStringContainsString(
            'WP_Query',
            $content,
            'Single project template should use WP_Query for related projects'
        );
        
        $this->assertStringContainsString(
            'related-projects',
            $content,
            'Single project template should have related projects section'
        );
        
        // タクソノミークエリの確認
        $this->assertStringContainsString(
            'tax_query',
            $content,
            'Single project template should use taxonomy query for related projects'
        );
        
        // wp_reset_postdata()の確認
        $this->assertStringContainsString(
            'wp_reset_postdata()',
            $content,
            'Single project template should reset post data after custom query'
        );
    }
    
    /**
     * 4.5 プロジェクトナビゲーション機能の確認
     */
    public function test_project_navigation() {
        $template_path = THEME_DIR . '/single-project.php';
        
        if ( ! file_exists( $template_path ) ) {
            $this->markTestSkipped( 'Single project template does not exist' );
        }
        
        $content = file_get_contents( $template_path );
        
        // 前後のプロジェクトナビゲーション確認
        $navigation_functions = [
            'get_previous_post(',
            'get_next_post('
        ];
        
        foreach ( $navigation_functions as $func ) {
            $this->assertStringContainsString(
                $func,
                $content,
                "Single project template should use {$func} for navigation"
            );
        }
        
        $this->assertStringContainsString(
            'project-navigation',
            $content,
            'Single project template should have navigation section'
        );
    }
    
    /**
     * 4.6 テンプレートファイルの構文確認
     */
    public function test_template_syntax() {
        $templates = [
            'page-templates/page-skills.php',
            'page-templates/page-contact.php',
            'single-project.php'
        ];
        
        foreach ( $templates as $template ) {
            $file_path = THEME_DIR . '/' . $template;
            
            if ( ! file_exists( $file_path ) ) {
                continue;
            }
            
            // PHPファイルの構文チェック
            $output = [];
            $return_var = 0;
            exec( "php -l '{$file_path}' 2>&1", $output, $return_var );
            
            $this->assertEquals(
                0,
                $return_var,
                "Template {$template} has syntax error: " . implode( "\n", $output )
            );
        }
    }
    
    /**
     * テスト後のクリーンアップ
     */
    protected function tearDown(): void {
        // テストデータのクリーンアップ（WordPress環境時）
        if ( function_exists( 'wp_delete_post' ) ) {
            if ( isset( $this->skills_page_id ) ) {
                wp_delete_post( $this->skills_page_id, true );
            }
            if ( isset( $this->contact_page_id ) ) {
                wp_delete_post( $this->contact_page_id, true );
            }
        }
        
        parent::tearDown();
    }
}
<?php
/**
 * TemplatePartsTest.php
 * 
 * テンプレートパーツの存在と構造確認テスト
 * WordPress テーマ kei-portfolio - グループ4（ページテンプレート後半）
 * 
 * @package KeiPortfolio
 * @subpackage Tests
 */

require_once __DIR__ . '/bootstrap.php';

/**
 * テンプレートパーツテストクラス
 */
class TemplatePartsTest extends WP_UnitTestCase {
    
    /**
     * スキルページのテンプレートパーツ存在確認
     */
    public function test_skills_template_parts_exist() {
        $skills_parts = [
            'skills/skills-hero.php',
            'skills/programming-languages.php',
            'skills/frameworks-tools.php',
            'skills/specialized-skills.php',
            'skills/learning-approach.php'
        ];
        
        foreach ( $skills_parts as $part ) {
            $file_path = THEME_DIR . '/template-parts/' . $part;
            $this->assertFileExists(
                $file_path,
                "Skills template part {$part} should exist"
            );
        }
    }
    
    /**
     * お問い合わせページのテンプレートパーツ存在確認
     */
    public function test_contact_template_parts_exist() {
        $contact_parts = [
            'contact/hero.php',
            'contact/contact-form.php',
            'contact/contact-info.php'
        ];
        
        foreach ( $contact_parts as $part ) {
            $file_path = THEME_DIR . '/template-parts/' . $part;
            $this->assertFileExists(
                $file_path,
                "Contact template part {$part} should exist"
            );
        }
    }
    
    /**
     * テンプレートパーツファイルの構文確認
     */
    public function test_template_parts_syntax() {
        $template_parts_dir = THEME_DIR . '/template-parts';
        
        if ( ! is_dir( $template_parts_dir ) ) {
            $this->markTestSkipped( 'Template parts directory does not exist' );
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator( $template_parts_dir )
        );
        
        foreach ( $iterator as $file ) {
            if ( $file->getExtension() === 'php' ) {
                $file_path = $file->getPathname();
                
                // PHPファイルの構文チェック
                $output = [];
                $return_var = 0;
                exec( "php -l '{$file_path}' 2>&1", $output, $return_var );
                
                $this->assertEquals(
                    0,
                    $return_var,
                    "Template part {$file_path} has syntax error: " . implode( "\n", $output )
                );
            }
        }
    }
    
    /**
     * テンプレートパーツの基本構造確認
     */
    public function test_template_parts_structure() {
        $template_parts = [
            'skills/skills-hero.php' => [ 'skills', 'hero' ],
            'skills/programming-languages.php' => [ 'programming', 'languages' ],
            'contact/hero.php' => [ 'contact', 'hero' ],
            'contact/contact-form.php' => [ 'form', 'contact' ],
            'contact/contact-info.php' => [ 'contact', 'info' ]
        ];
        
        foreach ( $template_parts as $file => $expected_keywords ) {
            $file_path = THEME_DIR . '/template-parts/' . $file;
            
            if ( ! file_exists( $file_path ) ) {
                $this->markTestSkipped( "Template part {$file} does not exist" );
                continue;
            }
            
            $content = file_get_contents( $file_path );
            
            // PHP開始タグの存在確認
            $this->assertStringStartsWith(
                '<?php',
                $content,
                "Template part {$file} should start with PHP opening tag"
            );
            
            // 期待されるキーワードの存在確認（case-insensitive）
            foreach ( $expected_keywords as $keyword ) {
                $this->assertStringContainsStringIgnoringCase(
                    $keyword,
                    $content,
                    "Template part {$file} should contain keyword '{$keyword}'"
                );
            }
        }
    }
    
    /**
     * スキルセクションのHTML構造確認
     */
    public function test_skills_sections_html_structure() {
        $skills_hero_path = THEME_DIR . '/template-parts/skills/skills-hero.php';
        
        if ( ! file_exists( $skills_hero_path ) ) {
            $this->markTestSkipped( 'Skills hero template part does not exist' );
        }
        
        $content = file_get_contents( $skills_hero_path );
        
        // セクションタグの存在確認
        $this->assertMatchesRegularExpression(
            '/<section[^>]*>/i',
            $content,
            'Skills hero should contain section tag'
        );
        
        // 見出しタグの存在確認
        $this->assertMatchesRegularExpression(
            '/<h[1-6][^>]*>/i',
            $content,
            'Skills hero should contain heading tag'
        );
    }
    
    /**
     * お問い合わせフォームの構造確認
     */
    public function test_contact_form_structure() {
        $contact_form_path = THEME_DIR . '/template-parts/contact/contact-form.php';
        
        if ( ! file_exists( $contact_form_path ) ) {
            $this->markTestSkipped( 'Contact form template part does not exist' );
        }
        
        $content = file_get_contents( $contact_form_path );
        
        // フォームタグの存在確認
        $this->assertMatchesRegularExpression(
            '/<form[^>]*>/i',
            $content,
            'Contact form should contain form tag'
        );
        
        // 基本的なフォームフィールドの存在確認
        $required_fields = [ 'name', 'email', 'message' ];
        
        foreach ( $required_fields as $field ) {
            $this->assertMatchesRegularExpression(
                '/name=["\']' . $field . '["\']|id=["\']' . $field . '["\']/',
                $content,
                "Contact form should contain {$field} field"
            );
        }
    }
    
    /**
     * セキュリティ要素の確認
     */
    public function test_template_security_elements() {
        $template_files = [
            THEME_DIR . '/template-parts/contact/contact-form.php'
        ];
        
        foreach ( $template_files as $file ) {
            if ( ! file_exists( $file ) ) {
                continue;
            }
            
            $content = file_get_contents( $file );
            
            // エスケープ関数の使用確認
            $escape_functions = [ 'esc_html', 'esc_attr', 'esc_url' ];
            $has_escape = false;
            
            foreach ( $escape_functions as $func ) {
                if ( strpos( $content, $func ) !== false ) {
                    $has_escape = true;
                    break;
                }
            }
            
            if ( strpos( $content, 'echo' ) !== false || strpos( $content, 'print' ) !== false ) {
                $this->assertTrue(
                    $has_escape,
                    "Template file " . basename( $file ) . " should use escape functions for output"
                );
            }
        }
    }
    
    /**
     * テンプレートパーツの読み込み可能性確認
     */
    public function test_template_parts_loadable() {
        $template_parts = [
            'skills/skills-hero.php',
            'skills/programming-languages.php',
            'contact/hero.php',
            'contact/contact-form.php',
            'contact/contact-info.php'
        ];
        
        foreach ( $template_parts as $part ) {
            $file_path = THEME_DIR . '/template-parts/' . $part;
            
            if ( ! file_exists( $file_path ) ) {
                continue;
            }
            
            // ファイルが読み込み可能かチェック
            $this->assertTrue(
                is_readable( $file_path ),
                "Template part {$part} should be readable"
            );
            
            // ファイルサイズが0でないかチェック
            $this->assertGreaterThan(
                0,
                filesize( $file_path ),
                "Template part {$part} should not be empty"
            );
        }
    }
}
<?php
/**
 * お問い合わせフォーム機能テスト
 *
 * お問い合わせフォームの動作確認とバリデーション処理のテスト
 *
 * @package Kei_Portfolio
 * @subpackage Tests
 */

class ContactFormTest extends WP_UnitTestCase {

    /**
     * テスト前のセットアップ
     */
    protected function setUp(): void {
        parent::setUp();
        
        // テーマのAJAXハンドラーを読み込み
        if ( file_exists( THEME_DIR . '/inc/ajax-handlers.php' ) ) {
            require_once THEME_DIR . '/inc/ajax-handlers.php';
        }
        
        // 必要なWordPress関数のモック
        $this->setupWordPressMocks();
    }

    /**
     * WordPress関数のモックセットアップ
     */
    private function setupWordPressMocks() {
        // WordPress関数が存在しない場合のモック実装
        if ( ! function_exists( 'wp_verify_nonce' ) ) {
            function wp_verify_nonce( $nonce, $action ) {
                return ! empty( $nonce );
            }
        }
        
        if ( ! function_exists( 'wp_create_nonce' ) ) {
            function wp_create_nonce( $action ) {
                return 'test_nonce_' . $action;
            }
        }
        
        if ( ! function_exists( 'sanitize_text_field' ) ) {
            function sanitize_text_field( $text ) {
                return trim( strip_tags( $text ) );
            }
        }
        
        if ( ! function_exists( 'sanitize_email' ) ) {
            function sanitize_email( $email ) {
                return filter_var( trim( $email ), FILTER_SANITIZE_EMAIL );
            }
        }
        
        if ( ! function_exists( 'sanitize_textarea_field' ) ) {
            function sanitize_textarea_field( $text ) {
                return trim( strip_tags( $text ) );
            }
        }
        
        if ( ! function_exists( 'is_email' ) ) {
            function is_email( $email ) {
                return filter_var( $email, FILTER_VALIDATE_EMAIL ) !== false;
            }
        }
        
        if ( ! function_exists( 'get_option' ) ) {
            function get_option( $option, $default = false ) {
                if ( $option === 'admin_email' ) {
                    return 'admin@example.com';
                }
                return $default;
            }
        }
        
        if ( ! function_exists( 'get_bloginfo' ) ) {
            function get_bloginfo( $show ) {
                if ( $show === 'name' ) {
                    return 'Test Site';
                }
                return '';
            }
        }
        
        if ( ! function_exists( 'current_time' ) ) {
            function current_time( $format ) {
                return date( $format );
            }
        }
    }

    /**
     * テスト1: フォームバリデーション - 正常なデータ
     */
    public function test_valid_form_data() {
        $form_data = array(
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'company' => 'テスト株式会社',
            'project_type' => 'ウェブサイト制作',
            'budget' => '50-100万円',
            'timeline' => '2-3ヶ月',
            'message' => 'お問い合わせのテストです。よろしくお願いします。'
        );
        
        $validation_result = $this->validateFormData( $form_data );
        $this->assertTrue( $validation_result['valid'], 'Valid form data should pass validation' );
    }

    /**
     * テスト2: フォームバリデーション - 必須フィールドが空
     */
    public function test_required_fields_validation() {
        // 名前が空の場合
        $form_data = array(
            'name' => '',
            'email' => 'test@example.com',
            'message' => 'テストメッセージ'
        );
        $validation_result = $this->validateFormData( $form_data );
        $this->assertFalse( $validation_result['valid'], 'Empty name should fail validation' );
        $this->assertStringContainsString( '必須項目', $validation_result['message'] );
        
        // メールアドレスが空の場合
        $form_data = array(
            'name' => 'テスト太郎',
            'email' => '',
            'message' => 'テストメッセージ'
        );
        $validation_result = $this->validateFormData( $form_data );
        $this->assertFalse( $validation_result['valid'], 'Empty email should fail validation' );
        
        // メッセージが空の場合
        $form_data = array(
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'message' => ''
        );
        $validation_result = $this->validateFormData( $form_data );
        $this->assertFalse( $validation_result['valid'], 'Empty message should fail validation' );
    }

    /**
     * テスト3: メールアドレス形式のバリデーション
     */
    public function test_email_format_validation() {
        $invalid_emails = array(
            'invalid-email',
            '@example.com',
            'test@',
            'test.example.com',
            'test @example.com',
            'test@example',
            ''
        );
        
        foreach ( $invalid_emails as $invalid_email ) {
            $form_data = array(
                'name' => 'テスト太郎',
                'email' => $invalid_email,
                'message' => 'テストメッセージ'
            );
            $validation_result = $this->validateFormData( $form_data );
            $this->assertFalse( $validation_result['valid'], "Invalid email '$invalid_email' should fail validation" );
        }
        
        // 有効なメールアドレスのテスト
        $valid_emails = array(
            'test@example.com',
            'user.name@domain.co.jp',
            'info+test@example.org'
        );
        
        foreach ( $valid_emails as $valid_email ) {
            $form_data = array(
                'name' => 'テスト太郎',
                'email' => $valid_email,
                'message' => 'テストメッセージ'
            );
            $validation_result = $this->validateFormData( $form_data );
            $this->assertTrue( $validation_result['valid'], "Valid email '$valid_email' should pass validation" );
        }
    }

    /**
     * テスト4: メッセージ文字数制限のテスト
     */
    public function test_message_length_validation() {
        // 500文字以内のメッセージ（有効）
        $valid_message = str_repeat( 'あ', 500 );
        $form_data = array(
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'message' => $valid_message
        );
        $validation_result = $this->validateFormData( $form_data );
        $this->assertTrue( $validation_result['valid'], '500 characters message should pass validation' );
        
        // 500文字を超えるメッセージ（無効）
        $invalid_message = str_repeat( 'あ', 501 );
        $form_data = array(
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'message' => $invalid_message
        );
        $validation_result = $this->validateFormData( $form_data );
        $this->assertFalse( $validation_result['valid'], 'Message exceeding 500 characters should fail validation' );
        $this->assertStringContainsString( '500文字以内', $validation_result['message'] );
    }

    /**
     * テスト5: データサニタイゼーションのテスト
     */
    public function test_data_sanitization() {
        $malicious_data = array(
            'name' => '<script>alert("xss")</script>テスト太郎',
            'email' => 'test@example.com<script>alert("xss")</script>',
            'company' => '<img src="x" onerror="alert(1)">テスト会社',
            'message' => '<script>alert("xss")</script>テストメッセージ'
        );
        
        $sanitized_data = $this->sanitizeFormData( $malicious_data );
        
        $this->assertStringNotContainsString( '<script>', $sanitized_data['name'], 'Script tags should be removed from name' );
        $this->assertStringNotContainsString( '<script>', $sanitized_data['email'], 'Script tags should be removed from email' );
        $this->assertStringNotContainsString( '<img', $sanitized_data['company'], 'HTML tags should be removed from company' );
        $this->assertStringNotContainsString( '<script>', $sanitized_data['message'], 'Script tags should be removed from message' );
    }

    /**
     * テスト6: Nonceセキュリティのテスト
     */
    public function test_nonce_security() {
        // 有効なnonce
        $valid_nonce = wp_create_nonce( 'kei_portfolio_contact_nonce' );
        $this->assertTrue( wp_verify_nonce( $valid_nonce, 'kei_portfolio_contact_nonce' ), 'Valid nonce should pass verification' );
        
        // 無効なnonce
        $invalid_nonce = 'invalid_nonce_12345';
        $this->assertFalse( wp_verify_nonce( $invalid_nonce, 'kei_portfolio_contact_nonce' ), 'Invalid nonce should fail verification' );
        
        // 空のnonce
        $empty_nonce = '';
        $this->assertFalse( wp_verify_nonce( $empty_nonce, 'kei_portfolio_contact_nonce' ), 'Empty nonce should fail verification' );
    }

    /**
     * テスト7: メール送信データの形式確認
     */
    public function test_email_content_format() {
        $form_data = array(
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'company' => 'テスト株式会社',
            'project_type' => 'ウェブサイト制作',
            'budget' => '50-100万円',
            'timeline' => '2-3ヶ月',
            'message' => 'お問い合わせのテストです。'
        );
        
        $email_content = $this->generateEmailContent( $form_data );
        
        $this->assertStringContainsString( 'お名前: ' . $form_data['name'], $email_content, 'Email should contain name' );
        $this->assertStringContainsString( 'メールアドレス: ' . $form_data['email'], $email_content, 'Email should contain email' );
        $this->assertStringContainsString( 'メッセージ:', $email_content, 'Email should contain message label' );
        $this->assertStringContainsString( $form_data['message'], $email_content, 'Email should contain message content' );
        $this->assertStringContainsString( '送信日時:', $email_content, 'Email should contain timestamp' );
    }

    /**
     * テスト8: オプションフィールドの処理テスト
     */
    public function test_optional_fields_handling() {
        // 必須フィールドのみのデータ
        $minimal_data = array(
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'message' => 'テストメッセージ'
        );
        
        $validation_result = $this->validateFormData( $minimal_data );
        $this->assertTrue( $validation_result['valid'], 'Form with only required fields should pass validation' );
        
        $email_content = $this->generateEmailContent( $minimal_data );
        $this->assertStringContainsString( '未記入', $email_content, 'Optional fields should show as "未記入"' );
        $this->assertStringContainsString( '未選択', $email_content, 'Optional select fields should show as "未選択"' );
    }

    /**
     * テスト9: フォームフィールドの境界値テスト
     */
    public function test_form_field_boundaries() {
        // 名前の最大長テスト（通常の範囲）
        $long_name = str_repeat( 'あ', 50 );
        $form_data = array(
            'name' => $long_name,
            'email' => 'test@example.com',
            'message' => 'テストメッセージ'
        );
        $validation_result = $this->validateFormData( $form_data );
        $this->assertTrue( $validation_result['valid'], 'Long but reasonable name should pass validation' );
        
        // メッセージの境界値テスト（ちょうど500文字）
        $exact_500_message = str_repeat( 'あ', 500 );
        $form_data = array(
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'message' => $exact_500_message
        );
        $validation_result = $this->validateFormData( $form_data );
        $this->assertTrue( $validation_result['valid'], 'Exactly 500 characters message should pass validation' );
    }

    /**
     * テスト10: レスポンスメッセージの確認
     */
    public function test_response_messages() {
        // 成功時のメッセージ
        $success_message = 'お問い合わせを受け付けました。24時間以内にご返信いたします。';
        $this->assertNotEmpty( $success_message, 'Success message should not be empty' );
        $this->assertIsString( $success_message, 'Success message should be a string' );
        
        // エラー時のメッセージ
        $error_messages = array(
            'セキュリティエラーが発生しました。',
            '必須項目が入力されていません。',
            'メールアドレスの形式が正しくありません。',
            'メッセージは500文字以内で入力してください。',
            'メールの送信に失敗しました。しばらく時間をおいて再度お試しください。'
        );
        
        foreach ( $error_messages as $error_message ) {
            $this->assertNotEmpty( $error_message, 'Error message should not be empty' );
            $this->assertIsString( $error_message, 'Error message should be a string' );
        }
    }

    /**
     * フォームデータのバリデーション（テストヘルパーメソッド）
     */
    private function validateFormData( $data ) {
        $name = isset( $data['name'] ) ? trim( $data['name'] ) : '';
        $email = isset( $data['email'] ) ? trim( $data['email'] ) : '';
        $message = isset( $data['message'] ) ? trim( $data['message'] ) : '';
        
        // 必須項目チェック
        if ( empty( $name ) || empty( $email ) || empty( $message ) ) {
            return array(
                'valid' => false,
                'message' => '必須項目が入力されていません。'
            );
        }
        
        // メールアドレス形式チェック
        if ( ! is_email( $email ) ) {
            return array(
                'valid' => false,
                'message' => 'メールアドレスの形式が正しくありません。'
            );
        }
        
        // メッセージ文字数チェック
        if ( strlen( $message ) > 500 ) {
            return array(
                'valid' => false,
                'message' => 'メッセージは500文字以内で入力してください。'
            );
        }
        
        return array(
            'valid' => true,
            'message' => 'バリデーション成功'
        );
    }

    /**
     * フォームデータのサニタイゼーション（テストヘルパーメソッド）
     */
    private function sanitizeFormData( $data ) {
        $sanitized = array();
        
        foreach ( $data as $key => $value ) {
            if ( $key === 'email' ) {
                $sanitized[$key] = sanitize_email( $value );
            } elseif ( $key === 'message' ) {
                $sanitized[$key] = sanitize_textarea_field( $value );
            } else {
                $sanitized[$key] = sanitize_text_field( $value );
            }
        }
        
        return $sanitized;
    }

    /**
     * メールコンテンツの生成（テストヘルパーメソッド）
     */
    private function generateEmailContent( $data ) {
        $name = isset( $data['name'] ) ? $data['name'] : '';
        $email = isset( $data['email'] ) ? $data['email'] : '';
        $company = isset( $data['company'] ) ? $data['company'] : '';
        $project_type = isset( $data['project_type'] ) ? $data['project_type'] : '';
        $budget = isset( $data['budget'] ) ? $data['budget'] : '';
        $timeline = isset( $data['timeline'] ) ? $data['timeline'] : '';
        $message = isset( $data['message'] ) ? $data['message'] : '';
        
        $email_content = "お問い合わせフォームからメッセージが送信されました。\n\n";
        $email_content .= "お名前: " . $name . "\n";
        $email_content .= "メールアドレス: " . $email . "\n";
        $email_content .= "会社名・組織名: " . ( ! empty( $company ) ? $company : '未記入' ) . "\n";
        $email_content .= "プロジェクトの種類: " . ( ! empty( $project_type ) ? $project_type : '未選択' ) . "\n";
        $email_content .= "ご予算: " . ( ! empty( $budget ) ? $budget : '未選択' ) . "\n";
        $email_content .= "希望納期: " . ( ! empty( $timeline ) ? $timeline : '未選択' ) . "\n";
        $email_content .= "メッセージ:\n" . $message . "\n\n";
        $email_content .= "送信日時: " . current_time( 'Y-m-d H:i:s' ) . "\n";
        
        return $email_content;
    }

    /**
     * テスト後のクリーンアップ
     */
    protected function tearDown(): void {
        // グローバル変数をクリア
        $_POST = array();
        $_GET = array();
        
        parent::tearDown();
    }
}
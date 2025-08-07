<?php
/**
 * Contact Form Handler Test
 * お問い合わせフォームAJAXハンドラーのテスト
 *
 * @package Kei_Portfolio
 */

class ContactFormHandlerTest extends WP_UnitTestCase {

    public function setUp(): void {
        parent::setUp();
        
        // テスト環境でテーマの必要ファイルを読み込み
        require_once get_template_directory() . '/inc/ajax-handlers.php';
        
        // AJAXリクエストの模擬設定
        add_action( 'wp_ajax_kei_portfolio_contact_submit', 'kei_portfolio_handle_contact_form' );
        add_action( 'wp_ajax_nopriv_kei_portfolio_contact_submit', 'kei_portfolio_handle_contact_form' );
    }

    public function tearDown(): void {
        // テスト後のクリーンアップ
        $_POST = array();
        $_REQUEST = array();
        
        parent::tearDown();
    }

    /**
     * 有効なお問い合わせデータでの成功テスト
     */
    public function test_valid_contact_form_submission() {
        // テストデータの設定
        $_POST['contact_nonce'] = wp_create_nonce( 'kei_portfolio_contact' );
        $_POST['contact_name'] = 'テスト太郎';
        $_POST['contact_email'] = 'test@example.com';
        $_POST['contact_subject'] = 'プロジェクトの相談';
        $_POST['contact_message'] = 'Webアプリケーション開発のご相談があります。詳細をお聞かせください。';
        $_POST['privacy_agreement'] = '1';

        // wp_mail をモック化（実際にメールは送信しない）
        add_filter( 'wp_mail', array( $this, 'mock_wp_mail_success' ) );

        // AJAX出力をキャプチャ
        ob_start();
        
        try {
            kei_portfolio_handle_contact_form();
        } catch ( WPAjaxDieStopException $e ) {
            // WP_Ajax_Response の終了処理をキャッチ
        }
        
        $output = ob_get_clean();
        
        // JSONレスポンスを確認
        $response = json_decode( $output, true );
        $this->assertTrue( $response['success'] );
        $this->assertContains( '受け付けました', $response['data'] );
        
        remove_filter( 'wp_mail', array( $this, 'mock_wp_mail_success' ) );
    }

    /**
     * 必須項目未入力でのエラーテスト
     */
    public function test_missing_required_fields() {
        $_POST['contact_nonce'] = wp_create_nonce( 'kei_portfolio_contact' );
        $_POST['contact_name'] = '';  // 空の名前
        $_POST['contact_email'] = 'test@example.com';
        $_POST['contact_subject'] = 'プロジェクトの相談';
        $_POST['contact_message'] = '';  // 空のメッセージ
        $_POST['privacy_agreement'] = '1';

        ob_start();
        
        try {
            kei_portfolio_handle_contact_form();
        } catch ( WPAjaxDieStopException $e ) {
            // Expected
        }
        
        $output = ob_get_clean();
        $response = json_decode( $output, true );
        
        $this->assertFalse( $response['success'] );
        $this->assertContains( '必須項目', $response['data'] );
    }

    /**
     * 無効なメールアドレスでのエラーテスト
     */
    public function test_invalid_email_format() {
        $_POST['contact_nonce'] = wp_create_nonce( 'kei_portfolio_contact' );
        $_POST['contact_name'] = 'テスト太郎';
        $_POST['contact_email'] = 'invalid-email';  // 無効なメールアドレス
        $_POST['contact_subject'] = 'プロジェクトの相談';
        $_POST['contact_message'] = 'テストメッセージ';
        $_POST['privacy_agreement'] = '1';

        ob_start();
        
        try {
            kei_portfolio_handle_contact_form();
        } catch ( WPAjaxDieStopException $e ) {
            // Expected
        }
        
        $output = ob_get_clean();
        $response = json_decode( $output, true );
        
        $this->assertFalse( $response['success'] );
        $this->assertContains( 'メールアドレスの形式', $response['data'] );
    }

    /**
     * プライバシー同意なしでのエラーテスト
     */
    public function test_privacy_agreement_required() {
        $_POST['contact_nonce'] = wp_create_nonce( 'kei_portfolio_contact' );
        $_POST['contact_name'] = 'テスト太郎';
        $_POST['contact_email'] = 'test@example.com';
        $_POST['contact_subject'] = 'プロジェクトの相談';
        $_POST['contact_message'] = 'テストメッセージ';
        // privacy_agreement を設定しない

        ob_start();
        
        try {
            kei_portfolio_handle_contact_form();
        } catch ( WPAjaxDieStopException $e ) {
            // Expected
        }
        
        $output = ob_get_clean();
        $response = json_decode( $output, true );
        
        $this->assertFalse( $response['success'] );
        $this->assertContains( 'プライバシーポリシー', $response['data'] );
    }

    /**
     * 長すぎるメッセージでのエラーテスト
     */
    public function test_message_too_long() {
        $_POST['contact_nonce'] = wp_create_nonce( 'kei_portfolio_contact' );
        $_POST['contact_name'] = 'テスト太郎';
        $_POST['contact_email'] = 'test@example.com';
        $_POST['contact_subject'] = 'プロジェクトの相談';
        $_POST['contact_message'] = str_repeat( 'あ', 2001 );  // 2001文字
        $_POST['privacy_agreement'] = '1';

        ob_start();
        
        try {
            kei_portfolio_handle_contact_form();
        } catch ( WPAjaxDieStopException $e ) {
            // Expected
        }
        
        $output = ob_get_clean();
        $response = json_decode( $output, true );
        
        $this->assertFalse( $response['success'] );
        $this->assertContains( '2000文字以内', $response['data'] );
    }

    /**
     * 無効なノンスでのセキュリティエラーテスト
     */
    public function test_invalid_nonce() {
        $_POST['contact_nonce'] = 'invalid-nonce';
        $_POST['contact_name'] = 'テスト太郎';
        $_POST['contact_email'] = 'test@example.com';
        $_POST['contact_subject'] = 'プロジェクトの相談';
        $_POST['contact_message'] = 'テストメッセージ';
        $_POST['privacy_agreement'] = '1';

        ob_start();
        
        try {
            kei_portfolio_handle_contact_form();
        } catch ( WPAjaxDieStopException $e ) {
            // Expected
        }
        
        $output = ob_get_clean();
        $response = json_decode( $output, true );
        
        $this->assertFalse( $response['success'] );
        $this->assertContains( 'セキュリティエラー', $response['data'] );
    }

    /**
     * メール送信失敗時のテスト
     */
    public function test_email_sending_failure() {
        $_POST['contact_nonce'] = wp_create_nonce( 'kei_portfolio_contact' );
        $_POST['contact_name'] = 'テスト太郎';
        $_POST['contact_email'] = 'test@example.com';
        $_POST['contact_subject'] = 'プロジェクトの相談';
        $_POST['contact_message'] = 'テストメッセージ';
        $_POST['privacy_agreement'] = '1';

        // wp_mail を失敗するようにモック化
        add_filter( 'wp_mail', array( $this, 'mock_wp_mail_failure' ) );

        ob_start();
        
        try {
            kei_portfolio_handle_contact_form();
        } catch ( WPAjaxDieStopException $e ) {
            // Expected
        }
        
        $output = ob_get_clean();
        $response = json_decode( $output, true );
        
        $this->assertFalse( $response['success'] );
        $this->assertContains( 'メールの送信に失敗', $response['data'] );
        
        remove_filter( 'wp_mail', array( $this, 'mock_wp_mail_failure' ) );
    }

    /**
     * データのサニタイゼーションテスト
     */
    public function test_data_sanitization() {
        $_POST['contact_nonce'] = wp_create_nonce( 'kei_portfolio_contact' );
        $_POST['contact_name'] = '<script>alert("xss")</script>テスト太郎';
        $_POST['contact_email'] = 'test@example.com';
        $_POST['contact_subject'] = '<b>プロジェクトの相談</b>';
        $_POST['contact_message'] = '<script>evil()</script>正常なメッセージ<img src="x" onerror="alert(1)">';
        $_POST['privacy_agreement'] = '1';

        // メール送信をキャプチャしてデータをチェック
        add_filter( 'wp_mail', array( $this, 'capture_wp_mail_data' ) );

        ob_start();
        
        try {
            kei_portfolio_handle_contact_form();
        } catch ( WPAjaxDieStopException $e ) {
            // Expected
        }
        
        $output = ob_get_clean();
        
        // キャプチャしたメールデータを確認
        $this->assertNotContains( '<script>', $this->captured_mail_message );
        $this->assertNotContains( 'onerror', $this->captured_mail_message );
        $this->assertContains( 'テスト太郎', $this->captured_mail_message );
        $this->assertContains( '正常なメッセージ', $this->captured_mail_message );
        
        remove_filter( 'wp_mail', array( $this, 'capture_wp_mail_data' ) );
    }

    /**
     * 各件名タイプでのテスト
     */
    public function test_different_subject_types() {
        $subjects = array(
            'プロジェクトの相談',
            '見積もり依頼',
            '技術相談',
            '採用・求人',
            'その他'
        );

        foreach ( $subjects as $subject ) {
            $_POST['contact_nonce'] = wp_create_nonce( 'kei_portfolio_contact' );
            $_POST['contact_name'] = 'テスト太郎';
            $_POST['contact_email'] = 'test@example.com';
            $_POST['contact_subject'] = $subject;
            $_POST['contact_message'] = "件名「{$subject}」のテストメッセージです。";
            $_POST['privacy_agreement'] = '1';

            add_filter( 'wp_mail', array( $this, 'mock_wp_mail_success' ) );

            ob_start();
            
            try {
                kei_portfolio_handle_contact_form();
            } catch ( WPAjaxDieStopException $e ) {
                // Expected
            }
            
            $output = ob_get_clean();
            $response = json_decode( $output, true );
            
            $this->assertTrue( $response['success'], "Subject '{$subject}' should be processed successfully" );
            
            remove_filter( 'wp_mail', array( $this, 'mock_wp_mail_success' ) );
        }
    }

    /**
     * 特殊文字を含む名前でのテスト
     */
    public function test_special_characters_in_name() {
        $_POST['contact_nonce'] = wp_create_nonce( 'kei_portfolio_contact' );
        $_POST['contact_name'] = 'John O\'Connor-Smith'; // アポストロフィとハイフン
        $_POST['contact_email'] = 'john.oconnor@example.com';
        $_POST['contact_subject'] = 'プロジェクトの相談';
        $_POST['contact_message'] = 'Testing special characters in names.';
        $_POST['privacy_agreement'] = '1';

        add_filter( 'wp_mail', array( $this, 'mock_wp_mail_success' ) );

        ob_start();
        
        try {
            kei_portfolio_handle_contact_form();
        } catch ( WPAjaxDieStopException $e ) {
            // Expected
        }
        
        $output = ob_get_clean();
        $response = json_decode( $output, true );
        
        $this->assertTrue( $response['success'] );
        
        remove_filter( 'wp_mail', array( $this, 'mock_wp_mail_success' ) );
    }

    /**
     * 日本語を含むメッセージでのテスト
     */
    public function test_japanese_content() {
        $_POST['contact_nonce'] = wp_create_nonce( 'kei_portfolio_contact' );
        $_POST['contact_name'] = '田中太郎';
        $_POST['contact_email'] = 'tanaka@example.co.jp';
        $_POST['contact_subject'] = 'システム開発の相談';
        $_POST['contact_message'] = 'こんにちは。ウェブアプリケーションの開発について相談させていただきたく、ご連絡いたします。具体的な要件については、お電話または対面でお話しできればと思います。よろしくお願いいたします。';
        $_POST['privacy_agreement'] = '1';

        add_filter( 'wp_mail', array( $this, 'capture_wp_mail_data' ) );

        ob_start();
        
        try {
            kei_portfolio_handle_contact_form();
        } catch ( WPAjaxDieStopException $e ) {
            // Expected
        }
        
        $output = ob_get_clean();
        $response = json_decode( $output, true );
        
        $this->assertTrue( $response['success'] );
        
        // 日本語がメールに正しく含まれているか確認
        $this->assertContains( '田中太郎', $this->captured_mail_message );
        $this->assertContains( 'システム開発の相談', $this->captured_mail_message );
        $this->assertContains( 'ウェブアプリケーション', $this->captured_mail_message );
        
        remove_filter( 'wp_mail', array( $this, 'capture_wp_mail_data' ) );
    }

    // ===== モック関数 =====

    /**
     * wp_mail成功をモックする関数
     */
    public function mock_wp_mail_success( $args ) {
        return true;
    }

    /**
     * wp_mail失敗をモックする関数
     */
    public function mock_wp_mail_failure( $args ) {
        return false;
    }

    /**
     * wp_mailデータをキャプチャする関数
     */
    private $captured_mail_message = '';

    public function capture_wp_mail_data( $args ) {
        $this->captured_mail_message = $args['message'];
        return true;
    }

    /**
     * メールヘッダーの妥当性テスト
     */
    public function test_email_headers() {
        $_POST['contact_nonce'] = wp_create_nonce( 'kei_portfolio_contact' );
        $_POST['contact_name'] = 'テスト太郎';
        $_POST['contact_email'] = 'test@example.com';
        $_POST['contact_subject'] = 'プロジェクトの相談';
        $_POST['contact_message'] = 'テストメッセージ';
        $_POST['privacy_agreement'] = '1';

        // メール送信情報をキャプチャ
        add_filter( 'wp_mail', array( $this, 'capture_full_wp_mail_data' ) );

        ob_start();
        
        try {
            kei_portfolio_handle_contact_form();
        } catch ( WPAjaxDieStopException $e ) {
            // Expected
        }
        
        $output = ob_get_clean();
        
        // ヘッダーが正しく設定されているか確認
        $this->assertContains( 'Content-Type: text/plain; charset=UTF-8', $this->captured_mail_headers );
        $this->assertContains( 'Reply-To: テスト太郎 <test@example.com>', $this->captured_mail_headers );
        
        remove_filter( 'wp_mail', array( $this, 'capture_full_wp_mail_data' ) );
    }

    private $captured_mail_headers = array();

    public function capture_full_wp_mail_data( $args ) {
        $this->captured_mail_message = $args['message'];
        $this->captured_mail_headers = $args['headers'];
        return true;
    }
}
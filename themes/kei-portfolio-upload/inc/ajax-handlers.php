<?php
/**
 * AJAX処理ハンドラー
 *
 * @package Kei_Portfolio
 */

/**
 * Contact Form AJAX Handler
 */
function kei_portfolio_handle_contact_form() {
    // Nonce verification
    if ( ! wp_verify_nonce( $_POST['contact_nonce'], 'kei_portfolio_contact' ) ) {
        wp_send_json_error( 'セキュリティエラーが発生しました。' );
    }

    // Sanitize form data
    $name = sanitize_text_field( $_POST['contact_name'] );
    $email = sanitize_email( $_POST['contact_email'] );
    $subject = sanitize_text_field( $_POST['contact_subject'] );
    $message = sanitize_textarea_field( $_POST['contact_message'] );
    $privacy_agreement = isset( $_POST['privacy_agreement'] ) ? true : false;

    // Basic validation
    if ( empty( $name ) || empty( $email ) || empty( $subject ) || empty( $message ) ) {
        wp_send_json_error( '必須項目が入力されていません。' );
    }

    if ( ! is_email( $email ) ) {
        wp_send_json_error( 'メールアドレスの形式が正しくありません。' );
    }

    if ( ! $privacy_agreement ) {
        wp_send_json_error( 'プライバシーポリシーへの同意が必要です。' );
    }

    if ( strlen( $message ) > 2000 ) {
        wp_send_json_error( 'メッセージは2000文字以内で入力してください。' );
    }

    // Prepare email content
    $admin_email = get_option( 'admin_email' );
    $site_name = get_bloginfo( 'name' );
    $email_subject = sprintf( '[%s] お問い合わせ: %s', $site_name, $subject );
    
    $email_message = "お問い合わせフォームからメッセージが送信されました。\n\n";
    $email_message .= "お名前: " . $name . "\n";
    $email_message .= "メールアドレス: " . $email . "\n";
    $email_message .= "件名: " . $subject . "\n";
    $email_message .= "メッセージ:\n" . $message . "\n\n";
    $email_message .= "送信日時: " . current_time( 'Y-m-d H:i:s' ) . "\n";
    $email_message .= "送信者IP: " . $_SERVER['REMOTE_ADDR'] . "\n";

    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'Reply-To: ' . $name . ' <' . $email . '>',
        'From: ' . $site_name . ' <noreply@' . $_SERVER['SERVER_NAME'] . '>'
    );

    // Send email
    $mail_sent = wp_mail( $admin_email, $email_subject, $email_message, $headers );

    if ( $mail_sent ) {
        // Log successful submission (optional)
        error_log( "Contact form submission from: " . $email . " - Subject: " . $subject );
        
        wp_send_json_success( 'お問い合わせを受け付けました。24時間以内にご返信いたします。' );
    } else {
        // Log email failure
        error_log( "Failed to send contact form email from: " . $email );
        
        wp_send_json_error( 'メールの送信に失敗しました。しばらく時間をおいて再度お試しください。' );
    }
}

// Register AJAX handlers
add_action( 'wp_ajax_kei_portfolio_contact_submit', 'kei_portfolio_handle_contact_form' );
add_action( 'wp_ajax_nopriv_kei_portfolio_contact_submit', 'kei_portfolio_handle_contact_form' );
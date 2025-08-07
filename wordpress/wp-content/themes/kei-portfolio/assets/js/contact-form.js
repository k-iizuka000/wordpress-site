/**
 * Contact Form JavaScript
 * 
 * @package Kei_Portfolio
 */

jQuery(document).ready(function($) {
    const contactForm = $('#contact-form');
    const submitButton = $('#submit-button');
    const submitText = $('#submit-text');
    const submitLoader = $('#submit-loader');
    const messageTextarea = $('#message');
    const messageCount = $('#message-count');
    const successMessage = $('#submit-success-message');
    const errorMessage = $('#submit-error-message');
    
    // Message character counter
    messageTextarea.on('input', function() {
        const currentLength = this.value.length;
        messageCount.text(currentLength);
        
        if (currentLength > 500) {
            submitButton.prop('disabled', true);
            messageCount.parent().addClass('text-red-500');
        } else {
            submitButton.prop('disabled', false);
            messageCount.parent().removeClass('text-red-500');
        }
    });
    
    // Form submission
    contactForm.on('submit', function(e) {
        e.preventDefault();
        
        // Hide previous messages
        successMessage.addClass('hidden');
        errorMessage.addClass('hidden');
        
        // Show loading state
        submitButton.prop('disabled', true);
        submitText.text('送信中...');
        submitLoader.removeClass('hidden').addClass('inline-block');
        
        // Prepare form data
        const formData = new FormData(this);
        formData.append('action', 'kei_portfolio_contact_submit');
        
        // Send AJAX request
        $.ajax({
            url: kei_portfolio_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    // Success
                    successMessage.removeClass('hidden');
                    contactForm[0].reset();
                    messageCount.text('0');
                    
                    // Scroll to success message
                    $('html, body').animate({
                        scrollTop: successMessage.offset().top - 100
                    }, 500);
                } else {
                    // Error from server
                    errorMessage.find('p').text(response.data || 'エラーが発生しました。');
                    errorMessage.removeClass('hidden');
                }
            },
            error: function() {
                // AJAX error
                errorMessage.removeClass('hidden');
            },
            complete: function() {
                // Reset button state
                submitButton.prop('disabled', false);
                submitText.text('無料相談を申し込む');
                submitLoader.addClass('hidden');
                
                // Auto-hide messages after 5 seconds
                setTimeout(function() {
                    successMessage.addClass('hidden');
                    errorMessage.addClass('hidden');
                }, 5000);
            }
        });
    });
});
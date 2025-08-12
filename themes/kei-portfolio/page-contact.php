<?php
/**
 * Contact ページテンプレート
 *
 * @package Kei_Portfolio
 */

get_header(); ?>

    <main id="main" class="site-main">
        <div class="max-w-6xl mx-auto px-4 py-12">
            <?php
            while ( have_posts() ) :
                the_post();
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    
                    <!-- ページヘッダー -->
                    <header class="page-header text-center mb-16">
                        <h1 class="text-5xl font-bold text-gray-800 mb-4">
                            <?php echo esc_html( get_the_title() ); ?>
                        </h1>
                        <!-- <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                            お仕事のご相談、お問い合わせはお気軽にどうぞ
                        </p> -->
                        <p style="color: #dc2626; font-weight: bold;">
                            現在このページは機能していません。
                        </p>
                    </header>

                    <!-- ページコンテンツ -->
                    <div class="prose max-w-none mb-12">
                        <?php the_content(); ?>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                        
                        <!-- お問い合わせフォーム -->
                        <div class="bg-white p-8 rounded-lg shadow-sm border border-gray-100">
                            <h2 class="text-2xl font-bold text-gray-800 mb-6">お問い合わせフォーム</h2>
                            
                            <form id="contact-form" method="post" class="space-y-6">
                                <?php wp_nonce_field( 'kei_portfolio_contact', 'contact_nonce' ); ?>
                                
                                <!-- 名前 -->
                                <div>
                                    <label for="contact-name" class="block text-sm font-medium text-gray-700 mb-2">
                                        お名前 <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           id="contact-name" 
                                           name="contact_name" 
                                           required
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>

                                <!-- メールアドレス -->
                                <div>
                                    <label for="contact-email" class="block text-sm font-medium text-gray-700 mb-2">
                                        メールアドレス <span class="text-red-500">*</span>
                                    </label>
                                    <input type="email" 
                                           id="contact-email" 
                                           name="contact_email" 
                                           required
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>

                                <!-- 件名 -->
                                <div>
                                    <label for="contact-subject" class="block text-sm font-medium text-gray-700 mb-2">
                                        件名 <span class="text-red-500">*</span>
                                    </label>
                                    <select id="contact-subject" 
                                            name="contact_subject" 
                                            required
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">選択してください</option>
                                        <option value="プロジェクトの相談">プロジェクトの相談</option>
                                        <option value="見積もり依頼">見積もり依頼</option>
                                        <option value="技術相談">技術相談</option>
                                        <option value="採用・求人">採用・求人</option>
                                        <option value="その他">その他</option>
                                    </select>
                                </div>

                                <!-- メッセージ -->
                                <div>
                                    <label for="contact-message" class="block text-sm font-medium text-gray-700 mb-2">
                                        メッセージ <span class="text-red-500">*</span>
                                    </label>
                                    <textarea id="contact-message" 
                                              name="contact_message" 
                                              rows="6" 
                                              required
                                              placeholder="プロジェクトの詳細、予算、スケジュールなど、お気軽にお書きください。"
                                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                                </div>

                                <!-- プライバシーポリシー同意 -->
                                <div class="flex items-start space-x-3">
                                    <input type="checkbox" 
                                           id="privacy-agreement" 
                                           name="privacy_agreement" 
                                           required
                                           class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="privacy-agreement" class="text-sm text-gray-700">
                                        <span class="text-red-500">*</span>
                                        個人情報の取り扱いについて同意します。
                                        <span class="text-xs text-gray-500 block mt-1">
                                            お預かりした個人情報は、お問い合わせへの回答のみに使用いたします。
                                        </span>
                                    </label>
                                </div>

                                <!-- 送信ボタン -->
                                <button type="submit" 
                                        class="w-full bg-blue-600 text-white font-semibold py-4 px-6 rounded-lg hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                    <i class="ri-send-plane-line mr-2"></i>
                                    送信する
                                </button>
                            </form>

                            <!-- メッセージ表示エリア -->
                            <div id="contact-message-area" class="mt-6 hidden">
                                <div id="contact-success" class="p-4 bg-green-50 border border-green-200 rounded-lg hidden">
                                    <div class="flex items-center">
                                        <i class="ri-check-circle-line text-green-600 mr-2"></i>
                                        <span class="text-green-800">お問い合わせを送信いたしました。ありがとうございます。</span>
                                    </div>
                                </div>
                                <div id="contact-error" class="p-4 bg-red-50 border border-red-200 rounded-lg hidden">
                                    <div class="flex items-center">
                                        <i class="ri-error-warning-line text-red-600 mr-2"></i>
                                        <span class="text-red-800">送信に失敗しました。もう一度お試しください。</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- お問い合わせ情報 -->
                        <div class="space-y-8">
                            
                            <!-- 直接連絡 -->
                            <div class="bg-blue-50 p-8 rounded-lg">
                                <h3 class="text-2xl font-bold text-gray-800 mb-6">直接のご連絡</h3>
                                <div class="space-y-4">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center">
                                            <i class="ri-mail-line text-white text-xl"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-gray-800">メールアドレス</h4>
                                            <a href="mailto:contact@example.com" class="text-blue-600 hover:text-blue-800">
                                                contact@example.com
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 対応可能な業務 -->
                            <div class="bg-gray-50 p-8 rounded-lg">
                                <h3 class="text-2xl font-bold text-gray-800 mb-6">対応可能な業務</h3>
                                <div class="space-y-3">
                                    <div class="flex items-center space-x-3">
                                        <i class="ri-check-line text-green-600"></i>
                                        <span class="text-gray-700">Webアプリケーション開発</span>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <i class="ri-check-line text-green-600"></i>
                                        <span class="text-gray-700">システム設計・アーキテクチャ</span>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <i class="ri-check-line text-green-600"></i>
                                        <span class="text-gray-700">自動化ツール開発</span>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <i class="ri-check-line text-green-600"></i>
                                        <span class="text-gray-700">既存システムの改善・最適化</span>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <i class="ri-check-line text-green-600"></i>
                                        <span class="text-gray-700">技術相談・コードレビュー</span>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <i class="ri-check-line text-green-600"></i>
                                        <span class="text-gray-700">チーム開発支援</span>
                                    </div>
                                </div>
                            </div>

                            <!-- レスポンス時間 -->
                            <div class="bg-green-50 p-8 rounded-lg">
                                <h3 class="text-2xl font-bold text-gray-800 mb-6">レスポンス時間</h3>
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center">
                                        <i class="ri-time-line text-white text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="text-gray-700">
                                            通常、<span class="font-semibold text-green-700">24時間以内</span>にご返信いたします。
                                            <br>
                                            <span class="text-sm text-gray-600">土日祝日は翌営業日の対応となる場合があります。</span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- ソーシャルリンク -->
                            <div class="bg-gray-50 p-8 rounded-lg">
                                <h3 class="text-2xl font-bold text-gray-800 mb-6">SNS・ポートフォリオ</h3>
                                <div class="flex space-x-4">
                                    <a href="https://github.com/kei-aokiki" 
                                       target="_blank" 
                                       rel="noopener noreferrer"
                                       class="flex items-center justify-center w-12 h-12 bg-gray-700 text-white rounded-full hover:bg-gray-800 transition-colors">
                                        <i class="ri-github-line text-xl"></i>
                                    </a>
                                    <a href="https://linkedin.com/in/kei-aokiki" 
                                       target="_blank" 
                                       rel="noopener noreferrer"
                                       class="flex items-center justify-center w-12 h-12 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition-colors">
                                        <i class="ri-linkedin-line text-xl"></i>
                                    </a>
                                    <a href="https://twitter.com/kei_aokiki" 
                                       target="_blank" 
                                       rel="noopener noreferrer"
                                       class="flex items-center justify-center w-12 h-12 bg-blue-400 text-white rounded-full hover:bg-blue-500 transition-colors">
                                        <i class="ri-twitter-x-line text-xl"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ( get_edit_post_link() ) : ?>
                        <footer class="entry-footer mt-8 pt-4 border-t border-gray-200">
                            <?php
                            edit_post_link(
                                sprintf(
                                    wp_kses(
                                        /* translators: %s: Name of current post. Only visible to screen readers */
                                        __( 'Edit <span class="screen-reader-text">%s</span>', 'kei-portfolio' ),
                                        array(
                                            'span' => array(
                                                'class' => array(),
                                            ),
                                        )
                                    ),
                                    wp_kses_post( get_the_title() )
                                ),
                                '<span class="edit-link text-sm text-blue-600 hover:text-blue-800">',
                                '</span>'
                            );
                            ?>
                        </footer>
                    <?php endif; ?>
                </article>
                <?php
            endwhile; // End of the loop.
            ?>
        </div>
    </main><!-- #main -->

<script>
// Contact form handling
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('contact-form');
    const messageArea = document.getElementById('contact-message-area');
    const successMsg = document.getElementById('contact-success');
    const errorMsg = document.getElementById('contact-error');
    
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(form);
            formData.append('action', 'kei_portfolio_contact_submit');
            
            try {
                const response = await fetch('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                messageArea.classList.remove('hidden');
                
                if (result.success) {
                    successMsg.classList.remove('hidden');
                    errorMsg.classList.add('hidden');
                    form.reset();
                } else {
                    errorMsg.classList.remove('hidden');
                    successMsg.classList.add('hidden');
                }
                
                // Scroll to message
                messageArea.scrollIntoView({ behavior: 'smooth' });
                
            } catch (error) {
                console.error('Contact form error:', error);
                messageArea.classList.remove('hidden');
                errorMsg.classList.remove('hidden');
                successMsg.classList.add('hidden');
            }
        });
    }
});
</script>

<?php
get_footer();
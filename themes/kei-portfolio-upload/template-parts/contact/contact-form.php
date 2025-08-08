<?php
/**
 * Contact Form Template Part
 * 
 * @package Kei_Portfolio
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<section class="py-20 bg-white">
    <div class="max-w-4xl mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">
                プロジェクトのご相談
            </h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                以下のフォームにご記入いただくか、直接メールでお問い合わせください。
                詳しい要件をお聞かせいただければ、より具体的な提案をいたします。
            </p>
        </div>
        
        <div class="bg-gray-50 rounded-2xl p-8">
            <!-- Submit status messages -->
            <div id="submit-success-message" class="bg-green-50 border border-green-200 rounded-lg p-4 text-center mb-6 hidden">
                <div class="flex items-center justify-center text-green-600 mb-2">
                    <i class="ri-check-circle-line text-xl mr-2"></i>
                    <span class="font-semibold">送信完了</span>
                </div>
                <p class="text-green-700 text-sm">
                    お問い合わせありがとうございます。24時間以内にご返信いたします。
                </p>
            </div>
            
            <div id="submit-error-message" class="bg-red-50 border border-red-200 rounded-lg p-4 text-center mb-6 hidden">
                <div class="flex items-center justify-center text-red-600 mb-2">
                    <i class="ri-error-warning-line text-xl mr-2"></i>
                    <span class="font-semibold">送信エラー</span>
                </div>
                <p class="text-red-700 text-sm">
                    申し訳ございません。送信に失敗しました。しばらく時間をおいて再度お試しください。
                </p>
            </div>

            <form id="contact-form" class="space-y-6">
                <?php wp_nonce_field('kei_portfolio_contact_nonce', 'contact_nonce'); ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                            お名前 *
                        </label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                            placeholder="山田 太郎"
                        />
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                            メールアドレス *
                        </label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                            placeholder="example@company.com"
                        />
                    </div>
                </div>
                
                <div>
                    <label for="company" class="block text-sm font-semibold text-gray-700 mb-2">
                        会社名・組織名
                    </label>
                    <input
                        type="text"
                        id="company"
                        name="company"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                        placeholder="株式会社サンプル"
                    />
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="projectType" class="block text-sm font-semibold text-gray-700 mb-2">
                            プロジェクトの種類
                        </label>
                        <div class="relative">
                            <select
                                id="projectType"
                                name="project_type"
                                class="w-full px-4 py-3 pr-8 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none text-sm"
                            >
                                <option value="">選択してください</option>
                                <option value="automation">業務自動化ツール</option>
                                <option value="webapp">Webアプリ開発</option>
                                <option value="scraping">データ収集・分析</option>
                                <option value="api">API連携システム</option>
                                <option value="consultation">コンサルティング</option>
                                <option value="other">その他</option>
                            </select>
                            <div class="absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none">
                                <i class="ri-arrow-down-s-line text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <label for="budget" class="block text-sm font-semibold text-gray-700 mb-2">
                            ご予算
                        </label>
                        <div class="relative">
                            <select
                                id="budget"
                                name="budget"
                                class="w-full px-4 py-3 pr-8 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none text-sm"
                            >
                                <option value="">選択してください</option>
                                <option value="under50">50万円未満</option>
                                <option value="50to100">50万円〜100万円</option>
                                <option value="100to300">100万円〜300万円</option>
                                <option value="over300">300万円以上</option>
                                <option value="undecided">未定・相談したい</option>
                            </select>
                            <div class="absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none">
                                <i class="ri-arrow-down-s-line text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div>
                    <label for="timeline" class="block text-sm font-semibold text-gray-700 mb-2">
                        希望納期
                    </label>
                    <div class="relative">
                        <select
                            id="timeline"
                            name="timeline"
                            class="w-full px-4 py-3 pr-8 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none text-sm"
                        >
                            <option value="">選択してください</option>
                            <option value="urgent">1ヶ月以内（緊急）</option>
                            <option value="1to3months">1〜3ヶ月</option>
                            <option value="3to6months">3〜6ヶ月</option>
                            <option value="6months">6ヶ月以上</option>
                            <option value="flexible">柔軟に対応可能</option>
                        </select>
                        <div class="absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none">
                            <i class="ri-arrow-down-s-line text-gray-400"></i>
                        </div>
                    </div>
                </div>
                
                <div>
                    <label for="message" class="block text-sm font-semibold text-gray-700 mb-2">
                        プロジェクトの詳細・ご質問 *
                    </label>
                    <textarea
                        id="message"
                        name="message"
                        required
                        maxlength="500"
                        rows="6"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-vertical text-sm"
                        placeholder="現在の課題、実現したいこと、技術的な要件など、詳しくお聞かせください。（500文字以内）"
                    ></textarea>
                    <div class="text-right text-xs text-gray-500 mt-1">
                        <span id="message-count">0</span>/500文字
                    </div>
                </div>
                
                <div class="text-center">
                    <button
                        type="submit"
                        id="submit-button"
                        class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white px-8 py-4 rounded-full text-lg font-semibold transition-all transform hover:scale-105 disabled:transform-none whitespace-nowrap cursor-pointer"
                    >
                        <span id="submit-text">無料相談を申し込む</span>
                        <i id="submit-loader" class="ri-loader-4-line animate-spin mr-2 hidden"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<?php
// Contact form specific script enqueue
wp_enqueue_script(
    'kei-portfolio-contact-form',
    get_template_directory_uri() . '/assets/js/contact-form.js',
    array('jquery', 'kei-portfolio-script'),
    wp_get_theme()->get('Version'),
    true
);
?>
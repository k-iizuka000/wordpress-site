<?php
/**
 * フッターテンプレート
 *
 * @package Kei_Portfolio_Pro
 */
?>

    <footer class="bg-gray-50 border-t border-gray-200 mt-20">
        <div class="max-w-6xl mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- 左カラム: サイト紹介 -->
                <div>
                    <h3 class="font-['Pacifico'] text-2xl text-blue-600 mb-4">Portfolio</h3>
                    <p class="text-gray-600 leading-relaxed">
                        <?php echo esc_html( get_theme_mod( 'footer_description', 'フリーランスエンジニアとして、自動化ツールの開発を中心に活動しています。明るく前向きな姿勢で、お客様の課題解決に取り組みます。' ) ); ?>
                    </p>
                </div>
                
                <!-- 中央カラム: サイトマップ -->
                <div>
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">サイトマップ</h4>
                    <div class="space-y-2">
                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="block text-gray-600 hover:text-blue-600 transition-colors cursor-pointer">
                            ホーム
                        </a>
                        <a href="<?php echo esc_url( home_url( '/about' ) ); ?>" class="block text-gray-600 hover:text-blue-600 transition-colors cursor-pointer">
                            自己紹介
                        </a>
                        <a href="<?php echo esc_url( get_post_type_archive_link( 'project' ) ); ?>" class="block text-gray-600 hover:text-blue-600 transition-colors cursor-pointer">
                            制作実績
                        </a>
                        <a href="<?php echo esc_url( home_url( '/skills' ) ); ?>" class="block text-gray-600 hover:text-blue-600 transition-colors cursor-pointer">
                            スキル一覧
                        </a>
                        <a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="block text-gray-600 hover:text-blue-600 transition-colors cursor-pointer">
                            お問い合わせ
                        </a>
                    </div>
                </div>
                
                <!-- 右カラム: コンタクト情報 -->
                <div>
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">コンタクト</h4>
                    <div class="space-y-2">
                        <div class="flex items-center space-x-2">
                            <i class="ri-mail-line text-blue-600"></i>
                            <span class="text-gray-600"><?php echo esc_html( get_theme_mod( 'contact_email', 'contact@portfolio.com' ) ); ?></span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <i class="ri-bike-line text-green-600"></i>
                            <span class="text-gray-600"><?php echo esc_html( get_theme_mod( 'hobby_text', 'ロードバイクでリフレッシュ中' ) ); ?></span>
                        </div>
                        
                        <!-- ソーシャルリンク -->
                        <?php if ( get_theme_mod( 'social_github', '' ) || get_theme_mod( 'social_linkedin', '' ) || get_theme_mod( 'social_twitter', '' ) ) : ?>
                        <div class="mt-4">
                            <h5 class="text-sm font-medium text-gray-800 mb-2">フォローする</h5>
                            <div class="flex space-x-3">
                                <?php if ( $github_url = get_theme_mod( 'social_github', '' ) ) : ?>
                                    <a href="<?php echo esc_url( $github_url ); ?>" target="_blank" rel="noopener noreferrer" class="text-gray-600 hover:text-gray-800 transition-colors">
                                        <i class="ri-github-line text-lg" aria-hidden="true"></i>
                                        <span class="sr-only">GitHub</span>
                                    </a>
                                <?php endif; ?>
                                <?php if ( $linkedin_url = get_theme_mod( 'social_linkedin', '' ) ) : ?>
                                    <a href="<?php echo esc_url( $linkedin_url ); ?>" target="_blank" rel="noopener noreferrer" class="text-gray-600 hover:text-blue-600 transition-colors">
                                        <i class="ri-linkedin-line text-lg" aria-hidden="true"></i>
                                        <span class="sr-only">LinkedIn</span>
                                    </a>
                                <?php endif; ?>
                                <?php if ( $twitter_url = get_theme_mod( 'social_twitter', '' ) ) : ?>
                                    <a href="<?php echo esc_url( $twitter_url ); ?>" target="_blank" rel="noopener noreferrer" class="text-gray-600 hover:text-blue-400 transition-colors">
                                        <i class="ri-twitter-x-line text-lg" aria-hidden="true"></i>
                                        <span class="sr-only">Twitter</span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- フッターボトム -->
            <div class="border-t border-gray-300 mt-8 pt-8 text-center text-gray-600">
                <p>&copy; <?php echo esc_html( date( 'Y' ) ); ?> Freelance Engineer Portfolio. All rights reserved.</p>
            </div>
        </div>
    </footer>

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
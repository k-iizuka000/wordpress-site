<?php
/**
 * ヘッダーテンプレート - Reactから変換
 *
 * @package Kei_Portfolio
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    
    <?php wp_head(); ?>
    
    <!-- Service Workerエラー対策: 既存のService Worker登録を削除 -->
    <script>
    // Service Worker 404エラーを防ぐため、既存の登録をクリア
    if ('serviceWorker' in navigator) {
        // 既存のすべてのService Worker登録を取得して削除
        navigator.serviceWorker.getRegistrations().then(function(registrations) {
            for(let registration of registrations) {
                // 現在のサイトのService Workerのみ削除
                if (registration.scope.indexOf(window.location.origin) === 0) {
                    registration.unregister().then(function(success) {
                        if (success) {
                            console.log('Service Worker unregistered:', registration.scope);
                        }
                    });
                }
            }
        }).catch(function(error) {
            console.log('Service Worker クリアエラー:', error);
        });
    }
    </script>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
    <a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Skip to content', 'kei-portfolio' ); ?></a>

    <header class="bg-white/95 backdrop-blur-sm border-b border-blue-100 sticky top-0 z-50">
        <nav class="max-w-6xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="font-['Pacifico'] text-2xl text-blue-600 hover:text-blue-700 transition-colors">
                    Portfolio
                </a>
                
                <!-- デスクトップメニュー -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="text-gray-700 hover:text-blue-600 transition-colors whitespace-nowrap cursor-pointer">
                        ホーム
                    </a>
                    <a href="<?php echo esc_url( home_url( '/about' ) ); ?>" class="text-gray-700 hover:text-blue-600 transition-colors whitespace-nowrap cursor-pointer">
                        自己紹介
                    </a>
                    <a href="<?php echo esc_url( get_post_type_archive_link( 'project' ) ); ?>" class="text-gray-700 hover:text-blue-600 transition-colors whitespace-nowrap cursor-pointer">
                        制作実績
                    </a>
                    <a href="<?php echo esc_url( home_url( '/skills' ) ); ?>" class="text-gray-700 hover:text-blue-600 transition-colors whitespace-nowrap cursor-pointer">
                        スキル一覧
                    </a>
                    <a href="<?php echo esc_url( home_url( '/blog' ) ); ?>" class="text-gray-700 hover:text-blue-600 transition-colors whitespace-nowrap cursor-pointer">
                        ブログ
                    </a>
                    <a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="text-gray-700 hover:text-blue-600 transition-colors whitespace-nowrap cursor-pointer">
                        お問い合わせ
                    </a>
                </div>

                <!-- モバイルメニューボタン -->
                <button 
                    class="md:hidden w-6 h-6 flex items-center justify-center cursor-pointer"
                    id="mobile-menu-toggle"
                    aria-expanded="false"
                    aria-controls="mobile-menu"
                >
                    <i class="ri-menu-line text-xl" id="mobile-menu-icon"></i>
                </button>
            </div>

            <!-- モバイルメニュー -->
            <div class="md:hidden mt-4 py-4 border-t border-blue-100 hidden" id="mobile-menu">
                <div class="flex flex-col space-y-4">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="text-gray-700 hover:text-blue-600 transition-colors whitespace-nowrap cursor-pointer">
                        ホーム
                    </a>
                    <a href="<?php echo esc_url( home_url( '/about' ) ); ?>" class="text-gray-700 hover:text-blue-600 transition-colors whitespace-nowrap cursor-pointer">
                        自己紹介
                    </a>
                    <a href="<?php echo esc_url( get_post_type_archive_link( 'project' ) ); ?>" class="text-gray-700 hover:text-blue-600 transition-colors whitespace-nowrap cursor-pointer">
                        制作実績
                    </a>
                    <a href="<?php echo esc_url( home_url( '/skills' ) ); ?>" class="text-gray-700 hover:text-blue-600 transition-colors whitespace-nowrap cursor-pointer">
                        スキル一覧
                    </a>
                    <a href="<?php echo esc_url( home_url( '/blog' ) ); ?>" class="text-gray-700 hover:text-blue-600 transition-colors whitespace-nowrap cursor-pointer">
                        ブログ
                    </a>
                    <a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="text-gray-700 hover:text-blue-600 transition-colors whitespace-nowrap cursor-pointer">
                        お問い合わせ
                    </a>
                </div>
            </div>
        </nav>
    </header>
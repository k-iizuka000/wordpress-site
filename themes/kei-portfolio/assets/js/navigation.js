/**
 * ナビゲーション制御 - Reactから変換
 */
document.addEventListener('DOMContentLoaded', function() {
    // モバイルメニューの状態管理
    let isMenuOpen = false;
    
    const toggleButton = document.getElementById('mobile-menu-toggle');
    const mobileMenu = document.getElementById('mobile-menu');
    const menuIcon = document.getElementById('mobile-menu-icon');
    
    if (!toggleButton || !mobileMenu || !menuIcon) {
        return; // 必要な要素が見つからない場合は終了
    }
    
    // モバイルメニューの切り替え関数
    function toggleMobileMenu() {
        isMenuOpen = !isMenuOpen;
        
        // メニューの表示/非表示
        if (isMenuOpen) {
            mobileMenu.classList.remove('hidden');
            toggleButton.setAttribute('aria-expanded', 'true');
            menuIcon.className = 'ri-close-line text-xl';
        } else {
            mobileMenu.classList.add('hidden');
            toggleButton.setAttribute('aria-expanded', 'false');
            menuIcon.className = 'ri-menu-line text-xl';
        }
    }
    
    // クリックイベントリスナー
    toggleButton.addEventListener('click', toggleMobileMenu);
    
    // ウィンドウサイズ変更時の処理
    window.addEventListener('resize', function() {
        // デスクトップサイズ（768px以上）でメニューを閉じる
        if (window.innerWidth >= 768 && isMenuOpen) {
            isMenuOpen = false;
            mobileMenu.classList.add('hidden');
            toggleButton.setAttribute('aria-expanded', 'false');
            menuIcon.className = 'ri-menu-line text-xl';
        }
    });
    
    // モバイルメニューのリンククリック時にメニューを閉じる
    const mobileMenuLinks = mobileMenu.querySelectorAll('a');
    mobileMenuLinks.forEach(function(link) {
        link.addEventListener('click', function() {
            if (isMenuOpen) {
                toggleMobileMenu();
            }
        });
    });
});
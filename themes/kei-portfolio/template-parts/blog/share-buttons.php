<?php
/**
 * ソーシャルシェアボタンコンポーネント
 * 
 * Twitter、Facebook、LinkedIn、はてなブックマーク、コピー機能を提供
 * 
 * @package Kei_Portfolio
 * @since 1.0.0
 */

// single.php以外では表示しない
if (!is_single()) {
    return;
}

// シェアに必要な情報を取得
$post_title = get_the_title();
$post_url = get_permalink();
$post_excerpt = get_the_excerpt();
$post_excerpt = $post_excerpt ? wp_trim_words($post_excerpt, 20, '...') : wp_trim_words(get_the_content(), 20, '...');

// OGP画像URL
$og_image = '';
if (has_post_thumbnail()) {
    $og_image = get_the_post_thumbnail_url(null, 'large');
}

// エンコードされたURLとテキスト
$encoded_url = urlencode($post_url);
$encoded_title = urlencode($post_title);
$encoded_text = urlencode($post_excerpt);

// ソーシャルシェアURL
$twitter_url = "https://twitter.com/intent/tweet?url={$encoded_url}&text={$encoded_title}";
$facebook_url = "https://www.facebook.com/sharer/sharer.php?u={$encoded_url}";
$linkedin_url = "https://www.linkedin.com/sharing/share-offsite/?url={$encoded_url}";
$hatena_url = "https://b.hatena.ne.jp/entry/{$post_url}";
$line_url = "https://line.me/R/msg/text/?" . urlencode($post_title . ' ' . $post_url);
?>

<div class="social-share-section bg-gray-50 rounded-lg p-6 mb-8" data-post-url="<?php echo esc_attr($post_url); ?>" data-post-title="<?php echo esc_attr($post_title); ?>">
    <div class="share-header text-center mb-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-2">
            <i class="ri-share-line mr-2 text-blue-600"></i>
            この記事をシェア
        </h3>
        <p class="text-gray-600 text-sm">
            役に立った記事は友達や同僚とシェアしてください
        </p>
    </div>
    
    <div class="share-buttons flex flex-wrap justify-center gap-3">
        
        <!-- Twitter -->
        <a href="<?php echo esc_url($twitter_url); ?>" 
           target="_blank" 
           rel="noopener noreferrer"
           class="share-button twitter-share flex items-center px-4 py-2 bg-blue-400 text-white rounded-lg hover:bg-blue-500 transition-colors text-sm font-medium"
           data-share="twitter"
           aria-label="Twitterでシェア">
            <i class="ri-twitter-x-line mr-2"></i>
            Twitter
        </a>
        
        <!-- Facebook -->
        <a href="<?php echo esc_url($facebook_url); ?>" 
           target="_blank" 
           rel="noopener noreferrer"
           class="share-button facebook-share flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium"
           data-share="facebook"
           aria-label="Facebookでシェア">
            <i class="ri-facebook-line mr-2"></i>
            Facebook
        </a>
        
        <!-- LinkedIn -->
        <a href="<?php echo esc_url($linkedin_url); ?>" 
           target="_blank" 
           rel="noopener noreferrer"
           class="share-button linkedin-share flex items-center px-4 py-2 bg-blue-700 text-white rounded-lg hover:bg-blue-800 transition-colors text-sm font-medium"
           data-share="linkedin"
           aria-label="LinkedInでシェア">
            <i class="ri-linkedin-line mr-2"></i>
            LinkedIn
        </a>
        
        <!-- はてなブックマーク -->
        <a href="<?php echo esc_url($hatena_url); ?>" 
           target="_blank" 
           rel="noopener noreferrer"
           class="share-button hatena-share flex items-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-sm font-medium"
           data-share="hatena"
           aria-label="はてなブックマークに追加">
            <span class="mr-2 font-bold text-sm">B!</span>
            はてな
        </a>
        
        <!-- LINE -->
        <a href="<?php echo esc_url($line_url); ?>" 
           target="_blank" 
           rel="noopener noreferrer"
           class="share-button line-share flex items-center px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors text-sm font-medium"
           data-share="line"
           aria-label="LINEでシェア">
            <i class="ri-line-line mr-2"></i>
            LINE
        </a>
        
        <!-- URLコピー -->
        <button type="button" 
                class="share-button copy-url-button flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-sm font-medium"
                data-share="copy"
                aria-label="URLをコピー">
            <i class="ri-link mr-2"></i>
            <span class="copy-text">URLコピー</span>
        </button>
        
    </div>
    
    <!-- シェア数表示（オプション） -->
    <div class="share-counts mt-4 text-center text-sm text-gray-500" id="share-counts" style="display: none;">
        <span>この記事は <span id="total-shares">0</span> 回シェアされています</span>
    </div>
    
</div>

<!-- コピー成功メッセージ用のトースト -->
<div id="copy-toast" class="fixed top-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg transform translate-x-full opacity-0 transition-all duration-300 z-50">
    <div class="flex items-center">
        <i class="ri-check-line mr-2"></i>
        URLをクリップボードにコピーしました
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const shareSection = document.querySelector('.social-share-section');
    const postUrl = shareSection.dataset.postUrl;
    const postTitle = shareSection.dataset.postTitle;
    const copyButton = document.querySelector('.copy-url-button');
    const copyToast = document.getElementById('copy-toast');
    
    // シェアボタンのクリック追跡
    const shareButtons = document.querySelectorAll('.share-button');
    shareButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const shareType = this.dataset.share;
            
            // コピーボタンの場合
            if (shareType === 'copy') {
                e.preventDefault();
                copyUrlToClipboard();
                return;
            }
            
            // アナリティクス追跡（Google Analytics等に送信可能）
            if (typeof gtag !== 'undefined') {
                gtag('event', 'share', {
                    method: shareType,
                    content_type: 'article',
                    item_id: postUrl
                });
            }
            
            // ポップアップウィンドウでシェア（モバイル以外）
            if (!isMobile() && shareType !== 'line') {
                e.preventDefault();
                const width = 600;
                const height = 400;
                const left = (screen.width - width) / 2;
                const top = (screen.height - height) / 2;
                
                window.open(
                    this.href,
                    'share-popup',
                    `width=${width},height=${height},left=${left},top=${top},scrollbars=yes,resizable=yes`
                );
            }
        });
    });
    
    // URLコピー機能
    async function copyUrlToClipboard() {
        try {
            await navigator.clipboard.writeText(postUrl);
            showCopyToast();
            
            // ボタンテキストを一時的に変更
            const copyText = copyButton.querySelector('.copy-text');
            const originalText = copyText.textContent;
            copyText.textContent = 'コピー完了!';
            copyButton.classList.add('bg-green-600', 'hover:bg-green-700');
            copyButton.classList.remove('bg-gray-600', 'hover:bg-gray-700');
            
            setTimeout(() => {
                copyText.textContent = originalText;
                copyButton.classList.remove('bg-green-600', 'hover:bg-green-700');
                copyButton.classList.add('bg-gray-600', 'hover:bg-gray-700');
            }, 2000);
            
        } catch (err) {
            // クリップボードAPIが使えない場合のフォールバック
            const textArea = document.createElement('textarea');
            textArea.value = postUrl;
            textArea.style.position = 'fixed';
            textArea.style.opacity = '0';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                document.execCommand('copy');
                showCopyToast();
            } catch (copyErr) {
                console.warn('コピーに失敗しました:', copyErr);
                alert('URLのコピーに失敗しました。手動でコピーしてください: ' + postUrl);
            }
            
            document.body.removeChild(textArea);
        }
    }
    
    // コピー完了トースト表示
    function showCopyToast() {
        copyToast.classList.remove('translate-x-full', 'opacity-0');
        copyToast.classList.add('translate-x-0', 'opacity-100');
        
        setTimeout(() => {
            copyToast.classList.add('translate-x-full', 'opacity-0');
            copyToast.classList.remove('translate-x-0', 'opacity-100');
        }, 3000);
    }
    
    // モバイル判定
    function isMobile() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }
    
    // Web Share API対応（モバイルデバイスで利用可能な場合）
    if (navigator.share && isMobile()) {
        // ネイティブシェアボタンを追加
        const nativeShareButton = document.createElement('button');
        nativeShareButton.type = 'button';
        nativeShareButton.className = 'share-button native-share flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-sm font-medium';
        nativeShareButton.innerHTML = '<i class="ri-share-line mr-2"></i>シェア';
        nativeShareButton.setAttribute('aria-label', 'ネイティブシェア');
        
        nativeShareButton.addEventListener('click', async function() {
            try {
                await navigator.share({
                    title: postTitle,
                    url: postUrl,
                    text: postTitle
                });
            } catch (err) {
                console.warn('ネイティブシェアに失敗しました:', err);
            }
        });
        
        // ボタンを先頭に追加
        const shareButtonsContainer = document.querySelector('.share-buttons');
        shareButtonsContainer.insertBefore(nativeShareButton, shareButtonsContainer.firstChild);
    }
    
    // キーボードアクセシビリティ
    shareButtons.forEach(button => {
        button.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });
});
</script>

<style>
/* ホバーエフェクトとアニメーション */
.share-button {
    transition: all 0.2s ease;
}

.share-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.share-button:active {
    transform: translateY(0);
}

/* レスポンシブ対応 */
@media (max-width: 640px) {
    .share-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .share-button {
        width: 100%;
        max-width: 200px;
        justify-content: center;
    }
}

/* ダークモード対応（将来的な拡張） */
@media (prefers-color-scheme: dark) {
    .social-share-section {
        background-color: #1f2937;
        color: #f9fafb;
    }
}
</style>
/**
 * Technical Approach Animations
 * 技術的アプローチのアニメーション機能
 */
document.addEventListener('DOMContentLoaded', function() {
    // プログレスバーのアニメーション
    function animateProgressBars() {
        const valueItems = document.querySelectorAll('.value-item');
        
        valueItems.forEach((item, index) => {
            const percentage = parseInt(item.dataset.percentage);
            const progressBar = item.querySelector('.progress-bar');
            const counter = item.querySelector('.percentage-counter');
            
            setTimeout(() => {
                // プログレスバーのアニメーション
                progressBar.style.width = percentage + '%';
                
                // カウンターのアニメーション
                animateCounter(counter, 0, percentage, 1000);
            }, index * 200);
        });
    }
    
    // カウンターアニメーション関数
    function animateCounter(element, start, end, duration) {
        const range = end - start;
        const increment = range / (duration / 16);
        let current = start;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= end) {
                current = end;
                clearInterval(timer);
            }
            element.textContent = Math.floor(current) + '%';
        }, 16);
    }
    
    // Intersection Observer でアニメーションをトリガー
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateProgressBars();
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });
    
    const valuesSection = document.querySelector('.space-y-6');
    if (valuesSection) {
        observer.observe(valuesSection);
    }
    
    // アプローチカードのホバー効果
    const approachCards = document.querySelectorAll('.approach-card');
    approachCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.transition = 'transform 0.3s ease';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // 資料ダウンロードボタン
    const downloadBtn = document.querySelector('.download-btn');
    if (downloadBtn) {
        downloadBtn.addEventListener('click', function() {
            // 実際の実装では、PDFダウンロード処理を行う
            alert('実績資料のダウンロードを開始します。（実装予定）');
        });
    }
});
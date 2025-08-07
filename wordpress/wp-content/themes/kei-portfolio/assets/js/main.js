/**
 * メインJavaScriptファイル
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // モバイルメニューの開閉
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const mainNavigation = document.querySelector('.main-navigation');
    
    if (mobileMenuToggle && mainNavigation) {
        mobileMenuToggle.addEventListener('click', function() {
            mainNavigation.classList.toggle('is-open');
            this.setAttribute('aria-expanded', 
                this.getAttribute('aria-expanded') === 'true' ? 'false' : 'true'
            );
        });
    }
    
    // スムーススクロール
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href !== '#0') {
                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
    
    // ヘッダーのスクロール制御
    let lastScroll = 0;
    const header = document.querySelector('.site-header');
    
    if (header) {
        window.addEventListener('scroll', function() {
            const currentScroll = window.pageYOffset;
            
            if (currentScroll > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
            
            lastScroll = currentScroll;
        });
    }
    
    // フェードインアニメーション
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.fade-in').forEach(el => {
        observer.observe(el);
    });
    
    // プロジェクトフィルター（アーカイブページ用）
    const techFilter = document.getElementById('technology-filter');
    const industryFilter = document.getElementById('industry-filter');
    const sortOrder = document.getElementById('sort-order');
    
    function applyFilters() {
        const params = new URLSearchParams();
        
        if (techFilter && techFilter.value) {
            params.set('technology', techFilter.value);
        }
        if (industryFilter && industryFilter.value) {
            params.set('industry', industryFilter.value);
        }
        if (sortOrder && sortOrder.value !== 'date-desc') {
            params.set('orderby', sortOrder.value);
        }
        
        const queryString = params.toString();
        const newUrl = window.location.pathname + (queryString ? '?' + queryString : '');
        window.location.href = newUrl;
    }
    
    if (techFilter) techFilter.addEventListener('change', applyFilters);
    if (industryFilter) industryFilter.addEventListener('change', applyFilters);
    if (sortOrder) sortOrder.addEventListener('change', applyFilters);
});

// フォームバリデーション
function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('error');
            isValid = false;
        } else {
            field.classList.remove('error');
        }
    });
    
    return isValid;
}

// お問い合わせフォーム処理
document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.querySelector('.wpcf7-form');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                alert('必須項目を入力してください。');
            }
        });
    }
});
/**
 * Portfolio Project Filtering
 * プロジェクトフィルタリング機能
 */
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.category-filter-btn');
    const projectCards = document.querySelectorAll('.project-card');
    const noResults = document.getElementById('no-results');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const selectedCategory = this.dataset.category;
            
            // ボタンの状態を更新
            filterButtons.forEach(btn => {
                btn.classList.remove('bg-blue-600', 'text-white');
                btn.classList.add('bg-white', 'text-gray-600', 'hover:bg-gray-100');
            });
            this.classList.remove('bg-white', 'text-gray-600', 'hover:bg-gray-100');
            this.classList.add('bg-blue-600', 'text-white');
            
            // プロジェクトカードをフィルタリング
            let visibleCount = 0;
            
            projectCards.forEach(card => {
                const cardCategory = card.dataset.category;
                
                if (selectedCategory === 'all' || cardCategory === selectedCategory) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // 結果なしメッセージの表示/非表示
            if (visibleCount === 0) {
                noResults.classList.remove('hidden');
            } else {
                noResults.classList.add('hidden');
            }
        });
    });
});
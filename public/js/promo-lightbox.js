document.addEventListener('DOMContentLoaded', function() {
    // Client-side Instant Filter
    const promoFilter = document.getElementById('promo-hospital-filter');
    const promoCards = document.querySelectorAll('.promo-card');
    const promoEmptyState = document.getElementById('promo-empty-state');
    const promoGrid = document.getElementById('promo-grid');

    if (promoFilter) {
        promoFilter.addEventListener('change', function() {
            const selectedVal = this.value;
            let visibleCount = 0;

            promoCards.forEach(card => {
                const hosp = card.getAttribute('data-hospital');
                if (selectedVal === 'all' || hosp === selectedVal) {
                    // First make display flex so it's layouted, then fade in
                    card.style.display = 'flex';
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0) scale(1)';
                    }, 50);
                    visibleCount++;
                } else {
                    // Fade out
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(15px) scale(0.95)';
                    setTimeout(() => {
                        card.style.display = 'none';
                    }, 300);
                }
            });

            // Manage empty state
            setTimeout(() => {
                if (visibleCount === 0) {
                    promoGrid.style.opacity = '0';
                    setTimeout(() => {
                        promoGrid.style.display = 'none';
                        promoEmptyState.classList.remove('hidden');
                        promoEmptyState.style.opacity = '1';
                    }, 150);
                } else {
                    promoEmptyState.classList.add('hidden');
                    promoGrid.style.display = 'grid';
                    setTimeout(() => {
                        promoGrid.style.opacity = '1';
                    }, 50);
                }
            }, 300);
        });
    }
});

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

    // Lightbox Poster Modal functionality
    const lightbox = document.getElementById('promo-lightbox');
    const lightboxContent = document.getElementById('promo-lightbox-content');
    const lightboxImg = document.getElementById('promo-lightbox-img');
    const lightboxTitle = document.getElementById('promo-lightbox-title');
    const lightboxDesc = document.getElementById('promo-lightbox-desc');
    const lightboxClose = document.getElementById('promo-lightbox-close');

    const openLightbox = (imgSrc, titleText, descText) => {
        lightboxImg.src = imgSrc;
        lightboxTitle.textContent = titleText;
        lightboxDesc.textContent = descText;

        // Open overlay and animate contents
        lightbox.classList.remove('pointer-events-none');
        lightbox.classList.add('opacity-100');
        lightbox.style.opacity = '1';
        setTimeout(() => {
            lightboxContent.classList.remove('opacity-0', 'scale-95');
            lightboxContent.classList.add('opacity-100', 'scale-100');
        }, 50);
    };

    const closeLightbox = () => {
        lightboxContent.classList.remove('opacity-100', 'scale-100');
        lightboxContent.classList.add('opacity-0', 'scale-95');
        setTimeout(() => {
            lightbox.style.opacity = '0';
            lightbox.classList.add('pointer-events-none');
            lightbox.classList.remove('opacity-100');
        }, 300);
    };

    // Bind click to each card image or button
    promoCards.forEach(card => {
        const imgWrapper = card.querySelector('.promo-img-wrapper');
        const viewBtn = card.querySelector('.promo-view-btn');
        const img = card.querySelector('img');
        const title = card.querySelector('h3').textContent.trim();
        const desc = card.querySelector('p').textContent.trim();

        const handleOpen = (e) => {
            e.preventDefault();
            openLightbox(img.src, title, desc);
        };

        if (imgWrapper) imgWrapper.addEventListener('click', handleOpen);
        if (viewBtn) viewBtn.addEventListener('click', handleOpen);
    });

    if (lightboxClose) lightboxClose.addEventListener('click', closeLightbox);

    // Close on overlay clicking
    if (lightbox) {
        lightbox.addEventListener('click', function(e) {
            if (e.target === lightbox) {
                closeLightbox();
            }
        });
    }

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeLightbox();
        }
    });
});

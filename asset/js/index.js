(function () {
    "use strict";

    function initCarousel() {
        const inner  = document.getElementById('carousel-inner');
        const slides = inner ? Array.from(inner.querySelectorAll('.slide')) : [];
        const prev   = document.getElementById('prev');
        const next   = document.getElementById('next');

        if (!inner || !slides.length) return;

        let current = 0;

        const visibleCount = () => {
            if (window.innerWidth < 700)  return 1;
            if (window.innerWidth < 1100) return 2;
            return 3;
        };

        const getSlideWidth = () => {
            const style = window.getComputedStyle(inner);
            const gap   = parseFloat(style.gap) || 20;
            return slides[0].getBoundingClientRect().width + gap;
        };

        function slideTo(idx) {
            const max = Math.max(0, slides.length - visibleCount());
            current = Math.max(0, Math.min(idx, max));
            inner.style.transform = `translateX(-${current * getSlideWidth()}px)`;
        }

        inner.style.transition = 'transform 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94)';

        if (prev) prev.addEventListener('click', () => slideTo(current - 1));
        if (next) next.addEventListener('click', () => slideTo(current + 1));

        // Auto-play
        let timer = setInterval(() => {
            const max = slides.length - visibleCount();
            slideTo(current >= max ? 0 : current + 1);
        }, 4000);

        inner.addEventListener('mouseenter', () => clearInterval(timer));
        inner.addEventListener('mouseleave', () => {
            timer = setInterval(() => {
                const max = slides.length - visibleCount();
                slideTo(current >= max ? 0 : current + 1);
            }, 4000);
        });

        // Touch swipe
        let startX = 0;
        inner.addEventListener('touchstart', e => { startX = e.touches[0].clientX; });
        inner.addEventListener('touchend',   e => {
            const dx = e.changedTouches[0].clientX - startX;
            if (Math.abs(dx) > 40) slideTo(dx < 0 ? current + 1 : current - 1);
        });

        window.addEventListener('resize', () => slideTo(current));
    }

    // Attendre que le DOM + les styles soient appliqués
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCarousel);
    } else {
        // Laisser le navigateur peindre avant de lire offsetWidth
        requestAnimationFrame(() => requestAnimationFrame(initCarousel));
    }
})();
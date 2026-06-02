(function () {
    "use strict";

    // ── Carrousel catégories ───────────────────────────────
    const inner  = document.getElementById('carousel-inner');
    const slides = inner ? Array.from(inner.querySelectorAll('.slide')) : [];
    const prev   = document.getElementById('prev');
    const next   = document.getElementById('next');

    if (!inner || !slides.length) return;

    let current  = 0;
    const visibleCount = () => window.innerWidth < 700 ? 1 : window.innerWidth < 1100 ? 2 : 3;

    function slideTo(idx) {
        const max = slides.length - visibleCount();
        current = Math.max(0, Math.min(idx, max));
        const slideW = slides[0].offsetWidth + 20; // gap = 20px
        inner.style.transform = `translateX(-${current * slideW}px)`;
    }

    inner.style.transition = 'transform 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94)';

    if (prev) prev.addEventListener('click', () => slideTo(current - 1));
    if (next) next.addEventListener('click', () => slideTo(current + 1));

    // Auto-play
    let timer = setInterval(() => slideTo(current + 1 > slides.length - visibleCount() ? 0 : current + 1), 4000);
    inner.addEventListener('mouseenter', () => clearInterval(timer));
    inner.addEventListener('mouseleave', () => {
        timer = setInterval(() => slideTo(current + 1 > slides.length - visibleCount() ? 0 : current + 1), 4000);
    });

    // Touch swipe
    let startX = 0;
    inner.addEventListener('touchstart', e => { startX = e.touches[0].clientX; });
    inner.addEventListener('touchend',   e => {
        const dx = e.changedTouches[0].clientX - startX;
        if (Math.abs(dx) > 40) slideTo(dx < 0 ? current + 1 : current - 1);
    });

    window.addEventListener('resize', () => slideTo(current));
})();

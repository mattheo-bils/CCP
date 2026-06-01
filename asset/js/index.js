(async () => {
    "use strict";
    await fetchProduits();

    // ── Carrousel catégories ──────────────────────────────────────────────
    const slideTimeout = 5000;
    const prev    = document.querySelector('#prev');
    const next    = document.querySelector('#next');
    const $slides = document.querySelectorAll('.slide');
    let currentSlide = 0;
    let intervalId;

    function slideTo(index) {
        if (index >= $slides.length) index = 0;
        if (index < 0) index = $slides.length - 1;
        currentSlide = index;
        $slides.forEach($s => $s.style.transform = `translateX(-${currentSlide * 100}%)`);
    }

    if (prev && next) {
        prev.addEventListener('click', () => slideTo(--currentSlide));
        next.addEventListener('click', () => slideTo(++currentSlide));
        intervalId = setInterval(() => { currentSlide++; slideTo(currentSlide); }, slideTimeout);

        $slides.forEach($s => {
            let startX, endX;
            $s.addEventListener('mouseover', () => clearInterval(intervalId));
            $s.addEventListener('mouseout',  () => { intervalId = setInterval(() => { currentSlide++; slideTo(currentSlide); }, slideTimeout); });
            $s.addEventListener('touchstart', e => { startX = e.touches[0].clientX; });
            $s.addEventListener('touchend',   e => {
                endX = e.changedTouches[0].clientX;
                if (startX > endX) slideTo(currentSlide + 1);
                else if (startX < endX) slideTo(currentSlide - 1);
            });
        });
    }

    // ── Cartes tendances cliquables ───────────────────────────────────────
    document.querySelectorAll('#trending-cards .card').forEach(card => {
        card.style.cursor = 'pointer';
        const titre   = card.querySelector('.card-title')?.textContent.trim().toLowerCase();
        const produit = produits.find(p => p.titre.toLowerCase() === titre);
        if (produit) {
            card.addEventListener('click', e => {
                if (e.target.closest('.btn-add')) return;
                window.location.href = `views/produit.php?id=${produit.id}`;
            });
        }
    });
})();

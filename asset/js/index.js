/**
 * index.js — Carrousel des catégories (page d'accueil)
 *
 * Fonctionnalités :
 *   - Navigation par boutons précédent/suivant
 *   - Auto-play toutes les 4 secondes (pause au survol)
 *   - Swipe tactile sur mobile
 *   - Recalcul de la position au redimensionnement
 */
(function () {
    'use strict';

    function initCarousel() {
        const inner  = document.getElementById('carousel-inner');
        const slides = inner ? Array.from(inner.querySelectorAll('.slide')) : [];
        const prev   = document.getElementById('prev');
        const next   = document.getElementById('next');

        if (!inner || !slides.length) return;

        let current = 0; // Index du slide actuel

        // ── Nombre de slides visibles selon la largeur d'écran ──
        const visibleCount = () => {
            if (window.innerWidth < 700)  return 1;
            if (window.innerWidth < 1100) return 2;
            return 3;
        };

        // ── Largeur d'un slide + gap pour calculer le déplacement ──
        const getSlideWidth = () => {
            const gap = parseFloat(window.getComputedStyle(inner).gap) || 20;
            return slides[0].getBoundingClientRect().width + gap;
        };

        // ── Déplace le carrousel vers le slide d'index idx ──────
        function slideTo(idx) {
            const max = Math.max(0, slides.length - visibleCount());
            current   = Math.max(0, Math.min(idx, max)); // Borner entre 0 et max
            inner.style.transform = `translateX(-${current * getSlideWidth()}px)`;
        }

        // Transition CSS fluide
        inner.style.transition = 'transform 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94)';

        // ── Boutons de navigation ────────────────────────────────
        if (prev) prev.addEventListener('click', () => slideTo(current - 1));
        if (next) next.addEventListener('click', () => slideTo(current + 1));

        // ── Auto-play : avance toutes les 4 secondes ─────────────
        let timer = setInterval(() => {
            const max = slides.length - visibleCount();
            slideTo(current >= max ? 0 : current + 1); // Retour au début si fin atteinte
        }, 4000);

        // Pause de l'auto-play au survol de la souris
        inner.addEventListener('mouseenter', () => clearInterval(timer));
        inner.addEventListener('mouseleave', () => {
            timer = setInterval(() => {
                const max = slides.length - visibleCount();
                slideTo(current >= max ? 0 : current + 1);
            }, 4000);
        });

        // ── Swipe tactile (mobile) ───────────────────────────────
        let startX = 0;
        inner.addEventListener('touchstart', e => { startX = e.touches[0].clientX; });
        inner.addEventListener('touchend', e => {
            const dx = e.changedTouches[0].clientX - startX;
            if (Math.abs(dx) > 40) {
                slideTo(dx < 0 ? current + 1 : current - 1); // Gauche = suivant, droite = précédent
            }
        });

        // ── Recalcul au redimensionnement de la fenêtre ──────────
        window.addEventListener('resize', () => slideTo(current));
    }

    // Double requestAnimationFrame pour s'assurer que le navigateur
    // a peint les slides avant de lire leurs dimensions
    requestAnimationFrame(() => requestAnimationFrame(initCarousel));
})();

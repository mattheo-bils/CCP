/**
 * index.js — Carrousel des catégories sur la page d'accueil
 *
 * Gère le défilement horizontal des slides de catégories.
 * Fonctionnalités : boutons prev/next, auto-play, swipe tactile, responsive.
 */
(function () {
    'use strict'; // Mode strict : détecte les erreurs silencieuses JS

    /** Initialise le carrousel une fois que le DOM est prêt */
    function initCarousel() {
        // Récupération de la piste du carrousel et de ses slides
        const inner  = document.getElementById('carousel-inner'); // Conteneur des slides
        const slides = inner ? Array.from(inner.querySelectorAll('.slide')) : []; // Liste des slides
        const prev   = document.getElementById('prev'); // Bouton "précédent"
        const next   = document.getElementById('next'); // Bouton "suivant"

        // Arrêt si les éléments n'existent pas
        if (!inner || !slides.length) return;

        let current = 0; // Index du slide actuellement visible (premier slide = 0)

        // ── Calcul du nombre de slides visibles ───────────────
        // Retourne le nombre de slides affichées simultanément selon la largeur
        const visibleCount = () => {
            if (window.innerWidth < 700)  return 1; // Mobile : 1 slide
            if (window.innerWidth < 1100) return 2; // Tablette : 2 slides
            return 3; // Desktop : 3 slides
        };

        // ── Calcul de la largeur d'un slide + gap ─────────────
        // getBoundingClientRect() retourne les dimensions réelles après rendu CSS
        const getSlideWidth = () => {
            const gap = parseFloat(window.getComputedStyle(inner).gap) || 20; // Gap CSS ou 20px par défaut
            return slides[0].getBoundingClientRect().width + gap; // Largeur slide + espace entre slides
        };

        // ── Déplacement vers un slide spécifique ──────────────
        /**
         * @param {number} idx - Index du slide cible
         */
        function slideTo(idx) {
            // Calcul du déplacement maximum (ne pas aller trop loin)
            const max = Math.max(0, slides.length - visibleCount());
            // Borner l'index entre 0 et max pour éviter de sortir des limites
            current = Math.max(0, Math.min(idx, max));
            // Déplacement horizontal par translation CSS
            inner.style.transform = `translateX(-${current * getSlideWidth()}px)`;
        }

        // Animation fluide pour les transitions entre slides
        inner.style.transition = 'transform 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94)';

        // ── Boutons de navigation ──────────────────────────────
        // Clic sur "précédent" : recule d'un slide
        if (prev) prev.addEventListener('click', () => slideTo(current - 1));
        // Clic sur "suivant" : avance d'un slide
        if (next) next.addEventListener('click', () => slideTo(current + 1));

        // ── Auto-play : défilement automatique toutes les 4s ──
        let timer = setInterval(() => {
            const max = slides.length - visibleCount();
            // Si on est au dernier slide, retour au début ; sinon avance d'un
            slideTo(current >= max ? 0 : current + 1);
        }, 4000); // 4000ms = 4 secondes

        // Pause de l'auto-play quand la souris survole le carrousel
        inner.addEventListener('mouseenter', () => clearInterval(timer));

        // Reprise de l'auto-play quand la souris quitte le carrousel
        inner.addEventListener('mouseleave', () => {
            timer = setInterval(() => {
                const max = slides.length - visibleCount();
                slideTo(current >= max ? 0 : current + 1);
            }, 4000);
        });

        // ── Support du swipe tactile (mobile/tablette) ────────
        let startX = 0; // Position X au début du touch

        // Mémorise la position initiale du doigt
        inner.addEventListener('touchstart', e => {
            startX = e.touches[0].clientX;
        });

        // Calcule la direction du swipe à la fin du touch
        inner.addEventListener('touchend', e => {
            const dx = e.changedTouches[0].clientX - startX; // Déplacement horizontal
            if (Math.abs(dx) > 40) { // Seuil minimum de 40px pour éviter les faux positifs
                slideTo(dx < 0 ? current + 1 : current - 1); // Gauche = suivant, droite = précédent
            }
        });

        // ── Recalcul au redimensionnement de la fenêtre ───────
        // Les dimensions changent quand on redimensionne, il faut recalculer
        window.addEventListener('resize', () => slideTo(current));
    }

    // Double requestAnimationFrame pour s'assurer que le navigateur
    // a complètement rendu les slides avant de lire leurs dimensions.
    // Un seul rAF ne suffit pas car le premier frame peut être avant le paint.
    requestAnimationFrame(() => requestAnimationFrame(initCarousel));
})();

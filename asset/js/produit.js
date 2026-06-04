/**
 * produit.js — Interactions client sur la fiche produit
 *
 * Toute la donnée produit est rendue côté PHP (produit.php).
 * Ce script gère uniquement les miniatures d'images si présentes.
 */
document.addEventListener('DOMContentLoaded', () => {

    // ── Miniatures d'images ───────────────────────────────
    // Clique sur une miniature → change l'image principale
    document.querySelectorAll('.image-autre').forEach(thumb => {
        thumb.addEventListener('click', () => {
            const main = document.getElementById('main-img');
            if (main) {
                main.src = thumb.src; // Remplace l'image principale
                // Retire la classe active de toutes les miniatures
                document.querySelectorAll('.image-autre')
                    .forEach(t => t.classList.remove('active'));
                thumb.classList.add('active'); // Active la miniature cliquée
            }
        });
    });

});

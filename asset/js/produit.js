/**
 * produit.js — Interactions client uniquement.
 * Toute la donnée produit est rendue par PHP ; ce script gère
 * uniquement les miniatures d'images (si présentes).
 */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.image-autre').forEach(thumb => {
        thumb.addEventListener('click', () => {
            const main = document.getElementById('main-img');
            if (main) {
                main.src = thumb.src;
                document.querySelectorAll('.image-autre')
                    .forEach(t => t.classList.remove('active'));
                thumb.classList.add('active');
            }
        });
    });
});

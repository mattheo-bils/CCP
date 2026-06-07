/**
 * produit.js — Interactions client sur la fiche produit
 *
 * Toute la donnée produit est rendue côté serveur par PHP (produit.php).
 * Ce script gère uniquement les miniatures d'images si elles sont présentes.
 * (La fiche produit peut avoir plusieurs photos du même manga)
 */
document.addEventListener('DOMContentLoaded', () => {

    // ── Galerie de miniatures ──────────────────────────────────

    // Sélection de toutes les images miniatures (classe .image-autre)
    document.querySelectorAll('.image-autre').forEach(thumb => {

        // Au clic sur une miniature...
        thumb.addEventListener('click', () => {
            // Récupération de l'image principale
            const main = document.getElementById('main-img');

            if (main) {
                // Remplacement de l'image principale par celle de la miniature cliquée
                main.src = thumb.src;

                // Retrait de la classe 'active' de toutes les miniatures
                document.querySelectorAll('.image-autre')
                    .forEach(t => t.classList.remove('active'));

                // Ajout de la classe 'active' sur la miniature cliquée
                // (permet de la mettre en surbrillance via CSS)
                thumb.classList.add('active');
            }
        });
    });

});

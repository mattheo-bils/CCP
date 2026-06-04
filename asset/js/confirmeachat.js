/**
 * confirmeachat.js — Nettoyage après confirmation de commande
 *
 * Vide le panier localStorage après une commande réussie.
 * Le panier BDD est vidé côté PHP dans achat.php (DELETE FROM panier).
 */
localStorage.removeItem('mm_cart');

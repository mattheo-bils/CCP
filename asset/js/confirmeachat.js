/**
 * confirmeachat.js — Nettoyage après confirmation de commande
 *
 * Ce script s'exécute automatiquement quand la page de confirmation
 * est chargée, c'est-à-dire après une commande réussie.
 *
 * Il vide le panier du localStorage pour les invités.
 * Pour les utilisateurs connectés, le panier BDD est vidé
 * côté PHP dans achat.php (DELETE FROM panier WHERE utilisateur_id = ?).
 */

// Suppression de la clé 'mm_cart' du localStorage
// Si la clé n'existe pas, localStorage.removeItem() ne génère pas d'erreur
localStorage.removeItem('mm_cart');

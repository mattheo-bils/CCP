/**
 * data.js — Chargement des données produits depuis l'API PHP
 *
 * ATTENTION : Ce fichier est conservé pour compatibilité mais n'est plus
 * utilisé dans la version actuelle du site.
 *
 * Dans l'ancienne architecture :
 *   - Ce fichier chargeait tous les produits en JS au démarrage
 *   - catalogue.js et search.js filtraient ensuite ce tableau côté client
 *
 * Dans la nouvelle architecture :
 *   - Le catalogue est rendu côté PHP avec filtres GET (catalogue.php)
 *   - La recherche appelle directement api/search_api.php (search.js)
 *   - Le panier appelle api/panier.php (panier.js et header.js)
 *   - Ce fichier et data.js ne sont donc plus nécessaires
 *
 * Si vous voyez ce fichier chargé dans footer.php, vous pouvez le supprimer
 * de la liste $pageJs.
 */

// Variable globale qui contiendra les produits après fetchProduits()
// Initialisée à tableau vide pour éviter les erreurs si fetchProduits() échoue
let produits = [];

/**
 * Charge tous les produits depuis l'API PHP et les stocke dans `produits`.
 * Cette fonction était appelée au chargement des pages catalogue et produit.
 *
 * L'API api/produits.php retourne un tableau JSON de tous les produits.
 * @returns {Promise<void>}
 */
async function fetchProduits() {
    try {
        // Appel à l'API produits (chemin relatif depuis views/)
        const res = await fetch('../api/produits.php');
        // Décodage du JSON et stockage dans la variable globale
        produits = await res.json();
    } catch(e) {
        // En cas d'erreur réseau ou JSON invalide, produits reste []
        // Les fonctions qui utilisent produits (filtrer, afficherProduits)
        // afficheront simplement "aucun produit trouvé"
        console.error('Erreur chargement produits', e);
    }
}
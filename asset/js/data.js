// data.js — données chargées dynamiquement depuis l'API PHP
// La variable `produits` est peuplée via fetchProduits() appelé au chargement

let produits = [];

async function fetchProduits() {
    try {
        const res = await fetch('../api/produits.php');
        produits = await res.json();
    } catch(e) {
        console.error('Erreur chargement produits', e);
    }
}

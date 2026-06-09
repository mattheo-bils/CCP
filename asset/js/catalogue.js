/**
 * catalogue.js — Ancien script de filtrage du catalogue côté client
 *
 * ATTENTION : Ce fichier est conservé pour compatibilité mais n'est plus
 * utilisé dans la version actuelle du site. Le filtrage se fait maintenant
 * entièrement côté PHP (catalogue.php) via des paramètres GET.
 *
 * Ce script dépendait de la variable globale `produits` chargée par data.js,
 * qui a été supprimée au profit d'une API PHP.
 */

// Récupération des éléments du DOM pour le catalogue
const container  = document.querySelector('.fullcard');          // Grille des cartes produits
const checkboxes = document.querySelectorAll('.filtre input[type="checkbox"]'); // Cases à cocher des catégories
const range      = document.getElementById('price-range');       // Slider de prix maximum
const display    = document.getElementById('price-display');     // Affichage de la valeur du slider
const countEl    = document.getElementById('catalogue-count');   // Compteur de résultats

// Détection de la position dans l'arborescence pour les liens et images
// Depuis views/ : les chemins doivent remonter d'un niveau avec "../"
const inViews = window.location.pathname.includes('/views/');

// ── Synchronisation de l'affichage du prix ────────────────────
// Met à jour le label de prix en temps réel pendant le déplacement du slider
if (range && display) {
    range.addEventListener('input', () => {
        // toFixed(2) force 2 décimales, replace remplace le point par une virgule
        display.textContent = parseFloat(range.value).toFixed(2).replace('.', ',') + ' €';
        filtrer(); // Relance le filtrage à chaque changement de prix
    });
}

// ── Affichage des produits filtrés ────────────────────────────

/**
 * Vide la grille et réinjecte les cartes produits filtrées
 * @param {Array} liste - Tableau de produits à afficher
 */
function afficherProduits(liste) {
    container.innerHTML = ''; // Vide la grille existante

    // Cas "aucun résultat"
    if (liste.length === 0) {
        container.innerHTML = '<p style="color:var(--grey-400);grid-column:1/-1;padding:40px 0">Aucun produit trouvé.</p>';
        if (countEl) countEl.textContent = '0 produit';
        return;
    }

    // Mise à jour du compteur de résultats avec pluriel conditionnel
    if (countEl) countEl.textContent = liste.length + ' produit' + (liste.length > 1 ? 's' : '');

    // Création d'une carte HTML pour chaque produit
    liste.forEach(produit => {
        // Lien vers la fiche produit selon la position dans l'arborescence
        const href = inViews
            ? `produit.php?id=${produit.id}`
            : `views/produit.php?id=${produit.id}`;

        // Chemin de l'image selon la position dans l'arborescence
        const imgSrc = inViews
            ? `../${produit.image}`
            : produit.image;

        // Création de la carte comme lien <a>
        const card = document.createElement('a');
        card.className = 'card card-link'; // Classes CSS pour le style et le clic
        card.href      = href;

        // Structure HTML de la carte : image + corps (titre, prix, bouton)
        card.innerHTML = `
            <img src="${imgSrc}" alt="${produit.titre}">
            <div class="card-body">
                <div class="card-title">${produit.titre}</div>
                <div class="card-row">
                    <span class="card-price">${produit.prix.toFixed(2).replace('.', ',')} €</span>
                    <span class="card-tome">Tome ${produit.tome}</span>
                </div>
                <div class="card-row" style="margin-top:10px">
                    <!-- data-attributes utilisés par header.js pour l'ajout au panier -->
                    <button class="btn-add"
                            data-id="${produit.id}"
                            data-titre="${produit.titre}"
                            data-prix="${produit.prix.toFixed(2).replace('.', ',')}"
                            data-img="${imgSrc}"
                            onclick="event.preventDefault()">
                        + Ajouter
                    </button>
                </div>
            </div>
        `;
        container.appendChild(card); // Ajout à la grille
    });
}

// ── Fonction de filtrage ──────────────────────────────────────

/**
 * Filtre les produits selon les catégories cochées et le prix maximum.
 * Utilise la variable globale `produits` (chargée par data.js).
 */
function filtrer() {
    // Récupération des slugs de catégories dont la case est cochée
    const catsActives = [...checkboxes]
        .filter(cb => cb.checked) // Garde uniquement les cases cochées
        .map(cb => {
            // Remonte jusqu'au bouton parent qui porte l'attribut data-categorie
            return cb.closest('[data-categorie]')?.dataset.categorie;
        })
        .filter(Boolean); // Supprime les valeurs null/undefined

    // Prix maximum : valeur du slider ou infini si slider absent
    const prixMax = range ? parseFloat(range.value) : Infinity;

    // Filtrage du tableau global produits
    const filtrés = produits.filter(p => {
        // catOk : true si aucune catégorie sélectionnée (tout afficher) ou si le produit correspond
        const catOk  = catsActives.length === 0 || catsActives.includes(p.categorie);
        // prixOk : true si le prix du produit ne dépasse pas le maximum
        const prixOk = p.prix <= prixMax;
        return catOk && prixOk; // Le produit s'affiche seulement si les deux conditions sont vraies
    });

    afficherProduits(filtrés);
}

// ── Pré-sélection depuis l'URL ────────────────────────────────
// Si l'URL contient ?categorie=shonen, la case correspondante est pré-cochée
const urlCat = new URLSearchParams(window.location.search).get('categorie');
if (urlCat) {
    checkboxes.forEach(cb => {
        const btn = cb.closest('[data-categorie]'); // Bouton parent de la checkbox
        if (btn?.dataset.categorie === urlCat) {
            cb.checked = true;                // Coche la case
            btn.classList.add('active');      // Applique le style "actif"
        }
    });
}

// ── Événements des boutons de filtre ─────────────────────────
// Chaque bouton filtre toggle sa classe active et relance le filtrage
document.querySelectorAll('.filtre-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        btn.classList.toggle('active'); // Toggle visuel du bouton
        filtrer(); // Refiltrage immédiat
    });
});

// ── Affichage initial ─────────────────────────────────────────
// Affiche tous les produits au chargement de la page
afficherProduits(produits);
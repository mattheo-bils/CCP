/**
 * achat.js — Scripts de la page de finalisation de commande
 *
 * Gère :
 *   1. Le formatage automatique du numéro de carte bancaire
 *   2. Le formatage automatique de la date d'expiration
 *   3. Le chargement du récapitulatif depuis localStorage (invités uniquement)
 */

// ── Formatage de la date d'expiration ─────────────────────

/**
 * Formate la saisie de la date d'expiration au format "MM / AA"
 * Appelé via l'attribut oninput sur le champ #expiry dans le HTML.
 * @param {HTMLInputElement} input - Le champ de saisie
 */
function formatExpiry(input) {
    // Supprime tous les caractères non numériques
    let v = input.value.replace(/\D/g, '');
    // Insère " / " après les 2 premiers chiffres (le mois)
    // Ex: "1225" devient "12 / 25"
    if (v.length >= 2) v = v.slice(0, 2) + ' / ' + v.slice(2, 4);
    input.value = v; // Met à jour la valeur affichée
}

// ── Formatage du numéro de carte bancaire ─────────────────

const carteInput = document.getElementById('carte'); // Champ numéro de carte
if (carteInput) {
    carteInput.addEventListener('input', function () {
        // 1. Supprime tous les caractères non numériques
        // 2. Ajoute un espace tous les 4 chiffres
        // 3. trim() supprime l'espace éventuel à la fin
        // Ex: "4242424242424242" → "4242 4242 4242 4242"
        this.value = this.value.replace(/\D/g, '').replace(/(.{4})/g, '$1 ').trim();
    });
}

// ── Filtre numérique pour le CVV ──────────────────────────

const cvvInput = document.getElementById('cvv'); // Champ CVV
if (cvvInput) {
    cvvInput.addEventListener('input', function () {
        // N'autorise que les chiffres (supprime lettres et symboles)
        this.value = this.value.replace(/\D/g, '');
    });
}

// ── Récapitulatif panier pour les invités ─────────────────

/**
 * Ce bloc s'exécute uniquement si l'élément #recap-js-loading est présent.
 * Cet élément est injecté par PHP quand il ne peut pas charger le panier
 * depuis la BDD (utilisateur non connecté et pas d'achat direct ?id=X).
 * Dans ce cas, on charge le panier depuis le localStorage.
 */
(function () {
    const jsLoading  = document.getElementById('recap-js-loading'); // Placeholder "chargement..."
    if (!jsLoading) return; // PHP a déjà rendu le récap → rien à faire

    const recapItems  = document.getElementById('recap-items');  // Zone des articles
    const totEl       = document.getElementById('recap-total'); // Affichage du total
    const panierInput = document.getElementById('panier_json'); // Champ caché pour soumission PHP

    // Lecture du panier depuis le localStorage
    let cart = [];
    try {
        cart = JSON.parse(localStorage.getItem('mm_cart') || '[]');
    } catch (e) {} // Tableau vide si JSON corrompu

    // Cas panier vide
    if (!cart.length) {
        jsLoading.textContent = 'Votre panier est vide.';
        totEl.textContent     = '0,00 €';
        return;
    }

    // Pré-remplissage du champ caché avec le contenu du panier
    // PHP lira ce champ à la soumission pour enregistrer la commande
    if (panierInput) panierInput.value = JSON.stringify(cart);

    // ── Construction du HTML du récapitulatif ─────────────
    let html = '', total = 0;

    cart.forEach(item => {
        // Conversion du prix (gère "7,20" et "7.20")
        const p = parseFloat(String(item.prix).replace(',', '.')) || 0;
        total  += p * (item.qty || 1); // Accumulation du total

        // Image du produit (si disponible)
        const img = item.img
            ? `<img src="${item.img}" alt="${item.titre}"
                    style="width:46px;height:66px;object-fit:cover;border-radius:6px;flex-shrink:0">`
            : '';

        // Ligne HTML pour cet article
        html += `
        <div style="display:flex;align-items:center;gap:14px;padding:10px 0;
                    border-bottom:1px solid rgba(255,255,255,0.05)">
            ${img}
            <span style="flex:1">
                ${item.titre}
                <span style="color:var(--grey-400)">× ${item.qty || 1}</span>
            </span>
            <span style="color:var(--gold-light);font-weight:700">
                ${(p * (item.qty || 1)).toFixed(2).replace('.', ',')} €
            </span>
        </div>`;
    });

    // Injection du HTML dans la page
    recapItems.innerHTML = html;
    // Affichage du total formaté
    totEl.textContent = total.toFixed(2).replace('.', ',') + ' €';

    // Mise à jour du champ caché juste avant la soumission du formulaire
    // Au cas où le contenu du panier aurait changé entre le chargement de la page
    // et le clic sur "Confirmer"
    const form = document.getElementById('achat-form');
    if (form) {
        form.addEventListener('submit', () => {
            if (panierInput) panierInput.value = JSON.stringify(cart);
        });
    }
})();

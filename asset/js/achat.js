/**
 * achat.js — Page de finalisation de commande
 *
 * Responsabilités :
 *   - Formatage automatique du numéro de carte bancaire (espaces tous les 4 chiffres)
 *   - Formatage automatique de la date d'expiration (MM / AA)
 *   - Chargement du récap panier depuis localStorage pour les invités
 *     (uniquement si PHP n'a pas pu le charger depuis la BDD)
 */

// ── Formatage de la date d'expiration ─────────────────────
/**
 * Appelé via oninput sur le champ #expiry.
 * Transforme "1225" en "12 / 25".
 */
function formatExpiry(input) {
    let v = input.value.replace(/\D/g, ''); // Supprime tout sauf les chiffres
    if (v.length >= 2) v = v.slice(0, 2) + ' / ' + v.slice(2, 4);
    input.value = v;
}

// ── Formatage du numéro de carte ──────────────────────────
// Ajoute un espace tous les 4 chiffres : "4242424242424242" → "4242 4242 4242 4242"
const carteInput = document.getElementById('carte');
if (carteInput) {
    carteInput.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').replace(/(.{4})/g, '$1 ').trim();
    });
}

// ── Filtre numérique pour le CVV ─────────────────────────
const cvvInput = document.getElementById('cvv');
if (cvvInput) {
    cvvInput.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, ''); // Chiffres uniquement
    });
}

// ── Récap panier invité (chargement depuis localStorage) ──
/**
 * Ce bloc s'exécute uniquement si #recap-js-loading est présent dans le DOM,
 * c'est-à-dire quand PHP n'a pas pu récupérer le panier depuis la BDD
 * (utilisateur non connecté et pas d'achat direct).
 */
(function () {
    const jsLoading  = document.getElementById('recap-js-loading');
    if (!jsLoading) return; // Le récap a déjà été rendu par PHP → rien à faire

    const recapItems  = document.getElementById('recap-items');
    const totEl       = document.getElementById('recap-total');
    const panierInput = document.getElementById('panier_json'); // Champ caché pour soumission

    // Lecture du panier depuis le localStorage
    let cart = [];
    try { cart = JSON.parse(localStorage.getItem('mm_cart') || '[]'); } catch (e) {}

    if (!cart.length) {
        jsLoading.textContent = 'Votre panier est vide.';
        totEl.textContent     = '0,00 €';
        return;
    }

    // Pré-remplir le champ caché pour que PHP reçoive le panier à la soumission
    if (panierInput) panierInput.value = JSON.stringify(cart);

    // ── Construction du HTML du récap ─────────────────────
    let html = '', total = 0;
    cart.forEach(item => {
        const p = parseFloat(String(item.prix).replace(',', '.')) || 0;
        total  += p * (item.qty || 1);

        const img = item.img
            ? `<img src="${item.img}" alt="${item.titre}"
                    style="width:46px;height:66px;object-fit:cover;border-radius:6px;flex-shrink:0">`
            : '';

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

    recapItems.innerHTML = html;
    totEl.textContent    = total.toFixed(2).replace('.', ',') + ' €';

    // Mise à jour du champ caché juste avant la soumission du formulaire
    // (au cas où le cart aurait changé entre le chargement et la soumission)
    const form = document.getElementById('achat-form');
    if (form) {
        form.addEventListener('submit', () => {
            if (panierInput) panierInput.value = JSON.stringify(cart);
        });
    }
})();

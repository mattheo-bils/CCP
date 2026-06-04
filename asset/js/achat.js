/**
 * achat.js — Récapitulatif commande + formatage carte bancaire
 */

function formatExpiry(input) {
    let v = input.value.replace(/\D/g, '');
    if (v.length >= 2) v = v.slice(0, 2) + ' / ' + v.slice(2, 4);
    input.value = v;
}

// Formatage numéro de carte
const carteInput = document.getElementById('carte');
if (carteInput) {
    carteInput.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').replace(/(.{4})/g, '$1 ').trim();
    });
}

const cvvInput = document.getElementById('cvv');
if (cvvInput) {
    cvvInput.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '');
    });
}

// Récap invité (uniquement si #recap-js-loading est présent,
// c'est-à-dire quand PHP n'a pas pu charger le panier depuis la BDD)
(function () {
    const jsLoading  = document.getElementById('recap-js-loading');
    if (!jsLoading) return;

    const recapItems  = document.getElementById('recap-items');
    const totEl       = document.getElementById('recap-total');
    const panierInput = document.getElementById('panier_json');

    let cart = [];
    try { cart = JSON.parse(localStorage.getItem('mm_cart') || '[]'); } catch (e) {}

    if (!cart.length) {
        jsLoading.textContent = 'Votre panier est vide.';
        totEl.textContent     = '0,00 €';
        return;
    }

    if (panierInput) panierInput.value = JSON.stringify(cart);

    let html = '', total = 0;
    cart.forEach(item => {
        const p = parseFloat(String(item.prix).replace(',', '.')) || 0;
        total  += p * (item.qty || 1);
        const img = item.img
            ? `<img src="${item.img}" alt="${item.titre}" style="width:46px;height:66px;object-fit:cover;border-radius:6px;flex-shrink:0">`
            : '';
        html += `
        <div style="display:flex;align-items:center;gap:14px;padding:10px 0;border-bottom:1px solid rgba(255,255,255,0.05)">
            ${img}
            <span style="flex:1">${item.titre} <span style="color:var(--grey-400)">× ${item.qty || 1}</span></span>
            <span style="color:var(--gold-light);font-weight:700">${(p * (item.qty || 1)).toFixed(2).replace('.', ',')} €</span>
        </div>`;
    });

    recapItems.innerHTML = html;
    totEl.textContent    = total.toFixed(2).replace('.', ',') + ' €';

    const form = document.getElementById('achat-form');
    if (form) {
        form.addEventListener('submit', () => {
            if (panierInput) panierInput.value = JSON.stringify(cart);
        });
    }
})();

/**
 * header.js — Script global chargé sur toutes les pages
 */

// ── Burger menu ───────────────────────────────────────────
const burgerBtn = document.getElementById('burger-btn');
const mobileNav = document.getElementById('mobile-nav');

if (burgerBtn && mobileNav) {
    burgerBtn.addEventListener('click', () => {
        burgerBtn.classList.toggle('active');
        mobileNav.classList.toggle('open');
    });
    document.addEventListener('click', e => {
        if (!e.target.closest('header')) {
            burgerBtn.classList.remove('active');
            mobileNav.classList.remove('open');
        }
    });
}

// ── Badge panier ──────────────────────────────────────────
async function updateCartBadge() {
    const badge = document.getElementById('cart-count');
    if (!badge) return;

    const inViews = window.location.pathname.includes('/views/');
    const apiBase = inViews ? '../api/panier.php' : 'api/panier.php';

    try {
        const res  = await fetch(apiBase + '?action=list');
        const data = await res.json();
        let count = 0;
        if (data.connected && data.items) {
            count = data.items.reduce((sum, i) => sum + (parseInt(i.quantite) || 1), 0);
        } else {
            const cart = JSON.parse(localStorage.getItem('mm_cart') || '[]');
            count = cart.reduce((sum, i) => sum + (i.qty || 1), 0);
        }
        badge.textContent   = count;
        badge.style.display = count > 0 ? 'flex' : 'none';
    } catch (e) {
        try {
            const cart  = JSON.parse(localStorage.getItem('mm_cart') || '[]');
            const count = cart.reduce((sum, i) => sum + (i.qty || 1), 0);
            badge.textContent   = count;
            badge.style.display = count > 0 ? 'flex' : 'none';
        } catch (e2) {}
    }
}
updateCartBadge();

// ── Boutons "Ajouter au panier" ───────────────────────────
document.addEventListener('click', async e => {
    const btn = e.target.closest('.btn-add');
    if (!btn) return;

    e.preventDefault();
    e.stopPropagation();

    const id    = btn.dataset.id    || '0';
    const titre = btn.dataset.titre || '';
    const prix  = btn.dataset.prix  || '0';
    const img   = btn.dataset.img   || '';

    const inViews = window.location.pathname.includes('/views/');
    const apiBase = inViews ? '../api/panier_api.php' : 'api/panier_api.php';

    try {
        const res  = await fetch(apiBase + '?action=list');
        const data = await res.json();
        if (data.connected) {
            const form = new FormData();
            form.append('action',     'add');
            form.append('produit_id', id);
            form.append('quantite',   '1');
            await fetch(apiBase, { method: 'POST', body: form });
        } else {
            const cart = JSON.parse(localStorage.getItem('mm_cart') || '[]');
            const idx  = cart.findIndex(i => i.id === id);
            if (idx >= 0) { cart[idx].qty = (cart[idx].qty || 1) + 1; }
            else { cart.push({ id, titre, prix, img, qty: 1 }); }
            localStorage.setItem('mm_cart', JSON.stringify(cart));
        }
    } catch (e) {
        const cart = JSON.parse(localStorage.getItem('mm_cart') || '[]');
        const idx  = cart.findIndex(i => i.id === id);
        if (idx >= 0) { cart[idx].qty = (cart[idx].qty || 1) + 1; }
        else { cart.push({ id, titre, prix, img, qty: 1 }); }
        localStorage.setItem('mm_cart', JSON.stringify(cart));
    }

    updateCartBadge();

    const orig = btn.textContent;
    btn.textContent       = '✓ Ajouté !';
    btn.style.background  = '#27AE60';
    btn.disabled          = true;
    setTimeout(() => {
        btn.textContent      = orig;
        btn.style.background = '';
        btn.disabled         = false;
    }, 1500);
});

// ── Styles dynamiques ─────────────────────────────────────
const style = document.createElement('style');
style.textContent = `
    /* Dropdown résultats de recherche */
    #search-results {
        display: none;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.3);
        overflow: hidden;
        position: absolute;
        width: 100%;
        z-index: 999;
        top: calc(100% + 6px);
        left: 0;
    }
    #search-results.visible { display: block; }

    /* Item individuel */
    .search-result-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 14px;
        cursor: pointer;
        transition: background 0.15s;
        text-decoration: none;
        background: #ffffff;
        color: #131937;
    }
    .search-result-item:hover,
    .search-result-item[aria-selected="true"] {
        background: #f0f4ff;
        color: #131937;
    }
    .search-result-item img {
        width: 36px; height: 52px;
        object-fit: cover;
        border-radius: 4px;
        flex-shrink: 0;
    }
    .search-result-info { display: flex; flex-direction: column; }
    .search-result-titre {
        font-weight: 600;
        font-size: 14px;
        color: #131937 !important;
    }
    .search-result-meta  { font-size: 12px; color: #666666; }
    .search-result-prix  {
        margin-left: auto;
        font-weight: 700;
        font-size: 14px;
        color: #c44444;
        white-space: nowrap;
    }
    .search-no-result    { padding: 14px; text-align: center; color: #888; font-size: 14px; }
    .search-highlight    { background: #ffe082; border-radius: 2px; color: #131937; }

    /* Alertes */
    .alert-error {
        background: rgba(192,57,43,0.15);
        border: 1px solid var(--red);
        border-radius: 12px;
        padding: 14px 18px;
        margin-bottom: 20px;
    }
    .alert-error p { color: var(--red-bright); margin: 4px 0; font-size: 0.9rem; }
    .alert-success {
        background: rgba(39,174,96,0.15);
        border: 1px solid #27AE60;
        border-radius: 12px;
        padding: 14px 18px;
        margin-bottom: 20px;
        color: #2ECC71;
        font-size: 0.95rem;
    }
    .card-link { text-decoration: none; display: block; }
`;
document.head.appendChild(style);

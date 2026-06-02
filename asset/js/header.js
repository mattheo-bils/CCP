// ── Burger menu ───────────────────────────────────────────
const burgerBtn = document.getElementById('burger-btn');
const mobileNav = document.getElementById('mobile-nav');

if (burgerBtn && mobileNav) {
    burgerBtn.addEventListener('click', () => {
        burgerBtn.classList.toggle('active');
        mobileNav.classList.toggle('open');
    });
    // Fermer en cliquant ailleurs
    document.addEventListener('click', e => {
        if (!e.target.closest('header')) {
            burgerBtn.classList.remove('active');
            mobileNav.classList.remove('open');
        }
    });
}

// ── Badge panier ──────────────────────────────────────────
function updateCartBadge() {
    const badge = document.getElementById('cart-count');
    if (!badge) return;
    try {
        const cart  = JSON.parse(localStorage.getItem('mm_cart') || '[]');
        const count = cart.reduce((sum, item) => sum + (item.qty || 1), 0);
        badge.textContent = count;
        badge.style.display = count > 0 ? 'flex' : 'none';
    } catch (e) {}
}
updateCartBadge();

// ── Boutons "Ajouter au panier" ───────────────────────────
document.addEventListener('click', e => {
    const btn = e.target.closest('.btn-add');
    if (!btn) return;

    e.preventDefault();
    e.stopPropagation();

    const id    = btn.dataset.id    || '0';
    const titre = btn.dataset.titre || btn.closest('.card')?.querySelector('.card-title')?.textContent?.trim() || 'Manga';
    const prix  = btn.dataset.prix  || btn.closest('.card')?.querySelector('.card-price')?.textContent?.replace('€','').trim() || '0';
    const img   = btn.dataset.img   || '';

    try {
        const cart = JSON.parse(localStorage.getItem('mm_cart') || '[]');
        const idx  = cart.findIndex(i => i.id === id);
        if (idx >= 0) {
            cart[idx].qty = (cart[idx].qty || 1) + 1;
        } else {
            cart.push({ id, titre, prix, img, qty: 1 });
        }
        localStorage.setItem('mm_cart', JSON.stringify(cart));
        updateCartBadge();
    } catch (e) {}

    // Feedback visuel
    const orig = btn.textContent;
    btn.textContent  = '✓ Ajouté !';
    btn.style.background = '#27AE60';
    btn.disabled = true;
    setTimeout(() => {
        btn.textContent  = orig;
        btn.style.background = '';
        btn.disabled = false;
    }, 1500);
});

// ── Styles dynamiques search results ──────────────────────
const style = document.createElement('style');
style.textContent = `
    #search-results { display: none; }
    #search-results.visible { display: block; }
    .search-result-item {
        display: flex; align-items: center; gap: 12px;
        padding: 10px 14px; cursor: pointer;
        transition: background 0.15s; color: #131937;
        text-decoration: none;
    }
    .search-result-item:hover,
    .search-result-item[aria-selected="true"] { background: #f0f4ff; }
    .search-result-item img { width: 36px; height: 52px; object-fit: cover; border-radius: 4px; flex-shrink: 0; }
    .search-result-info { display: flex; flex-direction: column; }
    .search-result-titre { font-weight: 600; font-size: 14px; }
    .search-result-meta  { font-size: 12px; color: #666; }
    .search-result-prix  { margin-left: auto; font-weight: 700; font-size: 14px; color: #c44444; white-space: nowrap; }
    .search-no-result    { padding: 14px; text-align: center; color: #888; font-size: 14px; }
    .search-highlight    { background: #ffe082; border-radius: 2px; }

    /* Alerte error/success globale */
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
    /* Card cliquable */
    .card-link { text-decoration: none; display: block; }
`;
document.head.appendChild(style);

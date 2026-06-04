/**
 * header.js — Script global chargé sur toutes les pages
 *
 * Responsabilités :
 *   - Burger menu (navigation mobile)
 *   - Badge panier (nombre d'articles)
 *   - Boutons "Ajouter au panier" (BDD si connecté, localStorage sinon)
 *   - Styles dynamiques pour la recherche et les alertes
 */

// ── Burger menu (navigation mobile) ──────────────────────
const burgerBtn = document.getElementById('burger-btn');
const mobileNav = document.getElementById('mobile-nav');

if (burgerBtn && mobileNav) {
    burgerBtn.addEventListener('click', () => {
        burgerBtn.classList.toggle('active');
        mobileNav.classList.toggle('open');
    });
    // Ferme le menu mobile en cliquant en dehors du header
    document.addEventListener('click', e => {
        if (!e.target.closest('header')) {
            burgerBtn.classList.remove('active');
            mobileNav.classList.remove('open');
        }
    });
}

// ── Badge panier ──────────────────────────────────────────
/**
 * Met à jour le badge de quantité dans le header.
 * Si l'utilisateur est connecté → compte depuis la BDD via l'API.
 * Si invité → compte depuis le localStorage.
 */
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
            // Utilisateur connecté : somme des quantités en BDD
            count = data.items.reduce((sum, i) => sum + (parseInt(i.quantite) || 1), 0);
        } else {
            // Invité : somme des quantités en localStorage
            const cart = JSON.parse(localStorage.getItem('mm_cart') || '[]');
            count = cart.reduce((sum, i) => sum + (i.qty || 1), 0);
        }

        badge.textContent   = count;
        badge.style.display = count > 0 ? 'flex' : 'none';
    } catch (e) {
        // Fallback localStorage si l'API est inaccessible
        try {
            const cart  = JSON.parse(localStorage.getItem('mm_cart') || '[]');
            const count = cart.reduce((sum, i) => sum + (i.qty || 1), 0);
            badge.textContent   = count;
            badge.style.display = count > 0 ? 'flex' : 'none';
        } catch (e2) {}
    }
}

// Initialisation du badge au chargement de la page
updateCartBadge();

// ── Boutons "Ajouter au panier" ───────────────────────────
/**
 * Délégation d'événement sur tous les boutons .btn-add de la page.
 * Utilise la BDD si connecté, localStorage sinon.
 */
document.addEventListener('click', async e => {
    const btn = e.target.closest('.btn-add');
    if (!btn) return;

    e.preventDefault();
    e.stopPropagation();

    // Récupération des données du produit depuis les data-attributes
    const id    = btn.dataset.id    || '0';
    const titre = btn.dataset.titre || '';
    const prix  = btn.dataset.prix  || '0';
    const img   = btn.dataset.img   || '';

    const inViews = window.location.pathname.includes('/views/');
    const apiBase = inViews ? '../api/panier.php' : 'api/panier.php';

    try {
        const res  = await fetch(apiBase + '?action=list');
        const data = await res.json();

        if (data.connected) {
            // Utilisateur connecté → ajout en BDD via l'API
            const form = new FormData();
            form.append('action',     'add');
            form.append('produit_id', id);
            form.append('quantite',   '1');
            await fetch(apiBase, { method: 'POST', body: form });
        } else {
            // Invité → ajout dans le localStorage
            const cart = JSON.parse(localStorage.getItem('mm_cart') || '[]');
            const idx  = cart.findIndex(i => i.id === id);
            if (idx >= 0) {
                cart[idx].qty = (cart[idx].qty || 1) + 1; // Incrémente si déjà présent
            } else {
                cart.push({ id, titre, prix, img, qty: 1 }); // Nouveau produit
            }
            localStorage.setItem('mm_cart', JSON.stringify(cart));
        }
    } catch (e) {
        // Fallback localStorage en cas d'erreur API
        const cart = JSON.parse(localStorage.getItem('mm_cart') || '[]');
        const idx  = cart.findIndex(i => i.id === id);
        if (idx >= 0) { cart[idx].qty = (cart[idx].qty || 1) + 1; }
        else { cart.push({ id, titre, prix, img, qty: 1 }); }
        localStorage.setItem('mm_cart', JSON.stringify(cart));
    }

    // Mise à jour du badge
    updateCartBadge();

    // Feedback visuel temporaire sur le bouton (1.5 secondes)
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

// ── Styles dynamiques injectés globalement ────────────────
/**
 * Styles pour les résultats de recherche et les alertes.
 * Injectés en JS car utilisés par plusieurs composants dynamiques.
 */
const style = document.createElement('style');
style.textContent = `
    /* Résultats de recherche — masqués par défaut */
    #search-results { display: none; }
    #search-results.visible { display: block; }

    /* Item de résultat de recherche */
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

    /* Alertes erreur */
    .alert-error {
        background: rgba(192,57,43,0.15);
        border: 1px solid var(--red);
        border-radius: 12px;
        padding: 14px 18px;
        margin-bottom: 20px;
    }
    .alert-error p { color: var(--red-bright); margin: 4px 0; font-size: 0.9rem; }

    /* Alertes succès */
    .alert-success {
        background: rgba(39,174,96,0.15);
        border: 1px solid #27AE60;
        border-radius: 12px;
        padding: 14px 18px;
        margin-bottom: 20px;
        color: #2ECC71;
        font-size: 0.95rem;
    }

    /* Carte cliquable sans décoration */
    .card-link { text-decoration: none; display: block; }
`;
document.head.appendChild(style);

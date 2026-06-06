/**
 * panier.js — Gestion de la page panier
 */
(function () {
    const container = document.getElementById('panier-items');
    const empty     = document.getElementById('panier-empty');
    const summary   = document.getElementById('panier-summary');
    if (!container) return;

    const inViews = window.location.pathname.includes('/views/');
    const apiBase = inViews ? '../api/panier.php' : 'api/panier.php';

    // ── Helpers localStorage ──────────────────────────────
    function getCartLocal() {
        try { return JSON.parse(localStorage.getItem('mm_cart') || '[]'); } catch (e) { return []; }
    }
    function saveCartLocal(cart) {
        localStorage.setItem('mm_cart', JSON.stringify(cart));
    }

    // ── Appel API ─────────────────────────────────────────
    async function apiCall(action, body = {}) {
        const form = new FormData();
        form.append('action', action);
        for (const [k, v] of Object.entries(body)) form.append(k, v);
        const res = await fetch(apiBase, { method: 'POST', body: form });
        return res.json();
    }

    // ── Rendu HTML ────────────────────────────────────────
    function renderItems(items) {
        Array.from(container.children).forEach(c => { if (c !== empty) c.remove(); });

        if (!items.length) {
            empty.style.display   = '';
            summary.style.display = 'none';
            return;
        }

        empty.style.display   = 'none';
        summary.style.display = '';

        let total = 0;

        items.forEach((item, idx) => {
            const prix = parseFloat(String(item.prix).replace(',', '.').replace('€', '').trim()) || 0;
            const qty  = parseInt(item.qty || item.quantite) || 1;
            // Stock disponible retourné par l'API (peut être absent pour les invités)
            const stock = parseInt(item.stock) || 999;
            total += prix * qty;

            // Correction du chemin image : l'API retourne "asset/img/..."
            // depuis la racine, il faut ajouter "../" quand on est dans views/
            let imgSrc = item.img || item.image || '';
            if (imgSrc && inViews && !imgSrc.startsWith('../') && !imgSrc.startsWith('http')) {
                imgSrc = '../' + imgSrc;
            }

            const imgTag = imgSrc
                ? `<img src="${imgSrc}" alt="${item.titre}" style="width:70px;height:100px;object-fit:cover;border-radius:6px;flex-shrink:0">`
                : '';

            // Désactiver + si quantité = stock max
            const plusDisabled = qty >= stock ? 'disabled style="opacity:0.4;cursor:not-allowed"' : '';

            const div = document.createElement('div');
            div.className = 'panier-item';
            div.innerHTML = `
                ${imgTag}
                <div class="panier-item-info">
                    <div class="panier-item-title">${item.titre}</div>
                    <div class="panier-item-price">${prix.toFixed(2).replace('.', ',')} €</div>
                    ${stock <= 3 && stock > 0 ? `<div style="font-size:0.75rem;color:var(--gold-light);margin-top:4px">⚠ Plus que ${stock} en stock</div>` : ''}
                </div>
                <div class="panier-qty">
                    <button data-idx="${idx}" data-id="${item.id || item.produit_id}" data-action="minus">−</button>
                    <span>${qty}</span>
                    <button data-idx="${idx}" data-id="${item.id || item.produit_id}" data-action="plus" ${plusDisabled}>+</button>
                </div>
                <button class="panier-remove material-symbols-outlined"
                        data-idx="${idx}" data-id="${item.id || item.produit_id}" title="Supprimer">delete</button>
            `;
            container.insertBefore(div, empty);
        });

        const fmt = v => v.toFixed(2).replace('.', ',') + ' €';
        document.getElementById('sous-total').textContent = fmt(total);
        document.getElementById('total').textContent      = fmt(total);
    }

    // ── Chargement initial ────────────────────────────────
    async function loadCart() {
        try {
            const res  = await fetch(apiBase + '?action=list');
            const data = await res.json();

            if (data.connected) {
                const local = getCartLocal();
                if (local.length) {
                    for (const item of local) {
                        await apiCall('add', { produit_id: item.id, quantite: item.qty || 1 });
                    }
                    localStorage.removeItem('mm_cart');
                    const res2  = await fetch(apiBase + '?action=list');
                    const data2 = await res2.json();
                    renderItems(data2.items || []);
                } else {
                    renderItems(data.items || []);
                }
            } else {
                renderItems(getCartLocal());
            }
        } catch (e) {
            renderItems(getCartLocal());
        }
    }

    // ── Événements ────────────────────────────────────────
    container.addEventListener('click', async e => {
        const btn = e.target.closest('[data-action], .panier-remove');
        if (!btn || btn.disabled) return;

        const produitId = btn.dataset.id;
        const idx       = parseInt(btn.dataset.idx, 10);

        let connected = false;
        try {
            const r = await fetch(apiBase + '?action=list');
            const d = await r.json();
            connected = d.connected;
        } catch (e) {}

        if (connected) {
            if (btn.classList.contains('panier-remove')) {
                await apiCall('delete', { produit_id: produitId });
            } else if (btn.dataset.action === 'plus') {
                const result = await apiCall('add', { produit_id: produitId, quantite: 1 });
                // Si stock insuffisant, ne pas recharger avec une erreur silencieuse
                if (result.error === 'stock_insuffisant' || result.error === 'rupture') {
                    await loadCart();
                    return;
                }
            } else if (btn.dataset.action === 'minus') {
                await apiCall('remove', { produit_id: produitId });
            }
        } else {
            const cart = getCartLocal();
            if (btn.classList.contains('panier-remove')) {
                cart.splice(idx, 1);
            } else if (btn.dataset.action === 'plus') {
                cart[idx].qty = (cart[idx].qty || 1) + 1;
            } else if (btn.dataset.action === 'minus') {
                cart[idx].qty = (cart[idx].qty || 1) - 1;
                if (cart[idx].qty <= 0) cart.splice(idx, 1);
            }
            saveCartLocal(cart);
        }

        await loadCart();
        if (typeof updateCartBadge === 'function') updateCartBadge();
    });

    loadCart();
})();
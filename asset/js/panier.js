(function () {
    const container = document.getElementById('panier-items');
    const empty     = document.getElementById('panier-empty');
    const summary   = document.getElementById('panier-summary');
    if (!container) return;

    function getCart() {
        try { return JSON.parse(localStorage.getItem('mm_cart') || '[]'); } catch(e) { return []; }
    }
    function saveCart(cart) {
        localStorage.setItem('mm_cart', JSON.stringify(cart));
    }

    function render() {
        const cart = getCart();
        // Supprimer les items existants sauf #panier-empty
        Array.from(container.children).forEach(c => { if (c !== empty) c.remove(); });

        if (!cart.length) {
            empty.style.display  = '';
            summary.style.display = 'none';
            return;
        }
        empty.style.display  = 'none';
        summary.style.display = '';

        let total = 0;

        cart.forEach((item, idx) => {
            const prix = parseFloat(String(item.prix).replace(',', '.').replace('€', '').trim()) || 0;
            const qty  = item.qty || 1;
            total += prix * qty;

            const div = document.createElement('div');
            div.className = 'panier-item';
            div.innerHTML = `
                ${item.img ? `<img src="${item.img}" alt="${item.titre}" style="width:70px;height:100px;object-fit:cover;border-radius:6px;flex-shrink:0">` : ''}
                <div class="panier-item-info">
                    <div class="panier-item-title">${item.titre}</div>
                    <div class="panier-item-price">${prix.toFixed(2).replace('.', ',')} €</div>
                </div>
                <div class="panier-qty">
                    <button data-idx="${idx}" data-action="minus">−</button>
                    <span>${qty}</span>
                    <button data-idx="${idx}" data-action="plus">+</button>
                </div>
                <button class="panier-remove material-symbols-outlined" data-idx="${idx}" title="Supprimer">delete</button>
            `;
            container.insertBefore(div, empty);
        });

        const fmt = v => v.toFixed(2).replace('.', ',') + ' €';
        document.getElementById('sous-total').textContent = fmt(total);
        document.getElementById('total').textContent      = fmt(total);
    }

    // Délégation d'événements
    container.addEventListener('click', e => {
        const btn = e.target.closest('[data-action], .panier-remove');
        if (!btn) return;
        const idx  = parseInt(btn.dataset.idx, 10);
        const cart = getCart();

        if (btn.classList.contains('panier-remove')) {
            cart.splice(idx, 1);
        } else if (btn.dataset.action === 'plus') {
            cart[idx].qty = (cart[idx].qty || 1) + 1;
        } else if (btn.dataset.action === 'minus') {
            cart[idx].qty = (cart[idx].qty || 1) - 1;
            if (cart[idx].qty <= 0) cart.splice(idx, 1);
        }

        saveCart(cart);
        render();
        // Mettre à jour le badge dans le header
        if (typeof updateCartBadge === 'function') updateCartBadge();
    });

    render();
})();

// panier.js — gestion du panier via localStorage

function renderPanier() {
    const container = document.getElementById('panier-items');
    const empty     = document.getElementById('panier-empty');
    const summary   = document.getElementById('panier-summary');
    if (!container) return;

    let cart = [];
    try { cart = JSON.parse(localStorage.getItem('mm_cart') || '[]'); } catch(e) {}

    // Vider le container (sauf l'empty)
    Array.from(container.children).forEach(c => { if (c !== empty) c.remove(); });

    if (cart.length === 0) {
        empty.style.display = '';
        summary.style.display = 'none';
        return;
    }
    empty.style.display = 'none';
    summary.style.display = '';

    let total = 0;
    cart.forEach((item, idx) => {
        const prix = parseFloat(item.prix.replace(',', '.').replace('€', '').trim()) || 0;
        total += prix * (item.qty || 1);

        const div = document.createElement('div');
        div.className = 'panier-item';
        div.innerHTML = `
            <div class="panier-item-info">
                <div class="panier-item-title">${item.titre}</div>
                <div class="panier-item-price">${item.prix}</div>
            </div>
            <div class="panier-qty">
                <button data-idx="${idx}" data-action="minus">−</button>
                <span>${item.qty || 1}</span>
                <button data-idx="${idx}" data-action="plus">+</button>
            </div>
            <button class="panier-remove material-symbols-outlined" data-idx="${idx}">delete</button>
        `;
        container.insertBefore(div, empty);
    });

    document.getElementById('sous-total').textContent = total.toFixed(2).replace('.', ',') + ' €';
    document.getElementById('total').textContent       = total.toFixed(2).replace('.', ',') + ' €';
}

document.addEventListener('click', e => {
    const btn = e.target.closest('[data-action], .panier-remove');
    if (!btn) return;
    let cart = JSON.parse(localStorage.getItem('mm_cart') || '[]');
    const idx = parseInt(btn.dataset.idx, 10);
    if (btn.classList.contains('panier-remove')) {
        cart.splice(idx, 1);
    } else if (btn.dataset.action === 'plus') {
        cart[idx].qty = (cart[idx].qty || 1) + 1;
    } else if (btn.dataset.action === 'minus') {
        cart[idx].qty = (cart[idx].qty || 1) - 1;
        if (cart[idx].qty <= 0) cart.splice(idx, 1);
    }
    localStorage.setItem('mm_cart', JSON.stringify(cart));
    renderPanier();
});

renderPanier();

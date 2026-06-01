// Burger menu toggle
const burgerBtn  = document.getElementById('burger-btn');
const mobileNav  = document.getElementById('mobile-nav');

if (burgerBtn && mobileNav) {
    burgerBtn.addEventListener('click', () => {
        burgerBtn.classList.toggle('active');
        mobileNav.classList.toggle('open');
    });
}

// Cart badge update from localStorage
function updateCartBadge() {
    const badge = document.getElementById('cart-count');
    if (!badge) return;
    try {
        const cart = JSON.parse(localStorage.getItem('mm_cart') || '[]');
        const count = cart.reduce((sum, item) => sum + (item.qty || 1), 0);
        badge.textContent = count;
        badge.style.display = count > 0 ? 'flex' : 'none';
    } catch(e) {}
}
updateCartBadge();

// "Ajouter" buttons
document.addEventListener('click', e => {
    const btn = e.target.closest('.btn-add, .ajouter');
    if (!btn) return;
    const titre = btn.dataset.titre || btn.closest('.card')?.querySelector('.card-title')?.textContent || 'Manga';
    const prix  = btn.dataset.prix  || btn.closest('.card')?.querySelector('.card-price')?.textContent  || '0€';
    try {
        const cart = JSON.parse(localStorage.getItem('mm_cart') || '[]');
        const idx  = cart.findIndex(i => i.titre === titre);
        if (idx >= 0) cart[idx].qty = (cart[idx].qty || 1) + 1;
        else cart.push({ titre, prix, qty: 1 });
        localStorage.setItem('mm_cart', JSON.stringify(cart));
        updateCartBadge();

        // Feedback visuel
        btn.textContent = '✓ Ajouté !';
        btn.style.background = '#27AE60';
        setTimeout(() => {
            btn.textContent = '+ Ajouter';
            btn.style.background = '';
        }, 1500);
    } catch(e) {}
});

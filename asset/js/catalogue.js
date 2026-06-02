const container  = document.querySelector('.fullcard');
const checkboxes = document.querySelectorAll('.filtre input[type="checkbox"]');
const range      = document.getElementById('price-range');
const display    = document.getElementById('price-display');
const countEl    = document.getElementById('catalogue-count');

// Détecter si on est dans views/ ou à la racine
const inViews = window.location.pathname.includes('/views/');

// Sync affichage du prix
if (range && display) {
    range.addEventListener('input', () => {
        display.textContent = parseFloat(range.value).toFixed(2).replace('.', ',') + ' €';
        filtrer();
    });
}

function afficherProduits(liste) {
    container.innerHTML = '';

    if (liste.length === 0) {
        container.innerHTML = '<p style="color:var(--grey-400);grid-column:1/-1;padding:40px 0">Aucun produit trouvé.</p>';
        if (countEl) countEl.textContent = '0 produit';
        return;
    }

    if (countEl) countEl.textContent = liste.length + ' produit' + (liste.length > 1 ? 's' : '');

    liste.forEach(produit => {
        const href = inViews
            ? `produit.php?id=${produit.id}`
            : `views/produit.php?id=${produit.id}`;

        const imgSrc = inViews
            ? `../${produit.image}`
            : produit.image;

        const card = document.createElement('a');
        card.className   = 'card card-link';
        card.href        = href;

        card.innerHTML = `
            <img src="${imgSrc}" alt="${produit.titre}">
            <div class="card-body">
                <div class="card-title">${produit.titre}</div>
                <div class="card-row">
                    <span class="card-price">${produit.prix.toFixed(2).replace('.', ',')} €</span>
                    <span class="card-tome">Tome ${produit.tome}</span>
                </div>
                <div class="card-row" style="margin-top:10px">
                    <button class="btn-add"
                            data-id="${produit.id}"
                            data-titre="${produit.titre}"
                            data-prix="${produit.prix.toFixed(2).replace('.', ',')}"
                            data-img="${imgSrc}"
                            onclick="event.preventDefault()">
                        + Ajouter
                    </button>
                </div>
            </div>
        `;
        container.appendChild(card);
    });
}

function filtrer() {
    const catsActives = [...checkboxes]
        .filter(cb => cb.checked)
        .map(cb => {
            // Remonter jusqu'au bouton parent qui porte data-categorie
            return cb.closest('[data-categorie]')?.dataset.categorie;
        })
        .filter(Boolean);

    const prixMax = range ? parseFloat(range.value) : Infinity;

    const filtrés = produits.filter(p => {
        const catOk  = catsActives.length === 0 || catsActives.includes(p.categorie);
        const prixOk = p.prix <= prixMax;
        return catOk && prixOk;
    });

    afficherProduits(filtrés);
}

// Pré-sélection depuis l'URL (?categorie=shonen)
const urlCat = new URLSearchParams(window.location.search).get('categorie');
if (urlCat) {
    checkboxes.forEach(cb => {
        const btn = cb.closest('[data-categorie]');
        if (btn?.dataset.categorie === urlCat) {
            cb.checked = true;
            btn.classList.add('active');
        }
    });
}

// Boutons filtres — toggle classe active
document.querySelectorAll('.filtre-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        btn.classList.toggle('active');
        filtrer();
    });
});

afficherProduits(produits);

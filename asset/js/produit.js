document.addEventListener("DOMContentLoaded", async () => {
    await fetchProduits();

    const params  = new URLSearchParams(window.location.search);
    const id      = parseInt(params.get("id"));
    const produit = produits.find(p => p.id === id);

    if (!produit) {
        window.location.href = "catalogue.php";
        return;
    }

    // Image principale
    const mainImg = document.getElementById('main-img');
    if (mainImg) { mainImg.src = `../${produit.image}`; mainImg.alt = produit.titre; }

    // Thumbnail
    const thumb = document.querySelector('.thumbnails img');
    if (thumb) { thumb.src = `../${produit.image}`; thumb.alt = produit.titre; }

    // Fil d'Ariane
    const breadcrumb = document.querySelector('.chemin span:last-child');
    if (breadcrumb) breadcrumb.textContent = `${produit.titre} Tome ${produit.tome}`;

    // Titre
    const h1 = document.querySelector('.description h1');
    if (h1) h1.textContent = `${produit.titre} — Tome ${produit.tome}`;

    // Prix
    const prixVal = document.querySelector('.prix-valeur');
    if (prixVal) prixVal.textContent = produit.prix.toFixed(2).replace('.', ',') + ' €';

    // Meta table
    const cells = document.querySelectorAll('.meta-table td:nth-child(2)');
    const catLabel = produit.categorie.charAt(0).toUpperCase() + produit.categorie.slice(1);
    if (cells[0]) cells[0].textContent = catLabel;
    if (cells[1]) cells[1].textContent = produit.auteur || 'Non renseigné';

    // Description
    const descEl = document.querySelector('.description-text');
    if (descEl) descEl.textContent = produit.description || 'Aucune description disponible.';

    // Titre onglet
    document.title = `${produit.titre} — Tome ${produit.tome} | MangaMarket`;

    // Bouton panier
    const btnPanier = document.getElementById('btn-panier');
    if (btnPanier) {
        btnPanier.dataset.titre = produit.titre;
        btnPanier.dataset.prix  = produit.prix.toFixed(2).replace('.', ',') + ' €';
        btnPanier.classList.add('btn-add');
    }

    // Suggestions
    const suggestions = produits
        .filter(p => p.id !== produit.id)
        .sort(() => Math.random() - 0.5)
        .slice(0, 3);

    const grid = document.querySelector('.suggestion-grid');
    if (grid) {
        grid.innerHTML = '';
        suggestions.forEach(s => {
            const card = document.createElement('article');
            card.className = 'card';
            card.style.cursor = 'pointer';
            card.innerHTML = `
                <img src="../${s.image}" alt="${s.titre}">
                <div class="card-body">
                    <div class="card-title">${s.titre}</div>
                    <div class="card-row">
                        <span class="card-price">${s.prix.toFixed(2).replace('.', ',')} €</span>
                        <span class="card-tome">Tome ${s.tome}</span>
                    </div>
                    <div class="card-row" style="margin-top:10px">
                        <button class="btn-add"
                            data-titre="${s.titre}"
                            data-prix="${s.prix.toFixed(2).replace('.', ',')} €">
                            + Ajouter
                        </button>
                    </div>
                </div>
            `;
            card.addEventListener('click', e => {
                if (e.target.closest('.btn-add')) return;
                window.location.href = `produit.php?id=${s.id}`;
            });
            grid.appendChild(card);
        });
    }
});

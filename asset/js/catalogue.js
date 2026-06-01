(async () => {
    await fetchProduits();

    const container   = document.querySelector('.fullcard');
    const paginDiv    = document.getElementById('pagination');
    const countDiv    = document.getElementById('catalogue-count');
    const range       = document.getElementById('price-range');
    const priceDisplay= document.getElementById('price-display');

    const PER_PAGE = 9;
    let currentPage = 1;
    let filteredList = [...produits];

    // Prix max dynamique
    const maxPrix = Math.ceil(Math.max(...produits.map(p => p.prix)));
    range.max   = maxPrix;
    range.value = maxPrix;
    priceDisplay.textContent = maxPrix + ' €';

    range.addEventListener('input', () => {
        priceDisplay.textContent = range.value + ' €';
        currentPage = 1;
        appliquerFiltres();
    });

    // Filtres catégories
    document.querySelectorAll('.filtre-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            btn.classList.toggle('active');
            const cb = btn.querySelector('input[type="checkbox"]');
            if (cb) cb.checked = !cb.checked;
            currentPage = 1;
            appliquerFiltres();
        });
    });

    function appliquerFiltres() {
        const cats = [...document.querySelectorAll('.filtre-btn.active')]
            .map(b => b.dataset.categorie);
        const prixMax = parseFloat(range.value);

        filteredList = produits.filter(p => {
            const catOk  = cats.length === 0 || cats.includes(p.categorie);
            const prixOk = p.prix <= prixMax;
            return catOk && prixOk;
        });

        afficherPage(currentPage);
    }

    function afficherPage(page) {
        container.innerHTML = '';
        const start = (page - 1) * PER_PAGE;
        const slice = filteredList.slice(start, start + PER_PAGE);

        countDiv.textContent = `${filteredList.length} produit${filteredList.length !== 1 ? 's' : ''}`;

        if (slice.length === 0) {
            container.innerHTML = '<p style="color:white;padding:24px">Aucun produit trouvé.</p>';
            paginDiv.innerHTML = '';
            return;
        }

        slice.forEach(produit => {
            const card = document.createElement('article');
            card.className = 'card';
            card.style.cursor = 'pointer';
            card.innerHTML = `
                <img src="../${produit.image}" alt="${produit.titre}">
                <div class="card-body">
                    <div class="card-title">${produit.titre}</div>
                    <div class="card-row">
                        <span class="card-price">${produit.prix.toFixed(2).replace('.',',')} €</span>
                        <span class="card-tome">Tome ${produit.tome}</span>
                    </div>
                    <div class="card-row" style="margin-top:10px">
                        <button class="btn-add"
                            data-titre="${produit.titre}"
                            data-prix="${produit.prix.toFixed(2).replace('.',',')} €">
                            + Ajouter
                        </button>
                    </div>
                </div>
            `;
            card.addEventListener('click', e => {
                if (e.target.closest('.btn-add')) return;
                window.location.href = `produit.php?id=${produit.id}`;
            });
            container.appendChild(card);
        });

        renderPagination();
    }

    function renderPagination() {
        const total = Math.ceil(filteredList.length / PER_PAGE);
        paginDiv.innerHTML = '';
        if (total <= 1) return;

        for (let i = 1; i <= total; i++) {
            const btn = document.createElement('button');
            btn.textContent = i;
            btn.className = 'page-btn' + (i === currentPage ? ' active' : '');
            btn.addEventListener('click', () => {
                currentPage = i;
                afficherPage(currentPage);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
            paginDiv.appendChild(btn);
        }
    }

    afficherPage(1);
})();

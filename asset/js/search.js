document.addEventListener("DOMContentLoaded", async () => {
    await fetchProduits();

    const input      = document.getElementById('search-input');
    const resultsBox = document.getElementById('search-results');
    if (!input || !resultsBox) return;

    resultsBox.className = 'search-results';

    // Détecter si on est à la racine (index) ou dans views/
    const isRoot = !window.location.pathname.includes('/views/');
    const base   = isRoot ? '' : '../';

    function normaliser(str) {
        return str.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
    }

    function surligner(texte, motCle) {
        if (!motCle) return texte;
        const regex = new RegExp(`(${motCle.replace(/[.*+?^${}()|[\]\\]/g, "\\$&")})`, "gi");
        return texte.replace(regex, '<mark class="search-highlight">$1</mark>');
    }

    function afficherResultats(query) {
        const q = normaliser(query.trim());
        resultsBox.innerHTML = '';

        if (!q) { resultsBox.classList.remove('visible'); return; }

        const resultats = produits.filter(p =>
            normaliser(p.titre).includes(q) ||
            normaliser(p.categorie).includes(q) ||
            normaliser('tome ' + p.tome).includes(q)
        );

        if (resultats.length === 0) {
            resultsBox.innerHTML = `<p class="search-no-result">Aucun manga trouvé pour "<strong>${query}</strong>"</p>`;
            resultsBox.classList.add('visible');
            return;
        }

        resultats.forEach((produit, index) => {
            const item = document.createElement('a');
            item.className = 'search-result-item';
            item.href = `${base}views/produit.php?id=${produit.id}`;
            item.setAttribute('role', 'option');
            item.setAttribute('id', `result-${index}`);

            const prix    = produit.prix.toFixed(2).replace('.', ',') + ' €';
            const titreHL = surligner(produit.titre, query.trim());

            item.innerHTML = `
                <img src="${base}${produit.image}" alt="${produit.titre}" onerror="this.style.display='none'">
                <div class="search-result-info">
                    <span class="search-result-titre">${titreHL}</span>
                    <span class="search-result-meta">Tome ${produit.tome} · ${produit.categorie}</span>
                </div>
                <span class="search-result-prix">${prix}</span>
            `;
            resultsBox.appendChild(item);
        });

        resultsBox.classList.add('visible');
    }

    input.addEventListener('input', e => afficherResultats(e.target.value));

    input.addEventListener('keydown', e => {
        const items   = resultsBox.querySelectorAll('.search-result-item');
        const current = resultsBox.querySelector('[aria-selected="true"]');
        let idx = Array.from(items).indexOf(current);

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (current) current.removeAttribute('aria-selected');
            const next = items[idx + 1] || items[0];
            if (next) next.setAttribute('aria-selected', 'true');
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (current) current.removeAttribute('aria-selected');
            const prev = items[idx - 1] || items[items.length - 1];
            if (prev) prev.setAttribute('aria-selected', 'true');
        } else if (e.key === 'Enter' && current) {
            e.preventDefault();
            window.location.href = current.href;
        } else if (e.key === 'Escape') {
            resultsBox.classList.remove('visible');
            input.blur();
        }
    });

    document.addEventListener('click', e => {
        if (!e.target.closest('.search-bar')) resultsBox.classList.remove('visible');
    });

    input.addEventListener('focus', () => {
        if (input.value.trim()) afficherResultats(input.value);
    });
});

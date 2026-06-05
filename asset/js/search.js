/**
 * search.js — Barre de recherche avec autocomplétion
 */
document.addEventListener('DOMContentLoaded', () => {
    const input      = document.getElementById('search-input');
    const resultsBox = document.getElementById('search-results');
    if (!input || !resultsBox) return;

    const inViews = window.location.pathname.includes('/views/');
    const apiBase = inViews ? '../api/search_api.php' : 'api/search_api.php';

    let debounceTimer = null;

    // Surlignage du terme recherché
    function surligner(texte, motCle) {
        if (!motCle) return texte;
        const re = new RegExp(`(${motCle.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
        return texte.replace(re, '<mark class="search-highlight">$1</mark>');
    }

    // Affichage des résultats
    function afficherResultats(produits, query) {
        resultsBox.innerHTML = '';

        if (!produits.length) {
            resultsBox.innerHTML = `<p class="search-no-result">Aucun manga trouvé pour "<strong>${query}</strong>"</p>`;
            resultsBox.classList.add('visible');
            return;
        }

        produits.forEach((produit, idx) => {
            const href   = inViews
                ? `produit.php?id=${produit.id}`
                : `views/produit.php?id=${produit.id}`;
            const imgSrc = inViews
                ? `../${produit.image}`
                : produit.image;
            const prix    = parseFloat(produit.prix).toFixed(2).replace('.', ',') + ' €';
            const titreHL = surligner(produit.titre, query);

            const item = document.createElement('a');
            item.className = 'search-result-item';
            item.href      = href;
            item.setAttribute('role', 'option');
            item.setAttribute('id', `result-${idx}`);
            item.innerHTML = `
                <img src="${imgSrc}" alt="${produit.titre}" onerror="this.style.display='none'">
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

    // Appel API avec debounce
    function rechercher(q) {
        // Ne rien faire si la recherche est vide
        if (!q.trim()) {
            resultsBox.classList.remove('visible');
            resultsBox.innerHTML = '';
            return;
        }

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(async () => {
            try {
                const res  = await fetch(`${apiBase}?q=${encodeURIComponent(q)}`);
                const data = await res.json();
                afficherResultats(data, q.trim());
            } catch (e) {
                console.error('Erreur recherche', e);
            }
        }, 200);
    }

    // Déclenche uniquement sur frappe, pas au focus
    input.addEventListener('input', e => rechercher(e.target.value));

    // Navigation clavier
    input.addEventListener('keydown', e => {
        const items   = resultsBox.querySelectorAll('.search-result-item');
        const current = resultsBox.querySelector('[aria-selected="true"]');
        const idx     = Array.from(items).indexOf(current);

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            current?.removeAttribute('aria-selected');
            (items[idx + 1] || items[0])?.setAttribute('aria-selected', 'true');
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            current?.removeAttribute('aria-selected');
            (items[idx - 1] || items[items.length - 1])?.setAttribute('aria-selected', 'true');
        } else if (e.key === 'Enter' && current) {
            e.preventDefault();
            window.location.href = current.href;
        } else if (e.key === 'Escape') {
            resultsBox.classList.remove('visible');
            input.blur();
        }
    });

    // Ferme en cliquant en dehors
    document.addEventListener('click', e => {
        if (!e.target.closest('.search-bar')) {
            resultsBox.classList.remove('visible');
            resultsBox.innerHTML = '';
        }
    });
});

/**
 * search.js — Barre de recherche avec autocomplétion
 *
 * Envoie les requêtes à api/search.php (côté PHP/BDD).
 * Gère l'affichage des résultats, le surlignage du terme
 * et la navigation clavier dans les résultats.
 */
document.addEventListener('DOMContentLoaded', () => {
    const input      = document.getElementById('search-input');
    const resultsBox = document.getElementById('search-results');
    if (!input || !resultsBox) return;

    // Détection du chemin pour construire l'URL de l'API
    const inViews = window.location.pathname.includes('/views/');
    const apiBase = inViews ? '../api/search.php' : 'api/search.php';

    let debounceTimer = null; // Timer pour limiter les appels API

    // ── Surlignage du terme recherché dans le titre ───────
    function surligner(texte, motCle) {
        if (!motCle) return texte;
        const re = new RegExp(`(${motCle.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
        return texte.replace(re, '<mark class="search-highlight">$1</mark>');
    }

    // ── Affichage des résultats dans la liste déroulante ──
    function afficherResultats(produits, query) {
        resultsBox.innerHTML = '';

        if (!produits.length) {
            // Aucun résultat trouvé
            resultsBox.innerHTML = `<p class="search-no-result">Aucun manga trouvé pour "<strong>${query}</strong>"</p>`;
            resultsBox.classList.add('visible');
            return;
        }

        produits.forEach((produit, idx) => {
            // Liens selon la position dans l'arborescence
            const href   = inViews
                ? `produit.php?id=${produit.id}`
                : `views/produit.php?id=${produit.id}`;
            const imgSrc = inViews
                ? `../${produit.image}`
                : produit.image;
            const prix    = parseFloat(produit.prix).toFixed(2).replace('.', ',') + ' €';
            const titreHL = surligner(produit.titre, query); // Titre avec surlignage

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

    // ── Appel à l'API avec debounce (200ms) ───────────────
    function rechercher(q) {
        if (!q.trim()) { resultsBox.classList.remove('visible'); return; }

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

    // Déclenche la recherche à chaque frappe et au focus
    input.addEventListener('input', e => rechercher(e.target.value));
    input.addEventListener('focus', () => { if (input.value.trim()) rechercher(input.value); });

    // ── Navigation clavier dans les résultats ─────────────
    input.addEventListener('keydown', e => {
        const items   = resultsBox.querySelectorAll('.search-result-item');
        const current = resultsBox.querySelector('[aria-selected="true"]');
        const idx     = Array.from(items).indexOf(current);

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            current?.removeAttribute('aria-selected');
            (items[idx + 1] || items[0])?.setAttribute('aria-selected', 'true'); // Descend ou revient au début
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            current?.removeAttribute('aria-selected');
            (items[idx - 1] || items[items.length - 1])?.setAttribute('aria-selected', 'true'); // Monte ou va en bas
        } else if (e.key === 'Enter' && current) {
            e.preventDefault();
            window.location.href = current.href; // Navigation vers le résultat sélectionné
        } else if (e.key === 'Escape') {
            resultsBox.classList.remove('visible');
            input.blur();
        }
    });

    // Ferme la liste en cliquant en dehors de la barre de recherche
    document.addEventListener('click', e => {
        if (!e.target.closest('.search-bar')) resultsBox.classList.remove('visible');
    });
});

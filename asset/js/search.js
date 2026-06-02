document.addEventListener("DOMContentLoaded", () => {
    const input      = document.getElementById('search-input');
    const resultsBox = document.getElementById('search-results');

    if (!input || !resultsBox || typeof produits === 'undefined') return;

    const inViews = window.location.pathname.includes('/views/');

    function normaliser(str) {
        return str.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
    }

    function surligner(texte, motCle) {
        if (!motCle) return texte;
        const re = new RegExp(`(${motCle.replace(/[.*+?^${}()|[\]\\]/g, "\\$&")})`, "gi");
        return texte.replace(re, '<mark class="search-highlight">$1</mark>');
    }

    function rechercher(q) {
        const n = normaliser(q.trim());
        if (!n) return [];
        return produits.filter(p =>
            normaliser(p.titre).includes(n) ||
            normaliser(p.categorie).includes(n) ||
            normaliser("tome " + p.tome).includes(n)
        );
    }

    function afficherResultats(query) {
        resultsBox.innerHTML = "";
        if (!query.trim()) { resultsBox.classList.remove("visible"); return; }

        const resultats = rechercher(query);

        if (!resultats.length) {
            resultsBox.innerHTML = `<p class="search-no-result">Aucun manga trouvé pour "<strong>${query}</strong>"</p>`;
            resultsBox.classList.add("visible");
            return;
        }

        resultats.forEach((produit, idx) => {
            const href   = inViews ? `produit.php?id=${produit.id}` : `views/produit.php?id=${produit.id}`;
            const imgSrc = inViews ? `../${produit.image}` : produit.image;
            const prix   = produit.prix.toFixed(2).replace(".", ",") + " €";
            const titreHL= surligner(produit.titre, query.trim());

            const item = document.createElement("a");
            item.className = "search-result-item";
            item.href      = href;
            item.setAttribute("role", "option");
            item.setAttribute("id", `result-${idx}`);
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

        resultsBox.classList.add("visible");
    }

    input.addEventListener("input",  e => afficherResultats(e.target.value));
    input.addEventListener("focus",  () => { if (input.value.trim()) afficherResultats(input.value); });

    input.addEventListener("keydown", e => {
        const items   = resultsBox.querySelectorAll(".search-result-item");
        const current = resultsBox.querySelector('[aria-selected="true"]');
        const idx     = Array.from(items).indexOf(current);

        if (e.key === "ArrowDown") {
            e.preventDefault();
            if (current) current.removeAttribute("aria-selected");
            (items[idx + 1] || items[0])?.setAttribute("aria-selected", "true");
        } else if (e.key === "ArrowUp") {
            e.preventDefault();
            if (current) current.removeAttribute("aria-selected");
            (items[idx - 1] || items[items.length - 1])?.setAttribute("aria-selected", "true");
        } else if (e.key === "Enter" && current) {
            e.preventDefault();
            window.location.href = current.href;
        } else if (e.key === "Escape") {
            resultsBox.classList.remove("visible");
            input.blur();
        }
    });

    document.addEventListener("click", e => {
        if (!e.target.closest(".search-bar")) resultsBox.classList.remove("visible");
    });
});

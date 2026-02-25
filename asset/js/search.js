document.addEventListener("DOMContentLoaded", () => {
    const input = document.querySelector(".search-bar input");
    const resultsBox = document.querySelector(".search-bar [role='listbox']");

    if (!input || !resultsBox) return;

    const style = document.createElement("style");
    style.textContent = `
        .search-bar {
            position: relative;
        }
        .search-results {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            right: 0;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            max-height: 360px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }
        .search-results.visible {
            display: block;
        }
        .search-result-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            cursor: pointer;
            transition: background 0.15s;
            text-decoration: none;
            color: #131937;
        }
        .search-result-item:hover,
        .search-result-item[aria-selected="true"] {
            background: #f0f4ff;
        }
        .search-result-item img {
            width: 36px;
            height: 52px;
            object-fit: cover;
            border-radius: 4px;
            flex-shrink: 0;
        }
        .search-result-info {
            display: flex;
            flex-direction: column;
        }
        .search-result-titre {
            font-weight: 600;
            font-size: 14px;
        }
        .search-result-meta {
            font-size: 12px;
            color: #666;
        }
        .search-result-prix {
            margin-left: auto;
            font-weight: 700;
            font-size: 14px;
            color: #c44444;
            white-space: nowrap;
        }
        .search-no-result {
            padding: 14px;
            text-align: center;
            color: #888;
            font-size: 14px;
        }
        .search-highlight {
            background: #ffe082;
            border-radius: 2px;
        }
    `;
    document.head.appendChild(style);

    resultsBox.className = "search-results";

    function normaliser(str) {
        return str
            .toLowerCase()
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "");
    }

    function surligner(texte, motCle) {
        if (!motCle) return texte;
        const regex = new RegExp(`(${motCle.replace(/[.*+?^${}()|[\]\\]/g, "\\$&")})`, "gi");
        return texte.replace(regex, '<mark class="search-highlight">$1</mark>');
    }

    function rechercher(query) {
        const q = normaliser(query.trim());
        if (!q) return [];

        return produits.filter(p => {
            return (
                normaliser(p.titre).includes(q) ||
                normaliser(p.categorie).includes(q) ||
                normaliser("tome " + p.tome).includes(q)
            );
        });
    }

    function afficherResultats(query) {
        const resultats = rechercher(query);
        resultsBox.innerHTML = "";

        if (query.trim() === "") {
            resultsBox.classList.remove("visible");
            return;
        }

        if (resultats.length === 0) {
            resultsBox.innerHTML = `<p class="search-no-result">Aucun manga trouvé pour "<strong>${query}</strong>"</p>`;
            resultsBox.classList.add("visible");
            return;
        }

        resultats.forEach((produit, index) => {
            const item = document.createElement("a");
            item.className = "search-result-item";
            item.href = `produit.html?id=${produit.id}`;
            item.setAttribute("role", "option");
            item.setAttribute("id", `result-${index}`);

            const prix = produit.prix.toFixed(2).replace(".", ",") + " €";
            const titreHL = surligner(produit.titre, query.trim());

            item.innerHTML = `
                <img src="${produit.image}" alt="${produit.titre}" onerror="this.style.display='none'">
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

    input.addEventListener("input", (e) => {
        afficherResultats(e.target.value);
    });

    input.addEventListener("keydown", (e) => {
        const items = resultsBox.querySelectorAll(".search-result-item");
        const current = resultsBox.querySelector('[aria-selected="true"]');
        let idx = Array.from(items).indexOf(current);

        if (e.key === "ArrowDown") {
            e.preventDefault();
            if (current) current.removeAttribute("aria-selected");
            const next = items[idx + 1] || items[0];
            if (next) next.setAttribute("aria-selected", "true");
        } else if (e.key === "ArrowUp") {
            e.preventDefault();
            if (current) current.removeAttribute("aria-selected");
            const prev = items[idx - 1] || items[items.length - 1];
            if (prev) prev.setAttribute("aria-selected", "true");
        } else if (e.key === "Enter") {
            if (current) {
                e.preventDefault();
                window.location.href = current.href;
            }
        } else if (e.key === "Escape") {
            resultsBox.classList.remove("visible");
            input.blur();
        }
    });

    document.addEventListener("click", (e) => {
        if (!e.target.closest(".search-bar")) {
            resultsBox.classList.remove("visible");
        }
    });

    input.addEventListener("focus", () => {
        if (input.value.trim()) afficherResultats(input.value);
    });
});
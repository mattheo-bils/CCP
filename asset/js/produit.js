document.addEventListener("DOMContentLoaded", () => {

    // --- 1. Récupérer l'id dans l'URL (ex: produit.html?id=7) ---
    const params = new URLSearchParams(window.location.search);
    const id = parseInt(params.get("id"));

    // Chercher le produit dans le tableau
    const produit = produits.find(p => p.id === id);

    // Si l'id est invalide, on redirige vers le catalogue
    if (!produit) {
        window.location.href = "catalogue.html";
        return;
    }

    // --- 2. Remplir les infos du produit ---

    // Images
    document.querySelector(".image_affiche").src = produit.image;
    document.querySelector(".image_affiche").alt = produit.titre;
    document.querySelector(".image_autre").src = produit.image;
    document.querySelector(".image_autre").alt = produit.titre;

    // Titre dans le fil d'ariane
    document.querySelector(".chemin p:last-child").textContent = produit.titre;

    // Titre principal
    document.querySelector(".description h3").textContent = `${produit.titre} — Tome ${produit.tome}`;

    // Prix
    const prixEl = document.querySelectorAll(".prix p");
    prixEl[1].textContent = produit.prix.toFixed(2).replace(".", ",") + " €";

    // Genre
    const genreEl = document.querySelectorAll(".genre p");
    // Capitalise la première lettre
    genreEl[1].textContent = produit.categorie.charAt(0).toUpperCase() + produit.categorie.slice(1);

    // Auteur (si disponible)
    const auteurEl = document.querySelectorAll(".auteur p");
    auteurEl[1].textContent = produit.auteur ? produit.auteur : "Non renseigné";

    // Description
    const descEl = document.querySelector(".description div > div:last-child p:last-child");
    if (descEl) descEl.textContent = produit.description || "Aucune description disponible.";

    // Titre de la page
    document.title = `${produit.titre} — Tome ${produit.tome} | MangaMarket`;


    // --- 3. Suggestions : 3 produits de la même catégorie (hors produit actuel) ---
    const suggestions = produits
        .filter(p => p.categorie === produit.categorie && p.id !== produit.id)
        .slice(0, 3);

    // Fallback si pas assez dans la même catégorie
    const fallback = produits.filter(p => p.id !== produit.id);
    while (suggestions.length < 3) {
        const next = fallback.find(p => !suggestions.includes(p));
        if (!next) break;
        suggestions.push(next);
    }

    const fullcard = document.querySelector(".suggestion .fullcard");
    fullcard.innerHTML = "";

    suggestions.forEach(s => {
        const card = document.createElement("div");
        card.className = "card";
        card.style.cursor = "pointer";
        card.innerHTML = `
            <img src="${s.image}" alt="${s.titre}">
            <div>
                <div class="card_text">
                    <p>${s.titre}</p>
                    <p>${s.prix.toFixed(2).replace(".", ",")}€</p>
                </div>
                <div class="card_info">
                    <p>Tome ${s.tome}</p>
                    <button class="ajouter">Ajouter</button>
                </div>
            </div>
        `;

        // Clic sur la card → aller vers ce produit
        card.addEventListener("click", (e) => {
            if (e.target.classList.contains("ajouter")) return; // ne pas naviguer si clic sur "Ajouter"
            window.location.href = `produit.html?id=${s.id}`;
        });

        fullcard.appendChild(card);
    });

});
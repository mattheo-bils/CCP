document.addEventListener("DOMContentLoaded", () => {

    const params = new URLSearchParams(window.location.search);
    const id = parseInt(params.get("id"));

    const produit = produits.find(p => p.id === id);

    if (!produit) {
        window.location.href = "catalogue.html";
        return;
    }


    document.querySelector(".image_affiche").src = produit.image;
    document.querySelector(".image_affiche").alt = produit.titre;
    document.querySelector(".image_autre").src = produit.image;
    document.querySelector(".image_autre").alt = produit.titre;

    document.querySelector(".chemin p:last-child").textContent = produit.titre;

    document.querySelector(".description h3").textContent = `${produit.titre} — Tome ${produit.tome}`;

    const prixEl = document.querySelectorAll(".prix p");
    prixEl[1].textContent = produit.prix.toFixed(2).replace(".", ",") + " €";

    const genreEl = document.querySelectorAll(".genre p");
    genreEl[1].textContent = produit.categorie.charAt(0).toUpperCase() + produit.categorie.slice(1);

    const auteurEl = document.querySelectorAll(".auteur p");
    auteurEl[1].textContent = produit.auteur ? produit.auteur : "Non renseigné";

    const descEl = document.querySelector(".description div > div:last-child p:last-child");
    if (descEl) descEl.textContent = produit.description || "Aucune description disponible.";

    document.title = `${produit.titre} — Tome ${produit.tome} | MangaMarket`;


    const suggestions = produits
        .filter(p => p.id !== produit.id)
        .sort(() => Math.random() - 0.5)
        .slice(0, 3);

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

        card.addEventListener("click", (e) => {
            if (e.target.classList.contains("ajouter")) return;
            window.location.href = `produit.html?id=${s.id}`;
        });

        fullcard.appendChild(card);
    });

});
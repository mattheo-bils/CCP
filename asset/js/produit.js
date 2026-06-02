document.addEventListener("DOMContentLoaded", () => {
    // Le produit est déjà rendu côté PHP.
    // Ce script gère uniquement les interactions côté client.

    // Clic sur les miniatures → change l'image principale
    document.querySelectorAll('.image-autre').forEach(thumb => {
        thumb.addEventListener('click', () => {
            const main = document.getElementById('main-img');
            if (main) {
                main.src = thumb.src;
                document.querySelectorAll('.image-autre').forEach(t => t.classList.remove('active'));
                thumb.classList.add('active');
            }
        });
    });

    // Si on arrive avec ?id= mais que la page est rendue statiquement (fallback),
    // on tente de peupler depuis data.js (rétrocompatibilité).
    const params  = new URLSearchParams(window.location.search);
    const id      = parseInt(params.get("id"));
    const mainImg = document.getElementById('main-img');

    if (!id || !mainImg || typeof produits === 'undefined') return;

    const produit = produits.find(p => p.id === id);
    if (!produit) return;

    // Mise à jour uniquement si le contenu est encore le placeholder statique
    const titreEl = document.querySelector(".description h1");
    if (titreEl && titreEl.textContent.trim() === 'Bleach — Tome 2' && produit.titre !== 'Bleach') {
        document.title = `${produit.titre} — Tome ${produit.tome} | MangaMarket`;
        titreEl.textContent = `${produit.titre} — Tome ${produit.tome}`;

        const prixEl = document.querySelector(".prix-valeur");
        if (prixEl) prixEl.textContent = produit.prix.toFixed(2).replace(".", ",") + " €";

        const inViews = window.location.pathname.includes('/views/');
        const imgPath = inViews ? `../${produit.image}` : produit.image;
        mainImg.src = imgPath;
        mainImg.alt = produit.titre;

        const rows = document.querySelectorAll(".meta-table tr");
        if (rows[0]) rows[0].cells[1].textContent = produit.categorie.charAt(0).toUpperCase() + produit.categorie.slice(1);
        if (rows[1]) rows[1].cells[1].textContent = produit.auteur || "Non renseigné";

        const descEl = document.querySelector(".description-text");
        if (descEl) descEl.textContent = produit.description || "";

        // Breadcrumb
        const breadEl = document.querySelector(".chemin span:last-child");
        if (breadEl) breadEl.textContent = `${produit.titre} — Tome ${produit.tome}`;

        // Bouton "Ajouter au panier" — patch les data-attributes
        const btnPanier = document.querySelector('.btn-add, #btn-panier');
        if (btnPanier) {
            btnPanier.dataset.id    = produit.id;
            btnPanier.dataset.titre = produit.titre;
            btnPanier.dataset.prix  = produit.prix.toFixed(2).replace(".", ",");
            btnPanier.dataset.img   = imgPath;
        }
    }
});

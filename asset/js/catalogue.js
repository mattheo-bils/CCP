const container = document.querySelector('.fullcard');
const checkboxes = document.querySelectorAll('.filtre input[type="checkbox"]');
const range = document.querySelector('input[type="range"]');


range.min = 0;
range.max = 20;
range.value = 20;

function afficherProduits(liste) {
    container.innerHTML = '';

    if (liste.length === 0) {
        container.innerHTML = '<p style="color:white; margin-left:50px">Aucun produit trouvé</p>';
        return;
    }

    for (let i = 0; i < liste.length; i += 3) {
        const row = document.createElement('div');
        row.className = 'three_card';

        liste.slice(i, i + 3).forEach(produit => {
            row.innerHTML += `
                <div class="card" data-categorie="${produit.categorie}" data-prix="${produit.prix}">
                    <img src="${produit.image}" alt="${produit.titre}">
                    <div>
                        <div class="card_text">
                            <p>${produit.titre}</p>
                            <p>${produit.prix.toFixed(2)}€</p>
                        </div>
                        <div class="card_info">
                            <p>Tome ${produit.tome}</p>
                            <button class="ajouter">Ajouter</button>
                        </div>
                    </div>
                </div>
            `;
        });

        container.appendChild(row);
    }
}

function filtrer() {
    const categoriesActives = [...checkboxes]
        .filter(cb => cb.checked)
        .map(cb => cb.dataset.categorie);

    const prixMax = parseFloat(range.value);


    const produitsFiltres = produits.filter(produit => {
        const categorieOk = categoriesActives.length === 0 || categoriesActives.includes(produit.categorie);
        const prixOk = produit.prix <= prixMax;
        return categorieOk && prixOk;
    });

    afficherProduits(produitsFiltres);
}


afficherProduits(produits);


checkboxes.forEach(cb => cb.addEventListener('change', filtrer));
range.addEventListener('input', filtrer);
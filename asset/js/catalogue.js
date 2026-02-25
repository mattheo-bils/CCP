const container = document.querySelector('.fullcard');

function afficherProduits(liste) {
    container.innerHTML = '';

    // Regroupe par 3
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

afficherProduits(produits);
/**
 * search.js — Barre de recherche avec autocomplétion en temps réel
 *
 * Envoie les frappes au clavier vers api/search_api.php
 * et affiche les résultats dans une liste déroulante.
 * Gère aussi la navigation clavier (flèches, Entrée, Échap).
 */
document.addEventListener('DOMContentLoaded', () => {
    // Récupération des éléments de la barre de recherche
    const input      = document.getElementById('search-input');  // Champ de saisie
    const resultsBox = document.getElementById('search-results'); // Conteneur des résultats

    // Si les éléments n'existent pas sur cette page, on arrête
    if (!input || !resultsBox) return;

    // Détection du chemin pour construire l'URL de l'API
    const inViews = window.location.pathname.includes('/views/');
    const apiBase = inViews ? '../api/search_api.php' : 'api/search_api.php';

    // Timer pour le debounce (évite d'envoyer une requête à chaque frappe)
    let debounceTimer = null;

    // ── Surlignage du terme recherché dans les résultats ──────

    /**
     * Entoure les occurrences du terme dans le texte avec une balise <mark>
     * @param {string} texte   - Le texte du titre à afficher
     * @param {string} motCle  - Le terme recherché à surligner
     * @returns {string}       - Le HTML avec le terme surligné
     */
    function surligner(texte, motCle) {
        if (!motCle) return texte; // Rien à surligner si pas de terme

        // Échappement des caractères spéciaux pour la regex (ex: "one+" ne casse pas)
        const re = new RegExp(`(${motCle.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');

        // Remplacement par <mark> qui applique le style jaune défini dans le CSS
        return texte.replace(re, '<mark class="search-highlight">$1</mark>');
    }

    // ── Fermeture de la liste de résultats ────────────────────

    /** Cache et vide la liste des résultats */
    function fermer() {
        resultsBox.classList.remove('visible'); // Cache via CSS (display:none)
        resultsBox.innerHTML = '';              // Vide le contenu
    }

    // ── Affichage des résultats retournés par l'API ───────────

    /**
     * Construit et affiche les résultats de recherche
     * @param {Array}  produits - Tableau de produits retourné par l'API
     * @param {string} query    - Terme recherché (pour le surlignage)
     */
    function afficherResultats(produits, query) {
        resultsBox.innerHTML = ''; // On efface les anciens résultats

        // Cas "aucun résultat trouvé"
        if (!produits.length) {
            resultsBox.innerHTML = `<p class="search-no-result">Aucun manga trouvé pour "<strong>${query}</strong>"</p>`;
            resultsBox.classList.add('visible'); // Affiche quand même (pour montrer "aucun résultat")
            return;
        }

        // Construction des éléments de résultat
        produits.forEach((produit, idx) => {
            // Lien vers la fiche produit (chemin relatif selon la page courante)
            const href = inViews
                ? `produit.php?id=${produit.id}`
                : `views/produit.php?id=${produit.id}`;

            // Chemin de l'image miniature
            const imgSrc = inViews
                ? `../${produit.image}`
                : produit.image;

            // Formatage du prix en format français (virgule au lieu du point)
            const prix = parseFloat(produit.prix).toFixed(2).replace('.', ',') + ' €';

            // Titre avec le terme recherché surligné en jaune
            const titreHL = surligner(produit.titre, query);

            // Création du lien <a> pour cet item de résultat
            const item = document.createElement('a');
            item.className = 'search-result-item';
            item.href      = href;
            item.setAttribute('role', 'option');       // Accessibilité ARIA
            item.setAttribute('id', `result-${idx}`); // ID unique pour ARIA

            // Structure HTML de l'item : image + infos + prix
            item.innerHTML = `
                <img src="${imgSrc}" alt="${produit.titre}" onerror="this.style.display='none'">
                <div class="search-result-info">
                    <span class="search-result-titre">${titreHL}</span>
                    <span class="search-result-meta">Tome ${produit.tome} · ${produit.categorie}</span>
                </div>
                <span class="search-result-prix">${prix}</span>
            `;
            resultsBox.appendChild(item); // Ajout dans la liste
        });

        // Rend la liste visible
        resultsBox.classList.add('visible');
    }

    // ── Appel à l'API avec debounce ───────────────────────────

    /**
     * Lance la recherche avec un délai de 200ms après la dernière frappe
     * Le debounce évite d'envoyer une requête à chaque caractère tapé
     * @param {string} q - Le terme saisi par l'utilisateur
     */
    function rechercher(q) {
        clearTimeout(debounceTimer); // Annule le timer précédent

        // Ne rien faire si le terme fait moins de 2 caractères
        if (q.trim().length < 2) {
            fermer();
            return;
        }

        // Lance la requête après 200ms d'inactivité
        debounceTimer = setTimeout(async () => {
            try {
                // Appel à l'API avec le terme encodé pour l'URL
                const res  = await fetch(`${apiBase}?q=${encodeURIComponent(q)}`);
                const data = await res.json(); // Décodage du JSON
                afficherResultats(data, q.trim());
            } catch (e) {
                console.error('Erreur recherche', e); // Log l'erreur sans casser la page
            }
        }, 200); // Délai de 200ms
    }

    // ── Événements de la barre de recherche ───────────────────

    // Déclenche la recherche à chaque frappe (pas au focus)
    input.addEventListener('input', e => rechercher(e.target.value));

    // ── Navigation clavier dans les résultats ─────────────────
    input.addEventListener('keydown', e => {
        // Récupération de tous les items de résultats affichés
        const items   = resultsBox.querySelectorAll('.search-result-item');
        // Item actuellement sélectionné (via ARIA)
        const current = resultsBox.querySelector('[aria-selected="true"]');
        // Index de l'item sélectionné (-1 si aucun)
        const idx     = Array.from(items).indexOf(current);

        if (e.key === 'ArrowDown') {
            e.preventDefault(); // Empêche le scroll de la page
            current?.removeAttribute('aria-selected'); // Désélectionne l'actuel
            // Passe au suivant, ou revient au début si on est au dernier
            (items[idx + 1] || items[0])?.setAttribute('aria-selected', 'true');

        } else if (e.key === 'ArrowUp') {
            e.preventDefault(); // Empêche le scroll de la page
            current?.removeAttribute('aria-selected');
            // Passe au précédent, ou va au dernier si on est au premier
            (items[idx - 1] || items[items.length - 1])?.setAttribute('aria-selected', 'true');

        } else if (e.key === 'Enter' && current) {
            e.preventDefault(); // Empêche la soumission d'un formulaire parent
            window.location.href = current.href; // Navigation vers le résultat sélectionné

        } else if (e.key === 'Escape') {
            fermer();     // Ferme la liste
            input.blur(); // Retire le focus du champ
        }
    });

    // Ferme la liste quand l'utilisateur clique ailleurs sur la page
    document.addEventListener('click', e => {
        // On vérifie que le clic n'est pas dans la barre de recherche elle-même
        if (!e.target.closest('.search-bar')) fermer();
    });
});

/**
 * header.js — Script global chargé sur toutes les pages du site
 *
 * Ce fichier est inclus en bas de chaque page via footer.php.
 * Il gère les interactions communes à toutes les pages :
 *   - Le menu burger (navigation mobile)
 *   - Le badge de quantité sur l'icône panier
 *   - Les boutons "Ajouter au panier" avec vérification du stock
 *   - Les styles CSS dynamiques pour la recherche et les alertes
 */

// ── Burger menu (navigation mobile) ──────────────────────────

// Récupération du bouton burger et du menu mobile
const burgerBtn = document.getElementById('burger-btn');
const mobileNav = document.getElementById('mobile-nav');

if (burgerBtn && mobileNav) {
    // Clic sur le burger : toggle l'état ouvert/fermé
    burgerBtn.addEventListener('click', () => {
        burgerBtn.classList.toggle('active'); // Anime les 3 barres en croix
        mobileNav.classList.toggle('open');   // Affiche/masque le menu mobile
    });

    // Fermeture du menu en cliquant n'importe où en dehors du header
    document.addEventListener('click', e => {
        if (!e.target.closest('header')) {
            burgerBtn.classList.remove('active'); // Réinitialise l'animation
            mobileNav.classList.remove('open');   // Cache le menu
        }
    });
}

// ── Badge de quantité sur l'icône panier ──────────────────────

/**
 * Met à jour le badge rouge sur l'icône panier dans le header.
 * - Si connecté → compte depuis la BDD via l'API
 * - Si invité   → compte depuis le localStorage
 * Appelé au chargement et après chaque modification du panier.
 */
async function updateCartBadge() {
    const badge = document.getElementById('cart-count'); // Le badge rouge
    if (!badge) return; // Pas de badge sur cette page

    // Construction de l'URL de l'API selon la position dans l'arborescence
    const inViews = window.location.pathname.includes('/views/');
    const apiBase = inViews ? '../api/panier.php' : 'api/panier.php';

    try {
        // Appel à l'API pour récupérer le panier
        const res  = await fetch(apiBase + '?action=list');
        const data = await res.json();

        let count = 0;
        if (data.connected && data.items) {
            // Utilisateur connecté : somme des quantités depuis la BDD
            // item.quantite vient de la BDD, item.qty est l'alias retourné
            count = data.items.reduce((sum, i) => sum + (parseInt(i.quantite) || parseInt(i.qty) || 1), 0);
        } else {
            // Invité : somme des quantités depuis le localStorage
            const cart = JSON.parse(localStorage.getItem('mm_cart') || '[]');
            count = cart.reduce((sum, i) => sum + (i.qty || 1), 0);
        }

        // Mise à jour de l'affichage du badge
        badge.textContent   = count;
        badge.style.display = count > 0 ? 'flex' : 'none'; // Cache si panier vide

    } catch (e) {
        // En cas d'erreur API (ex: 404), fallback sur le localStorage
        try {
            const cart  = JSON.parse(localStorage.getItem('mm_cart') || '[]');
            const count = cart.reduce((sum, i) => sum + (i.qty || 1), 0);
            badge.textContent   = count;
            badge.style.display = count > 0 ? 'flex' : 'none';
        } catch (e2) {} // Silencieux si localStorage aussi indisponible
    }
}

// Initialisation du badge au chargement de la page
updateCartBadge();

// ── Boutons "Ajouter au panier" ───────────────────────────────

/**
 * Délégation d'événement sur tous les boutons .btn-add de la page.
 * On utilise la délégation (listener sur document) plutôt qu'un listener
 * par bouton pour gérer les boutons ajoutés dynamiquement.
 */
document.addEventListener('click', async e => {
    // Recherche du bouton .btn-add le plus proche du clic
    const btn = e.target.closest('.btn-add');
    if (!btn || btn.disabled) return; // Ignorer si pas de bouton ou désactivé

    // Empêche la navigation si le bouton est dans un lien <a>
    e.preventDefault();
    e.stopPropagation();

    // Récupération des données du produit depuis les data-attributes HTML
    // Ex: <button data-id="7" data-titre="Bleach" data-prix="7,80" data-img="...">
    const id    = btn.dataset.id    || '0';
    const titre = btn.dataset.titre || '';
    const prix  = btn.dataset.prix  || '0';
    const img   = btn.dataset.img   || '';

    // URL de l'API selon la position dans l'arborescence
    const inViews = window.location.pathname.includes('/views/');
    const apiBase = inViews ? '../api/panier.php' : 'api/panier.php';

    try {
        // Vérification de l'état de connexion avant d'agir
        const res  = await fetch(apiBase + '?action=list');
        const data = await res.json();

        if (data.connected) {
            // ── Utilisateur connecté : ajout en BDD via l'API ──

            // Création du formulaire de données pour la requête POST
            const form = new FormData();
            form.append('action',     'add');      // Action à effectuer
            form.append('produit_id', id);         // ID du produit à ajouter
            form.append('quantite',   '1');         // Quantité (toujours 1 par clic)

            const addRes  = await fetch(apiBase, { method: 'POST', body: form });
            const addData = await addRes.json();

            // Gestion des erreurs de stock retournées par l'API
            if (addData.error === 'rupture') {
                // Produit en rupture totale : désactiver définitivement le bouton
                btn.textContent      = '✕ Rupture !';
                btn.style.background = '#922B21'; // Rouge foncé
                btn.disabled         = true;
                setTimeout(() => {
                    btn.textContent      = 'Indisponible';
                    btn.style.background = 'var(--grey-600)'; // Gris
                }, 2000);
                return; // Arrêt : pas de feedback "ajouté"
            }

            if (addData.error === 'stock_insuffisant') {
                // Stock max atteint pour ce produit dans le panier
                const orig = btn.textContent;
                btn.textContent      = '✕ Stock max atteint';
                btn.style.background = '#e2b04a'; // Doré/orange
                btn.style.color      = '#111';
                btn.disabled         = true;
                setTimeout(() => {
                    btn.textContent      = orig;
                    btn.style.background = '';
                    btn.style.color      = '';
                    btn.disabled         = false;
                }, 2000);
                return;
            }

        } else {
            // ── Invité non connecté : ajout dans le localStorage ──

            // Lecture du panier actuel
            const cart = JSON.parse(localStorage.getItem('mm_cart') || '[]');
            // Recherche si le produit est déjà dans le panier
            const idx  = cart.findIndex(i => i.id === id);

            if (idx >= 0) {
                // Produit déjà présent : incrémentation de la quantité
                cart[idx].qty = (cart[idx].qty || 1) + 1;
            } else {
                // Nouveau produit : ajout avec quantité 1
                cart.push({ id, titre, prix, img, qty: 1 });
            }

            // Sauvegarde du panier mis à jour
            localStorage.setItem('mm_cart', JSON.stringify(cart));
        }

    } catch (e) {
        // Fallback localStorage en cas d'erreur réseau
        const cart = JSON.parse(localStorage.getItem('mm_cart') || '[]');
        const idx  = cart.findIndex(i => i.id === id);
        if (idx >= 0) { cart[idx].qty = (cart[idx].qty || 1) + 1; }
        else { cart.push({ id, titre, prix, img, qty: 1 }); }
        localStorage.setItem('mm_cart', JSON.stringify(cart));
    }

    // Mise à jour du badge dans le header
    updateCartBadge();

    // ── Feedback visuel temporaire sur le bouton ──────────────
    const orig = btn.textContent; // Sauvegarde du texte original
    btn.textContent      = '✓ Ajouté !';   // Confirmation visuelle
    btn.style.background = '#27AE60';       // Vert succès
    btn.disabled         = true;            // Empêche les double-clics
    setTimeout(() => {
        btn.textContent      = orig;  // Restauration du texte
        btn.style.background = '';    // Restauration de la couleur
        btn.disabled         = false; // Réactivation du bouton
    }, 1500); // Après 1,5 secondes
});

// ── Styles CSS injectés dynamiquement ─────────────────────────

/**
 * Ces styles sont injectés en JS plutôt qu'en CSS car ils concernent
 * des éléments créés dynamiquement (résultats de recherche, alertes).
 * Ils sont ajoutés dans le <head> de la page.
 */
const style = document.createElement('style');
style.textContent = `
    /* ── Dropdown résultats de recherche ── */

    /* Conteneur des résultats, fond blanc pour lisibilité */
    #search-results {
        display: none;           /* Masqué par défaut */
        background: #ffffff;     /* Fond blanc pour contraste avec les textes sombres */
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.3); /* Ombre portée pour l'effet dropdown */
        overflow: hidden;        /* Arrondi les coins des items du bord */
        position: absolute;      /* Positionné par rapport à .search-bar */
        width: 100%;             /* Même largeur que la barre de recherche */
        z-index: 999;            /* Au-dessus de tout le reste */
        top: calc(100% + 6px);  /* 6px sous la barre de recherche */
        left: 0;
    }
    /* Classe ajoutée par JS pour rendre la liste visible */
    #search-results.visible { display: block; }

    /* Item individuel : flex pour aligner image, texte et prix */
    .search-result-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 14px;
        cursor: pointer;
        transition: background 0.15s; /* Animation douce du survol */
        text-decoration: none;        /* Supprime le soulignement du lien */
        background: #ffffff;          /* Fond blanc forcé */
        color: #131937;               /* Texte bleu foncé */
    }
    /* Survol ou sélection clavier : fond légèrement bleuté */
    .search-result-item:hover,
    .search-result-item[aria-selected="true"] { background: #f0f4ff; color: #131937; }

    /* Miniature du manga dans les résultats */
    .search-result-item img { width: 36px; height: 52px; object-fit: cover; border-radius: 4px; flex-shrink: 0; }

    /* Bloc texte (titre + métadonnées) */
    .search-result-info { display: flex; flex-direction: column; flex: 1; }

    /* Titre du manga : texte foncé forcé avec !important */
    .search-result-titre { font-weight: 600; font-size: 14px; color: #131937 !important; }

    /* Métadonnées : tome et catégorie, gris discret */
    .search-result-meta  { font-size: 12px; color: #666; }

    /* Prix : aligné à droite, rouge manga */
    .search-result-prix  { margin-left: auto; font-weight: 700; font-size: 14px; color: #c44444; white-space: nowrap; }

    /* Message "aucun résultat" */
    .search-no-result    { padding: 14px; text-align: center; color: #888; font-size: 14px; background: #fff; }

    /* Surlignage jaune du terme recherché */
    .search-highlight    { background: #ffe082; border-radius: 2px; color: #131937; }

    /* ── Alertes erreur (formulaires) ── */
    .alert-error {
        background: rgba(192,57,43,0.15); /* Rouge transparent */
        border: 1px solid var(--red);
        border-radius: 12px;
        padding: 14px 18px;
        margin-bottom: 20px;
    }
    .alert-error p { color: var(--red-bright); margin: 4px 0; font-size: 0.9rem; }

    /* ── Alertes succès (formulaires) ── */
    .alert-success {
        background: rgba(39,174,96,0.15); /* Vert transparent */
        border: 1px solid #27AE60;
        border-radius: 12px;
        padding: 14px 18px;
        margin-bottom: 20px;
        color: #2ECC71;
        font-size: 0.95rem;
    }

    /* Carte cliquable : supprime la décoration de lien */
    .card-link { text-decoration: none; display: block; }
`;
// Injection dans le <head> de la page
document.head.appendChild(style);

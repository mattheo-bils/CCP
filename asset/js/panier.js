/**
 * panier.js — Gestion complète de la page panier
 *
 * Ce script s'exécute uniquement sur la page panier.php.
 * Il gère l'affichage des articles, les boutons +/- et suppression,
 * et synchronise avec la BDD (connecté) ou le localStorage (invité).
 */
(function () {
    // Récupération des éléments du DOM nécessaires
    const container = document.getElementById('panier-items');  // Zone qui contient les articles
    const empty     = document.getElementById('panier-empty');  // Message "panier vide"
    const summary   = document.getElementById('panier-summary'); // Bloc récapitulatif + total

    // Si la page n'a pas ces éléments, on arrête tout de suite
    if (!container) return;

    // Détection de la position dans l'arborescence pour construire les URLs
    // Les pages dans views/ doivent remonter d'un niveau pour accéder à api/
    const inViews = window.location.pathname.includes('/views/');
    const apiBase = inViews ? '../api/panier.php' : 'api/panier.php';

    // ── Helpers localStorage (pour les invités non connectés) ──

    // Lit le panier depuis le localStorage du navigateur
    function getCartLocal() {
        try {
            return JSON.parse(localStorage.getItem('mm_cart') || '[]');
        } catch (e) {
            return []; // Retourne tableau vide si le JSON est corrompu
        }
    }

    // Sauvegarde le panier dans le localStorage
    function saveCartLocal(cart) {
        localStorage.setItem('mm_cart', JSON.stringify(cart));
    }

    // ── Appel générique vers l'API panier ─────────────────────

    /**
     * Envoie une requête POST à l'API panier
     * @param {string} action - L'action à effectuer (add, remove, delete, clear)
     * @param {object} body   - Les paramètres supplémentaires (ex: produit_id)
     * @returns {Promise}     - La réponse JSON de l'API
     */
    async function apiCall(action, body = {}) {
        const form = new FormData(); // Création d'un formulaire multipart
        form.append('action', action); // Ajout de l'action
        // Ajout de tous les paramètres supplémentaires
        for (const [k, v] of Object.entries(body)) form.append(k, v);
        const res = await fetch(apiBase, { method: 'POST', body: form });
        return res.json(); // Retourne la réponse décodée en objet JS
    }

    // ── Rendu HTML des articles du panier ─────────────────────

    /**
     * Affiche la liste des articles dans le DOM
     * @param {Array} items - Tableau d'articles à afficher
     */
    function renderItems(items) {
        // Suppression de tous les éléments existants sauf #panier-empty
        Array.from(container.children).forEach(c => {
            if (c !== empty) c.remove();
        });

        // Cas panier vide : on affiche le message et on cache le récap
        if (!items.length) {
            empty.style.display   = '';    // Affiche le message vide
            summary.style.display = 'none'; // Cache le récapitulatif
            return;
        }

        // Cas panier non vide
        empty.style.display   = 'none'; // Cache le message vide
        summary.style.display = '';     // Affiche le récapitulatif

        let total = 0; // Accumulateur pour le calcul du total

        items.forEach((item, idx) => {
            // Conversion du prix en nombre (gère les virgules françaises)
            const prix = parseFloat(String(item.prix).replace(',', '.').replace('€', '').trim()) || 0;

            // Récupération de la quantité (qty pour localStorage, quantite pour BDD)
            const qty = parseInt(item.qty || item.quantite) || 1;

            // Stock disponible (999 par défaut si non fourni = invité)
            const stock = parseInt(item.stock) || 999;

            // Ajout au total général
            total += prix * qty;

            // Construction du chemin de l'image
            // L'API retourne "asset/img/..." depuis la racine
            // Depuis views/ il faut ajouter "../"
            let imgSrc = item.img || item.image || '';
            if (imgSrc && inViews && !imgSrc.startsWith('../') && !imgSrc.startsWith('http')) {
                imgSrc = '../' + imgSrc; // Ajout du préfixe relatif
            }

            // Balise image HTML (vide si pas d'image)
            const imgTag = imgSrc
                ? `<img src="${imgSrc}" alt="${item.titre}" style="width:70px;height:100px;object-fit:cover;border-radius:6px;flex-shrink:0">`
                : '';

            // Désactivation du bouton + si on a atteint le stock maximum
            const plusDisabled = qty >= stock ? 'disabled style="opacity:0.4;cursor:not-allowed"' : '';

            // Création de l'élément HTML pour cet article
            const div = document.createElement('div');
            div.className = 'panier-item';
            div.innerHTML = `
                ${imgTag}
                <div class="panier-item-info">
                    <div class="panier-item-title">${item.titre}</div>
                    <div class="panier-item-price">${prix.toFixed(2).replace('.', ',')} €</div>
                    ${stock <= 3 && stock > 0
                        ? `<div style="font-size:0.75rem;color:var(--gold-light);margin-top:4px">⚠ Plus que ${stock} en stock</div>`
                        : '' /* Avertissement stock faible si <= 3 exemplaires */
                    }
                </div>
                <!-- Contrôles de quantité -->
                <div class="panier-qty">
                    <!-- Bouton diminuer la quantité -->
                    <button data-idx="${idx}" data-id="${item.id || item.produit_id}" data-action="minus">−</button>
                    <span>${qty}</span>
                    <!-- Bouton augmenter la quantité (désactivé si stock max atteint) -->
                    <button data-idx="${idx}" data-id="${item.id || item.produit_id}" data-action="plus" ${plusDisabled}>+</button>
                </div>
                <!-- Bouton de suppression de l'article -->
                <button class="panier-remove material-symbols-outlined"
                        data-idx="${idx}" data-id="${item.id || item.produit_id}" title="Supprimer">delete</button>
            `;
            // Insertion avant le message vide pour maintenir l'ordre
            container.insertBefore(div, empty);
        });

        // Mise à jour des totaux affichés dans le récapitulatif
        const fmt = v => v.toFixed(2).replace('.', ',') + ' €'; // Format français
        document.getElementById('sous-total').textContent = fmt(total);
        document.getElementById('total').textContent      = fmt(total);
    }

    // ── Chargement initial du panier ──────────────────────────

    /**
     * Charge les articles depuis la BDD ou le localStorage
     * et gère la fusion du localStorage vers la BDD à la connexion
     */
    async function loadCart() {
        try {
            // Appel à l'API pour vérifier si l'utilisateur est connecté
            const res  = await fetch(apiBase + '?action=list');
            const data = await res.json();

            if (data.connected) {
                // Utilisateur connecté → utiliser la BDD

                // Vérifier s'il reste des articles en localStorage (cas d'une connexion récente)
                const local = getCartLocal();
                if (local.length) {
                    // Fusion : transférer chaque article du localStorage vers la BDD
                    for (const item of local) {
                        await apiCall('add', { produit_id: item.id, quantite: item.qty || 1 });
                    }
                    // Vider le localStorage après la fusion
                    localStorage.removeItem('mm_cart');

                    // Recharger depuis la BDD après la fusion
                    const res2  = await fetch(apiBase + '?action=list');
                    const data2 = await res2.json();
                    renderItems(data2.items || []);
                } else {
                    // Pas de localStorage à fusionner : afficher directement la BDD
                    renderItems(data.items || []);
                }
            } else {
                // Invité non connecté → afficher depuis le localStorage
                renderItems(getCartLocal());
            }
        } catch (e) {
            // En cas d'erreur réseau, fallback sur le localStorage
            renderItems(getCartLocal());
        }
    }

    // ── Gestion des clics sur les boutons +/−/supprimer ───────

    // Délégation d'événement : un seul listener pour tous les boutons du container
    container.addEventListener('click', async e => {
        // Recherche du bouton cliqué (gère les clics sur les enfants)
        const btn = e.target.closest('[data-action], .panier-remove');
        if (!btn || btn.disabled) return; // Ignorer si bouton désactivé

        const produitId = btn.dataset.id;           // ID du produit concerné
        const idx       = parseInt(btn.dataset.idx, 10); // Index dans le tableau local

        // Vérifier l'état de connexion avant chaque action
        let connected = false;
        try {
            const r = await fetch(apiBase + '?action=list');
            const d = await r.json();
            connected = d.connected;
        } catch (e) {}

        if (connected) {
            // ── Mode BDD : appels API ──────────────────────
            if (btn.classList.contains('panier-remove')) {
                // Suppression complète de l'article
                await apiCall('delete', { produit_id: produitId });

            } else if (btn.dataset.action === 'plus') {
                // Incrémentation avec vérification du stock
                const result = await apiCall('add', { produit_id: produitId, quantite: 1 });
                if (result.error === 'stock_insuffisant' || result.error === 'rupture') {
                    // Rechargement pour mettre à jour l'affichage même en cas d'erreur
                    await loadCart();
                    return;
                }

            } else if (btn.dataset.action === 'minus') {
                // Décrémentation (-1 ou suppression si quantité = 0)
                await apiCall('remove', { produit_id: produitId });
            }

        } else {
            // ── Mode localStorage pour les invités ─────────
            const cart = getCartLocal();

            if (btn.classList.contains('panier-remove')) {
                cart.splice(idx, 1); // Suppression par index

            } else if (btn.dataset.action === 'plus') {
                cart[idx].qty = (cart[idx].qty || 1) + 1; // Incrémentation

            } else if (btn.dataset.action === 'minus') {
                cart[idx].qty = (cart[idx].qty || 1) - 1;
                if (cart[idx].qty <= 0) cart.splice(idx, 1); // Suppression si qty <= 0
            }

            saveCartLocal(cart); // Sauvegarde dans le localStorage
        }

        // Rechargement de l'affichage après l'action
        await loadCart();

        // Mise à jour du badge de quantité dans le header
        if (typeof updateCartBadge === 'function') updateCartBadge();
    });

    // Chargement initial au démarrage du script
    loadCart();
})();

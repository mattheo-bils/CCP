<?php
/**
 * views/panier.php — Page du panier d'achats
 *
 * Cette page ne contient que la structure HTML.
 * Tout le contenu dynamique (articles, totaux) est géré par panier.js
 * qui appelle l'API (connecté) ou lit le localStorage (invité).
 *
 * Structure :
 *   - #panier-items  : zone où panier.js injecte les articles
 *   - #panier-empty  : message "panier vide" (masqué si articles présents)
 *   - #panier-summary: récapitulatif avec totaux et bouton commander
 */

$pageTitle = "Mon panier";
$pageCss   = "pages.css";       // CSS partagé avec d'autres pages
$pageJs    = ["panier.js"];     // Script qui gère toute la logique du panier
$basePath  = '../';

// Chargement du header commun (session démarrée dans header.php)
require_once '../includes/header.php';
?>

<main>
    <div class="panier-page">
        <h1>Mon Panier</h1>

        <!-- Zone des articles du panier
             panier.js insère les articles ici et gère l'affichage de #panier-empty -->
        <div id="panier-items">
            <!-- Message panier vide : affiché par défaut, masqué si articles présents -->
            <div class="panier-empty" id="panier-empty" style="display:none">
                <span style="font-size:4rem">🛒</span>
                <p>Votre panier est vide.</p>
                <!-- Lien vers le catalogue pour inciter à ajouter des articles -->
                <a href="catalogue.php" class="btn btn-primary" style="margin-top:16px">
                    Voir le catalogue
                </a>
            </div>
        </div>

        <!-- Récapitulatif des prix : masqué par défaut, affiché si articles présents -->
        <div id="panier-summary" style="display:none">
            <div class="panier-summary">
                <!-- Ligne sous-total (mis à jour par panier.js) -->
                <div class="panier-summary-row">
                    <span>Sous-total</span>
                    <span id="sous-total">0,00 €</span>
                </div>
                <!-- Livraison gratuite (fixe) -->
                <div class="panier-summary-row">
                    <span>Livraison</span>
                    <span>Gratuite</span>
                </div>
                <!-- Ligne total en gras doré -->
                <div class="panier-total">
                    <span>Total</span>
                    <span id="total">0,00 €</span>
                </div>
                <!-- Bouton vers la page de finalisation de commande -->
                <a href="achat.php" class="btn btn-primary"
                   style="width:100%;text-align:center;padding:14px">
                    Passer la commande
                </a>
            </div>
        </div>
    </div>
</main>

<!-- CSS inline pour le centrage du message panier vide -->
<style>
.panier-page {
    display: flex;
    flex-direction: column;
    align-items: center; /* Centre horizontalement */
    min-height: 60vh;    /* Hauteur minimum pour que la page ne soit pas trop courte */
}
.panier-empty {
    display: flex;
    flex-direction: column;
    align-items: center;       /* Centre le contenu */
    justify-content: center;   /* Centre verticalement */
    text-align: center;
    padding: 80px 20px;
    width: 100%;
}
</style>

<?php require_once '../includes/footer.php'; ?>

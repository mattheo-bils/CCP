<?php
$pageTitle = "Mon panier";
$pageCss   = "pages.css";
$pageJs    = ["panier.js"];
$basePath  = '../';
require_once '../includes/header.php';
?>

<main>
    <div class="panier-page">
        <h1>Mon Panier</h1>
        <div id="panier-items">
            <div class="panier-empty" id="panier-empty" style="display:none">
                <span style="font-size:4rem">🛒</span>
                <p>Votre panier est vide.</p>
                <a href="catalogue.php" class="btn btn-primary" style="margin-top:16px">
                    Voir le catalogue
                </a>
            </div>
        </div>
        <div id="panier-summary" style="display:none">
            <div class="panier-summary">
                <div class="panier-summary-row">
                    <span>Sous-total</span>
                    <span id="sous-total">0,00 €</span>
                </div>
                <div class="panier-summary-row">
                    <span>Livraison</span>
                    <span>Gratuite</span>
                </div>
                <div class="panier-total">
                    <span>Total</span>
                    <span id="total">0,00 €</span>
                </div>
                <a href="achat.php" class="btn btn-primary"
                   style="width:100%;text-align:center;padding:14px">
                    Passer la commande
                </a>
            </div>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>

<?php
/**
 * footer.php — Pied de page commun à toutes les pages
 *
 * Variables attendues :
 *   $basePath — Chemin relatif vers la racine (défini dans header.php)
 *   $pageJs   — Tableau de fichiers JS à charger (optionnel, défini par la page)
 */
$base = $basePath ?? '';
?>

<footer>
    <div class="footer-inner">
        <div class="footer-brand">
            <div class="footer-logo">
                <span class="logo-manga">MANGA</span><span class="logo-market">MARKET</span>
            </div>
            <p class="footer-tagline">Votre univers manga, livré chez vous.</p>
        </div>
        <div class="footer-links">
            <div class="footer-col">
                <h4>Légal</h4>
                <a href="<?= $base ?>views/legals.php">Mentions légales</a>
                <a href="#">Politique de confidentialité</a>
            </div>
            <div class="footer-col">
                <h4>À propos</h4>
                <a href="#">Qui sommes-nous</a>
                <a href="#">FAQ</a>
            </div>
            <div class="footer-col">
                <h4>Service client</h4>
                <a href="<?= $base ?>views/contact.php">Nous contacter</a>
                <a href="#">Faire un retour</a>
                <a href="#">Suivi de commande</a>
                <a href="#">Livraison</a>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>© <?= date('Y') ?> MangaMarket. Tous droits réservés.</p>
    </div>
</footer>

<!-- Scripts spécifiques à la page courante -->
<?php if (isset($pageJs)): ?>
    <?php foreach ((array)$pageJs as $js): ?>
        <script src="<?= $base ?>asset/js/<?= htmlspecialchars($js) ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<!-- search.js chargé sur toutes les pages (barre de recherche dans le header) -->
<script src="<?= $base ?>asset/js/search.js"></script>

<!-- header.js chargé en dernier (burger, badge panier, boutons ajouter) -->
<script src="<?= $base ?>asset/js/header.js"></script>

</body>
</html>

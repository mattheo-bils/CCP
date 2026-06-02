<?php
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
<?php if (isset($pageJs)): ?>
    <script src="<?= $base ?>asset/js/data.js"></script>
    <?php foreach ((array)$pageJs as $js): ?>
        <script src="<?= $base ?>asset/js/<?= htmlspecialchars($js) ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
<script src="<?= $base ?>asset/js/header.js"></script>
</body>
</html>

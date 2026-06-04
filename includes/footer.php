<?php
/**
 * footer.php — Pied de page commun à toutes les pages
 *
 * Inclus en fin de chaque page via require_once.
 * Charge également les fichiers JS définis dans $pageJs.
 *
 * Variables attendues :
 *   $basePath — Chemin relatif vers la racine (défini dans header.php)
 *   $pageJs   — Tableau de fichiers JS à charger (optionnel, défini par la page)
 */
$base = $basePath ?? '';
?>

<!-- ── Pied de page ──────────────────────────────────────── -->
<footer>
    <div class="footer-inner">

        <!-- Bloc marque -->
        <div class="footer-brand">
            <div class="footer-logo">
                <span class="logo-manga">MANGA</span><span class="logo-market">MARKET</span>
            </div>
            <p class="footer-tagline">Votre univers manga, livré chez vous.</p>
        </div>

        <!-- Liens de navigation du footer -->
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

    <!-- Copyright avec année dynamique -->
    <div class="footer-bottom">
        <p>© <?= date('Y') ?> MangaMarket. Tous droits réservés.</p>
    </div>
</footer>

<!-- ── Chargement des scripts JS ─────────────────────────── -->

<?php if (isset($pageJs)): ?>
    <!-- data.js contient les données produits partagées entre les scripts -->
    <script src="<?= $base ?>asset/js/data.js"></script>
    <!-- Scripts spécifiques à la page courante -->
    <?php foreach ((array)$pageJs as $js): ?>
        <script src="<?= $base ?>asset/js/<?= htmlspecialchars($js) ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Script global du header (burger menu, badge panier, boutons ajouter) -->
<script src="<?= $base ?>asset/js/header.js"></script>

</body>
</html>

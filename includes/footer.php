<?php
/**
 * includes/footer.php — Pied de page commun à toutes les pages
 *
 * Inclus en fin de chaque page via require_once '../includes/footer.php'.
 * Contient le HTML du footer et charge les fichiers JS.
 *
 * Variables attendues (définies par la page appelante) :
 *   $basePath — Chemin relatif vers la racine : '' ou '../'
 *   $pageJs   — Tableau de fichiers JS spécifiques à la page (optionnel)
 */

// Récupération du chemin de base (défini dans header.php)
$base = $basePath ?? '';
?>

<!-- ── Pied de page ──────────────────────────────────────────── -->
<footer>
    <div class="footer-inner">

        <!-- Bloc marque : logo + tagline -->
        <div class="footer-brand">
            <div class="footer-logo">
                <!-- Logo identique au header -->
                <span class="logo-manga">MANGA</span><span class="logo-market">MARKET</span>
            </div>
            <p class="footer-tagline">Votre univers manga, livré chez vous.</p>
        </div>

        <!-- Colonnes de liens de navigation -->
        <div class="footer-links">

            <!-- Colonne Légal -->
            <div class="footer-col">
                <h4>Légal</h4>
                <a href="<?= $base ?>views/legals.php">Mentions légales</a>
                <a href="#">Politique de confidentialité</a>
            </div>

            <!-- Colonne À propos -->
            <div class="footer-col">
                <h4>À propos</h4>
                <a href="#">Qui sommes-nous</a>
                <a href="#">FAQ</a>
            </div>

            <!-- Colonne Service client -->
            <div class="footer-col">
                <h4>Service client</h4>
                <a href="<?= $base ?>views/contact.php">Nous contacter</a>
                <a href="#">Faire un retour</a>
                <a href="#">Suivi de commande</a>
                <a href="#">Livraison</a>
            </div>
        </div>
    </div>

    <!-- Barre de copyright avec année dynamique -->
    <div class="footer-bottom">
        <!-- date('Y') retourne l'année courante automatiquement -->
        <p>© <?= date('Y') ?> MangaMarket. Tous droits réservés.</p>
    </div>
</footer>

<!-- ── Chargement des scripts JavaScript ─────────────────────── -->

<?php if (isset($pageJs)): ?>
    <!-- Scripts spécifiques à la page courante (définis dans $pageJs) -->
    <?php foreach ((array)$pageJs as $js): ?>
        <!-- htmlspecialchars() sécurise le nom de fichier contre les injections XSS -->
        <script src="<?= $base ?>asset/js/<?= htmlspecialchars($js) ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<!-- search.js chargé sur toutes les pages car la barre de recherche
     est présente dans le header commun à toutes les pages -->
<script src="<?= $base ?>asset/js/search.js"></script>

<!-- header.js chargé en dernier pour avoir accès à tous les éléments du DOM
     (burger menu, badge panier, boutons "Ajouter au panier") -->
<script src="<?= $base ?>asset/js/header.js"></script>

</body>
</html>

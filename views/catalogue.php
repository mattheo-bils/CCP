<?php
$pageTitle = "Catalogue";
$pageCss   = "catalogue.css";
$pageJs    = ["catalogue.js", "search.js"];
$basePath = '../';
require_once '../includes/header.php';
?>

<main>
    <div class="catalogue-layout">
        <!-- Filtres -->
        <aside class="filtre">
            <h3>Filtres</h3>

            <div class="filtre-group">
                <label>Catégories</label>
                <?php
                $categories = ['kodomo' => 'Kodomo', 'shonen' => 'Shonen', 'shojo' => 'Shojo', 'seinen' => 'Seinen', 'josei' => 'Josei'];
                foreach ($categories as $val => $label): ?>
                <button class="filtre-btn" data-categorie="<?= $val ?>">
                    <input type="checkbox" id="cat-<?= $val ?>">
                    <label for="cat-<?= $val ?>" style="pointer-events:none"><?= $label ?></label>
                </button>
                <?php endforeach; ?>
            </div>

            <div class="filtre-group">
                <label>Prix maximum</label>
                <input type="range" id="price-range" min="0" max="30" value="30" step="0.5">
                <div class="range-value" id="price-display">30 €</div>
                <div class="range-row"><span>0 €</span><span>30 €</span></div>
            </div>
        </aside>

        <!-- Grille produits -->
        <div class="catalogue-grid-wrapper">
            <div class="catalogue-topbar">
                <span class="catalogue-count" id="catalogue-count">Chargement…</span>
            </div>
            <div class="fullcard" id="catalogue-grid"></div>
            <div class="page" id="pagination"></div>
        </div>
    </div>
</main>

<?php $basePath = '../';
require_once '../includes/footer.php'; ?>

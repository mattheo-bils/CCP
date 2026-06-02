<?php
$pageTitle = "Catalogue";
$pageCss   = "catalogue.css";
$pageJs    = ["catalogue.js", "search.js"];
$basePath  = '../';
require_once '../includes/header.php';

// Récupération des catégories depuis la BDD pour les filtres
$categories = [];
try {
    require_once '../includes/db.php';
    $stmt = $pdo->query("SELECT slug, nom FROM categories ORDER BY id");
    $categories = $stmt->fetchAll();
} catch (Exception $e) {
    $categories = [
        ['slug'=>'kodomo','nom'=>'Kodomo'],
        ['slug'=>'shonen','nom'=>'Shonen'],
        ['slug'=>'shojo', 'nom'=>'Shojo'],
        ['slug'=>'seinen','nom'=>'Seinen'],
        ['slug'=>'josei', 'nom'=>'Josei'],
    ];
}

// Catégorie pré-sélectionnée via URL (?categorie=shonen)
$preselect = $_GET['categorie'] ?? '';
?>

<main>
    <div class="catalogue-layout">
        <!-- Filtres -->
        <aside class="filtre">
            <h3>Filtres</h3>
            <div class="filtre-group">
                <label>Catégories</label>
                <?php foreach ($categories as $cat): ?>
                <button class="filtre-btn <?= $preselect === $cat['slug'] ? 'active' : '' ?>"
                        data-categorie="<?= htmlspecialchars($cat['slug']) ?>">
                    <input type="checkbox"
                           id="cat-<?= htmlspecialchars($cat['slug']) ?>"
                           <?= $preselect === $cat['slug'] ? 'checked' : '' ?>>
                    <label for="cat-<?= htmlspecialchars($cat['slug']) ?>"
                           style="pointer-events:none">
                        <?= htmlspecialchars($cat['nom']) ?>
                    </label>
                </button>
                <?php endforeach; ?>
            </div>
            <div class="filtre-group">
                <label>Prix maximum</label>
                <input type="range" id="price-range" min="0" max="15" value="15" step="0.5">
                <div class="range-value" id="price-display">15 €</div>
                <div class="range-row"><span>0 €</span><span>15 €</span></div>
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

<?php require_once '../includes/footer.php'; ?>

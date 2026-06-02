<?php
$pageTitle = "Catalogue";
$pageCss   = "catalogue.css";
$pageJs    = []; // plus de catalogue.js ni data.js
$basePath  = '../';

// ── Filtres via GET ────────────────────────────────────────
$catsActives = isset($_GET['categories']) ? (array)$_GET['categories'] : [];
$prixMax     = isset($_GET['prix_max'])   ? (float)$_GET['prix_max']   : 15.0;
// Rétrocompatibilité : ?categorie=shonen (lien depuis index)
if (empty($catsActives) && !empty($_GET['categorie'])) {
    $catsActives = [$_GET['categorie']];
}
$prixMax = max(0, min(15, $prixMax));

// ── Données BDD ────────────────────────────────────────────
$categories = [];
$produits   = [];

try {
    require_once '../includes/db.php';

    $stmt = $pdo->query("SELECT slug, nom FROM categories ORDER BY id");
    $categories = $stmt->fetchAll();

    // Construction de la requête filtrée
    $where  = ['p.prix <= :prix_max'];
    $params = [':prix_max' => $prixMax];

    if (!empty($catsActives)) {
        // Sanitize slugs (alphanum + underscore uniquement)
        $catsActives = array_filter($catsActives, fn($s) => preg_match('/^[a-z_]+$/', $s));
        if (!empty($catsActives)) {
            $placeholders = implode(',', array_map(fn($i) => ":cat$i", array_keys($catsActives)));
            $where[] = "c.slug IN ($placeholders)";
            foreach ($catsActives as $i => $slug) {
                $params[":cat$i"] = $slug;
            }
        }
    }

    $sql = "
        SELECT p.id, p.titre, p.tome, p.prix, p.image, c.slug AS categorie, c.nom AS categorie_nom
        FROM produits p
        JOIN categories c ON c.id = p.categorie_id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY p.titre
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $produits = $stmt->fetchAll();

} catch (Exception $e) {
    $categories = [
        ['slug'=>'kodomo','nom'=>'Kodomo'],
        ['slug'=>'shonen','nom'=>'Shonen'],
        ['slug'=>'shojo', 'nom'=>'Shojo'],
        ['slug'=>'seinen','nom'=>'Seinen'],
        ['slug'=>'josei', 'nom'=>'Josei'],
    ];
    $produits = [];
}

require_once '../includes/header.php';
?>

<main>
    <div class="catalogue-layout">

        <!-- ── Filtres ──────────────────────────────────── -->
        <aside class="filtre">
            <h3>Filtres</h3>
            <form method="get" action="catalogue.php" id="filtre-form">

                <div class="filtre-group">
                    <label>Catégories</label>
                    <?php foreach ($categories as $cat):
                        $checked = in_array($cat['slug'], $catsActives, true);
                    ?>
                    <button type="button"
                            class="filtre-btn <?= $checked ? 'active' : '' ?>"
                            data-categorie="<?= htmlspecialchars($cat['slug']) ?>">
                        <input type="checkbox"
                               name="categories[]"
                               id="cat-<?= htmlspecialchars($cat['slug']) ?>"
                               value="<?= htmlspecialchars($cat['slug']) ?>"
                               <?= $checked ? 'checked' : '' ?>>
                        <label for="cat-<?= htmlspecialchars($cat['slug']) ?>"
                               style="pointer-events:none">
                            <?= htmlspecialchars($cat['nom']) ?>
                        </label>
                    </button>
                    <?php endforeach; ?>
                </div>

                <div class="filtre-group">
                    <label>Prix maximum</label>
                    <input type="range" id="price-range" name="prix_max"
                           min="0" max="15" step="0.5"
                           value="<?= htmlspecialchars($prixMax) ?>">
                    <div class="range-value" id="price-display">
                        <?= number_format($prixMax, 2, ',', '') ?> €
                    </div>
                    <div class="range-row"><span>0 €</span><span>15 €</span></div>
                </div>

                <button type="submit" class="btn btn-primary"
                        style="width:100%;margin-top:12px">
                    Appliquer
                </button>
                <?php if (!empty($catsActives) || $prixMax < 15): ?>
                <a href="catalogue.php" class="btn btn-outline"
                   style="width:100%;text-align:center;margin-top:8px;display:block">
                    Réinitialiser
                </a>
                <?php endif; ?>

            </form>
        </aside>

        <!-- ── Grille produits ──────────────────────────── -->
        <div class="catalogue-grid-wrapper">
            <div class="catalogue-topbar">
                <span class="catalogue-count">
                    <?= count($produits) ?> produit<?= count($produits) > 1 ? 's' : '' ?>
                </span>
            </div>

            <div class="fullcard">
                <?php if (empty($produits)): ?>
                    <p style="color:var(--grey-400);grid-column:1/-1;padding:40px 0">
                        Aucun produit trouvé.
                    </p>
                <?php else: ?>
                    <?php foreach ($produits as $p): ?>
                    <a href="produit.php?id=<?= (int)$p['id'] ?>" class="card card-link">
                        <img src="../<?= htmlspecialchars($p['image']) ?>"
                             alt="<?= htmlspecialchars($p['titre']) ?>">
                        <div class="card-body">
                            <div class="card-title"><?= htmlspecialchars($p['titre']) ?></div>
                            <div class="card-row">
                                <span class="card-price">
                                    <?= number_format($p['prix'], 2, ',', '') ?> €
                                </span>
                                <span class="card-tome">Tome <?= (int)$p['tome'] ?></span>
                            </div>
                            <div class="card-row" style="margin-top:10px">
                                <button class="btn-add"
                                        data-id="<?= (int)$p['id'] ?>"
                                        data-titre="<?= htmlspecialchars($p['titre']) ?>"
                                        data-prix="<?= number_format($p['prix'], 2, ',', '') ?>"
                                        data-img="../<?= htmlspecialchars($p['image']) ?>"
                                        onclick="event.preventDefault()">
                                    + Ajouter
                                </button>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>
</main>

<script>
// Mise à jour live du label prix + soumission auto au relâchement
(function () {
    const range   = document.getElementById('price-range');
    const display = document.getElementById('price-display');
    const form    = document.getElementById('filtre-form');
    if (!range || !display) return;

    range.addEventListener('input', () => {
        display.textContent = parseFloat(range.value).toFixed(2).replace('.', ',') + ' €';
    });
    range.addEventListener('change', () => form.submit());

    // Toggle visuel des boutons catégorie + soumission auto
    document.querySelectorAll('.filtre-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            btn.classList.toggle('active');
            const cb = btn.querySelector('input[type="checkbox"]');
            if (cb) cb.checked = !cb.checked;
            form.submit();
        });
    });
})();
</script>

<?php require_once '../includes/footer.php'; ?>

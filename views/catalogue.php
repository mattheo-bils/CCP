<?php
/**
 * catalogue.php — Page catalogue avec filtres PHP côté serveur
 *
 * Filtres disponibles via GET :
 *   ?categories[]  — Slugs des catégories sélectionnées (tableau)
 *   ?prix_max      — Prix maximum (float, défaut 15)
 *   ?categorie     — Rétrocompatibilité : slug unique depuis les liens catégorie
 */

$pageTitle = "Catalogue";
$pageCss   = "catalogue.css";
$pageJs    = []; // Pas de JS spécifique : tout est rendu côté PHP
$basePath  = '../';

// ── Récupération des filtres depuis l'URL ─────────────────
$catsActives = isset($_GET['categories']) ? (array)$_GET['categories'] : [];
$prixMax     = isset($_GET['prix_max'])   ? (float)$_GET['prix_max']   : 15.0;

// Rétrocompatibilité : ?categorie=shonen (liens depuis la page d'accueil)
if (empty($catsActives) && !empty($_GET['categorie'])) {
    $catsActives = [$_GET['categorie']];
}

// Borner le prix entre 0 et 15 €
$prixMax = max(0, min(15, $prixMax));

// ── Chargement des données depuis la BDD ──────────────────
$categories = [];
$produits   = [];

try {
    require_once '../includes/db.php';

    // Récupération de toutes les catégories pour les boutons de filtre
    $stmt = $pdo->query("SELECT slug, nom FROM categories ORDER BY id");
    $categories = $stmt->fetchAll();

    // ── Construction de la requête filtrée ────────────────
    $where  = ['p.prix <= :prix_max'];
    $params = [':prix_max' => $prixMax];

    // Ajout du filtre catégorie si des catégories sont sélectionnées
    if (!empty($catsActives)) {
        // Sécurité : on n'accepte que des slugs alphanumérique + underscore
        $catsActives = array_filter($catsActives, fn($s) => preg_match('/^[a-z_]+$/', $s));
        if (!empty($catsActives)) {
            $placeholders = implode(',', array_map(fn($i) => ":cat$i", array_keys($catsActives)));
            $where[] = "c.slug IN ($placeholders)";
            foreach ($catsActives as $i => $slug) {
                $params[":cat$i"] = $slug;
            }
        }
    }

    // Dédoublonnage par titre (GROUP BY) pour éviter les doublons de tomes
    $sql = "
        SELECT MIN(p.id) AS id, p.titre, MIN(p.tome) AS tome,
               MIN(p.prix) AS prix, MIN(p.image) AS image,
               c.slug AS categorie, c.nom AS categorie_nom
        FROM produits p
        JOIN categories c ON c.id = p.categorie_id
        WHERE " . implode(' AND ', $where) . "
        GROUP BY p.titre, c.slug, c.nom
        ORDER BY p.titre
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $produits = $stmt->fetchAll();

} catch (Exception $e) {
    // Fallback si la BDD est indisponible
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

        <!-- ── Panneau de filtres ─────────────────────────── -->
        <aside class="filtre">
            <h3>Filtres</h3>
            <!-- Formulaire GET : les filtres sont passés dans l'URL -->
            <form method="get" action="catalogue.php" id="filtre-form">

                <!-- Filtre par catégorie -->
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

                <!-- Filtre par prix maximum -->
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

                <!-- Bouton d'application des filtres -->
                <button type="submit" class="btn btn-primary"
                        style="width:100%;margin-top:12px">
                    Appliquer
                </button>

                <!-- Bouton de réinitialisation affiché uniquement si des filtres sont actifs -->
                <?php if (!empty($catsActives) || $prixMax < 15): ?>
                <a href="catalogue.php" class="btn btn-outline"
                   style="width:100%;text-align:center;margin-top:8px;display:block">
                    Réinitialiser
                </a>
                <?php endif; ?>

            </form>
        </aside>

        <!-- ── Grille des produits ────────────────────────── -->
        <div class="catalogue-grid-wrapper">
            <!-- Compteur de résultats -->
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
                    <!-- Carte produit cliquable -->
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
                                <!-- Bouton géré par header.js -->
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
/**
 * Interactions client légères pour les filtres :
 * - Mise à jour live du label de prix sans rechargement
 * - Soumission automatique du formulaire au relâchement du slider
 * - Toggle des boutons catégorie + soumission automatique
 */
(function () {
    const range   = document.getElementById('price-range');
    const display = document.getElementById('price-display');
    const form    = document.getElementById('filtre-form');
    if (!range || !display) return;

    // Mise à jour de l'affichage du prix en temps réel
    range.addEventListener('input', () => {
        display.textContent = parseFloat(range.value).toFixed(2).replace('.', ',') + ' €';
    });

    // Soumission auto quand l'utilisateur relâche le slider
    range.addEventListener('change', () => form.submit());

    // Toggle visuel des boutons catégorie + cochage de la checkbox + soumission
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

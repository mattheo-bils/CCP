<?php
/**
 * produit.php — Fiche produit détaillée
 *
 * Récupère le produit depuis la BDD via ?id=X.
 * Affiche : image, titre, prix, auteur, genre, description.
 * Affiche également des suggestions de la même catégorie.
 * Redirige vers catalogue.php si l'ID est invalide ou introuvable.
 */

$basePath = '../';

$produit     = null;
$suggestions = [];

try {
    require_once '../includes/db.php';

    // Récupération et validation de l'ID produit
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        header('Location: catalogue.php');
        exit;
    }

    // Chargement du produit avec sa catégorie
    $stmt = $pdo->prepare("
        SELECT p.*, c.slug AS categorie_slug, c.nom AS categorie_nom
        FROM produits p
        JOIN categories c ON c.id = p.categorie_id
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    $produit = $stmt->fetch();

    // Produit introuvable → retour au catalogue
    if (!$produit) {
        header('Location: catalogue.php');
        exit;
    }

    // Suggestions : autres mangas de la même catégorie (dédoublonnés par titre)
    $stmt2 = $pdo->prepare("
        SELECT MIN(p.id) AS id, p.titre, MIN(p.tome) AS tome,
               MIN(p.prix) AS prix, MIN(p.image) AS image
        FROM produits p
        WHERE p.categorie_id = ? AND p.id != ?
        GROUP BY p.titre
        ORDER BY RAND()
        LIMIT 4
    ");
    $stmt2->execute([$produit['categorie_id'], $id]);
    $suggestions = $stmt2->fetchAll();

} catch (Exception $e) {
    // Fallback statique si la BDD est indisponible
    $produit = [
        'id'            => 7,
        'titre'         => 'Bleach',
        'auteur'        => 'Tite Kubo',
        'tome'          => 2,
        'prix'          => 7.80,
        'categorie_slug'=> 'shonen',
        'categorie_nom' => 'Shonen',
        'image'         => '../asset/img/bleach_tome_2_page_de_couverture.jpg',
        'description'   => "Bleach raconte l'histoire d'Ichigo Kurosaki, un adolescent capable de voir les esprits.",
    ];
    $suggestions = [];
}

// Définition des métadonnées de la page
$pageTitle = htmlspecialchars($produit['titre']) . ' — Tome ' . (int)$produit['tome'];
$pageCss   = "produit.css";
$pageJs    = ["produit.js", "search.js"];

require_once '../includes/header.php';
?>

<main>
    <!-- ── Fil d'Ariane ──────────────────────────────────── -->
    <nav class="chemin" aria-label="Fil d'Ariane">
        <a href="../index.php">Accueil</a>
        <span class="sep">/</span>
        <a href="catalogue.php">Catalogue</a>
        <span class="sep">/</span>
        <span><?= htmlspecialchars($produit['titre']) ?> — Tome <?= (int)$produit['tome'] ?></span>
    </nav>

    <!-- ── Fiche produit ─────────────────────────────────── -->
    <section class="produit">
        <!-- Image principale -->
        <div class="image-col">
            <img src="../<?= htmlspecialchars($produit['image']) ?>"
                 alt="<?= htmlspecialchars($produit['titre']) ?> Tome <?= (int)$produit['tome'] ?>"
                 class="image-affiche" id="main-img">
        </div>

        <!-- Informations produit -->
        <div class="description">
            <h1><?= htmlspecialchars($produit['titre']) ?> — Tome <?= (int)$produit['tome'] ?></h1>

            <!-- Prix -->
            <div class="prix-row">
                <span class="prix-label">Prix unitaire</span>
                <span class="prix-valeur"><?= number_format($produit['prix'], 2, ',', '') ?> €</span>
            </div>

            <!-- Métadonnées -->
            <table class="meta-table">
                <tr><td>Genre</td><td><?= htmlspecialchars($produit['categorie_nom']) ?></td></tr>
                <tr><td>Auteur</td><td><?= htmlspecialchars($produit['auteur']) ?></td></tr>
                <tr><td>Tome</td><td><?= (int)$produit['tome'] ?></td></tr>
            </table>

            <!-- Description -->
            <p class="description-text"><?= htmlspecialchars($produit['description'] ?? '') ?></p>

            <!-- Boutons d'action -->
            <div class="btn-suite">
                <!-- Ajouter au panier (géré par header.js) -->
                <button class="btn btn-outline btn-add"
                        data-id="<?= (int)$produit['id'] ?>"
                        data-titre="<?= htmlspecialchars($produit['titre']) ?>"
                        data-prix="<?= number_format($produit['prix'], 2, ',', '') ?>"
                        data-img="../<?= htmlspecialchars($produit['image']) ?>">
                    <span class="material-symbols-outlined" style="vertical-align:middle;font-size:18px">shopping_cart</span>
                    Ajouter au panier
                </button>
                <!-- Achat direct → passe l'id du produit à achat.php -->
                <a href="achat.php?id=<?= (int)$produit['id'] ?>" class="btn btn-primary">Acheter maintenant</a>
            </div>
        </div>
    </section>

    <!-- ── Suggestions (même catégorie) ─────────────────── -->
    <?php if (!empty($suggestions)): ?>
    <section class="suggestion">
        <h2 class="section-heading">Vous aimerez aussi</h2>
        <div class="suggestion-grid">
            <?php foreach ($suggestions as $s): ?>
            <a href="produit.php?id=<?= (int)$s['id'] ?>" class="card card-link">
                <img src="../<?= htmlspecialchars($s['image']) ?>" alt="<?= htmlspecialchars($s['titre']) ?>">
                <div class="card-body">
                    <div class="card-title"><?= htmlspecialchars($s['titre']) ?></div>
                    <div class="card-row">
                        <span class="card-price"><?= number_format($s['prix'], 2, ',', '') ?> €</span>
                        <span class="card-tome">Tome <?= (int)$s['tome'] ?></span>
                    </div>
                    <div class="card-row" style="margin-top:10px">
                        <button class="btn-add"
                                data-id="<?= (int)$s['id'] ?>"
                                data-titre="<?= htmlspecialchars($s['titre']) ?>"
                                data-prix="<?= number_format($s['prix'], 2, ',', '') ?>"
                                data-img="../<?= htmlspecialchars($s['image']) ?>"
                                onclick="event.preventDefault()">
                            + Ajouter
                        </button>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</main>

<?php require_once '../includes/footer.php'; ?>

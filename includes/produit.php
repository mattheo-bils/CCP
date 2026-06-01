<?php
$pageTitle = "Produit";
$pageCss   = "produit.css";
$pageJs    = ["produit.js", "search.js"];
require_once 'includes/header.php';
?>

<main>
    <!-- Fil d'Ariane -->
    <nav class="chemin" aria-label="Fil d'Ariane">
        <a href="index.php">Accueil</a>
        <span class="sep">/</span>
        <a href="catalogue.php">Catalogue</a>
        <span class="sep">/</span>
        <span>Bleach Tome 2</span>
    </nav>

    <!-- Produit principal -->
    <section class="produit">
        <div class="image-col">
            <img src="asset/img/bleach_tome_2_page_de_couverture.jpg"
                 alt="Bleach Tome 2 couverture"
                 class="image-affiche" id="main-img">
            <div class="thumbnails">
                <img src="asset/img/bleach_tome_2_page_de_couverture.jpg"
                     alt="Vue 1" class="image-autre active">
            </div>
        </div>

        <div class="description">
            <h1>Bleach — Tome 2</h1>

            <div class="prix-row">
                <span class="prix-label">Prix unitaire</span>
                <span class="prix-valeur">7,20 €</span>
            </div>

            <table class="meta-table">
                <tr>
                    <td>Genre</td>
                    <td>Shonen</td>
                </tr>
                <tr>
                    <td>Auteur</td>
                    <td>Tite Kubo</td>
                </tr>
                <tr>
                    <td>Éditeur</td>
                    <td>Viz Media</td>
                </tr>
                <tr>
                    <td>Parution</td>
                    <td>2002</td>
                </tr>
                <tr>
                    <td>Pages</td>
                    <td>192</td>
                </tr>
            </table>

            <p class="description-text">
                Bleach raconte l'histoire d'Ichigo Kurosaki, un adolescent capable de voir les esprits,
                qui obtient les pouvoirs d'un Shinigami après avoir rencontré Rukia Kuchiki.
                Chargé de protéger les humains des Hollows, des esprits maléfiques, il combat de puissants
                ennemis tout en découvrant le monde de la Soul Society. Au fil des batailles, Ichigo dévoile
                des pouvoirs cachés et se retrouve au cœur de conflits majeurs entre Shinigamis,
                Arrancars et Quincy.
            </p>

            <div class="btn-suite">
                <button class="btn btn-outline" id="btn-panier">
                    <span class="material-symbols-outlined" style="vertical-align:middle;font-size:18px">shopping_cart</span>
                    Ajouter au panier
                </button>
                <a href="achat.php" class="btn btn-primary">Acheter maintenant</a>
            </div>
        </div>
    </section>

    <!-- Suggestions -->
    <section class="suggestion">
        <h2 class="section-heading">Suggestions</h2>
        <div class="suggestion-grid">
            <?php
            $suggestions = [
                ['titre' => 'Berserk',      'tome' => 'Tome 1',  'prix' => '7,20€', 'img' => 'berserk_tome_1_page_de_couverture.jpg'],
                ['titre' => 'Dragon Ball',  'tome' => 'Tome 35', 'prix' => '7,20€', 'img' => 'dragonball_tome_35_page_de_couverture.jpg'],
                ['titre' => 'One Piece',    'tome' => 'Tome 35', 'prix' => '7,20€', 'img' => 'one_piece_tome_35_page_de_couverture.jpg'],
            ];
            foreach ($suggestions as $m): ?>
            <article class="card" style="min-width:210px">
                <img src="asset/img/<?= htmlspecialchars($m['img']) ?>" alt="<?= htmlspecialchars($m['titre']) ?>">
                <div class="card-body">
                    <div class="card-title"><?= htmlspecialchars($m['titre']) ?></div>
                    <div class="card-row">
                        <span class="card-price"><?= htmlspecialchars($m['prix']) ?></span>
                        <span class="card-tome"><?= htmlspecialchars($m['tome']) ?></span>
                    </div>
                    <div class="card-row" style="margin-top:10px">
                        <button class="btn-add">+ Ajouter</button>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>

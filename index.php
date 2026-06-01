<?php
$pageTitle = "Accueil";
$pageCss   = "index.css";
$pageJs    = ["index.js", "search.js"];
require_once 'includes/header.php';
?>

<main>
    <!-- Hero -->
    <section class="hero noise">
        <div class="hero-content">
            <span class="hero-tag">Nouveauté Shonen</span>
            <h1>Commencer un shonen ?<br>Voici le premier tome de <em>Dragon Ball</em></h1>
            <div class="hero-buttons">
                <a href="produit.php" class="btn btn-primary">Voir l'article</a>
                <a href="catalogue.php" class="btn btn-outline">Voir la collection</a>
            </div>
        </div>
        <div class="hero-scroll">
            <span class="material-symbols-outlined" style="font-size:18px">expand_more</span>
            scroll
        </div>
    </section>

    <!-- Catégories -->
    <section class="categories-section">
        <h2 class="section-heading">Catégories</h2>
        <div class="carousel-wrapper">
            <div class="carousel-inner" id="carousel-inner">
                <div id="kodomo" class="slide">
                    <div class="slide-bg"></div>
                    <div class="slide-label"><span>Catégorie</span>Kodomo</div>
                </div>
                <div id="shonen" class="slide">
                    <div class="slide-bg"></div>
                    <div class="slide-label"><span>Catégorie</span>Shonen</div>
                </div>
                <div id="shojo" class="slide">
                    <div class="slide-bg"></div>
                    <div class="slide-label"><span>Catégorie</span>Shojo</div>
                </div>
                <div id="seinen" class="slide">
                    <div class="slide-bg"></div>
                    <div class="slide-label"><span>Catégorie</span>Seinen</div>
                </div>
                <div id="josei" class="slide">
                    <div class="slide-bg"></div>
                    <div class="slide-label"><span>Catégorie</span>Josei</div>
                </div>
            </div>
        </div>
        <div class="carousel-controls">
            <button class="carousel-btn" id="prev">
                <span class="material-symbols-outlined">arrow_back</span>
            </button>
            <button class="carousel-btn" id="next">
                <span class="material-symbols-outlined">arrow_forward</span>
            </button>
        </div>
    </section>

    <!-- Tendances -->
    <section class="trending-section">
        <h2 class="section-heading">Tendances</h2>
        <div class="cards-grid" id="trending-cards">
            <?php
            $tendances = [
                ['titre' => 'Berserk',         'tome' => 'Tome 1',  'prix' => '7,20€', 'img' => 'berserk_tome_1_page_de_couverture.jpg'],
                ['titre' => 'Dragon Ball',      'tome' => 'Tome 35', 'prix' => '7,20€', 'img' => 'dragonball_tome_35_page_de_couverture.jpg'],
                ['titre' => 'One Piece',        'tome' => 'Tome 35', 'prix' => '7,20€', 'img' => 'one_piece_tome_35_page_de_couverture.jpg'],
                ['titre' => 'Inazuma Eleven',   'tome' => 'Tome 1',  'prix' => '7,20€', 'img' => 'inazuma_eleven_tome_1_page_de_couverture.jpg'],
                ['titre' => 'Vagabond',         'tome' => 'Tome 1',  'prix' => '7,20€', 'img' => 'vagabond_tome_1_page_de_couverture.jpg'],
            ];
            foreach ($tendances as $m): ?>
            <article class="card">
                <img src="asset/img/<?= htmlspecialchars($m['img']) ?>" alt="<?= htmlspecialchars($m['titre']) ?> <?= htmlspecialchars($m['tome']) ?>">
                <div class="card-body">
                    <div class="card-title"><?= htmlspecialchars($m['titre']) ?></div>
                    <div class="card-row">
                        <span class="card-price"><?= htmlspecialchars($m['prix']) ?></span>
                        <span class="card-tome"><?= htmlspecialchars($m['tome']) ?></span>
                    </div>
                    <div class="card-row" style="margin-top:10px">
                        <button class="btn-add" data-titre="<?= htmlspecialchars($m['titre']) ?>" data-prix="<?= htmlspecialchars($m['prix']) ?>">
                            + Ajouter
                        </button>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>

<?php
$pageTitle = "Accueil";
$pageCss   = "index.css";
$pageJs    = ["index.js", "search.js"];
$basePath  = '';
require_once 'includes/header.php';

// Tendances depuis la BDD (fallback tableau statique si pas de connexion)
$tendances = [];
try {
    require_once 'includes/db.php';
    $stmt = $pdo->query("
        SELECT p.id, p.titre, p.tome, p.prix, p.image, c.slug AS categorie
        FROM produits p
        JOIN categories c ON c.id = p.categorie_id
        ORDER BY RAND()
        LIMIT 5
    ");
    $tendances = $stmt->fetchAll();
} catch (Exception $e) {
    // Fallback statique si BDD non disponible
    $tendances = [
        ['id'=>1,  'titre'=>'Berserk',       'tome'=>1,  'prix'=>7.20, 'image'=>'asset/img/berserk_tome_1_page_de_couverture.jpg',        'categorie'=>'seinen'],
        ['id'=>2,  'titre'=>'Dragon Ball',   'tome'=>35, 'prix'=>7.20, 'image'=>'asset/img/dragonball_tome_35_page_de_couverture.jpg',     'categorie'=>'shonen'],
        ['id'=>3,  'titre'=>'One Piece',     'tome'=>35, 'prix'=>7.20, 'image'=>'asset/img/one_piece_tome_35_page_de_couverture.jpg',      'categorie'=>'shonen'],
        ['id'=>4,  'titre'=>'Inazuma Eleven','tome'=>1,  'prix'=>7.20, 'image'=>'asset/img/inazuma_eleven_tome_1_page_de_couverture.jpg',  'categorie'=>'kodomo'],
        ['id'=>5,  'titre'=>'Vagabond',      'tome'=>1,  'prix'=>7.20, 'image'=>'asset/img/vagabond_tome_1_page_de_couverture.jpg',        'categorie'=>'seinen'],
    ];
}
?>

<main>
    <!-- Hero -->
    <section class="hero noise">
        <div class="hero-content">
            <span class="hero-tag">Nouveauté Shonen</span>
            <h1>Commencer un shonen ?<br>Voici le premier tome de <em>Dragon Ball</em></h1>
            <div class="hero-buttons">
                <a href="views/produit.php?id=2" class="btn btn-primary">Voir l'article</a>
                <a href="views/catalogue.php" class="btn btn-outline">Voir la collection</a>
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
                <?php
                $cats = [
                    'kodomo' => 'Kodomo',
                    'shonen' => 'Shonen',
                    'shojo'  => 'Shojo',
                    'seinen' => 'Seinen',
                    'josei'  => 'Josei',
                ];
                foreach ($cats as $slug => $nom): ?>
                <a href="views/catalogue.php?categorie=<?= $slug ?>" id="<?= $slug ?>" class="slide">
                    <div class="slide-bg"></div>
                    <div class="slide-label">
                        <span>Catégorie</span><?= $nom ?>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="carousel-controls">
            <button class="carousel-btn" id="prev">&#8592;</button>
            <button class="carousel-btn" id="next">&#8594;</button>
        </div>
    </section>

    <!-- Tendances -->
    <section class="trending-section">
        <h2 class="section-heading">Tendances</h2>
        <div class="cards-grid">
            <?php foreach ($tendances as $m): ?>
            <a href="views/produit.php?id=<?= (int)$m['id'] ?>" class="card card-link">
                <img src="<?= htmlspecialchars($m['image']) ?>"
                     alt="<?= htmlspecialchars($m['titre']) ?> Tome <?= (int)$m['tome'] ?>">
                <div class="card-body">
                    <div class="card-title"><?= htmlspecialchars($m['titre']) ?></div>
                    <div class="card-row">
                        <span class="card-price"><?= number_format($m['prix'], 2, ',', '') ?> €</span>
                        <span class="card-tome">Tome <?= (int)$m['tome'] ?></span>
                    </div>
                    <div class="card-row" style="margin-top:10px">
                        <button class="btn-add"
                                data-id="<?= (int)$m['id'] ?>"
                                data-titre="<?= htmlspecialchars($m['titre']) ?>"
                                data-prix="<?= number_format($m['prix'], 2, ',', '') ?>"
                                data-img="<?= htmlspecialchars($m['image']) ?>"
                                onclick="event.preventDefault()">
                            + Ajouter
                        </button>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>

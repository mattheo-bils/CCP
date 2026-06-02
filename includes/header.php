<?php
$basePath = $basePath ?? '';
$title    = isset($pageTitle) ? "MangaMarket – $pageTitle" : "MangaMarket";
$base     = $basePath;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Noto+Sans+JP:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=arrow_forward,arrow_back,task_alt,search,shopping_cart,expand_more,delete" />
    <link rel="stylesheet" href="<?= $base ?>asset/css/global.css">
    <link rel="stylesheet" href="<?= $base ?>asset/css/header.css">
    <link rel="stylesheet" href="<?= $base ?>asset/css/footer.css">
    <?php if (isset($pageCss)): ?>
        <link rel="stylesheet" href="<?= $base ?>asset/css/<?= htmlspecialchars($pageCss) ?>">
    <?php endif; ?>
</head>
<body>
<header>
    <a href="<?= $base ?>index.php" class="logo-link">
        <div class="logo-text">
            <span class="logo-manga">MANGA</span><span class="logo-market">MARKET</span>
        </div>
    </a>
    <div class="search-bar" role="search">
        <span class="material-symbols-outlined search-icon">search</span>
        <input type="text" placeholder="Rechercher un manga…" autocomplete="off" id="search-input">
        <div role="listbox" aria-label="résultat de recherche" id="search-results"></div>
    </div>
    <nav class="nav-desktop">
        <a href="<?= $base ?>views/catalogue.php">Catalogue</a>
        <a href="<?= $base ?>views/panier.php" class="cart-link">
            🛒 Panier
            <span class="cart-badge" id="cart-count" style="display:none">0</span>
        </a>
        <a href="<?= $base ?>views/creecompte.php" class="btn-connect">Se connecter</a>
    </nav>
    <button class="burger" id="burger-btn" aria-label="Menu">
        <span></span><span></span><span></span>
    </button>
    <nav class="nav-mobile" id="mobile-nav">
        <a href="<?= $base ?>views/catalogue.php">Catalogue</a>
        <a href="<?= $base ?>views/panier.php">Panier</a>
        <a href="<?= $base ?>views/creecompte.php" class="btn-connect">Se connecter</a>
    </nav>
</header>
<div class="header-spacer"></div>

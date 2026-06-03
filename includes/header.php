<?php
$basePath = $basePath ?? '';
$title    = isset($pageTitle) ? "MangaMarket – $pageTitle" : "MangaMarket";
$base     = $basePath;

if (session_status() === PHP_SESSION_NONE) session_start();
$connected  = !empty($_SESSION['user_id']);
$userPrenom = $_SESSION['user_prenom'] ?? '';
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
        <?php if ($connected): ?>
            <div class="user-menu">
                <button class="btn-user" id="user-menu-btn">
                    👤 <?= htmlspecialchars($userPrenom) ?>
                    <span class="user-chevron">▾</span>
                </button>
                <div class="user-dropdown" id="user-dropdown">
                    <a href="<?= $base ?>views/profil.php">Mon profil</a>
                    <a href="<?= $base ?>views/commandes.php">Mes commandes</a>
                    <a href="<?= $base ?>views/deconnexion.php" class="dropdown-logout">Se déconnecter</a>
                </div>
            </div>
        <?php else: ?>
            <a href="<?= $base ?>views/creecompte.php" class="btn-connect">Se connecter</a>
        <?php endif; ?>
    </nav>
    <button class="burger" id="burger-btn" aria-label="Menu">
        <span></span><span></span><span></span>
    </button>
    <nav class="nav-mobile" id="mobile-nav">
        <a href="<?= $base ?>views/catalogue.php">Catalogue</a>
        <a href="<?= $base ?>views/panier.php">Panier</a>
        <?php if ($connected): ?>
            <a href="<?= $base ?>views/profil.php">Mon profil</a>
            <a href="<?= $base ?>views/commandes.php">Mes commandes</a>
            <a href="<?= $base ?>views/deconnexion.php" class="btn-connect">Se déconnecter</a>
        <?php else: ?>
            <a href="<?= $base ?>views/creecompte.php" class="btn-connect">Se connecter</a>
        <?php endif; ?>
    </nav>
</header>
<div class="header-spacer"></div>

<style>
.user-menu { position: relative; }
.btn-user {
    background: none;
    border: 2px solid var(--gold-light, #e2b04a);
    color: var(--gold-light, #e2b04a);
    border-radius: 30px;
    padding: 8px 18px;
    font-family: inherit;
    font-size: 0.95rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: background 0.2s, color 0.2s;
}
.btn-user:hover { background: var(--gold-light, #e2b04a); color: #111; }
.user-chevron { font-size: 0.75rem; transition: transform 0.2s; }
.user-menu.open .user-chevron { transform: rotate(180deg); }
.user-dropdown {
    display: none;
    position: absolute;
    right: 0;
    top: calc(100% + 8px);
    background: #1a2040;
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 12px;
    min-width: 180px;
    overflow: hidden;
    z-index: 999;
    box-shadow: 0 8px 24px rgba(0,0,0,0.4);
}
.user-menu.open .user-dropdown { display: block; }
.user-dropdown a {
    display: block;
    padding: 12px 18px;
    color: #fff;
    text-decoration: none;
    font-size: 0.9rem;
    transition: background 0.15s;
}
.user-dropdown a:hover { background: rgba(255,255,255,0.08); }
.dropdown-logout { border-top: 1px solid rgba(255,255,255,0.1); color: #e74c3c !important; }
</style>

<script>
const userMenuBtn = document.getElementById('user-menu-btn');
const userMenu    = userMenuBtn?.closest('.user-menu');
if (userMenuBtn && userMenu) {
    userMenuBtn.addEventListener('click', e => {
        e.stopPropagation();
        userMenu.classList.toggle('open');
    });
    document.addEventListener('click', () => userMenu.classList.remove('open'));
}
</script>

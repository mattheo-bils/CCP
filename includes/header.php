<?php
/**
 * includes/header.php — En-tête HTML commun à toutes les pages
 *
 * Génère le DOCTYPE, le <head> avec CSS, et le header de navigation.
 * Gère aussi l'affichage conditionnel selon l'état de connexion.
 *
 * Variables attendues (définies par la page appelante avant require) :
 *   $pageTitle — Titre affiché dans l'onglet du navigateur
 *   $pageCss   — Nom du fichier CSS spécifique à la page (ex: "index.css")
 *   $pageJs    — Tableau de fichiers JS à charger en fin de page
 *   $basePath  — Chemin relatif : '' depuis index.php, '../' depuis views/
 */

// Valeur par défaut si $basePath n'est pas défini par la page
$basePath = $basePath ?? '';
// Construction du titre : "MangaMarket – Titre" ou juste "MangaMarket"
$title    = isset($pageTitle) ? "MangaMarket – $pageTitle" : "MangaMarket";
$base     = $basePath; // Alias court utilisé dans le HTML

// Démarrage de la session PHP si elle n'est pas déjà active
// Nécessaire pour accéder à $_SESSION['user_id'] et $_SESSION['user_prenom']
if (session_status() === PHP_SESSION_NONE) session_start();

// Vérification de l'état de connexion de l'utilisateur
$connected  = !empty($_SESSION['user_id']);      // true si connecté, false sinon
$userPrenom = $_SESSION['user_prenom'] ?? '';     // Prénom pour le menu utilisateur
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <!-- Viewport responsive : s'adapte à la largeur de l'écran -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- htmlspecialchars() protège contre les injections XSS dans le titre -->
    <title><?= htmlspecialchars($title) ?></title>

    <!-- Préconnexion à Google Fonts pour accélérer le chargement -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- Police d'affichage (titres) et police de texte (contenu) -->
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Noto+Sans+JP:wght@300;400;700&display=swap" rel="stylesheet">

    <!-- Icônes Material Symbols (loupe, panier, flèches, etc.) -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=arrow_forward,arrow_back,task_alt,search,shopping_cart,expand_more,delete" />

    <!-- CSS globaux : variables, reset, utilitaires, cartes -->
    <link rel="stylesheet" href="<?= $base ?>asset/css/global.css">
    <!-- CSS du header : navigation, logo, barre de recherche -->
    <link rel="stylesheet" href="<?= $base ?>asset/css/header.css">
    <!-- CSS du footer : colonnes, copyright -->
    <link rel="stylesheet" href="<?= $base ?>asset/css/footer.css">

    <!-- CSS spécifique à la page (si défini par $pageCss) -->
    <?php if (isset($pageCss)): ?>
        <link rel="stylesheet" href="<?= $base ?>asset/css/<?= htmlspecialchars($pageCss) ?>">
    <?php endif; ?>
</head>
<body>

<!-- ── Barre de navigation principale ───────────────────────── -->
<header>

    <!-- Logo cliquable qui ramène à l'accueil -->
    <a href="<?= $base ?>index.php" class="logo-link">
        <div class="logo-text">
            <!-- "MANGA" en blanc, "MARKET" en rouge (via CSS) -->
            <span class="logo-manga">MANGA</span><span class="logo-market">MARKET</span>
        </div>
    </a>

    <!-- Barre de recherche avec autocomplétion (gérée par search.js) -->
    <div class="search-bar" role="search">
        <!-- Icône loupe Material Symbols -->
        <span class="material-symbols-outlined search-icon">search</span>
        <!-- Champ de saisie : autocomplete="off" désactive les suggestions du navigateur -->
        <input type="text" placeholder="Rechercher un manga…" autocomplete="off" id="search-input">
        <!-- Conteneur des résultats injecté dynamiquement par search.js -->
        <div role="listbox" aria-label="résultat de recherche" id="search-results"></div>
    </div>

    <!-- Navigation desktop (masquée sur mobile via CSS) -->
    <nav class="nav-desktop">
        <!-- Lien vers le catalogue -->
        <a href="<?= $base ?>views/catalogue.php">Catalogue</a>

        <!-- Lien panier avec badge de quantité -->
        <a href="<?= $base ?>views/panier.php" class="cart-link">
            🛒 Panier
            <!-- Badge rouge mis à jour par header.js, masqué si panier vide -->
            <span class="cart-badge" id="cart-count" style="display:none">0</span>
        </a>

        <?php if ($connected): ?>
            <!-- Menu déroulant pour l'utilisateur connecté -->
            <div class="user-menu">
                <!-- Bouton qui affiche le prénom et ouvre le dropdown -->
                <button class="btn-user" id="user-menu-btn">
                    👤 <?= htmlspecialchars($userPrenom) ?> <!-- Sécurisé contre XSS -->
                    <span class="user-chevron">▾</span> <!-- Flèche indiquant l'état ouvert/fermé -->
                </button>
                <!-- Dropdown avec les options du compte -->
                <div class="user-dropdown" id="user-dropdown">
                    <a href="<?= $base ?>views/profil.php">Mon profil</a>
                    <a href="<?= $base ?>views/commandes.php">Mes commandes</a>
                    <!-- Lien de déconnexion en rouge (style via CSS .dropdown-logout) -->
                    <a href="<?= $base ?>views/deconnexion.php" class="dropdown-logout">Se déconnecter</a>
                </div>
            </div>
        <?php else: ?>
            <!-- Bouton de connexion si l'utilisateur n'est pas connecté -->
            <a href="<?= $base ?>views/creecompte.php" class="btn-connect">Se connecter</a>
        <?php endif; ?>
    </nav>

    <!-- Bouton burger pour afficher/masquer la navigation mobile -->
    <button class="burger" id="burger-btn" aria-label="Menu">
        <!-- 3 barres qui s'animent en croix quand le menu est ouvert (via CSS) -->
        <span></span><span></span><span></span>
    </button>

    <!-- Navigation mobile (affichée/masquée par header.js via la classe .open) -->
    <nav class="nav-mobile" id="mobile-nav">
        <a href="<?= $base ?>views/catalogue.php">Catalogue</a>
        <a href="<?= $base ?>views/panier.php">Panier</a>
        <?php if ($connected): ?>
            <!-- Liens supplémentaires si connecté -->
            <a href="<?= $base ?>views/profil.php">Mon profil</a>
            <a href="<?= $base ?>views/commandes.php">Mes commandes</a>
            <a href="<?= $base ?>views/deconnexion.php" class="btn-connect">Se déconnecter</a>
        <?php else: ?>
            <a href="<?= $base ?>views/creecompte.php" class="btn-connect">Se connecter</a>
        <?php endif; ?>
    </nav>
</header>

<!-- Espace vide qui compense la hauteur du header fixe (position:fixed)
     sans lui, le contenu de la page serait caché derrière le header -->
<div class="header-spacer"></div>

<!-- ── Styles du menu déroulant utilisateur ─────────────────── -->
<style>
/* Conteneur relatif pour positionner le dropdown en absolu */
.user-menu { position: relative; }

/* Bouton avec le prénom : style "outlined" doré */
.btn-user {
    background: none;
    border: 2px solid var(--gold-light, #e2b04a); /* Bordure dorée */
    color: var(--gold-light, #e2b04a);            /* Texte doré */
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
/* Au survol : fond doré, texte sombre (inversion) */
.btn-user:hover { background: var(--gold-light, #e2b04a); color: #111; }

/* Flèche chevron qui tourne quand le menu est ouvert */
.user-chevron { font-size: 0.75rem; transition: transform 0.2s; }
.user-menu.open .user-chevron { transform: rotate(180deg); } /* Rotation 180° */

/* Dropdown : masqué par défaut, affiché quand .open est présent */
.user-dropdown {
    display: none;
    position: absolute;
    right: 0;                                   /* Aligné à droite du bouton */
    top: calc(100% + 8px);                      /* 8px sous le bouton */
    background: #1a2040;                        /* Fond bleu foncé */
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 12px;
    min-width: 180px;
    overflow: hidden;                           /* Arrondit les items aux extrémités */
    z-index: 999;                               /* Au-dessus du reste */
    box-shadow: 0 8px 24px rgba(0,0,0,0.4);
}
/* Classe .open ajoutée par JS pour afficher le dropdown */
.user-menu.open .user-dropdown { display: block; }

/* Liens du dropdown */
.user-dropdown a {
    display: block;
    padding: 12px 18px;
    color: #fff;
    text-decoration: none;
    font-size: 0.9rem;
    transition: background 0.15s;
}
.user-dropdown a:hover { background: rgba(255,255,255,0.08); }

/* Lien déconnexion en rouge avec séparateur */
.dropdown-logout {
    border-top: 1px solid rgba(255,255,255,0.1);
    color: #e74c3c !important; /* Rouge forcé */
}
</style>

<!-- ── Script d'ouverture/fermeture du dropdown utilisateur ──── -->
<script>
// Récupération du bouton et de son conteneur parent
const userMenuBtn = document.getElementById('user-menu-btn');
const userMenu    = userMenuBtn?.closest('.user-menu'); // Remonte au parent .user-menu

if (userMenuBtn && userMenu) {
    userMenuBtn.addEventListener('click', e => {
        e.stopPropagation(); // Empêche la fermeture immédiate par le listener document ci-dessous
        userMenu.classList.toggle('open'); // Ouvre ou ferme le dropdown
    });

    // Fermeture du dropdown en cliquant n'importe où ailleurs sur la page
    document.addEventListener('click', () => userMenu.classList.remove('open'));
}
</script>

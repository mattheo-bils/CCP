<?php
/**
 * views/legals.php — Page des mentions légales
 *
 * Obligatoire légalement pour tout site e-commerce en France (loi LCEN).
 * Le contenu est structuré dans un tableau PHP $articles pour faciliter
 * la maintenance sans toucher au HTML.
 *
 * Structure de chaque article :
 *   - titre   : numéro et intitulé de la section (ex: "1. Éditeur du site")
 *   - sous    : sous-titre optionnel (ex: "Le site est édité par :")
 *   - contenu : tableau de lignes de texte à afficher
 */

$pageTitle = "Mentions légales";
$pageCss   = "legals.css"; // CSS spécifique à la mise en page des mentions légales
$basePath  = '../';        // On est dans views/, la racine est un niveau au-dessus

// Chargement du header avant de définir $articles
// (le header démarre la session et affiche la navigation)
require_once '../includes/header.php';

// ── Contenu des mentions légales sous forme de tableau ────────
// Chaque entrée est un article avec titre, sous-titre optionnel et contenu
$articles = [

    // Article 1 : informations sur l'éditeur du site
    [
        'titre' => '1. Éditeur du site',
        'sous'  => 'Le site MangaMarket est édité par :',
        'contenu' => [
            'Raison sociale : MangaMarket',
            'Forme juridique : À compléter',
            'Capital social : À compléter',
            'Adresse : À compléter',
            'SIRET : À compléter',                        // Numéro d'identification de l'entreprise
            'TVA intracommunautaire : À compléter',
            'Email : contact@mangamarket.fr',
            'Téléphone : À compléter',
        ]
    ],

    // Article 2 : responsable éditorial du site
    [
        'titre' => '2. Directeur de la publication',
        'contenu' => [
            'Nom : Matthéo Bils',
            'Email : mattheo.bils@gmail.com',
        ]
    ],

    // Article 3 : informations sur l'hébergeur
    [
        'titre' => '3. Hébergeur du site',
        'sous'  => 'Le site MangaMarket est hébergé par :',
        'contenu' => [
            'Hébergeur : À compléter',
            'Adresse : À compléter',
            'Téléphone : À compléter',
            'Site web : À compléter',
        ]
    ],

    // Article 4 : droits sur le contenu du site
    [
        'titre' => '4. Propriété intellectuelle',
        'contenu' => [
            "L'ensemble du contenu du site MangaMarket est protégé par le droit d'auteur et reste la propriété exclusive de MangaMarket, sauf mention contraire. Toute reproduction sans autorisation est strictement interdite.",
            // Note : les visuels des mangas appartiennent à leurs éditeurs respectifs
            "Les noms, logos et visuels des mangas sont la propriété de leurs auteurs et éditeurs respectifs (Shueisha, Kodansha, Viz Media, etc.).",
        ]
    ],

    // Article 5 : obligations RGPD (Règlement Général sur la Protection des Données)
    [
        'titre' => '5. Protection des données personnelles (RGPD)',
        'contenu' => [
            "Conformément au RGPD, vous disposez des droits d'accès, de rectification, d'effacement, d'opposition et à la portabilité de vos données.",
            "Pour exercer ces droits : contact@mangamarket.fr",
            // Données collectées : email, nom, adresse (pour les commandes)
            "Les données collectées sont utilisées uniquement pour la gestion des commandes et la relation client.",
        ]
    ],

    // Article 6 : utilisation des cookies
    [
        'titre' => '6. Cookies',
        'contenu' => [
            // Le site utilise un cookie de session PHP pour maintenir la connexion
            "Le site utilise des cookies pour améliorer l'expérience utilisateur et réaliser des statistiques de visites. Vous pouvez paramétrer vos préférences depuis votre navigateur.",
        ]
    ],

    // Article 7 : responsabilité sur les liens externes
    [
        'titre' => '7. Liens hypertextes',
        'contenu' => [
            "MangaMarket peut contenir des liens vers des sites tiers fournis à titre informatif. MangaMarket ne saurait être tenu responsable du contenu de ces sites.",
        ]
    ],

    // Article 8 : limitation de responsabilité sur le contenu du site
    [
        'titre' => '8. Limitation de responsabilité',
        'contenu' => [
            "MangaMarket s'efforce de fournir des informations exactes et à jour, sans pouvoir en garantir l'exhaustivité. L'utilisation du site se fait sous l'entière responsabilité de l'utilisateur.",
        ]
    ],

    // Article 9 : juridiction compétente en cas de litige
    [
        'titre' => '9. Droit applicable',
        'contenu' => [
            "Les présentes mentions légales sont soumises au droit français. En cas de litige, les tribunaux français seront seuls compétents.",
        ]
    ],
];
?>

<main>
    <div class="legals-page">
        <h1>Mentions Légales</h1>

        <!-- Introduction légale obligatoire (référence à la loi LCEN) -->
        <p class="legals-intro">
            Conformément aux dispositions de la loi n° 2004-575 du 21 juin 2004 pour la confiance
            en l'économie numérique (LCEN), il est précisé aux utilisateurs du site MangaMarket
            les informations suivantes.
        </p>

        <!-- Boucle sur chaque article des mentions légales -->
        <?php foreach ($articles as $a): ?>
        <article class="legals-article">
            <!-- Titre de la section (ex: "1. Éditeur du site") -->
            <!-- htmlspecialchars() protège contre les injections XSS -->
            <h2><?= htmlspecialchars($a['titre']) ?></h2>

            <!-- Sous-titre optionnel (présent seulement pour certains articles) -->
            <?php if (!empty($a['sous'])): ?>
                <h3><?= htmlspecialchars($a['sous']) ?></h3>
            <?php endif; ?>

            <!-- Lignes de contenu : chaque élément du tableau devient un paragraphe -->
            <?php foreach ($a['contenu'] as $ligne): ?>
                <p><?= htmlspecialchars($ligne) ?></p>
            <?php endforeach; ?>
        </article>
        <?php endforeach; ?>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
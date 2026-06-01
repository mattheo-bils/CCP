<?php
$pageTitle = "Mentions légales";
$pageCss   = "legals.css";
$basePath = '../';
require_once '../includes/header.php';

$articles = [
    [
        'titre' => '1. Éditeur du site',
        'sous'  => 'Le site MangaMarket est édité par :',
        'contenu' => [
            'Raison sociale : MangaMarket',
            'Forme juridique : À compléter',
            'Capital social : À compléter',
            'Adresse du siège social : À compléter',
            'Numéro SIRET : À compléter',
            'Numéro TVA intracommunautaire : À compléter',
            'Email : contact@mangamarket.fr',
            'Téléphone : À compléter',
        ],
    ],
    [
        'titre'   => '2. Directeur de la publication',
        'contenu' => ['Nom : Matthéo Bils', 'Email : mattheo.bils@gmail.com'],
    ],
    [
        'titre' => '3. Hébergeur du site',
        'sous'  => 'Le site MangaMarket est hébergé par :',
        'contenu' => [
            'Nom de l\'hébergeur : À compléter',
            'Adresse : À compléter',
            'Téléphone : À compléter',
            'Site web : À compléter',
        ],
    ],
    [
        'titre'   => '4. Propriété intellectuelle',
        'contenu' => [
            'L\'ensemble du contenu du site MangaMarket (textes, images, logos, illustrations, vidéos, etc.) est protégé par le droit d\'auteur et reste la propriété exclusive de MangaMarket, sauf mention contraire. Toute reproduction, distribution, modification ou utilisation de ces contenus sans autorisation préalable est strictement interdite.',
            'Les noms, logos et visuels des mangas présentés sur le site sont la propriété de leurs auteurs et éditeurs respectifs (ex : Shueisha, Kodansha, Viz Media, etc.).',
        ],
    ],
    [
        'titre'   => '5. Protection des données personnelles (RGPD)',
        'contenu' => [
            'Conformément au RGPD et à la loi Informatique et Libertés, vous disposez des droits suivants : droit d\'accès, de rectification, d\'effacement, d\'opposition, à la portabilité.',
            'Pour exercer ces droits, contactez-nous à : contact@mangamarket.fr',
            'Les données collectées sont utilisées uniquement pour la gestion des commandes, la relation client et l\'amélioration du service.',
        ],
    ],
    [
        'titre'   => '6. Cookies',
        'contenu' => ['Le site MangaMarket utilise des cookies afin d\'améliorer l\'expérience utilisateur et de réaliser des statistiques de visites. Vous pouvez paramétrer vos préférences depuis votre navigateur ou via notre bandeau de consentement.'],
    ],
    [
        'titre'   => '7. Liens hypertextes',
        'contenu' => ['MangaMarket peut contenir des liens vers des sites tiers. Ces liens sont fournis à titre informatif uniquement. MangaMarket ne saurait être tenu responsable du contenu de ces sites externes.'],
    ],
    [
        'titre'   => '8. Limitation de responsabilité',
        'contenu' => ['MangaMarket s\'efforce de fournir des informations exactes et à jour. Toutefois, il ne peut garantir l\'exactitude ou l\'actualité des informations diffusées. L\'utilisation du site se fait sous l\'entière responsabilité de l\'utilisateur.'],
    ],
    [
        'titre'   => '9. Droit applicable et juridiction compétente',
        'contenu' => ['Les présentes mentions légales sont soumises au droit français. En cas de litige, les tribunaux français seront seuls compétents.'],
    ],
];
?>

<main>
    <div class="legals-page">
        <h1>Mentions Légales</h1>
        <p class="legals-intro">
            Conformément aux dispositions de la loi n° 2004-575 du 21 juin 2004 pour la confiance
            en l'économie numérique (LCEN), il est précisé aux utilisateurs du site MangaMarket
            les informations suivantes.
        </p>

        <?php foreach ($articles as $article): ?>
        <article class="legals-article">
            <h2><?= htmlspecialchars($article['titre']) ?></h2>
            <?php if (!empty($article['sous'])): ?>
                <h3><?= htmlspecialchars($article['sous']) ?></h3>
            <?php endif; ?>
            <?php foreach ($article['contenu'] as $ligne): ?>
                <p><?= htmlspecialchars($ligne) ?></p>
            <?php endforeach; ?>
        </article>
        <?php endforeach; ?>
    </div>
</main>

<?php $basePath = '../';
require_once '../includes/footer.php'; ?>

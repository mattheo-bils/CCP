<?php
$pageTitle = "Mentions légales";
$pageCss   = "legals.css";
$basePath  = '../';
require_once '../includes/header.php';

$articles = [
    ['titre' => '1. Éditeur du site', 'sous' => 'Le site MangaMarket est édité par :',
     'contenu' => ['Raison sociale : MangaMarket','Forme juridique : À compléter','Capital social : À compléter','Adresse : À compléter','SIRET : À compléter','TVA intracommunautaire : À compléter','Email : contact@mangamarket.fr','Téléphone : À compléter']],
    ['titre' => '2. Directeur de la publication',
     'contenu' => ['Nom : Matthéo Bils','Email : mattheo.bils@gmail.com']],
    ['titre' => '3. Hébergeur du site', 'sous' => 'Le site MangaMarket est hébergé par :',
     'contenu' => ['Hébergeur : À compléter','Adresse : À compléter','Téléphone : À compléter','Site web : À compléter']],
    ['titre' => '4. Propriété intellectuelle',
     'contenu' => ["L'ensemble du contenu du site MangaMarket est protégé par le droit d'auteur et reste la propriété exclusive de MangaMarket, sauf mention contraire. Toute reproduction sans autorisation est strictement interdite.","Les noms, logos et visuels des mangas sont la propriété de leurs auteurs et éditeurs respectifs (Shueisha, Kodansha, Viz Media, etc.)."]],
    ['titre' => '5. Protection des données personnelles (RGPD)',
     'contenu' => ["Conformément au RGPD, vous disposez des droits d'accès, de rectification, d'effacement, d'opposition et à la portabilité de vos données.","Pour exercer ces droits : contact@mangamarket.fr","Les données collectées sont utilisées uniquement pour la gestion des commandes et la relation client."]],
    ['titre' => '6. Cookies',
     'contenu' => ["Le site utilise des cookies pour améliorer l'expérience utilisateur et réaliser des statistiques de visites. Vous pouvez paramétrer vos préférences depuis votre navigateur."]],
    ['titre' => '7. Liens hypertextes',
     'contenu' => ["MangaMarket peut contenir des liens vers des sites tiers fournis à titre informatif. MangaMarket ne saurait être tenu responsable du contenu de ces sites."]],
    ['titre' => '8. Limitation de responsabilité',
     'contenu' => ["MangaMarket s'efforce de fournir des informations exactes et à jour, sans pouvoir en garantir l'exhaustivité. L'utilisation du site se fait sous l'entière responsabilité de l'utilisateur."]],
    ['titre' => '9. Droit applicable',
     'contenu' => ["Les présentes mentions légales sont soumises au droit français. En cas de litige, les tribunaux français seront seuls compétents."]],
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
        <?php foreach ($articles as $a): ?>
        <article class="legals-article">
            <h2><?= htmlspecialchars($a['titre']) ?></h2>
            <?php if (!empty($a['sous'])): ?>
                <h3><?= htmlspecialchars($a['sous']) ?></h3>
            <?php endif; ?>
            <?php foreach ($a['contenu'] as $ligne): ?>
                <p><?= htmlspecialchars($ligne) ?></p>
            <?php endforeach; ?>
        </article>
        <?php endforeach; ?>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>

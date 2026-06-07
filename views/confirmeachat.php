<?php
/**
 * views/confirmeachat.php — Page de confirmation de commande
 *
 * Affichée après une commande réussie (redirection depuis achat.php).
 * Affiche un message de remerciement et un lien vers l'accueil.
 * Le JS (confirmeachat.js) vide le localStorage côté client.
 */

$pageTitle = "Commande confirmée";
$pageCss   = "confirmeachat.css"; // CSS spécifique (icône animée, centrage)
$pageJs    = ["confirmeachat.js"]; // JS qui vide le localStorage
$basePath  = '../';

// Chargement du header commun
require_once '../includes/header.php';
?>

<!-- Page de confirmation centrée -->
<main class="confirm-page">
    <!-- Icône de validation Material Symbols (coche dans un cercle) -->
    <span class="material-symbols-outlined confirm-icon">task_alt</span>

    <!-- Message principal -->
    <h1>Merci pour votre commande&nbsp;!</h1>

    <!-- Sous-titre informatif -->
    <p>Vous recevrez un email de confirmation dans quelques instants.</p>

    <!-- Bouton de retour à l'accueil -->
    <a href="../index.php" class="btn btn-primary">Revenir à l'accueil</a>
</main>

<?php require_once '../includes/footer.php'; ?>

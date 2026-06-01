<?php
$pageTitle = "Commande confirmée";
$pageCss   = "confirmeachat.css";
$basePath = '../';
require_once '../includes/header.php';
?>

<main class="confirm-page">
    <span class="material-symbols-outlined confirm-icon">task_alt</span>
    <h1>Merci pour votre commande !</h1>
    <p>Vous recevrez un email de confirmation dans quelques instants.</p>
    <a href="index.php" class="btn btn-primary">Revenir à l'accueil</a>
</main>

<?php $basePath = '../';
require_once '../includes/footer.php'; ?>

<?php
$pageTitle = "Finaliser la commande";
$pageCss   = "pages.css";

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prenom = trim($_POST['prenom'] ?? '');
    $nom    = trim($_POST['nom']    ?? '');
    $adresse= trim($_POST['adresse']?? '');
    $ville  = trim($_POST['ville']  ?? '');
    $cp     = trim($_POST['cp']     ?? '');
    $carte  = trim($_POST['carte']  ?? '');

    if (empty($prenom))  $errors[] = "Prénom requis.";
    if (empty($nom))     $errors[] = "Nom requis.";
    if (empty($adresse)) $errors[] = "Adresse requise.";
    if (empty($ville))   $errors[] = "Ville requise.";
    if (empty($cp))      $errors[] = "Code postal requis.";

    if (empty($errors)) {
        // TODO: traitement paiement / création commande
        header('Location: confirmeachat.php');
        exit;
    }
}

require_once 'includes/header.php';
?>

<main>
    <div class="achat-page">
        <h1>Finaliser la commande</h1>

        <?php if (!empty($errors)): ?>
            <div style="background:rgba(192,57,43,0.15);border:1px solid var(--red);border-radius:12px;padding:16px 20px;margin-bottom:24px">
                <?php foreach ($errors as $e): ?>
                    <p style="color:var(--red-bright);margin:4px 0;font-size:0.9rem">⚠ <?= htmlspecialchars($e) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="achat.php">
            <div class="achat-section">
                <h3>Adresse de livraison</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom"
                               value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>"
                               placeholder="Jean" required>
                    </div>
                    <div class="form-group">
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom"
                               value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"
                               placeholder="Dupont" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="adresse">Adresse</label>
                    <input type="text" id="adresse" name="adresse"
                           value="<?= htmlspecialchars($_POST['adresse'] ?? '') ?>"
                           placeholder="12 rue des Lilas" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="cp">Code postal</label>
                        <input type="text" id="cp" name="cp"
                               value="<?= htmlspecialchars($_POST['cp'] ?? '') ?>"
                               placeholder="75000" required>
                    </div>
                    <div class="form-group">
                        <label for="ville">Ville</label>
                        <input type="text" id="ville" name="ville"
                               value="<?= htmlspecialchars($_POST['ville'] ?? '') ?>"
                               placeholder="Paris" required>
                    </div>
                </div>
            </div>

            <div class="achat-section">
                <h3>Paiement</h3>
                <div class="form-group">
                    <label for="carte">Numéro de carte</label>
                    <input type="text" id="carte" name="carte"
                           placeholder="4242 4242 4242 4242"
                           maxlength="19" autocomplete="cc-number">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="expiry">Date d'expiration</label>
                        <input type="text" id="expiry" name="expiry" placeholder="MM/AA" maxlength="5">
                    </div>
                    <div class="form-group">
                        <label for="cvv">CVV</label>
                        <input type="text" id="cvv" name="cvv" placeholder="123" maxlength="4">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;padding:16px;font-size:1rem;margin-top:8px">
                Confirmer la commande
            </button>
        </form>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>

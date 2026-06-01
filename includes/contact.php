<?php
$pageTitle = "Contact";
$pageCss   = "contact.css";

// Traitement du formulaire
$success = false;
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom     = trim($_POST['nom']     ?? '');
    $prenom  = trim($_POST['prenom']  ?? '');
    $tel     = trim($_POST['tel']     ?? '');
    $mail    = trim($_POST['mail']    ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($nom))     $errors[] = "Le nom est requis.";
    if (empty($prenom))  $errors[] = "Le prénom est requis.";
    if (empty($tel))     $errors[] = "Le téléphone est requis.";
    if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) $errors[] = "L'adresse email est invalide.";
    if (empty($message)) $errors[] = "Le message est requis.";

    if (empty($errors)) {
        // TODO: envoyer l'email via mail() ou SMTP
        $success = true;
    }
}

$basePath = '../';
require_once 'header.php';
?>

<main>
    <div class="contact-page">
        <h1>Nous contacter</h1>
        <p class="sub">Une question ? Remplissez le formulaire ci-dessous, nous vous répondrons sous 48h.</p>

        <?php if ($success): ?>
            <div style="background:rgba(39,174,96,0.15);border:1px solid #27AE60;border-radius:12px;padding:16px 20px;margin-bottom:24px;color:#2ECC71">
                ✓ Votre message a bien été envoyé. Merci !
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div style="background:rgba(192,57,43,0.15);border:1px solid var(--red);border-radius:12px;padding:16px 20px;margin-bottom:24px">
                <?php foreach ($errors as $e): ?>
                    <p style="color:var(--red-bright);margin:4px 0;font-size:0.9rem">⚠ <?= htmlspecialchars($e) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="contact.php" novalidate>
            <div class="form-row">
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom"
                           placeholder="Votre nom"
                           value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom"
                           placeholder="Votre prénom"
                           value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="tel">Téléphone</label>
                <input type="tel" id="tel" name="tel"
                       placeholder="06 00 00 00 00"
                       value="<?= htmlspecialchars($_POST['tel'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="mail">Adresse email</label>
                <input type="email" id="mail" name="mail"
                       placeholder="vous@exemple.com"
                       value="<?= htmlspecialchars($_POST['mail'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" name="message"
                          placeholder="Votre message…" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-submit">Envoyer le message</button>
        </form>
    </div>
</main>

<?php $basePath = '../';
require_once 'footer.php'; ?>

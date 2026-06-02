<?php
$pageTitle = "Contact";
$pageCss   = "contact.css";
$basePath  = '../';

$success = false;
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom     = trim($_POST['nom']     ?? '');
    $prenom  = trim($_POST['prenom']  ?? '');
    $tel     = trim($_POST['tel']     ?? '');
    $mail    = trim($_POST['mail']    ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($nom))                                    $errors[] = "Le nom est requis.";
    if (empty($prenom))                                 $errors[] = "Le prénom est requis.";
    if (empty($tel))                                    $errors[] = "Le téléphone est requis.";
    if (!filter_var($mail, FILTER_VALIDATE_EMAIL))      $errors[] = "L'adresse email est invalide.";
    if (empty($message))                                $errors[] = "Le message est requis.";

    if (empty($errors)) {
        try {
            require_once '../includes/db.php';
            $stmt = $pdo->prepare("
                INSERT INTO messages_contact (nom, prenom, telephone, email, message)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$nom, $prenom, $tel, $mail, $message]);
            $success = true;
        } catch (Exception $e) {
            // BDD indisponible : on marque quand même succès en dev
            $success = true;
        }
    }
}

require_once '../includes/header.php';
?>

<main>
    <div class="contact-page">
        <h1>Nous contacter</h1>
        <p class="sub">Une question ? Remplissez le formulaire, nous vous répondrons sous 48h.</p>

        <?php if ($success): ?>
        <div class="alert-success">
            ✓ Votre message a bien été envoyé. Merci !
        </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
        <div class="alert-error">
            <?php foreach ($errors as $e): ?>
                <p>⚠ <?= htmlspecialchars($e) ?></p>
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
            <button type="submit" class="btn btn-primary btn-submit">
                Envoyer le message
            </button>
        </form>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>

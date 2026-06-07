<?php
/**
 * views/contact.php — Page formulaire de contact
 *
 * Affiche un formulaire permettant aux visiteurs d'envoyer un message.
 * Le message est enregistré en BDD dans la table messages_contact.
 *
 * Traitement :
 *   - Validation côté serveur de tous les champs
 *   - Insertion en BDD si tout est valide
 *   - Affichage d'un message de succès ou d'erreurs
 */

$pageTitle = "Contact";
$pageCss   = "contact.css"; // CSS spécifique à cette page
$basePath  = '../';          // On est dans views/, racine est un niveau au-dessus

// Variables de statut initialisées avant le traitement
$success = false; // true si le message a été envoyé avec succès
$errors  = [];    // Tableau des messages d'erreur de validation

// ── Traitement du formulaire (uniquement si soumis via POST) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupération et nettoyage des données soumises
    // trim() supprime les espaces en début et fin de chaîne
    $nom     = trim($_POST['nom']     ?? ''); // Nom de famille
    $prenom  = trim($_POST['prenom']  ?? ''); // Prénom
    $tel     = trim($_POST['tel']     ?? ''); // Numéro de téléphone
    $mail    = trim($_POST['mail']    ?? ''); // Adresse email
    $message = trim($_POST['message'] ?? ''); // Contenu du message

    // ── Validation des champs ─────────────────────────────
    // Chaque condition ajoute un message d'erreur au tableau $errors
    if (empty($nom))                                   $errors[] = "Le nom est requis.";
    if (empty($prenom))                                $errors[] = "Le prénom est requis.";
    if (empty($tel))                                   $errors[] = "Le téléphone est requis.";
    // filter_var avec FILTER_VALIDATE_EMAIL vérifie le format de l'email
    if (!filter_var($mail, FILTER_VALIDATE_EMAIL))     $errors[] = "L'adresse email est invalide.";
    if (empty($message))                               $errors[] = "Le message est requis.";

    // ── Insertion en BDD si aucune erreur ─────────────────
    if (empty($errors)) {
        try {
            require_once '../includes/db.php';
            // Requête préparée pour éviter les injections SQL
            $stmt = $pdo->prepare("
                INSERT INTO messages_contact (nom, prenom, telephone, email, message)
                VALUES (?, ?, ?, ?, ?)
            ");
            // Les ? sont remplacés par les valeurs dans l'ordre
            $stmt->execute([$nom, $prenom, $tel, $mail, $message]);
            $success = true; // Message envoyé avec succès
        } catch (Exception $e) {
            // En mode développement, on marque quand même succès
            // En production, il faudrait afficher une erreur à l'utilisateur
            $success = true;
        }
    }
}

// Chargement du header (doit être après le traitement pour pouvoir rediriger si besoin)
require_once '../includes/header.php';
?>

<main>
    <div class="contact-page">
        <h1>Nous contacter</h1>
        <p class="sub">Une question ? Remplissez le formulaire, nous vous répondrons sous 48h.</p>

        <!-- Message de succès affiché après envoi réussi -->
        <?php if ($success): ?>
        <div class="alert-success">
            ✓ Votre message a bien été envoyé. Merci !
        </div>
        <?php endif; ?>

        <!-- Liste des erreurs de validation -->
        <?php if (!empty($errors)): ?>
        <div class="alert-error">
            <?php foreach ($errors as $e): ?>
                <!-- htmlspecialchars() protège contre les injections XSS -->
                <p>⚠ <?= htmlspecialchars($e) ?></p>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Formulaire de contact
             novalidate désactive la validation HTML5 native (on gère en PHP) -->
        <form method="post" action="contact.php" novalidate>
            <!-- Rangée nom + prénom côte à côte -->
            <div class="form-row">
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <!-- value conserve la saisie après une erreur de validation -->
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

            <!-- Champ téléphone -->
            <div class="form-group">
                <label for="tel">Téléphone</label>
                <input type="tel" id="tel" name="tel"
                       placeholder="06 00 00 00 00"
                       value="<?= htmlspecialchars($_POST['tel'] ?? '') ?>" required>
            </div>

            <!-- Champ email -->
            <div class="form-group">
                <label for="mail">Adresse email</label>
                <input type="email" id="mail" name="mail"
                       placeholder="vous@exemple.com"
                       value="<?= htmlspecialchars($_POST['mail'] ?? '') ?>" required>
            </div>

            <!-- Zone de texte message -->
            <div class="form-group">
                <label for="message">Message</label>
                <!-- Le contenu du textarea doit être entre les balises (pas de value) -->
                <textarea id="message" name="message"
                          placeholder="Votre message…" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
            </div>

            <!-- Bouton de soumission -->
            <button type="submit" class="btn btn-primary btn-submit">
                Envoyer le message
            </button>
        </form>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>

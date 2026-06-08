<?php
/**
 * views/profil.php — Page de profil utilisateur
 *
 * Accessible uniquement aux utilisateurs connectés.
 * Contient trois sections :
 *   1. Informations personnelles (nom, prénom, email) — modifiables
 *   2. Sécurité — changement de mot de passe
 *   3. Dernières commandes — tableau des 5 dernières commandes
 *
 * Deux formulaires POST distincts identifiés par le champ hidden "action" :
 *   - action=update   : mise à jour du profil
 *   - action=password : changement de mot de passe
 */

$pageTitle = "Mon profil";
$pageCss   = "pages.css"; // CSS partagé avec d'autres pages
$basePath  = '../';

// Démarrage de session si pas encore active
if (session_status() === PHP_SESSION_NONE) session_start();

// Protection de la page : redirection si non connecté
if (empty($_SESSION['user_id'])) {
    header('Location: creecompte.php');
    exit;
}

// Variables d'état pour les formulaires
$success = false; // true si mise à jour du profil réussie
$errors  = [];    // Erreurs du formulaire de modification du profil
$userId  = (int)$_SESSION['user_id']; // ID de l'utilisateur connecté

// Connexion à la BDD
require_once '../includes/db.php';

// ── Chargement des données de l'utilisateur ───────────────────
// Requête préparée sauvegardée dans $stmt pour pouvoir la réexécuter
// après une mise à jour (pour rafraîchir les données affichées)
$stmt = $pdo->prepare("SELECT prenom, nom, email, created_at FROM utilisateurs WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(); // Tableau associatif avec les données de l'utilisateur

// ── Traitement : mise à jour du profil ───────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    // Nettoyage des données soumises
    $prenom = trim($_POST['prenom'] ?? '');
    $nom    = trim($_POST['nom']    ?? '');
    $email  = trim($_POST['email']  ?? '');

    // Validation des champs obligatoires
    if (empty($prenom)) $errors[] = "Prénom requis.";
    if (empty($nom))    $errors[] = "Nom requis.";
    // Vérification du format de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide.";

    if (empty($errors)) {
        // Vérification doublon email : l'email ne doit pas appartenir à un AUTRE utilisateur
        // "AND id != ?" exclut l'utilisateur actuel de la vérification
        $check = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ? AND id != ?");
        $check->execute([$email, $userId]);
        if ($check->fetch()) {
            $errors[] = "Cet email est déjà utilisé.";
        } else {
            // Mise à jour en BDD
            $pdo->prepare("UPDATE utilisateurs SET prenom = ?, nom = ?, email = ? WHERE id = ?")
                ->execute([$prenom, $nom, $email, $userId]);

            // Mise à jour de la session pour que le header affiche le nouveau prénom
            $_SESSION['user_prenom'] = $prenom;
            $success = true;

            // Rechargement des données fraîches depuis la BDD
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
        }
    }
}

// ── Traitement : changement de mot de passe ───────────────────
$pwSuccess = false; // true si changement de mot de passe réussi
$pwErrors  = [];    // Erreurs du formulaire de mot de passe

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'password') {
    $current = $_POST['current_password']  ?? ''; // Mot de passe actuel (pour vérification)
    $new     = $_POST['new_password']      ?? ''; // Nouveau mot de passe
    $confirm = $_POST['confirm_password']  ?? ''; // Confirmation du nouveau mot de passe

    // Validation
    if (empty($current))       $pwErrors[] = "Mot de passe actuel requis.";
    if (strlen($new) < 8)      $pwErrors[] = "Le nouveau mot de passe doit faire au moins 8 caractères.";
    if ($new !== $confirm)     $pwErrors[] = "Les mots de passe ne correspondent pas.";

    if (empty($pwErrors)) {
        // Récupération du hash actuel pour vérification
        $stmt2 = $pdo->prepare("SELECT password FROM utilisateurs WHERE id = ?");
        $stmt2->execute([$userId]);
        $hash = $stmt2->fetchColumn(); // Retourne directement la valeur de la colonne

        // Vérification que le mot de passe actuel est correct
        if (!password_verify($current, $hash)) {
            $pwErrors[] = "Mot de passe actuel incorrect.";
        } else {
            // Hachage du nouveau mot de passe avec bcrypt
            $pdo->prepare("UPDATE utilisateurs SET password = ? WHERE id = ?")
                ->execute([password_hash($new, PASSWORD_BCRYPT), $userId]);
            $pwSuccess = true;
        }
    }
}

// ── Statistiques : nombre total de commandes ─────────────────
$nbCommandes = $pdo->prepare("SELECT COUNT(*) FROM commandes WHERE utilisateur_id = ?");
$nbCommandes->execute([$userId]);
$nbCommandes = (int)$nbCommandes->fetchColumn(); // Conversion en entier

// ── 5 dernières commandes ─────────────────────────────────────
$commandes = $pdo->prepare("
    SELECT id, total, statut, created_at
    FROM commandes
    WHERE utilisateur_id = ?
    ORDER BY created_at DESC -- Du plus récent au plus ancien
    LIMIT 5                  -- Seulement les 5 dernières
");
$commandes->execute([$userId]);
$commandes = $commandes->fetchAll();

// Chargement du header (après le traitement pour pouvoir rediriger si besoin)
require_once '../includes/header.php';
?>

<main>
<div class="profil-page">

    <!-- ── En-tête du profil : avatar + nom + stats ── -->
    <div class="profil-header">
        <!-- Avatar généré automatiquement avec les initiales de l'utilisateur -->
        <div class="profil-avatar">
            <!-- mb_substr() gère correctement les caractères UTF-8 (accents, etc.) -->
            <?= strtoupper(mb_substr($user['prenom'], 0, 1)) . strtoupper(mb_substr($user['nom'], 0, 1)) ?>
        </div>
        <div>
            <!-- Nom complet de l'utilisateur -->
            <h1><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h1>
            <p class="profil-since">
                <!-- date() formate la date d'inscription en "Mois Année" -->
                Membre depuis <?= date('F Y', strtotime($user['created_at'])) ?>
                &nbsp;·&nbsp;
                <!-- Pluriel conditionnel pour "commande(s)" -->
                <?= $nbCommandes ?> commande<?= $nbCommandes > 1 ? 's' : '' ?>
            </p>
        </div>
    </div>

    <!-- ── Grille des deux cartes de formulaires ── -->
    <div class="profil-grid">

        <!-- ── Carte 1 : Informations personnelles ── -->
        <div class="profil-card">
            <h2>Informations personnelles</h2>

            <!-- Message de succès après mise à jour -->
            <?php if ($success): ?>
                <div class="alert-success" style="margin-bottom:20px">✓ Profil mis à jour.</div>
            <?php endif; ?>

            <!-- Erreurs de validation -->
            <?php if (!empty($errors)): ?>
                <div class="alert-error" style="margin-bottom:20px">
                    <?php foreach ($errors as $e): ?>
                        <p>⚠ <?= htmlspecialchars($e) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Formulaire de modification du profil -->
            <form method="post" action="profil.php">
                <!-- Identifie ce formulaire comme une mise à jour du profil -->
                <input type="hidden" name="action" value="update">

                <!-- Rangée prénom + nom côte à côte -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="prenom">Prénom</label>
                        <!-- value pré-rempli avec les données actuelles de l'utilisateur -->
                        <input type="text" id="prenom" name="prenom"
                               value="<?= htmlspecialchars($user['prenom']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom"
                               value="<?= htmlspecialchars($user['nom']) ?>" required>
                    </div>
                </div>

                <!-- Champ email -->
                <div class="form-group">
                    <label for="email">Adresse email</label>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;padding:13px">
                    Enregistrer les modifications
                </button>
            </form>
        </div>

        <!-- ── Carte 2 : Changement de mot de passe ── -->
        <div class="profil-card">
            <h2>Sécurité</h2>

            <!-- Message de succès après changement de mot de passe -->
            <?php if ($pwSuccess): ?>
                <div class="alert-success" style="margin-bottom:20px">✓ Mot de passe modifié.</div>
            <?php endif; ?>

            <!-- Erreurs de validation du mot de passe -->
            <?php if (!empty($pwErrors)): ?>
                <div class="alert-error" style="margin-bottom:20px">
                    <?php foreach ($pwErrors as $e): ?>
                        <p>⚠ <?= htmlspecialchars($e) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Formulaire de changement de mot de passe -->
            <form method="post" action="profil.php">
                <!-- Identifie ce formulaire comme un changement de mot de passe -->
                <input type="hidden" name="action" value="password">

                <!-- Mot de passe actuel requis pour confirmer l'identité -->
                <div class="form-group">
                    <label for="current_password">Mot de passe actuel</label>
                    <!-- autocomplete="current-password" aide les gestionnaires de mots de passe -->
                    <input type="password" id="current_password" name="current_password"
                           placeholder="••••••••" required>
                </div>

                <!-- Nouveau mot de passe -->
                <div class="form-group">
                    <label for="new_password">Nouveau mot de passe</label>
                    <!-- minlength="8" ajoute aussi une validation HTML5 -->
                    <input type="password" id="new_password" name="new_password"
                           placeholder="••••••••" required minlength="8">
                </div>

                <!-- Confirmation pour éviter les erreurs de frappe -->
                <div class="form-group">
                    <label for="confirm_password">Confirmer</label>
                    <input type="password" id="confirm_password" name="confirm_password"
                           placeholder="••••••••" required minlength="8">
                </div>

                <!-- Style "outline" pour différencier visuellement des deux boutons -->
                <button type="submit" class="btn btn-outline" style="width:100%;padding:13px">
                    Changer le mot de passe
                </button>
            </form>
        </div>

    </div>

    <!-- ── Tableau des dernières commandes ── -->
    <!-- Affiché uniquement si l'utilisateur a au moins une commande -->
    <?php if (!empty($commandes)): ?>
    <div class="profil-card profil-card-full">
        <h2>Dernières commandes</h2>
        <table class="commandes-table">
            <thead>
                <tr>
                    <th>Commande</th> <!-- Numéro de commande -->
                    <th>Date</th>
                    <th>Total</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Mapping des statuts BDD vers labels et couleurs d'affichage
                $statutLabels = [
                    'en_attente' => ['label' => 'En attente', 'color' => '#e2b04a'], // Doré
                    'payee'      => ['label' => 'Payée',      'color' => '#2ecc71'], // Vert
                    'expediee'   => ['label' => 'Expédiée',   'color' => '#3498db'], // Bleu
                    'livree'     => ['label' => 'Livrée',     'color' => '#27ae60'], // Vert foncé
                    'annulee'    => ['label' => 'Annulée',    'color' => '#e74c3c'], // Rouge
                ];

                foreach ($commandes as $cmd):
                    // Récupération du label et de la couleur pour ce statut
                    // Fallback sur le statut brut si non trouvé dans le mapping
                    $s = $statutLabels[$cmd['statut']] ?? ['label' => $cmd['statut'], 'color' => '#fff'];
                ?>
                <tr>
                    <!-- Numéro de commande avec # devant -->
                    <td>#<?= (int)$cmd['id'] ?></td>
                    <!-- Date formatée en dd/mm/yyyy -->
                    <td><?= date('d/m/Y', strtotime($cmd['created_at'])) ?></td>
                    <!-- Total en doré pour mettre en valeur -->
                    <td style="color:var(--gold-light);font-weight:700">
                        <?= number_format($cmd['total'], 2, ',', '') ?> €
                    </td>
                    <!-- Badge coloré avec opacité sur le fond (couleur + "22" = 13% opacité en hex) -->
                    <td>
                        <span class="statut-badge"
                              style="background:<?= $s['color'] ?>22;color:<?= $s['color'] ?>">
                            <?= $s['label'] ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

</div>
</main>

<?php require_once '../includes/footer.php'; ?>

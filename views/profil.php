<?php
$pageTitle = "Mon profil";
$pageCss   = "pages.css";
$basePath  = '../';

if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: creecompte.php');
    exit;
}

$success = false;
$errors  = [];
$userId  = (int)$_SESSION['user_id'];

require_once '../includes/db.php';

$stmt = $pdo->prepare("SELECT prenom, nom, email, created_at FROM utilisateurs WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    $prenom = trim($_POST['prenom'] ?? '');
    $nom    = trim($_POST['nom']    ?? '');
    $email  = trim($_POST['email']  ?? '');

    if (empty($prenom)) $errors[] = "Prénom requis.";
    if (empty($nom))    $errors[] = "Nom requis.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide.";

    if (empty($errors)) {
        $check = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ? AND id != ?");
        $check->execute([$email, $userId]);
        if ($check->fetch()) {
            $errors[] = "Cet email est déjà utilisé.";
        } else {
            $pdo->prepare("UPDATE utilisateurs SET prenom = ?, nom = ?, email = ? WHERE id = ?")
                ->execute([$prenom, $nom, $email, $userId]);
            $_SESSION['user_prenom'] = $prenom;
            $success = true;
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
        }
    }
}

$pwSuccess = false;
$pwErrors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'password') {
    $current = $_POST['current_password']  ?? '';
    $new     = $_POST['new_password']      ?? '';
    $confirm = $_POST['confirm_password']  ?? '';

    if (empty($current))       $pwErrors[] = "Mot de passe actuel requis.";
    if (strlen($new) < 8)      $pwErrors[] = "Le nouveau mot de passe doit faire au moins 8 caractères.";
    if ($new !== $confirm)     $pwErrors[] = "Les mots de passe ne correspondent pas.";

    if (empty($pwErrors)) {
        $stmt2 = $pdo->prepare("SELECT password FROM utilisateurs WHERE id = ?");
        $stmt2->execute([$userId]);
        $hash = $stmt2->fetchColumn();
        if (!password_verify($current, $hash)) {
            $pwErrors[] = "Mot de passe actuel incorrect.";
        } else {
            $pdo->prepare("UPDATE utilisateurs SET password = ? WHERE id = ?")
                ->execute([password_hash($new, PASSWORD_BCRYPT), $userId]);
            $pwSuccess = true;
        }
    }
}

$nbCommandes = $pdo->prepare("SELECT COUNT(*) FROM commandes WHERE utilisateur_id = ?");
$nbCommandes->execute([$userId]);
$nbCommandes = (int)$nbCommandes->fetchColumn();

$commandes = $pdo->prepare("
    SELECT id, total, statut, created_at
    FROM commandes WHERE utilisateur_id = ?
    ORDER BY created_at DESC LIMIT 5
");
$commandes->execute([$userId]);
$commandes = $commandes->fetchAll();

require_once '../includes/header.php';
?>

<main>
<div class="profil-page">

    <!-- ── Header ── -->
    <div class="profil-header">
        <div class="profil-avatar">
            <?= strtoupper(mb_substr($user['prenom'],0,1)) . strtoupper(mb_substr($user['nom'],0,1)) ?>
        </div>
        <div>
            <h1><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h1>
            <p class="profil-since">
                Membre depuis <?= date('F Y', strtotime($user['created_at'])) ?>
                &nbsp;·&nbsp; <?= $nbCommandes ?> commande<?= $nbCommandes > 1 ? 's' : '' ?>
            </p>
        </div>
    </div>

    <div class="profil-grid">

        <!-- ── Informations personnelles ── -->
        <div class="profil-card">
            <h2>Informations personnelles</h2>

            <?php if ($success): ?>
                <div class="alert-success" style="margin-bottom:20px">✓ Profil mis à jour.</div>
            <?php endif; ?>
            <?php if (!empty($errors)): ?>
                <div class="alert-error" style="margin-bottom:20px">
                    <?php foreach ($errors as $e): ?><p>⚠ <?= htmlspecialchars($e) ?></p><?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="profil.php">
                <input type="hidden" name="action" value="update">
                <div class="form-row">
                    <div class="form-group">
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom"
                               value="<?= htmlspecialchars($user['prenom']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom"
                               value="<?= htmlspecialchars($user['nom']) ?>" required>
                    </div>
                </div>
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

        <!-- ── Mot de passe ── -->
        <div class="profil-card">
            <h2>Sécurité</h2>

            <?php if ($pwSuccess): ?>
                <div class="alert-success" style="margin-bottom:20px">✓ Mot de passe modifié.</div>
            <?php endif; ?>
            <?php if (!empty($pwErrors)): ?>
                <div class="alert-error" style="margin-bottom:20px">
                    <?php foreach ($pwErrors as $e): ?><p>⚠ <?= htmlspecialchars($e) ?></p><?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="profil.php">
                <input type="hidden" name="action" value="password">
                <div class="form-group">
                    <label for="current_password">Mot de passe actuel</label>
                    <input type="password" id="current_password" name="current_password"
                           placeholder="••••••••" required>
                </div>
                <div class="form-group">
                    <label for="new_password">Nouveau mot de passe</label>
                    <input type="password" id="new_password" name="new_password"
                           placeholder="••••••••" required minlength="8">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirmer</label>
                    <input type="password" id="confirm_password" name="confirm_password"
                           placeholder="••••••••" required minlength="8">
                </div>
                <button type="submit" class="btn btn-outline" style="width:100%;padding:13px">
                    Changer le mot de passe
                </button>
            </form>
        </div>

    </div>

    <!-- ── Dernières commandes ── -->
    <?php if (!empty($commandes)): ?>
    <div class="profil-card profil-card-full">
        <h2>Dernières commandes</h2>
        <table class="commandes-table">
            <thead>
                <tr>
                    <th>Commande</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $statutLabels = [
                    'en_attente' => ['label' => 'En attente', 'color' => '#e2b04a'],
                    'payee'      => ['label' => 'Payée',      'color' => '#2ecc71'],
                    'expediee'   => ['label' => 'Expédiée',   'color' => '#3498db'],
                    'livree'     => ['label' => 'Livrée',     'color' => '#27ae60'],
                    'annulee'    => ['label' => 'Annulée',    'color' => '#e74c3c'],
                ];
                foreach ($commandes as $cmd):
                    $s = $statutLabels[$cmd['statut']] ?? ['label' => $cmd['statut'], 'color' => '#fff'];
                ?>
                <tr>
                    <td>#<?= (int)$cmd['id'] ?></td>
                    <td><?= date('d/m/Y', strtotime($cmd['created_at'])) ?></td>
                    <td style="color:var(--gold-light);font-weight:700">
                        <?= number_format($cmd['total'], 2, ',', '') ?> €
                    </td>
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

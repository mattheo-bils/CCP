<?php
$pageTitle = "Mon profil";
$pageCss   = "pages.css";
$basePath  = '../';

if (session_status() === PHP_SESSION_NONE) session_start();

// Rediriger si non connecté
if (empty($_SESSION['user_id'])) {
    header('Location: creecompte.php');
    exit;
}

$success = false;
$errors  = [];
$userId  = (int)$_SESSION['user_id'];

require_once '../includes/db.php';

// Charger les données utilisateur
$stmt = $pdo->prepare("SELECT prenom, nom, email, created_at FROM utilisateurs WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// ── Mise à jour du profil ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $prenom = trim($_POST['prenom'] ?? '');
    $nom    = trim($_POST['nom']    ?? '');
    $email  = trim($_POST['email']  ?? '');

    if (empty($prenom)) $errors[] = "Prénom requis.";
    if (empty($nom))    $errors[] = "Nom requis.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide.";

    if (empty($errors)) {
        // Vérifier doublon email (sauf pour l'utilisateur actuel)
        $check = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ? AND id != ?");
        $check->execute([$email, $userId]);
        if ($check->fetch()) {
            $errors[] = "Cet email est déjà utilisé.";
        } else {
            $pdo->prepare("UPDATE utilisateurs SET prenom = ?, nom = ?, email = ? WHERE id = ?")
                ->execute([$prenom, $nom, $email, $userId]);
            $_SESSION['user_prenom'] = $prenom;
            $success = true;
            // Recharger les données
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
        }
    }
}

// ── Changement de mot de passe ─────────────────────────────
$pwSuccess = false;
$pwErrors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'password') {
    $current  = $_POST['current_password']  ?? '';
    $new      = $_POST['new_password']      ?? '';
    $confirm  = $_POST['confirm_password']  ?? '';

    if (empty($current))          $pwErrors[] = "Mot de passe actuel requis.";
    if (strlen($new) < 8)         $pwErrors[] = "Le nouveau mot de passe doit faire au moins 8 caractères.";
    if ($new !== $confirm)        $pwErrors[] = "Les mots de passe ne correspondent pas.";

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

// Statistiques panier et commandes
$nbCommandes = $pdo->prepare("SELECT COUNT(*) FROM commandes WHERE utilisateur_id = ?");
$nbCommandes->execute([$userId]);
$nbCommandes = (int)$nbCommandes->fetchColumn();

require_once '../includes/header.php';
?>

<main>
    <div class="profil-page">

        <div class="profil-header">
            <div class="profil-avatar">
                <?= strtoupper(mb_substr($user['prenom'], 0, 1)) . strtoupper(mb_substr($user['nom'], 0, 1)) ?>
            </div>
            <div>
                <h1><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h1>
                <p class="profil-since">
                    Membre depuis <?= date('F Y', strtotime($user['created_at'])) ?>
                    · <?= $nbCommandes ?> commande<?= $nbCommandes > 1 ? 's' : '' ?>
                </p>
            </div>
        </div>

        <div class="profil-grid">

            <!-- ── Informations personnelles ── -->
            <div class="profil-card">
                <h2>Informations personnelles</h2>

                <?php if ($success): ?>
                    <div class="alert-success">✓ Profil mis à jour avec succès.</div>
                <?php endif; ?>
                <?php if (!empty($errors)): ?>
                    <div class="alert-error">
                        <?php foreach ($errors as $e): ?>
                            <p>⚠ <?= htmlspecialchars($e) ?></p>
                        <?php endforeach; ?>
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
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email"
                               value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </form>
            </div>

            <!-- ── Changer le mot de passe ── -->
            <div class="profil-card">
                <h2>Changer le mot de passe</h2>

                <?php if ($pwSuccess): ?>
                    <div class="alert-success">✓ Mot de passe modifié avec succès.</div>
                <?php endif; ?>
                <?php if (!empty($pwErrors)): ?>
                    <div class="alert-error">
                        <?php foreach ($pwErrors as $e): ?>
                            <p>⚠ <?= htmlspecialchars($e) ?></p>
                        <?php endforeach; ?>
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
                        <label for="confirm_password">Confirmer le mot de passe</label>
                        <input type="password" id="confirm_password" name="confirm_password"
                               placeholder="••••••••" required minlength="8">
                    </div>
                    <button type="submit" class="btn btn-primary">Modifier</button>
                </form>
            </div>

        </div>

        <!-- ── Dernières commandes ── -->
        <?php
        $commandes = $pdo->prepare("
            SELECT id, total, statut, created_at
            FROM commandes
            WHERE utilisateur_id = ?
            ORDER BY created_at DESC
            LIMIT 5
        ");
        $commandes->execute([$userId]);
        $commandes = $commandes->fetchAll();
        ?>
        <?php if (!empty($commandes)): ?>
        <div class="profil-card profil-card-full">
            <h2>Dernières commandes</h2>
            <table class="commandes-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($commandes as $cmd):
                        $statutLabels = [
                            'en_attente' => ['label' => 'En attente', 'color' => '#e2b04a'],
                            'payee'      => ['label' => 'Payée',      'color' => '#2ecc71'],
                            'expediee'   => ['label' => 'Expédiée',   'color' => '#3498db'],
                            'livree'     => ['label' => 'Livrée',     'color' => '#27ae60'],
                            'annulee'    => ['label' => 'Annulée',    'color' => '#e74c3c'],
                        ];
                        $s = $statutLabels[$cmd['statut']] ?? ['label' => $cmd['statut'], 'color' => '#fff'];
                    ?>
                    <tr>
                        <td>#<?= (int)$cmd['id'] ?></td>
                        <td><?= date('d/m/Y', strtotime($cmd['created_at'])) ?></td>
                        <td><?= number_format($cmd['total'], 2, ',', '') ?> €</td>
                        <td>
                            <span class="statut-badge" style="background:<?= $s['color'] ?>22;color:<?= $s['color'] ?>">
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

<style>
.profil-page { max-width: 900px; margin: 0 auto; padding: 40px 20px; }

.profil-header {
    display: flex;
    align-items: center;
    gap: 24px;
    margin-bottom: 40px;
}
.profil-avatar {
    width: 72px; height: 72px;
    border-radius: 50%;
    background: var(--gold-light, #e2b04a);
    color: #111;
    font-size: 1.6rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.profil-header h1 { margin: 0; font-size: 1.8rem; }
.profil-since { color: var(--grey-400, #888); margin: 4px 0 0; font-size: 0.9rem; }

.profil-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-bottom: 24px;
}
@media (max-width: 700px) { .profil-grid { grid-template-columns: 1fr; } }

.profil-card {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 16px;
    padding: 28px;
}
.profil-card h2 { margin: 0 0 20px; font-size: 1.1rem; }
.profil-card-full { grid-column: 1 / -1; }

.commandes-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
.commandes-table th {
    text-align: left;
    padding: 10px 12px;
    color: var(--grey-400, #888);
    border-bottom: 1px solid rgba(255,255,255,0.08);
    font-weight: 500;
}
.commandes-table td {
    padding: 12px;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}
.statut-badge {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}
</style>

<?php require_once '../includes/footer.php'; ?>

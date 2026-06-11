<?php
/**
 * views/admin/utilisateurs.php — Gestion des utilisateurs
 *
 * Permet à l'admin de voir tous les utilisateurs,
 * modifier leurs rôles et supprimer des comptes.
 */

require_once '../../includes/auth_admin.php';

$pageTitle = "Gestion des utilisateurs";
$pageCss   = "pages.css";
$basePath  = '../../';

require_once '../../includes/db.php';

$adminId = (int)$_SESSION['user_id'];

// ── Modification du rôle ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'change_role') {
        $uid  = (int)$_POST['user_id'];
        $role = $_POST['role'] === 'admin' ? 'admin' : 'client';
        // Empêche un admin de se retirer lui-même ses droits
        if ($uid !== $adminId) {
            $pdo->prepare("UPDATE utilisateurs SET role = ? WHERE id = ?")
                ->execute([$role, $uid]);
        }
        header('Location: utilisateurs.php?updated=1');
        exit;
    }

    if ($_POST['action'] === 'delete') {
        $uid = (int)$_POST['user_id'];
        // Empêche la suppression de son propre compte
        if ($uid !== $adminId) {
            $pdo->prepare("DELETE FROM utilisateurs WHERE id = ?")->execute([$uid]);
        }
        header('Location: utilisateurs.php?deleted=1');
        exit;
    }
}

// ── Chargement des utilisateurs ───────────────────────────────
$utilisateurs = $pdo->query("
    SELECT u.id, u.prenom, u.nom, u.email, u.role, u.created_at,
           COUNT(c.id) AS nb_commandes
    FROM utilisateurs u
    LEFT JOIN commandes c ON c.utilisateur_id = u.id
    GROUP BY u.id
    ORDER BY u.created_at DESC
")->fetchAll();

require_once '../../includes/header.php';
?>

<main>
<div class="profil-page">
    <div class="profil-header">
        <div class="profil-avatar" style="background:linear-gradient(135deg,var(--red),var(--red-dark))">👥</div>
        <div>
            <h1>Gestion des utilisateurs</h1>
            <p class="profil-since"><a href="dashboard.php" style="color:var(--grey-400)">← Dashboard</a></p>
        </div>
    </div>

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert-success" style="margin-bottom:20px">✓ Rôle mis à jour.</div>
    <?php elseif (isset($_GET['deleted'])): ?>
        <div class="alert-success" style="margin-bottom:20px">✓ Utilisateur supprimé.</div>
    <?php endif; ?>

    <div class="profil-card profil-card-full">
        <h2><?= count($utilisateurs) ?> utilisateur<?= count($utilisateurs) > 1 ? 's' : '' ?></h2>
        <table class="commandes-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Commandes</th>
                    <th>Inscrit le</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($utilisateurs as $u): ?>
                <tr>
                    <td><?= (int)$u['id'] ?></td>
                    <td><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?></td>
                    <td style="color:var(--grey-400);font-size:0.85rem">
                        <?= htmlspecialchars($u['email']) ?>
                    </td>
                    <td>
                        <!-- Badge rôle coloré -->
                        <span style="background:<?= $u['role'] === 'admin' ? 'rgba(192,57,43,0.2)' : 'rgba(52,152,219,0.2)' ?>;
                                     color:<?= $u['role'] === 'admin' ? 'var(--red-bright)' : '#3498db' ?>;
                                     padding:3px 10px;border-radius:20px;font-size:0.78rem;font-weight:700">
                            <?= $u['role'] === 'admin' ? '⚙ Admin' : '👤 Client' ?>
                        </span>
                    </td>
                    <td><?= (int)$u['nb_commandes'] ?></td>
                    <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <?php if ($u['id'] !== $adminId): ?>
                        <div style="display:flex;gap:6px;align-items:center">

                            <!-- Formulaire changement de rôle -->
                            <form method="post" action="utilisateurs.php">
                                <input type="hidden" name="action"  value="change_role">
                                <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                <input type="hidden" name="role"
                                       value="<?= $u['role'] === 'admin' ? 'client' : 'admin' ?>">
                                <button type="submit" class="btn btn-outline"
                                        style="padding:4px 10px;font-size:0.78rem">
                                    <?= $u['role'] === 'admin' ? '→ Client' : '→ Admin' ?>
                                </button>
                            </form>

                            <!-- Formulaire suppression -->
                            <form method="post" action="utilisateurs.php"
                                  onsubmit="return confirm('Supprimer cet utilisateur ?')">
                                <input type="hidden" name="action"  value="delete">
                                <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                <button type="submit" class="btn"
                                        style="padding:4px 10px;font-size:0.78rem;
                                               background:rgba(192,57,43,0.2);color:var(--red-bright)">
                                    Supprimer
                                </button>
                            </form>
                        </div>
                        <?php else: ?>
                            <span style="color:var(--grey-400);font-size:0.8rem">Vous</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</main>

<?php require_once '../../includes/footer.php'; ?>

<?php
/**
 * views/admin/commandes.php — Gestion des commandes
 *
 * Permet à l'admin de voir toutes les commandes et de modifier leur statut.
 */

require_once '../../includes/auth_admin.php';

$pageTitle = "Gestion des commandes";
$pageCss   = "pages.css";
$basePath  = '../../';

require_once '../../includes/db.php';

// ── Mise à jour du statut d'une commande ──────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['commande_id'], $_POST['statut'])) {
    $statutsValides = ['en_attente', 'payee', 'expediee', 'livree', 'annulee'];
    $statut = $_POST['statut'];
    if (in_array($statut, $statutsValides)) {
        $pdo->prepare("UPDATE commandes SET statut = ? WHERE id = ?")
            ->execute([$statut, (int)$_POST['commande_id']]);
    }
    header('Location: commandes.php?updated=1');
    exit;
}

// ── Filtre par statut ─────────────────────────────────────────
$filtreStatut = $_GET['statut'] ?? '';
$statutsValides = ['en_attente', 'payee', 'expediee', 'livree', 'annulee'];

$where  = '';
$params = [];
if ($filtreStatut && in_array($filtreStatut, $statutsValides)) {
    $where  = 'WHERE c.statut = ?';
    $params = [$filtreStatut];
}

// ── Chargement des commandes ──────────────────────────────────
$commandes = $pdo->prepare("
    SELECT c.id, c.prenom, c.nom, c.adresse, c.code_postal, c.ville,
           c.total, c.statut, c.created_at,
           u.email, u.id AS user_id
    FROM commandes c
    LEFT JOIN utilisateurs u ON u.id = c.utilisateur_id
    $where
    ORDER BY c.created_at DESC
");
$commandes->execute($params);
$commandes = $commandes->fetchAll();

$statutLabels = [
    'en_attente' => ['label' => 'En attente', 'color' => '#e2b04a'],
    'payee'      => ['label' => 'Payée',      'color' => '#2ecc71'],
    'expediee'   => ['label' => 'Expédiée',   'color' => '#3498db'],
    'livree'     => ['label' => 'Livrée',     'color' => '#27ae60'],
    'annulee'    => ['label' => 'Annulée',    'color' => '#e74c3c'],
];

require_once '../../includes/header.php';
?>

<main>
<div class="profil-page">
    <div class="profil-header">
        <div class="profil-avatar" style="background:linear-gradient(135deg,var(--red),var(--red-dark))">📦</div>
        <div>
            <h1>Gestion des commandes</h1>
            <p class="profil-since">
                <a href="dashboard.php" style="color:var(--grey-400)">← Dashboard</a>
            </p>
        </div>
    </div>

    <?php if (isset($_GET['updated'])): ?>
    <div class="alert-success" style="margin-bottom:20px">✓ Statut mis à jour.</div>
    <?php endif; ?>

    <!-- Filtres par statut -->
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px">
        <a href="commandes.php"
           class="btn <?= !$filtreStatut ? 'btn-primary' : 'btn-outline' ?>"
           style="padding:8px 16px;font-size:0.85rem">Toutes</a>
        <?php foreach ($statutLabels as $slug => $s): ?>
        <a href="commandes.php?statut=<?= $slug ?>"
           class="btn <?= $filtreStatut === $slug ? 'btn-primary' : 'btn-outline' ?>"
           style="padding:8px 16px;font-size:0.85rem">
            <?= $s['label'] ?>
        </a>
        <?php endforeach; ?>
    </div>

    <div class="profil-card profil-card-full">
        <h2><?= count($commandes) ?> commande<?= count($commandes) > 1 ? 's' : '' ?></h2>
        <?php if (empty($commandes)): ?>
            <p style="color:var(--grey-400)">Aucune commande trouvée.</p>
        <?php else: ?>
        <table class="commandes-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Client</th>
                    <th>Adresse</th>
                    <th>Total</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th>Modifier</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($commandes as $cmd):
                    $s = $statutLabels[$cmd['statut']] ?? ['label' => $cmd['statut'], 'color' => '#fff'];
                ?>
                <tr>
                    <td>#<?= (int)$cmd['id'] ?></td>
                    <td>
                        <?= htmlspecialchars($cmd['prenom'] . ' ' . $cmd['nom']) ?>
                        <div style="font-size:0.78rem;color:var(--grey-400)">
                            <?= htmlspecialchars($cmd['email'] ?? 'Invité') ?>
                        </div>
                    </td>
                    <td style="font-size:0.82rem;color:var(--grey-400)">
                        <?= htmlspecialchars($cmd['adresse']) ?>,
                        <?= htmlspecialchars($cmd['code_postal']) ?>
                        <?= htmlspecialchars($cmd['ville']) ?>
                    </td>
                    <td style="color:var(--gold-light);font-weight:700">
                        <?= number_format($cmd['total'], 2, ',', '') ?> €
                    </td>
                    <td><?= date('d/m/Y H:i', strtotime($cmd['created_at'])) ?></td>
                    <td>
                        <span class="statut-badge"
                              style="background:<?= $s['color'] ?>22;color:<?= $s['color'] ?>">
                            <?= $s['label'] ?>
                        </span>
                    </td>
                    <td>
                        <!-- Formulaire de changement de statut -->
                        <form method="post" action="commandes.php" style="display:flex;gap:6px;align-items:center">
                            <input type="hidden" name="commande_id" value="<?= (int)$cmd['id'] ?>">
                            <select name="statut"
                                    style="background:var(--navy-mid);border:1px solid rgba(255,255,255,0.1);
                                           color:var(--white);border-radius:8px;padding:4px 8px;font-size:0.8rem">
                                <?php foreach ($statutLabels as $slug => $sl): ?>
                                <option value="<?= $slug ?>"
                                        <?= $cmd['statut'] === $slug ? 'selected' : '' ?>>
                                    <?= $sl['label'] ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-primary"
                                    style="padding:4px 10px;font-size:0.8rem">✓</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
</main>

<?php require_once '../../includes/footer.php'; ?>

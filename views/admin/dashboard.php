<?php
/**
 * views/admin/dashboard.php — Tableau de bord administrateur
 *
 * Page principale de l'interface d'administration.
 * Accessible uniquement aux utilisateurs avec role='admin'.
 *
 * Affiche :
 *   - Statistiques globales (commandes, revenus, utilisateurs, produits)
 *   - Dernières commandes
 *   - Produits en rupture ou stock faible
 *   - Liens vers les sections de gestion
 */

// Protection : redirige si non connecté ou non admin
require_once '../../includes/auth_admin.php';

$pageTitle = "Administration";
$pageCss   = "pages.css";
$basePath  = '../../';

require_once '../../includes/db.php';

// ── Statistiques globales ─────────────────────────────────────

// Nombre total de commandes
$nbCommandes = (int)$pdo->query("SELECT COUNT(*) FROM commandes")->fetchColumn();

// Revenu total toutes commandes confondues
$revenuTotal = (float)$pdo->query("SELECT COALESCE(SUM(total), 0) FROM commandes")->fetchColumn();

// Nombre total de clients (role='client')
$nbClients = (int)$pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'client'")->fetchColumn();

// Nombre total de produits en catalogue
$nbProduits = (int)$pdo->query("SELECT COUNT(DISTINCT titre) FROM produits")->fetchColumn();

// ── Dernières commandes ───────────────────────────────────────
$dernieresCommandes = $pdo->query("
    SELECT c.id, c.prenom, c.nom, c.total, c.statut, c.created_at,
           u.email
    FROM commandes c
    LEFT JOIN utilisateurs u ON u.id = c.utilisateur_id
    ORDER BY c.created_at DESC
    LIMIT 8
")->fetchAll();

// ── Produits avec stock faible ou rupture ─────────────────────
$stockAlerte = $pdo->query("
    SELECT id, titre, tome, stock
    FROM produits
    WHERE stock <= 3
    ORDER BY stock ASC
    LIMIT 10
")->fetchAll();

// Labels et couleurs des statuts de commande
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

    <!-- ── En-tête admin ── -->
    <div class="profil-header">
        <div class="profil-avatar" style="background:linear-gradient(135deg,var(--red),var(--red-dark))">
            ⚙
        </div>
        <div>
            <h1>Tableau de bord</h1>
            <p class="profil-since">Administration MangaMarket</p>
        </div>
    </div>

    <!-- ── Statistiques ── -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:24px">

        <!-- Carte commandes -->
        <div class="profil-card" style="text-align:center">
            <div style="font-size:2.5rem;margin-bottom:8px">📦</div>
            <div style="font-family:var(--font-display);font-size:2rem;color:var(--gold-light)">
                <?= $nbCommandes ?>
            </div>
            <div style="color:var(--grey-400);font-size:0.85rem;margin-top:4px">Commandes</div>
        </div>

        <!-- Carte revenus -->
        <div class="profil-card" style="text-align:center">
            <div style="font-size:2.5rem;margin-bottom:8px">💰</div>
            <div style="font-family:var(--font-display);font-size:2rem;color:var(--gold-light)">
                <?= number_format($revenuTotal, 2, ',', ' ') ?> €
            </div>
            <div style="color:var(--grey-400);font-size:0.85rem;margin-top:4px">Revenu total</div>
        </div>

        <!-- Carte clients -->
        <div class="profil-card" style="text-align:center">
            <div style="font-size:2.5rem;margin-bottom:8px">👥</div>
            <div style="font-family:var(--font-display);font-size:2rem;color:var(--gold-light)">
                <?= $nbClients ?>
            </div>
            <div style="color:var(--grey-400);font-size:0.85rem;margin-top:4px">Clients</div>
        </div>

        <!-- Carte produits -->
        <div class="profil-card" style="text-align:center">
            <div style="font-size:2.5rem;margin-bottom:8px">📚</div>
            <div style="font-family:var(--font-display);font-size:2rem;color:var(--gold-light)">
                <?= $nbProduits ?>
            </div>
            <div style="color:var(--grey-400);font-size:0.85rem;margin-top:4px">Mangas</div>
        </div>
    </div>

    <!-- ── Navigation admin ── -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:24px">
        <a href="commandes.php" class="btn btn-outline" style="text-align:center;padding:14px">
            📦 Commandes
        </a>
        <a href="produits.php" class="btn btn-outline" style="text-align:center;padding:14px">
            📚 Produits
        </a>
        <a href="utilisateurs.php" class="btn btn-outline" style="text-align:center;padding:14px">
            👥 Utilisateurs
        </a>
        <a href="messages.php" class="btn btn-outline" style="text-align:center;padding:14px">
            ✉ Messages
        </a>
    </div>

    <div class="profil-grid">

        <!-- ── Dernières commandes ── -->
        <div class="profil-card profil-card-full">
            <h2>Dernières commandes</h2>
            <?php if (empty($dernieresCommandes)): ?>
                <p style="color:var(--grey-400)">Aucune commande pour l'instant.</p>
            <?php else: ?>
            <table class="commandes-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Client</th>
                        <th>Email</th>
                        <th>Total</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dernieresCommandes as $cmd):
                        $s = $statutLabels[$cmd['statut']] ?? ['label' => $cmd['statut'], 'color' => '#fff'];
                    ?>
                    <tr>
                        <td>#<?= (int)$cmd['id'] ?></td>
                        <td><?= htmlspecialchars($cmd['prenom'] . ' ' . $cmd['nom']) ?></td>
                        <td style="color:var(--grey-400);font-size:0.85rem">
                            <?= htmlspecialchars($cmd['email'] ?? 'Invité') ?>
                        </td>
                        <td style="color:var(--gold-light);font-weight:700">
                            <?= number_format($cmd['total'], 2, ',', '') ?> €
                        </td>
                        <td><?= date('d/m/Y', strtotime($cmd['created_at'])) ?></td>
                        <td>
                            <span class="statut-badge"
                                  style="background:<?= $s['color'] ?>22;color:<?= $s['color'] ?>">
                                <?= $s['label'] ?>
                            </span>
                        </td>
                        <td>
                            <a href="commandes.php?id=<?= (int)$cmd['id'] ?>"
                               style="color:var(--red-bright);font-size:0.85rem">
                                Gérer →
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <!-- ── Alertes stock ── -->
        <?php if (!empty($stockAlerte)): ?>
        <div class="profil-card profil-card-full">
            <h2>⚠ Alertes stock</h2>
            <table class="commandes-table">
                <thead>
                    <tr>
                        <th>Manga</th>
                        <th>Tome</th>
                        <th>Stock</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stockAlerte as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['titre']) ?></td>
                        <td>Tome <?= (int)$p['tome'] ?></td>
                        <td>
                            <span style="color:<?= $p['stock'] <= 0 ? 'var(--red-bright)' : 'var(--gold-light)' ?>;font-weight:700">
                                <?= $p['stock'] <= 0 ? 'Rupture' : $p['stock'] . ' restant(s)' ?>
                            </span>
                        </td>
                        <td>
                            <a href="produits.php?edit=<?= (int)$p['id'] ?>"
                               style="color:var(--red-bright);font-size:0.85rem">
                                Modifier →
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

    </div>
</div>
</main>

<?php require_once '../../includes/footer.php'; ?>

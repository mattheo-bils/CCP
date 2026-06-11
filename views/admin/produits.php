<?php
/**
 * views/admin/produits.php — Gestion des produits
 *
 * Permet à l'admin de :
 *   - Voir tous les produits avec leur stock
 *   - Modifier le stock d'un produit
 *   - Modifier le prix d'un produit
 */

require_once '../../includes/auth_admin.php';

$pageTitle = "Gestion des produits";
$pageCss   = "pages.css";
$basePath  = '../../';

require_once '../../includes/db.php';

$success = false;
$errors  = [];

// ── Modification d'un produit ─────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'update_stock') {
        $id    = (int)$_POST['produit_id'];
        $stock = max(0, (int)$_POST['stock']); // Stock minimum = 0
        $pdo->prepare("UPDATE produits SET stock = ? WHERE id = ?")
            ->execute([$stock, $id]);
        header('Location: produits.php?updated=1');
        exit;
    }

    if ($_POST['action'] === 'update_prix') {
        $id   = (int)$_POST['produit_id'];
        $prix = max(0, (float)$_POST['prix']); // Prix minimum = 0
        $pdo->prepare("UPDATE produits SET prix = ? WHERE id = ?")
            ->execute([$prix, $id]);
        header('Location: produits.php?updated=1');
        exit;
    }
}

// ── Filtre par stock ──────────────────────────────────────────
$filtre = $_GET['filtre'] ?? 'tous';
$where  = '';
if ($filtre === 'rupture')    $where = 'WHERE p.stock = 0';
if ($filtre === 'faible')     $where = 'WHERE p.stock > 0 AND p.stock <= 3';
if ($filtre === 'disponible') $where = 'WHERE p.stock > 3';

// ── Chargement des produits ───────────────────────────────────
$produits = $pdo->query("
    SELECT p.id, p.titre, p.auteur, p.tome, p.prix, p.stock,
           c.nom AS categorie
    FROM produits p
    JOIN categories c ON c.id = p.categorie_id
    $where
    ORDER BY p.titre, p.tome
")->fetchAll();

require_once '../../includes/header.php';
?>

<main>
<div class="profil-page">
    <div class="profil-header">
        <div class="profil-avatar" style="background:linear-gradient(135deg,var(--red),var(--red-dark))">📚</div>
        <div>
            <h1>Gestion des produits</h1>
            <p class="profil-since">
                <a href="dashboard.php" style="color:var(--grey-400)">← Dashboard</a>
            </p>
        </div>
    </div>

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert-success" style="margin-bottom:20px">✓ Produit mis à jour.</div>
    <?php endif; ?>

    <!-- Filtres -->
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px">
        <?php
        $filtres = [
            'tous'        => 'Tous',
            'rupture'     => '🔴 Rupture',
            'faible'      => '🟡 Stock faible',
            'disponible'  => '🟢 Disponible',
        ];
        foreach ($filtres as $slug => $label): ?>
        <a href="produits.php?filtre=<?= $slug ?>"
           class="btn <?= $filtre === $slug ? 'btn-primary' : 'btn-outline' ?>"
           style="padding:8px 16px;font-size:0.85rem">
            <?= $label ?>
        </a>
        <?php endforeach; ?>
    </div>

    <div class="profil-card profil-card-full">
        <h2><?= count($produits) ?> produit<?= count($produits) > 1 ? 's' : '' ?></h2>
        <table class="commandes-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Titre</th>
                    <th>Auteur</th>
                    <th>Catégorie</th>
                    <th>Tome</th>
                    <th>Prix</th>
                    <th>Stock</th>
                    <th>Modifier stock</th>
                    <th>Modifier prix</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produits as $p):
                    $enRupture  = $p['stock'] <= 0;
                    $stockFaible = $p['stock'] > 0 && $p['stock'] <= 3;
                ?>
                <tr>
                    <td style="color:var(--grey-400)"><?= (int)$p['id'] ?></td>
                    <td><strong><?= htmlspecialchars($p['titre']) ?></strong></td>
                    <td style="color:var(--grey-400);font-size:0.85rem">
                        <?= htmlspecialchars($p['auteur']) ?>
                    </td>
                    <td style="font-size:0.85rem"><?= htmlspecialchars($p['categorie']) ?></td>
                    <td>T.<?= (int)$p['tome'] ?></td>
                    <td style="color:var(--gold-light);font-weight:700">
                        <?= number_format($p['prix'], 2, ',', '') ?> €
                    </td>
                    <td>
                        <!-- Affichage coloré selon l'état du stock -->
                        <span style="font-weight:700;color:<?=
                            $enRupture   ? 'var(--red-bright)'  :
                            ($stockFaible ? 'var(--gold-light)'  : '#2ecc71')
                        ?>">
                            <?= $enRupture ? 'Rupture' : $p['stock'] ?>
                        </span>
                    </td>
                    <td>
                        <!-- Formulaire modification stock -->
                        <form method="post" action="produits.php"
                              style="display:flex;gap:4px;align-items:center">
                            <input type="hidden" name="action"     value="update_stock">
                            <input type="hidden" name="produit_id" value="<?= (int)$p['id'] ?>">
                            <input type="number" name="stock"
                                   value="<?= (int)$p['stock'] ?>"
                                   min="0" max="9999" step="1"
                                   style="width:65px;background:var(--navy-mid);
                                          border:1px solid rgba(255,255,255,0.1);
                                          color:var(--white);border-radius:6px;
                                          padding:4px 6px;font-size:0.85rem">
                            <button type="submit" class="btn btn-primary"
                                    style="padding:4px 10px;font-size:0.8rem">✓</button>
                        </form>
                    </td>
                    <td>
                        <!-- Formulaire modification prix -->
                        <form method="post" action="produits.php"
                              style="display:flex;gap:4px;align-items:center">
                            <input type="hidden" name="action"     value="update_prix">
                            <input type="hidden" name="produit_id" value="<?= (int)$p['id'] ?>">
                            <input type="number" name="prix"
                                   value="<?= number_format($p['prix'], 2, '.', '') ?>"
                                   min="0" max="999" step="0.01"
                                   style="width:75px;background:var(--navy-mid);
                                          border:1px solid rgba(255,255,255,0.1);
                                          color:var(--white);border-radius:6px;
                                          padding:4px 6px;font-size:0.85rem">
                            <button type="submit" class="btn btn-primary"
                                    style="padding:4px 10px;font-size:0.8rem">✓</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</main>

<?php require_once '../../includes/footer.php'; ?>

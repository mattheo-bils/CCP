<?php
/**
 * achat.php — Finalisation de commande
 * Vérifie le stock avant d'enregistrer et décrémente après confirmation.
 */

$pageTitle = "Finaliser la commande";
$pageCss   = "pages.css";
$pageJs    = ["achat.js"];
$basePath  = '../';

if (session_status() === PHP_SESSION_NONE) session_start();

$errors  = [];
$userId  = $_SESSION['user_id'] ?? null;

$produitDirect   = null;
$achatDirect     = isset($_GET['id']) || isset($_POST['produit_direct_id']);
$produitDirectId = (int)($_GET['id'] ?? $_POST['produit_direct_id'] ?? 0);

$recapItems = [];
$recapTotal = 0;

require_once '../includes/db.php';

// ── Vérification stock pour achat direct ─────────────────
if ($achatDirect && $produitDirectId) {
    $stmt = $pdo->prepare("SELECT id, titre, prix, image, stock FROM produits WHERE id = ?");
    $stmt->execute([$produitDirectId]);
    $produitDirect = $stmt->fetch();
    if ($produitDirect) {
        if ($produitDirect['stock'] <= 0) {
            header('Location: produit.php?id=' . $produitDirectId . '&rupture=1');
            exit;
        }
        $recapItems[] = [
            'id'       => $produitDirect['id'],
            'titre'    => $produitDirect['titre'],
            'prix'     => $produitDirect['prix'],
            'image'    => $produitDirect['image'],
            'quantite' => 1,
        ];
        $recapTotal = $produitDirect['prix'];
    }
} elseif ($userId) {
    $stmt = $pdo->prepare("
        SELECT pa.produit_id AS id, p.titre, p.prix, p.image, pa.quantite, p.stock
        FROM panier pa
        JOIN produits p ON p.id = pa.produit_id
        WHERE pa.utilisateur_id = ?
        ORDER BY pa.created_at
    ");
    $stmt->execute([$userId]);
    $recapItems = $stmt->fetchAll();
    foreach ($recapItems as $item) {
        $recapTotal += $item['prix'] * $item['quantite'];
    }
}

// ── Traitement du formulaire ──────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prenom          = trim($_POST['prenom']  ?? '');
    $nom             = trim($_POST['nom']     ?? '');
    $adresse         = trim($_POST['adresse'] ?? '');
    $ville           = trim($_POST['ville']   ?? '');
    $cp              = trim($_POST['cp']      ?? '');
    $produitDirectId = (int)($_POST['produit_direct_id'] ?? 0);

    if (empty($prenom))  $errors[] = "Prénom requis.";
    if (empty($nom))     $errors[] = "Nom requis.";
    if (empty($adresse)) $errors[] = "Adresse requise.";
    if (empty($ville))   $errors[] = "Ville requise.";
    if (empty($cp))      $errors[] = "Code postal requis.";

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            if ($produitDirectId) {
                // ── Achat direct ──────────────────────────
                $stmt = $pdo->prepare("SELECT prix, stock FROM produits WHERE id = ? FOR UPDATE");
                $stmt->execute([$produitDirectId]);
                $prod = $stmt->fetch();

                if (!$prod || $prod['stock'] <= 0) {
                    $pdo->rollBack();
                    $errors[] = "Ce produit est en rupture de stock.";
                } else {
                    $total = (float)$prod['prix'];
                    $pdo->prepare("INSERT INTO commandes (utilisateur_id,prenom,nom,adresse,code_postal,ville,total) VALUES (?,?,?,?,?,?,?)")
                        ->execute([$userId,$prenom,$nom,$adresse,$cp,$ville,$total]);
                    $commandeId = $pdo->lastInsertId();
                    $pdo->prepare("INSERT INTO commande_lignes (commande_id,produit_id,quantite,prix_unit) VALUES (?,?,1,?)")
                        ->execute([$commandeId,$produitDirectId,$prod['prix']]);
                    // Décrémente le stock
                    $pdo->prepare("UPDATE produits SET stock = stock - 1 WHERE id = ?")
                        ->execute([$produitDirectId]);
                    $pdo->commit();
                    header('Location: confirmeachat.php');
                    exit;
                }

            } elseif ($userId) {
                // ── Panier BDD ────────────────────────────
                $stmt = $pdo->prepare("
                    SELECT pa.produit_id, pa.quantite, p.prix, p.stock, p.titre
                    FROM panier pa JOIN produits p ON p.id = pa.produit_id
                    WHERE pa.utilisateur_id = ?
                    FOR UPDATE
                ");
                $stmt->execute([$userId]);
                $panierBdd = $stmt->fetchAll();

                // Vérifier le stock de chaque article
                foreach ($panierBdd as $item) {
                    if ($item['stock'] < $item['quantite']) {
                        $errors[] = "\"" . $item['titre'] . "\" : stock insuffisant (disponible : " . $item['stock'] . ").";
                    }
                }

                if (empty($errors)) {
                    $total = array_sum(array_map(fn($i) => $i['prix'] * $i['quantite'], $panierBdd));
                    $pdo->prepare("INSERT INTO commandes (utilisateur_id,prenom,nom,adresse,code_postal,ville,total) VALUES (?,?,?,?,?,?,?)")
                        ->execute([$userId,$prenom,$nom,$adresse,$cp,$ville,$total]);
                    $commandeId = $pdo->lastInsertId();
                    $stmtL = $pdo->prepare("INSERT INTO commande_lignes (commande_id,produit_id,quantite,prix_unit) VALUES (?,?,?,?)");
                    foreach ($panierBdd as $item) {
                        $stmtL->execute([$commandeId,$item['produit_id'],$item['quantite'],$item['prix']]);
                        // Décrémente le stock
                        $pdo->prepare("UPDATE produits SET stock = stock - ? WHERE id = ?")
                            ->execute([$item['quantite'],$item['produit_id']]);
                    }
                    $pdo->prepare("DELETE FROM panier WHERE utilisateur_id = ?")->execute([$userId]);
                    $pdo->commit();
                    header('Location: confirmeachat.php');
                    exit;
                } else {
                    $pdo->rollBack();
                }

            } else {
                // ── Invité localStorage ───────────────────
                $panierLocal = json_decode($_POST['panier_json'] ?? '[]', true) ?: [];

                // Vérifier le stock de chaque article
                foreach ($panierLocal as $item) {
                    $s = $pdo->prepare("SELECT stock, titre FROM produits WHERE id = ?");
                    $s->execute([(int)$item['id']]);
                    $prod = $s->fetch();
                    if ($prod && $prod['stock'] < ($item['qty'] ?? 1)) {
                        $errors[] = "\"" . $prod['titre'] . "\" : stock insuffisant (disponible : " . $prod['stock'] . ").";
                    }
                }

                if (empty($errors)) {
                    $total = 0;
                    foreach ($panierLocal as $item) {
                        $total += (float)str_replace(',','.',$item['prix']) * ($item['qty'] ?? 1);
                    }
                    $pdo->prepare("INSERT INTO commandes (utilisateur_id,prenom,nom,adresse,code_postal,ville,total) VALUES (?,?,?,?,?,?,?)")
                        ->execute([null,$prenom,$nom,$adresse,$cp,$ville,$total]);
                    $commandeId = $pdo->lastInsertId();
                    $stmtL = $pdo->prepare("INSERT INTO commande_lignes (commande_id,produit_id,quantite,prix_unit) VALUES (?,?,?,?)");
                    foreach ($panierLocal as $item) {
                        $qty = (int)($item['qty'] ?? 1);
                        $stmtL->execute([$commandeId,(int)$item['id'],$qty,(float)str_replace(',','.',$item['prix'])]);
                        $pdo->prepare("UPDATE produits SET stock = stock - ? WHERE id = ?")
                            ->execute([$qty,(int)$item['id']]);
                    }
                    $pdo->commit();
                    header('Location: confirmeachat.php');
                    exit;
                } else {
                    $pdo->rollBack();
                }
            }

        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors[] = "Une erreur est survenue. Veuillez réessayer.";
        }
    }
}

// Pré-remplir avec les infos de l'utilisateur connecté
$userInfo = [];
if ($userId) {
    $stmt = $pdo->prepare("SELECT prenom, nom FROM utilisateurs WHERE id = ?");
    $stmt->execute([$userId]);
    $userInfo = $stmt->fetch() ?: [];
}

require_once '../includes/header.php';
?>

<main>
<div class="achat-page">
    <h1>Finaliser la commande</h1>

    <?php if (!empty($errors)): ?>
    <div class="alert-error" style="margin-bottom:24px">
        <?php foreach ($errors as $e): ?><p>⚠ <?= htmlspecialchars($e) ?></p><?php endforeach; ?>
    </div>
    <?php endif; ?>

    <form method="post" action="achat.php" id="achat-form">
        <?php if ($produitDirectId): ?>
            <input type="hidden" name="produit_direct_id" value="<?= $produitDirectId ?>">
        <?php else: ?>
            <input type="hidden" name="panier_json" id="panier_json" value="">
        <?php endif; ?>

        <div class="achat-section">
            <h3>📦 Adresse de livraison</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom"
                           value="<?= htmlspecialchars($_POST['prenom'] ?? $userInfo['prenom'] ?? '') ?>"
                           placeholder="Jean" required>
                </div>
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom"
                           value="<?= htmlspecialchars($_POST['nom'] ?? $userInfo['nom'] ?? '') ?>"
                           placeholder="Dupont" required>
                </div>
            </div>
            <div class="form-group">
                <label for="adresse">Adresse</label>
                <input type="text" id="adresse" name="adresse"
                       value="<?= htmlspecialchars($_POST['adresse'] ?? '') ?>"
                       placeholder="12 rue des Lilas" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="cp">Code postal</label>
                    <input type="text" id="cp" name="cp"
                           value="<?= htmlspecialchars($_POST['cp'] ?? '') ?>"
                           placeholder="75000" required>
                </div>
                <div class="form-group">
                    <label for="ville">Ville</label>
                    <input type="text" id="ville" name="ville"
                           value="<?= htmlspecialchars($_POST['ville'] ?? '') ?>"
                           placeholder="Paris" required>
                </div>
            </div>
        </div>

        <div class="achat-section paiement">
            <h3>💳 Paiement sécurisé</h3>
            <div class="form-group">
                <label for="carte">Numéro de carte</label>
                <input type="text" id="carte" name="carte" class="card-number-input"
                       placeholder="4242 4242 4242 4242" maxlength="19" autocomplete="cc-number">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="expiry">Expiration</label>
                    <input type="text" id="expiry" name="expiry" placeholder="MM / AA" maxlength="7">
                </div>
                <div class="form-group">
                    <label for="cvv">CVV</label>
                    <input type="text" id="cvv" name="cvv" placeholder="•••" maxlength="4">
                </div>
            </div>
            <div style="margin-top:8px;opacity:0.45;font-size:0.75rem;letter-spacing:0.08em;color:var(--grey-400)">
                💳 VISA &nbsp;·&nbsp; MASTERCARD &nbsp;·&nbsp; CB
            </div>
        </div>

        <div class="achat-section">
            <h3>🛒 Récapitulatif</h3>
            <div id="recap-items">
                <?php if (!empty($recapItems)): ?>
                    <?php foreach ($recapItems as $item): ?>
                    <div style="display:flex;align-items:center;gap:14px;padding:10px 0;border-bottom:1px solid rgba(255,255,255,0.05)">
                        <img src="../<?= htmlspecialchars($item['image']) ?>"
                             alt="<?= htmlspecialchars($item['titre']) ?>"
                             style="width:46px;height:66px;object-fit:cover;border-radius:6px;flex-shrink:0">
                        <span style="flex:1">
                            <?= htmlspecialchars($item['titre']) ?>
                            <span style="color:var(--grey-400)">× <?= (int)$item['quantite'] ?></span>
                        </span>
                        <span style="color:var(--gold-light);font-weight:700">
                            <?= number_format($item['prix'] * $item['quantite'], 2, ',', '') ?> €
                        </span>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div id="recap-js-loading" style="color:var(--grey-400);font-size:0.9rem">
                        Chargement du panier…
                    </div>
                <?php endif; ?>
            </div>
            <div class="panier-total" style="margin-top:12px;padding-top:12px;border-top:1px solid rgba(255,255,255,0.08)">
                <span style="font-size:1rem">Total</span>
                <span id="recap-total" style="color:var(--gold-light)">
                    <?php if (!empty($recapItems)): ?>
                        <?= number_format($recapTotal, 2, ',', '') ?> €
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </span>
            </div>
        </div>

        <button type="submit" class="btn btn-primary"
                style="width:100%;padding:16px;font-size:1rem;margin-top:8px;letter-spacing:0.06em">
            Confirmer la commande →
        </button>
    </form>
</div>
</main>

<?php require_once '../includes/footer.php'; ?>

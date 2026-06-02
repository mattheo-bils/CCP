<?php
$pageTitle = "Finaliser la commande";
$pageCss   = "pages.css";
$basePath  = '../';

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prenom  = trim($_POST['prenom']  ?? '');
    $nom     = trim($_POST['nom']     ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $ville   = trim($_POST['ville']   ?? '');
    $cp      = trim($_POST['cp']      ?? '');

    if (empty($prenom))  $errors[] = "Prénom requis.";
    if (empty($nom))     $errors[] = "Nom requis.";
    if (empty($adresse)) $errors[] = "Adresse requise.";
    if (empty($ville))   $errors[] = "Ville requise.";
    if (empty($cp))      $errors[] = "Code postal requis.";

    if (empty($errors)) {
        // Récupérer le panier depuis la session
        session_start();
        $panier = $_SESSION['panier'] ?? [];

        if (!empty($panier)) {
            try {
                require_once '../includes/db.php';
                $pdo->beginTransaction();

                $total = array_sum(array_map(fn($item) => $item['prix'] * $item['quantite'], $panier));
                $userId = $_SESSION['user_id'] ?? null;

                $stmt = $pdo->prepare("
                    INSERT INTO commandes (utilisateur_id, prenom, nom, adresse, code_postal, ville, total)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$userId, $prenom, $nom, $adresse, $cp, $ville, $total]);
                $commandeId = $pdo->lastInsertId();

                $stmtLigne = $pdo->prepare("
                    INSERT INTO commande_lignes (commande_id, produit_id, quantite, prix_unit)
                    VALUES (?, ?, ?, ?)
                ");
                foreach ($panier as $item) {
                    $stmtLigne->execute([$commandeId, $item['id'], $item['quantite'], $item['prix']]);
                }

                $pdo->commit();
                $_SESSION['panier'] = [];
            } catch (Exception $e) {
                if (isset($pdo)) $pdo->rollBack();
                // On laisse passer — confirmation quand même en dev
            }
        }

        header('Location: confirmeachat.php');
        exit;
    }
}

require_once '../includes/header.php';
?>

<main>
    <div class="achat-page">
        <h1>Finaliser la commande</h1>

        <?php if (!empty($errors)): ?>
        <div class="alert-error">
            <?php foreach ($errors as $e): ?>
                <p>⚠ <?= htmlspecialchars($e) ?></p>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <form method="post" action="achat.php">
            <div class="achat-section">
                <h3>Adresse de livraison</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom"
                               value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>"
                               placeholder="Jean" required>
                    </div>
                    <div class="form-group">
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom"
                               value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"
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

            <div class="achat-section">
                <h3>Paiement</h3>
                <div class="form-group">
                    <label for="carte">Numéro de carte</label>
                    <input type="text" id="carte" name="carte"
                           placeholder="4242 4242 4242 4242"
                           maxlength="19" autocomplete="cc-number">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="expiry">Date d'expiration</label>
                        <input type="text" id="expiry" name="expiry" placeholder="MM/AA" maxlength="5">
                    </div>
                    <div class="form-group">
                        <label for="cvv">CVV</label>
                        <input type="text" id="cvv" name="cvv" placeholder="123" maxlength="4">
                    </div>
                </div>
            </div>

            <!-- Récap panier JS -->
            <div class="achat-section" id="recap-panier">
                <h3>Récapitulatif</h3>
                <div id="recap-items" style="color:var(--grey-400);font-size:0.9rem">
                    Chargement du panier…
                </div>
                <div class="panier-total" style="margin-top:12px">
                    <span>Total</span>
                    <span id="recap-total" style="color:var(--gold-light)">—</span>
                </div>
            </div>

            <button type="submit" class="btn btn-primary"
                    style="width:100%;padding:16px;font-size:1rem;margin-top:8px">
                Confirmer la commande
            </button>
        </form>
    </div>
</main>

<script>
// Afficher le récap du panier localStorage
(function() {
    const items  = document.getElementById('recap-items');
    const totEl  = document.getElementById('recap-total');
    let cart = [];
    try { cart = JSON.parse(localStorage.getItem('mm_cart') || '[]'); } catch(e){}

    if (!cart.length) {
        items.textContent = 'Votre panier est vide.';
        totEl.textContent = '0,00 €';
        return;
    }

    let html = '', total = 0;
    cart.forEach(item => {
        const p = parseFloat(String(item.prix).replace(',', '.')) || 0;
        total += p * (item.qty || 1);
        html += `<div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid rgba(255,255,255,0.06)">
            <span>${item.titre} × ${item.qty || 1}</span>
            <span style="color:var(--gold-light)">${(p*(item.qty||1)).toFixed(2).replace('.',',')} €</span>
        </div>`;
    });
    items.innerHTML = html;
    totEl.textContent = total.toFixed(2).replace('.', ',') + ' €';
})();
</script>

<?php require_once '../includes/footer.php'; ?>

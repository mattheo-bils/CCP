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
        if (session_status() === PHP_SESSION_NONE) session_start();
        $panier = $_SESSION['panier'] ?? [];

        if (!empty($panier)) {
            try {
                require_once '../includes/db.php';
                $pdo->beginTransaction();
                $total  = array_sum(array_map(fn($i) => $i['prix'] * $i['quantite'], $panier));
                $userId = $_SESSION['user_id'] ?? null;
                $stmt   = $pdo->prepare("INSERT INTO commandes (utilisateur_id,prenom,nom,adresse,code_postal,ville,total) VALUES (?,?,?,?,?,?,?)");
                $stmt->execute([$userId,$prenom,$nom,$adresse,$cp,$ville,$total]);
                $commandeId = $pdo->lastInsertId();
                $stmtL = $pdo->prepare("INSERT INTO commande_lignes (commande_id,produit_id,quantite,prix_unit) VALUES (?,?,?,?)");
                foreach ($panier as $item) $stmtL->execute([$commandeId,$item['id'],$item['quantite'],$item['prix']]);
                $pdo->commit();
                $_SESSION['panier'] = [];
            } catch (Exception $e) {
                if (isset($pdo)) $pdo->rollBack();
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
    <div class="alert-error" style="margin-bottom:24px">
        <?php foreach ($errors as $e): ?><p>⚠ <?= htmlspecialchars($e) ?></p><?php endforeach; ?>
    </div>
    <?php endif; ?>

    <form method="post" action="achat.php">

        <!-- Livraison -->
        <div class="achat-section">
            <h3>📦 Adresse de livraison</h3>
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

        <!-- Paiement -->
        <div class="achat-section paiement">
            <h3>💳 Paiement sécurisé</h3>
            <div class="form-group">
                <label for="carte">Numéro de carte</label>
                <input type="text" id="carte" name="carte" class="card-number-input"
                       placeholder="4242 4242 4242 4242" maxlength="19"
                       autocomplete="cc-number"
                       oninput="this.value=this.value.replace(/\D/g,'').replace(/(.{4})/g,'$1 ').trim()">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="expiry">Expiration</label>
                    <input type="text" id="expiry" name="expiry"
                           placeholder="MM / AA" maxlength="7"
                           oninput="formatExpiry(this)">
                </div>
                <div class="form-group">
                    <label for="cvv">CVV</label>
                    <input type="text" id="cvv" name="cvv"
                           placeholder="•••" maxlength="4"
                           oninput="this.value=this.value.replace(/\D/g,'')">
                </div>
            </div>
            <div class="card-logos" style="display:flex;gap:8px;margin-top:4px;opacity:0.5;font-size:0.75rem;letter-spacing:0.08em;color:var(--grey-400)">
                💳 VISA &nbsp;·&nbsp; MASTERCARD &nbsp;·&nbsp; CB
            </div>
        </div>

        <!-- Récap -->
        <div class="achat-section">
            <h3>🛒 Récapitulatif</h3>
            <div id="recap-items" style="color:var(--grey-400);font-size:0.9rem;min-height:40px">
                Chargement du panier…
            </div>
            <div class="panier-total" style="margin-top:12px;padding-top:12px;border-top:1px solid rgba(255,255,255,0.08)">
                <span style="font-size:1rem">Total</span>
                <span id="recap-total" style="color:var(--gold-light)">—</span>
            </div>
        </div>

        <button type="submit" class="btn btn-primary"
                style="width:100%;padding:16px;font-size:1rem;margin-top:8px;letter-spacing:0.06em">
            Confirmer la commande →
        </button>
    </form>
</div>
</main>

<script>
function formatExpiry(input) {
    let v = input.value.replace(/\D/g,'');
    if (v.length >= 2) v = v.slice(0,2) + ' / ' + v.slice(2,4);
    input.value = v;
}

(function() {
    const items = document.getElementById('recap-items');
    const totEl = document.getElementById('recap-total');
    let cart = [];
    try { cart = JSON.parse(localStorage.getItem('mm_cart') || '[]'); } catch(e){}

    if (!cart.length) {
        items.textContent = 'Votre panier est vide.';
        totEl.textContent = '0,00 €';
        return;
    }

    let html = '', total = 0;
    cart.forEach(item => {
        const p = parseFloat(String(item.prix).replace(',','.')) || 0;
        total += p * (item.qty || 1);
        html += `<div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(255,255,255,0.05)">
            <span>${item.titre} <span style="color:var(--grey-400)">× ${item.qty||1}</span></span>
            <span style="color:var(--gold-light);font-weight:700">${(p*(item.qty||1)).toFixed(2).replace('.',',')} €</span>
        </div>`;
    });
    items.innerHTML = html;
    totEl.textContent = total.toFixed(2).replace('.',',') + ' €';
})();
</script>

<?php require_once '../includes/footer.php'; ?>

<?php
/**
 * views/admin/messages.php — Gestion des messages de contact
 *
 * Affiche les messages reçus via le formulaire de contact.
 * Permet de les marquer comme lus.
 */

require_once '../../includes/auth_admin.php';

$pageTitle = "Messages de contact";
$pageCss   = "pages.css";
$basePath  = '../../';

require_once '../../includes/db.php';

// ── Marquer un message comme lu ───────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message_id'])) {
    $pdo->prepare("UPDATE messages_contact SET lu = 1 WHERE id = ?")
        ->execute([(int)$_POST['message_id']]);
    header('Location: messages.php');
    exit;
}

// ── Chargement des messages (non lus en premier) ──────────────
$messages = $pdo->query("
    SELECT id, nom, prenom, email, telephone, message, lu, created_at
    FROM messages_contact
    ORDER BY lu ASC, created_at DESC
")->fetchAll();

$nbNonLus = array_reduce($messages, fn($c, $m) => $c + ($m['lu'] ? 0 : 1), 0);

require_once '../../includes/header.php';
?>

<main>
<div class="profil-page">
    <div class="profil-header">
        <div class="profil-avatar" style="background:linear-gradient(135deg,var(--red),var(--red-dark))">✉</div>
        <div>
            <h1>Messages de contact</h1>
            <p class="profil-since">
                <?= $nbNonLus ?> non lu<?= $nbNonLus > 1 ? 's' : '' ?>
                &nbsp;·&nbsp;
                <a href="dashboard.php" style="color:var(--grey-400)">← Dashboard</a>
            </p>
        </div>
    </div>

    <?php if (empty($messages)): ?>
        <div class="profil-card">
            <p style="color:var(--grey-400)">Aucun message reçu.</p>
        </div>
    <?php else: ?>
        <?php foreach ($messages as $msg): ?>
        <div class="profil-card profil-card-full" style="margin-bottom:16px;
             <?= !$msg['lu'] ? 'border-color:var(--red);' : 'opacity:0.7' ?>">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px">
                <div>
                    <!-- Nom et prénom de l'expéditeur -->
                    <strong style="font-size:1rem">
                        <?= htmlspecialchars($msg['prenom'] . ' ' . $msg['nom']) ?>
                    </strong>
                    <!-- Badge non lu -->
                    <?php if (!$msg['lu']): ?>
                        <span style="background:var(--red);color:#fff;
                                     padding:2px 8px;border-radius:10px;
                                     font-size:0.7rem;margin-left:8px">NOUVEAU</span>
                    <?php endif; ?>
                    <div style="color:var(--grey-400);font-size:0.85rem;margin-top:4px">
                        <!-- Email cliquable -->
                        <a href="mailto:<?= htmlspecialchars($msg['email']) ?>"
                           style="color:var(--gold-light)">
                            <?= htmlspecialchars($msg['email']) ?>
                        </a>
                        <?php if ($msg['telephone']): ?>
                            &nbsp;·&nbsp; <?= htmlspecialchars($msg['telephone']) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div style="text-align:right;font-size:0.82rem;color:var(--grey-400)">
                    <?= date('d/m/Y à H:i', strtotime($msg['created_at'])) ?>
                </div>
            </div>

            <!-- Contenu du message -->
            <p style="line-height:1.7;color:var(--grey-100)">
                <?= nl2br(htmlspecialchars($msg['message'])) ?>
            </p>

            <!-- Bouton marquer comme lu -->
            <?php if (!$msg['lu']): ?>
            <form method="post" action="messages.php" style="margin-top:12px">
                <input type="hidden" name="message_id" value="<?= (int)$msg['id'] ?>">
                <button type="submit" class="btn btn-outline"
                        style="padding:6px 14px;font-size:0.85rem">
                    ✓ Marquer comme lu
                </button>
            </form>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</main>

<?php require_once '../../includes/footer.php'; ?>

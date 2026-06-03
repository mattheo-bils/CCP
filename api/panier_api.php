<?php
/**
 * api/panier.php — CRUD panier BDD
 *
 * GET    ?action=list                        → liste les articles du panier
 * POST   action=add    produit_id= quantite= → ajoute ou incrémente
 * POST   action=remove produit_id=           → décrémente ou supprime
 * POST   action=delete produit_id=           → supprime complètement
 * POST   action=clear                        → vide le panier
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

// Utilisateur non connecté → répondre vide (le JS utilisera localStorage)
if (empty($_SESSION['user_id'])) {
    echo json_encode(['connected' => false]);
    exit;
}

require_once '../includes/db.php';
$userId = (int)$_SESSION['user_id'];

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {

        // ── Lister ────────────────────────────────────────
        case 'list':
            $stmt = $pdo->prepare("
                SELECT pa.produit_id AS id, p.titre, p.prix, p.image,
                       pa.quantite AS qty
                FROM panier pa
                JOIN produits p ON p.id = pa.produit_id
                WHERE pa.utilisateur_id = ?
                ORDER BY pa.created_at
            ");
            $stmt->execute([$userId]);
            echo json_encode(['connected' => true, 'items' => $stmt->fetchAll()]);
            break;

        // ── Ajouter / incrémenter ─────────────────────────
        case 'add':
            $produitId = (int)($_POST['produit_id'] ?? 0);
            $quantite  = max(1, (int)($_POST['quantite'] ?? 1));
            if (!$produitId) { http_response_code(400); echo json_encode(['error' => 'produit_id requis']); exit; }

            $stmt = $pdo->prepare("
                INSERT INTO panier (utilisateur_id, produit_id, quantite)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE quantite = quantite + VALUES(quantite)
            ");
            $stmt->execute([$userId, $produitId, $quantite]);
            echo json_encode(['connected' => true, 'success' => true]);
            break;

        // ── Décrémenter ───────────────────────────────────
        case 'remove':
            $produitId = (int)($_POST['produit_id'] ?? 0);
            if (!$produitId) { http_response_code(400); echo json_encode(['error' => 'produit_id requis']); exit; }

            // Décrémenter
            $pdo->prepare("
                UPDATE panier SET quantite = quantite - 1
                WHERE utilisateur_id = ? AND produit_id = ?
            ")->execute([$userId, $produitId]);

            // Supprimer si quantite <= 0
            $pdo->prepare("
                DELETE FROM panier
                WHERE utilisateur_id = ? AND produit_id = ? AND quantite <= 0
            ")->execute([$userId, $produitId]);

            echo json_encode(['connected' => true, 'success' => true]);
            break;

        // ── Supprimer complètement ────────────────────────
        case 'delete':
            $produitId = (int)($_POST['produit_id'] ?? 0);
            if (!$produitId) { http_response_code(400); echo json_encode(['error' => 'produit_id requis']); exit; }

            $pdo->prepare("
                DELETE FROM panier WHERE utilisateur_id = ? AND produit_id = ?
            ")->execute([$userId, $produitId]);

            echo json_encode(['connected' => true, 'success' => true]);
            break;

        // ── Vider le panier ───────────────────────────────
        case 'clear':
            $pdo->prepare("DELETE FROM panier WHERE utilisateur_id = ?")->execute([$userId]);
            echo json_encode(['connected' => true, 'success' => true]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'action invalide']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur']);
}

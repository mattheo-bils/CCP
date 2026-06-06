<?php
/**
 * api/panier.php — API REST panier avec vérification du stock
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

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
                       pa.quantite AS qty, p.stock
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

            if (!$produitId) {
                http_response_code(400);
                echo json_encode(['error' => 'produit_id requis']);
                exit;
            }

            // Vérifier le stock disponible
            $stockStmt = $pdo->prepare("SELECT stock FROM produits WHERE id = ?");
            $stockStmt->execute([$produitId]);
            $stock = (int)$stockStmt->fetchColumn();

            // Vérifier la quantité déjà dans le panier
            $qtyStmt = $pdo->prepare("SELECT quantite FROM panier WHERE utilisateur_id = ? AND produit_id = ?");
            $qtyStmt->execute([$userId, $produitId]);
            $qtyPanier = (int)($qtyStmt->fetchColumn() ?: 0);

            if ($stock <= 0) {
                echo json_encode(['connected' => true, 'success' => false, 'error' => 'rupture']);
                exit;
            }
            if ($qtyPanier + $quantite > $stock) {
                echo json_encode(['connected' => true, 'success' => false, 'error' => 'stock_insuffisant', 'stock' => $stock]);
                exit;
            }

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
            if (!$produitId) {
                http_response_code(400);
                echo json_encode(['error' => 'produit_id requis']);
                exit;
            }
            $pdo->prepare("
                UPDATE panier SET quantite = quantite - 1
                WHERE utilisateur_id = ? AND produit_id = ?
            ")->execute([$userId, $produitId]);
            $pdo->prepare("
                DELETE FROM panier
                WHERE utilisateur_id = ? AND produit_id = ? AND quantite <= 0
            ")->execute([$userId, $produitId]);
            echo json_encode(['connected' => true, 'success' => true]);
            break;

        // ── Supprimer complètement ────────────────────────
        case 'delete':
            $produitId = (int)($_POST['produit_id'] ?? 0);
            if (!$produitId) {
                http_response_code(400);
                echo json_encode(['error' => 'produit_id requis']);
                exit;
            }
            $pdo->prepare("DELETE FROM panier WHERE utilisateur_id = ? AND produit_id = ?")
                ->execute([$userId, $produitId]);
            echo json_encode(['connected' => true, 'success' => true]);
            break;

        // ── Vider ─────────────────────────────────────────
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

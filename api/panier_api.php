<?php
/**
 * api/panier.php — API REST pour la gestion du panier en BDD
 *
 * Accessible uniquement par les utilisateurs connectés (session active).
 * Si non connecté, retourne { connected: false } → le JS utilisera localStorage.
 *
 * Actions disponibles :
 *   GET  ?action=list                         → Liste les articles du panier
 *   POST action=add    produit_id= quantite=  → Ajoute ou incrémente un article
 *   POST action=remove produit_id=            → Décrémente (ou supprime si quantité = 0)
 *   POST action=delete produit_id=            → Supprime complètement un article
 *   POST action=clear                         → Vide tout le panier
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

// ── Vérification de la connexion ──────────────────────────
// Si l'utilisateur n'est pas connecté, on répond "not connected"
// Le JS client basculera alors sur le localStorage
if (empty($_SESSION['user_id'])) {
    echo json_encode(['connected' => false]);
    exit;
}

require_once '../includes/db.php';
$userId = (int)$_SESSION['user_id'];

// L'action peut venir de GET (list) ou POST (add, remove, delete, clear)
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {

        // ── Lister les articles du panier ─────────────────
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

        // ── Ajouter ou incrémenter un article ─────────────
        case 'add':
            $produitId = (int)($_POST['produit_id'] ?? 0);
            $quantite  = max(1, (int)($_POST['quantite'] ?? 1));

            if (!$produitId) {
                http_response_code(400);
                echo json_encode(['error' => 'produit_id requis']);
                exit;
            }

            // INSERT ou UPDATE si déjà présent (clé unique user+produit)
            $stmt = $pdo->prepare("
                INSERT INTO panier (utilisateur_id, produit_id, quantite)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE quantite = quantite + VALUES(quantite)
            ");
            $stmt->execute([$userId, $produitId, $quantite]);
            echo json_encode(['connected' => true, 'success' => true]);
            break;

        // ── Décrémenter la quantité d'un article ──────────
        case 'remove':
            $produitId = (int)($_POST['produit_id'] ?? 0);

            if (!$produitId) {
                http_response_code(400);
                echo json_encode(['error' => 'produit_id requis']);
                exit;
            }

            // Décrémente de 1
            $pdo->prepare("
                UPDATE panier SET quantite = quantite - 1
                WHERE utilisateur_id = ? AND produit_id = ?
            ")->execute([$userId, $produitId]);

            // Supprime si la quantité est tombée à 0 ou moins
            $pdo->prepare("
                DELETE FROM panier
                WHERE utilisateur_id = ? AND produit_id = ? AND quantite <= 0
            ")->execute([$userId, $produitId]);

            echo json_encode(['connected' => true, 'success' => true]);
            break;

        // ── Supprimer complètement un article ─────────────
        case 'delete':
            $produitId = (int)($_POST['produit_id'] ?? 0);

            if (!$produitId) {
                http_response_code(400);
                echo json_encode(['error' => 'produit_id requis']);
                exit;
            }

            $pdo->prepare("
                DELETE FROM panier WHERE utilisateur_id = ? AND produit_id = ?
            ")->execute([$userId, $produitId]);

            echo json_encode(['connected' => true, 'success' => true]);
            break;

        // ── Vider entièrement le panier ───────────────────
        case 'clear':
            $pdo->prepare("DELETE FROM panier WHERE utilisateur_id = ?")
                ->execute([$userId]);
            echo json_encode(['connected' => true, 'success' => true]);
            break;

        // ── Action non reconnue ───────────────────────────
        default:
            http_response_code(400);
            echo json_encode(['error' => 'action invalide']);
    }

} catch (Exception $e) {
    // Erreur serveur : on ne divulgue pas les détails en production
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur']);
}

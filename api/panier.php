<?php
/**
 * api/panier.php — API REST pour la gestion du panier en base de données
 *
 * Gère toutes les opérations sur le panier (liste, ajout, suppression...)
 * Accessible uniquement aux utilisateurs connectés via session PHP.
 * Retourne du JSON dans tous les cas.
 */

// Démarrage de la session pour accéder à $_SESSION
session_start();

// On indique au navigateur que la réponse sera du JSON encodé en UTF-8
header('Content-Type: application/json; charset=utf-8');

// Si l'utilisateur n'est pas connecté, on retourne connected:false
// Le JS côté client utilisera alors le localStorage à la place
if (empty($_SESSION['user_id'])) {
    echo json_encode(['connected' => false]);
    exit; // On arrête l'exécution ici
}

// Connexion à la base de données
require_once '../includes/db.php';

// Récupération de l'ID utilisateur connecté (converti en entier pour sécurité)
$userId = (int)$_SESSION['user_id'];

// L'action peut venir d'une requête GET (ex: ?action=list)
// ou d'une requête POST (ex: action=add)
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {

        // ── Action : lister les articles du panier ────────
        case 'list':
            // Correction automatique : si une quantité dépasse le stock disponible,
            // on la ramène au stock maximum
            $pdo->prepare("
                UPDATE panier pa
                JOIN produits p ON p.id = pa.produit_id
                SET pa.quantite = p.stock
                WHERE pa.utilisateur_id = ? AND pa.quantite > p.stock AND p.stock > 0
            ")->execute([$userId]);

            // Suppression des articles en rupture de stock du panier
            $pdo->prepare("
                DELETE pa FROM panier pa
                JOIN produits p ON p.id = pa.produit_id
                WHERE pa.utilisateur_id = ? AND p.stock = 0
            ")->execute([$userId]);

            // Récupération de tous les articles du panier avec les infos produit
            $stmt = $pdo->prepare("
                SELECT pa.produit_id AS id, -- ID du produit
                       p.titre,             -- Nom du manga
                       p.prix,              -- Prix unitaire
                       p.image,             -- Chemin de l'image
                       pa.quantite AS qty,  -- Quantité dans le panier
                       p.stock              -- Stock disponible (pour bloquer le bouton +)
                FROM panier pa
                JOIN produits p ON p.id = pa.produit_id
                WHERE pa.utilisateur_id = ?
                ORDER BY pa.created_at      -- Du plus ancien au plus récent
            ");
            $stmt->execute([$userId]);

            // On retourne connected:true avec la liste des articles
            echo json_encode(['connected' => true, 'items' => $stmt->fetchAll()]);
            break;

        // ── Action : ajouter ou incrémenter un article ────
        case 'add':
            // Récupération et validation de l'ID produit
            $produitId = (int)($_POST['produit_id'] ?? 0);
            // La quantité doit être au minimum 1
            $quantite  = max(1, (int)($_POST['quantite'] ?? 1));

            // Vérification : l'ID produit est obligatoire
            if (!$produitId) {
                http_response_code(400); // Code HTTP "Bad Request"
                echo json_encode(['error' => 'produit_id requis']);
                exit;
            }

            // Vérification du stock disponible pour ce produit
            $stockStmt = $pdo->prepare("SELECT stock FROM produits WHERE id = ?");
            $stockStmt->execute([$produitId]);
            $stock = (int)$stockStmt->fetchColumn(); // Stock total disponible

            // Vérification de la quantité déjà présente dans le panier
            $qtyStmt = $pdo->prepare("SELECT quantite FROM panier WHERE utilisateur_id = ? AND produit_id = ?");
            $qtyStmt->execute([$userId, $produitId]);
            $qtyPanier = (int)($qtyStmt->fetchColumn() ?: 0); // 0 si pas encore dans le panier

            // Bloquer si le produit est en rupture totale
            if ($stock <= 0) {
                echo json_encode(['connected' => true, 'success' => false, 'error' => 'rupture']);
                exit;
            }

            // Bloquer si l'ajout dépasserait le stock disponible
            if ($qtyPanier + $quantite > $stock) {
                echo json_encode([
                    'connected' => true,
                    'success'   => false,
                    'error'     => 'stock_insuffisant',
                    'stock'     => $stock // On retourne le stock max pour info
                ]);
                exit;
            }

            // INSERT si le produit n'est pas encore dans le panier
            // UPDATE (quantite + x) s'il y est déjà
            // LEAST() garantit que la quantité ne dépasse jamais le stock
            $stmt = $pdo->prepare("
                INSERT INTO panier (utilisateur_id, produit_id, quantite)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    quantite = LEAST(
                        quantite + VALUES(quantite),
                        (SELECT stock FROM produits WHERE id = VALUES(produit_id))
                    )
            ");
            $stmt->execute([$userId, $produitId, $quantite]);
            echo json_encode(['connected' => true, 'success' => true]);
            break;

        // ── Action : décrémenter la quantité d'un article ─
        case 'remove':
            $produitId = (int)($_POST['produit_id'] ?? 0);

            if (!$produitId) {
                http_response_code(400);
                echo json_encode(['error' => 'produit_id requis']);
                exit;
            }

            // Décrémente la quantité de 1
            $pdo->prepare("
                UPDATE panier SET quantite = quantite - 1
                WHERE utilisateur_id = ? AND produit_id = ?
            ")->execute([$userId, $produitId]);

            // Si la quantité est tombée à 0 ou moins, on supprime la ligne
            $pdo->prepare("
                DELETE FROM panier
                WHERE utilisateur_id = ? AND produit_id = ? AND quantite <= 0
            ")->execute([$userId, $produitId]);

            echo json_encode(['connected' => true, 'success' => true]);
            break;

        // ── Action : supprimer complètement un article ────
        case 'delete':
            $produitId = (int)($_POST['produit_id'] ?? 0);

            if (!$produitId) {
                http_response_code(400);
                echo json_encode(['error' => 'produit_id requis']);
                exit;
            }

            // Suppression directe sans vérification de quantité
            $pdo->prepare("
                DELETE FROM panier WHERE utilisateur_id = ? AND produit_id = ?
            ")->execute([$userId, $produitId]);

            echo json_encode(['connected' => true, 'success' => true]);
            break;

        // ── Action : vider entièrement le panier ──────────
        case 'clear':
            // Supprime tous les articles du panier de cet utilisateur
            $pdo->prepare("DELETE FROM panier WHERE utilisateur_id = ?")
                ->execute([$userId]);
            echo json_encode(['connected' => true, 'success' => true]);
            break;

        // ── Action non reconnue ───────────────────────────
        default:
            http_response_code(400); // Bad Request
            echo json_encode(['error' => 'action invalide']);
    }

} catch (Exception $e) {
    // En cas d'erreur serveur, on retourne un code 500
    // On ne divulgue pas le message d'erreur en production
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur']);
}

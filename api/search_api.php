<?php
/**
 * API recherche — remplace search.js (côté données)
 * GET /api/search.php?q=naruto
 * Retourne un tableau JSON de produits correspondants.
 */
header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');

if (mb_strlen($q) < 1) {
    echo json_encode([]);
    exit;
}

require_once '../includes/db.php';

// Recherche insensible à la casse et aux accents via LIKE + COLLATE
$like = '%' . $q . '%';

$stmt = $pdo->prepare("
    SELECT p.id, p.titre, p.tome, p.prix, p.image, c.slug AS categorie
    FROM produits p
    JOIN categories c ON c.id = p.categorie_id
    WHERE p.titre      LIKE :q COLLATE utf8mb4_general_ci
       OR c.nom        LIKE :q COLLATE utf8mb4_general_ci
       OR CONCAT('tome ', p.tome) LIKE :q COLLATE utf8mb4_general_ci
    ORDER BY p.titre
    LIMIT 10
");
$stmt->execute([':q' => $like]);
$results = $stmt->fetchAll();

echo json_encode($results, JSON_UNESCAPED_UNICODE);

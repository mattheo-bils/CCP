<?php
/**
 * api/search_api.php — API de recherche de produits
 */
header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');

if (mb_strlen($q) < 1) {
    echo json_encode([]);
    exit;
}

require_once '../includes/db.php';

$like = '%' . $q . '%';

// Paramètres nommés pour éviter le conflit avec plusieurs ?
$stmt = $pdo->prepare("
    SELECT p.id, p.titre, p.tome, p.prix, p.image, c.slug AS categorie
    FROM produits p
    JOIN categories c ON c.id = p.categorie_id
    WHERE p.titre LIKE :q1
       OR c.nom   LIKE :q2
    ORDER BY p.titre
    LIMIT 10
");
$stmt->execute([':q1' => $like, ':q2' => $like]);
$results = $stmt->fetchAll();

echo json_encode($results, JSON_UNESCAPED_UNICODE);
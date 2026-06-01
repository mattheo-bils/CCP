<?php
// api/produits.php — retourne tous les produits en JSON
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    $pdo  = getPDO();
    $stmt = $pdo->query("
        SELECT
            p.id,
            p.titre,
            p.auteur,
            p.tome,
            CAST(p.prix AS FLOAT) AS prix,
            c.slug  AS categorie,
            p.image,
            p.description
        FROM produits p
        JOIN categories c ON c.id = p.categorie_id
        ORDER BY p.id
    ");
    echo json_encode($stmt->fetchAll(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur base de données']);
}

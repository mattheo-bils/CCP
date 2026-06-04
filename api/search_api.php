<?php
/**
 * api/search.php — API de recherche de produits
 *
 * Remplace l'ancienne logique de filtrage côté JS (search.js).
 * La recherche est maintenant effectuée en BDD, ce qui est plus
 * performant et insensible à la casse/accents via COLLATE.
 *
 * Paramètre GET :
 *   ?q=naruto — Terme de recherche (min. 1 caractère)
 *
 * Retourne un tableau JSON de produits correspondants (max 10).
 */

header('Content-Type: application/json; charset=utf-8');

// Récupération et validation du terme de recherche
$q = trim($_GET['q'] ?? '');

// Retourner un tableau vide si la requête est trop courte
if (mb_strlen($q) < 1) {
    echo json_encode([]);
    exit;
}

require_once '../includes/db.php';

// Construction du pattern LIKE (% de chaque côté pour recherche partielle)
$like = '%' . $q . '%';

// Recherche insensible à la casse et aux accents grâce à utf8mb4_general_ci
// Recherche sur : titre, nom de catégorie, ou "tome X"
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

// Encodage JSON avec support des caractères Unicode (accents, etc.)
echo json_encode($results, JSON_UNESCAPED_UNICODE);

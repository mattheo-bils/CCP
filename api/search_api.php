<?php
/**
 * api/search_api.php — API de recherche de produits
 *
 * Reçoit un terme de recherche via GET ?q=...
 * Retourne un tableau JSON de produits correspondants (max 10).
 * Utilisé par search.js pour l'autocomplétion de la barre de recherche.
 */

// Indique que la réponse est du JSON encodé en UTF-8
header('Content-Type: application/json; charset=utf-8');

// Récupération et nettoyage du terme de recherche
// trim() supprime les espaces en début et fin de chaîne
$q = trim($_GET['q'] ?? '');

// Si la requête est vide, on retourne un tableau vide immédiatement
if (mb_strlen($q) < 1) {
    echo json_encode([]);
    exit; // Arrêt de l'exécution
}

// Connexion à la base de données
require_once '../includes/db.php';

// Construction du pattern LIKE avec % de chaque côté
// Ex: "dragon" devient "%dragon%" pour trouver "Dragon Ball"
$like = '%' . $q . '%';

// Requête de recherche avec paramètres nommés (:q1, :q2)
// pour éviter les conflits PDO avec plusieurs paramètres identiques
$stmt = $pdo->prepare("
    SELECT p.id,              -- ID du produit pour le lien vers la fiche
           p.titre,           -- Titre du manga
           p.tome,            -- Numéro de tome
           p.prix,            -- Prix affiché dans les résultats
           p.image,           -- Image miniature dans les résultats
           c.slug AS categorie -- Catégorie affichée sous le titre
    FROM produits p
    JOIN categories c ON c.id = p.categorie_id
    WHERE p.titre LIKE :q1    -- Recherche dans le titre du manga
       OR c.nom   LIKE :q2    -- Recherche dans le nom de la catégorie
    ORDER BY p.titre          -- Résultats triés alphabétiquement
    LIMIT 10                  -- Maximum 10 résultats pour ne pas surcharger
");

// Exécution avec les deux paramètres identiques (même valeur, noms différents)
$stmt->execute([':q1' => $like, ':q2' => $like]);

// Récupération de tous les résultats
$results = $stmt->fetchAll();

// Encodage JSON avec support des caractères Unicode (accents, caractères japonais)
echo json_encode($results, JSON_UNESCAPED_UNICODE);

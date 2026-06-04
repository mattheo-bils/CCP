<?php
/**
 * db.php — Connexion à la base de données via PDO
 *
 * Ce fichier est inclus par toutes les pages et API qui ont besoin
 * d'accéder à la base de données. Il expose la variable $pdo.
 *
 * À modifier selon votre environnement (WAMP, XAMPP, serveur distant…)
 */

// ── Paramètres de connexion ───────────────────────────────
define('DB_HOST',    'localhost');   // Adresse du serveur MySQL
define('DB_NAME',    'mangamarket'); // Nom de la base de données
define('DB_USER',    'root');        // Nom d'utilisateur MySQL
define('DB_PASS',    '');            // Mot de passe MySQL
define('DB_CHARSET', 'utf8mb4');     // Encodage (supporte les emojis et caractères spéciaux)

// ── Construction du DSN (Data Source Name) ────────────────
$dsn = sprintf(
    'mysql:host=%s;dbname=%s;charset=%s',
    DB_HOST, DB_NAME, DB_CHARSET
);

// ── Options PDO ───────────────────────────────────────────
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // Lance des exceptions en cas d'erreur SQL
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // Retourne les résultats sous forme de tableaux associatifs
    PDO::ATTR_EMULATE_PREPARES   => false,                   // Utilise les vraies requêtes préparées (plus sécurisé)
];

// ── Instanciation de la connexion ─────────────────────────
try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // En production : loguer l'erreur et afficher un message générique
    throw new RuntimeException('Connexion BDD impossible : ' . $e->getMessage());
}

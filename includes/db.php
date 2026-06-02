<?php
/**
 * MangaMarket — Connexion PDO
 * Adapter les constantes selon votre environnement.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'mangamarket');
define('DB_USER', 'root');       // ← à modifier
define('DB_PASS', '');           // ← à modifier
define('DB_CHARSET', 'utf8mb4');

$dsn = sprintf(
    'mysql:host=%s;dbname=%s;charset=%s',
    DB_HOST, DB_NAME, DB_CHARSET
);

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // En production, loguer l'erreur et afficher un message générique
    throw new RuntimeException('Connexion BDD impossible : ' . $e->getMessage());
}

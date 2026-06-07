<?php
/**
 * includes/db.php — Connexion à la base de données via PDO
 *
 * Ce fichier est inclus par toutes les pages et APIs qui ont besoin
 * d'accéder à la base de données MySQL. Il expose la variable $pdo
 * qui est une instance de la classe PDO prête à l'emploi.
 *
 * À modifier selon votre environnement (WAMP, XAMPP, serveur distant…)
 */

// ── Paramètres de connexion ───────────────────────────────────
define('DB_HOST',    'localhost');    // Adresse du serveur MySQL (localhost pour WAMP/XAMPP)
define('DB_NAME',    'mangamarket'); // Nom de la base de données
define('DB_USER',    'root');        // Nom d'utilisateur MySQL (root par défaut en local)
define('DB_PASS',    '');            // Mot de passe MySQL (vide par défaut avec WAMP)
define('DB_CHARSET', 'utf8mb4');     // Encodage : utf8mb4 supporte emojis et caractères japonais

// ── Construction du DSN (Data Source Name) ────────────────────
// Le DSN est la chaîne de connexion qui indique à PDO où et comment se connecter
$dsn = sprintf(
    'mysql:host=%s;dbname=%s;charset=%s', // Format : mysql:host=...;dbname=...;charset=...
    DB_HOST, DB_NAME, DB_CHARSET          // Injection des constantes définies ci-dessus
);

// ── Options de configuration PDO ──────────────────────────────
$options = [
    // Lance une exception PHP en cas d'erreur SQL (plutôt que retourner false silencieusement)
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,

    // Retourne les résultats sous forme de tableaux associatifs ['colonne' => 'valeur']
    // plutôt que des tableaux numérotés [0 => 'valeur']
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

    // Désactive la simulation des requêtes préparées : utilise les vraies requêtes préparées MySQL
    // Plus sécurisé contre les injections SQL
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// ── Instanciation de la connexion PDO ─────────────────────────
try {
    // Création de l'objet PDO avec les paramètres définis
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // En cas d'échec de connexion (BDD éteinte, mauvais mot de passe…)
    // On lance une RuntimeException avec le message d'erreur
    // En production, il faudrait loguer l'erreur et afficher un message générique
    throw new RuntimeException('Connexion BDD impossible : ' . $e->getMessage());
}

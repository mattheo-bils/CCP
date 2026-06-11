<?php
/**
 * includes/auth_client.php — Protection des pages réservées aux connectés
 *
 * À inclure en haut de chaque page nécessitant une connexion via :
 *   require_once '../includes/auth_client.php';
 *
 * Vérifie que l'utilisateur est connecté (client OU admin).
 * Redirige vers la connexion sinon.
 */

// Démarrage de session si pas encore active
if (session_status() === PHP_SESSION_NONE) session_start();

// Pas connecté → redirection vers la page de connexion
if (empty($_SESSION['user_id'])) {
    header('Location: creecompte.php');
    exit;
}

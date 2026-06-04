<?php
/**
 * deconnexion.php — Déconnexion de l'utilisateur
 *
 * Détruit la session PHP et redirige vers la page d'accueil.
 * Le panier localStorage n'est pas touché (géré par confirmeachat.js).
 */

// Démarrer la session si elle n'est pas encore active
if (session_status() === PHP_SESSION_NONE) session_start();

// Destruction complète de la session (supprime le cookie + les données)
session_destroy();

// Redirection vers l'accueil
header('Location: ../index.php');
exit;

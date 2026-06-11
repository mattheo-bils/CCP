<?php
/**
 * includes/auth_admin.php — Protection des pages d'administration
 *
 * À inclure en haut de chaque page admin via :
 *   require_once '../../includes/auth_admin.php';
 *
 * Vérifie que l'utilisateur est connecté ET qu'il est admin.
 * Redirige sinon vers la page appropriée.
 */

// Démarrage de session si pas encore active
if (session_status() === PHP_SESSION_NONE) session_start();

// Pas connecté → redirection vers la page de connexion
if (empty($_SESSION['user_id'])) {
    header('Location: ../../views/creecompte.php');
    exit;
}

// Connecté mais pas admin → redirection vers l'accueil avec message d'erreur
if (($_SESSION['user_role'] ?? 'client') !== 'admin') {
    header('Location: ../../index.php?error=acces_refuse');
    exit;
}

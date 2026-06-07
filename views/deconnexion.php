<?php
/**
 * views/deconnexion.php — Déconnexion de l'utilisateur
 *
 * Ce fichier n'affiche rien : il détruit la session et redirige.
 * Accessible via le lien "Se déconnecter" dans le menu utilisateur.
 *
 * Note : Le panier localStorage n'est PAS vidé ici.
 * Il sera vidé par confirmeachat.js après une commande.
 * Si l'utilisateur se reconnecte avec un autre compte, son localStorage
 * sera fusionné dans son panier BDD au prochain chargement du panier.
 */

// Démarrage de la session si elle n'est pas déjà active
// Nécessaire pour pouvoir la détruire
if (session_status() === PHP_SESSION_NONE) session_start();

// Destruction complète de la session :
// - Supprime toutes les variables de session ($_SESSION)
// - Invalide le cookie de session côté navigateur
// - Libère les données en mémoire serveur
session_destroy();

// Redirection vers la page d'accueil après déconnexion
// L'en-tête Location doit être envoyé avant tout contenu HTML
header('Location: ../index.php');
exit; // Arrêt immédiat du script (bonne pratique après header Location)

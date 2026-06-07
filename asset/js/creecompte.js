/**
 * creecompte.js — Gestion des onglets Connexion / Inscription
 *
 * La page contient deux formulaires superposés :
 *   - #form-login    : formulaire de connexion
 *   - #form-register : formulaire d'inscription
 * Ce script gère l'affichage de l'un ou l'autre selon l'onglet actif.
 */

/**
 * Bascule entre les onglets "Se connecter" et "Créer un compte"
 * sans rechargement de page.
 *
 * @param {string} tab - 'login' pour connexion, 'register' pour inscription
 */
function switchTab(tab) {
    // Affichage du formulaire de connexion si tab='login', masquage sinon
    document.getElementById('form-login').style.display    = tab === 'login'    ? '' : 'none';
    // Affichage du formulaire d'inscription si tab='register', masquage sinon
    document.getElementById('form-register').style.display = tab === 'register' ? '' : 'none';

    // Ajout/suppression de la classe 'active' sur le bouton de l'onglet connexion
    // La classe 'active' ajoute le soulignement rouge sous l'onglet actif (voir CSS)
    document.getElementById('tab-login').classList.toggle('active',    tab === 'login');
    // Même chose pour l'onglet inscription
    document.getElementById('tab-register').classList.toggle('active', tab === 'register');
}

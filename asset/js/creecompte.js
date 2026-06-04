/**
 * creecompte.js — Gestion des onglets Connexion / Inscription
 *
 * Alterne l'affichage entre le formulaire de connexion
 * et le formulaire d'inscription sans rechargement de page.
 */

/**
 * Bascule entre les onglets "Se connecter" et "Créer un compte".
 * @param {string} tab - 'login' ou 'register'
 */
function switchTab(tab) {
    // Affichage/masquage des formulaires
    document.getElementById('form-login').style.display    = tab === 'login'    ? '' : 'none';
    document.getElementById('form-register').style.display = tab === 'register' ? '' : 'none';

    // Mise à jour de la classe active sur les boutons d'onglet
    document.getElementById('tab-login').classList.toggle('active',    tab === 'login');
    document.getElementById('tab-register').classList.toggle('active', tab === 'register');
}

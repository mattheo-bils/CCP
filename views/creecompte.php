<?php
/**
 * views/creecompte.php — Page de connexion et d'inscription
 *
 * Contient deux formulaires dans une même page :
 *   - Formulaire de connexion (tab "Se connecter")
 *   - Formulaire d'inscription (tab "Créer un compte")
 * Le basculement entre les deux est géré par creecompte.js (sans rechargement).
 *
 * Traitement PHP :
 *   - Connexion : vérifie email + mot de passe, crée la session
 *   - Inscription : valide les champs, hache le mdp, insère en BDD
 *   - Redirection vers l'accueil après connexion/inscription réussie
 */

$pageTitle = "Connexion / Inscription";
$pageCss   = "pages.css";
$pageJs    = ["creecompte.js"]; // Gère le switch entre les deux onglets
$basePath  = '../';

// Tableaux pour stocker les erreurs de chaque formulaire séparément
$errorsLogin    = []; // Erreurs du formulaire de connexion
$errorsRegister = []; // Erreurs du formulaire d'inscription
$activeTab      = 'login'; // Onglet actif par défaut : connexion

// Démarrage de session pour stocker l'état de connexion
if (session_status() === PHP_SESSION_NONE) session_start();

// ── Traitement de la connexion ────────────────────────────────
// Déclenché quand le formulaire de connexion est soumis
if (isset($_POST['action']) && $_POST['action'] === 'login') {
    $activeTab = 'login'; // Reste sur l'onglet connexion en cas d'erreur

    // Nettoyage des données soumises
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validation basique
    if (empty($email))    $errorsLogin[] = "Email requis.";
    if (empty($password)) $errorsLogin[] = "Mot de passe requis.";

    if (empty($errorsLogin)) {
        try {
            require_once '../includes/db.php';

            // Recherche de l'utilisateur par email
            // On ne filtre PAS par mot de passe dans la requête SQL pour éviter
            // les timing attacks. La vérification se fait avec password_verify()
            $stmt = $pdo->prepare("SELECT id, prenom, password FROM utilisateurs WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(); // Retourne false si email non trouvé

            // Vérification du mot de passe avec la fonction sécurisée PHP
            // password_verify() compare le mot de passe saisi avec le hash bcrypt en BDD
            if ($user && password_verify($password, $user['password'])) {
                // Connexion réussie : création de la session
                $_SESSION['user_id']     = $user['id'];     // ID pour les requêtes BDD
                $_SESSION['user_prenom'] = $user['prenom']; // Prénom pour l'affichage

                // Redirection vers l'accueil
                header('Location: ../index.php');
                exit;
            } else {
                // Email non trouvé ou mot de passe incorrect
                // Message générique pour ne pas révéler lequel des deux est faux
                $errorsLogin[] = "Email ou mot de passe incorrect.";
            }
        } catch (Exception $e) {
            $errorsLogin[] = "Service temporairement indisponible.";
        }
    }
}

// ── Traitement de l'inscription ───────────────────────────────
// Déclenché quand le formulaire d'inscription est soumis
if (isset($_POST['action']) && $_POST['action'] === 'register') {
    $activeTab = 'register'; // Reste sur l'onglet inscription en cas d'erreur

    // Nettoyage des données soumises
    $prenom   = trim($_POST['prenom']   ?? '');
    $nom      = trim($_POST['nom']      ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validation des champs
    if (empty($prenom))   $errorsRegister[] = "Prénom requis.";
    if (empty($nom))      $errorsRegister[] = "Nom requis.";
    // filter_var vérifie le format de l'email (présence de @, domaine valide…)
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errorsRegister[] = "Email invalide.";
    // Minimum 8 caractères pour la sécurité du mot de passe
    if (strlen($password) < 8) $errorsRegister[] = "Le mot de passe doit faire au moins 8 caractères.";

    if (empty($errorsRegister)) {
        try {
            require_once '../includes/db.php';

            // Vérification que l'email n'est pas déjà utilisé (contrainte UNIQUE en BDD)
            $check = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
            $check->execute([$email]);
            if ($check->fetch()) {
                // Email déjà présent en BDD
                $errorsRegister[] = "Cet email est déjà utilisé.";
            } else {
                // Hachage sécurisé du mot de passe avec bcrypt
                // PASSWORD_BCRYPT génère automatiquement un sel (salt) aléatoire
                // Ne jamais stocker un mot de passe en clair !
                $hash = password_hash($password, PASSWORD_BCRYPT);

                // Insertion du nouvel utilisateur
                $stmt = $pdo->prepare("INSERT INTO utilisateurs (prenom, nom, email, password) VALUES (?,?,?,?)");
                $stmt->execute([$prenom, $nom, $email, $hash]);

                // Connexion automatique après inscription
                $_SESSION['user_id']     = $pdo->lastInsertId(); // ID généré par AUTO_INCREMENT
                $_SESSION['user_prenom'] = $prenom;

                // Redirection vers l'accueil
                header('Location: ../index.php');
                exit;
            }
        } catch (Exception $e) {
            $errorsRegister[] = "Service temporairement indisponible.";
        }
    }
}

// Chargement du header (après le traitement pour pouvoir rediriger)
require_once '../includes/header.php';
?>

<main>
    <div class="compte-page">
        <div class="compte-card">
            <h1>MangaMarket</h1>
            <p class="sub">Accédez à votre espace personnel</p>

            <!-- Onglets de navigation entre connexion et inscription -->
            <div class="compte-tabs">
                <!-- La classe 'active' est définie par PHP selon $activeTab
                     et peut être modifiée par JS (switchTab) sans rechargement -->
                <button class="compte-tab <?= $activeTab === 'login'    ? 'active' : '' ?>"
                        id="tab-login" onclick="switchTab('login')">Se connecter</button>
                <button class="compte-tab <?= $activeTab === 'register' ? 'active' : '' ?>"
                        id="tab-register" onclick="switchTab('register')">Créer un compte</button>
            </div>

            <!-- ── Formulaire de connexion ── -->
            <!-- Masqué si l'onglet actif n'est pas 'login' -->
            <div id="form-login" <?= $activeTab !== 'login' ? 'style="display:none"' : '' ?>>
                <?php if (!empty($errorsLogin)): ?>
                <div class="alert-error" style="margin-bottom:16px">
                    <?php foreach ($errorsLogin as $e): ?>
                        <p>⚠ <?= htmlspecialchars($e) ?></p>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <form method="post" action="creecompte.php" class="compte-form">
                    <!-- Champ caché pour identifier quel formulaire est soumis -->
                    <input type="hidden" name="action" value="login">
                    <div class="form-group">
                        <label for="login-email">Email</label>
                        <!-- value conserve la saisie après une erreur -->
                        <input type="email" id="login-email" name="email"
                               placeholder="vous@exemple.com"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="login-password">Mot de passe</label>
                        <!-- type="password" masque la saisie -->
                        <input type="password" id="login-password" name="password"
                               placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Se connecter</button>
                </form>
                <a href="#" class="compte-link">Mot de passe oublié ?</a>
            </div>

            <!-- ── Formulaire d'inscription ── -->
            <!-- Masqué si l'onglet actif n'est pas 'register' -->
            <div id="form-register" <?= $activeTab !== 'register' ? 'style="display:none"' : '' ?>>
                <?php if (!empty($errorsRegister)): ?>
                <div class="alert-error" style="margin-bottom:16px">
                    <?php foreach ($errorsRegister as $e): ?>
                        <p>⚠ <?= htmlspecialchars($e) ?></p>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <form method="post" action="creecompte.php" class="compte-form">
                    <!-- Champ caché pour identifier ce formulaire -->
                    <input type="hidden" name="action" value="register">
                    <div class="form-group">
                        <label for="reg-prenom">Prénom</label>
                        <input type="text" id="reg-prenom" name="prenom"
                               placeholder="Jean"
                               value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="reg-nom">Nom</label>
                        <input type="text" id="reg-nom" name="nom"
                               placeholder="Dupont"
                               value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="reg-email">Email</label>
                        <input type="email" id="reg-email" name="email"
                               placeholder="vous@exemple.com"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <!-- <small> pour l'indication de longueur minimale -->
                        <label for="reg-password">Mot de passe <small>(min. 8 caractères)</small></label>
                        <!-- minlength="8" ajoute aussi une validation HTML5 -->
                        <input type="password" id="reg-password" name="password"
                               placeholder="••••••••" required minlength="8">
                    </div>
                    <button type="submit" class="btn btn-primary">Créer le compte</button>
                </form>
                <!-- Lien pour revenir à l'onglet connexion -->
                <a href="#" class="compte-link" onclick="switchTab('login');return false">
                    Déjà inscrit ? <span>Se connecter</span>
                </a>
            </div>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>

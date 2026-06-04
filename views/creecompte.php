<?php
$pageTitle = "Connexion / Inscription";
$pageCss   = "pages.css";
$pageJs    = ["creecompte.js"];
$basePath  = '../';

$errorsLogin    = [];
$errorsRegister = [];
$activeTab      = 'login';

if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_POST['action']) && $_POST['action'] === 'login') {
    $activeTab = 'login';
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email))    $errorsLogin[] = "Email requis.";
    if (empty($password)) $errorsLogin[] = "Mot de passe requis.";

    if (empty($errorsLogin)) {
        try {
            require_once '../includes/db.php';
            $stmt = $pdo->prepare("SELECT id, prenom, password FROM utilisateurs WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id']     = $user['id'];
                $_SESSION['user_prenom'] = $user['prenom'];
                header('Location: ../index.php');
                exit;
            } else {
                $errorsLogin[] = "Email ou mot de passe incorrect.";
            }
        } catch (Exception $e) {
            $errorsLogin[] = "Service temporairement indisponible.";
        }
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'register') {
    $activeTab = 'register';
    $prenom   = trim($_POST['prenom']   ?? '');
    $nom      = trim($_POST['nom']      ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($prenom))   $errorsRegister[] = "Prénom requis.";
    if (empty($nom))      $errorsRegister[] = "Nom requis.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errorsRegister[] = "Email invalide.";
    if (strlen($password) < 8) $errorsRegister[] = "Le mot de passe doit faire au moins 8 caractères.";

    if (empty($errorsRegister)) {
        try {
            require_once '../includes/db.php';
            $check = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
            $check->execute([$email]);
            if ($check->fetch()) {
                $errorsRegister[] = "Cet email est déjà utilisé.";
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO utilisateurs (prenom, nom, email, password) VALUES (?,?,?,?)");
                $stmt->execute([$prenom, $nom, $email, $hash]);
                $_SESSION['user_id']     = $pdo->lastInsertId();
                $_SESSION['user_prenom'] = $prenom;
                header('Location: ../index.php');
                exit;
            }
        } catch (Exception $e) {
            $errorsRegister[] = "Service temporairement indisponible.";
        }
    }
}

require_once '../includes/header.php';
?>

<main>
    <div class="compte-page">
        <div class="compte-card">
            <h1>MangaMarket</h1>
            <p class="sub">Accédez à votre espace personnel</p>

            <div class="compte-tabs">
                <button class="compte-tab <?= $activeTab === 'login'    ? 'active' : '' ?>"
                        id="tab-login" onclick="switchTab('login')">Se connecter</button>
                <button class="compte-tab <?= $activeTab === 'register' ? 'active' : '' ?>"
                        id="tab-register" onclick="switchTab('register')">Créer un compte</button>
            </div>

            <div id="form-login" <?= $activeTab !== 'login' ? 'style="display:none"' : '' ?>>
                <?php if (!empty($errorsLogin)): ?>
                <div class="alert-error" style="margin-bottom:16px">
                    <?php foreach ($errorsLogin as $e): ?>
                        <p>⚠ <?= htmlspecialchars($e) ?></p>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <form method="post" action="creecompte.php" class="compte-form">
                    <input type="hidden" name="action" value="login">
                    <div class="form-group">
                        <label for="login-email">Email</label>
                        <input type="email" id="login-email" name="email"
                               placeholder="vous@exemple.com"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="login-password">Mot de passe</label>
                        <input type="password" id="login-password" name="password"
                               placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Se connecter</button>
                </form>
                <a href="#" class="compte-link">Mot de passe oublié ?</a>
            </div>

            <div id="form-register" <?= $activeTab !== 'register' ? 'style="display:none"' : '' ?>>
                <?php if (!empty($errorsRegister)): ?>
                <div class="alert-error" style="margin-bottom:16px">
                    <?php foreach ($errorsRegister as $e): ?>
                        <p>⚠ <?= htmlspecialchars($e) ?></p>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <form method="post" action="creecompte.php" class="compte-form">
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
                        <label for="reg-password">Mot de passe <small>(min. 8 caractères)</small></label>
                        <input type="password" id="reg-password" name="password"
                               placeholder="••••••••" required minlength="8">
                    </div>
                    <button type="submit" class="btn btn-primary">Créer le compte</button>
                </form>
                <a href="#" class="compte-link" onclick="switchTab('login');return false">
                    Déjà inscrit ? <span>Se connecter</span>
                </a>
            </div>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>

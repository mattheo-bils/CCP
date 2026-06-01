<?php
$pageTitle = "Connexion / Inscription";
$pageCss   = "pages.css";
require_once 'includes/header.php';
?>

<main>
    <div class="compte-page">
        <div class="compte-card">
            <h1>MangaMarket</h1>
            <p class="sub">Accédez à votre espace personnel</p>

            <div class="compte-tabs">
                <button class="compte-tab active" id="tab-login" onclick="switchTab('login')">Se connecter</button>
                <button class="compte-tab" id="tab-register" onclick="switchTab('register')">Créer un compte</button>
            </div>

            <!-- Connexion -->
            <div id="form-login">
                <form method="post" action="compte.php" class="compte-form">
                    <div class="form-group">
                        <label for="login-email">Email</label>
                        <input type="email" id="login-email" name="email" placeholder="vous@exemple.com" required>
                    </div>
                    <div class="form-group">
                        <label for="login-password">Mot de passe</label>
                        <input type="password" id="login-password" name="password" placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Se connecter</button>
                </form>
                <a href="#" class="compte-link">Mot de passe oublié ?</a>
            </div>

            <!-- Inscription -->
            <div id="form-register" style="display:none">
                <form method="post" action="creecompte.php" class="compte-form">
                    <div class="form-group">
                        <label for="reg-prenom">Prénom</label>
                        <input type="text" id="reg-prenom" name="prenom" placeholder="Jean" required>
                    </div>
                    <div class="form-group">
                        <label for="reg-nom">Nom</label>
                        <input type="text" id="reg-nom" name="nom" placeholder="Dupont" required>
                    </div>
                    <div class="form-group">
                        <label for="reg-email">Email</label>
                        <input type="email" id="reg-email" name="email" placeholder="vous@exemple.com" required>
                    </div>
                    <div class="form-group">
                        <label for="reg-password">Mot de passe</label>
                        <input type="password" id="reg-password" name="password" placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Créer le compte</button>
                </form>
                <a href="#" class="compte-link">
                    Déjà inscrit ? <span>Se connecter</span>
                </a>
            </div>
        </div>
    </div>
</main>

<script>
function switchTab(tab) {
    document.getElementById('form-login').style.display     = tab === 'login'    ? '' : 'none';
    document.getElementById('form-register').style.display  = tab === 'register' ? '' : 'none';
    document.getElementById('tab-login').classList.toggle('active',    tab === 'login');
    document.getElementById('tab-register').classList.toggle('active', tab === 'register');
}
</script>

<?php require_once 'includes/footer.php'; ?>

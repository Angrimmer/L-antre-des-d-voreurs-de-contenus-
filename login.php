<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Déjà connecté → on redirige
if (!empty($_SESSION['user_id'])) {
    header('Location: page-principale.php');
    exit;
}

// Rate limiting — max 5 tentatives par 15 minutes
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['login_last']     = time();
}
if (time() - $_SESSION['login_last'] > 900) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['login_last']     = time();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_SESSION['login_attempts'] >= 5) {
        $error = 'Trop de tentatives. Réessaie dans 15 minutes.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$username || !$password) {
            $error = 'Remplis tous les champs.';
        } elseif (strlen($password) > 255) {
            $error = 'Mot de passe invalide.';
        } else {
            try {
                $db   = getDB();
                $stmt = $db->prepare('SELECT id, username, password_hash FROM users WHERE username = ?');
                $stmt->execute([$username]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password_hash'])) {
                    $_SESSION['login_attempts'] = 0;
                    $_SESSION['user_id']        = $user['id'];
                    $_SESSION['username']       = $user['username'];
                    header('Location: page-principale.php');
                    exit;
                } else {
                    $_SESSION['login_attempts']++;
                    $_SESSION['login_last'] = time();
                    $error = 'Nom d\'utilisateur ou mot de passe incorrect.';
                }
            } catch (PDOException) {
                $error = 'Impossible de se connecter à la base de données.';
            }
        }
    }
}

// Vérifie si c'est le premier lancement (aucun utilisateur)
$firstRun = false;
try {
    $db       = getDB();
    $count    = $db->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $firstRun = ($count == 0);
} catch (PDOException) {}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>L'Antre — Connexion</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/login.css">
    <link rel="icon" href="src/assets/controller.png"/>
</head>
<body class="landing">

    <main class="login-wrap">
        <a href="index.php" class="login-back">← Retour</a>

        <h1 class="login-title">
            <?= $firstRun ? 'Créer un compte' : 'Se connecter' ?>
        </h1>

        <?php if ($error): ?>
            <p class="login-error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <?php if ($firstRun): ?>
            <form method="POST" action="setup.php" class="login-form">
                <p class="login-hint">Aucun compte trouvé. Crée ton compte.</p>
                <div class="field">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" required autocomplete="username">
                </div>
                <div class="field">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required autocomplete="new-password">
                </div>
                <button type="submit" class="btn-submit">Créer le compte</button>
            </form>
        <?php else: ?>
            <form method="POST" class="login-form">
                <div class="field">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" required autocomplete="username"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>
                <div class="field">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>
                <button type="submit" class="btn-submit">Entrer</button>
            </form>
            <a href="register.php" class="login-register-link">Pas encore de compte ? Créer un compte</a>
        <?php endif; ?>
    </main>

</body>
</html>

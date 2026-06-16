<?php
session_start();
require_once __DIR__ . '/config/database.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: page-principale.php');
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm']  ?? '';
    $invite   = trim($_POST['invite_code'] ?? '');

    if (!$username || !$password || !$confirm || !$invite) {
        $error = 'Remplis tous les champs.';
    } elseif ($invite !== INVITE_CODE) {
        $error = 'Code d\'invitation invalide.';
    } elseif (strlen($password) < 6) {
        $error = 'Le mot de passe doit faire au moins 6 caractères.';
    } elseif (strlen($password) > 255) {
        $error = 'Le mot de passe est trop long (255 caractères max).';
    } elseif ($password !== $confirm) {
        $error = 'Les mots de passe ne correspondent pas.';
    } else {
        try {
            $db   = getDB();
            $stmt = $db->prepare('SELECT id FROM users WHERE username = ?');
            $stmt->execute([$username]);

            if ($stmt->fetch()) {
                $error = 'Ce nom d\'utilisateur est déjà pris.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $db->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)')
                   ->execute([$username, $hash]);
                $success = 'Compte créé ! Tu peux te connecter.';
            }
        } catch (PDOException) {
            $error = 'Erreur base de données.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>L'Antre — Créer un compte</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/login.css">
    <link rel="icon" href="src/assets/controller.png"/>
</head>
<body class="landing">

    <main class="login-wrap">
        <a href="login.php" class="login-back">← Retour</a>

        <h1 class="login-title">Créer un compte</h1>

        <?php if ($error): ?>
            <p class="login-error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <?php if ($success): ?>
            <p class="login-success"><?= htmlspecialchars($success) ?></p>
            <a href="login.php" class="btn-submit" style="text-align:center">Se connecter</a>
        <?php else: ?>
            <form method="POST" class="login-form">
                <div class="field">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" required autocomplete="username"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>
                <div class="field">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required autocomplete="new-password">
                </div>
                <div class="field">
                    <label for="confirm">Confirmer le mot de passe</label>
                    <input type="password" id="confirm" name="confirm" required autocomplete="new-password">
                </div>
                <div class="field">
                    <label for="invite_code">Code d'invitation</label>
                    <input type="password" id="invite_code" name="invite_code" required autocomplete="off">
                </div>
                <button type="submit" class="btn-submit">Créer le compte</button>
            </form>
        <?php endif; ?>
    </main>

</body>
</html>

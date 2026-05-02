<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>L'Antre des Dévoreurs de Contenu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="landing">

    <nav class="nav-landing">
        <a href="#" class="btn-nav">À propos</a>
        <a href="#" class="btn-nav">Nous contacter</a>
        <a href="login.php" class="btn-nav">Se connecter</a>
    </nav>

    <main class="landing-main">
        <h1 class="landing-title">L'Antre des dévoreurs de contenu</h1>
        <p class="landing-subtitle">Votre base de données perso pour ne plus oublier vos sources favorites !</p>

        <div class="landing-art">
            <!-- Remplace cette image par ton pixel art une fois que tu l'as -->
            <img src="assets/pixel-art.png" alt="Pixel art gamepad" class="pixel-art"
                 onerror="this.style.display='none'; document.getElementById('art-fallback').style.display='block'">
            <div id="art-fallback" class="art-fallback" style="display:none;">
                <span>🎮</span>
                <span>📚</span>
            </div>
        </div>

        <a href="page-principale.php" class="btn-start">Appuyez sur Start...</a>
    </main>

</body>
</html>

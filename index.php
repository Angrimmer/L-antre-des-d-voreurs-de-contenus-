<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>L'Antre des Dévoreurs de Contenu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="src/assets/controller.png"/>
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
            
            <img src="src/assets/controller.png" alt="logo de l'antre des dévoreurs de contenu v1.0" class="pixel-art">
        </div>

        <a href="page-principale.php" class="btn-start-link">
          <img 
            src="src/assets/button_off.png" 
            alt="Start"
            class="btn-start-img"
            style="width:10em; height:auto; display:block;"
            onmouseenter="this.src='src/assets/button_on.png'"
            onmouseleave="this.src='src/assets/button_off.png'">
        </a>

        <span class="press_enter">appuyez sur start pour commencer</span>
    </main>

</body>
</html>

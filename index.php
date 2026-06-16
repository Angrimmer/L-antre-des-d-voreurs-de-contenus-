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
        <button class="btn-nav" id="btnAbout">À propos</button>
        <button class="btn-nav" id="btnContact">Nous contacter</button>
        <a href="login.php" class="btn-nav">Se connecter</a>
    </nav>

    <main class="landing-main">
        <h1 class="landing-title">L'Antre des dévoreurs de contenu</h1>
        <p class="landing-subtitle">Votre base de données perso pour ne plus oublier vos sources favorites !</p>

        <div class="landing-art">
            
            <img src="src/assets/controller.png" alt="logo de l'antre des dévoreurs de contenu v1.0" class="pixel-art">
        </div>

        <a href="page-principale.php" class="btn-start-link">
            <img src="src/assets/button_off.png" alt="Start" class="btn-start-img" id="btnStart">
        </a>

        <span class="press_enter">appuyez sur start pour commencer</span>
    </main>

    <!-- Modale À propos -->
    <div class="landing-modal-overlay" id="modalAbout">
        <div class="landing-modal">
            <button class="landing-modal-close" data-close="modalAbout">✕</button>
            <h2 class="landing-modal-title">À propos</h2>
            <div class="landing-modal-body">
                <p>Bienvenue dans <strong>L'Antre des Dévoreurs de Contenu</strong> — un outil personnel conçu pour ne plus jamais perdre le fil de tes séries, films, animes et jeux vidéo.</p>
                <p>Ici, pas de réseau social, pas de recommandations algorithmiques. Juste ta bibliothèque, à toi, organisée comme tu l'entends : planifié, en cours, terminé, abandonné.</p>
                <p>Le projet est né d'un besoin simple : avoir un endroit unique pour tout tracker, avec une interface qui a du caractère.</p>
                <p>Je suis un dev seul sur le projet, mais n'hésitez pas à me contacter si vous avez des idées ou des suggestions !</p>
                <p>Petite mention spéciale pour un de mes amis (un frère, que dis-je !) qui préfère rester plus anonyme et à qui je dois mon logo ici et mes boutons !</p>
                <p class="landing-modal-version">v1.0 — Projet personnel</p>
            </div>
        </div>
    </div>

    <!-- Modale Contact -->
    <div class="landing-modal-overlay" id="modalContact">
        <div class="landing-modal">
            <button class="landing-modal-close" data-close="modalContact">✕</button>
            <h2 class="landing-modal-title">Nous contacter</h2>
            <div class="landing-modal-body">
                <p>Une question, une suggestion ou juste envie de dire bonjour ?</p>
                <p class="contact-email">📧 <a href="mailto:angrimmer@pm.me">angrimmer@pm.me</a></p>
                <form class="contact-form" id="contactForm">
                    <input  type="text"  name="name"    class="contact-input" placeholder="Ton nom" required>
                    <input  type="email" name="email"   class="contact-input" placeholder="Ton email" required>
                    <textarea            name="message" class="contact-textarea" placeholder="Ton message..." required></textarea>
                    <button type="submit" class="contact-submit">Envoyer</button>
                </form>
                <p class="contact-feedback" id="contactFeedback"></p>
            </div>
        </div>
    </div>

    <script src="js/landing.js"></script>

</body>
</html>

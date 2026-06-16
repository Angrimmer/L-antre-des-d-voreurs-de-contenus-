<?php
// Base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'antre_devolib');
define('DB_USER', 'root');
define('DB_PASS', '');

// Clés API — à remplir avec tes propres clés
// TMDB  : https://www.themoviedb.org/settings/api  (gratuit)
// RAWG  : https://rawg.io/apidocs                  (gratuit)
// AniList n'a pas besoin de clé
define('TMDB_API_KEY', 'YOUR_TMDB_API_KEY');
define('RAWG_API_KEY', 'YOUR_RAWG_API_KEY');

// Sécurité
// Mettre à true en production (hébergement avec un vrai certificat SSL)
define('VERIFY_SSL', true);

// Code d'invitation pour créer un nouveau compte (register.php)
// Change cette valeur avant de mettre en ligne
define('INVITE_CODE', 'change-moi-en-prod');

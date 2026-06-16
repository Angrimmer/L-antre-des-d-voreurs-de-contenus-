# L'Antre des Dévoreurs de Contenu

## Quezako ?

Bonjour à tous !  
Ce projet est né d'un besoin très simple : ne plus perdre le fil de tout ce qu'on regarde, joue, lit ou met de côté. Pas d'appli tierce, pas de compte sur un réseau social, juste **ma bibliothèque perso**, avec une interface qui a du caractère.

L'idée c'est d'avoir un endroit unique pour tracker films, séries, animes et jeux vidéo — avec le statut, les notes, les avis en cours de route, et tout ça sauvegardé automatiquement.

## Ce qui m'a amusé dans ce projet ?

C'est clairement le côté "full stack from scratch" sans framework. Pas de React, pas de Laravel — du PHP vanilla, du JS vanilla, du CSS à la main. On se retrouve vite à se demander "comment je fais passer mes données PHP côté JS proprement ?" ou "comment je structure mes API sans ORM ?". Ce genre de questions qu'on finit par résoudre et qui font qu'on comprend vraiment ce qu'on fait.

L'esthétique pixel/rétro gaming était aussi un vrai plaisir à développer — trouver le bon équilibre entre fonctionnel et "j'ai l'impression d'être dans un vieux RPG".

## Une petite image ?

A venir !

## Des difficultés ?

Quelques-unes. La gestion des APIs externes (TMDB, AniList en GraphQL, RAWG) avec des environnements HTTPS qui ne coopèrent pas toujours sous WAMP. La synchronisation des sauvegardes automatiques sans perdre de données quand on ajoute un item. Et le traditionnel "pourquoi mon CSS fait ça ??" à 23h.

## Stack

- **Front** — HTML / CSS / JS vanilla
- **Back** — PHP 8.4 + PDO
- **BDD** — MySQL (WAMP en local)
- **APIs** — TMDB (films & séries), AniList GraphQL (animes), RAWG (jeux)
- **Tests** — PHPUnit 11 sur SQLite en mémoire, lancés automatiquement via GitHub Actions

## Installation

Je prévois de finir par mettre le projet en Dev, donc un peu de patience !

## Remerciements

- À vous déjà, pour avoir pris le temps de zieuter ça.
- Mon ami qui s'est amusé à me faire les 2/3 images dont j'avais besoin simplement car il m'a entendu râler dessus !
- À tous ceux qui me laisseront une chance à l'avenir en ayant eu la curiosité de passer par ici !

### PS
Bonne journée à vous tous !

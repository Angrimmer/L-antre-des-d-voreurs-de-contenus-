<?php
session_start();
require_once __DIR__ . '/config/database.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$category = $_GET['cat'] ?? 'anime';
$validCats = ['film', 'serie', 'anime', 'jeu', 'livre'];
if (!in_array($category, $validCats)) $category = 'anime';

$items = [];
try {
    $db = getDB();
    $stmt = $db->prepare(
        'SELECT * FROM library_items WHERE user_id = ? AND category = ? ORDER BY title ASC'
    );
    $stmt->execute([$userId, $category]);
    $items = $stmt->fetchAll();
} catch (PDOException $e) {
    // DB pas encore configurée — on affiche quand même la page
}

$labels = ['film' => 'Films', 'serie' => 'Séries', 'anime' => 'Animes', 'jeu' => 'Jeux', 'livre' => 'Livres'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>L'Antre — Bibliothèque</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/library.css">
</head>
<body class="library-page">

    <!-- En-tête -->
    <header class="lib-header">
        <a href="index.php" class="lib-logo">
            <span class="logo-icon">📚</span>
        </a>
        <nav class="lib-tabs">
            <?php foreach ($labels as $cat => $label): ?>
            <a href="?cat=<?= $cat ?>"
               class="lib-tab <?= $cat === $category ? 'active' : '' ?>">
                <?= $label ?>
            </a>
            <?php endforeach; ?>
        </nav>
        <div class="lib-search-wrap">
            <input type="text" id="libSearch" class="lib-search-input" placeholder="Filtrer…" autocomplete="off">
        </div>
        <button class="btn-add" id="btnAdd">+ Ajouter</button>
    </header>

    <!-- Corps principal -->
    <div class="lib-body">

        <!-- Colonne gauche : liste -->
        <aside class="lib-list">
            <?php if (empty($items)): ?>
                <p class="empty-list">Aucun <?= $labels[$category] ?> dans ta liste.<br>Clique sur "+ Ajouter" pour commencer !</p>
            <?php else: ?>
                <?php $currentLetter = ''; foreach ($items as $i => $item):
                    $letter = mb_strtoupper(mb_substr($item['title'], 0, 1));
                    if ($letter !== $currentLetter):
                        $currentLetter = $letter;
                ?>
                <div class="list-letter"><?= htmlspecialchars($letter) ?></div>
                <?php endif; ?>
                <div class="lib-item <?= $i === 0 ? 'selected' : '' ?>"
                     data-id="<?= htmlspecialchars($item['external_id']) ?>"
                     data-cat="<?= htmlspecialchars($item['category']) ?>"
                     data-dbid="<?= $item['id'] ?>">
                    <span class="item-title"><?= htmlspecialchars($item['title']) ?></span>
                    <span class="item-status status-<?= $item['status'] ?>"></span>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </aside>

        <!-- Zone principale : image + détails empilés -->
        <main class="lib-main">
            <section class="lib-cover" id="libCover">
                <div class="cover-placeholder">
                    <p>Sélectionne un titre<br>pour voir les détails</p>
                </div>
            </section>

            <aside class="lib-details" id="libDetails">
                <div class="details-placeholder">
                    <p>Les infos s'afficheront ici</p>
                </div>
            </aside>
        </main>

    </div>

    <!-- Barre du bas -->
    <footer class="lib-footer">
        <span class="footer-tagline">Restez les joueurs de votre propre bibliothèque numérique</span>
        <?php if (!empty($_SESSION['username'])): ?>
            <span class="footer-user">
                👾 <?= htmlspecialchars($_SESSION['username']) ?>
                &nbsp;·&nbsp;
                <a href="logout.php" class="footer-logout">Déconnexion</a>
            </span>
        <?php else: ?>
            <a href="login.php" class="footer-login">Se connecter</a>
        <?php endif; ?>
        <span class="footer-brand">L'Antre des Dévoreurs de Contenu</span>
    </footer>

    <!-- Toast sauvegarde -->
    <div class="save-toast" id="saveToast">✓ Sauvegardé</div>

    <!-- Lightbox image -->
    <div class="lightbox" id="lightbox">
        <img id="lightboxImg" src="" alt="">
    </div>

    <!-- Modal recherche / ajout -->
    <div class="modal-overlay" id="modalSearch">
        <div class="modal">
            <button class="modal-close" id="modalClose">✕</button>
            <h2 class="modal-title">Ajouter un <?= $labels[$category] ?></h2>
            <?php if ($category === 'livre'): ?>
            <div class="search-bar search-bar--book">
                <select id="bookSubtype" class="select-book-search">
                    <option value="livre">📖 Livre / BD</option>
                    <option value="manga">🇯🇵 Manga</option>
                </select>
                <input type="text" id="searchInput" placeholder="Rechercher..." autocomplete="off">
                <button id="searchBtn">Rechercher</button>
            </div>
            <?php else: ?>
            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="Rechercher..." autocomplete="off">
                <button id="searchBtn">Rechercher</button>
            </div>
            <?php endif; ?>
            <div class="search-results" id="searchResults"></div>

            <div class="manual-sep">— ou ajouter manuellement —</div>
            <div class="manual-form">
                <input type="text"   id="manualTitle" class="manual-input" placeholder="Titre *" autocomplete="off">
                <input type="number" id="manualYear"  class="manual-input manual-input--short" placeholder="Année">
                <input type="text"   id="manualCover" class="manual-input" placeholder="URL de la cover (optionnel)">
                <button id="manualSubmit" class="btn-manual-add">+ Ajouter</button>
            </div>
        </div>
    </div>

    <script>
        // Données PHP → JS
        const CURRENT_CAT = '<?= $category ?>';
        const ITEMS = <?= json_encode($items) ?>;
    </script>
    <script src="js/library.js"></script>

</body>
</html>

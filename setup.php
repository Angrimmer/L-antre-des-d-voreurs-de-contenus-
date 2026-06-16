<?php
// Création du premier compte — accessible seulement si aucun utilisateur n'existe
require_once __DIR__ . '/config/database.php';

try {
    $db    = getDB();
    $count = $db->query('SELECT COUNT(*) FROM users')->fetchColumn();

    if ($count > 0) {
        header('Location: login.php');
        exit;
    }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        header('Location: login.php');
        exit;
    }

    if (strlen($password) < 6 || strlen($password) > 255) {
        header('Location: login.php?error=Mot+de+passe+invalide+(6-255+caractères)');
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
    $stmt->execute([$username, $hash]);

    header('Location: login.php?created=1');
    exit;

} catch (PDOException $e) {
    die('Erreur : ' . $e->getMessage());
}

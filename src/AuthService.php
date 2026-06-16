<?php

namespace Antre;

use PDO;

class AuthService
{
    public function __construct(private PDO $db) {}

    public function register(string $username, string $password): array
    {
        $username = trim($username);
        if (strlen($username) < 3 || strlen($username) > 50) {
            return ['error' => 'Nom d\'utilisateur invalide (3-50 caractères).'];
        }
        if (strlen($password) < 6 || strlen($password) > 255) {
            return ['error' => 'Mot de passe invalide (6-255 caractères).'];
        }

        $stmt = $this->db->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            return ['error' => 'Nom d\'utilisateur déjà pris.'];
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $this->db->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)')->execute([$username, $hash]);

        return ['success' => true, 'id' => (int) $this->db->lastInsertId()];
    }

    public function login(string $username, string $password): array
    {
        $stmt = $this->db->prepare('SELECT id, password_hash FROM users WHERE username = ?');
        $stmt->execute([trim($username)]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return ['error' => 'Identifiants incorrects.'];
        }

        return ['success' => true, 'id' => (int) $user['id']];
    }
}

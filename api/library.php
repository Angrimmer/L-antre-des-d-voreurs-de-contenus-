<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';

$userId = $_SESSION['user_id'] ?? 1;
$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = getDB();

    switch ($method) {

        // Récupérer les items d'une catégorie
        case 'GET':
            $cat = $_GET['cat'] ?? null;
            if (!$cat) {
                echo json_encode(['error' => 'Paramètre cat manquant']); exit;
            }
            $stmt = $db->prepare(
                'SELECT * FROM library_items WHERE user_id = ? AND category = ? ORDER BY title ASC'
            );
            $stmt->execute([$userId, $cat]);
            echo json_encode(['items' => $stmt->fetchAll()]);
            break;

        // Ajouter un item
        case 'POST':
            $body = json_decode(file_get_contents('php://input'), true);
            $required = ['external_id', 'title', 'category'];
            foreach ($required as $field) {
                if (empty($body[$field])) {
                    echo json_encode(['error' => "Champ manquant : {$field}"]); exit;
                }
            }
            $stmt = $db->prepare(
                'INSERT INTO library_items (user_id, category, external_id, title, cover_url, year, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE title = VALUES(title)'
            );
            $stmt->execute([
                $userId,
                $body['category'],
                $body['external_id'],
                $body['title'],
                $body['cover_url'] ?? null,
                $body['year']       ?? null,
                $body['status']     ?? 'planifie',
            ]);
            echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
            break;

        // Mettre à jour les champs d'un item
        case 'PUT':
            $body  = json_decode(file_get_contents('php://input'), true);
            $id    = (int)($body['id'] ?? 0);
            if (!$id) { echo json_encode(['error' => 'ID manquant']); exit; }

            $allowed = [
                'status', 'personal_rating', 'personal_notes',
                'planned_date', 'current_episode', 'current_season', 'airing_season',
                'temp_review', 'final_review',
            ];

            $fields = [];
            $params = [];

            foreach ($allowed as $col) {
                if (array_key_exists($col, $body)) {
                    $fields[] = "$col = ?";
                    $params[] = $body[$col] === '' ? null : $body[$col];
                }
            }

            if (empty($fields)) { echo json_encode(['success' => true]); exit; }

            $params[] = $id;
            $params[] = $userId;
            $db->prepare('UPDATE library_items SET ' . implode(', ', $fields) . ' WHERE id = ? AND user_id = ?')
               ->execute($params);
            echo json_encode(['success' => true]);
            break;

        // Supprimer un item
        case 'DELETE':
            $id = (int)($_GET['id'] ?? 0);
            if (!$id) { echo json_encode(['error' => 'ID manquant']); exit; }
            $stmt = $db->prepare('DELETE FROM library_items WHERE id = ? AND user_id = ?');
            $stmt->execute([$id, $userId]);
            echo json_encode(['success' => true]);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Méthode non autorisée']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur base de données : ' . $e->getMessage()]);
}

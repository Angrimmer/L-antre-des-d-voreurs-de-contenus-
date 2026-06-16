<?php

namespace Antre;

use PDO;

class LibraryService
{
    public function __construct(private PDO $db) {}

    public function getItems(int $userId, string $category): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM library_items WHERE user_id = ? AND category = ? ORDER BY title ASC'
        );
        $stmt->execute([$userId, $category]);
        return $stmt->fetchAll();
    }

    public function addItem(int $userId, array $body): array
    {
        foreach (['external_id', 'title', 'category'] as $field) {
            if (empty($body[$field])) {
                return ['error' => "Champ manquant : {$field}"];
            }
        }

        $stmt = $this->db->prepare(
            'INSERT INTO library_items (user_id, category, external_id, title, cover_url, year, status, book_type)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $userId,
            $body['category'],
            $body['external_id'],
            $body['title'],
            $body['cover_url'] ?? null,
            $body['year']      ?? null,
            $body['status']    ?? 'planifie',
            $body['book_type'] ?? null,
        ]);

        return ['success' => true, 'id' => (int) $this->db->lastInsertId()];
    }

    public function updateItem(int $userId, array $body): array
    {
        $id = (int) ($body['id'] ?? 0);
        if (!$id) {
            return ['error' => 'ID manquant'];
        }

        $allowed = [
            'status', 'personal_rating', 'personal_notes',
            'planned_date', 'current_episode', 'current_season', 'airing_season',
            'temp_review', 'final_review',
            'book_type', 'volumes_out', 'volumes_owned',
        ];

        $fields = [];
        $params = [];
        foreach ($allowed as $col) {
            if (array_key_exists($col, $body)) {
                $fields[] = "$col = ?";
                $params[] = $body[$col] === '' ? null : $body[$col];
            }
        }

        if (empty($fields)) {
            return ['success' => true];
        }

        $params[] = $id;
        $params[] = $userId;
        $this->db->prepare(
            'UPDATE library_items SET ' . implode(', ', $fields) . ' WHERE id = ? AND user_id = ?'
        )->execute($params);

        return ['success' => true];
    }

    public function deleteItem(int $userId, int $id): array
    {
        if (!$id) {
            return ['error' => 'ID manquant'];
        }

        $stmt = $this->db->prepare('DELETE FROM library_items WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);

        return ['success' => true];
    }
}

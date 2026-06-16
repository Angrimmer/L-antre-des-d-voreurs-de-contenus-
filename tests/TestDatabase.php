<?php

namespace Antre\Tests;

use PDO;

class TestDatabase
{
    public static function create(): PDO
    {
        $pdo = new PDO('sqlite::memory:', null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $pdo->exec("
            CREATE TABLE users (
                id            INTEGER PRIMARY KEY AUTOINCREMENT,
                username      TEXT UNIQUE NOT NULL,
                password_hash TEXT NOT NULL,
                created_at    TEXT DEFAULT (datetime('now'))
            );

            CREATE TABLE library_items (
                id               INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id          INTEGER NOT NULL,
                category         TEXT NOT NULL,
                external_id      TEXT NOT NULL,
                title            TEXT NOT NULL,
                cover_url        TEXT,
                year             TEXT,
                status           TEXT DEFAULT 'planifie',
                personal_rating  INTEGER,
                personal_notes   TEXT,
                planned_date     TEXT,
                current_episode  INTEGER,
                current_season   INTEGER,
                airing_season    INTEGER,
                temp_review      TEXT,
                final_review     TEXT,
                book_type        TEXT,
                volumes_out      INTEGER,
                volumes_owned    INTEGER,
                added_at         TEXT DEFAULT (datetime('now')),
                updated_at       TEXT DEFAULT (datetime('now')),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                UNIQUE (user_id, category, external_id)
            );
        ");

        return $pdo;
    }
}

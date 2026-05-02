CREATE DATABASE IF NOT EXISTS antre_devolib CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE antre_devolib;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS library_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category ENUM('film', 'serie', 'anime', 'jeu') NOT NULL,
    external_id VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    cover_url VARCHAR(500) DEFAULT NULL,
    year VARCHAR(10) DEFAULT NULL,
    status ENUM('en_cours', 'termine', 'planifie', 'abandonne') DEFAULT 'planifie',
    personal_rating TINYINT UNSIGNED DEFAULT NULL,
    personal_notes TEXT DEFAULT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_item (user_id, category, external_id)
);

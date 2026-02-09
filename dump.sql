CREATE DATABASE bllendr CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bllendr;

-- Tabella Film
CREATE TABLE movies (
    movie_id INT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    year INT,
    genres VARCHAR(255),
    INDEX idx_genres (genres)
);

-- Tabella Generi (normalizzata)
CREATE TABLE genres (
    genre_id INT AUTO_INCREMENT PRIMARY KEY,
    genre_name VARCHAR(50) UNIQUE NOT NULL
);

-- Tabella relazione Film-Generi (molti a molti)
CREATE TABLE movie_genres (
    movie_id INT,
    genre_id INT,
    PRIMARY KEY (movie_id, genre_id),
    FOREIGN KEY (movie_id) REFERENCES movies(movie_id),
    FOREIGN KEY (genre_id) REFERENCES genres(genre_id)
);

-- Tabella Ratings (opzionale, utile per l'algoritmo)
CREATE TABLE ratings (
    rating_id INT AUTO_INCREMENT PRIMARY KEY,
    movie_id INT,
    rating DECIMAL(2,1),
    FOREIGN KEY (movie_id) REFERENCES movies(movie_id)
);

-- Tabella per le preferenze degli utenti (per il tuo algoritmo blend)
CREATE TABLE user_preferences (
    preference_id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(100),
    user_number TINYINT, -- 1 o 2
    preferred_genres VARCHAR(255),
    min_year INT,
    max_duration INT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
-- Database Bllendr - Schema SQL
-- Progetto Maturità 5D

CREATE DATABASE IF NOT EXISTS bllendr CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bllendr;

-- Tabella Film principale
CREATE TABLE IF NOT EXISTS movies (
    movie_id INT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    title_clean VARCHAR(255), -- Titolo senza anno
    year INT,
    genres VARCHAR(255),
    avg_rating DECIMAL(3,2) DEFAULT 0,
    rating_count INT DEFAULT 0,
    INDEX idx_year (year),
    INDEX idx_genres (genres),
    INDEX idx_rating (avg_rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella Generi (normalizzata)
CREATE TABLE IF NOT EXISTS genres (
    genre_id INT AUTO_INCREMENT PRIMARY KEY,
    genre_name VARCHAR(50) UNIQUE NOT NULL,
    INDEX idx_name (genre_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella relazione Film-Generi (molti a molti)
CREATE TABLE IF NOT EXISTS movie_genres (
    movie_id INT,
    genre_id INT,
    PRIMARY KEY (movie_id, genre_id),
    FOREIGN KEY (movie_id) REFERENCES movies(movie_id) ON DELETE CASCADE,
    FOREIGN KEY (genre_id) REFERENCES genres(genre_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella Links (collegamenti esterni IMDb/TMDb)
CREATE TABLE IF NOT EXISTS movie_links (
    movie_id INT PRIMARY KEY,
    imdb_id VARCHAR(20),
    tmdb_id INT,
    FOREIGN KEY (movie_id) REFERENCES movies(movie_id) ON DELETE CASCADE,
    INDEX idx_imdb (imdb_id),
    INDEX idx_tmdb (tmdb_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella Rating (valutazioni aggregate)
CREATE TABLE IF NOT EXISTS ratings (
    rating_id INT AUTO_INCREMENT PRIMARY KEY,
    movie_id INT,
    user_id INT,
    rating DECIMAL(2,1) NOT NULL,
    timestamp INT,
    FOREIGN KEY (movie_id) REFERENCES movies(movie_id) ON DELETE CASCADE,
    INDEX idx_movie (movie_id),
    INDEX idx_rating (rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella Tags (etichette utenti)
CREATE TABLE IF NOT EXISTS tags (
    tag_id INT AUTO_INCREMENT PRIMARY KEY,
    movie_id INT,
    user_id INT,
    tag VARCHAR(255),
    timestamp INT,
    FOREIGN KEY (movie_id) REFERENCES movies(movie_id) ON DELETE CASCADE,
    INDEX idx_movie (movie_id),
    INDEX idx_tag (tag)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella per le sessioni di matching (per l'algoritmo Blend)
CREATE TABLE IF NOT EXISTS match_sessions (
    session_id VARCHAR(100) PRIMARY KEY,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed BOOLEAN DEFAULT FALSE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella preferenze utenti per sessione
CREATE TABLE IF NOT EXISTS user_preferences (
    preference_id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(100),
    user_number TINYINT NOT NULL, -- 1 o 2
    preferred_genres VARCHAR(255),
    excluded_genres VARCHAR(255),
    min_year INT,
    max_year INT,
    min_rating DECIMAL(2,1),
    max_duration INT, -- In minuti (campo futuro)
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES match_sessions(session_id) ON DELETE CASCADE,
    INDEX idx_session (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella risultati del matching
CREATE TABLE IF NOT EXISTS match_results (
    result_id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(100),
    movie_id INT,
    match_score DECIMAL(5,2), -- Punteggio algoritmo blend
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES match_sessions(session_id) ON DELETE CASCADE,
    FOREIGN KEY (movie_id) REFERENCES movies(movie_id) ON DELETE CASCADE,
    INDEX idx_session (session_id),
    INDEX idx_score (match_score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

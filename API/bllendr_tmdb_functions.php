<?php
/**
 * Esempio di funzioni da usare nella pagina principale di Bllendr
 * per mostrare film con dati TMDb durante il processo di "blend"
 */

require_once 'config.php';
require_once 'TMDbAPI.php';

/**
 * Ottiene film consigliati basati sulle preferenze di due utenti
 * @param array $user1_prefs - Preferenze utente 1 (generi, anno, durata)
 * @param array $user2_prefs - Preferenze utente 2 (generi, anno, durata)
 * @return array - Lista di film che matchano entrambe le preferenze
 */
function getBlendedMovies($user1_prefs, $user2_prefs) {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $tmdb = new TMDbAPI(TMDB_API_KEY);
    
    // Trova generi in comune
    $user1_genres = explode(',', $user1_prefs['genres']);
    $user2_genres = explode(',', $user2_prefs['genres']);
    $common_genres = array_intersect($user1_genres, $user2_genres);
    
    // Se non ci sono generi in comune, prendi tutti i generi di entrambi
    if (empty($common_genres)) {
        $all_genres = array_merge($user1_genres, $user2_genres);
        $genres_condition = "genres REGEXP '" . implode('|', $all_genres) . "'";
    } else {
        $genres_condition = "genres REGEXP '" . implode('|', $common_genres) . "'";
    }
    
    // Range anno (prendi l'overlap)
    $min_year = max($user1_prefs['min_year'], $user2_prefs['min_year']);
    $max_year = min($user1_prefs['max_year'], $user2_prefs['max_year']);
    
    // Range durata (prendi l'overlap)
    $max_duration = min($user1_prefs['max_duration'], $user2_prefs['max_duration']);
    
    // Query per trovare film che matchano
    $sql = "SELECT movie_id, title, year, genres, overview, poster_path, 
                   backdrop_path, runtime, vote_average, trailer_key
            FROM movies
            WHERE $genres_condition
            AND year BETWEEN $min_year AND $max_year
            AND runtime <= $max_duration
            AND runtime > 0
            AND poster_path IS NOT NULL
            AND vote_average >= 6.5
            ORDER BY vote_average DESC, RAND()
            LIMIT 20";
    
    $result = $conn->query($sql);
    $movies = [];
    
    while ($movie = $result->fetch_assoc()) {
        // Aggiungi URL poster
        $movie['poster_url'] = $tmdb->getPosterUrl($movie['poster_path'], 'w342');
        $movie['backdrop_url'] = $tmdb->getBackdropUrl($movie['backdrop_path']);
        
        // Calcola "match score" in percentuale
        $match_score = calculateMatchScore($movie, $user1_prefs, $user2_prefs);
        $movie['match_score'] = $match_score;
        
        $movies[] = $movie;
    }
    
    // Ordina per match score
    usort($movies, function($a, $b) {
        return $b['match_score'] <=> $a['match_score'];
    });
    
    $conn->close();
    return $movies;
}

/**
 * Calcola quanto un film matcha con le preferenze di entrambi gli utenti
 * @return int - Score da 0 a 100
 */
function calculateMatchScore($movie, $user1_prefs, $user2_prefs) {
    $score = 0;
    $max_score = 100;
    
    // Generi (40 punti)
    $movie_genres = explode('|', $movie['genres']);
    $user1_genres = explode(',', $user1_prefs['genres']);
    $user2_genres = explode(',', $user2_prefs['genres']);
    
    $u1_match = count(array_intersect($movie_genres, $user1_genres));
    $u2_match = count(array_intersect($movie_genres, $user2_genres));
    $genre_score = ($u1_match + $u2_match) * 8; // max 40 punti
    $score += min($genre_score, 40);
    
    // Anno (20 punti)
    $u1_year_diff = abs($movie['year'] - $user1_prefs['preferred_year']);
    $u2_year_diff = abs($movie['year'] - $user2_prefs['preferred_year']);
    $avg_year_diff = ($u1_year_diff + $u2_year_diff) / 2;
    $year_score = max(0, 20 - $avg_year_diff);
    $score += $year_score;
    
    // Durata (20 punti)
    $u1_duration_diff = abs($movie['runtime'] - $user1_prefs['preferred_duration']);
    $u2_duration_diff = abs($movie['runtime'] - $user2_prefs['preferred_duration']);
    $avg_duration_diff = ($u1_duration_diff + $u2_duration_diff) / 2;
    $duration_score = max(0, 20 - ($avg_duration_diff / 5));
    $score += $duration_score;
    
    // Valutazione TMDb (20 punti)
    $rating_score = ($movie['vote_average'] / 10) * 20;
    $score += $rating_score;
    
    return round(min($score, $max_score));
}

/**
 * HTML per mostrare un singolo film nella UI di Bllendr
 */
function renderMovieCard($movie) {
    $poster = htmlspecialchars($movie['poster_url']);
    $title = htmlspecialchars($movie['title']);
    $year = $movie['year'];
    $rating = number_format($movie['vote_average'], 1);
    $match = $movie['match_score'];
    $runtime = $movie['runtime'];
    
    return "
    <div class='movie-card' data-movie-id='{$movie['movie_id']}'>
        <div class='movie-poster'>
            <img src='$poster' alt='$title'>
            <div class='match-badge'>{$match}% Match</div>
        </div>
        <div class='movie-info'>
            <h3 class='movie-title'>$title</h3>
            <div class='movie-meta'>
                <span class='year'>$year</span>
                <span class='rating'>⭐ $rating</span>
                <span class='duration'>{$runtime}min</span>
            </div>
            <button class='btn-details' onclick='showMovieDetails({$movie['movie_id']})'>
                Dettagli
            </button>
        </div>
    </div>
    ";
}

/**
 * Esempio di utilizzo per la pagina blend_results.php
 */
function exampleUsage() {
    // Simula preferenze di due utenti
    $user1 = [
        'genres' => 'Action,Adventure,Sci-Fi',
        'min_year' => 2010,
        'max_year' => 2024,
        'max_duration' => 150,
        'preferred_year' => 2020,
        'preferred_duration' => 120
    ];
    
    $user2 = [
        'genres' => 'Action,Thriller,Drama',
        'min_year' => 2015,
        'max_year' => 2024,
        'max_duration' => 140,
        'preferred_year' => 2018,
        'preferred_duration' => 130
    ];
    
    // Ottieni film blended
    $movies = getBlendedMovies($user1, $user2);
    
    // Mostra i film
    echo '<div class="movies-grid">';
    foreach ($movies as $movie) {
        echo renderMovieCard($movie);
    }
    echo '</div>';
}

// CSS per le card dei film
function getMovieCardCSS() {
    return "
    <style>
        .movies-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
            padding: 20px;
        }
        
        .movie-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }
        
        .movie-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        }
        
        .movie-poster {
            position: relative;
            width: 100%;
            height: 375px;
            overflow: hidden;
        }
        
        .movie-poster img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .match-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        
        .movie-info {
            padding: 15px;
        }
        
        .movie-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .movie-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            color: #666;
            margin-bottom: 15px;
        }
        
        .movie-meta .rating {
            color: #667eea;
            font-weight: bold;
        }
        
        .btn-details {
            width: 100%;
            padding: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            transition: opacity 0.3s;
        }
        
        .btn-details:hover {
            opacity: 0.9;
        }
    </style>
    ";
}
?>

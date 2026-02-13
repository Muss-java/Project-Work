<?php
/**
 * Script per sincronizzare i film di MovieLens con TMDb
 * Arricchisce il database con poster, trame, trailer, etc.
 */

require_once 'config.php';
require_once 'TMDbAPI.php';

// Connessione al database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Errore connessione: " . $conn->connect_error);
}

// Inizializza API TMDb
$tmdb = new TMDbAPI(TMDB_API_KEY);

// Aggiungi nuove colonne alla tabella movies se non esistono già
$alter_queries = [
    "ALTER TABLE movies ADD COLUMN IF NOT EXISTS tmdb_id INT NULL",
    "ALTER TABLE movies ADD COLUMN IF NOT EXISTS overview TEXT NULL",
    "ALTER TABLE movies ADD COLUMN IF NOT EXISTS poster_path VARCHAR(255) NULL",
    "ALTER TABLE movies ADD COLUMN IF NOT EXISTS backdrop_path VARCHAR(255) NULL",
    "ALTER TABLE movies ADD COLUMN IF NOT EXISTS runtime INT NULL",
    "ALTER TABLE movies ADD COLUMN IF NOT EXISTS vote_average DECIMAL(3,1) NULL",
    "ALTER TABLE movies ADD COLUMN IF NOT EXISTS release_date DATE NULL",
    "ALTER TABLE movies ADD COLUMN IF NOT EXISTS trailer_key VARCHAR(50) NULL",
    "ALTER TABLE movies ADD COLUMN IF NOT EXISTS synced_at TIMESTAMP NULL",
    "ALTER TABLE movies ADD INDEX IF NOT EXISTS idx_tmdb_id (tmdb_id)"
];

echo "<h2>🎬 Sincronizzazione MovieLens → TMDb</h2>";
echo "<p>Preparazione database...</p>";

foreach ($alter_queries as $query) {
    // Ignora errori se le colonne esistono già
    @$conn->query($query);
}

// Parametri per la sincronizzazione
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50; // Numero di film da sincronizzare per volta
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

// Seleziona film non ancora sincronizzati
$sql = "SELECT movie_id, title, year FROM movies 
        WHERE tmdb_id IS NULL 
        ORDER BY movie_id 
        LIMIT $limit OFFSET $offset";

$result = $conn->query($sql);
$total = $result->num_rows;

echo "<p>Sincronizzazione di <strong>$total film</strong> (da $offset a " . ($offset + $limit) . ")...</p>";
echo "<div style='background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px;'>";

$synced = 0;
$errors = 0;

while ($row = $result->fetch_assoc()) {
    $movie_id = $row['movie_id'];
    $title = $row['title'];
    $year = $row['year'];
    
    // Rimuovi anno dal titolo se presente (es. "Toy Story (1995)" → "Toy Story")
    $clean_title = preg_replace('/\s*\(\d{4}\)\s*$/', '', $title);
    
    echo "<p>🔍 Cerco: <strong>$clean_title</strong> ($year)...</p>";
    
    // Cerca il film su TMDb
    $search = $tmdb->searchMovie($clean_title, $year);
    
    if (isset($search['results']) && count($search['results']) > 0) {
        $movie = $search['results'][0]; // Prendi il primo risultato
        $tmdb_id = $movie['id'];
        
        // Ottieni dettagli completi
        $details = $tmdb->getMovieDetails($tmdb_id);
        
        if (!isset($details['error'])) {
            // Estrai trailer (se disponibile)
            $trailer_key = null;
            if (isset($details['videos']['results'])) {
                foreach ($details['videos']['results'] as $video) {
                    if ($video['type'] == 'Trailer' && $video['site'] == 'YouTube') {
                        $trailer_key = $video['key'];
                        break;
                    }
                }
            }
            
            // Prepara dati per l'update
            $overview = $conn->real_escape_string($details['overview'] ?? '');
            $poster_path = $conn->real_escape_string($details['poster_path'] ?? '');
            $backdrop_path = $conn->real_escape_string($details['backdrop_path'] ?? '');
            $runtime = (int)($details['runtime'] ?? 0);
            $vote_average = (float)($details['vote_average'] ?? 0);
            $release_date = $details['release_date'] ?? null;
            $trailer = $conn->real_escape_string($trailer_key ?? '');
            
            // Aggiorna il database
            $update_sql = "UPDATE movies SET 
                tmdb_id = $tmdb_id,
                overview = '$overview',
                poster_path = '$poster_path',
                backdrop_path = '$backdrop_path',
                runtime = $runtime,
                vote_average = $vote_average,
                release_date = " . ($release_date ? "'$release_date'" : "NULL") . ",
                trailer_key = '$trailer',
                synced_at = NOW()
                WHERE movie_id = $movie_id";
            
            if ($conn->query($update_sql)) {
                echo "<p style='color: green;'>✅ Sincronizzato: $clean_title (TMDb ID: $tmdb_id)</p>";
                $synced++;
            } else {
                echo "<p style='color: red;'>❌ Errore aggiornamento DB: $clean_title</p>";
                $errors++;
            }
        } else {
            echo "<p style='color: orange;'>⚠️ Errore dettagli TMDb: $clean_title</p>";
            $errors++;
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Non trovato su TMDb: $clean_title</p>";
        $errors++;
    }
    
    // Piccola pausa per non sovraccaricare l'API (rate limit: 40 req/10 sec)
    usleep(250000); // 0.25 secondi
    
    // Flush output per mostrare progresso in tempo reale
    if (ob_get_level() > 0) {
        ob_flush();
        flush();
    }
}

echo "</div>";

echo "<hr>";
echo "<h3>📊 Riepilogo</h3>";
echo "<p>✅ Film sincronizzati: <strong>$synced</strong></p>";
echo "<p>❌ Errori: <strong>$errors</strong></p>";

// Conta quanti film rimangono da sincronizzare
$remaining_sql = "SELECT COUNT(*) as count FROM movies WHERE tmdb_id IS NULL";
$remaining_result = $conn->query($remaining_sql);
$remaining = $remaining_result->fetch_assoc()['count'];

echo "<p>📋 Film rimanenti da sincronizzare: <strong>$remaining</strong></p>";

if ($remaining > 0) {
    $next_offset = $offset + $limit;
    echo "<hr>";
    echo "<p><a href='sync_tmdb.php?limit=$limit&offset=$next_offset' style='display: inline-block; padding: 10px 20px; background: #01b4e4; color: white; text-decoration: none; border-radius: 5px;'>
        ▶️ Continua sincronizzazione (prossimi $limit film)
    </a></p>";
} else {
    echo "<hr>";
    echo "<h2 style='color: green;'>🎉 Sincronizzazione completata!</h2>";
    echo "<p><a href='test_tmdb.php' style='display: inline-block; padding: 10px 20px; background: #01b4e4; color: white; text-decoration: none; border-radius: 5px;'>
        🎬 Testa l'integrazione TMDb
    </a></p>";
}

$conn->close();
?>

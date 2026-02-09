<?php
/**
 * Script di importazione MovieLens per Bllendr
 * Progetto Maturità 5D - Vacondio & Mouanid
 * 
 * Istruzioni:
 * 1. Posiziona questo file nella cartella del dataset MovieLens
 * 2. Assicurati che XAMPP sia avviato
 * 3. Esegui da browser: http://localhost/percorso/import_movielens.php
 */

// === CONFIGURAZIONE DATABASE ===
$config = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',  // Modifica se hai impostato una password
    'db' => 'bllendr'
];

// === CONFIGURAZIONE FILE CSV ===
$csv_files = [
    'movies' => 'movies.csv',
    'ratings' => 'ratings.csv',
    'links' => 'links.csv',
    'tags' => 'tags.csv'
];

// Limita il numero di rating da importare (troppi potrebbero rallentare)
$MAX_RATINGS = 10000; // Imposta a null per importare tutto

set_time_limit(300); // 5 minuti di timeout
ini_set('memory_limit', '512M');

echo "<!DOCTYPE html>
<html>
<head>
    <title>Import MovieLens - Bllendr</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #333; }
        .step { padding: 10px; margin: 10px 0; border-left: 4px solid #4CAF50; background: #f9f9f9; }
        .success { color: #4CAF50; }
        .error { color: #f44336; }
        .info { color: #2196F3; }
        .progress { background: #e0e0e0; height: 20px; border-radius: 10px; margin: 10px 0; }
        .progress-bar { background: #4CAF50; height: 100%; border-radius: 10px; transition: width 0.3s; }
    </style>
</head>
<body>
<div class='container'>
<h1>🎬 Import Dataset MovieLens per Bllendr</h1>";

// === CONNESSIONE AL DATABASE ===
echo "<div class='step'><strong>Step 1:</strong> Connessione al database...</div>";
$conn = new mysqli($config['host'], $config['user'], $config['pass']);

if ($conn->connect_error) {
    die("<p class='error'>❌ Errore connessione: " . $conn->connect_error . "</p></div></body></html>");
}

// Crea database se non esiste
$conn->query("CREATE DATABASE IF NOT EXISTS {$config['db']} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db($config['db']);
echo "<p class='success'>✅ Connesso al database '{$config['db']}'</p>";

// === IMPORTA SCHEMA ===
echo "<div class='step'><strong>Step 2:</strong> Creazione tabelle...</div>";
$schema_file = 'bllendr_schema.sql';
if (file_exists($schema_file)) {
    $schema = file_get_contents($schema_file);
    $queries = explode(';', $schema);
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            $conn->query($query);
        }
    }
    echo "<p class='success'>✅ Schema database creato</p>";
} else {
    echo "<p class='info'>ℹ️ File schema non trovato, uso tabelle esistenti</p>";
}

// === FUNZIONI HELPER ===

/**
 * Estrae l'anno dal titolo (es. "Toy Story (1995)" -> 1995)
 */
function extractYear($title) {
    if (preg_match('/\((\d{4})\)/', $title, $matches)) {
        return (int)$matches[1];
    }
    return null;
}

/**
 * Rimuove l'anno dal titolo
 */
function cleanTitle($title) {
    return trim(preg_replace('/\s*\(\d{4}\)\s*$/', '', $title));
}

/**
 * Mostra progresso
 */
function showProgress($current, $total, $label) {
    $percent = ($current / $total) * 100;
    echo "<div style='margin: 5px 0;'>
            <small>$label: $current / $total</small>
            <div class='progress'><div class='progress-bar' style='width: {$percent}%'></div></div>
          </div>";
    flush();
    ob_flush();
}

// === IMPORTAZIONE MOVIES ===
echo "<div class='step'><strong>Step 3:</strong> Importazione film...</div>";

if (!file_exists($csv_files['movies'])) {
    die("<p class='error'>❌ File {$csv_files['movies']} non trovato!</p></div></body></html>");
}

$file = fopen($csv_files['movies'], 'r');
fgetcsv($file); // Salta header

$movie_count = 0;
$genre_list = [];

// Prima passata: conta righe
$total_movies = 0;
while (fgetcsv($file) !== false) $total_movies++;
rewind($file);
fgetcsv($file); // Salta header di nuovo

echo "<p class='info'>📊 Trovati $total_movies film da importare</p>";

while ($row = fgetcsv($file)) {
    $movie_id = (int)$row[0];
    $title = $conn->real_escape_string($row[1]);
    $genres = $row[2];
    
    $year = extractYear($row[1]);
    $title_clean = $conn->real_escape_string(cleanTitle($row[1]));
    $genres_escaped = $conn->real_escape_string($genres);
    
    // Inserisci film
    $year_sql = $year ? $year : 'NULL';
    $sql = "INSERT INTO movies (movie_id, title, title_clean, year, genres) 
            VALUES ($movie_id, '$title', '$title_clean', $year_sql, '$genres_escaped')
            ON DUPLICATE KEY UPDATE 
            title = '$title', title_clean = '$title_clean', year = $year_sql, genres = '$genres_escaped'";
    $conn->query($sql);
    
    // Raccogli generi unici
    if ($genres && $genres !== '(no genres listed)') {
        $genre_array = explode('|', $genres);
        foreach ($genre_array as $genre) {
            $genre = trim($genre);
            if (!in_array($genre, $genre_list)) {
                $genre_list[] = $genre;
            }
        }
    }
    
    $movie_count++;
    if ($movie_count % 500 == 0) {
        showProgress($movie_count, $total_movies, "Film importati");
    }
}

fclose($file);
echo "<p class='success'>✅ Importati $movie_count film</p>";

// === IMPORTAZIONE GENERI ===
echo "<div class='step'><strong>Step 4:</strong> Importazione generi...</div>";

foreach ($genre_list as $genre) {
    $genre_escaped = $conn->real_escape_string($genre);
    $conn->query("INSERT IGNORE INTO genres (genre_name) VALUES ('$genre_escaped')");
}

echo "<p class='success'>✅ Importati " . count($genre_list) . " generi unici</p>";

// === RELAZIONE FILM-GENERI ===
echo "<div class='step'><strong>Step 5:</strong> Creazione relazioni film-generi...</div>";

$result = $conn->query("SELECT movie_id, genres FROM movies WHERE genres IS NOT NULL AND genres != '(no genres listed)'");
$relation_count = 0;

while ($row = $result->fetch_assoc()) {
    $movie_id = $row['movie_id'];
    $genres = explode('|', $row['genres']);
    
    foreach ($genres as $genre) {
        $genre = trim($genre);
        $genre_result = $conn->query("SELECT genre_id FROM genres WHERE genre_name = '" . $conn->real_escape_string($genre) . "'");
        
        if ($genre_row = $genre_result->fetch_assoc()) {
            $genre_id = $genre_row['genre_id'];
            $conn->query("INSERT IGNORE INTO movie_genres (movie_id, genre_id) VALUES ($movie_id, $genre_id)");
            $relation_count++;
        }
    }
}

echo "<p class='success'>✅ Create $relation_count relazioni film-generi</p>";

// === IMPORTAZIONE LINKS ===
echo "<div class='step'><strong>Step 6:</strong> Importazione links esterni...</div>";

if (file_exists($csv_files['links'])) {
    $file = fopen($csv_files['links'], 'r');
    fgetcsv($file); // Salta header
    
    $link_count = 0;
    while ($row = fgetcsv($file)) {
        $movie_id = (int)$row[0];
        $imdb_id = $row[1] ? "'" . $conn->real_escape_string($row[1]) . "'" : 'NULL';
        $tmdb_id = $row[2] && $row[2] !== '' ? (int)$row[2] : 'NULL';
        
        $conn->query("INSERT INTO movie_links (movie_id, imdb_id, tmdb_id) 
                     VALUES ($movie_id, $imdb_id, $tmdb_id)
                     ON DUPLICATE KEY UPDATE imdb_id = $imdb_id, tmdb_id = $tmdb_id");
        $link_count++;
    }
    
    fclose($file);
    echo "<p class='success'>✅ Importati $link_count links</p>";
} else {
    echo "<p class='info'>ℹ️ File links.csv non trovato, saltato</p>";
}

// === IMPORTAZIONE RATINGS ===
echo "<div class='step'><strong>Step 7:</strong> Importazione ratings...</div>";

if (file_exists($csv_files['ratings'])) {
    $file = fopen($csv_files['ratings'], 'r');
    fgetcsv($file); // Salta header
    
    $rating_count = 0;
    $total_ratings_file = 0;
    
    // Conta ratings
    while (fgetcsv($file) !== false) $total_ratings_file++;
    rewind($file);
    fgetcsv($file);
    
    $limit = $MAX_RATINGS ?? $total_ratings_file;
    echo "<p class='info'>📊 Importazione di $limit ratings (su $total_ratings_file totali)</p>";
    
    while ($row = fgetcsv($file)) {
        if ($MAX_RATINGS && $rating_count >= $MAX_RATINGS) break;
        
        $user_id = (int)$row[0];
        $movie_id = (int)$row[1];
        $rating = (float)$row[2];
        $timestamp = (int)$row[3];
        
        $conn->query("INSERT INTO ratings (movie_id, user_id, rating, timestamp) 
                     VALUES ($movie_id, $user_id, $rating, $timestamp)");
        
        $rating_count++;
        if ($rating_count % 1000 == 0) {
            showProgress($rating_count, $limit, "Ratings importati");
        }
    }
    
    fclose($file);
    echo "<p class='success'>✅ Importati $rating_count ratings</p>";
} else {
    echo "<p class='info'>ℹ️ File ratings.csv non trovato, saltato</p>";
}

// === CALCOLO RATING MEDIO ===
echo "<div class='step'><strong>Step 8:</strong> Calcolo rating medi per film...</div>";

$conn->query("UPDATE movies m 
              SET avg_rating = (
                  SELECT AVG(rating) 
                  FROM ratings r 
                  WHERE r.movie_id = m.movie_id
              ),
              rating_count = (
                  SELECT COUNT(*) 
                  FROM ratings r 
                  WHERE r.movie_id = m.movie_id
              )");

echo "<p class='success'>✅ Rating medi calcolati</p>";

// === IMPORTAZIONE TAGS ===
echo "<div class='step'><strong>Step 9:</strong> Importazione tags...</div>";

if (file_exists($csv_files['tags'])) {
    $file = fopen($csv_files['tags'], 'r');
    fgetcsv($file); // Salta header
    
    $tag_count = 0;
    while ($row = fgetcsv($file)) {
        $user_id = (int)$row[0];
        $movie_id = (int)$row[1];
        $tag = $conn->real_escape_string($row[2]);
        $timestamp = (int)$row[3];
        
        $conn->query("INSERT INTO tags (movie_id, user_id, tag, timestamp) 
                     VALUES ($movie_id, $user_id, '$tag', $timestamp)");
        $tag_count++;
    }
    
    fclose($file);
    echo "<p class='success'>✅ Importati $tag_count tags</p>";
} else {
    echo "<p class='info'>ℹ️ File tags.csv non trovato, saltato</p>";
}

// === STATISTICHE FINALI ===
echo "<div class='step'><strong>✅ IMPORTAZIONE COMPLETATA!</strong></div>";

$stats = [
    'Film totali' => $conn->query("SELECT COUNT(*) as c FROM movies")->fetch_assoc()['c'],
    'Generi unici' => $conn->query("SELECT COUNT(*) as c FROM genres")->fetch_assoc()['c'],
    'Ratings totali' => $conn->query("SELECT COUNT(*) as c FROM ratings")->fetch_assoc()['c'],
    'Tags totali' => $conn->query("SELECT COUNT(*) as c FROM tags")->fetch_assoc()['c'],
    'Film con rating > 4' => $conn->query("SELECT COUNT(*) as c FROM movies WHERE avg_rating >= 4")->fetch_assoc()['c']
];

echo "<div style='background: #e8f5e9; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>📊 Statistiche Database</h3><ul>";
foreach ($stats as $label => $value) {
    echo "<li><strong>$label:</strong> " . number_format($value) . "</li>";
}
echo "</ul></div>";

// === QUERY DI TEST ===
echo "<div class='step'><strong>🔍 Query di Test</strong></div>";

echo "<h4>Top 10 Film per Rating Medio (con almeno 50 voti):</h4>";
$top_movies = $conn->query("SELECT title, year, avg_rating, rating_count 
                            FROM movies 
                            WHERE rating_count >= 50 
                            ORDER BY avg_rating DESC, rating_count DESC 
                            LIMIT 10");

echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Titolo</th><th>Anno</th><th>Rating</th><th>Voti</th></tr>";
while ($row = $top_movies->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['title']) . "</td>";
    echo "<td>" . ($row['year'] ?? 'N/A') . "</td>";
    echo "<td>" . number_format($row['avg_rating'], 2) . " ⭐</td>";
    echo "<td>" . number_format($row['rating_count']) . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h4>Distribuzione Generi:</h4>";
$genre_stats = $conn->query("SELECT g.genre_name, COUNT(mg.movie_id) as count 
                             FROM genres g 
                             LEFT JOIN movie_genres mg ON g.genre_id = mg.genre_id 
                             GROUP BY g.genre_id 
                             ORDER BY count DESC");

echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>Genere</th><th>Numero Film</th></tr>";
while ($row = $genre_stats->fetch_assoc()) {
    echo "<tr><td>" . htmlspecialchars($row['genre_name']) . "</td><td>" . number_format($row['count']) . "</td></tr>";
}
echo "</table>";

$conn->close();

echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>🎯 Prossimi Passi</h3>";
echo "<ol>";
echo "<li>Il database è pronto per essere usato con Bllendr</li>";
echo "<li>Puoi iniziare a sviluppare l'algoritmo di 'blend' delle preferenze</li>";
echo "<li>Usa le tabelle <code>user_preferences</code> e <code>match_sessions</code> per salvare i dati degli utenti</li>";
echo "<li>Query esempio per recuperare film: <code>SELECT * FROM movies WHERE avg_rating >= 3.5 ORDER BY RAND() LIMIT 10</code></li>";
echo "</ol></div>";

echo "</div></body></html>";
?>

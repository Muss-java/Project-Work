<?php
/**
 * API endpoint per ottenere i dettagli di un film
 * Usato dalla pagina test_tmdb.php per il modal
 */

header('Content-Type: application/json');

require_once 'config.php';
require_once 'TMDbAPI.php';

// Connessione database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    echo json_encode(['error' => 'Errore connessione database']);
    exit;
}

// Inizializza API
$tmdb = new TMDbAPI(TMDB_API_KEY);

// Ottieni ID film dalla query string
$movie_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($movie_id === 0) {
    echo json_encode(['error' => 'ID film non valido']);
    exit;
}

// Query per ottenere i dati del film
$sql = "SELECT movie_id, title, year, genres, tmdb_id, overview, poster_path, 
               backdrop_path, runtime, vote_average, release_date, trailer_key
        FROM movies 
        WHERE movie_id = $movie_id";

$result = $conn->query($sql);

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Film non trovato']);
    exit;
}

$movie = $result->fetch_assoc();

// Prepara risposta JSON
$response = [
    'movie_id' => $movie['movie_id'],
    'title' => $movie['title'],
    'year' => $movie['year'],
    'genres' => $movie['genres'],
    'overview' => $movie['overview'],
    'runtime' => $movie['runtime'],
    'vote_average' => $movie['vote_average'],
    'release_date' => $movie['release_date'],
    'trailer_key' => $movie['trailer_key'],
    'poster_path' => $movie['poster_path'],
    'poster_url' => $movie['poster_path'] ? $tmdb->getPosterUrl($movie['poster_path'], 'w500') : null,
    'backdrop_path' => $movie['backdrop_path'],
    'backdrop_url' => $movie['backdrop_path'] ? $tmdb->getBackdropUrl($movie['backdrop_path']) : null
];

echo json_encode($response);

$conn->close();
?>

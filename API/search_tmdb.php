<?php
/**
 * API endpoint per ricerca film su TMDb in tempo reale
 * Ritorna risultati in formato JSON
 */

header('Content-Type: application/json');

require_once 'config.php';
require_once 'TMDbAPI.php';

// Inizializza API
$tmdb = new TMDbAPI(TMDB_API_KEY);

// Ottieni query dalla richiesta
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (empty($query)) {
    echo json_encode(['error' => 'Query di ricerca mancante']);
    exit;
}

// Cerca film
$results = $tmdb->searchMovie($query);

if (isset($results['error'])) {
    echo json_encode(['error' => 'Errore nella ricerca: ' . $results['error']]);
    exit;
}

// Ritorna risultati
echo json_encode($results);
?>

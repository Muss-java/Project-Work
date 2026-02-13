<?php
/**
 * Classe per gestire le chiamate all'API di TMDb (The Movie Database)
 * Per il progetto Bllendr
 */
class TMDbAPI {
    private $api_key;
    private $base_url = 'https://api.themoviedb.org/3';
    private $image_base_url = 'https://image.tmdb.org/t/p/';
    
    /**
     * Costruttore - Inizializza con la tua API key
     */
    public function __construct($api_key) {
        $this->api_key = $api_key;
    }
    
    /**
     * Funzione privata per fare richieste HTTP all'API
     */
    private function makeRequest($endpoint, $params = []) {
        $params['api_key'] = $this->api_key;
        $params['language'] = 'it-IT'; // Risultati in italiano
        
        $url = $this->base_url . $endpoint . '?' . http_build_query($params);
        
        // Usa cURL per la richiesta
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code == 200) {
            return json_decode($response, true);
        } else {
            return ['error' => 'Errore API: HTTP ' . $http_code];
        }
    }
    
    /**
     * Cerca un film per titolo
     * @param string $title - Titolo del film
     * @return array - Risultati della ricerca
     */
    public function searchMovie($title, $year = null) {
        $params = ['query' => $title];
        if ($year) {
            $params['year'] = $year;
        }
        
        $result = $this->makeRequest('/search/movie', $params);
        return $result;
    }
    
    /**
     * Ottieni dettagli completi di un film tramite TMDb ID
     * @param int $tmdb_id - ID del film su TMDb
     * @return array - Dettagli del film
     */
    public function getMovieDetails($tmdb_id) {
        $result = $this->makeRequest('/movie/' . $tmdb_id, [
            'append_to_response' => 'videos,credits,images'
        ]);
        return $result;
    }
    
    /**
     * Ottieni i video/trailer di un film
     * @param int $tmdb_id - ID del film su TMDb
     * @return array - Lista di video/trailer
     */
    public function getMovieVideos($tmdb_id) {
        $result = $this->makeRequest('/movie/' . $tmdb_id . '/videos');
        return $result;
    }
    
    /**
     * Cerca film per genere
     * @param int $genre_id - ID del genere TMDb
     * @return array - Lista di film
     */
    public function getMoviesByGenre($genre_id, $page = 1) {
        $result = $this->makeRequest('/discover/movie', [
            'with_genres' => $genre_id,
            'page' => $page,
            'sort_by' => 'popularity.desc'
        ]);
        return $result;
    }
    
    /**
     * Ottieni film popolari
     * @param int $page - Numero pagina
     * @return array - Lista di film popolari
     */
    public function getPopularMovies($page = 1) {
        $result = $this->makeRequest('/movie/popular', ['page' => $page]);
        return $result;
    }
    
    /**
     * Ottieni URL completo per l'immagine del poster
     * @param string $poster_path - Path del poster da TMDb
     * @param string $size - Dimensione (w92, w154, w185, w342, w500, w780, original)
     * @return string - URL completo del poster
     */
    public function getPosterUrl($poster_path, $size = 'w500') {
        if (empty($poster_path)) {
            return null;
        }
        return $this->image_base_url . $size . $poster_path;
    }
    
    /**
     * Ottieni URL completo per l'immagine di sfondo
     * @param string $backdrop_path - Path dello sfondo da TMDb
     * @param string $size - Dimensione (w300, w780, w1280, original)
     * @return string - URL completo dello sfondo
     */
    public function getBackdropUrl($backdrop_path, $size = 'w1280') {
        if (empty($backdrop_path)) {
            return null;
        }
        return $this->image_base_url . $size . $backdrop_path;
    }
    
    /**
     * Ottieni lista di tutti i generi disponibili
     * @return array - Lista generi
     */
    public function getGenreList() {
        $result = $this->makeRequest('/genre/movie/list');
        return $result;
    }
    
    /**
     * Cerca film simili a uno specifico
     * @param int $tmdb_id - ID del film su TMDb
     * @return array - Lista di film simili
     */
    public function getSimilarMovies($tmdb_id, $page = 1) {
        $result = $this->makeRequest('/movie/' . $tmdb_id . '/similar', ['page' => $page]);
        return $result;
    }
    
    /**
     * Ottieni raccomandazioni basate su un film
     * @param int $tmdb_id - ID del film su TMDb
     * @return array - Lista di film raccomandati
     */
    public function getRecommendations($tmdb_id, $page = 1) {
        $result = $this->makeRequest('/movie/' . $tmdb_id . '/recommendations', ['page' => $page]);
        return $result;
    }
}
?>

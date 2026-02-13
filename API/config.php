<?php
/**
 * File di configurazione per TMDb API
 * IMPORTANTE: Non condividere questo file pubblicamente!
 */

// La tua API key di TMDb
define('TMDB_API_KEY', 'INSERISCI_QUI_LA_TUA_API_KEY');

// Configurazione database
define('DB_HOST', 'localhost');
define('DB_NAME', 'bllendr');
define('DB_USER', 'root');
define('DB_PASS', '');

// Impostazioni cache (opzionale - per non sovraccaricare l'API)
define('CACHE_ENABLED', true);
define('CACHE_DURATION', 86400); // 24 ore in secondi

?>

<?php
/**
 * Script per processare le preferenze degli utenti
 * (Opzionale - attualmente si usa sessionStorage JavaScript)
 * 
 * Questo file può essere usato per salvare le preferenze nel database
 * se vuoi tenere traccia storica delle ricerche
 */

require_once 'config.php';

// Connessione database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die(json_encode(['error' => 'Errore connessione database']));
}

// Ricevi dati dal POST
$user_number = isset($_POST['user_number']) ? (int)$_POST['user_number'] : 1;
$genres = isset($_POST['genres']) ? $_POST['genres'] : [];
$min_year = isset($_POST['min_year']) ? (int)$_POST['min_year'] : 2000;
$max_year = isset($_POST['max_year']) ? (int)$_POST['max_year'] : 2024;
$max_duration = isset($_POST['max_duration']) ? (int)$_POST['max_duration'] : 150;
$min_rating = isset($_POST['min_rating']) ? (float)$_POST['min_rating'] : 6.0;

// Validazione
if (empty($genres) || count($genres) < 2) {
    die(json_encode(['error' => 'Seleziona almeno 2 generi']));
}

// Genera un session_id univoco se non esiste
session_start();
if (!isset($_SESSION['blend_session_id'])) {
    $_SESSION['blend_session_id'] = uniqid('blend_', true);
}
$session_id = $_SESSION['blend_session_id'];

// Prepara i generi per il database
$genres_string = implode('|', $genres);

// Verifica se esiste già una preferenza per questo utente in questa sessione
$check_sql = "SELECT preference_id FROM user_preferences 
              WHERE session_id = ? AND user_number = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param('si', $session_id, $user_number);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    // UPDATE se esiste già
    $row = $result->fetch_assoc();
    $preference_id = $row['preference_id'];
    
    $update_sql = "UPDATE user_preferences SET 
                   preferred_genres = ?,
                   min_year = ?,
                   max_year = ?,
                   max_duration = ?,
                   min_rating = ?,
                   timestamp = NOW()
                   WHERE preference_id = ?";
    
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param('siiidi', $genres_string, $min_year, $max_year, $max_duration, $min_rating, $preference_id);
} else {
    // INSERT se non esiste
    $insert_sql = "INSERT INTO user_preferences 
                   (session_id, user_number, preferred_genres, min_year, max_year, max_duration, min_rating, timestamp) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param('sisiiid', $session_id, $user_number, $genres_string, $min_year, $max_year, $max_duration, $min_rating);
}

// Esegui la query
if ($stmt->execute()) {
    // Salva le preferenze anche in sessione PHP per accesso rapido
    $_SESSION['user' . $user_number . '_prefs'] = [
        'genres' => $genres,
        'min_year' => $min_year,
        'max_year' => $max_year,
        'max_duration' => $max_duration,
        'min_rating' => $min_rating
    ];
    
    // Redirect appropriato
    if ($user_number == 1) {
        header('Location: select_preferences.php?user=2');
    } else {
        header('Location: blend_results.php');
    }
    exit;
} else {
    die(json_encode(['error' => 'Errore nel salvataggio delle preferenze']));
}

$stmt->close();
$conn->close();
?>
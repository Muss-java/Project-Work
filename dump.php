<?php
// Credenziali database Altervista
// Le trovi nel tuo pannello -> Database
$host = 'localhost';
$dbname = 'my_mouanid'; 
$username = 'mouanid';
$password = '';

$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Crea tabella
$pdo->exec("CREATE TABLE IF NOT EXISTS movies (
    id INT PRIMARY KEY,
    title VARCHAR(255),
    release_date DATE,
    overview TEXT,
    vote_average DECIMAL(3,2),
    popularity DECIMAL(10,6)
)");

// API TMDB - USA CURL (non file_get_contents)
$access_token = 'IL_TUO_TOKEN_TMDB';
$query = 'Jack Reacher';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.themoviedb.org/3/search/movie?query=' . urlencode($query));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $access_token
]);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

// Inserisci nel database
$stmt = $pdo->prepare("
    INSERT INTO movies (id, title, release_date, overview, vote_average, popularity)
    VALUES (?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE title=VALUES(title)
");

foreach ($data['results'] as $movie) {
    $stmt->execute([
        $movie['id'],
        $movie['title'],
        $movie['release_date'],
        $movie['overview'],
        $movie['vote_average'],
        $movie['popularity']
    ]);
}

echo "Film importati!";
?>
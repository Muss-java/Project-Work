<?php
// Database connection
$host = 'localhost';
$dbname = 'movie_database';
$username = 'your_username';
$password = 'your_password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Create table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS movies (
    id INT PRIMARY KEY,
    title VARCHAR(255),
    original_title VARCHAR(255),
    release_date DATE,
    overview TEXT,
    vote_average DECIMAL(3,2),
    popularity DECIMAL(10,6),
    poster_path VARCHAR(255),
    backdrop_path VARCHAR(255)
)";
$pdo->exec($sql);

// TMDB API call
$access_token = 'YOUR_ACCESS_TOKEN_HERE';
$search_query = 'Jack Reacher';
$url = 'https://api.themoviedb.org/3/search/movie?query=' . urlencode($search_query);

$options = [
    'http' => [
        'header' => "Authorization: Bearer $access_token\r\n"
    ]
];
$context = stream_context_create($options);
$response = file_get_contents($url, false, $context);
$data = json_decode($response, true);

// Insert movies into database
$stmt = $pdo->prepare("
    INSERT INTO movies 
    (id, title, original_title, release_date, overview, vote_average, popularity, poster_path, backdrop_path)
    VALUES (:id, :title, :original_title, :release_date, :overview, :vote_average, :popularity, :poster_path, :backdrop_path)
    ON DUPLICATE KEY UPDATE
    title = :title,
    vote_average = :vote_average,
    popularity = :popularity
");

foreach ($data['results'] as $movie) {
    $stmt->execute([
        ':id' => $movie['id'],
        ':title' => $movie['title'],
        ':original_title' => $movie['original_title'],
        ':release_date' => $movie['release_date'],
        ':overview' => $movie['overview'],
        ':vote_average' => $movie['vote_average'],
        ':popularity' => $movie['popularity'],
        ':poster_path' => $movie['poster_path'],
        ':backdrop_path' => $movie['backdrop_path']
    ]);
}

echo "Movies imported successfully!";
?>
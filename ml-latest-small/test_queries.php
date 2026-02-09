<?php
/**
 * Test Query per Bllendr - Esempi Algoritmo Blend
 * Progetto Maturità 5D
 */

// Connessione database
$conn = new mysqli('localhost', 'root', '', 'bllendr');

if ($conn->connect_error) {
    die("Errore connessione: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Bllendr - Test Query Database</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
        }
        .container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        h1 {
            color: #667eea;
            text-align: center;
        }
        h2 {
            color: #764ba2;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
        }
        th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px;
            text-align: left;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #e0e0e0;
        }
        tr:hover {
            background: #f5f5f5;
        }
        .query-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            border-left: 4px solid #667eea;
        }
        code {
            background: #e9ecef;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
        }
        .movie-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
            transition: transform 0.2s;
        }
        .movie-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .rating {
            color: #ffc107;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>🎬 Bllendr - Test Database & Algoritmo</h1>
    
    <!-- STATISTICHE GENERALI -->
    <h2>📊 Statistiche Database</h2>
    <div class="stats">
        <?php
        $stats_queries = [
            'Film Totali' => "SELECT COUNT(*) as c FROM movies",
            'Con Rating' => "SELECT COUNT(*) as c FROM movies WHERE avg_rating > 0",
            'Generi' => "SELECT COUNT(*) as c FROM genres",
            'Valutazioni' => "SELECT COUNT(*) as c FROM ratings"
        ];
        
        foreach ($stats_queries as $label => $query) {
            $result = $conn->query($query);
            $value = $result->fetch_assoc()['c'];
            echo "<div class='stat-card'>
                    <div class='stat-number'>" . number_format($value) . "</div>
                    <div>$label</div>
                  </div>";
        }
        ?>
    </div>

    <!-- TOP FILM -->
    <h2>⭐ Top 20 Film per Rating</h2>
    <div class="query-box">
        <code>SELECT title, year, avg_rating, rating_count FROM movies WHERE rating_count >= 50 ORDER BY avg_rating DESC LIMIT 20</code>
    </div>
    
    <table>
        <tr>
            <th>#</th>
            <th>Titolo</th>
            <th>Anno</th>
            <th>Rating</th>
            <th>Voti</th>
        </tr>
        <?php
        $result = $conn->query("SELECT title, year, avg_rating, rating_count 
                                FROM movies 
                                WHERE rating_count >= 50 
                                ORDER BY avg_rating DESC, rating_count DESC 
                                LIMIT 20");
        $pos = 1;
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>$pos</td>
                    <td><strong>" . htmlspecialchars($row['title']) . "</strong></td>
                    <td>" . ($row['year'] ?? 'N/A') . "</td>
                    <td class='rating'>" . number_format($row['avg_rating'], 2) . " ⭐</td>
                    <td>" . number_format($row['rating_count']) . "</td>
                  </tr>";
            $pos++;
        }
        ?>
    </table>

    <!-- ESEMPIO BLEND: GENERI COMUNI -->
    <h2>🎯 Esempio Algoritmo Blend - Generi Comuni</h2>
    <div class="query-box">
        <strong>Scenario:</strong> User 1 preferisce [Action, Sci-Fi] - User 2 preferisce [Drama, Sci-Fi]<br>
        <strong>Genere in comune:</strong> Sci-Fi
    </div>
    
    <?php
    // Simula preferenze
    $user1_genres = ['Action', 'Sci-Fi'];
    $user2_genres = ['Drama', 'Sci-Fi'];
    
    // Trova generi in comune
    $common_genres = array_intersect($user1_genres, $user2_genres);
    
    echo "<p><strong>Generi comuni:</strong> " . implode(', ', $common_genres) . "</p>";
    
    // Query film con generi comuni
    $genre_list = "'" . implode("','", $common_genres) . "'";
    $sql = "SELECT DISTINCT m.title, m.year, m.avg_rating, m.rating_count, GROUP_CONCAT(g.genre_name SEPARATOR ', ') as all_genres
            FROM movies m
            JOIN movie_genres mg ON m.movie_id = mg.movie_id
            JOIN genres g ON mg.genre_id = g.genre_id
            WHERE m.movie_id IN (
                SELECT mg2.movie_id 
                FROM movie_genres mg2
                JOIN genres g2 ON mg2.genre_id = g2.genre_id
                WHERE g2.genre_name IN ($genre_list)
            )
            AND m.avg_rating >= 3.5
            AND m.rating_count >= 30
            GROUP BY m.movie_id
            ORDER BY m.avg_rating DESC, m.rating_count DESC
            LIMIT 10";
    
    $result = $conn->query($sql);
    
    echo "<h3>Top 10 Film Consigliati:</h3>";
    while ($row = $result->fetch_assoc()) {
        echo "<div class='movie-card'>
                <h4>" . htmlspecialchars($row['title']) . " (" . ($row['year'] ?? 'N/A') . ")</h4>
                <p><strong>Generi:</strong> " . htmlspecialchars($row['all_genres']) . "</p>
                <p class='rating'>Rating: " . number_format($row['avg_rating'], 2) . " ⭐ (" . number_format($row['rating_count']) . " voti)</p>
              </div>";
    }
    ?>

    <!-- ESEMPIO 2: BLEND CON ANNO -->
    <h2>📅 Esempio Blend - Con Filtro Anno</h2>
    <div class="query-box">
        <strong>Scenario:</strong> User 1 preferisce film dopo il 2000 - User 2 preferisce [Comedy]<br>
        <strong>Filtro:</strong> Comedy + Anno >= 2000
    </div>
    
    <?php
    $preferred_genre = 'Comedy';
    $min_year = 2000;
    
    $sql = "SELECT DISTINCT m.title, m.year, m.avg_rating, m.rating_count, GROUP_CONCAT(g.genre_name SEPARATOR ', ') as all_genres
            FROM movies m
            JOIN movie_genres mg ON m.movie_id = mg.movie_id
            JOIN genres g ON mg.genre_id = g.genre_id
            WHERE m.movie_id IN (
                SELECT mg2.movie_id 
                FROM movie_genres mg2
                JOIN genres g2 ON mg2.genre_id = g2.genre_id
                WHERE g2.genre_name = '$preferred_genre'
            )
            AND m.year >= $min_year
            AND m.avg_rating >= 3.5
            AND m.rating_count >= 20
            GROUP BY m.movie_id
            ORDER BY m.avg_rating DESC, m.rating_count DESC
            LIMIT 10";
    
    $result = $conn->query($sql);
    
    echo "<h3>Top 10 Commedie dopo il 2000:</h3>";
    echo "<table>
            <tr>
                <th>Titolo</th>
                <th>Anno</th>
                <th>Generi</th>
                <th>Rating</th>
            </tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td><strong>" . htmlspecialchars($row['title']) . "</strong></td>
                <td>" . $row['year'] . "</td>
                <td>" . htmlspecialchars($row['all_genres']) . "</td>
                <td class='rating'>" . number_format($row['avg_rating'], 2) . " ⭐</td>
              </tr>";
    }
    echo "</table>";
    ?>

    <!-- DISTRIBUZIONE GENERI -->
    <h2>📊 Distribuzione Generi nel Database</h2>
    
    <?php
    $sql = "SELECT g.genre_name, 
                   COUNT(mg.movie_id) as film_count,
                   AVG(m.avg_rating) as avg_genre_rating,
                   SUM(m.rating_count) as total_ratings
            FROM genres g
            JOIN movie_genres mg ON g.genre_id = mg.genre_id
            JOIN movies m ON mg.movie_id = m.movie_id
            WHERE m.avg_rating > 0
            GROUP BY g.genre_id
            ORDER BY film_count DESC";
    
    $result = $conn->query($sql);
    
    echo "<table>
            <tr>
                <th>Genere</th>
                <th>Film</th>
                <th>Rating Medio</th>
                <th>Voti Totali</th>
            </tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td><strong>" . htmlspecialchars($row['genre_name']) . "</strong></td>
                <td>" . number_format($row['film_count']) . "</td>
                <td class='rating'>" . number_format($row['avg_genre_rating'], 2) . " ⭐</td>
                <td>" . number_format($row['total_ratings']) . "</td>
              </tr>";
    }
    echo "</table>";
    ?>

    <!-- QUERY AVANZATA: PUNTEGGIO BLEND -->
    <h2>🔬 Query Avanzata - Punteggio Blend Personalizzato</h2>
    <div class="query-box">
        <strong>Formula Blend Score:</strong><br>
        <code>score = (avg_rating * 0.6) + (genre_match * 0.3) + (popularity * 0.1)</code><br><br>
        <strong>Scenario:</strong> User 1 [Action, Thriller] + User 2 [Action, Drama]<br>
        Film con Action (match perfetto) = bonus più alto
    </div>
    
    <?php
    // Generi preferiti
    $user1_prefs = ['Action', 'Thriller'];
    $user2_prefs = ['Action', 'Drama'];
    $all_prefs = array_unique(array_merge($user1_prefs, $user2_prefs));
    $common = array_intersect($user1_prefs, $user2_prefs);
    
    $all_genre_list = "'" . implode("','", $all_prefs) . "'";
    $common_genre_list = "'" . implode("','", $common) . "'";
    
    // Query con scoring personalizzato
    $sql = "SELECT 
                m.title,
                m.year,
                m.avg_rating,
                m.rating_count,
                GROUP_CONCAT(DISTINCT g.genre_name SEPARATOR ', ') as all_genres,
                (
                    (m.avg_rating * 0.6) + 
                    (
                        CASE 
                            WHEN COUNT(CASE WHEN g.genre_name IN ($common_genre_list) THEN 1 END) > 0 THEN 2.0
                            WHEN COUNT(CASE WHEN g.genre_name IN ($all_genre_list) THEN 1 END) > 0 THEN 1.0
                            ELSE 0.5
                        END
                    ) +
                    (LOG10(m.rating_count + 1) * 0.1)
                ) as blend_score
            FROM movies m
            JOIN movie_genres mg ON m.movie_id = mg.movie_id
            JOIN genres g ON mg.genre_id = g.genre_id
            WHERE m.avg_rating >= 3.0
            AND m.rating_count >= 20
            GROUP BY m.movie_id
            HAVING blend_score > 0
            ORDER BY blend_score DESC
            LIMIT 15";
    
    $result = $conn->query($sql);
    
    echo "<p><strong>Generi User 1:</strong> " . implode(', ', $user1_prefs) . "</p>";
    echo "<p><strong>Generi User 2:</strong> " . implode(', ', $user2_prefs) . "</p>";
    echo "<p><strong>Generi in comune (bonus):</strong> " . implode(', ', $common) . "</p>";
    
    echo "<h3>Top 15 Film con Blend Score:</h3>";
    echo "<table>
            <tr>
                <th>Titolo</th>
                <th>Anno</th>
                <th>Generi</th>
                <th>Rating</th>
                <th>Blend Score</th>
            </tr>";
    
    while ($row = $result->fetch_assoc()) {
        $score_color = $row['blend_score'] >= 4.5 ? '#4caf50' : ($row['blend_score'] >= 4.0 ? '#ff9800' : '#757575');
        echo "<tr>
                <td><strong>" . htmlspecialchars($row['title']) . "</strong></td>
                <td>" . ($row['year'] ?? 'N/A') . "</td>
                <td><small>" . htmlspecialchars($row['all_genres']) . "</small></td>
                <td class='rating'>" . number_format($row['avg_rating'], 2) . " ⭐</td>
                <td style='color: $score_color; font-weight: bold;'>" . number_format($row['blend_score'], 2) . "</td>
              </tr>";
    }
    echo "</table>";
    ?>

    <!-- FOOTER -->
    <div style="text-align: center; margin-top: 40px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
        <h3>✅ Database Pronto per Bllendr!</h3>
        <p>Puoi usare queste query come base per l'algoritmo di blend dell'app.</p>
        <p><strong>Prossimo Step:</strong> Implementare l'interfaccia per raccogliere le preferenze degli utenti</p>
    </div>

</div>
</body>
</html>

<?php
$conn->close();
?>

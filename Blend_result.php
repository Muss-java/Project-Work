<?php
// --- BACKEND API (Gestisce la richiesta AJAX) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    header('Content-Type: application/json');
    require_once 'config.php';
    
    // Ricevi i dati JSON da JavaScript
    $data = json_decode(file_get_contents('php://input'), true);
    $u1 = $data['user1'];
    $u2 = $data['user2'];

    // 1. Incrociamo i parametri rigidi (Filtri)
    $min_year = max($u1['min_year'], $u2['min_year']);
    $max_year = min($u1['max_year'], $u2['max_year']);
    $max_duration = min($u1['max_duration'], $u2['max_duration']);
    $min_rating = max($u1['min_rating'], $u2['min_rating']);

    // 2. Uniamo i generi e troviamo le preferenze comuni
    $u1_genres = $u1['genres'];
    $u2_genres = $u2['genres'];
    $common_genres = array_intersect($u1_genres, $u2_genres);
    $all_selected_genres = array_unique(array_merge($u1_genres, $u2_genres));

    // Connessione al DB
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die(json_encode(['error' => 'Errore connessione database: ' . $conn->connect_error]));
    }

    // Costruiamo la query (Assumendo una tabella 'movies')
    // NOTA: Adatta i nomi delle colonne al tuo database effettivo se sono diversi!
    $sql = "SELECT id, title, release_date, runtime, vote_average, poster_path, overview, genres 
            FROM movies 
            WHERE YEAR(release_date) BETWEEN ? AND ? 
            AND runtime <= ? 
            AND vote_average >= ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiid", $min_year, $max_year, $max_duration, $min_rating);
    $stmt->execute();
    $result = $stmt->get_result();

    $movies = [];
    while ($row = $result->fetch_assoc()) {
        $movie_genres = explode(',', $row['genres']); // Assumendo generi separati da virgola
        
        // Calcolo del Match Score
        $score = 0;
        $match_points = 0;
        $total_possible_points = count($common_genres) * 2 + (count($all_selected_genres) - count($common_genres));
        
        if ($total_possible_points == 0) $total_possible_points = 1; // Evita divisione per zero

        foreach ($movie_genres as $mg) {
            $mg = trim($mg);
            if (in_array($mg, $common_genres)) {
                $match_points += 2; // Genere in comune vale doppio
            } elseif (in_array($mg, $all_selected_genres)) {
                $match_points += 1; // Genere scelto da uno solo vale 1
            }
        }

        // Se il film ha almeno un genere scelto, lo calcoliamo
        if ($match_points > 0) {
            $score_percentage = min(100, round(($match_points / $total_possible_points) * 100));
            // Aggiustiamo il punteggio in base al rating del film
            $final_score = min(100, $score_percentage + ($row['vote_average'] * 2)); 

            $row['match_score'] = round($final_score);
            $movies[] = $row;
        }
    }

    // Ordina per Match Score decrescente
    usort($movies, function($a, $b) {
        return $b['match_score'] <=> $a['match_score'];
    });

    // Restituisci i top 10 risultati
    echo json_encode(array_slice($movies, 0, 10));
    $stmt->close();
    $conn->close();
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bllendr - I Vostri Match</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
            color: #333;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            color: white;
            margin-bottom: 40px;
            animation: fadeIn 1s ease-out;
        }
        .header h1 { font-size: 3em; margin-bottom: 10px; text-shadow: 2px 2px 10px rgba(0,0,0,0.2); }
        
        .loading { text-align: center; color: white; font-size: 1.5em; margin-top: 50px; }
        .spinner {
            display: inline-block; width: 50px; height: 50px;
            border: 5px solid rgba(255,255,255,0.3); border-radius: 50%;
            border-top-color: white; animation: spin 1s ease-in-out infinite;
            margin-bottom: 20px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        .movies-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px;
        }
        .movie-card {
            background: white; border-radius: 20px; overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            transition: transform 0.3s; position: relative;
            display: flex; flex-direction: column;
        }
        .movie-card:hover { transform: translateY(-10px); }
        
        .movie-poster {
            width: 100%; height: 400px; object-fit: cover;
            background-color: #ddd;
        }
        .match-badge {
            position: absolute; top: 15px; right: 15px;
            background: linear-gradient(135deg, #4CAF50 0%, #45a247 100%);
            color: white; font-weight: bold; padding: 10px 15px;
            border-radius: 30px; font-size: 1.2em;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        .movie-info { padding: 25px; flex-grow: 1; display: flex; flex-direction: column;}
        .movie-title { font-size: 1.4em; font-weight: bold; margin-bottom: 10px; color: #2c3e50; }
        .movie-meta { display: flex; gap: 15px; font-size: 0.9em; color: #7f8c8d; margin-bottom: 15px; }
        .movie-overview { font-size: 0.95em; color: #555; line-height: 1.5; margin-bottom: 20px; flex-grow: 1;}
        
        .btn-restart {
            display: block; width: 200px; margin: 40px auto 0;
            padding: 15px 30px; background: white; color: #764ba2;
            text-align: center; text-decoration: none; border-radius: 30px;
            font-weight: bold; font-size: 1.1em; transition: all 0.3s;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .btn-restart:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(0,0,0,0.2); }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🍿 I Vostri Match Perfetti</h1>
            <p>Abbiamo unito i vostri gusti, ecco cosa vi consigliamo di guardare stasera!</p>
        </div>

        <div id="loading" class="loading">
            <div class="spinner"></div>
            <p>L'algoritmo sta calcolando l'affinità di coppia...</p>
        </div>

        <div id="results" class="movies-grid" style="display: none;">
            </div>

        <a href="index.php" class="btn-restart" style="display: none;" id="restartBtn">🔄 Fai un nuovo Blend</a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            // 1. Recupera i dati da sessionStorage
            const u1_data = sessionStorage.getItem('user1_prefs');
            const u2_data = sessionStorage.getItem('user2_prefs');

            if (!u1_data || !u2_data) {
                alert("Mancano le preferenze! Torna alla home.");
                window.location.href = 'index.php';
                return;
            }

            const payload = {
                user1: JSON.parse(u1_data),
                user2: JSON.parse(u2_data)
            };

            // 2. Invia i dati al backend PHP (nello stesso file)
            try {
                const response = await fetch('Blend_results.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(payload)
                });

                const movies = await response.json();
                
                // 3. Nascondi caricamento e mostra risultati
                document.getElementById('loading').style.display = 'none';
                const resultsContainer = document.getElementById('results');
                resultsContainer.style.display = 'grid';
                document.getElementById('restartBtn').style.display = 'block';

                if (movies.error) {
                    resultsContainer.innerHTML = `<div style="color: white; grid-column: 1/-1; text-align: center;">Errore server: ${movies.error}</div>`;
                    return;
                }

                if (movies.length === 0) {
                    resultsContainer.innerHTML = `
                        <div style="color: white; grid-column: 1/-1; text-align: center; font-size: 1.2em;">
                            😅 Oh no! Siete troppo diversi! Non abbiamo trovato film che rispettino tutte le vostre regole.<br>
                            Provate ad allargare i filtri (anno, durata).
                        </div>`;
                    return;
                }

                // 4. Renderizza le card dei film
                movies.forEach(movie => {
                    // Costruisci URL poster (usa TMDb standard, cambialo se hai percorsi locali)
                    const posterUrl = movie.poster_path 
                        ? (movie.poster_path.startsWith('http') ? movie.poster_path : `https://image.tmdb.org/t/p/w500${movie.poster_path}`) 
                        : 'https://via.placeholder.com/500x750?text=No+Poster';
                    
                    const year = new Date(movie.release_date).getFullYear();

                    resultsContainer.innerHTML += `
                        <div class="movie-card">
                            <div class="match-badge">${movie.match_score}% Match</div>
                            <img src="${posterUrl}" alt="Poster ${movie.title}" class="movie-poster">
                            <div class="movie-info">
                                <h3 class="movie-title">${movie.title}</h3>
                                <div class="movie-meta">
                                    <span>📅 ${year}</span>
                                    <span>⏱️ ${movie.runtime} min</span>
                                    <span>⭐ ${movie.vote_average}/10</span>
                                </div>
                                <p class="movie-overview">${movie.overview ? movie.overview.substring(0, 150) + '...' : 'Trama non disponibile.'}</p>
                            </div>
                        </div>
                    `;
                });

            } catch (error) {
                console.error('Errore:', error);
                document.getElementById('loading').innerHTML = '<p>Oops! Qualcosa è andato storto nel calcolo.</p>';
            }
        });
    </script>
</body>
</html>
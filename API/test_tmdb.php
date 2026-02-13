<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test TMDb API - Bllendr</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 2.5em;
        }
        
        h2 {
            color: #667eea;
            margin: 30px 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        
        .search-box {
            margin: 20px 0;
            display: flex;
            gap: 10px;
        }
        
        input[type="text"] {
            flex: 1;
            padding: 12px 20px;
            border: 2px solid #ddd;
            border-radius: 25px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input[type="text"]:focus {
            outline: none;
            border-color: #667eea;
        }
        
        button {
            padding: 12px 30px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        button:hover {
            background: #764ba2;
        }
        
        .movie-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .movie-card {
            background: #f8f9fa;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }
        
        .movie-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.2);
        }
        
        .movie-poster {
            width: 100%;
            height: 300px;
            object-fit: cover;
            background: linear-gradient(45deg, #667eea, #764ba2);
        }
        
        .movie-info {
            padding: 15px;
        }
        
        .movie-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
            font-size: 14px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .movie-rating {
            color: #667eea;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .movie-year {
            color: #888;
            font-size: 12px;
            margin-top: 5px;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
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
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 0.9em;
            opacity: 0.9;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 1000;
            overflow-y: auto;
        }
        
        .modal-content {
            max-width: 900px;
            margin: 50px auto;
            background: white;
            border-radius: 15px;
            overflow: hidden;
        }
        
        .modal-header {
            position: relative;
            height: 400px;
            background-size: cover;
            background-position: center;
        }
        
        .modal-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
        }
        
        .modal-title {
            position: absolute;
            bottom: 20px;
            left: 20px;
            right: 20px;
            color: white;
            font-size: 2em;
            z-index: 1;
        }
        
        .close-modal {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 30px;
            color: white;
            cursor: pointer;
            z-index: 2;
            width: 40px;
            height: 40px;
            background: rgba(0,0,0,0.5);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-body {
            padding: 30px;
        }
        
        .overview {
            color: #555;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .info-row {
            display: flex;
            gap: 20px;
            margin: 10px 0;
        }
        
        .info-label {
            font-weight: bold;
            color: #667eea;
            min-width: 100px;
        }
        
        .trailer-container {
            margin-top: 20px;
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
        }
        
        .trailer-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎬 Test TMDb API - Bllendr</h1>
        <p style="color: #666; margin-bottom: 30px;">Esplora i film dal database arricchito con TMDb</p>
        
        <?php
        require_once 'config.php';
        require_once 'TMDbAPI.php';
        
        // Connessione database
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die("Errore connessione: " . $conn->connect_error);
        }
        
        // Inizializza API
        $tmdb = new TMDbAPI(TMDB_API_KEY);
        
        // Statistiche database
        $stats = [
            'total' => 0,
            'synced' => 0,
            'with_poster' => 0,
            'with_trailer' => 0
        ];
        
        $stats['total'] = $conn->query("SELECT COUNT(*) as c FROM movies")->fetch_assoc()['c'];
        $stats['synced'] = $conn->query("SELECT COUNT(*) as c FROM movies WHERE tmdb_id IS NOT NULL")->fetch_assoc()['c'];
        $stats['with_poster'] = $conn->query("SELECT COUNT(*) as c FROM movies WHERE poster_path IS NOT NULL AND poster_path != ''")->fetch_assoc()['c'];
        $stats['with_trailer'] = $conn->query("SELECT COUNT(*) as c FROM movies WHERE trailer_key IS NOT NULL AND trailer_key != ''")->fetch_assoc()['c'];
        ?>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['total']); ?></div>
                <div class="stat-label">Film Totali</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['synced']); ?></div>
                <div class="stat-label">Sincronizzati TMDb</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['with_poster']); ?></div>
                <div class="stat-label">Con Poster</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['with_trailer']); ?></div>
                <div class="stat-label">Con Trailer</div>
            </div>
        </div>
        
        <h2>🔍 Cerca Film</h2>
        <form method="GET" class="search-box">
            <input type="text" name="search" placeholder="Cerca un film..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
            <button type="submit">Cerca</button>
        </form>
        
        <?php
        // Query per i film
        $search = $_GET['search'] ?? '';
        $where = '';
        
        if (!empty($search)) {
            $search_safe = $conn->real_escape_string($search);
            $where = "WHERE title LIKE '%$search_safe%'";
        }
        
        $sql = "SELECT movie_id, title, year, genres, tmdb_id, overview, poster_path, 
                       backdrop_path, runtime, vote_average, release_date, trailer_key
                FROM movies 
                $where
                AND tmdb_id IS NOT NULL 
                ORDER BY vote_average DESC, title ASC 
                LIMIT 24";
        
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            echo '<h2>🎥 Film Trovati (' . $result->num_rows . ')</h2>';
            echo '<div class="movie-grid">';
            
            while ($movie = $result->fetch_assoc()) {
                $poster_url = $movie['poster_path'] 
                    ? $tmdb->getPosterUrl($movie['poster_path'], 'w342')
                    : 'https://via.placeholder.com/342x513/667eea/ffffff?text=No+Poster';
                
                $rating = $movie['vote_average'] ? number_format($movie['vote_average'], 1) : 'N/A';
                $year = $movie['year'] ?? ($movie['release_date'] ? date('Y', strtotime($movie['release_date'])) : 'N/A');
                
                echo '<div class="movie-card" onclick="showModal(' . $movie['movie_id'] . ')">';
                echo '<img src="' . $poster_url . '" alt="' . htmlspecialchars($movie['title']) . '" class="movie-poster">';
                echo '<div class="movie-info">';
                echo '<div class="movie-title">' . htmlspecialchars($movie['title']) . '</div>';
                echo '<div class="movie-rating">⭐ ' . $rating . '</div>';
                echo '<div class="movie-year">' . $year . '</div>';
                echo '</div>';
                echo '</div>';
            }
            
            echo '</div>';
        } else {
            echo '<p style="text-align: center; color: #666; margin: 40px 0;">Nessun film trovato. Prova a sincronizzare il database con <a href="sync_tmdb.php">sync_tmdb.php</a></p>';
        }
        
        $conn->close();
        ?>
    </div>
    
    <!-- Modal per i dettagli del film -->
    <div id="movieModal" class="modal" onclick="closeModal(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <div class="modal-header" id="modalHeader">
                <span class="close-modal" onclick="closeModal()">&times;</span>
                <h2 class="modal-title" id="modalTitle"></h2>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Contenuto caricato dinamicamente -->
            </div>
        </div>
    </div>
    
    <script>
        function showModal(movieId) {
            const modal = document.getElementById('movieModal');
            const modalHeader = document.getElementById('modalHeader');
            const modalTitle = document.getElementById('modalTitle');
            const modalBody = document.getElementById('modalBody');
            
            // Mostra modal con loading
            modal.style.display = 'block';
            modalBody.innerHTML = '<p style="text-align: center; padding: 40px;">Caricamento...</p>';
            
            // Carica dati del film via AJAX
            fetch('get_movie_details.php?id=' + movieId)
                .then(response => response.json())
                .then(data => {
                    // Imposta sfondo
                    if (data.backdrop_path) {
                        modalHeader.style.backgroundImage = `url(${data.backdrop_url})`;
                    }
                    
                    // Imposta titolo
                    modalTitle.textContent = data.title;
                    
                    // Crea contenuto
                    let html = '';
                    
                    if (data.overview) {
                        html += `<div class="overview">${data.overview}</div>`;
                    }
                    
                    html += '<div class="info-row"><span class="info-label">Anno:</span><span>' + (data.year || 'N/A') + '</span></div>';
                    html += '<div class="info-row"><span class="info-label">Durata:</span><span>' + (data.runtime ? data.runtime + ' min' : 'N/A') + '</span></div>';
                    html += '<div class="info-row"><span class="info-label">Valutazione:</span><span>⭐ ' + (data.vote_average || 'N/A') + '/10</span></div>';
                    html += '<div class="info-row"><span class="info-label">Generi:</span><span>' + (data.genres || 'N/A') + '</span></div>';
                    
                    if (data.trailer_key) {
                        html += '<h3 style="color: #667eea; margin-top: 30px;">🎬 Trailer</h3>';
                        html += '<div class="trailer-container">';
                        html += '<iframe src="https://www.youtube.com/embed/' + data.trailer_key + '" allowfullscreen></iframe>';
                        html += '</div>';
                    }
                    
                    modalBody.innerHTML = html;
                })
                .catch(error => {
                    modalBody.innerHTML = '<p style="color: red; text-align: center;">Errore nel caricamento dei dati</p>';
                });
        }
        
        function closeModal(event) {
            if (!event || event.target.id === 'movieModal') {
                document.getElementById('movieModal').style.display = 'none';
            }
        }
        
        // Chiudi modal con ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>

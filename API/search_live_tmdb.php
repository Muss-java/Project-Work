<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ricerca Film TMDb - Bllendr</title>
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
        }
        
        .search-box {
            margin: 30px 0;
            display: flex;
            gap: 10px;
        }
        
        input[type="text"] {
            flex: 1;
            padding: 15px 20px;
            border: 2px solid #ddd;
            border-radius: 25px;
            font-size: 16px;
        }
        
        input[type="text"]:focus {
            outline: none;
            border-color: #667eea;
        }
        
        button {
            padding: 15px 40px;
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
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .movie-card {
            background: #f8f9fa;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            cursor: pointer;
        }
        
        .movie-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.2);
        }
        
        .movie-poster {
            width: 100%;
            height: 270px;
            object-fit: cover;
            background: linear-gradient(45deg, #667eea, #764ba2);
        }
        
        .movie-info {
            padding: 12px;
        }
        
        .movie-title {
            font-weight: bold;
            color: #333;
            font-size: 13px;
            margin-bottom: 5px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .movie-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
        }
        
        .movie-rating {
            color: #667eea;
            font-weight: bold;
        }
        
        .movie-year {
            color: #888;
        }
        
        .loading {
            text-align: center;
            padding: 40px;
            color: #667eea;
            font-size: 18px;
        }
        
        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .no-results h2 {
            color: #667eea;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎬 Ricerca Film su TMDb</h1>
        <p style="color: #666;">Cerca film in tempo reale dal database di The Movie Database</p>
        
        <form class="search-box" onsubmit="searchMovies(event)">
            <input type="text" id="searchInput" placeholder="Cerca un film (es: Inception, Avengers, Toy Story...)" required>
            <button type="submit">🔍 Cerca</button>
        </form>
        
        <div id="results"></div>
    </div>
    
    <script>
        async function searchMovies(event) {
            event.preventDefault();
            
            const query = document.getElementById('searchInput').value;
            const resultsDiv = document.getElementById('results');
            
            // Mostra loading
            resultsDiv.innerHTML = '<div class="loading">🎬 Ricerca in corso...</div>';
            
            try {
                const response = await fetch('search_tmdb.php?q=' + encodeURIComponent(query));
                const data = await response.json();
                
                if (data.error) {
                    resultsDiv.innerHTML = '<div class="no-results"><h2>❌ Errore</h2><p>' + data.error + '</p></div>';
                    return;
                }
                
                if (!data.results || data.results.length === 0) {
                    resultsDiv.innerHTML = '<div class="no-results"><h2>🔍 Nessun risultato</h2><p>Prova con un altro titolo</p></div>';
                    return;
                }
                
                // Mostra risultati
                let html = '<h2 style="color: #667eea; margin: 20px 0;">Trovati ' + data.results.length + ' film</h2>';
                html += '<div class="movie-grid">';
                
                data.results.forEach(movie => {
                    const posterUrl = movie.poster_path 
                        ? 'https://image.tmdb.org/t/p/w342' + movie.poster_path
                        : 'https://via.placeholder.com/342x513/667eea/ffffff?text=No+Poster';
                    
                    const year = movie.release_date ? new Date(movie.release_date).getFullYear() : 'N/A';
                    const rating = movie.vote_average ? movie.vote_average.toFixed(1) : 'N/A';
                    
                    html += `
                        <div class="movie-card" onclick="showDetails(${movie.id})">
                            <img src="${posterUrl}" alt="${movie.title}" class="movie-poster">
                            <div class="movie-info">
                                <div class="movie-title">${movie.title}</div>
                                <div class="movie-meta">
                                    <span class="movie-rating">⭐ ${rating}</span>
                                    <span class="movie-year">${year}</span>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                html += '</div>';
                resultsDiv.innerHTML = html;
                
            } catch (error) {
                resultsDiv.innerHTML = '<div class="no-results"><h2>❌ Errore</h2><p>Errore nella ricerca. Riprova.</p></div>';
            }
        }
        
        function showDetails(tmdbId) {
            // In futuro qui puoi mostrare un modal con i dettagli completi
            alert('TMDb ID: ' + tmdbId + '\nIn futuro qui vedrai i dettagli completi del film!');
            
            // Oppure puoi reindirizzare a una pagina dettagli
            // window.location.href = 'movie_details.php?tmdb_id=' + tmdbId;
        }
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bllendr - Seleziona le tue Preferenze</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: slideIn 0.5s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo h1 {
            font-size: 3em;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
        }
        
        .user-indicator {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 25px;
            border-radius: 30px;
            text-align: center;
            margin-bottom: 30px;
            font-size: 1.2em;
            font-weight: bold;
        }
        
        .form-group {
            margin-bottom: 30px;
        }
        
        .form-group label {
            display: block;
            font-size: 1.1em;
            font-weight: bold;
            color: #333;
            margin-bottom: 12px;
        }
        
        .form-group label .required {
            color: #e74c3c;
        }
        
        .description {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 15px;
        }
        
        /* Stile per i generi */
        .genres-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 12px;
        }
        
        .genre-option {
            position: relative;
        }
        
        .genre-option input[type="checkbox"] {
            display: none;
        }
        
        .genre-label {
            display: block;
            padding: 15px 10px;
            background: #f5f5f5;
            border: 2px solid #ddd;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.95em;
        }
        
        .genre-option input[type="checkbox"]:checked + .genre-label {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: #667eea;
            transform: scale(1.05);
        }
        
        .genre-label:hover {
            border-color: #667eea;
            transform: translateY(-2px);
        }
        
        /* Stile per gli slider */
        .slider-container {
            margin-top: 15px;
        }
        
        .slider-value {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 1.1em;
            color: #667eea;
            font-weight: bold;
        }
        
        .range-slider {
            width: 100%;
            height: 8px;
            border-radius: 5px;
            background: #ddd;
            outline: none;
            -webkit-appearance: none;
        }
        
        .range-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        
        .range-slider::-moz-range-thumb {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            cursor: pointer;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        
        /* Range doppio per anni */
        .dual-range {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .range-labels {
            display: flex;
            justify-content: space-between;
            color: #888;
            font-size: 0.85em;
            margin-top: 5px;
        }
        
        /* Select personalizzato */
        select {
            width: 100%;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 10px;
            font-size: 1em;
            background: white;
            cursor: pointer;
            transition: border-color 0.3s;
        }
        
        select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        /* Bottone submit */
        .btn-submit {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 30px;
            font-size: 1.2em;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            margin-top: 20px;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }
        
        .btn-submit:active {
            transform: translateY(0);
        }
        
        /* Progress bar */
        .progress-bar {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .progress-step {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #999;
            position: relative;
        }
        
        .progress-step.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .progress-step.completed {
            background: #4CAF50;
            color: white;
        }
        
        .progress-step::after {
            content: '';
            position: absolute;
            width: 50px;
            height: 3px;
            background: #e0e0e0;
            right: -50px;
            top: 50%;
            transform: translateY(-50%);
        }
        
        .progress-step:last-child::after {
            display: none;
        }
        
        .progress-step.completed::after {
            background: #4CAF50;
        }
        
        /* Error message */
        .error-message {
            background: #ffebee;
            border-left: 4px solid #e74c3c;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            color: #c62828;
            display: none;
        }
        
        .error-message.show {
            display: block;
            animation: shake 0.5s;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        
        /* Responsive */
        @media (max-width: 600px) {
            .container {
                padding: 25px;
            }
            
            .logo h1 {
                font-size: 2em;
            }
            
            .genres-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Logo -->
        <div class="logo">
            <h1>🎬 Bllendr</h1>
            <p style="color: #666;">Trova il film perfetto per due</p>
        </div>
        
        <!-- Progress Bar -->
        <div class="progress-bar">
            <div class="progress-step" id="step1">1</div>
            <div class="progress-step" id="step2">2</div>
            <div class="progress-step" id="step3">🎯</div>
        </div>
        
        <!-- User Indicator -->
        <div class="user-indicator" id="userIndicator">
             Utente 1 - Seleziona le tue preferenze
        </div>
        
        <!-- Error Message -->
        <div class="error-message" id="errorMessage"></div>
        
        <!-- Form -->
        <form id="preferencesForm" method="POST" action="process_preferences.php">
            <input type="hidden" name="user_number" id="userNumber" value="1">
            
            <!-- Generi Preferiti -->
            <div class="form-group">
                <label>
                     Generi Preferiti <span class="required">*</span>
                </label>
                <div class="description">Seleziona almeno 2 generi che ti piacciono</div>
                <div class="genres-grid" id="genresGrid">
                    <!-- Generi popolari -->
                </div>
            </div>
            
            <!-- Anno Minimo -->
            <div class="form-group">
                <label> Periodo Film</label>
                <div class="description">Da quale anno preferisci i film?</div>
                <div class="slider-container">
                    <div class="slider-value">
                        <span>Anno Minimo:</span>
                        <span id="minYearValue">2000</span>
                    </div>
                    <input type="range" name="min_year" id="minYear" class="range-slider" 
                           min="1970" max="2024" value="2000" step="1">
                    <div class="range-labels">
                        <span>1970</span>
                        <span>2024</span>
                    </div>
                </div>
            </div>
            
            <!-- Anno Massimo -->
            <div class="form-group">
                <div class="slider-container">
                    <div class="slider-value">
                        <span>Anno Massimo:</span>
                        <span id="maxYearValue">2024</span>
                    </div>
                    <input type="range" name="max_year" id="maxYear" class="range-slider" 
                           min="1970" max="2024" value="2024" step="1">
                    <div class="range-labels">
                        <span>1970</span>
                        <span>2024</span>
                    </div>
                </div>
            </div>
            
            <!-- Durata Massima -->
            <div class="form-group">
                <label>⏱️ Durata Massima Film</label>
                <div class="description">Quanto può durare al massimo il film?</div>
                <div class="slider-container">
                    <div class="slider-value">
                        <span>Massimo:</span>
                        <span id="maxDurationValue">150 minuti</span>
                    </div>
                    <input type="range" name="max_duration" id="maxDuration" class="range-slider" 
                           min="60" max="240" value="150" step="10">
                    <div class="range-labels">
                        <span>60 min</span>
                        <span>240 min (4h)</span>
                    </div>
                </div>
            </div>
            
            <!-- Valutazione Minima -->
            <div class="form-group">
                <label>⭐ Valutazione Minima</label>
                <div class="description">Punteggio minimo su TMDb (0-10)</div>
                <div class="slider-container">
                    <div class="slider-value">
                        <span>Minimo:</span>
                        <span id="minRatingValue">6.0 / 10</span>
                    </div>
                    <input type="range" name="min_rating" id="minRating" class="range-slider" 
                           min="0" max="10" value="6.0" step="0.5">
                    <div class="range-labels">
                        <span>0.0</span>
                        <span>10.0</span>
                    </div>
                </div>
            </div>
            
            <!-- Submit Button -->
            <button type="submit" class="btn-submit" id="submitBtn">
                Continua ➔
            </button>
        </form>
    </div>
    
    <script>
        // Lista generi comuni (compatibili con MovieLens)
        const genres = [
            'Action', 'Adventure', 'Animation', 'Children', 'Comedy', 
            'Crime', 'Documentary', 'Drama', 'Fantasy', 'Film-Noir',
            'Horror', 'Musical', 'Mystery', 'Romance', 'Sci-Fi', 
            'Thriller', 'War', 'Western'
        ];
        
        // Emoji per ogni genere
        const genreEmojis = {
            'Action': '💥',
            'Adventure': '🗺️',
            'Animation': '🎨',
            'Children': '👶',
            'Comedy': '😂',
            'Crime': '🔫',
            'Documentary': '📹',
            'Drama': '🎭',
            'Fantasy': '🧙',
            'Film-Noir': '🕵️',
            'Horror': '👻',
            'Musical': '🎵',
            'Mystery': '🔍',
            'Romance': '💕',
            'Sci-Fi': '🚀',
            'Thriller': '😱',
            'War': '⚔️',
            'Western': '🤠'
        };
        
        // Popola i generi
        const genresGrid = document.getElementById('genresGrid');
        genres.forEach(genre => {
            const emoji = genreEmojis[genre] || '🎬';
            genresGrid.innerHTML += `
                <div class="genre-option">
                    <input type="checkbox" name="genres[]" value="${genre}" id="genre_${genre}">
                    <label for="genre_${genre}" class="genre-label">
                        ${emoji} ${genre}
                    </label>
                </div>
            `;
        });
        
        // Update slider values in real-time
        const minYear = document.getElementById('minYear');
        const maxYear = document.getElementById('maxYear');
        const maxDuration = document.getElementById('maxDuration');
        const minRating = document.getElementById('minRating');
        
        minYear.addEventListener('input', function() {
            document.getElementById('minYearValue').textContent = this.value;
            // Assicura che min year <= max year
            if (parseInt(this.value) > parseInt(maxYear.value)) {
                maxYear.value = this.value;
                document.getElementById('maxYearValue').textContent = this.value;
            }
        });
        
        maxYear.addEventListener('input', function() {
            document.getElementById('maxYearValue').textContent = this.value;
            // Assicura che max year >= min year
            if (parseInt(this.value) < parseInt(minYear.value)) {
                minYear.value = this.value;
                document.getElementById('minYearValue').textContent = this.value;
            }
        });
        
        maxDuration.addEventListener('input', function() {
            document.getElementById('maxDurationValue').textContent = this.value + ' minuti';
        });
        
        minRating.addEventListener('input', function() {
            document.getElementById('minRatingValue').textContent = this.value + ' / 10';
        });
        
        // Gestione form submit con validazione
        const form = document.getElementById('preferencesForm');
        const errorMessage = document.getElementById('errorMessage');
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validazione: almeno 2 generi selezionati
            const selectedGenres = document.querySelectorAll('input[name="genres[]"]:checked');
            
            if (selectedGenres.length < 2) {
                errorMessage.textContent = '⚠️ Seleziona almeno 2 generi!';
                errorMessage.classList.add('show');
                setTimeout(() => errorMessage.classList.remove('show'), 3000);
                return;
            }
            
            // Salva in sessionStorage e procedi
            const formData = new FormData(form);
            const userNumber = document.getElementById('userNumber').value;
            
            // Crea oggetto con tutte le preferenze
            const preferences = {
                genres: Array.from(selectedGenres).map(cb => cb.value),
                min_year: minYear.value,
                max_year: maxYear.value,
                max_duration: maxDuration.value,
                min_rating: minRating.value
            };
            
            // Salva in sessionStorage
            sessionStorage.setItem('user' + userNumber + '_prefs', JSON.stringify(preferences));
            
            // Se è l'utente 1, ricarica la pagina per l'utente 2
            if (userNumber === '1') {
                // Marca step 1 come completato
                document.getElementById('step1').classList.add('completed');
                
                // Passa all'utente 2
                window.location.href = 'select_preferences.php?user=2';
            } else {
                // Se è l'utente 2, vai ai risultati
                window.location.href = 'blend_results.php';
            }
        });
        
        // Controlla se siamo all'utente 2
        const urlParams = new URLSearchParams(window.location.search);
        const currentUser = urlParams.get('user') || '1';
        
        if (currentUser === '2') {
            document.getElementById('userNumber').value = '2';
            document.getElementById('userIndicator').innerHTML = ' Utente 2 - Seleziona le tue preferenze';
            document.getElementById('submitBtn').innerHTML = 'Trova i Film! 🎬';
            document.getElementById('step1').classList.add('completed');
            document.getElementById('step2').classList.add('active');
        } else {
            document.getElementById('step1').classList.add('active');
        }
    </script>
</body>
</html>
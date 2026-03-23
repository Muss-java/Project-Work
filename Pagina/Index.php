<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bllendr - Trova il Film Perfetto per Due</title>
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
            overflow-x: hidden;
        }
        
        .hero {
            text-align: center;
            color: white;
            padding: 40px 20px;
            max-width: 800px;
            animation: fadeIn 1s ease-out;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logo {
            font-size: 5em;
            margin-bottom: 20px;
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }
        
        h1 {
            font-size: 4em;
            margin-bottom: 20px;
            text-shadow: 2px 2px 10px rgba(0,0,0,0.2);
        }
        
        .tagline {
            font-size: 1.5em;
            margin-bottom: 40px;
            opacity: 0.95;
        }
        
        .description {
            font-size: 1.1em;
            line-height: 1.8;
            margin-bottom: 50px;
            opacity: 0.9;
        }
        
        .cta-button {
            display: inline-block;
            padding: 20px 60px;
            background: white;
            color: #667eea;
            font-size: 1.3em;
            font-weight: bold;
            border: none;
            border-radius: 50px;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            }
            50% {
                box-shadow: 0 15px 40px rgba(0,0,0,0.3);
            }
        }
        
        .cta-button:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
        }
        
        .cta-button:active {
            transform: translateY(-2px);
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            margin: 60px 0;
            animation: slideUp 1s ease-out 0.3s both;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .feature {
            background: rgba(255,255,255,0.1);
            padding: 30px 20px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            transition: transform 0.3s;
        }
        
        .feature:hover {
            transform: translateY(-5px);
            background: rgba(255,255,255,0.15);
        }
        
        .feature-icon {
            font-size: 3em;
            margin-bottom: 15px;
        }
        
        .feature-title {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .feature-description {
            font-size: 0.9em;
            opacity: 0.9;
        }
        
        .stats {
            display: flex;
            justify-content: center;
            gap: 50px;
            margin-top: 60px;
            animation: slideUp 1s ease-out 0.6s both;
        }
        
        .stat {
            text-align: center;
        }
        
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 0.9em;
            opacity: 0.9;
        }
        
        .demo-link {
            margin-top: 30px;
            font-size: 0.9em;
        }
        
        .demo-link a {
            color: white;
            text-decoration: underline;
            opacity: 0.8;
            transition: opacity 0.3s;
        }
        
        .demo-link a:hover {
            opacity: 1;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            h1 {
                font-size: 2.5em;
            }
            
            .tagline {
                font-size: 1.2em;
            }
            
            .logo {
                font-size: 3em;
            }
            
            .cta-button {
                padding: 15px 40px;
                font-size: 1.1em;
            }
            
            .stats {
                flex-direction: column;
                gap: 30px;
            }
        }
        
        /* Background animation */
        .background-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }
        
        .shape {
            position: absolute;
            opacity: 0.1;
            animation: float 20s infinite;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            50% {
                transform: translateY(-50px) rotate(180deg);
            }
        }
        
        .shape:nth-child(1) {
            top: 10%;
            left: 10%;
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            animation-delay: 0s;
        }
        
        .shape:nth-child(2) {
            top: 60%;
            right: 15%;
            width: 100px;
            height: 100px;
            background: white;
            border-radius: 20px;
            animation-delay: 3s;
        }
        
        .shape:nth-child(3) {
            bottom: 20%;
            left: 20%;
            width: 60px;
            height: 60px;
            background: white;
            clip-path: polygon(50% 0%, 0% 100%, 100% 100%);
            animation-delay: 6s;
        }
    </style>
</head>
<body>
    <!-- Background shapes -->
    <div class="background-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    
    <div class="hero">
        <div class="logo">🎬</div>
        <h1>Bllendr</h1>
        <p class="tagline">Trova il film perfetto per due</p>
        
        <p class="description">
            Non litigate più su quale film guardare! Bllendr combina le vostre preferenze
            e vi suggerisce i film perfetti che piaceranno a entrambi.
        </p>
        
        <a href="select_preferences.php" class="cta-button">
            🚀 Inizia il Blend
        </a>
        
        <!-- Features -->
        <div class="features">
            <div class="feature">
                <div class="feature-icon">🎯</div>
                <div class="feature-title">Algoritmo Smart</div>
                <div class="feature-description">
                    Analizziamo le preferenze di entrambi e troviamo il match perfetto
                </div>
            </div>
            
            <div class="feature">
                <div class="feature-icon">🎭</div>
                <div class="feature-title">9.700+ Film</div>
                <div class="feature-description">
                    Database completo con poster, trame e trailer
                </div>
            </div>
            
            <div class="feature">
                <div class="feature-icon">⚡</div>
                <div class="feature-title">Veloce e Facile</div>
                <div class="feature-description">
                    In meno di 2 minuti trovi il film giusto
                </div>
            </div>
        </div>
        
        <!-- Stats -->
        <div class="stats">
            <div class="stat">
                <div class="stat-number">9.700+</div>
                <div class="stat-label">Film disponibili</div>
            </div>
            <div class="stat">
                <div class="stat-number">18</div>
                <div class="stat-label">Generi tra cui scegliere</div>
            </div>
            <div class="stat">
                <div class="stat-number">2 min</div>
                <div class="stat-label">Per trovare il match</div>
            </div>
        </div>
        
        <!-- Demo link -->
        <div class="demo-link">
            <a href="test_tmdb.php">Esplora il database completo →</a>
        </div>
    </div>
    
    <script>
        // Pulisci sessionStorage all'arrivo sulla homepage
        sessionStorage.clear();
        
        // Aggiungi effetto parallax al movimento del mouse
        document.addEventListener('mousemove', (e) => {
            const shapes = document.querySelectorAll('.shape');
            const moveX = (e.clientX - window.innerWidth / 2) / 50;
            const moveY = (e.clientY - window.innerHeight / 2) / 50;
            
            shapes.forEach((shape, index) => {
                const speed = (index + 1) * 0.5;
                shape.style.transform = `translate(${moveX * speed}px, ${moveY * speed}px)`;
            });
        });
    </script>
</body>
</html>
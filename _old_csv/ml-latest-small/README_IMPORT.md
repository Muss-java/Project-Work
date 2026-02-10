# 🎬 Bllendr - Importazione Database MovieLens

## 📋 Istruzioni Complete

### 1. Preparazione File

Hai scaricato **MovieLens Small**. La cartella dovrebbe contenere:
- `movies.csv`
- `ratings.csv`
- `links.csv`
- `tags.csv`
- `README.txt`

### 2. Setup

**Passo 1:** Copia i file che ti ho generato nella cartella del dataset MovieLens:
- `bllendr_schema.sql` (schema database)
- `import_movielens.php` (script di importazione)

**Passo 2:** Avvia XAMPP
- Apri XAMPP Control Panel
- Start **Apache**
- Start **MySQL**

**Passo 3:** Sposta la cartella MovieLens
- Copia l'intera cartella in `C:\xampp\htdocs\` (Windows)
- Oppure in `/Applications/XAMPP/htdocs/` (Mac)
- Esempio: `C:\xampp\htdocs\ml-latest-small\`

### 3. Esecuzione Import

**Metodo 1 - Automatico (CONSIGLIATO):**

1. Apri il browser
2. Vai su: `http://localhost/ml-latest-small/import_movielens.php`
   (sostituisci `ml-latest-small` con il nome della tua cartella)
3. Lo script importerà automaticamente tutto
4. Attendi il completamento (1-2 minuti)

**Metodo 2 - Manuale (solo schema):**

1. Apri phpMyAdmin: `http://localhost/phpmyadmin`
2. Clicca su "Nuovo" per creare un database
3. Nome database: `bllendr`
4. Collation: `utf8mb4_unicode_ci`
5. Vai su "SQL" e incolla il contenuto di `bllendr_schema.sql`
6. Clicca "Esegui"

Poi esegui lo script PHP come nel Metodo 1.

### 4. Verifica Importazione

Dopo l'esecuzione, lo script mostrerà:
- ✅ Numero di film importati
- ✅ Generi unici
- ✅ Ratings importati
- ✅ Top 10 film per rating
- ✅ Distribuzione generi

### 5. Struttura Database Finale

```
bllendr/
├── movies (9,700+ film)
│   ├── movie_id
│   ├── title
│   ├── title_clean (senza anno)
│   ├── year
│   ├── genres
│   ├── avg_rating (calcolato)
│   └── rating_count
├── genres (20 generi)
│   ├── genre_id
│   └── genre_name
├── movie_genres (relazioni molti-a-molti)
├── ratings (100,000+ valutazioni)
├── tags (etichette utenti)
├── movie_links (IMDb/TMDb IDs)
├── user_preferences (per tuo algoritmo)
├── match_sessions (sessioni blend)
└── match_results (risultati algoritmo)
```

### 6. Test Database

Apri phpMyAdmin e prova queste query:

```sql
-- Film più votati
SELECT title, year, avg_rating, rating_count 
FROM movies 
WHERE rating_count >= 100 
ORDER BY avg_rating DESC 
LIMIT 10;

-- Film per genere
SELECT m.title, m.year, m.avg_rating
FROM movies m
JOIN movie_genres mg ON m.movie_id = mg.movie_id
JOIN genres g ON mg.genre_id = g.genre_id
WHERE g.genre_name = 'Action'
ORDER BY m.avg_rating DESC
LIMIT 20;

-- Statistiche generi
SELECT g.genre_name, COUNT(*) as film_count, AVG(m.avg_rating) as avg_genre_rating
FROM genres g
JOIN movie_genres mg ON g.genre_id = mg.genre_id
JOIN movies m ON mg.movie_id = m.movie_id
GROUP BY g.genre_id
ORDER BY film_count DESC;
```

### 7. Configurazione (se necessario)

Se hai impostato una password per MySQL, modifica in `import_movielens.php`:

```php
$config = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => 'TUA_PASSWORD_QUI',  // ← Cambia qui
    'db' => 'bllendr'
];
```

### 8. Limiti Rating (opzionale)

Di default importa 10,000 rating. Per importare tutti (100,000+):

Nel file `import_movielens.php`, linea 28:
```php
$MAX_RATINGS = null; // null = importa tutto
```

⚠️ Attenzione: importare tutti i rating richiede 2-3 minuti extra.

### 9. Troubleshooting

**Errore "Table doesn't exist":**
- Assicurati che `bllendr_schema.sql` sia nella stessa cartella
- Oppure importa manualmente lo schema tramite phpMyAdmin

**Errore "File not found":**
- Verifica che i file CSV siano nella stessa cartella dello script PHP
- Controlla i nomi dei file (case-sensitive su Linux)

**Timeout dello script:**
- Nel file PHP, aumenta: `set_time_limit(600);` (linea 26)

**Errore connessione database:**
- Verifica che MySQL sia avviato in XAMPP
- Controlla username/password in `$config`

### 10. Prossimi Passi - Algoritmo Blend

Ora puoi sviluppare l'algoritmo! Esempio base:

```php
// Recupera preferenze User 1 e User 2
$genres_user1 = ['Action', 'Sci-Fi'];
$genres_user2 = ['Drama', 'Sci-Fi'];

// Trova generi in comune
$common = array_intersect($genres_user1, $genres_user2); // ['Sci-Fi']

// Query suggerimento
$sql = "SELECT m.* 
        FROM movies m
        JOIN movie_genres mg ON m.movie_id = mg.movie_id
        JOIN genres g ON mg.genre_id = g.genre_id
        WHERE g.genre_name IN ('" . implode("','", $common) . "')
        AND m.avg_rating >= 3.5
        ORDER BY m.avg_rating DESC, m.rating_count DESC
        LIMIT 10";
```

---

## 📞 Supporto

Se hai problemi, controlla:
1. XAMPP è avviato?
2. I file sono nella cartella giusta?
3. Il database esiste in phpMyAdmin?

## 📚 Risorse

- MovieLens: https://grouplens.org/datasets/movielens/
- phpMyAdmin: http://localhost/phpmyadmin
- XAMPP Docs: https://www.apachefriends.org/

---

**Progetto Bllendr - Vacondio & Mouanid - 5D**

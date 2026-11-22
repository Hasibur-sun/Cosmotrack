<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include "includes/db.php";

$user_id = $_SESSION['user_id'];

// Filter
$genre    = $_GET['genre']    ?? '';
$min_acc  = $_GET['min_acc']  ?? '';
$min_edu  = $_GET['min_edu']  ?? '';
$search   = $_GET['search']   ?? '';

// Build WHERE
$where = "WHERE 1=1";
if (!empty($genre)) {
    $safe = mysqli_real_escape_string($conn, $genre);
    $where .= " AND space_movies.genre LIKE '%$safe%'";
}
if (!empty($min_acc)) {
    $where .= " AND space_movies.scientific_accuracy >= " . (int)$min_acc;
}
if (!empty($min_edu)) {
    $where .= " AND space_movies.educational_value >= " . (int)$min_edu;
}
if (!empty($search)) {
    $safe = mysqli_real_escape_string($conn, $search);
    $where .= " AND (space_movies.title LIKE '%$safe%'
                OR space_movies.director LIKE '%$safe%'
                OR space_movies.description LIKE '%$safe%')";
}

// Movies with avg rating and user rating
$movies = mysqli_query($conn,
    "SELECT space_movies.*,
            ROUND(AVG(movie_ratings.user_rating), 1) AS avg_rating,
            COUNT(movie_ratings.rating_id) AS total_ratings,
            MAX(CASE WHEN movie_ratings.user_id = $user_id
                THEN movie_ratings.user_rating END) AS my_rating,
            MAX(CASE WHEN movie_ratings.user_id = $user_id
                THEN movie_ratings.review END) AS my_review
     FROM space_movies
     LEFT JOIN movie_ratings ON space_movies.movie_id = movie_ratings.movie_id
     $where
     GROUP BY space_movies.movie_id
     ORDER BY avg_rating DESC, space_movies.scientific_accuracy DESC"
);

$total = mysqli_num_rows($movies);

// Stats
$totalMovies  = mysqli_fetch_row(mysqli_query($conn,
    "SELECT COUNT(*) FROM space_movies"))[0];
$myRatings    = mysqli_fetch_row(mysqli_query($conn,
    "SELECT COUNT(*) FROM movie_ratings WHERE user_id = $user_id"))[0];
$topRated     = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT space_movies.title,
            ROUND(AVG(movie_ratings.user_rating),1) AS avg_r
     FROM space_movies
     LEFT JOIN movie_ratings ON space_movies.movie_id = movie_ratings.movie_id
     GROUP BY space_movies.movie_id
     ORDER BY avg_r DESC LIMIT 1"
));

// GROUP BY genre report
$byGenre = mysqli_query($conn,
    "SELECT genre, COUNT(*) AS total,
            ROUND(AVG(scientific_accuracy),1) AS avg_acc
     FROM space_movies
     GROUP BY genre
     ORDER BY total DESC"
);

$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Space Movies | CosmoTrack</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .movie-card {
      background: rgba(255,255,255,.04);
      border: 1px solid var(--line2);
      border-radius: 16px;
      padding: 22px;
      transition: var(--trans);
      position: relative;
    }
    .movie-card:hover {
      border-color: rgba(93,232,255,.2);
      transform: translateY(-3px);
      box-shadow: 0 12px 30px var(--glow-c);
    }
    .movie-grid {
      display: grid;
      grid-template-columns: repeat(2,1fr);
      gap: 16px;
      margin-top: 16px;
    }
    .score-bar {
      height: 6px;
      border-radius: 999px;
      background: var(--line);
      margin-top: 4px;
      overflow: hidden;
    }
    .score-fill {
      height: 100%;
      border-radius: 999px;
      background: linear-gradient(90deg, var(--cyan), var(--violet));
    }
    .star-rating {
      display: flex;
      gap: 4px;
      margin: 8px 0;
    }
    .star {
      font-size: 18px;
      cursor: pointer;
      transition: var(--trans);
      color: var(--line);
    }
    .star.filled { color: var(--gold); }
    .star:hover  { transform: scale(1.2); }
    @media (max-width:700px) {
      .movie-grid { grid-template-columns: 1fr; }
    }
  </style>
</head>

<body data-page="movies">
  <div class="stars"></div>
  <div class="nebula nebula-one"></div>
  <div class="nebula nebula-two"></div>

  <div class="app-shell">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-panel">
      <button class="mobile-menu" type="button">☰ Menu</button>

      <section class="page-head">
        <div>
          <p class="eyebrow">Movie Library</p>
          <h1>🎬 Space Movie Library</h1>
          <p class="muted">
            Rate and review astronomy and space science films.
          </p>
        </div>
      </section>

      <?php if ($msg == 'rated'): ?>
        <div style="background:rgba(77,255,195,.1);
                    border:1px solid rgba(77,255,195,.3);
                    border-radius:12px; padding:14px 20px;
                    margin-bottom:18px; color:var(--green);">
          ✅ Rating saved successfully.
        </div>
      <?php elseif ($msg == 'updated'): ?>
        <div style="background:rgba(255,200,71,.1);
                    border:1px solid rgba(255,200,71,.3);
                    border-radius:12px; padding:14px 20px;
                    margin-bottom:18px; color:var(--gold);">
          ✅ Rating updated.
        </div>
      <?php endif; ?>

      <!-- STATS -->
      <div class="stats-grid"
           style="grid-template-columns:repeat(4,1fr); margin-bottom:20px;">
        <div class="stat glass">
          <span>Total Movies</span>
          <strong><?php echo $totalMovies; ?></strong>
        </div>
        <div class="stat glass">
          <span>My Ratings</span>
          <strong><?php echo $myRatings; ?></strong>
        </div>
        <div class="stat glass">
          <span>Top Rated</span>
          <strong style="font-size:13px; margin-top:4px;">
            <?php echo $topRated
                  ? htmlspecialchars($topRated['title'])
                  : '—'; ?>
          </strong>
        </div>
        <div class="stat glass">
          <span>Results Showing</span>
          <strong><?php echo $total; ?></strong>
        </div>
      </div>

      <div class="dashboard-grid" style="margin-bottom:20px;">

        <!-- FILTER -->
        <section class="glass panel">
          <h2>🔍 Filter Movies</h2>
          <form method="GET" action="movies.php"
                style="display:grid; gap:14px; margin-top:14px;">

            <label>Search
              <input type="text" name="search"
                     value="<?php echo htmlspecialchars($search); ?>"
                     placeholder="Title, director...">
            </label>

            <label>Min Scientific Accuracy (1-10)
              <input type="number" name="min_acc"
                     value="<?php echo $min_acc; ?>"
                     min="1" max="10" placeholder="e.g. 7">
            </label>

            <label>Min Educational Value (1-10)
              <input type="number" name="min_edu"
                     value="<?php echo $min_edu; ?>"
                     min="1" max="10" placeholder="e.g. 8">
            </label>

            <div style="display:flex; gap:10px;">
              <button class="btn primary" type="submit" style="flex:1;">
                🔍 Filter
              </button>
              <a class="btn ghost" href="movies.php" style="flex:1; text-align:center;">
                ✕ Clear
              </a>
            </div>
          </form>
        </section>

        <!-- GENRE REPORT -->
        <section class="glass panel">
          <h2>📊 Movies by Genre</h2>
          <p class="muted" style="font-size:12px; margin-bottom:14px;">
            SELECT genre, COUNT(*), AVG(scientific_accuracy)
            FROM space_movies GROUP BY genre
          </p>
          <table>
            <thead>
              <tr>
                <th>Genre</th>
                <th>Count</th>
                <th>Avg Accuracy</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = mysqli_fetch_assoc($byGenre)): ?>
                <tr>
                  <td style="font-size:13px;">
                    <?php echo htmlspecialchars($row['genre']); ?>
                  </td>
                  <td>
                    <span class="badge badge-violet">
                      <?php echo $row['total']; ?>
                    </span>
                  </td>
                  <td>
                    <span class="badge badge-cyan">
                      <?php echo $row['avg_acc']; ?>/10
                    </span>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </section>

      </div>

      <!-- MOVIE CARDS -->
      <section class="glass panel">
        <div class="panel-head">
          <h2>Movie Library
            <span class="badge badge-cyan"><?php echo $total; ?> movies</span>
          </h2>
        </div>

        <div class="movie-grid">
          <?php while ($movie = mysqli_fetch_assoc($movies)): ?>
            <div class="movie-card">

              <!-- Title and year -->
              <div style="display:flex; justify-content:space-between;
                          align-items:flex-start; margin-bottom:10px;">
                <div style="flex:1; padding-right:10px;">
                  <h3 style="font-size:17px; font-weight:800;
                             color:var(--text); margin-bottom:4px;">
                    <?php echo htmlspecialchars($movie['title']); ?>
                  </h3>
                  <p class="muted" style="font-size:12px;">
                    <?php echo $movie['director']; ?> •
                    <?php echo $movie['release_year']; ?>
                  </p>
                </div>
                <span class="badge badge-gold" style="flex-shrink:0;">
                  <?php echo $movie['release_year']; ?>
                </span>
              </div>

              <!-- Community rating -->
              <div style="display:flex; align-items:center;
                          gap:10px; margin-bottom:12px;">
                <span style="color:var(--gold); font-size:16px;">★</span>
                <strong style="color:var(--gold); font-size:16px;">
                  <?php echo $movie['avg_rating'] ?? 'No ratings'; ?>
                </strong>
                <span class="muted" style="font-size:12px;">
                  (<?php echo $movie['total_ratings']; ?> ratings)
                </span>
              </div>

              <!-- Score bars -->
              <div style="margin-bottom:14px;">
                <div style="display:flex; justify-content:space-between;
                            font-size:11px; color:var(--muted); margin-bottom:3px;">
                  <span>Scientific Accuracy</span>
                  <span style="color:var(--cyan);">
                    <?php echo $movie['scientific_accuracy']; ?>/10
                  </span>
                </div>
                <div class="score-bar">
                  <div class="score-fill"
                       style="width:<?php echo $movie['scientific_accuracy'] * 10; ?>%">
                  </div>
                </div>

                <div style="display:flex; justify-content:space-between;
                            font-size:11px; color:var(--muted);
                            margin-top:8px; margin-bottom:3px;">
                  <span>Educational Value</span>
                  <span style="color:var(--violet);">
                    <?php echo $movie['educational_value']; ?>/10
                  </span>
                </div>
                <div class="score-bar">
                  <div class="score-fill"
                       style="width:<?php echo $movie['educational_value'] * 10; ?>%;
                              background:linear-gradient(90deg,var(--violet),var(--pink));">
                  </div>
                </div>
              </div>

              <!-- Description -->
              <p style="font-size:12px; color:var(--muted);
                        line-height:1.6; margin-bottom:14px;">
                <?php echo htmlspecialchars(substr($movie['description'], 0, 120)); ?>...
              </p>

              <!-- MY RATING FORM -->
              <div style="border-top:1px solid var(--line2); padding-top:14px;">
                <p style="font-size:12px; font-weight:700;
                          color:var(--text); margin-bottom:8px;">
                  <?php echo $movie['my_rating']
                        ? 'Your Rating: ' . $movie['my_rating'] . '/5 ★'
                        : 'Rate this movie:'; ?>
                </p>

                <form method="POST" action="actions/rate_movie.php">
                  <input type="hidden" name="movie_id"
                         value="<?php echo $movie['movie_id']; ?>">

                  <!-- Star rating -->
                  <div class="star-rating" id="stars-<?php echo $movie['movie_id']; ?>">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                      <span class="star <?php echo ($movie['my_rating'] >= $i) ? 'filled' : ''; ?>"
                            onclick="setRating(<?php echo $movie['movie_id']; ?>, <?php echo $i; ?>)"
                            title="<?php echo $i; ?> star<?php echo $i > 1 ? 's' : ''; ?>">
                        ★
                      </span>
                    <?php endfor; ?>
                  </div>
                  <input type="hidden" name="user_rating"
                         id="rating-<?php echo $movie['movie_id']; ?>"
                         value="<?php echo $movie['my_rating'] ?? 0; ?>">

                  <label style="text-transform:none; font-size:12px;
                                font-weight:400; margin-top:8px;">
                    <textarea name="review" rows="2"
                              style="font-size:12px; padding:8px;"
                              placeholder="Write a short review...">
<?php echo htmlspecialchars($movie['my_review'] ?? ''); ?></textarea>
                  </label>

                  <button class="btn primary tiny" type="submit"
                          style="margin-top:8px; width:100%;">
                    <?php echo $movie['my_rating'] ? '✏️ Update Rating' : '💾 Save Rating'; ?>
                  </button>
                </form>

                <!-- IMDB link -->
                <?php if ($movie['imdb_link']): ?>
                  <a href="<?php echo $movie['imdb_link']; ?>"
                     target="_blank"
                     style="color:var(--cyan); font-size:12px;
                            display:block; margin-top:10px; text-align:center;">
                    View on IMDB →
                  </a>
                <?php endif; ?>
              </div>

            </div>
          <?php endwhile; ?>
        </div>
      </section>

    </main>
  </div>

  <script>
  function setRating(movieId, rating) {
    // Update hidden input
    document.getElementById('rating-' + movieId).value = rating;

    // Update star colors
    const stars = document.querySelectorAll(
      '#stars-' + movieId + ' .star'
    );
    stars.forEach((star, index) => {
      star.classList.toggle('filled', index < rating);
    });
  }
  </script>

  <script src="assets/js/app.js"></script>
</body>
</html>
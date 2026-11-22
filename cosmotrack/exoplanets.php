<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include "includes/db.php";

$user_id = $_SESSION['user_id'];

// Filters from URL
$habitability    = $_GET['habitability']    ?? '';
$discovery       = $_GET['discovery']       ?? '';
$max_distance    = $_GET['max_distance']    ?? '';
$max_mass        = $_GET['max_mass']        ?? '';
$search          = $_GET['search']          ?? '';

// Build WHERE clause
$where = "WHERE 1=1";

if (!empty($habitability)) {
    $safe = mysqli_real_escape_string($conn, $habitability);
    $where .= " AND habitability_zone = '$safe'";
}
if (!empty($discovery)) {
    $safe = mysqli_real_escape_string($conn, $discovery);
    $where .= " AND discovery_method = '$safe'";
}
if (!empty($max_distance)) {
    $where .= " AND distance_ly <= " . (float)$max_distance;
}
if (!empty($max_mass)) {
    $where .= " AND planet_mass_earth <= " . (float)$max_mass;
}
if (!empty($search)) {
    $safe = mysqli_real_escape_string($conn, $search);
    $where .= " AND (planet_name LIKE '%$safe%'
                OR host_star LIKE '%$safe%'
                OR description LIKE '%$safe%')";
}

// Fetch planets
$planets = mysqli_query($conn,
    "SELECT exoplanets.*,
            CASE WHEN exoplanet_bookmarks.bookmark_id IS NOT NULL
                 THEN 1 ELSE 0 END AS is_bookmarked
     FROM exoplanets
     LEFT JOIN exoplanet_bookmarks
       ON exoplanets.planet_id = exoplanet_bookmarks.planet_id
       AND exoplanet_bookmarks.user_id = $user_id
     $where
     ORDER BY distance_ly ASC"
);

$total = mysqli_num_rows($planets);

// Stats
$totalPlanets   = mysqli_fetch_row(mysqli_query($conn,
    "SELECT COUNT(*) FROM exoplanets"))[0];
$habitableCount = mysqli_fetch_row(mysqli_query($conn,
    "SELECT COUNT(*) FROM exoplanets
     WHERE habitability_zone = 'Habitable Zone'"))[0];
$avgDistance    = mysqli_fetch_row(mysqli_query($conn,
    "SELECT ROUND(AVG(distance_ly), 2) FROM exoplanets"))[0];
$myBookmarks    = mysqli_fetch_row(mysqli_query($conn,
    "SELECT COUNT(*) FROM exoplanet_bookmarks
     WHERE user_id = $user_id"))[0];

// GROUP BY discovery method
$byMethod = mysqli_query($conn,
    "SELECT discovery_method, COUNT(*) AS total,
            ROUND(AVG(distance_ly), 2) AS avg_distance
     FROM exoplanets
     GROUP BY discovery_method
     ORDER BY total DESC"
);

// GROUP BY habitability
$byHabitability = mysqli_query($conn,
    "SELECT habitability_zone, COUNT(*) AS total
     FROM exoplanets
     GROUP BY habitability_zone
     ORDER BY total DESC"
);

$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Exoplanet Explorer | CosmoTrack</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .planet-card {
      background: rgba(255,255,255,.04);
      border: 1px solid var(--line2);
      border-radius: 16px;
      padding: 20px;
      transition: var(--trans);
      position: relative;
    }
    .planet-card:hover {
      border-color: rgba(93,232,255,.25);
      transform: translateY(-3px);
      box-shadow: 0 12px 30px var(--glow-c);
    }
    .planet-card.habitable {
      border-color: rgba(77,255,195,.2);
    }
    .planet-card.habitable:hover {
      border-color: rgba(77,255,195,.4);
      box-shadow: 0 12px 30px rgba(77,255,195,.1);
    }
    .planet-grid {
      display: grid;
      grid-template-columns: repeat(3,1fr);
      gap: 16px;
      margin-top: 16px;
    }
    .planet-stat {
      font-size: 11px;
      color: var(--muted);
      margin-top: 4px;
    }
    .planet-stat strong {
      color: var(--text);
      font-size: 13px;
    }
    .bookmark-btn {
      position: absolute;
      top: 14px;
      right: 14px;
      background: none;
      border: none;
      font-size: 18px;
      cursor: pointer;
      transition: var(--trans);
    }
    .bookmark-btn:hover { transform: scale(1.3); }
    @media (max-width:900px) {
      .planet-grid { grid-template-columns: repeat(2,1fr); }
    }
    @media (max-width:600px) {
      .planet-grid { grid-template-columns: 1fr; }
    }
  </style>
</head>

<body data-page="exoplanets">
  <div class="stars"></div>
  <div class="nebula nebula-one"></div>
  <div class="nebula nebula-two"></div>

  <div class="app-shell">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-panel">
      <button class="mobile-menu" type="button">☰ Menu</button>

      <section class="page-head">
        <div>
          <p class="eyebrow">Exoplanet Module</p>
          <h1>🌍 Exoplanet Explorer</h1>
          <p class="muted">
            Browse and filter <?php echo $totalPlanets; ?> discovered exoplanets.
            Bookmark your favourites.
          </p>
        </div>
      </section>

      <?php if ($msg == 'bookmarked'): ?>
        <div style="background:rgba(77,255,195,.1);
                    border:1px solid rgba(77,255,195,.3);
                    border-radius:12px; padding:14px 20px;
                    margin-bottom:18px; color:var(--green);">
          ✅ Exoplanet bookmarked successfully.
        </div>
      <?php elseif ($msg == 'removed'): ?>
        <div style="background:rgba(255,95,126,.1);
                    border:1px solid rgba(255,95,126,.3);
                    border-radius:12px; padding:14px 20px;
                    margin-bottom:18px; color:var(--danger);">
          🗑️ Bookmark removed.
        </div>
      <?php endif; ?>

      <!-- STATS -->
      <div class="stats-grid"
           style="grid-template-columns:repeat(4,1fr); margin-bottom:20px;">
        <div class="stat glass">
          <span>Total Exoplanets</span>
          <strong><?php echo $totalPlanets; ?></strong>
        </div>
        <div class="stat glass">
          <span>In Habitable Zone</span>
          <strong style="color:var(--green);">
            <?php echo $habitableCount; ?>
          </strong>
        </div>
        <div class="stat glass">
          <span>Avg Distance</span>
          <strong style="font-size:18px; margin-top:6px;">
            <?php echo $avgDistance; ?> ly
          </strong>
        </div>
        <div class="stat glass">
          <span>My Bookmarks</span>
          <strong><?php echo $myBookmarks; ?></strong>
        </div>
      </div>

      <div class="dashboard-grid" style="margin-bottom:20px;">

        <!-- FILTER FORM -->
        <section class="glass panel">
          <h2>🔍 Filter Exoplanets</h2>
          <form method="GET" action="exoplanets.php"
                style="display:grid; gap:14px; margin-top:14px;">

            <label>Search
              <input type="text" name="search"
                     value="<?php echo htmlspecialchars($search); ?>"
                     placeholder="Planet name, star, description...">
            </label>

            <label>Habitability Zone
              <select name="habitability">
                <option value="">-- All Zones --</option>
                <option value="Habitable Zone"
                  <?php echo $habitability=='Habitable Zone' ? 'selected':''; ?>>
                  🟢 Habitable Zone
                </option>
                <option value="Too Hot"
                  <?php echo $habitability=='Too Hot' ? 'selected':''; ?>>
                  🔴 Too Hot
                </option>
                <option value="Too Cold"
                  <?php echo $habitability=='Too Cold' ? 'selected':''; ?>>
                  🔵 Too Cold
                </option>
                <option value="Unknown"
                  <?php echo $habitability=='Unknown' ? 'selected':''; ?>>
                  ⚪ Unknown
                </option>
              </select>
            </label>

            <label>Discovery Method
              <select name="discovery">
                <option value="">-- All Methods --</option>
                <option value="Transit"
                  <?php echo $discovery=='Transit' ? 'selected':''; ?>>
                  Transit
                </option>
                <option value="Radial Velocity"
                  <?php echo $discovery=='Radial Velocity' ? 'selected':''; ?>>
                  Radial Velocity
                </option>
                <option value="Direct Imaging"
                  <?php echo $discovery=='Direct Imaging' ? 'selected':''; ?>>
                  Direct Imaging
                </option>
                <option value="Microlensing"
                  <?php echo $discovery=='Microlensing' ? 'selected':''; ?>>
                  Microlensing
                </option>
              </select>
            </label>

            <label>Max Distance (light years)
              <input type="number" name="max_distance"
                     value="<?php echo $max_distance; ?>"
                     placeholder="e.g. 500">
            </label>

            <label>Max Mass (× Earth mass)
              <input type="number" name="max_mass"
                     value="<?php echo $max_mass; ?>"
                     placeholder="e.g. 10">
            </label>

            <div style="display:flex; gap:10px;">
              <button class="btn primary" type="submit"
                      style="flex:1;">🔍 Filter</button>
              <a class="btn ghost" href="exoplanets.php"
                 style="flex:1; text-align:center;">✕ Clear</a>
            </div>

          </form>
        </section>

        <!-- REPORTS -->
        <section class="glass panel">
          <h2>📊 Discovery Reports</h2>

          <p style="color:var(--cyan); font-size:12px;
                    font-weight:700; margin:14px 0 6px;">
            By Discovery Method
          </p>
          <table>
            <thead>
              <tr>
                <th>Method</th>
                <th>Count</th>
                <th>Avg Distance</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = mysqli_fetch_assoc($byMethod)): ?>
                <tr>
                  <td><?php echo htmlspecialchars($row['discovery_method']); ?></td>
                  <td><span class="badge badge-cyan"><?php echo $row['total']; ?></span></td>
                  <td style="font-size:12px; color:var(--muted);">
                    <?php echo $row['avg_distance']; ?> ly
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>

          <p style="color:var(--cyan); font-size:12px;
                    font-weight:700; margin:18px 0 6px;">
            By Habitability Zone
          </p>
          <table>
            <thead>
              <tr><th>Zone</th><th>Count</th></tr>
            </thead>
            <tbody>
              <?php while ($row = mysqli_fetch_assoc($byHabitability)): ?>
                <tr>
                  <td>
                    <?php
                      $z = $row['habitability_zone'];
                      $badge = $z == 'Habitable Zone' ? 'badge-green'
                             : ($z == 'Too Hot' ? 'badge-red' : 'badge-cyan');
                    ?>
                    <span class="badge <?php echo $badge; ?>">
                      <?php echo htmlspecialchars($z); ?>
                    </span>
                  </td>
                  <td><span class="badge badge-violet">
                    <?php echo $row['total']; ?>
                  </span></td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>

          <div style="margin-top:20px; padding-top:16px;
                      border-top:1px solid var(--line2);">
            <p class="eyebrow" style="margin-bottom:10px;">SQL Used</p>
            <div class="sql-box">SELECT discovery_method,
  COUNT(*) AS total,
  AVG(distance_ly)
FROM exoplanets
GROUP BY discovery_method;</div>
          </div>
        </section>

      </div>

      <!-- PLANET CARDS -->
      <section class="glass panel">
        <div class="panel-head">
          <h2>Exoplanet Database
            <span class="badge badge-cyan"><?php echo $total; ?> results</span>
          </h2>
          <a class="btn ghost tiny" href="my_bookmarks.php">
            ★ My Bookmarks (<?php echo $myBookmarks; ?>)
          </a>
        </div>

        <?php if ($total == 0): ?>
          <p class="muted" style="padding:20px;">
            No exoplanets found matching your filters.
            <a href="exoplanets.php" style="color:var(--cyan);">Clear filters →</a>
          </p>
        <?php else: ?>
          <div class="planet-grid">
            <?php while ($planet = mysqli_fetch_assoc($planets)): ?>
              <?php
                $habClass = $planet['habitability_zone'] == 'Habitable Zone'
                          ? 'habitable' : '';
                $habBadge = $planet['habitability_zone'] == 'Habitable Zone'
                          ? 'badge-green'
                          : ($planet['habitability_zone'] == 'Too Hot'
                             ? 'badge-red' : 'badge-cyan');
              ?>
              <div class="planet-card <?php echo $habClass; ?>">

                <!-- Bookmark button -->
                <form method="POST"
                      action="actions/toggle_bookmark.php"
                      style="display:inline;">
                  <input type="hidden" name="planet_id"
                         value="<?php echo $planet['planet_id']; ?>">
                  <input type="hidden" name="is_bookmarked"
                         value="<?php echo $planet['is_bookmarked']; ?>">
                  <button type="submit" class="bookmark-btn"
                          title="<?php echo $planet['is_bookmarked']
                                         ? 'Remove bookmark'
                                         : 'Bookmark this planet'; ?>">
                    <?php echo $planet['is_bookmarked'] ? '★' : '☆'; ?>
                  </button>
                </form>

                <!-- Planet name -->
                <p class="eyebrow" style="font-size:10px; margin-bottom:6px;">
                  <?php echo htmlspecialchars($planet['host_star']); ?>
                </p>
                <h3 style="font-size:16px; font-weight:800;
                           margin-bottom:10px; color:var(--text);
                           padding-right:28px;">
                  <?php echo htmlspecialchars($planet['planet_name']); ?>
                </h3>

                <!-- Badges -->
                <div style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:12px;">
                  <span class="badge <?php echo $habBadge; ?>">
                    <?php echo $planet['habitability_zone']; ?>
                  </span>
                  <span class="badge badge-violet">
                    <?php echo $planet['discovery_method']; ?>
                  </span>
                  <span class="badge badge-gold">
                    <?php echo $planet['discovery_year']; ?>
                  </span>
                </div>

                <!-- Stats -->
                <div style="display:grid; grid-template-columns:1fr 1fr;
                            gap:8px; margin-bottom:12px;">
                  <div class="planet-stat">
                    Distance<br>
                    <strong><?php echo $planet['distance_ly']; ?> ly</strong>
                  </div>
                  <div class="planet-stat">
                    Mass<br>
                    <strong><?php echo $planet['planet_mass_earth']; ?>× Earth</strong>
                  </div>
                  <div class="planet-stat">
                    Radius<br>
                    <strong><?php echo $planet['planet_radius_earth']; ?>× Earth</strong>
                  </div>
                  <div class="planet-stat">
                    Orbital Period<br>
                    <strong><?php echo $planet['orbital_period_days']; ?> days</strong>
                  </div>
                </div>

                <!-- Description -->
                <p style="font-size:12px; color:var(--muted);
                          line-height:1.6; margin-bottom:12px;">
                  <?php echo htmlspecialchars(
                    substr($planet['description'], 0, 100)
                  ); ?>...
                </p>

                <!-- Surface temp -->
                <?php if ($planet['surface_temp_k']): ?>
                  <p class="planet-stat">
                    Surface Temp:
                    <strong>
                      <?php echo $planet['surface_temp_k']; ?>K
                      (<?php echo $planet['surface_temp_k'] - 273; ?>°C)
                    </strong>
                  </p>
                <?php endif; ?>

                <!-- NASA link -->
                <?php if ($planet['nasa_link']): ?>
                  <a href="<?php echo $planet['nasa_link']; ?>"
                     target="_blank"
                     style="color:var(--cyan); font-size:12px;
                            display:block; margin-top:10px;">
                    View on NASA →
                  </a>
                <?php endif; ?>

              </div>
            <?php endwhile; ?>
          </div>
        <?php endif; ?>
      </section>

    </main>
  </div>
  <script src="assets/js/app.js"></script>
</body>
</html>
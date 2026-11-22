<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include "includes/db.php";

$user_id = $_SESSION['user_id'];

// 1. Total observations
$totalObs = mysqli_fetch_row(mysqli_query($conn,
    "SELECT COUNT(*) FROM observations WHERE user_id = $user_id"))[0];

// 2. Average quality score
$avgQuality = mysqli_fetch_row(mysqli_query($conn,
    "SELECT ROUND(AVG(quality_score), 1)
     FROM observations
     WHERE user_id = $user_id"))[0];

// 3. Best quality score
$bestQuality = mysqli_fetch_row(mysqli_query($conn,
    "SELECT MAX(quality_score)
     FROM observations
     WHERE user_id = $user_id"))[0];

// 4. Most observed object
$topObject = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT object_name, COUNT(*) AS total
     FROM observations
     WHERE user_id = $user_id
     GROUP BY object_name
     ORDER BY total DESC
     LIMIT 1"));

// 5. Observations by object type (for bar chart)
$byType = mysqli_query($conn,
    "SELECT object_type, COUNT(*) AS total
     FROM observations
     WHERE user_id = $user_id
     AND object_type != ''
     GROUP BY object_type
     ORDER BY total DESC");

// 6. Observations by month (for bar chart)
$byMonth = mysqli_query($conn,
    "SELECT MONTHNAME(observation_date) AS month_name,
            MONTH(observation_date) AS month_num,
            COUNT(*) AS total
     FROM observations
     WHERE user_id = $user_id
     AND observation_date IS NOT NULL
     GROUP BY MONTH(observation_date), MONTHNAME(observation_date)
     ORDER BY month_num ASC");

// 7. Observations by sky condition
$bySky = mysqli_query($conn,
    "SELECT sky_condition, COUNT(*) AS total
     FROM observations
     WHERE user_id = $user_id
     AND sky_condition != ''
     GROUP BY sky_condition
     ORDER BY total DESC");

// 8. Most used location
$topLocation = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT locations.location_name, COUNT(*) AS total
     FROM observations
     JOIN locations ON observations.location_id = locations.location_id
     WHERE observations.user_id = $user_id
     GROUP BY locations.location_name
     ORDER BY total DESC
     LIMIT 1"));

// 9. Most used telescope
$topTelescope = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT telescopes.telescope_name, COUNT(*) AS total
     FROM observations
     JOIN telescopes ON observations.telescope_id = telescopes.telescope_id
     WHERE observations.user_id = $user_id
     GROUP BY telescopes.telescope_name
     ORDER BY total DESC
     LIMIT 1"));

// 10. Observations by visibility
$byVisibility = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT
        SUM(CASE WHEN visibility = 'Public'  THEN 1 ELSE 0 END) AS public_count,
        SUM(CASE WHEN visibility = 'Private' THEN 1 ELSE 0 END) AS private_count
     FROM observations
     WHERE user_id = $user_id"));

// Store chart data in PHP arrays for JavaScript
$typeLabels = [];
$typeData   = [];
while ($row = mysqli_fetch_assoc($byType)) {
    $typeLabels[] = $row['object_type'];
    $typeData[]   = (int)$row['total'];
}

$monthLabels = [];
$monthData   = [];
while ($row = mysqli_fetch_assoc($byMonth)) {
    $monthLabels[] = substr($row['month_name'], 0, 3);
    $monthData[]   = (int)$row['total'];
}

$skyLabels = [];
$skyData   = [];
while ($row = mysqli_fetch_assoc($bySky)) {
    $skyLabels[] = $row['sky_condition'];
    $skyData[]   = (int)$row['total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Statistics | CosmoTrack</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body data-page="stats">
  <div class="stars"></div>
  <div class="nebula nebula-one"></div>
  <div class="nebula nebula-two"></div>

  <div class="app-shell">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-panel">
      <button class="mobile-menu" type="button">☰ Menu</button>

      <section class="page-head">
        <div>
          <p class="eyebrow">Analytics</p>
          <h1>My Observation Statistics</h1>
          <p class="muted">
            Generated using GROUP BY, COUNT, AVG, MAX queries on your observations.
          </p>
        </div>
      </section>

      <!-- TOP STATS -->
      <div class="stats-grid" style="grid-template-columns:repeat(4,1fr); margin-bottom:20px;">
        <div class="stat glass">
          <span>Total Observations</span>
          <strong><?php echo $totalObs ?? 0; ?></strong>
        </div>
        <div class="stat glass">
          <span>Average Quality</span>
          <strong><?php echo $avgQuality ?? 0; ?></strong>
        </div>
        <div class="stat glass">
          <span>Best Quality</span>
          <strong><?php echo $bestQuality ?? 0; ?></strong>
        </div>
        <div class="stat glass">
          <span>Most Observed</span>
          <strong style="font-size:16px; margin-top:6px;">
            <?php echo $topObject ? htmlspecialchars($topObject['object_name']) : '—'; ?>
          </strong>
        </div>
      </div>

      <!-- INSIGHT CARDS -->
      <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:20px;">

        <div class="glass panel">
          <p class="eyebrow">Top Location</p>
          <p style="font-size:1.1rem; font-weight:700; margin-top:10px; color:var(--text);">
            <?php echo $topLocation ? htmlspecialchars($topLocation['location_name']) : 'No data'; ?>
          </p>
          <?php if ($topLocation): ?>
            <p class="muted" style="font-size:12px;">
              Used <?php echo $topLocation['total']; ?> times
            </p>
          <?php endif; ?>
        </div>

        <div class="glass panel">
          <p class="eyebrow">Top Telescope</p>
          <p style="font-size:1.1rem; font-weight:700; margin-top:10px; color:var(--text);">
            <?php echo $topTelescope ? htmlspecialchars($topTelescope['telescope_name']) : 'No data'; ?>
          </p>
          <?php if ($topTelescope): ?>
            <p class="muted" style="font-size:12px;">
              Used <?php echo $topTelescope['total']; ?> times
            </p>
          <?php endif; ?>
        </div>

        <div class="glass panel">
          <p class="eyebrow">Visibility Split</p>
          <p style="margin-top:10px;">
            <span class="vis-public" style="font-size:1.1rem; font-weight:700;">
              <?php echo $byVisibility['public_count'] ?? 0; ?> Public
            </span>
          </p>
          <p>
            <span class="vis-private" style="font-size:1.1rem; font-weight:700;">
              <?php echo $byVisibility['private_count'] ?? 0; ?> Private
            </span>
          </p>
        </div>

      </div>

      <!-- CHARTS ROW 1 -->
      <div class="dashboard-grid" style="margin-bottom:20px;">

        <!-- Object Type Chart -->
        <section class="glass panel">
          <h2>Observations by Object Type</h2>
          <p class="muted" style="font-size:12px; margin-bottom:16px;">
            SELECT object_type, COUNT(*) FROM observations GROUP BY object_type
          </p>
          <?php if (empty($typeLabels)): ?>
            <p class="muted">No data yet — add some observations first.</p>
          <?php else: ?>
            <canvas id="typeChart" height="220"></canvas>
          <?php endif; ?>
        </section>

        <!-- Sky Condition Chart -->
        <section class="glass panel">
          <h2>Observations by Sky Condition</h2>
          <p class="muted" style="font-size:12px; margin-bottom:16px;">
            SELECT sky_condition, COUNT(*) FROM observations GROUP BY sky_condition
          </p>
          <?php if (empty($skyLabels)): ?>
            <p class="muted">No data yet — add some observations first.</p>
          <?php else: ?>
            <canvas id="skyChart" height="220"></canvas>
          <?php endif; ?>
        </section>

      </div>

      <!-- CHARTS ROW 2 -->
      <section class="glass panel wide" style="margin-bottom:20px;">
        <h2>Observations by Month</h2>
        <p class="muted" style="font-size:12px; margin-bottom:16px;">
          SELECT MONTHNAME(observation_date), COUNT(*) FROM observations
          GROUP BY MONTH(observation_date) ORDER BY month_num ASC
        </p>
        <?php if (empty($monthLabels)): ?>
          <p class="muted">No data yet — add observations with dates first.</p>
        <?php else: ?>
          <canvas id="monthChart" height="120"></canvas>
        <?php endif; ?>
      </section>

      <!-- SQL QUERIES USED -->
      <section class="glass panel wide">
        <h2>📊 SQL Queries Behind This Page</h2>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-top:12px;">

          <div>
            <p style="color:var(--cyan); font-size:12px; font-weight:700; margin-bottom:6px;">
              AVG + MAX
            </p>
            <div class="sql-box">SELECT ROUND(AVG(quality_score),1),
       MAX(quality_score)
FROM observations
WHERE user_id = $user_id;</div>
          </div>

          <div>
            <p style="color:var(--cyan); font-size:12px; font-weight:700; margin-bottom:6px;">
              GROUP BY object_type
            </p>
            <div class="sql-box">SELECT object_type, COUNT(*) AS total
FROM observations
WHERE user_id = $user_id
GROUP BY object_type
ORDER BY total DESC;</div>
          </div>

          <div>
            <p style="color:var(--cyan); font-size:12px; font-weight:700; margin-bottom:6px;">
              GROUP BY month
            </p>
            <div class="sql-box">SELECT MONTHNAME(observation_date),
       COUNT(*) AS total
FROM observations
WHERE user_id = $user_id
GROUP BY MONTH(observation_date)
ORDER BY MONTH(observation_date);</div>
          </div>

          <div>
            <p style="color:var(--cyan); font-size:12px; font-weight:700; margin-bottom:6px;">
              JOIN + GROUP BY
            </p>
            <div class="sql-box">SELECT locations.location_name,
       COUNT(*) AS total
FROM observations
JOIN locations ON
  observations.location_id = locations.location_id
WHERE observations.user_id = $user_id
GROUP BY locations.location_name
ORDER BY total DESC LIMIT 1;</div>
          </div>

        </div>
      </section>

    </main>
  </div>

  <!-- CHART.JS SCRIPTS -->
  <script>
    const cyan   = 'rgba(93,232,255,';
    const violet = 'rgba(139,111,255,';
    const pink   = 'rgba(255,111,216,';
    const gold   = 'rgba(255,200,71,';
    const green  = 'rgba(77,255,195,';

    const chartDefaults = {
      plugins: {
        legend: { labels: { color: '#aabbd8', font: { size: 12 } } }
      },
      scales: {
        x: { ticks: { color: '#aabbd8' }, grid: { color: 'rgba(255,255,255,.06)' } },
        y: { ticks: { color: '#aabbd8' }, grid: { color: 'rgba(255,255,255,.06)' }, beginAtZero: true }
      }
    };

    <?php if (!empty($typeLabels)): ?>
    // Object Type Chart
    new Chart(document.getElementById('typeChart'), {
      type: 'bar',
      data: {
        labels: <?php echo json_encode($typeLabels); ?>,
        datasets: [{
          label: 'Observations',
          data: <?php echo json_encode($typeData); ?>,
          backgroundColor: [
            cyan+'0.7)', violet+'0.7)', pink+'0.7)',
            gold+'0.7)', green+'0.7)', cyan+'0.5)',
            violet+'0.5)', pink+'0.5)'
          ],
          borderColor: [
            cyan+'1)', violet+'1)', pink+'1)',
            gold+'1)', green+'1)', cyan+'1)',
            violet+'1)', pink+'1)'
          ],
          borderWidth: 1,
          borderRadius: 6,
        }]
      },
      options: chartDefaults
    });
    <?php endif; ?>

    <?php if (!empty($skyLabels)): ?>
    // Sky Condition Chart
    new Chart(document.getElementById('skyChart'), {
      type: 'doughnut',
      data: {
        labels: <?php echo json_encode($skyLabels); ?>,
        datasets: [{
          data: <?php echo json_encode($skyData); ?>,
          backgroundColor: [
            cyan+'0.75)', violet+'0.75)',
            pink+'0.75)', gold+'0.75)', green+'0.75)'
          ],
          borderColor: 'rgba(0,0,0,0)',
          borderWidth: 0,
        }]
      },
      options: {
        plugins: {
          legend: { labels: { color: '#aabbd8', font: { size: 12 } } }
        },
        cutout: '65%'
      }
    });
    <?php endif; ?>

    <?php if (!empty($monthLabels)): ?>
    // Month Chart
    new Chart(document.getElementById('monthChart'), {
      type: 'line',
      data: {
        labels: <?php echo json_encode($monthLabels); ?>,
        datasets: [{
          label: 'Observations',
          data: <?php echo json_encode($monthData); ?>,
          borderColor: 'rgba(93,232,255,1)',
          backgroundColor: 'rgba(93,232,255,0.08)',
          pointBackgroundColor: 'rgba(93,232,255,1)',
          pointRadius: 5,
          fill: true,
          tension: 0.4,
        }]
      },
      options: chartDefaults
    });
    <?php endif; ?>
  </script>

  <script src="assets/js/app.js"></script>
</body>
</html>
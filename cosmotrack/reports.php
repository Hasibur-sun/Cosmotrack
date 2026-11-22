<?php
include "includes/db.php";

// Report 1: Total observations by object type
$byType = mysqli_query($conn, "
    SELECT object_type, COUNT(*) AS total
    FROM observations
    GROUP BY object_type
    ORDER BY total DESC
");

// Report 2: Most used telescope
$byTelescope = mysqli_query($conn, "
    SELECT telescopes.telescope_name, COUNT(observations.observation_id) AS total_used
    FROM observations
    JOIN telescopes ON observations.telescope_id = telescopes.telescope_id
    GROUP BY telescopes.telescope_name
    ORDER BY total_used DESC
");

// Report 3: Most active location
$byLocation = mysqli_query($conn, "
    SELECT locations.location_name, COUNT(observations.observation_id) AS total_observations
    FROM observations
    JOIN locations ON observations.location_id = locations.location_id
    GROUP BY locations.location_name
    ORDER BY total_observations DESC
");

// Report 4: Observations by month
$byMonth = mysqli_query($conn, "
    SELECT MONTH(observation_date) AS month_number,
           MONTHNAME(observation_date) AS month_name,
           COUNT(*) AS total
    FROM observations
    WHERE observation_date IS NOT NULL
    GROUP BY MONTH(observation_date), MONTHNAME(observation_date)
    ORDER BY month_number
");

// Report 5: Locations with more than 1 observation (HAVING)
$havingReport = mysqli_query($conn, "
    SELECT locations.location_name, COUNT(observations.observation_id) AS total_observations
    FROM observations
    JOIN locations ON observations.location_id = locations.location_id
    GROUP BY locations.location_name
    HAVING COUNT(observations.observation_id) > 1
    ORDER BY total_observations DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>SQL Reports | CosmoTrack</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .report-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 10px;
    }
    .report-table th {
      background: #1a1a3e;
      color: #4fc3f7;
      padding: 10px 14px;
      text-align: left;
      border: 1px solid #333;
    }
    .report-table td {
      padding: 10px 14px;
      border: 1px solid #2a2a4a;
      color: #ccc;
    }
    .report-table tr:hover { background: #1a1a2e; }
    .sql-box {
      background: #0d0d1f;
      border-left: 3px solid #4fc3f7;
      padding: 12px 16px;
      font-family: monospace;
      font-size: 0.85rem;
      color: #a0c4ff;
      margin: 10px 0 16px;
      border-radius: 4px;
      white-space: pre-wrap;
    }
    .report-section { margin-bottom: 40px; }
    .report-section h2 { color: #4fc3f7; border-bottom: 1px solid #333; padding-bottom: 8px; }
  </style>
</head>

<body data-page="reports">
  <div class="stars"></div>
  <div class="nebula nebula-one"></div>
  <div class="nebula nebula-two"></div>

  <div class="app-shell">

    <?php include 'includes/sidebar.php'; ?>

    <main class="main-panel">
      <button class="mobile-menu" type="button">☰ Menu</button>

      <section class="page-head">
        <div>
          <p class="eyebrow">SQL Reports</p>
          <h1>Database Reports</h1>
          <p class="muted">Generated using GROUP BY, HAVING, JOIN, and COUNT queries on MySQL.</p>
        </div>
      </section>

      <!-- REPORT 1 -->
      <section class="glass panel report-section">
        <h2>📊 Observations by Object Type</h2>
        <div class="sql-box">SELECT object_type, COUNT(*) AS total
FROM observations
GROUP BY object_type
ORDER BY total DESC;</div>
        <table class="report-table">
          <thead><tr><th>Object Type</th><th>Total Observations</th></tr></thead>
          <tbody>
            <?php if (mysqli_num_rows($byType) == 0): ?>
              <tr><td colspan="2" style="color:#666">No data yet.</td></tr>
            <?php endif; ?>
            <?php while ($row = mysqli_fetch_assoc($byType)): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['object_type'] ?: 'Unknown'); ?></td>
                <td><?php echo $row['total']; ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </section>

      <!-- REPORT 2 -->
      <section class="glass panel report-section">
        <h2>🔭 Most Used Telescope</h2>
        <div class="sql-box">SELECT telescopes.telescope_name, COUNT(observations.observation_id) AS total_used
FROM observations
JOIN telescopes ON observations.telescope_id = telescopes.telescope_id
GROUP BY telescopes.telescope_name
ORDER BY total_used DESC;</div>
        <table class="report-table">
          <thead><tr><th>Telescope</th><th>Times Used</th></tr></thead>
          <tbody>
            <?php if (mysqli_num_rows($byTelescope) == 0): ?>
              <tr><td colspan="2" style="color:#666">No data yet.</td></tr>
            <?php endif; ?>
            <?php while ($row = mysqli_fetch_assoc($byTelescope)): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['telescope_name']); ?></td>
                <td><?php echo $row['total_used']; ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </section>

      <!-- REPORT 3 -->
      <section class="glass panel report-section">
        <h2>📍 Most Active Observation Location</h2>
        <div class="sql-box">SELECT locations.location_name, COUNT(observations.observation_id) AS total_observations
FROM observations
JOIN locations ON observations.location_id = locations.location_id
GROUP BY locations.location_name
ORDER BY total_observations DESC;</div>
        <table class="report-table">
          <thead><tr><th>Location</th><th>Total Observations</th></tr></thead>
          <tbody>
            <?php if (mysqli_num_rows($byLocation) == 0): ?>
              <tr><td colspan="2" style="color:#666">No data yet.</td></tr>
            <?php endif; ?>
            <?php while ($row = mysqli_fetch_assoc($byLocation)): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['location_name']); ?></td>
                <td><?php echo $row['total_observations']; ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </section>

      <!-- REPORT 4 -->
      <section class="glass panel report-section">
        <h2>📅 Observations by Month</h2>
        <div class="sql-box">SELECT MONTHNAME(observation_date) AS month_name, COUNT(*) AS total
FROM observations
WHERE observation_date IS NOT NULL
GROUP BY MONTH(observation_date), MONTHNAME(observation_date)
ORDER BY MONTH(observation_date);</div>
        <table class="report-table">
          <thead><tr><th>Month</th><th>Total Observations</th></tr></thead>
          <tbody>
            <?php if (mysqli_num_rows($byMonth) == 0): ?>
              <tr><td colspan="2" style="color:#666">No data yet.</td></tr>
            <?php endif; ?>
            <?php while ($row = mysqli_fetch_assoc($byMonth)): ?>
              <tr>
                <td><?php echo $row['month_name']; ?></td>
                <td><?php echo $row['total']; ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </section>

      <!-- REPORT 5 -->
      <section class="glass panel report-section">
        <h2>🏆 Locations With More Than 1 Observation (HAVING)</h2>
        <div class="sql-box">SELECT locations.location_name, COUNT(observations.observation_id) AS total_observations
FROM observations
JOIN locations ON observations.location_id = locations.location_id
GROUP BY locations.location_name
HAVING COUNT(observations.observation_id) > 1
ORDER BY total_observations DESC;</div>
        <table class="report-table">
          <thead><tr><th>Location</th><th>Total Observations</th></tr></thead>
          <tbody>
            <?php if (mysqli_num_rows($havingReport) == 0): ?>
              <tr><td colspan="2" style="color:#666">No location has more than 1 observation yet.</td></tr>
            <?php endif; ?>
            <?php while ($row = mysqli_fetch_assoc($havingReport)): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['location_name']); ?></td>
                <td><?php echo $row['total_observations']; ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </section>

    </main>
  </div>

  <script src="assets/js/app.js"></script>
</body>
</html>
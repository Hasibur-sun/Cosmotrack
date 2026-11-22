<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include "includes/db.php";

$user_id = $_SESSION['user_id'];

// My logged passes
$myPasses = mysqli_query($conn,
    "SELECT satellite_passes.*, locations.location_name
     FROM satellite_passes
     LEFT JOIN locations ON satellite_passes.location_id = locations.location_id
     WHERE satellite_passes.user_id = $user_id
     ORDER BY pass_date DESC, pass_time DESC"
);

// Stats
$totalPasses = mysqli_fetch_row(mysqli_query($conn,
    "SELECT COUNT(*) FROM satellite_passes WHERE user_id = $user_id"))[0];

$topSatellite = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT satellite_name, COUNT(*) AS total
     FROM satellite_passes
     WHERE user_id = $user_id
     GROUP BY satellite_name
     ORDER BY total DESC
     LIMIT 1"));

$avgElevation = mysqli_fetch_row(mysqli_query($conn,
    "SELECT ROUND(AVG(max_elevation), 1)
     FROM satellite_passes
     WHERE user_id = $user_id"))[0];

// Passes by satellite type (GROUP BY)
$byType = mysqli_query($conn,
    "SELECT satellite_type, COUNT(*) AS total
     FROM satellite_passes
     WHERE user_id = $user_id
     GROUP BY satellite_type
     ORDER BY total DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Satellite Tracker | CosmoTrack</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body data-page="satellite">
  <div class="stars"></div>
  <div class="nebula nebula-one"></div>
  <div class="nebula nebula-two"></div>

  <div class="app-shell">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-panel">
      <button class="mobile-menu" type="button">☰ Menu</button>

      <section class="page-head">
        <div>
          <p class="eyebrow">Satellite Module</p>
          <h1>🛰️ Satellite Tracker</h1>
          <p class="muted">
            Log and track satellite passes. Live tracking powered by N2YO.
          </p>
        </div>
        <a class="btn primary" href="add_satellite.php">+ Log Pass</a>
      </section>

      <!-- STATS -->
      <div class="stats-grid" style="grid-template-columns:repeat(3,1fr); margin-bottom:20px;">
        <div class="stat glass">
          <span>Total Passes Logged</span>
          <strong><?php echo $totalPasses ?? 0; ?></strong>
        </div>
        <div class="stat glass">
          <span>Most Tracked</span>
          <strong style="font-size:13px; margin-top:6px;">
            <?php echo $topSatellite
                ? htmlspecialchars($topSatellite['satellite_name'])
                : '—'; ?>
          </strong>
        </div>
        <div class="stat glass">
          <span>Avg Max Elevation</span>
          <strong><?php echo $avgElevation ?? 0; ?>°</strong>
        </div>
      </div>

      <div class="dashboard-grid">

        <!-- LIVE TRACKER -->
        <section class="glass panel wide" style="margin-bottom:18px;">
          <div class="panel-head">
            <h2>🌍 Live Satellite Map</h2>
            <a class="btn ghost tiny"
               href="https://www.n2yo.com" target="_blank">
              Open Full Tracker →
            </a>
          </div>
          <!-- Live Tracking Buttons -->
<div style="display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-bottom:20px;">

  <a href="https://www.n2yo.com/satellite/?s=25544"
     target="_blank" class="btn ghost"
     style="border-color:rgba(93,232,255,.3); justify-content:center;">
     🛰️ Track ISS Live
  </a>

  <a href="https://www.heavens-above.com/PassSummary.aspx?satid=25544"
     target="_blank" class="btn ghost"
     style="border-color:rgba(139,111,255,.3); justify-content:center;">
     📡 ISS Pass Times
  </a>

  <a href="https://stellarium-web.org/"
     target="_blank" class="btn ghost"
     style="border-color:rgba(255,111,216,.3); justify-content:center;">
     🌌 Stellarium Sky Map
  </a>

</div>

<!-- Embedded Stellarium (works better than N2YO) -->
<div style="border-radius:12px; overflow:hidden; border:1px solid var(--line); position:relative;">
  <iframe
    src="https://stellarium-web.org/"
    width="100%"
    height="450"
    frameborder="0"
    allowfullscreen
    style="display:block;">
  </iframe>
  <div id="iframe-fallback"
       style="display:none; padding:40px; text-align:center; background:rgba(0,0,0,.5);">
    <p style="color:var(--muted); margin-bottom:16px;">
      Live map blocked by browser. Open directly:
    </p>
    <a href="https://www.n2yo.com/satellite/?s=25544"
       target="_blank" class="btn primary">
      Open Satellite Tracker →
    </a>
  </div>
</div>

<p class="muted" style="font-size:12px; margin-top:10px;">
  Live sky map powered by Stellarium Web.
  For real-time satellite tracking open
  <a href="https://www.n2yo.com" target="_blank"
     style="color:var(--cyan);">N2YO.com →</a>
</p>

<script>
// Show fallback if iframe fails to load
const iframe = document.querySelector('.glass.panel.wide iframe');
if (iframe) {
  iframe.addEventListener('error', function() {
    document.getElementById('iframe-fallback').style.display = 'block';
    iframe.style.display = 'none';
  });
}
</script>
        </section>

        <!-- LOG PASS FORM -->
        <section class="glass panel">
          <h2>📝 Log a Satellite Pass</h2>
          <form method="POST" action="actions/add_satellite_pass.php"
                style="display:grid; gap:14px;">

            <label>Satellite Name
              <select name="satellite_name">
                <option value="">-- Select Satellite --</option>
                <option value="ISS (International Space Station)">ISS</option>
                <option value="Hubble Space Telescope">Hubble Space Telescope</option>
                <option value="Starlink Constellation">Starlink Constellation</option>
                <option value="NOAA Weather Satellite">NOAA Weather Satellite</option>
                <option value="Tiangong Space Station">Tiangong Space Station</option>
                <option value="Other">Other</option>
              </select>
            </label>

            <label>Satellite Type
              <select name="satellite_type">
                <option value="Space Station">Space Station</option>
                <option value="Starlink Constellation">Starlink Constellation</option>
                <option value="Space Telescope">Space Telescope</option>
                <option value="Weather Satellite">Weather Satellite</option>
                <option value="Communication Satellite">Communication Satellite</option>
                <option value="Other">Other</option>
              </select>
            </label>

            <label>Pass Date
              <input type="date" name="pass_date" required>
            </label>

            <label>Pass Time
              <input type="time" name="pass_time">
            </label>

            <label>Max Elevation (degrees)
              <input type="number" name="max_elevation"
                     min="0" max="90" placeholder="e.g. 78">
            </label>

            <label>Duration (seconds)
              <input type="number" name="duration_seconds"
                     placeholder="e.g. 360">
            </label>

            <label>Direction Start → End
              <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                <select name="direction_start">
                  <option value="">From</option>
                  <option>N</option><option>NE</option><option>E</option>
                  <option>SE</option><option>S</option><option>SW</option>
                  <option>W</option><option>NW</option>
                </select>
                <select name="direction_end">
                  <option value="">To</option>
                  <option>N</option><option>NE</option><option>E</option>
                  <option>SE</option><option>S</option><option>SW</option>
                  <option>W</option><option>NW</option>
                </select>
              </div>
            </label>

            <label>Brightness (magnitude)
              <input type="text" name="brightness" placeholder="e.g. -3.5 mag">
            </label>

            <label>Notes
              <textarea name="notes" rows="3"
                        placeholder="What did you see?"></textarea>
            </label>

            <button class="btn primary" type="submit">💾 Save Pass</button>
          </form>
        </section>

        <!-- PASS TYPE BREAKDOWN -->
        <section class="glass panel">
          <h2>📊 Passes by Type</h2>
          <p class="muted" style="font-size:12px; margin-bottom:14px;">
            SELECT satellite_type, COUNT(*) GROUP BY satellite_type
          </p>
          <?php if (mysqli_num_rows($byType) == 0): ?>
            <p class="muted">No passes logged yet.</p>
          <?php else: ?>
            <table style="width:100%">
              <thead>
                <tr>
                  <th>Type</th>
                  <th>Total</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($row = mysqli_fetch_assoc($byType)): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($row['satellite_type']); ?></td>
                    <td>
                      <span class="badge badge-cyan">
                        <?php echo $row['total']; ?>
                      </span>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </section>

      </div>

      <!-- MY LOGGED PASSES TABLE -->
      <section class="glass panel" style="margin-top:18px;">
        <div class="panel-head">
          <h2>My Logged Satellite Passes</h2>
          <span class="badge badge-cyan"><?php echo $totalPasses; ?> passes</span>
        </div>

        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Satellite</th>
                <th>Type</th>
                <th>Date</th>
                <th>Time</th>
                <th>Max Elevation</th>
                <th>Duration</th>
                <th>Direction</th>
                <th>Brightness</th>
                <th>Notes</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (mysqli_num_rows($myPasses) == 0): ?>
                <tr>
                  <td colspan="10"
                      style="text-align:center; color:var(--muted); padding:30px;">
                    No passes logged yet. Use the form to log your first one.
                  </td>
                </tr>
              <?php endif; ?>

              <?php while ($row = mysqli_fetch_assoc($myPasses)): ?>
                <tr>
                  <td>
                    <strong style="color:var(--text);">
                      <?php echo htmlspecialchars($row['satellite_name']); ?>
                    </strong>
                  </td>
                  <td>
                    <span class="badge badge-violet">
                      <?php echo htmlspecialchars($row['satellite_type']); ?>
                    </span>
                  </td>
                  <td><?php echo $row['pass_date']; ?></td>
                  <td><?php echo $row['pass_time'] ?: '—'; ?></td>
                  <td>
                    <span class="badge badge-cyan">
                      <?php echo $row['max_elevation']; ?>°
                    </span>
                  </td>
                  <td><?php echo $row['duration_seconds']
                        ? $row['duration_seconds'] . 's'
                        : '—'; ?></td>
                  <td>
                    <?php echo $row['direction_start']
                        ? $row['direction_start'] . ' → ' . $row['direction_end']
                        : '—'; ?>
                  </td>
                  <td><?php echo htmlspecialchars($row['brightness'] ?: '—'); ?></td>
                  <td style="max-width:140px; font-size:12px; color:var(--muted);">
                    <?php echo htmlspecialchars($row['notes'] ?: '—'); ?>
                  </td>
                  <td>
                    <a class="danger"
                       href="actions/delete_satellite_pass.php?id=<?php echo $row['pass_id']; ?>"
                       onclick="return confirm('Delete this pass?')">
                      Delete
                    </a>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </section>

    </main>
  </div>
  <script src="assets/js/app.js"></script>
</body>
</html>
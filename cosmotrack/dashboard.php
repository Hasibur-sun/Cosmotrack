<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard | CosmoTrack</title>
  <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body data-page="dashboard">
  <div class="stars"></div>
  <div class="nebula nebula-one"></div>
  <div class="nebula nebula-two"></div>

  <div class="app-shell">

    <?php include 'includes/sidebar.php'; ?>

    <main class="main-panel">
      <button class="mobile-menu" type="button">☰ Menu</button>

      <section class="page-head">
        <div>
          <p class="eyebrow">Control Center</p>
          <h1>Welcome to CosmoTrack</h1>
          <p class="muted">Astronomy Observation and Research Data Management System.</p>
        </div>
      </section>

      <!-- LIVE STATS FROM MYSQL -->
      <?php
        $totalObs = mysqli_fetch_row(mysqli_query($conn,
    "SELECT COUNT(*) FROM observations
     WHERE user_id = {$_SESSION['user_id']}"))[0];
        $totalLoc      = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM locations"))[0];
        $totalTel      = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM telescopes"))[0];
        $totalUsers    = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users"))[0];
        $totalEvents   = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM public_events WHERE event_date >= CURDATE()"))[0];
        $totalFacts    = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM space_facts"))[0];
      ?>

      <div class="stats-grid">
        <div class="stat glass">
          <span>Total Observations</span>
          <strong><?php echo $totalObs; ?></strong>
        </div>
        <div class="stat glass">
          <span>Saved Locations</span>
          <strong><?php echo $totalLoc; ?></strong>
        </div>
        <div class="stat glass">
          <span>Telescopes</span>
          <strong><?php echo $totalTel; ?></strong>
        </div>
        <div class="stat glass">
          <span>Registered Users</span>
          <strong><?php echo $totalUsers; ?></strong>
        </div>
        <div class="stat glass">
          <span>Upcoming Events</span>
          <strong><?php echo $totalEvents; ?></strong>
        </div>
        <div class="stat glass">
          <span>Space Facts</span>
          <strong><?php echo $totalFacts; ?></strong>
        </div>
      </div>

      <!-- QUICK ACTIONS -->
      <div class="dashboard-grid">
        <section class="glass panel">
          <h2>Quick Actions</h2>
          <div class="quick-grid">
            <a class="quick" href="observation.php">+ Observation</a>
            <a class="quick" href="locations.html">+ Location</a>
            <a class="quick" href="telescopes.html">+ Telescope</a>
            <a class="quick" href="events.php">View Events</a>
            <a class="quick" href="fact.php">Random Fact</a>
            <a class="quick" href="reports.php">SQL Reports</a>
          </div>
        </section>

        <!-- UPCOMING EVENT TICKER -->
        <section class="glass panel">
          <h2>🌠 Next Upcoming Event</h2>
          <?php
            $nextEvent = mysqli_fetch_assoc(mysqli_query($conn,
              "SELECT event_title, event_type, event_date
               FROM public_events
               WHERE event_date >= CURDATE()
               ORDER BY event_date ASC
               LIMIT 1"
            ));
          ?>
          <?php if ($nextEvent): ?>
            <p style="color:#4fc3f7; font-size:1.1rem;">
              <?php echo htmlspecialchars($nextEvent['event_title']); ?>
            </p>
            <p class="muted">
              <?php echo $nextEvent['event_type']; ?> —
              <?php echo $nextEvent['event_date']; ?>
            </p>
            <a class="btn ghost" href="events.php">See All Events →</a>
          <?php else: ?>
            <p class="muted">No upcoming events found.</p>
          <?php endif; ?>
        </section>

        <!-- RANDOM FACT -->
        <section class="glass panel">
          <h2>✨ Cosmic Fact of the Day</h2>
          <?php
            $todayFact = mysqli_fetch_assoc(mysqli_query($conn,
              "SELECT fact_text, category FROM space_facts ORDER BY RAND() LIMIT 1"
            ));
          ?>
          <?php if ($todayFact): ?>
            <p class="muted" style="font-style:italic;">
              "<?php echo htmlspecialchars($todayFact['fact_text']); ?>"
            </p>
            <p style="color:#4fc3f7; font-size:0.85rem;">
              Category: <?php echo htmlspecialchars($todayFact['category']); ?>
            </p>
            <a class="btn ghost" href="fact.php">Another Fact →</a>
          <?php else: ?>
            <p class="muted">No facts found in database.</p>
          <?php endif; ?>
        </section>

        <!-- RECENT OBSERVATIONS -->
        <section class="glass panel wide">
          <h2>Recent Observations</h2>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Object</th>
                  <th>Type</th>
                  <th>Date</th>
                  <th>Location</th>
                  <th>Quality</th>
                </tr>
              </thead>
              <tbody>
                <?php
                 $recent = mysqli_query($conn,
    "SELECT observations.object_name, observations.object_type,
            observations.observation_date, observations.quality_score,
            locations.location_name
     FROM observations
     LEFT JOIN locations ON observations.location_id = locations.location_id
     WHERE observations.user_id = {$_SESSION['user_id']}
     ORDER BY observations.observation_id DESC
     LIMIT 5"
);
                  if (mysqli_num_rows($recent) == 0):
                ?>
                  <tr><td colspan="5" style="color:#666">No observations yet.</td></tr>
                <?php else: ?>
                  <?php while ($r = mysqli_fetch_assoc($recent)): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($r['object_name']); ?></td>
                      <td><?php echo htmlspecialchars($r['object_type']); ?></td>
                      <td><?php echo $r['observation_date'] ?: '—'; ?></td>
                      <td><?php echo $r['location_name'] ? htmlspecialchars($r['location_name']) : '—'; ?></td>
                      <td><?php echo $r['quality_score']; ?></td>
                    </tr>
                  <?php endwhile; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </section>

      </div>
    </main>
  </div>

  <script src="assets/js/app.js"></script>
</body>
</html>
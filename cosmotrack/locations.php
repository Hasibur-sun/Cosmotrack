<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Add Location | CosmoTrack</title>
  <link rel="stylesheet" href="assets/css/style.css" />
</head>

<body data-page="locations">
  <div class="stars"></div>
  <div class="nebula nebula-one"></div>
  <div class="nebula nebula-two"></div>

  <div class="app-shell">

    <?php include 'includes/sidebar.php'; ?>

    <main class="main-panel">
      <button class="mobile-menu" type="button">☰ Menu</button>

      <section class="page-head">
        <div>
          <p class="eyebrow">Location Module</p>
          <h1>Add Location</h1>
          <p class="muted">Add a new observation location to the MySQL database.</p>
        </div>
        <a class="btn ghost" href="view_locations.php">View All →</a>
      </section>

      <section class="glass panel">
        <div class="panel-head">
          <h2>New Location Record</h2>
        </div>

        <form class="form-grid" method="POST" action="actions/add_location.php">

          <label>Location Name
            <input type="text" name="location_name" required
                   placeholder="e.g. Rooftop Observatory">
          </label>

          <label>City
            <input type="text" name="city" placeholder="e.g. Dhaka">
          </label>

          <label>Country
            <input type="text" name="country" placeholder="e.g. Bangladesh">
          </label>

          <label>Bortle Scale
            <select name="bortle_scale">
              <option value="">-- Select Bortle Scale --</option>
              <option value="1">1 — Excellent Dark Sky</option>
              <option value="2">2 — Typical Truly Dark Site</option>
              <option value="3">3 — Rural Sky</option>
              <option value="4">4 — Rural/Suburban Transition</option>
              <option value="5">5 — Suburban Sky</option>
              <option value="6">6 — Bright Suburban Sky</option>
              <option value="7">7 — Suburban/Urban Transition</option>
              <option value="8">8 — City Sky</option>
              <option value="9">9 — Inner City Sky</option>
            </select>
          </label>

          <label>Latitude
            <input type="text" name="latitude" placeholder="e.g. 23.8103">
          </label>

          <label>Longitude
            <input type="text" name="longitude" placeholder="e.g. 90.4125">
          </label>

          <div class="form-actions">
            <button class="btn primary" type="submit">💾 Save Location</button>
            <a class="btn ghost" href="view_locations.php">Cancel</a>
          </div>

        </form>
      </section>

    </main>
  </div>

  <script src="assets/js/app.js"></script>
</body>
</html>
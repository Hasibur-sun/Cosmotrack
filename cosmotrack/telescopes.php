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
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Telescope | CosmoTrack</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body data-page="telescopes">

<div class="stars"></div>
<div class="nebula nebula-one"></div>
<div class="nebula nebula-two"></div>

<div class="app-shell">

  <?php include 'includes/sidebar.php'; ?>

  <main class="main-panel">
    <button class="mobile-menu" type="button">☰ Menu</button>

    <section class="page-head">
      <div>
        <p class="eyebrow">Equipment</p>
        <h1>Add Telescope</h1>
        <p class="muted">Add your personal telescope to the MySQL database.</p>
      </div>
      <a class="btn ghost" href="view_telescopes.php">View All →</a>
    </section>

    <section class="glass panel">
      <div class="panel-head">
        <h2>New Telescope Record</h2>
      </div>

      <form class="form-grid" method="POST" action="actions/add_telescope.php">

        <label>Telescope Name
          <input type="text" name="telescope_name" required
                 placeholder="e.g. Celestron NexStar 8SE">
        </label>

        <label>Telescope Type
          <select name="telescope_type">
            <option value="">-- Select Type --</option>
            <option value="Refractor">Refractor</option>
            <option value="Reflector">Reflector</option>
            <option value="Cassegrain">Cassegrain</option>
            <option value="Dobsonian">Dobsonian</option>
            <option value="Schmidt-Cassegrain">Schmidt-Cassegrain</option>
            <option value="Maksutov">Maksutov</option>
            <option value="Binoculars">Binoculars</option>
            <option value="Naked Eye">Naked Eye</option>
            <option value="Other">Other</option>
          </select>
        </label>

        <label>Aperture (mm)
          <input type="text" name="aperture" placeholder="e.g. 200mm">
        </label>

        <label>Focal Length (mm)
          <input type="text" name="focal_length" placeholder="e.g. 2000mm">
        </label>

        <label>Mount Type
          <select name="mount_type">
            <option value="">-- Select Mount --</option>
            <option value="Alt-Azimuth">Alt-Azimuth</option>
            <option value="Equatorial">Equatorial</option>
            <option value="Dobsonian">Dobsonian</option>
            <option value="GoTo">GoTo (Computerized)</option>
            <option value="Tracking">Tracking Mount</option>
            <option value="Tripod">Tripod</option>
            <option value="None">None / Handheld</option>
          </select>
        </label>

        <label>Aperture Ratio (f/)
          <input type="text" name="aperture_ratio" placeholder="e.g. f/10">
        </label>

        <label style="grid-column:1/-1">Description
          <textarea name="description"
                    placeholder="Any extra details about this telescope..."></textarea>
        </label>

        <div class="form-actions">
          <button class="btn primary" type="submit">💾 Save Telescope</button>
          <a class="btn ghost" href="view_telescopes.php">Cancel</a>
        </div>

      </form>
    </section>

  </main>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>
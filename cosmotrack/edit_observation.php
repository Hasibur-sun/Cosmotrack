<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include "includes/db.php";

$id  = (int)($_GET['id'] ?? 0);
$obs = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM observations WHERE observation_id = $id"
));

if (!$obs) {
    header("Location: view_observations.php");
    exit();
}

$user_id    = $_SESSION['user_id'];
$locations  = mysqli_query($conn, "SELECT * FROM locations ORDER BY location_name ASC");
$telescopes = mysqli_query($conn,
    "SELECT * FROM telescopes WHERE user_id = $user_id ORDER BY telescope_name ASC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Observation | CosmoTrack</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body data-page="observations">
  <div class="stars"></div>
  <div class="nebula nebula-one"></div>
  <div class="nebula nebula-two"></div>

  <div class="app-shell">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-panel">
      <button class="mobile-menu" type="button">☰ Menu</button>

      <section class="page-head">
        <div>
          <p class="eyebrow">Edit Record</p>
          <h1>Edit Observation #<?php echo $id; ?></h1>
          <p class="muted">Update this observation record in the MySQL database.</p>
        </div>
        <a class="btn ghost" href="view_observations.php">← Back</a>
      </section>

      <section class="glass panel">
        <form class="form-grid" method="POST" action="actions/edit_observation.php">
          <input type="hidden" name="observation_id" value="<?php echo $id; ?>">

          <label>Object Name
            <input type="text" name="object_name" required
                   value="<?php echo htmlspecialchars($obs['object_name']); ?>">
          </label>

          <label>Object Type
            <select name="object_type">
              <?php
                $types = ['Planet','Moon','Star','Galaxy','Nebula','Comet','Asteroid','Meteor','Cluster','Other'];
                foreach ($types as $t) {
                  $sel = $obs['object_type'] == $t ? 'selected' : '';
                  echo "<option value='$t' $sel>$t</option>";
                }
              ?>
            </select>
          </label>

          <label>Observation Date
            <input type="date" name="observation_date"
                   value="<?php echo $obs['observation_date']; ?>">
          </label>

          <label>Observation Time
            <input type="time" name="observation_time"
                   value="<?php echo $obs['observation_time']; ?>">
          </label>

          <label>Location
            <select name="location_id">
              <option value="">-- None --</option>
              <?php while ($loc = mysqli_fetch_assoc($locations)): ?>
                <option value="<?php echo $loc['location_id']; ?>"
                  <?php echo $obs['location_id'] == $loc['location_id'] ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($loc['location_name']); ?>
                </option>
              <?php endwhile; ?>
            </select>
          </label>

          <label>My Telescope
            <select name="telescope_id">
              <option value="">-- None --</option>
              <?php while ($tel = mysqli_fetch_assoc($telescopes)): ?>
                <option value="<?php echo $tel['telescope_id']; ?>"
                  <?php echo $obs['telescope_id'] == $tel['telescope_id'] ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($tel['telescope_name']); ?>
                </option>
              <?php endwhile; ?>
            </select>
          </label>

          <label>Sky Condition
            <select name="sky_condition">
              <?php
                $skies = ['Clear','Partly Cloudy','Hazy','Cloudy','Windy'];
                foreach ($skies as $s) {
                  $sel = $obs['sky_condition'] == $s ? 'selected' : '';
                  echo "<option value='$s' $sel>$s</option>";
                }
              ?>
            </select>
          </label>

          <label>Visibility
            <select name="visibility">
              <option value="Public"  <?php echo $obs['visibility'] == 'Public'  ? 'selected' : ''; ?>>Public</option>
              <option value="Private" <?php echo $obs['visibility'] == 'Private' ? 'selected' : ''; ?>>Private</option>
            </select>
          </label>

          <label style="grid-column:1/-1">Notes
            <textarea name="notes"><?php echo htmlspecialchars($obs['notes']); ?></textarea>
          </label>

          <div class="form-actions">
            <button class="btn primary" type="submit">💾 Save Changes</button>
            <a class="btn ghost" href="view_observations.php">Cancel</a>
          </div>
        </form>
      </section>
    </main>
  </div>
  <script src="assets/js/app.js"></script>
</body>
</html>
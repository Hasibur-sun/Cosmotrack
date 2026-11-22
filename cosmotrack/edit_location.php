<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include "includes/db.php";

$id  = (int)($_GET['id'] ?? 0);
$loc = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM locations WHERE location_id = $id"
));

if (!$loc) {
    header("Location: view_locations.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Location | CosmoTrack</title>
  <link rel="stylesheet" href="assets/css/style.css">
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
          <p class="eyebrow">Edit Record</p>
          <h1>Edit Location #<?php echo $id; ?></h1>
          <p class="muted">Update this location record in MySQL.</p>
        </div>
        <a class="btn ghost" href="view_locations.php">← Back</a>
      </section>

      <section class="glass panel">
        <form class="form-grid" method="POST" action="actions/edit_location.php">
          <input type="hidden" name="location_id" value="<?php echo $id; ?>">

          <label>Location Name
            <input type="text" name="location_name" required
                   value="<?php echo htmlspecialchars($loc['location_name']); ?>">
          </label>

          <label>City
            <input type="text" name="city"
                   value="<?php echo htmlspecialchars($loc['city']); ?>">
          </label>

          <label>Country
            <input type="text" name="country"
                   value="<?php echo htmlspecialchars($loc['country']); ?>">
          </label>

          <label>Bortle Scale (1-9)
            <input type="number" name="bortle_scale" min="1" max="9"
                   value="<?php echo $loc['bortle_scale']; ?>">
          </label>

          <label>Latitude
            <input type="text" name="latitude"
                   value="<?php echo htmlspecialchars($loc['latitude']); ?>">
          </label>

          <label>Longitude
            <input type="text" name="longitude"
                   value="<?php echo htmlspecialchars($loc['longitude']); ?>">
          </label>

          <div class="form-actions">
            <button class="btn primary" type="submit">💾 Save Changes</button>
            <a class="btn ghost" href="view_locations.php">Cancel</a>
          </div>
        </form>
      </section>
    </main>
  </div>
  <script src="assets/js/app.js"></script>
</body>
</html>
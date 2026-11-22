<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include "includes/db.php";

$id  = (int)($_GET['id'] ?? 0);
$tel = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM telescopes WHERE telescope_id = $id"
));

if (!$tel) {
    header("Location: view_telescopes.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Telescope | CosmoTrack</title>
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
          <p class="eyebrow">Edit Record</p>
          <h1>Edit Telescope #<?php echo $id; ?></h1>
          <p class="muted">Update this telescope record in MySQL.</p>
        </div>
        <a class="btn ghost" href="view_telescopes.php">← Back</a>
      </section>

      <section class="glass panel">
        <form class="form-grid" method="POST" action="actions/edit_telescope.php">
          <input type="hidden" name="telescope_id" value="<?php echo $id; ?>">

          <label>Telescope Name
            <input type="text" name="telescope_name" required
                   value="<?php echo htmlspecialchars($tel['telescope_name']); ?>">
          </label>

          <label>Telescope Type
            <input type="text" name="telescope_type"
                   value="<?php echo htmlspecialchars($tel['telescope_type']); ?>">
          </label>

          <label>Aperture
            <input type="text" name="aperture"
                   value="<?php echo htmlspecialchars($tel['aperture']); ?>">
          </label>

          <label>Focal Length
            <input type="text" name="focal_length"
                   value="<?php echo htmlspecialchars($tel['focal_length']); ?>">
          </label>

          <label>Mount Type
            <input type="text" name="mount_type"
                   value="<?php echo htmlspecialchars($tel['mount_type']); ?>">
          </label>

          <label style="grid-column:1/-1">Description
            <textarea name="description"><?php echo htmlspecialchars($tel['description']); ?></textarea>
          </label>

          <div class="form-actions">
            <button class="btn primary" type="submit">💾 Save Changes</button>
            <a class="btn ghost" href="view_telescopes.php">Cancel</a>
          </div>
        </form>
      </section>
    </main>
  </div>
  <script src="assets/js/app.js"></script>
</body>
</html>